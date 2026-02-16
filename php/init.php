<?php

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
$version = $osiris->system->findOne(['key' => 'version']);
if (str_ends_with($_SERVER['REQUEST_URI'], '/install')){
    // just let the install script run
} elseif (empty($version)) { ?>
    <!-- include css -->
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css">
    <link href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet" />
    <div class="align-items-center container d-flex h-full">
        <div class="alert danger mb-20 w-full">
            <h3 class="title">
                <?= lang('
                OSIRIS has not been installed yet.', '
                OSIRIS wurde noch nicht installiert.') ?>
            </h3>

            <p>
                <b><?= lang('Warning', 'Achtung') ?>:</b>
                <?= lang(
                    'OSIRIS will be installed and set up automatically. This won\'t take long, but please make sure not to reload or close the page during the process.',
                    'OSIRIS wird automatisch installiert und eingerichtet. Dies wird nicht lange dauern, aber bitte stelle sicher, dass du die Seite während des Prozesses nicht neu lädst oder schließt.'
                ) ?>
            </p>

            <a href="<?= ROOTPATH ?>/install" class="btn danger">
                <?= lang('Install OSIRIS', 'OSIRIS installieren') ?>
            </a>
        </div>
    </div>
<?php
    die;
} else if (version_compare($version['value'], OSIRIS_VERSION, '<') && !str_ends_with($_SERVER['REQUEST_URI'], '/migrate')) { ?>
    <!-- include css -->
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css">
    <link href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet" />
    <div class="align-items-center container d-flex h-full">
        <div class="alert danger mb-20 w-full">
            <h3 class="title"><?= lang('A new OSIRIS-Version has been found.', 'Eine neue OSIRIS-Version wurde gefunden.') ?></h3>
            <p>
                <?= lang(
                    'OSIRIS will be updated and set up automatically. Depending on the version, this might take some time, so please make sure not to reload or close the page during the process.',
                    'OSIRIS wird automatisch aktualisiert und eingerichtet. Abhängig von der Version kann dies eine ganze Weile dauern, stelle also bitte sicher, dass du die Seite während des Prozesses nicht neu lädst oder schließt.'
                ) ?>
            </p>
            <p class="text-muted">
                <small>Installed: <?= $version['value'] ?></small> | <small>Latest: <?= OSIRIS_VERSION ?></small>
            </p>
            <a href="<?= ROOTPATH ?>/migrate" class="btn danger">
                <?= lang('Update OSIRIS', 'OSIRIS aktualisieren') ?>
            </a>
        </div>
    </div>
<?php
    die;
} 
// if (empty($version)) {
//     $osiris->system->insertOne(['key' => 'version', 'value' => OSIRIS_VERSION]);
//     $version = $osiris->system->findOne(['key' => 'version']);
// }
define('OSIRIS_DB_VERSION', $version['value'] ?? '0.0.0');




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

// initialize user
global $USER;
$USER = $DB->initUser();

// Get all Settings
global $Settings;
$Settings = new Settings($USER);
