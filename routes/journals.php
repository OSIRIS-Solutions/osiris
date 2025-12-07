<?php

/**
 * Routing file for journals
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


Route::get('/journals?', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => $Settings->journalLabel(), 'path' => "/journal"],
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/journals/table.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/journal/metrics', function () {
    include_once BASEPATH . "/php/init.php";
    if ($Settings->featureEnabled('no-journal-metrics')) {
        echo "<p class='alert alert-danger'>" . lang('Feature not available', 'Funktion nicht verfügbar') . "</p>";
        die;
    }
    $breadcrumb = [
        ['name' => $Settings->journalLabel(), 'path' => "/journal"],
        ['name' => lang('Metrics', 'Metriken')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/journals/metrics.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/journals?/view/([a-zA-Z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $id = $DB->to_ObjectID($id);

    $data = $osiris->journals->findOne(['_id' => $id]);
    $breadcrumb = [
        ['name' => $Settings->journalLabel(), 'path' => "/journal"],
        ['name' => $data['abbr'] ?? $data['journal'] ?? '']
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/journals/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/journal/add', function () {
    include_once BASEPATH . "/php/init.php";
    $id = null;
    $data = [];
    $breadcrumb = [
        ['name' => $Settings->journalLabel(), 'path' => "/journal"],
        ['name' => lang("Add", "Hinzufügen")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/journals/editor.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/journal/edit/([a-zA-Z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $id = $DB->to_ObjectID($id);

    $data = $osiris->journals->findOne(['_id' => $id]);
    $breadcrumb = [
        ['name' => $Settings->journalLabel(), 'path' => "/journal"],
        ['name' => $data['abbr'] ?? $data['journal'] ?? '', 'path' => "/journal/view/$id"],
        ['name' => lang("Edit", "Bearbeiten")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/journals/editor.php";
    include BASEPATH . "/footer.php";
}, 'login');


// journal/check-metrics
Route::get('/journal/check-metrics', function () {
    include_once BASEPATH . "/php/init.php";
    // enhance time limit
    set_time_limit(6000);
    // first check the year from https://osiris-app.de/api/v1
    $url = "https://osiris-app.de/api/v1";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $result = json_decode($result, true);
    $year = $result['latest_year'] ?? date('Y');
    // {"metrics.year": {$ne: 2023}}
    $collection = $osiris->journals;
    $cursor = $collection->find(['metrics.year' => ['$ne' => $year], 'no_metrics' => ['$ne' => true]], ['issn' => 1]);
    $N = 0;
    foreach ($cursor as $doc) {
        $issn = $doc['issn'] ?? [];
        if (empty($issn)) continue;

        $metrics = [];
        $categories = [];
        foreach ($issn as $i) {
            if (empty($i)) continue;

            $url = "https://osiris-app.de/api/v1/journals/" . $i;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                // "X-ApiKey: $apikey"
            ]);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            $result = json_decode($result, true);
            if (!empty($result['metrics'] ?? null)) {
                $metrics = array_values($result['metrics']);
                $categories = $result['categories'] ?? [];
                break;
            }
        }
        if (empty($metrics)) {
            // make sure to skip for future check
            $updateResult = $collection->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['no_metrics' => true]]
            );
            continue;
        }
        # sort metrics by year
        usort($metrics, function ($a, $b) {
            return $a['year'] <=> $b['year'];
        });

        $impact = [];
        foreach ($metrics as $i) {
            $impact[] = [
                'year' => $i['year'],
                'impact' => floatval($i['if_2y'])
            ];
        }

        $updateResult = $collection->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => ['metrics' => $metrics, 'impact' => $impact, 'categories' => $categories]]
        );
        $N++;
    }
    $_SESSION['msg'] = "Updated metrics of $N journals";
    if ($N > 100) {
        $_SESSION['msg'] .= " (max. 100). Please reload to check more.";
        die;
    }

    header("Location: " . ROOTPATH . "/journal");
});

// update metrics

// journals/metrics/update/:year
Route::post('/journal/metrics/update/(\d{4})', function ($year) {
    include_once BASEPATH . "/php/init.php";
    // enhance time limit
    set_time_limit(0);
    $year = intval($year);

    $count = $osiris->journals->count([
        'metrics.year' => ['$ne' => $year],
        'no_metrics' => ['$ne' => true]
    ]);

    $url = "https://osiris-app.de/api/v1/metrics/$year";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
    ]);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    $result = json_decode($result, true);
    $result = array_column($result, null, 'issn');

    if (empty($result)) {
        echo json_encode([
            'done' => true,
            'message' => lang("No metrics found for this year.", "Keine Metriken für dieses Jahr gefunden."),
            'value' => $count
        ]);
        die;
    }
    $collection = $osiris->journals;
    // get issn list of all journals
    $cursor = $collection->find(['metrics.year' => ['$ne' => $year], 'no_metrics' => ['$ne' => true]], ['issn' => 1]);

    foreach ($cursor as $j) {
        $mongoid = $j['_id'];
        //check if issn is empty
        $issn = $j['issn'] ?? [];
        $found = false;
        $metric = null;
        foreach ($issn as $i) {
            if (empty($i)) continue;
            // remove '-' from issn
            $i = str_replace('-', '', $i);
            // check if issn exists in result
            if (isset($result[$i])) {
                $found = true;
                $metric = $result[$i];
                break;
            }
        }
        if (empty($issn) || !$found || empty($metric)) {
            // make sure to skip for future check
            $collection->updateOne(
                ['_id' => $mongoid],
                ['$set' => ['no_metrics' => true]]
            );
            continue;
        }
        // Remove existing year
        $collection->updateOne(
            ['_id' => $mongoid, 'impact.year' => ['$exists' => true]],
            [
                '$pull' => [
                    'impact' => ['year' => ['$in' => [$year, strval($year)]]],
                ]
            ]
        );
        // Remove existing metrics
        $collection->updateOne(
            ['_id' => $mongoid, 'metrics.year' => ['$exists' => true]],
            [
                '$pull' => [
                    'metrics' => ['year' => ['$in' => [$year, strval($year)]]],
                ]
            ]
        );

        // Add new metrics
        // check for BulkWriteException
        try {
            $collection->updateOne(
                ['_id' => $mongoid],
                [
                    '$push' => [
                        'impact' => ['year' => $year, 'impact' => $metric['if_2y'] ?? 0],
                        'metrics' => [
                            'year' => $year,
                            'quartile' => $metric['quartile'] ?? null,
                            'sjr' => $metric['sjr'] ?? null,
                            'if_2y' => $metric['if_2y'] ?? 0,
                            'if_3y' => $metric['if_3y'] ?? null,
                        ]
                    ]
                ]
            );
        } catch (MongoDB\Driver\Exception\BulkWriteException $th) {
            // if the journal has no metrics, set it to empty array
            $collection->updateOne(
                ['_id' => $mongoid],
                [
                    '$set' => [
                        'impact' => [['year' => $year, 'impact' => $metric['if_2y'] ?? 0]],
                        'metrics' => [[
                            'year' => $year,
                            'quartile' => $metric['quartile'] ?? null,
                            'sjr' => $metric['sjr'] ?? null,
                            'if_2y' => $metric['if_2y'] ?? 0,
                            'if_3y' => $metric['if_3y'] ?? null,
                        ]]
                    ]
                ]
            );
        }
    }
});
// journals/metrics/progress/:year
Route::get('/journal/metrics/progress/(\d{4})', function ($year) {
    include_once BASEPATH . "/php/init.php";
    $count = $osiris->journals->count([
        'metrics.year' => ['$ne' => $year],
        'no_metrics' => ['$ne' => true]
    ]);
    if (empty($count)) {
        echo json_encode([
            'done' => true,
            'message' => lang("All journals have been updated.", "Alle Journale wurden aktualisiert."),
            'value' => $count
        ]);
        die;
    }
    echo json_encode([
        'done' => false,
        'message' => lang("Still updating journals...", "Aktualisiere..."),
        'value' => $count
    ]);
});



/**
 * CRUD routes
 */

