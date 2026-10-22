<?php

// Run with: php tests/news-editorial.php (no database required).
require __DIR__ . '/news-requests.php';
require_once BASEPATH . '/php/NewsEditorial.php';
$testLanguage = 'en';
$_SESSION['username'] = 'editor';
$now = '2026-10-22T16:00:00+02:00';
$news = ['_id' => str_repeat('a', 24), 'title' => 'Original', 'title_de' => 'Original DE', 'content' => 'Keep this original',
    'created_by' => 'author', 'publication_mode' => 'outlets', 'internal_only' => false, 'revision' => 1, 'original_revision' => 1,
    'outlet_requests' => [['outlet_id' => $website['id'], 'source' => 'author', 'status' => 'requested', 'requested_by' => 'author', 'requested_at' => $now, 'original_revision' => 1]]];
$original = $news;
$started = NewsEditorial::apply('start', ['outlet_id' => $website['id']], $news, $initial, 'editor', $now);
check($started['outlet_requests'][0]['status'] === 'in_progress' && $started['outlet_requests'][0]['started_by'] === 'editor', 'Starting a requested outlet records editorial ownership.');
check(NewsRequests::locked($started['outlet_requests'][0]), 'Starting work locks author changes.');
check(!isset($started['title'], $started['content'], $started['visibility'], $started['internal_only']), 'Editorial writes contain no original or publication fields.');
$news = array_merge($news, $started);
$contribution = NewsEditorial::contributions($news)[$website['id']];
check($contribution['title'] === ['en' => 'Original', 'de' => 'Original DE'] && $contribution['content'] === ['en' => '', 'de' => ''], 'Start a separate localized draft.');
rejects(fn() => NewsEditorial::apply('ready', ['outlet_id' => $website['id']], $news, $initial, 'editor', $now), 'Empty drafts cannot be marked ready.');
$draft = ['outlet_id' => $website['id'], 'title' => ['de' => 'Neue Fassung'], 'content' => ['de' => '<script>plain text</script>'], 'created_by' => 'forged', 'revision' => 999];
$saved = NewsEditorial::apply('save', $draft, $news, $initial, 'second-editor', $now);
$news = array_merge($news, $saved);
$contribution = NewsEditorial::contributions($news)[$website['id']];
check($contribution['content'] === ['en' => '', 'de' => '<script>plain text</script>'] && $contribution['revision'] === 1, 'Save both language keys and assign versions on the server.');
check($contribution['created_by'] === 'editor' && $contribution['updated_by'] === 'second-editor', 'Keep creator and last editor independent of submitted metadata.');
$news['original_revision'] = 2;
rejects(fn() => NewsEditorial::apply('ready', $draft, $news, $initial, 'editor', $now), 'Changed originals need explicit review before completion.');
$ready = NewsEditorial::apply('ready', $draft + ['source_reviewed' => '1'], $news, $initial, 'editor', $now);
$news = array_merge($news, $ready);
check($news['outlet_requests'][0]['status'] === 'ready' && NewsEditorial::contributions($news)[$website['id']]['original_revision'] === 2, 'Complete a draft after reviewing the original.');
$again = NewsEditorial::apply('save', $draft, $news, $initial, 'editor', $now);
check($again['outlet_requests'][0]['status'] === 'in_progress' && !isset($again['outlet_requests'][0]['ready_at']), 'Editing a completed draft returns it to in-progress.');
rejects(fn() => NewsEditorial::apply('decline', ['outlet_id' => $website['id'], 'decline_reason' => ' '], $news, $initial, 'editor', $now), 'Declining requires a reason.');
$declined = NewsEditorial::apply('decline', ['outlet_id' => $website['id'], 'decline_reason' => 'Not suitable for this outlet'], $news, $initial, 'editor', $now);
check($declined['outlet_requests'][0]['status'] === 'declined' && $declined['editorial_contributions'] === $news['editorial_contributions'], 'Declining keeps the draft intact.');
$reopened = NewsEditorial::apply('reopen', ['outlet_id' => $website['id']], array_merge($news, $declined), $initial, 'editor', $now);
check($reopened['outlet_requests'][0]['status'] === 'in_progress' && !isset($reopened['outlet_requests'][0]['decline_reason']), 'Reopening clears the current rejection but retains it in history.');
check($reopened['editorial_history'][count($reopened['editorial_history']) - 2]['reason'] === 'Not suitable for this outlet', 'Retain rejection history.');

