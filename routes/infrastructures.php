<?php

/**
 * Routing file for research infrastructures
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/infrastructures', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => $Settings->infrastructureLabel()]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/infrastructures/statistics', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => $Settings->infrastructureLabel(), 'path' => "/infrastructures"],
        ['name' => lang("Statistics", "Statistiken")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/statistics.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/infrastructures/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    if (!$Settings->hasPermission('infrastructures.edit')) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=no-permission");
        die;
    }

    $breadcrumb = [
        ['name' => $Settings->infrastructureLabel(), 'path' => "/infrastructures"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/infrastructures/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Infrastructure.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $osiris_id = $DB->to_ObjectID($id);
        $infrastructure = $osiris->infrastructures->findOne(['_id' => $osiris_id]);
    } else {
        $infrastructure = $osiris->infrastructures->findOne(['id' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($infrastructure)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => $Settings->infrastructureLabel(), 'path' => "/infrastructures"],
        ['name' => $infrastructure['name']]
    ];

    $Infra = new Infrastructure();

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/infrastructures/edit/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (!$Settings->hasPermission('infrastructures.edit') && !$Settings->hasPermission('infrastructures.edit-own')) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
        die;
    }
    global $form;

    if (DB::is_ObjectID($id)) {
        $osiris_id = $DB->to_ObjectID($id);
        $form = $osiris->infrastructures->findOne(['_id' => $osiris_id]);
    } else {
        $form = $osiris->infrastructures->findOne(['name' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    // check if user is allowed to edit the infrastructure
    if (!$Settings->hasPermission('infrastructures.edit') && $Settings->hasPermission('infrastructures.edit-own')) {
        $permission = false;
        foreach ($form['persons'] ?? [] as $person) {
            if ($person['user'] == $_SESSION['username']) {
                $permission = true;
                break;
            }
        }
        if (!$permission) {
            header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
            die;
        }
    }
    $breadcrumb = [
        ['name' => $Settings->infrastructureLabel(), 'path' => "/infrastructures"],
        ['name' => $form['name'], 'path' => "/infrastructures/view/$id"],
        ['name' => lang("Edit", "Bearbeiten")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/infrastructures/persons/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (!$Settings->hasPermission('infrastructures.edit') && !$Settings->hasPermission('infrastructures.edit-own')) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
        die;
    }

    global $form;
    if (DB::is_ObjectID($id)) {
        $osiris_id = $DB->to_ObjectID($id);
        $form = $osiris->infrastructures->findOne(['_id' => $osiris_id]);
    } else {
        $form = $osiris->infrastructures->findOne(['name' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    if (!$Settings->hasPermission('infrastructures.edit') && $Settings->hasPermission('infrastructures.edit-own')) {
        $permission = false;
        foreach ($form['persons'] ?? [] as $person) {
            if ($person['user'] == $_SESSION['username']) {
                $permission = true;
                break;
            }
        }
        if (!$permission) {
            header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
            die;
        }
    }
    $breadcrumb = [
        ['name' => $Settings->infrastructureLabel(), 'path' => "/infrastructures"],
        ['name' => $form['name'], 'path' => "/infrastructures/view/$id"],
        ['name' => lang("Persons", "Personen")]
    ];

    include_once BASEPATH . "/php/Infrastructure.php";
    $Infra = new Infrastructure();

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/persons.php";
    include BASEPATH . "/footer.php";
}, 'login');


// Route::get('/infrastructures/year/(.*)', function ($id) {
//     include_once BASEPATH . "/php/init.php";
//     $user = $_SESSION['username'];

//     global $form;

//     if (DB::is_ObjectID($id)) {
//         $osiris_id = $DB->to_ObjectID($id);
//         $form = $osiris->infrastructures->findOne(['_id' => $osiris_id]);
//     } else {
//         $form = $osiris->infrastructures->findOne(['name' => $id]);
//         $id = strval($infrastructure['_id'] ?? '');
//     }
//     if (empty($form)) {
//         header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
//         die;
//     }
//     if (!$Settings->hasPermission('infrastructures.edit') && !$Settings->hasPermission('infrastructures.statistics')) {
//         // check if person is part of the infrastructure and is set as reporter
//         $permission = false;
//         foreach ($form['persons'] ?? [] as $person) {
//             if ($person['user'] == $_SESSION['username'] && (($person['reporter'] ?? false) || $Settings->hasPermission('infrastructures.edit-own'))) {
//                 $permission = true;
//                 break;
//             }
//         }
//         if (!$permission) {
//             header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
//             die;
//         }
//     }

//     $breadcrumb = [
//         ['name' => $Settings->infrastructureLabel(), 'path' => "/infrastructures"],
//         ['name' => $form['name'], 'path' => "/infrastructures/view/$id"],
//         ['name' => lang("Year Statistics", "Jahresstatistik")]
//     ];

//     include BASEPATH . "/header.php";
//     include BASEPATH . "/pages/infrastructures/year.php";
//     include BASEPATH . "/footer.php";
// }, 'login');


/**
 * CRUD routes
 */

