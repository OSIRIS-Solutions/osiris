<?php

/**
 * Routing file for organizations
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/organizations', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.organizations')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/organizations/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    if (!$Settings->hasPermission('organizations.edit')) {
        abortwith(403, lang('organizations.you_do_not_have_permission_to_create_a_new_organization'), '/organizations', lang('organizations.go_back_to_organizations'));
    }

    $breadcrumb = [
        ['name' => lang('common.organizations'), 'path' => "/organizations"],
        ['name' => lang('common.new')]
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
        abortwith(404, lang('common.organization'), '/organizations');
    }
    $breadcrumb = [
        ['name' => lang('common.organizations'), 'path' => "/organizations"],
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
        abortwith(403, lang('organizations.you_do_not_have_permission_to_edit_this_organization'), '/organizations/view/' . $id, lang('organizations.go_back_to_organization'));
    }

    global $form;

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->organizations->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->organizations->findOne(['name' => $id]);
        $id = strval($form['_id'] ?? '');
    }
    if (empty($form)) {
        abortwith(404, lang('common.organization'), '/organizations');
    }
    $breadcrumb = [
        ['name' => lang('common.organizations'), 'path' => "/organizations"],
        ['name' => $form['name'], 'path' => "/organizations/view/$id"],
        ['name' => lang('action.edit')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/organizations/map', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.organizations'), 'path' => "/organizations"],
        ['name' => lang('common.map')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/organizations/map.php";
    include BASEPATH . "/footer.php";
}, 'login');

/**
 * CRUD routes
 */

Route::post('/crud/organizations/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('organizations.edit')) {
        abortwith(403, lang('organizations.you_do_not_have_permission_to_create_a_new_organization'), '/organizations', lang('organizations.go_back_to_organizations'));
    }

    if (!isset($_POST['values']) || empty($_POST['values'])) abortwith(500, lang('error.no_values'));
    $collection = $osiris->organizations;

    $values = validateValues($_POST['values'], $DB);
    if (empty($values['name'])) {
        echo json_encode([
            'msg' => lang('organizations.organization_name_is_required'),
            'status' => 'error'
        ]);
        die();
    }
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
            $_SESSION['msg'] = lang('organizations.organization_does_already_exist');
            $_SESSION['msg_type'] = "warning";
            header("Location: " . $red);
        } else {
            echo json_encode([
                'msg' => lang('organizations.organization_does_already_exist_and_was_connected'),
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
        $_SESSION['msg'] = lang('organizations.organization_has_been_created_successfully');
        $_SESSION['msg_type'] = "success";
        header("Location: " . $red);
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => strval($new_id),
        'ror' => $values['ror'] ?? '',
        'name' => $values['name'],
        'location' => $values['location'] ?? '',
    ]);
});


Route::post('/crud/organizations/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('organizations.edit')) {
        abortwith(403, lang('organizations.you_do_not_have_permission_to_edit_this_organization'), '/organizations/view/' . $id, lang('organizations.go_back_to_organization'));
    }
    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));
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
        $_SESSION['msg'] = lang('organizations.organization_has_been_updated_successfully');
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect']);
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
        abortwith(403, lang('organizations.you_do_not_have_permission_to_delete_this_organization'), '/organizations/view/' . $id, lang('organizations.go_back_to_organization'));
    }

    // $organization = $osiris->organizations->findOne(['_id' => $DB->to_ObjectID($id)]);

    // remove organization name from activities
    // $osiris->activities->updateMany(
    //     ['organizations' => $organization['id']],
    //     ['$pull' => ['organizations' => $organization['id']]]
    // );

    // remove organization
    $osiris->organizations->deleteOne(
        ['_id' => $DB->to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang('organizations.organisation_has_been_deleted_successfully');
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/organizations");
});


Route::post('/crud/organizations/upload-picture/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $mongo_id = $DB->to_ObjectID($id);
    // get organization id    
    $organization = $osiris->organizations->findOne(['_id' => $mongo_id]);
    if (empty($organization)) {
        abortwith(404, lang('common.organization'), '/organizations');
    }
    if (isset($_FILES["file"])) {
        // if ($_FILES['file']['type'] != 'image/jpeg') die('Wrong extension, only JPEG is allowed.');

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('error.file_upload_exceeds_limit'),
                2 => lang('error.file_too_big_max_2MB'),
                3 => lang('error.file_partially_uploaded'),
                4 => lang('error.no_file_uploaded'),
                6 => lang('error.file_upload_missing_temp'),
                7 => lang('error.file_upload_write_failed'),
                8 => lang('error.file_upload_stopped'),
                default => lang('error.something_went_wrong') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
            $_SESSION['msg_type'] = "error";
        } else if ($_FILES["file"]["size"] > 2000000) {
            $_SESSION['msg'] = lang('error.file_too_big_max_2MB');
            $_SESSION['msg_type'] = "error";
        } else {
            // check image settings
            $file = file_get_contents($_FILES["file"]["tmp_name"]);
            $type = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            // encode image
            $file = base64_encode($file);
            $img = new MongoDB\BSON\Binary($file, MongoDB\BSON\Binary::TYPE_GENERIC);
            // first: delete old image, then: insert new one
            $updateResult = $osiris->organizations->updateOne(
                ['_id' => $mongo_id],
                ['$set' => ['image' => [
                    'data' => $img,
                    'type' => $type,
                    'extension' => $type,
                    'uploaded_by' => $_SESSION['username'],
                    'uploaded' => date('Y-m-d')
                ]]]
            );
            $_SESSION['msg'] = lang('organizations.organisation_logo_uploaded_successfully');
            $_SESSION['msg_type'] = "success";
            header("Location: " . ROOTPATH . "/organizations/view/$id");
            die;
            // printMsg(lang('error.file_upload_generic'), "error");
        }
    } else if (isset($_POST['delete'])) {
        $osiris->organizations->updateOne(
            ['_id' => $mongo_id],
            ['$unset' => ['image' => ""]]
        );
        $_SESSION['msg'] = lang('organizations.organisation_logo_deleted');
        $_SESSION['msg_type'] = "success";
        header("Location: " . ROOTPATH . "/organizations/view/$id");
        die;
    }

    header("Location: " . ROOTPATH . "/organizations/view/$id");
    die;
});



Route::get('/organizations/image/(.*)', function ($id) {
    // print image
    include_once BASEPATH . "/php/init.php";
    $mongo_id = $DB->to_ObjectID($id);
    // get organization id    
    $organization = $osiris->organizations->findOne(['_id' => $mongo_id]);
    if (empty($organization)) {
        abortwith(404, lang('common.organization'), '/organizations');
    }
    include_once BASEPATH . "/php/Organization.php";
    echo Organization::getLogo($organization, "", "Logo of " . $organization['name'], $organization['type'] ?? "");
});
