<?php
/**
 * Routing file for database manipulations
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


Route::get('/rerender', function () {
    set_time_limit(6000);
    include_once BASEPATH . "/php/Render.php";
    renderActivities();
    echo "Done.";
});

Route::get('/rerender-projects', function () {
    set_time_limit(6000);
    include_once BASEPATH . "/php/Render.php";
    renderAuthorUnitsProjects();
    echo "Done.";
});

Route::get('/rerender-units/?(.*)', function ($username) {
    set_time_limit(6000);
    include_once BASEPATH . "/php/Render.php";
    $filter = [];
    if (!empty($username)) $filter['authors.user'] = $username;
    renderAuthorUnitsMany($filter);
    echo "Done.";
});

Route::get('/check-duplicate-id', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_GET['type']) || !isset($_GET['id'])) die('false');
    if ($_GET['type'] != 'doi' && $_GET['type'] != 'pubmed') die('false');

    $form = $osiris->activities->findOne([$_GET['type'] => $_GET['id']]);
    if (empty($form)) die('false');
    echo 'true';
});

Route::get('/check-duplicate', function () {
    include_once BASEPATH . "/php/init.php";

    $values = $_GET['values'] ?? array();
    if (empty($values)) die('false');

    $search = [];
    if (isset($values['title']) && !empty($values['title'])) $search['title'] = new \MongoDB\BSON\Regex(preg_quote($values['title']), 'i');
    else die('false');

    if (isset($values['year']) && !empty($values['year'])) $search['year'] = intval($values['year']);
    else die('false');

    if (isset($values['month']) && !empty($values['month'])) $search['month'] = intval($values['month']);
    else die('false');

    if (isset($values['type']) && !empty($values['type'])) $search['type'] = trim($values['type']);
    else die('false');

    if (isset($values['subtype']) && !empty($values['subtype'])) $search['subtype'] = trim($values['subtype']);
    else die('false');

    // dump($search, true);
    $doc = $osiris->activities->findOne($search);

    // dump($doc, true);
    if (empty($doc)) die('false');

    // $format = new Document();
    // $format->setDocument($doc);
    // echo $format->format();
    echo $doc['rendered']['web'] ?? '';
});


Route::get('/settings', function () {
    include_once BASEPATH . "/php/init.php";

    $file_name = BASEPATH . "/settings.json";
    if (!file_exists($file_name)) {
        $file_name = BASEPATH . "/settings.default.json";
    }
    $json = file_get_contents($file_name);
    echo $json;
});
