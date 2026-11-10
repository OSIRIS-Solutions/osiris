<?php
// Run: php tests/person-fields.php. Shared in-memory application harness; no live database.
ob_start();
require __DIR__ . '/initiatives.php';
$baselineOutput = ob_get_clean();
ob_start(); // Keep headers available while exercising write routes.
require_once BASEPATH . '/php/PersonField.php';
loadIsolated('php/Render.php');
loadIsolated('routes/activities.php');
$baselineChecks = $checks;
$Settings->permissions = ['initiatives.view', 'initiatives.edit', 'activities.edit'];
$Groups = new class {
    public function getParents($unit, $includeRoot = false) { return ['ROOT']; }
    public function getGroup($unit) { return ['id' => $unit, 'name' => ['en' => $unit]]; }
};
$osiris->persons->updateOne(['username' => 'reviewer'], ['$set' => [
    'first' => 'Rae', 'last' => 'Reviewer', 'units' => [['unit' => 'SCI', 'scientific' => true]],
]]);
$osiris->persons->insertOne(['username' => 'colleague', 'first' => 'Cam', 'last' => 'Colleague']);
$input = [
    ['user' => 'reviewer', 'last' => 'Wrong', 'first' => 'Wrong', 'role' => 'lead', 'aoi' => '1'],
    ['user' => 'colleague', 'role' => 'co-lead', 'aoi' => 'false'],
    ['user' => '', 'last' => 'External', 'first' => 'Eve', 'role' => 'member', 'aoi' => '0'],
];
$people = validateValues(['persons' => $input], $DB, 'activities')['persons'];
check($people[0]['first'] === 'Rae' && $people[0]['last'] === 'Reviewer', 'Linked accounts supply canonical names.');
check($people[0]['role'] === 'lead' && !isset($people[0]['position']), 'Persons have roles rather than bibliographic positions.');
check($people[0]['approved'] === true && $people[1]['approved'] === false, 'Approval is not spoofed by the submitted person.');
check($people[1]['aoi'] === false && $people[2]['user'] === null, 'Boolean flags and external persons normalize correctly.');
check(validateValues(['persons' => ''], $DB, 'activities')['persons'] === [], 'An explicit empty person field clears the list.');
check(PersonField::normalize([[], ['last' => []]], $DB) === [], 'Blank or malformed rows do not create phantom people.');
$legacy = validateValues(['persons' => [['name' => 'Existing project member', 'role' => 'applicant', 'allocation' => '50']]], $DB);
check($legacy['persons'][0]['name'] === 'Existing project member' && $legacy['persons'][0]['role'] === 'applicant' && $legacy['persons'][0]['allocation'] === 50, 'Existing project-person metadata remains outside the new module normalizer.');
check(PersonField::defaultRole([
    ['id' => 'member', 'inactive' => true], ['id' => 'custom-lead', 'en' => 'Coordinator'],
]) === 'custom-lead', 'New rows never default to an inactive role.');

$formatter = new Document(false, 'list');
$formatter->doc = ['authors' => [$people[0]], 'editors' => [$people[1]], 'persons' => $people];
check($formatter->getUsers() === ['reviewer', 'colleague'], 'All role groups contribute a deduplicated user union.');
check($formatter->getUsers(true) === ['reviewer'], 'Affiliated users respect person affiliation flags.');
check(Document::author_role_from_field('persons') === 'persons' && Document::author_role_from_field('persons-role') === 'persons', 'Both modules are recognized contributor fields.');
check(str_contains($formatter->get_field('persons-role'), '/profile/reviewer') && str_contains($formatter->get_field('persons-role'), '(Lead)'), 'Persons render profile links and vocabulary role labels.');

foreach (['activities', 'initiatives'] as $entity) {
    $config = new FormBuilder('Test', '', entity: $entity);
    check(isset($config->modules['persons'], $config->modules['persons-role']), 'Both person modules are offered for ' . $entity);
    check($config->modules['persons']['fields'] === ['persons'] && $config->modules['persons-role']['fields'] === ['persons'], 'Both modules use the same storage field.');
}
$catalog = array_column(json_decode(file_get_contents(BASEPATH . '/data/vocabulary.json'), true), null, 'id');
check(array_column($catalog['persons-role']['values'], 'id') === ['member', 'lead', 'co-lead'], 'The vocabulary provides the requested defaults.');
rejects(fn() => FormBuilder::validateFields([
    ['id' => 'persons', 'type' => 'field'], ['id' => 'persons-role', 'type' => 'field'],
]), 'The shared storage field cannot be added twice.');

Vocabulary::$roles = [
    ['id' => 'member', 'en' => 'Participant', 'de' => 'Teilnehmend'],
    ['id' => 'lead', 'en' => 'Former lead', 'de' => 'Frühere Leitung', 'inactive' => true],
];
$modules = new Modules(['persons' => [$people[0]]]);
foreach (['persons', 'persons-role'] as $module) {
    ob_start(); $modules->print_module($module); $html = ob_get_clean();
    check(str_contains($html, 'values[persons][0][user]'), 'Module writes structured person references.');
    check(!str_contains($html, 'values[authors]'), 'Person module does not overwrite author input.');
    if ($module === 'persons-role') {
        check(str_contains($html, 'Former lead') && str_contains($html, 'Participant'), 'Selected inactive roles and customized labels remain available.');
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        check($xpath->query('//table/tbody/tr/td/select/option[@value="lead" and @selected]')->length === 1, 'The existing inactive role remains selected.');
    } else check(str_contains($html, 'type="hidden" name="values[persons][0][role]"'), 'The role-free variant retains a previously stored role.');
    if (isset($argv[1])) file_put_contents($argv[1] . '/' . $module . '.html', $html);
}
Vocabulary::$roles = null;

