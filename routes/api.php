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
    // always true if API Key is not set
    if (!isset($APIKEY) || empty($APIKEY)) return true;
    // return true for same page origin
    if (isset($_SERVER['HTTP_SEC_FETCH_SITE']) && $_SERVER['HTTP_SEC_FETCH_SITE'] == 'same-origin') return true;
    // check if API key is valid
    if ($APIKEY == $key) return true;
    // otherwise return false
    return false;
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
    ), JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES);
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
    return json_encode($result, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

Route::get('/api/test', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }


    // check if API key is set and request is not cors
    if (apikey_check($_GET['apikey'] ?? null)) {
        dump($_SERVER, true);
    } else {

        echo return_permission_denied();
        die;
    }
});

/**
 * @api {get} /activities All activities
 * @apiName GetAllActivities
 * @apiGroup Activities
 * 
 * @apiParam {String} [apikey] Your API key, if defined
 * @apiParam {Object} [filter] Filter as valid params request
 * @apiParam {String} [json] Filter string from the advanced search (will overwrite filter)
 * @apiParam {String} [full] If parameter is given, the full database entries are retreived instead of rendered output
 *
 * @apiSampleRequest /api/activities?filter[type]=publication
 * 
 * @apiSuccess {String} id Unique ID of the activity.
 * @apiSuccess {String} activity  Full web formatted activity (with relative links to osiris).
 * @apiSuccess {String} print Formatted activity for export (print) 
 * @apiSuccess {String} icon OSIRIS activity icon
 * @apiSuccess {String} type Type of activity
 * @apiSuccess {String} subtype Subtype of activity
 * @apiSuccess {Int} year Year of activity
 * @apiSuccess {String} authors Formatted authors (affiliated authors are bold)
 * @apiSuccess {String} title Title of the activity
 * @apiSuccess {String[]} departments All associated departments, indicated by their ID
 * @apiSuccessExample {json} Example data:
 *  {
        "id": "6458fcb30d695c593828763f",
        "activity": "<a href='/activities/view/6458fcb30d695c593828763f'>Metabolism from the magic angle</a><br><small class='text-muted d-block'><a href='/osiris/profile/juk20'>Koblitz,&nbsp;J.</a><br> <i>Nature Chemical Biology</i> (2023) <a href='/uploads/6458fcb30d695c593828763f/Metabolism from the magic angle.pdf' target='_blank' data-toggle='tooltip' data-title='pdf: Metabolism from the magic angle.pdf' class='file-link'><i class='ph ph-file ph-file-pdf'></i></a></small>",
        "print": "<b>Koblitz,&nbsp;J.</b> (2023) Metabolism from the magic angle. <i>Nature Chemical Biology</i> <a target='_blank' href=''></a>",
        "icon": "<span data-toggle='tooltip' data-title='Non-refereed'><i class='ph text-publication ph-newspaper'></i></span>",
        "type": "Publikationen",
        "subtype": "Non-refereed",
        "year": 2023,
        "authors": "<b>Koblitz,&nbsp;J.</b>",
        "title": "Metabolism from the magic angle",
        "departments": [
            "BID"
        ]
    }, ...
 */
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
        $result = $osiris->activities->find(
            $filter,
            ['sort' => ['year' => -1]]
        )->toArray();
        echo return_rest($result, count($result));
        die;
    }

    $projection = [
        '_id' => 0,
        'id' => ['$toString' => '$_id'],
        // 'activity' => '$rendered.web',
        // 'print' => '$rendered.print',
        // 'icon' => '$rendered.icon',
        // 'type' => '$rendered.type',
        // 'subtype' => '$rendered.subtype',
        // 'year' => '$year',
        // 'authors' => '$rendered.authors',
        // 'title' => '$rendered.title',
        // 'departments' => '$rendered.depts'
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
            // 'departments' => '$rendered.depts'
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

        $entry = [
            'id' => strval($doc['_id']),
            'html' => $rendered['print'],
            'year' => $doc['year'] ?? null,
            'departments' => $rendered['depts'],
            'link' => $link
        ];
        $result[] = $entry;
    }

    echo return_rest($result, count($result));
});

