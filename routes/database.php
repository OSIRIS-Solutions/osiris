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
    // TODO: tell the browser not to cache this page

    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/Render.php";
    include BASEPATH . "/header.php"; ?>

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
    renderAuthorUnitsMany($filter);
    echo "Done.";
    include BASEPATH . "/footer.php";
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


Route::get('/documents', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('documents')) {
        die(lang('You do not have permission to view documents.', 'Du hast keine Berechtigung, Dokumente anzusehen.'));
    }
    include_once BASEPATH . "/php/Vocabulary.php";
    $Vocabulary = new Vocabulary();
    $documents = $osiris->uploads->find([], ['sort' => ['uploaded' => -1]])->toArray();
    $breadcrumb = [
        ['name' => lang('Documents', 'Dokumente')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/documents.php";
    include BASEPATH . "/footer.php";
});



// central upload of documents
Route::post('/data/upload', function () {
    include_once BASEPATH . "/php/init.php";

    $values = $_POST['values'] ?? [];

    if (!isset($values['type']) || !isset($values['id'])) {
        die(lang('Invalid request. Missing type or id.', 'Ungültige Anfrage. Typ oder ID fehlt.'));
    }

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo lang('File upload failed with the following error: ','Datei-Upload fehlgeschlagen mit folgendem Fehler: ') . '<br>';
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                echo lang('The uploaded file exceeds the upload_max_filesize directive in php.ini. Please contact admin.', 'Die hochgeladene Datei überschreitet die upload_max_filesize Direktive in der php.ini. Bitte kontaktiere den Administrator.');
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo lang('The uploaded file exceeds the maximum allowed size.', 'Die hochgeladene Datei überschreitet die maximal erlaubte Größe.');
                break;
            case UPLOAD_ERR_PARTIAL:
                echo lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.');
                break;
            case UPLOAD_ERR_NO_FILE:
                echo lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.');
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo lang('Missing a temporary folder.', 'Es fehlt ein temporärer Ordner.');
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo lang('Failed to write file to disk.', 'Die Datei konnte nicht auf die Festplatte geschrieben werden.');
                break;
            case UPLOAD_ERR_EXTENSION:
                echo lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.');
                break;
            default:
                echo lang('Unknown upload error.', 'Unbekannter Upload-Fehler.');
                break;
        }
        die;
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
        die("Fehler beim Speichern in der Datenbank");
    }

    // Get the inserted document ID
    $doc_id = $result->getInsertedId();

    $targetPath = BASEPATH . '/uploads/' . strval($doc_id) . '.' . $extension;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Wenn der Upload fehlschlägt, entferne den Eintrag aus der Datenbank
        $osiris->uploads->deleteOne(['_id' => $doc_id]);
        die("Upload fehlgeschlagen");
    }

    // redirect
    $_SESSION['msg'] = lang('Document uploaded successfully.', 'Dokument erfolgreich hochgeladen.');
    if (!empty($values['redirect'])) {
        $redirectUrl = $values['redirect'];
    } else {
        $redirectUrl = ROOTPATH . "/" . $values['type'] . "/view/" . $values['id'] . "?tab=documents";
    }
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
