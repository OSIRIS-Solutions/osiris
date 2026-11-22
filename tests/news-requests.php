<?php

// Run with: php tests/news-requests.php (no database required).
require __DIR__ . '/fixtures/news.php';
require_once BASEPATH . '/php/NewsEditorial.php';
$testLanguage = 'en';


$_SESSION['username'] = 'author';
$configuration = $initial;
$settings = new NewsRequestTestSettings(['news.add', 'news.edit-own'], $configuration);
$authorNews = ['_id' => str_repeat('a', 24), 'created_by' => 'author', 'title' => '<News>', 'original_revision' => 1];
check(NewsEditorial::canCreate($settings), 'Members can create news with the new permission.');
check(NewsEditorial::canEdit($authorNews, $settings), 'Members can edit their own original.');
check(!NewsEditorial::canEdit(array_merge($authorNews, ['created_by' => 'other']), $settings), 'Own-news permission never grants access to other originals.');
check(!NewsEditorial::canCreate(new NewsRequestTestSettings([], $configuration)), 'Creating news requires permission.');
check(NewsEditorial::canCreate(new NewsRequestTestSettings(['news.edit'], $configuration)), 'Keep existing creation permissions.');
check(!NewsEditorial::canEdit($authorNews, new NewsRequestTestSettings(['news.editorial'], $configuration)), 'Editorial permission alone does not grant original editing.');

$time = '2026-10-22T16:00:00+02:00';
$changes = NewsEditorial::reconcileRequests([$website['id'], $newsletter['id'], $website['id']], $configuration, $authorNews, 'author', $time);
check(count($changes['requests']) === 2 && count($changes['added']) === 2, 'Group multiple requests and deduplicate IDs.');
check($changes['requests'][0]['requested_by'] === 'author' && $changes['requests'][0]['source'] === 'author', 'Record author and source on the server.');
check($changes['requests'][0]['status'] === 'requested' && !isset($changes['requests'][0]['original_revision']), 'Store initial state without coupling requests to original revisions.');
$news = array_merge($authorNews, ['outlet_requests' => $changes['requests']]);
$unchanged = NewsEditorial::reconcileRequests([$website['id'], $newsletter['id']], $configuration, $news, 'author', $time);
check($unchanged['added'] === [] && $unchanged['withdrawn'] === [] && $unchanged['requests'] === $changes['requests'], 'Saving the original preserves request metadata without duplicate notifications.');
$withdrawal = NewsEditorial::reconcileRequests([$newsletter['id']], $configuration, $news, 'author', $time);
check($withdrawal['requests'][0]['status'] === 'withdrawn' && $withdrawal['withdrawn'] === [$website['id']], 'Withdraw open requests without deleting their record.');
$resubmitted = NewsEditorial::reconcileRequests([$website['id'], $newsletter['id']], $configuration, array_merge($news, ['outlet_requests' => $withdrawal['requests']]), 'author', $time);
check($resubmitted['added'] === [$website['id']] && isset($resubmitted['requests'][0]['resubmitted_at']), 'Support resubmitting an open withdrawn request.');
check($resubmitted['requests'][0]['requested_at'] === $time && !isset($resubmitted['requests'][0]['original_revision']), 'Retain the initial request date without a redundant original revision.');

foreach (['in_progress', 'declined', 'ready', 'pending_approval', 'approved', 'published', 'future_status'] as $status) {
    $lockedRequests = $changes['requests'];
    $lockedRequests[0]['status'] = $status;
    $lockedRequests[0]['editorial_text'] = 'Never overwrite this';
    $lockedNews = array_merge($news, ['outlet_requests' => $lockedRequests]);
    $result = NewsEditorial::reconcileRequests([], $configuration, $lockedNews, 'author', $time);
    check($result['requests'][0] === NewsEditorial::requests($lockedNews)[0], 'Preserve editorial state and content for ' . $status);
}
$editorRequests = $changes['requests'];
$editorRequests[0]['source'] = 'editorial';
$result = NewsEditorial::reconcileRequests([], $configuration, array_merge($news, ['outlet_requests' => $editorRequests]), 'author', $time);
check($result['requests'][0] === $editorRequests[0], 'Authors cannot withdraw editorial additions.');
$startedRequests = $changes['requests'];
$startedRequests[0]['started_at'] = $time;
$result = NewsEditorial::reconcileRequests([], $configuration, array_merge($news, ['outlet_requests' => $startedRequests]), 'author', $time);
check($result['requests'][0] === $startedRequests[0], 'Starting editorial work locks the request even before a status change.');

