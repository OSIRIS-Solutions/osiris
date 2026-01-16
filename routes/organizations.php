<?php

/**
 * Routing file for organizations
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

Route::get('/organizations', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Organisations", "Organisationen")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/organizations/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    if (!$Settings->hasPermission('organizations.edit')) {
        header("Location: " . ROOTPATH . "/organizations?msg=no-permission");
        die;
    }

    $breadcrumb = [
        ['name' => lang('Organisations', 'Organisationen'), 'path' => "/organizations"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/organizations/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Organization.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $organization = $osiris->organizations->findOne(['_id' => $mongo_id]);
    } else {
        $organization = $osiris->organizations->findOne(['id' => $id]);
        $id = strval($organization['_id'] ?? '');
    }
    if (empty($organization)) {
        header("Location: " . ROOTPATH . "/organizations?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Organisations', 'Organisationen'), 'path' => "/organizations"],
        ['name' => $organization['name']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/view.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/organizations/edit/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (!$Settings->hasPermission('organizations.edit')) {
        header("Location: " . ROOTPATH . "/organizations/view/$id?msg=no-permission");
        die;
    }

    global $form;

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->organizations->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->organizations->findOne(['name' => $id]);
        $id = strval($organization['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/organizations?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Organisations', 'Organisationen'), 'path' => "/organizations"],
        ['name' => $form['name'], 'path' => "/organizations/view/$id"],
        ['name' => lang("Edit", "Bearbeiten")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


/**
 * CRUD routes
 */

Route::post('/crud/organizations/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->organizations;

    $values = validateValues($_POST['values'], $DB);
    unset($values['chosen']);
    unset($values['id']);

    $filter = [
        'name' => $values['name'],
        'country' => $values['country'] ?? ''
    ];
    $ror = $values['ror'] ?? $values['ror_id'] ?? '';
    unset($values['ror_id']);

    if (!empty($ror)) {
        // make sure ror is a valid URL
        $values['ror'] = str_replace("https://ror.org/", "", $ror);
        $values['ror'] =  "https://ror.org/" . $values['ror'];
        $filter = [
            '$or' => [
                $filter,
                ['ror' => $values['ror']]
            ]
        ];
    }
    // check if organization id already exists:
    $exist = $collection->findOne($filter);
    if (!empty($exist)) {
        if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
            $red = str_replace("*", strval($exist['_id']), $_POST['redirect']);
            $_SESSION['msg'] = lang("Organization does already exist.", "Organisation existiert bereits.");
            $_SESSION['msg_type'] = "warning";
            header("Location: " . $red);
        } else {
            echo json_encode([
                'msg' => lang("Organization does already exist and was connected.", "Organisation existiert bereits und wurde verknüpft."),
                'id' => strval($exist['_id']),
                'ror' => $exist['ror'] ?? '',
                'name' => $exist['name'],
                'location' => $exist['location'],
            ]);
        }
        die();
    }

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
        'ror' => $values['ror'] ?? '',
        'name' => $values['name'],
        'location' => $values['location'] ?? '',
    ]);
});


Route::post('/crud/organizations/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('organizations.edit')) {
        header("Location: " . ROOTPATH . "/organizations?msg=no-permission");
        die;
    }
    if (!isset($_POST['values'])) die("no values given");
    $collection = $osiris->organizations;

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



Route::post('/crud/organizations/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('organizations.delete')) {
        header("Location: " . ROOTPATH . "/organizations?msg=no-permission");
        die;
    }

    // $organization = $osiris->organizations->findOne(['_id' => $DB->to_ObjectID($id)]);

    // remove organization name from activities
    // $osiris->activities->updateMany(
    //     ['organizations' => $organization['id']],
    //     ['$pull' => ['organizations' => $organization['id']]]
    // );

    // remove organization
    $osiris->organizations->deleteOne(
        ['_id' => $DB::to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang("Organisation has been deleted successfully.", "Organisation wurde erfolgreich gelöscht.");
    header("Location: " . ROOTPATH . "/organizations");
});
