<?php
// Real widgets, normalization and migrations with in-memory entity collections.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
require_once BASEPATH . '/php/CustomFields.php';
foreach (['activities', 'projects', 'proposals', 'persons', 'groups', 'journals', 'conferences', 'events', 'deadlines', 'infrastructures', 'initiatives', 'organizations', 'news'] as $collection) {
    $osiris->$collection ??= new MemoryCollection();
}
$definition = ['id' => 'local-notes', 'name' => 'Local notes', 'format' => 'text', 'localized' => true];
$osiris->projects->insertOne(['_id' => new MongoDB\BSON\ObjectId(), 'local-notes' => 'Original project text']);
$osiris->journals->insertOne(['_id' => new MongoDB\BSON\ObjectId(), 'local-notes' => '0123']);
$ordinary = array_replace($definition, ['localized' => false]);
$osiris->adminFields->insertOne($ordinary);
$created = CustomFields::definitionValues($definition);
check($created['localized'] && $osiris->projects->findOne([])['local-notes'] === 'Original project text', 'Configuring a new localized field performs no data migration.');
$saved = CustomFields::definitionValues($definition, $ordinary);
check(!$saved['localized'], 'Ordinary updates cannot activate localization on an existing field.');
CustomFields::convertToLocalized($ordinary, $osiris);
check($osiris->adminFields->findOne(['id' => 'local-notes'])['localized'] && $osiris->projects->findOne([])['local-notes'] === ['en' => 'Original project text'] && $osiris->journals->findOne([])['local-notes'] === ['en' => '0123'], 'Explicit conversion updates data and then the stored definition.');
CustomFields::convertToLocalized($ordinary, $osiris);
check($osiris->projects->findOne([])['local-notes'] === ['en' => 'Original project text'], 'Retried conversion does not wrap existing language maps again.');
$values = validateValues(['local-notes' => ['en' => '0123', 'de' => 'Deutsch', 'bad-key' => 'Ignored']], $DB, 'projects');
check($values['local-notes'] === ['en' => '0123', 'de' => 'Deutsch'], 'Custom values preserve textual numbers and normalize translation keys.');
$paths = CustomFields::queryPaths($osiris);
$filter = ['$and' => [['local-notes' => ['$ne' => 'excluded']], ['$or' => [['local-notes' => ['$regex' => 'Original']], ['status' => 'project']]]]];
$mapped = CustomFields::filterPaths($filter, $paths);
check($mapped['$and'][0] === ['local-notes.en' => ['$ne' => 'excluded']] && isset($mapped['$and'][1]['$or'][0]['local-notes.en']), 'Existing nested searches, including negative filters, use English paths.');
check(CustomFields::filterPaths($mapped, $paths) === $mapped, 'Already mapped filters remain unchanged.');
foreach (['string' => 'input', 'text' => 'textarea', 'text-format' => 'textarea'] as $format => $tag) {
    $osiris->adminFields->updateOne(['id' => 'local-notes'], ['$set' => ['format' => $format]]);
    $renderer = new Modules(['local-notes' => $values['local-notes']]);
    ob_start(); $renderer->custom_field('local-notes', true); $html = ob_get_clean();
    $dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
    check($xpath->query('//' . $tag . '[@name="values[local-notes][en]"]')->length === 1 && $xpath->query('//' . $tag . '[@name="values[local-notes][de]"]')->length === 1, "$format offers both translations using the appropriate input control.");
}
$custom = new CustomFields(['local-notes' => $values['local-notes']]);
check($custom->value('local-notes') === '0123', 'The journal value renderer resolves localized custom text.');
$format = new Document(false, 'list'); $format->doc = ['local-notes' => $values['local-notes']];
check($format->get_field('local-notes') === '0123', 'Document resolves localized custom text.');
$journal = $osiris->journals->findOne([]);
$osiris->journals->updateOne(['_id' => $journal['_id']], ['$set' => ['local-notes' => ['en' => '0123', 'de' => 'Übersetzung']]]);
$saved = CustomFields::definitionValues(array_replace($definition, ['localized' => false]), $definition);
check($saved['localized'] && $osiris->journals->findOne([])['local-notes']['de'] === 'Übersetzung', 'Normal saves cannot disable localization or change stored translations.');
try { CustomFields::definitionValues(array_replace($definition, ['format' => 'string']), $definition); throw new RuntimeException('Expected format lock.'); }
catch (InvalidArgumentException $error) { check(true, 'The format of a localized field remains locked.'); }
$project = $osiris->projects->findOne([]);
$osiris->projects->updateOne(['_id' => $project['_id']], ['$set' => ['local-notes' => ['en' => '']]]);
$osiris->journals->updateOne(['_id' => $journal['_id']], ['$set' => ['local-notes' => ['en' => '', 'de' => '']]]);
check(CustomFields::definitionValues(array_replace($definition, ['localized' => false]), $definition)['localized'], 'Localization remains irreversible even for empty fields.');
try { CustomFields::definitionValues(array_replace($definition, ['format' => 'list'])); throw new RuntimeException('Expected format guard.'); }
catch (InvalidArgumentException $error) { check(true, 'Other field formats cannot enable localization.'); }
// Exercise the dedicated POST route, including the deliberate confirmation.
loadIsolated('routes/admin.fields.php');
$Settings->permissions[] = 'admin.see';
$osiris->adminFields->insertOne(['id' => 'route-notes', 'format' => 'string', 'name' => 'Saved label']);
$osiris->projects->insertOne(['_id' => new MongoDB\BSON\ObjectId(), 'route-notes' => 'Saved text']);
$convert = Route::$routes['POST /crud/fields/localize/(.*)'][0];
$_POST = [];
try { $convert('route-notes'); throw new RuntimeException('Expected confirmation guard.'); }
catch (Aborted $error) { check($error->getCode() === 400 && !isset($osiris->adminFields->findOne(['id' => 'route-notes'])['localized']), 'Conversion requires explicit confirmation before changing data.'); }
$_POST = ['confirm_localize' => 'yes', 'values' => ['name' => 'Unsaved change']];
$convert('route-notes');
check($osiris->adminFields->findOne(['id' => 'route-notes'])['name'] === 'Saved label' && $osiris->adminFields->findOne(['id' => 'route-notes'])['localized'], 'The dedicated action converts only the saved definition and ignores other form edits.');
ob_end_clean(); echo $baselineOutput; echo 'Localized custom field checks passed (' . ($checks - $baselineChecks) . " checks).\n";
