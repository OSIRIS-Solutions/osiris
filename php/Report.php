<?php
require_once "init.php";
include_once 'fields.php';

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;

require_once "MyParsedown.php";

class Report
{
    public $report = array();
    public $steps = array();
    private $timefilter = ['year' => CURRENTYEAR - 1];
    private $startmonth = 1;
    private $endmonth = 12;
    private $startyear = CURRENTYEAR - 1;
    private $endyear = CURRENTYEAR - 1;
    public $fields = array();
    private $variables = array();

    public function __construct($report)
    {
        $this->report = DB::doc2Arr($report);
        $this->steps = DB::doc2Arr($this->report['steps'] ?? array());

        // add default variables for year and months
        $this->variables['startyear'] = $this->startyear;
        $this->variables['endyear'] = $this->endyear;
        $this->variables['startmonth'] = $this->startmonth;
        $this->variables['endmonth'] = $this->endmonth;

        // we need fields for labels
        $Fields = new Fields();
        // field array with id as key
        $this->fields = array_column($Fields->fields, null, 'id');
    }

    public function setYear($year)
    {
        $startyear = $year;
        $endyear = $year;
        $startmonth = $this->report['start'] ?? 1;
        $duration = $this->report['duration'] ?? 12;
        $endmonth = $startmonth + $duration - 1;
        if ($endmonth > 12) {
            $endmonth -= 12;
            $endyear++;
        }

        $this->setTime($startyear, $endyear, $startmonth, $endmonth);
    }

    public function setVariables($vars)
    {
        // if (empty($vars)) return;
        $this->variables = $this->resolve_vars($vars);
        // apply variable substitutions to report definition
        $this->steps = $this->apply_tokens_recursive($this->steps, $this->variables);
        $this->report['steps'] = $this->steps;
    }

    public function setTime($startyear, $endyear, $startmonth, $endmonth)
    {
        global $Settings; // contains continuousTypes

        $this->startmonth = intval($startmonth);
        $this->endmonth   = intval($endmonth);
        $this->startyear  = intval($startyear);
        $this->endyear    = intval($endyear);

        // 1) Continuous / long-running activities:
        // Include if they overlap with the selected year range.
        $continuousFilter = [
            'start.year' => ['$lte' => $this->endyear],
            '$or' => [
                ['end.year' => ['$gte' => $this->startyear]],
                [
                    'end'     => null,
                    'subtype' => ['$in' => $Settings->continuousTypes],
                ],
            ],
        ];

        // 2) "Simple" activities that only use year/month
        if ($this->startyear == $this->endyear) {
            // Single year with month range
            $discreteFilter = [
                '$and' => [
                    ['year'  => $this->startyear],
                    ['month' => ['$gte' => $this->startmonth]],
                    ['month' => ['$lte' => $this->endmonth]],
                ],
            ];
        } else {
            // Multi-year range
            $discreteFilter = [
                '$or' => [
                    // First year: from start month to end of year
                    [
                        '$and' => [
                            ['year'  => $this->startyear],
                            ['month' => ['$gte' => $this->startmonth]],
                        ],
                    ],
                    // Last year: from beginning of year to end month
                    [
                        '$and' => [
                            ['year'  => $this->endyear],
                            ['month' => ['$lte' => $this->endmonth]],
                        ],
                    ],
                    // All full years in between
                    [
                        'year' => [
                            '$gt' => $this->startyear,
                            '$lt' => $this->endyear,
                        ],
                    ],
                ],
            ];
        }

        // 3) Combine both: running OR discrete activities
        $this->timefilter = [
            '$or' => [
                $continuousFilter,
                $discreteFilter,
            ],
        ];
        // OLD code for reference:
        // $this->startmonth = intval($startmonth);
        // $this->endmonth = intval($endmonth);
        // $this->startyear = intval($startyear);
        // $this->endyear = intval($endyear);

        // if ($this->startyear == $this->endyear) {
        //     $this->timefilter = [
        //         '$and' => [
        //             ['year' => ['$eq' => $this->startyear]],
        //             ['month' => ['$gte' => $this->startmonth]],
        //             ['month' => ['$lte' => $this->endmonth]]
        //         ]
        //     ];
        // } else {
        //     $this->timefilter = [
        //         '$or' => [
        //             [
        //                 '$and' => [
        //                     ['year' => ['$eq' => $this->startyear]],
        //                     ['month' => ['$gte' => $this->startmonth]]
        //                 ]
        //             ],
        //             [
        //                 '$and' => [
        //                     ['year' => ['$eq' => $this->endyear]],
        //                     ['month' => ['$lte' => $this->endmonth]]
        //                 ]
        //             ]
        //         ]
        //     ];
        // }
    }