Route::get('/api/all-activities', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    include_once BASEPATH . "/php/Render.php";
    include_once BASEPATH . "/php/Document.php";

    header("Content-Type: application/json");
    header("Pragma: no-cache");
    header("Expires: 0");

    $user = $_GET['user'] ?? $_SESSION['username'] ?? null;
    $page = $_GET['page'] ?? 'all-activities';
    $highlight = true;
    if ($page == 'my-activities') {
        $highlight = $user;
    }
    // $Format = new Document($highlight);

    $filter = [];
    if (isset($_GET['filter'])) {
        $filter = $_GET['filter'];
    }
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }

    $result = [];
    if ($page == "my-activities") {
        // only own work
        $filter = ['$or' => [['authors.user' => $user], ['editors.user' => $user], ['user' => $user]]];
    }
    if (isset($_GET['type'])) {
        $filter['type'] = $_GET['type'];
    }
    $cursor = $osiris->activities->find($filter);
    $cart = readCart();
    foreach ($cursor as $doc) {
        $id = $doc['_id'];
        if (isset($doc['rendered'])) {
            $rendered = $doc['rendered'];
        } else {
            $rendered = renderActivities(['_id' => $id]);
        }

        // $depts = $Groups->getDeptFromAuthors($doc['authors']??[]);
        $depts = DB::doc2Arr($rendered['depts'] ?? []);

        $type = $doc['type'];
        $format_full = $rendered['print'];
        if (($_GET['display_activities'] ?? 'web') == 'web') {
            $format = $rendered['web'];
        } else {
            $format = $format_full;
        }
        if (isset($_GET['path'])) {
            $format = str_replace(ROOTPATH . "/activities/view", $_GET['path'] . "/activity", $format);
            $format = str_replace(ROOTPATH . "/profile", $_GET['path'] . "/person", $format);
        } else if ($page == 'portal') {
            $format = str_replace(ROOTPATH . "/activities/view", PORTALPATH . "/activity", $format);
            $format = str_replace(ROOTPATH . "/profile", PORTALPATH . "/person", $format);
        }

        $active = false;
        // if (!isset($doc['year'])) {dump($doc, true); die;}
        $sm = intval($doc['month'] ?? 0);
        $sy = intval($doc['year'] ?? 0);
        // die;
        $em = $sm;
        $ey = $sy;

        if (isset($doc['end']) && !empty($doc['end'])) {
            $em = $doc['end']['month'];
            $ey = $doc['end']['year'];
        } elseif (
            (
                ($doc['type'] == 'misc' && ($doc['subtype'] ?? $doc['iteration']) == 'annual') ||
                ($doc['type'] == 'review' && in_array($doc['subtype'] ?? $doc['role'], ['Editor', 'editorial', 'editor']))
            ) && empty($doc['end'])
        ) {
            $em = CURRENTMONTH;
            $ey = CURRENTYEAR;
            $active = true;
        }
        $sq = $sy . 'Q' . ceil($sm / 3);
        $eq = $ey . 'Q' . ceil($em / 3);

        $datum = [
            'quarter' => $sq,
            'icon' => $rendered['icon'] . '<span style="display:none">' . $type . " " . $rendered['type'] . '</span>',
            'activity' => $format,
            'links' => '',
            'search-text' => $format_full,
            'start' => $doc['start_date'] ?? '',
            'end' => $doc['end_date'] ?? '',
            'departments' => $depts, //implode(', ', $depts),
            'epub' => (isset($doc['epub']) && boolval($doc['epub']) ? 'true' : 'false'),
            'type' => $rendered['type'],
            'subtype' => $rendered['subtype'],
            'year' => $doc['year'] ?? 0,
            'authors' => $rendered['authors'] ?? '',
            'title' => $rendered['title'] ?? '',
            'topics' => $doc['topics'] ?? [],
            'raw_type' => $doc['type'],
            'raw_subtype' => $doc['subtype'],

        ];

        if ($active) {
            $datum['quarter'] .= ' - today';
        } elseif ($sq != $eq) {
            if ($sy == $ey) {
                $datum['quarter'] .= ' - ' . 'Q' . ceil($em / 3);
            } else {
                $datum['quarter'] .= ' - ' . $eq;
            }
        }

        if (defined('ROOTPATH')) {
            $datum['links'] =
                "<a class='btn link square' href='" . ROOTPATH . "/activities/view/$id'>
                <i class='ph ph-arrow-fat-line-right'></i>
            </a>";
            // $useractivity = $DB->isUserActivity($doc, $user);
            // if ($useractivity) {
            //     $datum['links'] .= " <a class='btn link square' href='" . ROOTPATH . "/activities/edit/$id'>
            //         <i class='ph ph-pencil-simple-line'></i>
            //     </a>";
            // }
            $datum['links'] .= "<button class='btn link square' onclick='addToCart(this, \"$id\")'>
            <i class='" . (in_array($id, $cart) ? 'ph ph-fill ph-shopping-cart ph-shopping-cart-plus text-success' : 'ph ph-shopping-cart ph-shopping-cart-plus') . "'></i>
        </button>";
        }
        $result[] = $datum;
    }
    echo return_rest($result, count($result));
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


