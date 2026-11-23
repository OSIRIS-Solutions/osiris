<?php

require_once BASEPATH . "/php/NewsEditorial.php";

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

    if (
        !isset($connectionFields[$type]) ||
        !in_array($entityId, $connections[$connectionFields[$type]] ?? [], true)
    ) {
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
        abortwith(500, t('error.news_not_enabled'));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => t('common.news')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/list.php";
    include BASEPATH . "/footer.php";
});

Route::get('/news/add', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, t('error.news_not_enabled'));
    }

    if (!NewsEditorial::canCreate($Settings)) {
        abortwith(403, t('news.you_do_not_have_permission_to_create_news_items'));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => t('common.news')],
        ['path' => '/news/add', 'name' => t('common.create_news_item')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/edit.php";
    include BASEPATH . "/footer.php";
});

Route::get('/news/view/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        return abortwith(500, t('error.news_not_enabled'));
    }

    $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);

    if (!$news) {
        return abortwith(404, t('news.news_item_not_found'));
    }
    if (!$Settings->hasPermission('news.see-future')) {
        $created_by = $news['created_by'] ?? null;
        $persons = DB::doc2Arr($news['persons'] ?? []);
        if ($created_by !== $_SESSION['username'] && !in_array(strval($USER['_id']), $persons) && $news['date'] > date('Y-m-d')) {
            return abortwith(403, t('news.you_do_not_have_permission_to_view_future_news_items_please_ask_an_admin_to_grant_you'));
        }
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => t('common.news')],
        ['path' => '/news/view/' . e($id), 'name' => lang($news['title'] ?? '', $news['title_de'] ?? null)]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/view.php";
    include BASEPATH . "/footer.php";
});

Route::get('/news/edit/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, t('error.news_not_enabled'));
    }

    $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);

    if (!$news) {
        abortwith(404, t('news.news_item_not_found'));
    }

    $news = DB::doc2Arr($news);
    if (!NewsEditorial::canEdit($news, $Settings)) {
        abortwith(403, t('news.you_do_not_have_permission_to_edit_this_news_item'));
    }

    $breadcrumb = [
        ['path' => '/news', 'name' => t('common.news')],
        ['path' => '/news/view/' . e($id), 'name' => lang($news['title'] ?? '', $news['title_de'] ?? null)],
        ['path' => '/news/edit/' . e($id), 'name' => t('action.edit')]
    ];

    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/news/edit.php";
    include BASEPATH . "/footer.php";
});


Route::post('/crud/news/create', function () {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, t('error.news_not_enabled'));
    }

    if (!NewsEditorial::canCreate($Settings)) {
        abortwith(403, t('news.you_do_not_have_permission_to_create_news_items'));
    }

    if (!valid_csrf_token($_POST['csrf_token'] ?? null, 'news')) {
        abortwith(403, t('news.the_form_has_expired_reload_the_news_form'));
    }
    $data = $_POST['news'] ?? [];
    if (!is_array($data)) abortwith(400, t('news.invalid_news_data'));
    $connections = normalize_news_connections($data);

    // basic validation
    if (empty($data['title']) || empty($data['content']) || empty($data['date'])) {
        abortwith(400, t('news.please_fill_in_all_required_fields'));
    }

    $configuration = $Settings->getNewsOutlets();
    try {
        $publication = NewsEditorial::publicationFields($data, $configuration);
    } catch (InvalidArgumentException $exception) {
        abortwith(400, $exception->getMessage());
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
    $newsItem = array_merge($newsItem, $publication);
    // replace empty strings with null to avoid storing empty fields
    foreach ($newsItem as $key => $value) {
        if (is_string($value) && trim($value) === '') {
            $newsItem[$key] = null;
        }
    }

    $newsItem['revision'] = 1;
    $newsItem['original_revision'] = 1;
    $newsItem['original_updated_at'] = date('c');
    try {
        $changes = NewsEditorial::reconcileRequests($data['outlets'] ?? [], $configuration, $newsItem, $_SESSION['username'], date('c'));
    } catch (InvalidArgumentException $exception) {
        abortwith(400, $exception->getMessage());
    }
    $newsItem['outlet_requests'] = $changes['requests'];
    $result = $osiris->news->insertOne($newsItem);

    if ($result->getInsertedCount() === 1) {
        NewsEditorial::notifyRequests($DB, $newsItem, (string) $result->getInsertedId(), $changes, $configuration);
        header('Location: ' . ROOTPATH . '/news/view/' . e($result->getInsertedId()));
        exit;
    } else {
        abortwith(500, t('news.failed_to_create_news_item'));
    }
});


