<?php

// Run with: php tests/news-editorial-routes.php (no database required).
require __DIR__ . '/fixtures/news.php';
$testRoot = news_test_route_root();

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
    $richPost = $draftPost;
    $richPost['editorial']['format'] = 'html';
    $richPost['editorial']['content'] = ['de' => '<p><strong>Formatted contribution</strong><script>alert(1)</script></p>'];
    $richDraft = requestRoute(array_replace($base, ['news' => $start['news'], 'post' => $richPost]));
    check($richDraft['error'] === null && $richDraft['news']['editorial_contributions'][0]['format'] === 'html' && $richDraft['news']['editorial_contributions'][0]['content']['de'] === '<p><strong>Formatted contribution</strong></p>', 'Persist supported Quill HTML while removing executable markup through the real route.');
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
    $stale = requestRoute(array_replace($base, ['news' => $ready['news'], 'form_news' => $base['news'], 'post' => $startPost]));
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
    check($sourceChanged['error'] === null, 'Original edits do not block completion of independent outlet texts.');
    $reviewed = requestRoute(array_replace($base, ['news' => $connections['news'], 'post' => $changedSourcePost]));
    check($reviewed['error'] === null && $reviewed['news']['editorial_contributions'][0]['original_revision'] === 2, 'Acknowledge the current source version explicitly.');
    $reviewRoute = static function ($news, $action, $input = [], $actor = 'editor', $permissions = ['news.editorial'], $overrides = []) use ($base) {
        return requestRoute(array_replace($base, ['news' => $news, 'actor' => $actor, 'permissions' => $permissions,
            'post' => ['csrf_token' => 'token', 'news_revision' => $news['revision'], 'action' => $action, 'editorial' => $input]], $overrides));
    };
    $reviewRequest = $reviewRoute($ready['news'], 'request_review');
    check($reviewRequest['error'] === null && $reviewRequest['news']['outlet_requests'][0]['status'] === 'pending_approval', 'Request an author review of prepared outlets.');
    check(count($reviewRequest['messages']) === 1 && $reviewRequest['messages'][0]['group'] === 'user:author', 'Notify the author once for the entire batch.');
    $reviewInput = ['review_id' => $reviewRequest['news']['author_reviews'][0]['id']];
    $authorApproval = $reviewRoute($reviewRequest['news'], 'approve_review', $reviewInput, 'author', []);
    check($authorApproval['error'] === null && $authorApproval['news']['outlet_requests'][0]['status'] === 'released', 'The actual author can confirm without news-edit or editorial permissions.');
    check(count($authorApproval['messages']) === 1 && $authorApproval['messages'][0]['group'] === 'right:news.editorial', 'Notify the editorial team after author confirmation.');
    $originalEdit = requestRoute(['route' => '/crud/news/update/([a-f0-9]{24})', 'args' => [$id], 'actor' => 'author',
        'news' => $authorApproval['news'], 'permissions' => ['news.edit-own'], 'post' => ['csrf_token' => 'token', 'news' => [
            'title' => $source['title'], 'date' => $source['date'], 'content' => 'A later edit to the original only.', 'revision' => $authorApproval['news']['revision'],
        ]]]);
    check($originalEdit['error'] === null && $originalEdit['messages'] === [], 'Editing the original after approval sends no new editorial notification.');
    check($originalEdit['news']['outlet_requests'] === $authorApproval['news']['outlet_requests'] && $originalEdit['news']['editorial_contributions'] === $authorApproval['news']['editorial_contributions'], 'An original edit preserves every outlet release and its text.');
    $foreignResponse = $reviewRoute($reviewRequest['news'], 'approve_review', $reviewInput, 'other', ['news.editorial']);
    check($foreignResponse['error']['code'] === 403 && !$foreignResponse['write_attempted'] && $foreignResponse['messages'] === [], 'Another editor cannot impersonate the author.');
    $feedbackResponse = $reviewRoute($reviewRequest['news'], 'request_changes', $reviewInput + ['comment' => 'Please correct the date.'], 'author', []);
    check($feedbackResponse['error'] === null && $feedbackResponse['news']['author_reviews'][0]['comment'] === 'Please correct the date.' && $feedbackResponse['news']['outlet_requests'][0]['status'] === 'in_progress', 'Return the batch to editing with the author comment.');
    check(count($feedbackResponse['messages']) === 1 && $feedbackResponse['messages'][0]['group'] === 'right:news.editorial', 'Change requests notify the editorial inbox.');
    $badFeedback = $reviewRoute($reviewRequest['news'], 'request_changes', $reviewInput, 'author', []);
    check($badFeedback['error']['code'] === 400 && !$badFeedback['write_attempted'] && !$badFeedback['messages'], 'Reject empty feedback before writing or notifying.');
    $directRelease = $reviewRoute($ready['news'], 'release');
    check($directRelease['error'] === null && $directRelease['news']['outlet_requests'][0]['release_mode'] === 'without_review', 'Editors can release directly without requesting author review.');
    $confirmedRelease = $authorApproval;
    check($authorApproval['news']['outlet_requests'][0]['release_mode'] === 'author_approved' && !isset($authorApproval['news']['outlet_requests'][0]['released_revision']), 'Author approval directly records the final release.');
    $unconfirmedBypass = $reviewRoute($reviewRequest['news'], 'release');
    check($unconfirmedBypass['error']['code'] === 400 && !$unconfirmedBypass['write_attempted'], 'A pending review needs deliberate bypass confirmation.');
    $bypass = $reviewRoute($reviewRequest['news'], 'release', ['confirm_without_response' => '1']);
    check($bypass['error'] === null && $bypass['news']['author_reviews'][0]['status'] === 'closed_without_response' && $bypass['news']['outlet_requests'][0]['release_mode'] === 'without_response', 'Close without response and record the release mode.');
    $lateReply = $reviewRoute($bypass['news'], 'approve_review', $reviewInput, 'author', []);
    check($lateReply['error']['code'] === 400 && !$lateReply['write_attempted'] && !$lateReply['messages'], 'A late response cannot alter the completed release.');
    $reviewRace = $reviewRoute($ready['news'], 'request_review', [], 'editor', ['news.editorial'], ['race' => true]);
    check($reviewRace['error']['code'] === 409 && !$reviewRace['messages'] && $reviewRace['news'] === $ready['news'], 'Never send a notification when a concurrent change prevented the review write.');
    $cancel = $reviewRoute($reviewRequest['news'], 'cancel_review', $reviewInput);
    check($cancel['error'] === null && $cancel['news']['outlet_requests'][0]['status'] === 'ready' && $cancel['messages'][0]['group'] === 'user:author', 'Editors can end a pending review and notify the author.');
    $revise = $reviewRoute($confirmedRelease['news'], 'revise', ['outlet_id' => $website['id']]);
    check($revise['error'] === null && $revise['news']['outlet_requests'][0]['status'] === 'in_progress' && !isset($revise['news']['outlet_requests'][0]['release_mode']), 'Explicit revision invalidates the current release.');
    $forbiddenRelease = $reviewRoute($ready['news'], 'release', [], 'author', ['news.edit-own']);
    check($forbiddenRelease['error']['code'] === 403 && !$forbiddenRelease['write_attempted'], 'Authors do not gain editorial release permission.');
    $forgedResponse = $reviewRoute($reviewRequest['news'], 'approve_review', $reviewInput, 'author', [], ['post' => ['action' => 'approve_review', 'csrf_token' => 'forged']]);
    check($forgedResponse['error']['code'] === 403 && !$forgedResponse['write_attempted'], 'Author responses require a valid CSRF token.');
    $staleReviewPost = ['csrf_token' => 'token', 'action' => 'approve_review', 'news_revision' => 0, 'editorial' => $reviewInput];
    $editedOriginal = array_replace($reviewRequest['news'], ['revision' => 99, 'original_revision' => 6, 'content' => 'A later original edit']);
    $staleResponse = $reviewRoute($editedOriginal, 'approve_review', $reviewInput, 'author', [], ['post' => $staleReviewPost]);
    check($staleResponse['error'] === null && $staleResponse['news']['content'] === 'A later original edit' && $staleResponse['news']['revision'] === 100, 'An author can approve unchanged outlet texts from an older form while preserving later original edits.');
    echo "News editorial route checks passed.\n";
} finally {
    news_test_remove_root($testRoot);
}