Route::get('/api/conferences', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    include_once BASEPATH . "/php/Document.php";

    $concepts = $osiris->conferences->find(
        [],
        ['sort' => ['start' => -1]]
    )->toArray();

    foreach ($concepts as $i => $row) {
        $concepts[$i]['activities'] = $osiris->activities->count(['conference_id' => strval($row['_id'])]);
        $concepts[$i]['id'] = strval($row['_id']);
    }

    echo return_rest($concepts);
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
    $result = $osiris->persons->find($filter)->toArray();

    if (!isset($_GET['full'])) {
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
            $topics = '';
            if ($user['topics'] ?? false) {
                $topics = '<span class="float-right topic-icons">';
                foreach ($user['topics'] as $topic) {
                    $topics .= '<a href="' . ROOTPATH . '/topics/view/' . $topic . '" class="topic-icon topic-' . $topic . '"></a> ';
                }
                $topics .= '</span>';
            }
            $table[] = [
                'id' => strval($user['_id']),
                'username' => $user['username'],
                'img' => $Settings->printProfilePicture($user['username'], 'profile-img'),
                'html' =>  "<div class='w-full'>
                    <div style='display: none;'>" . $user['first'] . " " . $user['last'] . "</div>
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
                'position' => $user['position'] ?? '',
                'mail' => $user['mail'] ?? '',
                'telephone' => $user['telephone'] ?? '',
                'orcid' => $user['orcid'] ?? '',
                'academic_title' => $user['academic_title'],
                'dept' => $Groups->personDept($user['depts'] ?? [], 1)['id'],
                'active' => ($user['is_active'] ?? true) ? 'yes' : 'no',
                'public_image' => $user['public_image'] ?? true,
                'topics' => $user['topics'] ?? array()
            ];
        }
        $result = $table;
    }
    echo return_rest($result, count($result));
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
        if (!array_key_exists($doc['user'], $reviews)) {
            $u = $DB->getNameFromId($doc['user']);
            $reviews[$doc['user']] = [
                'User' => $doc['user'],
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
                $reviews[$doc['user']]['Editor']++;
                $date = format_date($doc['start'] ?? $doc);
                if (isset($doc['end']) && !empty($doc['end'])) {
                    $date .= " - " . format_date($doc['end']);
                } else {
                    $date .= " - today";
                }

                $reviews[$doc['user']]['Editorials'][] = [
                    'id' => strval($doc['_id']),
                    'date' => $date,
                    'details' => $doc['editor_type'] ?? ''
                ];
                break;

            case 'reviewer':
            case 'review':
                $reviews[$doc['user']]['Reviewer']++;
                $reviews[$doc['user']]['Reviews'][] = [
                    'id' => strval($doc['_id']),
                    'date' => format_date($doc)
                ];
                break;
            default:
                $reviews[$doc['user']]['Reviewer']++;
                $reviews[$doc['user']]['Reviews'][] = [
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


/**
 * @api {get} /projects Get projects based on search criteria
 * @apiName Projects
 * @apiGroup Projects
 * 
 * @apiParam {String} [apikey] Your API key, if defined
 * @apiParam {String} [search] Search string (looked for in name and ID of the Project)
 * @apiParam {Object} [filter] Filter as valid params request
 * @apiParam {String} [json] Filter string from the advanced search (will overwrite filter)  
 *
 * @apiSampleRequest /api/projects?filter[public]=1
 * 
 * @apiSuccess {Object} Full Object containing project data, see example.

 * @apiSuccessExample {json} Example data:
 *  {
    "_id": {
        "$oid": "65c9c42f1e82e991fd06f5d2"
    },
    "name": "OSIRIS",
    "type": "Eigenfinanziert",
    "title": "OSIRIS - das moderne Forschungsinformationssystem",
    "contact": "juk20",
    "status": "applied",
    "funder": "Eigenmittel",
    "funding_organization": "Eigenmittel",
    "funding_number": null,
    "purpose": "others",
    "role": "coordinator",
    "coordinator": null,
    "start": {
        "year": 2023,
        "month": 1,
        "day": 1
    },
    "end": {
        "year": 2025,
        "month": 12,
        "day": 31
    },
    "grant_sum": null,
    "grant_income": null,
    "personal": null,
    "website": 'https://osiris-app.de',
    "abstract": null,
    "created": "2024-02-12",
    "public": true,
    "persons": [
        {
            "user": "juk20",
            "role": "PI",
            "name": "Julia Koblitz"
        }
    ]
}, ...
 */
Route::get('/api/projects', function () {
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
    if (isset($_GET['json'])) {
        $filter = json_decode($_GET['json'], true);
    }
    if (isset($filter['public'])) $filter['public'] = boolval($filter['public']);

    if (isset($_GET['search'])) {
        $j = new \MongoDB\BSON\Regex(trim($_GET['search']), 'i');
        $filter = ['$or' =>  [
            ['title' => ['$regex' => $j]],
            ['id' => $_GET['search']]
        ]];
    }
    $result = $osiris->projects->find($filter)->toArray();

    if (isset($_GET['formatted'])) {
        $data = [];
        include_once BASEPATH . "/php/Project.php";
        $Project = new Project();
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
                'persons' => array_column(DB::doc2Arr($project['persons'] ?? []), 'name')
            ];
        }
        $result = $data;
    }
    echo return_rest($result, count($result));
});

/**
 * @api {get} /journal Find a journal
 * @apiName FindJournal
 * @apiGroup Journals
 * 
 * @apiParam {String} [apikey] Your API key, if defined
 * @apiParam {String} [search] Search string (looked for in name and ISSN of the journal)
 * 
 * @apiSampleRequest /api/journal?search=Systematic
 * 
 * @apiSuccess {String} _id Unique Mongo ID of the journal.
 * @apiSuccess {String} journal  Full name of the journal
 * @apiSuccess {String} abbr Official abbreviation of the journal
 * @apiSuccess {String} publisher Publisher of this journal
 * @apiSuccess {Object[]} impact All known impact factors of the journal, given as an array of objects with year and impact
 * @apiSuccess {String[]} issn All ISSN of the journal
 * @apiSuccess {Boolean|Integer} oa Year of open access start of false
 * @apiSuccessExample {json} Example data:
 *  
    "_id": {
        "$oid": "6364d153f7323cdc8253104a"
    },
    "nlmid": 100899600,
    "journal": "International journal of systematic and evolutionary microbiology",
    "abbr": "Int J Syst Evol Microbiol",
    "publisher": "Microbiology Society",
    "issn": [
        "1466-5034",
        "1466-5026",
        "0020-7713"
    ],
    "impact": [
        {
            "year": 2021,
            "impact": 2.689
        },
        {
            "year": 2020,
            "impact": 2.747
        },
        {
            "year": 2019,
            "impact": 2.415
        }
    ],
    "oa": false,
}, ...
 */
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
    $result = $osiris->journals->find($filter)->toArray();
    echo return_rest($result, count($result));
});

/**
 * @api {get} /journals All journals
 * @apiName GetAllJournals
 * @apiGroup Journals
 * 
 * @apiParam {String} [apikey] Your API key, if defined
 *
 * @apiSampleRequest /api/journals
 * 
 * @apiSuccess {String} id Unique ID of the journal.
 * @apiSuccess {String} name  Full web formatted journal (with relative links to osiris).
 * @apiSuccess {String} abbr Formatted journal for export (print) 
 * @apiSuccess {String} publisher OSIRIS journal icon
 * @apiSuccess {String} open_access 
 * @apiSuccess {String} issn All ISSN of the journal, separated by comma
 * @apiSuccess {Float} if Last year impact factor
 * @apiSuccess {Integer} count Number of activities associated to this journal
 * @apiSuccessExample {json} Example data:
 *  {
      "id": "6389ae62c902176a283535e2",
      "name": "Frontiers in Microbiology",
      "abbr": "Front Microbiol",
      "publisher": "Frontiers Research Foundation",
      "open_access": "seit 2010",
      "issn": "1664-302X",
      "if": 6.064,
      "count": 103
    }, ...
 */
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

    $journals = $osiris->journals->find()->toArray();
    $result = ['data' => []];
    // $i = 0;
    $activities = $osiris->activities->find(['journal_id' => ['$exists' => 1, '$ne' => null]], ['projection' => ['journal_id' => 1]])->toArray();
    $activities = array_column($activities, 'journal_id');
    $activities = array_count_values($activities);
    $no = lang('No', 'Nein');
    $yes = lang('Yes', 'Ja');
    $since = lang('since ', 'seit ');
    foreach ($journals as $doc) {
        if (!isset($doc['oa']) || $doc['oa'] === false) {
            $oa = $no;
        } elseif ($doc['oa'] > 1900) {
            $oa =  $since . $doc['oa'];
        } else {
            $oa =  $yes;
        }
        $result['data'][] = [
            'id' => strval($doc['_id']),
            'name' => $doc['journal'],
            'abbr' => $doc['abbr'],
            'publisher' => $doc['publisher'] ?? '',
            'open_access' => $oa,
            'issn' => $doc['issn'],
            'country' => $doc['country'] ?? '',
            'if' => $DB->latest_impact($doc) ?? '',
            'count' => $activities[strval($doc['_id'])] ?? 0
        ];
    }

    echo json_encode($result);
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


/**
 * @api {get} /levenshtein Search activity by title using the Levenshtein similarity
 * @apiName Levenshtein
 * @apiGroup Activities
 * 
 * @apiParam {String} [apikey] Your API key, if defined
 * @apiParam {String} title The title of the activity you are looking for
 * @apiParam {String} [doi] If available: DOI of activity
 * @apiParam {Integer} [pubmed] If available: Pubmed-ID of activity
 *
 * @apiSampleRequest /api/levenshtein?title=metabolism frm the magic angle
 * 
 * @apiSuccess {Float} similarity The Levenshtein Similarity of the title. Will be 100, if ID matches
 * @apiSuccess {String} id  Unique ID of the found activity
 * @apiSuccess {String} title Title of the found activity
 * @apiSuccessExample {json} Example data:
 *  {
  "similarity": 98.4,
  "id": "6458fcb30d695c593828763f",
  "title": "metabolism from the magic angle"
}
 */
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


