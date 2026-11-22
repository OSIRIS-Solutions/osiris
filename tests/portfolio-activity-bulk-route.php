<?php

// Run with: php tests/portfolio-activity-bulk-route.php (no database required).
$fixture = sys_get_temp_dir() . '/osiris-activity-bulk-' . bin2hex(random_bytes(6));
mkdir($fixture . '/php', 0700, true);
file_put_contents($fixture . '/php/Portfolio.php', '<?php class Portfolio {}');
file_put_contents($fixture . '/php/Document.php', '<?php // Document is supplied by the test.');

define('BASEPATH', $fixture);

class Route
{
    public static array $routes = [];

    public static function get($path, $callback, $visibility = 'public'): void
    {
        self::$routes[$path] = $callback;
    }
}

class ActivityFilterSettings
{
    public function __construct(private bool $workflow, private string $visibility = 'all') {}

    public function featureEnabled($feature): bool
    {
        return $feature === 'quality-workflow' && $this->workflow;
    }

    public function get($key, $default = null)
    {
        return $key === 'portfolio-workflow-visibility' ? $this->visibility : $default;
    }
}

function check($condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

// Lightweight rendering/services let the production detail function run without a database.
class Document
{
    private array $document;
    private string $language = 'en';
    public array $subtypeArr = ['modules' => []];
    public string $usecase;
    public function setDocument($document) { $this->document = $document; }
    public function setLanguage($language) { $this->language = $language; }
    public function getTitle() { return $this->document['title'][$this->language]; }
    public function format($template = null) { return $this->getTitle(); }
    public function bibtex() { return ''; }
    public function ris() { return ''; }
    public function activity_type() { return $this->language === 'de' ? 'Publikation' : 'Publication'; }
    public function activity_subtype() { return $this->language === 'de' ? 'Artikel' : 'Article'; }
}
class Modules { public function __construct($document) {} public function getPortfolioFields() { return []; } }
class DB { public static function doc2Arr($value) { return $value instanceof Traversable ? iterator_to_array($value) : $value; } }
function apiLocalized($value) { return is_array($value) ? ($value[$_GET['lang'] ?? 'en'] ?? $value['en'] ?? '') : $value; }
class BulkDetailCollection
{
    public array $lastFilter = [];
    public function __construct(private array $documents = []) {}
    public function find($filter, $options = []) {
        $this->lastFilter = $filter;
        return new class($this->documents) extends ArrayIterator {
            public function toArray() { return $this->getArrayCopy(); }
        };
    }
}

try {
    require dirname(__DIR__) . '/routes/api/portfolio.php';

    check(isset(Route::$routes['/portfolio/activities/details']), 'Bulk activity route is not registered.');
    check(portfolio_activity_batch_limit(null) === 100, 'Default batch limit must be 100.');
    check(portfolio_activity_batch_limit(25) === 25, 'Custom batch limit was ignored.');
    check(portfolio_activity_batch_limit(1000) === 500, 'Batch limit must be capped at 500.');
    check(portfolio_activity_batch_limit(0) === 100, 'Invalid batch limit must use the default.');

    $filter = portfolio_activity_filter(new ActivityFilterSettings(false), ['article']);
    check($filter['hide'] === ['$ne' => true], 'Hidden activities are not excluded.');
    check($filter['subtype'] === ['$in' => ['article']], 'Portfolio subtypes are not applied.');

    $approved = portfolio_activity_filter(
        new ActivityFilterSettings(true, 'only-approved'),
        ['article']
    );
    check(($approved['workflow.status'] ?? null) === 'verified', 'Approval filter is missing.');

    $approvedOrEmpty = portfolio_activity_filter(
        new ActivityFilterSettings(true, 'approved-or-empty'),
        ['article']
    );
    check(count($approvedOrEmpty['$or'] ?? []) === 3, 'Approved-or-empty filter is incomplete.');

    $Settings = new ActivityFilterSettings(false);
    $publicProject = ['id' => '001', 'name' => ['en' => 'Water', 'de' => 'Wasser'], 'title' => ['en' => 'Water research', 'de' => 'Wasserforschung']];
    $osiris = (object) [
        'projects' => new BulkDetailCollection([$publicProject]),
        'activitiesConnections' => new BulkDetailCollection(),
    ];
    $document = ['_id' => 'activity-id', 'type' => 'publication', 'subtype' => 'article',
        'title' => ['en' => 'Research', 'de' => 'Forschung'], 'authors' => [], 'projects' => ['project-id']];
    foreach (['en', 'de'] as $language) {
        $_GET = ['lang' => $language];
        $detail = portfolio_activity_detail($document, ['article']);
        check($detail['title'] === $document['title'][$language], 'The shared detail renderer must receive the requested language.');
        check($detail['typeStr'] === ($language === 'de' ? 'Publikation' : 'Publication'), 'Activity type labels must follow the requested language.');
        check($detail['projects'][0]['name'] === $publicProject['name'][$language]
            && $detail['projects'][0]['title'] === $publicProject['title'][$language], 'Embedded projects must keep localized name and title support.');
        check($detail['projects'][0]['id'] === '001', 'Rendering must preserve numeric-looking project identifiers.');
        check($osiris->projects->lastFilter === ['_id' => ['$in' => ['project-id']], 'public' => true], 'The batched project lookup must retain public visibility filtering.');
    }
    $_GET = [];

    echo "Portfolio activity bulk route checks passed.\n";
} finally {
    unlink($fixture . '/php/Portfolio.php');
    unlink($fixture . '/php/Document.php');
    rmdir($fixture . '/php');
    rmdir($fixture);
}
