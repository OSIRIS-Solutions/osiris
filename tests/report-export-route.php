<?php
/** Exercise the real /reports callback with fixture data and real PHPWord writers. */
require_once dirname(__DIR__) . '/vendor/autoload.php';

function expect($condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

if (!isset($argv[1])) {
    foreach (['html', 'word', 'bson-html', 'bson-word', 'broken', 'warning', 'throwable', 'writer-failure'] as $scenario) {
        $process = proc_open([PHP_BINARY, __FILE__, $scenario], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);
        expect($status === 0, "$scenario failed: $output $errors");
        echo "PASS route $scenario\n";
    }
    exit;
}

$scenario = $argv[1];
$fixture = sys_get_temp_dir() . '/osiris-route-test-' . bin2hex(random_bytes(8));
mkdir($fixture . '/php', 0700, true);
define('BASEPATH', $fixture);
define('ROOTPATH', '');
function lang($en, $de = null) { return $en; }
class Route
{
    public static $export;
    public static function get(...$args) {}
    public static function post($path, $handler, ...$args) { if ($path === '/reports') self::$export = $handler; }
}
class DB { public static function to_ObjectID($id) { return $id; } }
class ReportTemplate
{
    public function getStyle() { return []; }
    public function applyReportStyle($word) {}
    public function addReportSection($word) { return $word->addSection(); }
    public function addReportFooter($section)
    {
        if ($GLOBALS['scenario'] !== 'writer-failure') return;
        // Force a serialization error after document construction has succeeded.
        $image = $GLOBALS['fixture'] . '/test.png';
        file_put_contents($image, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a3ioAAAAASUVORK5CYII='));
        $section->addImage($image);
        unlink($image);
    }
}
class Report
{
    public $steps;
    public $report = [];
    public function __construct($report)
    {
        $middle = match ($GLOBALS['scenario']) {
            'broken' => '<b>Broken<br>entry',
            default => 'KVK<br>chapter',
        };
        $this->steps = [
            ['type' => 'list', 'rows' => [['RGE chapter 1'], ['RGE chapter 2']]],
            ['type' => 'list', 'title' => 'KVK', 'rows' => $GLOBALS['scenario'] === 'broken'
                ? [[$middle], ['Later item in same block']] : [[$middle]]],
            ['type' => 'list', 'rows' => [['Following block']]],
            ['type' => 'list', 'rows' => [['', 'Year'], ['Table chapter', '2025']]],
            ['type' => 'list', 'rows' => []],
            ['type' => 'table'],
        ];
        if (str_starts_with($GLOBALS['scenario'], 'bson-')) {
            // DB::doc2Arr converts the outer BSONArray, leaving BSONDocument steps.
            $storedSteps = new \MongoDB\Model\BSONArray(array_map(
                static fn($step) => new \MongoDB\Model\BSONDocument($step),
                $this->steps
            ));
            $this->steps = $storedSteps->bsonSerialize();
        }
    }
    public function setTime(...$args) {}
    public function setVariables($vars) {}
    public function getTable($step)
    {
        // Empty grouping values can occur in both column headings and row labels.
        return [['Category', '<em>Leer</em>'], ['<em>Leer</em>', 0]];
    }
    public function prepareList($step)
    {
        if (($step['title'] ?? '') === 'KVK') {
            if ($GLOBALS['scenario'] === 'warning') trigger_error('Fixture warning', E_USER_WARNING);
            if ($GLOBALS['scenario'] === 'throwable') throw new TypeError('Fixture type error');
        }
        return $step['rows'];
    }
}
file_put_contents($fixture . '/php/init.php', '<?php $osiris = (object) ["adminReports" => new class { public function findOne($filter) { return ["title" => "Fixture report"]; } }]; echo "INCIDENTAL OUTPUT";');
file_put_contents($fixture . '/php/Report.php', '<?php');
file_put_contents($fixture . '/php/ReportTemplate.php', '<?php');
file_put_contents($fixture . '/php/ReportExport.php', '<?php require_once ' . var_export(dirname(__DIR__) . '/php/ReportExport.php', true) . ';');
require dirname(__DIR__) . '/routes/export.php';
require dirname(__DIR__) . '/routes/reports.php';
$_POST = ['id' => 'fixture', 'format' => str_ends_with($scenario, 'html') ? 'html' : 'word', 'startyear' => 2025];
$_SESSION = [];
$previousDisplay = ini_get('display_errors');
$previousReporting = error_reporting();
$previousBuffers = ob_get_level();
$previousTemps = glob(sys_get_temp_dir() . '/osiris-report-*');
try {
    ob_start();
    (Route::$export)();
    $body = ob_get_clean();
    expect(!str_contains($body, 'INCIDENTAL OUTPUT'), 'Incidental output leaked');
    expect(ini_get('display_errors') === $previousDisplay, 'display_errors was not restored');
    expect(error_reporting() === $previousReporting, 'error_reporting was changed');
    expect(ob_get_level() === $previousBuffers, 'Output buffer leaked');
    expect(glob(sys_get_temp_dir() . '/osiris-report-*') === $previousTemps, 'Temporary export file leaked');
    if ($scenario === 'writer-failure') {
        expect($body === '', 'Failed writer returned a partial document');
        expect(($_SESSION['msg_type'] ?? '') === 'error', 'Writer failure did not set a session error');
    } else {
        if (str_ends_with($scenario, 'html')) {
            expect(str_starts_with($body, '<!DOCTYPE html>'), 'HTML response has a corrupt prefix');
            $text = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $html = new DOMDocument();
            $html->loadHTML($body);
            $xpath = new DOMXPath($html);
            expect($xpath->query('//*[contains(@style, "italic") and normalize-space(.)="Leer"]')->length >= 2,
                'Empty table headings or row labels are not italic in HTML');
        } else {
            expect(str_starts_with($body, 'PK'), 'DOCX response has a corrupt prefix');
            $document = $fixture . '/result.docx';
            file_put_contents($document, $body);
            $zip = new ZipArchive();
            expect($zip->open($document) === true, 'Invalid DOCX ZIP');
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            $dom = new DOMDocument();
            expect($dom->loadXML($xml), 'Invalid DOCX document XML');
            $text = $dom->textContent;
            $xpath = new DOMXPath($dom);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
            expect($xpath->query('//w:tc//w:r[w:rPr/w:i]/w:t[text()="Leer"]')->length === 2,
                'Empty table headings or row labels are not italic in DOCX');
            expect($xpath->query('//w:tc//w:r[w:rPr/w:i and w:rPr/w:b]/w:t[text()="Leer"]')->length === 1,
                'Table heading lost its bold formatting');
            unlink($document);
        }
        foreach (['RGE chapter 1', 'RGE chapter 2', 'Following block', 'Table chapter', '2025', 'No data available'] as $part) {
            expect(str_contains($text, $part), "Missing $part");
        }
        expect(!str_contains($text, '<em>Leer</em>'), 'Empty table placeholder was exported as literal markup');
        if (in_array($scenario, ['warning', 'throwable'], true)) {
            expect(str_contains($text, 'Block 2 (KVK)'), 'Missing error marker');
            expect(str_contains($text, 'could not be fully exported'), 'Missing incomplete-block notice');
        } elseif ($scenario === 'broken') {
            expect(str_contains($text, 'Broken') && str_contains($text, 'entry'), 'Fallback lost the original text');
            expect(str_contains($text, 'plain text'), 'Missing formatting warning');
            expect(str_contains($text, 'Later item in same block'), 'A broken item skipped the rest of its block');
        } else {
            expect(str_contains($text, 'KVK') && str_contains($text, 'chapter'), 'Single-result list lost');
        }
        expect(($_SESSION['msg_type'] ?? null) === (in_array($scenario, ['broken', 'warning', 'throwable'], true) ? 'warning' : null), 'Incorrect session notification');
    }
} finally {
    while (ob_get_level() > $previousBuffers) ob_end_clean();
    foreach (glob($fixture . '/php/*') as $file) unlink($file);
    foreach (glob($fixture . '/*') as $file) if (is_file($file)) unlink($file);
    rmdir($fixture . '/php');
    rmdir($fixture);
}
