<?php

require_once BASEPATH . '/php/NewsEditorial.php';
require_once BASEPATH . '/php/NewsReview.php';

Route::post('/crud/news/editorial/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->featureEnabled('news', true)) abortwith(500, t('error.news_not_enabled'));
    if (!$Settings->featureEnabled('news-editorial')) abortwith(403);
    $authorAction = in_array($_POST['action'] ?? null, ['approve_review', 'request_changes'], true);
    if (!$authorAction && !$Settings->hasPermission('news.editorial')) {
        abortwith(403, t('news.you_do_not_have_editorial_permission'));
    }
    if (!valid_csrf_token($_POST['csrf_token'] ?? null, 'news')) {
        abortwith(403, t('news.the_form_has_expired_reload_the_news_item'));
    }
    $action = $_POST['action'] ?? '';
    $input = $_POST['editorial'] ?? [];
    if (!is_string($action) || !is_array($input)) abortwith(400, t('news.invalid_editorial_data'));
    $outletActions = ['start', 'reopen', 'save', 'ready', 'decline', 'revise'];
    $scope = in_array($action, $outletActions, true) ? 'outlet:' . (is_string($input['outlet_id'] ?? null) ? $input['outlet_id'] : '') : ($action === 'connections' ? 'connections' : 'review');
    for ($attempt = 0; $attempt < 3; $attempt++) {
        $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);
        if (!$news) abortwith(404, t('news.news_item_not_found'));
        $news = DB::doc2Arr($news);
        if (!NewsEditorial::workflowAvailable($Settings->getNewsOutlets(), $news)) {
            abortwith(400, t('news.the_editorial_news_workflow_is_disabled'));
        }
        if ($authorAction && !NewsEditorial::isAuthor($news)) {
            abortwith(403, t('news.only_the_author_can_respond_to_this_review'));
        }
        if (!$authorAction && (!is_string($_POST['edit_token'] ?? null) || !hash_equals(NewsEditorial::editToken($news, $scope), $_POST['edit_token']))) {
            abortwith(409, t('news.these_fields_have_changed_keep_your_draft_and_reload_before_saving'));
        }
        try {
            if ($action === 'connections') {
                $data = $_POST['news'] ?? [];
                if (!is_array($data)) throw new InvalidArgumentException(t('news.invalid_news_connections'));
                $fields = NewsEditorial::connections($data, $news);
                $fields['featured'] = normalize_news_featured(['featured' => $data['featured'] ?? DB::doc2Arr($news['featured'] ?? [])], $fields);
                if (array_key_exists('topics', $data)) {
                    if ($data['topics'] === '') $data['topics'] = [];
                    if (!is_array($data['topics'])) throw new InvalidArgumentException(t('news.invalid_topics'));
                    foreach ($data['topics'] as $topic) {
                        if (!is_string($topic) || strlen($topic) > 200) throw new InvalidArgumentException(t('news.invalid_topics'));
                    }
                    $fields['topics'] = array_values(array_unique($data['topics']));
                }
                $changed = false;
                foreach ($fields as $key => $value) if (DB::doc2Arr($news[$key] ?? null) != $value) $changed = true;
                if ($changed) {
                    $fields['original_revision'] = (int) ($news['original_revision'] ?? 1) + 1;
                    $fields['original_updated_at'] = date('c');
                    $fields['updated_by'] = $_SESSION['username'];
                    $fields['updated'] = date('Y-m-d');
                    $history = NewsEditorial::history($news);
                    $history[] = ['action' => 'connections', 'actor' => $_SESSION['username'], 'at' => date('c')];
                    $fields['editorial_history'] = $history;
                }
            } elseif (in_array($action, ['request_review', 'approve_review', 'request_changes', 'cancel_review', 'release', 'revise'], true)) {
                $fields = NewsReview::apply($action, $input, $news, $Settings->getNewsOutlets(), $_SESSION['username'], $Settings->hasPermission('news.editorial'), date('c'));
            } else {
                $fields = NewsEditorial::apply($action, $input, $news, $Settings->getNewsOutlets(), $_SESSION['username'], date('c'));
            }
        } catch (InvalidArgumentException $exception) {
            abortwith(400, $exception->getMessage());
        }
        $fields['revision'] = (int) ($news['revision'] ?? 0) + 1;
        $filter = ['_id' => DB::to_ObjectID($id), 'revision' => array_key_exists('revision', $news) ? $news['revision'] : ['$exists' => false]];
        $result = $osiris->news->updateOne($filter, ['$set' => $fields]);
        if ($result->getMatchedCount() === 1) break;
    }
    if ($result->getMatchedCount() !== 1) {
        abortwith(409, t('news.the_news_is_being_edited_simultaneously_keep_your_draft_and_try_again'));
    }
    NewsReview::notify($DB, $action, array_merge($news, $fields));
    $_SESSION['msg'] = match ($action) {
        'request_review' => t('news.author_review_requested'),
        'approve_review' => t('news.contributions_released'),
        'request_changes' => t('news.change_request_sent_to_the_editorial_team'),
        'cancel_review' => t('news.author_review_ended'),
        'release' => t('news.prepared_outlets_released'),
        default => t('news.editorial_changes_saved'),
    };
    $_SESSION['msg_type'] = 'success';
    $anchor = $authorAction ? 'news-author-review' : ($action === 'connections' ? 'editorial-connections' : 'news-editorial');
    header('Location: ' . ROOTPATH . '/news/view/' . e($id) . '#' . $anchor);
    exit;
}, 'login');