Route::post('/crud/journal/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->journals;

    $values = validateValues($_POST['values'], $DB);
    $values['impact'] = [];

    $values['abbr'] = $values['abbr'] ?? $values['journal'];

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    // check if issn already exists:
    if (isset($values['issn']) && !empty($values['issn'])) {
        $issn_exist = $collection->findOne(['issn' => ['$in' => $values['issn']]]);
        if (!empty($issn_exist)) {
            echo json_encode([
                'msg' => "ISSN already existed",
                'id' => $issn_exist['_id'],
                'journal' => $issn_exist['journal'],
                'issn' => $issn_exist['issn'],
            ]);
            die;
        }
    }

    $values['issn'] = array_filter($values['issn'] ?? []);

    if (!$Settings->featureEnabled('no-journal-metrics')) {
        try {
            foreach ($values['issn'] as $issn) {
                if (empty($issn)) continue;

                $url = "https://osiris-app.de/api/v1/journals/" . $issn;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    // "X-ApiKey: $apikey"
                ]);
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($curl);
                $result = json_decode($result, true);
                if (!empty($result['metrics'] ?? null)) {
                    $values['metrics'] = $result['metrics'];
                    # sort metrics by year
                    usort($values['metrics'], function ($a, $b) {
                        return $a['year'] <=> $b['year'];
                    });

                    $values['impact'] = [];
                    foreach ($values['metrics'] as $i) {
                        $values['impact'][] = [
                            'year' => $i['year'],
                            'impact' => floatval($i['if_2y'])
                        ];
                    }
                    break;
                }
            }
        } catch (\Throwable $th) {
        }
    }

    // dump($values, true);
    // die;

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
    // $result = $collection->findOne(['_id' => $id]);
});


