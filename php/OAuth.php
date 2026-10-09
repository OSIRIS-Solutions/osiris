<?php

/** Shared CONFIG.php switches for standalone and parallel OAuth login. */
function osirisOAuthEnabled(): bool
{
    return defined('USER_MANAGEMENT')
        && in_array(strtoupper(USER_MANAGEMENT), ['AUTH', 'OAUTH'], true)
        && defined('OAUTH') && is_string(OAUTH) && trim(OAUTH) !== '';
}

function osirisOAuthConfigurationError(): ?string
{
    if (!osirisOAuthEnabled()) {
        return 'OAuth is not enabled in CONFIG.php.';
    }
    foreach (['AUTHORITY', 'CLIENT_ID', 'CLIENT_SECRET', 'REDIRECT_URI', 'SCOPES'] as $key) {
        if (!defined($key) || !is_string(constant($key)) || trim(constant($key)) === '') {
            return 'OAuth configuration is incomplete: ' . $key . ' is missing in CONFIG.php.';
        }
    }
    foreach (['AUTHORITY', 'REDIRECT_URI'] as $key) {
        $url = parse_url(constant($key));
        if (!$url || empty($url['host']) || !in_array($url['scheme'] ?? '', ['http', 'https'], true)) {
            return 'Invalid ' . $key . ' URL in CONFIG.php.';
        }
    }
    return null;
}

function osirisOAuthLabel(): string
{
    return defined('OAUTH') ? OAUTH : 'Single Sign-On';
}

function osirisOAuthFail(string $message): void
{
    osirisOAuthClearAttempt();
    $_SESSION['msg'] = $message;
    $_SESSION['msg_type'] = 'error';
    header('Location: ' . ROOTPATH . '/user/login');
    exit;
}

function osirisOAuthLinked($person): bool
{
    return !empty($person['sso']['issuer']) && !empty($person['sso']['subject']);
}

function osirisOAuthClearAttempt(): void
{
    unset($_SESSION['oauth_login'], $_SESSION['oauth_link'], $_SESSION['oauth_registration'], $_SESSION['openid_connect_state'],
        $_SESSION['openid_connect_nonce'], $_SESSION['openid_connect_code_verifier']);
}

function osirisOAuthClient(): \Jumbojett\OpenIDConnectClient
{
    require_once BASEPATH . '/vendor/autoload.php';
    // Stable upstream still has implicit nullable declarations deprecated in PHP 8.4.
    // Limit suppression to loading that class, so redirects cannot be broken by notices.
    $reporting = error_reporting();
    try {
        error_reporting($reporting & ~E_DEPRECATED);
        class_exists(\Jumbojett\OpenIDConnectClient::class);
    } finally {
        error_reporting($reporting);
    }
    // Keep the existing Keycloak endpoint prefix in CONFIG.php; discovery uses the realm URL.
    $issuer = preg_replace('~/protocol/openid-connect/?$~', '', rtrim(AUTHORITY, '/'));
    if (parse_url($issuer, PHP_URL_HOST) === 'login.microsoftonline.com' && !str_ends_with($issuer, '/v2.0')) {
        $issuer .= '/v2.0';
    }
    $client = new \Jumbojett\OpenIDConnectClient($issuer, CLIENT_ID, CLIENT_SECRET);
    $client->setRedirectURL(REDIRECT_URI);
    $client->addScope(preg_split('/\s+/', trim(SCOPES)));
    $client->setCodeChallengeMethod('S256');
    $client->setTimeout(15);
    $client->setHttpUpgradeInsecureRequests(false);
    return $client;
}

function osirisOAuthOwnAccount(): string
{
    $username = $_SESSION['username'] ?? '';
    if (empty($_SESSION['loggedin']) || !$username || ($_SESSION['realuser'] ?? $username) !== $username) {
        osirisOAuthFail(lang('Please log in with your own OSIRIS account first.', 'Bitte melde dich zuerst mit deinem eigenen OSIRIS-Konto an.'));
    }
    return $username;
}

function osirisOAuthStart(string $purpose, ?string $username = null): void
{
    if ($error = osirisOAuthConfigurationError()) osirisOAuthFail($error);
    osirisOAuthClearAttempt();
    $_SESSION['oauth_login'] = ['purpose' => $purpose, 'username' => $username, 'created' => time()];
    // The library generates and stores state, nonce and the PKCE verifier.
    $_REQUEST = [];
    try {
        osirisOAuthClient()->authenticate();
    } catch (\Throwable $e) {
        error_log('OSIRIS: unable to start OIDC authentication (' . get_class($e) . ')');
        osirisOAuthFail(lang('Unable to contact the single sign-on provider. Please check the configuration.', 'Der SSO-Anbieter konnte nicht erreicht werden. Bitte prüfe die Konfiguration.'));
    }
    exit;
}

