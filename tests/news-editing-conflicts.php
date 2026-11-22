<?php

// Independent original/outlet editing must not overwrite changes or require unrelated reloads.
require __DIR__ . '/fixtures/news.php';
$testRoot = news_test_route_root();
try {
    $formNews = $preparedNews;
    $post = ['csrf_token' => 'token', 'action' => 'save', 'editorial' => ['outlet_id' => $website['id'], 'content' => ['de' => 'Saved from an older form']]];
    $base = ['route' => '/crud/news/editorial/([a-f0-9]{24})', 'args' => [$formNews['_id']], 'actor' => 'editor', 'permissions' => ['news.editorial'], 'news' => $formNews, 'form_news' => $formNews, 'post' => $post];
    $changedOriginal = array_replace($formNews, ['content' => 'New original', 'revision' => 80, 'original_revision' => 20]);
    $saved = requestRoute(array_replace($base, ['news' => $changedOriginal]));
    check($saved['error'] === null && $saved['news']['content'] === 'New original' && $saved['news']['revision'] === 81, 'An older outlet form saves while retaining newer original text.');
    $changedOther = array_merge($formNews, NewsEditorial::apply('save', ['outlet_id' => $newsletter['id'], 'content' => ['de' => 'Changed newsletter']], $formNews, $reviewConfiguration, 'other-editor', $now));
    $saved = requestRoute(array_replace($base, ['news' => $changedOther]));
    check($saved['error'] === null && $saved['news']['editorial_contributions'][1]['content']['de'] === 'Changed newsletter', 'Editing a different outlet does not invalidate this form.');
    $sameOutlet = array_merge($formNews, NewsEditorial::apply('save', ['outlet_id' => $website['id'], 'content' => ['de' => 'A concurrent edit']], $formNews, $reviewConfiguration, 'other-editor', $now));
    $conflict = requestRoute(array_replace($base, ['news' => $sameOutlet]));
    check($conflict['error']['code'] === 409 && !$conflict['write_attempted'] && $conflict['news'] === $sameOutlet, 'The same outlet still rejects stale text without overwriting it.');
    $retry = requestRoute($base + ['race_once' => ['content' => 'Original changed during save']]);
    check($retry['error'] === null && $retry['write_attempts'] === 2 && $retry['news']['content'] === 'Original changed during save', 'Retry a concurrent independent write against fresh state.');
    $retryOther = requestRoute($base + ['race_once' => ['outlet_requests' => $changedOther['outlet_requests'], 'editorial_contributions' => $changedOther['editorial_contributions']]]);
    check($retryOther['error'] === null && $retryOther['news']['editorial_contributions'][1]['content']['de'] === 'Changed newsletter', 'Retry retains another outlet edited during the atomic write window.');
    $retryConflict = requestRoute($base + ['race_once' => ['editorial_contributions' => $sameOutlet['editorial_contributions']]]);
    check($retryConflict['error']['code'] === 409 && $retryConflict['write_attempts'] === 1 && $retryConflict['messages'] === [], 'A same-outlet change during the retry is a real conflict.');

    $originalPost = ['csrf_token' => 'token', 'news' => ['title' => $formNews['title'], 'date' => $formNews['date'], 'content' => 'Updated by the author']];
    $originalRoute = ['route' => '/crud/news/update/([a-f0-9]{24})', 'args' => [$formNews['_id']], 'actor' => 'author', 'permissions' => ['news.edit-own'], 'form_news' => $formNews, 'post' => $originalPost];
    $saved = requestRoute($originalRoute + ['news' => $changedOther]);
    check($saved['error'] === null && $saved['news']['editorial_contributions'] === $changedOther['editorial_contributions'], 'An older original form preserves later outlet edits.');
    $conflict = requestRoute($originalRoute + ['news' => $changedOriginal]);
    check($conflict['error']['code'] === 409 && !$conflict['write_attempted'], 'Two writers of the original still conflict.');
    $saved = requestRoute($originalRoute + ['news' => $formNews, 'race_once' => ['editorial_contributions' => $changedOther['editorial_contributions']]]);
    check($saved['error'] === null && $saved['write_attempts'] === 2 && $saved['news']['editorial_contributions'] === $changedOther['editorial_contributions'], 'An original write retries without losing a simultaneous outlet update.');

    $selectionChanged = $formNews;
    $selectionChanged['outlet_requests'][0]['status'] = 'withdrawn';
    $selectionChanged['outlet_requests'][0]['withdrawn_at'] = $now;
    $conflict = requestRoute($originalRoute + ['news' => $selectionChanged]);
    check($conflict['error']['code'] === 409 && !$conflict['write_attempted'], 'Independent editorial progress is allowed, but concurrent author selections cannot overwrite each other.');

    $legacy = $approvedNews;
    foreach ($legacy['outlet_requests'] as &$request) {
        $request['status'] = 'approved';
        unset($request['release_mode'], $request['released_revision']);
    }
    unset($request);
    $canonical = NewsEditorial::requests($legacy);
    check(array_column($canonical, 'status') === ['released', 'released'] && array_column($canonical, 'release_mode') === ['author_approved', 'author_approved'], 'Read existing author approvals as canonical final releases.');
    check(!isset($approvedNews['outlet_requests'][0]['released_revision'], $pendingNews['author_reviews'][0]['original_revision']), 'New releases and review rounds omit redundant source and release revisions.');
    echo "News independent editing checks passed.\n";
} finally {
    news_test_remove_root($testRoot);
}
