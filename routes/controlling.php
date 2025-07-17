<?php

/**
 * Routing file for controlling pages
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


Route::get('/controlling', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('activities.lock')) die('You have no permission to be here.');
    $breadcrumb = [
        ['name' => lang("Controlling")]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/controlling.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::post('/controlling', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('activities.lock')) die('You have no permission to be here.');

    $breadcrumb = [
        ['name' => lang("Controlling")]
    ];

    include BASEPATH . "/header.php";

    $changes = 0;
    if (isset($_POST['action']) && isset($_POST['start']) && isset($_POST['end'])) {

        $lock = ($_POST['action'] == 'lock');
        // dump($lock);

        $cursor = $DB->get_reportable_activities($_POST['start'], $_POST['end']);
        foreach ($cursor as $doc) {
            // dump($doc['title'] ?? 'REVIEW');

            if ($lock) {
                // in progress stuff is not locked
                if ( in_array($doc['subtype'], $Settings->continuousTypes) && is_null($doc['end'])) {
                    continue;
                }
                if ($doc['type'] == "students" && isset($doc['status']) && $doc['status'] == 'in progress') {
                    continue;
                }
            }

            $updateResult = $osiris->activities->updateOne(
                ['_id' => $doc['_id']],
                ['$set' => ['locked' => $lock]]
            );

            $changes += $updateResult->getModifiedCount();
        }
        // construct output message
        $header = $lock ? lang('Locked activities.', 'Aktivitäten gesperrt.') : lang('Unlocked activities.', 'Aktivitäten entsperrt.');
        $text = lang(
            "Successfully changed the status of $changes activities.",
            "Es wurde erfolgreich der Status von $changes Aktivitäten geändert."
        );
        printMsg($text, 'success', $header);
    } else {
        echo 'Nothing to do.';
    }

    include BASEPATH . "/pages/controlling.php";
    include BASEPATH . "/footer.php";
}, 'login');
