<?php

/**
 * Normalize entity connections submitted by the news editor.
 */
function normalize_news_connections(array $data): array
{
    $connections = [];
    foreach (['persons', 'activities', 'projects', 'events', 'infrastructures'] as $field) {
        $values = $data[$field] ?? [];
        if (!is_array($values)) {
            $values = [$values];
        }
        $values = array_map(static fn($value) => trim((string) $value), $values);
        $connections[$field] = array_values(array_unique(array_filter($values)));
    }
    return $connections;
}

/**
 * Only accept a featured entity that is part of the submitted connections.
 */
function normalize_news_featured(array $data, array $connections): ?array
{
    $featured = $data['featured'] ?? [];
    if (!is_array($featured)) {
        return null;
    }

    $connectionFields = [
        'person' => 'persons',
        'activity' => 'activities',
        'project' => 'projects',
        'event' => 'events',
        'infrastructure' => 'infrastructures'
    ];
    $type = trim((string) ($featured['type'] ?? ''));
    $entityId = trim((string) ($featured['id'] ?? ''));

    if (!isset($connectionFields[$type]) ||
        !in_array($entityId, $connections[$connectionFields[$type]] ?? [], true)) {
        return null;
    }

    $limitText = static function ($value): ?string {
        $value = trim((string) $value);
        if ($value === '') return null;
        return function_exists('mb_substr') ? mb_substr($value, 0, 240) : substr($value, 0, 240);
    };

    return [
        'type' => $type,
        'id' => $entityId,
        'text' => $limitText($featured['text'] ?? ''),
        'text_de' => $limitText($featured['text_de'] ?? '')
    ];
}

Route::get('/news', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => lang('News', 'News')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/list.php";
    include BASEPATH . "/footer.php";
});

Route::get('/news/add', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    if (!$Settings->hasPermission('news.edit')) {
        abortwith(403, lang('You do not have permission to create news items.', "Sie haben keine Berechtigung, News zu erstellen."));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => lang('News', 'News')],
        ['path' => '/news/add', 'name' => lang('Create news item', 'Nachricht erstellen')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/edit.php";
    include BASEPATH . "/footer.php";
});

Route::get('/news/view/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);

    if (!$news) {
        abortwith(404, lang('News item not found.', "Nachricht nicht gefunden."));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => lang('News', 'News')],
        ['path' => '/news/view/' . e($id), 'name' => lang($news['title'] ?? '', $news['title_de'] ?? null)]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/view.php";
    include BASEPATH . "/footer.php";
});

Route::get('/news/edit/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);

    if (!$news) {
        abortwith(404, lang('News item not found.', "Nachricht nicht gefunden."));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => lang('News', 'News')],
        ['path' => '/news/view/' . e($id), 'name' => lang($news['title'] ?? '', $news['title_de'] ?? null)],
        ['path' => '/news/edit/' . e($id), 'name' => lang('Edit', 'Bearbeiten')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/edit.php";
    include BASEPATH . "/footer.php";
});


Route::post('/crud/news/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    if (!$Settings->hasPermission('news.edit')) {
        abortwith(403, lang('You do not have permission to create news items.', "Sie haben keine Berechtigung, News zu erstellen."));
    }

    $data = $_POST['news'] ?? [];
    $connections = normalize_news_connections($data);

    // basic validation
    if (empty($data['title']) || empty($data['content']) || empty($data['date'])) {
        abortwith(400, lang('Please fill in all required fields.', "Bitte füllen Sie alle erforderlichen Felder aus."));
    }

    $newsItem = [
        'type' => $data['type'] ?? null,
        'title' => $data['title'],
        'title_de' => $data['title_de'] ?? null,
        'teaser' => $data['teaser'] ?? null,
        'teaser_de' => $data['teaser_de'] ?? null,
        'content' => $data['content'] ?? null,
        'content_de' => $data['content_de'] ?? null,
        'date' => $data['date'],
        'visibility' => $data['visibility'] ?? 'internal',
        'persons' => $connections['persons'],
        'activities' => $connections['activities'],
        'projects' => $connections['projects'],
        'events' => $connections['events'],
        'infrastructures' => $connections['infrastructures'],
        'featured' => normalize_news_featured($data, $connections),
        'topics' => $data['topics'] ?? [],
        'created_by' => $_SESSION['username'],
        'created' => date('Y-m-d')
    ];
    // replace empty strings with null to avoid storing empty fields
    foreach ($newsItem as $key => $value) {
        if (is_string($value) && trim($value) === '') {
            $newsItem[$key] = null;
        }
    }

    $result = $osiris->news->insertOne($newsItem);

    if ($result->getInsertedCount() === 1) {
        header('Location: ' . ROOTPATH . '/news/view/' . e($result->getInsertedId()));
        exit;
    } else {
        abortwith(500, lang('Failed to create news item.', "Die Erstellung der Nachricht ist fehlgeschlagen."));
    }
});


