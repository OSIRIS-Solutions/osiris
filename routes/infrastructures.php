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
        ['name' => lang("Infrastructures", "Infrastrukturen")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/infrastructures/statistics', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
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
        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
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
        $mongo_id = $DB->to_ObjectID($id);
        $infrastructure = $osiris->infrastructures->findOne(['_id' => $mongo_id]);
    } else {
        $infrastructure = $osiris->infrastructures->findOne(['id' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($infrastructure)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
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

    if (!$Settings->hasPermission('infrastructures.edit')) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
        die;
    }

    global $form;

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->infrastructures->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->infrastructures->findOne(['name' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
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

    if (!$Settings->hasPermission('infrastructures.edit')) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
        die;
    }

    global $form;
    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->infrastructures->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->infrastructures->findOne(['name' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    $breadcrumb = [
        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
        ['name' => $form['name'], 'path' => "/infrastructures/view/$id"],
        ['name' => lang("Persons", "Personen")]
    ];

    include_once BASEPATH . "/php/Infrastructure.php";
    $Infra = new Infrastructure();

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/persons.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/infrastructures/year/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    global $form;

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->infrastructures->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->infrastructures->findOne(['name' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }
    if (empty($form)) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=not-found");
        die;
    }
    if (!$Settings->hasPermission('infrastructures.edit')) {
        // check if person is part of the infrastructure and is set as reporter
        $permission = false;
        foreach ($form['persons'] ?? [] as $person) {
            if ($person['user'] == $_SESSION['username'] && $person['reporter']) {
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
        ['name' => lang('Infrastructures', 'Infrastrukturen'), 'path' => "/infrastructures"],
        ['name' => $form['name'], 'path' => "/infrastructures/view/$id"],
        ['name' => lang("Year Statistics", "Jahresstatistik")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/infrastructures/year.php";
    include BASEPATH . "/footer.php";
}, 'login');


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


// Route::post('/crud/infrastructures/upload/([A-Za-z0-9]*)', function ($id) {
//     include_once BASEPATH . "/php/init.php";

//     if (!$Settings->hasPermission('infrastructures.edit')) {
//         header("Location: " . ROOTPATH . "/infrastructures?msg=no-permission");
//         die;
//     }

//     $target_dir = BASEPATH . "/uploads/";
//     if (!is_writable($target_dir)) {
//         die("Upload directory $target_dir is unwritable. Please contact admin.");
//     }
//     $target_dir .= "infrastructures/";

//     if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {

//         if (!file_exists($target_dir) || !is_dir($target_dir)) {
//             mkdir($target_dir, 0777);
//         }
//         // random filename
//         $filename = $id . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
//         // $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
//         $filesize = $_FILES["file"]["size"];
//         $values['image'] = "infrastructures/" . $filename;

//         if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
//             $errorMsg = match ($_FILES['file']['error']) {
//                 1 => lang('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini'),
//                 2 => lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt."),
//                 3 => lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.'),
//                 4 => lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.'),
//                 6 => lang('Missing a temporary folder.', 'Der temporäre Ordner fehlt.'),
//                 7 => lang('Failed to write file to disk.', 'Datei konnte nicht auf die Festplatte geschrieben werden.'),
//                 8 => lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.'),
//                 default => lang('Something went wrong.', 'Etwas ist schiefgelaufen.') . " (" . $_FILES['file']['error'] . ")"
//             };
//             $_SESSION['msg'] = $errorMsg;
//         } else if ($filesize > 2000000) {
//             $_SESSION['msg'] = lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt.");
//         } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
//             $osiris->infrastructures->updateOne(
//                 ['_id' => $DB->to_ObjectID($id)],
//                 ['$set' => $values]
//             );
//             $_SESSION['msg'] = lang("The file $filename has been uploaded.", "Die Datei <q>$filename</q> wurde hochgeladen.");
//         } else {
//             $_SESSION['msg'] = lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload.");
//         }
//     } else if (isset($_POST['delete'])) {
//         $filename = $_POST['delete'];
//         if (file_exists($target_dir . $filename)) {
//             // Use unlink() function to delete a file
//             if (!unlink($target_dir . $filename)) {
//                 $_SESSION['msg'] = lang("$filename cannot be deleted due to an error.", "$filename kann nicht gelöscht werden, da ein Fehler aufgetreten ist.");
//             } else {
//                 $_SESSION['msg'] = lang("$filename has been deleted.", "$filename wurde gelöscht.");
//             }
//         }
//     }
//     header("Location: " . ROOTPATH . "/infrastructures/view/$id");
// });


Route::post('/crud/infrastructures/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('infrastructures.edit')) {
        header("Location: " . ROOTPATH . "/infrastructures?msg=no-permission");
        die;
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


Route::post('/crud/infrastructures/year/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('infrastructures.edit')) {
        header("Location: " . ROOTPATH . "/infrastructures/view/$id?msg=no-permission");
        die;
    }

    if (!isset($_POST['values'])) die("no values given");
    $values = $_POST['values'];
    if (!isset($_POST['values']['year'])) die("no year given");

    $collection = $osiris->infrastructures;

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


Route::post('/crud/infrastructures/update-persons/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Infrastructure.php";
    $Infra = new Infrastructure();

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
