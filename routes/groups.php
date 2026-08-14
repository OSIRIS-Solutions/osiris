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
        ['name' => lang("Units", "Einheiten")]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/groups.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::get('/groups/new', function () {
    include_once BASEPATH . "/php/init.php";
    $user = $_SESSION['username'];
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten"), 'path' => "/groups"],
        ['name' => lang("New", "Neu")]
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
        abortwith(404, lang("Unit", "Einheit"), '/groups');
    }
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten"), 'path' => "/groups"],
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
        abortwith(404, lang("Unit", "Einheit"), '/groups');
    }
    $breadcrumb = [
        ['name' => lang("Units", "Einheiten"), 'path' => "/groups"],
        ['name' =>  $group['id'], 'path' => "/groups/view/$id"],
    ];
    if ($page == 'edit') {
        $breadcrumb[] = ['name' => lang("Edit", "Bearbeiten")];
    }

    global $form;
    $form = DB::doc2Arr($group);

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/groups/edit.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::post('/crud/groups/create', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));
    $collection = $osiris->groups;

    $values = validateValues($_POST['values'], $DB);

    // check if group name already exists:
    $group_exist = $collection->findOne(['id' => $values['id']]);
    if (!empty($group_exist)) {
        $_SESSION['msg'] = lang("Group ID does already exist.", "Gruppen-ID existiert bereits.");
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
        $_SESSION['msg'] = lang("Group created successfully.", "Gruppe erfolgreich erstellt.");
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
    if (!isset($_POST['values'])) abortwith(500, lang('No values provided.', 'Keine Werte angegeben.'));

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
        $_SESSION['msg'] = lang("Unit updated successfully.", "Einheit erfolgreich aktualisiert.");
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
    if (empty($group)) abortwith(404, lang("Unit", "Einheit"), '/groups');

    $editPerm = $Settings->hasPermission('units.add') || $Groups->editPermission($group['id']);
    if (!$editPerm) {
        abortwith(403, lang(
            'You are not allowed to edit this unit.',
            'Du darfst diese Einheit nicht bearbeiten.'
        ));
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        redirectFromGroupImage($group['id'], lang(
            'No image was uploaded.',
            'Es wurde kein Bild hochgeladen.'
        ), 'info');
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = match ($_FILES['file']['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => lang(
                'The image is too large. A maximum of 16 MB is allowed.',
                'Das Bild ist zu groß. Maximal 16 MB sind erlaubt.'
            ),
            UPLOAD_ERR_PARTIAL => lang(
                'The image was only partially uploaded.',
                'Das Bild wurde nur teilweise hochgeladen.'
            ),
            UPLOAD_ERR_NO_TMP_DIR => lang(
                'The temporary upload directory is missing.',
                'Der temporäre Upload-Ordner fehlt.'
            ),
            UPLOAD_ERR_CANT_WRITE => lang(
                'The image could not be written to disk.',
                'Das Bild konnte nicht auf die Festplatte geschrieben werden.'
            ),
            UPLOAD_ERR_EXTENSION => lang(
                'A PHP extension stopped the upload.',
                'Eine PHP-Erweiterung hat den Upload gestoppt.'
            ),
            default => lang('The image could not be uploaded.', 'Das Bild konnte nicht hochgeladen werden.'),
        };
        redirectFromGroupImage($group['id'], $errorMessage, 'error');
    }

    $file = $_FILES['file'];
    if ($file['size'] > 16000000) {
        redirectFromGroupImage($group['id'], lang(
            'The image is too large. A maximum of 16 MB is allowed.',
            'Das Bild ist zu groß. Maximal 16 MB sind erlaubt.'
        ), 'error');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $dimensions = @getimagesize($file['tmp_name']);
    if ($dimensions === false || !isset($allowedMimeTypes[$mime])) {
        redirectFromGroupImage($group['id'], lang(
            'Only JPEG, PNG and WebP images are allowed.',
            'Es sind nur JPEG-, PNG- und WebP-Bilder erlaubt.'
        ), 'error');
    }

    [$width, $height] = $dimensions;
    if ($width * $height > 25000000) {
        redirectFromGroupImage($group['id'], lang(
            'The image resolution is too large. A maximum of 25 megapixels is allowed.',
            'Die Bildauflösung ist zu groß. Maximal 25 Megapixel sind erlaubt.'
        ), 'error');
    }

    if (!extension_loaded('gd') || !function_exists('imagewebp')) {
        redirectFromGroupImage($group['id'], lang(
            'Image processing is not available on this server. Please contact an administrator.',
            'Die Bildverarbeitung ist auf diesem Server nicht verfügbar. Bitte kontaktiere die Administration.'
        ), 'error');
    }

    $takenAt = trim($_POST['taken_at'] ?? '');
    if ($takenAt !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $takenAt);
        if ($date === false || $date->format('Y-m-d') !== $takenAt) {
            redirectFromGroupImage($group['id'], lang(
                'The date is invalid.',
                'Das Datum ist ungültig.'
            ), 'error');
        }
    } else {
        $takenAt = null;
    }

    $imageId = bin2hex(random_bytes(12));
    $targetDirectory = BASEPATH . "/uploads/groups/$id";
    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true)) {
        redirectFromGroupImage($group['id'], lang(
            'The upload directory could not be created.',
            'Der Upload-Ordner konnte nicht erstellt werden.'
        ), 'error');
    }

    $extension = $allowedMimeTypes[$mime];
    $originalPath = "$targetDirectory/$imageId.$extension";
    $thumbnailPath = "$targetDirectory/$imageId-thumb.webp";
    if (!move_uploaded_file($file['tmp_name'], $originalPath)) {
        redirectFromGroupImage($group['id'], lang(
            'The image could not be saved.',
            'Das Bild konnte nicht gespeichert werden.'
        ), 'error');
    }

    if (!createGroupImageThumbnail($originalPath, $thumbnailPath, $mime)) {
        @unlink($originalPath);
        redirectFromGroupImage($group['id'], lang(
            'The image preview could not be created.',
            'Die Bildvorschau konnte nicht erstellt werden.'
        ), 'error');
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
        redirectFromGroupImage($group['id'], lang(
            'The image metadata could not be saved.',
            'Die Bildinformationen konnten nicht gespeichert werden.'
        ), 'error');
    }

    redirectFromGroupImage($group['id'], lang(
        'The image has been uploaded.',
        'Das Bild wurde hochgeladen.'
    ), 'success');
}, 'login');

