<?php

/**
 * Core routing file
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

if (file_exists('CONFIG.php')) {
    require_once 'CONFIG.php';
    require_once 'CONFIG.fallback.php';
} else {
    require_once 'CONFIG.default.php';
}
require_once 'php/_config.php';
define('CSS_JS_VERSION', '12');

// error_reporting(E_ERROR);

session_start();

define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] . ROOTPATH);
define('OSIRIS_VERSION', '1.5.0');
define('OSIRIS_BUILD', '250812');

// set time constants
$year = date("Y");
$month = date("n");
$quarter = ceil($month / 3);
define('CURRENTQUARTER', intval($quarter));
define('CURRENTMONTH', intval($month));
define('CURRENTYEAR', intval($year));

function lang($en, $de = null)
{
    if ($de === null) return $en;
    // Standard language = DE
    $lang = $_GET['lang'] ?? $_COOKIE['osiris-language'] ?? 'de';
    if ($lang == "en") return $en;
    if ($lang == "de") return $de;
    return $en;
}

// $_SESSION['loggedin'] = true;
// $_SESSION['username'] = 'jko'; // default user for testing purposes


include_once BASEPATH . "/php/Route.php";

Route::get('/', function () {
    if (isset($_GET['code']) && defined('USER_MANAGEMENT') && strtoupper(USER_MANAGEMENT) == 'OAUTH') {
        header("Location: " . ROOTPATH . "/user/oauth-callback?code=" . $_GET['code']);
        exit();
    }
    include_once BASEPATH . "/php/init.php";
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) {
        header("Location: " . ROOTPATH . "/user/login");
    } else {
        $path = ROOTPATH . "/profile/" . $_SESSION['username'];
        if (!empty($_SERVER['QUERY_STRING'])) $path .= "?" . $_SERVER['QUERY_STRING'];
        header("Location: $path");
    }
});


if (defined('USER_MANAGEMENT') && strtoupper(USER_MANAGEMENT) == 'AUTH') {
    require_once BASEPATH . '/addons/auth/index.php';
}

include_once BASEPATH . "/routes/login.php";

// Route::get('/test', function () {
//     include_once BASEPATH . "/php/init.php";
//     include_once BASEPATH . "/php/LDAPInterface.php";

//     include BASEPATH . "/header.php";

//     $LDAP = new LDAPInterface();
//     // $LDAP->attributes = [];
//     // $user = $LDAP->fetchUser('juk20');
//     // echo $LDAP->convertObjectGUID($user['objectguid'][0]);
//     $user = $LDAP->newUser('ironman');
//     dump($user, true);

//     include BASEPATH . "/footer.php";
// });


// route for language setting
Route::get('/set-preferences', function () {

    // Language settings and cookies
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && array_key_exists('language', $_GET)) {
        $_COOKIE['osiris-language'] = $_GET['language'] === 'en' ? 'en' : 'de';
        $domain = ($_SERVER['HTTP_HOST'] != 'testserver') ? $_SERVER['HTTP_HOST'] : false;
        setcookie('osiris-language', $_COOKIE['osiris-language'], [
            'expires' => time() + 86400,
            'path' => ROOTPATH . '/',
            'domain' =>  $domain,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
    }
    // check if accessibility settings are given
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && array_key_exists('accessibility', $_GET)) {
        // define base parameter
        $domain = $_SERVER['HTTP_HOST'];
        $cookie_settings = [
            'expires' => time() + 86400,
            'path' => ROOTPATH . '/',
            'domain' =>  $domain,
            'httponly' => false,
            'samesite' => 'Lax',
        ];

        // set cookies for current sessions
        $_COOKIE['D3-accessibility-contrast'] = $_GET['accessibility']['contrast'] ?? '';
        $_COOKIE['D3-accessibility-transitions'] = $_GET['accessibility']['transitions'] ?? '';
        $_COOKIE['D3-accessibility-dyslexia'] = $_GET['accessibility']['dyslexia'] ?? '';

        // save cookies for persistent use
        setcookie('D3-accessibility-dyslexia', $_COOKIE['D3-accessibility-dyslexia'], $cookie_settings);
        setcookie('D3-accessibility-contrast', $_COOKIE['D3-accessibility-contrast'], $cookie_settings);
        setcookie('D3-accessibility-transitions', $_COOKIE['D3-accessibility-transitions'], $cookie_settings);
    }
    $redirect = $_GET['redirect'] ?? ROOTPATH . '/';
    header("Location: " . $redirect);
});

// always include the static routes
include_once BASEPATH . "/routes/static.php";

if (
    isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true
    &&
    isset($_SESSION['username']) && !empty($_SESSION['username'])
) {
    include_once BASEPATH . "/routes/data.php";
    include_once BASEPATH . "/routes/export.php";
    include_once BASEPATH . "/routes/database.php";
    include_once BASEPATH . "/routes/docs.php";
    include_once BASEPATH . "/routes/groups.php";
    include_once BASEPATH . "/routes/import.php";
    include_once BASEPATH . "/routes/journals.php";
    include_once BASEPATH . "/routes/projects.php";
    include_once BASEPATH . "/routes/topics.php";
    include_once BASEPATH . "/routes/queue.php";
    include_once BASEPATH . "/routes/tags.php";
    include_once BASEPATH . "/routes/teaching.php";
    include_once BASEPATH . "/routes/users.php";
    include_once BASEPATH . "/routes/visualize.php";
    include_once BASEPATH . "/routes/activities.php";
    include_once BASEPATH . "/routes/reports.php";
    include_once BASEPATH . "/routes/concepts.php";
    include_once BASEPATH . "/routes/admin.php";
    include_once BASEPATH . "/routes/events.php";
    require_once BASEPATH . '/routes/guests.php';
    include_once BASEPATH . "/routes/calendar.php";
    include_once BASEPATH . "/routes/infrastructures.php";
    include_once BASEPATH . "/routes/organizations.php";
    // include_once BASEPATH . "/routes/adminGeneral.php";
    // include_once BASEPATH . "/routes/adminRoles.php";

    include_once BASEPATH . "/addons/ida/index.php";
}
include_once BASEPATH . "/routes/migrate.php";

include_once BASEPATH . "/routes/api/api.php";
include_once BASEPATH . "/routes/api/dashboard.php";
include_once BASEPATH . "/routes/api/portfolio.php";


/**
 * Routes for OSIRIS Portal
 */

