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
        $contentLanguage = currentLanguage() === 'keys' ? OSIRIS_BASE_LANGUAGE : currentLanguage();

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
                'label' => t('people.username'),
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
                'label' => t('common.name_first'),
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
                'label' => t('common.name_last'),
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
                'label' => t('people.acad_title'),
                'module_of' => $typeModules['academic_title'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    '' => t('people.none'),
                    'Dr.' => 'Dr.',
                    'Prof.' => 'Prof.',
                    'Prof. Dr.' => 'Prof. Dr.'
                ]
            ],
            [
                'id' => "mail",
                'label' => t('people.mail'),
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
                'label' => t('common.orcid'),
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
                'label' => t('people.is_active'),
                'module_of' => $typeModules['is_active'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'boolean',
                'values' => [
                    'true' => t('common.yes'),
                    'false' => t('common.no')
                ],
                'input' => 'radio',
                'default_value' => true
            ],
            [
                'id' => 'created',
                'label' => t('people.created_at'),
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
                'label' => t('people.updated_at'),
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
                'label' => t('common.roles'),
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
                "label" => t('common.gender'),
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
                "label" => t('people.phone')
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
                "label" => t('people.mobile')
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
                "label" => t('common.internal_id')
            ],
            [
                "id" => "position.$contentLanguage",
                "module_of" => $typeModules['position'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => t('common.position'),
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
                "label" => t('common.room')
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
                "label" => t('people.hide_in_portfolio_person_fields'),
            ],
            [
                "id" => "research.$contentLanguage",
                "module_of" => $typeModules['research'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => t('groups.research_interests'),
            ],
            [
                "id" => "research_profile.$contentLanguage",
                "module_of" => $typeModules['research_profile'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => t('people.research_profile'),
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
                "label" => t('people.cv'),
            ],
            [
                "id" => "biography.$contentLanguage",
                "module_of" => $typeModules['biography'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => t('common.biography'),
            ],
            [
                "id" => "education.$contentLanguage",
                "module_of" => $typeModules['education'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "type" => "string",
                "label" => t('common.education_profile'),
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
                    "label" => t('common.expertise'),
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
        $unitDocs = $osiris->groups->find([], ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1]])->toArray();
        $units = [];
        foreach ($unitDocs as $unit) $units[$unit['id']] = localized($unit['name']);
        asort($units, SORT_NATURAL | SORT_FLAG_CASE);
        $FIELDS[] = [
            'id' => 'units.unit',
            'module_of' => ['general'],
            'usage' => [
                'aggregate',
                'filter',
                'columns'
            ],
            'label' => t('common.organizational_unit'),
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
            'label' => t('people.current_organizational_unit'),
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
