<?php

Route::get('/user/oauth-link', function () {
    include_once BASEPATH . '/php/init.php';
    if (strtoupper(USER_MANAGEMENT) !== 'AUTH' || !osirisOAuthEnabled()) osirisOAuthFail('SSO is not configured.');
    $username = osirisOAuthOwnAccount();
    $person = $osiris->persons->findOne(['username' => $username]);
    if (osirisOAuthLinked($person)) {
        header('Location: ' . ROOTPATH . '/user/edit/' . rawurlencode($username));
        exit;
    }
    $_SESSION['oauth_link_csrf'] ??= bin2hex(random_bytes(32));
    $pendingLink = $_SESSION['oauth_link'] ?? null;
    if ($pendingLink && (($pendingLink['created'] ?? 0) < time() - 600 || $pendingLink['username'] !== $username)) {
        osirisOAuthClearAttempt();
        $pendingLink = null;
    }
    $page = 'oauth-link';
    include BASEPATH . '/pages/user/auth-page.php';
}, 'login');

Route::post('/user/oauth-link', function () {
    include_once BASEPATH . '/php/init.php';
    if (strtoupper(USER_MANAGEMENT) !== 'AUTH' || !osirisOAuthEnabled()) osirisOAuthFail('SSO is not configured.');
    $username = osirisOAuthOwnAccount();
    if (!is_string($_POST['csrf'] ?? null) || empty($_SESSION['oauth_link_csrf'])
        || !hash_equals($_SESSION['oauth_link_csrf'], $_POST['csrf'])) {
        osirisOAuthFail(lang('Invalid request. Please start again.', 'Ungültige Anfrage. Bitte beginne erneut.'));
    }
    if (($_POST['action'] ?? '') === 'cancel') {
        osirisOAuthClearAttempt();
        header('Location: ' . ROOTPATH . '/user/edit/' . rawurlencode($username));
        exit;
    }
    $person = $osiris->persons->findOne(['username' => $username]);
    if (!$person || osirisOAuthLinked($person)) osirisOAuthFail(lang('Account is already linked or missing.', 'Konto ist bereits verknüpft oder nicht vorhanden.'));
    if (($_POST['action'] ?? '') === 'confirm') {
        $identity = $_SESSION['oauth_link'] ?? null;
        if (!$identity || $identity['username'] !== $username || $identity['created'] < time() - 600) {
            osirisOAuthFail(lang('Link request expired. Please start again.', 'Die Verknüpfungsanfrage ist abgelaufen. Bitte beginne erneut.'));
        }
        try {
            osirisOAuthLinkProfile($osiris, $username, $identity);
        } catch (\Throwable $e) {
            error_log('OSIRIS: SSO linking failed (' . get_class($e) . ')');
            osirisOAuthFail(lang('The link could not be completed. Please check your account settings before trying again.', 'Die Verknüpfung konnte nicht abgeschlossen werden. Bitte prüfe deine Account-Einstellungen, bevor du es erneut versuchst.'));
        }
        osirisOAuthClearAttempt();
        unset($_SESSION['oauth_link_csrf']);
        setcookie('osiris-remember', '', ['expires' => time() - 3600, 'path' => ROOTPATH . '/',
            'secure' => !empty($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax']);
        $person = $osiris->persons->findOne(['username' => $username]);
        osirisOAuthSession($person);
        $_SESSION['msg'] = lang('Your profile is now linked. Please use single sign-on for future logins.', 'Dein Profil ist jetzt verknüpft. Bitte nutze künftig Single Sign-On zur Anmeldung.');
        $_SESSION['msg_type'] = 'success';
        header('Location: ' . ROOTPATH . '/user/edit/' . rawurlencode($username));
        exit;
    }
    if (($_POST['action'] ?? '') !== 'start') osirisOAuthFail('Invalid request.');
    $account = $osiris->accounts->findOne(['username' => $username]);
    if (!is_string($_POST['password'] ?? null) || empty($account['password'])
        || !password_verify($_POST['password'], $account['password'])) {
        $_SESSION['msg'] = lang('Your OSIRIS password is incorrect.', 'Dein OSIRIS-Passwort ist falsch.');
        $_SESSION['msg_type'] = 'error';
        header('Location: ' . ROOTPATH . '/user/oauth-link');
        exit;
    }
    osirisOAuthStart('link', $username);
}, 'login');
