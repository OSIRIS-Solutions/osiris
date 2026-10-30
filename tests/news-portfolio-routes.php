<?php

// Run with: php tests/news-portfolio-routes.php (no database or web server required).
require __DIR__ . '/fixtures/news.php';
$testRoot = sys_get_temp_dir() . '/osiris-portfolio-news-' . bin2hex(random_bytes(6));
mkdir($testRoot . '/php', 0700, true);
symlink(BASEPATH . '/vendor', $testRoot . '/vendor');
symlink(BASEPATH . '/php/Portfolio.php', $testRoot . '/php/Portfolio.php');
file_put_contents($testRoot . '/php/init.php', '<?php $Settings = $GLOBALS["Settings"]; $DB = $GLOBALS["DB"]; $osiris = $GLOBALS["osiris"];');
$api = static function (string $route, array $overrides = []) use ($testRoot, $portfolioConfiguration, $portfolioNews) {
    $input = array_replace(['root' => $testRoot, 'repository' => BASEPATH, 'route' => $route, 'args' => [$portfolioNews['id']],
        'configuration' => $portfolioConfiguration, 'collections' => [
            'news' => [$portfolioNews],
            'persons' => [['_id' => str_repeat('b', 24), 'displayname' => 'Public member', 'hide' => false]],
            'activities' => [['_id' => str_repeat('c', 24), 'hide' => false, 'rendered' => ['title' => 'Public activity', 'portfolio' => '<p>Activity</p>']]],
        ]], $overrides);
    $process = proc_open([PHP_BINARY, __DIR__ . '/fixtures/news-portfolio-route.php'], [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
    fwrite($pipes[0], json_encode($input, JSON_THROW_ON_ERROR)); fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]); $errors = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    check(proc_close($process) === 0 && $errors === '', 'Portfolio route fixture failed: ' . $errors);
    return json_decode($output, true, 512, JSON_THROW_ON_ERROR);
};
try {
    $list = $api('/portfolio/news');
    check($list['status'] === 200 && $list['count'] === 1 && $list['data'][0]['id'] === $portfolioNews['id'], 'List outlet-only news through the existing endpoint and retain its original news ID.');
    check(count($list['data'][0]['outlets']) === 1 && !isset($list['data'][0]['content'], $list['data'][0]['title']), 'List summaries contain only enabled released outlets and no disabled original texts.');
    $detail = $api('/portfolio/news/([^/]*)');
    check($detail['status'] === 200 && $detail['data']['outlets'][0]['content']['en'] === '<p><strong>Public text</strong></p>', 'Fetch the full localized released contribution through the existing detail endpoint.');
    check($detail['data']['persons'][0]['displayname'] === 'Public member' && $detail['data']['activities'][0]['title'] === 'Public activity', 'Retain the existing resolution of shared person and activity connections.');
    check(!str_contains(json_encode($detail), 'Private') && !isset($detail['data']['outlet_requests'], $detail['data']['editorial_contributions']), 'Detail projection and serializer never expose drafts or editorial feedback.');
    $confirmedNews = $portfolioNews;
    $confirmedNews['original_revision'] = 6;
    $confirmedNews['outlet_requests'][0]['status'] = 'approved';
    $confirmedNews['outlet_requests'][0]['author_review_id'] = 'existing-author-review';
    unset($confirmedNews['outlet_requests'][0]['released_revision']);
    $confirmedNews['author_reviews'] = [['id' => 'existing-author-review', 'status' => 'approved', 'original_revision' => 5,
        'contributions' => [['outlet_id' => $confirmedNews['outlet_requests'][0]['outlet_id'], 'revision' => 2]]]];
    $confirmed = $api('/portfolio/news/([^/]*)', ['collections' => ['news' => [$confirmedNews]]]);
    check($confirmed['status'] === 200 && count($confirmed['data']['outlets']) === 1 && !isset($confirmed['data']['content']), 'Existing author confirmations are final even with a later original revision and disabled original output.');
    check($api('/portfolio/news', ['collections' => ['news' => [$confirmedNews]]])['count'] === 1, 'The list filter also admits author-approved outlets.');
    foreach (['internal', 'draft', 'revision', 'future'] as $case) {
        $blockedNews = $portfolioNews;
        if ($case === 'internal') $blockedNews['internal_only'] = true;
        if ($case === 'draft') $blockedNews['outlet_requests'][0]['status'] = 'pending_approval';
        if ($case === 'revision') $blockedNews['outlet_requests'][0]['status'] = 'in_progress';
        if ($case === 'future') $blockedNews['date'] = '2999-01-01';
        $blocked = $api('/portfolio/news', ['collections' => ['news' => [$blockedNews]]]);
        check($blocked['count'] === 0 && $blocked['data'] === [], 'Do not list ' . $case . ' news.');
        if ($case !== 'future') {
            $blocked = $api('/portfolio/news/([^/]*)', ['collections' => ['news' => [$blockedNews]]]);
            check($blocked['status'] === 404, 'Protect direct detail requests for ' . $case . ' news.');
        }
    }
    $configuration = $portfolioConfiguration;
    $configuration['outlets'][0]['portfolio'] = false;
    check($api('/portfolio/news/([^/]*)', ['configuration' => $configuration])['status'] === 404, 'Revoking Portfolio output immediately hides direct details.');
    $configuration['original_portfolio'] = true;
    $original = $api('/portfolio/news/([^/]*)', ['configuration' => $configuration]);
    check($original['data']['title'] === $portfolioNews['title'] && $original['data']['outlets'] === [], 'Retain original output when outlet output is disabled independently.');
    $denied = $api('/portfolio/news', ['apikey' => 'secret-key', 'get' => ['apikey' => 'wrong-key']]);
    check($denied['status'] === 403, 'Retain Portfolio API key checks.');
    $disabled = $api('/portfolio/news', ['enabled' => false]);
    check($disabled['data'] === [], 'Retain the news feature switch.');
    echo "News Portfolio route checks passed.\n";
} finally {
    unlink($testRoot . '/php/init.php');
    unlink($testRoot . '/php/Portfolio.php');
    unlink($testRoot . '/vendor');
    rmdir($testRoot . '/php'); rmdir($testRoot);
}
