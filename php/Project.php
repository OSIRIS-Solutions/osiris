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
require_once "Country.php";
require_once "Groups.php";

class Project
{
    public $project = array();

    public $fields = [
        'Drittmittel' => [
            'name',
            'title',
            'status',
            'time',
            'abstract',
            'public',
            'internal_number',
            'website',
            'grant_sum',
            'grant_income',
            'funder',
            'funding_organization',
            'funding_number',
            'grant_sum_proposed',
            'grant_income_proposed',
            'personnel',
            'ressources',
            'contact',
            'purpose',
            'role',
            'coordinator',
            'nagoya',
            'countries'
        ],
        'Stipendium' => [
            'name',
            'title',
            'status',
            'time',
            'abstract',
            'public',
            'internal_number',
            'website',
            'grant_sum',
            'grant_income',
            'supervisor',
            'scholar',
            'scholarship',
            'university',
        ],
        'Eigenfinanziert' => [
            'name',
            'title',
            'status',
            'time',
            'abstract',
            'public',
            'internal_number',
            'website',
            'personnel',
            'ressources',
            'contact',
        ],
        'Teilprojekt' => [
            'name',
            'title',
            'time',
            'abstract',
            'public',
            'internal_number',
            'grant_subproject',
            'funding_number',
            'grant_subproject_proposed',
            'personnel',
            'ressources',
            'contact',
            // 'status',
            // 'website',
            // 'grant_sum',
            // 'grant_income',
            // 'funder',
            // 'funding_organization',
            // 'grant_sum_proposed',
            // 'grant_income_proposed',
            // 'purpose',
            // 'role',
            // 'coordinator',
        ],
        'default' => [
            'name',
            'title',
            'status',
            'time',
            'abstract',
            'public',
            'internal_number',
            'website',
        ]
    ];
    public const FIELDS = [
        'abstract',
        'contact',
        'coordinator',
        'funder',
        'funding_number',
        'funding_organization',
        'grant_income_proposed',
        'grant_income',
        'grant_subproject_proposed',
        'grant_subproject',
        'grant_sum_proposed',
        'grant_sum',
        'internal_number',
        'nagoya',
        'countries',
        'name',
        'personnel',
        'public',
        'purpose',
        'ressources',
        'role',
        'scholar',
        'scholarship',
        'status',
        'supervisor',
        'time',
        'title',
        'university',
        'website',
    ];

    public const STATUS = [
        'applied' => 'beantragt',
        'approved' => 'bewilligt',
        'rejected' => 'abgelehnt',
        'finished' => 'abgeschlossen',
    ];

    public const PURPOSE = [
        'research' => 'Forschung',
        'teaching' => 'Lehre',
        'promotion' => 'Förderung des wissenschaftlichen Nachwuchs',
        'transfer' => 'Transfer',
        'others' => 'Sonstiger Zweck',
    ];

    public const TYPE = [
        'Drittmittel' => 'Drittmittel',
        'Stipendium' => 'Stipendium',
        'Eigenfinanziert' => 'Eigenfinanziert',
        'Teilprojekt' => 'Teilprojekt',
        'other' => 'Sonstiges',
    ];

    public const ROLE = [
        'coordinator' => 'Koordinator',
        'partner' => 'Partner',
        'associated' => 'Beteiligt',
    ];

    public const FUNDING = [
        'funding' => 'Förderung',
        'scholarship' => 'Stipendium',
        'self_funded' => 'Eigenfinanziert',
        'subproject' => 'Teilprojekt',
        'other' => 'Sonstiges',
    ];
    public const FUNDER = [
        'DFG',
        'Bund',
        'Bundesländer',
        'Wirtschaft',
        'EU',
        'Stiftungen',
        'Leibniz Wettbewerb',
        'Sonstige Drittmittelgeber',
    ];

