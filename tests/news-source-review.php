<?php

// Run with: php tests/news-source-review.php (no database required).
require __DIR__ . '/fixtures/news.php';
require_once BASEPATH . '/php/Portfolio.php';
$configuration = $reviewConfiguration;
$configuration['original_portfolio'] = false;
$configuration['outlets'][0]['portfolio'] = true;
$configuration['outlets'][1]['portfolio'] = true;
$news = $preparedNews;
$news['id'] = (string) $news['_id'];
$news['original_revision'] = 6;
foreach ($news['editorial_contributions'] as &$contribution) $contribution['original_revision'] = 5;
unset($contribution);
$editorialInternalOnly = false;
$editorialOutlets = array_column($configuration['outlets'], null, 'id');
$editorialAction = '/crud/news/editorial/' . $news['_id'];
$editorialHiddenFields = static function () {};
ob_start(); include BASEPATH . '/pages/news/review-controls.php'; $html = ob_get_clean();
check(str_contains($html, 'value="request_review"') && str_contains($html, 'value="release"'), 'Later original edits do not hide or block review and direct release.');
check(!str_contains($html, 'source_reviewed'), 'No manual source-version acknowledgement is required.');
$news = array_merge($news, NewsReview::apply('request_review', [], $news, $configuration, 'editor', true, $now));
$review = NewsReview::pending($news);
$news['original_revision']++;
$news['content'] = 'The author has since updated the original.';
$news = array_merge($news, NewsReview::apply('approve_review', ['review_id' => $review['id']], $news, $configuration, 'author', false, $now));
check(NewsEditorial::prepared($news) === [], 'Author approval completes the workflow without another editorial release action.');
$output = Portfolio::newsOutput($news, $configuration, true);
check(count($output['outlets']) === 2 && !isset($output['content']), 'Confirmed outlets enter Portfolio immediately while the original source stays disabled.');
$before = $output['outlets'];
$news['content'] = 'Another independent original edit.';
$news['original_revision']++;
check(Portfolio::newsOutput($news, $configuration, true)['outlets'] === $before, 'Original edits never remove or overwrite already approved outlet outputs.');
ob_start(); include BASEPATH . '/pages/news/review-controls.php'; $html = ob_get_clean();
check(!str_contains($html, 'value="release"') && !str_contains($html, 'value="request_review"'), 'Do not ask for a redundant release after author approval.');
// Previously stored confirmations did not yet have release metadata.
foreach ($news['outlet_requests'] as &$request) unset($request['released_revision'], $request['released_by'], $request['released_at'], $request['release_mode']);
unset($request);
check(Portfolio::newsOutput($news, $configuration, true)['outlets'] === $before, 'Existing approved reviews work immediately without rewriting database records.');
$changedOutlet = $news;
$changedOutlet = $mutate('revise', $changedOutlet, ['outlet_id' => $website['id']]);
check(count(Portfolio::newsOutput($changedOutlet, $configuration, true)['outlets']) === 1, 'Actual outlet text changes still need an explicit new release.');
$news['outlet_requests'][0]['status'] = 'in_progress';
$news = array_merge($news, NewsEditorial::apply('ready', ['outlet_id' => $website['id']], $news, $configuration, 'editor', $now));
check($news['outlet_requests'][0]['status'] === 'ready', 'Prepared text can be completed without source-version checkboxes.');
echo "News source independence checks passed.\n";
