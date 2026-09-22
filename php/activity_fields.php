<?php
require_once "fields.php";

class ActivityFields extends Fields
{

    function __construct()
    {
        parent::__construct();
        $Settings = new Settings();
        $DB = new DB();
        $osiris = $DB->db;
        $types = $osiris->adminCategories->find()->toArray();
        $types = array_column($types, lang('common.field_name_language'), 'id');

        $subtypes = $osiris->adminTypes->find()->toArray();
        $subtypes = array_column($subtypes, lang('common.field_name_language'), 'id');


        $adminCategories = $osiris->adminCategories->find()->toArray();
        $typeModules = [];
        foreach ($adminCategories as $m) {
            $modules = $osiris->adminTypes->distinct('modules', ['parent' => $m['id']]);
            // merge all 'modules' keys
            $modules = array_map(fn($m) => str_replace('*', '', $m), $modules);
            $modules = array_unique($modules);
            foreach ($modules as $module) {
                if (!isset($typeModules[$module])) $typeModules[$module] = [];
                if (!in_array($m['id'], $typeModules[$module])) $typeModules[$module][] = $m['id'];
            }
        }

        $typeModules = array_merge($typeModules, [
            'print' => ['general'],
            'web' => ['general'],
            'icon' => ['general'],
            'type' => ['general'],
            'subtype' => ['general'],
            'title' => ['general'],
            'authors' => ['general'],
            'year' => ['general'],
            'month' => ['general'],
            'start_date' => ['general'],
            'end_date' => ['general'],
            'created' => ['general'],
            'created_by' => ['general'],
            'updated' => ['general'],
            'updated_by' => ['general'],
            'imported' => ['general'],
            'imported_by' => ['general'],
            'topics' => ['general'],
            'affiliated' => ['general'],
            'affiliated_positions' => ['general'],
            'cooperative' => ['general'],
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
                'id' => 'print',
                'module_of' => $typeModules['print'] ?? [],
                'usage' => [
                    'columns',
                    'filter'
                ],
                'label' => lang('activities.print_version'),
                'type' => 'string'
            ],
            [
                'id' => 'web',
                'module_of' => $typeModules['web'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('activities.web_version'),
                'type' => 'string'
            ],
            [
                'id' => 'icon',
                'module_of' => $typeModules['icon'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('admin.icon'),
                'type' => 'string'
            ],
            [
                'id' => 'type',
                'module_of' => $typeModules['type'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.category'),
                'type' => 'string',
                'input' => 'select',
                'values' => $types
            ],
            [
                'id' => 'subtype',
                'module_of' => $typeModules['subtype'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.type'),
                'type' => 'string',
                'input' => 'select',
                'values' => $subtypes
            ],
            [
                'id' => 'title',
                'module_of' => $typeModules['title'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('common.title'),
                'type' => 'string'
            ],
            [
                'id' => 'start_date',
                'module_of' => $typeModules['start_date'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('common.start_date'),
                'type' => 'datetime',
                'input' => 'date',
            ],
            [
                'id' => 'end_date',
                'module_of' => $typeModules['end_date'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('common.end_date'),
                'type' => 'datetime',
                'input' => 'date',
            ],
            [
                'id' => 'abstract',
                'module_of' => $typeModules['abstract'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.abstract'),
                'type' => 'string'
            ],
            [
                'id' => 'authors',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('common.authors_all_activities'),
                'type' => 'list',
            ],
            [
                'id' => 'authors.first',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.author_first_name'),
                'type' => 'string'
            ],
            [
                'id' => 'authors.last',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.author_last_name'),
                'type' => 'string'
            ],
            [
                'id' => 'authors.user',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.author_username'),
                'type' => 'string'
            ],
            [
                'id' => 'authors.position',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.author_position'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['first', 'middle', 'last', 'corresponding']
            ],
            [
                'id' => 'authors.approved',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [],
                'label' => lang('activities.author_approved'),
                'type' => 'boolean',
            ],
            [
                'id' => 'authors.aoi',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.author_affiliated'),
                'type' => 'boolean',
            ],
            [
                'id' => 'authors.units',
                'module_of' => $typeModules['authors'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.author_unit'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('teaching.supervisors'),
                'type' => 'list',
            ],
            [
                'id' => 'supervisors.first',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.supervisor_first_name'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.last',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.supervisor_last_name'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.user',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.supervisor_username'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.approved',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [],
                'label' => lang('activities.supervisor_approved'),
                'type' => 'boolean',
            ],
            [
                'id' => 'supervisors.aoi',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.supervisor_affiliated'),
                'type' => 'boolean',
            ],
            [
                'id' => 'supervisors.units',
                'module_of' => $typeModules['supervisor'] ?? $typeModules['supervisor-thesis'] ?? [],
                'usage' => [
                    'filter',
                ],
                'label' => lang('activities.supervisor_unit'),
                'type' => 'string'
            ],
            [
                'id' => 'supervisors.sws',
                'module_of' => $typeModules['supervisor'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.supervisor_sws'),
                'type' => 'integer'
            ],
            [
                'id' => 'editors',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'columns'
                ],
                'label' => lang('activities.editor'),
                'type' => 'list',
            ],
            [
                'id' => 'editors.first',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.editor_first_name'),
                'type' => 'string'
            ],
            [
                'id' => 'editors.last',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.editor_last_name'),
                'type' => 'string'
            ],
            [
                'id' => 'editors.user',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.editor_username'),
                'type' => 'string'
            ],
            [
                'id' => 'editors.aoi',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.editor_affiliated'),
                'type' => 'boolean',
            ],
            [
                'id' => 'editors.units',
                'module_of' => $typeModules['editors'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.editor_unit'),
                'type' => 'string'
            ],
            [
                'id' => 'affiliated',
                'module_of' => $typeModules['affiliated'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.affiliated'),
                'type' => 'boolean',
            ],
            [
                'id' => 'affiliated_positions',
                'module_of' => $typeModules['affiliated_positions'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.affiliated_positions'),
                'type' => 'list',
                'input' => 'select',
                'values' => [
                    'first' => lang('common.first_author'),
                    'last' => lang('common.last_author'),
                    'first_and_last' => lang('common.first_and_last_author'),
                    'first_or_last' => lang('common.first_or_last_author'),
                    'middle' => lang('common.middle_author'),
                    'single' => lang('common.one_single_affiliated_author'),
                    'none' => lang('common.no_author_affiliated_view'),
                    'all' => lang('common.all_authors_affiliated'),
                    'corresponding' => lang('common.corresponding_author'),
                    'not_first' => lang('common.not_first_author'),
                    'not_last' => lang('common.not_last_author'),
                    'not_middle' => lang('common.not_middle_author'),
                    'not_corresponding' => lang('common.not_corresponding_author'),
                    'not_first_or_last' => lang('common.not_first_or_last_author')
                ]
            ],
            [
                'id' => 'cooperative',
                'module_of' => $typeModules['cooperative'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.cooperation_type'),
                'type' => 'string',
                'values' => [
                    'individual' => lang('activities.individual_only_one_affiliated_author'),
                    'departmental' => lang('activities.departmental_cooperation_within_one_department'),
                    'institutional' => lang('activities.institutional_cooperation_between_departments_of_the_same_institute'),
                    'contributing' => lang('activities.contributing_cooperation_with_other_institutes_with_middle_authorships'),
                    'leading' => lang('activities.leading_cooperation_with_other_institutes_with_a_corresponding_role_first_o'),
                    'none' => lang('activities.none_no_author_affiliated')
                ],
                'input' => 'select'
            ],
            [
                'id' => 'journal',
                'module_of' => $typeModules['journal'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => $Settings->journalLabel(),
                'type' => 'string'
            ],
            [
                'id' => 'issn',
                'module_of' => $typeModules['issn'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('journals.issn'),
                'type' => 'list'
            ],
            [
                'id' => 'magazine',
                'module_of' => $typeModules['magazine'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.magazine'),
                'type' => 'string'
            ],
            [
                'id' => 'year',
                'module_of' => $typeModules['year'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.year'),
                'type' => 'integer',
                'default_value' => CURRENTYEAR
            ],
            [
                'id' => 'history',
                'module_of' => [],
                'usage' => [],
                'label' => lang('activities.history'),
                'type' => 'list'
            ],
            [
                'id' => 'workflow',
                'module_of' => [],
                'usage' => [],
                'label' => lang('workflows.workflow'),
                'type' => 'list'
            ],
            [
                'id' => 'rendered',
                'module_of' => [],
                'usage' => [],
                'label' => lang('activities.rendered'),
                'type' => 'list'
            ],
            [
                'id' => 'license',
                'module_of' => $typeModules['license'] ?? [],
                'usage' => [
                    'filter',
                    'aggregate',
                    'columns'
                ],
                'label' => lang('common.license'),
                'type' => 'string'
            ],
            [
                'id' => 'month',
                'module_of' => $typeModules['month'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.month'),
                'type' => 'integer'
            ],
            [
                'id' => 'lecture_type',
                'module_of' => $typeModules['lecture_type'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'type' => 'string',
                'label' => lang('activities.lecture_type'),
                'input' => 'select',
                'values' => ['short', 'long', 'repetition']
            ],
            [
                'id' => 'editorial',
                'module_of' => $typeModules['editorial'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.editor_type'),
                'type' => 'string'
            ],
            [
                'id' => 'doi',
                'module_of' => $typeModules['doi'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.doi'),
                'type' => 'string'
            ],
            [
                'id' => 'link',
                'module_of' => $typeModules['link'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('common.link'),
                'type' => 'string'
            ],
            [
                'id' => 'pubmed',
                'module_of' => $typeModules['pubmed'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.pubmed_id'),
                'type' => 'integer'
            ],
            [
                'id' => 'pubtype',
                'module_of' => $typeModules['pubtype'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.publication_type'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['article', 'book', 'chapter', 'preprint', 'magazine', 'dissertation', 'others']
            ],
            [
                'id' => 'gender',
                'module_of' => $typeModules['gender'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.gender'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['f', 'm', 'd']
            ],
            [
                'id' => 'issue',
                'module_of' => $typeModules['issue'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.issue'),
                'type' => 'string'
            ],
            [
                'id' => 'volume',
                'module_of' => $typeModules['volume'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.volume'),
                'type' => 'string'
            ],
            [
                'id' => 'pages',
                'module_of' => $typeModules['pages'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.pages'),
                'type' => 'string'
            ],
            [
                'id' => 'impact',
                'module_of' => $typeModules['journal'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => $Settings->impactLabel(),
                'type' => 'double'
            ],
            [
                'id' => 'quartile',
                'module_of' => $typeModules['journal'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.quartile'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['Q1', 'Q2', 'Q3', 'Q4']
            ],
            [
                'id' => 'book-title',
                'module_of' => $typeModules['book-title'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.book_title'),
                'type' => 'string'
            ],
            [
                'id' => 'publisher',
                'module_of' => $typeModules['publisher'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('journals.publisher'),
                'type' => 'string'
            ],
            [
                'id' => 'city',
                'module_of' => $typeModules['city'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.location_publisher'),
                'type' => 'string'
            ],
            [
                'id' => 'edition',
                'module_of' => $typeModules['edition'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.edition'),
                'type' => 'string'
            ],
            [
                'id' => 'isbn',
                'module_of' => $typeModules['isbn'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.isbn'),
                'type' => 'string'
            ],
            [
                'id' => 'doctype',
                'module_of' => $typeModules['doctype'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('documents.document_type'),
                'type' => 'string'
            ],
            [
                'id' => 'iteration',
                'module_of' => $typeModules['iteration'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.iteration_misc'),
                'type' => 'string',
                'input' => 'select',
                'values' => ['once', 'annual']
            ],
            [
                'id' => 'software_type',
                'module_of' => $typeModules['software_type'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.type_of_software'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'software' => lang('activities.software'),
                    'database' => lang('admin.database'),
                    'dataset' => lang('activities.dataset'),
                    'webtool' => lang('activities.webtool'),
                    'report' => lang('reports.report')
                ]
            ],
            [
                'id' => 'software_venue',
                'module_of' => $typeModules['software_venue'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.publication_venue_software'),
                'type' => 'string'
            ],
            [
                'id' => 'version',
                'module_of' => $typeModules['version'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('common.version'),
                'type' => 'string'
            ],
            [
                'id' => 'category',
                'module_of' => $typeModules['category'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.category_students_guests'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'guest scientist' => lang('common.guest_scientist'),
                    'lecture internship' => lang('common.lecture_internship'),
                    'student internship' => lang('common.student_internship'),
                    'other' => lang('common.other'),
                    'doctoral thesis' => lang('activities.doctoral_thesis'),
                    'master thesis' => lang('activities.master_thesis'),
                    'bachelor thesis' => lang('activities.bachelor_thesis')
                ]
            ],
            [
                'id' => 'thesis',
                'module_of' => $typeModules['thesis'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.thesis_type'),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('thesis')
            ],
            [
                'id' => 'pub-language',
                'module_of' => $typeModules['pub-language'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.publication_language'),
                'type' => 'string',
                'input' => 'select',
                'values' => $this->vocabularyValues('pub-language')
            ],
            [
                'id' => 'status',
                'module_of' => $typeModules['status'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.thesis_status'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'in progress' => lang('documents.in_progress'),
                    'completed' => lang('common.completed'),
                    'aborted' => lang('error.aborted')
                ]
            ],
            [
                'id' => 'name',
                'module_of' => $typeModules['name'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('guests.name_of_guest'),
                'type' => 'string'
            ],
            [
                'id' => 'academic_title',
                'module_of' => $typeModules['academic_title'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.academic_title_of_guest'),
                'type' => 'string'
            ],
            [
                'id' => 'details',
                'module_of' => $typeModules['details'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('common.details'),
                'type' => 'string'
            ],
            [
                'id' => 'conference',
                'module_of' => $typeModules['conference'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.event'),
                'type' => 'string'
            ],
            [
                'id' => 'location',
                'module_of' => $typeModules['location'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.location'),
                'type' => 'string'
            ],
            [
                'id' => 'country',
                'module_of' => $typeModules['country'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('common.country'),
                'type' => 'string'
            ],
            [
                'id' => 'peer_reviewed',
                'module_of' => $typeModules['peer-reviewed'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.peer_reviewed'),
                'type' => 'boolean',
            ],
            [
                'id' => 'open_access',
                'module_of' => $typeModules['openaccess'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('journals.open_access'),
                'type' => 'boolean',
            ],
            [
                'id' => 'oa_status',
                'module_of' => $typeModules['openaccess-status'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.open_access_status'),
                'type' => 'string',
                'values' => ['gold', 'diamond', 'green', 'bronze', 'hybrid', 'open', 'closed'],
                'input' => 'select'
            ],
            [
                'id' => 'epub',
                'module_of' => $typeModules['epub'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.online_ahead_of_print'),
                'type' => 'boolean',
            ],
            [
                'id' => 'correction',
                'module_of' => $typeModules['correction'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.correction'),
                'type' => 'boolean',
            ],
            [
                'id' => 'political_consultation',
                'module_of' => $typeModules['political_consultation'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.contribution_to_political_and_social_consulting'),
                'type' => 'string',
                'values' => ['Gutachten', 'Positionspapier', 'Studie', 'Sonstiges', ''],
                'input' => 'select'
            ],
            [
                'id' => 'invited_lecture',
                'module_of' => $typeModules['lecture-invited'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.invited_lecture'),
                'type' => 'boolean',
            ],
            [
                'id' => 'created_by',
                'module_of' => $typeModules['created_by'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.created_by_abbreviation'),
                'type' => 'string'
            ],
            [
                'id' => 'created',
                'module_of' => $typeModules['created'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.created_at_activity_fields'),
                'type' => 'datetime',
                'input' => 'date'
            ],
            [
                'id' => 'imported',
                'module_of' => $typeModules['imported'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.imported_at'),
                'type' => 'datetime',
                'input' => 'date'
            ],
            [
                'id' => 'updated',
                'module_of' => $typeModules['updated'] ?? [],
                'usage' => [
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.updated_at'),
                'type' => 'datetime',
                'input' => 'date'
            ],
            [
                'id' => 'updated_by',
                'module_of' => $typeModules['updated_by'] ?? [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.updated_by_abbreviation'),
                'type' => 'string'
            ],
            [
                'id' => 'exclude_from_reports',
                'module_of' => ['general'],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.reports_exclude'),
                'type' => 'boolean',
            ],
            [
                'id' => 'rendered.users',
                'module_of' => [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.user_names'),
                'type' => 'list'
            ],
            [
                'id' => 'openalex.topics.id',
                'module_of' => 'publication',
                'usage' => [
                    'aggregate',
                    'filter'
                ],
                'label' => lang('activities.research_spectrum_id'),
                'type' => 'string'
            ],
            [
                'id' => 'projects',
                'module_of' => ['general'],
                'usage' => [
                    'filter',
                ],
                'label' => lang('activities.project_id'),
                'type' => 'string'
            ]
        ];

        $units = $osiris->groups->find(['inactive' => ['$ne' => true]], ['sort' => [lang('common.field_name_language') => 1], 'projection' => ['_id' => 1, 'id'=> 1, 'name' => 1, 'name_de' => 1]])->toArray();
        $units = array_column(DB::doc2Arr($units), lang('common.field_name_language'), 'id');
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

        if ($Settings->featureEnabled('quality-workflow')) {
            $workflowTypes = $osiris->adminCategories->find(['workflow' => ['$ne' => null]])->toArray();
            $FIELDS[] = [
                'id' => 'workflow.status',
                'module_of' => $workflowTypes ? array_column($workflowTypes, 'id') : [],
                'usage' => [
                    'aggregate',
                    'filter',
                    'columns'
                ],
                'label' => lang('activities.workflow_status_activity_fields'),
                'type' => 'string',
                'input' => 'select',
                'values' => [
                    'verified' => lang('activities.verified'),
                    'rejected' => lang('common.rejected'),
                    'in_progress' => lang('activities.in_process'),
                ]
            ];
        }

        

        $FIELDS = parent::addCustomFields($FIELDS, $osiris, $typeModules);
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