function osirisOAuthSession($person): void
{
    session_regenerate_id(true);
    unset($_SESSION['realuser']);
    $_SESSION['username'] = $person['username'];
    $_SESSION['name'] = $person['displayname'];
    $_SESSION['loggedin'] = true;
    $_SESSION['auth_method'] = 'oauth';
}

function osirisOAuthIdentityIndex($database): void
{
    $database->persons->createIndex(['sso.issuer' => 1, 'sso.subject' => 1], [
        'name' => 'unique_sso_identity', 'unique' => true,
        'partialFilterExpression' => ['sso.issuer' => ['$type' => 'string'], 'sso.subject' => ['$type' => 'string']],
    ]);
}

/** Called only after the OIDC library has verified the ID token and UserInfo subject. */
function osirisOAuthLinkProfile($database, string $username, array $identity): void
{
    osirisOAuthIdentityIndex($database);
    $result = $database->persons->updateOne(
        ['username' => $username, 'sso' => ['$exists' => false]],
        ['$set' => ['sso' => [
            'issuer' => $identity['issuer'], 'subject' => $identity['subject'],
            'linked' => date('Y-m-d H:i:s'),
        ], 'mail' => $identity['mail']]]
    );
    if ($result->getMatchedCount() !== 1) {
        throw new \RuntimeException('Account already linked or missing.');
    }
    // Profile state is authoritative: all local password paths check it, even if cleanup fails.
    $database->accounts->updateOne(['username' => $username],
        ['$unset' => ['password' => '', 'hash' => '', 'reset' => '']]);
    $database->rememberTokens->deleteMany(['username' => $username]);
}

/** One-time upgrade of the old OAuth-only username mapping; never used with AUTH. */
function osirisOAuthUpgradeLegacy($database, array $identity, object $info, object $claims)
{
    if (strtoupper(USER_MANAGEMENT) !== 'OAUTH') return null;
    $mail = $info->email ?? $claims->email ?? '';
    $preferred = $info->preferred_username ?? $claims->preferred_username ?? $mail;
    // Microsoft previously preferred Graph mail, then userPrincipalName.
    $externalName = parse_url(AUTHORITY, PHP_URL_HOST) === 'login.microsoftonline.com'
        ? ($mail !== '' ? $mail : $preferred) : $preferred;
    if (!is_string($externalName) || $externalName === '') return null;
    $username = explode('@', $externalName)[0];
    if ($username === '') return null;
    $person = $database->persons->findOne(['username' => $username, 'sso' => ['$exists' => false]]);
    // Local accounts must use the explicit password-confirmed linking workflow.
    if (!$person || $database->accounts->findOne(['username' => $username])) return null;
    osirisOAuthIdentityIndex($database);
    $sso = ['issuer' => $identity['issuer'], 'subject' => $identity['subject'], 'linked' => date('Y-m-d H:i:s')];
    $result = $database->persons->updateOne(
        ['_id' => $person['_id'], 'sso' => ['$exists' => false]], ['$set' => ['sso' => $sso]]
    );
    if ($result->getMatchedCount() !== 1) {
        throw new \RuntimeException('Legacy profile changed during SSO migration.');
    }
    $person['sso'] = $sso;
    return $person;
}

