<?php

// Shared fixtures only: loading this file never executes another test suite.
define('BASEPATH', dirname(__DIR__, 2));
define('DB_STRING', 'mongodb://localhost:27017');
define('ROOTPATH', '');
define('OSIRIS_BUILD', 'test');
require_once BASEPATH . '/php/Settings.php';
require_once BASEPATH . '/php/NewsOutlets.php';
require_once BASEPATH . '/php/NewsEditorial.php';
require_once BASEPATH . '/php/NewsReview.php';
require_once BASEPATH . '/php/Portfolio.php';

function lang($en, $de = null) { return ($GLOBALS['testLanguage'] ?? 'en') === 'de' ? ($de ?? $en) : $en; }

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

function rejects(callable $action, string $message): void
{
    try {
        $action();
    } catch (InvalidArgumentException $exception) {
        return;
    }
    throw new RuntimeException($message);
}

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }

function publicationMatches(array $document, array $query): bool
{
    foreach ($query as $field => $value) {
        if ($field === '$and' || $field === '$or') {
            $matches = array_map(fn($condition) => publicationMatches($document, $condition), $value);
            if ($field === '$and' ? in_array(false, $matches, true) : !in_array(true, $matches, true)) return false;
        } elseif (is_array($value)) {
            foreach ($value as $operator => $expected) {
                $actual = $document[$field] ?? null;
                if ($operator === '$exists' && array_key_exists($field, $document) !== $expected) return false;
                if ($operator === '$ne' && $actual === $expected) return false;
                if ($operator === '$lte' && ($actual === null || $actual > $expected)) return false;
                if ($operator === '$in' && !in_array($actual, $expected, true)) return false;
                if ($operator === '$elemMatch' && (!is_array($actual) || !array_filter($actual, fn($item) => publicationMatches($item, $expected)))) return false;
            }
        } elseif (is_array($document[$field] ?? null) ? !in_array($value, $document[$field], true) : ($document[$field] ?? null) !== $value) {
            return false;
        }
    }
    return true;
}

