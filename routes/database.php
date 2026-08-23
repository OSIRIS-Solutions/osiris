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
                <?= lang('Access denied', 'Zugriff verweigert') ?>
            </h4>
            <?= lang('You do not have permission to access this page.', 'Du hast keine Berechtigung, diese Seite zu betreten.') ?>
        </div>
    <?php
        include BASEPATH . "/footer.php";
        die;
    } ?>

    <p class="text-danger">
        <i class="ph ph-warning"></i>
        <?= lang('Start to render all activities. This might take a while. Please be patient and do not reload the page.', 'Ich starte damit, die Aktivitäten neu zu rendern. Dies kann eine Weile dauern. Bitte sei geduldig und lade die Seite nicht neu.') ?>
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
            <?= lang('Success', 'Erfolg') ?>
        </h4>
        <?= lang('The rendering has finished. All activities should now be displayed correctly. You can now safely close this window.', 'Das Rendering ist abgeschlossen. Alle Aktivitäten sollten jetzt korrekt dargestellt werden. Du kannst diese Seite jetzt schließen.') ?>
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
                <?= lang('Access denied', 'Zugriff verweigert') ?>
            </h4>
            <?= lang('You do not have permission to access this page.', 'Du hast keine Berechtigung, diese Seite zu betreten.') ?>
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
                <?= lang('Access denied', 'Zugriff verweigert') ?>
            </h4>
            <?= lang('You do not have permission to access this page.', 'Du hast keine Berechtigung, diese Seite zu betreten.') ?>
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
        return abortwith(403, lang('You do not have permission to view documents.', 'Du hast keine Berechtigung, Dokumente anzusehen.'), '/');
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
        return abortwith(403, lang('You do not have permission to view this type of documents.', 'Du hast keine Berechtigung, diese Art von Dokumenten anzusehen.'), '/');
    }
    $documents = $osiris->uploads->find($filter, ['sort' => ['uploaded' => -1]])->toArray();
    $breadcrumb = [
        ['name' => lang('Documents', 'Dokumente')]
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
            lang(
                'You do not have permission to manage central documents.',
                'Du hast keine Berechtigung, zentrale Dokumente zu verwalten.'
            ),
            '/documents',
            lang('Back to documents', 'Zurück zu den Dokumenten')
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
        redirectFromCentralDocuments(lang('Please enter a title.', 'Bitte gib einen Titel ein.'));
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
    if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
        redirectFromCentralDocuments(lang('Please select a file.', 'Bitte wähle eine Datei aus.'));
    }

    $file = $_FILES['file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => lang('The file is too large.', 'Die Datei ist zu groß.'),
            UPLOAD_ERR_PARTIAL => lang('The file was only partially uploaded.', 'Die Datei wurde nur teilweise hochgeladen.'),
            UPLOAD_ERR_NO_TMP_DIR => lang('The temporary upload directory is missing.', 'Der temporäre Upload-Ordner fehlt.'),
            UPLOAD_ERR_CANT_WRITE => lang('The file could not be written to disk.', 'Die Datei konnte nicht auf die Festplatte geschrieben werden.'),
            UPLOAD_ERR_EXTENSION => lang('A PHP extension stopped the upload.', 'Eine PHP-Erweiterung hat den Upload gestoppt.'),
            default => lang('The file could not be uploaded.', 'Die Datei konnte nicht hochgeladen werden.'),
        };
        redirectFromCentralDocuments($message);
    }

    if ((int) $file['size'] <= 0 || (int) $file['size'] > 25 * 1024 * 1024) {
        redirectFromCentralDocuments(lang('Files may be up to 25 MB in size.', 'Dateien dürfen maximal 25 MB groß sein.'));
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
        redirectFromCentralDocuments(lang(
            'This file type is not supported. Please upload a common document, spreadsheet, presentation, text file, PDF or image.',
            'Dieser Dateityp wird nicht unterstützt. Bitte lade ein gängiges Dokument, eine Tabelle, Präsentation, Textdatei, PDF oder ein Bild hoch.'
        ));
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: 'application/octet-stream';
    $blockedMimes = ['text/html', 'application/x-httpd-php', 'application/x-php', 'application/x-executable'];
    if (in_array($mime, $blockedMimes, true)) {
        redirectFromCentralDocuments(lang('This file type is not supported.', 'Dieser Dateityp wird nicht unterstützt.'));
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
        ['name' => lang('Documents', 'Dokumente'), 'path' => '/documents'],
        ['name' => lang('Manage central documents', 'Zentrale Dokumente verwalten')],
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/documents-manage.php";
    include BASEPATH . "/footer.php";
}, 'login');


