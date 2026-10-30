<?php
// Standalone regression checks: php tests/render-author-units.php
// Load the production functions without init.php (no database/session required).
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
class DB
{
    public static $people = [];
    public static $calls = 0;
    public static function doc2Arr($value) { return $value ?? []; }
    public function getPerson($user = null)
    {
        self::$calls++;
        if ($user === null) throw new RuntimeException('Unexpected session-user lookup');
        return self::$people[$user] ?? [];
    }
}
$Groups = new class {
    public function getParents($unit, $includeRoot = false) { return ['ROOT', $unit]; }
};
function loadFunctionSection($file, $first, $next)
{
    $source = file_get_contents(__DIR__ . '/../' . $file);
    $start = strpos($source, 'function ' . $first . '(');
    $end = strpos($source, 'function ' . $next . '(', $start);
    eval(substr($source, $start, $end - $start));
}
loadFunctionSection('php/Render.php', 'renderAuthorUnits', 'renderAuthorUnitsMany');
loadFunctionSection('php/_config.php', 'valueFromDateArray', 'fromToDate');
$checks = 0;
function check($label, $actual, $expected)
{
    global $checks;
    if ($actual !== $expected) {
        throw new RuntimeException($label . ': ' . json_encode($actual) . ' != ' . json_encode($expected));
    }
    $checks++;
}
$a = ['first' => 'Ada', 'last' => 'Test', 'aoi' => true, 'user' => 'ada'];
$date = ['start_date' => '2025-01-01'];
$manual = $a + ['manually' => true, 'units' => ['OLD']];
$old = $date + ['authors' => [$manual]];
DB::$people['ada']['units'] = [['unit' => 'PROFILE', 'scientific' => true, 'start' => '2020-01-01']];
foreach ([['NEW'], [], null] as $units) {
    $out = renderAuthorUnits($date + ['authors' => [array_replace($manual, ['units' => $units])]], $old);
    check('Current manual input wins', $out['authors'][0]['units'], $units ?? []);
}
$out = renderAuthorUnits($old);
check('Manual rendering without old document', $out['authors'][0]['units'], ['OLD']);
$first = renderAuthorUnits($date + ['authors' => [$a]], $old);
check('Manual flag survives', $first['authors'][0]['manually'], true);
$second = renderAuthorUnits($date + ['authors' => [$a]], $first);
check('Second save preserves units', $second['authors'][0]['units'], ['OLD']);
$out = renderAuthorUnits($date + ['authors' => [$a + ['manually' => false]]], $old);
check('Explicit false restores automatic units', $out['authors'][0]['units'], ['PROFILE']);
$editor = ['first' => 'Ed', 'last' => 'Test', 'aoi' => true, 'manually' => true, 'units' => ['EDITOR']];
$both = $old + ['editors' => [$editor]];
$out = renderAuthorUnits(['authors' => [$a]], $both);
check('Other roles survive partial update', $out['units'], ['OLD', 'EDITOR', 'ROOT']);
$out = renderAuthorUnits(['authors' => []], $both);
check('Explicit empty role removes only its units', $out['units'], ['EDITOR', 'ROOT']);
$out = renderAuthorUnits(['authors' => []], $old);
check('Last role removal clears aggregate', $out['units'], []);
DB::$people['ada']['units'] = [
    ['unit' => 'EXPIRED', 'scientific' => true, 'end' => '2020-01-01'],
    ['unit' => 'OPEN', 'scientific' => true],
    ['unit' => 'BOUNDARY', 'scientific' => true, 'start' => '2025-01-01', 'end' => '2025-01-01'],
    ['unit' => 'FUTURE', 'scientific' => true, 'start' => '2026-01-01'],
    ['unit' => 'ADMIN', 'scientific' => false],
    ['unit' => 'INVALID', 'scientific' => true, 'end' => 'bad-date'],
];
$out = renderAuthorUnits($date + ['authors' => [$a]]);
check('Membership bounds and scientific filter', $out['authors'][0]['units'], ['OPEN', 'BOUNDARY']);
DB::$people['ada']['units'] = [
    ['unit' => 'OLD_YEAR', 'scientific' => true, 'end' => '2025-12-31'],
    ['unit' => 'NEW_YEAR', 'scientific' => true, 'start' => '2026-01-01'],
];
$autoOld = $date + ['authors' => [$a]];
foreach ([['year' => 2026], ['start' => ['year' => 2026]], ['start_date' => '2026-01-01']] as $change) {
    $out = renderAuthorUnits($change, $autoOld);
    check('Date-only change updates absent role', $out['authors'][0]['units'], ['NEW_YEAR']);
}
$out = renderAuthorUnits(['year' => 2026, 'start_date' => '2025-01-01', 'authors' => [$a]]);
check('Raw date overrides stale materialized date', $out['authors'][0]['units'], ['NEW_YEAR']);
$out = renderAuthorUnits(['month' => 2], ['year' => 2026, 'month' => 1, 'authors' => [$a]]);
check('Partial flat date retains year', $out['start_date'], '2026-02-01');
$out = renderAuthorUnits(['start_date' => 'bad-date', 'authors' => [$a]]);
check('Invalid date does not assign epoch units', $out['units'], []);
DB::$calls = 0;
$external = array_replace($a, ['user' => null]);
$out = renderAuthorUnits($date + ['authors' => [$external]]);
check('No account does not call getPerson', DB::$calls, 0);
$out = renderAuthorUnits($date + ['authors' => [$external + ['manually' => true, 'units' => ['EXTERNAL']]]]);
check('Manual affiliation without account', $out['authors'][0]['units'], ['EXTERNAL']);
$out = renderAuthorUnits($date + ['authors' => [array_replace($a, ['last' => 'Renamed'])]], $old);
check('Stable account survives rename', $out['authors'][0]['units'], ['OLD']);
$out = renderAuthorUnits($date + ['authors' => [$external]], $old);
check('Unique name fallback', $out['authors'][0]['units'], ['OLD']);
$out = renderAuthorUnits($date + ['authors' => [array_replace($a, ['user' => 'different'])]], $old);
check('Different account cannot inherit by name', $out['authors'][0]['units'], []);
$ambiguous = $date + ['authors' => [$manual, array_replace($manual, ['user' => 'other', 'units' => ['OTHER']])]];
$out = renderAuthorUnits($date + ['authors' => [$external]], $ambiguous);
check('Ambiguous old names do not transfer units', $out['authors'][0]['units'], []);
$out = renderAuthorUnits($date + ['authors' => [$external, $external]], $old);
check('Ambiguous current names do not transfer units', $out['authors'][0]['units'], []);
$out = renderAuthorUnits($date + ['authors' => [array_replace($a, ['aoi' => false])]]);
check('Unaffiliated automatic person has no units', $out['units'], []);
$out = renderAuthorUnits($date + ['persons' => [array_replace($a, ['aoi' => false])]]);
check('Project persons do not require aoi', $out['persons'][0]['units'], ['OLD_YEAR']);
echo "Passed $checks regression checks.\n";
