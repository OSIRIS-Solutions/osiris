<?php

/** Anonymous SSO onboarding. No OSIRIS session is granted before registration. */
function osirisOAuthRegistrationPending(): array
{
    if (!osirisOAuthEnabled()) osirisOAuthFail('SSO is not configured.');
    if (!empty($_SESSION['loggedin'])) {
        osirisOAuthClearAttempt();
        header('Location: ' . ROOTPATH . '/home');
        exit;
    }
    $pending = $_SESSION['oauth_registration'] ?? null;
    if (!is_array($pending) || ($pending['created'] ?? 0) < time() - 1800) {
        osirisOAuthFail(lang('Your SSO registration has expired. Please log in with SSO again.', 'Deine SSO-Registrierung ist abgelaufen. Bitte melde dich erneut über SSO an.'));
    }
    return $pending;
}

function osirisOAuthRegistrationPage(array $pending, array $authMessages = []): void
{
    global $Settings, $Groups;
    $registrationIsSso = true;
    $registrationAction = '/user/oauth-register';
    $registrationRequiresToken = false;
    $registrationData = $pending['prefill'];
    foreach (['first', 'last', 'academic_title', 'telephone', 'gender', 'is_scientist'] as $key) {
        if (is_string($_POST['values'][$key] ?? null)) $registrationData[$key] = $_POST['values'][$key];
    }
    $registrationMailLocked = is_string($pending['mail']) && filter_var($pending['mail'], FILTER_VALIDATE_EMAIL);
    $registrationData['mail'] = $registrationMailLocked ? $pending['mail'] : (is_string($_POST['values']['mail'] ?? null) ? $_POST['values']['mail'] : '');
    $registrationUsername = is_string($_POST['username'] ?? null) ? $_POST['username']
        : preg_replace('/[^a-zA-Z0-9_.-]/', '', explode('@', $pending['prefill']['username'])[0]);
    $registrationToken = is_string($_POST['token'] ?? null) ? $_POST['token'] : '';
    $registrationTokenRequired = (string) ($Settings->get('auth-token') ?? '') !== '';
    $registrationCsrf = $pending['csrf'];
    $registrationUnits = is_array($_POST['units'] ?? null) ? $_POST['units'] : [];
    $page = 'register';
    include BASEPATH . '/pages/user/auth-page.php';
}

