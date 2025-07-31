<?php

/**
 * Routing file for login and -out
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

Route::get('/user/login', function () {
    include_once BASEPATH . "/php/init.php";
    $breadcrumb = [
        ['name' => lang('User login', 'Login')]
    ];
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/profile/$_SESSION[username]");
        die;
    }
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/userlogin.php";
    include BASEPATH . "/footer.php";
});


Route::get('/user/oauth', function () {
    include_once BASEPATH . "/php/init.php";
    $authorizationUrl = AUTHORITY . '/oauth2/v2.0/authorize' .
        '?client_id=' . CLIENT_ID .
        '&response_type=code' .
        '&redirect_uri=' . urlencode(REDIRECT_URI) .
        '&response_mode=query' .
        '&scope=' . urlencode(SCOPES);
    header("Location: $authorizationUrl");
    exit();
});


Route::get('/user/oauth-callback', function () {
    include_once BASEPATH . "/php/init.php";
    // dump($_SESSION);
    // die;
    if (isset($_GET['code'])) {
        $code = $_GET['code'];
        $tokenUrl = AUTHORITY . '/oauth2/v2.0/token';
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
        if (isset($data['access_token'])) {
            // Zugangstoken verwenden, um Benutzerinformationen zu erhalten
            $accessToken = $data['access_token'];
            $userInfoUrl = 'https://graph.microsoft.com/v1.0/me';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $accessToken"]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $userInfoResponse = curl_exec($ch);
            curl_close($ch);
            $user = json_decode($userInfoResponse, true);
            // session_start();

            // username not supported by Microsoft
            // take username from mail
            $username = explode('@', $user['mail'])[0];

            // check if user exists in our database
            $USER = $DB->getPerson($username);
            if (empty($USER)) {
                // create user from LDAP
                $new_user = array(
                    'username' => $username,
                    'displayname' => $user['displayName'],
                    'first' => $user['givenName'],
                    'last' => $user['surname'],
                    'mail' => $user['mail'],
                    'position' => $user['jobTitle'],
                    'telephone' => $user['businessPhones'][0],
                    'lastlogin' => date('d.m.Y'),
                    'created' => date('d.m.Y'),
                );
                $osiris->persons->insertOne($new_user);

                $USER = $DB->getPerson($username);
            } else {
                $updateResult = $osiris->persons->updateOne(
                    ['username' => $USER['username']],
                    ['$set' => ["lastlogin" => date('d.m.Y')]]
                );
            }

            $_SESSION['username'] = $USER['username'];
            $_SESSION['name'] = $USER['displayname'];
            $_SESSION['loggedin'] = true;

            if (isset($_GET['redirect'])) {
                header("Location: " . $_GET['redirect']);
                die();
            }
            header("Location: " . ROOTPATH . "/");

            exit();
        } else {
            echo "Error getting access token.";
        }
    } else {
        echo "No authorization code returned.";
    }
});

Route::post('/user/login', function () {
    include_once BASEPATH . "/php/init.php";
    $msg = "?msg=welcome";
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/profile/$_SESSION[username]");
        die;
    }

    if (defined('USER_MANAGEMENT') && strtoupper(USER_MANAGEMENT) == 'AUTH') {
        require_once BASEPATH . '/addons/auth/_login.php';
    } else {
        include BASEPATH . "/php/_login.php";
    }

    if (isset($_POST['username']) && isset($_POST['password'])) {

        // check if user is allowed to login
        $auth = login($_POST['username'], $_POST['password']);
        if (isset($auth["success"]) && $auth["success"] == false) {
            $msg = "?msg=" . $auth["msg"];
        } else if (isset($auth["success"]) && $auth["success"] == true) {
            // check if user exists in our database
            $USER = $DB->getPerson($_SESSION['username']);

            //get uniqueid from LDAP
            $uniqueid = $auth['uniqueid'] ?? null;

            if (empty($USER)) {
                // user does not exist in our database
                // if possible, check for the uniqueid
                if (!empty($uniqueid)) {
                    $USER = $DB->getPersonByUniqueID($uniqueid);
                }
                if (empty($USER)) {
                    // create user from LDAP
                    $new_user = newUser($_SESSION['username']);
                    if (empty($new_user)) {
                        die('Sorry, the user does not exist. Please contact system administrator!');
                    }
                    $osiris->persons->insertOne($new_user);

                    $user = $new_user['username'];

                    $USER = $DB->getPerson($user);

                    // try to connect the user with existing authors
                    // $updateResult = $osiris->activities->updateMany(
                    //     [
                    //         'authors.last' => $USER['last'],
                    //         'authors.first' => new MongoDB\BSON\Regex('^' . $USER['first'][0] . '.*')
                    //     ],
                    //     ['$set' => ["authors.$.user" => ($user)]]
                    // );
                    // $n = $updateResult->getModifiedCount();
                    // $msg .= "&new=$n";
                }
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

            if (isset($_POST['redirect']) && !str_contains($_POST['redirect'], "//")) {
                header("Location: " . $_POST['redirect'] . $msg);
                die();
            }
            header("Location: " . ROOTPATH . "/" . $msg);
            die();
        }
    }
    $breadcrumb = [
        ['name' => lang('User Login', 'Login')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/userlogin.php";
    if (isset($auth)) {
        printMsg($auth["msg"] ?? 'Something went wrong', "error", "");
    }
    if (empty($_POST['username'])) {
        printMsg("Username is required!", "error", "");
    }
    if (empty($_POST['password'])) {
        printMsg("Password is required!", "error", "");
    }
    include BASEPATH . "/footer.php";
});


Route::get('/user/logout', function () {
    unset($_SESSION["username"]);
    unset($_SESSION["name"]);
    unset($_SESSION["realuser"]);
    $_SESSION['loggedin'] = false;
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



// OAUTH2
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
