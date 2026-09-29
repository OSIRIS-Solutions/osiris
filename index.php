<?php

/**
 * Core routing file
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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

// error_reporting(E_ERROR);

session_start();

define('BASEPATH', $_SERVER['DOCUMENT_ROOT'] . ROOTPATH);

include_once BASEPATH . "/version.php";

// set time constants
$year = date("Y");
$month = date("n");
$quarter = ceil($month / 3);
define('CURRENTQUARTER', intval($quarter));
define('CURRENTMONTH', intval($month));
define('CURRENTYEAR', intval($year));

class Html
{
    public function __construct(
        public readonly string $value
    ) {}
}

require_once BASEPATH . '/php/Language.php';
$Translations = new Language(BASEPATH . '/lang');

function currentLanguage(): string
{
    global $USER, $Settings;
    $default = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2) === 'de' ? 'de' : 'en';
    $language = $_GET['lang'] ?? $_COOKIE['osiris-language'] ?? ($USER['lang'] ?? $default);

    $supported = $Settings !== null ? $Settings->languages() : ['en'];
    return in_array($language, $supported, true) ? $language : OSIRIS_BASE_LANGUAGE;
}

function replaceTranslationPlaceholders(string $translation, array $replace): string
{
    foreach ($replace as $key => $value) {
        $replacement = $value instanceof Html ? $value->value : (string) $value;
        $translation = str_replace('{{' . $key . '}}', $replacement, $translation);
    }

    return $translation;
}

/**
 * Translate a versioned interface key.
 */
function translate(string $key, array $replace = []): string
{
    global $Translations;
    if (!isset($Translations) || !$Translations instanceof Language) {
        $Translations = new Language(BASEPATH . '/lang');
    }

    $result = $Translations->resolve($key, currentLanguage(), OSIRIS_BASE_LANGUAGE);
    return replaceTranslationPlaceholders($result, $replace);
}

/**
 * Resolve a localized content value for the requested or current language.
 *
 * Localized content is stored as an associative array, for example:
 * ['en' => 'Research Group', 'de' => 'Forschungsgruppe'].
 * Plain strings remain supported while existing data is migrated gradually.
 */
function localized($value, ?string $language = null, ?string $fallbackLanguage = null): string
{
    if (is_string($value) || is_numeric($value)) {
        return (string) $value;
    }

    if ($value instanceof Traversable) {
        $value = iterator_to_array($value);
    }
    if (!is_array($value)) {
        return '';
    }

    $fallbackLanguage ??= OSIRIS_BASE_LANGUAGE;
    $language ??= currentLanguage();

    // The admin-only interface language "keys" has no equivalent for content.
    if ($language === 'keys') {
        $language = $fallbackLanguage;
    }

    foreach (array_unique([$language, $fallbackLanguage]) as $candidate) {
        $translation = $value[$candidate] ?? null;
        if ((is_string($translation) || is_numeric($translation)) && trim((string) $translation) !== '') {
            return (string) $translation;
        }
    }

    return '';
}

/**
 * Language requested by an API consumer. "all" keeps complete language maps.
 */
function apiLanguage(): string
{
    global $Settings;

    $language = strtolower(trim((string) ($_GET['lang'] ?? currentLanguage())));
    if ($language === 'all') return 'all';

    $available = isset($Settings) && $Settings instanceof Settings
        ? $Settings->contentLanguages()
        : [OSIRIS_BASE_LANGUAGE];

    return in_array($language, $available, true) ? $language : OSIRIS_BASE_LANGUAGE;
}

/**
 * Resolve localized content for API output, or return its complete map.
 */
function apiLocalized($value)
{
    if (apiLanguage() !== 'all') {
        return localized($value, apiLanguage());
    }
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value);
    }
    return is_array($value) ? $value : [OSIRIS_BASE_LANGUAGE => (string) $value];
}

/**
 * Localize selected document fields and remove their legacy *_de companions.
 */
function apiLocalizedFields(array $document, array $fields): array
{
    foreach ($fields as $field) {
        $legacyField = $field . '_de';
        if (!array_key_exists($field, $document) && !array_key_exists($legacyField, $document)) continue;

        $value = $document[$field] ?? '';
        $legacyGerman = $document[$legacyField] ?? null;
        if (apiLanguage() === 'all' && !is_array($value) && !is_object($value)) {
            $translations = [OSIRIS_BASE_LANGUAGE => (string) $value];
            if ($legacyGerman !== null && $legacyGerman !== '') $translations['de'] = $legacyGerman;
            $document[$field] = $translations;
        } elseif (apiLanguage() === 'de' && $legacyGerman !== null && !is_array($value) && !is_object($value)) {
            $document[$field] = $legacyGerman;
        } else {
            $document[$field] = apiLocalized($value);
        }
        unset($document[$legacyField]);
    }
    return $document;
}

/**
 * Backwards-compatible wrapper for interface keys and legacy EN/DE calls.
 */
function lang($en, ?string $de = null, array $replace = []): string
{
    // Keep existing callers working while individual data structures migrate
    // from name/name_de fields to localized content arrays.
    if (is_array($en) || $en instanceof Traversable) {
        return localized($en);
    }

    $en = (string) $en;
    $language = currentLanguage();

    // Preserve the legacy two-language format: lang('Login', 'Anmelden').
    if ($de !== null) {
        return $language === 'de' ? $de : $en;
    }

    // Plain text without a translation key remains unchanged.
    if (!str_contains($en, '.') || str_contains($en, ' ')) {
        return $en;
    }

    return translate($en, $replace);
}

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
        $path = ROOTPATH . "/home";
        if (!empty($_SERVER['QUERY_STRING'])) $path .= "?" . $_SERVER['QUERY_STRING'];
        header("Location: $path");
    }
});