    public const PERSON_ROLE = [
        'PI' => 'Projektleitung',
        'applicant' => 'Antragsteller:in',
        'worker' => 'Projektmitarbeiter:in',
        'scholar' => 'Stipediat:in',
        'supervisor' => 'Betreuer:in',
        'associate' => 'Beteiligte Person',
        'coordinator' => 'Wiss. Koordinator:in'
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

    public const INHERITANCE = [
        'status',
        'website',
        'grant_sum',
        'grant_income',
        'funder',
        'funding_organization',
        'grant_sum_proposed',
        'grant_income_proposed',
        'purpose',
        'role',
        'coordinator',
    ];
    public const INHERITANCE_PUBLIC = [
        'website',
        'funder',
        'funding_organization',
        'purpose',
        'role',
        'coordinator',
        'collaborators'
    ];

    private $db;

    function __construct($project = null)
    {
        if ($project !== null)
            $this->project = $project;
        $DB = new DB();
        $this->db = $DB->db;
    }

    public function getFields($type)
    {
        return $this->fields[$type] ?? $this->fields['default'];
    }
    public function setProject($project)
    {
        $this->project = $project;
    }
    public function setProjectById($project_id)
    {
        $this->project = $this->db->projects->findOne(['_id' => DB::to_ObjectID($project_id)]);
    }

    public function getStatus()
    {
        switch ($this->project['status'] ?? '') {
            case 'applied':
                return "<span class='badge signal'>" . lang('applied', 'beantragt') . "</span>";
            case 'approved':
                if ($this->inPast())
                    return "<span class='badge dark'>" . lang('expired', 'abgelaufen') . "</span>";
                return "<span class='badge success'>" . lang('approved', 'bewilligt') . "</span>";
            case 'rejected':
                return "<span class='badge danger'>" . lang('rejected', 'abgelehnt') . "</span>";
            case 'finished':
                return "<span class='badge success'>" . lang('finished', 'abgeschlossen') . "</span>";
            default:
                return "<span class='badge'>-</span>";
        }
    }
    public function getType()
    {
        $type = $this->project['type'] ?? 'Drittmittel';
        if ($type == 'Drittmittel') { ?>
            <span class="badge text-danger no-wrap">
                <i class="ph ph-hand-coins"></i>
                <?= lang('Third-party funded', 'Drittmittel') ?>
            </span>

        <?php } elseif ($type == 'Stipendium') { ?>
            <span class="badge text-success no-wrap">
                <i class="ph ph-tip-jar"></i>
                <?= lang('Stipendiate', 'Stipendium') ?>
            </span>
        <?php } else if ($type == 'Eigenfinanziert') { ?>
            <span class="badge text-signal no-wrap">
                <i class="ph ph-piggy-bank"></i>
                <?= lang('Self-funded', 'Eigenfinanziert') ?>
            </span>
        <?php } else if ($type == 'Teilprojekt') { ?>
            <span class="badge text-danger no-wrap">
                <i class="ph ph-hand-coins"></i>
                <?= lang('Subproject', 'Teilprojekt') ?>
            </span>
        <?php } else { ?>
            <span class="badge text-muted no-wrap">
                <i class="ph ph-coin"></i>
                <?= lang('Other', 'Sonstiges') ?>
            </span>
<?php }
    }

    public function getRoleRaw()
    {
        if (($this->project['role'] ?? '') == 'coordinator')
            return lang('Coordinator', 'Koordinator');
        if (($this->project['role'] ?? '') == 'associated')
            return lang('Associated', 'Beteiligt');
        return 'Partner';
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

    public function getPurpose()
    {
        switch ($this->project['purpose'] ?? '') {
            case "research":
                return lang('Research', 'Forschung');
            case "teaching":
                return lang('Teaching', 'Lehre');
            case "promotion":
                return lang('Promotion of young scientists', 'Förderung des wissenschaftlichen Nachwuchs');
            case "transfer":
                return lang('Transfer', 'Transfer');
            case "others":
                return lang('Other purpose', 'Sonstiger Zweck');
            default:
                return '-';
        }
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
        $year1 = $this->project['start']['year'];
        $year2 = $this->project['end']['year'];

        $month1 = $this->project['start']['month'];
        $month2 = $this->project['end']['month'];

        return (($year2 - $year1) * 12) + ($month2 - $month1) + 1;
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

    public static function personRoleRaw($role)
    {
        switch ($role) {
            case 'PI':
                return ['en' => 'Project lead', 'de' => 'Projektleitung'];
            case 'applicant':
                return ['en' => 'Applicant', 'de' => 'Antragsteller:in'];
            case 'worker':
                return ['en' => 'Project member', 'de' => 'Projektmitarbeiter:in'];
            case 'scholar':
                return ['en' => 'Scholar', 'de' => 'Stipediat:in'];
            case 'supervisor':
                return ['en' => 'Supervisor', 'de' => 'Betreuer:in'];
            case 'coordinator':
                return ['en' => 'Scientific Coordinator', 'de' => 'Wiss. Koordinator:in'];
            default:
                return ['en' => 'Associate', 'de' => 'Beteiligte Person'];
        }
    }

    public static function personRole($role, $gender = 'n')
    {
        $role = self::personRoleRaw($role);
        return lang($role['en'], $role['de']);
    }

    public function widgetSmall()
    {
        $widget = '<a class="module ' . ($this->inPast() ? 'inactive' : '') . '" href="' . ROOTPATH . '/projects/view/' . $this->project['_id'] . '">';
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
        $widget = '<a class="module ' . ($this->inPast() ? 'inactive' : '') . '" href="' . ROOTPATH . '/projects/view/' . $this->project['_id'] . '">';
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
        $widget = '<a class="module ' . ($this->inPast() ? 'inactive' : '') . '" href="' . ROOTPATH . '/projects/view/' . $this->project['_id'] . '" ' . ($external ? 'target="_blank"' : '') . '>';

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
        $widget .= '<span class="mr-10">' . $this->getStatus() . '</span> ';
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
        if (count($countries) == 1) return ['scope' => $scope, 'region' => Country::get($countries[0])];

        $scope = 'continental';
        $continents = [];
        foreach ($countries as $code) {
            $continents[] = Country::countryToContinent($code);
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
            $continents[] = Country::countryToContinent($code);
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
}
