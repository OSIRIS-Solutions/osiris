<?php

/**
 * Page to view a single topic of the research spectrum.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /spectrum/<type>/<id>
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$path = [];
$child = 'field';
$childIdField = 'field_id';
$childNameField = 'field';
if ($level == 'field' || $level == 'subfield' || $level == 'topic') {
    $path[] = '<a href="' . ROOTPATH . '/spectrum/domain/' . $spectrum['domain_id'] . '">' . $spectrum['domain'] . '</a>';
    $child = 'subfield';
    $childIdField = 'subfield_id';
    $childNameField = 'subfield';
}
if ($level == 'subfield' || $level == 'topic') {
    $path[] = '<a href="' . ROOTPATH . '/spectrum/field/' . $spectrum['field_id'] . '">' . $spectrum['field'] . '</a>';
    $child = 'topic';
    $childIdField = 'id';
    $childNameField = 'name';
}
if ($level == 'topic') {
    $path[] = '<a href="' . ROOTPATH . '/spectrum/subfield/' . $spectrum['subfield_id'] . '">' . $spectrum['subfield'] . '</a>';
    $child = null;
    $childIdField = null;
    $childNameField = null;
}
?>


<style>
    .spectrum-bar {
        display: grid;
        grid-template-columns: 2fr 4fr 60px;
        align-items: center;
        margin-bottom: 8px;
    }

    .spectrum-bar a {
        color: var(--primary-color);
    }

    .spectrum-bar .bar {
        background: #eee;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }

    .spectrum-bar .fill {
        background: var(--primary-color);
        height: 100%;
    }

    .spectrum-bar .count {
        font-size: 0.9em;
        color: #666;
        margin-left: 1rem;
    }

    #spectrum {
        --primary-color: var(--spectrum-<?= e($spectrum['domain_id']) ?>-color);
        --primary-color-dark: var(--spectrum-<?= e($spectrum['domain_id']) ?>-color);
        --primary-color-20: var(--spectrum-<?= e($spectrum['domain_id']) ?>-color-20);
        --primary-color-30: var(--spectrum-<?= e($spectrum['domain_id']) ?>-color-20);
    }
</style>
<div id="spectrum">
    <ul class="breadcrumb category" style="--highlight-color: var(--primary-color);">
        <?php foreach ($path as $p) { ?>
            <li><?= $p ?></li>
        <?php } ?>
    </ul>
    <h1 class="text-primary">
        <?= e($name) ?>
    </h1>

    <div class="spectrum-meta">

        <div class="stats">
            <div><strong><?= $totalPublications ?></strong> <?= lang('Publications', 'Publikationen') ?></div>
            <div><?= round($share * 100, 1) ?> % <?= lang('of institutional output', 'des Gesamtoutputs') ?></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-9">
            <!-- children -->
            <?php
            if ($childIdField && $childNameField) {
                $children = $osiris->activities->aggregate([
                    ['$match' => ['openalex.topics.' . $idField => $id]],
                    ['$unwind' => '$openalex.topics'],
                    ['$match' => ['openalex.topics.' . $idField => $id]],
                    ['$group' => [
                        '_id' => '$openalex.topics.' . $childIdField,
                        'name' => ['$first' => '$openalex.topics.' . $childNameField],
                        'count' => ['$sum' => 1]
                    ]],
                    ['$sort' => ['count' => -1]],
                    ['$limit' => 10]
                ])->toArray();
            ?>
                <h2 id="subtopics">
                    <?= lang('Subtopics', 'Unterthemen') ?>
                </h2>
                <div class="spectrum-chart box padded">
                    <?php
                    $max = 0;
                    $max = $children[0]['count'];
                    foreach ($children as $s):
                        $percent = round($s['count'] * 100 / $max, 1);
                        $name = $s['name'];
                    ?>
                        <div class="spectrum-bar">
                            <a href="<?= ROOTPATH ?>/spectrum/<?= $child ?>/<?= e($s['_id']) ?>"><?= e($name) ?></a>
                            <div class="bar">
                                <div class="fill" style="width: <?= $percent ?>%"></div>
                            </div>
                            <div class="count"><?= $s['count'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php } ?>



            <?php
            // Timeline data
            $timelineMatch = [
                'openalex.topics.' . $idField => $id,
                'affiliated' => true,
                // 'year' => ['$gte' => $Settings->get('startyear', 2000)]
            ];
            $timeline = $osiris->activities->aggregate([
                ['$match' => $timelineMatch],
                ['$group' => [
                    '_id' => '$year',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['_id' => 1]]
            ])->toArray();
            $timelineData = [];

            foreach ($timeline as $row) {
                if (!$row['_id']) continue;
                $timelineData[] = [
                    'year' => (int)$row['_id'],
                    'count' => (int)$row['count']
                ];
            }
            // fill in missing years
            $currentYear = (int)date('Y');
            $firstYear = $timelineData ? $timelineData[0]['year'] : $currentYear;
            for ($y = $firstYear; $y <= $currentYear; $y++) {
                if (!isset($timelineDataByYear[$y])) {
                    $timelineData[] = [
                        'year' => $y,
                        'count' => 0
                    ];
                }
            }
            // sort by year
            usort($timelineData, function ($a, $b) {
                return $a['year'] <=> $b['year'];
            });
            ?>

            <script>
                const timelineData = <?= json_encode($timelineData) ?>;
            </script>

            <h2 id="publication-timeline">
                <?= lang('Publication timeline', 'Publikationszeitstrahl') ?>
            </h2>
            <div class="box padded">
                <div id="timeline-chart" style="width:100%; height:320px;"></div>
            </div>

            <script src="<?= ROOTPATH ?>/js/d3.v7.min.js"></script>
            <script>
                (function() {

                    if (!timelineData || timelineData.length === 0) return;

                    const container = d3.select("#timeline-chart");
                    const width = container.node().clientWidth;
                    const height = 320;
                    const margin = {
                        top: 20,
                        right: 20,
                        bottom: 40,
                        left: 50
                    };

                    const svg = container.append("svg")
                        .attr("width", width)
                        .attr("height", height);

                    const chartWidth = width - margin.left - margin.right;
                    const chartHeight = height - margin.top - margin.bottom;

                    const chart = svg.append("g")
                        .attr("transform", `translate(${margin.left},${margin.top})`);

                    const x = d3.scaleBand()
                        .domain(timelineData.map(d => d.year))
                        .range([0, chartWidth])
                        .padding(0.2);

                    const y = d3.scaleLinear()
                        .domain([0, d3.max(timelineData, d => d.count)])
                        .nice()
                        .range([chartHeight, 0]);

                    // X axis
                    chart.append("g")
                        .attr("transform", `translate(0,${chartHeight})`)
                        .call(d3.axisBottom(x).tickFormat(d3.format("d")))
                        .selectAll("text")
                        .style("font-size", "12px");

                    // Y axis
                    chart.append("g")
                        .call(d3.axisLeft(y).ticks(5))
                        .selectAll("text")
                        .style("font-size", "12px");

                    const domainColor = getComputedStyle(document.documentElement)
                        .getPropertyValue('--spectrum-<?= e($spectrum['domain_id']) ?>-color');

                    // Tooltip
                    const tooltip = d3.select("body")
                        .append("div")
                        .attr("class", "timeline-tooltip")
                        .style("position", "absolute")
                        .style("background", "#fff")
                        .style("padding", "6px 10px")
                        .style("border", "1px solid #ddd")
                        .style("border-radius", "4px")
                        .style("font-size", "12px")
                        .style("pointer-events", "none")
                        .style("opacity", 0);

                    chart.selectAll(".bar")
                        .data(timelineData)
                        .enter()
                        .append("rect")
                        .attr("class", "bar")
                        .attr("x", d => x(d.year))
                        .attr("y", d => y(d.count))
                        .attr("width", x.bandwidth())
                        .attr("height", d => chartHeight - y(d.count))
                        .attr("fill", domainColor.trim())
                        .on("mouseover", function(event, d) {
                            tooltip.transition().duration(150).style("opacity", 1);
                            tooltip.html(
                                    `<strong>${d.year}</strong><br>${d.count} publications`
                                )
                                .style("left", (event.pageX - 40) + "px")
                                .style("top", (event.pageY - 50) + "px");
                        })
                        .on("mouseout", function() {
                            tooltip.transition().duration(150).style("opacity", 0);
                        });

                })();
            </script>



            <?php
            $dept_ids = array_keys($Departments);
            $units = $osiris->activities->aggregate([
                ['$match' => [
                    'openalex.topics.' . $idField => $id,
                    'units' => ['$in' => $dept_ids]
                ]],
                ['$unwind' => '$units'],
                ['$match' => ['units' => ['$in' => $dept_ids]]],
                ['$group' => [
                    '_id' => '$units',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]],
                ['$limit' => 10]
            ])->toArray();
            foreach ($units as &$u) {
                $group = $osiris->groups->findOne(
                    ['id' => ($u['_id'])],
                    ['projection' => ['name' => 1]]
                );

                $u['name'] = $group['name'] ?? 'Unknown';
            }
            unset($u);
            ?>

            <h2 id="organizational-units"><?= lang('Organizational units', 'Organisationseinheiten') ?></h2>


            <div class="spectrum-chart box padded">
                <?php
                $max = 0;
                foreach ($units as $s) {
                    if ($s['count'] > $max) $max = $s['count'];
                }
                foreach ($units as $s):
                    $percent = round($s['count'] * 100 / $max, 1);
                    $name = $s['name'];
                ?>
                    <div class="spectrum-bar">
                        <span><?= e($name) ?></span>
                        <div class="bar">
                            <div class="fill" style="width: <?= $percent ?>%"></div>
                        </div>
                        <div class="count"><?= $s['count'] ?></div>
                    </div>
                <?php endforeach; ?>
            </div>



            <?php
            $researchers = $osiris->activities->aggregate([
                ['$match' => [
                    'openalex.topics.' . $idField => $id,
                    'type' => 'publication',
                    // 'affiliated' => true
                ]],
                ['$unwind' => '$rendered.users'],
                ['$group' => [
                    '_id' => '$rendered.users',
                    'count' => ['$sum' => 1]
                ]],
                ['$sort' => ['count' => -1]],
                // lookup user names and is_active status
                [
                    '$lookup' => [
                        'from' => 'persons',
                        'localField' => '_id',
                        'foreignField' => 'username',
                        'as' => 'user'
                    ]
                ],
                ['$unwind' => '$user'],
                ['$match' => ['user.is_active' => ['$ne' => false]]],
                ['$limit' => 10],
                ['$sort' => ['_id' => 1]] // sort alphabetically by name
            ])->toArray();
            ?>
            <h2 id="top-researchers"><?= lang('Researchers', 'Forschende') ?></h2>

            <p>
                <?= lang(
                    'This section shows researchers who have publications in OSIRIS that are associated with this topic. For better overview, only a selection is shown.',
                    'Hier werden Forschende angezeigt, die in OSIRIS Publikationen haben, die diesem Schwerpunkt zugeordnet sind. Zur besseren Übersicht wird nur eine Auswahl angezeigt.'
                ) ?>
            </p>

            <p class="font-size-16">
                <?php foreach ($researchers as $r): ?>
                    <a class="badge primary mr-5 mb-5" href="<?= ROOTPATH ?>/profile/<?= e($r['_id']) ?>">
                        <?= e($r['user']['displayname'] ?? $r['user']['username'] ?? $r['user']) ?>
                    </a>
                <?php endforeach; ?>
            </p>

            <p class="font-size-12 text-muted">
                <?= lang(
                    'Only up to ten researchers are shown. The list is alphabetically ordered. If a person does not appear in the list does not mean that they cannot have contributed to the topic. This list is for orientation purposes only.',
                    'Diese Liste dient nur zur Orientierung. Es werden nur bis zu zehn Forschende angezeigt und die Liste ist alphabetisch sortiert. Wenn eine Person nicht in der Liste erscheint, bedeutet das nicht, dass sie nicht zum Thema beigetragen haben kann. '
                ) ?>
            </p>



            <h2 id="related-publications">
                <?= lang('Related publications', 'Zugehörige Publikationen') ?>
            </h2>
            <div class="mt-20 w-full">
                <table class="table dataTable responsive" id="publications-table">
                    <thead>
                        <tr>
                            <th><?= lang('Type', 'Typ') ?></th>
                            <th><?= lang('Publication', 'Publikation') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <script>
                initActivities('#publications-table', {
                    filter: {
                        type: 'publication',
                        'openalex.topics.<?= $idField ?>': '<?= $id ?>'
                    },
                });
            </script>

        </div>
        <div class="col-md-3 d-none d-md-block">
            <nav class="on-this-page-nav">
                <div class="content">
                    <div class="title">
                        <?= lang('On this page', 'Auf dieser Seite') ?>
                    </div>

                    <a href="#spectrum"><?= lang('Overview', 'Übersicht') ?></a>
                    <?php if ($childIdField && $childNameField) { ?>
                        <a href="#subtopics"><?= lang('Subtopics', 'Unterthemen') ?></a>
                    <?php } ?>
                    <a href="#publication-timeline"><?= lang('Publication timeline', 'Publikationszeitstrahl') ?></a>
                    <a href="#organizational-units"><?= lang('Organizational units', 'Organisationseinheiten') ?></a>
                    <a href="#top-researchers"><?= lang('Top researchers', 'Beteiligte Forschende') ?></a>
                    <a href="#related-publications"><?= lang('Related publications', 'Zugehörige Publikationen') ?></a>
                </div>
            </nav>

        </div>


    </div>

</div>