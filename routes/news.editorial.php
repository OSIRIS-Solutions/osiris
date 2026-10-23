<?php

require_once BASEPATH . '/php/NewsEditorial.php';
require_once BASEPATH . '/php/NewsReview.php';

Route::post('/crud/news/editorial/([a-f0-9]{24})', function ($id) {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->featureEnabled('news', true)) abortwith(500, lang('News are not enabled.', 'News sind nicht aktiviert.'));
    $authorAction = in_array($_POST['action'] ?? null, ['approve_review', 'request_changes'], true);
    if (!$authorAction && !$Settings->hasPermission('news.editorial')) {
        abortwith(403, lang('You do not have editorial permission.', 'Du hast keine Redaktionsberechtigung.'));
    }
    if (!valid_csrf_token($_POST['csrf_token'] ?? null, 'news')) {
        abortwith(403, lang('The form has expired. Reload the news item.', 'Das Formular ist abgelaufen. Bitte lade die News neu.'));
    }
    $action = $_POST['action'] ?? '';
    $input = $_POST['editorial'] ?? [];
    if (!is_string($action) || !is_array($input)) abortwith(400, lang('Invalid editorial data.', 'Ungültige Redaktionsdaten.'));
    $outletActions = ['start', 'reopen', 'save', 'ready', 'decline', 'revise'];
    $scope = in_array($action, $outletActions, true) ? 'outlet:' . (is_string($input['outlet_id'] ?? null) ? $input['outlet_id'] : '') : ($action === 'connections' ? 'connections' : 'review');
    for ($attempt = 0; $attempt < 3; $attempt++) {
        $news = $osiris->news->findOne(['_id' => DB::to_ObjectID($id)]);
        if (!$news) abortwith(404, lang('News item not found.', 'Nachricht nicht gefunden.'));
        $news = DB::doc2Arr($news);
        if ($authorAction && !NewsEditorial::isAuthor($news)) {
            abortwith(403, lang('Only the author can respond to this review.', 'Nur die Autorin oder der Autor kann auf diese Prüfung antworten.'));
        }
        if (!$authorAction && (!is_string($_POST['edit_token'] ?? null) || !hash_equals(NewsEditorial::editToken($news, $scope), $_POST['edit_token']))) {
            abortwith(409, lang('These fields have changed. Keep your draft and reload before saving again.', 'Diese Felder wurden inzwischen geändert. Sichere deinen Entwurf und lade die Seite vor dem erneuten Speichern neu.'));
        }
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
        abortwith(409, lang('The news is being edited simultaneously. Keep your draft and try again.', 'Die News wird gleichzeitig bearbeitet. Sichere deinen Entwurf und versuche es erneut.'));
    }
    NewsReview::notify($DB, $action, array_merge($news, $fields));
    $_SESSION['msg'] = match ($action) {
        'request_review' => lang('Author review requested.', 'Autorenprüfung angefordert.'),
        'approve_review' => lang('Contributions released.', 'Beiträge freigegeben.'),
        'request_changes' => lang('Change request sent to the editorial team.', 'Änderungswunsch an die Redaktion gesendet.'),
        'cancel_review' => lang('Author review ended.', 'Autorenprüfung beendet.'),
        'release' => lang('Prepared outlets released.', 'Vorbereitete Outlets freigegeben.'),
        default => lang('Editorial changes saved.', 'Redaktionelle Änderungen gespeichert.'),
    };
    $_SESSION['msg_type'] = 'success';
    $anchor = $authorAction ? 'news-author-review' : ($action === 'connections' ? 'editorial-connections' : 'news-editorial');
    header('Location: ' . ROOTPATH . '/news/view/' . e($id) . '#' . $anchor);
    exit;
}, 'login');