Route::post('/crud/infrastructures/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('infrastructures.edit')) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=no-permission");
        die;
    }

    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->infrastructures;

    $values = validateValues($_POST['values'], $DB);

    $id = $values['id'] ?? uniqid();

    // check if infrastructure id already exists:
    $infrastructure_exist = $collection->findOne(['id' => $id]);
    if (!empty($infrastructure_exist)) {
        header("Location: " . $red . "?msg=infrastructure ID does already exist.");
        die();
    }
    // dump($values, true);

    // format collaborators
    if (isset($values['collaborative'])) {
        $values['collaborative'] = $values['collaborative'] == 'yes' ? true : false;
        if (isset($values['collaborators'])) {

            $values['coordinator_organization'] = null;
            if (DB::is_ObjectID($values['coordinator'] ?? null)) {
                $values['coordinator_organization'] = DB::to_ObjectID($values['coordinator']);
                $values['coordinator_institute'] = false;
            } else {
                $values['coordinator_institute'] = true;
            }
        }
        $values['collaborators'] = array_map('DB::to_ObjectID', $values['collaborators'] ?? []);
    }
    // dump($values, true);
    // die;

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    $insertOneResult  = $collection->insertOne($values);
    $id = $insertOneResult->getInsertedId();

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $id, $_POST['redirect']);
        header("Location: " . $red . "?msg=success");
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});


Route::post('/crud/infrastructures/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('infrastructures.edit')) {
        $permission = false;
        if ($Settings->hasPermission('infrastructures.edit-own')) {
            // check if person is part of the infrastructure and is set as reporter
            $infrastructure = $osiris->infrastructures->findOne(['_id' => $DB->to_ObjectID($id)]);
            foreach (($infrastructure['persons'] ?? []) as $person) {
                if ($person['user'] == $_SESSION['username']) {
                    $permission = true;
                    break;
                }
            }
        }
        if (!$permission) {
            header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
            die;
        }
    }
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->infrastructures;

    $values = validateValues($_POST['values'], $DB);
    if (isset($values['collaborative'])) {
        $values['collaborative'] = $values['collaborative'] == 'yes' ? true : false;
        if (isset($values['collaborators'])) {
            $values['coordinator_organization'] = null;
            if (DB::is_ObjectID($values['coordinator'] ?? null)) {
                $values['coordinator_organization'] = DB::to_ObjectID($values['coordinator']);
                $values['coordinator_institute'] = false;
            } else {
                $values['coordinator_institute'] = true;
            }
        }
        $values['collaborators'] = array_map('DB::to_ObjectID', $values['collaborators'] ?? []);
        unset($values['coordinator']);
    }

    // add information on creating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    $id = $DB->to_ObjectID($id);
    $updateResult = $collection->updateOne(
        ['_id' => $id],
        ['$set' => $values]
    );

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect'] . "?msg=update-success");
        die();
    }

    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
});


