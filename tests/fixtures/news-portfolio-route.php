<?php

// Exercise the actual Portfolio news endpoints with isolated collections.
$fixture = json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR);
define('BASEPATH', $fixture['root']);
define('DB_STRING', 'mongodb://localhost:27017');
define('ROOTPATH', '');
$_GET = $fixture['get'] ?? [];
function lang($en, $de = null) { return $en; }
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function return_permission_denied() { return json_encode(['status' => 403]); }
require $fixture['repository'] . '/php/Settings.php';

class PortfolioFixtureSettings extends Settings
{
    public function __construct() {}
    public function get($key, $default = null) {
        return match ($key) {
            'news-outlets' => $GLOBALS['fixture']['configuration'],
            'portfolio_apikey' => $GLOBALS['fixture']['apikey'] ?? null,
            default => $default,
        };
    }
    public function featureEnabled($feature, $default = false) { return $feature === 'news' && ($GLOBALS['fixture']['enabled'] ?? true); }
}
class PortfolioFixtureDB extends DB
{
    public function __construct() {}
    public static function to_ObjectID($value) { return $value; }
}
class PortfolioFixtureCursor extends ArrayIterator
{
    public function toArray() { return $this->getArrayCopy(); }
}
class PortfolioFixtureCollection
{
    public function __construct(private array $documents) {}
    private static function matches(array $document, array $query): bool
    {
        foreach ($query as $field => $condition) {
            if ($field === '$and' || $field === '$or') {
                $values = array_map(static fn($item) => self::matches($document, $item), $condition);
                if ($field === '$and' ? in_array(false, $values, true) : !in_array(true, $values, true)) return false;
                continue;
            }
            $actual = $document[$field] ?? null;
            if (!is_array($condition)) {
                if (is_array($actual) ? !in_array($condition, $actual, true) : $actual !== $condition) return false;
                continue;
            }
            foreach ($condition as $operator => $expected) {
                if ($operator === '$exists' && array_key_exists($field, $document) !== $expected) return false;
                if ($operator === '$ne' && $actual === $expected) return false;
                if ($operator === '$lte' && ($actual === null || $actual > $expected)) return false;
                if ($operator === '$in' && !in_array($actual, $expected, true)) return false;
                if ($operator === '$elemMatch' && (!is_array($actual) || !array_filter($actual, static fn($item) => self::matches($item, $expected)))) return false;
            }
        }
        return true;
    }
    private static function project(array $document, array $projection): array
    {
        if (!$projection) return $document;
        $result = [];
        foreach ($projection as $field => $value) {
            if ($value === 0) continue;
            if ($value === 1) {
                if (array_key_exists($field, $document)) $result[$field] = $document[$field];
            } else {
                $path = is_array($value) ? $value['$toString'] : $value;
                $item = $document;
                foreach (explode('.', ltrim($path, '$')) as $part) $item = $item[$part] ?? null;
                $result[$field] = $item;
            }
        }
        return $result;
    }
    public function find(array $filter, array $options = []): PortfolioFixtureCursor
    {
        $documents = array_values(array_filter($this->documents, static fn($document) => self::matches($document, $filter)));
        if (isset($options['sort']['date'])) usort($documents, static fn($a, $b) => strcmp($b['date'], $a['date']));
        return new PortfolioFixtureCursor(array_map(static fn($document) => self::project($document, $options['projection'] ?? []), $documents));
    }
    public function findOne(array $filter, array $options = []) { return $this->find($filter, $options)->getArrayCopy()[0] ?? null; }
}
$Settings = new PortfolioFixtureSettings();
$DB = new PortfolioFixtureDB();
$osiris = new stdClass();
foreach (['news', 'persons', 'activities', 'projects', 'infrastructures', 'conferences'] as $collection) {
    $osiris->$collection = new PortfolioFixtureCollection($fixture['collections'][$collection] ?? []);
}
$DB->db = $osiris;
require $fixture['repository'] . '/php/Route.php';
require $fixture['repository'] . '/routes/api/portfolio.php';
foreach (Route::getAll() as $route) {
    if ($route['expression'] === $fixture['route'] && $route['method'] === 'get') {
        $route['function'](...($fixture['args'] ?? []));
        break;
    }
}
