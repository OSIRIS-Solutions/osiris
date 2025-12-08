<?php
require_once "fields.php";
class JournalFields extends Fields
{

    function __construct()
    {
        parent::__construct();
        $Settings = new Settings();
        $DB = new DB();
        $osiris = $DB->db;

        $FIELDS = [
            [
                'id' => 'id',
                'module_of' => ['general'],
                'label' => lang('ID', 'ID'),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "journal",
                "module_of" => ['general'],
                "label" => lang("Journal", "Zeitschrift"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "issn",
                "module_of" => ['general'],
                "label" => lang("ISSN", "ISSN"),
                'type' => 'list',
                'usage' => [
                    'filter',
                    'columns'
                ],
                'input' => 'select',
                'values' => function () use ($osiris) {
                    $issns = $osiris->journals->distinct('issn');
                    $list = [];
                    foreach ($issns as $issn) {
                        $list[$issn] = $issn;
                    }
                    return $list;
                }
            ],
            [
                "id" => "publisher",
                "module_of" => ['general'],
                "label" => lang("Publisher", "Verlag"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "impact.impact",
                "module_of" => ['general'],
                "label" => lang("Impact Factor", "Impact Factor"),
                'type' => 'double',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "impact.year",
                "module_of" => ['general'],
                "label" => lang("Impact Factor Year", "Impact Factor Jahr"),
                'type' => 'integer',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "country",
                "module_of" => ['general'],
                "label" => lang("Country", "Land"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "oa",
                "module_of" => ['general'],
                "label" => lang("Open Access", "Open Access"),
                'type' => 'boolean',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
        ];

        $this->fields = array_values($FIELDS);
        // Sort fields by name
        usort($this->fields, function ($a, $b) {
            if (isset($a['label']) && !isset($b['label'])) return -1;
            if (!isset($a['label']) && isset($b['label'])) return 1;
            if (!isset($a['label']) && !isset($b['label'])) return 0;
            return strnatcmp($a['label'], $b['label']);
        });
    }
}
