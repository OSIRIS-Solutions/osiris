<?php
// Standalone regression tests: php tests/initiatives.php
// Execute the production classes/routes with in-memory storage, omitting application bootstrap includes.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
define('BASEPATH', dirname(__DIR__));
define('ROOTPATH', '/osiris');
define('OSIRIS_BUILD', 'test');
define('OSIRIS_VERSION', 'test');
define('CURRENTYEAR', 2026);
define('OSIRIS_BASE_LANGUAGE', 'en');
require BASEPATH . '/vendor/autoload.php';
// Keep the production normalizers, but replace the page-rendering abort handler.
$configSource = file_get_contents(BASEPATH . '/php/_config.php');
$abortStart = strpos($configSource, 'function abortwith(');
$abortEnd = strpos($configSource, 'function formatBytes(', $abortStart);
eval('?>' . str_replace('__DIR__', var_export(BASEPATH . '/php', true), substr($configSource, 0, $abortStart) . substr($configSource, $abortEnd)));
require BASEPATH . '/php/Language.php';
$language = new Language(BASEPATH . '/lang');
function translate($key) { global $language; return $language->resolve($key, 'en', 'en'); }
function lang($en, $de = null, array $replace = []) { return $de === null ? translate($en) : $en; }
function localized($value, ?string $language = null) {
    if ($value instanceof Traversable) $value = iterator_to_array($value);
    if (!is_array($value)) return (string) $value;
    foreach (array_unique([$language ?? 'en', OSIRIS_BASE_LANGUAGE, ...array_keys($value)]) as $key) {
        if (isset($value[$key]) && $value[$key] !== '') return (string) $value[$key];
    }
    return '';
}
class Aborted extends RuntimeException {}
function abortwith($code, ...$args) { throw new Aborted('', $code); }
function loadIsolated(string $file): void
{
    global $DB, $osiris, $Settings, $initiative, $form, $role, $id, $Groups;
    $source = file_get_contents(BASEPATH . '/' . $file);
    $source = str_replace("include_once BASEPATH . '/php/init.php';", 'global $DB, $osiris, $Settings;', $source);
    $source = str_replace('include_once BASEPATH . "/php/init.php";', 'global $DB, $osiris, $Settings;', $source);
    $source = preg_replace('/^\s*(?:include|require)(?:_once)? (?![^\r\n]*(?:components\/modules\/|pages\/initiatives\/objective-form\.php)).*;\R/m', '', $source);
    $source = str_replace('__DIR__', var_export(dirname(BASEPATH . '/' . $file), true), $source);
    eval('?>' . $source);
}
class MemoryCollection
{
    public array $documents = [];
    private function matches(array $document, array $filter): bool
    {
        foreach ($filter as $key => $value) {
            if (is_array($value) && array_key_exists('$in', $value)) {
                if (!in_array((string) ($document[$key] ?? ''), array_map('strval', $value['$in']), true)) return false;
                continue;
            }
            if (is_array($value) && array_key_exists('$ne', $value)) {
                if (($document[$key] ?? null) === $value['$ne']) return false;
                continue;
            }
            if ((string) ($document[$key] ?? '') !== (string) $value) return false;
        }
        return true;
    }
    public function findOne(array $filter) {
        foreach ($this->documents as $document) if ($this->matches($document, $filter)) return $document;
        return null;
    }
    public function find(array $filter = [], array $options = []) {
        $items = array_values(array_filter($this->documents, fn($d) => $this->matches($d, $filter)));
        return new class($items) implements IteratorAggregate {
            public function __construct(private array $items) {}
            public function toArray() { return $this->items; }
            public function getIterator(): Traversable { return new ArrayIterator($this->items); }
        };
    }
    public function insertOne(array $document) {
        $document['_id'] ??= new MongoDB\BSON\ObjectId();
        $this->documents[(string) $document['_id']] = $document;
    }
    public function updateOne(array $filter, array $update) {
        foreach ($this->documents as &$document) {
            if ($this->matches($document, $filter)) {
                $document = array_replace($document, $update['$set']);
                return new class { public function getModifiedCount() { return 1; } };
            }
        }
        return new class { public function getModifiedCount() { return 0; } };
    }
}
class DB
{
    public static $database;
    public $db;
    public function __construct() { $this->db = self::$database; }
    public function getPerson($user = null) { return $this->db->persons->findOne(['username' => $user]) ?? []; }
    public function updateHistory($values, $id) { return $values; }
    public static function isUserActivity($doc, $user) { return in_array($user, self::doc2Arr($doc['rendered']['users'] ?? []), true) || ($doc['created_by'] ?? '') === $user; }
    public static function doc2Arr($value) { return $value instanceof Traversable ? iterator_to_array($value) : ($value ?? []); }
    public static function is_ObjectID($value) { return is_string($value) && preg_match('/^[a-f0-9]{24}$/i', $value); }
    public static function to_ObjectID($value) { return $value instanceof MongoDB\BSON\ObjectId ? $value : new MongoDB\BSON\ObjectId($value); }
}
class Settings {
    public function __construct() {}
    public function get($key, $default = null) { return $default; }
    public function getActivity($category, $type = null) {
        $id = $type ?? $category;
        return DB::$database->adminTypes->findOne(['id' => $id]) ?? ['id' => $id, 'name' => $id, 'color' => '#333', 'icon' => 'users', 'upload' => false];
    }
}
class Vocabulary {
    public static ?array $roles = null;
    public static ?array $objectiveTypes = null;
    public function getValues($key, $all = false) {
        if (!in_array($key, ['persons-role', 'objective-type'], true)) return [];
        $catalog = array_column(json_decode(file_get_contents(BASEPATH . '/data/vocabulary.json'), true), null, 'id');
        $roles = ($key === 'persons-role' ? self::$roles : self::$objectiveTypes) ?? $catalog[$key]['values'];
        return $all ? $roles : array_values(array_filter($roles, fn($role) => empty($role['inactive'])));
    }
    public function getValue($key, $id, $default = '') {
        foreach ($this->getValues($key, true) as $role) if ($role['id'] === $id) return $role['en'];
        return $default;
    }
}
class Route
{
    public static array $routes = [];
    public static function get($path, $fn, $visibility = 'public') { self::$routes['GET ' . $path] = [$fn, $visibility]; }
    public static function post($path, $fn, $visibility = 'public') { self::$routes['POST ' . $path] = [$fn, $visibility]; }
}
$Settings = new class {
    public bool $enabled = true;
    public array $continuousTypes = [];
    public function contentLanguages() { return ['en', 'de']; }
    public function get($key, $default = null) { return $default; }
    public array $permissions = ['initiatives.view', 'initiatives.edit'];
    public function featureEnabled($key) { return $this->enabled; }
    public function hasPermission($key) { return in_array($key, $this->permissions, true); }
};
$osiris = new stdClass();
foreach (['initiatives', 'objectives', 'persons', 'adminGeneral', 'adminFields', 'organizations', 'adminTypes', 'activities', 'groups'] as $collection) $osiris->$collection = new MemoryCollection();
DB::$database = $osiris;
$DB = new DB();
$_SESSION = ['username' => 'reviewer'];
$USER = [];
$osiris->persons->insertOne(['username' => 'reviewer', 'roles' => ['scientist']]);
loadIsolated('php/FormBuilder.php');
loadIsolated('php/Modules.php');
loadIsolated('php/Document.php');
loadIsolated('php/Organization.php');
loadIsolated('php/Initiatives.php');
loadIsolated('routes/initiatives.php');

