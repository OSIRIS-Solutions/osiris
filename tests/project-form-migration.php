<?php
// Run: php tests/project-form-migration.php (no database required).
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
define('BASEPATH', dirname(__DIR__));
require BASEPATH . '/vendor/autoload.php';
require BASEPATH . '/php/Language.php';
require BASEPATH . '/php/ProjectFormMigration.php';
$checks = 0;
function check(bool $condition, string $message): void {
    global $checks;
    $checks++;
    if (!$condition) throw new RuntimeException($message);
}
function lang($en, $de = null) { return $en; }
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function migrationCard(...$args) {}
class DB {
    public static function doc2Arr($value) { return $value instanceof Traversable ? iterator_to_array($value) : $value; }
}
class Vocabulary {
    protected $db;
    public function __construct() { global $osiris; $this->db = $osiris; }
}
// Exercise the real legacy catalogue and getFields(), without application bootstrap.
$projectSource = file_get_contents(BASEPATH . '/php/Project.php');
$projectSource = preg_replace('/^require_once .*;\R/m', '', $projectSource);
eval('?>' . $projectSource);
class MigrationCollection {
    public int $writes = 0;
    public function __construct(public array $documents = []) {}
    private function matches($document, $filter): bool {
        foreach ($filter as $path => $expected) {
            $value = $document;
            $exists = true;
            foreach (explode('.', $path) as $key) {
                if (!isset($value[$key])) { $exists = false; break; }
                $value = $value[$key];
            }
            if (is_array($expected) && isset($expected['$exists'])) {
                if ($exists !== $expected['$exists']) return false;
            } elseif (!$exists || $value != $expected) return false;
        }
        return true;
    }
    public function find($filter = [], $options = []) {
        return array_values(array_filter($this->documents, fn($document) => $this->matches($document, $filter)));
    }
    public function findOne($filter) { return $this->find($filter)[0] ?? null; }
    public function updateOne($filter, $update) {
        $modified = 0;
        foreach ($this->documents as &$document) {
            if (!$this->matches($document, $filter)) continue;
            foreach ($update['$set'] as $path => $value) {
                $target = &$document;
                foreach (explode('.', $path) as $key) $target = &$target[$key];
                $target = $value;
                unset($target);
            }
            $modified = 1;
            $this->writes++;
            break;
        }
        return new class($modified) {
            public function __construct(private int $modified) {}
            public function getModifiedCount() { return $this->modified; }
        };
    }
}
$osiris = (object) [
    'adminFields' => new MigrationCollection([
        ['id' => 'custom-second', 'name' => 'Second', 'name_de' => 'Zweites', 'order' => 20],
        ['id' => 'custom-first', 'name' => 'First', 'name_de' => 'Erstes', 'order' => 10],
    ]),
    'adminFeatures' => new MigrationCollection([['feature' => 'topics', 'enabled' => true]]),
    'adminGeneral' => new MigrationCollection([['key' => 'topics_label', 'value' => ['en' => 'Research areas', 'de' => 'Forschungsbereiche']]]),
    'languageOverrides' => new MigrationCollection(),
];
$legacy = new Project();
$language = new Language(BASEPATH . '/lang');
$migration = new ProjectFormMigration($legacy->FIELDS, $language);
$modules = [
    ['module' => 'abstract_de', 'required' => true],
    ['module' => 'custom-second', 'required' => true],
    ['module' => 'website', 'required' => true],
    ['module' => 'title_de', 'required' => true],
    ['module' => 'name_de', 'required' => true],
    ['module' => 'custom-first', 'required' => false],
    ['module' => 'comment', 'required' => true],
    ['module' => 'topics', 'required' => false],
    ['module' => 'nagoya', 'required' => false],
    ['module' => 'personnel', 'required' => false],
    ['module' => 'funding_program', 'required' => true],
    ['module' => 'funding_program_select', 'required' => false],
    ['module' => 'retired-field', 'required' => true],
];
$fields = $migration->fields('proposed', new MongoDB\Model\BSONArray(array_map(fn($m) => new MongoDB\Model\BSONDocument($m), $modules)));
$byId = array_column(array_filter($fields, fn($f) => isset($f['id'])), null, 'id');
$ids = array_keys($byId);
check($ids === ['submission_date', 'comment', 'name-loc', 'title-loc', 'start_proposed', 'end_proposed', 'applicants', 'funder', 'funding_program_select', 'funding_program', 'abstract-loc', 'website', 'topics', 'custom-first', 'custom-second', 'nagoya', 'personnel', 'retired-field'], 'Fields follow the old visible form, including custom catalogue order and retained unknown fields.');
foreach (['name-loc', 'title-loc', 'submission_date', 'start_proposed', 'end_proposed', 'applicants', 'funder'] as $id) {
    check($byId[$id]['props']['required'] === true, "System requirement retained for $id.");
}
check($byId['abstract-loc']['props']['required'] === false, 'A required German-only field does not make the English value mandatory.');
check(!isset($byId['abstract_de'], $byId['name_de'], $byId['title_de']), 'Legacy translations merge into localized modules.');
check($byId['website']['props']['required'] && $byId['comment']['props']['required'], 'Configured optional fields keep their required flags.');
check($byId['custom-first']['type'] === 'custom' && $byId['custom-second']['props']['required'], 'Custom fields retain type and requirements.');
check($byId['name-loc']['props']['label'] === 'Short title' && $byId['name-loc']['props']['label_de'] === 'Kurztitel', 'Project-specific labels survive generic module reuse.');
check($byId['topics']['props']['label'] === 'Research areas', 'Institutional topic labels are retained.');
$headings = array_values(array_filter($fields, fn($f) => $f['type'] === 'heading'));
check(array_column(array_column($headings, 'props'), 'text') === ['Submission', 'General information', 'Persons', 'Funding', 'Project description', 'Institutional fields', 'Nagoya Protocol', 'Resources and Personnel'], 'Existing section headings are explicit ordered builder elements.');
check($headings[1]['props']['text_de'] === $language->resolve('projects.general_information', 'de', 'en'), 'Headings include German translations.');
$empty = $migration->fields('rejected', []);
check(array_column($empty, 'id') === ['rejection_date'], 'Even an empty phase includes its system fields.');
check(count(array_filter($empty, fn($f) => $f['type'] === 'heading')) === 1, 'Empty sections are omitted.');
$plain = array_column($migration->fields('project', []), null, 'id');
check(isset($plain['name'], $plain['title']) && !isset($plain['name-loc']), 'Without a German companion, plain modules are retained.');
$duplicates = array_column($migration->fields('rejected', ['website', 'website*']), null, 'id');
check($duplicates['website']['props']['required'] === true, 'Repeated references merge without losing required flags.');
$allModules = array_map(fn($id) => ['module' => $id, 'required' => false], array_keys($legacy->FIELDS));
$allFields = $migration->fields('proposed', $allModules);
$allIds = array_column($allFields, 'id');
foreach (array_keys($legacy->FIELDS) as $id) {
    if (in_array($id, ['type', 'status'], true)) continue;
    $base = str_ends_with($id, '_de') ? substr($id, 0, -3) : $id;
    $target = in_array($base, ['name', 'title', 'abstract'], true) ? $base . '-loc' : $id;
    check(in_array($target, $allIds, true), "Catalogue field $id survives migration.");
}
check(!in_array('type', $allIds, true) && !in_array('status', $allIds, true), 'Hidden workflow values stay outside the editable form configuration.');
check(count($allIds) === count(array_unique($allIds)), 'Every target field appears once.');

