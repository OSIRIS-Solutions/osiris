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

        $if_label = $Settings->impactLabel();

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
                "label" => lang("Name", "Name"),
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
                "label" => $if_label . lang(" Value", "-Wert"),
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
                "label" => $if_label . lang(" Year", "-Jahr"),
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

        // Add custom fields from the database
        $data_fields = $Settings->get('journal-data');
        $data_fields = DB::doc2Arr($data_fields);
        $FIELDS = parent::addCustomFields($FIELDS, $osiris, $data_fields, true);

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
