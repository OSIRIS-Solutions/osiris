<?php
// Run: php tests/objectives.php. Fixed-field objective forms and routes without a live database.
ob_start();
require __DIR__ . '/initiatives.php';
$baselineOutput = ob_get_clean();
ob_start();
$baselineChecks = $checks;
$catalogue = array_column(json_decode(file_get_contents(BASEPATH . '/data/vocabulary.json'), true), null, 'id');
check(array_column($catalogue['objective-type']['values'], 'id') === ['objective'], 'Objective types start with only the requested default.');
check(Initiatives::objectiveValues(['name' => 'Default type'])['type'] === 'objective', 'New objectives default to the vocabulary key objective.');
Vocabulary::$objectiveTypes = [
    ['id' => 'objective', 'en' => 'Objective', 'de' => 'Objective'],
    ['id' => 'research', 'en' => 'Research', 'de' => 'Forschung'],
    ['id' => 'validation', 'en' => 'Validation', 'de' => 'Validierung'],
    ['id' => 'inactive', 'en' => 'Inactive', 'de' => 'Inaktiv', 'inactive' => true],
];
rejects(fn() => Initiatives::objectiveValues(['name' => 'Unknown', 'type' => 'unknown']), 'Unconfigured type keys are rejected.');
rejects(fn() => Initiatives::objectiveValues(['name' => 'Inactive', 'type' => 'inactive']), 'Inactive type keys cannot be assigned.');
$Settings->permissions = ['initiatives.edit', 'initiatives.view'];
$parent = array_values($osiris->initiatives->documents)[0];
$id = (string) $parent['_id'];
$input = [
    'code' => ' EA-01 ', 'name' => ' Validate climate forecasts ',
    'description' => "Compare model predictions\nwith field observations.", 'type' => 'research',
    'start_date' => '2026-11-01', 'end_date' => '2027-12-31',
];
$values = Initiatives::objectiveValues($input);
check($values['code'] === 'EA-01' && $values['name'] === 'Validate climate forecasts', 'Identifiers and titles are normalized.');
check($values['description'] === $input['description'] && $values['type'] === 'research', 'Description and type are retained.');
check($values['start'] === ['year' => 2026, 'month' => 11, 'day' => 1] && $values['end']['year'] === 2027, 'Objective periods include structured dates.');
check($values['start_date'] === '2026-11-01' && $values['end_date'] === '2027-12-31', 'ISO dates support date inputs and expiry checks.');
check(Initiatives::objectiveValues(['name' => 'Legacy title'])['start_date'] === null, 'New fields remain optional for existing objectives.');
check(Initiatives::objectiveValues(['name' => 'Same day', 'start_date' => '2026-11-01', 'end_date' => '2026-11-01'])['end_date'] === '2026-11-01', 'A same-day period is valid.');
rejects(fn() => Initiatives::objectiveValues(array_replace($input, ['start_date' => '2028-01-01'])), 'Reversed objective dates are rejected.');
rejects(fn() => Initiatives::objectiveValues(array_replace($input, ['start_date' => '2026-02-30'])), 'Invalid start dates are rejected.');
foreach (['code', 'name', 'description', 'type', 'start_date', 'end_date'] as $field) {
    rejects(fn() => Initiatives::objectiveValues(array_replace($input, [$field => ['invalid']])), 'Malformed objective fields are rejected without warnings.');
}
check(Initiatives::period(['end_date' => '2027-12-31']) === 'End date: 31.12.2027', 'Existing objectives with only a deadline render correctly.');
check(str_contains(Initiatives::period(['start_date' => '2026-11-01']), 'Open-end'), 'An open-ended period is displayed without inventing an end date.');

postRoute('/crud/initiatives/(.*)/objectives/create', ['values' => $input + ['initiative' => 'spoof', '_id' => 'spoof']], $id);
$objective = end($osiris->objectives->documents);
$objectiveId = (string) $objective['_id'];
check($objective['initiative'] === $parent['_id'] && $objective['_id'] instanceof MongoDB\BSON\ObjectId, 'New fields cannot override identity or parentage.');
check($objective['code'] === 'EA-01' && $objective['type'] === 'research', 'Create persists all new objective fields.');
postRoute('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', ['values' => array_replace($input, [
    'code' => 'EA-02', 'description' => 'Updated description', 'type' => 'validation', 'end_date' => '',
])], $id, 'update', $objectiveId);
$updated = $osiris->objectives->findOne(['_id' => $objective['_id']]);
check($updated['code'] === 'EA-02' && $updated['description'] === 'Updated description' && $updated['type'] === 'validation', 'Editing persists code, description and type.');
check($updated['end'] === null && $updated['end_date'] === null && $updated['start_date'] === $input['start_date'], 'Clearing an end date clears both representations.');
check($updated['created_by'] === 'reviewer' && $updated['initiative'] === $parent['_id'], 'Editing preserves creation metadata and parentage.');

$count = count($osiris->objectives->documents);
postRoute('/crud/initiatives/(.*)/objectives/create', ['values' => array_replace($input, ['name' => '', 'initiative' => 'spoof'])], $id);
check(count($osiris->objectives->documents) === $count, 'Invalid create does not insert an objective.');
check($_SESSION['objective_form'][$id]['new']['code'] === $input['code'] && !isset($_SESSION['objective_form'][$id]['new']['initiative']), 'Invalid create preserves only editable inputs for the modal.');
postRoute('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', ['values' => array_replace($input, ['code' => 'Keep input', 'start_date' => 'invalid'])], $id, 'update', $objectiveId);
check($_SESSION['objective_form'][$id][$objectiveId]['code'] === 'Keep input', 'Invalid edits retain the entered values.');
check($osiris->objectives->findOne(['_id' => $objective['_id']])['code'] === 'EA-02', 'Invalid edits leave stored values intact.');