// Dashboard interface

Route::get('/api/dashboard/oa-status', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = ['oa_status' => ['$ne' => null]];
    if (isset($_GET['year'])) {
        $filter['year'] = $_GET['year'];
    } else {
        $filter['year'] = ['$gte' => $Settings->get('startyear')];
    }

    $result = array();
    $result = $osiris->activities->aggregate([
        ['$match' => $filter],
        [
            '$group' => [
                '_id' => [
                    'status' => '$oa_status',
                    'year' => '$year'
                ],
                'count' => ['$sum' => 1],
            ]
        ],
        ['$project' => ['_id' => 0, 'status' => '$_id.status', 'year' => '$_id.year', 'count' => 1]],
        ['$sort' => ['year' => 1]],
        [
            '$group' => [
                '_id' => '$status',
                'data' => ['$push' => '$$ROOT']
            ]
        ],
    ])->toArray();
    echo return_rest($result, count($result));
});


Route::get('/api/dashboard/collaborators', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    include(BASEPATH . '/php/Project.php');

    $result = [];
    if (isset($_GET['project'])) {
        $id = $_GET['project'];
        if (DB::is_ObjectID($id)) {
            $mongo_id = $DB->to_ObjectID($id);
            $project = $osiris->projects->findOne(['_id' => $mongo_id]);
        } else {
            $project = $osiris->projects->findOne(['name' => $id]);
            $id = strval($project['_id'] ?? '');
        }
        if (empty($project)) {
            die("Project could not be found.");
        } elseif (empty($project['collaborators'] ?? [])) {
            die("Project has no collaborators");
        } else {
            $P = new Project($project);
            $result = $P->getScope();

            $data = [
                'lon' => [],
                'lat' => [],
                'text' => [],
                'marker' => [
                    'size' => 15,
                    'color' => []
                ]
            ];
            // order by role
            $collabs = DB::doc2Arr($project['collaborators']);
            usort($collabs, function ($a, $b) {
                return $b['role'] <=> $a['role'];
            });
            foreach ($collabs as $c) {
                // if (empty($c['lng']))
                $data['lon'][] = $c['lng'];
                $data['lat'][] = $c['lat'];
                $data['text'][] = "<b>$c[name]</b><br>$c[location]";
                $color = ($c['role'] == 'partner' ? '#008083' : '#f78104');
                $data['marker']['color'][] = $color;
            }
            $institute = $Settings->get('affiliation_details');
            $institute['role'] = $project['role'];
            if (isset($institute['lat']) && isset($institute['lng'])) {

                $data['lon'][] = $institute['lng'];
                $data['lat'][] = $institute['lat'];
                $data['text'][] = "<b>$institute[name]</b><br>$institute[location]";
                $color = ($institute['role'] == 'partner' ? '#008083' : '#f78104');
                $data['marker']['color'][] = $color;
            }

            $result['collaborators'] = $data;
        }
    } else {
        $filter = ['collaborators' => ['$exists' => 1]];
        if (isset($_GET['dept'])) {
            // only for portal
            $dept = $_GET['dept'];

            $child_ids = $Groups->getChildren($dept);
            $persons = $osiris->persons->find(['depts' => ['$in' => $child_ids], 'is_active' => ['$ne'=>false]], ['sort' => ['last' => 1]])->toArray();
            $users = array_column($persons, 'username');
            $filter = [
                'persons.user' => ['$in' => $users],
                "public" => true,
                "status" => ['$ne' => "rejected"],
                'collaborators' => ['$exists' => 1]
            ];
        }
        $result = $osiris->projects->aggregate([
            ['$match' => $filter],
            ['$project' => ['collaborators' => 1]],
            ['$unwind' => '$collaborators'],
            [
                '$group' => [
                    '_id' => '$collaborators.name',
                    'count' => ['$sum' => 1],
                    'data' => [
                        '$first' => '$collaborators'
                    ]
                ]
            ],
        ])->toArray();

        $institute = $Settings->get('affiliation_details');
        if (isset($institute['lat']) && isset($institute['lng'])) {
            $result[] = [
                '_id' => $institute['ror'] ?? '',
                'count' => 3,
                'data' => $institute,
                'color' => '#f78104'
            ];
        }
    }




    echo return_rest($result, count($result));
});


Route::get('/api/dashboard/author-role', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $result = array(
        'labels' => [],
        'y' => [],
        'colors' => []
    );

    $filter = ['year' => ['$gte' => $Settings->get('startyear')], 'type' => 'publication'];
    if (isset($_GET['user'])) {
        $user = $_GET['user'];
        $filter['authors.user'] = $user;

        $data = $osiris->activities->aggregate([
            ['$match' => $filter],
            ['$project' => ['authors' => 1]],
            ['$unwind' => '$authors'],
            ['$match' => ['authors.user' => $user, 'authors.aoi' => true]],
            [
                '$group' => [
                    '_id' => '$authors.position',
                    'count' => ['$sum' => 1],
                ]
            ],
            ['$sort' => ['count' => -1]],
            ['$project' => ['_id' => 0, 'x' => '$_id', 'y' => '$count']],
        ])->toArray();

        $editorials = $osiris->activities->count(['editors.user' => $user]);
        if ($editorials !== 0)
            $data[] = [
                'x' => 'editor',
                'y' => $editorials
            ];
    }

    foreach ($data as $el) {
        switch ($el['x']) {
            case 'first':
                $label = lang("First author", "Erstautor");
                $color = '#006EB799';
                break;
            case 'last':
                $label = lang("Last author", "Letztautor");
                $color = '#004d8099';
                break;
            case 'middle':
                $label = lang("Middle author", "Mittelautor");
                $color = '#cce2f099';
                break;
            case 'editor':
                $label = lang("Editorship", "Editorenschaft");
                $color = '#002c4999';
                break;
            case 'corresponding':
                $label = lang("Corresponding", "Korrespondierender Autor");
                $color = '#4c99cc99';
                break;
            default:
                $label = $el['x'];
                $color = '#ffffff';
                break;
        }
        $result['labels'][] = $label;
        $result['y'][] = $el['y'];
        $result['colors'][] = $color;
    }

    echo return_rest($result, count($result));
});