Route::post('/crud/groups/images/([A-Fa-f0-9]{24})/([A-Fa-f0-9]{24})/update', function ($id, $imageId) {
    include_once BASEPATH . "/php/init.php";

    $groupId = $DB->to_ObjectID($id);
    $group = $osiris->groups->findOne(['_id' => $groupId]);
    if (empty($group)) abortwith(404, lang("Unit", "Einheit"), '/groups');

    $editPerm = $Settings->hasPermission('units.add') || $Groups->editPermission($group['id']);
    if (!$editPerm) {
        abortwith(403, lang(
            'You are not allowed to edit this unit.',
            'Du darfst diese Einheit nicht bearbeiten.'
        ));
    }

    $imageExists = false;
    foreach (DB::doc2Arr($group['images'] ?? []) as $image) {
        if (($image['id'] ?? '') === $imageId) {
            $imageExists = true;
            break;
        }
    }
    if (!$imageExists) abortwith(404, lang('Image', 'Bild'), "/groups/view/{$group['id']}");

    $takenAt = trim($_POST['taken_at'] ?? '');
    if ($takenAt !== '') {
        $date = DateTime::createFromFormat('!Y-m-d', $takenAt);
        if ($date === false || $date->format('Y-m-d') !== $takenAt) {
            redirectFromGroupImage($group['id'], lang(
                'The date is invalid.',
                'Das Datum ist ungültig.'
            ), 'error');
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

    redirectFromGroupImage($group['id'], lang(
        'The image information has been updated.',
        'Die Bildinformationen wurden aktualisiert.'
    ), 'success');
}, 'login');

Route::post('/crud/groups/images/([A-Fa-f0-9]{24})/([A-Fa-f0-9]{24})/delete', function ($id, $imageId) {
    include_once BASEPATH . "/php/init.php";

    $groupId = $DB->to_ObjectID($id);
    $group = $osiris->groups->findOne(['_id' => $groupId]);
    if (empty($group)) abortwith(404, lang("Unit", "Einheit"), '/groups');

    $editPerm = $Settings->hasPermission('units.add') || $Groups->editPermission($group['id']);
    if (!$editPerm) {
        abortwith(403, lang(
            'You are not allowed to edit this unit.',
            'Du darfst diese Einheit nicht bearbeiten.'
        ));
    }

    $selectedImage = null;
    foreach (DB::doc2Arr($group['images'] ?? []) as $image) {
        if (($image['id'] ?? '') === $imageId) {
            $selectedImage = $image;
            break;
        }
    }
    if ($selectedImage === null) abortwith(404, lang('Image', 'Bild'), "/groups/view/{$group['id']}");

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

    redirectFromGroupImage($group['id'], lang(
        'The image has been deleted.',
        'Das Bild wurde gelöscht.'
    ), 'success');
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
        $_SESSION['msg'] = lang("Unit deleted successfully.", "Einheit erfolgreich gelöscht.");
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

    $_SESSION['msg'] = lang("Person added successfully.", "Person erfolgreich hinzugefügt.");
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

    $_SESSION['msg'] = lang("Person removed successfully.", "Person erfolgreich entfernt.");
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

    $_SESSION['msg'] = lang("Editor rights updated successfully.", "Bearbeitungsrechte erfolgreich aktualisiert.");
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

    $_SESSION['msg'] = lang("Group reordered successfully.", "Gruppe erfolgreich neu geordnet.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/groups/view/$id");
});
