<?php

// Run with: php tests/news-requests.php (no database required).
require __DIR__ . '/news-outlets.php';
require_once BASEPATH . '/php/NewsRequests.php';
$testLanguage = 'en';

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

$_SESSION['username'] = 'author';
$configuration = $initial;
$settings = new NewsRequestTestSettings(['news.add', 'news.edit-own'], $configuration);
$authorNews = ['_id' => str_repeat('a', 24), 'created_by' => 'author', 'title' => '<News>', 'original_revision' => 1];
check(NewsRequests::canCreate($settings), 'Members can create news with the new permission.');
check(NewsRequests::canEdit($authorNews, $settings), 'Members can edit their own original.');
check(!NewsRequests::canEdit(array_merge($authorNews, ['created_by' => 'other']), $settings), 'Own-news permission never grants access to other originals.');
check(!NewsRequests::canCreate(new NewsRequestTestSettings([], $configuration)), 'Creating news requires permission.');
check(NewsRequests::canCreate(new NewsRequestTestSettings(['news.edit'], $configuration)), 'Keep existing creation permissions.');
check(!NewsRequests::canEdit($authorNews, new NewsRequestTestSettings(['news.editorial'], $configuration)), 'Editorial permission alone does not grant original editing.');

$time = '2026-10-22T16:00:00+02:00';
$changes = NewsRequests::reconcile([$website['id'], $newsletter['id'], $website['id']], $configuration, $authorNews, 'author', $time, 1);
check(count($changes['requests']) === 2 && count($changes['added']) === 2, 'Group multiple requests and deduplicate IDs.');
check($changes['requests'][0]['requested_by'] === 'author' && $changes['requests'][0]['source'] === 'author', 'Record author and source on the server.');
check($changes['requests'][0]['status'] === 'requested' && $changes['requests'][0]['original_revision'] === 1, 'Store initial state and source revision.');
$news = array_merge($authorNews, ['outlet_requests' => $changes['requests']]);
$unchanged = NewsRequests::reconcile([$website['id'], $newsletter['id']], $configuration, $news, 'author', $time, 2);
check($unchanged['added'] === [] && $unchanged['withdrawn'] === [] && $unchanged['requests'] === $changes['requests'], 'Saving the original preserves request metadata without duplicate notifications.');
$withdrawal = NewsRequests::reconcile([$newsletter['id']], $configuration, $news, 'author', $time, 2);
check($withdrawal['requests'][0]['status'] === 'withdrawn' && $withdrawal['withdrawn'] === [$website['id']], 'Withdraw open requests without deleting their record.');
$resubmitted = NewsRequests::reconcile([$website['id'], $newsletter['id']], $configuration, array_merge($news, ['outlet_requests' => $withdrawal['requests']]), 'author', $time, 2);
check($resubmitted['added'] === [$website['id']] && isset($resubmitted['requests'][0]['resubmitted_at']), 'Support resubmitting an open withdrawn request.');
check($resubmitted['requests'][0]['requested_at'] === $time && $resubmitted['requests'][0]['original_revision'] === 2, 'Retain initial request date and record the current original revision on resubmission.');

foreach (['in_progress', 'declined', 'ready', 'pending_approval', 'approved', 'published', 'future_status'] as $status) {
    $lockedRequests = $changes['requests'];
    $lockedRequests[0]['status'] = $status;
    $lockedRequests[0]['editorial_text'] = 'Never overwrite this';
    $lockedNews = array_merge($news, ['outlet_requests' => $lockedRequests]);
    $result = NewsRequests::reconcile([], $configuration, $lockedNews, 'author', $time, 3);
    check($result['requests'][0] === $lockedRequests[0], 'Preserve editorial state and content for ' . $status);
}
$editorRequests = $changes['requests'];
$editorRequests[0]['source'] = 'editorial';
$result = NewsRequests::reconcile([], $configuration, array_merge($news, ['outlet_requests' => $editorRequests]), 'author', $time, 2);
check($result['requests'][0] === $editorRequests[0], 'Authors cannot withdraw editorial additions.');
$startedRequests = $changes['requests'];
$startedRequests[0]['started_at'] = $time;
$result = NewsRequests::reconcile([], $configuration, array_merge($news, ['outlet_requests' => $startedRequests]), 'author', $time, 2);
check($result['requests'][0] === $startedRequests[0], 'Starting editorial work locks the request even before a status change.');

