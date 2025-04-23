<?php

/**
 * Page to see details on one activity
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/view/<activity_id>
 *
 * @package     OSIRIS
 * @since       1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};

include_once BASEPATH . "/php/Modules.php";

// check if this is an ongoing activity type
$ongoing = false;
$sws = false;

$typeArr = $Format->subtypeArr;

$M = $typeArr['modules'] ?? array();
foreach ($M as $m) {
    if (str_ends_with($m, '*')) $m = str_replace('*', '', $m);
    if ($m == 'date-range-ongoing') $ongoing = true;
    if ($m == 'supervisor') $sws = true;
}

$guests_involved = boolval($typeArr['guests'] ?? false);
$guests = $doc['guests'] ?? [];
// if ($guests_involved)
//     $guests = $osiris->guests->find(['activity' => $id])->toArray();

if (isset($_GET['msg']) && $_GET['msg'] == 'add-success') { ?>


    <?php if ($Settings->featureEnabled('projects') && !empty($doc['projects'] ?? [])) { ?>
        <div class="alert success mb-20">
            <h3 class="title">
                <?= lang('Projects connected', 'Projekte verknüpft') ?>
            </h3>
            <?= lang(
                'This activity was automatically connected to projects based on funding numbers. You can add more projects or remove the existing ones.',
                'Diese Aktivität wurde automatisch anhand von Fördernummern mit Projekten verknüpft. Du kannst weitere Projekte hinzufügen oder die bestehenden entfernen.'
            ) ?>
            <br>
            <a href="#projects" class="btn success">
                <i class="ph ph-tree-structure"></i>
                <?= lang('Projects', 'Projekte') ?>
            </a>
        </div>
    <?php } ?>
    <div class="alert signal mb-20">
        <h3 class="title">
            <?= lang('For the good practice: ', 'Für die gute Praxis:') ?>
        </h3>
        <?= lang(
            'Upload now all relevant files for this activity (e.g. as PDF) to have them available for documentation and exchange.',
            'Lade jetzt die relevanten Dateien (z.B. PDF) hoch, um sie für die Dokumentation parat zu haben.'
        ) ?>
        <i class="ph ph-smiley"></i>
        <b><?= lang('Thank you!', 'Danke!') ?></b>
        <br>
        <a href="#upload-files" class="btn signal">
            <i class="ph ph-upload"></i>
            <?= lang('Upload files', 'Dateien hochladen') ?>
        </a>
    </div>


<?php } ?>

<style>
    [class^="col-"] .box {
        margin: 0;
        /* height: 100%; */
    }

    .btn-toolbar {
        margin: 0 0 1rem;
        /* background-color: white;
        padding: .5rem;
        border-radius: .5rem; */
    }

    .filelink {
        display: block;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: inherit !important;
        padding: .5rem 1rem;
        margin: 0 0 1rem;
        background: white;
    }

    .filelink:hover {
        text-decoration: none;
        background-color: rgba(0, 110, 183, 0.05);
    }

    .show-on-hover:hover .invisible {
        visibility: visible !important;
    }

    .badge.block {
        display: block;
        text-align: center;
    }
</style>

<script>
    const ACTIVITY_ID = '<?= $id ?>';
    const TYPE = '<?= $doc['type'] ?>';
</script>

<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>

<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
<script src="<?= ROOTPATH ?>/js/activity.js?v=<?= CSS_JS_VERSION ?>"></script>



