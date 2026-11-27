<?php
// Project language maps in connection editors, featured news cards and lookup APIs.
ob_start(); require __DIR__ . '/initiative-api.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
$Settings->enabled = true;
$Settings->permissions = ['projects.connect'];
$allowedScopes = ['projects.read'];
$portfolioAllowed = true;
$osiris->projects = new MemoryCollection();
$projectId = new MongoDB\BSON\ObjectId();
$osiris->projects->insertOne([
    '_id' => $projectId, 'public' => true, 'funding_number' => 'TEST',
    'name' => new MongoDB\Model\BSONDocument(['en' => 'Climate & water', 'de' => 'Klima & Wasser']),
    'title' => new MongoDB\Model\BSONDocument(['en' => 'Full <title>', 'de' => 'Voller Titel']),
]);
$plainId = new MongoDB\BSON\ObjectId();
$osiris->projects->insertOne(['_id' => $plainId, 'public' => true, 'funding_number' => 'TEST', 'name' => 'Plain project', 'title' => 'Plain title']);
$activity = ['projects' => [$projectId]];
$id = 'test-activity';
ob_start(); loadIsolated('components/connect-projects.php'); $html = ob_get_clean();
check(str_contains($html, 'Climate &amp; water') && str_contains($html, 'Full &lt;title&gt;') && str_contains($html, 'Plain project'), 'The project connection component renders BSON translations and plain text, escaping dropdown values.');

// Render the actual project section of each editor without unrelated feature widgets.
function projectObjectIds($values): array { return array_map([DB::class, 'to_ObjectID'], DB::doc2Arr($values)); }
foreach (['pages/activities/connections.php', 'pages/news/connections-editor.php'] as $file) {
    $source = file_get_contents(BASEPATH . '/' . $file);
    $start = strpos($source, "<?php if (\$Settings->featureEnabled('projects')) {");
    $end = strpos($source, "<?php if (\$Settings->featureEnabled(", $start + 10);
    $section = substr($source, $start, $end - $start);
    $section = str_replace('DB::to_ObjectIDs', 'projectObjectIds', $section);
    $news = ['projects' => [(string) $projectId]];
    $projects = $osiris->projects->find(['_id' => ['$in' => [$projectId]]])->toArray();
    ob_start(); eval('?>' . $section); $html = ob_get_clean();
    check(str_contains($html, 'Climate &amp; water') && str_contains($html, 'Full &lt;title&gt;'), "$file supports translated names and titles in both options and selected projects.");
    if (str_contains($file, 'news/')) {
        $dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
        check($xpath->query('//tr[@data-feature-type="project"]')->item(0)->getAttribute('data-feature-label') === 'Climate & water', 'The featured-project selector receives a text label instead of a language map.');
    }
}
$source = file_get_contents(BASEPATH . '/pages/news/view.php');
$start = strpos($source, "        case 'project':");
$end = strpos($source, "        case 'event':", $start);
$featuredId = (string) $projectId; $featuredCard = [];
eval("switch ('project') {" . substr($source, $start, $end - $start) . '}');
check($featuredCard['title'] === 'Climate & water' && $featuredCard['subtitle'] === 'Full <title>', 'A featured project resolves its title and subtitle before rendering the news card.');

// Exercise both actual lookup routes, preserving their existing language-map option.
$portfolio = str_replace("\r\n", "\n", file_get_contents(BASEPATH . '/routes/api/portfolio.php'));
$start = strpos($portfolio, 'function portfolio_localized_fields(');
eval(substr($portfolio, $start, strpos($portfolio, "\n}\n", $start) + 3 - $start));
foreach ([
    'routes/api/api.php' => ["Route::get('/api/projects-by-funding-number'", "Route::get('/api/journal'", '/api/projects-by-funding-number'],
    'routes/api/portfolio.php' => ["Route::get('/portfolio/projects'", "Route::get('/portfolio/persons'", '/portfolio/projects'],
] as $file => [$startMarker, $endMarker, $route]) {
    $source = file_get_contents(BASEPATH . '/' . $file);
    $start = strpos($source, $startMarker);
    $source = substr($source, $start, strpos($source, $endMarker, $start) - $start);
    $source = preg_replace('/(?:include|include_once)\s*\(?BASEPATH\s*\.\s*[\'"]\/php\/init.php[\'"]\)?;/', 'global $osiris, $Settings;', $source);
    eval($source);
    foreach (['en' => 'Climate & water', 'de' => 'Klima & Wasser', 'all' => null] as $languageCode => $expected) {
        $_GET = ['number' => 'TEST', 'lang' => $languageCode];
        ob_start(); Route::$routes['GET ' . $route][0](); $response = json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
        $name = $response['data'][0]['name'];
        check($languageCode === 'all' ? $name === ['en' => 'Climate & water', 'de' => 'Klima & Wasser'] : $name === $expected,
            "$route supports $languageCode without exposing a language map as a display string.");
    }
}
ob_end_clean(); echo $baselineOutput; echo 'Project localization checks passed (' . ($checks - $baselineChecks) . " checks).\n";
