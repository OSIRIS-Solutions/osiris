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
                'label' => lang('Username', 'Kürzel'),
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
                'label' => lang('First name', 'Vorname'),
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
                'label' => lang('Last name', 'Nachname'),
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
                'label' => lang('Acad. title', 'Akad. Titel'),
                'module_of' => $typeModules['academic_title'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    '' => lang('none', 'keiner'),
                    'Dr.' => 'Dr.',
                    'Prof.' => 'Prof.',
                    'Prof. Dr.' => 'Prof. Dr.'
                ]
            ],
            [
                'id' => "mail",
                'label' => lang('Mail', 'Email'),
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
                'label' => lang('ORCID', 'ORCID'),
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
                'label' => lang('Is active', 'Ist aktiv'),
                'module_of' => $typeModules['is_active'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'boolean',
                'values' => [
                    'true' => lang('yes', 'ja'),
                    'false' => lang('no', 'nein')
                ],
                'input' => 'radio',
                'default_value' => true
            ],
            [
                'id' => 'created',
                'label' => lang('Created at', 'Angelegt am'),
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
                'label' => lang('Updated at', 'Geändert am'),
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
                'label' => lang('Roles', 'Rollen'),
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
                "label" => lang("Gender", "Geschlecht"),
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
                "label" => lang("Phone", "Telefon")
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
                "label" => lang("Mobile", "Mobiltelefon")
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
                "label" => lang("Internal ID", "Interne ID")
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
                "label" => lang("Position", "Position"),
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
                "label" => lang("Room", "Raum")
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
                "label" => lang("Hide in Portfolio", "Profil im Portfolio ausblenden"),
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
                "label" => lang("Research interests", "Forschungsinteressen"),
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
                "label" => lang("Research profile", "Forschungsprofil"),
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
                "label" => lang("CV", "Lebenslauf"),
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
                "label" => lang("Biography", "Biografie"),
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
                "label" => lang("Education", "Ausbildung"),
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
                    "label" => lang("Expertise", "Expertise"),
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
        $units = $osiris->groups->find([], ['sort' => [lang('name', 'name_de') => 1]])->toArray();
        $units = array_column($units, lang('name', 'name_de'), 'id');
        $FIELDS[] = [
            'id' => 'units.unit',
            'module_of' => ['general'],
            'usage' => [
                'aggregate',
                'filter',
                'columns'
            ],
            'label' => lang('Organizational unit', 'Organisationseinheit'),
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
