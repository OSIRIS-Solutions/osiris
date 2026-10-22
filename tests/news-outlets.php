<?php

// Run with: php tests/news-outlets.php (no database required).
define('BASEPATH', dirname(__DIR__));
define('DB_STRING', 'mongodb://localhost:27017');
require_once BASEPATH . '/php/NewsOutlets.php';
require_once BASEPATH . '/php/Settings.php';

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

$defaults = NewsOutlets::read(null);
check($defaults === ['original_portfolio' => true, 'outlets' => []], 'Preserve existing Portfolio behavior.');
$initial = NewsOutlets::normalize([
    'original_portfolio' => '1',
    'outlets' => [
        ['name' => ['en' => ' Website ', 'de' => ' Webseite '], 'description' => ['en' => 'For the institute website', 'de' => 'Für die Institutswebseite'], 'active' => '1', 'portfolio' => '1'],
        ['name' => ['en' => 'Newsletter', 'de' => 'Rundbrief'], 'active' => '1', 'portfolio' => '0'],
    ],
], $defaults);
[$website, $newsletter] = $initial['outlets'];
check($website['name'] === ['en' => 'Website', 'de' => 'Webseite'], 'Trim names.');
check((bool) preg_match('/^[a-f0-9]{24}$/', $website['id']), 'Generate stable identifiers on the server.');
check($website['id'] !== $newsletter['id'], 'Use different identifiers.');
check($website['description'] === ['en' => 'For the institute website', 'de' => 'Für die Institutswebseite'], 'Store localized descriptions.');
check($newsletter['description'] === ['en' => '', 'de' => ''], 'Include both language keys for optional fields.');
check(!isset($website['name_de']) && !isset($website['description_de']), 'Use language keys instead of separate translation fields.');
check($website['portfolio'] && !$newsletter['portfolio'], 'Portfolio output is independent per outlet.');

$updated = NewsOutlets::normalize([
    'original_portfolio' => '0',
    'outlets' => [
        array_merge($newsletter, ['name' => ['en' => 'Monthly newsletter', 'de' => 'Monatlicher Rundbrief']]),
        array_merge($website, ['active' => '0']),
    ],
], $initial);
check($updated['outlets'][0]['id'] === $newsletter['id'], 'Renaming and sorting retain IDs.');
check($updated['outlets'][0]['order'] === 0 && $updated['outlets'][1]['order'] === 1, 'Store the selected order.');
check(!$updated['original_portfolio'], 'Allow only editorial outlets as Portfolio sources.');
check(!$updated['outlets'][1]['active'] && $updated['outlets'][1]['portfolio'], 'Deactivation for new requests retains output configuration.');
check(NewsOutlets::revision($updated) !== NewsOutlets::revision($initial), 'Detect stale forms.');

rejects(fn() => NewsOutlets::normalize(['outlets' => [$website]], $initial), 'Reject deletion of saved outlets.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [$website, $website, $newsletter]], $initial), 'Reject duplicate IDs.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['id' => str_repeat('a', 24), 'name' => ['en' => 'Forged']]]], $defaults), 'Reject unknown IDs.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => '  ']]]], $defaults), 'Require names.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => ['Invalid']]]]], $defaults), 'Reject non-text values.');
rejects(fn() => NewsOutlets::normalize(['outlets' => 'Invalid'], $defaults), 'Reject malformed lists.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test'], 'portfolio' => 'false']]], $defaults), 'Reject ambiguous boolean strings.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => str_repeat('a', 121)]]]], $defaults), 'Enforce name limits.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test'], 'description' => ['de' => str_repeat('a', 501)]]]], $defaults), 'Enforce description limits.');

rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => 'Legacy scalar']]], $defaults), 'Require localized name fields.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test'], 'description' => 'Legacy scalar']]], $defaults), 'Require localized description fields.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test', 'de' => str_repeat('a', 121)]]]], $defaults), 'Validate each name translation.');

$mongoValue = new MongoDB\Model\BSONDocument([
    'original_portfolio' => false,
    'outlets' => new MongoDB\Model\BSONArray([
        new MongoDB\Model\BSONDocument(array_merge($website, [
            'name' => new MongoDB\Model\BSONDocument($website['name']),
            'description' => new MongoDB\Model\BSONDocument($website['description']),
        ])),
        new MongoDB\Model\BSONDocument($newsletter),
    ]),
]);
check(NewsOutlets::read($mongoValue) === ['original_portfolio' => false, 'outlets' => [$website, $newsletter]], 'Read nested MongoDB documents.');

class NewsOutletTestSettings extends Settings
{
    public $configuration;
    public function __construct($configuration) { $this->configuration = $configuration; }
    public function get($key, $default = null) { return $this->configuration ?? $default; }
}
$settings = new NewsOutletTestSettings(null);
$filter = ['visibility' => 'public', 'date' => ['$lte' => '2026-10-22']];
check($settings->getNewsPortfolioFilter($filter) === $filter, 'Default settings preserve public and date filters.');
$settings->configuration = $updated;
$blocked = $settings->getNewsPortfolioFilter($filter);
check($blocked['$and'][0] === $filter && $blocked['$and'][1] === ['_id' => ['$exists' => false]], 'Disable original output without weakening other filters.');
$detail = ['_id' => 'existing-id', 'visibility' => 'public'];
check($settings->getNewsPortfolioFilter($detail)['$and'][0] === $detail, 'Keep direct detail filters when blocking output.');
$settings->configuration = $initial;
check($settings->getNewsPortfolioFilter($detail) === $detail, 'Selecting sources never removes individual visibility restrictions.');

// Verify the form reads and submits the same localized structure as persistence.
define('ROOTPATH', '');
define('OSIRIS_BUILD', 'test');
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$configuration = NewsOutlets::read($mongoValue);
$revision = NewsOutlets::revision($configuration);
$_SESSION['news_outlets_csrf'] = 'test';
foreach (['en' => 'Website', 'de' => 'Webseite'] as $testLanguage => $expectedTitle) {
    ob_start();
    include BASEPATH . '/pages/admin/news.php';
    $html = ob_get_clean();
    check(str_contains($html, 'data-outlet-title>' . $expectedTitle . '</h3>'), 'Render the localized outlet title.');
    check(str_contains($html, 'name="configuration[outlets][0][name][en]" value="Website"'), 'Render the English name field.');
    check(str_contains($html, 'name="configuration[outlets][0][name][de]" value="Webseite"'), 'Render the German name field.');
    check(str_contains($html, 'name="configuration[outlets][0][description][de]" rows="2" maxlength="500">Für die Institutswebseite</textarea>'), 'Render the localized description.');
}

echo "News outlet checks passed.\n";
