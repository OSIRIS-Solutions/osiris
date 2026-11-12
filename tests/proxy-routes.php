<?php
// Standalone route regressions: php tests/proxy-routes.php
if (!isset($argv[1])) {
    foreach (['journal-ok', 'journal-offline', 'journal-json', 'country-ok', 'country-offline', 'country-json', 'country-empty', 'country-invalid'] as $scenario) {
        $process = proc_open([PHP_BINARY, __FILE__, $scenario], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0) throw new RuntimeException("$scenario: $output $errors");
        echo "PASS $scenario\n";
    }
    exit;
}
$scenario = $argv[1];
$fixture = sys_get_temp_dir() . '/osiris-proxy-routes-' . bin2hex(random_bytes(8));
mkdir($fixture . '/php', 0700, true);
foreach (['php/init.php', 'php/HttpClient.php', 'footer.php'] as $file) file_put_contents($fixture . '/' . $file, '<?php');
file_put_contents($fixture . '/php/init.php', '<?php global $osiris, $DB, $Settings;');
define('BASEPATH', $fixture);
class HttpClient
{
    public static $calls = [];
    public static function get($url, $headers = [], $timeout = 10)
    {
        global $scenario;
        self::$calls[] = [$url, $timeout];
        if (str_ends_with($scenario, '-offline')) throw new RuntimeException('HTTP request failed (cURL error 28).');
        if (str_ends_with($scenario, '-json')) return '<html>invalid JSON</html>';
        if ($scenario === 'country-empty') return '{}';
        if ($scenario === 'country-invalid') return '[{"alpha2":"DE","alpha3":"DEU","en":"Germany"},{"error":true}]';
        if ($scenario === 'country-ok') return '[{"alpha2":"de","alpha3":"deu","en":"Germany","de":"Deutschland"}]';
        return '{"metrics":[{"year":2025,"if_2y":2.5}]}';
    }
}
class Route
{
    public static $create;
    public static function get(...$args) {}
    public static function post($path, $callback, ...$args) { if ($path === '/crud/journal/create') self::$create = $callback; }
}
class Country { public static function countryToContinent($iso) { return 'Europe'; } }
function validateValues($values, $db) { return $values; }
class Collection
{
    public $documents = [['old' => true]];
    public $deleted = false;
    public function findOne($filter) { return null; }
    public function deleteMany($filter) { $this->deleted = true; $this->documents = []; }
    public function insertOne($values) { $this->documents[] = $values; return $this; }
    public function getInsertedId() { return 'test-id'; }
    public function getInsertedCount() { return 1; }
}
$collection = new Collection;
$osiris = (object)['countries' => $collection, 'journals' => $collection];
$Settings = new class { public function featureEnabled($feature) { return false; } };
$DB = null;
$_SESSION = ['username' => 'test'];
$_POST = ['values' => ['journal' => 'Test journal', 'issn' => ['1234-5678', '8765-4321']]];
ob_start();
register_shutdown_function(function () use ($fixture, $scenario, $collection) {
    $output = ob_get_clean();
    foreach (['php/init.php', 'php/HttpClient.php', 'footer.php'] as $file) unlink($fixture . '/' . $file);
    rmdir($fixture . '/php');
    rmdir($fixture);
    $ok = count(HttpClient::$calls) === 1;
    if (str_starts_with($scenario, 'journal-')) {
        $saved = $collection->documents[1] ?? [];
        $ok = $ok && ($saved['journal'] ?? null) === 'Test journal'
            && ($saved['issn'] ?? []) === ['1234-5678', '8765-4321']
            && (json_decode($output, true)['inserted'] ?? null) === 1;
        $ok = $ok && ($scenario === 'journal-ok' ? ($saved['impact'][0]['impact'] ?? null) === 2.5 : !isset($saved['metrics']));
    } elseif ($scenario === 'country-ok') {
        $ok = $ok && $collection->deleted && ($collection->documents[0]['iso'] ?? null) === 'DE';
    } else {
        $ok = $ok && !$collection->deleted && $collection->documents === [['old' => true]] && str_contains($output, 'Error');
    }
    if (!$ok) { fwrite(STDERR, "Failed: $scenario\n$output\n"); exit(1); }
});
if (str_starts_with($scenario, 'journal-')) {
    require dirname(__DIR__) . '/routes/journals.php';
    (Route::$create)();
} else {
    require dirname(__DIR__) . '/routes/migration/v1.4.2.php';
}