Route::post('/crud/news/update/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";

    if (!$Settings->featureEnabled('news', true)) {
        abortwith(500, t('error.news_not_enabled'));
    }

    for ($attempt = 0; $attempt < 3; $attempt++) {
        $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);
        if (!$news) abortwith(404, t('news.news_item_not_found'));
        $news = DB::doc2Arr($news);
        if (!NewsEditorial::canEdit($news, $Settings)) {
            abortwith(403, t('news.you_do_not_have_permission_to_edit_this_news_item'));
        }

        if (!valid_csrf_token($_POST['csrf_token'] ?? null, 'news')) {
            abortwith(403, t('news.the_form_has_expired_reload_the_news_form'));
        }
        $data = $_POST['news'] ?? [];
        if (!is_array($data)) abortwith(400, t('news.invalid_news_data'));
        $connections = normalize_news_connections($data);

        // basic validation
        if (empty($data['title']) || empty($data['content']) || empty($data['date'])) {
            abortwith(400, t('news.please_fill_in_all_required_fields'));
        }

        if (!NewsEditorial::isAuthor($news) && (isset($data['internal_only']) || isset($data['publication_choice_changed']))) {
            abortwith(403, t('news.only_the_author_can_change_the_outlet_selection_here'));
        }
        $configuration = $Settings->getNewsOutlets();
        try {
            $publication = NewsEditorial::publicationFields($data, $configuration, $news);
        } catch (InvalidArgumentException $exception) {
            abortwith(400, $exception->getMessage());
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
        $newsItem = array_merge($newsItem, $publication);
        // replace empty strings with null to avoid storing empty fields
        foreach ($newsItem as $key => $value) {
            if (is_string($value) && trim($value) === '') {
                $newsItem[$key] = null;
            }
        }

        if (!is_string($data['edit_token'] ?? null) || !hash_equals(NewsEditorial::editToken($news, 'original'), $data['edit_token'])) {
            abortwith(409, t('news.the_original_has_changed_keep_your_draft_and_reload_before_saving_again'));
        }
        $originalChanged = false;
        foreach ($newsItem as $key => $value) {
            if (in_array($key, ['updated_by', 'updated'], true)) continue;
            if (DB::doc2Arr($news[$key] ?? null) != $value) $originalChanged = true;
        }
        $newsItem['original_revision'] = (int) ($news['original_revision'] ?? 1) + ($originalChanged ? 1 : 0);
        if ($originalChanged) $newsItem['original_updated_at'] = date('c');
        $newsItem['revision'] = (int) ($news['revision'] ?? 0) + 1;
        $changes = ['requests' => NewsEditorial::requests($news), 'added' => [], 'withdrawn' => []];
        if (NewsEditorial::isAuthor($news) && isset($data['outlet_selection'])) {
            try {
                $changes = NewsEditorial::reconcileRequests($data['outlets'] ?? [], $configuration, $news, $_SESSION['username'], date('c'));
            } catch (InvalidArgumentException $exception) {
                abortwith(400, $exception->getMessage());
            }
            $newsItem['outlet_requests'] = $changes['requests'];
        } elseif (isset($data['outlets']) || isset($data['outlet_selection'])) {
            abortwith(403, t('news.only_the_author_can_change_outlet_requests_here'));
        }
        // The database revision protects the read/write window, not unrelated form scopes.
        $filter = ['_id' => DB::to_ObjectID($id), 'revision' => array_key_exists('revision', $news) ? $news['revision'] : ['$exists' => false]];
        $update = ['$set' => $newsItem];
        if (NewsEditorial::usesOutletWorkflow($newsItem)) $update['$unset'] = ['visibility' => ''];
        $result = $osiris->news->updateOne($filter, $update);
        if ($result->getMatchedCount() === 1) break;
    }
    if ($result->getMatchedCount() !== 1) {
        abortwith(409, t('news.the_news_is_being_edited_simultaneously_keep_your_draft_and_try_again'));
    }
    NewsEditorial::notifyRequests($DB, array_merge($news, $newsItem), $id, $changes, $configuration);
    header('Location: ' . ROOTPATH . '/news/view/' . e($id));
});


