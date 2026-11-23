<?php
/**
 * Routing file for dashboard and visualisations
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/visualize', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => t('search.visualisation')]
    ];
    // include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/visualize.php";
    include BASEPATH . "/footer.php";
});

Route::get('/visualize/(\w*)', function ($page) {
    $names = [
        "coauthors" => t('common.coauthor_network'),
        "sunburst" => t('common.department_overview'),
        "departments" => t('search.department_network'),
        "openaccess" => t('journals.open_access'),
        "wordcloud" => t('common.word_cloud'),
        "map" => t('common.map'),
    ];
    if (!array_key_exists($page, $names)) {
        die("404");
    }
    $breadcrumb = [
        ['name' => t('search.visualisation'), 'path' => "/visualize"],
        ['name' => $names[$page]]
    ];
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Document.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/visualize-$page.php";
    include BASEPATH . "/footer.php";
});

Route::get('/dashboard', function () {
    $breadcrumb = [
        ['name' => t('common.dashboard')]
    ];
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/dashboard.php";

    if ($Settings->hasPermission('report.dashboard')) {
        // echo '<a href="' . ROOTPATH . '/controlling" class="btn danger lg float-right">'.lang("Lock activities", "Aktivitäten sperren").'</a>';

        echo '<h1 class="m-0">Controlling-Dashboard</h1>';
        include BASEPATH . "/pages/dashboard-controlling.php";
        include BASEPATH . "/pages/dashboard-scientist.php";
    } else {
        echo '<h1 class="m-0">' . t('search.scientist') . '-Dashboard</h1>';
        include BASEPATH . "/pages/dashboard-scientist.php";
    }
    include BASEPATH . "/footer.php";
});

// pivot table
Route::get('/pivot', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => t('search.pivot_table')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/pivot.php";
    include BASEPATH . "/footer.php";
});


// pivot table
Route::get('/trips', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => $Settings->tripLabel()]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/trips.php";
    include BASEPATH . "/footer.php";
});