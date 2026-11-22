<?php

// Isolated process for exercising the real news routes without a live database.
$fixture = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
define('BASEPATH', $fixture['root']);
define('ROOTPATH', '');
define('OSIRIS_BUILD', 'test');
$_SESSION = ['username' => $fixture['actor'] ?? 'author', 'news_csrf' => 'token'];
$_POST = $fixture['post'] ?? [];
$_GET = $fixture['get'] ?? [];
if (!empty($fixture['capture_output'])) {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';
    ob_start();
}
function lang($en, $de = null) { return $en; }
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function abortwith($code, $message = '', ...$unused) { throw new RuntimeException($message, $code); }

class Settings
{
    public function hasPermission(string $right) { return in_array($right, $GLOBALS['fixture']['permissions'], true); }
    public function featureEnabled($feature, $default = false) { return $feature === 'news' || ($feature === 'news-editorial' && ($GLOBALS['fixture']['configuration']['enabled'] ?? false)); }
    public function getNewsOutlets() { return $GLOBALS['fixture']['configuration']; }
}
class DB
{
    public $db;
    public array $messages = [];
    public static function doc2Arr($value) { return $value ?? []; }
    public static function to_ObjectID($value) { return $value; }
    public function getNameFromId($id) { return $id; }
    public function addMessages($group, $en, $de = null, $type = 'general', $link = null)
    {
        $this->messages[] = compact('group', 'en', 'de', 'type', 'link');
    }
}
class NewsFixtureResult
{
    public function __construct(private int $matched = 1) {}
    public function getMatchedCount() { return $this->matched; }
    public function getInsertedCount() { return 1; }
    public function getInsertedId() { return str_repeat('a', 24); }
}
class NewsFixtureCollection
{
    public $document;
    public bool $writeAttempted = false;
    public int $writeAttempts = 0;
    public function __construct($document) { $this->document = $document; }
    public function find($filter = [], $options = []) { return $this->document ? [$this->document] : []; }
    public function findOne($filter)
    {
        return ($this->document['_id'] ?? null) === ($filter['_id'] ?? null) ? $this->document : null;
    }
    public function insertOne($document)
    {
        $this->document = array_merge($document, ['_id' => str_repeat('a', 24)]);
        $this->writeAttempted = true;
        return new NewsFixtureResult();
    }
    public function updateOne($filter, $update)
    {
        $this->writeAttempted = true;
        $this->writeAttempts++;
        if (!empty($GLOBALS['fixture']['race_once']) && $this->writeAttempts === 1) {
            $this->document = array_merge($this->document, $GLOBALS['fixture']['race_once']);
            $this->document['revision'] = (int) ($this->document['revision'] ?? 0) + 1;
            return new NewsFixtureResult(0);
        }
        if (!empty($GLOBALS['fixture']['race'])) return new NewsFixtureResult(0);
        if (($filter['_id'] ?? null) !== ($this->document['_id'] ?? null)) return new NewsFixtureResult(0);
        if (array_key_exists('revision', $filter)) {
            $expected = $filter['revision'];
            if (is_array($expected)) {
                if (array_key_exists('revision', $this->document)) return new NewsFixtureResult(0);
            } elseif (($this->document['revision'] ?? null) !== $expected) {
                return new NewsFixtureResult(0);
            }
        }
        $this->document = array_merge($this->document, $update['$set'] ?? []);
        foreach ($update['$unset'] ?? [] as $key => $_) unset($this->document[$key]);
        foreach ($update['$inc'] ?? [] as $key => $increment) $this->document[$key] = ($this->document[$key] ?? 0) + $increment;
        return new NewsFixtureResult();
    }
}

$Settings = new Settings();
$DB = new DB();
$osiris = (object) ['news' => new NewsFixtureCollection($fixture['news'] ?? null)];
$DB->db = $osiris;
$error = null;
require dirname(__DIR__, 2) . '/php/Route.php';
require dirname(__DIR__, 2) . '/routes/news.php';
register_shutdown_function(function () use ($osiris, $DB, &$error) {
    $output = !empty($GLOBALS['fixture']['capture_output']) ? base64_encode(ob_get_clean()) : null;
    echo json_encode(['output' => $output, 'news' => $osiris->news->document, 'messages' => $DB->messages, 'error' => $error, 'write_attempted' => $osiris->news->writeAttempted, 'write_attempts' => $osiris->news->writeAttempts], JSON_THROW_ON_ERROR);
});
try {
    foreach (Route::getAll() as $route) {
        if ($route['expression'] === $fixture['route'] && $route['method'] === ($fixture['method'] ?? 'post')) {
            $route['function'](...($fixture['args'] ?? []));
            break;
        }
    }
} catch (RuntimeException $exception) {
    $error = ['code' => $exception->getCode(), 'message' => $exception->getMessage()];
}
