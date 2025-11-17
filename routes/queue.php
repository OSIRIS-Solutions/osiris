<?php

/**
 * Routing file for the OpenAlex queue
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

Route::get('/queue/(user|editor)', function ($role) {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('Queue', 'Warteschlange')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/queue.php";
    include BASEPATH . "/footer.php";
});


Route::post('/queue/(accept|decline)/([a-zA-Z0-9]*)', function ($type, $id) {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Render.php";

    $mongo_id = $DB->to_ObjectID($id);

    if ($type == 'accept') {

        $new = $osiris->queue->findOne(['_id' => $mongo_id]);
        unset($new['_id']);
        foreach ($new['authors'] ?? array() as $i => $a) {
            if ($a['user'] ?? '' == $_SESSION['username']) {
                $new['authors'][$i]['approved'] = true;
            }
        }
        $new['created'] = date('Y-m-d');
        $new['created_by'] = $_SESSION['username'];

        $insertOneResult = $osiris->activities->insertOne($new);
        $new_id = $insertOneResult->getInsertedId();
        renderActivities(['_id' => $new_id]);

        $osiris->queue->deleteOne(['_id' => $mongo_id]);
        echo $new_id;
    } else {
        $osiris->queue->updateOne(
            ['_id' => $mongo_id],
            [
                '$set' => [
                    'declined' => true, 'declined_by' => $_SESSION['username']
                ]
            ]

        );
    }
});
