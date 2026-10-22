<?php

/** News outlet administration. Keep the original news independent of editorial outlets. */
Route::get('/admin/news', function () {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->hasPermission('admin.see') && !$Settings->hasPermission('news.outlets')) {
        abortwith(403, lang('You do not have permission to manage news outlets.', 'Du hast keine Berechtigung, News-Outlets zu verwalten.'));
    }
    if (empty($_SESSION['news_outlets_csrf'])) {
        $_SESSION['news_outlets_csrf'] = bin2hex(random_bytes(32));
    }
    $configuration = $Settings->getNewsOutlets();
    $revision = NewsOutlets::revision($configuration);
    $outletForm = $_SESSION['news_outlets_form'] ?? null;
    unset($_SESSION['news_outlets_form']);
    if ($outletForm !== null) {
        $configuration = NewsOutlets::read($outletForm['input']);
        $revision = $outletForm['revision'];
    }
    $breadcrumb = [
        ['name' => lang('Settings', 'Einstellungen'), 'path' => '/admin'],
        ['name' => lang('News outlets', 'News-Outlets')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/admin/news.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::post('/crud/admin/news', function () {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->hasPermission('admin.see') && !$Settings->hasPermission('news.outlets')) {
        abortwith(403, lang('You do not have permission to manage news outlets.', 'Du hast keine Berechtigung, News-Outlets zu verwalten.'));
    }
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || empty($_SESSION['news_outlets_csrf']) || !hash_equals($_SESSION['news_outlets_csrf'], $token)) {
        abortwith(403, lang('The form has expired. Reload the settings.', 'Das Formular ist abgelaufen. Bitte lade die Einstellungen neu.'));
    }
    $input = $_POST['configuration'] ?? [];
    $revision = $_POST['revision'] ?? '';
    if (!is_array($input) || !is_string($revision)) {
        abortwith(400, lang('Invalid outlet configuration.', 'Ungültige Outlet-Konfiguration.'));
    }
    $current = $Settings->getNewsOutlets();
    try {
        if (!hash_equals(NewsOutlets::revision($current), $revision)) {
            throw new InvalidArgumentException(lang('The settings have changed in the meantime. Reload this page before saving again.', 'Die Einstellungen wurden zwischenzeitlich geändert. Bitte lade diese Seite vor dem erneuten Speichern neu.'));
        }
        $configuration = NewsOutlets::normalize($input, $current);
        $Settings->set('news-outlets', $configuration);
        $_SESSION['msg'] = lang('News outlets saved.', 'News-Outlets gespeichert.');
        $_SESSION['msg_type'] = 'success';
    } catch (InvalidArgumentException $exception) {
        $_SESSION['news_outlets_form'] = ['input' => $input, 'revision' => $revision];
        $_SESSION['msg'] = $exception->getMessage();
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: ' . ROOTPATH . '/admin/news');
    exit;
}, 'login');