Route::post('/crud/infrastructures/stats/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    // get infrastructure
    $infrastructure = $osiris->infrastructures->findOne(['_id' => $DB->to_ObjectID($id)]);
    if (empty($infrastructure)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    if (!isset($_POST['values'])) die("no values given");

    $year = intval($_POST['year'] ?? 0);
    $base = [
        'infrastructure' => $infrastructure['id'],
        'year' => $year,
        'comment' => $_POST['comment'] ?? '',
    ];
    if (isset($_POST['month'])) {
        $date = explode('-', $_POST['month']);
        $base['month'] = intval($date[1]);
        $base['year'] = intval($date[0]);
    } elseif (isset($_POST['quarter'])) {
        $date = explode('-', $_POST['quarter']);
        $base['quarter'] = $date[1];
        $base['year'] = intval($date[0]);
    } elseif (isset($_POST['date'])) {
        $date = explode('-', $_POST['date']);
        $base['date'] = $_POST['date'];
        $base['year'] = intval($date[0]);
    }
    foreach ($_POST['values'] as $field => $value) {
        $entry = $base;
        $entry['field'] = $field;

        // check if entry already exists
        $existing = $osiris->infrastructureStats->findOne($entry);
        if (!empty($existing)) {
            if (empty($value) || !is_numeric($value) || $value == 0) {
                // delete entry
                $osiris->infrastructureStats->deleteOne(['_id' => $existing['_id']]);
                continue;
            }
            // update
            $osiris->infrastructureStats->updateOne(
                ['_id' => $existing['_id']],
                ['$set' => ['value' => intval($value), 'updated_by' => $_SESSION['username']]]
            );
        } else {
            // do not insert empty values
            if (empty($value) || !is_numeric($value) || $value == 0) continue;
            // insert
            $entry['value'] = intval($value);
            $entry['created_by'] = $_SESSION['username'];
            $osiris->infrastructureStats->insertOne($entry);
        }
    }
    // redirect
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect'] . "?msg=update-success#statistics");
        die();
    }
});




Route::get('/api/infrastructure/stats', function () {
    // Returns aggregated data for Plotly charts.
    // Requires: MongoDB PHP driver, collection "infrastructureStats"
    include_once BASEPATH . "/php/init.php";

    include_once BASEPATH . '/php/Vocabulary.php';
    $Vocabulary = new Vocabulary();

    header('Content-Type: application/json; charset=utf-8');

    $coll = $osiris->infrastructureStats;

    // --- Inputs ---
    $infra = $_GET['infrastructure'] ?? '';
    if (empty($infra)) {
        echo json_encode(['error' => 'infrastructure parameter is required']);
        die();
    }
    $infrastructure = $osiris->infrastructures->findOne(['id' => $infra]);
    if (empty($infrastructure)) {
        echo json_encode(['error' => 'infrastructure not found']);
        die();
    }

    $stat_frequency = $infrastructure['statistic_frequency'] ?? 'annual';

    $statistic_fields = DB::doc2Arr($infrastructure['statistic_fields'] ?? ['internal', 'national', 'international', 'hours', 'accesses']);

    $fields = $Vocabulary->getVocabulary('infrastructure-stats');
    $fields = $fields['values'] ?? [];
    $fields = array_filter($fields, function ($field) use ($statistic_fields) {
        return in_array($field['id'], $statistic_fields);
    });

    // get statistics ordered by year desc that are in the selected fields
    $statistics = $osiris->infrastructureStats->find(
        [
            'infrastructure' => $infrastructure['id'],
            'field' => ['$in' => $statistic_fields]
        ],
        [
            'sort' => ['year' => -1]
        ]
    )->toArray();

    $data = [];
    $fields_map = array_column($fields, null, 'id');
    foreach ($statistics as $stat) {
        $date = null;
        if (!array_key_exists($stat['field'], $data)) {
            $f = $fields_map[$stat['field']] ?? [];
            $data[$stat['field']] = [
                'x' => [],
                'y' => [],
                'type' => 'scatter',
                'mode' => 'lines+markers',
                'name' => lang($f['en'] ?? $stat['field'], $f['de'] ?? null),
            ];
        }
        if ($stat_frequency == 'annual') {
            $date = $stat['year'] . '-01-01';
        } elseif ($stat_frequency == 'quarterly' && isset($stat['quarter'])) {
            $quarter = str_replace('Q', '', $stat['quarter']);
            $month = (intval($quarter) - 1) * 3 + 1;
            $date = sprintf("%04d-%02d-01", $stat['year'], $month);
        } elseif ($stat_frequency == 'monthly' && isset($stat['month'])) {
            $date = sprintf("%04d-%02d-01", $stat['year'], $stat['month']);
        } elseif (isset($stat['date'])) {
            $date = $stat['date'];
        } else {
            $date = $stat['year'] . '-01-01';
        }
        $data[$stat['field']]['x'][] = $date;
        $data[$stat['field']]['y'][] = $stat['value'];
    }

    echo json_encode([
        'data' => array_values($data),
        'labels' => array_column($fields, lang('en', 'de'), 'id'),
    ]);
});

