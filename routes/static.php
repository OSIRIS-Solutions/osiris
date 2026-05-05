<?php

/**
 * Routing file for all static contents
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


Route::get('/impress', function () {
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/impressum.php";
    include BASEPATH . "/footer.php";
});

Route::get('/privacy', function () {
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/privacy.php";
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


Route::get('/license', function () {

    $breadcrumb = [
        ['name' => lang('License', 'Lizenz')]
    ];

    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/license.html";
    include BASEPATH . "/footer.php";
});


Route::get('/accessibility', function () {

    $breadcrumb = [
        ['name' => lang('Accessibility', 'Barrierefreiheit')]
    ];

    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/accessibility.php";
    include BASEPATH . "/footer.php";
});


Route::get('/image/(.*)', function ($user) {
    include_once BASEPATH . "/php/init.php";
    $user = urldecode($user);
    $img = $osiris->userImages->findOne(['user' => $user]);
    if (empty($img)) {
        $img = file_get_contents(BASEPATH . "/img/no-photo.png");
        $type = 'image/png';
    } else {
        $type = $img['ext'];
        if ($img['ext'] == 'svg') {
            $type = 'image/svg+xml';
        } else {
            $type = 'image/' . $img['ext'];
        }
        $img = $img['img']->getData();
        //if image is base64 encoded
        // if (str_starts_with($img, '/')) {
        //     $img = explode(',', $img)[1];
        // }

        $img = base64_decode($img);
    }
    header('Content-Type: ' . $type);
    echo $img;
    die;
});