<?php
// Run: php tests/project-form-migration.php (no database required).
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
define('BASEPATH', dirname(__DIR__));
define('ROOTPATH', '/osiris');
require BASEPATH . '/php/FormBuilder.php';
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
            foreach ($update['$unset'] ?? [] as $key => $ignored) unset($document[$key]);
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
check($ids === ['submission_date', 'comment', 'name-loc', 'title-loc', 'project-date-proposed', 'persons-project', 'funder', 'funding_program_select', 'funding_program', 'abstract-loc', 'website', 'topics', 'custom-first', 'custom-second', 'nagoya', 'personnel', 'retired-field'], 'Fields follow the old visible form, including custom catalogue order and retained unknown fields.');
foreach (['name-loc', 'title-loc', 'submission_date', 'project-date-proposed', 'persons-project', 'funder'] as $id) {
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
check(array_column($empty, 'id') === ['rejection_date', 'persons-project'], 'Even an empty phase includes its system fields.');
check(count(array_filter($empty, fn($f) => $f['type'] === 'heading')) === 2, 'Only sections containing fields are emitted.');
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
    $target = match ($id) {
        'start', 'end' => 'project-date',
        'start_proposed', 'end_proposed' => 'project-date-proposed',
        'role' => 'institute-role',
        'applicants', 'scholar', 'supervisor' => 'persons-project',
        default => in_array($base, ['name', 'title', 'abstract'], true) ? $base . '-loc' : $id,
    };
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
$activityOriginal = [
    ['_id' => 'legacy-activity', 'id' => 'thesis', 'modules' => ['title*', 'university*']],
    ['_id' => 'modern-activity', 'id' => 'other-thesis', 'modules' => ['university'], 'fields' => [
        ['type' => 'heading', 'props' => ['text' => 'Details']],
        ['id' => 'university', 'type' => 'field', 'props' => ['required' => false, 'label' => 'My university', 'label_de' => 'Meine Hochschule', 'width' => 6]],
    ]],
    ['_id' => 'custom-activity', 'id' => 'custom', 'modules' => [], 'fields' => [['id' => 'university', 'type' => 'custom', 'props' => []]]],
];
$osiris->adminTypes = new MigrationCollection($activityOriginal);
$osiris->adminProjects = new MigrationCollection($original);
$osiris->projects = new MigrationCollection([['_id' => 'project', 'name' => 'Old English', 'name_de' => 'Altes Deutsch']]);
$osiris->proposals = new MigrationCollection([['_id' => 'proposal', 'name' => 'Proposal', 'name_de' => 'Antrag']]);
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
check(runMigration() === [5, 0], 'Migration creates missing fields and adds the mandatory project persons to existing forms.');
$migrated = $osiris->adminProjects->documents;
check($migrated[0]['phases'][0]['fields'] === $fields, 'Production migration writes the converted phase fields.');
check($migrated[0]['phases'][1]['fields'][1] === $existingFields[0], 'Existing layout elements are preserved when system fields are added.');
check(array_column($migrated[0]['phases'][2]['fields'], 'id') === ['persons-project'], 'Even an empty form receives its mandatory persons field.');
foreach ($migrated as $i => $type) {
    foreach ($type['phases'] as $index => &$phase) {
        unset($phase['fields']);
    }
    unset($phase);
    $expected = $original[$i];
    foreach ($expected['phases'] as &$phase) unset($phase['fields']);
    unset($phase);
    check($type === $expected, 'All legacy configuration and phase metadata remains unchanged.');
}
check(runMigration() === [0, 5] && $osiris->adminProjects->documents === $migrated, 'Rerunning the migration does not overwrite fields or create duplicates.');
check($osiris->adminProjects->writes === 5, 'Rerunning performs no database writes.');
check($osiris->projects->documents === [['_id' => 'project', 'name' => ['en' => 'Old English', 'de' => 'Altes Deutsch']]] && $osiris->proposals->documents === [['_id' => 'proposal', 'name' => ['en' => 'Proposal', 'de' => 'Antrag']]], 'Project and proposal content migrates to language maps alongside the forms.');
check($osiris->projects->writes === 1 && $osiris->proposals->writes === 1, 'Content migration is idempotent.');
// getFields still sees precisely the same old configuration after migration.
check($legacy->getFields('grant', 'proposed') === $legacyBefore, 'Legacy phase lookup and required flags remain unchanged.');
$activityMigrated = $osiris->adminTypes->documents;
check($activityMigrated[0]['modules'] === ['title*', 'publisher*'], 'Legacy activity module identifiers are renamed without losing required flags.');
check($activityMigrated[0]['fields'][1] === ['id' => 'publisher', 'type' => 'field', 'props' => ['label' => 'University', 'label_de' => 'Universität', 'required' => true]], 'Activities with only modules get fields so the University label can be retained.');
check($activityMigrated[1]['fields'][0] === $activityOriginal[1]['fields'][0], 'Activity layout elements remain unchanged.');
check($activityMigrated[1]['fields'][1]['props']['label'] === 'My university' && $activityMigrated[1]['fields'][1]['props']['width'] === 6, 'Existing university label overrides and width are preserved.');
check($activityMigrated[2] === $activityOriginal[2], 'A custom field named university is not renamed.');
check($osiris->adminTypes->writes === 2, 'Activity migration is idempotent.');
$upgraded = $migration->upgradeFields([
    ['type' => 'heading', 'props' => ['text' => 'Custom heading']],
    ['id' => 'role', 'type' => 'field', 'props' => ['label' => 'Our role', 'required' => true]],
    ['id' => 'end', 'type' => 'field', 'props' => ['required' => false]],
    ['id' => 'start', 'type' => 'field', 'props' => ['required' => true]],
    ['id' => 'applicants', 'type' => 'field', 'props' => ['required' => false]],
    ['id' => 'scholar', 'type' => 'field', 'props' => []],
    ['id' => 'supervisor', 'type' => 'field', 'props' => []],
]);
check(array_column($upgraded, 'id') === ['institute-role', 'project-date', 'persons-project'], 'Prior migrated forms adopt the new module identities without duplicates.');
check($upgraded[0]['props']['text'] === 'Custom heading' && $upgraded[1]['props']['label'] === 'Our role', 'Unrelated layout and role label edits survive migration.');
check($upgraded[2]['props']['required'] === true, 'Combined project dates retain a required start or end.');
check($migration->upgradeFields($upgraded) === $upgraded, 'Upgrading fields is stable on repeated calls.');
$customized = $upgraded;
$customized[2]['props']['label'] = 'Duration';
$customized[3]['props']['label'] = 'Team';
check($migration->upgradeFields($customized) === $customized, 'Existing project-date and persons-project labels remain configurable.');
$osiris->projects->documents[0]['name'] = new MongoDB\Model\BSONDocument(['en' => 'Old English', 'de' => 'Altes Deutsch']);
runMigration();
check($osiris->projects->writes === 1, 'Already migrated BSON language maps do not trigger another write.');
echo "Project form migration tests passed ($checks checks).\n";
