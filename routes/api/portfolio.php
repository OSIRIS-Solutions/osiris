<?php

/**
 * Routing for the Portfolio-API
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

function portfolio_apikey_check($key)
{
    global $Settings;
    // ensure that settings is available, otherwise allow access
    if (!isset($Settings) || !$Settings instanceof Settings) return true;
    $apikey = $Settings->get('portfolio_apikey', null);
    if (empty($apikey)) return true;
    if ($key === $apikey) return true;
    return false;
}

function rest($data, $count = 0, $status = 200)
{
    $result = array();
    $limit = intval($_GET['limit'] ?? 0);
    if ($count == 0 && is_countable($data)) {
        $count = count($data);
    }

    if (!empty($limit) && $count > $limit && is_array($data)) {
        $offset = intval($_GET['offset'] ?? 0) || 0;
        $data = array_slice($data, $offset, min($limit, $count - $offset));
        $result += array(
            'limit' => $limit,
            'offset' => $offset
        );
    }

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");
    if ($status == 200) {
        $result += array(
            'status' => 200,
            'count' => $count,
            'data' => $data
        );
    } elseif ($status == 400) {
        $result += array(
            'status' => 400,
            'count' => 0,
            'error' => 'WrongCall',
            'msg' => $data
        );
    } else {
        $result += array(
            'status' => $status,
            'count' => 0,
            'error' => 'DataNotFound',
            'msg' => $data
        );
    }
    return json_encode($result, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function help_getProject($osiris, $id)
{
    if (DB::is_ObjectID($id)) {
        $id = DB::to_ObjectID($id);
        $result = $osiris->projects->findOne(
            ['_id' => $id],
        );
    } else {
        $result = $osiris->projects->findOne(
            ['name' => $id],
        );
    }
    return $result;
}

function help_getGroup($osiris, $id)
{
    if ($id == 0)
        $group = $osiris->groups->findOne(['level' => 0]);
    else
        $group = $osiris->groups->findOne(['id' => $id]);
    return $group;
}

Route::get('/portfolio/settings', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    // include_once BASEPATH . "/php/Portfolio.php";
    // $Portfolio = new Portfolio();
    // get Portfolio settings and other Data for the portfolio overview

    $result = [
        'version' => OSIRIS_VERSION,
        'features' => [
            'projects' => $Settings->featureEnabled('projects'),
            'infrastructures' => $Settings->featureEnabled('infrastructures'),
            'topics' => $Settings->featureEnabled('topics'),
        ]
    ];

    $labels = [
        'topics' => ['en' => 'Research Topics', 'de' => 'Forschungsbereiche'],
        'journals' => ['en' => 'Journals', 'de' => 'Journale'],
        'infrastructures' => ['en' => 'Infrastructures', 'de' => 'Infrastrukturen'],
    ];
    $topicLabel = $Settings->get('topics_label');
    if (!empty($topicLabel) && isset($topicLabel['en'])) {
        $labels['topics'] = $topicLabel;
    }
    $journalLabel = $Settings->get('journals_label');
    if (!empty($journalLabel) && isset($journalLabel['en'])) {
        $labels['journals'] = $journalLabel;
    }
    $infrastructureLabel = $Settings->get('infrastructures_label');
    if (!empty($infrastructureLabel) && isset($infrastructureLabel['en'])) {
        $labels['infrastructures'] = $infrastructureLabel;
    }
    $result['labels'] = $labels;

    $result['publication_types'] = $osiris->adminTypes->find(
        ['parent' => 'publication', 'portfolio' => ['$in' => [1, true]]],
        ['sort' => ['order' => 1], 'projection' => ['_id' => 0, 'id' => 1, 'en' => '$name', 'de' => '$name_de']]
    )->toArray();

    $portfolioCats = $osiris->adminTypes->distinct('parent', ['portfolio' => ['$in' => [true, 1]], 'parent' => ['$ne' => 'publication']]);
    $result['activity_categories'] = $osiris->adminCategories->find(
        ['id' => ['$in' => $portfolioCats]],
        ['sort' => ['order' => 1], 'projection' => ['_id' => 0, 'id' => 1, 'en' => '$name', 'de' => '$name_de']]
    )->toArray();

    $result['affiliation'] = $Settings->get('affiliation_details', null);

    echo rest($result);
});

Route::get('/portfolio/topics', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $result = $osiris->topics->find(
        ['inactive' => ['$ne' => true]],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'subtitle' => 1, 'subtitle_de' => 1, 'description' => 1, 'description_de' => 1, 'color' => 1]]
    )->toArray();
    echo rest($result);
});

Route::get('/portfolio/topic/([^/]*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $result = $osiris->topics->findOne(
        ['inactive' => ['$ne' => true], 'id' => $id],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'subtitle' => 1, 'subtitle_de' => 1, 'description' => 1, 'description_de' => 1, 'color' => 1]]
    );
    $result = DB::doc2Arr($result);
    $result['numbers'] = [
        'publications' => $osiris->activities->count([
            'topics' => $id,
            'type' => 'publication',
            'hide' => ['$ne' => true]
        ]),
        'activities' => $osiris->activities->count([
            'topics' => $id,
            'subtype' => ['$in' => $Settings->getActivitiesPortfolio()],
            'hide' => ['$ne' => true]
        ]),
        'persons' => $osiris->persons->count([
            'topics' => $id,
            'is_active' => ['$ne' => false],
            'hide' => ['$ne' => true]
        ]),
        'units' => $osiris->groups->count([
            'topics' => $id,
            'hide' => ['$ne' => true]
        ]),
        'projects' => $osiris->projects->count([
            'topics' => $id,
            'public' => true,
        ]),
        'collaborators' => 0
    ];
    if ($result['numbers']['projects'] > 0) {
        $collabs = $osiris->projects->aggregate([
            ['$match' => [
                'topics' => $id,
                'collaborators' => ['$exists' => true, '$ne' => []],
            ]],
            ['$project' => ['collaborators' => 1]],
            ['$unwind' => '$collaborators'],
            [
                '$group' => [
                    '_id' => '$collaborators.name',
                ]
            ],
            ['$count' => 'count']
        ])->toArray();
        $result['numbers']['collaborators'] = $collabs[0]['count'] ?? 0;
    }

    // general information for navigation:
    include_once BASEPATH . "/php/Portfolio.php";
    $Portfolio = new Portfolio();
    $result['nav_topics'] = $Portfolio->getTopics();
    $result["nav_units"] = $Portfolio->build_unit_hierarchy($id);
    echo rest($result);
});


Route::get('/portfolio/units', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $result = $osiris->groups->find(
        [],
        // ['hide' => ['$ne' => true]],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'parent' => 1, 'unit' => 1, 'level' => 1, 'hide' => 1, 'order' => 1]]
    )->toArray();
    echo rest($result);
});


Route::get('/portfolio/unit/([^/]*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    include_once BASEPATH . "/php/Portfolio.php";
    $Portfolio = new Portfolio();
    if ($id == 0) {
        $group = $osiris->groups->findOne(['level' => 0]);
        $id = $group['id'];
    } else
        $group = $osiris->groups->findOne(['id' => $id]);

    $head = $group['head'] ?? [];
    if (is_string($head)) $head = [$head];
    else $head = DB::doc2Arr($head);
    unset($group['head']);


    $unit = $Groups->getUnit($group['unit'] ?? null);
    $group['unit'] = $unit;

    // general information for navigation:
    $group['nav_topics'] = $Portfolio->getTopics();
    $group["nav_units"] = $Portfolio->build_unit_hierarchy($id);
    //     $topics_and_groups =
    // $group['topics_and_groups'] = !empty($topics) && !empty($hierarchy);

    if (!empty($head)) {
        $group['heads'] = [];
        foreach ($head as $h) {
            $p = $DB->getPerson($h);
            if (empty($p) || ($p['hide'] ?? false)) continue;

            if ($p['public_image'] ?? false) {
                $img = $Settings->printProfilePicture($p['username'], 'profile-img small');
            } else {
                $img = $Settings->printProfilePicture(null, 'profile-img small');
            }
            $group['heads'][] = [
                'id' => strval($p['_id']),
                'name' => $p['displayname'],
                'img' => $img,
                'position' => $p['position'],
                'position_de' => $p['position_de'] ?? null,
            ];
        }
    }

    $group['parent_details'] = $osiris->groups->findOne(
        ['id' => $group['parent']],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'level' => 1, 'hide' => 1]]
    );
    $group['children'] = $osiris->groups->find(
        ['parent' => $group['id'], 'hide' => ['$ne' => true]],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'level' => 1, 'hide' => 1]]
    )->toArray();

    if (isset($group['topics']) && !empty($group['topics'])) {
        $topics = $osiris->topics->find(
            ['id' => ['$in' => $group['topics']], 'inactive' => ['$ne' => true]],
            ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'color' => 1]]
        )->toArray();
        $group['topics'] = $topics;
    }

    $research = [];
    if (isset($group['research'])) {
        foreach ($group['research'] as $key => $value) {
            $res = [
                'title' => $value['title'] ?? '',
                'title_de' => $value['title_de'] ?? null,
                'subtitle' => $value['subtitle'] ?? '',
                'subtitle_de' => $value['subtitle_de'] ?? null,
                'info' => $value['info'] ?? '',
                'info_de' => $value['info_de'] ?? null,
            ];
            if (!empty($value['activities'])) {
                $res['activities'] = [];
                foreach ($value['activities'] as $a) {
                    $doc = $DB->getActivity($a);
                    if (empty($doc)) continue;
                    $res['activities'][] = [
                        'id' => strval($doc['_id']),
                        'icon' => $doc['rendered']['icon'],
                        'html' => $doc['rendered']['print']
                    ];
                }
            }
            $research[] = $res;
        }
    }
    $group['research'] = $research;

    // get all numbers
    $numbers = [];
    $child_ids = $Groups->getChildren($id);

    if (isset($group['description']) || isset($group['description_de'])) {
        $numbers['general'] = 1;
    }
    if (!empty($group['research'] ?? null)) {
        $numbers['research'] = 1;
    }

    $numbers['persons'] = $osiris->persons->count([
        'units' => [
            '$elemMatch' => [
                'unit' => ['$in' => $child_ids],
                '$or' => [
                    ['end' => null],
                    ['end' => ['$gte' => date('Y-m-d')]]
                ]
            ]
        ],
        'is_active' => ['$ne' => false],
        'hide' => ['$ne' => true]
    ]);

    $publication_filter = [
        'units' => ['$in' => $child_ids],
        'type' => 'publication',
        'hide' => ['$ne' => true]
    ];

    $activities_filter = [
        'units' => ['$in' => $child_ids],
        'subtype' => ['$in' => $Settings->getActivitiesPortfolio()],
        'hide' => ['$ne' => true]
    ];

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $publication_filter['workflow.status'] = 'verified';
            $activities_filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $publication_filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
            $activities_filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }

    $numbers['publications'] = $osiris->activities->count($publication_filter);
    $numbers['activities'] = $osiris->activities->count($activities_filter);

    // $numbers['memberships'] = $osiris->activities->count([
    //     'units' => ['$in' => $child_ids],
    //     'subtype' => ['$in' => $Settings->continuousTypes]
    // ]);

    $collabs = $osiris->projects->aggregate([
        ['$match' => [
            'units' => ['$in' => $child_ids],
            'collaborators' => ['$exists' => true, '$ne' => []],
        ]],
        ['$project' => ['collaborators' => 1]],
        ['$unwind' => '$collaborators'],
        [
            '$group' => [
                '_id' => '$collaborators.name',
            ]
        ],
        ['$count' => 'count']
    ])->toArray();
    $numbers['collaborators'] = $collabs[0]['count'] ?? 0;


    if ($Settings->featureEnabled('projects')) {
        $project_filter = [
            'units' => ['$in' => $child_ids],
            "public" => true,
        ];
        $numbers['projects'] = $osiris->projects->count($project_filter);
    } else {
        $numbers['projects'] = 0;
    }

    // if ($group['level'] == 1) {
    //     // copy from project filter
    //     $cooperation_filter = [
    //         'year' => ['$gte' => CURRENTYEAR - 4],
    //         'units' => $id
    //     ];
    //     $cooperation_filter = array_merge($cooperation_filter, $publication_filter);
    //     $coop = $osiris->activities->aggregate([
    //         ['$match' => $cooperation_filter],
    //         ['$unwind' => '$units'],
    //         ['$group' => ['_id' => '$units', 'count' => ['$sum' => 1]]],
    //         ['$sort' => ['count' => -1]]
    //     ])->toArray();

    //     $numbers['cooperation'] = max(0, count($coop) - 1);
    // }
    $numbers['infrastructures'] = 0;
    if ($group['level'] == 0 && $Settings->featureEnabled('infrastructures')) {
        $numbers['infrastructures'] = $osiris->infrastructures->count([
            // 'units' => ['$in' => $child_ids],
            'public' => true,
        ]);
    }
    $group['numbers'] = $numbers;

    echo rest($group);
});


Route::get('/portfolio/unit/([^/]*)/research', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    if ($id == 0)
        $group = $osiris->groups->findOne(['level' => 0]);
    else
        $group = $osiris->groups->findOne(['id' => $id]);

    // include(BASEPATH . '/php/MyParsedown.php');
    // $parsedown = new Parsedown();

    $research = [];
    if (isset($group['research'])) foreach ($group['research'] as $key => $value) {
        $res = [
            'title' => $value['title'] ?? '',
            'title_de' => $value['title_de'] ?? '',
            'subtitle' => $value['subtitle'] ?? '',
            'subtitle_de' => $value['subtitle_de'] ?? '',
            // 'info' => (!empty($value['info'] ?? '') ? $parsedown->text($value['info']) : null),
            'info' => $value['info'] ?? '',
            // 'info_de' => (!empty($value['info_de'] ?? '') ? $parsedown->text($value['info_de']) : null)
            'info_de' => $value['info_de'] ?? '',
        ];
        if (!empty($value['activities'])) {
            $res['activities'] = [];
            foreach ($value['activities'] as $a) {
                $doc = $DB->getActivity($a);
                if (empty($doc)) continue;
                $res['activities'][] = [
                    'id' => strval($doc['_id']),
                    'icon' => $doc['rendered']['icon'],
                    'html' => $doc['rendered']['print']
                ];
            }
        }
        $research[] = $res;
    }

    echo rest($research);
});


Route::get('/portfolio/unit/([^/]*)/numbers', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $base_group = false;
    $numbers = [];
    if ($id == 0) {
        $group = $osiris->groups->findOne(['level' => 0]);
        $id = $group['id'];
    } else
        $group = $osiris->groups->findOne(['id' => $id]);

    if (empty($group)) {
        echo rest('Group not found', 0, 404);
        die;
    }
    if ($group['level'] == 0) {
        $base_group = true;
    }
    $child_ids = $Groups->getChildren($id);

    if (isset($group['description']) || isset($group['description_de'])) {
        $numbers['general'] = 1;
    }
    if (!empty($group['research'] ?? null)) {
        $numbers['research'] = 1;
    }

    $person_filter = [
        'units' => [
            '$elemMatch' => [
                'unit' => ['$in' => $child_ids],
                '$or' => [
                    ['end' => null],
                    ['end' => ['$gte' => date('Y-m-d')]]
                ]
            ]
        ],
        'is_active' => ['$ne' => false],
        'hide' => ['$ne' => true]
    ];

    $numbers['persons'] = $osiris->persons->count($person_filter);

    $publication_filter = [
        'units' => ['$in' => $child_ids],
        'type' => 'publication',
        'hide' => ['$ne' => true]
    ];

    $activities_filter = [
        'units' => ['$in' => $child_ids],
        'subtype' => ['$in' => $Settings->getActivitiesPortfolio()],
        'hide' => ['$ne' => true]
    ];

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $publication_filter['workflow.status'] = 'verified';
            $activities_filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $publication_filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
            $activities_filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }

    $numbers['publications'] = $osiris->activities->count($publication_filter);

    $numbers['activities'] = $osiris->activities->count($activities_filter);

    $numbers['memberships'] = $osiris->activities->count([
        'units' => ['$in' => $child_ids],
        'subtype' => ['$in' => $Settings->continuousTypes]
    ]);

    if ($Settings->featureEnabled('projects')) {
        $project_filter = [
            'units' => ['$in' => $child_ids],
            "public" => true,
        ];

        $numbers['projects'] = $osiris->projects->count($project_filter);
    } else {
        $numbers['projects'] = 0;
    }

    if ($group['level'] == 1) {
        // copy from project filter
        $cooperation_filter = [
            'year' => ['$gte' => CURRENTYEAR - 4],
            'units' => $id
        ];
        $cooperation_filter = array_merge($cooperation_filter, $publication_filter);
        $coop = $osiris->activities->aggregate([
            ['$match' => $cooperation_filter],
            ['$unwind' => '$units'],
            ['$group' => ['_id' => '$units', 'count' => ['$sum' => 1]]],
            ['$sort' => ['count' => -1]]
        ])->toArray();

        $numbers['cooperation'] = max(0, count($coop) - 1);
    }
    $numbers['infrastructures'] = 0;
    if ($base_group && $Settings->featureEnabled('infrastructures')) {
        $numbers['infrastructures'] = $osiris->infrastructures->count([
            // 'units' => ['$in' => $child_ids],
            'public' => true,
        ]);
    }

    echo rest($numbers);
});


Route::get('/portfolio/(publications|activities|all-activities)', function ($type) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = ['hide' => ['$ne' => true]];

    if ($type == 'publications') {
        $filter['type'] = 'publication';
    } else if ($type == 'activities') {
        $filter['subtype'] = ['$in' => $Settings->getActivitiesPortfolio(false)];
    } else {
        $filter['subtype'] = ['$in' => $Settings->getActivitiesPortfolio(true)];
    }

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }

    $options = [
        'sort' => ['year' => -1, 'month' => -1, 'day' => -1],
        'projection' => [
            '_id' => 0,
            'id' => ['$toString' => '$_id'],
            'html' => '$rendered.portfolio',
            'print' => '$rendered.print',
            'search' => '$rendered.plain',
            'type' => 1,
            'subtype' => 1,
            'year' => 1,
            'month' => 1,
            'day' => 1,
            'icon' => '$rendered.icon',
            'affiliated' => 1
        ]
    ];

    $result = $osiris->activities->find(
        $filter,
        $options
    )->toArray();

    echo rest($result);
});

Route::get('/portfolio/(unit|person|project|topic)/([^/]*)/(publications|activities|all-activities)', function ($context, $id, $type) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    if ($context == 'topic') {
        $filter = [
            'topics' => $id,
            'hide' => ['$ne' => true],
            'authors.aoi' => ['$in' => [1, '1', true, 'true']]
        ];
    } elseif ($context == 'unit') {
        if ($id == 0) {
            $group = $osiris->groups->findOne(['level' => 0]);
            $id = $group['id'];
        }

        $child_ids = $Groups->getChildren($id);
        $filter = [
            'units' => ['$in' => $child_ids],
            'hide' => ['$ne' => true],
            'authors.aoi' => ['$in' => [1, '1', true, 'true']]
        ];
    } elseif ($context == 'project') {
        if (DB::is_ObjectID($id)) {
            $id = DB::to_ObjectID($id);
        }
        $filter = [
            'projects' => $id,
            'hide' => ['$ne' => true],
            'authors.aoi' => ['$in' => [1, '1', true, 'true']]
        ];
    } elseif ($context == 'person') {
        $id = DB::to_ObjectID($id);
        $person = $osiris->persons->findOne(['_id' => $id]);
        if (empty($person)) {
            echo rest('Person not found', 0, 404);
            die;
        }
        $id = $person['username'];
        $filter = [
            'rendered.users' => $id,
            'hide' => ['$ne' => true]
        ];
    } else {
        echo rest('Context not found', 0, 400);
        die;
    }
    if ($type == 'publications') {
        $filter['type'] = 'publication';
    } else if ($type == 'activities') {
        $filter['subtype'] = ['$in' => $Settings->getActivitiesPortfolio(false)];
    } else {
        $filter['subtype'] = ['$in' => $Settings->getActivitiesPortfolio(true)];
    }

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }

    $options = [
        'sort' => ['year' => -1, 'month' => -1, 'day' => -1],
        'projection' => [
            'html' => '$rendered.portfolio',
            'search' => '$rendered.plain',
            'print' => '$rendered.print',
            'type' => 1,
            'subtype' => 1,
            'year' => 1,
            'month' => 1,
            'day' => 1,
            'icon' => '$rendered.icon',
        ]
    ];

    $result = $osiris->activities->find(
        $filter,
        $options
    )->toArray();

    echo rest($result);
});

Route::get('/portfolio/(unit|person)/([^/]*)/teaching', function ($context, $id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = [
        '$and' => [['type' => 'teaching', 'module_id' => ['$ne' => null], 'hide' => ['$ne' => true]]]
    ];
    if ($context == 'unit') {
        if ($id == 0) {
            $group = $osiris->groups->findOne(['level' => 0]);
            $id = $group['id'];
        }
        $child_ids = $Groups->getChildren($id);
        $filter['$and'][] = ['units' => ['$in' => $child_ids]];
    } else {
        $id = DB::to_ObjectID($id);
        $person = $osiris->persons->findOne(['_id' => $id]);
        $id = $person['username'];
        $filter['$and'][] = ['rendered.users' => $id];
    }

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $filter['$and'][] = [
                '$or' => [
                    ['workflow' => ['$exists' => false]],
                    ['workflow.status' => 'verified'],
                    ['workflow.status' => ['$exists' => false]]
                ]
            ];
        }
    }

    $teaching = $osiris->activities->aggregate([
        ['$match' => $filter],
        [
            '$group' => [
                '_id' => '$module_id',
                'count' => ['$sum' => 1],
                // 'doc' => ['$push' => '$$ROOT']
            ]
        ],
        ['$sort' => ['count' => -1]]
    ])->toArray();

    $result = [];
    foreach ($teaching as $t) {
        $module = $osiris->teaching->findOne(['_id' => DB::to_ObjectID($t['_id'])]);
        if (empty($module)) continue;
        $result[] = [
            'id' => strval($module['_id']),
            'name' => $module['module'],
            'title' => $module['title'],
            'affiliation' => $module['affiliation'],
            'count' => $t['count']
        ];
    }

    echo rest($result);
});


Route::get('/portfolio/(person)/([^/]*)/infrastructures', function ($context, $id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    include_once(BASEPATH . '/php/Infrastructure.php');
    $Infra = new Infrastructure();

    $id = DB::to_ObjectID($id);
    $person = $osiris->persons->findOne(['_id' => $id]);
    if (empty($person)) {
        echo rest('Person not found', 0, 404);
        die;
    }
    $id = $person['username'];

    $filter = [
        'public' => true,
        'persons.user' => $id
    ];

    $options = [
        'sort' => ['name' => 1],
        'projection' => [
            'id' => 1,
            'name' => 1,
            'persons' => 1,
        ]
    ];

    $data = $osiris->infrastructures->find(
        $filter,
        $options
    )->toArray();

    $result = [];
    foreach ($data as $infrastructure) {
        $self = [];
        if (isset($infrastructure['persons'])) {
            foreach ($infrastructure['persons'] as $p) {
                if ($p['user'] == $id) {
                    $self = $p;
                    break;
                }
            }
        }
        $result[] = [
            'id' => $infrastructure['id'],
            'name' => $infrastructure['name'],
            'role' => $Infra->getRole($p['role'] ?? null),
            'start' => $self['start'] ?? '',
            'end' => $self['end'] ?? '',
        ];
    }
    echo rest($result);
});

Route::get('/portfolio/(unit|person|topic)/([^/]*)/projects', function ($context, $id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = [
        'public' => true,
    ];

    $projectTypes = $osiris->adminProjects->find(
        [],
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1]]
    )->toArray();
    $projectTypes = array_column($projectTypes, null, 'id');

    if ($context == 'unit') {
        if ($id == 0) {
            $group = $osiris->groups->findOne(['level' => 0]);
            $id = $group['id'];
        }
        $child_ids = $Groups->getChildren($id);
        $filter['units'] = ['$in' => $child_ids];
    } elseif ($context == 'person') {
        $id = DB::to_ObjectID($id);
        $person = $osiris->persons->findOne(['_id' => $id]);
        $id = $person['username'];
        $filter['persons.user'] =  $id;
    } elseif ($context == 'topic') {
        $filter['topics'] = $id;
    } else {
        echo rest('Context not found', 0, 400);
        die;
    }

    $options = [
        'sort' => ['year' => -1, 'month' => -1],
        'projection' => [
            'id' => ['$toString' => '$_id'],
            'acronym' => 1,
            'name' => 1,
            'name_de' => 1,
            'title' => 1,
            'title_de' => 1,
            'funder' => 1,
            'abstract' => 1,
            'abstract_de' => 1,
            'funding_organization' => 1,
            'funding_number' => 1,
            'role' => 1,
            'start' => 1,
            'end' => 1,
            'start_date' => 1,
            'end_date' => 1,
            'type' => 1,
            'teaser_en' => 1,
            'teaser_de' => 1,
        ]
    ];

    $result = $osiris->projects->find(
        $filter,
        $options
    )->toArray();

    // Add projectTypes info based on type key
    foreach ($result as &$project) {
        if (isset($project['type']) && isset($projectTypes[$project['type']])) {
            $project['type_details'] = $projectTypes[$project['type']];
            $project['type'] = $projectTypes[$project['type']]['name'];
        }
    }
    unset($project);

    echo rest($result);
});

Route::get('/portfolio/(unit|topic)/([^/]*)/staff', function ($context, $id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    // dump($_SERVER, true);

    if ($context == 'topic') {
        $filter = [
            'topics' => $id,
            'is_active' => ['$ne' => false],
            'hide' => ['$ne' => true]
        ];
    } elseif ($context == 'unit') {
        $filter = [
            'is_active' => ['$ne' => false],
            'hide' => ['$ne' => true]
        ];
        if ($id == 0) {
            $group = $osiris->groups->findOne(['level' => 0]);
            $id = $group['id'];
        } else {
            $child_ids = $Groups->getChildren($id);
            $filter['units'] = [
                '$elemMatch' => [
                    'unit' => ['$in' => $child_ids],
                    '$or' => [
                        ['end' => null],
                        ['end' => ['$gte' => date('Y-m-d')]]
                    ]
                ]
            ];
        }
    } else {
        echo rest('Context not found', 0, 400);
        die;
    }

    $persons = $osiris->persons->find(
        $filter,
        ['sort' => ['last' => 1]]
    )->toArray();
    $result = [];

    foreach ($persons as $person) {
        // $units = $person['units'] ?? [];
        // if (!empty($units)) {
        //     $units = array_column(DB::doc2Arr($units), 'unit');
        //     $units = $Groups->deptHierarchies($units);
        // }
        $row = [
            'displayname' => ($person['first'] ?? '') . ' ' . $person['last'],
            'academic_title' => $person['academic_title'],
            'position' => $person['position'],
            'position_de' => $person['position_de'],
            // 'depts' => $units,
            'lastname' => $person['last'],
            'firstname' => $person['first'] ?? ''
        ];
        // if ($person['public_image'] ?? false) {
        //     $row['img'] = $Settings->printProfilePicture($person['username'], 'profile-img');
        // } else {
        //     $row['img'] = $Settings->printProfilePicture(null, 'profile-img');
        // }
        $row['id'] = strval($person['_id']);
        $result[] = $row;
    }
    echo rest($result);
});

Route::get('/portfolio/topic/([^/]*)/units', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $filter = [
        'topics' => $id,
        'is_active' => ['$ne' => false],
        'hide' => ['$ne' => true]
    ];
    $units = $osiris->groups->find(
        $filter,
        ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'parent' => 1, 'unit' => 1, 'level' => 1, 'hide' => 1, 'order' => 1]]
    )->toArray();

    // add head info and unit details
    foreach ($units as &$unit) {
        $u = $Groups->getUnit($unit['unit'] ?? null);
        $unit['unit'] = $u;

        $head = $unit['head'] ?? [];
        if (is_string($head)) $head = [$head];
        else $head = DB::doc2Arr($head);
        unset($unit['head']);

        if (!empty($head)) {
            $unit['heads'] = [];
            foreach ($head as $h) {
                $p = $DB->getPerson($h);
                if (empty($p) || ($p['hide'] ?? false)) continue;

                if ($p['public_image'] ?? false) {
                    $img = $Settings->printProfilePicture($p['username'], 'profile-img small');
                } else {
                    $img = $Settings->printProfilePicture(null, 'profile-img small');
                }
                $unit['heads'][] = [
                    'id' => strval($p['_id']),
                    'name' => $p['displayname'],
                    'img' => $img,
                    'position' => $p['position'],
                    'position_de' => $p['position_de'] ?? null,
                ];
            }
        }
    }
    unset($unit);
    echo rest($units);
});


Route::get('/portfolio/project/([^/]*)/staff', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    include(BASEPATH . '/php/Project.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header('Content-Type: application/json');
    // dump($_SERVER, true);

    // $filter = [
    //     'hide' => ['$ne' => true],
    //     'is_active' => ['$ne'=>false], 'hide'=>['$ne'=>true]
    // ];
    $project = help_getProject($osiris, $id);
    if (empty($project)) {
        echo rest('Project not found', 0, 404);
        die;
    }
    if (empty($project['persons'])) {
        echo rest([]);
        die;
    }
    $Project = new Project($project);

    $persons = DB::doc2Arr($project['persons']);
    // sort project team by role (custom order)
    $roles = ['applicant', 'PI', 'Co-PI', 'worker', 'associate', 'student'];
    usort($persons, function ($a, $b) use ($roles) {
        return array_search($a['role'], $roles) - array_search($b['role'], $roles);
    });

    $result = [];

    foreach ($persons as $p) {
        $person = $DB->getPerson($p['user']);
        if (empty($person) || ($person['hide'] ?? false)) continue;
        $row = [
            'displayname' => $person['displayname'],
            'academic_title' => $person['academic_title'],
            'position' => $person['position'],
            'position_de' => $person['position_de'],
            'depts' => []
        ];
        // if ($person['public_image'] ?? false) {
        //     $row['img'] = $Settings->printProfilePicture($person['username'], 'profile-img small mr-20');
        // } else {
        //     $row['img'] = $Settings->printProfilePicture(null, 'profile-img small mr-20');
        // }
        $row['id'] = strval($person['_id']);
        $row['role'] = $Project->personRole($row['role']);

        if (!empty($person['depts'])) {
            foreach ($person['depts'] as $d) {
                $dept = $Groups->getGroup($d);
                if ($dept['level'] !== 1) continue;
                $row['depts'][$d] = [
                    'en' => $dept['name'],
                    'de' => $dept['name_de']
                ];
            }
        }
        $result[] = $row;
    }
    echo rest($result);
});

Route::get('/portfolio/activity/([^/]*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header('Content-Type: application/json');
    include(BASEPATH . '/php/Modules.php');
    $portfolio_types = $Settings->getActivitiesPortfolio(true);
    $id = DB::to_ObjectID($id);
    $result = [];
    $doc = $osiris->activities->findOne(
        ['_id' => $id]
    );
    if (empty($doc) || ($doc['hide'] ?? false) || !in_array($doc['subtype'], $portfolio_types)) {
        echo rest('Activity not found', 0, 404);
        die;
    }

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            if (($doc['workflow']['status'] ?? '') != 'verified') {
                echo rest('Activity not found', 0, 404);
                die;
            }
        } elseif ($visibility == 'approved-or-empty') {
            if (isset($doc['workflow']['status']) && $doc['workflow']['status'] != 'verified') {
                echo rest('Activity not found', 0, 404);
                die;
            }
        }
    }
    $result = [
        'id' => strval($doc['_id']),
        'type' => $doc['type'],
        'subtype' => $doc['subtype'],
        'year' => $doc['year'] ?? null,
        'month' => $doc['month'] ?? null,
        'abstract' => $doc['abstract'] ?? null,
        'doi' => $doc['doi'] ?? null,
        'pubmed' => $doc['pubmed'] ?? null,
        'title' => $doc['rendered']['title'],
        'authors' => [],
        'depts' => [],
        'projects' => [],
        'affiliated' => false,
        'typeStr' => $doc['rendered']['type'] ?? $doc['type'],
        'subtypeStr' => $doc['rendered']['subtype'] ?? $doc['subtype']
    ];

    foreach ($doc['authors'] as $a) {
        if ($a['aoi']) $result['affiliated'] = true;
        $i = null;
        $orcid = $a['orcid'] ?? null;
        if (!empty($a['user'])) {
            $person = $DB->getPerson($a['user']);
            if (!empty($person) && !($person['hide'] ?? false)) $i = strval($person['_id']);
            if (empty($orcid) && !empty($person['orcid'])) $orcid = $person['orcid'];
        }
        $result['authors'][] = [
            'id' => $i,
            'name' => ($a['first'] ?? '') . ' ' . ($a['last'] ?? ''),
            'orcid' => $orcid
        ];
    }

    $depts = [];
    if (!empty($doc['units'])) {
        foreach ($doc['units'] as $d) {
            $dept = $Groups->getGroup($d);
            if ($dept['level'] !== 1) continue;
            $depts[$d] = [
                'en' => $dept['name'],
                'de' => $dept['name_de']
            ];
        }
    }
    $result['depts'] = $depts;

    if (!empty($doc['projects'])) {
        $projects = [];
        foreach ($doc['projects'] as $p) {
            $project = $DB->getProject($p);
            if (empty($project)) continue;
            $projects[] = [
                'id' => strval($project['_id']),
                'name' => $project['name'],
                'title' => $project['title'],
                'funder' => $project['funder'],
                'funding_organization' => $project['funding_organization'],
                'funding_number' => $project['funding_number'],
                'role' => $project['role'],
                'start' => $project['start'],
                'end' => $project['end']
            ];
        }
        $result['projects'] = $projects;
    }

    $Format = new Document;
    $Format->setDocument($doc);
    $selected = $Format->subtypeArr['modules'] ?? array();
    $Modules = new Modules($doc);

    $Format->usecase = "list";

    // TODO: configurable
    $hidden_modules = ['authors', "editors", "supervisors", "semester-select", 'abstract', 'doi', 'pubmed', 'depts', 'projects', 'correction', 'epub', 'title'];
    $fields = [];
    foreach ($selected as $module) {
        if (str_ends_with($module, '*')) $module = str_replace('*', '', $module);
        if (in_array($module, $hidden_modules)) continue;
        if ($module == 'teaching-course' && isset($doc['module_id'])) :
            $teaching = $DB->getConnected('teaching', $doc['module_id']);
            $value = $teaching['module'];
        elseif ($module == 'journal' && isset($doc['journal_id'])) :
            $journal = $DB->getConnected('journal', $doc['journal_id']);
            $value = $journal['journal'];
            $result['journal'] = [
                'id' => strval($journal['_id']),
                'name' => $journal['journal'],
                'issn' => $journal['issn'] ?? null
            ];
        elseif ($module == 'organizations' || $module == 'organization') :
            $names = [];
            $orgs = [];
            $arr = $module == 'organizations' ? ($doc['organizations'] ?? []) : (isset($doc['organization']) ? [$doc['organization']] : []);
            foreach ($arr as $o) {
                $org = $DB->getConnected('organization', $o);
                if (!empty($org)) {
                    $names[] = $org['name'];
                    $orgs[] = [
                        'id' => strval($org['_id']),
                        'name' => $org['name'],
                        'country' => $org['country'] ?? null,
                        'location' => $org['location'] ?? null,
                        'synonyms' => $org['synonyms'] ?? [],
                        'ror' => $org['ror'] ?? null
                    ];
                }
            }
            $value = implode(', ', $names);
            $result['organizations'] = $orgs;

        elseif ($module == 'conference' && isset($doc['conference_id'])) :
            $conf = $DB->getConnected('conference', $doc['conference_id']);
            $value = $conf['title'];
            $result['event'] = [
                'id' => strval($conf['_id']),
                'name' => $conf['title'],
                'title' => $conf['title_full'] ?? null,
                'location' => $conf['location'] ?? null,
                'country' => $conf['country'] ?? null,
                'start' => $conf['start'] ?? null,
                'end' => $conf['end'] ?? null,
                'link' => $conf['url'] ?? null
            ];
        elseif ($Format->get_field($module) != '-') :
            $value = $Format->get_field($module);
        else :
            continue;
        endif;
        $names = $Modules->all_modules[$module] ?? [];
        $fields[] = [
            'id' => $module,
            'key_en' => $names['name'] ?? ucfirst($module),
            'key_de' => $names['name_de'] ?? ucfirst($module),
            'value' => $value
        ];
    }
    $result['fields'] = $fields;

    // bibtex format
    $result['print'] = $doc['rendered']['print'];
    $result['bibtex'] = $Format->bibtex();
    $result['ris'] = $Format->ris();

    if (isset($doc['infrastructures']) && !empty($doc['infrastructures']) && $Settings->featureEnabled('infrastructures')) {
        $result['infrastructures'] = [];
        foreach ($doc['infrastructures'] as $infra_id) {
            $infrastructure = $osiris->infrastructures->findOne(['id' => $infra_id]);
            if (empty($infrastructure)) continue;
            $result['infrastructures'][] = [
                'id' => $infrastructure['id'],
                'name' => $infrastructure['name'],
                'subtitle' => $infrastructure['subtitle'] ?? null,
                'start' => $infrastructure['start_date'] ?? null,
                'end' => $infrastructure['end_date'] ?? null,
            ];
        }
    }

    // add topic details
    if (isset($doc['topics']) && !empty($doc['topics'])) {
        $topics = $osiris->topics->find(
            ['id' => ['$in' => $doc['topics']], 'inactive' => ['$ne' => true]],
            ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'color' => 1]]
        )->toArray();
        $result['topics'] = $topics;
    }

    // add connected activities
    $connected_activities = $osiris->activitiesConnections->find(
        ['$or' => [['source_id' => $id], ['target_id' => $id]]]
    )->toArray();
    $result['connected_activities'] = [];
    foreach ($connected_activities as $conn) {
        $reverse = ($conn['target_id'] == $id);
        $doc = $osiris->activities->findOne(['_id' => $reverse ? $conn['source_id'] : $conn['target_id']], ['projection' => [
            'rendered' => 1,
            'subtype' => 1,
            'hide' => 1
        ]]);
        $conLabel = $Format->getRelationshipLabel($conn['relationship'], $reverse);
        if (empty($doc) || ($doc['hide'] ?? false) || !in_array($doc['subtype'], $portfolio_types)) {
            continue;
        }
        $result['connected_activities'][] = [
            'id' => strval($doc['_id']),
            'icon' => $doc['rendered']['icon'],
            'html' => str_replace('**PORTAL**', '', $doc['rendered']['portfolio']),
            'print' => $doc['rendered']['print'] ?? null,
            'relationship' => $conLabel
        ];
    }
    echo rest($result);
});


Route::get('/portfolio/project/([^/]*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    include(BASEPATH . '/php/Project.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header('Content-Type: application/json');
    $result = $DB->getProject($id);
    if (!($result['public'] ?? false)) {
        echo rest('Project not found', 0, 404);
        die;
    }
    $mongo_id = DB::to_ObjectID($id);
    if (empty($result)) {
        echo rest('Project not found', 0, 404);
        die;
    }
    $Project = new Project($result);
    $project_type = $Project->getProjectType($result['type'] ?? null);
    $project = [
        'id' => strval($result['_id']),
        'acronym' => $result['acronym'] ?? null,
        'name' => $result['name'],
        'name_de' => $result['name_de'] ?? null,
        'type' => lang($project_type['name'], $project_type['name_de'] ?? null),
        'title' => $result['title'] ?? '',
        'title_de' => $result['title_de'] ?? null,
        'abstract' => $result['abstract'] ?? '',
        'abstract_de' => $result['abstract_de'] ?? null,
        'funder' => $result['funder'] ?? null,
        // 'funding_organization' => $result['funding_organization'] ?? null,
        'funding_number' => $result['funding_number'] ?? null,
        'coordinator' => $result['coordinator'] ?? null,
        // 'scholarship' => $result['scholarship'] ?? null,
        // 'university' => $result['university'] ?? null,
        'role' => $result['role'] ?? 'partner',
        'start' => $result['start'] ?? '',
        'end' => $result['end'] ?? '',
        'start_date' => $result['start_date'] ?? null,
        'end_date' => $result['end_date'] ?? null,
        'persons' => [],
        'activities' => 0,
        'subprojects' => [],
        'collaborators' => [],
        'website' => $result['website'] ?? null,
        'img' => null
    ];
    foreach (['funding_organization', 'scholarship', 'university'] as $key) {
        $project[$key] = $Project->printField($key, $result[$key] ?? null, true);
    }

    if (!empty($result['collaborators'])) {
        foreach ($result['collaborators'] as $c) {
            $org_id = $c['organization'] ?? null;
            if (empty($org_id)) continue;
            $org = $osiris->organizations->findOne(['_id' => $org_id]);
            if (empty($org)) continue;
            $project['collaborators'][] = [
                'id' => strval($org['_id']),
                'role' => $c['role'] ?? null,
                'name' => $org['name'],
                'type' => $org['type'] ?? null,
                'location' => $org['location'] ?? null,
                'country' => $org['country'] ?? null,
                'ror' => $org['ror'] ?? null,
                'lat' => $org['lat'] ?? null,
                'lng' => $org['lng'] ?? null,
            ];
        }
    }

    if (isset($result['image']) && !empty($result['image'])) {
        $project['img'] = $Settings->getRequestScheme() . '://' . $_SERVER['HTTP_HOST'] . ROOTPATH . '/uploads/' . $result['image'];
    }

    $activities_filter = [
        '$or' => [
            ['projects' => $result['name']],
            ['projects' => DB::to_ObjectID($id)]
        ],
        'hide' => ['$ne' => true]
    ];

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $activities_filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $activities_filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }

    $project['activities'] = $osiris->activities->count($activities_filter);

    if (!empty($result['persons'])) {

        $persons = DB::doc2Arr($result['persons']);
        // sort project team by role (custom order)
        $roles = ['applicant', 'PI', 'Co-PI', 'worker', 'associate', 'student'];
        usort($persons, function ($a, $b) use ($roles) {
            return array_search($a['role'], $roles) - array_search($b['role'], $roles);
        });

        foreach ($persons as $row) {
            $person = $DB->getPerson($row['user']);
            if (empty($person) || ($person['hide'] ?? false)) continue;
            unset($row['user']);
            $row['id'] = strval($person['_id']);
            $row['role'] = $Project->personRoleRaw($row['role']);
            $depts = [];
            if (!empty($person['depts'])) {
                foreach ($Groups->deptHierarchies($person['depts']) as $d) {
                    $dept = $Groups->getGroup($d);
                    if ($dept['level'] !== 1) continue;
                    $depts[$d] = [
                        'en' => $dept['name'],
                        'de' => $dept['name_de']
                    ];
                }
            }
            $row['depts'] = $depts;

            $project['persons'][] = $row;
        }
    }

    // add parent project
    if (!empty($result['parent'] ?? null)) {
        $parent = $DB->getProject($result['parent']);
        if (!empty($parent) && ($parent['public'] ?? false)) {
            $project['parent'] = [
                'id' => strval($parent['_id']),
                'name' => $parent['name'],
                'title' => $parent['title'] ?? ''
            ];

            // // add inherited fields
            // foreach (Project::INHERITANCE_PUBLIC as $key) {
            //     if (isset($parent[$key]) && empty($project[$key])) {
            //         $project[$key] = $parent[$key];
            //     }
            // }
        }
    }

    // add subprojects
    $subprojects = $osiris->projects->find(['parent_id' => $mongo_id], ['projection' => ['name' => 1, 'title' => 1, 'id' => ['$toString' => '$_id'], 'public' => 1]])->toArray();
    foreach ($subprojects as $sub) {
        if (!($sub['public'] ?? false)) continue;
        $project['subprojects'][] = [
            'id' => $sub['id'],
            'name' => $sub['name'],
            'title' => $sub['title'] ?? ''
        ];
    }

    // add topic details
    if (isset($result['topics']) && !empty($result['topics'])) {
        $topics = $osiris->topics->find(
            ['id' => ['$in' => $result['topics']], 'inactive' => ['$ne' => true]],
            ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'color' => 1]]
        )->toArray();
        $project['topics'] = $topics;
    }

    echo rest($project);
});

Route::get('/portfolio/person/([^/]*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
    header('Content-Type: application/json');
    include(BASEPATH . '/php/Project.php');
    $Project = new Project;

    $id = DB::to_ObjectID($id);
    $person = $osiris->persons->findOne(
        ['_id' => $id]
    );
    if (empty($person)) {
        echo rest('Person not found', 0, 404);
        die;
    }
    if ($person['hide'] ?? false) {
        echo rest('Person not found', 0, 404);
        die;
    }
    $result = [
        'displayname' => $person['displayname'],
        'last' => $person['last'],
        'first' => $person['first'],
        'academic_title' => $person['academic_title'],
        'position' => $person['position'],
        'position_de' => $person['position_de'] ?? null,
        'depts' => [],
        'cv' => $person['cv'] ?? [],
        'contact' => [],
        'biography' => [
            'en' => $person['biography'] ?? null,
            'de' => $person['biography_de'] ?? null
        ],
        'research_profile' => [
            'en' => $person['research_profile'] ?? null,
            'de' => $person['research_profile_de'] ?? null
        ]
    ];

    if (!($person['is_active'] ?? true)) {
        $result['inactive'] = true;
    }

    if ($person['public_email'] ?? true) {
        $result['contact']['mail'] = $person['mail'];
    }
    if ($person['public_phone'] ?? true) {
        $result['contact']['phone'] = $person['telephone'];
    }
    foreach (
        [
            'mail_alternative',
            'mail_alternative_comment',
            'twitter',
            'linkedin',
            'orcid',
            'researchgate',
            'google_scholar',
            'matrix',
            'webpage'
        ] as $key
    ) {
        if (isset($person[$key]) && !empty($person[$key])) {
            $result['contact'][$key] = $person[$key];
        }
    }
    if (isset($person['socials']) && !empty($person['socials'])) {
        foreach ($person['socials'] as $key => $value) {
            if (empty($value)) continue;
            $result['contact'][$key] = $value;
        }
    }


    if ($person['research'] ?? false) {
        $person['research_de'] = $person['research_de'] ?? [];
        // $person['research_de'] = array_map(
        //     fn($val1, $val2) => empty($val1) ? $val2 : $val1,
        //     DB::doc2Arr($person['research_de'] ?? $person['research']),
        //     DB::doc2Arr($person['research'])
        // );
        $result['research'] = [];
        foreach ($person['research'] as $key => $value) {
            $result['research'][] = [
                'en' => $value,
                'de' => $person['research_de'][$key] ?? null
            ];
        }
    }

    $user = $person['username'];
    $url = $Settings->getRequestScheme() . '://' . $_SERVER['HTTP_HOST'] . ROOTPATH;
    $result['img'] = $url . "/img/no-photo.png";
    $result['show_image'] = false;
    if ($person['public_image'] ?? false) {
        $result['show_image'] = true;
        if ($Settings->featureEnabled('db_pictures')) {
            $result['img'] = $url . "/image/$user";
        } elseif (file_exists(BASEPATH . "/img/users/$user.jpg")) {
            $result['img'] = $url . "/img/users/$user.jpg";
        } else {
            $result['show_image'] = false;
        }
    }

    $result['id'] = strval($person['_id']);
    if (!empty($person['units'])) {
        $units = DB::doc2Arr($person['units'] ?? []);
        // filter units from the past
        $units = array_filter($units, function ($unit) {
            return !isset($unit['end']) || strtotime($unit['end']) > time();
        });
        $unit_ids = array_column($units, 'unit');
        $hierarchy = $Groups->getPersonHierarchyTree($unit_ids);
        $result['depts'] = $Groups->readableHierarchy($hierarchy);
    }

    $visibility = 'all';
    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
    }
    if (isset($person['highlighted']) && !empty($person['highlighted'])) {
        $docs = [];
        foreach ($person['highlighted'] as $id) {
            $doc = $DB->getActivity($id);
            if (!empty($doc) && !($doc['hide'] ?? false)) {
                if ($visibility == 'only-approved' && ($doc['workflow']['status'] ?? '') != 'verified') {
                    continue;
                } elseif ($visibility == 'approved-or-empty' && isset($doc['workflow']['status']) && $doc['workflow']['status'] != 'verified') {
                    continue;
                }
                $docs[] = [
                    'id' => strval($doc['_id']),
                    'icon' => $doc['rendered']['icon'],
                    'html' => str_replace('**PORTAL**', '', $doc['rendered']['portfolio']),
                    'print' => $doc['rendered']['print'] ?? null,
                ];
            }
        }
        $result['highlighted'] = $docs;
    }

    $defaultFilter = [
        'authors.user' => $person['username'], // TODO: check for other roles?
        'hide' => ['$ne' => true]
    ];

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $defaultFilter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $defaultFilter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }


    // public_teaching
    $result['numbers'] = [
        'publications' => $osiris->activities->count(array_merge($defaultFilter, ['type' => 'publication'])),
        'activities' => 0,
        'teaching' => 0,
        'projects' => $osiris->projects->count(['persons.user' => $person['username'], "public" => true,]),
        'infrastructures' => 0
    ];
    if ($person['public_other_activities'] ?? true) {
        $result['numbers']['activities'] = $osiris->activities->count(array_merge($defaultFilter, ['subtype' => ['$in' => $Settings->getActivitiesPortfolio()]]));
    }
    $person['teaching'] = [];
    if ($person['public_teaching'] ?? true) {
        $teaching_filter = array_merge($defaultFilter, ['module_id' => ['$ne' => null]]);
        $result['numbers']['teaching'] = $osiris->activities->count($teaching_filter);

        $teaching = $osiris->activities->aggregate([
            ['$match' => $teaching_filter],
            [
                '$group' => [
                    '_id' => '$module_id',
                    'count' => ['$sum' => 1],
                    // 'doc' => ['$push' => '$$ROOT']
                ]
            ],
            ['$sort' => ['count' => -1]]
        ])->toArray();

        foreach ($teaching as $t) {
            $module = $osiris->teaching->findOne(['_id' => DB::to_ObjectID($t['_id'])]);
            if (empty($module)) continue;
            $result['teaching'][] = [
                'id' => strval($module['_id']),
                'name' => $module['module'],
                'title' => $module['title'],
                'affiliation' => $module['affiliation'],
                'count' => $t['count']
            ];
        }
    }

    if ($result['numbers']['projects'] > 0) {
        $raw = $osiris->projects->find(['persons.user' => $person['username'], "public" => true,])->toArray();
        $projects = ['current' => [], 'past' => []];
        foreach ($raw as $project) {
            $Project->setProject($project);
            $past = $Project->inPast();
            if ($past) $key = 'past';
            else $key = 'current';
            $personRole = null;
            foreach ($project['persons'] as $p) {
                if ($p['user'] == $person['username']) {
                    $personRole = $p['role'];
                    break;
                }
            }
            $projects[$key][] = [
                'id' => strval($project['_id']),
                'name' => $project['name'],
                'title' => $project['title'],
                'funder' => $project['funder'] ?? $project['scholarship'] ?? null,
                'funding_organization' => $project['funding_organization'] ?? null,
                // 'funding_number' => $project['funding_number'] ,
                'role' => $project['role'],
                'start' => $project['start'],
                'end' => $project['end'],
                'personRole' => $Project->personRoleRaw($personRole),
            ];
        }
        $result['projects'] = $projects;
    }
    $result['infrastructures'] = [];
    if ($Settings->featureEnabled('infrastructures')) {

        include_once(BASEPATH . '/php/Infrastructure.php');
        $Infra = new Infrastructure();

        $result['numbers']['infrastructures'] = $osiris->infrastructures->count([
            'persons.user' => $person['username'],
            'public' => true,
        ]);

        $data = $osiris->infrastructures->find(
            [
                'public' => true,
                'persons.user' => $person['username']
            ],
            [
                'sort' => ['name' => 1],
                'projection' => [
                    'id' => 1,
                    'name' => 1,
                    'persons' => 1,
                ]
            ]
        )->toArray();

        foreach ($data as $infrastructure) {
            $self = [];
            if (isset($infrastructure['persons'])) {
                foreach ($infrastructure['persons'] as $p) {
                    if ($p['user'] == $person['username']) {
                        $self = $p;
                        break;
                    }
                }
            }
            $result['infrastructures'][] = [
                'id' => $infrastructure['id'],
                'name' => $infrastructure['name'],
                'role' => $Infra->getRole($self['role'] ?? null),
                'start' => $self['start'] ?? '',
                'end' => $self['end'] ?? '',
            ];
        }
    }

    // add topic details
    if (isset($person['topics']) && !empty($person['topics'])) {
        $topics = $osiris->topics->find(
            ['id' => ['$in' => $person['topics']], 'inactive' => ['$ne' => true]],
            ['projection' => ['_id' => 0, 'id' => 1, 'name' => 1, 'name_de' => 1, 'color' => 1]]
        )->toArray();
        $result['topics'] = $topics;
    }

    echo rest($result);
});


Route::get('/portfolio/unit/([^/]*)/collaborators-by-country', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $filter = ['collaborators' => ['$exists' => 1]];
    // only for portal
    $dept = $id;
    $child_ids = $Groups->getChildren($dept);
    $filter = [
        'units' => ['$in' => $child_ids],
        // "public" => true, // experimental: include also non-public projects in aggregation
        'collaborators' => ['$exists' => 1]
    ];
    $data = $osiris->projects->aggregate([
        ['$match' => $filter],
        ['$project' => ['collaborators' => 1]],
        ['$unwind' => '$collaborators'],
        [
            '$group' => [
                '_id' => '$collaborators.country',
                'count' => ['$sum' => 1],
            ]
        ],
        ['$project' => ['iso2' => '$_id', 'count' => 1, '_id' => 0]],
    ])->toArray();
    $institute = $Settings->get('affiliation_details');
    $data = array_map(function ($d) use ($DB) {
        $country = $DB->getCountry($d['iso2']);
        return [
            'iso' => $d['iso2'],
            'iso3' => $country['iso3'],
            'count' => $d['count'],
            'label' => '<b>' . lang($country['name'], $country['name_de']),
            '</b><br/>Projects: ' . $d['count']
        ];
    }, $data);
    $result = [
        'countries' => $data,
        'institute' => $institute
    ];
    echo rest($result, count($result));
});


Route::get('/portfolio/(unit|project|topic)/([^/]*)/collaborators-map', function ($context, $id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $result = [];
    if ($context == 'project') {

        if (DB::is_ObjectID($id)) {
            $mongo_id = $DB->to_ObjectID($id);
            $filter = ['_id' => $mongo_id];
        } else {
            $filter = ['name' => $id];
            $id = strval($project['_id'] ?? '');
        }
        $project = $osiris->projects->findOne($filter);
        if (empty($project)) {
            rest("Project could not be found.", 0, 404);
            exit;
        }

        // add parent project
        if (!empty($project['parent'] ?? null)) {
            $parent = $DB->getProject($project['parent']);
            if (!empty($parent) && isset($parent['collaborators'])) {
                $project['collaborators'] = $parent['collaborators'];
            }
        }

        if (empty($project['collaborators'] ?? [])) {
            rest("Project has no collaborators", 0, 404);
        } else {
            $result = [];
            // order by role
            $collabs = $project['collaborators'];
            // usort($collabs, function ($a, $b) {
            //     return $b['role'] <=> $a['role'];
            // });
            foreach ($collabs as $c) {
                $org = $osiris->organizations->findOne(['_id' => $c['organization']]);
                if (empty($org)) continue;
                $result[] = [
                    "_id" => strval($org['_id']),
                    "count" => 1,
                    "data" => [
                        'name' => $org['name'],
                        'type' => $org['type'] ?? null,
                        'location' => $org['location'] ?? null,
                        'country' => $org['country'] ?? null,
                        'ror' => $org['ror'] ?? null,
                        'lat' => $org['lat'] ?? null,
                        'lng' => $org['lng'] ?? null,
                        'role' => $c['role'] ?? null
                    ]
                ];
            }
        }
    } else {
        $filter = ['collaborators' => ['$exists' => 1]];
        // only for portal
        if ($context == 'unit') {
            if ($id == '0') {
                // all units
            } else {
                $child_ids = $Groups->getChildren($id);
                $filter['units'] = ['$in' => $child_ids];
            }
        } elseif ($context == 'topic') {
            $filter['topics'] = $id;
        }
        $result = $osiris->projects->aggregate([
            ['$match' => $filter],
            ['$project' => ['collaborators' => 1, 'public' => 1]],
            ['$unwind' => '$collaborators'],
            [
                '$group' => [
                    '_id' => '$collaborators.organization',
                    'count' => ['$sum' => 1],
                    'public_count' => [
                        '$sum' => [
                            '$cond' => [
                                'if' => ['$eq' => ['$public', true]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ]
                    ],
                    'data' => [
                        '$first' => '$collaborators'
                    ]
                ]
            ],
            ['$lookup' => [
                'from' => 'organizations',
                'localField' => '_id',
                'foreignField' => '_id',
                'as' => 'org'
            ]],
            ['$unwind' => '$org'],
            ['$project' => [
                '_id' => 1,
                'count' => 1,
                'public_count' => 1,
                'data.name' => '$org.name',
                'data.type' => '$org.type',
                'data.location' => '$org.location',
                'data.country' => '$org.country',
                'data.ror' => '$org.ror',
                'data.lat' => '$org.lat',
                'data.lng' => '$org.lng'
            ]],
            ['$sort' => ['count' => -1]]

        ])->toArray();

        // set all roles to 'partner'
        foreach ($result as $r) {
            $r['data']['role'] = 'partner';
        }
    }

    $institute = $Settings->get('affiliation_details');
    $institute['role'] = $project['role'] ?? 'coordinator';
    $institute['current'] = true;
    if (isset($institute['lat']) && isset($institute['lng'])) {
        $result[] = [
            '_id' => $institute['ror'] ?? '',
            'count' => 1,
            'data' => $institute,
            'self' => true,
            // 'color' => 'secondary'
        ];
    }
    // if ($institute['role'] == 'coordinator') 
    // $result = array_reverse($result);
    echo rest($result, count($result));
});



Route::get('/portfolio/unit/([^/]*)/cooperation', function ($id) {
    // error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    // select publications from the past five years where the department is involved
    $filter = [
        'type' => 'publication',
        'hide' => ['$ne' => true],
        'year' => ['$gte' => CURRENTYEAR - 4],
        'units' => $id
    ];

    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }
    $options = [
        'projection' => [
            'depts' => '$units'
        ]
    ];

    $result = $osiris->activities->find($filter, $options)->toArray();

    // get matrix of shared publications between departments
    function combine($array)
    {
        $result = [];
        $n = count($array);
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $result[] = [$array[$i], $array[$j]];
            }
        }
        return $result;
    }

    $arr = [];
    $labels = [];
    foreach ($result as $doc) {
        $depts = $doc['depts'];
        foreach ($depts as $d) {
            if (empty($d)) continue;
            if (!isset($labels[$d])) {
                $g = $Groups->getGroup($d);
                if (empty($g) || ($g['hide'] ?? false)) continue;
                $labels[$d] = [
                    'id' => $d,
                    'name' => $g['name'],
                    'name_de' => $g['name_de'] ?? $g['name'],
                    'color' => $g['color'],
                    'count' => 0,
                    'selected' => ($id == $d)
                ];
            }
            $labels[$d]['count']++;
        }
        if (count($depts) == 1) {
            $d = $depts[0];
            if (!isset($arr[$d])) {
                $arr[$d] = [];
            }
            if (!isset($arr[$d][$d])) {
                $arr[$d][$d] = 0;
            }
            $arr[$d][$d]++;
        }
        $combinations = combine($depts);
        foreach ($combinations as $c) {
            if (empty($c[0]) || empty($c[1])) continue;
            // if ($c[0] == $c[1]) continue;
            if (!array_key_exists($c[0], $labels) || !array_key_exists($c[1], $labels)) continue;

            if (!isset($arr[$c[0]])) {
                $arr[$c[0]] = [];
            }
            if (!isset($arr[$c[1]])) {
                $arr[$c[1]] = [];
            }
            if (!isset($arr[$c[0]][$c[1]])) {
                $arr[$c[0]][$c[1]] = 0;
            }
            if (!isset($arr[$c[1]][$c[0]])) {
                $arr[$c[1]][$c[0]] = 0;
            }
            $arr[$c[0]][$c[1]]++;
            $arr[$c[1]][$c[0]]++;
        }
    }
    $matrix = []; // numberical matrix n x m
    foreach ($arr as $key => $val) {
        $row = [];
        foreach ($arr as $k => $v) {
            $row[] = $val[$k] ?? 0;
        }
        $matrix[] = $row;
    }

    echo rest([
        'matrix' => $matrix,
        'labels' => $labels,
        // 'warnings' => $warnings
    ], count($labels));
});





// Route::get('/portfolio/unit/([^/]*)/infrastructures', function ($id) {
//     include(BASEPATH . '/php/init.php');
//     if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
//         echo return_permission_denied();
//         die;
//     }

//     // select publications from the past five years where the department is involved
//     $filter = [
//         'type' => 'publication',
//         'hide' => ['$ne' => true],
//         'year' => ['$gte' => CURRENTYEAR - 4],
//         'units' => $id
//     ];


Route::get('/portfolio/infrastructures', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    include(BASEPATH . '/php/Infrastructure.php');
    $Infra = new Infrastructure();
    $filter = [
        'public' => true,
    ];
    $options = [
        'sort' => ['name' => 1],
        'projection' => [
            'id' => 1,
            'name' => 1,
            'name_de' => 1,
            'subtitle' => 1,
            'subtitle_de' => 1,
            'description' => 1,
            'description_de' => 1,
            'start_date' => 1,
            'end_date' => 1,
            'image' => 1
        ]
    ];

    $result = [];
    $data = $osiris->infrastructures->find(
        $filter,
        $options
    )->toArray();
    foreach ($data as $infrastructure) {
        $infrastructure['logo'] = $Infra->getLogo($infrastructure);
        $result[] = $infrastructure;
    }

    echo rest($result);
});

Route::get('/portfolio/infrastructure/([^/]*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    include(BASEPATH . '/php/Infrastructure.php');
    $Infra = new Infrastructure();
    include_once(BASEPATH . '/php/Vocabulary.php');
    $Vocabulary = new Vocabulary();

    $infrastructure = $osiris->infrastructures->findOne(
        ['id' => $id, 'public' => true],
        [
            'projection' => [
                'id' => 1,
                'name' => 1,
                'name_de' => 1,
                'subtitle' => 1,
                'subtitle_de' => 1,
                'description' => 1,
                'description_de' => 1,
                'start_date' => 1,
                'end_date' => 1,
                'contact_email' => 1,
                'link' => 1,
                'image' => 1,
                'type' => 1,
                'infrastructure_type' => 1,
                'access' => 1,
                'collaborative' => 1,
                'coordinator_institute' => 1,
                'coordinator_organization' => 1,
                'collaborators' => 1,
                'persons' => 1
            ]
        ]
    );
    if (empty($infrastructure)) {
        echo rest('Infrastructure not found', 0, 404);
        die;
    }
    $infrastructure['logo'] = $Infra->getLogo($infrastructure);
    unset($infrastructure['image']);
    $persons = [];
    if (!empty($infrastructure['persons'])) {
        foreach (DB::doc2Arr($infrastructure['persons']) as $p) {
            $person = $DB->getPerson($p['user']);
            if (empty($person) || ($person['hide'] ?? false)) continue;
            // check if end date is in the past
            if (isset($p['end']) && strtotime($p['end']) < time()) {
                continue;
            }
            $startyear = isset($p['start']) ? date('Y', strtotime($p['start'])) : null;
            $row = [
                'displayname' => $person['displayname'],
                'academic_title' => $person['academic_title'],
                'position' => $person['position'],
                'position_de' => $person['position_de'],
                'depts' => [],
                'since' => $startyear
            ];
            // if ($person['public_image'] ?? false) {
            //     $row['img'] = $Settings->printProfilePicture($person['username'], 'profile-img small mr-20');
            // } else {
            //     $row['img'] = $Settings->printProfilePicture(null, 'profile-img small mr-20');
            // }
            $row['id'] = strval($person['_id']);
            $row['role'] = $Infra->getRole($p['role'] ?? null, $raw = true);
            $units = $Groups->getPersonUnit($person, null, true, false);
            if (!empty($units)) {
                foreach ($units as $u) {
                    $dept = $Groups->getGroup($u[0]);
                    if ($dept['level'] === 1) {
                        $row['depts'][$dept['id']] = [
                            'en' => $dept['name'],
                            'de' => $dept['name_de']
                        ];
                    }
                }
            }
            $persons[] = $row;
        }
    }
    $infrastructure['persons'] = $persons;

    $infrastructure['category'] = $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-');
    $infrastructure['type'] = $Vocabulary->getValue('infrastructure-type', $infrastructure['infrastructure_type'] ?? '-');
    $infrastructure['access'] = $Vocabulary->getValue('infrastructure-access', $infrastructure['access'] ?? '-');

    // collaborative infrastructures
    if ($infrastructure['collaborative'] ?? false) {
        $coordinator = [];
        $collaborators = [];

        $institute = $Settings->get('affiliation_details');
        if ($infrastructure['coordinator_institute']) {
            $coordinator = [
                'name' => $institute['name'],
                'location' => $institute['location'] ?? null,
                'type' => 'self',
                'ror' => $institute['ror'] ?? null
            ];
        } else {
            $org = $osiris->organizations->findOne(['_id' => $infrastructure['coordinator_organization']]);
            $coordinator = [
                'name' => $org['name'],
                'location' => $org['location'] ?? null,
                'ror' => $org['ror'] ?? null,
                'type' => $org['type'] ?? null,
            ];
            $collaborators[] = [
                'name' => $institute['name'],
                'location' => $institute['location'] ?? null,
                'type' => 'self',
                'ror' => $institute['ror'] ?? null
            ];
        }

        if (!empty($infrastructure['collaborators'])) {
            foreach ($infrastructure['collaborators'] as $c) {
                $org = $osiris->organizations->findOne(['_id' => $c]);
                if (empty($org)) continue;
                $collaborators[] = [
                    'name' => $org['name'],
                    'location' => $org['location'] ?? null,
                    'ror' => $org['ror'] ?? null,
                    'type' => $org['type'] ?? null,
                ];
            }
        }
        $infrastructure['coordinator'] = $coordinator;
        $infrastructure['collaborators'] = $collaborators;
    }


    $infrastructure['n_activities'] = $osiris->activities->count([
        'infrastructures' => $infrastructure['id'],
        'hide' => ['$ne' => true]
    ]);
    echo rest($infrastructure);
});


Route::get('/portfolio/infrastructure/([^/]*)/activities', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $filter = [
        'infrastructures' => $id,
        'hide' => ['$ne' => true]
    ];
    if ($Settings->featureEnabled('quality-workflow')) {
        $visibility = $Settings->get('portfolio-workflow-visibility', 'all');
        if ($visibility == 'only-approved') {
            $filter['workflow.status'] = 'verified';
        } elseif ($visibility == 'approved-or-empty') {
            $filter['$or'] = [
                ['workflow' => ['$exists' => false]],
                ['workflow.status' => 'verified'],
                ['workflow.status' => ['$exists' => false]]
            ];
        }
    }
    $options = [
        'sort' => ['year' => -1, 'month' => -1],
        'projection' => [
            'html' => '$rendered.portfolio',
            'print' => '$rendered.print',
            'search' => '$rendered.plain',
            'type' => 1,
            'subtype' => 1,
            'year' => 1,
            'month' => 1,
            'day' => 1,
            'icon' => '$rendered.icon',
            'affiliated' => 1
        ]
    ];
    $result = $osiris->activities->find(
        $filter,
        $options
    )->toArray();
    echo rest($result);
});


// get all projects
Route::get('/portfolio/projects', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $filter = [
        'public' => true,
    ];

    $options = [
        'sort' => ['year' => -1, 'month' => -1],
        'projection' => [
            '_id' => 0,
            'id' => ['$toString' => '$_id'],
            'name' => 1,
            'title' => 1,
            'funder' => 1,
            'funding_organization' => 1,
            'funding_number' => 1,
            'role' => 1,
            'start' => 1,
            'end' => 1,
            'type' => 1,
            'teaser_en' => 1,
            'teaser_de' => 1,
            'image' => 1
        ]
    ];

    $result = $osiris->projects->find(
        $filter,
        $options
    )->toArray();

    echo rest($result);
});

// get all persons
Route::get('/portfolio/persons', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $filter = [
        'hide' => ['$ne' => true]
    ];

    $persons = $osiris->persons->find(
        $filter,
        [
            'sort' => ['last' => 1],
            'projection' => [
                'id' => ['$toString' => '$_id'],
                'displayname' => 1,
                'academic_title' => 1,
                'position' => 1,
                'position_de' => 1,
                'depts' => 1
            ]
        ]
    )->toArray();

    echo rest($persons);
});

// get all persons
Route::get('/portfolio/person-images', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $filter = [
        'hide' => ['$ne' => true],
        'public_image' => true
    ];

    $persons = $osiris->persons->find(
        $filter,
        [
            'sort' => ['last' => 1],
            'projection' => [
                'username' => 1,
            ]
        ]
    )->toArray();

    $result = [];
    foreach ($persons as $person) {
        $user = $person['username'];
        if ($Settings->featureEnabled('db_pictures')) {
            $img = $Settings->getRequestScheme() . '://' . $_SERVER['HTTP_HOST'] . ROOTPATH . "/image/$user";
        } else {
            if (!file_exists(BASEPATH . "/img/users/$user.jpg")) {
                continue;
            } else {
                $img = $Settings->getRequestScheme() . '://' . $_SERVER['HTTP_HOST'] . ROOTPATH . "/img/users/$user.jpg";
            }
        }

        $result[] = [
            'id' => strval($person['_id']),
            'path' => $img
        ];
    }

    echo rest($result);
});




// get all news
Route::get('/portfolio/news', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');
    if (!portfolio_apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    if (!$Settings->featureEnabled('news', true)) {
        echo rest([], 0);
        die;
    }
    $filter = [
        'visibility' => 'public',
    ];
    $news = $osiris->news->find(
        $filter,
        [
            'sort' => ['date' => -1],
            'projection' => [
                'id' => ['$toString' => '$_id'],
                'title' => 1,
                'title_de' => 1,
                'teaser' => 1,
                'teaser_de' => 1,
                'content' => 1,
                'content_de' => 1,
                'date' => 1,
                'image' => 1,
                'activities' => 1
            ]
        ]
    )->toArray();

    echo rest($news);
});