$checks = 0;
function check(bool $value, string $message): void {
    global $checks;
    $checks++;
    if (!$value) throw new RuntimeException($message);
}
function rejects(callable $fn, string $message): void {
    try { $fn(); } catch (InvalidArgumentException $e) { check(true, $message); return; }
    throw new RuntimeException($message);
}
function aborts(callable $fn, int $code): void {
    try { $fn(); } catch (Aborted $e) { check($e->getCode() === $code, 'Wrong error status'); return; }
    throw new RuntimeException('Expected access rejection');
}
function postRoute(string $pattern, array $input, ...$args): void {
    $_POST = $input;
    $GLOBALS['_POST'] = $input;
    [$callback, $visibility] = Route::$routes['POST ' . $pattern];
    check($visibility === 'login', 'Every write route must require login.');
    $callback(...$args);
}

$fields = Initiatives::fields($osiris);
check(array_column($fields, 'id') === ['name', 'date-range-ongoing'], 'New forms include system fields.');
$config = new FormBuilder('Test', '', entity: 'initiatives', systemFields: FormBuilder::INITIATIVE_SYSTEM_FIELDS);
check(isset($config->modules['name'], $config->modules['date-range-ongoing']), 'System fields are offered for initiatives.');
check(!isset($config->modules['doi']), 'Activity-only fields are excluded.');
$scopeModule = new Modules(['scope' => 'international']);
ob_start(); $scopeModule->print_module('scope'); $scopeHtml = ob_get_clean();
$scopeDom = new DOMDocument(); @$scopeDom->loadHTML($scopeHtml); $scopeXpath = new DOMXPath($scopeDom);
check($scopeXpath->query('//option[@value="international" and @selected]')->length === 1, 'The selected scope round-trips using a stable vocabulary key.');
check($scopeXpath->query('//option[not(@value)]')->length === 0, 'Scope choices must not store translated display labels.');
$bsonFields = new MongoDB\Model\BSONArray([
    new MongoDB\Model\BSONDocument(['id' => 'name', 'type' => 'field', 'props' => new MongoDB\Model\BSONDocument(['required' => false])]),
]);
$bsonPrepared = FormBuilder::prepareFields(DB::doc2Arr($bsonFields), FormBuilder::INITIATIVE_SYSTEM_FIELDS);
check(count($bsonPrepared) === 2 && $bsonPrepared[1]['props']['required'] === true, 'Stored MongoDB form definitions are supported.');
rejects(fn() => Initiatives::dateInput("2026-01-01\0"), 'Malformed dates are rejected without crashing');
rejects(fn() => Initiatives::formValues(['name' => ' ', 'start' => '2026-01-01'], $fields, $config->modules), 'Empty names rejected');
rejects(fn() => Initiatives::formValues(['name' => 'Test', 'start' => '2026-02-30'], $fields, $config->modules), 'Invalid calendar dates rejected');
rejects(fn() => Initiatives::formValues(['name' => 'Test', 'start' => '2026-03-01', 'end' => '2026-02-01'], $fields, $config->modules), 'Reversed dates rejected');
rejects(fn() => Initiatives::formValues(['name' => 'Test'], $fields, $config->modules), 'Missing required start rejected');
check(!Initiatives::isExpired(['end_date' => '2026-11-10'], '2026-11-10'), 'End date is inclusive.');
check(Initiatives::isExpired(['end_date' => '2026-11-09'], '2026-11-10'), 'Past end date expires.');
check(!Initiatives::isExpired(['end_date' => null], '2026-11-10'), 'Open end remains current.');

