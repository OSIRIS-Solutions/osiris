<?php

/**
 * Routing file for all static contents
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


Route::get('/impress', function () {
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/impressum.html";
    include BASEPATH . "/footer.php";
});

Route::get('/new-stuff', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/MyParsedown.php";

    // update users last version if necessary
    if (isset($USER) && !empty($USER)) {
        if (!isset($USER['lastversion']) || $USER['lastversion'] !== OSIRIS_VERSION) {
            $updateResult = $osiris->persons->updateOne(
                ['username' => $_SESSION['username']],
                ['$set' => ['lastversion' => OSIRIS_VERSION]]
            );
            // reset last notification check
            $_SESSION['last_notification_check'] = 0;
        }
    }

    $breadcrumb = [
        ['name' => lang('News', 'Neuigkeiten')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news.php";
    include BASEPATH . "/footer.php";
});


Route::get('/about', function () {
    $breadcrumb = [
        ['name' => lang('About OSIRIS', 'Über OSIRIS')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/about.php";
    include BASEPATH . "/footer.php";
});


Route::get('/license', function () {

    $breadcrumb = [
        ['name' => lang('License', 'Lizenz')]
    ];

    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/license.html";
    include BASEPATH . "/footer.php";
});