<div class="btn-toolbar">
    <?php if ($doc['locked'] ?? false) { ?>
        <span class="badge danger cursor-default mr-10 border-danger" data-toggle="tooltip" data-title="<?= lang('This activity has been locked.', 'Diese Aktivität wurde gesperrt.') ?>">
            <i class="ph ph-lock text-danger"></i>
            <?= lang('Locked', 'Gesperrt') ?>
        </span>
    <?php } ?>

    <div class="btn-group">
        <?php if (($user_activity || $Settings->hasPermission('activities.edit')) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn text-primary border-primary">
                <i class="ph ph-pencil-simple-line"></i>
                <?= lang('Edit', 'Bearbeiten') ?>
            </a>
        <?php } ?>
        <?php if (!in_array($doc['type'], ['publication'])) { ?>
            <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn text-primary border-primary">
                <i class="ph ph-copy"></i>
                <?= lang("Copy", "Kopie") ?>
            </a>
        <?php } ?>
    </div>

    <a href="#upload-files" class="btn text-primary border-primary">
        <i class="ph ph-upload"></i>
        <?= lang('Upload file', 'Datei hochladen') ?>
    </a>
    <div class="btn-group">
        <?php if ($Settings->featureEnabled('projects')) { ?>
            <a href="#projects" class="btn text-primary border-primary">
                <i class="ph ph-plus-circle"></i>
                <?= lang("Project", "Projekt") ?>
            </a>
        <?php } ?>
        <!-- <a href="#connect" class="btn text-primary border-primary">
            <i class="ph ph-plus-circle"></i>
            <?= lang("Tags", "Schlagwörter") ?>
        </a> -->
        <?php if ($Settings->featureEnabled('infrastructures')) { ?>
            <a href="#infrastructures" class="btn text-primary border-primary">
                <i class="ph ph-plus-circle"></i>
                <?= lang("Infrastructure", "Infrastruktur") ?>
            </a>
        <?php } ?>

    </div>


    <div class="btn-group">
        <button class="btn text-primary border-primary" onclick="addToCart(this, '<?= $id ?>')">
            <i class="<?= (in_array($id, $cart)) ? 'ph ph-fill ph-shopping-cart ph-shopping-cart-plus text-success' : 'ph ph-shopping-cart ph-shopping-cart-plus' ?>"></i>
            <?= lang('Collect', 'Sammeln') ?>
        </button>
        <div class=" dropdown with-arrow btn-group ">
            <button class="btn text-primary border-primary" data-toggle="dropdown" type="button" id="download-btn" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-download"></i> Download
                <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="download-btn">
                <div class="content">
                    <form action="<?= ROOTPATH ?>/download" method="post">

                        <input type="hidden" name="filter[id]" value="<?= $id ?>">

                        <div class="form-group">

                            <?= lang('Highlight:', 'Hervorheben:') ?>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                                <label for="highlight-user"><?= lang('Me', 'Mich') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang(' Authors', '-Autoren') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-none" value="">
                                <label for="highlight-none"><?= lang('None', 'Nichts') ?></label>
                            </div>

                        </div>


                        <div class="form-group">

                            <?= lang('File format:', 'Dateiformat:') ?>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-word" value="word" checked="checked">
                                <label for="format-word">Word</label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-bibtex" value="bibtex">
                                <label for="format-bibtex">BibTex</label>
                            </div>

                        </div>
                        <button class="btn text-primary border-primary">Download</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($Settings->featureEnabled('portal')) { ?>
        <a class="btn text-primary border-primary ml-auto" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
            <i class="ph ph-eye ph-fw"></i>
            <?= lang('Preview', 'Vorschau') ?>
        </a>
    <?php } ?>
</div>

<!-- HEAD -->
<div class="my-20 pt-20">

    <ul class="breadcrumb category" style="--highlight-color:<?= $Format->typeArr['color'] ?? '' ?>">
        <li><?= $Format->activity_type() ?></li>
        <!-- <span class='mr-10'><?= $Format->activity_icon(false) ?></span> -->
        <li><?= $Format->activity_subtype() ?></li>
    </ul>
    <h1 class="mt-10">
        <?= $Format->getTitle() ?>
    </h1>

    <p class="lead"><?= $Format->getSubtitle() ?></p>

</div>


<!-- show research topics -->
<?= $Settings->printTopics($doc['topics'] ?? [], 'mb-20') ?>


