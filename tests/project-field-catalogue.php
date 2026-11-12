<?php
// Run: php tests/project-field-catalogue.php. Real modules with in-memory services.
ob_start();
require __DIR__ . '/initiatives.php';
$baselineOutput = ob_get_clean();
ob_start();
$baselineChecks = $checks;
loadIsolated('php/ProjectFormMigration.php');
loadIsolated('php/ProjectForms.php');
require_once BASEPATH . '/php/LocalizedFields.php';
$newModules = ['acronym', 'image', 'submission_date', 'approval_date', 'rejection_date', 'project-date-proposed', 'funder', 'funding_organization', 'funding_program', 'funding_program_select', 'joint_project', 'funding_number', 'project_type', 'grant_income', 'grant_income_proposed', 'grant_sum', 'grant_sum_proposed', 'grant_subproject', 'grant_subproject_proposed', 'internal_number', 'coordinator', 'scholarship', 'university', 'research-countries', 'comment', 'nagoya', 'kdsf-ffk', 'public', 'purpose', 'personnel', 'in-kind', 'ressources', 'topics'];
$projectConfig = new FormBuilder('Project', '', entity: 'projects');
$proposalConfig = new FormBuilder('Proposal', '', entity: 'proposals');
$settingsBackup = $Settings->enabled;
$Settings->enabled = true;
$orgId = new MongoDB\BSON\ObjectId();
$osiris->organizations->insertOne(['_id' => $orgId, 'name' => 'University & Institute', 'location' => 'Berlin']);
$osiris->topics->insertOne(['id' => 'climate', 'name' => 'Climate & Water', 'name_de' => 'Klima', 'color' => '#008083', 'subtitle' => 'Climate research']);
$fixture = [
    'acronym' => '001', 'internal_number' => '1e10', 'coordinator' => 'Institute & partners',
    'submission_date' => '2026-01-02', 'approval_date' => '2026-03-04', 'rejection_date' => '2026-04-05',
    'start_proposed' => ['year' => 2027, 'month' => 1, 'day' => 1], 'end_proposed' => '2029-12-31',
    'funding_number' => ['001', 'AB-2'], 'funding_program' => 'Program & One',
    'funding_organization' => $orgId, 'university' => ['$oid' => (string) $orgId],
    'joint_project' => true, 'joint_project_identifier' => 'ID-1', 'joint_project_title' => 'Title & consortium', 'joint_project_speaker' => true,
    'grant_sum' => 1234.56, 'grant_sum_proposed' => 2000, 'grant_income' => 100, 'grant_income_proposed' => 200, 'grant_subproject' => 50, 'grant_subproject_proposed' => 80,
    'research-countries' => [['iso' => 'DE', 'role' => 'in'], ['iso' => 'FR', 'role' => 'both']],
    'nagoya' => ['enabled' => false, 'countries' => []],
    'kdsf-ffk' => ['139'], 'topics' => ['climate'], 'tags' => ['Historic & tag'], 'countries' => ['DE'],
    'public' => true, 'personnel' => 'Two researchers', 'in-kind' => '10%', 'comment' => '<script>alert(1)</script>',
    'ressources' => ['material' => 'yes', 'material_details' => 'A & B', 'personnel' => 'no', 'room' => 'no', 'other' => 'yes', 'other_details' => 'Details'],
];
foreach (['funder' => 'funder', 'project_type' => 'project-type', 'purpose' => 'project-purpose', 'funding_program_select' => 'funding-program'] as $field => $vocab) {
    $options = (new Vocabulary())->getValues($vocab, true);
    $fixture[$field] = $options[0]['id'];
}
$renderer = new Modules($fixture);
$rendered = [];
foreach ($newModules as $id) {
    check(isset($projectConfig->modules[$id]), "$id is available to project builders.");
    check($renderer->all_modules[$id]['entities'] === ['projects'] && !isset($proposalConfig->modules[$id]), "$id is limited to projects.");
    ob_start(); $renderer->print_module($id, true, ['label' => 'Custom label', 'help' => 'Help & context']); $html = ob_get_clean();
    $rendered[$id] = $html;
    check(str_contains($html, 'data-module="' . $id . '"') && str_contains($html, 'Custom label') && str_contains($html, 'Help & context'), "$id renders with configurable labels and help.");
    check(!str_contains($html, 'Array'), "$id does not stringify stored arrays.");
    if (isset($argv[1])) file_put_contents($argv[1] . '/' . $id . '.html', $html);
}
function xpathFor($html) { $dom = new DOMDocument(); @$dom->loadHTML($html); return new DOMXPath($dom); }
check(xpathFor($rendered['funding_number'])->query('//input[@name="values[funding_number]" and @value="001, AB-2"]')->length === 1, 'Stored funding reference lists become editable comma-separated text.');
check(xpathFor($rendered['project-date-proposed'])->query('//input[@type="date" and @value="2027-01-01" and @required]')->length === 1, 'Proposed date arrays are supported.');
foreach (['funding_organization', 'scholarship', 'university'] as $id) {
    $field = $id === 'scholarship' ? 'funding_organization' : $id;
    check(xpathFor($rendered[$id])->query('//input[@name="values[' . $field . ']" and @value="' . $orgId . '"]')->length === 1, "$id preserves its organization reference and correct storage key.");
}
check(str_contains($rendered['university'], 'University &amp; Institute'), 'Organization names are escaped.');
check(str_contains($rendered['comment'], '&lt;script&gt;') && !str_contains($rendered['comment'], '<script>'), 'Free text is escaped.');
check(xpathFor($rendered['public'])->query('//input[@name="values[public]" and @type="hidden" and @value="0"]')->length === 1, 'Clearing public consent submits false.');
check(xpathFor($rendered['joint_project'])->query('//input[@name="values[joint_project_identifier]" and @value="ID-1"]')->length === 1, 'Joint project auxiliary fields are retained.');
check(xpathFor($rendered['joint_project'])->query('//input[@name="values[joint_project_speaker]" and @checked]')->length === 1, 'Consortium speaker role is retained.');
check(xpathFor($rendered['research-countries'])->query('//input[@name="values[research-countries][]" and @value="DE;in"]')->length === 1, 'Research country roles are encoded for submission.');
check(xpathFor($rendered['kdsf-ffk'])->query('//*[@id="kdsf-ffk-list"]')->length === 1, 'KDSF retains its original hierarchical widget.');
check(xpathFor($rendered['topics'])->query('//input[@type="checkbox" and @checked and @value="climate"]')->length === 1, 'Topic assignments remain selected in the original pill chooser.');
check(str_contains($rendered['ressources'], 'values[ressources][material_details]') && str_contains($rendered['ressources'], 'A &amp; B'), 'Structured resource values are preserved and escaped.');
foreach (['name', 'funding_type', 'countries', 'tags'] as $id) {
    check(isset($projectConfig->modules[$id]), "Existing compatible $id is enabled for projects.");
    ob_start(); $renderer->print_module($id); $html = ob_get_clean();
    check($html !== '', "Existing $id renders in a project context.");
    if ($id === 'tags') check(str_contains($html, 'Historic &amp; tag'), 'Tags removed from the settings are not silently lost.');
}
$normalized = validateValues([
    'research-countries' => ['', 'DE;in', ['iso' => 'FR', 'role' => 'both']],
    'topics' => ['', 'climate', 'climate'], 'kdsf-ffk' => ['', '139'], 'nagoya_countries' => ['', 'DE'],
    'tags' => ['', 'Old'], 'countries' => ['', 'DE'],
    'acronym' => '001', 'internal_number' => '1e10', 'funding_number' => '12345678901234567890',
    'grant_sum' => '1234.56', 'public' => 'false', 'joint_project' => 'true',
], $DB, 'projects');
check($normalized['research-countries'] === [['iso' => 'DE', 'role' => 'in'], ['iso' => 'FR', 'role' => 'both']], 'Legacy and structured research-country inputs normalize identically.');
check($normalized['topics'] === ['climate'] && $normalized['kdsf-ffk'] === ['139'] && $normalized['nagoya_countries'] === ['DE'], 'Empty sentinels are removed and identifiers remain strings.');
check($normalized['internal_number'] === '1e10' && $normalized['funding_number'] === '12345678901234567890', 'Identifier-like text is not converted into numbers.');
check($normalized['grant_sum'] === 1234.56 && $normalized['public'] === false && $normalized['joint_project'] === true, 'Amounts and boolean fields retain their expected types.');
foreach (['topics', 'kdsf-ffk', 'nagoya_countries', 'research-countries', 'tags', 'countries'] as $id) check(validateValues([$id => ['']], $DB, 'projects')[$id] === [], "Clearing $id saves an empty array.");
// Every migrated built-in field now has an available project module.
$definitions = array_column(json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true), null, 'id');
$migration = new ProjectFormMigration($definitions, $language);
foreach (['project', 'proposed', 'approved', 'rejected'] as $phase) {
    $schema = $migration->fields($phase, array_map(fn($id) => ['module' => $id, 'required' => false], array_keys($definitions)));
    foreach ($schema as $field) if (($field['type'] ?? '') === 'field') check(isset($projectConfig->modules[$field['id']]), "Migrated $phase field {$field['id']} is available.");
    FormBuilder::validateFields($schema, ProjectForms::systemFields($phase));
}
$schema = $migration->fields('project', array_map(fn($id) => ['module' => $id, 'required' => false], array_keys($definitions)));
$renderer->setFields($schema);
ob_start(); $renderer->render_form($schema); $assembled = ob_get_clean();
check(str_contains($assembled, 'data-module="persons-project"') && str_contains($assembled, 'data-module="university"') && str_contains($assembled, 'data-module="project-date"'), 'Migrated fields also render together through the shared form renderer.');
if (isset($argv[1])) file_put_contents($argv[1] . '/assembled-form.html', $assembled);
$ongoing = new Modules(['nagoya' => ['enabled' => true, 'countries' => [['code' => 'DE', 'abs' => ['protected' => true]]]]]);
ob_start(); $ongoing->print_module('nagoya'); $html = ob_get_clean();
check(!str_contains($html, 'name="values[nagoya]"'), 'Existing Nagoya cases do not emit inputs that overwrite their details.');
$Settings->enabled = false;
foreach (['nagoya', 'topics'] as $id) { ob_start(); $renderer->print_module($id); check(ob_get_clean() === '', 'Disabled features do not clear existing data.'); }
$Settings->enabled = $settingsBackup;
ob_end_clean();
echo $baselineOutput;
echo 'Project field catalogue tests passed (' . ($checks - $baselineChecks) . " checks).\n";
