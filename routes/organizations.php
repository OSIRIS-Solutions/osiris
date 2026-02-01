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

    if (!isset($_POST['values']) || empty($_POST['values'])) die("no values given");
    $collection = $osiris->organizations;

    $values = validateValues($_POST['values'], $DB);
    if (empty($values['name'])) {
        echo json_encode([
            'msg' => lang("Organization name is required.", "Organisationsname ist erforderlich."),
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
        'id' => strval($new_id),
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


Route::post('/crud/organizations/upload-picture/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $mongo_id = $DB->to_ObjectID($id);
    // get organization id    
    $organization = $osiris->organizations->findOne(['_id' => $mongo_id]);
    if (empty($organization)) {
        header("Location: " . ROOTPATH . "/organizations/view/$id?msg=not-found");
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
            $_SESSION['msg'] = lang("Organisation logo uploaded successfully.", "Organisations-Logo erfolgreich hochgeladen.");
            $_SESSION['msg_type'] = "success";
            header("Location: " . ROOTPATH . "/organizations/view/$id");
            die;
            // printMsg(lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload."), "error");
        }
    } else if (isset($_POST['delete'])) {
        $osiris->organizations->updateOne(
            ['_id' => $mongo_id],
            ['$unset' => ['image' => ""]]
        );
        $_SESSION['msg'] = lang("Organisation logo deleted.", "Organisations-Logo gelöscht.");
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
        header("Location: " . ROOTPATH . "/organizations/view/$id?msg=not-found");
        die;
    }
    include_once BASEPATH . "/php/Organization.php";
    echo Organization::getLogo($organization, "", "Logo of " . $organization['name'], $organization['type'] ?? "");
});