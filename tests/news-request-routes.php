<?php

// Run with: php tests/news-request-routes.php (no database or web server required).
require __DIR__ . '/fixtures/news.php';
$testRoot = news_test_route_root();

try {
    $post = ['csrf_token' => 'token', 'news' => ['title' => 'New infrastructure', 'content' => 'An update', 'date' => '2026-10-22', 'outlets' => [$website['id'], $newsletter['id']]]];
    $created = requestRoute(['route' => '/crud/news/create', 'post' => $post]);
    check($created['error'] === null && $created['news']['created_by'] === 'author', 'Create a news item as a member.');
    check($created['news']['publication_mode'] === 'outlets' && $created['news']['internal_only'] === false && !isset($created['news']['visibility']) && count($created['news']['outlet_requests']) === 2, 'New outlet news uses source settings instead of a visibility flag.');
    check(count($created['messages']) === 1 && $created['messages'][0]['group'] === 'right:news.editorial', 'Persist before notifying the editorial inbox once.');
    $plainPost = $post;
    unset($plainPost['news']['outlets']);
    $plain = requestRoute(['route' => '/crud/news/create', 'post' => $plainPost]);
    check($plain['error'] === null && $plain['news']['outlet_requests'] === [] && $plain['messages'] === [], 'Creating ordinary news works without any outlet request.');

    $id = $created['news']['_id'];
    $base = ['route' => '/crud/news/update/([a-f0-9]{24})', 'args' => [$id], 'news' => $created['news']];
    $updatePost = $post;
    $updatePost['news']['revision'] = 1;
    $updatePost['news']['outlet_selection'] = '1';
    $saved = requestRoute($base + ['post' => $updatePost]);
    check($saved['error'] === null && $saved['news']['revision'] === 2 && $saved['news']['original_revision'] === 1, 'Saving an unchanged original does not mark it as changed.');
    check($saved['messages'] === [], 'Repeated saves do not duplicate notifications.');
    $updatePost['news']['content'] = 'Corrected original';
    $updatePost['news']['outlets'] = [$newsletter['id']];
    $changed = requestRoute($base + ['post' => $updatePost]);
    check($changed['news']['original_revision'] === 2 && $changed['news']['outlet_requests'][0]['status'] === 'withdrawn', 'Save original edits and withdraw an open request together.');
    check(count($changed['messages']) === 1 && str_contains($changed['messages'][0]['en'], 'Withdrawn'), 'Notify the editorial team about withdrawals.');

    $lockedNews = $created['news'];
    $lockedNews['outlet_requests'][0]['status'] = 'in_progress';
    $lockedNews['editorial_contributions'] = [['content' => 'Prepared text']];
    $locked = requestRoute(array_replace($base, ['news' => $lockedNews, 'post' => $updatePost]));
    check($locked['news']['outlet_requests'][0] === $lockedNews['outlet_requests'][0] && $locked['news']['editorial_contributions'] === $lockedNews['editorial_contributions'], 'Saving the original never overwrites existing editorial work.');
    check($locked['messages'] === [], 'Omitted locked checkboxes are preserved silently.');
    $stale = requestRoute(array_replace($base, ['news' => $changed['news'], 'form_news' => $base['news'], 'post' => $updatePost]));
    check($stale['error']['code'] === 409 && !$stale['write_attempted'] && $stale['messages'] === [], 'Reject stale forms before writing or notifying.');
    $race = requestRoute($base + ['post' => $updatePost, 'race' => true]);
    check($race['error']['code'] === 409 && $race['news'] === $base['news'] && $race['messages'] === [], 'Handle a concurrent editorial write atomically.');
    $foreign = requestRoute($base + ['post' => $updatePost, 'actor' => 'other']);
    check($foreign['error']['code'] === 403 && !$foreign['write_attempted'], 'Protect other authors on the save endpoint.');
    $foreignGet = requestRoute(array_replace($base, ['method' => 'get', 'actor' => 'other', 'route' => '/news/edit/([a-f0-9]{24})']));
    check($foreignGet['error']['code'] === 403, 'Protect the original edit page as well.');
    $forgedCsrf = requestRoute($base + ['post' => array_replace($updatePost, ['csrf_token' => 'forged'])]);
    check($forgedCsrf['error']['code'] === 403 && !$forgedCsrf['write_attempted'], 'Protect outlet requests from forged forms.');
    $unknownPost = $post;
    $unknownPost['news']['outlets'] = [str_repeat('f', 24)];
    $unknown = requestRoute(['route' => '/crud/news/create', 'post' => $unknownPost]);
    check($unknown['error']['code'] === 400 && !$unknown['write_attempted'], 'Reject unavailable outlets before inserting a news item.');
    $otherEditor = requestRoute($base + ['post' => $updatePost, 'actor' => 'other', 'permissions' => ['news.edit']]);
    check($otherEditor['error']['code'] === 403 && !$otherEditor['write_attempted'], 'Legacy editors cannot change another author\'s requests through the author form.');
    $legacyNews = $base['news'];
    unset($legacyNews['revision'], $legacyNews['original_revision'], $legacyNews['original_updated_at'], $legacyNews['outlet_requests'], $legacyNews['publication_mode'], $legacyNews['internal_only']);
    $legacyNews['visibility'] = 'internal';
    $legacyPost = $updatePost;
    $legacyPost['news']['revision'] = 0;
    $legacy = requestRoute(array_replace($base, ['news' => $legacyNews, 'post' => $legacyPost]));
    check($legacy['error'] === null && $legacy['news']['revision'] === 1, 'Existing news can request outlets without a migration.');
    check($legacy['news']['visibility'] === 'internal' && !isset($legacy['news']['publication_mode']), 'An ordinary save preserves legacy original privacy.');
    $legacyPost['news']['publication_choice_changed'] = '1';
    $legacyPost['news']['internal_only'] = '0';
    $adopted = requestRoute(array_replace($base, ['news' => $legacyNews, 'post' => $legacyPost]));
    check($adopted['news']['publication_mode'] === 'outlets' && !isset($adopted['news']['visibility']), 'An explicit choice moves an old original to the source rules.');
    $internalPost = $plainPost;
    $internalPost['news']['internal_only'] = '1';
    $internal = requestRoute(['route' => '/crud/news/create', 'post' => $internalPost]);
    check($internal['error'] === null && $internal['news']['internal_only'] === true && $internal['messages'] === [], 'Internal-only news creates no editorial request or inbox message.');
    $conflictingPost = $post;
    $conflictingPost['news']['internal_only'] = '1';
    $conflict = requestRoute(['route' => '/crud/news/create', 'post' => $conflictingPost]);
    check($conflict['error']['code'] === 400 && !$conflict['write_attempted'], 'Reject internal-only and outlets selected together.');
    $stopPost = $updatePost;
    $stopPost['news']['outlets'] = [];
    $stopPost['news']['internal_only'] = '1';
    $stopped = requestRoute($base + ['post' => $stopPost]);
    check($stopped['news']['internal_only'] === true && $stopped['news']['outlet_requests'][0]['status'] === 'withdrawn', 'Choosing internal-only withdraws open outlet requests.');
    $started = requestRoute(array_replace($base, ['news' => $lockedNews, 'post' => $stopPost]));
    check($started['error']['code'] === 400 && !$started['write_attempted'], 'Changing to internal-only cannot silently cancel editorial work already started.');
    $noOutlets = requestRoute(['route' => '/crud/news/create', 'post' => $plainPost, 'configuration' => ['original_portfolio' => true, 'outlets' => []]]);
    check($noOutlets['news']['visibility'] === 'internal' && !isset($noOutlets['news']['publication_mode']), 'Installations without outlets keep the classic behavior.');
    $image = requestRoute(array_replace($base, ['route' => '/crud/news/upload-picture/([a-f0-9]{24})', 'actor' => 'other', 'post' => ['csrf_token' => 'token', 'delete' => '1']]));
    check($image['error']['code'] === 403 && !$image['write_attempted'], 'Protect original images with the same ownership permissions.');
    echo "News request route checks passed.\n";
} finally {
    news_test_remove_root($testRoot);
}
