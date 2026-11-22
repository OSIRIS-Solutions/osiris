<?php

// Run with: php tests/news-review.php (no database required).
require __DIR__ . '/fixtures/news.php';
require_once BASEPATH . '/php/NewsReview.php';
$reviewConfiguration = $initial;
$reviewConfiguration['outlets'][0]['fields'] = ['title', 'teaser', 'content'];
$preparedNews = $original;
$preparedNews['outlet_requests'][] = ['outlet_id' => $newsletter['id'], 'source' => 'author', 'status' => 'requested'];
foreach ([$website['id'], $newsletter['id']] as $outletId) {
    $preparedNews = array_merge($preparedNews, NewsEditorial::apply('start', ['outlet_id' => $outletId], $preparedNews, $reviewConfiguration, 'editor', $now));
    $preparedNews = array_merge($preparedNews, NewsEditorial::apply('ready', ['outlet_id' => $outletId,
        'title' => ['de' => 'Redaktioneller Titel'], 'teaser' => ['de' => '<script>Review teaser</script>'], 'content' => ['de' => 'Prepared text']], $preparedNews, $reviewConfiguration, 'editor', $now));
}
$mutate = static fn($action, $news, $input = [], $actor = 'editor', $editor = true, $config = null) =>
    array_merge($news, NewsReview::apply($action, $input, $news, $config ?? $reviewConfiguration, $actor, $editor, $now));
$pendingNews = $mutate('request_review', $preparedNews);
$review = NewsReview::pending($pendingNews);
$response = ['review_id' => $review['id']];
check(count($review['contributions']) === 2 && $review['contributions'][0]['teaser']['de'] === '<script>Review teaser</script>', 'Send all ready outlet versions in one immutable review snapshot.');
check(!isset($review['contributions'][1]['teaser']), 'Snapshots contain only configured text fields.');
check($pendingNews['title'] === $preparedNews['title'] && $pendingNews['editorial_contributions'] === $preparedNews['editorial_contributions'], 'Review actions never change original or editorial texts.');
rejects(fn() => $mutate('request_review', $pendingNews), 'Only one review can be pending.');
rejects(fn() => $mutate('approve_review', $pendingNews, $response, 'other', true), 'Even another editor cannot confirm as the author.');
rejects(fn() => $mutate('request_review', $preparedNews, [], 'author', false), 'Authors cannot request editorial reviews.');
rejects(fn() => $mutate('approve_review', $pendingNews, ['review_id' => 'old'], 'author', false), 'Reject responses to the wrong round.');
rejects(fn() => NewsEditorial::apply('save', ['outlet_id' => $website['id']], $pendingNews, $reviewConfiguration, 'editor', $now), 'Lock versions while they await author review.');
$approvedNews = $mutate('approve_review', $pendingNews, $response, 'author', false);
check(NewsReview::pending($approvedNews) === null && array_column($approvedNews['outlet_requests'], 'status') === ['released', 'released'], 'The author can confirm the entire batch without any editorial permission.');
rejects(fn() => $mutate('approve_review', $approvedNews, $response, 'author', false), 'Prevent repeated approval.');
$releasedNews = $approvedNews;
check(array_column($approvedNews['outlet_requests'], 'release_mode') === ['author_approved', 'author_approved'], 'Author approval is the final release with no additional editorial action.');
check(NewsEditorial::prepared($approvedNews) === [], 'Confirmed outlets disappear from the list of pending editorial release actions.');
rejects(fn() => $mutate('release', $approvedNews), 'Already approved outlets do not need or send a second release.');
$direct = $mutate('release', $preparedNews);
check(array_column($direct['outlet_requests'], 'release_mode') === ['without_review', 'without_review'] && $direct['author_reviews'] === [], 'Allow release without requesting author review.');
rejects(fn() => $mutate('release', $pendingNews), 'Require an explicit confirmation before bypassing a pending response.');
$override = $mutate('release', $pendingNews, ['confirm_without_response' => '1']);
check($override['author_reviews'][0]['status'] === 'closed_without_response' && $override['outlet_requests'][0]['release_mode'] === 'without_response', 'Record the decision to close without a response.');
rejects(fn() => $mutate('approve_review', $override, $response, 'author', false), 'A late author response cannot overwrite an editorial release.');
foreach (['', ['bad'], str_repeat('x', 4001)] as $invalidComment) {
    rejects(fn() => $mutate('request_changes', $pendingNews, $response + ['comment' => $invalidComment], 'author', false), 'Change requests need a valid comment.');
}
$feedback = $mutate('request_changes', $pendingNews, $response + ['comment' => ' Please fix the number. '], 'author', false);
check($feedback['author_reviews'][0]['comment'] === 'Please fix the number.' && array_column($feedback['outlet_requests'], 'status') === ['in_progress', 'in_progress'], 'Feedback returns the submitted outlets to editing and preserves the comment.');
check($feedback['author_reviews'][0]['contributions'] === $review['contributions'], 'Keep the reviewed snapshot intact after feedback.');
$cancelled = $mutate('cancel_review', $pendingNews, $response);
check($cancelled['author_reviews'][0]['status'] === 'cancelled' && $cancelled['outlet_requests'][0]['status'] === 'ready', 'The editor can end a review to continue editing.');
$secondRound = $mutate('request_review', $cancelled);
check(count($secondRound['author_reviews']) === 2 && NewsReview::pending($secondRound)['id'] !== $review['id'], 'New rounds have independent IDs and retain earlier history.');
rejects(fn() => $mutate('approve_review', $secondRound, $response, 'author', false), 'An older round cannot approve a new one.');
$changedOriginal = array_replace($pendingNews, ['original_revision' => 2]);
check(NewsReview::matches($review, $changedOriginal), 'Original changes do not invalidate the submitted outlet texts.');
check($mutate('approve_review', $changedOriginal, $response, 'author', false)['outlet_requests'][0]['status'] === 'released', 'Allow final author approval despite later original edits.');
check($mutate('release', $changedOriginal, ['confirm_without_response' => '1'])['outlet_requests'][0]['status'] === 'released', 'The editorial team can complete a pending review despite later original edits.');
$changedOutlet = $pendingNews;
$changedOutlet['editorial_contributions'][0]['revision']++;
rejects(fn() => $mutate('approve_review', $changedOutlet, $response, 'author', false), 'An author never approves a different outlet text than the one submitted for review.');
$changedFields = $reviewConfiguration;
$changedFields['outlets'][0]['fields'] = ['content'];
check($mutate('approve_review', $pendingNews, $response, 'author', false, $changedFields)['outlet_requests'][0]['status'] === 'released', 'Configuration changes do not force a new review of unchanged outlet texts.');
$revised = $mutate('revise', $approvedNews, ['outlet_id' => $website['id']]);
check($revised['outlet_requests'][0]['status'] === 'in_progress' && !isset($revised['outlet_requests'][0]['author_review_id']), 'Revising an approved outlet removes its current confirmation.');
$revised = array_merge($revised, NewsEditorial::apply('save', ['outlet_id' => $website['id'], 'content' => ['de' => 'Revised text']], $revised, $reviewConfiguration, 'editor', $now));
$otherReleased = $revised;
check($otherReleased['outlet_requests'][0]['status'] === 'in_progress' && $otherReleased['outlet_requests'][1]['release_mode'] === 'author_approved', 'Revising one outlet does not invalidate an unchanged outlet confirmation.');
$onlyOneReady = $preparedNews;
$onlyOneReady['outlet_requests'][1]['status'] = 'in_progress';
$partialReview = $mutate('request_review', $onlyOneReady);
check(count(NewsReview::pending($partialReview)['contributions']) === 1 && $partialReview['outlet_requests'][1]['status'] === 'in_progress', 'Only prepared outlets join the batch; unfinished outlets stay open.');
$bson = static function ($value) use (&$bson) {
    if (!is_array($value)) return $value;
    $converted = array_map($bson, $value);
    return array_is_list($value) ? new MongoDB\Model\BSONArray($converted) : new MongoDB\Model\BSONDocument($converted);
};
check(NewsReview::read(['author_reviews' => $bson($pendingNews['author_reviews'])]) === $pendingNews['author_reviews'], 'Read review snapshots and language maps from nested MongoDB documents.');

