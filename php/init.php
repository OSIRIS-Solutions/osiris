<?php
date_default_timezone_set('Europe/Berlin');

require_once BASEPATH . '/php/Settings.php';
include_once BASEPATH . "/php/_config.php";
include_once BASEPATH . "/php/DB.php";
include_once BASEPATH . "/php/JSON.php";

// Database connection
global $DB;
$DB = new DB;

global $osiris;
$osiris = $DB->db;

// get installed OSIRIS version
if (!defined('OSIRIS_VERSION')) {
    define('OSIRIS_VERSION', '0.0.0');
}

$version = $osiris->system->findOne(['key' => 'version']);
if (str_ends_with($_SERVER['REQUEST_URI'], '/install')) {
    // just let the install script run
} elseif (!isset($version['value']) || !is_string($version['value'])) { ?>
    <!-- include css -->
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css">
    <link href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet" />
    <div class="align-items-center container d-flex h-full">
        <div class="alert danger mb-20 w-full">
            <h3 class="title">
                <?= lang('common.osiris_has_not_been_installed_yet') ?>
            </h3>

            <p>
                <b><?= lang('common.warning') ?>:</b>
                <?= lang('common.osiris_will_be_installed_and_set_up_automatically_this_won_t_take_long_but') ?>
            </p>

            <a href="<?= ROOTPATH ?>/install" class="btn danger">
                <?= lang('common.install_osiris') ?>
            </a>
        </div>
    </div>
<?php
    die;
} elseif (version_compare(implode('.', array_slice(explode('.', $version['value']), 0, 2)), implode('.', array_slice(explode('.', OSIRIS_VERSION), 0, 2)), '<')) {
    # compare if major.minor version is lower, ignore patch level
    $allowed_routes = [
        ROOTPATH . '/migrate',
        ROOTPATH . '/migration-needed',
        ROOTPATH . '/user/logout',
        ROOTPATH . '/user/login',
        ROOTPATH . '/user/oauth',
        ROOTPATH . '/user/oauth-callback',
    ];
    $uri = strtok($_SERVER['REQUEST_URI'], '?');   // strip query string
    if (!in_array($uri, $allowed_routes)) {
        header('Location: ' . ROOTPATH . '/migration-needed');
        die;
    }
} elseif (version_compare($version['value'], OSIRIS_VERSION, '<')) {
    # if version including patch level is lower, update the version in the database
    $osiris->system->updateOne(
        ['key' => 'version'],
        ['$set' => ['value' => OSIRIS_VERSION]],
        ['upsert' => true]
    );

    $osiris->system->updateOne(
        ['key' => 'last_update'],
        ['$set' => ['value' => date('Y-m-d')]],
        ['upsert' => true]
    );
}


// initialize user
global $USER;
$USER = $DB->initUser();
// check if user is not empty, if not, log out
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] && (empty($USER) || !isset($USER['username']))) {
    $_SESSION['username'] = null;
    $_SESSION['loggedin'] = false;
    $_SESSION['msg'] = lang('common.your_session_has_expired_please_log_in_again');
    $_SESSION['msg_type'] = "error";
    header("Location: " . ROOTPATH . '/user/login');
    die();
}


// Get organizational units (Groups)
include_once BASEPATH . "/php/Groups.php";
global $Groups;
$Groups = new Groups();
global $Departments;
if (!empty($Groups->tree)) {
    // filter inactive groups
    $Departments = array_filter($Groups->tree['children'], function ($group) {
        return !($group['inactive'] ?? false);
    });
    // take only id => name
    $Departments = array_column($Departments, 'name', 'id');
} else $Departments = [];
// Activity categories and types
include_once BASEPATH . "/php/Categories.php";
global $Categories;
$Categories = new Categories();

// Get all Settings
global $Settings;
$Settings = new Settings($USER);
