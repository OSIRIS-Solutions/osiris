<?php

/**
 * Page to edit units a user is assigned to.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /user/units/<username>
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <small class="font-weight-normal"><?= lang('Organisational units of', 'Organisationseinheiten von') ?></small>
    <br>
    <div class="text-primary"><?= $data['displayname'] ?></div>
</h1>

<?php
$units = DB::doc2Arr($data['units'] ?? []);
?>

<table class="table w-auto my-20">
    <thead>
        <tr>
            <th>
                <?= lang('Unit', 'Einheit') ?>
            </th>
            <th>
                <?= lang('Start', 'Start') ?>
            </th>
            <th>
                <?= lang('End', 'Ende') ?>
            </th>
            <th>
                <?= lang('Scientific', 'Wissenschaftlich') ?>
            </th>
            <th class="text-center">
                <?= lang('Actions', 'Aktionen') ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($units as $unit) {
            $name = $Groups->getName($unit['unit']);
            // add name to unit
            $unit['name'] = $name;
            // get parents
            $parents = $Groups->getParents($unit['unit']);
            // remove last parent, which is the unit itself
            array_pop($parents);

            // check if unit is still active
            $past = false;
            if (isset($unit['end']) && strtotime($unit['end']) < time()) {
                $past = true;
            }
        ?>
            <tr data-id="<?= $unit['id'] ?>" class="<?= $past ? 'text-muted' : '' ?>">
                <td>
                    <?php
                    if (count($parents) > 0) {
                        echo '<small class="text-muted d-block">';
                        foreach ($parents as $parent) {
                            echo $Groups->getName($parent);
                            if ($parent != end($parents)) {
                                echo ' > ';
                            }
                        }
                        echo '</small> > ';
                    }
                    echo '<strong>' . $name . '</strong>';
                    ?>
                    <br>
                </td>
                <td><?php
                    if (isset($unit['start'])) {
                        echo format_date($unit['start']);
                    } else {
                        echo '<em class="text-danger">' . lang('unknown', 'unbekannt') . '</em>';
                    }
                    ?>
                </td>
                <td><?php
                    if (isset($unit['end'])) {
                        echo format_date($unit['end']);
                    } else {
                        echo '<b class="text-success">' . lang('current', 'laufend') . '</b>';
                    }
                    ?>
                </td>
                <td>
                    <?php if ($unit['scientific']) { ?>
                        <span class="badge primary">
                            <i class="ph ph-lightning"></i>
                            <?= lang('yes', 'ja') ?>
                        </span>
                    <?php } else { ?>
                        <span class="badge secondary">
                            <i class="ph ph-lightning-slash"></i>
                            <?= lang('no', 'nein') ?>
                        </span>
                    <?php } ?>
                </td>
                <td>
                    <div class="dropdown">
                        <button class="btn link" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-edit"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right w-200" aria-labelledby="dropdown-1">
                            <form action="<?= ROOTPATH ?>/crud/users/units/<?= $user ?>" method="POST" class="content">
                                <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/user/units/<?= $user ?>">
                                <input type="hidden" name="values[unit]" value="<?= $unit['unit'] ?>">

                                <div class="form-group">
                                    <label for="start"><?= lang('Start', 'Start') ?></label>
                                    <input type="date" class="form-control" id="start" name="values[start]" value="<?= $unit['start'] ?? '' ?>">
                                </div>
                                <div class="form-group">
                                    <label for="end"><?= lang('End', 'Ende') ?></label>
                                    <input type="date" class="form-control" id="end" name="values[end]" value="<?= $unit['end'] ?? '' ?>">
                                    <small class="text-muted">
                                        <?= lang('Leave empty if still active', 'Leer lassen, wenn noch aktiv') ?>
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label for="scientific"><?= lang('Scientific', 'Wissenschaftlich') ?></label>
                                    <select class="form-control" id="scientific" name="values[scientific]">
                                        <option value="1" <?= $unit['scientific'] ? 'selected' : '' ?>><?= lang('yes', 'ja') ?></option>
                                        <option value="0" <?= !$unit['scientific'] ? 'selected' : '' ?>><?= lang('no', 'nein') ?></option>
                                    </select>
                                </div>
                                <button class="btn block primary" type="submit"><?= lang('Save', 'Speichern') ?></button>
                            </form>
                        </div>
                    </div>

                    <div class="dropdown">
                        <button class="btn link text-danger" data-toggle="dropdown" type="button" id="remove-unit-<?= $unit['id'] ?>" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-trash"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right w-400 border-danger" aria-labelledby="remove-unit-<?= $unit['id'] ?>">
                            <form action="<?= ROOTPATH ?>/crud/users/units/<?= $user ?>" method="POST" class="content">
                                <i class="ph ph-warning text-danger"></i>
                                <?= lang('You are in the process of deleting the unit from the person profile. If the unit has changed, it can often be better to end the period instead in order to continue to show former participation.', 'Du bist dabei die Einheit aus dem Personenprofil zu löschen. Wenn sich die Einheit geändert hat, kann es oftmals besser sein stattdessen den Zeitraum zu beenden, um ehemalige Beteiligung weiterhin abzubilden.') ?>
                                <input type="hidden" name="id" value="<?= $unit['id'] ?>">
                                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/user/units/<?= $user ?>">
                                <button class="btn block danger" type="submit"><i class="ph ph-trash"></i> <?= lang('Remove unit', 'Einheit löschen') ?></button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">
                <a href="#add-unit" class="btn primary">
                    <i class="ph ph-plus"></i>
                    <?= lang('Add unit', 'Einheit hinzufügen') ?>
                </a>
            </td>
        </tr>
    </tfoot>
</table>

<!-- explain scientific -->
<p class="font-size-12">
    <i class="ph ph-lightning text-primary"></i>
    <?= lang('Scientific units will be added to all research activities that have happened within the time of affiliation.', 'Wissenschaftliche Einheiten werden zu allen Forschungsaktivitäten hinzugefügt, die während der Zeit der Zugehörigkeit stattgefunden haben.') ?>
</p>


<?php
$tree = [$Groups->tree];

function printTree($tree, $level = 0)
{
    foreach ($tree as $d) {
        $name = $d['name'];
        $id = $d['id'];
        echo '<option value="' . $id . '">' . str_repeat('—', $level) . ' ' . $name . '</option>';
        if (is_array($d['children'])) {
            printTree($d['children'], $level + 1);
        }
    }
}
?>


<div class="modal" id="add-unit" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <?= lang('Add new unit', 'Einheit hinzufügen') ?>
            </h5>
            <p>
                <?= lang('Please select the unit you want to add.', 'Bitte wählen Sie die Einheit aus, die Sie hinzufügen möchten.') ?>
            </p>
            <form action="<?= ROOTPATH ?>/crud/users/units/<?= $user ?>" method="POST" class="content">
                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/user/units/<?= $user ?>">
                <div class="form-group">
                    <label for="unit"><?= lang('Unit', 'Einheit') ?></label>
                    <select class="form-control" id="unit" name="values[unit]">
                        <?php printTree($tree) ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="start"><?= lang('Start', 'Start') ?></label>
                    <input type="date" class="form-control" id="start" name="values[start]">
                </div>
                <div class="form-group">
                    <label for="end"><?= lang('End', 'Ende') ?></label>
                    <input type="date" class="form-control" id="end" name="values[end]">
                    <small class="text-muted">
                        <?= lang('Leave empty if still active', 'Leer lassen, wenn noch aktiv') ?>
                    </small>
                </div>
                <div class="form-group">
                    <label for="scientific"><?= lang('Scientific', 'Wissenschaftlich') ?></label>
                    <select class="form-control" id="scientific" name="values[scientific]">
                        <option value="1"><?= lang('yes', 'ja') ?></option>
                        <option value="0"><?= lang('no', 'nein') ?></option>
                    </select>
                </div>
                <button class="btn primary" type="submit"><?= lang('Save', 'Speichern') ?></button>
            </form>
        </div>
    </div>
</div>

<style>
    .bar {
        fill: var(--secondary-color-60);
    }

    .bar-active {
        fill: var(--secondary-color);
    }

    .bar-scientific {
        fill: var(--primary-color-60);
    }

    .bar-scientific.bar-active {
        fill: var(--primary-color);
    }


    .axis text {
        font-size: 12px;
    }

    .axis line,
    .axis path {
        stroke: #333;
    }
</style>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<div class="box" id="unitTimeline"></div>
<script>
    // Deine Daten
    const units = <?= json_encode($units) ?>;

    // Parsing der Datumswerte
    const parseDate = d3.timeParse("%Y-%m-%d");
    const today = new Date();
    units.forEach(d => {
        d.start = parseDate(d.start);
        d.end = d.end ? parseDate(d.end) : today;
    });

    const divSelector = '#unitTimeline';
    const barWidth = 60;
    // SVG-Größe
    const margin = {
        top: 20,
        right: 30,
        bottom: 30,
        left: 100
    };
    const width = 800 - margin.left - margin.right;
    const height = (units.length * barWidth) - margin.top - margin.bottom;

    var svg = d3.select(divSelector).append('svg')
        .attr("viewBox", `0 0 ${width+margin.left + margin.right} ${height + margin.top + margin.bottom}`)
        .append("g")
        .attr("transform", `translate(${margin.left},${margin.top})`);

    // Skalen definieren
    const xScale = d3.scaleTime()
        .domain([d3.min(units, d => d.start), d3.max(units, d => d.end)])
        .range([0, width]);

    const yScale = d3.scaleBand()
        .domain(units.map(d => d.unit))
        .range([0, height])
        .padding(0.2);

    // Achsen hinzufügen
    const xAxis = d3.axisBottom(xScale).ticks(d3.timeYear.every(1)).tickFormat(d3.timeFormat("%Y"));
    const yAxis = d3.axisLeft(yScale);

    svg.append("g")
        .attr("transform", `translate(0, ${height})`)
        .call(xAxis);

    svg.append("g")
        .call(yAxis);


    var Tooltip = d3.select(divSelector)
        .append("div")
        .style("opacity", 0)
        .attr("class", "tooltip")
        .style("background-color", "white")
        .style("border", "solid")
        .style("border-width", "2px")
        .style("border-radius", "5px")
        .style("padding", "5px")


    function format_date(date) {
        return d3.timeFormat("%d.%m.%Y")(date);
    }

    function mouseover(d, i) {
        //Define and show the tooltip over the mouse location
        $(this).popover({
            placement: 'auto top',
            container: divSelector,
            mouseOffset: 10,
            followMouse: true,
            trigger: 'hover',
            html: true,
            content: function() {
                var icon = '<i class="ph ph-lightning-slash text-secondary"></i>';
                if (d.scientific) {
                    icon = '<i class="ph ph-lightning text-primary"></i>';
                }
                return `<b>${d.name ?? d.unit}</b> ${icon} 
                <br>
                ${format_date(d.start)} - ${format_date(d.end)}`;
            }
        });
        $(this).popover('show');
    } //mouseoverChord

    function mouseout(event, d) {
        //Hide the tooltip
        $('.popover').each(function() {
            $(this).remove();
        });
    }

    // Balken zeichnen
    svg.selectAll(".bar")
        .data(units)
        .enter()
        .append("rect")
        .attr("class", d => `bar ${d.scientific ? "bar-scientific" : ""}`)
        .attr("x", d => xScale(d.start))
        .attr("y", d => yScale(d.unit))
        .attr("width", d => xScale(d.end) - xScale(d.start))
        .attr("height", yScale.bandwidth())
        .on("mouseover", mouseover)
        // .on("mousemove", mousemove)
        .on("mouseout", mouseout)
        .on("click", (d) => {
            // $('tr.active').removeClass('active')
            var element = document.getElementById("tr-" + d.id);
            // element.className="active"
            var headerOffset = 60;
            var elementPosition = element.getBoundingClientRect().top;
            var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: "smooth"
            });
        });
</script>