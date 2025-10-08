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
    private $fields = array();

    public function __construct($report)
    {
        $this->report = $report;
        $this->steps = $report['steps'] ?? array();

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

    public function setTime($startyear, $endyear, $startmonth, $endmonth)
    {
        $this->startmonth = intval($startmonth);
        $this->endmonth = intval($endmonth);
        $this->startyear = intval($startyear);
        $this->endyear = intval($endyear);

        if ($this->startyear == $this->endyear) {
            $this->timefilter = [
                '$and' => [
                    ['year' => ['$eq' => $this->startyear]],
                    ['month' => ['$gte' => $this->startmonth]],
                    ['month' => ['$lte' => $this->endmonth]]
                ]
            ];
        } else {
            $this->timefilter = [
                '$or' => [
                    [
                        '$and' => [
                            ['year' => ['$eq' => $this->startyear]],
                            ['month' => ['$gte' => $this->startmonth]]
                        ]
                    ],
                    [
                        '$and' => [
                            ['year' => ['$eq' => $this->endyear]],
                            ['month' => ['$lte' => $this->endmonth]]
                        ]
                    ]
                ]
            ];
        }
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
            case 'activities-impact':
                return $this->formatActivitiesImpact($item);
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
        if (empty($text)) return '';
        $Parsedown = new Parsedown();
        return $Parsedown->line($text);
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

    public function getActivities($item, $impact = false)
    {
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
        }
        $options['projection'] = ['rendered.print' => 1];
        if ($impact) {
            $options['projection']['impact'] = 1;
        }

        $DB = new DB();
        $data = $DB->db->activities->find($filter, $options);

        if ($impact) {
            return array_map(function ($item) {
                return [$item['rendered']['print'], $item['impact'] ?? ''];
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
    private function formatActivitiesImpact($item)
    {
        $data = $this->getActivities($item, true);
        $html = "<table class='table my-20'><thead><tr><th></th><th>" . lang('Impact', 'Impact') . "</th></tr></thead><tbody>";
        foreach ($data as $activity) {
            $html .= "<tr>";
            $html .= "<td>" . $activity[0] . "</td>";
            $html .= "<td><strong>" . ($activity[1] ?? '-') . "</strong></td>";
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
        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'activity' => '$_id', 'count' => 1]];
        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'activity' => 1, 'count' => 1]];

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
