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
check($_SESSION['objective_form'][$id]['new']['code'] === $input['code'] && !isset($_SESSION['objective_form'][$id]['new']['initiative']), 'Invalid create preserves only editable inputs for the create page.');
postRoute('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', ['values' => array_replace($input, ['code' => 'Keep input', 'start_date' => 'invalid'])], $id, 'update', $objectiveId);
check($_SESSION['objective_form'][$id][$objectiveId]['code'] === 'Keep input', 'Invalid edits retain the entered values.');
check($osiris->objectives->findOne(['_id' => $objective['_id']])['code'] === 'EA-02', 'Invalid edits leave stored values intact.');

$initiative = $parent;
ob_start(); loadIsolated('pages/initiatives/view.php'); $html = ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
check($xpath->query('//a[@href="' . ROOTPATH . '/initiatives/' . $id . '/objectives/new"]')->length === 1, 'Adding an objective opens a dedicated page.');
check(str_contains($html, '/initiatives/objectives/view/' . $objectiveId), 'Objective cards navigate to the objective detail page.');
check($xpath->query('//form | //div[@role="dialog"]')->length === 0 && !str_contains($html, '/objectives/archive/'), 'The overview has no edit forms, modals or archive actions.');
check(isset($_SESSION['objective_form'][$id]['new'], $_SESSION['objective_form'][$id][$objectiveId]), 'Reading the overview does not consume pending form input.');

function renderObjectiveEditor(array $initiative, array $objective): string {
    global $Settings;
    $source = str_replace("require_once BASEPATH . '/php/Vocabulary.php';", '', file_get_contents(BASEPATH . '/pages/initiatives/objective-edit.php'));
    ob_start(); eval('?>' . $source); return ob_get_clean();
}
$newHtml = renderObjectiveEditor($parent, []);
$newDom = new DOMDocument(); @$newDom->loadHTML($newHtml); $newXpath = new DOMXPath($newDom);
check($newXpath->query('//select[@id="new-objective-type"]/option[@value="research" and @selected]')->length === 1, 'The create page retains the chosen vocabulary key after failed validation.');
check($newXpath->query('//select[@id="new-objective-type"]/option[@value="research"]')->item(0)->textContent === 'Research' && $newXpath->query('//option[@value="inactive"]')->length === 0, 'Vocabulary labels are shown and inactive choices omitted.');
foreach (['code', 'name', 'description', 'type', 'start_date', 'end_date'] as $field) {
    check($newXpath->query('//*[@name="values[' . $field . ']"]')->length === 1, 'The create page includes ' . $field . '.');
}
check($newXpath->query('//input[@id="new-objective-code" and @value=" EA-01 "]')->length === 1, 'Failed input is repopulated.');
check(!isset($_SESSION['objective_form'][$id]['new']) && isset($_SESSION['objective_form'][$id][$objectiveId]), 'Only the displayed form draft is consumed.');
check(str_contains($newHtml, ROOTPATH . '/initiatives/view/' . $id . '#objectives') && !str_contains($newHtml, '/objectives/archive/'), 'Cancel returns to the parent objectives and new records have no archive control.');
$editHtml = renderObjectiveEditor($parent, DB::doc2Arr($updated));
$editDom = new DOMDocument(); @$editDom->loadHTML($editHtml); $editXpath = new DOMXPath($editDom);
check($editXpath->query('//input[@id="edit-objective-code" and @value="Keep input"]')->length === 1, 'The edit page retains failed input.');
check(str_contains($editHtml, ROOTPATH . '/initiatives/objectives/view/' . $objectiveId), 'Edit cancellation returns to the objective detail page.');
check($editXpath->query('//form//form')->length === 0 && str_contains($editHtml, '/objectives/archive/' . $objectiveId), 'Archiving is a separate form outside normal saving.');
check(str_contains($editHtml, 'Archive “Validate climate forecasts”?') && $editXpath->query('//div[contains(@class,"dropdown-menu")]//form')->length === 1, 'Archiving requires opening a named confirmation before submitting.');

// Initiative dates are defaults only for new objectives; explicit draft/record values win.
$parent['start_date'] = '2026-01-01';
$parent['end_date'] = '2028-12-31';
$defaultHtml = renderObjectiveEditor($parent, []);
check(str_contains($defaultHtml, 'value="2026-01-01"') && str_contains($defaultHtml, 'value="2028-12-31"'), 'New objectives inherit both initiative dates.');
$editHtml = renderObjectiveEditor($parent, DB::doc2Arr($updated));
check(str_contains($editHtml, 'id="edit-objective-end" class="form-control" type="date" name="values[end_date]" value=""'), 'An existing open-ended objective never inherits a new end date.');
$_SESSION['objective_form'][$id]['new'] = ['start_date' => '', 'end_date' => '2029-01-01'];
$draftHtml = renderObjectiveEditor($parent, []);
check(str_contains($draftHtml, 'id="new-objective-start" class="form-control" type="date" name="values[start_date]" value=""') && str_contains($draftHtml, 'value="2029-01-01"'), 'Explicit draft dates, including a cleared start, override defaults.');
unset($parent['start_date'], $parent['end_date']);
$parent['start'] = ['year' => 2025, 'month' => 3, 'day' => 4];
$parent['end'] = null;
$arrayDateHtml = renderObjectiveEditor($parent, []);
check(str_contains($arrayDateHtml, 'value="2025-03-04"') && str_contains($arrayDateHtml, 'id="new-objective-end" class="form-control" type="date" name="values[end_date]" value=""'), 'Structured dates and an ongoing initiative also populate correctly.');
$Settings->permissions = ['initiatives.view'];
aborts(fn() => Route::$routes['GET /initiatives/(.*)/objectives/new'][0]($id), 403);
aborts(fn() => Route::$routes['GET /initiatives/objectives/edit/(.*)'][0]($objectiveId), 403);
ob_end_clean(); echo $baselineOutput;
echo 'Objective regression tests passed (' . ($checks - $baselineChecks) . " checks).\n";