Route::get('/api/dashboard/impact-factor-hist', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = ['year' => ['$gte' => $Settings->get('startyear')], 'impact' => ['$ne' => null]];
    if (isset($_GET['user'])) {
        $filter['authors.user'] = $_GET['user'];
    }
    $max = $osiris->activities->find(
        $filter,
        ['sort' => ['impact' => -1], 'limit' => 1, 'projection' => ['impact' => 1]]
    )->toArray();

    if (empty($max)) {
        echo return_rest([], 0);
        die;
    }
    $max_impact = ceil($max[0]['impact']);
    $x = [];
    for ($i = 1; $i <= $max_impact; $i++) {
        $x[] = $i;
    }

    $data = $osiris->activities->aggregate([
        ['$match' => $filter],
        ['$project' => ['_id' => 0, 'impact' => 1]],
        ['$bucket' => [
            'groupBy' => '$impact',
            'boundaries' => $x,
            'default' => 0
        ]],
        ['$project' => ['_id' => 0, 'x' => '$_id', 'y' => '$count']],
    ])->toArray();

    array_unshift($x, 0);

    $result = [
        'x' => $x,
        'y' => array_fill(0, $max_impact + 1, 0),
        'labels' => $x,
    ];
    foreach ($data as $i => $datum) {
        $result['y'][$datum['x']] = $datum['y'];
    }

    echo return_rest($result, count($result));
});



Route::get('/api/dashboard/activity-chart', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = ['year' => ['$gte' => $Settings->get('startyear')]];
    if (isset($_GET['user'])) {
        $filter['authors.user'] = $_GET['user'];
    }

    $result = [];
    $years = [];
    for ($i = $Settings->get('startyear'); $i <= CURRENTYEAR; $i++) {
        $years[] = strval($i);
    }
    $result['labels'] = $years;
    $data = $osiris->activities->aggregate([
        ['$match' => $filter],
        [
            '$group' => [
                '_id' => [
                    'type' => '$type',
                    'year' => '$year'
                ],
                'count' => ['$sum' => 1],
            ]
        ],
        ['$project' => ['_id' => 0, 'type' => '$_id.type', 'year' => '$_id.year', 'count' => 1]],
        ['$sort' => ['year' => 1]],
        [
            '$group' => [
                '_id' => '$type',
                'x' => ['$push' => '$year'],
                'y' => ['$push' => '$count'],

            ]
        ],

        // ['$project' => ['_id' => 0, 'data'=>['$arrayToObject' => ['$literal' =>  [
        //     '$x', '$y'
        // ]]]]],
    ])->toArray();

    // dump($data);

    $result['data'] = [];
    foreach ($data as $d) {
        $group = $Settings->getActivities($d['_id']);
        $element = [
            'label' => $group['name'],
            'backgroundColor' => $group['color'] . '95',
            'borderColor' => '#464646',
            'borderWidth' => 1,
            'borderRadius' => 4,
            'data' => [],
        ];
        foreach ($years as $y) {
            $i = array_search($y, DB::doc2Arr($d['x']));
            if ($i === false) $v = 0;
            else $v = $d['y'][$i];

            $element['data'][] = $v;
        }
        $result['data'][] = $element;
    }

    echo return_rest($result, count($result));
});


Route::get('/api/dashboard/project-timeline', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $filter = ['status' => ['$in' => ['approved', 'finished']]];
    if (isset($_GET['user'])) {
        $filter['persons.user'] = $_GET['user'];
    }

    $result = $osiris->projects->aggregate([
        ['$match' => $filter],
        ['$unwind' => '$persons'],
        ['$match' => $filter],
        ['$sort' => ['start' => 1]]
    ])->toArray();
    echo return_rest($result, count($result));
});


Route::get('/api/dashboard/wordcloud', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    function mb_preg_match_all($ps_pattern, $ps_subject, &$pa_matches, $pn_flags = PREG_PATTERN_ORDER, $pn_offset = 0, $ps_encoding = NULL)
    {
        // WARNING! - All this function does is to correct offsets, nothing else:
        //
        if (is_null($ps_encoding))
            $ps_encoding = mb_internal_encoding();

        $pn_offset = strlen(mb_substr($ps_subject, 0, $pn_offset, $ps_encoding));
        $ret = preg_match_all($ps_pattern, $ps_subject, $pa_matches, $pn_flags, $pn_offset);

        if ($ret && ($pn_flags & PREG_OFFSET_CAPTURE))
            foreach ($pa_matches as &$ha_match)
                foreach ($ha_match as &$ha_match)
                    $ha_match[1] = mb_strlen(substr($ps_subject, 0, $ha_match[1]), $ps_encoding);
        //
        // (code is independent of PREG_PATTER_ORDER / PREG_SET_ORDER)

        return $ret;
    }

    $filter = ['status' => 'approved'];
    if (isset($_GET['user'])) {
        $filter['persons.user'] = $_GET['user'];
    }

    $result = $osiris->activities->find(
        [
            'authors.user' => $_GET['user'] ?? $_SESSION['username'] ?? '',
            // 'type' => ['$in' => ['publication', 'poster', 'lecture']]
            'type' => 'publication'
        ],
        ['projection' => ['title' => 1, 'abstract' => 1, '_id' => 0]]
        // ['$unwind' => '$persons'],
        // ['$match' => $filter],
        // ['$sort' => ['start' => 1]]
    )->toArray();

    $text = "";
    foreach ($result as $a) {

        if (isset($a['title']) && is_string($a['title']))
            $text .= " " . $a['title'];
        if (isset($a['abstract']) && is_string($a['abstract']))
            $text .= " " . $a['abstract'];
    }
    $text = strip_tags($text);
    $pattern = "~\b\w+\b~u";
    mb_preg_match_all($pattern, $text, $words_raw);

    $words = [];
    include_once BASEPATH . "/php/stopwords.php";
    foreach ($words_raw[0] as $word) {
        if (in_array(strtolower($word), $stopwords) || is_numeric($word) || strlen($word) < 2) continue;
        $words[] = strtolower($word);
    }
    $words = array_count_values($words);
    arsort($words);
    echo return_rest(array_slice($words, 0, 300), count($result));
});


