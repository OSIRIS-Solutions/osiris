<?php

/**
 * Routing file for login and -out
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/user/login', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('User login', 'Login')]
    ];
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/home");
        die;
    }
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/userlogin.php";
    include BASEPATH . "/footer.php";
});

Route::get('/user/oauth', function () {
    include_once BASEPATH . "/php/init.php";

    $provider = defined('OAUTH') ? OAUTH : 'microsoft';
    $provider = strtolower($provider);
    switch ($provider) {
        case 'keycloak':
        case 'generic':
            $authorizationEndpoint = AUTHORITY . '/auth';
            break;

        case 'microsoft':
        default:
            $authorizationEndpoint = AUTHORITY . '/oauth2/v2.0/authorize';
            break;
    }

    $params = [
        'client_id' => CLIENT_ID,
        'response_type' => 'code',
        'redirect_uri' => REDIRECT_URI,
        'scope' => SCOPES,
    ];

    if ($provider === 'microsoft') {
        $params['response_mode'] = 'query';
    }

    $authorizationUrl = $authorizationEndpoint . '?' . http_build_query($params);

    header("Location: $authorizationUrl");
    exit();
});

Route::get('/user/oauth-callback', function () {
    include_once BASEPATH . "/php/init.php";

    $provider = defined('OAUTH') ? OAUTH : 'microsoft';
    $provider = strtolower($provider);
    if (!isset($_GET['code'])) {
        die("No authorization code returned.");
    }

    $code = $_GET['code'];

    switch ($provider) {
        case 'keycloak':
        case 'generic':
            $tokenUrl = AUTHORITY . '/token';
            $userInfoUrl = AUTHORITY . '/userinfo';
            break;

        case 'microsoft':
        default:
            $tokenUrl = AUTHORITY . '/oauth2/v2.0/token';
            $userInfoUrl = 'https://graph.microsoft.com/v1.0/me';
            break;
    }

    $postData = [
        'client_id' => CLIENT_ID,
        'scope' => SCOPES,
        'code' => $code,
        'redirect_uri' => REDIRECT_URI,
        'grant_type' => 'authorization_code',
        'client_secret' => CLIENT_SECRET
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['access_token'])) {
        dump($data);
        die("Error getting access token.");
    }

    $accessToken = $data['access_token'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userInfoResponse = curl_exec($ch);
    curl_close($ch);

    $user = json_decode($userInfoResponse, true);

    if ($provider === 'microsoft') {
        $mail = $user['mail'] ?? $user['userPrincipalName'] ?? '';
        $username = explode('@', $mail)[0];

        $new_user = [
            'username' => $username,
            'displayname' => $user['displayName'] ?? $username,
            'first' => $user['givenName'] ?? '',
            'last' => $user['surname'] ?? '',
            'mail' => $mail,
            'position' => $user['jobTitle'] ?? '',
            'telephone' => $user['businessPhones'][0] ?? '',
            'lastlogin' => date('d.m.Y'),
            'created' => date('d.m.Y'),
        ];
    } else {
        $mail = $user['email'] ?? '';
        $preferred = $user['preferred_username'] ?? $mail;
        $username = explode('@', $preferred)[0];

        $new_user = [
            'username' => $username,
            'displayname' => $user['name'] ?? $user['preferred_username'] ?? $username,
            'first' => $user['given_name'] ?? '',
            'last' => $user['family_name'] ?? '',
            'mail' => $mail,
            'position' => '',
            'telephone' => '',
            'lastlogin' => date('d.m.Y'),
            'created' => date('d.m.Y'),
        ];
    }

    if (empty($username)) {
        dump($user);
        die("Could not determine username from OAuth provider.");
    }

    $USER = $DB->getPerson($username);

    if (empty($USER)) {
        $osiris->persons->insertOne($new_user);
        $USER = $DB->getPerson($username);
    } else {
        $osiris->persons->updateOne(
            ['username' => $USER['username']],
            ['$set' => ["lastlogin" => date('d.m.Y')]]
        );
    }

    $_SESSION['username'] = $USER['username'];
    $_SESSION['name'] = $USER['displayname'];
    $_SESSION['loggedin'] = true;

    header("Location: " . ROOTPATH . "/");
    exit();
});

Route::post('/user/login', function () {
    include_once BASEPATH . "/php/init.php";
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/home");
        die;
    }

    if (defined('USER_MANAGEMENT') && strtoupper(USER_MANAGEMENT) == 'AUTH') {
        require_once BASEPATH . '/addons/auth/_login.php';
    } else {
        include BASEPATH . "/php/_login.php";
    }

    if (isset($_POST['username']) && isset($_POST['password'])) {

        // check if user is on the blacklist
        if (strtoupper(USER_MANAGEMENT) == 'LDAP') {
            $blacklist = $Settings->get('ldap-sync-blacklist');
            if (!empty($blacklist)) {
                $blacklist = explode(',', $blacklist);
                $blacklist = array_filter(array_map('trim', $blacklist));
                if (in_array($_POST['username'], $blacklist)) {
                    $_SESSION['loggedin'] = false;
                    abortwith(500, lang("Your account is blocked. Please contact the administrator.", "Dein Konto ist gesperrt. Bitte kontaktiere den Administrator."), "/user/login");
                }
            }
        }
        // check if user is allowed to login
        $auth = login($_POST['username'], $_POST['password']);
        if (isset($auth["success"]) && $auth["success"] == false) {
            $_SESSION['msg'] = $auth["msg"];
            $_SESSION['msg_type'] = 'error';
        } else if (isset($auth["success"]) && $auth["success"] == true) {
            // check if user exists in our database
            $USER = null;

            //get uniqueid from LDAP
            $uniqueid = $auth['uniqueid'] ?? null;
            $username = $_SESSION['username'];

            // try to get user by uniqueid first
            if (!empty($uniqueid)) {
                $USER = $DB->getPersonByUniqueID($uniqueid);
            }
            // then try to get user by username
            if (empty($USER) && !empty($username)) {
                $USER = $DB->getPerson($username);
            }

            // create user if not exists
            if (empty($USER)) {
                // create user from LDAP
                $new_user = newUser($username);
                if (empty($new_user)) {
                    $_SESSION['msg'] = lang("Sorry, the user does not exist. Please contact system administrator!", 'Leider existiert der Nutzer nicht. Bitte kontaktiere den Systemadministrator!');
                    $_SESSION['loggedin'] = false;
                    die('Sorry, the user does not exist. Please contact system administrator!');
                }
                $osiris->persons->insertOne($new_user);
                $user = $new_user['username'];
                $USER = $DB->getPerson($user);
            } else {
                // user exists in our database
                $set = ["lastlogin" => date('Y-m-d')];
                if (!empty($uniqueid)) {
                    $set['uniqueid'] = $uniqueid;
                }
                $updateResult = $osiris->persons->updateOne(
                    ['username' => $USER['username']],
                    ['$set' => $set]
                );
            }

            $_SESSION['username'] = $USER['username'];
            $_SESSION['name'] = $USER['displayname'];

            if (!empty($_POST['stay_logged_in'])) {
                $selector = bin2hex(random_bytes(12));
                $token = bin2hex(random_bytes(32));

                $expires = time() + (86400 * 30);

                $osiris->rememberTokens->insertOne([
                    'selector' => $selector,
                    'token_hash' => password_hash($token, PASSWORD_DEFAULT),
                    'username' => $USER['username'],
                    'expires' => date('Y-m-d H:i:s', $expires),
                    'created' => date('Y-m-d H:i:s'),
                ]);

                setcookie('osiris-remember', $selector . ':' . $token, [
                    'expires' => $expires,
                    'path' => ROOTPATH . '/',
                    'secure' => !empty($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
                header("Location: " . $_POST['redirect']);
                die();
            }
            header("Location: " . ROOTPATH . "/");
            die();
        }
    }
    $breadcrumb = [
        ['name' => lang('User Login', 'Login')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/userlogin.php";
    if (isset($auth)) {
        printMsg($auth["msg"] ?? lang('Something went wrong', 'Etwas ist schief gelaufen'), "error", "");
    }
    if (empty($_POST['username'])) {
        printMsg(lang("Username is required!", "Benutzername ist erforderlich!"), "error", "");
    }
    if (empty($_POST['password'])) {
        printMsg(lang("Password is required!", "Passwort ist erforderlich!"), "error", "");
    }
    include BASEPATH . "/footer.php";
});


Route::get('/user/logout', function () {
    include_once BASEPATH . "/php/init.php";
    unset($_SESSION["username"]);
    unset($_SESSION["name"]);
    unset($_SESSION["realuser"]);
    $_SESSION['loggedin'] = false;

    if (!empty($_COOKIE['osiris-remember'])) {
        [$selector] = explode(':', $_COOKIE['osiris-remember'], 2);

        $osiris->rememberTokens->deleteOne([
            'selector' => $selector
        ]);

        setcookie('osiris-remember', '', [
            'expires' => time() - 3600,
            'path' => ROOTPATH . '/',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    header("Location: " . ROOTPATH . "/");
}, 'login');


// Route::get('/user/test', function () {
//     include BASEPATH . "/php/init.php";
//     include BASEPATH . "/php/_login.php";
//     $arr = getUsers();
//     dump($arr, true);
// });

// Route::get('/user/test/(.*)', function ($id) {
//     include BASEPATH . "/php/init.php";
//     // $accountExpires = 133748892000000000;
//     // $isExpired = ($accountExpires != 0 && $accountExpires <= time() * 10000000 + 116444736000000000);

//     // dump($isExpired);
//     include BASEPATH . "/php/_login.php";
//     $arr = getUser($id);
//     dump($arr, true);
// });



// OAUTH
Route::get('/user/oauth', function () {
    include BASEPATH . "/php/init.php";
    // league/oauth2-client
    require BASEPATH . '/vendor/autoload.php';

    $settings = require BASEPATH . '/config/oauth2.php';

    $provider = new League\OAuth2\Client\Provider\GenericProvider($settings);


    // If we don't have an authorization code then get one
    if (!isset($_GET['code'])) {

        // Fetch the authorization URL from the provider; this returns the
        // urlAuthorize option and generates and applies any necessary parameters
        // (e.g. state).
        $authorizationUrl = $provider->getAuthorizationUrl();

        // Get the state generated for you and store it to the session.
        $_SESSION['oauth2state'] = $provider->getState();

        // Optional, only required when PKCE is enabled.
        // Get the PKCE code generated for you and store it to the session.
        $_SESSION['oauth2pkceCode'] = $provider->getPkceCode();

        // Redirect the user to the authorization URL.
        header('Location: ' . $authorizationUrl);
        exit;

        // Check given state against previously stored one to mitigate CSRF attack
    } elseif (empty($_GET['state']) || empty($_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {

        if (isset($_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
        }

        exit('Invalid state');
    } else {

        try {

            // Optional, only required when PKCE is enabled.
            // Restore the PKCE code stored in the session.
            $provider->setPkceCode($_SESSION['oauth2pkceCode']);

            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // We have an access token, which we may use in authenticated
            // requests against the service provider's API.
            echo 'Access Token: ' . $accessToken->getToken() . "<br>";
            echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
            echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
            echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

            // Using the access token, we may look up details about the
            // resource owner.
            $resourceOwner = $provider->getResourceOwner($accessToken);

            var_export($resourceOwner->toArray());

            // The provider provides a way to get an authenticated API request for
            // the service, using the access token; it returns an object conforming
            // to Psr\Http\Message\RequestInterface.
            $request = $provider->getAuthenticatedRequest(
                'GET',
                'https://service.example.com/resource',
                $accessToken
            );
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

            // Failed to get the access token or user details.
            exit($e->getMessage());
        }
    }
});


Route::get('/reset-guest-password', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_GET['token'])) die("no token given");
    $token = $_GET['token'];
    $guest = $osiris->guestAccounts->findOne(['reset_token' => $token, 'reset_token_valid_until' => ['$gt' => date('Y-m-d H:i:s')]]);
    if (empty($guest)) {
        $_SESSION['msg'] = lang("Invalid or expired token.", "Ungültiger oder abgelaufener Token.");
        header("Location: " . ROOTPATH . "/");
        die();
    }
    include BASEPATH . "/header.php";
?>
    <div class="container">
        <h1><?= lang("Reset password for guest account", "Passwort für Gastkonto zurücksetzen") ?></h1>
        <form action="<?= ROOTPATH ?>/reset-guest-password" method="post">
            <input type="hidden" name="token" value="<?= e($token) ?>">
            <div class="form-group">
                <label for="password"><?= lang("New password", "Neues Passwort") ?></label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirm"><?= lang("Confirm new password", "Neues Passwort bestätigen") ?></label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
            </div>

            <div id="feedback" class="mb-20"></div>

            <button type="submit" class="btn btn-primary"><?= lang("Reset password", "Passwort zurücksetzen") ?></button>
        </form>

        <script>
            const password = document.getElementById('password');
            const password_confirm = document.getElementById('password_confirm');
            const feedback = document.getElementById('feedback');

            function validatePasswords() {
                if (password.value === "" || password_confirm.value === "") {
                    feedback.textContent = "";
                    feedback.className = "";
                    return;
                }
                if (password.value === password_confirm.value) {
                    feedback.textContent = "<?= lang("Passwords match", "Passwörter stimmen überein") ?>";
                    feedback.className = "text-success";
                } else {
                    feedback.textContent = "<?= lang("Passwords do not match", "Passwörter stimmen nicht überein") ?>";
                    feedback.className = "text-danger";
                }
            }

            password.addEventListener('input', validatePasswords);
            password_confirm.addEventListener('input', validatePasswords);
        </script>
    </div>
<?php
    include BASEPATH . "/footer.php";
});

Route::post('/reset-guest-password', function () {
    include_once BASEPATH . "/php/init.php";
    if (!isset($_POST['token'])) die("no token given");
    $token = $_POST['token'];
    $guest = $osiris->guestAccounts->findOne(['reset_token' => $token, 'reset_token_valid_until' => ['$gt' => date('Y-m-d H:i:s')]]);
    if (empty($guest)) {
        $_SESSION['msg'] = lang("Invalid or expired token.", "Ungültiger oder abgelaufener Token.");
        header("Location: " . ROOTPATH . "/");
        die();
    }
    if (!isset($_POST['password']) || empty($_POST['password'])) {
        $_SESSION['msg'] = lang("Password cannot be empty.", "Passwort darf nicht leer sein.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/reset-guest-password?token=$token");
        die();
    }
    if (!isset($_POST['password_confirm']) || $_POST['password'] != $_POST['password_confirm']) {
        $_SESSION['msg'] = lang("Passwords do not match.", "Passwörter stimmen nicht überein.");
        $_SESSION['msg_type'] = 'error';
        header("Location: " . ROOTPATH . "/reset-guest-password?token=$token");
        die();
    }
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $osiris->guestAccounts->updateOne(
        ['username' => $guest['username']],
        ['$set' => ['password' => $password]],
        ['$unset' => ['reset_token' => "", 'reset_token_valid_until' => ""]]
    );
    $_SESSION['msg'] = lang("Password successfully reset. You can now log in with your new password.", "Passwort erfolgreich zurückgesetzt. Du kannst dich jetzt mit deinem neuen Passwort einloggen.");
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/");
    die();
});
