<?php
/** Run with: php tests/report-export.php (no database required). */
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/php/ReportExport.php';

function lang($english, $german = null) { return $english; }
function expect($condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

\PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);

$cases = [
    'HTML breaks' => ['First<br>Second<BR >Third<br/>Fourth<br />Last', ['First', 'Second', 'Third', 'Fourth', 'Last'], false],
    'entities and formatting' => ['<b>Research</b> &amp; A & B&nbsp;&copy; &lt;1&gt;', ['Research', 'A & B', '©', '<1>'], false],
    'quoted attributes' => ['First<br title="a > b">Last', ['First', 'Last'], false],
    'broken markup' => ['<b>Broken<br>entry', ['Broken', 'entry', 'plain text'], true],
    'PHPWord parser warning' => ['Encoded &#60; bracket', ['Encoded < bracket', 'plain text'], true],
    'invalid XML controls' => ["Before\x01After", ['BeforeAfter', 'plain text'], true],
    'empty content' => ['', [], false],
];

foreach ($cases as $label => [$html, $expected, $hasIssue]) {
    $word = new \PhpOffice\PhpWord\PhpWord();
    $section = $word->addSection();
    $export = new ReportExport();
    $export->setBlock(2, ['type' => 'list', 'title' => 'Chapters']);
    ob_start();
    $export->addHtml($section->addTextRun(), $html);
    expect(ob_get_clean() === '', "$label: parser output leaked");
    expect((bool) $export->getIssues() === $hasIssue, "$label: incorrect diagnostics");
    foreach (['HTML', 'Word2007'] as $format) {
        $file = tempnam(sys_get_temp_dir(), 'osiris-test-');
        try {
            $export->checked(static function () use ($word, $format, $file) {
                \PhpOffice\PhpWord\IOFactory::createWriter($word, $format)->save($file);
            });
            if ($format === 'Word2007') {
                $zip = new ZipArchive();
                expect($zip->open($file) === true, "$label: invalid DOCX");
                $content = $zip->getFromName('word/document.xml');
                $zip->close();
                $dom = new DOMDocument();
                expect($dom->loadXML($content), "$label: invalid document XML");
                $xpath = new DOMXPath($dom);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                $text = '';
                foreach ($xpath->query('//w:t') as $node) $text .= $node->textContent;
                if ($label === 'HTML breaks') expect($xpath->query('//w:br')->length === 4, 'Line breaks lost in DOCX');
                if ($label === 'entities and formatting') expect($xpath->query('//w:b')->length > 0, 'Bold formatting lost');
            } else {
                $content = file_get_contents($file);
                $text = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($label === 'HTML breaks') expect(preg_match_all('~<br\s*/?>~i', $content) === 4, 'Line breaks lost in HTML');
            }
            foreach ($expected as $part) expect(str_contains($text, $part), "$label/$format: missing $part");
            if ($hasIssue) expect(str_contains($text, 'Block 2 (Chapters)'), "$label: missing block context");
        } finally {
            unlink($file);
        }
    }
    echo "PASS $label (HTML and DOCX)\n";
}

// A warning must be catchable even if the caller's error_reporting hides it.
$previousReporting = error_reporting(E_ERROR);
$sentinel = static fn() => true;
set_error_handler($sentinel);
try {
    $export = new ReportExport();
    try {
        $export->checked(static function () { trigger_error('Simulated export warning', E_USER_WARNING); });
        throw new RuntimeException('Warning was not converted into an exception');
    } catch (ErrorException $error) {
        expect($error->getMessage() === 'Simulated export warning', 'Incorrect warning captured');
    }
    $restoredHandler = set_error_handler($sentinel);
    expect($restoredHandler === $sentinel, 'Previous error handler was not restored');
    restore_error_handler();
} finally {
    restore_error_handler();
    error_reporting($previousReporting);
}
echo "PASS warning capture and handler restoration\n";

libxml_use_internal_errors(false);
$export->addHtml((new \PhpOffice\PhpWord\PhpWord())->addSection(), '<b>Broken');
expect(libxml_use_internal_errors() === false, 'libxml error handling was not restored');
echo "PASS libxml state restoration\n";
