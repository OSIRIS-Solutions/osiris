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

require_once BASEPATH . '/php/OAuth.php';
require_once BASEPATH . '/routes/oauth-link.php';
require_once BASEPATH . '/routes/oauth-register.php';

Route::get('/user/login', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('User login', 'Login')]
    ];
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/home");
        die;
    }
    if (!defined('USER_MANAGEMENT')) {
        die('USER_MANAGEMENT not defined in CONFIG.php');
    }
    $userManagement = strtoupper(USER_MANAGEMENT);
    $page = 'login';
    $redirectTarget = $_GET['redirect'] ?? $_SERVER['REQUEST_URI'];
    $authMessages = [];
    if (isset($_GET['redirect']) && !isset($_SESSION['msg'])) {
        $authMessages[] = [
            'text' => lang(
                'Please log in to access the requested page.',
                'Bitte melde dich an, um auf die gewünschte Seite zuzugreifen.'
            ),
            'type' => 'error',
        ];
    }
    include BASEPATH . "/pages/user/auth-page.php";
});

Route::get('/user/oauth', function () {
    include_once BASEPATH . "/php/init.php";
    osirisOAuthStart('login');
});

Route::get('/user/oauth-callback', function () {
    include_once BASEPATH . "/php/init.php";
    osirisOAuthCallback();
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
    $page = 'login';
    $userManagement = strtoupper(USER_MANAGEMENT);
    $redirectTarget = $_POST['redirect'] ?? $_SERVER['REQUEST_URI'];
    $authMessages = [];
    if (empty($_POST['username'])) {
        $authMessages[] = ['text' => lang('Username is required.', 'Bitte gib deinen Nutzernamen ein.'), 'type' => 'error'];
    }
    if (empty($_POST['password'])) {
        $authMessages[] = ['text' => lang('Password is required.', 'Bitte gib dein Passwort ein.'), 'type' => 'error'];
    }
    include BASEPATH . "/pages/user/auth-page.php";
});


Route::get('/user/logout', function () {
    include_once BASEPATH . "/php/init.php";
    osirisOAuthClearAttempt();
    unset($_SESSION['oauth_link_csrf'], $_SESSION['auth_method']);
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
