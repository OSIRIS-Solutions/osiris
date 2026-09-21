<?php

/**
 * Routing file for organizational groups
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

function createGroupImageThumbnail(string $source, string $target, string $mime, int $maxSize = 800): bool
{
    if (!extension_loaded('gd') || !function_exists('imagewebp')) return false;

    $sourceImage = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($source),
        'image/png' => @imagecreatefrompng($source),
        'image/webp' => @imagecreatefromwebp($source),
        default => false,
    };
    if ($sourceImage === false) return false;

    $sourceWidth = imagesx($sourceImage);
    $sourceHeight = imagesy($sourceImage);
    $scale = min($maxSize / $sourceWidth, $maxSize / $sourceHeight, 1);
    $targetWidth = max(1, (int) round($sourceWidth * $scale));
    $targetHeight = max(1, (int) round($sourceHeight * $scale));
    $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

    imagealphablending($thumbnail, false);
    imagesavealpha($thumbnail, true);
    $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
    imagefilledrectangle($thumbnail, 0, 0, $targetWidth, $targetHeight, $transparent);

    $resampled = imagecopyresampled(
        $thumbnail,
        $sourceImage,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $sourceWidth,
        $sourceHeight
    );
    $saved = $resampled && imagewebp($thumbnail, $target, 82);

    imagedestroy($sourceImage);
    imagedestroy($thumbnail);
    return $saved;
}

function redirectFromGroupImage(string $groupId, string $message, string $type): void
{
    $_SESSION['msg'] = $message;
    $_SESSION['msg_type'] = $type;
    header("Location: " . ROOTPATH . "/groups/view/$groupId#images");
    die;
}

Route::get('/groups', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.units')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/groups.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/groups/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang('common.units'), 'path' => "/groups"],
        ['name' => lang('common.new')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/add.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/groups/view/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $group = $osiris->groups->findOne(['_id' => $mongo_id]);
        $id = $group['id'];
    } else {
        // wichtig für umlaute
        $group = $osiris->groups->findOne(['id' => $id]);
        // $id = strval($group['_id'] ?? '');
    }
    if (empty($group)) {
        abortwith(404, lang('common.unit'), '/groups');
    }
    $breadcrumb = [
        ['name' => lang('common.units'), 'path' => "/groups"],
        ['name' => $group['id']]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/group.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/groups/(edit|public)/(.*)', function ($page, $id) {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];

    if (DB::is_ObjectID($id)) {
        $mongo_id = $DB->to_ObjectID($id);
        $group = $osiris->groups->findOne(['_id' => $mongo_id]);
        $id = $group['id'];
    } else {
        // wichtig für umlaute
        $group = $osiris->groups->findOne(['id' => $id]);
        // $id = strval($group['_id'] ?? '');
    }
    if (empty($group)) {
        abortwith(404, lang('common.unit'), '/groups');
    }
    $breadcrumb = [
        ['name' => lang('common.units'), 'path' => "/groups"],
        ['name' =>  $group['id'], 'path' => "/groups/view/$id"],
    ];
    if ($page == 'edit') {
        $breadcrumb[] = ['name' => lang('action.edit')];
    }

    global $form;
    $form = DB::doc2Arr($group);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::post('/crud/groups/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));
    $collection = $osiris->groups;

    $values = validateValues($_POST['values'], $DB);

    // check if group name already exists:
    $group_exist = $collection->findOne(['id' => $values['id']]);
    if (!empty($group_exist)) {
        $_SESSION['msg'] = lang('people.group_id_does_already_exist');
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/groups/new");
        die();
    }

    // add information on creating process
    $values['created'] = date('Y-m-d');
    $values['created_by'] = $_SESSION['username'];

    if (!empty($values['parent'])) {
        $parent = $Groups->getGroup($values['parent']);
        if ($parent['color'] != '#000000') $values['color'] = $parent['color'];
    }

    if (isset($values['head'])) {
        foreach ($values['head'] as $head) {
            $osiris->persons->updateOne(
                ['username' => $head],
                ['$push' => [
                    "units" => [
                        'id' => uniqid(),
                        'unit' => $values['id'],
                        'start' => date('Y-m-d'),
                        'end' => null,
                        'scientific' => true
                    ]
                ]]
            );
        }
    }

    if (!empty($values['parent'])) {
        $parent = $Groups->getGroup($values['parent']);
        if ($parent['color'] != '#000000') $values['color'] = $parent['color'];
        $values['level'] = $parent['level'] + 1;
    }

    $insertOneResult  = $collection->insertOne($values);
    $id = $insertOneResult->getInsertedId();

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $red = str_replace("*", $id, $_POST['redirect']);
        $_SESSION['msg'] = lang('people.group_created_successfully');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $red);
        die();
    }

    echo json_encode([
        'inserted' => $insertOneResult->getInsertedCount(),
        'id' => $id,
    ]);
});

Route::post('/crud/groups/update/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) abortwith(500, lang('error.no_values'));

    $id = $DB->to_ObjectID($id);

    $group = $osiris->groups->findOne(['_id' => $id]);

    $values = validateValues($_POST['values'], $DB);
    // add information on creating process
    $values['updated'] = date('Y-m-d');
    $values['updated_by'] = $_SESSION['username'];

    // dump($values);
    // die;
    $id_changed = false;
    if (isset($values['hide'])) $values['hide'] = boolval($values['hide']);
    // check if ID has changes
    if (isset($values['id']) && $group['id'] != $values['id']) {
        $osiris->persons->updateMany(
            ["units.unit" => $group['id']],
            ['$set' => ["units.$.unit" => $values['id']]]
        );
        // change ID of child elements
        $osiris->groups->updateMany(
            ['parent' => $group['id']],
            ['$set' => ['parent' => $values['id']]]
        );
        $id_changed = true;
        // change top-level units: replace all occurrences of old id in units[]
        foreach (['activities', 'projects', 'proposals'] as $collection) {
            $osiris->$collection->updateMany(
                ['units' => $group['id']],
                ['$set' => ['units.$[u]' => $values['id']]],
                ['arrayFilters' => [['u' => $group['id']]]]
            );
            if ($collection == 'activities') {
                $keys = ['authors', 'editors', 'supervisors'];
            } else {
                $keys = ['persons'];
            }
            foreach ($keys as $key) {
                $osiris->$collection->updateMany(
                    [$key . '.units' => $group['id']],
                    ['$set' => [$key . '.$[a].units.$[u]' => $values['id']]],
                    ['arrayFilters' => [
                        ['a.units' => $group['id']],  // only authors where units contains oldId
                        ['u' => $group['id']]         // only replace matching unit entries
                    ]]
                );
            }
        }
    }

    if (isset($values['id'])) {
        // check if the right form is used
        if (!empty($values['parent'])) {
            $parent = $Groups->getGroup($values['parent']);
            $values['level'] = $parent['level'] + 1;
            if ($values['level'] == 1) {
                // spread color to all children
                $osiris->groups->updateMany(
                    ['parent' => $values['id']],
                    ['$set' => ['color' => $values['color']]]
                );
            } else {
                $values['color'] = $parent['color'] ?? '#000000';
            }
        } else {
            $values['level'] = 0;
        }
        if ($values['level'] != $group['level']) {
            // change level of all children
            $osiris->groups->updateMany(
                ['parent' => $values['id']],
                ['$set' => ['level' => $values['level'] + 1]]
            );
        }
    }

    if (isset($values['research'])) {
        if (!empty($values['research']) && is_array($values['research'])) {
            $values['research'] = array_values($values['research']);
        } else {
            $values['research'] = [];
        }
    }

    if (isset($values['synonyms'])) {
        if (!empty($values['synonyms'])) {
            $values['synonyms'] = array_map('trim', explode(';', $values['synonyms']));
            $values['synonyms'] = array_values($values['synonyms']);
        } else {
            $values['synonyms'] = null;
        }
    }


    // check if head is connected 
    if (isset($values['head'])) {
        foreach ($values['head'] as $head) {
            $N = $osiris->persons->count(['username' => $head, 'units.unit' => $values['id']]);
            if ($N == 0) {
                $osiris->persons->updateOne(
                    ['username' => $head],
                    ['$push' => [
                        "units" => [
                            'id' => uniqid(),
                            'unit' => $values['id'],
                            'start' => date('Y-m-d'),
                            'end' => null,
                            'scientific' => true
                        ]
                    ]]
                );
            }
        }
    }
    $updateResult = $osiris->groups->updateOne(
        ['_id' => $id],
        ['$set' => $values]
    );

    if ($id_changed) {
        include_once BASEPATH . "/php/Render.php";
        renderAuthorUnitsMany(['authors.units' => $group['id']]);
    }

    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang('common.unit_updated_successfully');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }

    echo json_encode([
        'inserted' => $updateResult->getModifiedCount(),
        'id' => $id,
    ]);
});

Route::post('/crud/groups/images/([A-Fa-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $groupId = $DB->to_ObjectID($id);
    $group = $osiris->groups->findOne(['_id' => $groupId]);
    if (empty($group)) abortwith(404, lang('common.unit'), '/groups');

    $editPerm = $Settings->hasPermission('units.add') || $Groups->editPermission($group['id']);
    if (!$editPerm) {
        abortwith(403, lang('people.you_are_not_allowed_to_edit_this_unit'));
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        redirectFromGroupImage($group['id'], lang('people.no_image_was_uploaded'), 'info');
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = match ($_FILES['file']['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => lang('people.the_image_is_too_large_a_maximum_of_16_mb_is_allowed'),
            UPLOAD_ERR_PARTIAL => lang('common.the_image_was_only_partially_uploaded'),
            UPLOAD_ERR_NO_TMP_DIR => lang('common.the_temporary_upload_directory_is_missing'),
            UPLOAD_ERR_CANT_WRITE => lang('common.the_image_could_not_be_written_to_disk'),
            UPLOAD_ERR_EXTENSION => lang('common.a_php_extension_stopped_the_upload'),
            default => lang('people.the_image_could_not_be_uploaded'),
        };
        redirectFromGroupImage($group['id'], $errorMessage, 'error');
    }

    $file = $_FILES['file'];
    if ($file['size'] > 16000000) {
        redirectFromGroupImage($group['id'], lang('people.the_image_is_too_large_a_maximum_of_16_mb_is_allowed'), 'error');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $dimensions = @getimagesize($file['tmp_name']);
    if ($dimensions === false || !isset($allowedMimeTypes[$mime])) {
        redirectFromGroupImage($group['id'], lang('common.only_jpeg_png_and_webp_images_are_allowed'), 'error');
    }

    [$width, $height] = $dimensions;
    if ($width * $height > 25000000) {
        redirectFromGroupImage($group['id'], lang('people.the_image_resolution_is_too_large_a_maximum_of_25_megapixels_is_allowed'), 'error');
    }

    if (!extension_loaded('gd') || !function_exists('imagewebp')) {
        redirectFromGroupImage($group['id'], lang('people.image_processing_is_not_available_on_this_server_please_contact_an_administ'), 'error');
    }

    $takenAt = trim($_POST['taken_at'] ?? '');
    if ($takenAt !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $takenAt);
        if ($date === false || $date->format('Y-m-d') !== $takenAt) {
            redirectFromGroupImage($group['id'], lang('people.the_date_is_invalid'), 'error');
        }
    } else {
        $takenAt = null;
    }

    $imageId = bin2hex(random_bytes(12));
    $targetDirectory = BASEPATH . "/uploads/groups/$id";
    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true)) {
        redirectFromGroupImage($group['id'], lang('common.the_upload_directory_could_not_be_created'), 'error');
    }

    $extension = $allowedMimeTypes[$mime];
    $originalPath = "$targetDirectory/$imageId.$extension";
    $thumbnailPath = "$targetDirectory/$imageId-thumb.webp";
    if (!move_uploaded_file($file['tmp_name'], $originalPath)) {
        redirectFromGroupImage($group['id'], lang('common.the_image_could_not_be_saved'), 'error');
    }

    if (!createGroupImageThumbnail($originalPath, $thumbnailPath, $mime)) {
        @unlink($originalPath);
        redirectFromGroupImage($group['id'], lang('people.the_image_preview_could_not_be_created'), 'error');
    }

    $images = DB::doc2Arr($group['images'] ?? []);
    $image = [
        'id' => $imageId,
        'file' => "groups/$id/$imageId.$extension",
        'thumbnail' => "groups/$id/$imageId-thumb.webp",
        'mime' => $mime,
        'size' => (int) $file['size'],
        'width' => (int) $width,
        'height' => (int) $height,
        'caption' => substr(trim($_POST['caption'] ?? ''), 0, 1000),
        'caption_de' => substr(trim($_POST['caption_de'] ?? ''), 0, 1000),
        'taken_at' => $takenAt,
        'credits' => substr(trim($_POST['credits'] ?? ''), 0, 255),
        'public' => boolval($_POST['public'] ?? false),
        'uploaded_at' => date('c'),
        'uploaded_by' => $_SESSION['username'],
        'order' => count($images),
    ];

    try {
        $osiris->groups->updateOne(
            ['_id' => $groupId],
            ['$push' => ['images' => $image]]
        );
    } catch (Throwable $exception) {
        @unlink($originalPath);
        @unlink($thumbnailPath);
        redirectFromGroupImage($group['id'], lang('people.the_image_metadata_could_not_be_saved'), 'error');
    }

    redirectFromGroupImage($group['id'], lang('people.the_image_has_been_uploaded'), 'success');
}, 'login');

Route::post('/crud/groups/images/([A-Fa-f0-9]{24})/([A-Fa-f0-9]{24})/update', function ($id, $imageId) {
    include_once BASEPATH . "/php/init.php";

    $groupId = $DB->to_ObjectID($id);
    $group = $osiris->groups->findOne(['_id' => $groupId]);
    if (empty($group)) abortwith(404, lang('common.unit'), '/groups');

    $editPerm = $Settings->hasPermission('units.add') || $Groups->editPermission($group['id']);
    if (!$editPerm) {
        abortwith(403, lang('people.you_are_not_allowed_to_edit_this_unit'));
    }

    $imageExists = false;
    foreach (DB::doc2Arr($group['images'] ?? []) as $image) {
        if (($image['id'] ?? '') === $imageId) {
            $imageExists = true;
            break;
        }
    }
    if (!$imageExists) abortwith(404, lang('common.image'), "/groups/view/{$group['id']}");

    $takenAt = trim($_POST['taken_at'] ?? '');
    if ($takenAt !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $takenAt);
        if ($date === false || $date->format('Y-m-d') !== $takenAt) {
            redirectFromGroupImage($group['id'], lang('people.the_date_is_invalid'), 'error');
        }
    } else {
        $takenAt = null;
    }

    $osiris->groups->updateOne(
        ['_id' => $groupId, 'images.id' => $imageId],
        ['$set' => [
            'images.$.caption' => substr(trim($_POST['caption'] ?? ''), 0, 1000),
            'images.$.caption_de' => substr(trim($_POST['caption_de'] ?? ''), 0, 1000),
            'images.$.taken_at' => $takenAt,
            'images.$.credits' => substr(trim($_POST['credits'] ?? ''), 0, 255),
            'images.$.public' => boolval($_POST['public'] ?? false),
        ]]
    );

    redirectFromGroupImage($group['id'], lang('people.the_image_information_has_been_updated'), 'success');
}, 'login');

Route::post('/crud/groups/images/([A-Fa-f0-9]{24})/([A-Fa-f0-9]{24})/delete', function ($id, $imageId) {
    include_once BASEPATH . "/php/init.php";

    $groupId = $DB->to_ObjectID($id);
    $group = $osiris->groups->findOne(['_id' => $groupId]);
    if (empty($group)) abortwith(404, lang('common.unit'), '/groups');

    $editPerm = $Settings->hasPermission('units.add') || $Groups->editPermission($group['id']);
    if (!$editPerm) {
        abortwith(403, lang('people.you_are_not_allowed_to_edit_this_unit'));
    }

    $selectedImage = null;
    foreach (DB::doc2Arr($group['images'] ?? []) as $image) {
        if (($image['id'] ?? '') === $imageId) {
            $selectedImage = $image;
            break;
        }
    }
    if ($selectedImage === null) abortwith(404, lang('common.image'), "/groups/view/{$group['id']}");

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $extension = $allowedMimeTypes[$selectedImage['mime'] ?? ''] ?? null;

    $osiris->groups->updateOne(
        ['_id' => $groupId],
        ['$pull' => ['images' => ['id' => $imageId]]]
    );

    if ($extension !== null) {
        $targetDirectory = BASEPATH . "/uploads/groups/$id";
        $originalPath = "$targetDirectory/$imageId.$extension";
        $thumbnailPath = "$targetDirectory/$imageId-thumb.webp";
        if (is_file($originalPath)) @unlink($originalPath);
        if (is_file($thumbnailPath)) @unlink($thumbnailPath);
    }

    redirectFromGroupImage($group['id'], lang('people.the_image_has_been_deleted'), 'success');
}, 'login');

Route::post('/crud/groups/delete/([A-Za-z0-9]*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    // select the right collection

    // prepare id
    $id = $DB->to_ObjectID($id);

    // remove from all users
    $group = $osiris->groups->findOne(['_id' => $id]);
    $osiris->persons->updateOne(
        ['units' => $group['id']],
        [
            '$pull' => ['units' => ['unit' => $group['id']]]
        ],
        ['multi' => true]
    );

    $updateResult = $osiris->groups->deleteOne(
        ['_id' => $id]
    );

    $deletedCount = $updateResult->getDeletedCount();

    // addUserActivity('delete');
    if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
        $_SESSION['msg'] = lang('common.unit_deleted_successfully');
        $_SESSION['msg_type'] = 'success';
        header("Location: " . $_POST['redirect']);
        die();
    }
    echo json_encode([
        'deleted' => $deletedCount
    ]);
});


Route::post('/crud/groups/addperson/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['username'])) die("no username given");
    $user = $_POST['username'];

    $mode = $_POST['change-or-add'] ?? 'add';
    if ($mode == 'change' && isset($_POST['start'])) {
        // set end date of all other units with null date to one day before start date
        $osiris->persons->updateMany(
            ['username' => $user, 'units.end' => null],
            [
                '$set' => ['units.$[elem].end' => date('Y-m-d', strtotime($_POST['start'] . ' -1 day'))]
            ],
            ['arrayFilters' => [['elem.end' => null]]]
        );
    }
    // add id to person dept
    $osiris->persons->updateOne(
        ['username' => $user],
        [
            '$push' => ["units" => [
                'id' => uniqid(),
                'unit' => $id,
                'start' => $_POST['start'] ?? null,
                'end' => null,
                'scientific' => boolval($_POST['scientific'] ?? true)
            ]]
        ]
    );
    // update activities from the period the person was in the group
    include_once BASEPATH . "/php/Render.php";
    if (isset($_POST['start'])) {
        renderAuthorUnitsMany(['rendered.affiliated_users' => $user, 'date' => ['$gte' => $_POST['start']]]);
    } else {
        renderAuthorUnitsMany(['rendered.affiliated_users' => $user]);
    }

    $_SESSION['msg'] = lang('people.person_added_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/edit/$id#section-personnel");
});

Route::post('/crud/groups/removeperson/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    // add id to person dept
    $updateResult = $osiris->persons->updateOne(
        ['username' => $_POST['username']],
        ['$pull' => ["units" => ['unit' => $id]]]
    );

    // update activities from the period the person was in the group
    include_once BASEPATH . "/php/Render.php";
    renderAuthorUnitsMany(['authors.user' => $_POST['username']]);

    $_SESSION['msg'] = lang('people.person_removed_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/edit/$id#section-personnel");
});


// delegate editing rights
Route::post('/crud/groups/editorperson/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['username'])) die("no username given");
    // add id to person dept
    $action = $_POST['action'] ?? 'add';
    $updateResult = $osiris->persons->updateOne(
        ['username' => $_POST['username']],
        // set units.editor to true where unit is the group id
        ['$set' => ["units.$[elem].editor" => ($action == 'add')]],
        [
            'arrayFilters' => [['elem.unit' => $id]]
        ]
    );

    $_SESSION['msg'] = lang('people.editor_rights_updated_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/edit/$id#section-personnel");
});


Route::post('/crud/groups/reorder/(.*)', function ($id) {
    include_once BASEPATH . "/php/init.php";

    $order = $_POST['order'];
    $i = 0;
    foreach ($order as $o) {
        $osiris->groups->updateOne(
            ['_id' => $DB->to_ObjectID($o)],
            ['$set' => ['order' => $i]]
        );
        $i++;
    }

    $_SESSION['msg'] = lang('people.group_reordered_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/view/$id");
});
