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
        $types = array_column($adminCategories, lang('common.field_name_language'), 'id');

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
                'label' => lang('common.id'),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
            ],
            [
                'id' => 'acronym',
                'module_of' => $typeModules['acronym'] ?? [],
                'label' => lang('projects.acronym'),
                'type' => 'string',
                'usage' => [
                    'filter',
                    'columns'
                ],
            ],
            [
                "id" => "type",
                "module_of" => $typeModules["type"] ?? [],
                "label" => lang('common.type'),
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
                'label' => lang('projects.project_staff_name'),
                'type' => 'list'
            ],
            [
                'id' => 'persons.user',
                'module_of' => $typeModules['persons'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('projects.project_staff_username'),
                'type' => 'list'
            ],
            [
                'id' => 'persons.role',
                'module_of' => $typeModules['persons'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('projects.project_staff_role'),
                'type' => 'list',
                'input' => 'select',
                'values' => $this->vocabularyValues('project-person-role'),
            ],
            [
                "id" => "name",
                "module_of" => $typeModules["name"] ?? [],
                "label" => lang('projects.short_title'),
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
                "label" => lang('projects.short_title_german'),
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
                "label" => lang('events.full_title'),
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
                "label" => lang('projects.full_title_german'),
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
                "label" => lang('common.status'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'proposed' => lang('projects.proposed'),
                    'approved' => lang('projects.approved'),
                    'rejected' => lang('projects.rejected'),
                    'withdrawn' => lang('projects.withdrawn'),
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
                "label" => lang('projects.applicants'),
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
                "label" => lang('projects.submission_date'),

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
                "label" => lang('projects.approval_date'),

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
                "label" => lang('projects.rejection_date'),
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
                "label" => lang('projects.project_start_project_fields'),
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
                "label" => lang('projects.project_end_project_fields'),
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
                "label" => lang('projects.proposed_project_start'),
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
                "label" => lang('projects.proposed_project_end'),
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
                "label" => lang('projects.funder_category'),
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
                "label" => lang('common.funding_organizations'),
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
                "id" => "funding_program_select",
                "module_of" => $typeModules["funding_program_select"] ?? [],
                "label" => lang('projects.funding_program_category'),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('funding-program'),
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
                "id" => "funding_program",
                "module_of" => $typeModules["funding_program"] ?? [],
                "label" => lang('projects.funding_program'),
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
                "label" => lang('projects.funding_reference_number'),
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
                "label" => lang('projects.funding_type_project_fields'),
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
                "label" => lang('projects.project_type_project_fields'),
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
                "id" => "joint_project",
                "module_of" => $typeModules["joint_project"] ?? [],
                "label" => lang('projects.joint_project'),
                'type' => 'boolean',
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
                "id" => "joint_project_identifier",
                "module_of" => $typeModules["joint_project"] ?? [],
                "label" => lang('projects.joint_project_identifier'),
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
                "id" => "joint_project_title",
                "module_of" => $typeModules["joint_project"] ?? [],
                "label" => lang('projects.joint_project_title'),
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
                "id" => "joint_project_speaker",
                "module_of" => $typeModules["joint_project"] ?? [],
                "label" => lang('projects.joint_project_speaker'),
                'type' => 'boolean',
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
                "id" => "grant_income",
                "module_of" => $typeModules["grant_income"] ?? [],
                "label" => lang('projects.grant_sum_institute_project_fields'),
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
                "label" => lang('projects.proposed_grant_sum_institute'),
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
                "label" => lang('projects.grant_sum_total_project_fields'),
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
                "label" => lang('projects.proposed_grant_sum_total'),
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
                "label" => lang('projects.abstract'),
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
                "label" => lang('projects.abstract_german_project_fields'),
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
                "label" => lang('projects.grant_sum_subproject_project_fields'),
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
                "label" => lang('projects.proposed_grant_sum_subproject'),
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
                "label" => lang('common.internal_id'),
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
                "label" => lang('projects.role_of_the_institute'),
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
                "label" => lang('projects.coordinator_facility'),
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
                "label" => lang('projects.scholar'),
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
                "label" => lang('projects.supervisor'),
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
                "label" => lang('organizations.university'),
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
                "label" => lang('projects.funding_organization_scholarship'),
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
                "label" => lang('projects.countries_of_research_project_fields'),
                'type' => 'list',
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
                "label" => lang('common.comment'),
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
                "label" => lang('projects.nagoya_protocol_compliance'),
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
                "label" => lang('projects.research_fields_kdsf'),
                'type' => 'list',
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
                "label" => lang('projects.public_presentation_consent'),
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
                "label" => lang('projects.purpose'),
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
                "label" => lang('infrastructures.website'),
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
                "label" => lang('projects.collaborators_country'),
                "type" => 'list',
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
                "label" => lang('projects.collaborators_location'),
                "type" => 'list',
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
                "label" => lang('projects.collaborators_name'),
                "type" => 'list',
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
                "label" => lang('projects.collaborators_role'),
                "type" => 'list',
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
                "label" => lang('projects.collaborators_ror'),
                "type" => 'list',
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
                "label" => lang('projects.collaborators_type'),
                "type" => 'list',
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

        $units = $osiris->groups->find(['inactive' => ['$ne' => true]], ['sort' => [lang('common.field_name_language') => 1]])->toArray();
        $units = array_column($units, lang('common.field_name_language'), 'id');
        $FIELDS[] = [
            'id' => 'units',
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