// Route::get('/api/infrastructure/stats', function () {
//     // Returns aggregated data for Plotly charts.
//     // Requires: MongoDB PHP driver, collection "infrastructureStats"
//     include_once BASEPATH . "/php/init.php";

//     header('Content-Type: application/json; charset=utf-8');

//     $coll = $osiris->infrastructureStats;

//     // --- Inputs ---
//     $infra = $_GET['infrastructure'] ?? '';
//     $granularity = $_GET['granularity'] ?? 'auto'; // 'auto'|'year'|'quarter'|'month'|'date'
//     $fieldForHeatmap = $_GET['field'] ?? null;

//     // Optional: limit fields to those enabled for the infra (if you have that list server-side)
//     $enabledFields = $_GET['enabled'] ?? ''; // comma-separated ids, optional
//     $enabled = array_filter(array_map('trim', explode(',', $enabledFields)));
//     $fieldsFilter = $enabled ? ['field' => ['$in' => $enabled]] : [];

//     // --- Helpers ---
//     function pad2($expr)
//     {
//         return ['$cond' => [
//             ['$lt' => [$expr, 10]],
//             ['$concat' => ['0', ['$toString' => $expr]]],
//             ['$toString' => $expr]
//         ]];
//     }

//     // Derive year/quarter/month from "date" if missing. Handles irregular entries.
//     $deriveStage = [
//         '$addFields' => [
//             'dateObj' => [
//                 '$cond' => [
//                     ['$and' => [['$ne' => ['$date', null]], ['$ne' => ['$date', '']]]],
//                     ['$dateFromString' => ['dateString' => '$date']],
//                     null
//                 ]
//             ]
//         ]
//     ];
//     $addYQM = [
//         '$addFields' => [
//             'year' => [
//                 '$ifNull' => [
//                     '$year',
//                     ['$cond' => [['$ne' => ['$dateObj', null]], ['$year' => '$dateObj'], null]]
//                 ]
//             ],
//             'quarter' => [
//                 '$ifNull' => [
//                     '$quarter',
//                     null
//                 ]
//             ],
//             'month' => [
//                 '$ifNull' => [
//                     '$month',
//                     ['$cond' => [['$ne' => ['$dateObj', null]], ['$month' => '$dateObj'], null]]
//                 ]
//             ],
//         ]
//     ];