function requestRoute(array $input): array
{
    global $testRoot, $initial;
    $input += ['root' => $testRoot, 'configuration' => $initial, 'permissions' => ['news.add', 'news.edit-own']];
    // Match a real form's independent scope; a test can pass an older form snapshot explicitly.
    $formNews = $input['form_news'] ?? $input['news'] ?? null;
    if ($formNews && ($input['method'] ?? 'post') === 'post') {
        $previousToken = $_SESSION['news_csrf'] ?? null;
        $_SESSION['news_csrf'] = 'token';
        if (str_starts_with($input['route'], '/crud/news/update/')) {
            $input['post']['news']['edit_token'] ??= NewsEditorial::editToken($formNews, 'original');
        } elseif (str_starts_with($input['route'], '/crud/news/editorial/')) {
            $action = $input['post']['action'] ?? '';
            $scope = in_array($action, ['start', 'reopen', 'save', 'ready', 'decline', 'revise'], true)
                ? 'outlet:' . ($input['post']['editorial']['outlet_id'] ?? '') : ($action === 'connections' ? 'connections' : 'review');
            $input['post']['edit_token'] ??= NewsEditorial::editToken($formNews, $scope);
        }
        if ($previousToken === null) unset($_SESSION['news_csrf']); else $_SESSION['news_csrf'] = $previousToken;
    }
    $process = proc_open([PHP_BINARY, __DIR__ . '/news-request-route.php'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
    fwrite($pipes[0], json_encode($input, JSON_THROW_ON_ERROR));
    fclose($pipes[0]);
    $result = stream_get_contents($pipes[1]);
    $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    $exit = proc_close($process);
    check($exit === 0 && $errors === '', 'Route fixture failed: ' . $errors);
    return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
}

class NewsOutletTestSettings extends Settings
{
    public $configuration;
    public function __construct($configuration) { $this->configuration = $configuration; }
    public function get($key, $default = null) { return $this->configuration ?? $default; }
    public function featureEnabled($feature, $default = false) { return $feature === 'news-editorial' ? ($this->configuration['enabled'] ?? false) : $default; }
}

class NewsRequestTestSettings extends NewsOutletTestSettings
{
    private array $permissions;
    public function __construct(array $permissions, array $configuration)
    {
        parent::__construct($configuration);
        $this->permissions = $permissions;
    }
    public function hasPermission(string $right) { return in_array($right, $this->permissions, true); }
}

class NewsRequestTestDB extends DB
{
    public array $messages = [];
    public function __construct() {}
    public function addMessages($group, $en, $de = null, $type = 'general', $link = null)
    {
        $this->messages[] = compact('group', 'en', 'de', 'type', 'link');
        return true;
    }
}

class NewsReviewTestDB extends NewsRequestTestDB
{
    public function getNameFromId($user, $reverse = false, $abbr = false) { return $user; }
}

/** A disposable application root for exercising real routes without a live database. */
function news_test_route_root(): string
{
    $root = sys_get_temp_dir() . '/osiris-news-test-' . bin2hex(random_bytes(6));
    mkdir($root . '/php', 0700, true);
    foreach (['NewsEditorial', 'NewsReview', 'NewsExport'] as $class) symlink(BASEPATH . '/php/' . $class . '.php', $root . '/php/' . $class . '.php');
    foreach (['routes', 'pages', 'vendor'] as $directory) symlink(BASEPATH . '/' . $directory, $root . '/' . $directory);
    file_put_contents($root . '/php/init.php', '<?php $Settings = $GLOBALS["Settings"]; $DB = $GLOBALS["DB"]; $osiris = $GLOBALS["osiris"];');
    file_put_contents($root . '/header.php', '');
    file_put_contents($root . '/footer.php', '');
    return $root;
}

function news_test_remove_root(string $root): void
{
    foreach (glob($root . '/php/*') as $file) unlink($file);
    rmdir($root . '/php');
    foreach (glob($root . '/*') as $file) unlink($file);
    rmdir($root);
}

$testLanguage = 'en';
$defaults = NewsOutlets::read(null);
$initial = NewsOutlets::normalize([
    'original_portfolio' => '1',
    'outlets' => [
        ['name' => ['en' => ' Website ', 'de' => ' Webseite '], 'description' => ['en' => 'For the institute website', 'de' => 'Für die Institutswebseite'], 'active' => '1', 'portfolio' => '1'],
        ['name' => ['en' => 'Newsletter', 'de' => 'Rundbrief'], 'active' => '1', 'portfolio' => '0'],
    ],
], $defaults);
$initial['enabled'] = true; // Runtime flag supplied by Settings::getNewsOutlets().
[$website, $newsletter] = $initial['outlets'];
$configuration = $initial;
$now = '2026-10-22T16:00:00+02:00';
$_SESSION = ['username' => 'author', 'news_csrf' => 'token'];
$original = ['_id' => str_repeat('a', 24), 'title' => 'Original', 'title_de' => 'Original DE', 'content' => 'Keep this original',
    'created_by' => 'author', 'date' => '2026-10-22', 'publication_mode' => 'outlets', 'internal_only' => false, 'revision' => 1, 'original_revision' => 1,
    'outlet_requests' => [['outlet_id' => $website['id'], 'source' => 'author', 'status' => 'requested', 'requested_by' => 'author', 'requested_at' => $now]]];
$created = ['news' => $original];
$created['news']['outlet_requests'][] = ['outlet_id' => $newsletter['id'], 'source' => 'author', 'status' => 'requested'];
$reviewConfiguration = $initial;
$reviewConfiguration['outlets'][0]['fields'] = ['title', 'teaser', 'content'];
$preparedNews = $created['news'];
foreach ([$website['id'], $newsletter['id']] as $outletId) {
    $preparedNews = array_merge($preparedNews, NewsEditorial::apply('start', ['outlet_id' => $outletId], $preparedNews, $reviewConfiguration, 'editor', $now));
    $preparedNews = array_merge($preparedNews, NewsEditorial::apply('ready', ['outlet_id' => $outletId,
        'title' => ['de' => 'Redaktioneller Titel'], 'teaser' => ['de' => '<script>Review teaser</script>'], 'content' => ['de' => 'Prepared text']], $preparedNews, $reviewConfiguration, 'editor', $now));
}
$mutate = static fn($action, $news, $input = [], $actor = 'editor', $editor = true, $config = null) =>
    array_merge($news, NewsReview::apply($action, $input, $news, $config ?? $reviewConfiguration, $actor, $editor, $now));
$pendingNews = $mutate('request_review', $preparedNews);
$approvedNews = $mutate('approve_review', $pendingNews, ['review_id' => NewsReview::pending($pendingNews)['id']], 'author', false);

$portfolioConfiguration = $initial;
$portfolioConfiguration['original_portfolio'] = false;
$portfolioConfiguration['outlets'][0]['fields'] = ['title', 'teaser', 'content'];
$portfolioNews = ['_id' => str_repeat('a', 24), 'id' => str_repeat('a', 24), 'date' => '2026-01-01',
    'publication_mode' => 'outlets', 'internal_only' => false, 'original_revision' => 1,
    'title' => 'Private original title', 'title_de' => 'Privater Originaltitel', 'teaser' => 'Private original teaser', 'content' => 'Private original content',
    'persons' => [str_repeat('b', 24)], 'activities' => [str_repeat('c', 24)], 'topics' => ['topic-1'], 'featured' => null,
    'author_reviews' => [['comment' => 'Private author feedback']], 'editorial_history' => [['actor' => 'editor']],
    'outlet_requests' => [
        ['outlet_id' => $website['id'], 'status' => 'released', 'released_revision' => 2, 'release_mode' => 'without_review'],
        ['outlet_id' => $newsletter['id'], 'status' => 'released', 'released_revision' => 1, 'decline_reason' => 'Private editorial feedback'],
    ],
    'editorial_contributions' => [
        ['outlet_id' => $website['id'], 'revision' => 2, 'format' => 'html', 'title' => ['en' => 'Public website title', 'de' => 'Öffentlicher Titel'],
            'teaser' => ['en' => 'Public teaser', 'de' => 'Öffentlicher Teaser'], 'content' => ['en' => '<p><strong>Public text</strong><script>alert(1)</script></p>', 'de' => '<p>Öffentlicher Beitrag</p>']],
        ['outlet_id' => $newsletter['id'], 'revision' => 1, 'format' => 'plain', 'title' => ['en' => 'Private newsletter draft'], 'content' => ['en' => 'Never send this newsletter text']],
    ]];
