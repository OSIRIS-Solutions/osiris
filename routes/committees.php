<?php

/**
 * Routing file for committees
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

Route::get('/committees', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Committees & Boards", "Gremien und Boards")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/committees/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/committees/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    if (!$Settings->hasPermission('committees.edit')) {
        header("Location: " . ROOTPATH . "/committees?msg=no-permission");
        die;
    }

    $breadcrumb = [
        ['name' => lang('Committees & Boards', 'Gremien und Boards'), 'path' => "/committees"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/committees/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/committees/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/committee.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $committee = $osiris->committees->findOne(['_id' => $mongo_id]);
    } else {
        $committee = $osiris->committees->findOne(['id' => $id]);
        $id = strval($committee['_id'] ?? '');
    }
    if (empty($committee)) {
        header("Location: " . ROOTPATH . "/committees?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Committees & Boards', 'Gremien und Boards'), 'path' => "/committees"],
        ['name' => $committee['name']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/committees/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/committees/edit/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (!$Settings->hasPermission('committees.edit')) {
        header("Location: " . ROOTPATH . "/committees/view/$id?msg=no-permission");
        die;
    }

    global $form;

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->committees->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->committees->findOne(['name' => $id]);
        $id = strval($committee['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/committees?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Committees & Boards', 'Gremien und Boards'), 'path' => "/committees"],
        ['name' => $form['name'], 'path' => "/committees/view/$id"],
        ['name' => lang("Edit", "Bearbeiten")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/committees/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


/**
 * CRUD routes
 */

Route::post('/crud/committees/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->committees;

    $values = validateValues($_POST['values'], $DB);

    // check if name is given
    if (empty($values['name'])) {
        if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
            header("Location: " . $_POST['redirect'] . "?msg=committee-name-required");
            die();
        }
        echo json_encode([
            'msg' => "Committee name is required.",
            'id' => null,
            'name' => null
        ]);
        die();
    }
    $values['name'] = trim($values['name']);
    $filter = [
        'name' => $values['name']
    ];
    // check if committee id already exists:
    $exist = $collection->findOne($filter);
    if (!empty($exist)) {
        if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
            $red = str_replace("*", $id, $_POST['redirect']);
            header("Location: " . $red . "?msg=committee does already exist.");
        } else {
            echo json_encode([
                'msg' => "Committee already exists.",
                'id' => strval($exist['_id']),
                'name' => $exist['name']
            ]);
        }
        die();
    }

    $values['active'] = boolval($values['active'] ?? true);

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    $insertOneResult  = $collection->insertOne($values);
    $new_id = $insertOneResult->getInsertedId();

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $new_id, $_POST['redirect']);
        header("Location: " . $red . "?msg=success");
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $new_id,
        'name' => $values['name']
    ]);
});


Route::post('/crud/committees/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('committees.edit')) {
        header("Location: " . ROOTPATH . "/committees?msg=no-permission");
        die;
    }
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->committees;

    $values = validateValues($_POST['values'], $DB);
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



Route::post('/crud/committees/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('committees.delete')) {
        header("Location: " . ROOTPATH . "/committees?msg=no-permission");
        die;
    }

    // $committee = $osiris->committees->findOne(['_id' => $DB->to_ObjectID($id)]);

    // remove committee name from activities
    // $osiris->activities->updateMany(
    //     ['committees' => $committee['id']],
    //     ['$pull' => ['committees' => $committee['id']]]
    // );

    // remove committee
    $osiris->committees->deleteOne(
        ['_id' => $DB::to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang("Organisation has been deleted successfully.", "Organisation wurde erfolgreich gelöscht.");
    header("Location: " . ROOTPATH . "/committees");
});
