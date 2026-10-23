<?php

// Run with: php tests/news-outlets.php (no database required).
require __DIR__ . '/fixtures/news.php';

$defaults = NewsOutlets::read(null);
check($defaults === ['original_portfolio' => true, 'outlets' => []], 'Preserve existing Portfolio behavior.');
$initial = NewsOutlets::normalize([
    'original_portfolio' => '1',
    'outlets' => [
        ['name' => ['en' => ' Website ', 'de' => ' Webseite '], 'description' => ['en' => 'For the institute website', 'de' => 'Für die Institutswebseite'], 'active' => '1', 'portfolio' => '1'],
        ['name' => ['en' => 'Newsletter', 'de' => 'Rundbrief'], 'active' => '1', 'portfolio' => '0'],
    ],
], $defaults);
[$website, $newsletter] = $initial['outlets'];
check(!array_key_exists('enabled', $initial), 'Outlet configuration does not store feature activation.');
check($website['name'] === ['en' => 'Website', 'de' => 'Webseite'], 'Trim names.');
check((bool) preg_match('/^[a-f0-9]{24}$/', $website['id']), 'Generate stable identifiers on the server.');
check($website['id'] !== $newsletter['id'], 'Use different identifiers.');
check($website['description'] === ['en' => 'For the institute website', 'de' => 'Für die Institutswebseite'], 'Store localized descriptions.');
check($newsletter['description'] === ['en' => '', 'de' => ''], 'Include both language keys for optional fields.');
check(!isset($website['name_de']) && !isset($website['description_de']), 'Use language keys instead of separate translation fields.');
check($website['portfolio'] && !$newsletter['portfolio'], 'Portfolio output is independent per outlet.');
check($website['fields'] === ['title', 'content'], 'Existing outlets default to the previous editorial fields.');
$fieldConfiguration = NewsOutlets::normalize(['outlets' => [
    array_replace($website, ['fields' => ['content', 'teaser', 'title', 'content']]),
    array_replace($newsletter, ['fields' => ['content']]),
]], $initial);
check($fieldConfiguration['outlets'][0]['fields'] === ['title', 'teaser', 'content'], 'Store selected text fields in display order without duplicates.');
check($fieldConfiguration['outlets'][1]['fields'] === ['content'], 'Allow an outlet with only a contribution.');
foreach (['', [], ['unknown'], [['content']]] as $invalidFields) {
    rejects(fn() => NewsOutlets::normalize(['outlets' => [array_replace($website, ['fields' => $invalidFields]), $newsletter]], $initial), 'Reject empty or invalid editorial field selections.');
}
$legacyWebsite = $website;
unset($legacyWebsite['fields']);
check(NewsOutlets::read(['outlets' => [$legacyWebsite]])['outlets'][0]['fields'] === ['title', 'content'], 'Read older saved configuration without a migration.');
check(NewsOutlets::fields(['fields' => new MongoDB\Model\BSONArray(['teaser', 'content'])]) === ['teaser', 'content'], 'Read MongoDB field lists.');

$updated = NewsOutlets::normalize([
    'original_portfolio' => '0',
    'outlets' => [
        array_merge($newsletter, ['name' => ['en' => 'Monthly newsletter', 'de' => 'Monatlicher Rundbrief']]),
        array_merge($website, ['active' => '0']),
    ],
], $initial);
check($updated['outlets'][0]['id'] === $newsletter['id'], 'Renaming and sorting retain IDs.');
check($updated['outlets'][0]['order'] === 0 && $updated['outlets'][1]['order'] === 1, 'Store the selected order.');
check(!$updated['original_portfolio'], 'Allow only editorial outlets as Portfolio sources.');
check(!$updated['outlets'][1]['active'] && $updated['outlets'][1]['portfolio'], 'Deactivation for new requests retains output configuration.');
check(NewsOutlets::revision($updated) !== NewsOutlets::revision($initial), 'Detect stale forms.');