$initiative = $parent;
ob_start(); loadIsolated('pages/initiatives/view.php'); $html = ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
check($xpath->query('//a[@href="#add-objective" and contains(@class,"btn")]')->length === 1, 'The add action opens the modal.');
check($xpath->query('//div[@id="add-objective" and @role="dialog"]')->length === 1, 'The create form is an accessible modal.');
check($xpath->query('//select[@id="new-objective-type"]/option[@value="research" and @selected]')->length === 1, 'The dropdown retains the chosen vocabulary key after failed validation.');
check($xpath->query('//select[@id="new-objective-type"]/option[@value="research"]')->item(0)->textContent === 'Research', 'Dropdowns show vocabulary labels rather than keys.');
check($xpath->query('//select[@id="new-objective-type"]/option[@value="inactive"]')->length === 0, 'Dropdowns omit inactive vocabulary choices.');
check(str_contains($html, 'Type: Validation'), 'Objective details use the vocabulary label.');
check($xpath->query('//div[@id="add-objective"]//a[@href="#close-modal"]')->length === 2, 'The modal provides close and cancel controls.');
foreach (['code', 'name', 'description', 'type', 'start_date', 'end_date'] as $field) {
    check($xpath->query('//*[@id="add-objective"]//*[@name="values[' . $field . ']"]')->length === 1, 'The modal includes ' . $field . '.');
}
check($xpath->query('//*[@id="new-objective-code" and @value=" EA-01 "]')->length === 1, 'The modal repopulates failed input.');
check($xpath->query('//div[@role="dialog"]//*[@id="edit-objective-' . $objectiveId . '-code" and @value="Keep input"]')->length === 1, 'The edit modal retains failed inputs.');
check(!isset($_SESSION['objective_form'][$id]), 'Displayed form drafts are consumed once.');
check($xpath->query('//a[@href="#edit-objective-' . $objectiveId . '"]')->length === 1, 'Edit opens the objective-specific modal.');
check($xpath->query('//div[@id="edit-objective-' . $objectiveId . '" and @role="dialog"]')->length === 1, 'Each objective has its own edit modal.');
check($xpath->query('//div[@id="edit-objective-' . $objectiveId . '"]//a[@href="#close-modal"]')->length === 2, 'Edit provides close and cancel controls.');

// Initiative dates are defaults only for new objectives; explicit draft/record values win.
$initiative['start_date'] = '2026-01-01';
$initiative['end_date'] = '2028-12-31';
ob_start(); loadIsolated('pages/initiatives/view.php'); $defaultHtml = ob_get_clean();
$defaultDom = new DOMDocument(); @$defaultDom->loadHTML($defaultHtml); $defaultXpath = new DOMXPath($defaultDom);
check($defaultXpath->query('//input[@id="new-objective-start" and @value="2026-01-01"]')->length === 1, 'New objectives inherit the initiative start.');
check($defaultXpath->query('//input[@id="new-objective-end" and @value="2028-12-31"]')->length === 1, 'New objectives inherit the initiative end.');
check($defaultXpath->query('//input[@id="edit-objective-' . $objectiveId . '-end" and @value=""]')->length === 1, 'An existing open-ended objective does not inherit a different end.');
$_SESSION['objective_form'][$id]['new'] = ['start_date' => '', 'end_date' => '2029-01-01'];
ob_start(); loadIsolated('pages/initiatives/view.php'); $draftHtml = ob_get_clean();
check(str_contains($draftHtml, 'id="new-objective-start" class="form-control" type="date" name="values[start_date]" value=""'), 'An explicitly cleared draft start stays empty.');
check(str_contains($draftHtml, 'value="2029-01-01"'), 'Entered draft dates override initiative defaults.');
unset($initiative['start_date'], $initiative['end_date']);
$initiative['start'] = ['year' => 2025, 'month' => 3, 'day' => 4];
$initiative['end'] = null;
ob_start(); loadIsolated('pages/initiatives/view.php'); $arrayDateHtml = ob_get_clean();
check(str_contains($arrayDateHtml, 'value="2025-03-04"'), 'Structured initiative dates also populate the new form.');
check(str_contains($arrayDateHtml, 'id="new-objective-end" class="form-control" type="date" name="values[end_date]" value=""'), 'An ongoing initiative leaves the default end empty.');
$ids = [];
foreach ($xpath->query('//*[@id]') as $element) $ids[] = $element->getAttribute('id');
check(count($ids) === count(array_unique($ids)), 'Create and edit forms use unique HTML IDs.');
if (isset($argv[1])) file_put_contents($argv[1] . '/objectives.html', $html);

$_GET = ['status' => 'objectives'];
ob_start(); loadIsolated('pages/initiatives/list.php'); $listHtml = ob_get_clean();
check(str_contains($listHtml, '/initiatives/view/' . $id . '#objective-' . $objectiveId), 'The objective overview links to the objective within its actual initiative.');
$Settings->permissions = ['initiatives.view'];
ob_start(); loadIsolated('pages/initiatives/view.php'); $readonlyHtml = ob_get_clean();
check(!str_contains($readonlyHtml, 'id="add-objective"') && !str_contains($readonlyHtml, '<form'), 'Read-only users receive neither modal nor editing forms.');
ob_end_clean();
echo $baselineOutput;
echo 'Objective regression tests passed (' . ($checks - $baselineChecks) . " checks).\n";