$inactiveConfig = $configuration;
$inactiveConfig['outlets'][0]['active'] = false;
rejects(fn() => NewsRequests::reconcile([$website['id']], $inactiveConfig, $authorNews, 'author', $time, 1), 'Reject new requests for deactivated outlets.');
check(NewsRequests::reconcile([$website['id'], $newsletter['id']], $inactiveConfig, $news, 'author', $time, 1)['requests'] === $changes['requests'], 'Retain existing requests for deactivated outlets.');
check(NewsRequests::reconcile([], $inactiveConfig, $news, 'author', $time, 1)['requests'][0]['status'] === 'withdrawn', 'Allow withdrawing an unprocessed inactive outlet.');
rejects(fn() => NewsRequests::reconcile([str_repeat('f', 24)], $configuration, $authorNews, 'author', $time, 1), 'Reject unknown IDs.');
rejects(fn() => NewsRequests::reconcile([['status' => 'approved']], $configuration, $authorNews, 'author', $time, 1), 'Reject forged state objects instead of IDs.');
rejects(fn() => NewsRequests::reconcile('invalid', $configuration, $authorNews, 'author', $time, 1), 'Reject malformed selections.');
rejects(fn() => NewsRequests::reconcile([], $configuration, $authorNews, 'other', $time, 1), 'Do not change another author\'s requests.');

$db = new NewsRequestTestDB();
NewsRequests::notify($db, $news, $authorNews['_id'], $changes, $configuration);
check(count($db->messages) === 1, 'Notify once for all newly selected outlets.');
check($db->messages[0]['group'] === 'right:news.editorial' && $db->messages[0]['link'] === '/news/view/' . $authorNews['_id'], 'Target the editorial permission and link to this news.');
check(str_contains($db->messages[0]['en'], '&lt;News&gt;') && str_contains($db->messages[0]['de'], 'Webseite'), 'Escape member text and use localized outlet labels.');
NewsRequests::notify($db, $news, $authorNews['_id'], $unchanged, $configuration);
check(count($db->messages) === 1, 'Do not notify again on unchanged selections.');
NewsRequests::notify($db, $news, $authorNews['_id'], $withdrawal, $configuration);
check(count($db->messages) === 2 && str_contains($db->messages[1]['de'], 'Zurückgezogen'), 'Notify about withdrawals.');

check(!NewsRequests::validCsrf('forged'), 'Reject forged CSRF tokens.');
check(NewsRequests::validCsrf(NewsRequests::csrfToken()), 'Accept the current form token.');
$news = array_merge($authorNews, ['outlet_requests' => new MongoDB\Model\BSONArray([
    new MongoDB\Model\BSONDocument(array_merge($changes['requests'][0], ['status' => 'in_progress'])),
    new MongoDB\Model\BSONDocument($changes['requests'][1]),
]), 'original_revision' => 2]);
$Settings = $settings;
$outletSelectionMode = 'edit';
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(str_contains($html, 'value="' . $website['id'] . '" checked disabled'), 'Render locked requests as selected but disabled.');
check(str_contains($html, 'value="' . $newsletter['id'] . '" checked >'), 'Render open requests as editable.');
check(str_contains($html, 'Original changed since request'), 'Make changes to the source visible.');
$_SESSION['username'] = 'other';
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(!str_contains($html, 'name="news[outlets][]"') && !str_contains($html, 'name="news[outlet_selection]"'), 'Other users only see a read-only summary.');
unset($_SESSION['username']);
check(!NewsRequests::canCreate($settings) && !NewsRequests::canEdit($authorNews, $settings), 'Require a signed-in author.');

echo "News request checks passed.\n";