//     // Decide granularity if 'auto': prefer most specific present in data (quarter > month > year > date fallback)
//     $detectGranularity = function () use ($coll, $infra, $fieldsFilter, $deriveStage, $addYQM) {
//         $pipeline = [
//             ['$match' => array_merge(['infrastructure' => $infra], $fieldsFilter)],
//             $deriveStage,
//             $addYQM,
//             ['$group' => [
//                 '_id' => null,
//                 'hasQuarter' => ['$max' => ['$cond' => [['$ne' => ['$quarter', null]], 1, 0]]],
//                 'hasMonth'   => ['$max' => ['$cond' => [['$ne' => ['$month', null]],   1, 0]]],
//                 'hasYear'    => ['$max' => ['$cond' => [['$ne' => ['$year', null]],    1, 0]]],
//                 'hasDate'    => ['$max' => ['$cond' => [['$ne' => ['$date', null]],    1, 0]]],
//             ]]
//         ];
//         $r = $coll->aggregate($pipeline)->toArray();
//         if (!$r) return 'year';
//         $f = $r[0];
//         // Priority: quarter -> month -> year -> date
//         if (($f['hasQuarter'] ?? 0) === 1) return 'quarter';
//         if (($f['hasMonth'] ?? 0) === 1)   return 'month';
//         if (($f['hasYear'] ?? 0) === 1)    return 'year';
//         if (($f['hasDate'] ?? 0) === 1)    return 'date';
//         return 'year';
//     };

//     if ($granularity === 'auto') {
//         $granularity = $detectGranularity();
//     }

//     // Build label for grouping
//     switch ($granularity) {
//         case 'quarter':
//             $labelExpr = [
//                 '$concat' => [
//                     ['$toString' => '$year'],
//                     '-',
//                     ['$toString' => ['$ifNull' => ['$quarter', 0]]]
//                 ]
//             ];
//             $sortKeys = ['year' => 1, 'quarter' => 1, 'month' => 1, 'label' => 1];
//             break;
//         case 'month':
//             $labelExpr = [
//                 '$concat' => [
//                     ['$toString' => '$year'],
//                     '-',
//                     pad2('$month')
//                 ]
//             ];
//             $sortKeys = ['year' => 1, 'month' => 1, 'label' => 1];
//             break;
//         case 'date':
//             // Use ISO string YYYY-MM-DD as label
//             $labelExpr = [
//                 '$cond' => [
//                     ['$ne' => ['$dateObj', null]],
//                     ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$dateObj']],
//                     'n/a'
//                 ]
//             ];
//             $sortKeys = ['label' => 1];
//             break;
//         case 'year':
//         default:
//             $labelExpr = ['$toString' => '$year'];
//             $sortKeys = ['year' => 1, 'label' => 1];
//             break;
//     }

//     // Common match
//     $baseMatch = array_merge(['infrastructure' => $infra], $fieldsFilter);

//     // Main time series: one series per field
//     $seriesPipeline = [
//         ['$match' => $baseMatch],
//         $deriveStage,
//         $addYQM,
//         ['$addFields' => ['label' => $labelExpr]],
//         ['$group' => [
//             '_id'   => ['label' => '$label', 'field' => '$field'],
//             'value' => ['$sum' => ['$toDouble' => ['$ifNull' => ['$value', 0]]]],
//             'year'  => ['$first' => '$year'],
//             'quarter' => ['$first' => '$quarter'],
//             'month'   => ['$first' => '$month'],
//         ]],
//         ['$sort' => array_merge(['_id.label' => 1], $sortKeys)],
//     ];

//     $series = [];
//     $labelsSet = [];
//     foreach ($coll->aggregate($seriesPipeline) as $row) {
//         $label = $row->_id->label ?? (string)($row['year'] ?? 'n/a');
//         $field = $row->_id->field ?? 'n/a';
//         $val   = (float)$row['value'];
//         $labelsSet[$label] = true;
//         if (!isset($series[$field])) $series[$field] = [];
//         $series[$field][$label] = $val;
//     }
//     $labels = array_keys($labelsSet);
//     natsort($labels);
//     $labels = array_values($labels);