include_once BASEPATH . "/addons/portal/index.php";

Route::get('/error/([0-9]*)', function ($error) {
    // header("HTTP/1.0 $error");
    http_response_code($error);
    include BASEPATH . "/header.php";
    echo "Error " . $error;
    // include BASEPATH . "/pages/error.php";
    include BASEPATH . "/footer.php";
});

// Add a 404 not found route
Route::pathNotFound(function ($path) {
    http_response_code(404);
    // Check the Accept header to determine the content type
    $acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/html';

    header("HTTP/1.0 404 Not Found");
    if (strpos($acceptHeader, 'application/json') !== false) {
        // Send JSON response for scripts expecting JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => '404 Not Found']);
    } elseif (strpos($acceptHeader, 'text/plain') !== false) {
        // Send plain text response for scripts expecting text
        header('Content-Type: text/plain');
        echo "404 Not Found";
    } elseif (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) {
        header("Location: " . ROOTPATH . "/user/login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    } else {
        // Send HTML response for users
        $error = 404;
        include BASEPATH . "/header.php";

        include BASEPATH . "/pages/error.php";
        include BASEPATH . "/footer.php";
    }
});

// Add a 405 method not allowed route
Route::methodNotAllowed(function ($path, $method) {
    // Do not forget to send a status header back to the client
    // The router will not send any headers by default
    // So you will have the full flexibility to handle this case
    header('HTTP/1.0 405 Method Not Allowed');
    $error = 405;
    include BASEPATH . "/header.php";
    // include BASEPATH . "/pages/error.php";
    echo "Error 405";
    include BASEPATH . "/footer.php";
});


Route::run(ROOTPATH);
