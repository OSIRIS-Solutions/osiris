<?php

/**
 * Routing for API
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

function apikey_check($key = null)
{
    $Settings = new Settings();
    $APIKEY = $Settings->get('apikey');
    // dump($_SERVER);
    // 1) Logged-in user via session: always allow
    if (($_SESSION['loggedin'] ?? false) && !empty($_SESSION['username'])) {
        if (isset($_SERVER['HTTP_SEC_FETCH_SITE'])) {
            if ($_SERVER['HTTP_SEC_FETCH_SITE'] === 'same-site' || $_SERVER['HTTP_SEC_FETCH_SITE'] === 'same-origin') {
                return true;
            }
        } else {
            // no Sec-Fetch-Site header (old browser/proxy) → fallback: same host via Referer
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $refHost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
                $host    = $_SERVER['HTTP_HOST'] ?? '';
                if ($refHost === $host) {
                    return true;
                }
            }
        }
    }

    // 2) If no API key is configured, nothing to check
    if (empty($APIKEY)) {
        return true;
    }

    // 3) Check query param ?apikey=...
    if ($APIKEY === $key) {
        return true;
    }

    // 4) Optional: allow header X-API-Key
    if (isset($_SERVER['HTTP_X_API_KEY']) && $_SERVER['HTTP_X_API_KEY'] === $APIKEY) {
        return true;
    }

    // 5) Everything else: no access
    return false;
    // $Settings = new Settings();
    // $APIKEY = $Settings->get('apikey');
    // // always true if API Key is not set
    // if (!isset($APIKEY) || empty($APIKEY)) return true;
    // // return true for same page origin
    // if (isset($_SERVER['HTTP_SEC_FETCH_SITE']) && $_SERVER['HTTP_SEC_FETCH_SITE'] == 'same-origin') return true;
    // // check if API key is valid
    // if ($APIKEY == $key) return true;
    // // otherwise return false
    // return false;
}

function return_permission_denied()
{
    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");
    return json_encode(array(
        'status' => 403,
        'count' => 0,
        'error' => 'PermissionDenied',
        'msg' => 'You need a valid API key for this request.'
    ), JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function return_rest($data, $count = 0, $status = 200)
{
    $result = array();
    $limit = intval($_GET['limit'] ?? 0);

    if (!empty($limit) && $count > $limit && is_array($data)) {
        $offset = intval($_GET['offset'] ?? 0) || 0;
        $data = array_slice($data, $offset, min($limit, $count - $offset));
        $result += array(
            'limit' => $limit,
            'offset' => $offset
        );
    }
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
    return json_encode($result, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
}

function return_rest_stream($data)
{
    header("Content-Type: application/json; charset=utf-8");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '{"status":200,"data":[';
    $i = 0;
    $output = '';
    foreach ($data as $doc) {
        if (empty($doc)) continue;
        $i++;
        // ensure no double commas
        if ($i > 1 && !empty($output)) echo ',';
        $output = json_encode($doc, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
        echo $output;
        // optional: flush() für Chunked Transfer
        flush();
    }
    echo '],"count":' . $i . '}';
}

Route::get('/api/activities', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    include_once BASEPATH . "/php/Render.php";

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }

    if (!isset($_GET['apikey']) && isset($_SESSION['username'])) {
        $filter = $Settings->getActivityFilter($filter);
    }

    if (isset($_GET['aggregate'])) {
        // aggregate by one column
        $group = $_GET['aggregate'];
        $aggregate = [
            ['$match' => $filter],
        ];
        if (strpos($group, 'authors') !== false) {
            $aggregate[] = ['$unwind' => '$authors'];
        }
        $aggregate[] =
            ['$group' => ['_id' => '$' . $group, 'count' => ['$sum' => 1]]];

        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'value' => '$_id', 'count' => 1]];
        // $aggregate[] = ['$limit' => 10];
        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'value' => 1, 'count' => 1]];
        // $aggregate = array_merge($filter);

        $result = $osiris->activities->aggregate(
            $aggregate
        )->toArray();
        echo return_rest($result, count($result));
        die;
    }

    if (isset($_GET['full'])) {
        $cursor = $osiris->activities->find(
            $filter,
            [
                'sort' => ['_id' => -1],
                // 'projection' => ['rendered' => 0, 'files' => 0],
                'batchSize' => 500,
                // 'noCursorTimeout' => true, // nur wenn nötig
            ]
        );
        return_rest_stream($cursor);
        return;
    }

    $projection = [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
    ];

    if (isset($_GET['columns'])) {
        $columns = $_GET['columns'];
        foreach ($columns as $c) {
            if (in_array($c, ['web', 'print', 'icon', 'type', 'subtype', 'authors', 'title', 'departments'])) {
                $projection[$c] = '$rendered.' . $c;
            } else {
                $projection[$c] = '$' . $c;
            }
        }
    } else {
        $projection = [
            '_id' => 0,
            'id' => ['$toString' => '$_id'],
            'activity' => '$rendered.web',
            // 'print' => '$rendered.print',
            'icon' => '$rendered.icon',
            'type' => '$rendered.type',
            'subtype' => '$rendered.subtype',
            'year' => '$year',
            // 'authors' => '$rendered.authors',
            // 'title' => '$rendered.title',
            // 'departments' => '$units'
        ];
    }

    $pipeline = [];
    // Nur `$match` hinzufügen, wenn `$filter` nicht leer ist
    if (!empty($filter)) {
        $pipeline[] = ['$match' => $filter];
    }
    // Füge das Sortieren und die Projektion hinzu
    $pipeline[] = ['$sort' => ['year' => -1]];
    $pipeline[] = [
        '$project' => $projection
    ];

    // Führe die Aggregation aus
    $result = $osiris->activities->aggregate($pipeline)->toArray();

    echo return_rest($result, count($result));
});


Route::get('/api/html', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    include_once BASEPATH . "/php/Render.php";
    include_once BASEPATH . "/php/Document.php";
    $Format = new Document(true, 'dsmz.de');
    $Format->full = true;
    // $Format->abbr_journal = true;

    $result = [];
    $docs = $osiris->activities->find([
        'type' => 'publication',
        'authors.aoi' => ['$in' => [true, 1, '1']],
        'year' => ['$gte' => 2023]
    ]);

    foreach ($docs as $i => $doc) {
        if (isset($_GET['limit']) && $i >= $_GET['limit']) break;

        if (isset($doc['rendered'])) {
            $rendered = $doc['rendered'];
        } else {
            $rendered = renderActivities(['_id' => $id]);
        }

        $link = null;
        if (!empty($doc['doi'] ?? null)) {
            $link = "https://dx.doi.org/" . $doc['doi'];
        } elseif (!empty($doc['pubmed'] ?? null)) {
            $link = "https://www.ncbi.nlm.nih.gov/pubmed/" . $doc['pubmed'];
        } elseif (!empty($doc['link'] ?? null)) {
            $link = $doc['link'];
        }
        $depts = DB::doc2Arr($doc['units'] ?? []);
        $depts = array_intersect($depts, array_keys($Departments));
        $depts = array_values($depts);
        $entry = [
            'id' => strval($doc['_id']),
            'html' => $rendered['print'],
            'year' => $doc['year'] ?? null,
            'departments' => $depts,
            'link' => $link
        ];
        $result[] = $entry;
    }

    echo return_rest($result, count($result));
});

Route::get('/api/all-activities', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    // if (!apikey_check($_GET['apikey'] ?? null)) {
    //     echo return_permission_denied();
    //     die;
    // }

    include_once BASEPATH . "/php/Render.php";
    include_once BASEPATH . "/php/Document.php";


    $user = $_GET['user'] ?? $_SESSION['username'] ?? null;
    $page = $_GET['page'] ?? 'all-activities';

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }

    if (isset($filter['projects'])) {
        $filter['projects'] = DB::to_ObjectID($filter['projects']);
    }
    // if (!isset($_GET['apikey']) && isset($_SESSION['username'])) {

    if (isset($_GET['type']) && $_GET['type'] !== '') {
        $filter['type'] = $_GET['type'];
    }
    // if (!empty($filter)){
    //     $filter = ['$and' => [$filter]];
    // }

    $perm_filter = $Settings->getActivityFilter($filter);
    if ($page == "my-activities") {
        // reduced filter for my activities
        if (!empty($perm_filter)) {
            $filter = ['$and' => [$perm_filter]];
        } else {
            $filter = [];
        }
        $filter['$and'][] = [
            '$or' => [['authors.user' => $user], ['editors.user' => $user], ['user' => $user]]
        ];
    } else if (!empty($perm_filter)) {
        if (!isset($_GET['apikey']) && isset($_SESSION['username'])) {
            $filter['$and'][] = $perm_filter;
        }
    }
    // stream output
    header("Content-Type: application/json; charset=utf-8");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo '{"status":200,"data":[';
    $i = 0;
    $first = true;

    $display = $_GET['display_activities'] ?? 'web';
    $activityField = $display === 'web'
        ? '$rendered.web'
        : '$rendered.print';

    $pipeline = [];
    if (!empty($filter)) {
        $pipeline[] = ['$match' => $filter];
    }
    $pipeline[] = ['$project' => [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
        'quarter' => ['$ifNull' => ['$rendered.quarter', '']],
        'icon' => '$rendered.icon',
        'activity' => $activityField,
        'links' => ['$literal' => ''],
        'search-text' => '$rendered.print',
        'start' => ['$ifNull' => ['$start_date', '']],
        'end' => ['$ifNull' => ['$end_date', '']],
        'departments' => '$units',
        'epub' => '$epub',
        'type' => '$rendered.type',
        'subtype' => '$rendered.subtype',
        'year' => ['$ifNull' => ['$year', 0]],
        'authors' => ['$ifNull' => ['$rendered.authors', '']],
        'editors' => ['$ifNull' => ['$rendered.editors', '']],
        'title' => ['$ifNull' => ['$rendered.title', '']],
        'topics' => ['$ifNull' => ['$topics', []]],
        'raw_type' => '$type',
        'raw_subtype' => '$subtype',
        'affiliated' => ['$ifNull' => ['$affiliated', false]],
        'workflow' => ['$ifNull' => ['$workflow.status', 'verif']],
        'tags' => '$tags'
    ]];

    $cursor = $osiris->activities->aggregate($pipeline);

    foreach ($cursor as $doc) {
        $i++;
        // Pfad-Anpassung wie bisher
        if (isset($_GET['path'])) {
            $doc['activity'] = str_replace(
                [ROOTPATH . "/activities/view", ROOTPATH . "/profile"],
                [$_GET['path'] . "/activity", $_GET['path'] . "/person"],
                $doc['activity']
            );
        } elseif ($page === 'portal') {
            $doc['activity'] = str_replace(
                [ROOTPATH . "/activities/view", ROOTPATH . "/profile"],
                [PORTALPATH . "/activity", PORTALPATH . "/person"],
                $doc['activity']
            );
        }
        if (!$first) echo ',';
        echo json_encode($doc, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
        $first = false;
        flush();
    }
    echo '],"count":' . $i . '}';
    // echo return_rest_stream($result, count($result));
});



Route::get('/api/concept-activities', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    include_once BASEPATH . "/php/Document.php";

    $name = $_GET['concept'];

    $concepts = $osiris->activities->aggregate(
        [
            ['$match' => ['concepts.display_name' => $name]],
            ['$project' => ['rendered' => 1, 'concepts' => 1]],
            ['$unwind' => '$concepts'],
            ['$match' => ['concepts.display_name' => $name]],
            ['$sort' => ['concepts.score' => -1]],
            ['$project' => [
                '_id' => 0,
                'score' => '$concepts.score',
                'icon' => '$rendered.icon',
                'activity' => '$rendered.web',
                'type' => '$rendered.type',
                'id' => ['$toString' => '$_id']
            ]]
        ]
    )->toArray();

    echo return_rest($concepts);
});


Route::get('/api/(conferences|events)', function ($type) {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $events = $osiris->conferences->find(
        [],
        ['sort' => ['start' => -1]]
    )->toArray();

    foreach ($events as $i => $row) {
        $events[$i]['id'] = strval($row['_id']);
    }

    echo return_rest($events);
});

Route::get('/api/users', function () {
    error_reporting(E_ERROR | E_PARSE);
    if (!isset($_POST['debug'])) {
        error_reporting(E_ERROR);
        ini_set('display_errors', 0);
    }
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $path = ROOTPATH;
    if (isset($_GET['path'])) {
        $path = $_GET['path'];
    }

    $filter = ['username' => ['$ne' => null]];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        }
        if (isset($filter['is_active'])) {
            $filter['is_active'] = boolval($filter['is_active']);
        }
    }
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }
    if (isset($filter['units'])) {
        $filter['units'] = [
            '$elemMatch' => [
                'unit' => ['$in' => $filter['units']],
                '$and' => [
                    ['$or' => [
                        ['start' => null],
                        ['start' => ['$lte' => date('Y-m-d')]]
                    ]],
                    ['$or' => [
                        ['end' => null],
                        ['end' => ['$gte' => date('Y-m-d')]]
                    ]]
                ]
            ]
        ];
    }
    $result = $osiris->persons->find($filter)->toArray();

    if (isset($_GET['full'])) {
        echo return_rest($result, count($result));
        die;
    }

    $topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
    $table = [];
    foreach ($result as $user) {
        $subtitle = "";
        if (isset($user['is_active']) && !$user['is_active']) {
            $subtitle = '<span class="badge text-danger">' . lang('Former employee', 'Ehemalige Beschäftigte') . '</span>';
        } elseif (isset($_GET['subtitle'])) {
            if ($_GET['subtitle'] == 'position') {
                $subtitle = lang($user['position'] ?? '', $user['position_de'] ?? null);
            } else {
                $subtitle = $user[$_GET['subtitle']] ?? '';
            }
        } else foreach (($user['depts'] ?? []) as $i => $d) {
            $dept = implode('/', $Groups->getParents($d));
            $subtitle .= '<a href="' . $path . '/groups/view/' . $d . '">
                    ' . $dept . '
                </a>';
        }
        $guest = '';
        if (isset($user['is_guest']) && $user['is_guest']) {
            $guest = ' <i class="ph ph-user-plus float-right text-signal" title="' . lang('Guest Account', 'Gast-Account') . '"></i>';
        }
        $topics = '';
        if ($topicsEnabled && $user['topics'] ?? false) {
            $topics = '<span class="float-right topic-icons">';
            foreach ($user['topics'] as $topic) {
                $topics .= '<a href="' . ROOTPATH . '/topics/view/' . $topic . '" class="topic-icon topic-' . $topic . '"></a> ';
            }
            $topics .= '</span>';
        }
        // dump($Groups->deptHierarchy($user['units'] ?? [], 1)['id'], true);
        $units = $Groups->getPersonDept($user['units'] ?? []);
        if (empty(trim($user['last'])) && empty(trim($user['first']))) {
            if (empty($user['username'])) {
                // this should not happen, but if it does, we set a default name
                $user['username'] = 'unknown_user';
            }
            $user['last'] = $user['username'];
        }
        $table[] = [
            'id' => strval($user['_id']),
            'username' => $user['username'],
            'img' => $Settings->printProfilePicture($user['username'], 'profile-img'),
            'html' =>  "<div class='w-full'>
                    <div style='display: none;'>" . $user['first'] . " " . $user['last'] . "</div>$guest
                    $topics
                    <h5 class='my-0'>
                        <a href='" . $path . "/profile/" . $user['_id'] . "'>
                            " . ($user['academic_title'] ?? '') . " " . $user['first'] . " " . $user['last'] . "
                        </a>
                    </h5>
                    <small>
                        " . $subtitle . "
                    </small>
                    <span class='hidden'>$user[username]</span>
                </div>",
            'name' => $user['first'] . " " . $user['last'],
            'names' => !empty($user['names'] ?? null) ? implode(', ', DB::doc2Arr($user['names'])) : '',
            'first' => $user['first'],
            'last' => $user['last'],
            'position' => lang($user['position'] ?? '', $user['position_de'] ?? null),
            'mail' => $user['mail'] ?? '',
            'telephone' => $user['telephone'] ?? '',
            'orcid' => $user['orcid'] ?? '',
            'academic_title' => $user['academic_title'],
            'dept' => $units,
            'active' => ($user['is_active'] ?? true) ? 'yes' : 'no',
            'public_image' => $user['public_image'] ?? true,
            'topics' => $user['topics'] ?? array(),
            'keywords' => $user['keywords'] ?? array(),
        ];
    }
    echo return_rest($table, count($table));
});



Route::get('/api/users/(.*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    if (DB::is_ObjectID($id)) {
        $filter = ['_id' => DB::to_ObjectID($id)];
    } else {
        $filter = ['username' => $id];
    }
    $options = [];
    if (isset($_GET['columns'])) {
        $options['projection'] = [
            'id' => ['$toString' => '$_id']
        ];
        foreach ($_GET['columns'] as $c) {
            $options['projection'][$c] = 1;
        }
    }
    $user = $osiris->persons->findOne($filter, $options);

    if (empty($user)) {
        echo return_rest('User not found', 0, 404);
        die;
    }

    echo return_rest($user, 1);
});

Route::get('/api/user-units/(.*)', function ($id) {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    // if (!apikey_check($_GET['apikey'] ?? null)) {
    //     echo return_permission_denied();
    //     die;
    // }
    if (DB::is_ObjectID($id)) {
        $filter = ['_id' => DB::to_ObjectID($id)];
    } else {
        $filter = ['username' => $id];
    }
    $person = $osiris->persons->findOne($filter, ['units' => 1, 'last' => 1, 'first' => 1]);
    if (empty($person)) {
        echo return_rest(lang('User not found', 'Nutzer nicht gefunden'), 0, 404);
        die;
    }
    if (empty($person['units'] ?? null)) {
        return_rest([
            'name' => $person['first'] . ' ' . $person['last'],
            'units' => []
        ]);
    }
    $used_unit_ids = [];
    $units = [];
    $person_units = DB::doc2Arr($person['units'] ?? []);
    // reverse to see newest first
    $person_units = array_reverse($person_units);
    foreach ($person_units as $unit) {
        // make sure units are unique
        if (in_array($unit['unit'], $used_unit_ids)) continue; // skip duplicates
        $unit['in_past'] =  isset($unit['end']) && date('Y-m-d') > $unit['end'];
        $group = $Groups->getGroup($unit['unit']);
        $unit['name'] = lang($group['name'] ?? 'Unit not found', $group['name_de'] ?? null);
        $used_unit_ids[] = $unit['unit'];
        $units[] = $unit;
    }

    echo return_rest([
        'name' => $person['first'] . ' ' . $person['last'],
        'units' => $units
    ], 1);
});

Route::get('/api/reviews', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    $filter['type'] = 'review';
    $result = $osiris->activities->find($filter)->toArray();

    $reviews = [];
    foreach ($result as $doc) {
        $authors = DB::doc2Arr($doc['authors'] ?? []);
        $user = $authors[0]['user'] ?? null;
        if (empty($user) && isset($doc['user'])) {
            $user = $doc['user'];
        }
        if (!array_key_exists($user, $reviews)) {
            $u = $DB->getNameFromId($user);
            $reviews[$user] = [
                'User' => $user,
                'Name' => $u,
                'Editor' => 0,
                'Editorials' => [],
                'Reviewer' => 0,
                "Reviews" => []
            ];
        }
        switch (strtolower($doc['subtype'] ?? $doc['role'] ?? 'review')) {
            case 'editor':
            case 'editorial':
                $reviews[$user]['Editor']++;
                $date = format_date($doc['start'] ?? $doc);
                if (isset($doc['end']) && !empty($doc['end'])) {
                    $date .= " - " . format_date($doc['end']);
                } else {
                    $date .= " - today";
                }

                $reviews[$user]['Editorials'][] = [
                    'id' => strval($doc['_id']),
                    'date' => $date,
                    'details' => $doc['editor_type'] ?? ''
                ];
                break;

            case 'reviewer':
            case 'review':
                $reviews[$user]['Reviewer']++;
                $reviews[$user]['Reviews'][] = [
                    'id' => strval($doc['_id']),
                    'date' => format_date($doc)
                ];
                break;
            default:
                $reviews[$user]['Reviewer']++;
                $reviews[$user]['Reviews'][] = [
                    'id' => strval($doc['_id']),
                    'date' => format_date($doc)
                ];
                break;
        }
    }

    $table = array_values($reviews);

    echo return_rest($table, count($result));
});


Route::get('/api/teaching', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = [];
    if (isset($_GET['search'])) {
        $j = new \MongoDB\BSON\Regex(trim($_GET['search']), 'i');
        $filter = ['$or' =>  [
            ['title' => ['$regex' => $j]],
            ['module' => $_GET['search']]
        ]];
    }
    $result = $osiris->teaching->find($filter)->toArray();
    echo return_rest($result, count($result));
});


Route::get('/api/(projects|proposals)', function ($type) {

    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Project.php";
    $Project = new Project();

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $collection = $osiris->$type;

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }
    if (isset($filter['public'])) $filter['public'] = boolval($filter['public']);

    if (isset($_GET['search'])) {
        $j = new \MongoDB\BSON\Regex(trim($_GET['search']), 'i');
        $filter = ['title' => ['$regex' => $j]];
    }

    if (!$Settings->hasPermission($type . '.view')) {
        $filter['$or'] = [
            ['persons.user' => $_SESSION['username']],
            ['created_by' => $_SESSION['username']]
        ];
    }

    if (isset($_GET['aggregate'])) {
        // aggregate by one column
        $group = $_GET['aggregate'];
        $aggregate = [
            ['$match' => $filter],
        ];
        if (strpos($group, 'persons') !== false) {
            $aggregate[] = ['$unwind' => '$persons'];
        } elseif (strpos($group, 'collaborators') !== false) {
            $aggregate[] = ['$unwind' => '$collaborators'];
        }
        $aggregate[] =
            ['$group' => ['_id' => '$' . $group, 'count' => ['$sum' => 1]]];

        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'value' => '$_id', 'count' => 1]];
        // $aggregate[] = ['$limit' => 10];
        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'value' => 1, 'count' => 1]];
        // $aggregate = array_merge($filter);

        $result = $collection->aggregate(
            $aggregate
        )->toArray();
        echo return_rest($result, count($result));
        die;
    }

    if (isset($_GET['full'])) {
        $cursor = $collection->find(
            $filter,
            [
                'sort' => ['_id' => -1],
                // 'projection' => ['rendered' => 0, 'files' => 0],
                'batchSize' => 500,
                // 'noCursorTimeout' => true, // nur wenn nötig
            ]
        );
        return_rest_stream($cursor);
        return;
    }


    if (isset($_GET['formatted'])) {
        $result = $collection->find($filter)->toArray();
        $data = [];
        foreach ($result as $project) {
            $Project->setProject($project);
            $data[] = [
                'id' => strval($project['_id']),
                'name' => $project['name'],
                'title' => $project['title'],
                'type' => $project['type'],
                'status' => $project['status'],
                'date_range' => $Project->getDateRange(),
                'start' => $project['start_date'] ?? '',
                'funder' => $project['funder'] ?? '-',
                'funding_organization' => ($project['funding_organization'] ?? '-'),
                'funding_numbers' => $Project->getFundingNumbers('; '),
                'applicant' => $DB->getNameFromId($project['contact'] ?? $project['supervisor'] ?? ''),
                'activities' => $osiris->activities->count(['projects' => strval($project['name'])]),
                'role' => $project['role'] ?? '',
                'topics' => $project['topics'] ?? array(),
                'units' => $Project->getUnits(true),
                'persons' => array_column(DB::doc2Arr($project['persons'] ?? []), 'name'),
                'subproject' => $doc['subproject'] ?? false,
                'tags' => $project['tags'] ?? [],
            ];
        }
        echo return_rest($data, count($data));
    }

    $projection = [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
    ];

    if (isset($_GET['columns'])) {
        $columns = $_GET['columns'];
        foreach ($columns as $c) {
            $projection[$c] = '$' . $c;
        }
    } else if ($type == 'projects') {
        $projection = [
            '_id' => 0,
            'id' => ['$toString' => '$_id'],
            'type' => '$type',
            'funder' => '$funder',
            'scholarship' => '$scholarship',
            'start_date' => '$start_date',
            'end_date' => '$end_date',
            'role' => '$role',
            'applicant' => '$applicant',
            'proposal_id' => '$proposal_id',
            'units' => '$units',
            'topics' => '$topics',
            'funding_organization' => '$funding_organization',
            'name' => '$name',
            'title' => '$title',
            'persons' => '$persons',
            'subproject' => '$subproject',
            'timeline' => '$timeline',
            'tags' => '$tags',
        ];
    } else if ($type == 'proposals') {
        $projection = [
            '_id' => 0,
            'id' => ['$toString' => '$_id'],
            'name' => '$name',
            'type' => '$type',
            'funder' => '$funder',
            'start_date' => '$start_date',
            'end_date' => '$end_date',
            'role' => '$role',
            'applicant' => '$applicant',
            'status' => '$status',
            'units' => '$units',
            'topics' => '$topics',
            'funder' => '$funder',
            'name' => '$name',
            'title' => '$title',
            'tags' => '$tags',
            'persons' => '$persons',
        ];
    }

    $pipeline = [];
    // Nur `$match` hinzufügen, wenn `$filter` nicht leer ist
    if (!empty($filter)) {
        $pipeline[] = ['$match' => $filter];
    }
    // Füge das Sortieren und die Projektion hinzu
    $pipeline[] = ['$sort' => ['year' => -1]];
    $pipeline[] = [
        '$project' => $projection
    ];

    // Führe die Aggregation aus
    $result = $collection->aggregate($pipeline)->toArray();

    if (!isset($_GET['raw'])) {
        foreach ($result as &$row) {
            $Project->setProject($row);
            foreach ($row as $key => $value) {
                $row[$key] = $Project->printField($key, $value);
            }
        }
    }

    echo return_rest($result, count($result));
});



Route::get('/api/search/(projects|proposals|activities|conferences|journals|persons)', function ($type) {

    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $collection = $osiris->$type;

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }
    if (isset($filter['public'])) $filter['public'] = boolval($filter['public']);

    if (isset($_GET['search'])) {
        $j = new \MongoDB\BSON\Regex(trim($_GET['search']), 'i');
        $filter = ['title' => ['$regex' => $j]];
    }

    if ($type == 'projects' || $type == 'proposals') {
        include_once BASEPATH . "/php/Project.php";
        $Project = new Project();
        if (!$Settings->hasPermission($type . '.view')) {
            $filter['$or'] = [
                ['persons.user' => $_SESSION['username']],
                ['created_by' => $_SESSION['username']]
            ];
        }
    } elseif ($type == 'activities') {
        if (!isset($_GET['apikey']) && isset($_SESSION['username'])) {
            $filter = $Settings->getActivityFilter($filter);
        }
    }

    $unwinds = ['authors', 'editors', 'persons', 'collaborators', 'topics', 'metrics', 'impact', 'units'];

    if (isset($_GET['aggregate'])) {
        // aggregate by one column
        $group = $_GET['aggregate'];
        $aggregate = [
            ['$match' => $filter],
        ];
        $group_parts = explode('.', $group);
        $first_part = $group_parts[0];
        if (in_array($first_part, $unwinds)) {
            // preserve null and empty arrays
            $aggregate[] = ['$unwind' => ['$path' => '$' . $first_part, 'preserveNullAndEmptyArrays' => true]];
        }
        $aggregate[] =
            ['$group' => ['_id' => '$' . $group, 'count' => ['$sum' => 1]]];

        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'value' => '$_id', 'count' => 1]];
        // $aggregate[] = ['$limit' => 10];
        $aggregate[] = ['$sort' => ['count' => -1]];
        $aggregate[] = ['$project' => ['_id' => 0, 'value' => 1, 'count' => 1]];
        // $aggregate = array_merge($filter);

        $result = $collection->aggregate(
            $aggregate
        )->toArray();
        echo return_rest($result, count($result));
        die;
    }

    $projection = [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
    ];
    $unwind = [];
    if (isset($_GET['columns'])) {
        $columns = $_GET['columns'];
        foreach ($columns as $c) {
            if ($c == 'id') continue;
            if ($type == 'activities' && in_array($c, ['web', 'print', 'icon', 'type', 'subtype', 'authors', 'title', 'departments'])) {
                $projection[$c] = '$rendered.' . $c;
                continue;
            }
            if (in_array(explode('.', $c)[0], $unwinds)) {
                $unwind[] = ['$unwind' => '$' . explode('.', $c)[0]];
            }
            $projection[$c] = '$' . $c;
        }
    } else {
        // default projection
        $projection = match ($type) {
            'activities' => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
                'activity' => '$rendered.web',
                'icon' => '$rendered.icon',
                'type' => '$rendered.type',
                'subtype' => '$rendered.subtype',
                'year' => '$year',
            ],
            'conferences' => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
                'name' => '$name',
                'location' => '$location',
                'start' => '$start',
                'end' => '$end',
            ],
            'journals' => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
                'journal' => '$journal',
                'issn' => '$issn',
                'publisher' => '$publisher',
            ],
            'projects' => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
                'name' => '$name',
                'title' => '$title',
                'type' => '$type',
            ],
            'proposals' => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
                'name' => '$name',
                'title' => '$title',
                'type' => '$type',
            ],
            'persons' => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
                'first' => '$first',
                'last' => '$last',
                'username' => '$username',
            ],
            default => [
                '_id' => 0,
                'id' => ['$toString' => '$_id'],
            ]
        };
    }

    $pipeline = [];
    if (!empty($unwind)) {
        $pipeline = array_merge($pipeline, $unwind);
    }
    // Nur `$match` hinzufügen, wenn `$filter` nicht leer ist
    if (!empty($filter)) {
        $pipeline[] = ['$match' => $filter];
    }
    // Füge das Sortieren und die Projektion hinzu
    $pipeline[] = ['$sort' => ['year' => -1]];
    $pipeline[] = [
        '$project' => $projection
    ];

    // Führe die Aggregation aus
    $result = $collection->aggregate($pipeline)->toArray();

    if (!isset($_GET['raw']) && ($type == 'projects' || $type == 'proposals')) {
        foreach ($result as &$row) {
            $Project->setProject($row);
            foreach ($row as $key => $value) {
                $row[$key] = $Project->printField($key, $value);
            }
        }
    }

    echo return_rest($result, count($result));
});



// get projects by funding number
Route::get('/api/projects-by-funding-number', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    if (!isset($_GET['number'])) {
        echo return_rest('No funding number provided', 0, 400);
        die;
    }
    if (is_array($_GET['number'])) {
        $number = array_map('urldecode', $_GET['number']);
    } else {
        $number = explode(',', urldecode($_GET['number']));
    }
    $filter = ['funding_number' => ['$in' => $number]];
    $result = $osiris->projects->find(
        $filter,
        ['projection' => ['name' => 1, 'title' => 1, '_id' => 1]]
    )->toArray();
    echo return_rest($result, count($result));
});

Route::get('/api/journal', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = [];
    if (isset($_GET['search'])) {
        $j = new \MongoDB\BSON\Regex(trim($_GET['search']), 'i');
        $filter = ['$or' =>  [
            ['journal' => ['$regex' => $j]],
            ['issn' => $_GET['search']]
        ]];
    }
    $result = $osiris->journals->find($filter,)->toArray();
    echo return_rest($result, count($result));
});

Route::get('/api/journals', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");
    $pipeline = [
        [
            '$unwind' => [
                'path' => '$impact',
                'preserveNullAndEmptyArrays' => true
            ]
        ],
        [
            '$sort' => ['impact.year' => -1]
        ],
        [
            '$group' => [
                '_id' => '$_id',
                'journal' => ['$first' => '$journal'],
                'abbr' => ['$first' => '$abbr'],
                'publisher' => ['$first' => '$publisher'],
                'open_access' => ['$first' => '$oa'],
                'issn' => ['$first' => '$issn'],
                'country' => ['$first' => '$country'],
                'latest_impact' => ['$first' => '$impact']
            ]
        ],
        [
            '$project' => [
                'id' => ['$toString' => '$_id'],
                'name' => '$journal',
                'abbr' => '$abbr',
                'publisher' => 1,
                'open_access' => '$open_access',
                'issn' => '$issn',
                'country' => '$country',
                'if' => '$latest_impact',
            ]
        ],
        [
            '$lookup' => [
                'from' => 'activities',
                'localField' => 'id',
                'foreignField' => 'journal_id',
                'as' => 'related_activities'
            ]
        ],
        [
            '$addFields' => [
                'count' => ['$size' => '$related_activities'],
            ]
        ],
        [
            '$sort' => ['count' => -1]
        ],
        [
            '$project' => [
                'id' => 1,
                'name' => 1,
                'abbr' => 1,
                'publisher' => 1,
                'open_access' => 1,
                'issn' => 1,
                'country' => 1,
                'if' => 1,
                'count' => 1
            ]
        ]
    ];

    $journals = $osiris->journals->aggregate($pipeline)->toArray();

    echo return_rest($journals, count($journals));
});



Route::get('/api/google', function () {
    error_reporting(E_ERROR | E_PARSE);
    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");
    if (!isset($_GET["user"]))
        exit - 1;

    include(BASEPATH . '/php/GoogleScholar.php');
    $user = $_GET["user"];
    $google = new GoogleScholar($user);
    # create and load the HTML

    if (!isset($_GET['doc'])) {
        $result = $google->getAllUserEntries();
    } else {
        $doc = $_GET["doc"];
        $result = $google->getDocumentDetails($doc);
    }

    echo json_encode($result);
});


Route::get('/api/levenshtein', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    include(BASEPATH . '/php/Levenshtein.php');
    $levenshtein = new Levenshtein($osiris);

    $result = [];

    $title = $_GET['title'];

    if (isset($_GET['pubmed'])) {
        $pubmed = $_GET['pubmed'];
        $test = $osiris->activities->findOne(['pubmed' => $pubmed]);
        if (!empty($test)) {
            $result = [
                'similarity' => 100,
                'id' => strval($test['_id']),
                'title' => $test['title']
            ];
        }
    }
    if (isset($_GET['doi'])) {
        $doi = $_GET['doi'];
        $test = $osiris->activities->findOne(['doi' => $doi]);
        if (!empty($test)) {
            $result = [
                'similarity' => 100,
                'id' => strval($test['_id']),
                'title' => $test['title']
            ];
        }
    }

    $l = $levenshtein->findDuplicate($title);
    $id = $l[0];
    $sim = round($l[2], 1);
    if ($sim < 50) $sim = 0;
    $result = [
        'similarity' => $sim,
        'id' => $id,
        'title' => $levenshtein->found
    ];

    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo json_encode($result);
});


// Infrastructures
Route::get('/api/infrastructures', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    } elseif (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }
    $result = $osiris->infrastructures->find($filter)->toArray();
    echo return_rest($result, count($result));
});

// Organizations
Route::get('/api/organizations', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $options = [
        'projection' => [
            '_id' => 0,
            'id' => ['$toString' => '$_id'],
            'name' => 1,
            'ror' => 1,
            'location' => 1,
        ]
    ];

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    } elseif (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    } elseif (isset($_GET['search'])) {
        $search = trim($_GET['search']);
        $ror = null;
        if (str_starts_with('https://ror.org/', $search)) {
            $ror = $search;
        } else if (preg_match('/^0[a-z|0-9]{6}[0-9]{2}$/', $search)) {
            $ror = 'https://ror.org/' . $search;
        }
        if ($ror) {
            // try finding the organization by ROR first
            $filter = ['ror' => $ror];
            $result = $osiris->organizations->find($filter, $options)->toArray();
            if (count($result) > 0) {
                echo return_rest($result, count($result));
                die;
            }
        }

        $j = new \MongoDB\BSON\Regex(trim($_GET['search']), 'i');
        $filter = ['name' => ['$regex' => $j]];
    }
    $result = $osiris->organizations->find($filter, $options)->toArray();
    echo return_rest($result, count($result));
});
