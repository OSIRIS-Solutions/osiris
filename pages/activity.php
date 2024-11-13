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
</style>

<script>
    const ACTIVITY_ID = '<?= $id ?>';
    const TYPE = '<?= $doc['type'] ?>';
</script>

<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>

<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
<script src="<?= ROOTPATH ?>/js/activity.js?v=1"></script>



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
        <a href="#connect" class="btn text-primary border-primary">
            <i class="ph ph-plus-circle"></i>
            <?= lang("Tags", "Schlagwörter") ?>
        </a>
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
 <?= $Settings->printTopics($doc['topics'] ?? []) ?>


<?php if ($Settings->featureEnabled('portal') && ($user_activity || $Settings->hasPermission('activities.edit'))) {
    $highlights = DB::doc2Arr($USER['highlighted'] ?? []);
    $highlighted = in_array($id, $highlights);

?>

    <div class="box">
        <div class="p-10">
            <b><?= lang('Portfolio visibility', 'Sichtbarkeit in Portfolio') ?></b>
            <div class="mt-10 d-flex">
                <?php if (in_array($doc['subtype'], $Settings->getActivitiesPortfolio(true))) { ?>
                    <?php if ($user_activity) { ?>
                        <div class="custom-switch ml-10">
                            <input type="checkbox" id="highlight" <?= ($highlighted) ? 'checked' : '' ?> name="values[highlight]" onchange="fav()">
                            <label for="highlight">
                                <?= lang('Highlight on your profile', 'Auf deinem Profil hervorheben') ?>
                            </label>
                        </div>
                    <?php } ?>

                    <!-- hide in all activities on portal -->
                    <div class="custom-switch ml-10">
                        <input type="checkbox" id="hide" <?= ($doc['hide'] ?? false) ? 'checked' : '' ?> name="values[hide]" onchange="hide()">
                        <label for="hide">
                            <?= lang('Hide in portfolio', 'Im Portfolio verstecken') ?>
                        </label>
                    </div>
                <?php } else { ?>
                    <span class="badge signal ml-10" data-toggle="tooltip" data-title="<?= lang('This activity cannot be shown on Portfolio due to institute settings.', 'Diese Aktivität kann aufgrund von Instituts-Einstellungen nicht im Portfolio gezeigt werden.') ?>">
                        <i class="ph ph-eye-slash"></i>
                        <?= lang('Not visible', 'Nicht sichtbar') ?>
                    </span>
                <?php } ?>
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
                            toastSuccess(lang('Highlight status changed', 'Hervorhebungsstatus geändert'))
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                }


                function hide() {
                    $.ajax({
                        type: "POST",
                        url: ROOTPATH + "/crud/activities/hide",
                        data: {
                            activity: ACTIVITY_ID
                        },
                        dataType: "json",
                        success: function(response) {
                            toastSuccess(lang('Highlight status changed', 'Hervorhebungsstatus geändert'))
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                }
            </script>



        </div>
    </div>
<?php } ?>


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

    <?php
    $count_data = count($doc['connections'] ?? []);
    if ($count_data) :
    ?>
        <a onclick="navigate('tags')" id="btn-tags" class="btn">
            <i class="ph ph-circles-three-plus" aria-hidden="true"></i>
            <?= lang('Tags', 'Schlagwörter') ?>
            <span class="index"><?= $count_data ?></span>
        </a>

    <?php else : ?>
        <a href="#connect" class="btn">
            <i class="ph ph-plus-circle"></i>
            <?= lang('Add research data', 'Schlagwörter') ?>
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
        <div class="col-lg-7">

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
                    if (in_array($module, ["semester-select"])) continue;
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
                            'This activity has been locked because it was already used by Controlling in a report. Due to the documentation and verification obligation, activities may not be easily changed or deleted after the report. However, if a change is necessary, please contact the responsible persons.',
                            'Diese Aktivität wurde gesperrt, da sie bereits vom Controlling in einem Report verwendet wurde. Wegen der Dokumentations- und Nachweispflicht dürfen Aktivitäten nach dem Report nicht mehr so einfach verändert oder gelöscht werden. Sollte dennoch eine Änderung notwenig sein, meldet euch bitte bei den Verantwortlichen.'
                        ) ?>
                    </p>
                    <?php
                    $body = $USER['displayname'] . " möchte folgenden OSIRIS-Eintrag bearbeiten/löschen: $name%0D%0A%0D%0ABegründung/Reason:%0D%0A%0D%0Ahttp://osiris.int.dsmz.de/activities/view/$id";
                    ?>
                    <!-- <a class="btn danger" href="mailto:dominic.koblitz@dsmz.de?cc=julia.koblitz@dsmz.de&subject=[OSIRIS] Antrag auf Änderung&body=<?= $body ?>">
                    <i class="ph ph-envelope" aria-hidden="true"></i>
                    <?= lang('Contact controlling', 'Controlling kontaktieren') ?>
                </a> -->
                <?php
                elseif ($Settings->hasPermission('activities.delete')) :
                ?>
                    <p class="mt-0">
                        <?= lang('You have permission to delete this activity:', 'Du hast die nötigen Rechte, um diese Aktivität zu löschen:') ?>
                    </p>
                    <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" class="d-inline-block ml-auto">
                        <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                        <button type="submit" class="btn danger">
                            <i class="ph ph-trash"></i>
                            <?= lang('Delete activity', 'Lösche Aktivität') ?>
                        </button>
                    </form>
                <?php elseif (!$user_activity) : ?>

                    <p class="mt-0">
                        <?= lang(
                            'This is not your own activity. If for any reason you want it changed or deleted, please contact the creator of the activity or the controlling.',
                            'Dies ist nicht deine Aktivität. Wenn du aus irgendwelchen Gründen willst, dass sie verändert oder gelöscht wird, kontaktiere bitte den Urheber der Aktivität oder das Controlling.'
                        ) ?>
                    </p>

                <?php else : ?>
                    <p class="mt-0">
                        <b>Info:</b>
                        <?= lang(
                            'This is your own activity and it has not been locked yet. You can delete it.',
                            'Dies ist deine eigene Aktivität und sie ist noch nicht gesperrt worden. Du kannst sie also löschen.'
                        ) ?>
                    </p>
                    <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" class="d-inline-block ml-auto">
                        <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                        <button type="submit" class="btn danger">
                            <i class="ph ph-trash"></i>
                            <?= lang('Delete activity', 'Lösche Aktivität') ?>
                        </button>
                        <br>
                        <small class="text-danger">
                            <?= lang('Cannot be made undone.', 'Kann nicht rückgängig gemacht werden.') ?>
                        </small>
                    </form>
                <?php endif; ?>

            </div>
        </div>


        <div class="col-lg-5">
            <?php foreach (['authors', 'editors'] as $role) {
                if (!isset($activity[$role])) continue;
            ?>

                <div class="btn-toolbar mb-10 float-sm-right">
                    <?php if (($user_activity || $Settings->hasPermission('activities.edit')) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                        <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/<?= $role ?>" class="btn secondary">
                            <i class="ph ph-pencil-simple-line"></i>
                            <?= lang("Edit", "Bearbeiten") ?>
                        </a>
                    <?php } ?>
                </div>

                <h2 class="mt-0">
                    <?php if ($role == 'authors') {
                        echo lang('Authors', 'Autoren');
                    } else {
                        echo lang('Editors', 'Editoren');
                    } ?>
                </h2>


                <table class="table">
                    <thead>
                        <tr>
                            <th><?= lang('Last', 'Nachname') ?></th>
                            <th><?= lang('First', 'Vorname') ?></th>

                            <?php if ($sws) : ?>
                                <th>SWS</th>
                            <?php elseif ($role == 'authors') : ?>
                                <th>Position</th>
                            <?php endif; ?>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody id="<?= $role ?>">
                        <?php foreach ($activity[$role] as $i => $author) {
                        ?>
                            <tr>
                                <td class="<?= (($author['aoi'] ?? 0) == '1' ? 'font-weight-bold' : '') ?>">
                                    <?= $author['last'] ?? '' ?>
                                </td>
                                <td>
                                    <?= $author['first'] ?? '' ?>
                                </td>
                                <?php if ($sws) : ?>
                                    <td>
                                        <?= $author['sws'] ?? 0 ?>
                                    </td>
                                <?php elseif ($role == 'authors') : ?>
                                    <td>
                                        <?= $author['position'] ?? '' ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if (isset($author['user']) && !empty($author['user'])) : ?>
                                        <a href="<?= ROOTPATH ?>/profile/<?= $author['user'] ?>"><i class="ph ph-user"></i></a>
                                        <span data-toggle="tooltip" data-title="<?= lang('Author approved activity?', 'Autor hat die Aktivität bestätigt?') ?>">
                                            <?= bool_icon($author['approved'] ?? 0) ?>
                                        </span>
                                    <?php else : ?>
                                        <div class="dropdown">
                                            <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                                <?= lang('Claim', 'Beanspruchen') ?>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right w-300" aria-labelledby="dropdown-1">
                                                <div class="content">
                                                    <small class="d-block text-danger mb-10">
                                                        <?= lang(
                                                            'You claim that you are this author.<br> This activity will be added to your list and the author name will be added to your list of alternative names.',
                                                            'Du beanspruchst, dass du diese Person bist.<br> Du fügst diese Aktivität deiner Liste hinzu und den Namen zur Liste deiner alternativen Namen.'
                                                        ) ?>
                                                    </small>
                                                    <form action="<?= ROOTPATH ?>/crud/activities/claim/<?= $id ?>" method="post">
                                                        <input type="hidden" name="role" value="<?= $role ?>">
                                                        <input type="hidden" name="index" value="<?= $i ?>">
                                                        <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/$id" ?>">
                                                        <button class="btn btn-block" type="submit"><?= lang('Claim', 'Beanspruchen') ?></button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>
</section>



<?php if ($Settings->featureEnabled('projects')) { ?>
    <div class="modal" id="projects" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title">
                    <?= lang('Connect projects', 'Projekte verknüpfen') ?>
                </h5>
                <div>
                    <?php
                    include BASEPATH . "/components/connect-projects.php";
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>


<?php if ($Settings->featureEnabled('projects')) { ?>
    <section id="projects" style="display: none;">
        <div class="btn-toolbar float-sm-right">
            <a href="#projects" class="btn secondary mr-5">
                <i class="ph ph-tree-structure"></i>
                <?= lang("Connect", "Verknüpfen") ?>
            </a>
        </div>

        <h2 class="title">
            <?= lang('Projects', 'Projekte') ?>
        </h2>

        <?php if (!empty($doc['projects'] ?? '') && !empty($doc['projects'][0])) {

            require_once BASEPATH . "/php/Project.php";
            $Project = new Project();

            foreach ($doc['projects'] as $project_id) {
                $project = $osiris->projects->findOne(['name' => $project_id]);
                if (empty($project)) continue;
                $Project->setProject($project);
        ?>
                <?= $Project->widgetSmall(true) ?>
            <?php } ?>

        <?php } else { ?>
            <?= lang('No projects connected.', 'Noch keine Projekte verknüpft.') ?>
        <?php } ?>

    </section>
<?php } ?>



<div class="modal" id="upload-files" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <?= lang('Upload files', 'Dateien hochladen') ?>
            </h5>
            <div>
                <?php
                include BASEPATH . "/components/upload-files.php";
                ?>
            </div>
        </div>
    </div>
</div>


<section id="files" style="display: none;">




    <div class="btn-toolbar float-sm-right">
        <a href="#upload-files" class="btn secondary">
            <i class="ph ph-upload"></i>
            <?= lang('Upload', 'Hochladen') ?>
        </a>
    </div>

    <h2 class="title"><?= lang('Files', 'Dateien') ?></h2>

    <?php if (!empty($doc['files'])) : ?>
        <?php foreach ($doc['files'] as $file) : ?>
            <a href="<?= $file['filepath'] ?>" target="_blank" class="filelink">
                <i class="ph ph-<?= getFileIcon($file['filetype']) ?> mr-10 ph-2x text-osiris"></i>

                <?= $file['filename'] ?>
            </a>
        <?php endforeach; ?>
    <?php else : ?>
        <span class="text-signal"><?= lang('No files attached', 'Noch keine Dateien hochgeladen') ?></span>
    <?php endif; ?>

</section>



<div class="modal" id="connect" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <?= lang('Connect research data', 'Schlagwörter verknüpfen') ?>
            </h5>
            <div>
                <?php
                include BASEPATH . "/components/connect-tags.php";
                ?>

            </div>
        </div>
    </div>
</div>

<section id="tags" style="display: none;">


    <div class="btn-toolbar float-sm-right">
        <a href="#connect" class="btn secondary mr-5">
            <i class="ph ph-circles-three-plus"></i>
            <?= lang("Connect", "Verknüpfen") ?>
        </a>
    </div>

    <h2 class="title">
        <?= lang('Tags', 'Schlagwörter') ?>
    </h2>

    <?php if (!empty($doc['connections'] ?? null)) { ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Entity</th>
                    <th>Name</th>
                    <th>Link</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($doc['connections'] as $con) { ?>
                    <tr>
                        <th class="mr-10"><?= $con['entity'] ?></th>
                        <td>
                            <?= $con['name'] ?>
                        </td>
                        <td>
                            <?php if (!empty($con['link'])) { ?>
                                <a href="<?= $con['link'] ?>" class="badge " target="_blank">
                                    <i class="ph ph-link text-primary" style="line-height: 0;"></i>
                                    <?= $con['link'] ?>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>

        </table>
    <?php } else { ?>
        <?= lang('No research data connected.', 'Noch keine Schlagwörter verknüpft.') ?>
    <?php } ?>
</section>

<?php if ($Settings->featureEnabled('concepts')) { ?>
    <section id="concepts" style="display:none">
        <?php if (isset($doc['concepts'])) :
        ?>

            <h3 class=""><?= lang('Concepts', 'Konzepte') ?></h3>
            <div class="box">
                <div class="content">
                    <?php foreach ($doc['concepts'] as $concept) {
                        $score =  round($concept['score'] * 100);
                        // if ($concept['score'] < .3) continue;
                    ?><span class="concept" target="_blank" data-score='<?= $score ?>' data-name='<?= $concept['display_name'] ?>' data-wikidata='<?= $concept['wikidata'] ?>'>
                            <div role="progressbar" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100" style="--value: <?= $score ?>"></div>
                            <?= $concept['display_name'] ?>
                        </span><?php } ?>
                </div>
            </div>
        <?php else : ?>
            <p>
                <?= lang('No concepts are assigned to this activity.', 'Zu dieser Aktivität sind keine Konzepte zugewiesen.') ?>
            </p>
        <?php endif; ?>
    </section>
<?php } ?>


<section id="coauthors" style="display:none">
    <h2>
        <i class="ph ph-graph" aria-hidden="true"></i>
        <?= lang('Coauthors', 'Koautoren') ?>
    </h2>
    <div class="row row-eq-spacing">
        <div class="col-md-6 flex-grow-0" style="max-width: 40rem">
            <div id="chart-authors">
                <canvas id="chart-authors-canvas"></canvas>
            </div>
        </div>
        <div class="offset-1"></div>
        <div class="col-md-5">
            <div id="dept-legend"></div>
        </div>
    </div>
</section>


<!-- new section with history -->
<section id="history" style="display: none;">
    <h2 class="title">
        <?= lang('History', 'Historie') ?>
    </h2>
    <p>
        <?= lang('History of changes to this activity.', 'Historie der Änderungen an dieser Aktivität.') ?>
    </p>

    <style>
        .history-list {
            /* reverse order */
            display: flex;
            flex-direction: column-reverse;
        }

        .history-list .box {
            margin-top: 0;
        }

        .del {
            color: var(--danger-color);
        }

        .ins {
            color: var(--success-color);
        }

        blockquote.signal {
            border-left: 5px solid var(--signal-color);
            padding-left: 1rem;
            margin-top: 1rem;
            margin-left: 0;
        }

        blockquote.signal .title {
            font-weight: bold;
            color: var(--signal-color);
        }
    </style>
    <?php
    if (empty($doc['history'] ?? [])) {
        echo lang('No history available.', 'Keine Historie verfügbar.');
    } else {
        // require BASEPATH . "/php/TextDiff/TextDiff.php";
        // $latest = '';
    ?>
        <div class="history-list">
            <?php foreach (($doc['history']) as $h) {
                if (!is_array($h)) continue;
            ?>
                <div class="box p-20">
                    <span class="badge primary float-md-right"><?= date('d.m.Y', strtotime($h['date'])) ?></span>
                    <h5 class="m-0">
                        <?php
                        switch ($h['type']) {
                            case 'created':
                                echo lang('Created by ', 'Erstellt von ');
                                break;
                            case 'edited':
                                echo lang('Edited by ', 'Bearbeitet von ');
                                break;
                            case 'deleted':
                                echo lang('Deleted by ', 'Gelöscht von ');
                                break;
                            case 'imported':
                                echo lang('Imported by ', 'Importiert von ');
                                break;
                            default:
                                echo $h['type'] . ' by ';
                                break;
                        }
                        if (isset($h['user']) && !empty($h['user']))
                            echo $DB->getNameFromId($h['user']);
                        else echo "OSIRIS System";
                        ?>
                    </h5>

                    <?php
                    if (isset($h['comment']) && !empty($h['comment'])) { ?>
                        <blockquote class=" signal">
                            <div class="title">
                                <?= lang('Comment', 'Kommentar') ?>
                            </div>
                            <?= $h['comment'] ?>
                        </blockquote>
                    <?php
                    }
                    if (isset($h['changes']) && !empty($h['changes'])) {
                        echo '<div class="font-weight-bold mt-10">' .
                            lang('Changes to the activity:', 'Änderungen an der Aktivität:') .
                            '</div>';
                        echo '<table class="table simple w-auto small border px-10">';
                        foreach ($h['changes'] as $key => $change) {
                            $before = $change['before'] ?? '<em>empty</em>';
                            $after = $change['after'] ?? '<em>empty</em>';
                            if ($before == $after) continue;
                            if (empty($before)) $before = '<em>empty</em>';
                            if (empty($after)) $after = '<em>empty</em>';
                            echo '<tr>
                                <td class="pl-0">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    <span class="del">' . $before . '</span>
                                    <i class="ph ph-arrow-right mx-10"></i>
                                    <span class="ins">' . $after . '</span>
                                </td>
                            </tr>';
                        }
                        echo '</table>';
                    } else  if (isset($h['data']) && !empty($h['data'])) {
                        echo '<div class="font-weight-bold mt-10">' .
                            lang('Status at this time point:', 'Status zu diesem Zeitpunkt:') .
                            '</div>';

                        echo '<table class="table simple w-auto small border px-10">';
                        foreach ($h['data'] as $key => $datum) {
                            echo '<tr>
                                <td class="pl-0">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    ' . $datum . ' 
                                </td>
                            </tr>';
                        }
                        echo '</table>';
                    } else if ($h['type'] == 'edited') {
                        echo lang('No changes tracked.', 'Es wurden keine Änderungen verfolgt.');
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<?php if ($guests_involved) { ?>


    <?php if ($Settings->featureEnabled('guest-forms')) {

        $guest_server = $Settings->get('guest-forms-server');
        $url = $guest_server . "/a/" . $id;
    ?>
        <script src="<?= ROOTPATH ?>/js/papaparse.min.js"></script>
        <!-- modals -->
        <div class="modal" id="add-guests" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title">
                        <?= lang('Add guests', 'Gäste hinzufügen') ?>
                    </h5>
                    <div>
                        <h3>
                            <?= lang('Add guests to this activity', 'Füge Gäste zu dieser Aktivität hinzu') ?>
                        </h3>
                        <p>
                            <?= lang('You can add guests to this activity by entering their names and affiliations.', 'Du kannst Gäste zu dieser Aktivität hinzufügen, indem du ihre Namen und Zugehörigkeiten eingibst.') ?>
                        </p>

                        <form action="<?= ROOTPATH ?>/crud/activities/guests" method="post">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <table class="table mb-20">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th><?= lang('Last', 'Nachname') ?></th>
                                        <th><?= lang('First', 'Vorname') ?></th>
                                        <th><?= lang('Email') ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="guest-list">
                                    <?php foreach ($guests as $guest) { ?>
                                        <tr>
                                            <td>
                                                <input type="text" name="guests[id][]" class="form-control disabled" required value="<?= $guest['id'] ?>" readonly>
                                            </td>
                                            <td>
                                                <input type="text" name="guests[last][]" class="form-control" required value="<?= $guest['last'] ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="guests[first][]" class="form-control" required value="<?= $guest['first'] ?>">
                                            </td>
                                            <td>
                                                <input type="email" name="guests[email][]" class="form-control" required value="<?= $guest['email'] ?>">
                                            </td>
                                            <td>
                                                <button type="button" class="btn small link" id="remove-guest" onclick="$(this).closest('tr').remove()">
                                                    <i class="ph ph-trash text-danger"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">
                                            <button type="button" class="btn small" id="add-guest" onclick="addGuestRow()">
                                                <i class="ph ph-plus"></i>
                                                <?= lang('Add guest', 'Gast hinzufügen') ?>
                                            </button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>

                            <button type="submit" class="btn primary">
                                <i class="ph ph-save"></i>
                                <?= lang('Save guests', 'Gäste speichern') ?>
                            </button>

                        </form>

                        <div class="box">
                            <div class="content">
                                <h3>
                                    <?= lang('Import guests from CSV', 'Gäste aus CSV importieren') ?>
                                </h3>
                                <p>
                                    <?= lang('You can import a list of guests from a CSV file.', 'Du kannst eine Liste von Gästen aus einer CSV-Datei importieren.') ?>
                                </p>
                                <div class="custom-file">
                                    <input type="file" id="guest-file">
                                    <label for="guest-file"><?= lang('Select file', 'Datei auswählen') ?></label>
                                </div>
                                <small>
                                    <?= lang('The file should contain columns for last name, first name and email address. A header row is required.', 'Die Datei sollte Spalten für Nachname, Vorname und E-Mail-Adresse enthalten. Eine Zeile mit Überschriften ist notwendig.') ?>
                                </small>
                            </div>

                            <script>
                                document.getElementById('guest-file').addEventListener('change', function(e) {
                                    var file = e.target.files[0];
                                    if (!file) return;
                                    Papa.parse(file, {
                                        header: true,
                                        complete: function(results) {
                                            console.log(results);
                                            results.data.forEach(function(raw) {
                                                var row = {};
                                                // try to find first and last name and email
                                                ['first', 'first name', 'vorname', 'First name', 'First', 'Vorname', 'FIRST', 'FIRST NAME', 'VORNAME'].forEach(key => {
                                                    if (raw[key]) {
                                                        row.first = raw[key];
                                                        return
                                                    }
                                                });
                                                ['last', 'last name', 'nachname', 'Last name', 'Last', 'Nachname', 'LAST', 'LAST NAME', 'NACHNAME'].forEach(key => {
                                                    if (raw[key]) {
                                                        row.last = raw[key];
                                                        return
                                                    }
                                                });
                                                ['email', 'mail', 'Email', 'Mail', 'E-Mail'].forEach(key => {
                                                    if (raw[key]) {
                                                        row.email = raw[key];
                                                        return
                                                    }
                                                });
                                                if (!row.first && !row.last) {
                                                    ['name', 'Name', 'NAME'].forEach(key => {
                                                        if (raw[key]) {

                                                            // try last, first
                                                            var parts = raw[key].split(', ');
                                                            if (parts.length == 2) {
                                                                row.last = parts[0];
                                                                row.first = parts[1];
                                                                return
                                                            }
                                                            // try first last
                                                            var parts = raw[key].split(' ');
                                                            if (parts.length == 2) {
                                                                row.first = parts[0];
                                                                row.last = parts[1];
                                                                return
                                                            }
                                                        }
                                                    });
                                                }


                                                addGuestRow(row);
                                            });
                                        }
                                    });
                                });
                            </script>

                        </div>

                        <script>
                            function addGuestRow(data = {}) {
                                var row = document.createElement('tr');
                                var id = Math.random().toString(36).substring(7);
                                row.innerHTML = `
                                    <td>
                                        <input type="text" name="guests[id][]" class="form-control disabled" required readonly value="${id}">
                                    </td>
                                    <td>
                                        <input type="text" name="guests[last][]" class="form-control" required value="${data.last ?? ''}">
                                    </td>
                                    <td>
                                        <input type="text" name="guests[first][]" class="form-control" required value="${data.first ?? ''}">
                                    </td>
                                    <td>
                                        <input type="email" name="guests[email][]" class="form-control" required value="${data.email ?? ''}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn small link" id="remove-guest" onclick="$(this).closest('tr').remove()">
                                            <i class="ph ph-trash text-danger"></i>
                                        </button>
                                    </td>
                                `;
                                document.getElementById('guest-list').appendChild(row);
                            }
                        </script>
                    </div>
                </div>
            </div>
        </div>

    <?php } ?>


    <section id="guests" style="display:none">

        <h2 class="title">
            <?= lang('Guests', 'Gäste') ?>
        </h2>

        <?php if ($Settings->featureEnabled('guest-forms')) {

        ?>
            <a href="#add-guests" class="btn primary">
                <i class="ph ph-plus" aria-hidden="true"></i>
                <?= lang('Add guests', 'Gäste hinzufügen') ?>
            </a>

        <?php } ?>

        <p>
            <?= lang('There are currently ' . count($guests) . ' guests involved in this activity.', 'Aktuell sind ' . count($guests) . ' Gäste an dieser Aktivität beteiligt.') ?>
        </p>

        <?php if ($user_activity || $Settings->hasPermission('guests.view')) {
            $new_guests = false;
        ?>

            <table class="table mb-20">
                <thead>
                    <tr>
                        <th><?= lang('Last', 'Nachname') ?></th>
                        <th><?= lang('First', 'Vorname') ?></th>
                        <th><?= lang('Email') ?></th>
                        <th><?= lang('Status') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guests as $guest) { ?>
                        <tr>
                            <td><?= $guest['last'] ?></td>
                            <td><?= $guest['first'] ?></td>
                            <td><?= $guest['email'] ?></td>
                            <td>
                                <?php
                                switch ($guest['status'] ?? 'new') {
                                    case 'pending':
                                        echo '<span class="badge primary">' . lang('Pending', 'Ausstehend') . '</span>';
                                        break;
                                    case 'approved':
                                        echo '<span class="badge success">' . lang('Approved', 'Bestätigt') . '</span>';
                                        break;
                                    case 'new':
                                        echo '<span class="badge signal">' . lang('New', 'Neu') . '</span>';
                                        $new_guests = true;
                                        break;
                                    default:
                                        echo '<span class="badge danger">' . lang('Unknown', 'Unbekannt') . '</span>';
                                        break;
                                }
                                ?>

                                <!-- action buttons -->
                                <!-- send mail -->
                                <?php if (($guest['status'] ?? 'new') == 'new') { ?>
                                    <form action="<?= ROOTPATH ?>/crud/activities/guest-mail/<?= $id ?>" method="post" class="d-inline-block">
                                        <input type="hidden" name="guest" value="<?= $guest['id'] ?>">
                                        <button type="submit" class="btn small">
                                            <i class="ph ph-envelope" aria-hidden="true"></i>
                                            <?= lang('Send email', 'E-Mail senden') ?>
                                        </button>
                                    </form>
                                <?php } ?>

                                <!-- show qr code -->
                                <button type="button" class="btn small" data-toggle="modal" data-target="qr-<?= $guest['id'] ?>">
                                    <i class="ph ph-qr-code" aria-hidden="true"></i>
                                    <?= lang('QR code', 'QR-Code') ?>
                                </button>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <!-- qr modals -->
            <?php foreach ($guests as $guest) {
                $guest_server = $Settings->get('guest-forms-server');
                $url = $guest_server . "/a/" . $id . "." . $guest['id'];
                $options = new QROptions([]);

                try {
                    $qr = (new QRCode($options))->render($url);
                } catch (Throwable $e) {
                    $qr = '';
                    exit($e->getMessage());
                }
            ?>
                <div class="modal" id="qr-<?= $guest['id'] ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                                <span aria-hidden="true">&times;</span>
                            </a>
                            <h5 class="title">
                                <?= lang('QR code for ', 'QR-Code für ') . $guest['first'] . ' ' . $guest['last'] ?>
                            </h5>
                            <div>
                                <div style="background-color: white; display: inline-block;">
                                    <img src="<?= $qr ?>" alt="QR code for <?= $guest['first'] . ' ' . $guest['last'] ?>" class="w-200">
                                </div>
                                <br>
                                <b>Link:</b>
                                <a href="<?= $url ?>" target="_blank"><?= $url ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php if ($new_guests) { ?>
                <!-- send email to all new guests -->
                <form action="<?= ROOTPATH ?>/crud/activities/guest-mail/<?= $id ?>" method="post">
                    <button type="submit" class="btn primary">
                        <i class="ph ph-envelope" aria-hidden="true"></i>
                        <?= lang('Send email to new guests', 'Sende E-Mail an neue Gäste') ?>
                    </button>
                </form>
            <?php } ?>


        <?php } else { ?>
            <p>
                <?= lang('You do not have permission to view the list of guests. Only authors of the activity and users with the `guests.view` permission can view the list.', 'Du hast keine Berechtigung, die Liste der Gäste einzusehen. Nur Autor:innen der Aktivität und Personen mit der `guests.view`-Berechtigung können die Liste sehen.') ?>
            </p>
        <?php } ?>




    </section>
<?php } ?>


<section id="raw" style="display:none">

    <h2 class="title">
        <?= lang('Raw data', 'Rohdaten') ?>
    </h2>

    <?= lang('Raw data as they are stored in the database.', 'Die Rohdaten, wie sie in der Datenbank gespeichert werden.') ?>

    <div class="box overflow-x-scroll">
        <?php
        dump($doc, true);
        ?>
    </div>

</section>