// helper function for network chord chards
function combinations($array)
{
    $results = array();
    foreach ($array as $a)
        foreach ($array as $b) {
            $t = [$a, $b];
            sort($t);
            if ($a == $b || in_array($t, $results)) continue;
            $results[] = $t;
        }
    return $results;
}

Route::get('/api/dashboard/department-network', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $dept_filter = $_GET['dept'] ?? null;
    $lvl = 1;
    if (isset($_GET['level'])) $lvl = intval($_GET['level']);
    if (!empty($dept_filter)) $lvl = $Groups->getLevel($dept_filter);

    $departments = array_filter($Groups->groups, function ($a) use ($lvl) {
        return true;
        // return ($a['level'] ?? '') == $lvl;
    });

    $dept_users = [];
    foreach (array_column($departments, 'id') as $id) {
        $dept_users[$id] = [];
    }
    $users = [];
    $warnings = [];
    foreach ($osiris->persons->find() as $person) {
        if (!isset($person['depts'])) continue;
        $d = [];
        foreach ($person['depts'] as $key) {
            // get parent dept
            $p = $Groups->getParents($key, true);
            if (!isset($p[$lvl])) $p = end($p);
            else $p = $p[$lvl];
            if (!in_array($p, $d)) {
                if (!empty($d)) $warnings[] =  $person['displayname'] . ' has multiple associations.';
                $d[] = $p;
                $dept_users[$p][] = $person['username'];
                $users[$person['username']] = $p;
            }
        }
    }


    // select activities from database
    $filter = [];
    if (isset($_GET['type']))
        $filter['type'] = $_GET['type'];
    if (!empty($dept_filter)) {
        $filter['authors.user'] = ['$in' => $dept_users[$dept_filter] ?? []];
    }
    if (isset($_GET['year'])) {
        $filter['year'] = $_GET['year'];
    } else {
        // past 5 years is default
        $filter['year'] = ['$gte' => CURRENTYEAR - 4];
    }
    if (isset($_GET['activity'])) {
        //overwrite
        $filter = ['_id' => DB::to_ObjectID($_GET['activity'])];
    }
    $activities = $osiris->activities->find($filter);
    $activities = $activities->toArray();


    // generate graph json
    $combinations = [];

    $labels = $departments;
    foreach ($departments as $dept) {
        $labels[$dept['id']]['count'] = 0;
    }

    foreach ($activities as $doc) {
        $authors = [];
        foreach ($doc['authors'] as $aut) {
            if (!($aut['aoi'] ?? false) || empty($aut['user']) || !array_key_exists($aut['user'], $users)) continue;

            $id = $aut['user'];

            // get top level unit
            $dept = $users[$id];

            if (!empty($dept) && !in_array($dept, $authors)) {
                if (!isset($labels[$dept])) {
                    $labels[$dept] = $Groups->getGroup($dept);
                    $labels[$dept]['count'] = 0;
                }
                $labels[$dept]['count']++;
                $authors[] = $dept;
            }
        }
        if (count($authors) == 1)
            $combinations = array_merge($combinations, [[$authors[0], $authors[0]]]);
        else
            $combinations = array_merge($combinations, combinations($authors));
    }

    // remove depts without publications
    $labels = array_filter($labels, function ($d) {
        return $d['count'] !== 0;
    });

    // add index (needed for following steps)
    $i = 0;
    foreach ($labels as $key => $val) {
        $labels[$key]['index'] = $i++;
    }

    // init matrix of n x n
    $matrix = array_fill(0, count($labels), 0);
    $matrix = array_fill(0, count($labels), $matrix);

    // fill matrix based on all combinations
    foreach ($combinations as $c) {
        $a = $labels[$c[0]]['index'];
        $b = $labels[$c[1]]['index'];

        $matrix[$a][$b] += 1;
        if ($a != $b)
            $matrix[$b][$a] += 1;
    }

    echo return_rest([
        'matrix' => $matrix,
        'labels' => $labels,
        'warnings' => $warnings
    ], count($labels));
});



Route::get('/api/dashboard/author-network', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $scientist = $_GET['user'] ?? '';
    $selectedUser = $osiris->persons->findone(['username' => $scientist]);
    // generate graph json
    $labels = [];
    $combinations = [];
    $filter = ['authors.user' => $scientist, 'type' => 'publication'];

    if (isset($_GET['year'])) {
        $filter['year'] = $_GET['year'];
    } else {
        // past 5 years is default
        $filter['year'] = ['$gte' => CURRENTYEAR - 4];
    }

    $activities = $osiris->activities->find($filter);
    $activities = $activities->toArray();
    $N = count($activities);

    foreach ($activities as $doc) {
        $authors = [];
        foreach ($doc['authors'] as $aut) {
            if (empty($aut['user'])) continue;
            //!($aut['aoi'] ?? false) || 

            $id = $aut['user'];
            if (array_key_exists($id, $labels)) {
                // $name = $labels[$id]['name'];
                $labels[$id]['count']++;
            } else {
                $name = $osiris->persons->findone(['username' => $aut['user']]);
                if (empty($name)) continue;
                $abbr_name = Document::abbreviateAuthor($name['last'], $name['first'], true, ' ');

                // get top level unit
                $dept = [];
                if (!empty($name['depts'] ?? []) && count($name['depts']) !== 0) {
                    $d = $Groups->getParents($name['depts'][0]);
                    $dept = $Groups->getGroup($d[0]);
                }

                $labels[$id] = [
                    'name' => $abbr_name,
                    'id' => $id,
                    'user' => $aut['user'],
                    'dept' => $dept,
                    'count' => 1
                ];
            }
            $authors[] = $id;
        }

        $combinations = array_merge($combinations, combinations($authors));
    }
    $departments = array_filter($Groups->groups, function ($a) {
        return ($a['level'] ?? '') == 1;
    });
    $depts = array_column($departments, 'id');
    usort($depts, function ($a, $b) use ($selectedUser) {
        if (in_array($a, DB::doc2Arr($selectedUser['depts']))) return -1;
        return 1;
    });
    // $labels = array_filter($labels, function ($a) {
    //     return !empty($a['dept']);
    // });
    uasort($labels, function ($a, $b) use ($depts) {
        $a = array_search($a['dept']['id'] ?? '', $depts);
        $b = array_search($b['dept']['id'] ?? '', $depts);
        if ($b === false) return -1;
        if ($a === false) return 1;
        return ($a < $b ? -1 : 1);
    });

    $i = 0;
    foreach ($labels as $key => $val) {
        $labels[$key]['index'] = $i++;
    }

    $matrix = array_fill(0, count($labels), 0);
    $matrix = array_fill(0, count($labels), $matrix);

    foreach ($combinations as $c) {
        $a = $labels[$c[0]]['index'];
        $b = $labels[$c[1]]['index'];

        $matrix[$a][$b] += 1;
        $matrix[$b][$a] += 1;
    }


    echo return_rest([
        'matrix' => $matrix,
        'labels' => $labels
    ], count($labels));
});


