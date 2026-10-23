<?php

// Exercise both read-only interfaces and their downloads using the actual routes.
require __DIR__ . '/fixtures/news.php';
require_once BASEPATH . '/php/NewsExport.php';
$reviewConfiguration = $initial;
$reportNews = $created['news'];
$outletId = $website['id'];
foreach (['start', 'ready'] as $action) {
    $reportNews = array_merge($reportNews, NewsEditorial::apply($action, ['outlet_id' => $outletId, 'content' => ['de' => 'Prepared text']], $reportNews, $reviewConfiguration, 'editor', date('c')));
}
$reportNews = array_merge($reportNews, NewsReview::apply('release', [], $reportNews, $reviewConfiguration, 'editor', true, date('c')));

$testRoot = news_test_route_root();

try {
    $base = ['method' => 'get', 'capture_output' => true, 'news' => $reportNews, 'configuration' => $reviewConfiguration];
    foreach (['/news/editorial' => 'news.editorial', '/news/export' => 'news.export'] as $route => $permission) {
        foreach ([[], ['news.add'], ['news.outlets'], [$permission === 'news.export' ? 'news.editorial' : 'news.export']] as $permissions) {
            $denied = requestRoute($base + ['route' => $route, 'permissions' => $permissions]);
            check($denied['error']['code'] === 403 && !$denied['write_attempted'] && !$denied['messages'], 'Enforce the dedicated right on the actual GET route.');
        }
        $disabled = requestRoute(array_replace($base, ['route' => $route, 'permissions' => [$permission], 'configuration' => array_replace($reviewConfiguration, ['enabled' => false])]));
        check($disabled['error']['code'] === 403 && !$disabled['write_attempted'], 'Disabled feature blocks editorial pages and exports despite sufficient permissions.');
        $allowed = requestRoute($base + ['route' => $route, 'permissions' => [$permission]]);
        check($allowed['error'] === null && str_contains(base64_decode($allowed['output']), '<h1>') && !$allowed['write_attempted'] && !$allowed['messages'], 'Authorized page renders without writes or inbox messages.');
    }
    $base += ['route' => '/news/export', 'permissions' => ['news.export']];
    $download = requestRoute($base + ['get' => ['channel' => $website['id'], 'language' => 'de', 'download' => '1']]);
    check($download['error'] === null && str_contains(base64_decode($download['output']), 'Prepared text'), 'HTML download contains the released channel.');
    $wordDownload = requestRoute($base + ['get' => ['channel' => $website['id'], 'format' => 'docx', 'download' => '1']]);
    check($wordDownload['error'] === null && str_starts_with(base64_decode($wordDownload['output']), 'PK'), 'Actual route streams a Word document without page markup.');
    $invalid = requestRoute($base + ['get' => ['channel' => 'unknown', 'download' => '1']]);
    check($invalid['error']['code'] === 400, 'Invalid downloads fail instead of silently exporting originals.');
    $empty = requestRoute($base + ['get' => ['from' => '2099-01-01', 'download' => '1']]);
    check($empty['error'] === null && str_contains(base64_decode($empty['output']), 'Adjust the channel or date range.') && str_contains(base64_decode($empty['output']), 'value="2099-01-01"'), 'An empty download retains the filter interface and explains how to change the selection.');
    echo "News reporting route checks passed.\n";
} finally {
    news_test_remove_root($testRoot);
}