postRoute('/crud/initiatives/create', ['values' => ['name' => '123', 'start' => '2035-01-01', 'end' => '', 'id' => 'spoof', 'created_by' => 'spoof']]);
$initiative = array_values($osiris->initiatives->documents)[0];
$id = (string) $initiative['_id'];
check($initiative['name'] === '123', 'Numeric-looking names remain text.');
check($initiative['id'] === $id && $initiative['created_by'] === 'reviewer', 'Identity and audit fields are server controlled.');
check($initiative['start']['year'] === 2035 && $initiative['start_date'] === '2035-01-01', 'Future initiatives avoid publication-year limits.');
check($initiative['end'] === null && $initiative['end_date'] === null, 'Open end round trips.');

postRoute('/crud/initiatives/update/(.*)', ['values' => ['name' => 'Renamed', 'start' => '2026-01-01', 'end' => '2026-12-31']], $id);
$initiative = $osiris->initiatives->findOne(['_id' => $initiative['_id']]);
check($initiative['name'] === 'Renamed' && count($osiris->initiatives->documents) === 1, 'Editing updates rather than recreates.');
check($initiative['id'] === $id && $initiative['created_by'] === 'reviewer', 'Editing preserves identity and creation metadata.');
postRoute('/crud/initiatives/update/(.*)', ['values' => ['name' => 'Renamed', 'start' => '2026-01-01', 'end' => '']], $id);
$initiative = $osiris->initiatives->findOne(['_id' => $initiative['_id']]);
check($initiative['end_date'] === null, 'Clearing an end date also clears its companion field.');

$osiris->adminFields->insertOne(['id' => 'notes', 'name' => 'Notes', 'format' => 'string']);
$osiris->adminFields->insertOne(['id' => 'secret', 'name' => 'Secret', 'format' => 'string']);
$osiris->adminGeneral->insertOne(['id' => 'initiative-fields', 'fields' => array_merge($fields, [
    ['id' => 'notes', 'type' => 'custom', 'props' => []],
    ['id' => 'secret', 'type' => 'custom', 'props' => ['roles' => 'admin']],
])]);
$osiris->initiatives->updateOne(['_id' => $initiative['_id']], ['$set' => ['secret' => 'Keep me']]);
postRoute('/crud/initiatives/update/(.*)', ['values' => ['name' => 'Custom', 'start' => '2026-01-01', 'notes' => 'Visible notes', 'secret' => 'Overwrite', 'unconfigured' => 'Ignore']], $id);
$initiative = $osiris->initiatives->findOne(['_id' => $initiative['_id']]);
check($initiative['notes'] === 'Visible notes' && $initiative['secret'] === 'Keep me' && !isset($initiative['unconfigured']), 'Only configured, visible fields can change.');

