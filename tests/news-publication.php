<?php

// Run with: php tests/news-publication.php (no database required).
require __DIR__ . '/fixtures/news.php';
$testLanguage = 'en';
$_SESSION['username'] = 'author';
$configuration = $initial;
$disabled = array_replace($configuration, ['enabled' => false]);
check(!NewsEditorial::workflowAvailable($disabled), 'Configured outlets do not implicitly enable the workflow.');
check(!NewsEditorial::workflowAvailable(array_diff_key($configuration, ['enabled' => true])), 'Existing settings without a switch default to disabled.');
check(NewsEditorial::rows($disabled, []) === [], 'Hide outlet selection before activation.');
check(NewsEditorial::publicationFields(['visibility' => 'internal'], $disabled) === ['visibility' => 'internal'], 'Disabled workflow preserves internal news creation.');
check(NewsEditorial::publicationFields(['visibility' => 'public'], $disabled) === ['visibility' => 'public'], 'Disabled workflow preserves public news creation.');
rejects(fn() => NewsEditorial::reconcileRequests([$website['id']], $disabled, ['created_by' => 'author'], 'author', $now), 'Reject direct outlet requests while the workflow is disabled.');
rejects(fn() => NewsEditorial::apply('add', ['outlet_id' => $website['id']], ['visibility' => 'public'], $disabled, 'editor', $now), 'Reject starting editorial work on ordinary news while disabled.');
$existingWorkflow = ['publication_mode' => 'outlets', 'internal_only' => false];
check(NewsEditorial::workflowAvailable($disabled, $existingWorkflow), 'Disabling retains existing outlet workflows.');
check(NewsEditorial::publicationFields([], $disabled, $existingWorkflow) === $existingWorkflow, 'Disabling preserves existing publication rules.');
$legacyRequest = ['outlet_requests' => [['outlet_id' => $website['id'], 'status' => 'requested']]];
check(NewsEditorial::workflowAvailable($disabled, $legacyRequest), 'Existing requests remain editable even without an explicit publication mode.');
$continued = NewsEditorial::apply('start', ['outlet_id' => $website['id']], $legacyRequest, $disabled, 'editor', $now);
check($continued['outlet_requests'][0]['status'] === 'in_progress', 'Existing requests can actually progress after disabling.');
$released = array_replace($original, ['outlet_requests' => [['outlet_id' => $website['id'], 'status' => 'released']]]);
check(Portfolio::newsOutput($released, $disabled) === Portfolio::newsOutput($released, $configuration), 'Disabling leaves Portfolio releases unchanged.');
$newNews = ['_id' => str_repeat('a', 24), 'created_by' => 'author', 'publication_mode' => 'outlets', 'internal_only' => false];
check(NewsEditorial::publicationFields(['visibility' => 'internal'], $configuration) === ['publication_mode' => 'outlets', 'internal_only' => false], 'Legacy form values do not control new outlet news.');
check(NewsEditorial::publicationFields(['internal_only' => '1'], $configuration)['internal_only'], 'Persist an explicit internal-only override.');
$oldNews = ['_id' => str_repeat('b', 24), 'created_by' => 'author', 'visibility' => 'internal'];
check(NewsEditorial::internalOnly($oldNews), 'Old internal originals default to internal-only when they have no outlet requests.');
check(NewsEditorial::publicationFields([], $configuration, $oldNews) === ['visibility' => 'internal'], 'An ordinary save does not release legacy originals.');
check(NewsEditorial::publicationFields(['internal_only' => '1'], $configuration, array_merge($oldNews, ['visibility' => 'public']))['internal_only'], 'An explicit internal choice always wins, even without JavaScript.');

// Evaluate the actual MongoDB query against representative documents.
$settings = new NewsOutletTestSettings($configuration);
$filter = $settings->getNewsPortfolioFilter(['date' => ['$lte' => '2026-10-22']]);
$document = ['_id' => 'id', 'date' => '2026-10-22'];
check(publicationMatches($document + ['publication_mode' => 'outlets'], $filter), 'Outlet originals are eligible without a public visibility flag.');
check(!publicationMatches($document + ['publication_mode' => 'outlets', 'internal_only' => true], $filter), 'Internal-only always prevents output.');
check(!publicationMatches($document + ['visibility' => 'public', 'internal_only' => true], $filter), 'Internal-only also overrides a legacy public release.');
check(publicationMatches($document + ['visibility' => 'public'], $filter), 'Keep existing public originals.');
check(!publicationMatches($document + ['visibility' => 'internal'], $filter), 'Do not expose existing internal originals.');
check(!publicationMatches(['_id' => 'id', 'date' => '2026-10-23', 'publication_mode' => 'outlets'], $filter), 'Keep publication-date constraints.');
$settings->configuration['original_portfolio'] = false;
check(!publicationMatches($document + ['publication_mode' => 'outlets'], $settings->getNewsPortfolioFilter([])), 'The original source setting still controls output.');
check(!publicationMatches($document + ['visibility' => 'public'], $settings->getNewsPortfolioFilter(['_id' => 'id'])), 'Disabling the original source also protects direct detail requests.');

$Settings = new NewsRequestTestSettings(['news.add', 'news.edit-own'], $configuration);
$news = $newNews;
ob_start(); include BASEPATH . '/pages/news/publication.php'; $html = ob_get_clean();
check(!str_contains($html, 'news-visibility'), 'Remove visibility selection when outlets are configured.');
$outletSelectionMode = 'edit';
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(str_contains($html, 'id="news-internal-only"') && str_contains($html, '/js/news-requests.js'), 'Render the exclusive internal-only option beside the outlets.');
$Settings->configuration['original_portfolio'] = false;
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(!str_contains($html, 'id="news-internal-only"'), 'Hide the option for new ordinary news when the original source is disabled.');
$news['internal_only'] = true;
ob_start(); include BASEPATH . '/pages/news/outlets.php'; $html = ob_get_clean();
check(str_contains($html, 'id="news-internal-only" value="1" checked'), 'Keep existing internal-only choices visible after an admin configuration change.');
$Settings->configuration = ['original_portfolio' => true, 'outlets' => []];
$news = $oldNews;
ob_start(); include BASEPATH . '/pages/news/publication.php'; $html = ob_get_clean();
check(str_contains($html, 'id="news-visibility"'), 'Keep the existing form for installations without outlets.');
echo "News publication checks passed.\n";
