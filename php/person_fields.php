<?php
require_once "fields.php";
class PersonFields extends Fields
{

    function __construct()
    {
        parent::__construct();
        $DB = new DB();
        $osiris = $DB->db;
        $Settings = new Settings();

        $data = $Settings->get('person-data');
        $data = DB::doc2Arr($data);
        $typeModules = [
            'username',
            'first',
            'last',
            'academic_title',
            'mail',
            'orcid',
            'is_active',
            'created',
            'updated',
            'roles'
        ];

        $typeModules = array_merge($data, $typeModules);
        $typeModules = array_fill_keys($typeModules, ['general']);
        $FIELDS = [
            [
                'id' => "username",
                'label' => lang('people.username'),
                'module_of' => $typeModules['username'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'text',
            ],
            [
                'id' => "first",
                'label' => lang('common.name_first'),
                'module_of' => $typeModules['first'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'text',
            ],
            [
                'id' => "last",
                'label' => lang('common.name_last'),
                'module_of' => $typeModules['last'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'text',
            ],
            [
                'id' => "academic_title",
                'label' => lang('people.acad_title'),
                'module_of' => $typeModules['academic_title'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    '' => lang('people.none'),
                    'Dr.' => 'Dr.',
                    'Prof.' => 'Prof.',
                    'Prof. Dr.' => 'Prof. Dr.'
                ]
            ],
            [
                'id' => "mail",
                'label' => lang('people.mail'),
                'module_of' => $typeModules['mail'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'text',
            ],
            [
                'id' => "orcid",
                'label' => lang('common.orcid'),
                'module_of' => $typeModules['orcid'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'text',
            ],
            [
                'id' => 'is_active',
                'label' => lang('people.is_active'),
                'module_of' => $typeModules['is_active'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'boolean',
                'values' => [
                    'true' => lang('common.yes'),
                    'false' => lang('common.no')
                ],
                'input' => 'radio',
                'default_value' => true
            ],
            [
                'id' => 'created',
                'label' => lang('people.created_at'),
                'module_of' => $typeModules['created'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'type' => 'datetime',
                'input' => 'date',
            ],
            [
                'id' => 'updated',
                'label' => lang('people.updated_at'),
                'module_of' => $typeModules['updated'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'type' => 'datetime',
                'input' => 'date',
            ],
            [
                'id' => 'roles',
                'label' => lang('common.roles'),
                'module_of' => $typeModules['roles'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'text',
            ],
            [
                "id" => "gender",
                "module_of" => $typeModules['gender'] ?? [],
                'usage' => [
                    'aggregate',
                ],
                "label" => lang('common.gender'),
                "type" => "string",
                "input" => "select",
                "values" => [
                    "m" => "male / männlich",
                    "f" => "female / weiblich",
                    "d" => "non-binary / divers",
                    "n" => "not specified / nicht angegeben"
                ]
            ],
            [
                "id" => "telephone",
                "module_of" => $typeModules['telephone'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('people.phone')
            ],
            [
                "id" => "mobile",
                "module_of" => $typeModules['mobile'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('people.mobile')
            ],
            [
                "id" => "internal_id",
                "module_of" => $typeModules['internal_id'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('common.internal_id')
            ],
            [
                "id" => "position",
                "module_of" => $typeModules['position'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('common.position'),
            ],
            [
                "id" => "room",
                "module_of" => $typeModules['room'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('common.room')
            ],
            [
                "id" => "hide",
                "module_of" => $typeModules['hide'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "boolean",
                "label" => lang('people.hide_in_portfolio_person_fields'),
            ],
            [
                "id" => "research",
                "module_of" => $typeModules['research'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('groups.research_interests'),
            ],
            [
                "id" => "research_profile",
                "module_of" => $typeModules['research_profile'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('people.research_profile'),
            ],
            [
                "id" => "cv",
                "module_of" => $typeModules['cv'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('people.cv'),
            ],
            [
                "id" => "biography",
                "module_of" => $typeModules['biography'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('common.biography'),
            ],
            [
                "id" => "education",
                "module_of" => $typeModules['education'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => lang('common.education_profile'),
            ]
        ];

        $expertise = $osiris->persons->distinct('expertise');
        if (!empty($expertise)) {
            $FIELDS[] =
                [
                    "id" => "expertise",
                    "module_of" => $typeModules['expertise'] ?? [],
                    "type" => "string",
                    'input' => 'select',
                    "values" => DB::doc2Arr($expertise),
                    "label" => lang('common.expertise'),
                    'usage' => [
                        'aggregate',
                        'filter',
                        'columns'
                    ]
                ];
        }

        $kw_name = $Settings->get('staff-keyword-name', 'Keywords');
        $all_kw = DB::doc2Arr($Settings->get('staff-keywords', []));
        if (!empty($all_kw)) {
            $FIELDS[] =
                [
                    "id" => "keywords",
                    "module_of" => $typeModules['keywords'] ?? [],
                    "type" => "string",
                    'input' => 'select',
                    "values" => $all_kw,
                    "label" => $kw_name,
                    'usage' => [
                        'aggregate',
                        'filter',
                        'columns'
                    ]
                ];
        }
        $units = $osiris->groups->find([], ['sort' => [lang('common.field_name_language') => 1]])->toArray();
        $units = array_column($units, lang('common.field_name_language'), 'id');
        $FIELDS[] = [
            'id' => 'units.unit',
            'module_of' => ['general'],
            'usage' => [
                'aggregate',
                'filter',
                'columns'
            ],
            'label' => lang('common.organizational_unit'),
            'type' => 'list',
            'input' => 'select',
            'values' => $units
        ];
        $FIELDS[] = [
            'id' => 'current_units',
            'module_of' => ['general'],
            'usage' => [
                'filter'
            ],
            'label' => lang('people.current_organizational_unit'),
            'type' => 'list',
            'input' => 'select',
            'values' => $units
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
        $FIELDS = parent::addCustomFields($FIELDS, $osiris, $typeModules, true);

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
