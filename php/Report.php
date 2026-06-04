<?php
require_once "init.php";
include_once "activity_fields.php";
include_once "project_fields.php";
include_once "event_fields.php";

use Amenadiel\JpGraph\Graph;
use Amenadiel\JpGraph\Plot;


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
    private $headers = array();
    private $DB;

    # new constant collections
    const COLLECTIONS = ['activities', 'projects', 'proposals', 'conferences'];

    public function __construct($report)
    {

        $this->DB = new DB();

        $this->report = DB::doc2Arr($report);
        $this->steps = DB::doc2Arr($this->report['steps'] ?? array());

        // add default variables for year and months
        $this->variables = [
            'startyear' => $this->startyear,
            'endyear' => $this->endyear,
            'startmonth' => $this->startmonth,
            'endmonth' => $this->endmonth,
        ];

        // we need fields for labels
        $Fields = new ActivityFields();
        // field array with id as key
        $this->fields = array_column($Fields->fields, null, 'id');
    }

    /**
     * Load fields for a collection if not present, otherwise return it
     *
     * @param string $collection
     * @return array
     */
    private function loadFields(string $collection)
    {
        if (isset($this->fields[$collection]) && !empty($this->fields[$collection])) {
            return $this->fields[$collection];
        }
        if (!in_array($collection, self::COLLECTIONS)) {
            throw new Exception("Invalid collection specified: " . e($collection));
        }

        if ($collection == 'activities') {
            $Fields = new ActivityFields();
            $this->fields[$collection] = array_column($Fields->fields, null, 'id');
        } elseif ($collection == 'projects' || $collection == 'proposals') {
            $Fields = new ProjectFields($collection);
            $this->fields[$collection] = array_column($Fields->fields, null, 'id');
        } elseif ($collection == 'conferences') {
            $Fields = new EventFields();
            $this->fields[$collection] = array_column($Fields->fields, null, 'id');
        }
        return $this->fields[$collection] ?? [];
    }

    public function setYear($year)
    {
        $startyear = $year;
        $endyear = $year;
        $startmonth = $this->report['start'] ?? 1;
        $duration = $this->report['duration'] ?? 12;
        $endmonth = $startmonth + $duration - 1;
        // make sure endmonth does not exceed 12 and adjust endyear accordingly
        while ($endmonth > 12) {
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

        // update variables for use in report steps
        $this->variables['startyear'] = $this->startyear;
        $this->variables['endyear'] = $this->endyear;
        $this->variables['startmonth'] = $this->startmonth;
        $this->variables['endmonth'] = $this->endmonth;

        $isostart = $this->startyear . '-' . str_pad($this->startmonth, 2, '0', STR_PAD_LEFT) . '-01';
        $isoend = $this->endyear . '-' . str_pad($this->endmonth, 2, '0', STR_PAD_LEFT) . '-31';

        // 1) Continuous / long-running activities:
        // Include if they overlap with the selected year range.
        $continuousFilter = [
            'subtype' => ['$in' => $Settings->continuousTypes],
            'start.year' => ['$lte' => $this->endyear],
            '$or' => [
                ['end'     => null],
                ['end.year' => ['$gte' => $this->startyear]],
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
        $this->timefilter['activities'] = [
            '$or' => [
                $continuousFilter,
                $discreteFilter,
            ],
        ];

        // time filter for proposals by submission_date (ISO)
        $this->timefilter['proposals'] = [
            '$and' => [
                ['submission_date' => ['$gte' => $isostart]],
                ['submission_date' => ['$lte' => $isoend]],
            ],
        ];

        // time filter for projects by start_date and end_date (ISO)
        $this->timefilter['projects'] = [
            '$and' => [
                ['start_date' => ['$lte' => $isoend]],
                ['end_date' => ['$gte' => $isostart]],
            ],
        ];

        // for conferences by start and end (ISO)
        $this->timefilter['conferences'] = [
            '$and' => [
                ['start' => ['$gte' => $isostart]],
                ['end' => ['$lte' => $isoend]],
            ],
        ];
    }

    public function getReport()
    {
        $this->headers = [];
        $html = "";
        $steps = $this->report['steps'] ?? array();
        foreach ($steps as $step) {
            $vars = [];
            if ($step['type'] == 'text' && ($step['level'] == 'h1' || $step['level'] == 'h2')) {
                $id = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $step['text']));
                $vars['id'] = $id;
                $text = $step['text'];
                if ($step['level'] == 'h2') {
                    $text = ' <i class="ph ph-caret-right"></i> ' . $text;
                }
                $this->headers[$id] = $text;
            }
            $html .= $this->format($step, $vars);
        }
        return $html;
    }


    public function getTOC()
    {
        $toc = [];
        $steps = $this->report['steps'] ?? array();
        foreach ($steps as $step) {
            if ($step['type'] == 'text' && ($step['level'] != 'p')) {
                $id = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $step['text']));
                $vars['id'] = $id;
                $text = $step['text'];
                $toc[] = ['id' => $id, 'text' => $text, 'level' => str_replace('h', '', $step['level'])];
            }
        }
        return $toc;
    }

    public function formatTOC()
    {
        $toc = $this->getTOC();
        $html = "<div class='report-toc'><h2>" . lang('Table of contents', 'Inhaltsverzeichnis') . "</h2><ul>";
        $previousLevel = 1;
        foreach ($toc as $item) {
            if ($item['level'] > $previousLevel) {
                $html .= "<ul>";
            } elseif ($item['level'] < $previousLevel) {
                $html .= str_repeat("</ul>", $previousLevel - $item['level']);
            }
            $html .= "<li class='toc-" . ($item['level']) . "'><a href='#" . e($item['id']) . "'>" . ($item['text']) . "</a></li>";
            $previousLevel = $item['level'];
        }
        $html .= str_repeat("</ul>", $previousLevel - 1) . "</div>";
        return $html;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function format($item, $vars = [])
    {
        try {
            switch ($item['type']) {
                case 'text':
                    return $this->formatText($item, $vars);
                case 'activities':
                    return $this->formatActivities($item);
                case 'activities-field':
                    return $this->formatActivitiesFields($item);
                case 'table':
                    return $this->formatTable($item);
                case 'line':
                    return $this->formatLine();
                case 'list':
                    return $this->formatList($item);
                case 'toc':
                    return $this->formatTOC();
                default:
                    throw new Exception("Unknown report type: " . $item['type']);
            }
        } catch (Exception $e) {
            error_log("Report format error: " . $e->getMessage());
            return "<div class='alert danger'><strong>Report Error:</strong> " . e($e->getMessage()) . "</div>";
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
        // make sure that img and br tags are self-closing for HTML compatibility
        $text = str_replace("<br>", "<br />", $text);
        $text = preg_replace('/<img([^>]+)(?<!\/)>/', '<img$1 width="100%" />', $text);
        return $text;
    }

    /**
     * Format Text for HTML output.
     *
     * @param array $item
     * @return string formatted HTML
     */
    private function formatText($item, $vars = [])
    {
        $level = $item['level'] ?? 'p';
        $text = $this->getText($item);
        return "<$level" . (isset($vars['id']) ? " id=\"" . e($vars['id']) . "\"" : "") . ">" . $text . "</$level>";
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
        // check for json errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON filter in report item: " . json_last_error_msg());
        }

        $timelimit = $item['timelimit'] ?? false;

        // add time limit filter
        if ($timelimit)
            $filter = $this->addTimeFilter($filter);
        $filter['exclude_from_reports'] = ['$ne' => true];
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

        $data = $this->DB->db->activities->find($filter, $options);

        if ($field) {
            return array_map(function ($item) use ($field) {
                return [$item['rendered']['print'], $item[$field] ?? ''];
            }, $data->toArray());
        }

        return array_map(function ($item) {
            return ($item['rendered']['print']);
        }, $data->toArray());
    }

    /**
     * Format activities as HTML list 
     *
     * @deprecated 2.0.0
     * @param array $item
     * @return void
     */
    private function formatActivities($item)
    {
        $data = $this->getActivities($item);
        $html = "";
        foreach ($data as $activity) {
            $html .= "<p>" . $activity . "</p>";
        }
        return $html;
    }


    /**
     * Add time filter to given filter based on report settings. If filter is empty, just return time filter, otherwise combine with AND
     *
     * @param array $filter
     * @param string $collection
     * @return array
     */
    private function addTimeFilter(array $filter, string $collection = 'activities')
    {
        if (!isset($this->timefilter[$collection])) {
            throw new Exception("No time filter defined for collection: " . $collection);
        }
        if (empty($filter)) {
            $filter = $this->timefilter[$collection];
        } else {
            $filter = [
                '$and' => [
                    $this->timefilter[$collection],
                    $filter
                ]
            ];
        }
        return $filter;
    }


    public function getList($item)
    {
        // apply variable substitutions
        $collection = $item['collection'] ?? 'activities';
        if (!in_array($collection, self::COLLECTIONS)) {
            throw new Exception("Invalid collection specified in report item: " . e($collection));
        }

        $defaultProjection = 'rendered.print';
        if ($collection == 'projects' || $collection == 'proposals') {
            $defaultProjection = 'title';
        } elseif ($collection == 'persons') {
            $defaultProjection = 'displayname';
        } elseif ($collection == 'conferences') {
            $defaultProjection = 'title';
        }

        $filter = json_decode($item['filter'], true);
        // check for json errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON filter in report item: " . json_last_error_msg());
        }

        $timelimit = $item['timelimit'] ?? false;

        // add time limit filter
        if ($timelimit) {
            $filter = $this->addTimeFilter($filter, $collection);
        }
        $filter['exclude_from_reports'] = ['$ne' => true];

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
        $options['projection'] = ['defaultField' => '$' . $defaultProjection];
        if ($item['field'] ?? false) {
            foreach ($item['field'] as $field) {
                $options['projection'][$field] = 1;
            }
        }

        $data = $this->DB->db->$collection->find($filter, $options)->toArray();

        if ($item['field'] ?? false) {
            return array_map(function ($el) use ($item) {
                $row = [$el['defaultField'] ?? ''];
                foreach ($item['field'] as $field) {
                    $row[] = $el[$field] ?? '';
                }
                return $row;
            }, $data);
        }

        return array_map(function ($el) {
            return ($el['defaultField'] ?? '');
        }, $data);
    }

    public function prepareList($item)
    {
        $result = [];
        $fields = $item['field'] ?? false;
        $labels = [];
        $formats = [];
        $transforms = [];
        if ($fields) {
            $fieldsInfo = $this->loadFields($item['collection'] ?? 'activities');
            foreach ($fields as $n => $field) {
                $labels[$field] = $fieldsInfo[$field]['label'] ?? $field;
                $formats[$n] = $fieldsInfo[$field]['type'] ?? 'text';
                $transforms[$n] = $fieldsInfo[$field]['values'] ?? null;
                if ($field == 'country' || $field == 'countries') {
                    $transforms[$n] = $this->DB->getCountries(lang('name', 'name_de'));
                }
            }
        }
        $data = $this->getList($item);
        if (count($labels) > 0) {
            $header = [''];
            foreach ($labels as $l) {
                $header[] = $l;
            }
            $result[] = $header;
            foreach ($data as $element) {
                $row = [];
                foreach ($element as $i => $cell) {
                    $f = $formats[$i - 1] ?? 'text';
                    $t = $transforms[$i - 1] ?? null;
                    if ($f == 'datetime' && !empty($cell)) {
                        $cell = date('d.m.Y', strtotime($cell));
                    } elseif ($f == 'boolean') {
                        $cell = $cell ? lang('Yes', 'Ja') : lang('No', 'Nein');
                    } elseif ($f == 'list' && is_array($cell)) {
                        $cell = implode(', ', $cell);
                    } elseif ($f == 'list' && $cell instanceof MongoDB\Model\BSONArray) {
                        $cell = DB::doc2Arr($cell);
                        if ($t) {
                            $cell = array_map(function ($v) use ($t) {
                                return $t[$v] ?? $v;
                            }, $cell);
                        }
                        $cell = implode(', ', $cell);
                    } elseif ($t && isset($t[$cell])) {
                        $cell = $t[$cell];
                    }
                    $row[] = $cell;
                }
                $result[] = $row;
            }
        } else {
            foreach ($data as $element) {
                $result[] = [$element];
            }
        }
        return $result;
    }
    private function formatList($item)
    {
        $html = "";
        $list = $this->prepareList($item);
        if (count($list) == 0) {
            return "<p><em>" . lang('No data available for the selected criteria.', 'Keine Daten für die ausgewählten Kriterien verfügbar.') . "</em></p>";
        }
        if (count($list[0]) > 1) {
            $html .= "<table class='table my-20'><thead><tr>";
            foreach ($list[0] as $header) {
                $html .= "<th>" . ($header) . "</th>";
            }
            $html .= "</tr></thead><tbody>";
            for ($i = 1; $i < count($list); $i++) {
                $html .= "<tr>";
                foreach ($list[$i] as $cell) {
                    $html .= "<td>" . ($cell) . "</td>";
                }
                $html .= "</tr>";
            }
            $html .= "</tbody></table>";
        } else {
            foreach ($list as $i => $element) {
                $html .= "<p>" . ($element[0] ?? '') . "</p>";
            }
        }
        return $html;
    }


    /**
     * Format a list of activities with a specific field in a table, e.g. for showing the distribution of activity types or similar. The field must be specified in the report item and will be used as column in the output table. The first column will always contain the activity description (rendered.print).
     *
     * @deprecated 2.0.0
     * @param array $item
     * @return string
     */
    private function formatActivitiesFields($item)
    {
        $field = $item['field'] ?? false;
        $label = $field;
        $fieldsInfo = $this->loadFields($item['collection'] ?? 'activities');
        if (isset($fieldsInfo[$field]) && !empty($fieldsInfo[$field])) {
            $f = $fieldsInfo[$field];
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
        $collection = $item['collection'] ?? 'activities';
        if (!in_array($collection, self::COLLECTIONS)) {
            throw new Exception("Invalid collection specified in report item: " . e($collection));
        }
        $fieldsInfo = $this->loadFields($collection);
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
        if (isset($fieldsInfo[$group]) && !empty($fieldsInfo[$group])) {
            $f = $fieldsInfo[$group];
            $label = $f['label'] ?? $f['id'];
            // if f value is an associative array, we need to transform the value
            if (isset($f['values']) && is_array($f['values']) && array_keys($f['values']) !== range(0, count($f['values']) - 1)) {
                $transform = $f['values'];
            } elseif ($f['id'] == 'country' || $f['id'] == 'countries') {
                $transform = $this->DB->getCountries(lang('name', 'name_de'));
            }
            if ($f['type'] == 'list') {
                $unwind = true;
            }
        }
        $transform2 = null;
        $unwind2 = false;
        if (!empty($group2) && isset($fieldsInfo[$group2]) && !empty($fieldsInfo[$group2])) {
            $f = $fieldsInfo[$group2];
            // if f value is an associative array, we need to transform the value
            if (isset($f['values']) && is_array($f['values']) && array_keys($f['values']) !== range(0, count($f['values']) - 1)) {
                $transform2 = $f['values'];
            }
            if ($f['type'] == 'list') {
                $unwind2 = true;
            }
        }

        if ($timelimit)
            $filter = $this->addTimeFilter($filter, $collection);


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

        $data = $this->DB->db->$collection->aggregate(
            $aggregate
        )->toArray();

        $table = [];

        if (empty($group2)) {
            $table[] = [$label, 'Count'];
            foreach ($data as $row) {
                $activity = $row['activity'];
                if (is_iterable($activity)) {
                    $activity = DB::doc2Arr($activity);
                    if ($transform) {
                        $activity = array_map(function ($v) use ($transform) {
                            return $transform[$v] ?? $v;
                        }, $activity);
                    }
                    $activity = implode(', ', $activity);
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
                if (is_iterable($g1)) {
                    $g1 = DB::doc2Arr($g1)[0] ?? '';
                }
                if (is_iterable($g2)) {
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
