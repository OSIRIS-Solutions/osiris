<?php
require_once "fields.php";

class ProjectFields extends Fields
{
    private $type = 'projects';

    function __construct($type = 'projects')
    {
        parent::__construct();
        $this->type = $type;
        $Settings = new Settings();
        $DB = new DB();
        $osiris = $DB->db;
        $adminCategories = $osiris->adminProjects->find()->toArray();
        $types = array_column($adminCategories, lang('name', 'name_de'), 'id');

        $proposalTypes = [];

        $typeModules = [];
        foreach ($adminCategories as $m) {
            foreach ($m['phases'] as $phase) {
                if ($phase['id'] == 'proposed') {
                    $proposalTypes[$m['id']] = lang($m['name'], $m['name_de'] ?? null);
                }
                $modules = $phase['modules'] ?? [];
                foreach ($modules as $module) {
                    $module = $module['module'];
                    if (!isset($typeModules[$module])) $typeModules[$module] = [];
                    if (!in_array($m['id'], $typeModules[$module])) $typeModules[$module][] = $m['id'];
                }
            }
        }

        if ($this->type == 'proposals') {
            $types = $proposalTypes;
        }

        $proposalTypes = array_keys($proposalTypes);

        $typeModules = array_merge($typeModules, [
            'type' => ['general'],
            'title' => ['general'],
            'persons' => ['general'],
            'name' => ['general'],
            'start_date' => ['general'],
            'end_date' => ['general'],
            'created' => ['general'],
            'created_by' => ['general'],
            'updated' => ['general'],
            'updated_by' => ['general'],
            'topics' => ['general'],

            "status" => $proposalTypes,
            "applicants" => $proposalTypes,
            "submission_date" => $proposalTypes,
            "approval_date" => $proposalTypes,
            "rejection_date" => $proposalTypes,
            "start_proposed" => $proposalTypes,
            "end_proposed" => $proposalTypes,
        ]);


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
                'id' => 'acronym',
                'module_of' => $typeModules['acronym'] ?? [],
                'label' => lang('Acronym', 'Akronym'),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "type",
                "module_of" => $typeModules["type"] ?? [],
                "label" => lang("Type", "Typ"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'input' => 'select',
                'values' => $types,
                "scope" => [
                    "project" => true,
                    "proposed" => true
                ],
            ],
            [
                'id' => 'persons.name',
                'module_of' => $typeModules['persons'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Project staff (name)', 'Projektmitarbeitende (Name)'),
                'type' => 'string'
            ],
            [
                'id' => 'persons.user',
                'module_of' => $typeModules['persons'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Project staff (username)', 'Projektmitarbeitende (Nutzername)'),
                'type' => 'string'
            ],
            [
                'id' => 'persons.role',
                'module_of' => $typeModules['persons'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('Project staff (role)', 'Projektmitarbeitende (Rolle)'),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('project-person-role'),
            ],
            [
                "id" => "name",
                "module_of" => $typeModules["name"] ?? [],
                "label" => lang("Short title", "Kurztitel"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => true,
                    "proposed" => true,
                    "approved" => true
                ],
            ],
            [
                "id" => "name_de",
                "module_of" => $typeModules["name_de"] ?? [],
                "label" => lang("Short title (German)", "Kurztitel (Deutsch)"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "title",
                "module_of" => $typeModules["title"] ?? [],
                "label" => lang("Full title", "Voller Titel"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => true,
                    "proposed" => true,
                    "approved" => true
                ],
            ],
            [
                "id" => "title_de",
                "module_of" => $typeModules["title_de"] ?? [],
                "label" => lang("Full title (German)", "Voller Titel (Deutsch)"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "status",
                "module_of" => $typeModules["status"] ?? [],
                "label" => lang("Status", "Status"),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'proposed' => lang('Proposed', 'Beantragt'),
                    'approved' => lang('Approved', 'Bewilligt'),
                    'rejected' => lang('Rejected', 'Abgelehnt'),
                ],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => true,
                    "approved" => true,
                    "rejected" => true
                ],
            ],
            [
                "id" => "applicants",
                "module_of" => $typeModules["applicants"] ?? [],
                "label" => lang("Applicants", "Antragstellende Personen"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => true
                ],
            ],
            [
                "id" => "submission_date",
                "module_of" => $typeModules["submission_date"] ?? [],
                "label" => lang("Submission date", "Einreichungsdatum"),

                'type' => 'datetime',
                'input' => 'date',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => true
                ],
            ],
            [
                "id" => "approval_date",
                "module_of" => $typeModules["approval_date"] ?? [],
                "label" => lang("Approval date", "Bewilligungsdatum"),

                'type' => 'datetime',
                'input' => 'date',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "approved" => true
                ],
            ],
            [
                "id" => "rejection_date",
                "module_of" => $typeModules["rejection_date"] ?? [],
                "label" => lang("Rejection date", "Ablehnungsdatum"),
                'type' => 'datetime',
                'input' => 'date',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "rejected" => true
                ],
            ],
            [
                "id" => "start_date",
                "module_of" => $typeModules["start_date"] ?? [],
                "label" => lang("Project start", "Projektbeginn"),
                'type' => 'datetime',
                'input' => 'date',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => true,
                    "approved" => true
                ],
            ],
            [
                "id" => "end_date",
                "module_of" => $typeModules["end_date"] ?? [],
                "label" => lang("Project end", "Projektende"),
                'type' => 'datetime',
                'input' => 'date',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => true,
                    "approved" => true
                ],
            ],
            [
                "id" => "start_proposed",
                "module_of" => $typeModules["start_proposed"] ?? [],
                "label" => lang("Proposed project start", "Beantragter Projektbeginn"),
                'type' => 'datetime',
                'input' => 'date',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => true
                ],
            ],
            [
                "id" => "end_proposed",
                "module_of" => $typeModules["end_proposed"] ?? [],
                "label" => lang("Proposed project end", "Beantragtes Projektende"),
                'type' => 'datetime',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => true
                ],
            ],
            [
                "id" => "funder",
                "module_of" => $typeModules["funder"] ?? [],
                "label" => lang("Funder (Category)", "Förderer (Kategorie)"),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('funder'),
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => true,
                    "proposed" => true
                ],
            ],
            [
                "id" => "funding_organization",
                "module_of" => $typeModules["funding_organization"] ?? [],
                "label" => lang("Funding organization", "Förderorganisation"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    // 'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false
                ],
            ],
            [
                "id" => "funding_program",
                "module_of" => $typeModules["funding_program"] ?? [],
                "label" => lang("Funding program", "Förderprogramm"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "funding_number",
                "module_of" => $typeModules["funding_number"] ?? [],
                "label" => lang("Funding reference number", "Förderkennzeichen"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "funding_type",
                "module_of" => $typeModules["funding_type"] ?? [],
                "label" => lang("Funding type", "Förderart"),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('funding-type'),
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "project_type",
                "module_of" => $typeModules["project_type"] ?? [],
                "label" => lang("Project type", "Art des Projekts"),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('project-type'),
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "grant_income",
                "module_of" => $typeModules["grant_income"] ?? [],
                "label" => lang("Grant sum (Institute)", "Fördersumme (Institut)"),
                'type' => 'integer',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "approved" => false
                ],
            ],
            [
                "id" => "grant_income_proposed",
                "module_of" => $typeModules["grant_income_proposed"] ?? [],
                "label" => lang("Proposed grant sum (Institute)", "Beantragte Fördersumme (Institut)"),
                'type' => 'integer',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => false
                ],
            ],
            [
                "id" => "grant_sum",
                "module_of" => $typeModules["grant_sum"] ?? [],
                "label" => lang("Grant sum (total)", "Fördersumme (gesamt)"),
                'type' => 'integer',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "approved" => false
                ],
            ],
            [
                "id" => "grant_sum_proposed",
                "module_of" => $typeModules["grant_sum_proposed"] ?? [],
                "label" => lang("Proposed grant sum (total)", "Beantragte Fördersumme (gesamt)"),
                'type' => 'integer',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => false
                ],
            ],
            [
                "id" => "abstract",
                "module_of" => $typeModules["abstract"] ?? [],
                "label" => lang("Abstract", "Zusammenfassung"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false
                ],
            ],
            [
                "id" => "abstract_de",
                "module_of" => $typeModules["abstract_de"] ?? [],
                "label" => lang("Abstract (German)", "Zusammenfassung (Deutsch)"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false
                ],
            ],
            [
                "id" => "grant_subproject",
                "module_of" => $typeModules["grant_subproject"] ?? [],
                "label" => lang("Grant sum (Subproject)", "Fördersumme (Teilprojekt)"),
                'type' => 'integer',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "approved" => false
                ],
            ],
            [
                "id" => "grant_subproject_proposed",
                "module_of" => $typeModules["grant_subproject_proposed"] ?? [],
                "label" => lang("Proposed grant sum (Subproject)", "Beantragte Fördersumme (Teilprojekt)"),
                'type' => 'integer',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => false
                ],
            ],
            [
                "id" => "internal_number",
                "module_of" => $typeModules["internal_number"] ?? [],
                "label" => lang("Internal ID", "Interne ID"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "approved" => false,
                    "proposed" => false
                ],
            ],
            [
                "id" => "role",
                "module_of" => $typeModules["role"] ?? [],
                "label" => lang("Role of the institute", "Rolle des Instituts"),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('project-institute-role'),
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "coordinator",
                "module_of" => $typeModules["coordinator"] ?? [],
                "label" => lang("Coordinator facility", "Koordinator-Einrichtung"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false
                ],
            ],
            [
                "id" => "scholar",
                "module_of" => $typeModules["scholar"] ?? [],
                "label" => lang("Scholar", "Forschende Person"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "supervisor",
                "module_of" => $typeModules["supervisor"] ?? [],
                "label" => lang("Supervisor", "Betreuende Person"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "university",
                "module_of" => $typeModules["university"] ?? [],
                "label" => lang("University", "Universität"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "scholarship",
                "module_of" => $typeModules["scholarship"] ?? [],
                "label" => lang("Funding organization (Scholarship)", "Förderorganisation (Stipendium)"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "countries",
                "module_of" => $typeModules["countries"] ?? [],
                "label" => lang("Countries of research", "Forschungsländer"),
                'type' => 'string',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "comment",
                "module_of" => $typeModules["comment"] ?? [],
                "label" => lang("Comment", "Kommentar"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false,
                    "rejected" => false
                ],
            ],
            [
                "id" => "nagoya.enabled",
                "module_of" => $typeModules["nagoya"] ?? [],
                "label" => lang("Nagoya Protocol Compliance", "Nagoya Protocol Compliance"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "kdsf-ffk",
                "module_of" => $typeModules["kdsf-ffk"] ?? [],
                "label" => lang("Research fields (KDSF)", "Forschungsfelder (KDSF)"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "public",
                "module_of" => $typeModules["public"] ?? [],
                "label" => lang("Public presentation consent <i class='ph ph-globe portfolio'></i>", "Zustimmung zur öffentlichen Präsentation <i class='ph ph-globe portfolio'></i>"),
                'type' => 'boolean',
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false
                ],
            ],
            [
                "id" => "purpose",
                "module_of" => $typeModules["purpose"] ?? [],
                "label" => lang("Purpose", "Zweck"),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('project-purpose'),
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "proposed" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => "website",
                "module_of" => $typeModules["website"] ?? [],
                "label" => lang("Website", "Webseite"),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
                "scope" => [
                    "project" => false,
                    "approved" => false
                ],
            ],
            [
                "id" => 'collaborators.country',
                "module_of" => ['general'],
                'usage' => [
                    'filter',
                    'columns',
                    'aggregate'
                ],
                "label" => lang('Collaborators (Country)', 'Kooperationspartner (Land)'),
                "type" => 'string',
                "scope" => [
                    "project" => false,
                ],
            ],
            [
                "id" => 'collaborators.location',
                "module_of" => ['general'],
                'usage' => [
                    'filter',
                    'columns',
                    'aggregate'
                ],
                "label" => lang('Collaborators (Location)', 'Kooperationspartner (Ort)'),
                "type" => 'string',
                "scope" => [
                    "project" => false,
                ],
            ],
            [
                "id" => 'collaborators.name',
                "module_of" => ['general'],
                'usage' => [
                    'filter',
                    'columns',
                    'aggregate'
                ],
                "label" => lang('Collaborators (Name)', 'Kooperationspartner (Name)'),
                "type" => 'string',
                "scope" => [
                    "project" => false,
                ],
            ],
            [
                "id" => 'collaborators.role',
                "module_of" => ['general'],
                'usage' => [
                    'filter',
                    'columns',
                    'aggregate'
                ],
                "label" => lang('Collaborators (Role)', 'Kooperationspartner (Rolle)'),
                "type" => 'string',
                "values" => ['gold', 'green', 'bronze', 'hybrid', 'open', 'closed'],
                "input" => 'select',
                "scope" => [
                    "project" => false,
                ],
            ],
            [
                "id" => 'collaborators.ror',
                "module_of" => ['general'],
                'usage' => [
                    'filter',
                    'columns',
                    'aggregate'
                ],
                "label" => lang('Collaborators (ROR)', 'Kooperationspartner (ROR)'),
                "type" => 'string',
                "scope" => [
                    "project" => false,
                ],
            ],
            [
                "id" => 'collaborators.type',
                "module_of" => ['general'],
                'usage' => [
                    'filter',
                    'columns',
                    'aggregate'
                ],
                "label" => lang('Collaborators (Type)', 'Kooperationspartner (Typ)'),
                "type" => 'string',
                "values" => ['Education', 'Healthcare', 'Company', 'Archive', 'Nonprofit', 'Government', 'Facility', 'Other'],
                "input" => 'select',
                "scope" => [
                    "project" => false,
                ],
            ],
        ];

        if ($this->type == 'projects') {
            // Remove proposed and approval fields
            $FIELDS = array_filter($FIELDS, function ($f) {
                if (isset($f['scope']) && !in_array('project', array_keys($f['scope']))) {
                    return false;
                }
                return true;
            });
        } else {
            // Remove project-only fields
            $FIELDS = array_filter($FIELDS, function ($f) {
                $proposals = ['approved', 'proposed', 'rejected'];
                if (isset($f['scope']) && !array_intersect($proposals, array_keys($f['scope']))) {
                    return false;
                }
                return true;
            });
        }

        $units = $osiris->groups->find(['inactive' => ['$ne' => true]], ['sort' => [lang('name', 'name_de') => 1]])->toArray();
        $units = array_column($units, lang('name', 'name_de'), 'id');
        $FIELDS[] = [
            'id' => 'units',
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

        $FIELDS = parent::addCustomFields($FIELDS, $osiris, $typeModules);
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

// dump($FIELDS);
