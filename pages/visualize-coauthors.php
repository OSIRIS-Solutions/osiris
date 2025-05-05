<?php

/**
 * Page to visualize coauthor network
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /visualize/coauthors
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$users = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]);

$scientist = $_GET['scientist'] ?? $_SESSION['username'];
$selectedUser = $osiris->persons->findone(['username' => $scientist]);
?>


<h1>
    <i class="ph ph-graph" aria-hidden="true"></i>
    <?= lang('Coauthor network', 'Koautoren-Netzwerk') ?>
</h1>


<form action="" method="get" class="w-400 mw-full">
    <div class="input-group">
        <select name="scientist" id="scientist-select" class="form-control">
            <?php foreach ($users as $u) { ?>
                <option value="<?= $u['username'] ?>" <?= $u['username'] == $scientist ? 'selected' : '' ?>><?= $u['formalname'] ?></option>
            <?php } ?>
        </select>
        <div class="input-group-append">
            <button class="btn secondary" type="submit">Select</button>
        </div>
    </div>
</form>

<a class="link" href="<?= ROOTPATH ?>/profile/<?= $scientist ?>"><i class="ph ph-student"></i>
    <?= lang('View scientist page of ', 'Zeige die Übersichtsseite von ') ?>
    <?= $selectedUser['displayname'] ?>
</a>

<div class="row">
    <div class="col-md-8" style="max-width: 80rem">
        <div id="chord"></div>
    </div>
    <div class="col-md-4">
        <div id="legend"></div>
    </div>
</div>

<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?=CSS_JS_VERSION?>"></script>

<script>
    const USER = '<?= $scientist ?>'

    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/author-network",
        data: {
            user: USER
        },
        dataType: "json",
        success: function(response) {
            var matrix = response.data.matrix;
            var DEPTS = response.data.labels;

            var data = Object.values(DEPTS);
            var labels = data.map(item => item['name']);

            var colors = []
            var links = []
            var depts_in_use = {};

            data.forEach(function(d, i) {
                colors.push(d.dept.color ?? '#cccccc');
                var link = null
                if (i !== 0) link = "?scientist=" + d.user
                links.push(link)

                if (d.dept.id && depts_in_use[d.dept.id] === undefined)
                    depts_in_use[d.dept.id] = d.dept;
            })

            Chords('#chord', matrix, labels, colors, data, links, false, DEPTS[USER]['index']);


            var legend = d3.select('#legend')
                .append('div').attr('class', 'content')

            legend.append('div')
                .style('font-weight', 'bold')
                .attr('class', 'mb-5')
                .text(lang("Departments", "Abteilungen"))

            for (const dept in depts_in_use) {
                if (Object.hasOwnProperty.call(depts_in_use, dept)) {
                    const d = depts_in_use[dept];
                    var row = legend.append('div')
                        .attr('class', 'd-flex mb-5')
                        .style('color', d.color)
                    row.append('div')
                        .style('background-color', d.color)
                        .style("width", "2rem")
                        .style("height", "2rem")
                        .style("border-radius", ".5rem")
                        .style("display", "inline-block")
                        .style("margin-right", "1rem")
                    row.append('span').text(d.name)
                }
            }

        },
        error: function(response) {
            console.log(response);
        }
    });
</script>