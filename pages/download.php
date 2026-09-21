<?php

/**
 * Page to download activities
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /download
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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
            <?= lang('documents.export_activities') ?>
        </h1>

        <div class="form-group">
            <label for="filter-type"><?= lang('documents.filter_by_type') ?></label>
            <select name="filter[type]" id="filter-type" class="form-control">
                <option value=""><?= lang('documents.all_type_of_activities') ?></option>
                <?php foreach ($Settings->getActivities() as $a) { ?>
                    <option value="<?= $a['id'] ?>"><?= lang($a['name'], $a['name_de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>


        <?php if ($Settings->hasPermission('download.all', true)) { ?>

            <div class="row position-relative mb-20">
                <div class="col">
                    <div class="mr-20">

                        <label for="filter-user"><?= lang('documents.filter_by_user') ?></label>
                        <select name="filter[user]" id="filter-user" class="form-control">
                            <option value="">Alle Nutzer</option>
                            <option value="<?= $_SESSION['username'] ?>"><?= lang('documents.only_my_own_activities') ?></option>
                        </select>
                    </div>
                </div>

                <div class="text-divider"><?= lang('common.or') ?></div>

                <div class="col">
                    <div class="ml-20">
                        <label for="dept"><?= lang('common.department') ?></label>
                        <select name="filter[dept]" id="dept" class="form-control">
                            <option value=""><?= lang('documents.all_departments') ?></option>
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
                <?= lang('documents.you_can_only_download_your_own_activities') ?>
            </p>
        <?php } ?>

        <?php if ($Settings->featureEnabled('quality-workflow') && $Settings->hasPermission('workflows.view')) { ?>
            <div class="form-group">
                <label for="filter-workflow"><?= lang('documents.filter_by_workflow_status') ?></label>
                <select name="filter[workflow]" id="filter-workflow" class="form-control">
                    <option value=""><?= lang('documents.do_not_filter') ?></option>
                    <option value="in_progress"><?= lang('documents.in_progress') ?></option>
                    <option value="verified"><?= lang('common.completed') ?></option>
                    <option value="verified-or-empty"><?= lang('documents.completed_or_no_workflow') ?></option>
                    <option value="rejected"><?= lang('common.rejected') ?></option>
                </select>
            </div>
        <?php } ?>

        <?php if ($Settings->featureEnabled('topics')) {
            $topics = $osiris->topics->find();
            if (!empty($topics)) { ?>
                <div class="form-group">
                    <label for="filter-topic"><?= lang('documents.filter_by_topic') ?></label>
                    <select name="filter[topic]" id="filter-topic" class="form-control">
                        <option value=""><?= lang('documents.all_topics') ?></option>
                        <?php foreach ($topics as $topic) { ?>
                            <option value="<?= $topic['id'] ?>"><?= lang($topic['name'], $topic['name_de'] ?? null) ?></option>
                        <?php } ?>
                    </select>
                </div>
            <?php } ?>
        <?php } ?>


        <div class="form-group">
            <label for="filter-year"><?= lang('documents.filter_by_time_frame') ?></label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text"><?= lang('common.from') ?></span>
                </div>
                <input type="number" name="filter[time][from][month]" class="form-control" placeholder="month" min="1" max="12" step="1" id="from-month" onchange="filtertime()">
                <input type="number" name="filter[time][from][year]" class="form-control" placeholder="year" min="1900" max="<?= CURRENTYEAR+1 ?>" step="1" id="from-year" onchange="filtertime()">
                <div class="input-group-prepend">
                    <span class="input-group-text"><?= lang('common.to') ?></span>
                </div>
                <input type="number" name="filter[time][to][month]" class="form-control" placeholder="month" min="1" max="12" step="1" id="to-month" onchange="filtertime()">
                <input type="number" name="filter[time][to][year]" class="form-control" placeholder="year" min="1900" max="<?= CURRENTYEAR+1 ?>" step="1" id="to-year" onchange="filtertime()">

                <div class="input-group-append">
                    <button class="btn" type="button" onclick="filtertime(true)">&times;</button>
                </div>
            </div>
        </div>


        <!-- sort -->
        <div class="form-group">
            <label for="sort-by"><?= lang('common.sort_by') ?></label>
            <select name="sortby" id="sort-by" class="form-control w-auto">
                <option value="date_desc"><?= lang('documents.date_newest_first') ?></option>
                <option value="date_asc"><?= lang('documents.date_oldest_first') ?></option>
                <option value="type_asc"><?= lang('documents.type_a_z') ?></option>
                <option value="type_desc"><?= lang('documents.type_z_a') ?></option>
                <option value="title_asc"><?= lang('documents.title_a_z') ?></option>
                <option value="title_desc"><?= lang('documents.title_z_a') ?></option>
                <option value="print_asc"><?= lang('documents.formatted_output_a_z') ?></option>
                <option value="print_desc"><?= lang('documents.formatted_output_z_a') ?></option>
            </select>
        </div>


        <div class="form-group">

            <?= lang('common.highlight') ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                <label for="highlight-user"><?= lang('common.me') ?></label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang('common.authors') ?></label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="highlight" id="highlight-none" value="">
                <label for="highlight-none"><?= lang('common.none_download') ?></label>
            </div>

        </div>


        <div class="form-group">

            <?= lang('common.file_format') ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-word" value="word" checked="checked">
                <label for="format-word">Word</label>
            </div>
            
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-html" value="html" checked="checked">
                <label for="format-html">HTML</label>
            </div>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="format" id="format-bibtex" value="bibtex">
                <label for="format-bibtex">BibTeX</label>
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
        if (fromYear.length == 0 || parseInt(fromYear) < 1900 || parseInt(fromYear) > today.getFullYear()) {
            fromYear = 1900
        }
        var toMonth = $("#to-month").val()
        if (toMonth.length == 0 || parseInt(toMonth) < 1 || parseInt(toMonth) > 12) {
            toMonth = 12
        }
        var toYear = $("#to-year").val()
        if (toYear.length == 0 || parseInt(toYear) < 1900 || parseInt(toYear) > today.getFullYear()) {
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