<?php

require_once dirname(__DIR__) . '/php/csrf.php';

/** News outlet administration. Keep the original news independent of editorial outlets. */
Route::get('/admin/news', function () {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->hasPermission('admin.see') && !$Settings->hasPermission('news.outlets')) {
        abortwith(403, t('news.you_do_not_have_permission_to_manage_news_outlets'));
    }
    csrf_token('news_outlets');
    $configuration = $Settings->getNewsOutlets();
    $revision = NewsOutlets::revision($configuration);
    $outletForm = $_SESSION['news_outlets_form'] ?? null;
    unset($_SESSION['news_outlets_form']);
    if ($outletForm !== null) {
        $configuration = NewsOutlets::read($outletForm['input']);
        $revision = $outletForm['revision'];
    }
    $breadcrumb = [
        ['name' => t('common.settings'), 'path' => '/admin'],
        ['name' => t('admin.news_outlets')],
    ];
    include BASEPATH . '/header.php';
    include BASEPATH . '/pages/admin/news.php';
    include BASEPATH . '/footer.php';
}, 'login');

Route::post('/crud/admin/news', function () {
    include_once BASEPATH . '/php/init.php';
    if (!$Settings->hasPermission('admin.see') && !$Settings->hasPermission('news.outlets')) {
        abortwith(403, t('news.you_do_not_have_permission_to_manage_news_outlets'));
    }
    if (!valid_csrf_token($_POST['csrf_token'] ?? null, 'news_outlets')) {
        abortwith(403, t('news.the_form_has_expired_reload_the_settings'));
    }
    $input = $_POST['configuration'] ?? [];
    $revision = $_POST['revision'] ?? '';
    if (!is_array($input) || !is_string($revision)) {
        abortwith(400, t('news.invalid_outlet_configuration'));
    }
    $current = $Settings->getNewsOutlets();
    try {
        if (!hash_equals(NewsOutlets::revision($current), $revision)) {
            throw new InvalidArgumentException(t('news.the_settings_have_changed_in_the_meantime_reload_this_page_before'));
        }
        $configuration = NewsOutlets::normalize($input, $current);
        $Settings->set('news-outlets', $configuration);
        $_SESSION['msg'] = t('news.news_outlets_saved');
        $_SESSION['msg_type'] = 'success';
    } catch (InvalidArgumentException $exception) {
        $_SESSION['news_outlets_form'] = ['input' => $input, 'revision' => $revision];
        $_SESSION['msg'] = $exception->getMessage();
        $_SESSION['msg_type'] = 'error';
    }
    header('Location: ' . ROOTPATH . '/admin/news');
    exit;
}, 'login');
