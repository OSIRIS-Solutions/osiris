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
        ['name' => lang("Teaching", "Lehrveranstaltungen")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/teaching/list.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/teaching/new', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('teaching.edit')) {
        abortwith(403, lang("You do not have permission to edit teaching modules.", "Du hast keine Berechtigung, Lehrveranstaltungen zu bearbeiten."), "/teaching", lang('Go back to teaching modules', 'Zurück zu Lehrveranstaltungen'));
    }
    $breadcrumb = [
        ['name' => lang('Teaching', 'Lehrveranstaltungen'), 'path' => '/teaching'],
        ['name' => lang('New teaching module', 'Neues Lehrmodul')]
    ];

    $form = [];
    $new = true;

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/teaching/edit.php";
    include BASEPATH . "/footer.php";
});


Route::get('/teaching/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Document.php";
    $Document = new Document();
    $mongo_id = DB::to_ObjectID($id);
    // get teaching module
    $module = $osiris->teaching->findOne(['_id' => $mongo_id]);
    if (!$module) {
        abortwith(404, lang("Teaching module", "Lehrveranstaltung"), "/teaching");
    }

    $breadcrumb = [
        ['name' => lang('Teaching', 'Lehrveranstaltungen'), 'path' => '/teaching'],
        ['name' => $module['title']]
    ];

    $activities = $osiris->activities->find(['module_id' => $id], ['sort' => ['start_date' => -1]])->toArray();

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/teaching/view.php";
    include BASEPATH . "/footer.php";
});


Route::get('/teaching/edit/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('teaching.edit')) {
        abortwith(403, lang("You do not have permission to edit teaching modules.", "Du hast keine Berechtigung, Lehrveranstaltungen zu bearbeiten."), "/teaching/view/$id", lang('Go back to the module', 'Zurück zu der Lehrveranstaltung'));
    }
    $mongo_id = DB::to_ObjectID($id);
    // get teaching module
    $new = false;
    global $form;
    $form = $osiris->teaching->findOne(['_id' => $mongo_id]);
    if (!$form) {
        abortwith(404, lang("Teaching module", "Lehrveranstaltung"), "/teaching");
    }

    $breadcrumb = [
        ['name' => lang('Teaching', 'Lehrveranstaltungen'), 'path' => '/teaching'],
        ['name' => $form['title'], 'path' => "/teaching/view/$id"],
        ['name' => lang("Edit", "Bearbeiten")]
    ];

    $activities = $osiris->activities->find(['module_id' => $id], ['sort' => ['start_date' => -1]])->toArray();
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/teaching/edit.php";
    include BASEPATH . "/footer.php";
});


Route::get('/teaching/statistics', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
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
    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));
    if (!$Settings->hasPermission('teaching.edit')) {
        abortwith(403, lang("You do not have permission to edit teaching modules.", "Du hast keine Berechtigung, Lehrveranstaltungen zu bearbeiten."), "/teaching", lang('Go back to teaching modules', 'Zurück zu Lehrveranstaltungen'));
    }
    $collection = $osiris->teaching;

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    $values['module'] = trim(strval($_POST['values']['module'] ?? ''));
    // check if module already exists:
    if (isset($values['module']) && !empty($values['module'])) {
        $exists = $osiris->teaching->count(['module' => $values['module']]);
        if ($exists > 0) {
            if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
                $_SESSION['msg'] = lang("Module with this module number already exists", "Modul mit dieser Modulnummer existiert bereits");
                $_SESSION['msg_type'] = "error";
                header("Location: " . ROOTPATH . '/teaching');
                die();
            }
            echo json_encode([
                'msg' => "Module with this module number already exists"
            ]);
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
        $_SESSION['msg'] = lang("Teaching module has been created successfully.", "Lehrveranstaltung wurde erfolgreich erstellt.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . $red);
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});


 Route::post('/crud/teaching/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('teaching.edit')) {
        abortwith(403, lang("You do not have permission to edit teaching modules.", "Du hast keine Berechtigung, Lehrveranstaltungen zu bearbeiten."), "/teaching/view/$id", lang('Go back to teaching module', 'Zurück zu der Lehrveranstaltung'));
    }

    $values = validateValues($_POST['values'], $DB);
    // add information on updating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];
    $mongo_id = $DB->to_ObjectID($id);
    $updateResult = $osiris->teaching->updateOne(
        ['_id' => $mongo_id],
        ['$set' => $values]
    );
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("Teaching module has been updated successfully.", "Lehrveranstaltung wurde erfolgreich aktualisiert.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'modified' => $updateResult->getModifiedCount()
    ]);
    
 });

Route::post('/crud/teaching/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('teaching.edit')) {
        abortwith(403, lang("You do not have permission to edit teaching modules.", "Du hast keine Berechtigung, Lehrveranstaltungen zu bearbeiten."), "/teaching/view/$id", lang('Go back to teaching module', 'Zurück zu der Lehrveranstaltung'));
    }
    //chack that no activities are connected
    $activities = $osiris->activities->count(['module_id' => strval($module['_id'])]);
    if ($activities != 0) {
        $_SESSION['msg'] = lang("Cannot delete teaching module when activities are still connected", "Lehrveranstaltung kann nicht gelöscht werden, wenn noch Aktivitäten damit verbunden sind");
        $_SESSION['msg_type'] = "error";
        header("Location: " . $_POST['redirect']);
        die();
    }

    // prepare id
    $id = $DB->to_ObjectID($id);
    $updateResult = $osiris->teaching->deleteOne(['_id' => $id]);
    $deletedCount = $updateResult->getDeletedCount();

    // addUserActivity('delete');
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang("Teaching module has been deleted successfully.", "Lehrveranstaltung wurde erfolgreich gelöscht.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect'] );
        die();
    }
    echo json_encode([
        'deleted' => $deletedCount
    ]);
});
