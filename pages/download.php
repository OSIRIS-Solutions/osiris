<?php

/**
 * Page to download activities
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /download
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>


<style>
    @media (min-width: 768px) {
        .row.row-eq-spacing-sm:not(.row-eq-spacing) {
            margin-left: calc(-2rem/2);
            padding-left: 0;
            margin-right: calc(-2rem/2);
            padding-right: 0;
        }
    }
</style>

<div class="container">

    <form action="<?= ROOTPATH ?>/download" method="post">

        <h1>
            <i class="ph-duotone ph-download"></i>
            <?= lang('Export activities', 'Exportiere Aktivitäten') ?>
        </h1>

        <div class="form-group">
            <label for="filter-type"><?= lang('Filter by type', 'Filter nach Art der Aktivität') ?></label>
            <select name="filter[type]" id="filter-type" class="form-control">
                <option value=""><?= lang('All type of activities', 'Alle Arten von Aktivitäten') ?></option>
                <?php foreach ($Settings->getActivities() as $a) { ?>
                    <option value="<?= $a['id'] ?>"><?= lang($a['name'], $a['name_de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>


        <?php if ($Settings->hasPermission('download.all', true)) { ?>

            <div class="row position-relative mb-20">
                <div class="col">
                    <div class="mr-20">

                        <label for="filter-user"><?= lang('Filter by user', 'Filter nach Nutzer') ?></label>
                        <select name="filter[user]" id="filter-user" class="form-control">
                            <option value="">Alle Nutzer</option>
                            <option value="<?= $_SESSION['username'] ?>"><?= lang('Only my own activities', 'Nur meine eigenen Aktivitäten') ?></option>
                        </select>
                    </div>
                </div>

                <div class="text-divider"><?= lang('OR', 'ODER') ?></div>

                <div class="col">
                    <div class="ml-20">
                        <label for="dept"><?= lang('Department', 'Abteilung') ?></label>
                        <select name="filter[dept]" id="dept" class="form-control">
                            <option value=""><?= lang('All departments', 'Alle Abteilungen') ?></option>
                            <?php
                            foreach ($Departments as $d => $dept) { ?>
                                <option value="<?= $d ?>"><?= $dept ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <input type="hidden" name="filter[user]" value="<?= $_SESSION['username'] ?>" class="hidden">
            <p>
                <i class="ph-duotone ph-warning text-primary"></i>
                <?= lang('You can only download your own activities.', 'Sie können nur Ihre eigenen Aktivitäten herunterladen.') ?>
            </p>
        <?php } ?>

        <?php if ($Settings->featureEnabled('quality-workflow') && $Settings->hasPermission('workflows.view')) { ?>
            <div class="form-group">
                <label for="filter-workflow"><?= lang('Filter by workflow status', 'Filter nach Workflow-Status') ?></label>
                <select name="filter[workflow]" id="filter-workflow" class="form-control">
                    <option value=""><?= lang('Do not filter', 'Nicht filtern') ?></option>
                    <option value="in_progress"><?= lang('In Progress', 'In Bearbeitung') ?></option>
                    <option value="verified"><?= lang('Completed', 'Abgeschlossen') ?></option>
                    <option value="verified-or-empty"><?= lang('Completed or no workflow', 'Abgeschlossen oder kein Workflow') ?></option>
                    <option value="rejected"><?= lang('Rejected', 'Abgelehnt') ?></option>
                </select>
            </div>
        <?php } ?>

        <?php if ($Settings->featureEnabled('topics')) {
            $topics = $osiris->topics->find();
            if (!empty($topics)) { ?>
                <div class="form-group">
                    <label for="filter-topic"><?= lang('Filter by topic', 'Filter nach Thema') ?></label>
                    <select name="filter[topic]" id="filter-topic" class="form-control">
                        <option value=""><?= lang('All topics', 'Alle Themen') ?></option>
                        <?php foreach ($topics as $topic) { ?>
                            <option value="<?= $topic['id'] ?>"><?= lang($topic['name'], $topic['name_de'] ?? null) ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
        <?php } ?>


        <div class="form-group">
            <label for="filter-year"><?= lang('Filter by time frame', 'Filter nach Zeitraum') ?></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><?= lang('From', 'Von') ?></span>
                </div>
                <input type="number" name="filter[time][from][month]" class="form-control" placeholder="month" min="1" max="12" step="1" id="from-month" onchange="filtertime()">
                <input type="number" name="filter[time][from][year]" class="form-control" placeholder="year" min="<?= $Settings->get('startyear') ?>" max="<?= CURRENTYEAR ?>" step="1" id="from-year" onchange="filtertime()">
                <div class="input-group-prepend">
                    <span class="input-group-text"><?= lang('to', 'bis') ?></span>
                </div>
                <input type="number" name="filter[time][to][month]" class="form-control" placeholder="month" min="1" max="12" step="1" id="to-month" onchange="filtertime()">
                <input type="number" name="filter[time][to][year]" class="form-control" placeholder="year" min="<?= $Settings->get('startyear') ?>" max="<?= CURRENTYEAR ?>" step="1" id="to-year" onchange="filtertime()">

                <div class="input-group-append">
                    <button class="btn" type="button" onclick="filtertime(true)">&times;</button>
                </div>
            </div>
        </div>


        <!-- sort -->
        <div class="form-group">
            <label for="sort-by"><?= lang('Sort by', 'Sortieren nach') ?></label>
            <select name="sortby" id="sort-by" class="form-control w-auto">
                <option value="date_desc"><?= lang('Date (newest first)', 'Datum (neueste zuerst)') ?></option>
                <option value="date_asc"><?= lang('Date (oldest first)', 'Datum (älteste zuerst)') ?></option>
                <option value="type_asc"><?= lang('Type (A-Z)', 'Art (A-Z)') ?></option>
                <option value="type_desc"><?= lang('Type (Z-A)', 'Art (Z-A)') ?></option>
                <option value="title_asc"><?= lang('Title (A-Z)', 'Titel (A-Z)') ?></option>
                <option value="title_desc"><?= lang('Title (Z-A)', 'Titel (Z-A)') ?></option>
                <option value="print_asc"><?= lang('Formatted output (A-Z)', 'Formattierte Ausgabe (A-Z)') ?></option>
                <option value="print_desc"><?= lang('Formatted output (Z-A)', 'Formattierte Ausgabe (Z-A)') ?></option>
            </select>
        </div>


        <div class="form-group">

            <?= lang('Highlight:', 'Hervorheben:') ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                <label for="highlight-user"><?= lang('Me', 'Mich') ?></label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang(' Authors', '-Autoren') ?></label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-none" value="">
                <label for="highlight-none"><?= lang('None', 'Nichts') ?></label>
            </div>

        </div>


        <div class="form-group">

            <?= lang('File format:', 'Dateiformat:') ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-word" value="word" checked="checked">
                <label for="format-word">Word</label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-bibtex" value="bibtex">
                <label for="format-bibtex">BibTex</label>
            </div>

        </div>


        <button class="btn secondary">Download</button>

    </form>
</div>


<script>
    $(document).ready(function() {
        filtertime(true);
    });

    function filtertime(reset = false) {
        var today = new Date();
        if (reset) {
            $("#from-month").val('')
            $("#from-year").val('')
            $("#to-month").val('')
            $("#to-year").val('')
            // dataTable.columns(0).search("", true, false, true).draw();
            return
        }

        var fromMonth = $("#from-month").val()
        if (fromMonth.length == 0 || parseInt(fromMonth) < 1 || parseInt(fromMonth) > 12) {
            fromMonth = 1
        }
        var fromYear = $("#from-year").val()
        if (fromYear.length == 0 || parseInt(fromYear) < <?= $Settings->get('startyear') ?> || parseInt(fromYear) > today.getFullYear()) {
            fromYear = <?= $Settings->get('startyear') ?>
        }
        var toMonth = $("#to-month").val()
        if (toMonth.length == 0 || parseInt(toMonth) < 1 || parseInt(toMonth) > 12) {
            toMonth = 12
        }
        var toYear = $("#to-year").val()
        if (toYear.length == 0 || parseInt(toYear) < <?= $Settings->get('startyear') ?> || parseInt(toYear) > today.getFullYear()) {
            toYear = today.getFullYear()
        }
        // take care that from is not larger than to
        fromMonth = parseInt(fromMonth)
        fromYear = parseInt(fromYear)
        toMonth = parseInt(toMonth)
        toYear = parseInt(toYear)
        if (fromYear > toYear) {
            fromYear = toYear
        }
        if (fromYear == toYear && fromMonth > toMonth) {
            fromMonth = toMonth
        }

        $("#from-month").val(fromMonth)
        $("#from-year").val(fromYear)
        $("#to-month").val(toMonth)
        $("#to-year").val(toYear)

        // var range = dateRange(fromMonth, fromYear, toMonth, toYear)
        // console.log(range);
        // regExSearch = '(' + range.join('|') + ')';
        // dataTable.columns(0).search(regExSearch, true, false, true).draw();
        // table.column(columnNo).search(regExSearch, true, false).draw();
    }
</script>