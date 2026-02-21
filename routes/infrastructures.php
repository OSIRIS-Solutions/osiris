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
        include_once BASEPATH . "/header.php";
        echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
        include_once BASEPATH . "/footer.php";
        die();
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
    if (DB::is_ObjectID($id)) {
        $osiris_id = $DB->to_ObjectID($id);
        $infrastructure = $osiris->infrastructures->findOne(['_id' => $osiris_id]);
    } else {
        $infrastructure = $osiris->infrastructures->findOne(['id' => $id]);
        $id = strval($infrastructure['_id'] ?? '');
    }

    if (!$Settings->hasPermission('infrastructures.view')) {
        $permission = false;
        foreach ($infrastructure['persons'] ?? [] as $person) {
            if ($person['user'] == $_SESSION['username']) {
                $permission = true;
                break;
            }
        }
        if (!$permission) {
            include_once BASEPATH . "/header.php";
            echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
            include_once BASEPATH . "/footer.php";
            die();
        }
    }
    if (empty($infrastructure)) {
        include BASEPATH . "/header.php";
        echo notFoundPage($Settings->infrastructureLabel(), '/infrastructures');
        include BASEPATH . "/footer.php";
        die();
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
        include_once BASEPATH . "/header.php";
        echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
        include_once BASEPATH . "/footer.php";
        die();
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
        include BASEPATH . "/header.php";
        echo notFoundPage($Settings->infrastructureLabel(), '/infrastructures');
        include BASEPATH . "/footer.php";
        die();
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
            include_once BASEPATH . "/header.php";
            echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
            include_once BASEPATH . "/footer.php";
            die();
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
        include_once BASEPATH . "/header.php";
        echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
        include_once BASEPATH . "/footer.php";
        die();
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
        include BASEPATH . "/header.php";
        echo notFoundPage($Settings->infrastructureLabel(), '/infrastructures');
        include BASEPATH . "/footer.php";
        die();
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
            include BASEPATH . "/header.php";
            echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures');
            include BASEPATH . "/footer.php";
            die();
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



/**
 * CRUD routes
 */

Route::post('/crud/infrastructures/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('infrastructures.edit')) {
        include_once BASEPATH . "/header.php";
        echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures');
        include_once BASEPATH . "/footer.php";
        die();
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
            include_once BASEPATH . "/header.php";
            echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures');
            include_once BASEPATH . "/footer.php";
            die();
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
        include_once BASEPATH . "/header.php";
        echo notFoundPage($Settings->infrastructureLabel(), '/infrastructures');
        include_once BASEPATH . "/footer.php";
        die();
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
        $_SESSION['msg'] = lang("Statistics updated successfully", "Statistiken erfolgreich aktualisiert");
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect'] . "#statistics");
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
            include_once BASEPATH . "/header.php";
            echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
            include_once BASEPATH . "/footer.php";
            die();
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
        include_once BASEPATH . "/header.php";
        echo noPermissionPage($Settings->infrastructureLabel(), '/infrastructures/view/' . $id);
        include_once BASEPATH . "/footer.php";
        die();
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
        include_once BASEPATH . "/header.php";
        echo notFoundPage($Settings->infrastructureLabel(), '/infrastructures');
        include_once BASEPATH . "/footer.php";
        die();
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
        include_once BASEPATH . "/header.php";
        echo notFoundPage($Settings->infrastructureLabel(), '/infrastructures');
        include_once BASEPATH . "/footer.php";
        die;
    }
    include_once BASEPATH . "/php/Infrastructure.php";
    echo Infrastructure::getLogo($infrastructure, "", "Logo of " . $infrastructure['name'], $infrastructure['type'] ?? "");
});