Route::get('/documents/central/file/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('documents.central') && !$Settings->hasPermission('documents.manage')) {
        abortwith(403, lang('You do not have permission to view central documents.', 'Du hast keine Berechtigung, zentrale Dokumente anzusehen.'), '/documents');
    }

    $document = centralDocumentById($osiris, $id);
    if (empty($document)) abortwith(404, lang('Document not found.', 'Dokument nicht gefunden.'), '/documents');

    $extension = strtolower((string) ($document['extension'] ?? ''));
    $path = BASEPATH . '/uploads/' . $id . '.' . $extension;
    if (!is_file($path)) abortwith(404, lang('File not found.', 'Datei nicht gefunden.'), '/documents');

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

    $values = $_POST['values'] ?? [];
    $metadata = centralDocumentMetadata(is_array($values) ? $values : []);
    [$file, $filename, $extension, $mime] = centralDocumentUpload();
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
            redirectFromCentralDocuments(lang('The file could not be saved.', 'Die Datei konnte nicht gespeichert werden.'));
        }
    } catch (Throwable $exception) {
        redirectFromCentralDocuments(lang('The document could not be saved.', 'Das Dokument konnte nicht gespeichert werden.'));
    }

    redirectFromCentralDocuments(lang('The document was uploaded successfully.', 'Das Dokument wurde erfolgreich hochgeladen.'), 'success');
}, 'login');


Route::post('/crud/documents/central/update/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);
    if (empty(centralDocumentById($osiris, $id))) {
        abortwith(404, lang('Document not found.', 'Dokument nicht gefunden.'), '/documents/manage');
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
        redirectFromCentralDocuments(lang('The document could not be updated.', 'Das Dokument konnte nicht aktualisiert werden.'));
    }

    redirectFromCentralDocuments(lang('The document was updated successfully.', 'Das Dokument wurde erfolgreich aktualisiert.'), 'success');
}, 'login');


Route::post('/crud/documents/central/replace/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);
    $document = centralDocumentById($osiris, $id);
    if (empty($document)) abortwith(404, lang('Document not found.', 'Dokument nicht gefunden.'), '/documents/manage');

    [$file, $filename, $extension, $mime] = centralDocumentUpload();
    $oldExtension = strtolower((string) ($document['extension'] ?? ''));
    $oldPath = BASEPATH . '/uploads/' . $id . '.' . $oldExtension;
    $targetPath = BASEPATH . '/uploads/' . $id . '.' . $extension;
    $temporaryPath = BASEPATH . '/uploads/' . $id . '.replacement-' . bin2hex(random_bytes(6)) . '.' . $extension;
    $backupPath = $oldPath . '.backup-' . bin2hex(random_bytes(6));

    if (!move_uploaded_file($file['tmp_name'], $temporaryPath)) {
        redirectFromCentralDocuments(lang('The replacement file could not be saved.', 'Die Ersatzdatei konnte nicht gespeichert werden.'));
    }

    $hadOldFile = is_file($oldPath);
    $hasBackup = $hadOldFile && rename($oldPath, $backupPath);
    if ($hadOldFile && !$hasBackup) {
        @unlink($temporaryPath);
        redirectFromCentralDocuments(lang('The existing file could not be prepared for replacement.', 'Die vorhandene Datei konnte nicht für das Ersetzen vorbereitet werden.'));
    }
    if (!rename($temporaryPath, $targetPath)) {
        @unlink($temporaryPath);
        if ($hasBackup) @rename($backupPath, $oldPath);
        redirectFromCentralDocuments(lang('The replacement file could not be saved.', 'Die Ersatzdatei konnte nicht gespeichert werden.'));
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
        redirectFromCentralDocuments(lang('The document could not be replaced.', 'Das Dokument konnte nicht ersetzt werden.'));
    }

    if ($hasBackup) @unlink($backupPath);
    if ($oldPath !== $targetPath && is_file($oldPath)) @unlink($oldPath);
    redirectFromCentralDocuments(lang('The file was replaced successfully. Existing links remain valid.', 'Die Datei wurde erfolgreich ersetzt. Bestehende Links bleiben gültig.'), 'success');
}, 'login');