// Exercise the production ownership check, rather than the harness stand-in.
$dbSource = file_get_contents(BASEPATH . '/php/DB.php');
$methodStart = strpos($dbSource, '    public static function isUserActivity(');
$methodEnd = strpos($dbSource, '    /**', $methodStart);
eval(str_replace('public static function isUserActivity', 'function productionIsUserActivity', substr($dbSource, $methodStart, $methodEnd - $methodStart)));
check(productionIsUserActivity(['rendered' => ['users' => ['colleague']]], 'colleague'), 'Rendered person membership grants the same ownership as other roles.');
check(!productionIsUserActivity(['rendered' => ['users' => ['colleague']]], 'reviewer'), 'Unrelated users do not acquire ownership.');
check(productionIsUserActivity(['persons' => [$people[0]]], 'reviewer'), 'Unrendered records recognize the new person field as a fallback.');

// Real rendering persists person-only activity memberships, without manufacturing authors.
$activityId = new MongoDB\BSON\ObjectId();
$activity = [
    '_id' => $activityId, 'type' => 'misc', 'subtype' => 'person-test', 'title' => 'Person-only activity',
    'year' => 2026, 'month' => 11, 'day' => 10, 'persons' => $people,
    'start' => ['year' => 2026, 'month' => 11, 'day' => 10], 'end' => null,
    'rendered' => ['users' => ['reviewer', 'colleague']],
];
$osiris->activities->insertOne($activity);
$osiris->adminTypes->insertOne(['id' => 'person-test', 'name' => 'Person test', 'icon' => 'users', 'fields' => [
    ['id' => 'persons-role', 'type' => 'field', 'props' => []],
]]);
renderActivities(['_id' => $activityId]);
$rendered = $osiris->activities->findOne(['_id' => $activityId]);
check($rendered['rendered']['users'] === ['reviewer', 'colleague'], 'Rendering stores persons in rendered.users.');
check(!isset($rendered['authors']), 'Person-only activities do not manufacture author records.');
check($rendered['rendered']['affiliated_users'] === ['reviewer'] && $rendered['affiliated'] === true, 'Rendering stores affiliation for person-only activities.');
check(str_contains($rendered['rendered']['persons'], '/profile/reviewer'), 'Rendered persons preserve person links.');
renderAuthorUnitsMany(['_id' => $activityId]);
$rendered = $osiris->activities->findOne(['_id' => $activityId]);
check(in_array('SCI', $rendered['persons'][0]['units'], true) && in_array('ROOT', $rendered['units'], true), 'Bulk unit rendering loads and processes persons.');

// Separate contributor editor supports vocabulary roles, security and clearing optional persons.
$role = 'persons'; $form = $rendered; $id = (string) $activityId;
ob_start(); loadIsolated('pages/author-editor.php'); $editorHtml = ob_get_clean();
check(str_contains($editorHtml, '/crud/activities/update-persons/' . $id), 'Editor submits to the persons endpoint.');
check(str_contains($editorHtml, 'authors[0][role]') && str_contains($editorHtml, 'Co-Lead'), 'Editor renders role choices for persons-role.');
if (isset($argv[1])) file_put_contents($argv[1] . '/author-editor-persons.html', $editorHtml);
postRoute('/crud/activities/update-(authors|editors|supervisors|persons)/([A-Za-z0-9]*)', ['authors' => [
    ['user' => 'colleague', 'last' => 'Colleague', 'first' => 'Cam', 'role' => 'member', 'aoi' => '1'],
]], 'persons', $id);
$updated = $osiris->activities->findOne(['_id' => $activityId]);
check($updated['persons'][0]['user'] === 'colleague' && $updated['rendered']['users'] === ['colleague'], 'Editor updates raw and rendered person memberships.');
postRoute('/crud/activities/update-(authors|editors|supervisors|persons)/([A-Za-z0-9]*)', ['authors' => ''], 'persons', $id);
$updated = $osiris->activities->findOne(['_id' => $activityId]);
check($updated['persons'] === [] && $updated['rendered']['users'] === [], 'Removing the last optional person clears the rendered union.');
$osiris->adminTypes->updateOne(['id' => 'person-test'], ['$set' => ['fields' => [
    ['id' => 'persons-role', 'type' => 'field', 'props' => ['required' => true]],
]]]);
aborts(fn() => postRoute('/crud/activities/update-(authors|editors|supervisors|persons)/([A-Za-z0-9]*)', ['authors' => [['last' => '', 'first' => '']]], 'persons', $id), 400);
$Settings->permissions = [];
aborts(fn() => postRoute('/crud/activities/update-(authors|editors|supervisors|persons)/([A-Za-z0-9]*)', ['authors' => []], 'persons', $id), 403);
$Settings->permissions = ['activities.edit'];
$osiris->activities->updateOne(['_id' => $activityId], ['$set' => ['locked' => true]]);
aborts(fn() => postRoute('/crud/activities/update-(authors|editors|supervisors|persons)/([A-Za-z0-9]*)', ['authors' => []], 'persons', $id), 403);

ob_end_clean();
echo $baselineOutput;
echo 'Person-field regression tests passed (' . ($checks - $baselineChecks) . " checks).\n";
