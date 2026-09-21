<?php

/**
 * Routing file for database manipulations
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

Route::get('/uploads/(.*)', function ($requestedPath) {
    include_once BASEPATH . '/php/init.php';

    if ($requestedPath === '') {
        return abortwith(404, lang('common.file'));
    }

    // Resolve the requested file and make sure it really is inside /uploads.
    $uploadsDirectory = realpath(BASEPATH . '/uploads');
    $filePath = realpath(BASEPATH . '/uploads/' . $requestedPath);
    if ($uploadsDirectory === false || $filePath === false) {
        return abortwith(404, lang('common.file'));
    }
    if ($filePath !== $uploadsDirectory && !str_starts_with($filePath, $uploadsDirectory . DIRECTORY_SEPARATOR)) {
        return abortwith(403, lang('people.access_denied'));
    }

    if (!is_file($filePath)) {
        return abortwith(404, lang('common.file'));
    }
    if (!is_readable($filePath)) {
        return abortwith(403, lang('common.file_is_not_readable'));
    }
    $downloadFilename = basename($filePath);

    // Files created by the generic upload are named after their MongoDB ID.
    // Apply the same permissions that are used in the corresponding views.
    if (preg_match('/^([a-f0-9]{24})\.[a-z0-9]+$/i', $requestedPath, $matches)) {
        $document = $osiris->uploads->findOne(['_id' => DB::to_ObjectID($matches[1])]);
        if (empty($document)) {
            return abortwith(404, lang('common.file'));
        }
        $downloadFilename = basename((string) ($document['filename'] ?? $downloadFilename));

        $type = (string) ($document['type'] ?? '');
        $allowed = false;

        if ($type === 'central') {
            $allowed = $Settings->hasPermission('documents.central')
                || $Settings->hasPermission('documents.manage');
        } elseif ($type === 'activities') {
            // Activities and their documents are visible to logged-in users.
            $activityId = (string) ($document['id'] ?? '');
            $allowed = DB::is_ObjectID($activityId)
                && $osiris->activities->count(['_id' => DB::to_ObjectID($activityId)]) > 0;
        } elseif ($type === 'proposals' || $type === 'nagoya-permit') {
            $allowed = $Settings->hasPermission('documents')
                || $Settings->hasPermission('proposals.view-documents')
                || ($type === 'nagoya-permit' && $Settings->hasPermission('nagoya.view'));

            if (!$allowed && DB::is_ObjectID((string) ($document['id'] ?? ''))) {
                $proposal = $osiris->proposals->findOne(['_id' => DB::to_ObjectID((string) $document['id'])]);
                if (!empty($proposal)) {
                    $persons = DB::doc2Arr($proposal['persons'] ?? []);
                    $personIds = array_map('strval', array_column($persons, 'user'));
                    $allowed = in_array($_SESSION['username'], $personIds, true)
                        || ($proposal['created_by'] ?? null) === $_SESSION['username'];
                }
            }
        }

        if (!$allowed) {
            return abortwith(403, lang('common.you_do_not_have_permission_to_view_this_file'));
        }
    } else {
        // Legacy guest documents are stored in /uploads/{guest-id}/{filename}.
        // Other subfolders contain internal images or legacy activity files and
        // are protected by the login requirement of this route.
        $pathParts = explode('/', $requestedPath, 2);
        if (count($pathParts) === 2) {
            $guest = $osiris->guests->findOne([
                'id' => $pathParts[0],
                'files.filename' => basename($pathParts[1]),
            ]);
            if (
                !empty($guest)
                && !$Settings->hasPermission('guests.see.documents')
                && !$Settings->hasPermission('guests.edit.documents')
            ) {
                return abortwith(403, lang('common.you_do_not_have_permission_to_view_this_file'));
            }
        }
    }

    // Deliver file
    $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
    $inlineMimeTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $disposition = in_array($mimeType, $inlineMimeTypes, true) ? 'inline' : 'attachment';

    header('Content-Type: ' . $mimeType);
    header('Content-Length: ' . filesize($filePath));
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, max-age=3600');
    header("Content-Disposition: $disposition; filename=\"file\"; filename*=UTF-8''" . rawurlencode($downloadFilename));
    readfile($filePath);
    die;
}, 'login');


Route::get('/rerender', function () {
    set_time_limit(6000);
    # Do not chache this page
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Render.php";
    include BASEPATH . "/header.php"; ?>
    <?php if (!$Settings->hasPermission('admin.see')) { ?>
        <div class="alert danger">
            <h4 class="title">
                <?= lang('people.access_denied') ?>
            </h4>
            <?= lang('common.you_do_not_have_permission_to_access_this_page') ?>
        </div>
    <?php
        include BASEPATH . "/footer.php";
        die;
    } ?>

    <p class="text-danger">
        <i class="ph ph-warning"></i>
        <?= lang('common.start_to_render_all_activities_this_might_take_a_while_please_be_patient_an') ?>
    </p>
    <?php
    // flush the output buffer
    flush();
    ob_flush();

    $filter = [];
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $filter['type'] = $_GET['type'];
    }
    if (isset($_GET['subtype']) && !empty($_GET['subtype'])) {
        $filter['subtype'] = $_GET['subtype'];
    }
    if (isset($_GET['username']) && !empty($_GET['username'])) {
        $filter['rendered.users'] = $_GET['username'];
    }
    if (isset($_GET['unit']) && !empty($_GET['unit'])) {
        $filter['units'] = $_GET['unit'];
    }

    // start rendering process
    renderActivities($filter);
    ?>

    <div class="alert success">
        <h4 class="title">
            <?= lang('common.success') ?>
        </h4>
        <?= lang('common.the_rendering_has_finished_all_activities_should_now_be_displayed_correctly') ?>
    </div>

    <?php
    include BASEPATH . "/footer.php";
});

Route::get('/rerender-projects', function () {
    set_time_limit(6000);
    include_once BASEPATH . "/php/Render.php";
    include BASEPATH . "/header.php";
    if (!$Settings->hasPermission('admin.see')) { ?>
        <div class="alert danger">
            <h4 class="title">
                <?= lang('people.access_denied') ?>
            </h4>
            <?= lang('common.you_do_not_have_permission_to_access_this_page') ?>
        </div>
    <?php
        include BASEPATH . "/footer.php";
        die;
    }
    renderAuthorUnitsProjects();
    echo "Done.";
    include BASEPATH . "/footer.php";
});

Route::get('/rerender-units/?(.*)', function ($username) {
    set_time_limit(6000);
    include_once BASEPATH . "/php/Render.php";
    $filter = [];
    if (!empty($username)) $filter['rendered.affiliated_users'] = $username;

    include BASEPATH . "/header.php";
    if (!$Settings->hasPermission('admin.see')) { ?>
        <div class="alert danger">
            <h4 class="title">
                <?= lang('people.access_denied') ?>
            </h4>
            <?= lang('common.you_do_not_have_permission_to_access_this_page') ?>
        </div>
<?php
        include BASEPATH . "/footer.php";
        die;
    }
    renderAuthorUnitsMany($filter);
    echo "Done.";
    include BASEPATH . "/footer.php";
});

Route::get('/check-duplicate-id', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_GET['type']) || !isset($_GET['id'])) die('false');
    if ($_GET['type'] != 'doi' && $_GET['type'] != 'pubmed') die('false');

    $type = $_GET['type'];
    $id = $_GET['id'];

    $form = $osiris->activities->findOne([
        $type => new MongoDB\BSON\Regex('^' . preg_quote($id) . '$', 'i')
    ]);
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


Route::get('/documents/?(central|connected)?', function ($type = null) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('documents') && !$Settings->hasPermission('documents.manage') && !$Settings->hasPermission('documents.central')) {
        return abortwith(403, lang('common.you_do_not_have_permission_to_view_documents'), '/');
    }
    $centralPerm = $Settings->hasPermission('documents.central');
    $connectPerm = $Settings->hasPermission('documents');
    $managePerm = $Settings->hasPermission('documents.manage');

    if (empty($type)) {
        $type = ($centralPerm || $managePerm) ? 'central' : 'connected';
    }

    include_once BASEPATH . "/php/Vocabulary.php";
    $Vocabulary = new Vocabulary();

    if ($type === 'central' && ($centralPerm || $managePerm)) {
        $filter = ['type' => 'central'];
    } elseif ($type === 'connected' && ($connectPerm)) {
        $filter = [
            'type' => ['$ne' => 'central']
        ];
    } else {
        return abortwith(403, lang('common.you_do_not_have_permission_to_view_this_type_of_documents'), '/');
    }
    $documents = $osiris->uploads->find($filter, ['sort' => ['uploaded' => -1]])->toArray();
    $breadcrumb = [
        ['name' => lang('common.documents')]
    ];
    include BASEPATH . "/header.php";
    if ($type === 'central') {
        include BASEPATH . "/pages/documents-central.php";
    } else {
        include BASEPATH . "/pages/documents.php";
    }
    include BASEPATH . "/footer.php";
});



function redirectFromCentralDocuments(string $message, string $type = 'error'): void
{
    $_SESSION['msg'] = $message;
    $_SESSION['msg_type'] = $type;
    header('Location: ' . ROOTPATH . '/documents/manage');
    die;
}

function requireCentralDocumentManagement($Settings): void
{
    if (!$Settings->hasPermission('documents.manage')) {
        abortwith(
            403,
            lang('common.you_do_not_have_permission_to_manage_central_documents'),
            '/documents',
            lang('common.back_to_documents')
        );
    }
}

function centralDocumentTags($value): array
{
    if (is_array($value)) {
        $parts = $value;
    } else {
        $parts = preg_split('/[,;\r\n]+/u', (string) $value) ?: [];
    }

    $tags = [];
    foreach ($parts as $part) {
        $tag = trim(strip_tags((string) $part));
        if ($tag === '') continue;
        $tag = mb_substr($tag, 0, 50);
        $tags[mb_strtolower($tag)] = $tag;
        if (count($tags) >= 20) break;
    }
    return array_values($tags);
}

function centralDocumentMetadata(array $values): array
{
    $name = trim(strip_tags((string) ($values['name'] ?? '')));
    if ($name === '') {
        redirectFromCentralDocuments(lang('common.please_enter_a_title'));
    }

    return [
        'name' => mb_substr($name, 0, 200),
        'description' => mb_substr(trim(strip_tags((string) ($values['description'] ?? ''))), 0, 500),
        'category' => mb_substr(trim(strip_tags((string) ($values['category'] ?? ''))), 0, 100),
        'tags' => centralDocumentTags($values['tags'] ?? []),
    ];
}

function centralDocumentUpload(): array
{
    $fileSizeLimit = Settings::getMaxFileSize('25M');
    $postSizeLimit = Settings::convertToBytes(ini_get('post_max_size'));
    $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($postSizeLimit > 0 && $contentLength > $postSizeLimit) {
        redirectFromCentralDocuments(lang('common.files_may_be_up_to_filesizelimit_in_size', replace: ['fileSizeLimit' => $fileSizeLimit['human']]));
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        redirectFromCentralDocuments(lang('common.please_select_a_file'));
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => lang('common.the_file_is_too_large'),
            UPLOAD_ERR_PARTIAL => lang('common.the_file_was_only_partially_uploaded'),
            UPLOAD_ERR_NO_TMP_DIR => lang('common.the_temporary_upload_directory_is_missing'),
            UPLOAD_ERR_CANT_WRITE => lang('common.the_file_could_not_be_written_to_disk'),
            UPLOAD_ERR_EXTENSION => lang('common.a_php_extension_stopped_the_upload'),
            default => lang('common.the_file_could_not_be_uploaded'),
        };
        redirectFromCentralDocuments($message);
    }

    if ((int) $file['size'] <= 0 || ($fileSizeLimit['bytes'] > 0 && (int) $file['size'] > $fileSizeLimit['bytes'])) {
        redirectFromCentralDocuments(lang('common.files_may_be_up_to_filesizelimit_in_size', replace: ['fileSizeLimit' => $fileSizeLimit['human']]));
    }

    $filename = trim(basename((string) $file['name']));
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExtensions = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'odt',
        'ods',
        'odp',
        'rtf',
        'txt',
        'csv',
        'zip',
        'jpg',
        'jpeg',
        'png',
    ];
    if ($filename === '' || !in_array($extension, $allowedExtensions, true)) {
        redirectFromCentralDocuments(lang('common.this_file_type_is_not_supported_please_upload_a_common_document_spreadsheet'));
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: 'application/octet-stream';
    $blockedMimes = ['text/html', 'application/x-httpd-php', 'application/x-php', 'application/x-executable'];
    if (in_array($mime, $blockedMimes, true)) {
        redirectFromCentralDocuments(lang('common.this_file_type_is_not_supported'));
    }

    if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        $expectedMime = $extension === 'png' ? 'image/png' : 'image/jpeg';
        $image = @getimagesize($file['tmp_name']);
        if ($image === false || $mime !== $expectedMime || ($image['mime'] ?? null) !== $expectedMime) {
            redirectFromCentralDocuments(lang('common.the_selected_image_is_invalid_or_does_not_match_its_file_extension'));
        }
    }

    return [$file, $filename, $extension, $mime];
}

function centralDocumentById($osiris, string $id)
{
    return $osiris->uploads->findOne([
        '_id' => DB::to_ObjectID($id),
        'type' => 'central',
    ]);
}


Route::get('/documents/manage', function () {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);

    $documents = $osiris->uploads->find(
        ['type' => 'central'],
        ['sort' => ['updated' => -1, 'uploaded' => -1]]
    )->toArray();
    $breadcrumb = [
        ['name' => lang('common.documents'), 'path' => '/documents'],
        ['name' => lang('common.manage_central_documents')],
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/documents-manage.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/documents/central/file/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('documents.central') && !$Settings->hasPermission('documents.manage')) {
        abortwith(403, lang('common.you_do_not_have_permission_to_view_central_documents'), '/documents');
    }

    $document = centralDocumentById($osiris, $id);
    if (empty($document)) abortwith(404, lang('common.document_not_found'), '/documents');

    $extension = strtolower((string) ($document['extension'] ?? ''));
    $path = BASEPATH . '/uploads/' . $id . '.' . $extension;
    if (!is_file($path)) abortwith(404, lang('common.file_not_found'), '/documents');

    $filename = basename((string) ($document['filename'] ?? ('document.' . $extension)));
    $disposition = isset($_GET['download']) ? 'attachment' : 'inline';
    header('Content-Type: ' . ($document['mimetype'] ?? 'application/octet-stream'));
    header('Content-Length: ' . filesize($path));
    header('X-Content-Type-Options: nosniff');
    header("Content-Disposition: $disposition; filename=\"document.$extension\"; filename*=UTF-8''" . rawurlencode($filename));
    readfile($path);
    die;
}, 'login');


Route::post('/crud/documents/central/upload', function () {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);

    [$file, $filename, $extension, $mime] = centralDocumentUpload();
    $values = $_POST['values'] ?? [];
    $metadata = centralDocumentMetadata(is_array($values) ? $values : []);
    $now = date('Y-m-d H:i:s');
    $document = array_merge($metadata, [
        'filename' => $filename,
        'mimetype' => $mime,
        'extension' => $extension,
        'size' => (int) $file['size'],
        'type' => 'central',
        'uploaded' => $now,
        'uploaded_by' => $_SESSION['username'] ?? null,
        'created' => $now,
        'created_by' => $_SESSION['username'] ?? null,
        'updated' => $now,
        'updated_by' => $_SESSION['username'] ?? null,
    ]);

    try {
        $result = $osiris->uploads->insertOne($document);
        $documentId = $result->getInsertedId();
        $target = BASEPATH . '/uploads/' . $documentId . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            $osiris->uploads->deleteOne(['_id' => $documentId]);
            redirectFromCentralDocuments(lang('common.the_file_could_not_be_saved'));
        }
    } catch (Throwable $exception) {
        redirectFromCentralDocuments(lang('common.the_document_could_not_be_saved'));
    }

    redirectFromCentralDocuments(lang('common.the_document_was_uploaded_successfully'), 'success');
}, 'login');


Route::post('/crud/documents/central/update/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);
    if (empty(centralDocumentById($osiris, $id))) {
        abortwith(404, lang('common.document_not_found'), '/documents/manage');
    }

    $values = $_POST['values'] ?? [];
    $metadata = centralDocumentMetadata(is_array($values) ? $values : []);
    $metadata['updated'] = date('Y-m-d H:i:s');
    $metadata['updated_by'] = $_SESSION['username'] ?? null;
    try {
        $osiris->uploads->updateOne(
            ['_id' => DB::to_ObjectID($id), 'type' => 'central'],
            ['$set' => $metadata]
        );
    } catch (Throwable $exception) {
        redirectFromCentralDocuments(lang('common.the_document_could_not_be_updated'));
    }

    redirectFromCentralDocuments(lang('common.the_document_was_updated_successfully'), 'success');
}, 'login');


Route::post('/crud/documents/central/replace/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);
    $document = centralDocumentById($osiris, $id);
    if (empty($document)) abortwith(404, lang('common.document_not_found'), '/documents/manage');

    [$file, $filename, $extension, $mime] = centralDocumentUpload();
    $oldExtension = strtolower((string) ($document['extension'] ?? ''));
    $oldPath = BASEPATH . '/uploads/' . $id . '.' . $oldExtension;
    $targetPath = BASEPATH . '/uploads/' . $id . '.' . $extension;
    $temporaryPath = BASEPATH . '/uploads/' . $id . '.replacement-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $backupPath = $oldPath . '.backup-' . bin2hex(random_bytes(6));

    if (!move_uploaded_file($file['tmp_name'], $temporaryPath)) {
        redirectFromCentralDocuments(lang('common.the_replacement_file_could_not_be_saved'));
    }

    $hadOldFile = is_file($oldPath);
    $hasBackup = $hadOldFile && rename($oldPath, $backupPath);
    if ($hadOldFile && !$hasBackup) {
        @unlink($temporaryPath);
        redirectFromCentralDocuments(lang('common.the_existing_file_could_not_be_prepared_for_replacement'));
    }
    if (!rename($temporaryPath, $targetPath)) {
        @unlink($temporaryPath);
        if ($hasBackup) @rename($backupPath, $oldPath);
        redirectFromCentralDocuments(lang('common.the_replacement_file_could_not_be_saved'));
    }

    $now = date('Y-m-d H:i:s');
    try {
        $osiris->uploads->updateOne(
            ['_id' => DB::to_ObjectID($id), 'type' => 'central'],
            ['$set' => [
                'filename' => $filename,
                'mimetype' => $mime,
                'extension' => $extension,
                'size' => (int) $file['size'],
                'uploaded' => $now,
                'uploaded_by' => $_SESSION['username'] ?? null,
                'updated' => $now,
                'updated_by' => $_SESSION['username'] ?? null,
            ]]
        );
    } catch (Throwable $exception) {
        @unlink($targetPath);
        if ($hasBackup) @rename($backupPath, $oldPath);
        redirectFromCentralDocuments(lang('common.the_document_could_not_be_replaced'));
    }

    if ($hasBackup) @unlink($backupPath);
    if ($oldPath !== $targetPath && is_file($oldPath)) @unlink($oldPath);
    redirectFromCentralDocuments(lang('common.the_file_was_replaced_successfully_existing_links_remain_valid'), 'success');
}, 'login');


Route::post('/crud/documents/central/delete/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);
    $document = centralDocumentById($osiris, $id);
    if (empty($document)) abortwith(404, lang('common.document_not_found'), '/documents/manage');

    $path = BASEPATH . '/uploads/' . $id . '.' . strtolower((string) ($document['extension'] ?? ''));
    $temporaryPath = $path . '.deleting-' . bin2hex(random_bytes(6));
    $hadFile = is_file($path);
    $fileMoved = $hadFile && rename($path, $temporaryPath);
    if ($hadFile && !$fileMoved) {
        redirectFromCentralDocuments(lang('common.the_file_could_not_be_prepared_for_deletion'));
    }

    try {
        $result = $osiris->uploads->deleteOne(['_id' => DB::to_ObjectID($id), 'type' => 'central']);
        if ($result->getDeletedCount() !== 1) throw new RuntimeException('Document was not deleted.');
    } catch (Throwable $exception) {
        if ($fileMoved) @rename($temporaryPath, $path);
        redirectFromCentralDocuments(lang('common.the_document_could_not_be_deleted'));
    }

    if ($fileMoved) @unlink($temporaryPath);
    redirectFromCentralDocuments(lang('common.the_document_was_deleted_successfully'), 'success');
}, 'login');


// central upload of documents
Route::post('/data/upload', function () {
    include_once BASEPATH . "/php/init.php";

    $values = $_POST['values'] ?? [];

    if (!isset($values['type']) || !isset($values['id'])) {
        die(lang('common.invalid_request_missing_type_or_id'));
    }

    if (!empty($values['redirect'])) {
        $redirectUrl = $values['redirect'];
    } else {
        $redirectUrl = ROOTPATH . "/" . $values['type'] . "/view/" . $values['id'] . "?tab=documents";
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $msg = lang('common.file_upload_failed_with_the_following_error') . '<br>';
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $msg .= lang('common.the_uploaded_file_exceeds_the_upload_max_filesize_directive_in_php_ini_plea');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $msg .= lang('common.the_uploaded_file_exceeds_the_maximum_allowed_size');
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg .= lang('error.file_partially_uploaded');
                break;
            case UPLOAD_ERR_NO_FILE:
                $msg .= lang('error.no_file_uploaded');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg .= lang('common.missing_a_temporary_folder');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $msg .= lang('common.failed_to_write_file_to_disk');
                break;
            case UPLOAD_ERR_EXTENSION:
                $msg .= lang('error.file_upload_stopped');
                break;
            default:
                $msg .= lang('common.unknown_upload_error');
                break;
        }
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = 'error';
        header("Location: " . $redirectUrl);
        return;
    }

    $file = $_FILES['file'];
    $filename = basename($file['name']);

    // Prepare MongoDB array
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $document = [
        'filename'     => $filename,
        'mimetype'     => mime_content_type($file['tmp_name']),
        'extension'    => $extension,
        'size'         => filesize($file['tmp_name']),
        'uploaded'     => date('Y-m-d'),
        'uploaded_by'  => $_SESSION['username'] ?? null,
        'type'         => $values['type'],
        'id'           => $values['id'],
        'name'         => $values['name'] ?? null,
        'description'  => $values['description'] ?? null,
    ];
    // optional fields
    if (isset($values['context'])) {
        $document['context'] = $values['context'];
    }
    if (isset($values['permit_id'])) {
        $document['permit_id'] = $values['permit_id'];
    }
    if (isset($values['country_code'])) {
        $document['country_code'] = $values['country_code'];
    }

    // Save the document to MongoDB
    $result = $osiris->uploads->insertOne($document);
    if ($result->getInsertedCount() === 0) {
        $msg = lang('common.failed_to_save_document_information_to_the_database_please_try_again');
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = 'error';
        header("Location: " . $redirectUrl);
        return;
    }

    // Get the inserted document ID
    $doc_id = $result->getInsertedId();

    $targetPath = BASEPATH . '/uploads/' . strval($doc_id) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Wenn der Upload fehlschlägt, entferne den Eintrag aus der Datenbank
        $osiris->uploads->deleteOne(['_id' => $doc_id]);
        $msg = lang('common.failed_to_move_uploaded_file_please_try_again');
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = 'error';
        header("Location: " . $redirectUrl);
        return;
    }

    // redirect
    $_SESSION['msg'] = lang('common.document_uploaded_successfully');
    $_SESSION['msg_type'] = 'success';
    header("Location: $redirectUrl");
});

// central delete of documents
Route::post('/data/delete', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['id'])) {
        die("Ungültige Anfrage");
    }
    $id = $_POST['id'];

    // get the document from the database
    $document = $osiris->uploads->findOne(['_id' => DB::to_ObjectID($id)]);
    if (empty($document)) {
        die("Dokument nicht gefunden");
    }

    // delete the document from the database
    $result = $osiris->uploads->deleteOne(['_id' => DB::to_ObjectID($id)]);
    if ($result->getDeletedCount() === 0) {
        die("Fehler beim Löschen des Dokuments");
    }

    // delete the file from the filesystem
    $filePath = BASEPATH . '/uploads/' . $id . '.' . ($document['extension'] ?? '');
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // redirect
    $_SESSION['msg'] = lang('common.document_deleted_successfully');
    $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "?tab=documents";
    header("Location: $redirectUrl");
});

// change name and description of document
Route::post('/data/document/update', function () {
    include_once BASEPATH . "/php/init.php";

    if (!isset($_POST['id'])) {
        die("Ungültige Anfrage");
    }
    $id = $_POST['id'];
    $document = $osiris->uploads->findOne(['_id' => DB::to_ObjectID($id)]);
    if (empty($document)) {
        die("Dokument nicht gefunden");
    }
    $update = [];
    if (isset($_POST['name'])) {
        $update['name'] = $_POST['name'];
    }
    if (isset($_POST['description'])) {
        $update['description'] = $_POST['description'];
    }
    if (empty($update)) {
        $_SESSION['msg'] = lang('common.no_changes_made_to_the_document');
        $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "#section-files";
        header("Location: $redirectUrl");
    }

    // update the document in the database
    $result = $osiris->uploads->updateOne(
        ['_id' => DB::to_ObjectID($id)],
        ['$set' => $update]
    );
    if ($result->getModifiedCount() === 0) {
        $_SESSION['msg'] = lang('common.no_changes_made_to_the_document');
        $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "#section-files";
        header("Location: $redirectUrl");
    }

    // redirect
    $_SESSION['msg'] = lang('common.document_updated_successfully');
    $document = $osiris->uploads->findOne(['_id' => DB::to_ObjectID($id)]);
    $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "#section-files";
    header("Location: $redirectUrl");
});
