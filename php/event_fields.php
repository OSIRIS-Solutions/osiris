<?php
require_once "fields.php";
class EventFields extends Fields
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
                "id" => "title",
                "module_of" => ['general'],
                "label" => lang("Title", "Titel"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "title_full",
                "module_of" => ['general'],
                "label" => lang("Full title", "Voller Titel"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "type",
                "module_of" => ['general'],
                "label" => lang("Type", "Typ"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'input' => 'select',
                'values' => $this->vocabularyValues('event-type'),
            ],
            [
                "id" => "description",
                "module_of" => ['general'],
                "label" => lang("Description", "Beschreibung"),
                'type' => 'string',
                'usage' => [
                    'columns'
                ],
            ],
            [
                "id" => "start",
                "module_of" => ['general'],
                "label" => lang("Start date", "Anfangsdatum"),
                'type' => 'date',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "end",
                "module_of" => ['general'],
                "label" => lang("End date", "Enddatum"),
                'type' => 'date',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "location",
                "module_of" => ['general'],
                "label" => lang("Location", "Ort"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "country",
                "module_of" => ['general'],
                "label" => lang("Country (ISO Code)", "Land (ISO-Code)"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "url",
                "module_of" => ['general'],
                "label" => lang("URL", "URL"),
                'type' => 'string',
                'usage' => [
                    'columns'
                ],
            ],
        ];

        if ($Settings->featureEnabled('topics')) {
            $topics = $osiris->topics->find()->toArray();
            $topics = array_column($topics, 'name', 'id');
            $FIELDS[] = [
                'id' => 'topics',
                'module_of' => $typeModules['topics'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => $Settings->topicLabel(),
                'type' => 'list',
                'input' => 'select',
                'values' => $topics
            ];
        }

        if ($Settings->featureEnabled('tags')) {
            $tags = $Settings->get('tags', []);
            $FIELDS[] = [
                'id' => 'tags',
                'module_of' => ['general'],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => $Settings->tagLabel(),
                'type' => 'list',
                'input' => 'select',
                'values' => $tags
            ];
        }


        // foreach ($osiris->adminFields->find() as $field) {
        //     // make sure that id does not exist yet
        //     $exists = false;
        //     foreach ($FIELDS as $existingField) {
        //         if ($existingField['id'] == $field['id']) {
        //             $exists = true;
        //             break;
        //         }
        //     }
        //     if ($exists) continue;
        //     $f = [
        //         'id' => $field['id'],
        //         'module_of' => $typeModules[$field['id']] ?? [],
        //         'usage' => [
        //             'aggregate',
        //             'filter',
        //             'columns'
        //         ],
        //         'label' => lang($field['name'], $field['name_de'] ?? null),
        //         'type' => parent::typeConvert($field['format'] ?? 'string'),
        //         'custom' => true
        //     ];

        //     if ($field['format'] == 'list') {
        //         $f['values'] =  DB::doc2Arr($field['values']);
        //         $f['input'] = 'select';
        //         if ($field['multiple'] ?? false) {
        //             $f['type'] = 'list';
        //         }
        //     }

        //     $FIELDS[] = $f;
        // }

        // remove 'filter' from all fields where module_of is empty
        // foreach ($FIELDS as &$f) {
        //     if (empty($f['module_of'])) {
        //         $f['usage'] = array_filter($f['usage'], function ($u) {
        //             return $u != 'filter';
        //         });
        //     }
        // }
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

