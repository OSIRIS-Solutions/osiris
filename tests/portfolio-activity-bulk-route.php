<?php

// Run with: php tests/portfolio-activity-bulk-route.php (no database required).
$fixture = sys_get_temp_dir() . '/osiris-activity-bulk-' . bin2hex(random_bytes(6));
mkdir($fixture . '/php', 0700, true);
file_put_contents($fixture . '/php/Portfolio.php', '<?php class Portfolio {}');

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

    echo "Portfolio activity bulk route checks passed.\n";
} finally {
    unlink($fixture . '/php/Portfolio.php');
    rmdir($fixture . '/php');
    rmdir($fixture);
}
