<?php
// Shared configuration, event storage compatibility and the user's participation.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/EventForms.php');
$osiris->conferences = new MemoryCollection();
$fields = EventForms::fields($osiris);
check(array_column($fields, 'id') === ['event-title', 'title_full', 'event-type', 'event-description', 'date-range', 'location', 'country', 'url', 'topics', 'tags', 'event-public'], 'The default preserves the existing event field order.');
$config = EventForms::builder();
check(array_diff(['event-title', 'event-description', 'event-public', 'event-type', 'title_full', 'url', 'initiatives', 'objectives', 'persons', 'persons-role'], array_keys($config->modules)) === [], 'Event fields and the new association modules are available.');
check(array_intersect(['title', 'description', 'public'], array_keys($config->modules)) === [], 'Event forms offer explicit event modules instead of context-dependent generic variants.');
$legacy = [
    ['id' => 'title', 'type' => 'field', 'props' => ['label' => 'Custom short title', 'required' => true, 'width' => 6]],
    ['id' => 'description', 'type' => 'field', 'props' => ['help' => 'Preserved help']],
    ['id' => 'public', 'type' => 'field', 'props' => []],
    ['type' => 'heading', 'props' => ['text' => 'Section']],
    ['id' => 'title', 'type' => 'custom', 'props' => []],
];
$upgraded = EventForms::upgradeFields($legacy);
check(array_column(array_slice($upgraded, 0, 3), 'id') === ['event-title', 'event-description', 'event-public'] && $upgraded[0]['props'] === $legacy[0]['props'] && $upgraded[1]['props'] === $legacy[1]['props'] && $upgraded[3] === $legacy[3] && $upgraded[4] === $legacy[4], 'Upgrading renames only built-in module IDs and preserves form properties, layout and custom fields.');
check(EventForms::upgradeFields($upgraded) === $upgraded, 'Renaming form modules is idempotent.');
rejects(fn() => FormBuilder::validateFields([], EventForms::SYSTEM_FIELDS), 'Event system fields cannot be removed.');
$type = (new Vocabulary())->getValues('event-type')[0]['id'];
$input = ['title' => 'Event', 'title_full' => 'Full title', 'type' => $type, 'description' => '<p>Description</p>', 'start' => '2026-11-20', 'end' => '', 'location' => 'Ankh', 'country' => 'DE', 'url' => 'https://example.org', 'public' => 'false', '_id' => 'forged', 'participants' => ['forged']];
$values = EventForms::values($input, $osiris, $Settings);
check($values['start'] === '2026-11-20' && $values['end'] === $values['start'] && $values['year'] === 2026, 'Dates remain strings, with an omitted end representing a single day.');
check($values['public'] === false && $values['url'] === $input['url'] && $values['title_full'] === $input['title_full'], 'Public visibility and established storage keys are preserved.');
check(!isset($values['_id'], $values['participants']), 'The form cannot overwrite identity or participation metadata.');
rejects(fn() => EventForms::values(array_replace($input, ['title' => '']), $osiris, $Settings), 'Empty system fields are rejected.');
rejects(fn() => EventForms::values(array_replace($input, ['end' => '2026-11-19']), $osiris, $Settings), 'Reversed date ranges are rejected.');
$form = array_replace(EventForms::redisplay($input), ['participants' => ['reviewer']]);
ob_start(); loadIsolated('components/event-form.php'); $html = ob_get_clean();
check(str_contains($html, 'name="attending" id="event-attended" value="1" checked'), 'The creation shortcut retains its selection after a validation error.');
$form['_id'] = new MongoDB\BSON\ObjectId();
ob_start(); loadIsolated('components/event-form.php'); $editHtml = ob_get_clean();
check(!str_contains($editHtml, 'name="attending"'), 'Editing an existing event shows no participation shortcut or hidden attendance input.');
$redisplayed = EventForms::redisplay(['_id' => 'forged', 'title' => ['invalid'], 'location' => 'New location'], ['_id' => $form['_id'], 'title' => 'Saved title', 'participants' => ['colleague']]);
check($redisplayed['_id'] === $form['_id'] && $redisplayed['title'] === 'Saved title' && $redisplayed['location'] === 'New location' && $redisplayed['participants'] === ['colleague'], 'Redisplaying invalid input keeps the record identity and valid inputs without rendering malformed text.');
check(str_contains($html, 'name="values[title_full]"') && str_contains($html, 'name="values[type]"') && str_contains($html, 'placeholder=" "'), 'The shared editor uses the existing event fields and floating label placeholders.');
$id = new MongoDB\BSON\ObjectId();
$osiris->conferences->insertOne(array_replace($values, ['_id' => $id, 'participants' => ['colleague'], 'interests' => ['interested'], 'created_by' => 'reviewer']));
$source = file_get_contents(BASEPATH . '/routes/events.php');
$source = str_replace('include_once BASEPATH . "/php/init.php";', 'global $DB, $osiris, $Settings;', $source);
$source = preg_replace('/^\s*(?:include|require)(?:_once)? .*;\R/m', '', $source);
eval('?>' . $source);
$save = Route::$routes['POST /crud/conferences/update/(.*)'][0];
$_POST = ['values' => $input, 'attending' => '1'];
$save((string) $id);
$saved = $osiris->conferences->findOne(['_id' => $id]);
check($saved['participants'] === ['colleague'] && $saved['interests'] === ['interested'], 'Posting an attendance flag to the edit endpoint cannot add a participant.');
$osiris->conferences->updateOne(['_id' => $id], ['$set' => ['participants' => ['colleague', 'reviewer']]]);
$_POST['attending'] = '0';
$save((string) $id);
check($osiris->conferences->findOne(['_id' => $id])['participants'] === ['colleague', 'reviewer'], 'Posting an unchecked attendance flag to the edit endpoint cannot remove participation.');
$osiris->conferences->updateOne(['_id' => $id], ['$set' => ['created_by' => 'another-user']]);
foreach (['editor link' => fn() => Route::$routes['GET /conferences/edit/(.*)'][0]((string) $id), 'save request' => fn() => $save((string) $id)] as $path => $request) {
    try { $request(); throw new RuntimeException('Expected permission rejection.'); }
    catch (Aborted $error) { check($error->getCode() === 403, "The $path enforces edit permission for other users' events."); }
}
$Settings->permissions[] = 'conferences.edit';
$save((string) $id);
check($osiris->conferences->findOne(['_id' => $id])['participants'] === ['colleague', 'reviewer'], 'Editors may update another event while leaving participation unchanged.');
$format = new Document(false, 'list');
check($format->getEventField($saved, 'event-type') === (new Vocabulary())->getValue('event-type', $type), 'Event types are rendered using vocabulary labels.');
check(str_contains($format->getEventField($saved, 'url'), 'href="https://example.org"'), 'Existing event URLs are rendered as links.');
check($format->get_field('event-title') === $saved['title'] && $format->get_field('event-description') === $saved['description'] && str_contains($format->get_field('event-public'), 'badge'), 'The event module renderers read the original storage keys without an event flag.');
$osiris->adminGeneral->insertOne(['id' => 'event-fields', 'fields' => array_values(array_filter($fields, fn($field) => !in_array($field['id'], ['topics', 'tags'], true)))]);
$conference = array_replace($saved, ['participants' => [], 'interests' => []]);
$activities = [];
$view = preg_replace('/^\s*(?:include|require)(?:_once)? .*;\R/m', '', file_get_contents(BASEPATH . '/pages/events/view.php'));
ob_start(); eval('?>' . $view); $html = ob_get_clean();
check(str_contains($html, 'Full title') && str_contains($html, 'Description') && str_contains($html, '2026'), 'The event detail page renders its configured fields without activity initialization.');
ob_end_clean(); echo $baselineOutput; echo 'Event form checks passed (' . ($checks - $baselineChecks) . " checks).\n";
