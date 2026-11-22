<?php
// Run the real 3.0 upgrade callback with in-memory records, then repeat localization.
$repository = dirname(__DIR__);
$fixture = sys_get_temp_dir() . '/osiris-localization-upgrade-' . bin2hex(random_bytes(6));
mkdir($fixture . '/php', 0700, true);
mkdir($fixture . '/routes/migration', 0700, true);
define('BASEPATH', $fixture);
define('ROOTPATH', '');
define('OSIRIS_VERSION', '3.0.0');
function lang($key, $de = null, array $replace = []) { return $key; }
class Route {
    public static array $posts = [];
    public static array $gets = [];
    public static function get($path, $callback, ...$options) { self::$gets[$path] = $callback; }
    public static function post($path, $callback, ...$options) { self::$posts[$path] = $callback; }
}
class DB { public static function doc2Arr($value) { return $value instanceof Traversable ? iterator_to_array($value) : ($value ?? []); } }
class UpgradeCollection {
    public int $writes = 0;
    public function __construct(public array $documents) {}
    public function find() { return $this->documents; }
    public function findOne($filter) {
        foreach ($this->documents as $document) if (!array_diff_assoc($filter, $document)) return $document;
        return null;
    }
    public function updateOne($filter, $update, $options = []) {
        foreach ($this->documents as &$document) {
            if (array_diff_assoc($filter, $document)) continue;
            foreach ($update['$set'] ?? [] as $key => $value) {
                if ($key === 'value.positions') $document['value']['positions'] = $value;
                else $document[$key] = $value;
            }
            foreach ($update['$unset'] ?? [] as $key => $value) unset($document[$key]);
            $this->writes++;
            return;
        }
        if (!empty($options['upsert'])) { $this->documents[] = $filter + $update['$set']; $this->writes++; }
    }
    public function createIndex(...$arguments) {}
}
function checkUpgrade($condition, $message) { if (!$condition) throw new RuntimeException($message); }
$Settings = new class { public function hasPermission($right) { return $right === 'admin.see'; } };
$osiris = (object) [
    'system' => new UpgradeCollection([['key' => 'version', 'value' => '2.2.0']]),
    'groups' => new UpgradeCollection([['_id' => 'group', 'name' => 'Water', 'name_de' => 'Wasser', 'description' => ['en' => 'Current', 'de' => 'Aktuell'], 'description_de' => 'Old', 'research' => [['title' => 'Climate', 'title_de' => 'Klima']]]]),
    'persons' => new UpgradeCollection([['_id' => 'person', 'position' => 'Scientist', 'position_de' => 'Wissenschaftler', 'education' => ['en' => 'Degree', 'fr' => 'Diplôme'], 'education_de' => 'Abschluss', 'research' => ['Water'], 'research_de' => ['Wasser', 'Klima']]]),
    'adminGeneral' => new UpgradeCollection([['_id' => 'staff', 'key' => 'staff', 'value' => ['positions' => [['Scientist', 'Wissenschaftler']]]]]),
];
$created = [];
try {
    foreach (['php/init.php' => '<?php global $osiris, $Settings;', 'php/Render.php' => '<?php', 'header.php' => '', 'footer.php' => ''] as $file => $contents) {
        file_put_contents($fixture . '/' . $file, $contents); $created[] = $fixture . '/' . $file;
    }
    foreach (['languages', 'localization', 'project-forms', 'event-forms', 'deadline-forms', 'initiative-forms', 'infrastructure-forms'] as $migration) {
        $file = $fixture . '/routes/migration/' . $migration . '.php';
        file_put_contents($file, $migration === 'localization' ? '<?php include ' . var_export($repository . '/routes/migration/localization.php', true) . ';' : '<?php');
        $created[] = $file;
    }
    require $repository . '/routes/migrate.php';
    ob_start(); ob_start();
    (Route::$gets['/migrate'])();
    ob_end_clean(); ob_end_clean();
    checkUpgrade($osiris->system->findOne(['key' => 'version'])['value'] === '3.0.0', 'Upgrade must finish at the configured release version.');
    $group = $osiris->groups->documents[0]; $person = $osiris->persons->documents[0];
    checkUpgrade($group['name'] === ['en' => 'Water', 'de' => 'Wasser'] && !isset($group['name_de']), 'The general upgrade must migrate group names and their German translations.');
    checkUpgrade($group['description']['de'] === 'Aktuell' && $group['research'][0]['title']['de'] === 'Klima', 'Existing translations must win and nested group research must be preserved.');
    checkUpgrade($person['position']['de'] === 'Wissenschaftler' && $person['education']['fr'] === 'Diplôme' && $person['education']['de'] === 'Abschluss', 'The upgrade must retain existing and legacy profile translations.');
    checkUpgrade($person['research'] === [['en' => 'Water', 'de' => 'Wasser'], ['de' => 'Klima']], 'Different-length research lists must not lose entries.');
    checkUpgrade($osiris->adminGeneral->documents[0]['value']['positions'] === [['en' => 'Scientist', 'de' => 'Wissenschaftler']], 'The staff position vocabulary must be migrated too.');
    $before = serialize([$osiris->groups, $osiris->persons, $osiris->adminGeneral]);
    ob_start(); include $repository . '/routes/migration/localization.php'; ob_end_clean();
    checkUpgrade(serialize([$osiris->groups, $osiris->persons, $osiris->adminGeneral]) === $before, 'Repeating localization must not change records or perform another write.');
    echo "Localization upgrade checks passed.\n";
} finally {
    foreach ($created as $file) unlink($file);
    rmdir($fixture . '/routes/migration'); rmdir($fixture . '/routes'); rmdir($fixture . '/php'); rmdir($fixture);
}