Route::post('/crud/news/update/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    if (!$Settings->hasPermission('news.edit')) {
        abortwith(403, lang('You do not have permission to edit news items.', "Sie haben keine Berechtigung, News zu bearbeiten."));
    }

    $data = $_POST['news'] ?? [];
    $connections = normalize_news_connections($data);

    // basic validation
    if (empty($data['title']) || empty($data['content']) || empty($data['date'])) {
        abortwith(400, lang('Please fill in all required fields.', "Bitte füllen Sie alle erforderlichen Felder aus."));
    }

    $newsItem = [
        'type' => $data['type'] ?? null,
        'title' => $data['title'],
        'title_de' => $data['title_de'] ?? null,
        'teaser' => $data['teaser'] ?? null,
        'teaser_de' => $data['teaser_de'] ?? null,
        'content' => $data['content'] ?? null,
        'content_de' => $data['content_de'] ?? null,
        'date' => $data['date'],
        'visibility' => $data['visibility'] ?? 'internal',
        'persons' => $connections['persons'],
        'activities' => $connections['activities'],
        'projects' => $connections['projects'],
        'events' => $connections['events'],
        'infrastructures' => $connections['infrastructures'],
        'featured' => normalize_news_featured($data, $connections),
        'topics' => ($data['topics'] ?? []),
        'updated_by' => $_SESSION['username'],
        'updated' => date('Y-m-d')
    ];
    // replace empty strings with null to avoid storing empty fields
    foreach ($newsItem as $key => $value) {
        if (is_string($value) && trim($value) === '') {
            $newsItem[$key] = null;
        }
    }

    $result = $osiris->news->updateOne(['_id' => DB::to_ObjectID($id)], ['$set' => $newsItem]);

    header('Location: ' . ROOTPATH . '/news/view/' . e($id));
});


Route::post('/crud/news/upload-picture/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    $mongo_id = $DB->to_ObjectID($id);
    // get news id    
    $news = $osiris->news->findOne(['_id' => $mongo_id]);
    if (empty($news)) {
        abortwith(404, lang('News item', 'Nachricht'), '/news');
    }
    if (isset($_FILES["file"])) {
        // if ($_FILES['file']['type'] != 'image/jpeg') die('Wrong extension, only JPEG is allowed.');

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini'),
                2 => lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt."),
                3 => lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.'),
                4 => lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.'),
                6 => lang('Missing a temporary folder.', 'Der temporäre Ordner fehlt.'),
                7 => lang('Failed to write file to disk.', 'Datei konnte nicht auf die Festplatte geschrieben werden.'),
                8 => lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.'),
                default => lang('Something went wrong.', 'Etwas ist schiefgelaufen.') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
            $_SESSION['msg_type'] = "error";
        } else if ($_FILES["file"]["size"] > 2000000) {
            $_SESSION['msg'] = lang("File is too big: max 2 MB is allowed.", "Die Datei ist zu groß: maximal 2 MB sind erlaubt.");
            $_SESSION['msg_type'] = "error";
        } else {
            // check image settings
            $file = file_get_contents($_FILES["file"]["tmp_name"]);
            $type = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
            // encode image
            $file = base64_encode($file);
            $img = new MongoDB\BSON\Binary($file, MongoDB\BSON\Binary::TYPE_GENERIC);
            // first: delete old image, then: insert new one
            $updateResult = $osiris->news->updateOne(
                ['_id' => $mongo_id],
                ['$set' => ['image' => [
                    'data' => $img,
                    'type' => $type,
                    'extension' => $type,
                    'uploaded_by' => $_SESSION['username'],
                    'uploaded' => date('Y-m-d')
                ]]]
            );
            $_SESSION['msg'] = lang("News image uploaded successfully.", "Bild erfolgreich hochgeladen.");
            $_SESSION['msg_type'] = "success";
            header("Location: " . ROOTPATH . "/news/view/$id");
            die;
            // printMsg(lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload."), "error");
        }
    } else if (isset($_POST['delete'])) {
        $osiris->news->updateOne(
            ['_id' => $mongo_id],
            ['$unset' => ['image' => ""]]
        );
        $_SESSION['msg'] = lang("News image deleted.", "Bild gelöscht.");
        $_SESSION['msg_type'] = "success";
        header("Location: " . ROOTPATH . "/news/view/$id");
        die;
    }

    header("Location: " . ROOTPATH . "/news/view/$id");
    die;
});


Route::post('/crud/news/delete', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, lang('News are not enabled.', "News sind nicht aktiviert."));
    }

    if (!$Settings->hasPermission('news.delete')) {
        abortwith(403, lang('You do not have permission to delete news items.', "Sie haben keine Berechtigung, News zu löschen."));
    }

    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        abortwith(400, lang('Invalid news item ID.', "Ungültige News-ID."));
    }

    $result = $osiris->news->deleteOne(['_id' => DB::to_ObjectID($id)]);

    if ($result->getDeletedCount() === 1) {
        header('Location: ' . ROOTPATH . '/news');
        exit;
    } else {
        abortwith(500, lang('Failed to delete news item.', "Die Löschung der Nachricht ist fehlgeschlagen."));
    }
});
