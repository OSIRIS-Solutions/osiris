<?php
// Initiative navigation and dynamic command-palette results, without a live database.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/CommandPalette.php');
class InitiativePaletteSettings extends Settings
{
    public bool $enabled = true;
    public array $permissions = ['initiatives.view'];
    public array $topics = [], $activityCategories = [], $roles = ['scientist'];
    public object $osiris;
    public function featureEnabled($feature) { return $this->enabled && $feature === 'initiatives'; }
    public function hasPermission($permission) { return in_array($permission, $this->permissions, true); }
    public function contentLanguages() { return ['en', 'de', 'it']; }
    public function topicLabel() { return 'Research areas'; }
    public function journalLabel() { return 'Journals'; }
    public function infrastructureLabel() { return 'Infrastructures'; }
    public function tripLabel() { return 'Trips'; }
}
$Settings = new InitiativePaletteSettings();
$Settings->osiris = $osiris;
$osiris->queries = new MemoryCollection();
$items = array_column((new CommandPalette($Settings))->get()['groups'][0]['items'], null, 'url');
check(isset($items['/initiatives'], $items['/initiatives?status=objectives']) && $items['/initiatives']['icon'] === 'compass' && $items['/initiatives?status=objectives']['icon'] === 'target', 'Palette navigation includes both overview pages with their entity icons.');
class InitiativePaletteCollection
{
    public array $pipeline = [];
    public function __construct(public array $records) {}
    public function aggregate(array $pipeline) { $this->pipeline = $pipeline; return $this->records; }
}
$initiativeId = new MongoDB\BSON\ObjectId();
$objectiveId = new MongoDB\BSON\ObjectId();
$osiris->initiatives = new InitiativePaletteCollection([['_id' => $initiativeId, 'name' => ['en' => 'Climate Futures', 'de' => 'Klimazukunft'], '_cp_score' => 40]]);
$osiris->objectives = new InitiativePaletteCollection([['_id' => $objectiveId, 'code' => '26-01', 'name' => 'Resilience indicators', '_cp_score' => 60]]);
$osiris->adminGeneral = new MemoryCollection();
$osiris->adminGeneral->insertOne(setupDefinition('initiatives'));
$source = file_get_contents(BASEPATH . '/routes/api/dashboard.php');
$start = strpos($source, '    // --- Initiatives and objectives');
$source = substr($source, $start, strpos($source, '    // --- Projects', $start) - $start);
$source = preg_replace('/^\s*require_once .*;\R/m', '', $source);
$search = function () use ($source, $Settings, $osiris) {
    $q = 'Climate (test)'; $rxContain = preg_quote($q, '/'); $rxPrefix = '^' . $rxContain; $groups = [];
    eval($source);
    return $groups;
};
$groups = $search();
check($groups[0]['items'][0]['url'] === '/initiatives/view/' . $initiativeId && $groups[1]['items'][0]['url'] === '/initiatives/objectives/view/' . $objectiveId && $groups[1]['items'][0]['label'] === '26-01: Resilience indicators', 'Search results link to entity details and distinguish objectives by identifier.');
$paths = array_merge(...array_map('array_keys', $osiris->initiatives->pipeline[0]['$match']['$or']));
check(in_array('name.it', $paths, true) && in_array('title.de', $paths, true) && $osiris->initiatives->pipeline[0]['$match']['$or'][0]['name']['$regex'] === 'Climate \(test\)' && $osiris->initiatives->pipeline[3]['$limit'] === 6, 'Search includes configured translations and visible titles, escapes regex input and limits results per entity.');
$fields = setupDefinition('initiatives')['fields'];
foreach ($fields as &$field) if ($field['id'] === 'title') $field['props']['roles'] = 'admin';
unset($field);
$osiris->adminGeneral->updateOne(['id' => 'initiative-fields'], ['$set' => ['fields' => $fields]]);
$search();
$paths = array_merge(...array_map('array_keys', $osiris->initiatives->pipeline[0]['$match']['$or']));
check(!in_array('title', $paths, true) && !in_array('title.de', $paths, true), 'Restricted titles cannot be discovered through palette searches.');
$Settings->permissions = ['initiatives.edit'];
check(count($search()) === 2, 'Initiative editors can find records without a separate view grant.');
$Settings->permissions = [];
$items = array_column((new CommandPalette($Settings))->get()['groups'][0]['items'], null, 'url');
check($search() === [] && !isset($items['/initiatives'], $items['/initiatives?status=objectives']), 'View and edit permissions govern both static navigation and search results.');
$Settings->enabled = false;
$Settings->permissions = ['initiatives.view'];
$items = array_column((new CommandPalette($Settings))->get()['groups'][0]['items'], null, 'url');
check($search() === [] && !isset($items['/initiatives']), 'Disabling initiatives removes both static and dynamic results.');
ob_end_clean(); echo $baselineOutput; echo 'Command palette initiative checks passed (' . ($checks - $baselineChecks) . " checks).\n";