if (defined('USER_MANAGEMENT') && strtoupper(USER_MANAGEMENT) == 'AUTH') {
    require_once BASEPATH . '/addons/auth/index.php';
}

include_once BASEPATH . "/routes/login.php";

// check if user 
if (empty($_SESSION['loggedin']) && !empty($_COOKIE['osiris-remember'])) {
    include_once BASEPATH . "/php/DB.php";
    $DB = new DB();
    $osiris = $DB->db;
    [$selector, $token] = explode(':', $_COOKIE['osiris-remember'], 2) + [null, null];

    if ($selector && $token) {
        $remember = $osiris->rememberTokens->findOne([
            'selector' => $selector,
            'expires' => ['$gt' => date('Y-m-d H:i:s')]
        ]);

        if ($remember && password_verify($token, $remember['token_hash'])) {
            $USER = $osiris->persons->findOne(['username' => $remember['username']]);

            if ($USER) {
                $_SESSION['loggedin'] = true;
                $_SESSION['username'] = $USER['username'];
                $_SESSION['name'] = $USER['displayname'];
            }
        }
    }
    // clean up expired tokens
    $osiris->rememberTokens->deleteMany(['expires' => ['$lte' => date('Y-m-d H:i:s')]]);
}

// route for language setting
Route::get('/set-preferences', function () {
    include_once BASEPATH . "/php/init.php";

    // Language settings and cookies
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && array_key_exists('language', $_GET)) {
        $lang = $_GET['language'];
        if (!in_array($lang, $Settings->languages())) {

            $redirect = $_GET['redirect'] ?? ROOTPATH . '/';
            header("Location: " . $redirect);
            die;
        }
        $_COOKIE['osiris-language'] = $lang;
        $host = parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST);
        $domain = ($host != 'testserver') ? $host : false;

        setcookie('osiris-language', $_COOKIE['osiris-language'], [
            'expires' => time() + 86400,
            'path' => ROOTPATH . '/',
            'domain' =>  $domain,
            'httponly' => false,
            'samesite' => 'Lax',
        ]);
        // save language in user profile
        if (
            isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true
            && isset($_SESSION['username']) && !empty($_SESSION['username'])
        ) {
            $osiris->persons->updateOne(
                ['username' => $_SESSION['username']],
                ['$set' => ['lang' => $_COOKIE['osiris-language']]]
            );
        }
    }
    // check if accessibility settings are given
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'GET' && array_key_exists('accessibility', $_GET)) {
        // define base parameter
        $domain = parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST);
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

Route::get('/custom_style.css', function () {
    include_once BASEPATH . "/php/init.php";
    header("Content-Type: text/css");
    echo $Settings->generateStyleSheet();
});

if (
    isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true
    &&
    isset($_SESSION['username']) && !empty($_SESSION['username'])
) {

    include_once BASEPATH . "/routes/home.php";
    include_once BASEPATH . "/routes/data.php";
    include_once BASEPATH . "/routes/export.php";
    include_once BASEPATH . "/routes/database.php";
    include_once BASEPATH . "/routes/docs.php";
    include_once BASEPATH . "/routes/groups.php";
    include_once BASEPATH . "/routes/import.php";
    include_once BASEPATH . "/routes/journals.php";
    include_once BASEPATH . "/routes/projects.php";
    include_once BASEPATH . "/routes/nagoya.php";
    include_once BASEPATH . "/routes/topics.php";
    include_once BASEPATH . "/routes/queue.php";
    include_once BASEPATH . "/routes/teaching.php";
    include_once BASEPATH . "/routes/users.php";
    include_once BASEPATH . "/routes/visualize.php";
    include_once BASEPATH . "/routes/activities.php";
    include_once BASEPATH . "/routes/reports.php";
    include_once BASEPATH . "/routes/spectrum.php";
    include_once BASEPATH . "/routes/events.php";
    require_once BASEPATH . '/routes/guests.php';
    require_once BASEPATH . '/routes/news.php';
    include_once BASEPATH . "/routes/calendar.php";
    include_once BASEPATH . "/routes/infrastructures.php";
    include_once BASEPATH . "/routes/organizations.php";
    include_once BASEPATH . "/routes/workflows.php";
    include_once BASEPATH . "/routes/admin.php";
    include_once BASEPATH . "/routes/orcid.php";
    // include_once BASEPATH . "/routes/adminGeneral.php";
    // include_once BASEPATH . "/routes/adminRoles.php";

    include_once BASEPATH . "/addons/ida/index.php";
}
include_once BASEPATH . "/routes/migrate.php";

include_once BASEPATH . "/routes/api/api.php";
include_once BASEPATH . "/routes/api/mcp.php";
include_once BASEPATH . "/routes/api/dashboard.php";
include_once BASEPATH . "/routes/api/portfolio.php";

include_once BASEPATH . "/routes/cron.php";

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
    http_response_code(405);
    // Check the Accept header to determine the content type
    $acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : 'text/html';

    header("HTTP/1.0 405 Method Not Allowed");
    if (strpos($acceptHeader, 'application/json') !== false) {
        // Send JSON response for scripts expecting JSON
        header('Content-Type: application/json');
        echo json_encode(['error' => '405 Method Not Allowed']);
    } elseif (strpos($acceptHeader, 'text/plain') !== false) {
        // Send plain text response for scripts expecting text
        header('Content-Type: text/plain');
        echo "405 Method Not Allowed";
    } elseif (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] === false) {
        header("Location: " . ROOTPATH . "/user/login?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    } else {
        // Send HTML response for users
        $error = 405;
        include BASEPATH . "/header.php";

        include BASEPATH . "/pages/error.php";
        include BASEPATH . "/footer.php";
    }
});


Route::run(ROOTPATH);
