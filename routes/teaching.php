<?php

/**
 * Routing file for teaching
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

Route::get('/teaching', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('Activities', "Aktivitäten"), 'path' => "/activities"],
        ['name' => lang("Teaching", "Lehrveranstaltungen")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/teaching/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/teaching/statistics', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('Activities', "Aktivitäten"), 'path' => "/activities"],
        ['name' => lang("Teaching", "Lehrveranstaltungen"), 'path' => "/teaching"],
        ['name' => lang("Statistics", "Statistiken")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/teaching/statistics.php";
    include BASEPATH . "/footer.php";
}, 'login');


/**
 * CRUD routes
 */

 Route::post('/crud/teaching/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->teaching;

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];


    // check if module already exists:
    if (isset($values['module']) && !empty($values['module'])) {
        $module_exist = $collection->findOne(['module' => $values['module']]);
        if (!empty($module_exist)) {

            $updateResult = $collection->updateOne(
                ['_id' => $module_exist['_id']],
                ['$set' => $values]
            );
            // echo json_encode([
            //     'msg' => "module already existed",
            //     'id' => $module_exist['_id'],
            //     'journal' => $module_exist['journal'],
            //     'module' => $module_exist['module'],
            // ]);
            if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
                $red = str_replace("*", $id, $_POST['redirect']);
                header("Location: " . $red . "?msg=updated");
                die();
            }
            die;
        }
    } else {
        echo json_encode([
            'msg' => "Module must be given"
        ]);
        die;
    }

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


Route::post('/crud/teaching/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    //chack that no activities are connected
    $activities = $osiris->activities->count(['module_id' => strval($module['_id'])]);
    if ($activities != 0) {
        header("Location: " . $_POST['redirect'] . "?msg=Cannot+delete+teaching+module+when+activities+are+still+connected&msgType=error");
        die;
    }

    // prepare id
    $id = $DB->to_ObjectID($id);
    $updateResult = $osiris->teaching->deleteOne(['_id' => $id]);
    $deletedCount = $updateResult->getDeletedCount();

    // addUserActivity('delete');
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        header("Location: " . $_POST['redirect'] . "?msg=deleted-1&msgType=error");
        die();
    }
    echo json_encode([
        'deleted' => $deletedCount
    ]);
});