/** Allowlist profile fields; roles, identity and email cannot be supplied by the browser. */
function osirisOAuthRegistrationPerson(array $pending, array $input, array $groups): array
{
    $values = is_array($input['values'] ?? null) ? $input['values'] : [];
    $person = [];
    foreach (['first', 'last', 'academic_title', 'telephone', 'gender'] as $key) {
        $person[$key] = is_string($values[$key] ?? null) ? trim($values[$key]) : '';
    }
    $person['username'] = is_string($input['username'] ?? null) ? trim($input['username']) : '';
    if (!preg_match('/^[a-zA-Z0-9_.-]{1,100}$/D', $person['username'])) {
        throw new \InvalidArgumentException(lang('Use letters, numbers, dots, underscores or hyphens for the username.', 'Verwende für den Nutzernamen Buchstaben, Zahlen, Punkte, Unterstriche oder Bindestriche.'));
    }
    if ($person['first'] === '' || $person['last'] === '') {
        throw new \InvalidArgumentException(lang('Please enter your first and last name.', 'Bitte gib Vor- und Nachnamen an.'));
    }
    // A valid provider email is authoritative; missing email can be completed in the form.
    $person['mail'] = is_string($pending['mail']) && filter_var($pending['mail'], FILTER_VALIDATE_EMAIL)
        ? $pending['mail'] : (is_string($values['mail'] ?? null) ? trim($values['mail']) : '');
    if (!filter_var($person['mail'], FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException(lang('Please enter a valid email address.', 'Bitte gib eine gültige E-Mail-Adresse an.'));
    }
    $person['is_scientist'] = ($values['is_scientist'] ?? '') === '1';
    $person['roles'] = $person['is_scientist'] ? ['scientist'] : [];
    $person['gender'] = in_array($person['gender'], ['m', 'f', 'd', 'n'], true) ? $person['gender'] : 'n';
    $person['displayname'] = $person['first'] . ' ' . $person['last'];
    $person['formalname'] = $person['last'] . ', ' . $person['first'];
    $person['first_abbr'] = '';
    foreach (preg_split('/\s+/', $person['first']) as $name) $person['first_abbr'] .= ' ' . mb_substr($name, 0, 1) . '.';
    $person['created'] = $person['lastlogin'] = date('Y-m-d');
    $person['is_active'] = true;
    $person['sso'] = ['issuer' => $pending['issuer'], 'subject' => $pending['subject'], 'linked' => date('Y-m-d H:i:s')];
    $person['units'] = [];
    $selected = $input['units'] ?? [];
    if (!is_array($selected)) throw new \InvalidArgumentException(lang('Invalid organisational units.', 'Ungültige Organisationseinheiten.'));
    $available = [];
    foreach ($groups as $group) if (empty($group['inactive'])) $available[(string) $group['id']] = $group['id'];
    foreach (array_unique($selected, SORT_REGULAR) as $unit) {
        if (!is_string($unit) || !array_key_exists($unit, $available)) {
            throw new \InvalidArgumentException(lang('Please select valid organisational units.', 'Bitte wähle gültige Organisationseinheiten aus.'));
        }
        $person['units'][] = ['id' => bin2hex(random_bytes(8)), 'unit' => $available[$unit],
            'start' => null, 'end' => null, 'scientific' => $person['is_scientist']];
    }
    return $person;
}

Route::get('/user/oauth-register', function () {
    include_once BASEPATH . '/php/init.php';
    $pending = osirisOAuthRegistrationPending();
    if (empty($pending['confirmed'])) {
        $page = 'oauth-choice';
        include BASEPATH . '/pages/user/auth-page.php';
        return;
    }
    if (!$Settings->get('auth-self-registration', true)) {
        osirisOAuthFail(lang('Self-registration is disabled. Please contact the administrator.', 'Die Selbstregistrierung ist deaktiviert. Bitte kontaktiere die Administration.'));
    }
    osirisOAuthRegistrationPage($pending);
});

Route::post('/user/oauth-register', function () {
    include_once BASEPATH . '/php/init.php';
    $pending = osirisOAuthRegistrationPending();
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($pending['csrf'], $_POST['csrf'])) {
        osirisOAuthFail(lang('Invalid request. Please start again.', 'Ungültige Anfrage. Bitte beginne erneut.'));
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'existing' && strtoupper(USER_MANAGEMENT) !== 'AUTH') osirisOAuthFail('Invalid request.');
    if ($action === 'cancel' || $action === 'existing') {
        osirisOAuthClearAttempt();
        if ($action === 'existing') {
            $_SESSION['msg'] = lang('Log in with your existing OSIRIS account, then link SSO in your account settings.', 'Melde dich mit deinem bestehenden OSIRIS-Konto an und verknüpfe SSO in den Account-Einstellungen.');
            $_SESSION['msg_type'] = 'info';
        }
        header('Location: ' . ROOTPATH . '/user/login?redirect=' . urlencode(ROOTPATH . '/user/edit/#section-account'));
        exit;
    }
    // Recheck these settings on every request, including the final submission.
    if (!$Settings->get('auth-self-registration', true)) {
        osirisOAuthFail(lang('Self-registration is disabled. Please contact the administrator.', 'Die Selbstregistrierung ist deaktiviert. Bitte kontaktiere die Administration.'));
    }
    if ($action === 'new') {
        $_SESSION['oauth_registration']['confirmed'] = true;
        header('Location: ' . ROOTPATH . '/user/oauth-register');
        exit;
    }
    if ($action !== 'create' || empty($pending['confirmed'])) osirisOAuthFail('Invalid request.');
    $authToken = (string) ($Settings->get('auth-token') ?? '');
    if ($authToken !== '' && (!is_string($_POST['token'] ?? null) || !hash_equals($authToken, $_POST['token']))) {
        osirisOAuthRegistrationPage($pending, [['type' => 'error', 'text' => lang('The provided AUTH token is not valid.', 'Das angegebene AUTH-Token ist nicht gültig.')]]);
        return;
    }
    try {
        $person = osirisOAuthRegistrationPerson($pending, $_POST, $Groups->groups);
        if ($osiris->persons->findOne(['username' => $person['username']]) || $osiris->accounts->findOne(['username' => $person['username']])) {
            throw new \InvalidArgumentException(lang('The username is already taken. Please choose another one.', 'Der Nutzername ist bereits vergeben. Bitte wähle einen anderen.'));
        }
        if ($osiris->persons->findOne(['sso.issuer' => $pending['issuer'], 'sso.subject' => $pending['subject']])) {
            osirisOAuthFail(lang('This SSO account is already linked. Please log in with SSO again.', 'Dieses SSO-Konto ist bereits verknüpft. Bitte melde dich erneut über SSO an.'));
        }
        osirisOAuthIdentityIndex($osiris);
        // One document holds profile and identity. No local password account is created.
        $osiris->persons->insertOne($person);
    } catch (\InvalidArgumentException $e) {
        osirisOAuthRegistrationPage($pending, [['type' => 'error', 'text' => $e->getMessage()]]);
        return;
    } catch (\Throwable $e) {
        error_log('OSIRIS: SSO registration failed (' . get_class($e) . ')');
        osirisOAuthRegistrationPage($pending, [['type' => 'error', 'text' => lang('The account could not be created. Please try again or contact the administrator.', 'Das Konto konnte nicht erstellt werden. Bitte versuche es erneut oder kontaktiere die Administration.')]]);
        return;
    }
    osirisOAuthClearAttempt();
    osirisOAuthSession($person);
    $_SESSION['msg'] = lang('Your SSO account was created successfully.', 'Dein SSO-Konto wurde erfolgreich erstellt.');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/user/edit/' . rawurlencode($person['username']));
    exit;
});
