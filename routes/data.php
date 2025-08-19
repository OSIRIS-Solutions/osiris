<?php

/**
 * Routing file for data requests, modules and components
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


Route::get('/data/kdsf', function () {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');
    $json = file_get_contents(BASEPATH . "/data/kdsf-ffk.json");
    echo $json;
});

Route::get('/get-modules', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Modules.php";

    $form = array();
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $mongoid = $DB->to_ObjectID($_GET['id']);
        $form = $osiris->activities->findOne(['_id' => $mongoid]);
    }

    $Modules = new Modules($form, $_GET['copy'] ?? false);
    if (isset($_GET['modules'])) {
        $Modules->print_modules($_GET['modules']);
    } else {
        $Modules->print_all_modules();
    }
});

Route::get('/get-form', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Modules.php";

    $type = $_GET['type'] ?? null;
    $form = array();
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $mongoid = $DB->to_ObjectID($_GET['id']);
        $form = $osiris->activities->findOne(['_id' => $mongoid]);
        $type = $form['subtype'] ?? $type;
    }

    if (empty($type)) {
        http_response_code(400);
        echo 'Type parameter is required';
        return;
    }
    $Modules = new Modules($form, $_GET['copy'] ?? false);
    $Modules->print_form($type);
});


Route::get('/get-module/(.*)', function ($key) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Modules.php";
    $Modules = new Modules();
    $vals = $Modules->all_modules[$key] ?? null;
    $custom = false;
    if (empty($vals)) {
        // check if it is a custom field
        $field = $osiris->adminFields->findOne(['id' => $key]);
        if (!empty($field)) {
            $custom = true;
            $vals = [
                'name' => $field['name'] ?? $key,
                'name_de' => $field['name_de'] ?? null,
                'description' => $field['description'] ?? '',
                'description_de' => $field['description_de'] ?? null,
                'fields' => [$key]
            ];
        } else {
            http_response_code(404);
            echo 'Module not found';
            return;
        }
    }

    if ($key == 'journal') {
        // get any journal from collection
        $journal = $osiris->journals->findOne();
        $Modules->form['journal'] = $journal['journal'];
        $Modules->form['journal_id'] = strval($journal['_id']);
    } elseif ($key == 'teaching-course') {
        $module = $osiris->teaching->findOne();
        $Modules->form['module'] = $module['module'];
        $Modules->form['module_id'] = strval($module['_id']);
    } else {
        $Modules->set($vals['fields']);
    }

    if (isset($_GET['description'])) {
?>
        <div class="search-text">
            <h4 class="mt-0">
                <?= lang($vals['name'], $vals['name_de']) ?>
                <span class="code font-size-16 border ml-10"><?= $key ?></span>
            </h4>
            <?php if ($custom) { ?>
                <b>
                    <i class="ph ph-textbox"></i>
                    <?= lang('Custom field', 'Benutzerdefiniertes Feld') ?>
                </b>
            <?php } ?>

            <p class="text-muted ">
                <?= lang($vals['description'] ?? '', $vals['description_de'] ?? null) ?>
            </p>
            <p>
                <?= lang('Saved fields', 'Gespeicherte Felder') ?>:
                <?php foreach ($vals['fields'] as $f => $_) { ?>
                    <code class="badge primary"><?= $f ?></code>
                <?php } ?>
            </p>
        </div>
    <?php
    }

    ?>
    <div class="<?= $key == 'event-select' ? 'w-800' : '' ?> my-10 border p-10 rounded bg-light">
        <?php
        if (!$custom) {
            $Modules->print_module($key);
        } else {
            $Modules->custom_field($key);
        }
        ?>
    </div>
<?php

});


Route::get('/components/([A-Za-z0-9\-]*)', function ($path) {
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/components/$path.php";
});