//     // Align series to labels (fill gaps with 0)
//     $seriesAligned = [];
//     foreach ($series as $field => $dict) {
//         $arr = [];
//         foreach ($labels as $lab) {
//             $arr[] = isset($dict[$lab]) ? (float)$dict[$lab] : 0.0;
//         }
//         $seriesAligned[$field] = $arr;
//     }

//     // Latest snapshot for donut (distribution by field in max label)
//     $latestLabel = end($labels) ?: null;
//     $latestByField = [];
//     if ($latestLabel) {
//         foreach ($seriesAligned as $f => $arr) {
//             $latestByField[$f] = (float)end($arr);
//         }
//     }

//     // Heatmap (months × years) for one field (if not provided, pick first field found)
//     if (!$fieldForHeatmap) {
//         $fieldForHeatmap = array_key_first($seriesAligned) ?: null;
//     }
//     $heatYears = [];
//     $heatValues = [];
//     if ($fieldForHeatmap) {
//         $hmPipeline = [
//             ['$match' => array_merge($baseMatch, ['field' => $fieldForHeatmap])],
//             $deriveStage,
//             $addYQM,
//             // ensure months & years exist if possible
//             ['$match' => ['year' => ['$ne' => null], 'month' => ['$ne' => null]]],
//             ['$group' => [
//                 '_id' => ['year' => '$year', 'month' => '$month'],
//                 'value' => ['$sum' => ['$toDouble' => ['$ifNull' => ['$value', 0]]]],
//             ]],
//             ['$sort' => ['_id.year' => 1, '_id.month' => 1]]
//         ];
//         $heatData = iterator_to_array($coll->aggregate($hmPipeline));
//         // Build year list & matrix 12 months
//         $byYear = [];
//         foreach ($heatData as $r) {
//             $y = (int)$r->_id->year;
//             $m = (int)$r->_id->month;
//             $v = (float)$r['value'];
//             if (!isset($byYear[$y])) $byYear[$y] = array_fill(1, 12, 0.0);
//             $byYear[$y][$m] = $v;
//         }
//         $heatYears = array_keys($byYear);
//         sort($heatYears);
//         foreach ($heatYears as $y) {
//             $row = [];
//             for ($m = 1; $m <= 12; $m++) $row[] = $byYear[$y][$m] ?? 0.0;
//             $heatValues[] = $row;
//         }
//     }

//     // Output
//     echo json_encode([
//         'infrastructure' => $infra,
//         'granularity'    => $granularity,
//         'labels'         => $labels,
//         'series'         => $seriesAligned,   // {fieldId: [values ...]}
//         'latest'         => [
//             'label' => $latestLabel,
//             'byField' => $latestByField
//         ],
//         'heatmap'        => [
//             'field' => $fieldForHeatmap,
//             'years' => $heatYears,
//             'months' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
//             'values' => $heatValues
//         ]
//     ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
// });

// Route::post('/crud/infrastructures/year/([A-Za-z0-9]*)', function ($id) {
//     include_once BASEPATH . "/php/init.php";
//     include_once BASEPATH . "/php/Vocabulary.php";
//     $Vocabulary = new Vocabulary();
//     $fields = $Vocabulary->getVocabulary('infrastructure-stats');
//     if (empty($fields) || !is_array($fields) || empty($fields['values'])) {
//         $fields = ['internal', 'national', 'international', 'hours', 'accesses'];
//     } else {
//         $fields = array_column(DB::doc2Arr($fields['values']), 'id');
//     }

