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
        ['name' => lang("Organizations", "Organisationen")]
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
        ['name' => lang('Organizations', 'Organisationen'), 'path' => "/organizations"],
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
        ['name' => lang('Organizations', 'Organisationen'), 'path' => "/organizations"],
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
        ['name' => lang('Organizations', 'Organisationen'), 'path' => "/organizations"],
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

    $filter = [
        'name' => $values['name'],
        'country' => $values['country'] ?? ''
    ];
    $ror = $values['ror'] ?? $values['ror_id'] ?? '';
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
    unset($values['ror_id']);
    unset($values['chosen']);
    unset($values['id']);

    // check if organization id already exists:
    $exist = $collection->findOne($filter);
    if (!empty($exist)) {
        if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
            $red = str_replace("*", $id, $_POST['redirect']);
            header("Location: " . $red . "?msg=organization does already exist.");
        } else {
            echo json_encode([
                'msg' => "Organization ID already exists.",
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


Route::post('/crud/organizations/upload/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('organizations.edit')) {
        header("Location: " . ROOTPATH . "/organizations?msg=no-permission");
        die;
    }

    $target_dir = BASEPATH . "/uploads/";
    if (!is_writable($target_dir)) {
        die("Upload directory $target_dir is unwritable. Please contact admin.");
    }
    $target_dir .= "organizations/";

    if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {

        if (!file_exists($target_dir) || !is_dir($target_dir)) {
            mkdir($target_dir, 0777);
        }
        // random filename
        $filename = $id . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        // $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES["file"]["size"];
        $values['image'] = "organizations/" . $filename;

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini'),
                2 => lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt."),
                3 => lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.'),
                4 => lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.'),
                6 => lang('Missing a temporary folder.', 'Der temporäre Ordner fehlt.'),
                7 => lang('Failed to write file to disk.', 'Datei konnte nicht auf die Festplatte geschrieben werden.'),
                8 => lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.'),
                default => lang('Something went wrong.', 'Etwas ist schiefgelaufen.') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
        } else if ($filesize > 2000000) {
            $_SESSION['msg'] = lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt.");
        } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
            $osiris->organizations->updateOne(
                ['_id' => $DB->to_ObjectID($id)],
                ['$set' => $values]
            );
            $_SESSION['msg'] = lang("The file $filename has been uploaded.", "Die Datei <q>$filename</q> wurde hochgeladen.");
        } else {
            $_SESSION['msg'] = lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload.");
        }
    } else if (isset($_POST['delete'])) {
        $filename = $_POST['delete'];
        if (file_exists($target_dir . $filename)) {
            // Use unlink() function to delete a file
            if (!unlink($target_dir . $filename)) {
                $_SESSION['msg'] = lang("$filename cannot be deleted due to an error.", "$filename kann nicht gelöscht werden, da ein Fehler aufgetreten ist.");
            } else {
                $_SESSION['msg'] = lang("$filename has been deleted.", "$filename wurde gelöscht.");
            }
        }
    }
    header("Location: " . ROOTPATH . "/organizations/view/$id");
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


Route::post('/crud/organizations/year/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('organizations.edit')) {
        header("Location: " . ROOTPATH . "/organizations/view/$id?msg=no-permission");
        die;
    }

    if (!isset($_POST['values'])) die("no values given");
    $values = $_POST['values'];
    if (!isset($_POST['values']['year'])) die("no year given");

    $collection = $osiris->organizations;

    $year = intval($_POST['values']['year']);

    $values = [
        'year' => $year,
        'internal' => $values['internal'] ?? 0,
        'national' => $values['national'] ?? 0,
        'international' => $values['international'] ?? 0,
        'hours' => $values['hours'] ?? 0,
        'accesses' => $values['accesses'] ?? 0
    ];

    $id = $DB->to_ObjectID($id);

    // remove year if exists
    $collection->updateOne(
        ['_id' => $id],
        ['$pull' => ['statistics' => ['year' => $year]]]
    );

    // add year
    $updateResult = $collection->updateOne(
        ['_id' => $id],
        ['$push' => ['statistics' => $values]]
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


// Route::post('/crud/organizations/delete/([A-Za-z0-9]*)', function ($id) {
//     include_once BASEPATH . "/php/init.php";

//     if (!$Settings->hasPermission('organizations.delete')) {
//         header("Location: " . ROOTPATH . "/organizations?msg=no-permission");
//         die;
//     }

//     $organization = $osiris->organizations->findOne(['_id' => $DB->to_ObjectID($id)]);

//     // remove organization name from activities
//     $osiris->activities->updateMany(
//         ['organizations' => $organization['id']],
//         ['$pull' => ['organizations' => $organization['id']]]
//     );

//     // remove organization
//     $osiris->organizations->deleteOne(
//         ['_id' => $DB::to_ObjectID($id)]
//     );

//     $_SESSION['msg'] = lang("Organization has been deleted successfully.", "Infrastruktur wurde erfolgreich gelöscht.");
//     header("Location: " . ROOTPATH . "/organizations");
// });
