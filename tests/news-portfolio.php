<?php

// Run with: php tests/news-portfolio.php (no database required).
require __DIR__ . '/fixtures/news.php';
require_once BASEPATH . '/php/Portfolio.php';
$settings = new NewsOutletTestSettings($portfolioConfiguration);
$filter = $settings->getNewsPortfolioFilter(['date' => ['$lte' => '2026-10-22'], 'persons' => str_repeat('b', 24)]);
check(publicationMatches($portfolioNews, $filter), 'Find released Portfolio outlets even if the original source is disabled.');
$detail = Portfolio::newsOutput($portfolioNews, $portfolioConfiguration, true);
check(count($detail['outlets']) === 1 && $detail['outlets'][0]['id'] === $website['id'], 'Return only released outlets explicitly enabled for Portfolio.');
check($detail['outlets'][0]['title'] === ['en' => 'Public website title', 'de' => 'Öffentlicher Titel'] && $detail['outlets'][0]['teaser']['de'] === 'Öffentlicher Teaser', 'Keep outlet text localized with both language keys.');
check($detail['outlets'][0]['content']['en'] === '<p><strong>Public text</strong></p>', 'Preserve safe rich text while excluding executable HTML.');
check(!isset($detail['title'], $detail['teaser'], $detail['content'], $detail['title_de']), 'Do not expose original text when original output is disabled.');
check(!str_contains(json_encode($detail), 'Private') && !isset($detail['outlet_requests'], $detail['editorial_history'], $detail['author_reviews']), 'Do not leak editorial state, comments or unpublished outlet content.');
check($detail['persons'] === $portfolioNews['persons'] && $detail['activities'] === $portfolioNews['activities'] && !isset($detail['outlets'][0]['persons']), 'Keep connections shared at news level.');
$list = Portfolio::newsListing([$portfolioNews], $portfolioConfiguration);
check(count($list) === 1 && $list[0]['id'] === $portfolioNews['id'] && !isset($list[0]['content'], $list[0]['outlets'][0]['content']), 'Keep existing list IDs and summary-only responses.');
foreach (['requested', 'in_progress', 'ready', 'pending_approval', 'declined', 'withdrawn'] as $status) {
    $draft = $portfolioNews;
    $draft['outlet_requests'][0]['status'] = $status;
    check(!publicationMatches($draft, $filter) && Portfolio::newsOutput($draft, $portfolioConfiguration, true) === null, 'Never expose ' . $status . ' outlets; match release and enabled ID on the same request.');
}
$internal = array_replace($portfolioNews, ['internal_only' => true]);
check(!publicationMatches($internal, $filter) && Portfolio::newsOutput($internal, $portfolioConfiguration, true) === null, 'Internal-only overrides a released outlet and all source settings.');
$future = array_replace($portfolioNews, ['date' => '2999-01-01']);
check(!publicationMatches($future, $filter), 'Keep publication-date restrictions on lists.');
check(!publicationMatches($portfolioNews, $settings->getNewsPortfolioFilter(['persons' => str_repeat('d', 24)])), 'Keep relationship filters when adding outlet eligibility.');
$changedDraft = $portfolioNews;
$changedDraft['outlet_requests'][0]['status'] = 'in_progress';
check(Portfolio::newsOutput($changedDraft, $portfolioConfiguration, true) === null && count(Portfolio::newsListing([$changedDraft, $portfolioNews], $portfolioConfiguration)) === 1, 'An outlet explicitly reopened for editing cannot leak its draft or its parent item.');
$inactive = $portfolioConfiguration;
$inactive['outlets'][0]['active'] = false;
check(Portfolio::newsOutput($portfolioNews, $inactive, true) !== null, 'Deactivation for new requests retains already released output.');
$disabled = $portfolioConfiguration;
$disabled['outlets'][0]['portfolio'] = false;
check(Portfolio::newsOutput($portfolioNews, $disabled, true) === null, 'Disabling Portfolio output also protects direct detail requests.');
$contentOnly = $portfolioConfiguration;
$contentOnly['outlets'][0]['fields'] = ['content'];
$social = Portfolio::newsOutput($portfolioNews, $contentOnly, true)['outlets'][0];
check(!isset($social['title'], $social['teaser']) && isset($social['content']), 'Output only the configured text fields.');
$originalEnabled = $portfolioConfiguration;
$originalEnabled['original_portfolio'] = true;
$withOriginal = Portfolio::newsOutput($portfolioNews, $originalEnabled, true);
check($withOriginal['title'] === $portfolioNews['title'] && $withOriginal['content'] === $portfolioNews['content'] && count($withOriginal['outlets']) === 1, 'Output originals and released outlets independently when both are enabled.');
$legacy = $portfolioNews;
unset($legacy['publication_mode']);
$legacy['visibility'] = 'internal';
check(!isset(Portfolio::newsOutput($legacy, $originalEnabled, true)['content']), 'Do not release a legacy internal original through a released outlet.');
$legacy['visibility'] = 'public';
$legacy['outlet_requests'] = [];
check(Portfolio::newsOutput($legacy, $originalEnabled, true)['content'] === $legacy['content'], 'Keep classic public originals without outlets working.');
$updatedOriginal = array_replace($portfolioNews, ['original_revision' => 2, 'content' => 'Updated private original']);
check(Portfolio::newsOutput($updatedOriginal, $portfolioConfiguration, true)['outlets'][0]['content'] === $detail['outlets'][0]['content'], 'Later original edits do not overwrite independently released outlet texts.');
$bson = new MongoDB\Model\BSONDocument($portfolioNews);
check(Portfolio::newsListing(new MongoDB\Model\BSONArray([$bson]), $portfolioConfiguration) === $list, 'Read actual MongoDB document and cursor-compatible structures.');
echo "News Portfolio checks passed.\n";
