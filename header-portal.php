<?php

/**
 * Header component
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

include_once BASEPATH . "/php/init.php";

$breadcrumb = $breadcrumb ?? [];
$pagetitle = array('OSIRIS');
foreach ($breadcrumb as $crumb) {
    array_push($pagetitle, $crumb['name']);
}
$pagetitle = implode(' | ', array_reverse($pagetitle));

$uri = $_SERVER['REQUEST_URI'];
// $uri = str_replace(ROOTPATH."/", '', $uri, 1);
$uri = substr_replace($uri, '', 0, strlen(ROOTPATH . "/"));
$lasturl = explode("/", $uri);
// dump($lasturl);
$page =  $page ?? $lasturl[0]; //end($lasturl);

$pageactive = function ($p) use ($page) {
    if ($page == $p) return "active";
    $uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
    if ((ROOTPATH . "/" . $p) == $uri) return 'active';
    return "";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta tags -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport" />
    <meta name="description" content="OSIRIS ist ein modernes Forschungsinformationssystem, das besonderen Schwerpunkt auf Open Source und Nutzerfreundlichkeit legt." />

    <!-- Favicon and title -->
    <link rel="icon" href="img/favicon.png">
    <title><?= $pagetitle ?? 'OSIRIS-App' ?></title>
    <link rel="manifest" href="<?= ROOTPATH ?>/manifest.json">

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="OSIRIS - the open, smart and intuitive research information system" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://osiris-app.de" />
    <meta property="og:description" content="OSIRIS ist ein modernes Forschungsinformationssystem, das besonderen Schwerpunkt auf Open Source und Nutzerfreundlichkeit legt.." />
    <meta property="og:image" content="<?= ROOTPATH ?>/img/apple-touch-icon.png" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://osiris-app.de">
    <meta property="twitter:title" content="OSIRIS - the open, smart and intuitive research information system">
    <meta property="twitter:description" content="OSIRIS ist ein modernes Forschungsinformationssystem, das besonderen Schwerpunkt auf Open Source und Nutzerfreundlichkeit legt..">
    <meta property="twitter:image" content="<?= ROOTPATH ?>/img/apple-touch-icon.png">

    <!-- Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= ROOTPATH ?>/img/apple-touch-icon.png">
    <link rel="mask-icon" href="<?= ROOTPATH ?>/img/mask-icon.svg" color="#dd590e">

    <!-- Favicon and title -->
    <link rel="icon" href="<?= ROOTPATH ?>/img/favicon.png">
    <title><?= $pagetitle ?? 'OSIRIS' ?></title>

    <!-- Icon font -->
    <link href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= CSS_JS_VERSION ?>" rel="stylesheet" />
    <link href="<?= ROOTPATH ?>/css/phosphoricons/duotone/style.css?v=<?= CSS_JS_VERSION ?>" rel="stylesheet" />
    <!-- for open access icons -->
    <link href="<?= ROOTPATH ?>/css/fontello/css/osiris.css?v=<?= CSS_JS_VERSION ?>" rel="stylesheet" />

    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css?v=<?= CSS_JS_VERSION ?>">
    <?= $Settings->renderAdditionalStylesheetLinks() ?>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/custom_style.css?v=<?= uniqid() ?>" no-cache>


    <script>
        const ROOTPATH = "<?= ROOTPATH ?>";
        const AFFILIATION = "<?= $Settings->get('affiliation') ?>";
        const AFFILIATION_REGEX = new RegExp('<?= $Settings->getRegex(); ?>', 'i'); // Fallback to a simple regex if parsing fails
    </script>

    <script src="<?= ROOTPATH ?>/js/jquery-3.3.1.min.js?v=<?= CSS_JS_VERSION ?>"></script>
    <script src="<?= ROOTPATH ?>/js/datatables/datatables.min.js?v=<?= CSS_JS_VERSION ?>"></script>

    <script>
        $.extend($.fn.DataTable.ext.classes, {
            paging: {
                container: "pagination mt-10 ",
                first: "direction ",
                last: "direction ",
                previous: "direction ",
                next: "direction ",
                active: "active ",
            },
            search: {
                input: "form-control small d-inline w-auto ml-10 ",
                container: "d-inline-block mr-10"
            },
            length: {
                select: "form-control small d-inline w-auto mr-10",
                container: "text-right"
            },
            info: {
                container: "text-right text-muted"
            }
        });
        // default layout
        $.extend(true, $.fn.dataTable.defaults, {
            layout: {
                // top1Start: '',
                topStart: 'search',
                topEnd: 'pageLength',
                bottomStart: 'paging',
                bottomEnd: 'info',
            },
            lengthMenu: [5, 10, 25, 50, 100],
        });
    </script>
    <script src="<?= ROOTPATH ?>/js/osiris.js?<?= filemtime(BASEPATH . '/js/osiris.js') ?>"></script>
    <script src="<?= ROOTPATH ?>/js/script.js?<?= filemtime(BASEPATH . '/js/script.js') ?>"></script>

    <?php if (isset($additionalHead)) {
        echo $additionalHead;
    } ?>

</head>

<body>
    <div class="loader">
        <span></span>
    </div>

    <!-- Page wrapper start -->
    <div class="page-wrapper 
        <?= $_COOKIE['D3-accessibility-contrast'] ?? '' ?>
        <?= $_COOKIE['D3-accessibility-transitions'] ?? '' ?>
        <?= $_COOKIE['D3-accessibility-dyslexia'] ?? '' ?>
    " data-sidebar-hidden="true">
        <div class="sticky-alerts"></div>

        <!-- Sidebar overlay -->
        <div class="sidebar-overlay" onclick="osirisJS.toggleSidebar()"></div>

        <!-- Navbar start -->
        <div class="navbar navbar-top">
            <div class="container">
                <?php if (defined('MAINTENANCE') && MAINTENANCE) { ?>
                    <style>
                        .maintenance-msg {
                            background: var(--signal-color-20);
                            color: #856404;
                            width: 100%;
                            height: 6.5rem;
                            text-align: center;
                            vertical-align: middle;
                            margin: 1rem 0rem 1rem 1rem;
                            padding: 1rem;
                            border-radius: var(--border-radius);
                            border: var(--border-width) solid var(--signal-color);
                        }

                        .maintenance-msg .title {
                            font-size: 1.5rem;
                            font-weight: bold;
                        }

                        .page-wrapper>.navbar.navbar-top {
                            position: sticky;
                            top: 0;
                            z-index: 1000;
                        }
                    </style>
                    <div class="maintenance-msg">
                        <div class="title">
                            <i class="ph ph-barricade"></i>
                            <?= lang('System maintenance', 'Wartungsarbeiten') ?>.
                        </div>
                        <?= lang('Please do not add, edit or remove data. Changes might be overwritten.', 'Bitte keine Daten hinzufügen, bearbeiten oder löschen. Änderungen werden evtl. überschrieben.') ?>
                    </div>
                <?php } else { ?>
                    <a href="<?= ROOTPATH ?>/portal/info" class="navbar-brand">
                        <img src="<?= ROOTPATH ?>/img/Portfolio.svg" alt="OSIRIS-Portfolio" id="osiris-logo">
                    </a>

                    <a href="<?= $Settings->get('affiliation_details')['link'] ?? '#' ?>" class="navbar-brand ml-auto" target="_blank">
                        <?= $Settings->printLogo("") ?>
                    </a>
                <?php } ?>
            </div>
        </div>
        <nav class="navbar navbar-bottom">
            <!-- Button to toggle sidebar -->
            <!-- <button class="btn btn-action active" type="button" onclick="osirisJS.toggleSidebar(this);" aria-label="Toggle sidebar"></button> -->
            <div class="container">
                <ul class="navbar-nav">

                    <nav aria-label="breadcrumbs">
                        <ul class="breadcrumb">
                            <?php
                            $breadcrumb = $breadcrumb ?? [];
                            if (!empty($breadcrumb)) {
                                echo '<li class=""><a href="' . ROOTPATH . '/"><i class="ph ph-house" aria-label="Home"></i></a></li>';
                                foreach ($breadcrumb as $crumb) {
                                    $displayName = shortenName($crumb['name'] ?? '');
                                    if (!isset($crumb['path'])) {
                                        echo '<li class="active" aria-current="page"><a href="#">' . $displayName . '</a></li>';
                                    } else {
                                        echo '<li class=""><a href="' . ROOTPATH . $crumb['path'] . '">' . $displayName . '</a></li>';
                                    }
                                }
                            }
                            ?>
                        </ul>
                    </nav>

                </ul>

                <div class="dropdown modal-sm">
                    <button class="btn primary mr-5" data-toggle="dropdown" type="button" id="change-language" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-translate"></i>
                        <span class="sr-only"><?= lang('Change language', 'Sprache ändern') ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-center w-200" aria-labelledby="change-language">
                        <h6 class="header text-primary"><?= lang('Change language', 'Sprache ändern') ?></h6>

                        <form action="<?= ROOTPATH ?>/set-preferences" method="get" class="content pt-0">
                            <input type="hidden" name="language" value="<?= lang('de', 'en') ?>">
                            <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                            <button type="submit" class="btn primary block ">
                                <i class="ph ph-translate" aria-hidden="true"></i>
                                <span class="sr-only"><?= lang('Change language', 'Sprache ändern') ?></span>
                                <?= lang('Deutsch', 'English') ?>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Accessibility menu -->
                <div class="dropdown modal-sm">
                    <button class="btn primary mr-5" data-toggle="dropdown" type="button" id="accessibility-menu" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-person-arms-spread ph-person-simple-circle"></i>
                        <span class="sr-only"><?= lang('Accessibility Options', 'Accessibility-Optionen') ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-center w-300" aria-labelledby="accessibility-menu">
                        <h6 class="header text-primary">Accessibility</h6>
                        <form action="<?= ROOTPATH ?>/set-preferences" method="get" class="content pt-0">
                            <input type="hidden" name="accessibility[check]">
                            <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">

                            <div class="form-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="set-contrast" name="accessibility[contrast]" value="high-contrast" <?= !empty($_COOKIE['D3-accessibility-contrast'] ?? '') ? 'checked' : '' ?>>
                                    <label for="set-contrast"><?= lang('High contrast', 'Erhöhter Kontrast') ?></label><br>
                                    <small class="text-muted">
                                        <?= lang('Enhance the contrast of the web page for better readability.', 'Erhöht den Kontrast für bessere Lesbarkeit.') ?>
                                    </small>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="set-transitions" name="accessibility[transitions]" value="without-transitions" <?= !empty($_COOKIE['D3-accessibility-transitions'] ?? '') ? 'checked' : '' ?>>
                                    <label for="set-transitions"><?= lang('Reduce motion', 'Verringerte Bewegung') ?></label><br>
                                    <small class="text-muted">
                                        <?= lang('Reduce motion and animations on the page.', 'Verringert Animationen und Bewegungen auf der Seite.') ?>
                                    </small>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="set-dyslexia" name="accessibility[dyslexia]" value="dyslexia" <?= !empty($_COOKIE['D3-accessibility-dyslexia'] ?? '') ? 'checked' : '' ?>>
                                    <label for="set-dyslexia"><?= lang('Dyslexia mode', 'Dyslexie-Modus') ?></label><br>
                                    <small class="text-muted">
                                        <?= lang('Use a special font to increase readability for users with dyslexia.', 'OSIRIS nutzt eine spezielle Schriftart, die von manchen Menschen mit Dyslexie besser gelesen werden kann.') ?>
                                    </small>
                                </div>
                            </div>
                            <button class="btn primary">Apply</button>
                        </form>
                    </div>
                </div>


                <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['username'])) { ?>
                    <a href="<?= ROOTPATH ?>/" class="btn primary-5">
                        <i class="ph ph-sign-in" aria-hidden="true"></i>
                        <?= lang('Log in', 'Anmelden') ?>
                    </a>
                <?php } ?>

            </div>

        </nav>
        <!-- Sidebar start -->
        <div class="sidebar">

        </div>

        <script>
            function toggleSidebar(el) {
                el = $(el)
                let id = el.attr('id')
                let hide = el.hasClass('open')

                el.next().slideToggle();
                el.toggleClass('open');

                window.sessionStorage.setItem(id, hide);
            }

            $(function() {
                $('.title.collapse').each(function(n, el) {
                    var hide = window.sessionStorage.getItem($(el).attr('id'));
                    if (hide == 'true') {
                        $(el).removeClass('open')
                        $(el).next().hide()
                    }
                })
            });
        </script>

        <!-- Content wrapper start -->
        <div class="content-wrapper" data-sidebar-hidden="true">

            <div class="content-container">
                <?php
                if (function_exists('printMsg') && (isset($_GET['msg']) || isset($_GET['error'])) || isset($_SESSION['msg'])) {
                    printMsg();
                }
                ?>