<?php

// Run with: php tests/news-reporting.php (no database required).
require __DIR__ . '/fixtures/news.php';
require_once BASEPATH . '/php/NewsExport.php';

$filters = NewsExport::filters(['channel' => $website['id'], 'from' => '2026-10-01', 'until' => '2026-10-22', 'language' => 'de'], $reviewConfiguration);
$reportNews = array_replace($approvedNews, ['date' => '2026-10-22', 'original_revision' => 99]);
$entries = NewsExport::entries([$reportNews], $reviewConfiguration, $filters);
check(count($entries) === 1 && $entries[0]['title'] === 'Redaktioneller Titel', 'Export a confirmed outlet even after the original changes.');
check(str_contains($entries[0]['content'], 'Prepared text'), 'Include the selected outlet body.');
check(NewsExport::entries([$pendingNews + ['date' => '2026-10-22']], $reviewConfiguration, $filters) === [], 'Never export drafts or pending approvals.');
$internal = array_replace($reportNews, ['internal_only' => true]);
check(NewsExport::entries([$internal], $reviewConfiguration, $filters) === [], 'Internal-only news cannot produce an outlet report.');
$inactive = $reviewConfiguration;
$inactive['original_portfolio'] = false;
$inactive['outlets'][0]['active'] = false;
$inactive['outlets'][0]['portfolio'] = false;
check(count(NewsExport::entries([$reportNews], $inactive, $filters)) === 1, 'Reports are independent of Portfolio and include released inactive channels.');
foreach (['2026-09-30', '2026-10-23'] as $date) {
    check(NewsExport::entries([array_replace($reportNews, ['date' => $date])], $reviewConfiguration, $filters) === [], 'Respect both date boundaries.');
}
check(count(NewsExport::entries([array_replace($reportNews, ['date' => '2026-10-01'])], $reviewConfiguration, $filters)) === 1, 'Include the first day.');
check(NewsExport::query($filters) === ['date' => ['$gte' => '2026-10-01', '$lt' => '2026-10-23']], 'Database query includes the entire last day.');
foreach ([['from' => '2026-02-30'], ['from' => '2026-10-23', 'until' => '2026-10-22'], ['channel' => 'unknown'], ['format' => 'pdf'], ['language' => 'fr'], ['from' => []]] as $bad) {
    rejects(fn() => NewsExport::filters($bad, $reviewConfiguration), 'Reject invalid export parameters.');
}
$originalFilters = NewsExport::filters(['language' => 'de'], $reviewConfiguration);
$originalReport = array_replace($internal, ['title' => 'Original & title', 'title_de' => '', 'content' => '<p><strong>Original</strong><script>unsafe()</script><img src="https://example.org/a"></p>']);
$originalEntries = NewsExport::entries([$originalReport], $inactive, $originalFilters);
check($originalEntries[0]['title'] === 'Original & title' && $originalEntries[0]['content'] === '<p><strong>Original</strong></p>', 'Original report includes internal news with language fallback and safe formatting.');
$html = NewsExport::html($originalEntries, 'Report <title>', 'de');
check(str_contains($html, 'Original &amp; title') && !str_contains($html, '<script>') && str_contains($html, '<strong>Original</strong>'), 'HTML export escapes labels and retains safe text formatting.');
$pixel = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
$withImage = array_replace($reportNews, ['image' => ['data' => new MongoDB\BSON\Binary($pixel), 'type' => 'gif']]);
$illustrated = NewsExport::entries([$withImage], $reviewConfiguration, $filters);
check(str_starts_with($illustrated[0]['image'], 'data:image/gif;base64,'), 'Include the shared cover image in outlet exports.');
check(isset(NewsExport::entries([$withImage], $inactive, $originalFilters)[0]['image']), 'Include the cover image in original exports too.');
$illustrated[0]['content'] = '<p><a href="https://example.org/details?a=1&amp;b=2">Details</a> and https://example.org/plain, plus www.example.org/news.</p><p><img src="data:image/gif;base64,' . $pixel . '" alt="Additional image"></p>';
$illustratedHtml = NewsExport::html($illustrated, 'Illustrated report', 'en');
check(substr_count($illustratedHtml, '<img ') === 2 && str_contains($illustratedHtml, 'href="https://example.org/plain"') && str_contains($illustratedHtml, 'href="https://www.example.org/news"'), 'HTML embeds cover and inline images and links bare web addresses.');
$word = NewsExport::word(array_merge($illustrated, $entries, $originalEntries, [['date' => '2026-10-22', 'content' => '<p>One<br>Two &amp; three</p><ul><li>Item</li></ul>']]), 'Report & title');
$file = tempnam(sys_get_temp_dir(), 'news-report-');
try {
    \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007')->save($file);
    $zip = new ZipArchive();
    check($zip->open($file) === true, 'Generate a valid Word ZIP container.');
    $xml = $zip->getFromName('word/document.xml');
    $relationships = $zip->getFromName('word/_rels/document.xml.rels');
    $media = array_filter(range(0, $zip->numFiles - 1), static fn($index) => str_starts_with($zip->getNameIndex($index), 'word/media/'));
    check(count($media) > 0 && substr_count($xml, '<v:imagedata ') >= 2, 'Word embeds both the shared cover and additional inline image.');
    check(str_contains($relationships, 'Target="https://example.org/details?a=1&amp;b=2"') && str_contains($relationships, 'Target="https://example.org/plain"') && str_contains($relationships, 'Target="https://www.example.org/news"'), 'Word stores usable hyperlink relationships, including query parameters and bare URLs.');
    check(str_contains($xml, '<w:hyperlink '), 'Word document text references the hyperlink relationships.');
    $zip->close();
    $doc = new DOMDocument();
    check($doc->loadXML($xml) && str_contains($xml, 'Prepared text') && str_contains($xml, 'Original &amp; title'), 'Word export contains well-formed text and formatting.');
} finally { unlink($file); }
echo "News export checks passed.\n";
