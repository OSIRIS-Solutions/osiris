<?php

require_once BASEPATH . '/php/NewsEditorial.php';

Route::post('/crud/news/editorial/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->featureEnabled('news', true)) abortwith(500, lang('News are not enabled.', 'News sind nicht aktiviert.'));
    if (!$Settings->hasPermission('news.editorial')) {
        abortwith(403, lang('You do not have editorial permission.', 'Du hast keine Redaktionsberechtigung.'));
    }
    if (!NewsRequests::validCsrf($_POST['csrf_token'] ?? null)) {
        abortwith(403, lang('The form has expired. Reload the news item.', 'Das Formular ist abgelaufen. Bitte lade die News neu.'));
    }
    $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);
    if (!$news) abortwith(404, lang('News item not found.', 'Nachricht nicht gefunden.'));
    $news = DB::doc2Arr($news);
    $revision = filter_var($_POST['news_revision'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    if ($revision === false || $revision !== (int) ($news['revision'] ?? 0)) {
        abortwith(409, lang('This news item has changed. Reload it before saving again.', 'Diese News wurde zwischenzeitlich geändert. Bitte lade sie vor dem erneuten Speichern neu.'));
    }
    $action = $_POST['action'] ?? '';
    $input = $_POST['editorial'] ?? [];
    if (!is_string($action) || !is_array($input)) abortwith(400, lang('Invalid editorial data.', 'Ungültige Redaktionsdaten.'));
    try {
        if ($action === 'connections') {
            $data = $_POST['news'] ?? [];
            if (!is_array($data)) throw new InvalidArgumentException(lang('Invalid news connections.', 'Ungültige News-Verknüpfungen.'));
            $fields = NewsEditorial::connections($data, $news);
            $fields['featured'] = normalize_news_featured(['featured' => $data['featured'] ?? DB::doc2Arr($news['featured'] ?? [])], $fields);
            if (array_key_exists('topics', $data)) {
                if ($data['topics'] === '') $data['topics'] = [];
                if (!is_array($data['topics'])) throw new InvalidArgumentException(lang('Invalid topics.', 'Ungültige Forschungsbereiche.'));
                foreach ($data['topics'] as $topic) {
                    if (!is_string($topic) || strlen($topic) > 200) throw new InvalidArgumentException(lang('Invalid topics.', 'Ungültige Forschungsbereiche.'));
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
        } else {
            $fields = NewsEditorial::apply($action, $input, $news, $Settings->getNewsOutlets(), $_SESSION['username'], date('c'));
        }
    } catch (InvalidArgumentException $exception) {
        abortwith(400, $exception->getMessage());
    }
    $fields['revision'] = $revision + 1;
    $filter = ['_id' => DB::to_ObjectID($id), 'revision' => array_key_exists('revision', $news) ? $news['revision'] : ['$exists' => false]];
    $result = $osiris->news->updateOne($filter, ['$set' => $fields]);
    if ($result->getMatchedCount() !== 1) {
        abortwith(409, lang('This news item has changed. Reload it before saving again.', 'Diese News wurde zwischenzeitlich geändert. Bitte lade sie vor dem erneuten Speichern neu.'));
    }
    $_SESSION['msg'] = lang('Editorial changes saved.', 'Redaktionelle Änderungen gespeichert.');
    $_SESSION['msg_type'] = 'success';
    $anchor = $action === 'connections' ? 'editorial-connections' : 'news-editorial';
    header('Location: ' . ROOTPATH . '/news/view/' . e($id) . '#' . $anchor);
    exit;
}, 'login');
