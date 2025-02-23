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

?>

<h1>
    <i class="ph ph-graph" aria-hidden="true"></i>
    <?= lang('Department network', 'Abteilungs-Netzwerk') ?>
</h1>
<p class="text-muted">
    <?= lang('Based on publications within the past 5 years.', 'Basierend auf Publikationen aus den vergangenen 5 Jahren.') ?>
</p>

<?php if (!empty($warnings)) { ?>
    <div class="alert signal">
        <?= implode(' ', $warnings) ?>
    </div>
<?php } ?>


<div id="chart" class="d-flex " style="max-width: 80rem"></div>

<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?=CSS_JS_VERSION?>"></script>

<script>
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/department-network",
        data: {
            level: <?= $_GET['level'] ?? 1 ?>,
            type: '<?= $_GET['type'] ?? 'publication' ?>',
            dept: '<?= $_GET['dept'] ?? null ?>',
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
                labels.push(element.id);
                colors.push(element.color)
            });


            Chords('#chart', matrix, labels, colors, data, links = false, useGradient = true, highlightFirst = false, type = '<?= $_GET['type'] ?? 'publication' ?>');
        },
        error: function(response) {
            console.log(response);
        }
    });
</script>