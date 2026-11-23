<?php

/** Anonymous SSO onboarding. No OSIRIS session is granted before registration. */
function osirisOAuthRegistrationPending(): array
{
    if (!osirisOAuthEnabled()) osirisOAuthFail('SSO is not configured.');
    if (!empty($_SESSION['loggedin'])) {
        osirisOAuthClearAttempt();
        header('Location: ' . ROOTPATH . '/');
        exit;
    }
    $pending = $_SESSION['oauth_registration'] ?? null;
    if (!is_array($pending) || ($pending['created'] ?? 0) < time() - 1800) {
        osirisOAuthFail(t('auth.your_sso_registration_has_expired_please_log_in_with_sso_again'));
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
        throw new \InvalidArgumentException(t('auth.use_letters_numbers_dots_underscores_or_hyphens_for_the_username'));
    }
    if ($person['first'] === '' || $person['last'] === '') {
        throw new \InvalidArgumentException(t('auth.please_enter_your_first_and_last_name'));
    }
    // A valid provider email is authoritative; missing email can be completed in the form.
    $person['mail'] = is_string($pending['mail']) && filter_var($pending['mail'], FILTER_VALIDATE_EMAIL)
        ? $pending['mail'] : (is_string($values['mail'] ?? null) ? trim($values['mail']) : '');
    if (!filter_var($person['mail'], FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException(t('people.please_enter_a_valid_email_address'));
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
    if (!is_array($selected)) throw new \InvalidArgumentException(t('auth.invalid_organisational_units'));
    $available = [];
    foreach ($groups as $group) if (empty($group['inactive'])) $available[(string) $group['id']] = $group['id'];
    foreach (array_unique($selected, SORT_REGULAR) as $unit) {
        if (!is_string($unit) || !array_key_exists($unit, $available)) {
            throw new \InvalidArgumentException(t('auth.please_select_valid_organisational_units'));
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
        osirisOAuthFail(t('auth.self_registration_is_disabled_please_contact_the_administrator'));
    }
    osirisOAuthRegistrationPage($pending);
});

Route::post('/user/oauth-register', function () {
    include_once BASEPATH . '/php/init.php';
    $pending = osirisOAuthRegistrationPending();
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals($pending['csrf'], $_POST['csrf'])) {
        osirisOAuthFail(t('auth.invalid_request_please_start_again'));
    }
    $action = $_POST['action'] ?? '';
    if ($action === 'existing' && strtoupper(USER_MANAGEMENT) !== 'AUTH') osirisOAuthFail('Invalid request.');
    if ($action === 'cancel' || $action === 'existing') {
        osirisOAuthClearAttempt();
        if ($action === 'existing') {
            $_SESSION['msg'] = t('auth.log_in_with_your_existing_osiris_account_then_link_sso_in_your_acc');
            $_SESSION['msg_type'] = 'info';
        }
        header('Location: ' . ROOTPATH . '/user/login?redirect=' . urlencode(ROOTPATH . '/user/edit/#section-account'));
        exit;
    }
    // Recheck these settings on every request, including the final submission.
    if (!$Settings->get('auth-self-registration', true)) {
        osirisOAuthFail(t('auth.self_registration_is_disabled_please_contact_the_administrator'));
    }
    if ($action === 'new') {
        $_SESSION['oauth_registration']['confirmed'] = true;
        header('Location: ' . ROOTPATH . '/user/oauth-register');
        exit;
    }
    if ($action !== 'create' || empty($pending['confirmed'])) osirisOAuthFail('Invalid request.');
    $authToken = (string) ($Settings->get('auth-token') ?? '');
    if ($authToken !== '' && (!is_string($_POST['token'] ?? null) || !hash_equals($authToken, $_POST['token']))) {
        osirisOAuthRegistrationPage($pending, [['type' => 'error', 'text' => t('auth.token_invalid')]]);
        return;
    }
    try {
        $person = osirisOAuthRegistrationPerson($pending, $_POST, $Groups->groups);
        if ($osiris->persons->findOne(['username' => $person['username']]) || $osiris->accounts->findOne(['username' => $person['username']])) {
            throw new \InvalidArgumentException(t('auth.the_username_is_already_taken_please_choose_another_one'));
        }
        if ($osiris->persons->findOne(['sso.issuer' => $pending['issuer'], 'sso.subject' => $pending['subject']])) {
            osirisOAuthFail(t('auth.this_sso_account_is_already_linked_please_log_in_with_sso_again'));
        }
        osirisOAuthIdentityIndex($osiris);
        // One document holds profile and identity. No local password account is created.
        $osiris->persons->insertOne($person);
    } catch (\InvalidArgumentException $e) {
        osirisOAuthRegistrationPage($pending, [['type' => 'error', 'text' => $e->getMessage()]]);
        return;
    } catch (\Throwable $e) {
        error_log('OSIRIS: SSO registration failed (' . get_class($e) . ')');
        osirisOAuthRegistrationPage($pending, [['type' => 'error', 'text' => t('auth.the_account_could_not_be_created_please_try_again_or_contact_the_a')]]);
        return;
    }
    osirisOAuthClearAttempt();
    osirisOAuthSession($person);
    $_SESSION['msg'] = t('auth.your_sso_account_was_created_successfully');
    $_SESSION['msg_type'] = 'success';
    header('Location: ' . ROOTPATH . '/user/edit/' . rawurlencode($person['username']));
    exit;
});