// Execute the production migration against mixed existing, empty and legacy phases.
$existingFields = [['type' => 'heading', 'props' => ['text' => 'Already edited']]];
$original = [
    ['_id' => 'one', 'id' => 'grant', 'process' => 'proposal', 'modules' => ['old-root'], 'phases' => [
        ['id' => 'proposed', 'name' => 'Custom phase name', 'modules' => $modules],
        ['id' => 'approved', 'modules' => [], 'fields' => $existingFields],
        ['id' => 'project', 'modules' => [], 'fields' => []],
        ['id' => 'rejected', 'modules' => []],
    ]],
    ['_id' => 'two', 'id' => 'disabled', 'disabled' => true, 'phases' => [['id' => 'project', 'modules' => []]]],
    ['_id' => 'three', 'id' => 'no-phases', 'phases' => []],
];
$osiris->adminProjects = new MigrationCollection($original);
$osiris->projects = new MigrationCollection([['name' => 'Old English', 'name_de' => 'Altes Deutsch']]);
$osiris->proposals = new MigrationCollection([['name' => 'Proposal', 'name_de' => 'Antrag']]);
function runMigration(): array {
    global $osiris;
    $source = file_get_contents(BASEPATH . '/routes/migration/project-forms.php');
    $source = str_replace("require_once BASEPATH . '/php/Project.php';", '', $source);
    ob_start();
    eval('?>' . $source);
    ob_end_clean();
    return [$created, $existing];
}
$legacyBefore = $legacy->getFields('grant', 'proposed');
check(runMigration() === [3, 2], 'Migration creates only missing fields, including disabled types.');
$migrated = $osiris->adminProjects->documents;
check($migrated[0]['phases'][0]['fields'] === $fields, 'Production migration writes the converted phase fields.');
check($migrated[0]['phases'][1]['fields'] === $existingFields && $migrated[0]['phases'][2]['fields'] === [], 'Existing fields, including explicitly empty ones, are preserved.');
foreach ($migrated as $i => $type) {
    foreach ($type['phases'] as $index => &$phase) {
        if (!array_key_exists('fields', $original[$i]['phases'][$index])) unset($phase['fields']);
    }
    unset($phase);
    check($type === $original[$i], 'All legacy configuration and phase metadata remains unchanged.');
}
check(runMigration() === [0, 5] && $osiris->adminProjects->documents === $migrated, 'Rerunning the migration does not overwrite fields or create duplicates.');
check($osiris->adminProjects->writes === 3, 'Rerunning performs no database writes.');
check($osiris->projects->documents === [['name' => 'Old English', 'name_de' => 'Altes Deutsch']] && $osiris->proposals->documents === [['name' => 'Proposal', 'name_de' => 'Antrag']], 'Existing project and proposal data stays in its old format.');
// getFields still sees precisely the same old configuration after migration.
check($legacy->getFields('grant', 'proposed') === $legacyBefore, 'Legacy phase lookup and required flags remain unchanged.');
echo "Project form migration tests passed ($checks checks).\n";