    public function getReport()
    {
        $html = "";
        $steps = $this->report['steps'] ?? array();
        foreach ($steps as $step) {
            $html .= $this->format($step);
        }
        return $html;
    }


    public function format($item)
    {
        switch ($item['type']) {
            case 'text':
                return $this->formatText($item);
            case 'activities':
                return $this->formatActivities($item);
            case 'activities-field':
                return $this->formatActivitiesFields($item);
            case 'table':
                return $this->formatTable($item);
            case 'line':
                return $this->formatLine($item);
            default:
                return '';
        }
    }

    /**
     * Retreive translated text elements
     *
     * @param array $item
     * @return string Formatted paragraph
     */
    public function getText($item)
    {
        $text = $item['text'] ?? '';
        return $text;
        // if (empty($text)) return '';
        // $Parsedown = new Parsedown();
        // return $Parsedown->line($text);
    }

    /**
     * Format Text for HTML output.
     *
     * @param array $item
     * @return string formatted HTML
     */
    private function formatText($item)
    {
        $level = $item['level'] ?? 'p';
        $text = $this->getText($item);
        return "<$level>" . $text . "</$level>";
    }

    private function formatLine()
    {
        return '<hr />';
    }

    private function resolve_vars(array $runtime = []): array
    {
        $vars = [];
        foreach (($this->report['variables'] ?? []) as $k => $def) {
            $vars[$k] = $def['default'] ?? null;
        }
        // apply ad-hoc overrides
        foreach ($runtime as $k => $v) if ($k !== '_preset') $vars[$k] = $v;

        // add vars for time period
        $vars['startyear'] = $this->startyear;
        $vars['endyear'] = $this->endyear;
        $vars['startmonth'] = $this->startmonth;
        $vars['endmonth'] = $this->endmonth;

        return $vars;
    }

    private function apply_tokens_recursive($data, array $vars)
    {
        if (is_array($data) || $data instanceof MongoDB\Model\BSONArray  || $data instanceof MongoDB\Model\BSONDocument) {
            foreach ($data as $k => $v) $data[$k] = $this->apply_tokens_recursive($v, $vars);
            return $data;
        }
        if (!is_string($data)) return $data;

        return preg_replace_callback(
            '/\{\{\s*vars\.([a-zA-Z0-9_]+)(?:\|([a-z]+):"?([^"}]+)"?)?\s*\}\}/',
            function ($m) use ($vars) {
                $key = $m[1];
                $filter = $m[2] ?? null;
                $arg = $m[3] ?? null;
                $val = $vars[$key] ?? null;
                if ($filter === 'default' && ($val === null || $val === '')) $val = $arg;
                if ($filter === 'date' && !empty($val)) $val = date($arg ?: 'Y-m-d', is_numeric($val) ? $val : strtotime($val));
                return is_bool($val) ? ($val ? 'true' : 'false') : (string)$val;
            },
            $data
        );
    }