<div class="d-flex">

    <div class="mr-10 badge bg-white">
        <small><?= lang('Date', 'Datum') ?>: </small>
        <br />
        <span class="badge"><?= $Format->format_date($doc) ?></span>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= $Settings->get('affiliation') ?>: </small>
        <br />
        <?php

        if ($doc['affiliated'] ?? true) { ?>
            <div class="badge success" data-toggle="tooltip" data-title="<?= lang('At least on author of this activity has an affiliation with the institute.', 'Mindestens ein Autor dieser Aktivität ist mit dem Institut affiliert.') ?>">
                <!-- <i class="ph ph-handshake m-0"></i> -->
                <?= lang('Affiliated', 'Affiliert') ?>
            </div>
        <?php } else { ?>
            <div class="badge danger" data-toggle="tooltip" data-title="<?= lang('None of the authors has an affiliation to the Institute.', 'Keiner der Autoren ist mit dem Institut affiliert.') ?>">
                <!-- <i class="ph ph-hand-x m-0"></i> -->
                <?= lang('Not affiliated', 'Nicht affiliert') ?>
            </div>
        <?php } ?>
    </div>

    <!-- cooperative -->
    <div class="mr-10 badge bg-white">
        <small><?= lang('Cooperation', 'Zusammenarbeit') ?>: </small>
        <br />
        <?php
        switch ($doc['cooperative'] ?? '-') {
            case 'individual': ?>
                <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Only one author', 'Nur ein Autor/eine Autorin') ?>">
                    <?= lang('Individual', 'Einzelarbeit') ?>
                </span>
            <?php
                break;
            case 'departmental': ?>
                <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from the same department of this institute', 'Autoren aus der gleichen Abteilung des Instituts') ?>">
                    <?= lang('Departmental', 'Abteilungsübergreifend') ?>
                </span>
            <?php
                break;
            case 'institutional': ?>
                <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from different departments but all from this institute', 'Autoren aus verschiedenen Abteilungen, aber alle vom Institut') ?>">
                    <?= lang('Institutional', 'Institutionell') ?>
                </span>
            <?php
                break;
            case 'contributing': ?>
                <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from different institutes with us being middle authors', 'Autoren aus unterschiedlichen Instituten mit uns als Mittelautoren') ?>">
                    <?= lang('Cooperative (Contributing)', 'Kooperativ (Beitragend)') ?>
                </span>
            <?php
                break;
            case 'leading': ?>
                <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from different institutes with us being leading authors', 'Autoren aus unterschiedlichen Instituten mit uns als führenden Autoren') ?>">
                    <?= lang('Cooperative (Leading)', 'Kooperativ (Führend)') ?>
                </span>
            <?php
                break;
            default: ?>
                <span class="badge block" data-toggle="tooltip" data-title="<?= lang('No author affiliated', 'Autor:innen sind nicht affiliert') ?>">
                    <?= lang('None', 'Keine') ?>
                </span>
        <?php
                break;
        }
        ?>

    </div>

    <?php if ($doc['impact'] ?? false) { ?>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Impact', 'Impact') ?>: </small>
            <br />
            <span class="badge danger"><?= $doc['impact'] ?></span>
        </div>
    <?php } ?>
    <?php if ($doc['quartile'] ?? false) { ?>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Quartile', 'Quartil') ?>: </small>
            <br />
            <span class="quartile <?= $doc['quartile'] ?>"><?= $doc['quartile'] ?></span>
        </div>
    <?php } ?>

    <?php if (isset($doc['projects']) && count($doc['projects']) > 0) { ?>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Projects', 'Projekte') ?>: </small>
            <br />
            <?php foreach ($doc['projects'] as $p) { ?>
                <a class="badge" href="<?= ROOTPATH ?>/projects/view/<?= $p ?>"><?= $p ?></a>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($Settings->featureEnabled('portal')) {
        $doc['hide'] = $doc['hide'] ?? false;
    ?>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Online Visibility', 'Online-Sichtbarkeit') ?>: </small>
            <br />
            <?php if ($user_activity || $Settings->hasPermission('activities.edit')) { ?>
                <div class="custom-switch">
                    <input type="checkbox" id="hide" <?= $doc['hide'] ? 'checked' : '' ?> name="values[hide]" onchange="hide()">
                    <label for="hide" id="hide-label">
                        <?= $doc['hide'] ? lang('Visible', 'Sichtbar') : lang('Hidden', 'Versteckt') ?>
                    </label>
                </div>

                <script>
                    function hide() {
                        $.ajax({
                            type: "POST",
                            url: ROOTPATH + "/crud/activities/hide",
                            data: {
                                activity: ACTIVITY_ID
                            },
                            success: function(response) {
                                var hide = $('#hide').prop('checked');
                                $('#hide-label').text(hide ? '<?= lang('Visible', 'Sichtbar') ?>' : '<?= lang('Hidden', 'Versteckt') ?>');
                                toastSuccess(lang('Highlight status changed', 'Hervorhebungsstatus geändert'))
                            },
                            error: function(response) {
                                console.log(response);
                            }
                        });
                    }
                </script>


            <?php } else { ?>
                <?php if ($doc['hide']) { ?>
                    <span class="badge danger" data-toggle="tooltip" data-title="<?= lang('This activity is hidden on the portal.', 'Diese Aktivität ist auf dem Portal versteckt.') ?>">
                        <i class="ph ph-eye-slash"></i>
                        <?= lang('Hidden', 'Versteckt') ?>
                    </span>
                <?php } else { ?>
                    <span class="badge success" data-toggle="tooltip" data-title="<?= lang('This activity is visible on the portal.', 'Diese Aktivität ist auf dem Portal sichtbar.') ?>">
                        <i class="ph ph-eye"></i>
                        <?= lang('Visible', 'Sichtbar') ?>
                    </span>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($user_activity) {
        $highlights = DB::doc2Arr($USER['highlighted'] ?? []);
        $highlighted = in_array($id, $highlights);
    ?>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Displayed in your profile', 'Darstellung in deinem Profil') ?>: </small>
            <br />
            <div class="custom-switch">
                <input type="checkbox" id="highlight" <?= ($highlighted) ? 'checked' : '' ?> name="values[highlight]" onchange="fav()">
                <label for="highlight" id="highlight-label">
                    <?= $highlighted ? lang('Highlighted', 'Hervorgehoben') : lang('Normal', 'Normal') ?>
                </label>
            </div>
        </div>
        <script>
            function fav() {
                $.ajax({
                    type: "POST",
                    url: ROOTPATH + "/crud/activities/fav",
                    data: {
                        activity: ACTIVITY_ID
                    },
                    dataType: "json",
                    success: function(response) {
                        var highlight = $('#highlight').prop('checked');
                        $('#highlight-label').text(highlight ? '<?= lang('Highlighted', 'Hervorgehoben') ?>' : '<?= lang('Normal', 'Normal') ?>');
                        toastSuccess(lang('Highlight status changed', 'Hervorhebungsstatus geändert'))
                    },
                    error: function(response) {
                        console.log(response);
                    }
                });
            }
        </script>
    <?php } ?>

</div>

<!-- TAB AREA -->

<nav class="pills mt-20 mb-0">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-info" aria-hidden="true"></i>
        <?= lang('General', 'Allgemein') ?>
    </a>

    <?php if ($guests_involved) { ?>
        <a onclick="navigate('guests')" id="btn-guests" class="btn">
            <i class="ph ph-user-plus" aria-hidden="true"></i>
            <?= lang('Guests', 'Gäste') ?>
            <span class="index"><?= count($guests) ?></span>
        </a>
    <?php } ?>


    <?php if (count($doc['authors']) > 1) { ?>
        <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Coauthors', 'Koautoren') ?>
            <span class="index"><?= count($doc['authors']) ?></span>
        </a>
    <?php } ?>

    <?php if ($Settings->featureEnabled('projects')) { ?>
        <?php
        $count_projects = count($doc['projects'] ?? []);
        if ($count_projects) :
        ?>
            <a onclick="navigate('projects')" id="btn-projects" class="btn">
                <i class="ph ph-tree-structure" aria-hidden="true"></i>
                <?= lang('Projects', 'Projekte') ?>
                <span class="index"><?= $count_projects ?></span>
            </a>

        <?php else : ?>
            <a href="#projects" class="btn">
                <i class="ph ph-plus-circle"></i>
                <?= lang('Add projects', 'Projekt verknüpfen') ?>
            </a>
        <?php endif; ?>
    <?php } ?>

    <?php if ($Settings->featureEnabled('infrastructures')) { ?>
        <?php
        $count_infrastructures = count($doc['infrastructures'] ?? []);
        if ($count_infrastructures) :
        ?>
            <a onclick="navigate('infrastructures')" id="btn-infrastructures" class="btn">
                <i class="ph ph-tree-structure" aria-hidden="true"></i>
                <?= lang('Infrastructures', 'Infrastrukturen') ?>
                <span class="index"><?= $count_infrastructures ?></span>
            </a>

        <?php else : ?>
            <a href="#infrastructures" class="btn">
                <i class="ph ph-plus-circle"></i>
                <?= lang('Add infrastructures', 'Infrastrukturen') ?>
            </a>
        <?php endif; ?>
    <?php } ?>

    <?php
    $count_files = count($doc['files'] ?? []);
    if ($count_files) :
    ?>
        <a onclick="navigate('files')" id="btn-files" class="btn">
            <i class="ph ph-files" aria-hidden="true"></i>
            <?= lang('Files', 'Dateien') ?>
            <span class="index"><?= $count_files ?></span>
        </a>

    <?php else : ?>
        <a href="#upload-files" class="btn">
            <i class="ph ph-plus-circle"></i>
            <?= lang('Upload files', 'Datei hochladen') ?>
        </a>
    <?php endif; ?>

    <?php if ($Settings->featureEnabled('concepts')) { ?>
        <?php
        $count_concepts = count($doc['concepts'] ?? []);
        if ($count_concepts) :
        ?>
            <a onclick="navigate('concepts')" id="btn-concepts" class="btn">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('Concepts', 'Konzepte') ?>
                <span class="index"><?= $count_concepts ?></span>
            </a>
        <?php endif; ?>
    <?php } ?>


    <?php
    $count_history = count($doc['history'] ?? []);
    if ($count_history) :
    ?>
        <a onclick="navigate('history')" id="btn-history" class="btn">
            <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
            <?= lang('History', 'Historie') ?>
            <span class="index"><?= $count_history ?></span>
        </a>
    <?php endif; ?>

    <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
        <a onclick="navigate('raw')" id="btn-raw" class="btn">
            <i class="ph ph-code" aria-hidden="true"></i>
            <?= lang('Raw data', 'Rohdaten')  ?>
        </a>
    <?php } ?>

</nav>


<section id="general">
    <div class="row row-eq-spacing-lg">
        <div class="col-lg-6">

            <div class="btn-toolbar float-sm-right">
                <?php if (($user_activity || $Settings->hasPermission('activities.edit')) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                    <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn secondary">
                        <i class="ph ph-pencil-simple-line"></i>
                        <?= lang('Edit', 'Bearbeiten') ?>
                    </a>
                <?php } ?>


                <?php if (!in_array($doc['type'], ['publication'])) { ?>
                    <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn secondary">
                        <i class="ph ph-copy"></i>
                        <?= lang("Add a copy", "Kopie anlegen") ?>
                    </a>
                <?php } ?>


                <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                    <!-- End user activity even if activity is locked -->
                    <div class="dropdown">
                        <button class="btn secondary" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-calendar-check"></i>
                            <?= lang('End activity', 'Beenden') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-center w-200" aria-labelledby="update-end-date">
                            <form action="<?= ROOTPATH . "/crud/activities/update/" . $id ?>" method="POST" class="content">
                                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/" . $id ?>">
                                <div class="form-group">
                                    <label for="date_end"><?= lang('Activity ended at:', 'Aktivität beendet am:') ?></label>
                                    <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? null) ?>" required>
                                </div>
                                <button class="btn btn-block" type="submit"><?= lang('Save', 'Speichern') ?></button>
                            </form>
                        </div>
                    </div>
                <?php } ?>

            </div>

            <h2 class="mt-0">Details</h2>

            <table class="table" id="detail-table">

                <tr>
                    <td>
                        <span class="key"><?= lang('Formatted entry', 'Formatierter Eintrag') ?></span>
                        <?= $Format->format() ?>
                    </td>
                </tr>
                <?php
                $selected = $Format->subtypeArr['modules'] ?? array();
                $Modules = new Modules($doc);
                $Format->usecase = "list";

                foreach ($selected as $module) {
                    if (str_ends_with($module, '*')) $module = str_replace('*', '', $module);
                    if (in_array($module, ["semester-select", "event-select"])) continue;
                ?>
                    <?php if ($module == 'teaching-course' && isset($doc['module_id'])) :
                        $module = $DB->getConnected('teaching', $doc['module_id']);
                    ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Teaching module', 'Lehrveranstaltung') ?></span>

                                <a class="module " href="<?= ROOTPATH ?>/teaching#<?= $doc['module_id'] ?>">
                                    <h5 class="m-0"><span class="highlight-text"><?= $module['module'] ?></span> <?= $module['title'] ?></h5>
                                    <span class="text-muted-"><?= $module['affiliation'] ?></span>
                                </a>
                            </td>
                        </tr>

                    <?php elseif ($module == 'journal' && isset($doc['journal_id'])) :
                        $journal = $DB->getConnected('journal', $doc['journal_id']);
                    ?>

                        <tr>
                            <td>
                                <span class="key"><?= lang('Journal') ?></span>

                                <a class="module " href="<?= ROOTPATH ?>/journal/view/<?= $doc['journal_id'] ?>">
                                    <h6 class="m-0"><?= $journal['journal'] ?></h6>
                                    <span class="float-right text-muted-"><?= $journal['publisher'] ?></span>
                                    <span class="text-muted-">
                                        ISSN: <?= print_list($journal['issn']) ?>
                                        <br>
                                        Impact:
                                        <?= $doc['impact'] ?? 'unknown' ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php elseif ($module == 'conference' && isset($doc['conference_id'])) :
                        $conference = $DB->getConnected('conference', $doc['conference_id']);
                    ?>

                        <tr>
                            <td>
                                <span class="key">Event</span>
                                <?php if (empty($conference)) { ?>
                                    <span class="text-danger">
                                        <?= lang('This event has been deleted.', 'Diese Veranstaltung wurde gelöscht.') ?>
                                    </span>
                                <?php } else { ?>

                                    <div class="module ">
                                        <h6 class="m-0">
                                            <a href="<?= ROOTPATH ?>/conferences/<?= $doc['conference_id'] ?>">
                                                <?= $conference['title'] ?>
                                            </a>
                                        </h6>
                                        <div class="text-muted mb-10"><?= $conference['title_full'] ?></div>
                                        <ul class="horizontal mb-0">
                                            <li>
                                                <b><?= lang('Location', 'Ort') ?></b>: <?= $conference['location'] ?>
                                            </li>
                                            <li>
                                                <b><?= lang('Date', 'Datum') ?></b>: <?= fromToDate($conference['start'], $conference['end']) ?>
                                            </li>
                                            <li>
                                                <a href="<?= $conference['url'] ?>" target="_blank">
                                                    <i class="ph ph-link"></i>
                                                    <?= lang('Website', 'Website') ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php else : ?>

                        <tr>
                            <td>
                                <span class="key"><?= $Modules->get_name($module) ?></span>
                                <?= $Format->get_field($module) ?>
                            </td>
                        </tr>

                    <?php endif; ?>

                <?php } ?>


                <?php if (($user_activity || $Settings->hasPermission('activities.edit')) && isset($doc['comment'])) : ?>
                    <tr class="text-muted">
                        <td>
                            <span class="key" style="text-decoration: 1px dotted underline;" data-toggle="tooltip" data-title="<?= lang('Only visible for authors and editors.', 'Nur sichtbar für Autoren und Editor-MA.') ?>">
                                <?= lang('Comment', 'Kommentar') ?>:
                            </span>

                            <?= $doc['comment'] ?>
                        </td>
                    </tr>
                <?php endif; ?>


            </table>


            <div class="alert danger mt-20 py-20">
                <h2 class="title">
                    <?= lang('Delete', 'Löschen') ?>
                </h2>
                <?php

                // $in_quarter = inCurrentQuarter($doc['year'], $doc['month']);
                if ($locked && !$Settings->hasPermission('activities.delete-locked')) : ?>
                    <p class="mt-0">
                        <?= lang(
                            'This activity has been locked because it was already used by reporters in a report. Due to the documentation and verification obligation, activities may not be easily changed or deleted after the report. However, if a change is necessary, please contact the responsible persons.',
                            'Diese Aktivität wurde ge