Route::post('/crud/journal/update-metrics/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if ($Settings->featureEnabled('no-journal-metrics')) {
        echo json_encode([
            'msg' => "Feature not available",
            'id' => $id,
        ]);
        die;
    }
    $collection = $osiris->journals;
    $mongoid = $DB->to_ObjectID($id);

    $journal = $collection->findOne(['_id' => $mongoid]);
    if (empty($journal['issn'] ?? null)) {
        header("Location: " . ROOTPATH . "/journal/view/$id?msg=error-no-issn");
        die;
    }

    $metrics = [];
    $categories = [];
    $country = null;
    foreach ($journal['issn'] as $issn) {
        if (empty($issn)) continue;

        $url = "https://osiris-app.de/api/v1/journals/" . $issn;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            // "X-ApiKey: $apikey"
        ]);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $result = json_decode($result, true);
        if (!empty($result['metrics'] ?? null)) {
            $metrics = array_values($result['metrics']);
            $categories = $result['categories'] ?? [];
            $country = $result['country'] ?? null;
            break;
        }
    }

    if (empty($metrics)) {
        header("Location: " . ROOTPATH . "/journal/view/$id?msg=error-no-metrics");
        die;
    }

    # sort metrics by year
    usort($metrics, function ($a, $b) {
        return $a['year'] <=> $b['year'];
    });

    $impact = [];
    foreach ($metrics as $i) {
        $impact[] = [
            'year' => $i['year'],
            'impact' => floatval($i['if_2y'])
        ];
    }

    $values = [
        'metrics' => $metrics,
        'impact' => $impact,
        'categories' => $categories,
    ];
    if (!empty($country)) {
        $values['country'] = $country;
    }
    $updateResult = $collection->updateOne(
        ['_id' => $mongoid],
        ['$set' => $values]
    );

    header("Location: " . ROOTPATH . "/journal/view/$id?msg=update-success");
});


Route::post('/crud/journal/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $values = $_POST['values'];
    $values = validateValues($values, $DB);

    $collection = $osiris->journals;
    $mongoid = $DB->to_ObjectID($id);

    if (isset($values['year'])) {
        $year = intval($values['year']);
        if (isset($values['if'])) {
            $if = $values['if'] ?? null;

            // remove existing year
            $updateResult = $collection->updateOne(
                ['_id' => $mongoid, 'impact.year' => ['$exists' => true]],
                ['$pull' => ['impact' => ['year' => $year]]]
            );
            if (empty($if)) {
                // do nothing more
            } else {
                // add new impact factor
                try {
                    $updateResult = $collection->updateOne(
                        ['_id' => $mongoid],
                        ['$push' => ['impact' => ['year' => $year, 'impact' => $if]]]
                    );
                } catch (MongoDB\Driver\Exception\BulkWriteException $th) {
                    $updateResult = $collection->updateOne(
                        ['_id' => $mongoid],
                        ['$set' => ['impact' => [['year' => $year, 'impact' => $if]]]]
                    );
                }
            }
        } else if (isset($values['quartile'])) {
            $quartile = $values['quartile'] ?? null;
            // get existing metrics
            $metrics = $collection->findOne(['_id' => $mongoid], ['projection' => ['metrics' => 1]]);
            // find the year in metrics
            $found = false;
            $newMetric = [];
            foreach ($metrics['metrics'] as $m) {
                if ($m['year'] == $year) {
                    $found = true;
                    $newMetric = $m;
                    break;
                }
            }
            $newMetric['year'] = $year;
            $newMetric['quartile'] = $quartile;
            if ($found) {
                // remove existing year
                $updateResult = $collection->updateOne(
                    ['_id' => $mongoid, 'metrics.year' => ['$exists' => true]],
                    ['$pull' => ['metrics' => ['year' => $year]]]
                );
            }
            if (empty($quartile)) {
                // do nothing more
            } else {
                // add new metrics
                try {
                    $updateResult = $collection->updateOne(
                        ['_id' => $mongoid],
                        ['$push' => ['metrics' => $newMetric]]
                    );
                } catch (MongoDB\Driver\Exception\BulkWriteException $th) {
                    // if the journal has no metrics, set it to empty array
                    $updateResult = $collection->updateOne(
                        ['_id' => $mongoid],
                        ['$set' => ['metrics' => [$newMetric]]]
                    );
                }
            }
        }
    } else {

        // // add information on updating process
        $values['updated'] = date('Y-m-d');
        $values['updated_by'] = $_SESSION['username'];

        if (isset($values['oa']) && $values['oa'] !== false) {
            $updateResult = $osiris->activities->updateMany(
                ['journal_id' => $id, 'year' => ['$gt' => $values['oa']]],
                ['$set' => ['open_access' => true]]
            );
        }


        $updateResult = $collection->updateOne(
            ['_id' => $mongoid],
            ['$set' => $values]
        );
    }

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect'] . "?msg=update-success");
        die();
    }
    echo json_encode([
        'updated' => $updateResult->getModifiedCount(),
        'result' => $collection->findOne(['_id' => $id])
    ]);
});
