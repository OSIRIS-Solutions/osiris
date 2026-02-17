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
    <link rel="icon" href="<?= ROOTPATH ?>/img/favicon.png">
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
    <link href="<?= ROOTPATH ?>/css/phosphoricons/regular/style.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet" />
    <link href="<?= ROOTPATH ?>/css/phosphoricons/duotone/style.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet" />
    <!-- for open access icons -->
    <link href="<?= ROOTPATH ?>/css/fontello/css/osiris.css?v=<?= OSIRIS_BUILD ?>" rel="stylesheet" />

    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/main.css?v=<?= OSIRIS_BUILD ?>">
    <?= $Settings->renderAdditionalStylesheetLinks() ?>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/custom_style.css?v=<?= uniqid() ?>" no-cache>


    <script>
        const ROOTPATH = "<?= ROOTPATH ?>";
        const AFFILIATION = "<?= $Settings->get('affiliation') ?>";
        const AFFILIATION_REGEX = new RegExp('<?= $Settings->getRegex(); ?>', 'i'); // Fallback to a simple regex if parsing fails
    </script>

    <script src="<?= ROOTPATH ?>/js/jquery-3.3.1.min.js?v=<?= OSIRIS_BUILD ?>"></script>
    <script src="<?= ROOTPATH ?>/js/datatables/datatables.min.js?v=<?= OSIRIS_BUILD ?>"></script>

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
                topStart: ['search', 'buttons'],
                topEnd: 'pageLength',
                bottomStart: 'paging',
                bottomEnd: 'info',
                // bottom1End: ''
            },
            lengthMenu: [5, 10, 25, 50, 100],
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: ':visible'
                },
                className: 'btn small',
                text: `<i class="ph ph-file-xls"></i> Excel`,
            }]
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

    <?php include_once BASEPATH . "/components/command-palette.php"; ?>

    <!-- Page wrapper start -->
    <div class="page-wrapper 
        <?= $_COOKIE['D3-accessibility-contrast'] ?? '' ?>
        <?= $_COOKIE['D3-accessibility-transitions'] ?? '' ?>
        <?= $_COOKIE['D3-accessibility-dyslexia'] ?? '' ?>
    ">
        <div class="sticky-alerts"></div>

        <div class="modal" id="the-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="modal-title" id="modal-title"></h5>
                    <div id="modal-content"></div>
                </div>
            </div>
        </div>

        <!-- Sidebar overlay -->
        <div class="sidebar-overlay" onclick="osirisJS.toggleSidebar()"></div>

        <!-- Navbar start -->
        <div class="navbar navbar-top">
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
                <a href="<?= ROOTPATH ?>/" class="navbar-brand ml-20">
                    <img src="<?= ROOTPATH ?>/img/logo.svg" alt="OSIRIS" id="osiris-logo">
                    <?php if (defined('LIVE') && LIVE === false) { ?>
                        <span class=" position-absolute bottom-0 left-0 danger" style="font-size: 1rem;z-index:1">TESTSYSTEM</span>
                    <?php } ?>
                </a>

                <a href="<?= $Settings->get('affiliation_details')['link'] ?? '#' ?>" class="navbar-brand ml-auto" target="_blank">
                    <?= $Settings->printLogo("") ?>
                </a>
            <?php } ?>
        </div>
        <nav class="navbar navbar-bottom">
            <!-- Button to toggle sidebar -->
            <button class="btn btn-action active" type="button" onclick="osirisJS.toggleSidebar(this);" aria-label="Toggle sidebar"></button>
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

            <!-- messages -->
            <div class="dropdown modal-sm">
                <button class="btn primary outline mr-10" data-toggle="dropdown" type="button" id="change-language" aria-haspopup="true" aria-expanded="false">
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


            <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['username'])) { ?>
                <a href="<?= ROOTPATH ?>/" class="btn primary-5">
                    <i class="ph ph-sign-in" aria-hidden="true"></i>
                    <?= lang('Log in', 'Anmelden') ?>
                </a>
            <?php } else { ?>
                <!-- fake search input for command palette -->
                <div id="navbar-cp-trigger" class="d-none d-md-block">
                    <button type="button" class="cp-trigger" aria-label="<?= lang('Open search', 'Suche öffnen') ?>">
                        <i class="ph ph-magnifying-glass cp-trigger__icon" aria-hidden="true"></i>
                        <span class="cp-trigger__placeholder">
                            <?= lang('Search in OSIRIS', 'Suche in OSIRIS') ?>
                        </span>
                        <span class="cp-trigger__kbd" aria-hidden="true">
                            <span class="os-kbd"><?= (stripos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Mac') !== false) ? '⌘' : lang('Ctrl', 'Strg') ?></span>
                            <span class="os-kbd">K</span>
                        </span>
                    </button>
                </div>

                <script>
                    $(document).on('click', '#navbar-cp-trigger .cp-trigger', function() {
                        // Fire a custom event the palette listens to
                        document.dispatchEvent(new CustomEvent('osiris:command-palette:open'));
                    });
                </script>
            <?php
            } ?>


        </nav>
        <!-- Sidebar start -->
        <div class="sidebar">
            <?php include_once BASEPATH . "/components/sidebar.php"; ?>
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
        <div class="content-wrapper">
            <?php if ($pageactive('preview')) { ?>
                <div class="title-bar text-danger text-center font-weight-bold d-block font-size-20">
                    <i class="ph ph-globe"></i>
                    <b>PREVIEW</b>
                </div>
            <?php } ?>

            <?php if (!isset($no_container)) { ?>
                <div class="content-container">
                    <?php
                    if (function_exists('printMsg') && (isset($_GET['msg']) || isset($_GET['error'])) || isset($_SESSION['msg'])) {
                        printMsg();
                    }
                }

                if ($Settings->hasPermission('admin.give-right') && isset($Settings->errors) && !empty($Settings->errors)) {
                    ?>
                    <div class="alert danger mb-20">
                        <h3 class="title">There are errors in your settings:</h3>
                        <?= implode('<br>', $Settings->errors) ?>
                        <br>
                        Default settings are used. Go to the <a href="<?= ROOTPATH ?>/admin/general">Admin Panel</a> to fix this.
                    </div>
                <?php
                }
                ?>