Route::post('/crud/news/upload-picture/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('news', true)) abortwith(500, t('error.news_not_enabled'));
    $mongo_id = $DB->to_ObjectID($id);
    // get news id    
    $news = $osiris->news->findOne(['_id' => $mongo_id]);
    if (empty($news)) {
        abortwith(404, t('news.news_item'), '/news');
    }
    if (!NewsEditorial::canEdit(DB::doc2Arr($news), $Settings)) {
        abortwith(403, t('news.you_do_not_have_permission_to_edit_this_news_item'));
    }
    if (!valid_csrf_token($_POST['csrf_token'] ?? null, 'news')) {
        abortwith(403, t('news.the_form_has_expired_reload_the_news_item'));
    }
    if (isset($_FILES["file"])) {
        // if ($_FILES['file']['type'] != 'image/jpeg') die('Wrong extension, only JPEG is allowed.');

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => t('error.file_upload_exceeds_limit'),
                2 => t('error.file_too_big_max_2MB'),
                3 => t('error.file_partially_uploaded'),
                4 => t('error.no_file_uploaded'),
                6 => t('error.file_upload_missing_temp'),
                7 => t('error.file_upload_write_failed'),
                8 => t('error.file_upload_stopped'),
                default => t('error.something_went_wrong') . " (" . $_FILES['file']['error'] . ")"
            };
            $_SESSION['msg'] = $errorMsg;
            $_SESSION['msg_type'] = "error";
        } else if ($_FILES["file"]["size"] > 2000000) {
            $_SESSION['msg'] = t('error.file_too_big_max_2MB');
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
                ['$inc' => ['revision' => 1, 'original_revision' => 1], '$set' => ['original_updated_at' => date('c'), 'image' => [
                    'data' => $img,
                    'type' => $type,
                    'extension' => $type,
                    'uploaded_by' => $_SESSION['username'],
                    'uploaded' => date('Y-m-d')
                ]]]
            );
            $_SESSION['msg'] = t('news.news_image_uploaded_successfully');
            $_SESSION['msg_type'] = "success";
            header("Location: " . ROOTPATH . "/news/view/$id");
            die;
            // printMsg(lang('error.file_upload_generic'), "error");
        }
    } else if (isset($_POST['delete'])) {
        $osiris->news->updateOne(
            ['_id' => $mongo_id],
            ['$unset' => ['image' => ""], '$inc' => ['revision' => 1, 'original_revision' => 1], '$set' => ['original_updated_at' => date('c')]]
        );
        $_SESSION['msg'] = t('news.news_image_deleted');
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
        abortwith(500, t('error.news_not_enabled'));
    }

    if (!$Settings->hasPermission('news.delete')) {
        abortwith(403, t('news.you_do_not_have_permission_to_delete_news_items'));
    }

    $id = $_POST['id'] ?? '';

    if (empty($id)) {
        abortwith(400, t('news.invalid_news_item_id'));
    }

    $result = $osiris->news->deleteOne(['_id' => DB::to_ObjectID($id)]);

    if ($result->getDeletedCount() === 1) {
        header('Location: ' . ROOTPATH . '/news');
        exit;
    } else {
        abortwith(500, t('news.failed_to_delete_news_item'));
    }
});


include_once BASEPATH . '/routes/news.editorial.php';

include_once BASEPATH . '/routes/news.reporting.php';