    public function getActivities($item, $field = false)
    {
        // apply variable substitutions

        $filter = json_decode($item['filter'], true);
        $timelimit = $item['timelimit'] ?? false;

        // add time limit filter
        if ($timelimit)
            $filter = array_merge_recursive($this->timefilter, $filter);

        // default sorting by type, year, month
        $options = ['sort' => ["type" => 1, "year" => 1, "month" => 1]];
        if (isset($item['sort']) && !empty($item['sort'])) {
            $options['sort'] = [];
            foreach ($item['sort'] as $s) {
                $dir = ($s['dir'] == 'asc') ? 1 : -1;
                $options['sort'][$s['field']] = $dir;
            }

            $options['collation'] = [
                'locale' => lang('en', 'de'),     // je nach gewünschter Sprache
                'strength' => 1,
                'numericOrdering' => true  // optional: "10" > "2"
            ];
        }
        $options['projection'] = ['rendered.print' => 1];
        if ($field) {
            $options['projection'][$field] = 1;
        }

        $DB = new DB();
        $data = $DB->db->activities->find($filter, $options);

        if ($field) {
            return array_map(function ($item) use ($field) {
                return [$item['rendered']['print'], $item[$field] ?? ''];
            }, $data->toArray());
        }

        return array_map(function ($item) {
            return ($item['rendered']['print']);
        }, $data->toArray());
    }

    private function formatActivities($item)
    {
        $data = $this->getActivities($item);
        $html = "";
        foreach ($data as $activity) {
            $html .= "<p>" . $activity . "</p>";
        }
        return $html;
    }
    private function formatActivitiesFields($item)
    {
        $field = $item['field'] ?? false;
        $label = $field;
        if (isset($this->fields[$field]) && !empty($this->fields[$field])) {
            $f = $this->fields[$field];
            $label = $f['label'] ?? $f['id'];
        }
        $data = $this->getActivities($item, $field);
        $html = "<table class='table my-20'><thead><tr><th></th><th>" . $label . "</th></tr></thead><tbody>";
        foreach ($data as $activity) {
            $html .= "<tr>";
            $html .= "<td>" . $activity[0] . "</td>";
            $html .= "<td><strong>" . (!empty($activity[1] ?? '') ? $activity[1] : '-') . "</strong></td>";
            $html .= "</tr>";
        }
        $html .= "</tbody></table>";
        return $html;
    }