function osirisOAuthCallback(): void
{
    global $osiris;
    if ($error = osirisOAuthConfigurationError()) osirisOAuthFail($error);
    $attempt = $_SESSION['oauth_login'] ?? null;
    // Reject unsolicited/expired callbacks before the library exchanges a code.
    if (!is_array($attempt) || ($attempt['created'] ?? 0) < time() - 600
        || !is_string($_GET['state'] ?? null) || empty($_SESSION['openid_connect_state'])
        || !hash_equals($_SESSION['openid_connect_state'], $_GET['state'])) {
        osirisOAuthFail(lang('Invalid or expired login request. Please try again.', 'Ungültige oder abgelaufene Login-Anfrage. Bitte versuche es erneut.'));
    }
    if (!is_string($_GET['code'] ?? null) || $_GET['code'] === '' || isset($_GET['error'])) {
        osirisOAuthFail(lang('Single sign-on was cancelled or failed.', 'Single Sign-On wurde abgebrochen oder ist fehlgeschlagen.'));
    }
    if ($attempt['purpose'] === 'link' && osirisOAuthOwnAccount() !== $attempt['username']) {
        osirisOAuthFail(lang('Your OSIRIS session changed. Please start again.', 'Deine OSIRIS-Sitzung hat sich geändert. Bitte beginne erneut.'));
    }
    try {
        $expectedNonce = $_SESSION['openid_connect_nonce'] ?? '';
        $client = osirisOAuthClient();
        $_REQUEST = $_GET;
        if (!$client->authenticate()) throw new \RuntimeException('Authentication failed');
        $claims = $client->getVerifiedClaims();
        $info = $client->requestUserInfo();
        if (!is_string($claims->iss ?? null) || !is_string($claims->sub ?? null)
            || $claims->sub === '' || !is_string($claims->nonce ?? null) || $expectedNonce === ''
            || !hash_equals($expectedNonce, $claims->nonce) || !is_int($claims->exp ?? null)
            || $claims->exp < time() - 60 || !is_object($info) || ($info->sub ?? null) !== $claims->sub) {
            throw new \RuntimeException('Invalid identity');
        }
        $identity = ['issuer' => $claims->iss, 'subject' => $claims->sub,
            'mail' => $info->email ?? $claims->email ?? ''];
    } catch (\Throwable $e) {
        error_log('OSIRIS: OIDC authentication failed (' . get_class($e) . ')');
        osirisOAuthFail(lang('Single sign-on could not be verified. Please try again.', 'Single Sign-On konnte nicht geprüft werden. Bitte versuche es erneut.'));
    }
    osirisOAuthClearAttempt();
    $person = $osiris->persons->findOne(['sso.issuer' => $identity['issuer'], 'sso.subject' => $identity['subject']]);
    if ($attempt['purpose'] === 'link') {
        if ($person) osirisOAuthFail(lang('This SSO account is already linked to an OSIRIS profile.', 'Dieses SSO-Konto ist bereits mit einem OSIRIS-Profil verknüpft.'));
        if (!is_string($identity['mail']) || !filter_var($identity['mail'], FILTER_VALIDATE_EMAIL)) {
            osirisOAuthFail(lang('The provider did not return a valid email address. Your account was not changed.', 'Der Anbieter hat keine gültige E-Mail-Adresse geliefert. Dein Konto wurde nicht geändert.'));
        }
        $_SESSION['oauth_link'] = $identity + ['username' => $attempt['username'], 'created' => time()];
        header('Location: ' . ROOTPATH . '/user/oauth-link');
        exit;
    }
    if (!$person && strtoupper(USER_MANAGEMENT) === 'OAUTH') {
        try {
            $person = osirisOAuthUpgradeLegacy($osiris, $identity, $info, $claims);
        } catch (\Throwable $e) {
            error_log('OSIRIS: legacy SSO migration failed (' . get_class($e) . ')');
            osirisOAuthFail(lang('Your existing profile could not be assigned. Please try again or contact the administrator.', 'Dein bestehendes Profil konnte nicht zugeordnet werden. Bitte versuche es erneut oder kontaktiere die Administration.'));
        }
    }
    if (!$person) {
        // Keep the verified identity only in the session; it is not an OSIRIS login yet.
        session_regenerate_id(true);
        $prefill = [];
        foreach (['first' => 'given_name', 'last' => 'family_name', 'mail' => 'email', 'username' => 'preferred_username'] as $field => $claim) {
            $value = $info->$claim ?? $claims->$claim ?? '';
            $prefill[$field] = is_string($value) ? $value : '';
        }
        $_SESSION['oauth_registration'] = $identity + [
            'prefill' => $prefill, 'created' => time(), 'csrf' => bin2hex(random_bytes(32)), 'confirmed' => false,
        ];
        header('Location: ' . ROOTPATH . '/user/oauth-register');
        exit;
    }
    $update = ['lastlogin' => date('Y-m-d')];
    // External usernames and emails can change; issuer + subject still select the same profile.
    if (is_string($identity['mail']) && filter_var($identity['mail'], FILTER_VALIDATE_EMAIL)) {
        $update['mail'] = $identity['mail'];
    }
    $osiris->persons->updateOne(['_id' => $person['_id']], ['$set' => $update]);
    osirisOAuthSession($person);
    header('Location: ' . ROOTPATH . '/');
    exit;
}