Route::get('/api/dashboard/activity-authors', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    if (!isset($_GET['activity'])) return [];

    $lvl = 1;

    // select activities from database
    $filter = ['_id' => DB::to_ObjectID($_GET['activity'])];
    $doc = $osiris->activities->findOne($filter);

    $depts = [];

    if (isset($doc['authors']) && !empty($doc['authors'])) {
        // $users = array_column(DB::doc2Arr($doc['authors']), 'user');
        foreach ($doc['authors'] as $a) {
            $user = $a['user'] ?? null;
            $name = Document::abbreviateAuthor($a['last'], $a['first'] ?? null);
            if (empty($user)) {
                $depts['external'][] = $name;
                continue;
            }

            // get person group
            $person = $osiris->persons->findOne(['username' => $user]);
            if (!isset($person['depts'])) continue;
            $d = [];
            foreach ($person['depts'] as $key) {
                // get parent dept
                $p = $Groups->getParents($key, true);
                if (!isset($p[$lvl])) $p = end($p);
                else $p = $p[$lvl];
                if (!in_array($p, $d)) {
                    if (!empty($d)) $warnings[] =  $person['displayname'] . ' has multiple associations.';
                    $d[] = $p;
                    $dept_users[$p][] = $person['username'];
                    $users[$person['username']] = $p;
                }
            }
            $depts[$d[0]][] = $name;
        }
    }

    // $depts = array_count_values($depts);

    $labels = [];
    $y = [];
    $colors = [];
    $persons = [];
    foreach ($depts as $key => $value) {
        if ($key == 'external') {
            $labels[] = 'External partners';
            $colors[] = '#00000095';
        } else {
            $group = $Groups->getGroup($key);
            $labels[] = $group['name'];
            $colors[] = $group['color'] . '95';
        }
        $y[] = count($value);
        $persons[] = $value;
    }
    echo return_rest([
        'y' => $y,
        'colors' => $colors,
        'labels' => $labels,
        'persons' => $persons
    ], count($labels));
});


Route::get('/api/dashboard/department-graph', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }
    $group = $Groups->getGroup($_GET['dept']);
    $children = $Groups->getChildren($group['id']);
    $persons = $osiris->persons->find(['depts' => ['$in' => $children], 'is_active' => ['$ne'=>false]], ['sort' => ['last' => 1]])->toArray();
    $users = array_column($persons, 'username');
    $nodes = [];
    $links = [];
    $linklist = [];
    $node_users = array_column($persons, 'username');

    function getNode($p)
    {
        global $Groups;
        // $user = $p['username'] ?? null;
        $name = ($p['first_abbr'] ?? $p['first'][0] . ".") . ' ' . $p['last'];
        $color = '#000000';
        if (isset($p['depts'])) {
            $D = $p['depts'];
            foreach ($D as $d) {
                $c = $Groups->getGroup($d)['color'];
                if (isset($c) && $c != '#000000') {
                    $color = $c;
                    break;
                }
            }
        }

        return [
            'id' => $p['username'],
            'name' => $name,
            'group' => 1,
            'color' => $color,
            'value' => 0,
        ];
    }
    foreach ($persons as $p) {
        if (empty($p['username'])) {
            continue;
        }

        $node = getNode($p);
        // get all activities the person has with other authors and aggregate by username
        $activities = $osiris->activities->aggregate([
            ['$match' => [
                'authors' => [
                    '$elemMatch' => [
                        'user' => $p['username'],
                        'aoi' => ['$in' => ['true', true, 1]]
                    ]
                ],
                // 'authors.user' => ['$in' => $users],
                'type' => 'publication'
            ]],
            ['$unwind' => '$authors'],
            ['$match' => [
                'authors.user' => ['$in' => $users],
                // 'authors.user' => ['$ne' => null],
                'authors.aoi' => ['$in' => ['true', true, 1]]
            ]],
            ['$group' => [
                '_id' => '$authors.user',
                'count' => ['$sum' => 1]
            ]]
        ])->toArray();
        // dump($activities, true);

        if (empty($activities)) {
            continue;
        }

        foreach ($activities as $a) {
            if (empty($a['_id'])) continue;
            $user = $a['_id'] ?? null;
            if ($user == $p['username']) {
                $node['value'] = $a['count'];
                continue;
            }
            if (in_array($user, $linklist)) {
                continue;
            }
            // add other users
            if (!in_array($user, $node_users)) {
                $p2 = $DB->getPerson($user);
                if (empty($p2)) {
                    // dump($user, true);
                    continue;
                }
                $n = getNode(DB::doc2Arr($p2));
                $n['group'] = 2;
                $nodes[] = $n;
                $node_users[] = $user;
            }

            if (in_array($user, $node_users))
                $links[] = [
                    'source' => $p['username'],
                    'target' => $user,
                    'value' => $a['count']
                ];
        }

        $nodes[] = $node;
        $linklist[] = $p['username'];
    }
    echo return_rest([
        'nodes' => $nodes,
        'links' => $links
    ], count($nodes));
});