Route::post('/crud/documents/central/delete/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    requireCentralDocumentManagement($Settings);
    $document = centralDocumentById($osiris, $id);
    if (empty($document)) abortwith(404, lang('Document not found.', 'Dokument nicht gefunden.'), '/documents/manage');

    $path = BASEPATH . '/uploads/' . $id . '.' . strtolower((string) ($document['extension'] ?? ''));
    $temporaryPath = $path . '.deleting-' . bin2hex(random_bytes(6));
    $hadFile = is_file($path);
    $fileMoved = $hadFile && rename($path, $temporaryPath);
    if ($hadFile && !$fileMoved) {
        redirectFromCentralDocuments(lang('The file could not be prepared for deletion.', 'Die Datei konnte nicht für das Löschen vorbereitet werden.'));
    }

    try {
        $result = $osiris->uploads->deleteOne(['_id' => DB::to_ObjectID($id), 'type' => 'central']);
        if ($result->getDeletedCount() !== 1) throw new RuntimeException('Document was not deleted.');
    } catch (Throwable $exception) {
        if ($fileMoved) @rename($temporaryPath, $path);
        redirectFromCentralDocuments(lang('The document could not be deleted.', 'Das Dokument konnte nicht gelöscht werden.'));
    }

    if ($fileMoved) @unlink($temporaryPath);
    redirectFromCentralDocuments(lang('The document was deleted successfully.', 'Das Dokument wurde erfolgreich gelöscht.'), 'success');
}, 'login');


// central upload of documents
Route::post('/data/upload', function () {
    include_once BASEPATH . "/php/init.php";

    $values = $_POST['values'] ?? [];

    if (!isset($values['type']) || !isset($values['id'])) {
        die(lang('Invalid request. Missing type or id.', 'Ungültige Anfrage. Typ oder ID fehlt.'));
    }

    if (!empty($values['redirect'])) {
        $redirectUrl = $values['redirect'];
    } else {
        $redirectUrl = ROOTPATH . "/" . $values['type'] . "/view/" . $values['id'] . "?tab=documents";
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $msg = lang('File upload failed with the following error: ', 'Datei-Upload fehlgeschlagen mit folgendem Fehler: ') . '<br>';
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $msg .= lang('The uploaded file exceeds the upload_max_filesize directive in php.ini. Please contact admin.', 'Die hochgeladene Datei überschreitet die upload_max_filesize Direktive in der php.ini. Bitte kontaktiere den Administrator.');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $msg .= lang('The uploaded file exceeds the maximum allowed size.', 'Die hochgeladene Datei überschreitet die maximal erlaubte Größe.');
                break;
            case UPLOAD_ERR_PARTIAL:
                $msg .= lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.');
                break;
            case UPLOAD_ERR_NO_FILE:
                $msg .= lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg .= lang('Missing a temporary folder.', 'Es fehlt ein temporärer Ordner.');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $msg .= lang('Failed to write file to disk.', 'Die Datei konnte nicht auf die Festplatte geschrieben werden.');
                break;
            case UPLOAD_ERR_EXTENSION:
                $msg .= lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.');
                break;
            default:
                $msg .= lang('Unknown upload error.', 'Unbekannter Upload-Fehler.');
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
        $msg = lang('Failed to save document information to the database. Please try again.', 'Fehler beim Speichern der Dokumenteninformationen in der Datenbank. Bitte versuche es erneut.');
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
        $msg = lang('Failed to move uploaded file. Please try again.', 'Fehler beim Verschieben der hochgeladenen Datei. Bitte versuche es erneut.');
        $_SESSION['msg'] = $msg;
        $_SESSION['msg_type'] = 'error';
        header("Location: " . $redirectUrl);
        return;
    }

    // redirect
    $_SESSION['msg'] = lang('Document uploaded successfully.', 'Dokument erfolgreich hochgeladen.');
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
    $_SESSION['msg'] = lang('Document deleted successfully.', 'Dokument erfolgreich gelöscht.');
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
        $_SESSION['msg'] = lang('No changes made to the document.', 'Es wurden keine Änderungen am Dokument vorgenommen.');
        $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "#section-files";
        header("Location: $redirectUrl");
    }

    // update the document in the database
    $result = $osiris->uploads->updateOne(
        ['_id' => DB::to_ObjectID($id)],
        ['$set' => $update]
    );
    if ($result->getModifiedCount() === 0) {
        $_SESSION['msg'] = lang('No changes made to the document.', 'Es wurden keine Änderungen am Dokument vorgenommen.');
        $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "#section-files";
        header("Location: $redirectUrl");
    }

    // redirect
    $_SESSION['msg'] = lang('Document updated successfully.', 'Dokument erfolgreich aktualisiert.');
    $document = $osiris->uploads->findOne(['_id' => DB::to_ObjectID($id)]);
    $redirectUrl = ROOTPATH . "/" . $document['type'] . "/view/" . $document['id'] . "#section-files";
    header("Location: $redirectUrl");
});
