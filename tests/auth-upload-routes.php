<?php
// Execute the real callbacks in isolated processes: no database, mail delivery or web server.
if (!isset($argv[1])) {
    $run = static function ($scenario) {
        $process = proc_open([PHP_BINARY, __FILE__, $scenario], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $output = stream_get_contents($pipes[1]); $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]);
        if (proc_close($process) !== 0 || $errors !== '') throw new RuntimeException($scenario . ': ' . $errors . $output);
        return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
    };
    $check = static function ($condition, $message) { if (!$condition) throw new RuntimeException($message); };
    $unknown = $run('reset-unknown'); $waiting = $run('reset-waiting'); $sent = $run('reset-send');
    $check($unknown['session'] === $waiting['session'] && $sent['session'] === $waiting['session'], 'All accepted reset requests must show the same message and message type.');
    $check($unknown['mail_count'] === 0 && $unknown['writes'] === 0, 'An unknown account must not trigger mail or create a reset record.');
    $check($waiting['mail_count'] === 0 && $waiting['writes'] === 0 && $waiting['hash'] === 'existing-reset-link', 'The cooldown must preserve the previous link without sending another message.');
    $check($sent['mail_count'] === 1 && $sent['writes'] === 1 && $sent['hash'] !== 'existing-reset-link', 'A valid reset request must still save a new hash and send exactly one message.');
    $sso = $run('reset-no-password');
    $check($sso['mail_count'] === 1 && $sso['writes'] === 1, 'Profiles without a local password account must still be able to request one.');
    foreach (['central-reader', 'central-manager', 'proposal-member', 'activity'] as $scenario) {
        $result = $run('upload-' . $scenario);
        $check($result['error'] === null && $result['body'] === 'fixture file', $scenario . ': allowed file access stopped working.');
    }
    foreach (['central-denied' => 403, 'proposal-denied' => 403, 'guest-denied' => 403, 'outside' => 403, 'missing' => 404] as $scenario => $code) {
        $result = $run('upload-' . $scenario);
        $check($result['error'] === $code && $result['body'] === '', $scenario . ': unauthorized or invalid access must not disclose file contents.');
    }
    echo "Password reset and upload route checks passed.\n";
    exit;
}
$scenario = $argv[1];
$repository = dirname(__DIR__);
$fixture = sys_get_temp_dir() . '/osiris-auth-upload-' . bin2hex(random_bytes(6));
mkdir($fixture . '/php', 0700, true); mkdir($fixture . '/uploads/guest', 0700, true);
define('BASEPATH', $fixture); define('ROOTPATH', ''); define('BASE_URL', 'https://example.test');
file_put_contents($fixture . '/php/init.php', '<?php global $osiris, $Settings;');
file_put_contents($fixture . '/php/MailSender.php', '<?php // Mail is captured by this test.');
$id = str_repeat('a', 24);
file_put_contents($fixture . '/uploads/' . $id . '.txt', 'fixture file');
file_put_contents($fixture . '/uploads/guest/file.txt', 'fixture file');
file_put_contents($fixture . '/outside.txt', 'outside uploads');
$_SESSION = ['username' => 'member']; $_POST = ['mail' => 'person@example.test'];
$mailCount = 0; $errorCode = null;
function lang($en, $de = null) { return $en; }
function sendMail(...$arguments) { $GLOBALS['mailCount']++; }
function abortwith($code, ...$arguments) { throw new RuntimeException('Access denied', $code); }
class DB {
    public static function is_ObjectID($id) { return is_string($id) && preg_match('/^[a-f0-9]{24}$/', $id); }
    public static function to_ObjectID($id) { return $id; }
    public static function doc2Arr($value) { return $value ?? []; }
}
class Route {
    public static array $routes = [];
    public static function get($path, $callback, $visibility = 'public') { self::$routes[] = ['get', $path, $callback, $visibility]; }
    public static function post($path, $callback, $visibility = 'public') { self::$routes[] = ['post', $path, $callback, $visibility]; }
}
class RouteFixtureCollection {
    public int $writes = 0;
    public function __construct(public ?array $document = null) {}
    public function findOne($filter) {
        if ($this->document === null) return null;
        foreach ($filter as $key => $value) if (($this->document[$key] ?? null) !== $value) return null;
        return $this->document;
    }
    public function count($filter) { return $this->findOne($filter) ? 1 : 0; }
    public function updateOne($filter, $update, $options = []) { $this->document = array_merge($this->document ?? $filter, $update['$set']); $this->writes++; }
}
$rights = match ($scenario) {
    'upload-central-reader' => ['documents.central'], 'upload-central-manager' => ['documents.manage'], default => [],
};
$Settings = new class($rights) {
    public function __construct(private array $rights) {}
    public function hasPermission($right) { return in_array($right, $this->rights, true); }
};
$type = str_starts_with($scenario, 'upload-proposal') ? 'proposals' : ($scenario === 'upload-activity' ? 'activities' : 'central');
$osiris = (object) [
    'persons' => new RouteFixtureCollection($scenario === 'reset-unknown' ? null : ['mail' => 'person@example.test', 'username' => 'member']),
    'accounts' => new RouteFixtureCollection($scenario === 'reset-no-password' ? null : ['username' => 'member', 'hash' => 'existing-reset-link', 'reset' => $scenario === 'reset-waiting' ? time() : time() - 1000]),
    'uploads' => new RouteFixtureCollection(['_id' => $id, 'type' => $type, 'id' => $id, 'filename' => 'Original file.txt']),
    'activities' => new RouteFixtureCollection(['_id' => $id]),
    'proposals' => new RouteFixtureCollection(['_id' => $id, 'persons' => [['user' => $scenario === 'upload-proposal-member' ? 'member' : 'someone-else']]]),
    'guests' => new RouteFixtureCollection(['id' => 'guest', 'files.filename' => 'file.txt']),
];
ob_start();
register_shutdown_function(static function () use ($fixture, $id, &$errorCode, $osiris) {
    $body = ob_get_clean();
    $result = ['session' => $_SESSION, 'mail_count' => $GLOBALS['mailCount'], 'writes' => $osiris->accounts->writes,
        'hash' => $osiris->accounts->document['hash'] ?? null, 'body' => $body, 'error' => $errorCode];
    foreach (['php/init.php', 'php/MailSender.php', 'uploads/' . $id . '.txt', 'uploads/guest/file.txt', 'outside.txt'] as $file) unlink($fixture . '/' . $file);
    rmdir($fixture . '/uploads/guest'); rmdir($fixture . '/uploads'); rmdir($fixture . '/php'); rmdir($fixture);
    echo json_encode($result, JSON_THROW_ON_ERROR);
});
try {
    if (str_starts_with($scenario, 'reset-')) {
        require $repository . '/addons/auth/index.php';
        $routes = array_values(array_filter(Route::$routes, fn($route) => $route[0] === 'post' && $route[1] === '/auth/forgot-password'));
        $routes[0][2]();
    } else {
        require $repository . '/routes/database.php';
        $routes = array_values(array_filter(Route::$routes, fn($route) => $route[0] === 'get' && $route[1] === '/uploads/(.*)'));
        if (count($routes) !== 1 || $routes[0][3] !== 'login') throw new LogicException('Exactly one authenticated upload route must be registered.');
        $path = match ($scenario) { 'upload-outside' => '../outside.txt', 'upload-missing' => 'missing.txt', 'upload-guest-denied' => 'guest/file.txt', default => $id . '.txt' };
        $routes[0][2]($path);
    }
} catch (RuntimeException $error) { $errorCode = $error->getCode(); }