//     if (!$Settings->hasPermission('infrastructures.edit') && !$Settings->hasPermission('infrastructures.statistics')) {
//         // check if person is part of the infrastructure and is set as reporter
//         $permission = false;
//         $infrastructure = $osiris->infrastructures->findOne(['_id' => $DB->to_ObjectID($id)]);
//         foreach (($infrastructure['persons'] ?? []) as $person) {
//             if ($person['user'] == $_SESSION['username'] && (($person['reporter'] ?? false) || $Settings->hasPermission('infrastructures.edit-own'))) {
//                 $permission = true;
//                 break;
//             }
//         }
//         if (!$permission) {
//             header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
//             die;
//         }
//     }
//     if (!isset($_POST['values'])) die("no values given");
//     $values = $_POST['values'];
//     if (!isset($_POST['values']['year'])) die("no year given");

//     $collection = $osiris->infrastructures;

//     $year = intval($_POST['values']['year']);

//     $stats = [
//         'year' => $year,
//     ];
//     foreach ($fields as $field) {
//         if (isset($values[$field]) && is_numeric($values[$field])) {
//             $stats[$field] = intval($values[$field]);
//         } else {
//             $stats[$field] = 0;
//         }
//     }

//     $id = $DB->to_ObjectID($id);

//     // remove year if exists
//     $collection->updateOne(
//         ['_id' => $id],
//         ['$pull' => ['statistics' => ['year' => $year]]]
//     );

//     // add year
//     $updateResult = $collection->updateOne(
//         ['_id' => $id],
//         ['$push' => ['statistics' => $stats]]
//     );

//     if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
//         header("Location: " . $_POST['redirect'] . "?msg=update-success");
//         die();
//     }

//     echo json_encode([
//         'inserted' => $updateResult->getModifiedCount(),
//         'id' => $id,
//     ]);
// });





Route::post('/crud/infrastructures/update-persons/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Infrastructure.php";
    $Infra = new Infrastructure();

    if (!$Settings->hasPermission('infrastructures.edit')) {
        $permission = false;
        if ($Settings->hasPermission('infrastructures.edit-own')) {
            // check if person is part of the infrastructure
            $infrastructure = $osiris->infrastructures->findOne(['_id' => $DB->to_ObjectID($id)]);
            foreach (($infrastructure['persons'] ?? []) as $person) {
                if ($person['user'] == $_SESSION['username']) {
                    $permission = true;
                    break;
                }
            }
        }
        if (!$permission) {
            header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
            die;
        }
    }

    $values = $_POST['persons'];
    $users = [];
    foreach ($values as $i => $p) {
        if (empty($p['user'])) continue;
        if (in_array($p['user'], $users)) {
            unset($values[$i]);
            continue;
        }
        $users[] = $p['user'];
        $values[$i]['name'] =  $DB->getNameFromId($p['user']);
        $values[$i]['reporter'] = boolval($p['reporter'] ?? false);
        $values[$i]['fte'] = floatval($p['fte'] ?? 0);
        if (empty($p['start'])) {
            $values[$i]['start'] = null;
        }
        if (empty($p['end'])) {
            $values[$i]['end'] = null;
        }
    }

    $roles = array_keys($Infra->getRoles());
    // sort persons by role and end time (desc)
    usort($values, function ($a, $b) use ($roles) {
        if ($a['end'] == $b['end']) {
            return array_search($a['role'], $roles) - array_search($b['role'], $roles);
        }
        return $a['end'] <=> $b['end'];
    });

    // avoid object transformation
    $values = array_values($values);

    $osiris->infrastructures->updateOne(
        ['_id' => $DB::to_ObjectID($id)],
        ['$set' => ["persons" => $values]]
    );

    header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=update-success");
});


