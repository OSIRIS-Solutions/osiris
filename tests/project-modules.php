<?php
// Run: php tests/project-modules.php. Production renderers and normalizers, no database.
ob_start();
require __DIR__ . '/initiatives.php';
$baselineOutput = ob_get_clean();
ob_start();
$baselineChecks = $checks;
require_once BASEPATH . '/php/ProjectPersons.php';
$osiris->persons->updateOne(['username' => 'reviewer'], ['$set' => [
    'first' => 'Rae', 'last' => 'Reviewer', 'units' => [['unit' => 'SCI', 'scientific' => true]],
]]);
foreach (['projects', 'proposals'] as $entity) {
    $config = new FormBuilder('Project', '', entity: $entity);
    foreach (['persons-project', 'institute-role', 'project-date', 'website'] as $id) check(isset($config->modules[$id]), "$id is available for $entity.");
    check($config->systemFields['persons-project']['required'] === true, 'Every project/proposal builder requires the project persons module.');
    $fields = FormBuilder::prepareFields([], $config->systemFields);
    check($fields[0]['id'] === 'persons-project', 'Missing persons module is automatically inserted.');
    rejects(fn() => FormBuilder::validateFields([], $config->systemFields), 'Removing project persons is rejected.');
}
$persons = [['user' => 'reviewer', 'name' => 'Outdated', 'role' => 'applicant', 'start' => '2026-01-01', 'end' => '2027-12-31', 'units' => ['SCI', '', 'SCI']]];
foreach (['projects', 'proposals'] as $entity) {
    $values = validateValues(['persons' => $persons, 'role' => 'associated', 'start' => '2026-01-01', 'end' => '2027-12-31', 'website' => 'https://example.org'], $DB, $entity);
    check($values['persons'][0]['name'] === 'Rae Reviewer', 'Project person names are resolved from accounts.');
    check($values['persons'][0]['units'] === ['SCI'], 'Unit selections are normalized.');
    check($values['persons'][0]['start'] === '2026-01-01' && $values['persons'][0]['end'] === '2027-12-31', 'Membership dates stay strings, compatible with the existing project person editor.');
    check(isset($values['start']['year']) && $values['start_date'] === '2026-01-01', 'Project dates retain the existing date schema.');
    check(!isset($values['persons'][0]['first'], $values['persons'][0]['aoi']), 'Activity-specific person fields are not introduced.');
}
check(ProjectPersons::normalize('', $DB) === [], 'An empty project persons input becomes an empty list.');
check(ProjectPersons::normalize([['user' => 'reviewer', 'start' => '2026-02-30', 'units' => 'bad']], $DB)[0]['start'] === null, 'Invalid membership dates and malformed unit lists are handled.');
check(ProjectPersons::normalize([['user' => 'removed', 'name' => 'Former colleague', 'role' => 'custom', 'units' => ['OLD']]], $DB)[0]['name'] === 'Former colleague', 'Existing references to removed accounts are preserved.');
$renderer = new Modules(['persons' => $values['persons'], 'role' => 'associated', 'start_date' => '2026-01-01', 'end_date' => '2027-12-31', 'website' => 'https://example.org/?a=1&b=2']);
foreach (['persons-project', 'institute-role', 'project-date', 'website'] as $id) {
    ob_start(); $renderer->print_module($id, true, ['help' => 'Help']); $html = ob_get_clean();
    check(str_contains($html, 'Help'), "$id retains configured help text.");
    $dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
    if ($id === 'persons-project') {
        check($xpath->query('//select[@name="values[persons][0][user]"]/option[@selected and @value="reviewer"]')->length === 1, 'Project members are preselected.');
        check($xpath->query('//input[@name="values[persons][0][units][]" and @checked and @value="SCI"]')->length === 1, 'Membership units are retained.');
        check($xpath->query('//select[@name="values[persons][0][role]"]/option[@selected and @value="applicant"]')->length === 1, 'Project person vocabulary roles are retained.');
    } elseif ($id === 'institute-role') {
        check($xpath->query('//select[@name="values[role]"]/option[@selected and @value="associated"]')->length === 1, 'Institute role uses the project vocabulary and saves in role.');
    } elseif ($id === 'project-date') {
        check($xpath->query('//input[@name="values[start]" and @value="2026-01-01" and @required]')->length === 1, 'Project start supports existing date strings.');
        check($xpath->query('//input[@name="values[end]" and @value="2027-12-31" and @required]')->length === 1, 'Project end supports existing end_date values and remains required.');
    } else check($xpath->query('//input[@type="url" and @name="values[website]"]')->length === 1, 'Website stores its own field.');
    if (isset($argv[1])) file_put_contents($argv[1] . '/' . $id . '.html', $html);
}
// Project dates are opt-in; proposals retain their data without showing date controls.
foreach (['project', 'proposed'] as $status) {
    $renderer = new Modules(['status' => $status, 'persons' => $persons, 'start_date' => '2026-01-01', 'end_date' => '2027-12-31']);
    ob_start(); $renderer->print_module('persons-project', true); $html = ob_get_clean();
    $dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
    if ($status === 'proposed') {
        check($xpath->query('//input[@type="date"]')->length === 0, 'Proposals have no editable membership dates.');
        check($xpath->query('//input[@type="hidden" and @name="values[persons][0][start]" and @value="2026-01-01"]')->length === 1, 'Editing a proposal preserves existing membership dates.');
    } else {
        check($xpath->query('//div[contains(@class,"project-person-dates") and @id="project-person-dates-0" and @hidden]')->length === 1, 'Membership matching the project period starts in inherited mode.');
    }
}
$persons[0]['start'] = '2026-06-01';
$renderer = new Modules(['status' => 'project', 'persons' => $persons, 'start_date' => '2026-01-01', 'end_date' => '2027-12-31']);
ob_start(); $renderer->print_module('persons-project', true); $html = ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
check($xpath->query('//div[@id="project-person-dates-0" and not(@hidden)]//input[@data-person-date="start" and @value="2026-06-01"]')->length === 1, 'Existing individual dates are visible and retained.');
$format = new Document(false, 'web');
$format->doc = $values;
check(str_contains($format->get_field('persons-project'), 'Rae Reviewer') && str_contains($format->get_field('persons-project'), '/profile/reviewer'), 'Document renders linked project persons.');
check($format->get_field('institute-role') !== 'associated', 'Document resolves institute role labels.');
check(str_contains($format->get_field('website'), 'href="https://example.org"'), 'Document links websites.');
check(str_contains($format->get_field('project-date'), '2026') && str_contains($format->get_field('project-date'), '2027'), 'Document formats project dates.');
$format->doc['website'] = 'javascript:alert(1)';
check(!str_contains($format->get_field('website'), '<a'), 'Unsafe URL schemes do not become links.');
loadIsolated('php/Render.php');
$Groups = new class { public function getParents($unit, $includeSelf = false) { return ['ROOT']; } };
$rendered = renderProject(['start' => ['year' => 2026, 'month' => 1, 'day' => 1], 'persons' => $values['persons']]);
check($rendered['persons'][0]['units'] === ['SCI'] && in_array('ROOT', $rendered['units'], true), 'Project rendering retains selected membership units and includes parent units.');
$personsWithoutUnits = $values['persons']; $personsWithoutUnits[0]['units'] = [];
$rendered = renderProject(['start' => ['year' => 2026, 'month' => 1, 'day' => 1], 'persons' => $personsWithoutUnits]);
check($rendered['persons'][0]['units'] === [], 'Explicitly clearing membership units is respected.');
ob_end_clean();
echo $baselineOutput;
echo 'Project module tests passed (' . ($checks - $baselineChecks) . " checks).\n";