$DB = new NewsReviewTestDB();
$Settings = new NewsRequestTestSettings([], $reviewConfiguration);
$news = $pendingNews;
$_SESSION['username'] = 'author';
ob_start(); include BASEPATH . '/pages/news/review.php'; $html = ob_get_clean();
check(str_contains($html, 'value="approve_review"') && str_contains($html, 'value="request_changes"'), 'The author can respond without an editorial role.');
check(str_contains($html, '&lt;script&gt;Review teaser&lt;/script&gt;') && !str_contains($html, '<script>Review teaser</script>'), 'Escape snapshot text in the author preview.');
$news = $preparedNews;
$news['outlet_requests'][0] = array_merge($news['outlet_requests'][0], ['status' => 'declined', 'decline_reason' => "Not suitable for this outlet.\n<script>Keep this as text</script>", 'declined_by' => 'editor', 'declined_at' => $now]);
ob_start(); include BASEPATH . '/pages/news/review.php'; $html = ob_get_clean();
check(str_contains($html, 'id="news-author-review"') && str_contains($html, 'Editorial reason') && str_contains($html, 'Not suitable for this outlet.'), 'Display declined outlets with their reason in the review section even without a review round.');
check(str_contains($html, '<details class="collapse-panel mb-20" open>') && str_contains($html, '&lt;script&gt;Keep this as text&lt;/script&gt;'), 'Keep rejection details expanded and escape the editorial comment.');
$outletSelectionMode = 'view';
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(!str_contains($html, 'Not suitable for this outlet.') && str_contains($html, NewsEditorial::statusLabel('declined')), 'Keep the rejection status in the outlet list, with its reason only in the review section.');
$_SESSION['username'] = 'unrelated';
ob_start(); include BASEPATH . '/pages/news/review.php'; $html = ob_get_clean();
check($html === '', 'Unrelated users never see review snapshots or feedback.');
echo "News author review checks passed.\n";