rejects(fn() => NewsOutlets::normalize(['outlets' => [$website]], $initial), 'Reject deletion of saved outlets.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [$website, $website, $newsletter]], $initial), 'Reject duplicate IDs.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['id' => str_repeat('a', 24), 'name' => ['en' => 'Forged']]]], $defaults), 'Reject unknown IDs.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => '  ']]]], $defaults), 'Require names.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => ['Invalid']]]]], $defaults), 'Reject non-text values.');
rejects(fn() => NewsOutlets::normalize(['outlets' => 'Invalid'], $defaults), 'Reject malformed lists.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test'], 'portfolio' => 'false']]], $defaults), 'Reject ambiguous boolean strings.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => str_repeat('a', 121)]]]], $defaults), 'Enforce name limits.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test'], 'description' => ['de' => str_repeat('a', 501)]]]], $defaults), 'Enforce description limits.');

rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => 'Legacy scalar']]], $defaults), 'Require localized name fields.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test'], 'description' => 'Legacy scalar']]], $defaults), 'Require localized description fields.');
rejects(fn() => NewsOutlets::normalize(['outlets' => [['name' => ['en' => 'Test', 'de' => str_repeat('a', 121)]]]], $defaults), 'Validate each name translation.');

$mongoValue = new MongoDB\Model\BSONDocument([
    'original_portfolio' => false,
    'outlets' => new MongoDB\Model\BSONArray([
        new MongoDB\Model\BSONDocument(array_merge($website, [
            'name' => new MongoDB\Model\BSONDocument($website['name']),
            'description' => new MongoDB\Model\BSONDocument($website['description']),
        ])),
        new MongoDB\Model\BSONDocument($newsletter),
    ]),
]);
check(NewsOutlets::read($mongoValue) === ['original_portfolio' => false, 'outlets' => [$website, $newsletter]], 'Read nested MongoDB documents.');

$settings = new NewsOutletTestSettings(null);
$filter = ['visibility' => 'public', 'date' => ['$lte' => '2026-10-22']];
check($settings->getNewsPortfolioFilter($filter)['$and'][0] === ['date' => $filter['date']], 'The shared source filter retains date constraints.');
$settings->configuration = $updated;
$blocked = $settings->getNewsPortfolioFilter($filter);
check($blocked['$and'][0] === ['date' => $filter['date']] && $blocked['$and'][2]['$or'][0]['outlet_requests']['$elemMatch']['status'] === ['$in' => ['approved', 'released']], 'Keep date constraints and admit only released Portfolio outlets when original output is disabled.');
$detail = ['_id' => 'existing-id', 'visibility' => 'public'];
check($settings->getNewsPortfolioFilter($detail)['$and'][0] === ['_id' => 'existing-id'], 'Keep direct detail filters independently of the original source.');
$settings->configuration = $initial;
check($settings->getNewsPortfolioFilter($detail)['$and'][0] === ['_id' => 'existing-id'], 'The shared source filter retains direct detail constraints.');

// Verify the form reads and submits the same localized structure as persistence.
$configuration = NewsOutlets::read($mongoValue);
$revision = NewsOutlets::revision($configuration);
$_SESSION['news_outlets_csrf'] = 'test';
foreach (['en' => 'Website', 'de' => 'Webseite'] as $testLanguage => $expectedTitle) {
    ob_start();
    include BASEPATH . '/pages/admin/news.php';
    $html = ob_get_clean();
    check(!str_contains($html, 'configuration[enabled]'), 'Feature activation belongs exclusively in the general feature settings.');
    check(str_contains($html, 'data-outlet-title>' . $expectedTitle . '</h3>'), 'Render the localized outlet title.');
    check(str_contains($html, 'name="configuration[outlets][0][name][en]" value="Website"'), 'Render the English name field.');
    check(str_contains($html, 'name="configuration[outlets][0][name][de]" value="Webseite"'), 'Render the German name field.');
    check(str_contains($html, 'name="configuration[outlets][0][description][de]" rows="2" maxlength="500">Für die Institutswebseite</textarea>'), 'Render the localized description.');
    check(str_contains($html, 'name="configuration[outlets][0][fields][]" value="content" checked'), 'Render selected editorial fields.');
    check(str_contains($html, 'name="configuration[outlets][0][fields][]" value="teaser" >'), 'Leave new teaser fields optional for existing outlets.');
}

echo "News outlet checks passed.\n";