    /**
     * Get all data for the table based on step
     *
     * @param array $item
     * @return array Table rows as array with head being index 0
     */
    public function getTable($item)
    {
        $filter = json_decode($item['filter'], true);
        $sort = $item['table_sort'] ?? 'count-desc';
        $sortField = 'count';
        $sortDir = -1;
        if ($sort == 'aggregation-asc') {
            $sortField = 'activity';
            $sortDir = 1;
        } elseif ($sort == 'aggregation-desc') {
            $sortField = 'activity';
            $sortDir = -1;
        } elseif ($sort == 'count-asc') {
            $sortField = 'count';
            $sortDir = 1;
        }
        $timelimit = $item['timelimit'] ?? false;
        $group = $item['aggregate'] ?? '';
        $group2 = $item['aggregate2'] ?? null;

        // get labels for group fields
        $label = $group;
        $transform = null;
        $unwind = false;
        if (isset($this->fields[$group]) && !empty($this->fields[$group])) {
            $f = $this->fields[$group];
            $label = $f['label'] ?? $f['id'];
            // if f value is an associative array, we need to transform the value
            if (isset($f['values']) && is_array($f['values']) && array_keys($f['values']) !== range(0, count($f['values']) - 1)) {
                $transform = $f['values'];
            }
            if ($f['type'] == 'list') {
                $unwind = true;
            }
        }
        $transform2 = null;
        $unwind2 = false;
        if (!empty($group2) && isset($this->fields[$group2]) && !empty($this->fields[$group2])) {
            $f = $this->fields[$group2];
            // if f value is an associative array, we need to transform the value
            if (isset($f['values']) && is_array($f['values']) && array_keys($f['values']) !== range(0, count($f['values']) - 1)) {
                $transform2 = $f['values'];
            }
            if ($f['type'] == 'list') {
                $unwind2 = true;
            }
        }

        if ($timelimit)
            $filter = array_merge_recursive($this->timefilter, $filter);

        $DB = new DB();
        $aggregate = [
            ['$match' => $filter],
        ];
        // if (strpos($group, 'authors') !== false) {
        //     $aggregate[] = ['$unwind' => '$authors'];
        // }
        if ($unwind) {
            $aggregate[] = ['$unwind' => '$' . $group];
        }
        if ($unwind2) {
            $aggregate[] = ['$unwind' => '$' . $group2];
        }

        if (empty($group2)) {
            $aggregate[] =
                ['$group' => ['_id' => '$' . $group, 'count' => ['$sum' => 1]]];
        } else {
            $aggregate[] =
                ['$group' => ['_id' => ['$' . $group, '$' . $group2], 'count' => ['$sum' => 1]]];
        }
        $aggregate[] = ['$project' => ['_id' => 0, 'activity' => '$_id', 'count' => 1]];
        $aggregate[] = ['$sort' => [$sortField => $sortDir]];

        $data = $DB->db->activities->aggregate(
            $aggregate
        )->toArray();

        $table = [];

        if (empty($group2)) {
            $table[] = [$label, 'Count'];
            foreach ($data as $row) {
                $activity = $row['activity'];
                if (!is_string($activity)) {
                    $activity = DB::doc2Arr($activity)[0] ?? '';
                }
                if (empty($activity)) {
                    $activity = '<em>' . lang('Empty', 'Leer') . '</em>';
                } elseif ($transform && isset($transform[$activity])) {
                    $activity = $transform[$activity];
                }
                $table[] = [$activity, $row['count']];
            }
        } else {
            $activities = [];
            $header = [];
            foreach ($data as $row) {
                $g1 = $row['activity'][0];
                $g2 = $row['activity'][1];
                if (!is_string($g1)) {
                    $g1 = DB::doc2Arr($g1)[0] ?? '';
                }
                if (!is_string($g2)) {
                    $g2 = DB::doc2Arr($g2)[0] ?? '';
                }
                $activities[$g1][$g2] = $row['count'];
                if (!array_key_exists($g2, $header)) {
                    $name = $g2;
                    if (empty($g2)) {
                        $name = '<em>' . lang('Empty', 'Leer') . '</em>';
                    } elseif ($transform2 && isset($transform2[$g2])) {
                        $name = $transform2[$g2];
                    }
                    $header[$g2] = $name;
                }
            }

            asort($header);
            ksort($activities);

            $table[] = array_merge([$label], array_values($header));
            foreach ($activities as $activity => $counts) {
                if (empty($activity)) {
                    $activity = '<em>' . lang('Empty', 'Leer') . '</em>';
                } elseif ($transform && isset($transform[$activity])) {
                    $activity = $transform[$activity];
                }
                $row = [$activity];
                foreach ($header as $h => $hn) {
                    $row[] = $counts[$h] ?? 0;
                }
                $table[] = $row;
            }
        }
        return $table;
    }

    private function formatTable($item)
    {
        $result = $this->getTable($item);

        $html = "";
        if (count($result) > 0) {
            $html .= "<table class='table'>";
            $html .= "<thead><tr>";
            foreach ($result[0] as $h) {
                $html .= "<th>" . $h . "</th>";
            }
            $html .= "</tr></thead>";
            $html .= "<tbody>";
            foreach (array_slice($result, 1) as $row) {
                $html .= "<tr>";
                foreach ($row as $cell) {
                    $html .= "<td>" . $cell . "</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody>";
            $html .= "</table>";
        }
        return $html;
    }

    public function formatChart()
    {
        // Create the Pie Graph.
        $graph = new Graph\PieGraph(350, 250);
        $graph->title->Set("A Simple Pie Plot");
        $graph->SetBox(true);

        $data = array(40, 21, 17, 14, 23);
        $p1   = new Plot\PiePlot($data);
        $p1->ShowBorder();
        $p1->SetColor('black');
        $p1->SetSliceColors(array('#1E90FF', '#2E8B57', '#ADFF2F', '#DC143C', '#BA55D3'));

        $graph->Add($p1);
        $graph->Stroke();
    }
}