$inactiveConfig = $configuration;
$inactiveConfig['outlets'][0]['active'] = false;
rejects(fn() => NewsEditorial::reconcileRequests([$website['id']], $inactiveConfig, $authorNews, 'author', $time), 'Reject new requests for deactivated outlets.');
check(NewsEditorial::reconcileRequests([$website['id'], $newsletter['id']], $inactiveConfig, $news, 'author', $time)['requests'] === $changes['requests'], 'Retain existing requests for deactivated outlets.');
check(NewsEditorial::reconcileRequests([], $inactiveConfig, $news, 'author', $time)['requests'][0]['status'] === 'withdrawn', 'Allow withdrawing an unprocessed inactive outlet.');
rejects(fn() => NewsEditorial::reconcileRequests([str_repeat('f', 24)], $configuration, $authorNews, 'author', $time), 'Reject unknown IDs.');
rejects(fn() => NewsEditorial::reconcileRequests([['status' => 'approved']], $configuration, $authorNews, 'author', $time), 'Reject forged state objects instead of IDs.');
rejects(fn() => NewsEditorial::reconcileRequests('invalid', $configuration, $authorNews, 'author', $time), 'Reject malformed selections.');
rejects(fn() => NewsEditorial::reconcileRequests([], $configuration, $authorNews, 'other', $time), 'Do not change another author\'s requests.');

$db = new NewsRequestTestDB();
NewsEditorial::notifyRequests($db, $news, $authorNews['_id'], $changes, $configuration);
check(count($db->messages) === 1, 'Notify once for all newly selected outlets.');
check($db->messages[0]['group'] === 'right:news.editorial' && $db->messages[0]['link'] === '/news/view/' . $authorNews['_id'], 'Target the editorial permission and link to this news.');
check(str_contains($db->messages[0]['en'], '&lt;News&gt;') && str_contains($db->messages[0]['de'], 'Webseite'), 'Escape member text and use localized outlet labels.');
NewsEditorial::notifyRequests($db, $news, $authorNews['_id'], $unchanged, $configuration);
check(count($db->messages) === 1, 'Do not notify again on unchanged selections.');
NewsEditorial::notifyRequests($db, $news, $authorNews['_id'], $withdrawal, $configuration);
check(count($db->messages) === 2 && str_contains($db->messages[1]['de'], 'Zurückgezogen'), 'Notify about withdrawals.');

check(!valid_csrf_token('forged', 'news'), 'Reject forged CSRF tokens.');
check(valid_csrf_token(csrf_token('news'), 'news'), 'Accept the current form token.');
$news = array_merge($authorNews, ['outlet_requests' => new MongoDB\Model\BSONArray([
    new MongoDB\Model\BSONDocument(array_merge($changes['requests'][0], ['status' => 'in_progress'])),
    new MongoDB\Model\BSONDocument($changes['requests'][1]),
]), 'original_revision' => 2]);
$Settings = $settings;
$outletSelectionMode = 'edit';
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(str_contains($html, 'value="' . $website['id'] . '" checked disabled'), 'Render locked requests as selected but disabled.');
check(str_contains($html, 'value="' . $newsletter['id'] . '" checked >'), 'Render open requests as editable.');
check(!str_contains($html, 'Original changed since request'), 'Do not raise recurring source-change warnings in the request summary.');
$_SESSION['username'] = 'other';
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(!str_contains($html, 'name="news[outlets][]"') && !str_contains($html, 'name="news[outlet_selection]"'), 'Other users only see a read-only summary.');
unset($_SESSION['username']);
check(!NewsEditorial::canCreate($settings) && !NewsEditorial::canEdit($authorNews, $settings), 'Require a signed-in author.');

echo "News request checks passed.\n";
