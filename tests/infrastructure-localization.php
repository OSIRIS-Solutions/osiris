<?php
// Infrastructure language maps in linked cards and the news connection editor.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
$infrastructure = [
    '_id' => new MongoDB\BSON\ObjectId(), 'id' => 'TEST-LAB',
    'name' => new MongoDB\Model\BSONDocument(['en' => 'Climate & water lab', 'de' => 'Klimalabor']),
    'subtitle' => new MongoDB\Model\BSONDocument(['en' => 'Shared <facility>', 'de' => 'Gemeinsame Einrichtung']),
    'description' => new MongoDB\Model\BSONDocument(['en' => '<p>Research facility description</p>']),
];
foreach (['pages/activities/view.php', 'pages/news/view.php'] as $file) {
    $source = file_get_contents(BASEPATH . '/' . $file);
    $start = strpos($source, '<?php foreach ($infrastructures as $infrastructure): ?>');
    $end = strpos($source, '<?php endforeach; ?>', $start) + strlen('<?php endforeach; ?>');
    $infrastructures = [$infrastructure];
    ob_start(); eval('?>' . substr($source, $start, $end - $start)); $html = ob_get_clean();
    check(str_contains($html, 'Climate &amp; water lab') && str_contains($html, 'Shared &lt;facility&gt;'), "$file renders linked names and subtitles from BSON language maps without crashing.");
}
foreach (['pages/activities/activity.php', 'pages/profile.php', 'pages/organizations/view.php', 'pages/infrastructures/list.php'] as $file) {
    $source = file_get_contents(BASEPATH . '/' . $file);
    $start = strpos($source, 'if (!empty(localized($infra[\'subtitle\']');
    $end = strpos($source, '<?php } ?>', $start) + strlen('<?php } ?>');
    $section = '<?php ' . substr($source, $start, $end - $start);
    $infra = $infrastructure;
    ob_start(); eval('?>' . $section); $html = ob_get_clean();
    check(str_contains($html, 'Shared &lt;facility&gt;'), "$file resolves and escapes the subtitle.");
    $infra['subtitle'] = new MongoDB\Model\BSONDocument(['en' => '']);
    ob_start(); eval('?>' . $section); $html = ob_get_clean();
    check(str_contains($html, 'Research facility description') && !str_contains($html, '<p>'), "$file uses a plain description preview when the resolved subtitle is empty.");
}
// Render the entire infrastructure section, including selected entries and featured labels.
$Settings = new class {
    public function featureEnabled($key) { return $key === 'infrastructures'; }
    public function infrastructureLabel() { return 'Infrastructures'; }
};
$osiris->infrastructures = new MemoryCollection();
$osiris->infrastructures->insertOne($infrastructure);
$news = ['infrastructures' => ['TEST-LAB']];
$source = file_get_contents(BASEPATH . '/pages/news/connections-editor.php');
$start = strpos($source, '<?php if ($Settings->featureEnabled(\'infrastructures\')) {');
$end = strpos($source, '<?php $featured =', $start);
ob_start(); eval('?>' . substr($source, $start, $end - $start)); $html = ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML($html); $xpath = new DOMXPath($dom);
check(str_contains($html, 'Climate &amp; water lab') && str_contains($html, 'Shared &lt;facility&gt;')
    && $xpath->query('//tr[@data-feature-type="infrastructure"]')->item(0)->getAttribute('data-feature-label') === 'Climate & water lab',
    'The news connection editor resolves infrastructure options, selected names, subtitles and featured labels.');
ob_end_clean(); echo $baselineOutput; echo 'Infrastructure localization checks passed (' . ($checks - $baselineChecks) . " checks).\n";