$added = NewsEditorial::apply('add', ['outlet_id' => $newsletter['id']], $news, $initial, 'editor', $now);
check(count($added['outlet_requests']) === 2 && $added['outlet_requests'][1]['source'] === 'editorial', 'Editors can add an additional active outlet.');
check($added['editorial_contributions'][0] === $news['editorial_contributions'][0], 'Outlet contributions remain independent.');
$withdrawn = $news;
$withdrawn['outlet_requests'][0]['status'] = 'withdrawn';
$reopened = NewsEditorial::apply('reopen', ['outlet_id' => $website['id']], $withdrawn, $initial, 'editor', $now);
check($reopened['outlet_requests'][0]['source'] === 'editorial', 'An editor reactivating a withdrawn wish is visibly the source of that addition.');
rejects(fn() => NewsEditorial::apply('add', ['outlet_id' => $website['id']], $news, $initial, 'editor', $now), 'Do not create duplicate outlet contributions.');
$inactive = $initial;
$inactive['outlets'][1]['active'] = false;
rejects(fn() => NewsEditorial::apply('add', ['outlet_id' => $newsletter['id']], $news, $inactive, 'editor', $now), 'Do not add inactive outlets.');
rejects(fn() => NewsEditorial::apply('add', ['outlet_id' => str_repeat('f', 24)], $news, $initial, 'editor', $now), 'Do not add unknown outlets.');
rejects(fn() => NewsEditorial::apply('start', ['outlet_id' => $website['id']], $news, $initial, 'editor', $now), 'Prevent repeated starts.');
rejects(fn() => NewsEditorial::apply('save', $draft, $original, $initial, 'editor', $now), 'Require accepting a request before saving its draft.');
rejects(fn() => NewsEditorial::apply('add', ['outlet_id' => $newsletter['id']], array_merge($news, ['internal_only' => true]), $initial, 'editor', $now), 'Respect internal-only selection.');
rejects(fn() => NewsEditorial::apply('save', array_replace($draft, ['content' => ['de' => ['forged']]]), $news, $initial, 'editor', $now), 'Reject malformed localized text.');
rejects(fn() => NewsEditorial::apply('save', array_replace($draft, ['title' => ['en' => str_repeat('x', 241)]]), $news, $initial, 'editor', $now), 'Enforce title lengths.');
foreach (['pending_approval', 'approved', 'published', 'unknown'] as $status) {
    $protected = $news;
    $protected['outlet_requests'][0]['status'] = $status;
    rejects(fn() => NewsEditorial::apply('save', $draft, $protected, $initial, 'editor', $now), 'Protect ' . $status . ' from this draft editor.');
}
$mongoNews = $news;
$mongoNews['editorial_contributions'] = new MongoDB\Model\BSONArray([new MongoDB\Model\BSONDocument(array_merge($contribution, [
    'title' => new MongoDB\Model\BSONDocument($contribution['title']),
    'content' => new MongoDB\Model\BSONDocument($contribution['content']),
]))]);
check(NewsEditorial::contributions($mongoNews)[$website['id']]['content'] === $contribution['content'], 'Read localized text from nested MongoDB documents.');

// Field configuration controls submitted texts without destroying hidden existing values.
$configured = $initial;
$configured['outlets'][0]['fields'] = ['title', 'teaser', 'content'];
$withTeaser = NewsEditorial::apply('save', $draft + ['teaser' => ['de' => ' Ein kurzer Teaser ', 'en' => 'A short teaser']], $news, $configured, 'editor', $now);
$teaserNews = array_merge($news, $withTeaser);
check(NewsEditorial::contributions($teaserNews)[$website['id']]['teaser'] === ['en' => 'A short teaser', 'de' => 'Ein kurzer Teaser'], 'Store a separate localized teaser when configured.');
rejects(fn() => NewsEditorial::apply('save', $draft + ['teaser' => ['de' => str_repeat('x', 2001)]], $news, $configured, 'editor', $now), 'Validate teaser lengths.');
$configured['outlets'][0]['fields'] = ['content'];
$socialInput = ['outlet_id' => $website['id'], 'content' => ['de' => 'Social media text'], 'title' => ['de' => 'Forged hidden title'], 'teaser' => ['de' => ['forged']]];
$social = NewsEditorial::apply('ready', $socialInput, $teaserNews, $configured, 'editor', $now);
$socialNews = array_merge($teaserNews, $social);
$socialContribution = NewsEditorial::contributions($socialNews)[$website['id']];
check($socialContribution['title'] === NewsEditorial::contributions($teaserNews)[$website['id']]['title'] && $socialContribution['teaser'] === ['en' => 'A short teaser', 'de' => 'Ein kurzer Teaser'], 'Ignore submitted hidden fields and preserve previous texts.');
check($socialContribution['content']['de'] === 'Social media text' && $social['outlet_requests'][0]['status'] === 'ready', 'Complete a content-only social media contribution.');
rejects(fn() => NewsEditorial::apply('ready', ['outlet_id' => $website['id'], 'content' => ['en' => '', 'de' => '']], $socialNews, $configured, 'editor', $now), 'Hidden existing texts cannot satisfy readiness for a content-only outlet.');
$configured['outlets'][0]['fields'] = ['title', 'teaser', 'content'];
$reenabled = NewsEditorial::apply('save', ['outlet_id' => $website['id'], 'content' => ['de' => 'Another update']], $socialNews, $configured, 'editor', $now);
check($reenabled['editorial_contributions'][0]['teaser'] === $socialContribution['teaser'], 'Preserve text when a field is enabled after an editor opened the form.');
$configured['outlets'][0]['fields'] = ['teaser'];
$teaserOnly = NewsEditorial::apply('ready', ['outlet_id' => $website['id'], 'teaser' => ['de' => 'Short text']], $socialNews, $configured, 'editor', $now);
check($teaserOnly['outlet_requests'][0]['status'] === 'ready', 'Outlets without a body can complete a configured text field.');
$mongoNews['editorial_contributions'][0]['teaser'] = new MongoDB\Model\BSONDocument(['en' => '', 'de' => 'Mongo teaser']);
check(NewsEditorial::contributions($mongoNews)[$website['id']]['teaser']['de'] === 'Mongo teaser', 'Read localized teasers from MongoDB.');
$connections = NewsEditorial::connections(['persons' => '', 'activities' => [str_repeat('b', 24), str_repeat('b', 24)]], ['persons' => [str_repeat('c', 24)], 'projects' => [str_repeat('d', 24)], 'infrastructures' => ['infra-1']]);
check($connections['persons'] === [] && count($connections['activities']) === 1 && $connections['projects'] === [str_repeat('d', 24)] && $connections['infrastructures'] === ['infra-1'], 'Clear and deduplicate shared links while preserving omitted sections.');
rejects(fn() => NewsEditorial::connections(['persons' => [['$ne' => null]]], []), 'Reject malformed entity links.');
rejects(fn() => NewsEditorial::connections(['persons' => ['not-an-id']], []), 'Validate person IDs.');

echo "News editorial checks passed.\n";
