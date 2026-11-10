<?php
// Run: php tests/localized-modules.php. Real module renderers with in-memory application services.
ob_start();
require __DIR__ . '/initiatives.php';
$baselineOutput = ob_get_clean();
ob_start();
require_once BASEPATH . '/php/LocalizedFields.php';
$baselineChecks = $checks;
$Settings->permissions = ['initiatives.edit', 'initiatives.view'];
$modules = ['title-loc' => 'title', 'name-loc' => 'name', 'abstract-loc' => 'abstract', 'description-loc' => 'description'];
foreach (['activities', 'initiatives', 'projects'] as $entity) {
    $config = new FormBuilder('Test', '', entity: $entity);
    foreach ($modules as $id => $key) {
        if ($entity === 'initiatives' && $id === 'abstract-loc') {
            check(!isset($config->modules[$id]), 'Initiatives use descriptions rather than abstracts.');
            continue;
        }
        check($config->modules[$id]['fields'] === [$key], "$id stores $key and is available to $entity.");
    }
}
$form = [
    'title' => ['en' => '<p>English <em>title</em></p>', 'de' => '<p>Deutscher Titel</p>'],
    'name' => ['en' => '001', 'de' => 'true'],
    'abstract' => ['en' => '<p>English abstract</p>', 'de' => '<p>Deutsche Zusammenfassung</p>'],
    'description' => ['en' => '<p>English description</p>', 'de' => '<p>Deutsche Beschreibung</p>'],
];
$normalized = validateValues($form, $DB, 'initiatives');
check($normalized['name'] === $form['name'], 'Translations remain strings, including numeric and boolean-looking text.');
check($normalized['title']['en'] === 'English <em>title</em>', 'Localized titles retain scientific formatting and use ordinary title paragraph normalization.');
check(validateValues(['description' => ['en' => '<p><br></p>']], $DB)['description']['en'] === '', 'Empty rich text is normalized to an empty translation.');
check(normalizeLocalizedValues(['en' => 'Text', '$bad' => 'Bad', 'de' => ['nested']]) === ['en' => 'Text'], 'Malformed translation entries are discarded.');

$renderer = new Modules($normalized);
foreach ($modules as $id => $key) {
    ob_start(); $renderer->print_module($id, true, ['help' => 'Help text']); $html = ob_get_clean();
    $dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
    check($xpath->query('//*[@name="values[' . $key . '][en]"]')->length === 1, "$id renders a base-language input.");
    check($xpath->query('//*[@name="values[' . $key . '][de]"]')->length === 1, "$id renders additional configured languages.");
    check($xpath->query('//*[@data-language="de" and @role="tabpanel" and @hidden]')->length === 1, "$id initially hides the non-base language panel.");
    check(str_contains($html, 'Help text'), "$id keeps form-builder help text.");
    if ($key === 'name') {
        check($xpath->query('//input[@name="values[name][en]" and @required]')->length === 1, 'Only the base language is required for localized names.');
        check($xpath->query('//input[@name="values[name][de]" and @required]')->length === 0, 'Additional languages stay optional.');
    } else check(str_contains($html, 'localized-quill'), "$id uses the shared rich-text editor.");
    if (isset($argv[1])) file_put_contents($argv[1] . '/' . $id . '.html', $html);
}
ob_start(); $renderer->print_module('name'); $plainHtml = ob_get_clean();
check(str_contains($plainHtml, 'value="001"'), 'Existing plain modules can still display stored language maps.');
$legacy = new Modules(['name' => 'Legacy name']);
ob_start(); $legacy->print_module('name-loc'); $legacyHtml = ob_get_clean();
check(str_contains($legacyHtml, 'value="Legacy name"'), 'Plain existing values populate the base language.');

$format = new Document(false, 'list');
$format->doc = $normalized;
foreach ($modules as $id => $key) check($format->get_field($id) === $normalized[$key]['en'], "$id renders the selected language through Document.");
$format->subtypeArr = ['template' => ['print' => '{title} | {name} | {abstract} | {description}']];
$german = $format->format('de');
check(str_contains($german, 'Deutscher Titel') && str_contains($german, 'Deutsche Beschreibung'), 'Rendered activity templates resolve the requested content language.');
$format->doc['abstract'] = ['de' => 'German-only abstract'];
check($format->get_field('abstract-loc') === 'German-only abstract', 'Missing translations use the existing localization fallback.');

$fields = FormBuilder::prepareFields([
    ['id' => 'name-loc', 'type' => 'field', 'props' => ['width' => 6]],
    ['id' => 'description-loc', 'type' => 'field', 'props' => []],
], FormBuilder::INITIATIVE_SYSTEM_FIELDS);
check(!in_array('name', array_column($fields, 'id'), true), 'Localized names satisfy the system-name requirement without adding a duplicate field.');
FormBuilder::validateFields($fields, FormBuilder::INITIATIVE_SYSTEM_FIELDS);
rejects(fn() => FormBuilder::validateFields([
    ['id' => 'title', 'type' => 'field'], ['id' => 'title-loc', 'type' => 'field'],
]), 'Plain and localized variants cannot overwrite each other.');
$config = new FormBuilder('Test', '', entity: 'initiatives', systemFields: FormBuilder::INITIATIVE_SYSTEM_FIELDS);
rejects(fn() => Initiatives::formValues(['name' => ['en' => '', 'de' => 'Nur Deutsch'], 'start' => '2026-11-10'], $fields, $config->modules), 'A required localized field needs a base-language value.');
$values = Initiatives::formValues(['name' => $form['name'], 'description' => $form['description'], 'start' => '2026-11-10'], $fields, $config->modules);
check($values['name'] === $form['name'], 'Initiative form validation retains the language map.');
$details = Initiatives::details($normalized, $fields);
check(count($details['texts']) === 1 && $details['texts'][0]['value'] === $form['description']['en'], 'Localized descriptions render as detail text sections.');
check(!in_array('Name', array_column($details['metadata'], 'label'), true), 'Localized names are not repeated below the page title.');

// Full initiative create/edit preserves language maps in storage.
$osiris->adminGeneral->updateOne(['id' => 'initiative-fields'], ['$set' => ['fields' => $fields]]);
postRoute('/crud/initiatives/create', ['values' => ['name' => $form['name'], 'description' => $form['description'], 'start' => '2026-11-10']]);
$created = end($osiris->initiatives->documents);
check($created['name'] === $form['name'] && $created['description'] === $form['description'], 'Create stores multilingual field values unchanged.');
postRoute('/crud/initiatives/update/(.*)', ['values' => ['name' => ['en' => 'Updated', 'de' => 'Aktualisiert'], 'description' => $form['description'], 'start' => '2026-11-10']], (string) $created['_id']);
$updated = $osiris->initiatives->findOne(['_id' => $created['_id']]);
check($updated['name']['de'] === 'Aktualisiert', 'Edit saves all language values.');
ob_end_clean();
echo $baselineOutput;
echo 'Localized-module regression tests passed (' . ($checks - $baselineChecks) . " checks).\n";