Route::get('/api/dashboard/concept-search', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    if (!isset($_GET['concept'])) return return_rest([], 0);
    $name = $_GET['concept'];
    $active_users = $osiris->persons->distinct('username', ['is_active' => ['$ne'=>false]]);
    $concepts = $osiris->activities->aggregate(
        [
            ['$match' => ['concepts.display_name' => $name]],
            ['$project' => ['authors' => 1, 'concepts' => 1]],
            ['$unwind' => '$concepts'],
            ['$match' => ['concepts.display_name' => $name]],
            ['$unwind' => '$authors'],
            ['$match' => ['authors.user' => ['$in' => $active_users]]],
            [
                '$group' => [
                    '_id' => '$authors.user',
                    'total' => ['$sum' => 1],
                    'totalScore' => ['$sum' => '$concepts.score'],
                    'author' => ['$first' => '$authors']
                ]
            ],
            // ['$project' => ['score' => ['$divide' =>], 'concepts' => 1]],
            ['$match' => ['totalScore' => ['$gte' => 1]]],
            ['$sort' => ['author.last' => 1]],
        ]
    )->toArray();

    // $data = [];
    $data = [
        "x" => [],
        "y" => [],
        "mode" => 'markers',
        "marker" => [
            "size" => [],
            "sizemode" => 'area',
            'showlegend' => true
        ],
        'text' => [],
        'hovertemplate' => '%{x}<br>%{y}<br> Total Score: %{text}'
    ];
    foreach ($concepts as $i => $c) {
        // $author = Document::abbreviateAuthor($c['author']['last'], $c['author']['first'], true, ' ');
        $author = $DB->getNameFromId($c['_id'], true, true);
        // $data[] = [
        //     "x" => $name,
        //     "y" => $author,
        //     "r" => $c['totalScore']
        // ];
        $data['y'][] = $name;
        $data['x'][] = $author;
        $s = round($c['totalScore'], 1);
        $data['text'][] = "$s<br>$c[total] activities";
        $data['marker']['size'][] = $c['totalScore'] * 10;
    }

    echo return_rest($data, count($data['x']));
});

Route::get('/api/groups', function () {
    error_reporting(E_ERROR | E_PARSE);
    include(BASEPATH . '/php/init.php');

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $data = $osiris->groups->find()->toArray();

    echo return_rest($data, count($data));
});



Route::get('/api/activities-suggest/(.*)', function ($term) {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    // if (!apikey_check($_GET['apikey'] ?? null)) {
    //     echo return_permission_denied();
    //     die;
    // }

    $filter = ['$text' => ['$search' => $term]];

    // exclude project id
    if (isset($_GET['exclude-project'])) {
        $exclude = DB::doc2Arr($_GET['exclude-project']);
        $filter['projects'] = ['$ne' => $exclude];
    }

    if (isset($_GET['user'])) {
        $filter['authors.user'] = $_GET['user'];
    }
    // TODO: add filter for department
    // if (isset($_GET['unit'])) {
    //     $filter['depts'] = $_GET['unit'];
    // }

    // $osiris->activities->createIndex(['rendered.plain' => 'text']);

    $result = $osiris->activities->find(
        $filter,
        [
            'projection' => ['score' => ['$meta' => 'textScore'], 'details' => '$rendered', 'id' => ['$toString' => '$_id']],
            'sort' => ['score' => ['$meta' => 'textScore']],
            'limit' => 10
        ]
    )->toArray();


    echo return_rest($result, count($result));
});

// Groups->tree 
Route::get('/api/groups/tree', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    if (!apikey_check($_GET['apikey'] ?? null)) {
        echo return_permission_denied();
        die;
    }

    $tree = $Groups->tree;
    echo return_rest($tree, count($tree));
});

// events
Route::get('/api/calendar', function () {
    error_reporting(E_ERROR | E_PARSE);
    include_once BASEPATH . "/php/init.php";

    // if (!apikey_check($_GET['apikey'] ?? null)) {
    //     echo return_permission_denied();
    //     die;
    // }

    $filter = [];
    if (isset($_GET['start']) && isset($_GET['end'])) {
        $start = $_GET['start'];
        $end = $_GET['end'];
        $filter = [
            '$or' => [
                ['start' => ['$gte' => $start, '$lte' => $end]],
                ['end' => ['$gte' => $start, '$lte' => $end]],
                ['$and' => [['start' => ['$lte' => $start]], ['end' => ['$gte' => $end]]]]
            ]
        ];
    }

    // options for FullCalendar
    // conferences
    $events = $osiris->conferences->find($filter, [
        'projection' => ['start' => 1, 'end' => 1, 'title' => 1, 'id' => ['$toString' => '$_id'], 'type' => 'event']
    ])->toArray();

    // activities $filter = [];
    if (isset($_GET['start']) && isset($_GET['end'])) {
        $start = $_GET['start'];
        $end = $_GET['end'];
        $filter = [
            'authors.user' => $_GET['user'] ?? $_SESSION['username'] ?? '',
            '$or' => [
                ['start_date' => ['$gte' => $start, '$lte' => $end]],
                ['end_date' => ['$gte' => $start, '$lte' => $end]],
                ['$and' => [['start_date' => ['$lte' => $start]], ['end_date' => ['$gte' => $end]]]]
            ]
        ];
    }

    $activities = $osiris->activities->find($filter, [
        'projection' => ['start' => '$start_date', 'end' => '$end_date', 'title' => 1, 'id' => ['$toString' => '$_id'], 'type' => 'activity']
    ])->toArray();
   
    $events = array_merge($events, $activities);

    // projects
    $filter = [
        'persons.user' => $_GET['user'] ?? $_SESSION['username'] ?? '',
        '$or' => [
            ['start_date' => ['$gte' => $start, '$lte' => $end]],
            ['end_date' => ['$gte' => $start, '$lte' => $end]],
            ['$and' => [['start_date' => ['$lte' => $start]], ['end_date' => ['$gte' => $end]]]]
        ]
    ];

    // $projects = $osiris->projects->find($filter, [
    //     'projection' => ['start' => '$start_date', 'end' => '$end_date', 'title' => 1, 'id' => ['$toString' => '$_id'], 'type' => 'project']
    // ])->toArray();
    // $events = array_merge($events, $projects);

    // // guests
    // $filter = [
    //     // 'guests.user' => $_GET['user'] ?? $_SESSION['username'] ?? '',
    //     '$or' => [
    //         ['start_date' => ['$gte' => $start, '$lte' => $end]],
    //         ['end_date' => ['$gte' => $start, '$lte' => $end]],
    //         ['$and' => [['start_date' => ['$lte' => $start]], ['end_date' => ['$gte' => $end]]]]
    //     ]
    // ];

    // $guests = $osiris->guests->find($filter, [
    //     'projection' => ['start' => '$start_date', 'end' => '$end_date', 'title' => 1, 'id' => 1, 'type' => 'guest']
    // ])->toArray();
    // $events = array_merge($events, $guests);

    echo return_rest($events, count($events));
});