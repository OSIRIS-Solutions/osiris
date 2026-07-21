<?php

/**
 * Routing file for the database migration
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


Route::get('/aggregate', function () {
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/aggregate.php";
    include BASEPATH . "/footer.php";
});

Route::post('/aggregate', function () {
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/aggregate.php";
    include BASEPATH . "/footer.php";

    $results = [];
    $error = null;
    $pipelineText = '';


    try {
        // Whitelist the collection(s) allowed for aggregation
        $allowedCollections = [
            'activities',
            'conferences',
            'countries',
            'events',
            'groups',
            'infrastructures',
            'journals',
            'organizations',
            'persons',
            'projects',
            'proposals'
        ];

        $collectionName = $_POST['collection'] ?? '';

        if (!in_array($collectionName, $allowedCollections, true)) {
            throw new Exception('Invalid collection: ' . htmlspecialchars($collectionName));
        }

        $pipelineText = trim($_POST['pipeline'] ?? '');

        $pipeline = json_decode($pipelineText, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg());
        }

        if (!is_array($pipeline)) {
            throw new Exception('Pipeline must be an array.');
        }

        if (count($pipeline) > 10) {
            throw new Exception('Too many pipeline stages.');
        }

        $forbiddenStages = [
            '$merge',
            '$out',
        ];

        foreach ($pipeline as $stage) {

            if (!is_array($stage) || count($stage) !== 1) {
                throw new Exception('Every stage must contain exactly one operator.');
            }

            $operator = array_key_first($stage);

            if (in_array($operator, $forbiddenStages, true)) {
                throw new Exception("Stage '{$operator}' is not allowed.");
            }
        }

        $collection = $osiris->selectCollection($collectionName);

        $cursor = $collection->aggregate(
            $pipeline,
            [
                'maxTimeMS' => 100000,      // 100 second timeout
                'allowDiskUse' => false,   // don't spill to disk
                'batchSize' => 100,
            ]
        );

        $results = iterator_to_array($cursor, false);

        // Don't let someone accidentally return millions of docs
        // if (count($results) > 1000) {
        //     $results = array_slice($results, 0, 1000);
        // }

    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
});