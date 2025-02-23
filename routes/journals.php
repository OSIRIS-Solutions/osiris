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


Route::get('/journal', function () {
    // if ($page == 'users') 
    $breadcrumb = [
        ['name' => lang('Journals', 'Journale'), 'path' => "/journal"],
        ['name' => lang('Table', 'Tabelle')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/journals/table.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/journal/view/([a-zA-Z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $id = $DB->to_ObjectID($id);

    $data = $osiris->journals->findOne(['_id' => $id]);
    $breadcrumb = [
        ['name' => lang('Journals', 'Journale'), 'path' => "/journal"],
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
        ['name' => lang('Journals', 'Journale'), 'path' => "/journal"],
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
        ['name' => lang('Journals', 'Journale'), 'path' => "/journal"],
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
    $year = $result['year'] ?? date('Y');
    // {"metrics.year": {$ne: 2023}}
    $collection = $osiris->journals;
    $cursor = $collection->find(['metrics.year' => ['$ne' => $year], 'no_metrics'=> ['$ne'=>true]], ['issn' => 1]);
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

    try {
        // try to get impact factor from WoS Journal info
        // include_once BASEPATH . "/php/simple_html_dom.php";

        // if (defined('WOS_JOURNAL_INFO') && !empty(WOS_JOURNAL_INFO)) {
        //     $YEAR = WOS_JOURNAL_INFO ?? 2021;

        //     $html = new simple_html_dom();
        //     foreach ($values['issn'] as $i) {
        //         if (empty($i)) continue;
        //         $url = 'https://wos-journal.info/?jsearch=' . $i;
        //         $html->load_file($url);
        //         foreach ($html->find("div.row") as $row) {
        //             $el = $row->plaintext;
        //             if (preg_match('/Impact Factor \(IF\):\s+(\d+\.?\d*)/', $el, $match)) {
        //                 $values['impact'] = [['year' => $YEAR, 'impact' => floatval($match[1])]];
        //                 break 2;
        //             }
        //         }
        //     }
        // }

        // if (defined('WOS_STARTER_KEY') && !empty(WOS_STARTER_KEY)) {
        //     $apikey = WOS_STARTER_KEY;
        //     foreach ($values['issn'] as $i) {
        //         if (empty($i)) continue;

        //         $url = "https://api.clarivate.com/apis/wos-starter/v1/journals";
        //         $url .= "?issn=" . $i;

        //         $curl = curl_init();
        //         curl_setopt($curl, CURLOPT_HTTPHEADER, [
        //             'Accept: application/json',
        //             "X-ApiKey: $apikey"
        //         ]);
        //         curl_setopt($curl, CURLOPT_URL, $url);
        //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //         $result = curl_exec($curl);
        //         $result = json_decode($result, true);
        //         if (!empty($result['hits'])) {
        //             $values['wos'] = $result['hits'][0];
        //         }
        //     }
        // }

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

            // dump([$values, $updateResult], true);
            // die;
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