postRoute('/crud/initiatives/update/(.*)', ['values' => ['name' => 'Kept input', 'start' => 'invalid', '_id' => 'spoof']], $id);
check($_SESSION['initiative_form'][$id]['name'] === 'Kept input' && !isset($_SESSION['initiative_form'][$id]['_id']), 'Failed submissions preserve only editable input.');

rejects(fn() => Initiatives::objectiveValues(['name' => '']), 'Objectives require a name');
rejects(fn() => Initiatives::objectiveValues(['name' => 'Goal', 'end_date' => '2026-02-30']), 'Objectives require valid deadlines');

postRoute('/crud/initiatives/(.*)/objectives/create', ['values' => ['name' => 'Goal', 'end_date' => '2027-01-01', 'initiative' => 'spoof']], $id);
$objective = array_values($osiris->objectives->documents)[0];
$objectiveId = (string) $objective['_id'];
check($objective['initiative'] === $initiative['_id'], 'Objective belongs to its actual parent.');
postRoute('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', ['values' => ['name' => 'Updated goal', 'end_date' => '']], $id, 'update', $objectiveId);
check($osiris->objectives->findOne(['_id' => $objective['_id']])['end_date'] === null, 'Objective deadline can be cleared.');
postRoute('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', ['inactive' => '1'], $id, 'archive', $objectiveId);
check($osiris->objectives->findOne(['_id' => $objective['_id']])['inactive'] === true, 'Objectives archive without deletion.');
postRoute('/crud/initiatives/archive/(.*)', ['inactive' => '1'], $id);
check(count($osiris->objectives->documents) === 1, 'Archiving a parent preserves objectives.');
postRoute('/crud/initiatives/archive/(.*)', ['inactive' => '0'], $id);
check($osiris->initiatives->findOne(['_id' => $initiative['_id']])['inactive'] === false, 'Initiatives can be restored.');

$otherId = new MongoDB\BSON\ObjectId();
$osiris->initiatives->insertOne(['_id' => $otherId, 'name' => 'Other']);
aborts(fn() => postRoute('/crud/initiatives/(.*)/objectives/(update|archive)/(.*)', ['inactive' => '0'], (string) $otherId, 'archive', $objectiveId), 404);
$Settings->permissions = ['initiatives.view'];
aborts(fn() => postRoute('/crud/initiatives/archive/(.*)', ['inactive' => '1'], $id), 403);
aborts(fn() => postRoute('/crud/initiatives/create', ['values' => []]), 403);
$Settings->permissions = [];
aborts(fn() => Initiatives::requireAccess(), 403);
$Settings->permissions = ['initiatives.edit'];
check(Initiatives::canView($Settings), 'Editors can also see their initiatives.');
$Settings->enabled = false;
aborts(fn() => Initiatives::requireAccess(), 404);
$Settings->enabled = true;
aborts(fn() => Initiatives::find($osiris, 'invalid-id'), 404);

