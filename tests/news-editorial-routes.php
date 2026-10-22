<?php

// Run with: php tests/news-editorial-routes.php (no database required).
require __DIR__ . '/news-request-routes.php';
$testRoot = sys_get_temp_dir() . '/osiris-editorial-test-' . bin2hex(random_bytes(6));
mkdir($testRoot . '/php', 0700, true);
symlink(BASEPATH . '/php/NewsRequests.php', $testRoot . '/php/NewsRequests.php');
symlink(BASEPATH . '/php/NewsEditorial.php', $testRoot . '/php/NewsEditorial.php');
symlink(BASEPATH . '/routes', $testRoot . '/routes');
file_put_contents($testRoot . '/php/init.php', '<?php $Settings = $GLOBALS["Settings"]; $DB = $GLOBALS["DB"]; $osiris = $GLOBALS["osiris"];');

try {
    $source = $created['news'];
    $id = $source['_id'];
    $base = ['route' => '/crud/news/editorial/([a-f0-9]{24})', 'args' => [$id], 'news' => $source, 'actor' => 'editor', 'permissions' => ['news.editorial']];
    $startPost = ['csrf_token' => 'token', 'news_revision' => 1, 'action' => 'start', 'editorial' => ['outlet_id' => $website['id']]];
    $start = requestRoute($base + ['post' => $startPost]);
    check($start['error'] === null && $start['news']['revision'] === 2 && $start['news']['outlet_requests'][0]['status'] === 'in_progress', 'Start editing with the editorial permission alone.');
    check($start['news']['title'] === $source['title'] && $start['news']['content'] === $source['content'] && $start['news']['original_revision'] === 1, 'Editorial changes preserve the original text and its revision.');
    $draftPost = ['csrf_token' => 'token', 'news_revision' => 2, 'action' => 'save', 'editorial' => [
        'outlet_id' => $website['id'], 'title' => ['de' => 'Webseitenfassung'], 'content' => ['de' => 'Ausführlicher Beitrag für die Webseite'],
        'internal_only' => true, 'original_revision' => 99,
    ], 'news' => ['title' => 'Do not overwrite the original', 'internal_only' => '1']];
    $draft = requestRoute(array_replace($base, ['news' => $start['news'], 'post' => $draftPost]));
    check($draft['error'] === null && $draft['news']['editorial_contributions'][0]['content']['de'] === 'Ausführlicher Beitrag für die Webseite', 'Persist a separate localized contribution.');
    check($draft['news']['title'] === $source['title'] && !$draft['news']['internal_only'] && $draft['news']['original_revision'] === 1, 'Ignore forged original fields and metadata.');
    $configured = $initial;
    $configured['outlets'][0]['fields'] = ['title', 'teaser', 'content'];
    $teaserPost = $draftPost;
    $teaserPost['editorial']['teaser'] = ['de' => 'Kurzer Webseiten-Teaser'];
    $teaserDraft = requestRoute(array_replace($base, ['configuration' => $configured, 'news' => $start['news'], 'post' => $teaserPost]));
    check($teaserDraft['error'] === null && $teaserDraft['news']['editorial_contributions'][0]['teaser'] === ['en' => '', 'de' => 'Kurzer Webseiten-Teaser'], 'Persist configured localized teasers through the editorial route.');
    $configured['outlets'][0]['fields'] = ['content'];
    $teaserPost['news_revision'] = 3;
    $teaserPost['editorial']['teaser'] = ['de' => 'Do not replace this hidden field'];
    $contentOnly = requestRoute(array_replace($base, ['configuration' => $configured, 'news' => $teaserDraft['news'], 'post' => $teaserPost]));
    check($contentOnly['error'] === null && $contentOnly['news']['editorial_contributions'][0]['teaser']['de'] === 'Kurzer Webseiten-Teaser', 'Server-side field configuration preserves hidden text after settings changes.');
    $readyPost = $draftPost;
    $readyPost['action'] = 'ready';
    $readyPost['news_revision'] = 3;
    $ready = requestRoute(array_replace($base, ['news' => $draft['news'], 'post' => $readyPost]));
    check($ready['error'] === null && $ready['news']['outlet_requests'][0]['status'] === 'ready', 'Mark an independently saved draft ready.');
    check($ready['messages'] === [], 'Readiness does not send an unrequested author review notification.');
    $declinePost = ['csrf_token' => 'token', 'news_revision' => 4, 'action' => 'decline', 'editorial' => ['outlet_id' => $newsletter['id'], 'decline_reason' => 'Nicht für den Newsletter geeignet']];
    $decline = requestRoute(array_replace($base, ['news' => $ready['news'], 'post' => $declinePost]));
    check($decline['news']['outlet_requests'][1]['status'] === 'declined' && $decline['news']['editorial_contributions'] === $ready['news']['editorial_contributions'], 'Decline one outlet without changing another outlet\'s text.');
    $badReason = $declinePost;
    $badReason['editorial']['decline_reason'] = '';
    $invalid = requestRoute(array_replace($base, ['news' => $ready['news'], 'post' => $badReason]));
    check($invalid['error']['code'] === 400 && !$invalid['write_attempted'], 'Reject a decline without a reason before saving.');
    foreach ([['news.add', 'news.edit-own'], ['news.edit'], ['news.outlets'], ['admin.see'], []] as $permissions) {
        $denied = requestRoute(array_replace($base, ['permissions' => $permissions, 'post' => $startPost]));
        check($denied['error']['code'] === 403 && !$denied['write_attempted'], 'Require the editorial right for every action.');
    }
    $csrf = requestRoute($base + ['post' => array_replace($startPost, ['csrf_token' => 'invalid'])]);
    check($csrf['error']['code'] === 403 && !$csrf['write_attempted'], 'Reject forged editorial forms.');
    $stale = requestRoute(array_replace($base, ['news' => $ready['news'], 'post' => $startPost]));
    check($stale['error']['code'] === 409 && !$stale['write_attempted'], 'Reject stale editorial forms.');
    $race = requestRoute($base + ['post' => $startPost, 'race' => true]);
    check($race['error']['code'] === 409 && $race['news'] === $source, 'Concurrent author changes cannot be overwritten.');
    $internalNews = array_merge($source, ['internal_only' => true]);
    $internal = requestRoute(array_replace($base, ['news' => $internalNews, 'post' => $startPost]));
    check($internal['error']['code'] === 400 && !$internal['write_attempted'], 'Editors cannot bypass the internal-only selection.');
    $missing = requestRoute(array_replace($base, ['args' => [str_repeat('f', 24)], 'post' => $startPost]));
    check($missing['error']['code'] === 404 && !$missing['write_attempted'], 'Return not found for missing news.');

    $linked = $ready['news'];
    $linked['persons'] = [str_repeat('c', 24)];
    $linked['projects'] = [str_repeat('d', 24)];
    $linked['featured'] = ['type' => 'person', 'id' => str_repeat('c', 24), 'text' => 'Featured', 'text_de' => null];
    $linked['topics'] = ['topic-1'];
    $connectionPost = ['csrf_token' => 'token', 'news_revision' => 4, 'action' => 'connections', 'news' => [
        'persons' => '', 'activities' => [str_repeat('b', 24)], 'topics' => '', 'title' => 'Ignore this original change',
    ]];
    $connections = requestRoute(array_replace($base, ['news' => $linked, 'post' => $connectionPost]));
    check($connections['error'] === null && $connections['news']['persons'] === [] && $connections['news']['projects'] === $linked['projects'] && $connections['news']['topics'] === [], 'Edit shared connections and clear topics while retaining omitted feature sections.');
    check($connections['news']['featured'] === null && $connections['news']['title'] === $source['title'], 'Clear a removed featured connection without changing the original text.');
    check($connections['news']['original_revision'] === 2 && $connections['news']['editorial_contributions'] === $ready['news']['editorial_contributions'], 'Signal changed shared data without overwriting any outlet contribution.');
    $changedSourcePost = $readyPost;
    $changedSourcePost['news_revision'] = 5;
    $sourceChanged = requestRoute(array_replace($base, ['news' => $connections['news'], 'post' => $changedSourcePost]));
    check($sourceChanged['error']['code'] === 400 && !$sourceChanged['write_attempted'], 'Require reviewing source changes before completing a contribution again.');
    $changedSourcePost['editorial']['source_reviewed'] = '1';
    $reviewed = requestRoute(array_replace($base, ['news' => $connections['news'], 'post' => $changedSourcePost]));
    check($reviewed['error'] === null && $reviewed['news']['editorial_contributions'][0]['original_revision'] === 2, 'Acknowledge the current source version explicitly.');
    echo "News editorial route checks passed.\n";
} finally {
    unlink($testRoot . '/php/init.php');
    unlink($testRoot . '/php/NewsRequests.php');
    unlink($testRoot . '/php/NewsEditorial.php');
    unlink($testRoot . '/routes');
    rmdir($testRoot . '/php');
    rmdir($testRoot);
}
