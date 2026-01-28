<?php

/**
 * Page to visualize department network
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /visualize/departments
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$year = $_GET['year'] ?? CURRENTYEAR - 4;
$entity = $_GET['entity'] ?? 'units';
$level = $_GET['level'] ?? 1;
$type = $_GET['type'] ?? 'publication';
?>
<style>
    .popover h6 {
        margin-top: 0;
        margin-bottom: 5px;
    }
</style>
<h1>
    <i class="ph-duotone ph-graph" aria-hidden="true"></i>
    <?= lang('Activity network', 'Aktivitäten-Netzwerk') ?>
</h1>
<div class="row">
    <div class="col-md-3">

        <div class="pills">
            <button id="btn-units" class="btn <?= ($entity == 'units') ? 'active' : '' ?>" onclick="showForm('units')">
                <?= lang('Units', 'Einheiten') ?>
            </button>
            <?php if ($Settings->featureEnabled('topics')) { ?>
                <button id="btn-topics" class="btn <?= ($entity == 'topics') ? 'active' : '' ?>" onclick="showForm('topics')">
                    <?= lang('Topics', 'Themen') ?>
                </button>
            <?php } ?>
        </div>

        <form action="#" method="get" class="box padded" id="form-units" <?= $entity == 'units' ? '' : 'style="display:none;"' ?>>
            <input type="hidden" name="entity" value="units">
            <div class="form-group">
                <label for="level"><?= lang('Organizational level', 'Organisationsebene') ?></label>
                <select id="level" name="level" class="form-control">
                    <option value="1" <?= ($level == 1) ? 'selected' : '' ?>><?= lang('Level 1', 'Ebene 1') ?></option>
                    <option value="2" <?= ($level == 2) ? 'selected' : '' ?>><?= lang('Level 2', 'Ebene 2') ?></option>
                </select>
            </div>
            <div class="form-group">
                <label for="type"><?= lang('Activity type', 'Aktivitätstyp') ?></label>
                <select id="type" name="type" class="form-control">
                    <?php
                    foreach ($Categories->categories as $key) { ?>
                        <option value="<?= $key['id'] ?>" <?= ($type == $key['id']) ? 'selected' : '' ?>><?= lang($key['name'], $key['name_de'] ?? null) ?></option>
                    <?php }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="year"><?= lang('Starting year', 'Startjahr') ?></label>
                <select id="year" name="year" class="form-control">
                    <?php
                    $currentYear = (int)date('Y');
                    for ($i = $currentYear; $i >= $currentYear - 10; $i--) { ?>
                        <option value="<?= $i ?>" <?= ($year == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn primary">
                <?= lang('Update', 'Aktualisieren') ?>
            </button>
        </form>


        <form action="#" method="get" id="form-topics" class="box padded" <?= $entity == 'topics' ? '' : 'style="display:none;"' ?>>
            <input type="hidden" name="entity" value="topics">
            <div class="form-group">
                <label for="type"><?= lang('Activity type', 'Aktivitätstyp') ?></label>
                <select id="type" name="type" class="form-control">
                    <?php
                    foreach ($Categories->categories as $key) { ?>
                        <option value="<?= $key['id'] ?>" <?= ($type == $key['id']) ? 'selected' : '' ?>><?= lang($key['name'], $key['name_de'] ?? null) ?></option>
                    <?php }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="year"><?= lang('Starting year', 'Startjahr') ?></label>
                <select id="year" name="year" class="form-control">
                    <?php
                    $currentYear = (int)date('Y');
                    for ($i = $currentYear; $i >= $currentYear - 10; $i--) { ?>
                        <option value="<?= $i ?>" <?= ($year == $i) ? 'selected' : '' ?>><?= $i ?></option>
                    <?php }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn primary">
                <?= lang('Update', 'Aktualisieren') ?>
            </button>
        </form>

    </div>
    <div class="col-md-9">
        <div id="chart" class="d-flex " style="max-width: 100%"></div>
    </div>
</div>


<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?= OSIRIS_BUILD ?>"></script>

<script>
    function showForm(entity) {

        if (entity === 'units') {
            $('#form-units').show();
            $('#form-topics').hide();
            $('#btn-units').addClass('active');
            $('#btn-topics').removeClass('active');
        } else {
            $('#form-units').hide();
            $('#form-topics').show();
            $('#btn-units').removeClass('active');
            $('#btn-topics').addClass('active');
        }
    }
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/department-network",
        data: {

            level: <?= $level ?>,
            type: '<?= $type ?>',
            entity: '<?= $entity ?>',
            year: <?= $year ?>
        },
        dataType: "json",
        success: function(response) {
            console.log(response);
            var matrix = response.data.matrix;
            var data = response.data.labels;

            var labels = [];
            var colors = [];
            data = Object.values(data)
            data.forEach(element => {
                labels.push(lang(element.name, element.name_de ?? null));
                colors.push(element.color)
            });

            Chords('#chart', matrix, labels, colors, data, links = false, useGradient = true, highlightFirst = false, type = '<?= $type ?>');

            // register download button
            var svgNode = d3.select('#chart').select('svg').node();
            registerDownloadHandlers(svgNode, '#chart');
        },
        error: function(response) {
            console.log(response);
        }
    });
</script>