// Detail content delegates field values to the production Document renderer.
$organizationId = new MongoDB\BSON\ObjectId();
$osiris->organizations->insertOne(['_id' => $organizationId, 'name' => 'Partner & institute', 'location' => 'Berlin', 'type' => 'Education']);
foreach ([
    ['id' => 'choice', 'name' => 'Choice', 'format' => 'list', 'values' => [['en' => 'First choice', 'de' => 'Erste Wahl']]],
    ['id' => 'number', 'name' => 'Number', 'format' => 'int'],
    ['id' => 'flag', 'name' => 'Flag', 'format' => 'bool'],
    ['id' => 'narrative', 'name' => 'Narrative', 'format' => 'text-format'],
    ['id' => 'empty', 'name' => 'Empty', 'format' => 'string'],
] as $definition) $osiris->adminFields->insertOne($definition);
$detailFields = [
    ['id' => 'name', 'type' => 'field'],
    ['type' => 'heading', 'props' => ['text' => 'Form-only heading']],
    ['type' => 'paragraph', 'props' => ['text' => 'Form-only instructions']],
    ['type' => 'hr'],
    ['id' => 'date-range-ongoing', 'type' => 'field'],
    ['id' => 'organizations', 'type' => 'field'],
    ['id' => 'choice', 'type' => 'custom'],
    ['id' => 'number', 'type' => 'custom'],
    ['id' => 'flag', 'type' => 'custom'],
    ['id' => 'narrative', 'type' => 'custom'],
    ['id' => 'empty', 'type' => 'custom'],
    ['id' => 'notes', 'type' => 'custom', 'props' => ['label' => 'Public notes', 'width' => 3]],
];
$detailData = [
    'name' => 'Test', 'start' => ['year' => 2026, 'month' => 1, 'day' => 1], 'end' => null,
    'organizations' => [(string) $organizationId], 'choice' => 'First choice', 'number' => 0,
    'flag' => false, 'narrative' => '<p><strong>Formatted description</strong></p>',
    'empty' => null, 'notes' => '<script>alert(1)</script>',
];
$details = Initiatives::details($detailData, $detailFields);
$labels = array_column($details['metadata'], 'label');
check(!in_array('Form-only heading', $labels, true) && !in_array('Name', $labels, true), 'Form layout and duplicate page titles are excluded.');
check(!in_array('Empty', $labels, true), 'Empty metadata is omitted.');
$detailValues = array_column($details['metadata'], 'value', 'label');
$formatter = new Document(false, 'list');
$formatter->doc = $detailData;
check($detailValues['Date Range'] === $formatter->get_field('date-range-ongoing'), 'Dates use the shared formatter.');
check($detailValues['Organisations'] === $formatter->get_field('organizations'), 'Organization links use the shared formatter.');
check(str_contains($detailValues['Organisations'], '/organizations/view/' . $organizationId), 'Formatted links remain clickable.');
check($detailValues['Choice'] === 'First choice', 'Custom choices use the shared value labels.');
check($detailValues['Number'] === '0' && $detailValues['Flag'] === 'No', 'Zero and false are displayed rather than omitted.');
check($detailValues['Public notes'] === '&lt;script&gt;alert(1)&lt;/script&gt;', 'Plain-text custom fields remain escaped.');
check($details['texts'][0]['value'] === $formatter->get_field('narrative'), 'Rich-text fields retain their formatting in separate sections.');
$detailFields[11]['props']['width'] = 12;
check(Initiatives::details($detailData, $detailFields) === $details, 'Form column widths do not affect the detail layout.');

// Render the real templates with the same in-memory data; warnings are failures.
$initiative['name'] = '<script>alert(1)</script>Mission';
$initiative['notes'] = '<b>Notes</b>';
$osiris->initiatives->updateOne(['_id' => $initiative['_id']], ['$set' => ['name' => $initiative['name']]]);
foreach (['edit', 'view', 'list'] as $page) {
    $_GET = ['status' => 'all'];
    ob_start();
    loadIsolated('pages/initiatives/' . $page . '.php');
    $html = ob_get_clean();
    if (isset($argv[1])) file_put_contents($argv[1] . '/' . $page . '.html', $html);
    check(!str_contains($html, '<script>alert(1)</script>'), 'Untrusted names are escaped in ' . $page);
    if ($page === 'edit') {
        check(str_contains($html, ROOTPATH . '/crud/initiatives/update/' . $id), 'Edit form targets the existing record with ROOTPATH.');
        check(str_contains($html, 'Visible notes') || str_contains($html, '&lt;b&gt;Notes&lt;/b&gt;'), 'Configured values are populated.');
    }
    if ($page === 'view') check(!str_contains($html, 'Keep me'), 'Role-hidden fields are absent from details.');
}
$Settings->permissions = ['initiatives.view'];
ob_start();
loadIsolated('pages/initiatives/view.php');
$readOnlyHtml = ob_get_clean();
check(!str_contains($readOnlyHtml, '<form'), 'Read-only viewers receive no editing controls.');
$pastId = new MongoDB\BSON\ObjectId();
$osiris->initiatives->insertOne(['_id' => $pastId, 'name' => 'Expired entry', 'end_date' => '2000-01-01']);
$_GET = ['status' => 'current'];
ob_start(); loadIsolated('pages/initiatives/list.php'); $currentHtml = ob_get_clean();
check(!str_contains($currentHtml, 'Expired entry'), 'Current view excludes expired entries.');
$_GET = ['status' => 'past'];
ob_start(); loadIsolated('pages/initiatives/list.php'); $pastHtml = ob_get_clean();
check(str_contains($pastHtml, 'Expired entry'), 'Past view retains access to expired entries.');
echo "Initiative regression tests passed ($checks checks).\n";
