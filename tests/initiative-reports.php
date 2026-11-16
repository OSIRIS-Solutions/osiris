<?php
// Report field selection, name resolution, grouping and download scoping; no live database.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/Report.php');
$initiativeId = 'abcdefabcdefabcdefabcdef';
$objectiveId = '123456789012345678901234';
$initiativeNames = [$initiativeId => 'Climate initiative'];
$objectiveNames = [$objectiveId => 'Climate initiative – 26-01: Resilience'];
foreach (['activity_fields.php', 'project_fields.php', 'event_fields.php'] as $file) {
    $source = file_get_contents(BASEPATH . '/php/' . $file);
    $start = strpos($source, "foreach (['initiatives' => \$initiativeNames, 'objectives' => \$objectiveNames]");
    $end = strpos($source, "\n            }", $start) + strlen("\n            }");
    $FIELDS = [];
    eval(substr($source, $start, $end - $start));
    check(count($FIELDS) === 2 && in_array('aggregate', $FIELDS[0]['usage'], true) && in_array('aggregate', $FIELDS[1]['usage'], true), "$file exposes both relationships for grouping.");
}
$source = file_get_contents(BASEPATH . '/pages/report-builder.php');
$start = strpos($source, '$aggregate_filter =');
eval(substr($source, $start, strpos($source, '$data_fields =', $start) - $start));
check($additional_filter($FIELDS[0]) && $additional_filter($FIELDS[1]) && !$sort_filter($FIELDS[0]), 'Relationship lists are selectable as additional columns without becoming sort fields.');
class InitiativeReportCollection extends MemoryCollection
{
    public array $pipeline = [];
    public array $rows = [];
    public function aggregate(array $pipeline) {
        $this->pipeline = $pipeline;
        return new class($this->rows) {
            public function __construct(private array $rows) {}
            public function toArray() { return $this->rows; }
        };
    }
}
class InitiativeListReport extends Report
{
    public function getList($item) {
        global $initiativeId, $objectiveId;
        return [
            ['First publication', [$initiativeId], [$objectiveId]],
            ['Second publication', new MongoDB\Model\BSONArray([$initiativeId]), new MongoDB\Model\BSONArray([$objectiveId])],
        ];
    }
}
$report = (new ReflectionClass(InitiativeListReport::class))->newInstanceWithoutConstructor();
$report->fields = ['activities' => array_column($FIELDS, null, 'id')];
(new ReflectionProperty(Report::class, 'DB'))->setValue($report, $DB);
$list = $report->prepareList(['field' => ['initiatives', 'objectives']]);
check($list[1][1] === 'Climate initiative' && $list[1][2] === $objectiveNames[$objectiveId] && $list[2] === ['Second publication', $list[1][1], $list[1][2]], 'Additional report columns resolve both PHP and BSON arrays to names rather than IDs.');
$osiris->activities = new InitiativeReportCollection();
$osiris->activities->rows = [['activity' => $initiativeId, 'count' => 2]];
$table = $report->getTable(['filter' => '{}', 'aggregate' => 'initiatives']);
check($table[1] === ['Climate initiative', 2] && in_array(['$unwind' => '$initiatives'], $osiris->activities->pipeline, true), 'Grouping unwinds relationship lists and resolves the resulting names.');
$osiris->activities->rows = [['activity' => [$initiativeId, $objectiveId], 'count' => 2]];
$table = $report->getTable(['filter' => '{}', 'aggregate' => 'initiatives', 'aggregate2' => 'objectives']);
check($table[0][1] === $objectiveNames[$objectiveId] && $table[1] === ['Climate initiative', 2] && in_array(['$unwind' => '$objectives'], $osiris->activities->pipeline, true), 'Two-dimensional report tables resolve both axes.');
$source = file_get_contents(BASEPATH . '/routes/export.php');
$start = strpos($source, "    foreach (['initiative' => 'initiatives', 'objective' => 'objectives']");
$downloadFilter = substr($source, $start, strpos($source, "    if (isset(\$params['workflow'])", $start) - $start);
$params = ['initiative' => strtoupper($initiativeId), 'objective' => $objectiveId];
$filter = []; $filename = 'test';
eval($downloadFilter);
check($filter === ['$and' => [['initiatives' => $initiativeId], ['objectives' => $objectiveId]]], 'Downloads intersect initiative/objective filters and keep canonical string IDs.');
$params = ['initiative' => ['invalid']];
aborts(function () use ($downloadFilter, $params) { $filter = []; $filename = 'test'; eval($downloadFilter); }, 400);
$params = ['initiative' => '', 'objective' => ''];
$filter = []; $filename = 'test';
eval($downloadFilter);
check($filter === [] && $filename === 'test', 'Empty download selections leave the existing query unchanged.');
ob_end_clean(); echo $baselineOutput; echo 'Initiative report/export checks passed (' . ($checks - $baselineChecks) . " checks).\n";
