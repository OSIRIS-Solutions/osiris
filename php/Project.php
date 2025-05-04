<?php

/**
 * Class for all project associated methods.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @package OSIRIS
 * @since 1.2.2
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once "DB.php";
require_once "Vocabulary.php";
require_once "Country.php";
require_once "Groups.php";

class Project extends Vocabulary
{
    public $project = array();

    public $FIELDS = [];

    public const PHASES = [
        [
            'id' => 'proposed',
            'name' => 'Proposed',
            'name_de' => 'Beantragt',
            'color' => 'signal',
            'type' => 'proposal'
        ],
        [
            'id' => 'approved',
            'name' => 'Approved',
            'name_de' => 'Bewilligt',
            'color' => 'success',
            'type' => 'proposal'
        ],
        [
            'id' => 'rejected',
            'name' => 'Rejected',
            'name_de' => 'Abgelehnt',
            'color' => 'danger',
            'type' => 'proposal'

        ],
        [
            'id' => 'project',
            'name' => 'Project',
            'name_de' => 'Projekt',
            'color' => 'primary',
            'type' => 'project'
        ]
    ];

    public const TYPE = [
        'Drittmittel' => 'Drittmittel',
        'Stipendium' => 'Stipendium',
        'Eigenfinanziert' => 'Eigenfinanziert',
        'Teilprojekt' => 'Teilprojekt',
        'other' => 'Sonstiges',
    ];

    public const FUNDING = [
        'funding' => 'Förderung',
        'scholarship' => 'Stipendium',
        'self_funded' => 'Eigenfinanziert',
        'subproject' => 'Teilprojekt',
        'other' => 'Sonstiges',
    ];

    public const COLLABORATOR = [
        'Education' => 'Bildung',
        'Healthcare' => 'Gesundheit',
        'Company' => 'Unternehmen',
        'Archive' => 'Archiv',
        'Nonprofit' => 'Nonprofit',
        'Government' => 'Regierung',
        'Facility' => 'Einrichtung',
        'Other' => 'Sonstiges',
    ];

    // public const INHERITANCE = [
    //     'status',
    //     'website',
    //     'grant_sum',
    //     'grant_income',
    //     'funder',
    //     'funding_organization',
    //     'grant_sum_proposed',
    //     'grant_income_proposed',
    //     'purpose',
    //     'role',
    //     'coordinator',
    // ];
    // public const INHERITANCE_PUBLIC = [
    //     'website',
    //     'funder',
    //     'funding_organization',
    //     'purpose',
    //     'role',
    //     'coordinator',
    //     'collaborators'
    // ];

    function __construct($project = null)
    {
        parent::__construct();

        $this->initFields();

        if ($project !== null)
            $this->project = $project;
    }

    public function initFields()
    {
        // get all fields from file data/project-fields.json
        $fields = json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true);
        if (empty($fields)) return;
        $this->FIELDS = [];
        foreach ($fields as $field) {
            $this->FIELDS[$field['id']] = [
                'id' => $field['id'],
                'en' => $field['en'],
                'de' => $field['de'],
                'kdsf' => $field['kdsf'] ?? null,
                'custom' => false,
                "scope" => $field['scope'] ?? [],
                'order' => $field['order'] ?? 99,
            ];
        }

        $custom_fields = $this->db->adminFields->find();
        foreach ($custom_fields as $field) {
            $this->FIELDS[$field['id']] = [
                'id' => $field['id'],
                'en' => $field['name'],
                'de' => $field['name_de'],
                'kdsf' => null,
                'custom' => true,
                'scope' => ["project" => false, "proposed" => false, "approved" => false],
                'order' => $field['order'] ?? 99,
            ];
        }

        // check if topics are enabled
        $topics = $this->db->adminFeatures->findOne(['feature' => 'topics']);
        if ($topics['enabled'] ?? false) {
            $label = $this->db->adminGeneral->findOne(['key' => 'topics_label']);
            if (empty($label) || empty($label['value'])) $label = ['en' => 'Research topics', 'de' => 'Forschungsbereiche'];
            else $label = $label['value'];
            $this->FIELDS['topics'] = [
                'id' => 'topics',
                'en' => $label['en'],
                'de' => $label['de'],
                'required' => false,
                'kdsf' => null,
                'scope' => ["project" => false, "proposed" => false, "approved" => false],
                'order' => $field['order'] ?? 99,
            ];
        }

        // order by 'order'
        uasort($this->FIELDS, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });
    }

    public function getProjectType($id)
    {
        $filter = [
            'id' => $id
        ];
        $type = $this->db->adminProjects->findOne($filter);
        return DB::doc2Arr($type);
    }

    public function getProjectTypes($include_hidden = false)
    {
        $filter = [];
        if (!$include_hidden) $filter = ['disabled' => ['$ne' => true]];
        return $this->db->adminProjects->find($filter)->toArray();
    }

    public function getFields($type_id, $phase)
    {
        $type = $this->db->adminProjects->findOne(['id' => $type_id]);
        if (empty($type)) return [];
        $phases = $type['phases'] ?? [];

        $fields = [];
        foreach ($phases as $p) {
            if ($p['id'] == $phase) {
                $fields = $p['modules'] ?? [];
                break;
            }
        }
        $fields = DB::doc2Arr($fields);
        foreach ($this->FIELDS as $key => $value) {
            $scope = $value['scope'] ?? [];
            if (array_key_exists($phase, $scope) && $scope[$phase] === true) {
                $fields[] = [
                    'module' => $key,
                    'required' => true
                ];
            }
        }
        // sort by 'order' in $this->FIELDS
        usort($fields, function ($a, $b) {
            $a_order = $this->FIELDS[$a['module']]['order'] ?? 0;
            $b_order = $this->FIELDS[$b['module']]['order'] ?? 0;
            return $a_order <=> $b_order;
        });
        return $fields;
    }

    public function printLabel($key)
    {
        $field = $this->FIELDS[$key] ?? null;
        if (empty($field)) return $key;
        return lang($field['en'], $field['de'] ?? null);
    }

    public function printField($field, $value)
    {
        if (empty($value)) return '-';
        switch ($field) {
            case 'type':
                return $this->getType('');
            case 'website':
                $url = str_replace('https://', '', $value);
                $url = str_replace('http://', '', $url);
                $url = str_replace('www.', '', $url);
                return '<a href="' . $value . '" target="_blank" class="link">' . $url . '</a>';
            case 'start':
            case 'end':
            case 'start_proposed':
            case 'end_proposed':
            case 'submission_date':
            case 'approval_date':
            case 'rejection_date':
                return Document::format_date($value);
            case 'nagoya':
                if ($value == 'no') {
                    return lang('Not relevant', 'Nicht relevant');
                }
                $lang = lang('name', 'name_de');
                $countriesList = '';
                foreach ($this->project['nagoya_countries'] ?? [] as $c) {
                    $countriesList .= '<li>' . $this->getCountry($c, $lang) . '</li>';
                }
                return '<div class="alert signal">'
                    . '<h6 class="title">' . lang('Countries', 'Länder:') . '</h6>'
                    . '<ul class="list signal mb-0">' . $countriesList . '</ul>'
                    . '</div>';

            case 'countries':
                $lang = lang('name', 'name_de');
                $countriesList = '';
                foreach ($value ?? [] as $c) {
                    $role = '';
                    if (isset($c['country'])) {
                        $iso = $c['country'];
                    }
                    if (isset($c['role'])) {
                        $role = ' (' . $c['role'] . ')';
                    }
                    $countriesList .= '<li>' . $this->getCountry($c, $lang) . $role . '</li>';
                }
                return '<ul class="list signal mb-0">' . $countriesList . '</ul>';
            case 'purpose':
                return $this->getPurpose();
            case 'role':
                return $this->getRole();
            case 'status':
                return $this->getStatus();
            case 'funder':
                return $this->getFunder();
            case 'funding_number':
                return $this->getFundingNumbers('<br>');
            case 'funding_type':
                return $this->getFundingType();
            case 'contact':
            case 'stipendiate':
            case 'scholar':
            case 'supervisor':
                return '<a href="' . ROOTPATH . '/profile/' . ($value) . '">' . $this->getNameFromId($value) . '</a>';
            case 'persons':
                $value = DB::doc2Arr($value);
                $value = array_column($value, 'name');
                return implode(', ', $value);
            case 'units':
                $value = DB::doc2Arr($value);
                return implode(', ', $value);
            case 'applicants':
                $applicants = DB::doc2Arr($value);
                $applicantsList = '';
                foreach ($applicants as $a) {
                    $applicantsList .= '<li><a href="' . ROOTPATH . '/profile/' . ($a) . '">' . $this->getNameFromId($a) . '</a></li>';
                }
                return '<ul class="list mb-0">' . $applicantsList . '</ul>';
            case 'grant_sum_proposed':
            case 'grant_income_proposed':
            case 'grant_sum':
            case 'grant_income':
                return number_format($value, 2, ',', '.') . ' €';
            case 'abstract':
            case 'abstract_de':
                // shorten 
                $abstract = $value;
                if (strlen($abstract) > 200) {
                    $abstract = '<div class="preview-text">' . $value . '</div>';
                    $abstract .= '<a class="text-muted font-size-12" onclick="$(this).prev().removeClass(\'preview-text\'); $(this).toggle()">' . lang('Show more', 'Mehr anzeigen') . '...</a>';
                }
                return $abstract;
            case 'kdsf-ffk':
                $return = '<ul class="list mb-0">';
                foreach ($value as $k) {
                    $kdsf = $this->getKDSF($k, 'labels');
                    if (empty($kdsf)) continue;
                    $return .= '<li>' . lang($kdsf['en'], $kdsf['de'] ?? null) . '</li>';
                }
                return $return . '</ul>';
            case 'public':
                if ($value) {
                    return '<span class="text-success"><i class="ph ph-check"></i> ' . lang('yes', 'ja') . '</span>';
                } else {
                    return '<span class="text-danger"><i class="ph ph-x"></i> ' . lang('no', 'nein') . '</span>';
                }
            case 'image':
                if (empty($value)) return '-';
                $image = '<img src="' . ROOTPATH . '/uploads/' . $value . '" class="img-fluid" alt="' . lang('Project image', 'Projektbild') . '">';
                return $image;
            case 'topics':
                $topics = DB::doc2Arr($value);
                $Settings = new Settings();
                return $Settings->printTopics($topics);
            case 'ressources':
                # { "material": "no", "material_details": null, "personnel": "no", "personnel_details": null, "room": "yes", "room_details": "1 Schreibtischarbeitsplatz", "other": "no", "other_details": null }
                $return = '<ul class="list mb-0">';
                foreach (
                    [
                        'material' => lang('Additional material resources', 'Zusätzliche Sachmittel'),
                        'personnel' => lang('Additional personnel resources', 'Zusätzliche Personalmittel'),
                        'room' => lang('Additional room capacities', 'Zusätzliche Raumkapazitäten'),
                        'other' => lang('Other resources', 'Sonstige Ressourcen')
                    ] as $res => $label
                ) {
                    if (isset($value[$res]) && $value[$res] == 'yes') {
                        $details = $value[$res . '_details'] ?? null;
                        if (!empty($details)) {
                            $details = '<br><small>' . $details . '</small>';
                        }
                        $return .= '<li>' . $label . $details . '</li>';
                    }
                }
                return $return . '</ul>';
            case 'funding_organization':
            case 'scholarship':
            case 'university':
                $org = $this->db->organizations->findOne(['_id' => DB::to_ObjectID($value)]);
                if (empty($org)) return $value;
                return '<a href="' . ROOTPATH . '/organizations/view/' . $org['_id'] . '">' . $org['name'] . '</a>';
            default:
                if (is_string($value)) {
                    return $value;
                }
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
    }

    public function getFieldsLegacy($type)
    {
        return 'NO LONGER SUPPORTED';
    }
    public function setProject($project)
    {
        $this->project = $project;
    }
    public function setProjectById($project_id)
    {
        $this->project = $this->db->projects->findOne(['_id' => DB::to_ObjectID($project_id)]);
    }

    public function getStatus($status = '')
    {
        switch ($this->project['status'] ?? $status) {
            case 'applied':
            case 'proposed':
                return "<span class='badge signal'>" . lang('proposed', 'beantragt') . "</span>";
            case 'approved':
            case 'accepted':
                if ($this->inPast())
                    return "<span class='badge dark'>" . lang('expired', 'abgelaufen') . "</span>";
                return "<span class='badge success'>" . lang('approved', 'bewilligt') . "</span>";
            case 'rejected':
                return "<span class='badge danger'>" . lang('rejected', 'abgelehnt') . "</span>";
            case 'finished':
                return "<span class='badge success'>" . lang('finished', 'abgeschlossen') . "</span>";
            case 'project':
                if ($this->inPast())
                    return "<span class='badge dark'>" . lang('ended', 'finished') . "</span>";
                return "<span class='badge primary'>" . lang('ongoing', 'laufend') . "</span>";
            default:
                return "<span class='badge'>" . lang('unknown', 'unbekannt') . "</span>";
        }
    }

    public function getType($cls = '', $default='third-party')
    {
        $type = $this->project['type'] ?? $default;
        $project_type = $this->getProjectType($type);
        if (!empty($project_type)) {
            $style = "style='background-color: " . $project_type['color'] . "33; color: " . $project_type['color'] . "'";
            $return = "<span class='badge no-wrap $cls' $style>";
            if (isset($project_type['icon'])) {
                $return .= '<i class="ph ph-' . $project_type['icon'] . '"></i> ';
            }
            $return .= lang($project_type['name'], $project_type['name_de'] ?? null) . "</span>";
            return $return;
        }

        // LEGACY SUPPORT
        if ($type == 'Drittmittel') { ?>
            <span class="badge text-danger no-wrap <?= $cls ?>">
                <i class="ph ph-hand-coins"></i>
                <?= lang('Third-party funded', 'Drittmittel') ?>
            </span>

        <?php } elseif ($type == 'Stipendium') { ?>
            <span class="badge text-success no-wrap <?= $cls ?>">
                <i class="ph ph-tip-jar"></i>
                <?= lang('Stipendiate', 'Stipendium') ?>
            </span>
        <?php } else if ($type == 'Eigenfinanziert') { ?>
            <span class="badge text-signal no-wrap <?= $cls ?>">
                <i class="ph ph-piggy-bank"></i>
                <?= lang('Self-funded', 'Eigenfinanziert') ?>
            </span>
        <?php } else if ($type == 'Teilprojekt') { ?>
            <span class="badge text-danger no-wrap <?= $cls ?>">
                <i class="ph ph-hand-coins"></i>
                <?= lang('Subproject', 'Teilprojekt') ?>
            </span>
        <?php } else { ?>
            <span class="badge text-muted no-wrap <?= $cls ?>">
                <i class="ph ph-coin"></i>
                <?= lang('Other', 'Sonstiges') ?>
            </span>
<?php }
    }

    public function getRoleRaw()
    {
        $role = $this->project['role'] ?? 'associated';
        return $this->getValue('project-institute-role', $role);
    }

    public function getRole()
    {
        $label = $this->getRoleRaw();
        if (($this->project['role'] ?? '') == 'coordinator') {
            return "<span class='badge no-wrap'>" . '<i class="ph ph-crown text-signal"></i> ' . $label . "</span>";
        }
        if (($this->project['role'] ?? '') == 'associated') {
            return "<span class='badge no-wrap'>" . '<i class="ph ph-address-book text-muted"></i> ' . $label . "</span>";
        }
        return "<span class='badge no-wrap'>" . '<i class="ph ph-handshake text-muted"></i> ' . $label . "</span>";
    }

    public static function getCollaboratorIcon($collab, $cls = "")
    {
        switch ($collab) {
            case 'Education':
                return '<i class="ph ' . $cls . ' ph-graduation-cap"></i>';
            case 'Healthcare':
                return '<i class="ph ' . $cls . ' ph-heartbeat"></i>';
            case 'Company':
                return '<i class="ph ' . $cls . ' ph-buildings"></i>';
            case 'Archive':
                return '<i class="ph ' . $cls . ' ph-archive"></i>';
            case 'Nonprofit':
                return '<i class="ph ' . $cls . ' ph-hand-coins"></i>';
            case 'Government':
                return '<i class="ph ' . $cls . ' ph-bank"></i>';
            case 'Facility':
                return '<i class="ph ' . $cls . ' ph-warehouse"></i>';
            case 'Other':
                return '<i class="ph ' . $cls . ' ph-house"></i>';
            default:
                return '<i class="ph ' . $cls . ' ph-house"></i>';
        }
    }

    public function getFunder()
    {
        $funder = $this->project['funder'] ?? 'others';
        return $this->getValue('funder', $funder);
    }
    public function getFundingType()
    {
        $funder = $this->project['funding_type'] ?? 'others';
        return $this->getValue('funding-type', $funder);
    }

    public function getPurpose()
    {
        $purpose = $this->project['purpose'] ?? 'others';
        return $this->getValue('project-purpose', $purpose);
    }
    function getFundingNumbers($seperator)
    {
        if (!isset($this->project['funding_number']) || empty($this->project['funding_number']))
            return '-';
        if (is_string($this->project['funding_number']))
            return $this->project['funding_number'];
        return implode($seperator, DB::doc2Arr($this->project['funding_number']));
    }

    /**
     * Convert MongoDB document to array.
     *
     * @param $doc MongoDB Document.
     * @return array Document array.
     */
    public function getDateRange()
    {
        $start = $this->getStartDate();
        $end = $this->getEndDate();
        return "$start - $end";
    }

    function inPast()
    {
        $end = new DateTime();
        $end->setDate(
            $this->project['end']['year'],
            $this->project['end']['month'] ?? 1,
            $this->project['end']['day'] ?? 1
        );
        $today = new DateTime();
        if ($end < $today) return true;
        return false;
    }

    public function getStartDate()
    {
        return sprintf('%02d', $this->project['start']['month']) . "/" . $this->project['start']['year'];
    }
    public function getEndDate()
    {
        return sprintf('%02d', $this->project['end']['month']) . "/" . $this->project['end']['year'];
    }
    public function getDuration()
    {
        if (!isset($this->project['start']) || !isset($this->project['end']))
            return '-';

        $start = $this->project['start_date']; // in format yyyy-mm-dd
        $end = $this->project['end_date']; // in format yyyy-mm-dd

        // get number of month between start and end
        $start = new DateTime($start);
        $end = new DateTime($end);
        $interval = $start->diff($end);
        $years = $interval->y;
        $months = $interval->m;

        $total_month = $years * 12 + $months + 1;
        return $total_month;
    }
    public function getProgress()
    {
        $end = new DateTime();
        $end->setDate(
            $this->project['end']['year'],
            $this->project['end']['month'] ?? 1,
            $this->project['end']['day'] ?? 1
        );
        $start = new DateTime();
        $start->setDate(
            $this->project['start']['year'],
            $this->project['start']['month'] ?? 1,
            $this->project['start']['day'] ?? 1
        );
        $today = new DateTime();
        $progress = 0;
        if ($end <= $today) {
            $progress = 100;
        } else {
            $progress = $start->diff($today)->days / $start->diff($end)->days * 100;
        }
        return round($progress);
    }

    public function personRoleRaw($role)
    {
        return $this->getValues('project-person-role', $role);
    }

    public function personRole($role)
    {
        return $this->getValue('project-person-role', $role);
    }

    public function getProjectStatus(){
        if ($this->inPast()){
            return '<i class="ph ph-check-circle text-success"></i> '. lang('ended', 'abgeschlossen');
        } else {
            return '<i class="ph ph-play-circle text-signal"></i> ' . lang('ongoing', 'laufend');
        }
    }

    public function widgetSmall()
    {
        $widget = '<a class="module" href="' . ROOTPATH . '/projects/view/' . $this->project['_id'] . '">';
        $widget .= '<span class="float-right">' . $this->getProjectStatus() . '</span>';
        $widget .= '<h5 class="m-0">' . $this->project['name'] . '</h5>';
        $widget .= '<small class="d-block text-muted mb-5">' . $this->project['title'] . '</small>';
        if (isset($this->project['funder']))
            $widget .= '<span class="float-right text-muted">' . $this->project['funder'] . '</span>';
        $widget .= '<span class="text-muted">' . $this->getDateRange() . '</span>';
        $widget .= '</a>';
        return $widget;
    }

    public function widgetSubproject()
    {
        $contacts = array_column(DB::doc2Arr($this->project['persons']), 'name');
        $widget = '<a class="module" href="' . ROOTPATH . '/projects/view/' . $this->project['_id'] . '">';
        $widget .= '<h5 class="m-0">' . $this->project['name'] . '</h5>';
        $widget .= '<small class="d-block text-muted mb-5">' . $this->project['title'] . '</small>';
        // contact
        if (!empty($contacts)) {
            $widget .= '<span class=" text-muted">';
            $widget .= '<i class="ph ph-user"></i> ' . implode(', ', $contacts) . ' ';
            $widget .= '</span>';
        }
        $widget .= '</a>';
        return $widget;
    }

    public function widgetPortal($cls = "module")
    {
        $widget = '<a class="' . $cls . '" href="' . PORTALPATH . '/project/' . $this->project['_id'] . '">';
        $widget .= '<span class="float-right">' . $this->getProjectStatus() . '</span>';
        $widget .= '<h5 class="m-0">' . $this->project['name'] . '</h5>';
        $widget .= '<p class="d-block text-muted">' . $this->project['title'] . '</p>';
        if (isset($this->project['funder']))
            $widget .= '<span class="float-right text-muted">' . $this->project['funder'] . '</span>';
        $widget .= '<span class="text-muted">' . $this->getDateRange() . '</span>';
        $widget .= '</a>';
        return $widget;
    }


    public function widgetLarge($user = null, $external = false)
    {
        $widget = '<a class="module" href="' . ROOTPATH . '/projects/view/' . $this->project['_id'] . '" ' . ($external ? 'target="_blank"' : '') . '>';
        $widget .= '<span class="float-right">' . $this->getDateRange() . '</span>';
        $widget .= '<h5 class="m-0">' . $this->project['name'] . '</h5>';
        $widget .= '<small class="d-block text-muted mb-5">' . $this->project['title'] . '</small>';

        if ($user === null)
            $widget .= '<span class="float-right">' . $this->getRole() . '</span> ';
        else {
            $userrole = '';
            foreach ($this->project['persons'] as $p) {
                if ($p['user'] == $user) {
                    $userrole = $p['role'];
                    break;
                }
            }
            $widget .= '<span class="float-right badge">' . $this->personRole($userrole) . '</span> ';
        }
        if ($this->project['status'] != 'project') {
            $widget .= '<span class="mr-10">' . $this->getStatus() . '</span> ';
        }
        if (isset($this->project['funder']))
            $widget .= '<span class="text-muted">' . $this->project['funder'] . '</span>';
        $widget .= '</a>';
        return $widget;
    }

    public function getScope()
    {
        $req = $this->db->adminGeneral->findOne(['key' => 'affiliation']);
        $institute = DB::doc2Arr($req['value']);
        $institute['role'] = $this->project['role'] ?? 'Partner';

        $collaborators = DB::doc2Arr($this->project['collaborators'] ?? []);
        if (!empty($collaborators)) {
            $collaborators = array_merge($collaborators, [$institute]);
        }

        $scope = 'local';
        $countries = array_column($collaborators, 'country');
        if (empty($countries)) return ['scope' => $scope, 'region' => '-'];

        $scope = 'national';
        $countries = array_unique($countries);
        if (count($countries) == 1) return ['scope' => $scope, 'region' => $this->getCountry($countries[0], 'name')];

        $scope = 'continental';
        $continents = [];
        foreach ($countries as $code) {
            $continents[] = $this->getCountry($code, 'continent');
        }
        $continents = array_unique($continents);
        if (count($continents) == 1) return ['scope' => $scope, 'region' => $continents[0]];

        $scope = 'international';
        return ['scope' => $scope, 'region' => 'world'];
    }

    public function getContinents()
    {
        $collaborators = DB::doc2Arr($this->project['collaborators'] ?? []);
        $countries = array_column($collaborators, 'country');
        $countries = array_unique($countries);
        $continents = [];
        foreach ($countries as $code) {
            $continents[] = $this->getCountry($code, 'continent');
        }
        $continents = array_unique($continents);
        return $continents;
    }
    public function getUnits($depts_only = false)
    {
        // get units based on project persons
        $units = [];
        $Groups = new Groups();

        $start = $this->project['start_date'];
        foreach ($this->project['persons'] as $person) {
            $u = DB::doc2Arr($person['units'] ?? []);
            if (empty($u)) {
                $u = $Groups->getPersonUnit($person['user'], $start);
                if (empty($u)) continue;
                $u = array_column($u, 'unit');
            }

            if (!empty($u)) {
                $units = array_merge($units, $u);
            }
        }
        if (!$depts_only) return $units;
        return $Groups->deptHierarchies($units);
    }

    
    /**
     * Function to convert array into human readable Module fields
     */
    private function convertProject4humans($doc)
    {

        $omit_fields = ['_id', 'history', 'comment', 'files', 'activities', 'updated_by', 'updated', 'start_date'];

        $result = [];

        foreach ($doc as $key => $val) {
            if (in_array($key, $omit_fields)) continue;
            $val = $this->printField($key, $val);
            if ($val instanceof BSONArray || $val instanceof BSONDocument) {
                $val = DB::doc2Arr($val);
            }
            if (is_array($val)) {
                if (is_string($val[0])) {
                    $val = implode(', ', $val);
                } else {
                    $val = json_encode($val);
                }
            }
            $val = strip_tags($val);
            $result[$key] = $val;
        }
        return $result;
    }

    /**
     * function to add update history in a document
     */
    public function updateHistory($new_doc, $id, $collection = 'projects')
    {
        if (DB::is_ObjectID($id)) {
            $id = $this->to_ObjectID($id);
        }
        $old_doc = $this->db->$collection->findOne(['_id' => $id]);
        $hist = [
            'date' => date('Y-m-d'),
            'user' => $_SESSION['username'] ?? 'system',
            'type' => 'edited',
            // 'current' => 'unchanged'
            'changes' => []
        ];
        $new_ = $this->convertProject4humans($new_doc);
        $old_ = $this->convertProject4humans($old_doc);
        $diff = array_diff_assoc($new_, $old_);

        if (!empty($diff)) {
            $changes = [];
            foreach ($diff as $key => $val) {
                $changes[$key] = ['before' => $old_[$key] ?? null, 'after' => $val];
            }
            $hist['changes'] = $changes;
        }
        // dump($hist, true);
        // die;
        $new_doc['history'] = $old_doc['history'] ?? [];
        $new_doc['history'][] = $hist;
        return $new_doc;
    }
}

