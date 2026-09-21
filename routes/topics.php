<?php

/**
 * Routing file for topics
 * Created in cooperation with bicc
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.8
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/topics', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => $Settings->topicLabel(), 'path' => "/topics"]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/topics/topics.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/topics/new', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('topics.edit')) {
        abortwith(403, lang('topics.you_do_not_have_permission_to_create_a_new_topics'), "/topics", lang('topics.go_back_to_topics'));
    }
    $breadcrumb = [
        ['name' => $Settings->topicLabel(), 'path' => "/topics"],
        ['name' => lang('common.new')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/topics/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::get('/topics/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $topic = $osiris->topics->findOne(['_id' => $mongo_id]);
    } else {
        $topic = $osiris->topics->findOne(['id' => $id]);
        $id = strval($topic['_id'] ?? '');
    }
    if (empty($topic)) {
        abortwith(404, $Settings->topicLabel(), "/topics");
    }
    $breadcrumb = [
        ['name' => $Settings->topicLabel(), 'path' => "/topics"],
        ['name' => $topic['name']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/topics/topic.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/topics/edit/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('topics.edit')) {
        abortwith(403, lang('topics.you_do_not_have_permission_to_edit_topics'), "/topics/view/$id", lang('topics.go_back_to_topic'));
    }

    global $form;

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $form = $osiris->topics->findOne(['_id' => $mongo_id]);
    } else {
        $form = $osiris->topics->findOne(['name' => $id]);
        $id = strval($form['_id'] ?? '');
    }
    if (empty($form)) {
        abortwith(404, $Settings->topicLabel(), "/topics");
    }
    $breadcrumb = [
        ['name' => $Settings->topicLabel(), 'path' => "/topics"],
        ['name' => $form['name'], 'path' => "/topics/view/$id"],
        ['name' => lang('action.edit')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/topics/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');

/**
 * CRUD routes
 */

Route::post('/crud/topics/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('topics.edit')) {
        abortwith(403, lang('topics.you_do_not_have_permission_to_create_a_new_topics'), "/topics", lang('topics.go_back_to_topics'));
    }

    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));
    $collection = $osiris->topics;

    $values = validateValues($_POST['values'], $DB);

    $id = $values['id'] ?? uniqid();

    // check if topic id already exists:
    $topic_exist = $collection->findOne(['id' => $id]);
    if (!empty($topic_exist)) {
        $_SESSION['msg'] = $Settings->topicLabel() . " " . lang('topics.with_this_id_already_exists');
        $_SESSION['msg_type'] = "error";
        header("Location: " . ROOTPATH . "/topics/new");
        die();
    }

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    $insertOneResult  = $collection->insertOne($values);
    $id = $insertOneResult->getInsertedId();

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $id, $_POST['redirect']);
        $_SESSION['msg'] = $Settings->topicLabel() . " " . lang('topics.has_been_created_successfully');
        $_SESSION['msg_type'] = "success";
        header("Location: " . $red);
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});


Route::post('/crud/topics/upload/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('topics.edit')) {
        abortwith(403, lang('topics.you_do_not_have_permission_to_edit_topics'), "/topics/view/$id", lang('topics.go_back_to_topic'));
    }

    $target_dir = BASEPATH . "/uploads/";
    if (!is_writable($target_dir)) {
        die("Upload directory $target_dir is unwritable. Please contact admin.");
    }
    $target_dir .= "topics/";

    if (isset($_FILES["file"]) && $_FILES["file"]["size"] > 0) {

        if (!file_exists($target_dir) || !is_dir($target_dir)) {
            mkdir($target_dir, 0777);
        }
        // random filename
        $filename = $id . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        // $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES["file"]["size"];
        $values['image'] = "topics/" . $filename;

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('error.file_upload_exceeds_limit'),
                2 => lang('error.file_upload_too_large', replace:['max' => '16 MB']),
                3 => lang('error.file_partially_uploaded'),
                4 => lang('error.no_file_uploaded'),
                6 => lang('error.file_upload_missing_temp'),
                7 => lang('error.file_upload_write_failed'),
                8 => lang('error.file_upload_stopped'),
                default => lang('error.something_went_wrong') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
            $_SESSION['msg_type'] = "error";
        } else if ($filesize > 2000000) {
            $_SESSION['msg'] = lang('error.file_too_big_max_2MB');
            $_SESSION['msg_type'] = "error";
        } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
            $osiris->topics->updateOne(
                ['_id' => $DB->to_ObjectID($id)],
                ['$set' => $values]
            );
            $_SESSION['msg'] = lang('common.the_file_filename_has_been_uploaded', replace: ['filename' => $filename]);
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = lang('error.file_upload_generic');
            $_SESSION['msg_type'] = "error";
        }
    } else if (isset($_POST['delete'])) {
        $filename = $_POST['delete'];
        if (file_exists($target_dir . $filename)) {
            // Use unlink() function to delete a file
            if (!unlink($target_dir . $filename)) {
                $_SESSION['msg'] = lang('common.filename_cannot_be_deleted_due_to_an_error', replace: ['filename' => $filename]);
                $_SESSION['msg_type'] = "error";
            } else {
                $_SESSION['msg'] = lang('common.filename_has_been_deleted', replace: ['filename' => $filename]);
                $_SESSION['msg_type'] = "success";
            }
        }
    }
    header("Location: " . ROOTPATH . "/topics/view/$id");
});


Route::post('/crud/topics/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('topics.edit')) {
        abortwith(403, lang('topics.you_do_not_have_permission_to_edit_topics'), "/topics/view/$id", lang('topics.go_back_to_topic'));
    }
    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));
    $collection = $osiris->topics;

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    $id = $DB->to_ObjectID($id);
    $updateResult = $collection->updateOne(
        ['_id' => $id],
        ['$set' => $values]
    );

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = $Settings->topicLabel() . " " . lang('topics.has_been_updated_successfully');
        $_SESSION['msg_type'] = "success";
        header("Location: " . $_POST['redirect']);
        die();
    }

    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
});


Route::post('/crud/topics/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->hasPermission('topics.delete')) {
        abortwith(403, lang('topics.you_do_not_have_permission_to_delete_topics'), "/topics", lang('topics.go_back_to_topics'));
    }

    $topic = $osiris->topics->findOne(['_id' => $DB->to_ObjectID($id)]);

    // remove topic name from activities
    $osiris->activities->updateMany(
        ['topics' => $topic['id']],
        ['$pull' => ['topics' => $topic['id']]]
    );
    // remove topic name from persons
    $osiris->persons->updateMany(
        ['topics' => $topic['id']],
        ['$pull' => ['topics' => $topic['id']]]
    );
    // remove topic name from projects
    $osiris->projects->updateMany(
        ['topics' => $topic['id']],
        ['$pull' => ['topics' => $topic['id']]]
    );

    // delete files if exist
    if (isset($topic['image'])) {
        $target_dir = BASEPATH . "/uploads/";
        $filename = $topic['image'];
        if (file_exists($target_dir . $filename)) {
            unlink($target_dir . $filename);
        }
    }

    // remove topic
    $osiris->topics->deleteOne(
        ['_id' => $DB::to_ObjectID($id)]
    );

    $_SESSION['msg'] = lang('topics.research_topic_has_been_deleted_successfully');
    $_SESSION['msg_type'] = "success";
    header("Location: " . ROOTPATH . "/topics");
});
