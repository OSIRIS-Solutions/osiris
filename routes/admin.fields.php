<?php

/**
 * Routing file for custom fields admin settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/admin/fields', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) die('You have no permission to be here.');

    $breadcrumb = [
        ['name' => lang('Manage content', 'Inhalte verwalten'), 'path' => '/admin'],
        ['name' => lang("Custom fields")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/fields.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/admin/fields/new', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) die('You have no permission to be here.');

    $user = $_SESSION['username'];
    $form = [];
    $breadcrumb = [
        ['name' => lang("fields", "Kategorien"), 'path' => "/admin/fields"],
        ['name' => lang("New", "Neu")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/field.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/admin/fields/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) die('You have no permission to be here.');

    $user = $_SESSION['username'];

    $category = $osiris->adminFields->findOne(['id' => $id]);
    if (empty($category)) {
        abortwith(404, lang("Custom Field", "Benutzerdefiniertes Feld"), '/admin/fields');
    }
    $name = lang($category['name'], $category['name_de']);
    $breadcrumb = [
        ['name' => lang('Manage content', 'Inhalte verwalten'), 'path' => '/admin'],
        ['name' => lang("Custom Fields"), 'path' => "/admin/fields"],
        ['name' => $name]
    ];

    global $form;
    $form = DB::doc2Arr($category);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/admin/field.php";
    include BASEPATH . "/footer.php";
}, 'login');



/**
 * CRUD routes
 */

Route::post('/crud/fields/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) die('You have no permission to be here.');

    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));

    $values = validateValues($_POST['values'], $DB);

    if (isset($values['values']) && !empty($values['values'])) {
        $en = $values['values'];
        $de = $values['values_de'] ?? $en;

        $values['values'] = [];
        foreach ($en as $i => $e) {
            $values['values'][] = [
                $e,
                $de[$i] ?? $e
            ];
        }
    }

    // check if category ID already exists:
    $category_exist = $osiris->adminFields->findOne(['id' => $values['id']]);
    if (!empty($category_exist)) {
        $_SESSION['msg'] = lang("Field Name does already exist.", "Feldname existiert bereits.");
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/admin/fields/new");
        die();
    }

    $osiris->adminFields->insertOne($values);

    $_SESSION['msg'] = lang("Custom field created successfully.", "Benutzerdefiniertes Feld erfolgreich erstellt.");
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/admin/fields");
});

Route::post('/crud/fields/update/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) die('You have no permission to be here.');

    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));

    $values = validateValues($_POST['values'], $DB);
    $values['id'] = $id;

    
    if (isset($values['values']) && !empty($values['values'])) {
        $en = $values['values'];
        $de = $values['values_de'] ?? $en;

        $values['values'] = [];
        foreach ($en as $i => $e) {
            $values['values'][] = [
                $e,
                $de[$i] ?? $e
            ];
        }
    }

    $updateResult = $osiris->adminFields->updateOne(
        ['id' => $id],
        ['$set' => $values]
    );

    $_SESSION['msg'] = lang("Custom field updated successfully.", "Benutzerdefiniertes Feld erfolgreich aktualisiert.");
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/admin/fields/$id");
});


Route::post('/crud/fields/delete/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('admin.see')) die('You have no permission to be here.');

    $mongo_id = DB::to_ObjectID($id);
    $updateResult = $osiris->adminFields->deleteOne(
        ['_id' => $mongo_id]
    );

    $_SESSION['msg'] = lang("Custom field deleted successfully.", "Benutzerdefiniertes Feld erfolgreich gelöscht.");
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/admin/fields");
});