Route::post('/crud/infrastructures/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('infrastructures.delete')) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=no-permission");
        die;
    }

    $infrastructure = $osiris->infrastructures->findOne(['_id' => $DB->to_ObjectID($id)]);

    // remove infrastructure name from activities
    $osiris->activities->updateMany(
        ['infrastructures' => $infrastructure['id']],
        ['$pull' => ['infrastructures' => $infrastructure['id']]]
    );
    // remove infrastructure name from persons
    // $osiris->persons->updateMany(
    //     ['infrastructures' => $infrastructure['id']],
    //     ['$pull' => ['infrastructures' => $infrastructure['id']]]
    // );
    // // remove infrastructure name from projects
    // $osiris->projects->updateMany(
    //     ['infrastructures' => $infrastructure['id']],
    //     ['$pull' => ['infrastructures' => $infrastructure['id']]]
    // );

    // remove infrastructure
    $osiris->infrastructures->deleteOne(
        ['_id' => $DB::to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang("Infrastructure has been deleted successfully.", "Infrastruktur wurde erfolgreich gelöscht.");
    header("Location: " . ROOTPATH . "/infrastructures");
});


Route::post('/crud/infrastructures/upload-picture/(.*)', function ($infrastructure_id) {
    include_once BASEPATH . "/php/init.php";

    // get infrastructure id    
    $infrastructure = $osiris->infrastructures->findOne(['id' => $infrastructure_id]);
    if (empty($infrastructure)) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$infrastructure_id?msg=not-found");
        die;
    }
    if (isset($_FILES["file"])) {
        // if ($_FILES['file']['type'] != 'image/jpeg') die('Wrong extension, only JPEG is allowed.');

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini'),
                2 => lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt."),
                3 => lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.'),
                4 => lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.'),
                6 => lang('Missing a temporary folder.', 'Der temporäre Ordner fehlt.'),
                7 => lang('Failed to write file to disk.', 'Datei konnte nicht auf die Festplatte geschrieben werden.'),
                8 => lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.'),
                default => lang('Something went wrong.', 'Etwas ist schiefgelaufen.') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
            $_SESSION['msg_type'] = "error";
        } else if ($_FILES["file"]["size"] > 2000000) {
            $_SESSION['msg'] = lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt.");
            $_SESSION['msg_type'] = "error";
        } else {
            // check image settings
            $file = file_get_contents($_FILES["file"]["tmp_name"]);
            $type = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            // encode image
            $file = base64_encode($file);
            $img = new MongoDB\BSON\Binary($file, MongoDB\BSON\Binary::TYPE_GENERIC);
            // first: delete old image, then: insert new one
            $updateResult = $osiris->infrastructures->updateOne(
                ['id' => $infrastructure_id],
                ['$set' => ['image' => [
                    'data' => $img,
                    'type' => $type,
                    'extension' => $type,
                    'uploaded_by' => $_SESSION['username'],
                    'uploaded' => date('Y-m-d')
                ]]]
            );
            $_SESSION['msg'] = lang("Infrastructure logo uploaded successfully.", "Infrastruktur-Logo erfolgreich hochgeladen.");
            $_SESSION['msg_type'] = "success";
            header("Location: " . ROOTPATH . "/infrastructures/view/$infrastructure_id");
            die;
            // printMsg(lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload."), "error");
        }
    } else if (isset($_POST['delete'])) {
        $osiris->infrastructures->updateOne(
            ['id' => $infrastructure_id],
            ['$unset' => ['image' => ""]]
        );
        $_SESSION['msg'] = lang("Infrastructure logo deleted.", "Infrastruktur-Logo gelöscht.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . ROOTPATH . "/infrastructures/view/$infrastructure_id");
        die;
    }

    header("Location: " . ROOTPATH . "/infrastructures/view/$infrastructure_id");
    die;
});


Route::get('/infrastructures/image/(.*)', function ($id) {
    // print image
    include_once BASEPATH . "/php/init.php";
    $mongo_id = $DB->to_ObjectID($id);
    // get infrastructure id    
    $infrastructure = $osiris->infrastructures->findOne(['_id' => $mongo_id]);
    if (empty($infrastructure)) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=not-found");
        die;
    }
    include_once BASEPATH . "/php/Infrastructure.php";
    echo Infrastructure::getLogo($infrastructure, "", "Logo of " . $infrastructure['name'], $infrastructure['type'] ?? "");
});