<?php

/**
 * Teaching statistics page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Document = new Document();

$selectedSemester = $_GET['semester'] ?? 'SoSe2025'; // default

// Parse Semester → Start- & Enddatum
preg_match('/(SoSe|WiSe)(\d{4})/', $selectedSemester, $matches);
$semesterType = $matches[1] ?? 'SoSe';
$semesterYear = intval($matches[2]) ?? date('Y');

if ($semesterType === 'SoSe') {
    $start = "$semesterYear-04-01";
    $end   = "$semesterYear-09-30";
} else {
    $start = "$semesterYear-10-01";
    $end   = ($semesterYear + 1) . "-03-31";
}

// Filter Lehrveranstaltungen im Semester
$filter = [
    'module_id'       => ['$exists' => true],
    'authors.sws' => ['$exists' => true],
    'start_date' => ['$lte' => $end],
    'end_date'   => ['$gte' => $start]
];

$teaching = $osiris->activities->find($filter)->toArray();

$all = $osiris->activities->count(
    [
        'module_id' => ['$exists' => true],
        'authors.sws' => ['$exists' => true]
    ]
);

?>


<style>
    tfoot th {
        border-top: 1px solid var(--border-color);
        color: var(--muted-color);
        background-color: var(--gray-color-very-light);
    }

    tfoot th:first-child {
        border-bottom-left-radius: var(--border-radius);
    }

    tfoot th:last-child {
        border-bottom-right-radius: var(--border-radius);
    }
</style>


<h1>
    <i class="ph-duotone ph-chart-line-up"></i>
    <?= lang("Teaching statistics", "Statistiken zu Lehrveranstaltungen") ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/teaching">
        <i class="ph ph-arrow-left"></i>
        <?= lang('Back to Teaching modules', 'Zurück zu Lehrveranstaltungen') ?>
    </a>
</div>


<!-- UI-Änderung -->
<div class="alert signal">
    <i class="ph ph-warning text-signal"></i>
    <?= lang('All of the following statistics are based on the selected semester.', 'Alle unten aufgeführten Statistiken basieren auf dem ausgewählten Semester.') ?>


    <form method="get" class="d-flex align-items-baseline mt-10" style="grid-gap: 1rem;">
        <h6 class="mb-0 mt-5"><?= lang('Select semester', 'Semester auswählen') ?>:</h6>
        <select name="semester" class="form-control w-auto">
            <?php foreach (['SoSe2024', 'WiSe2024', 'SoSe2025'] as $s): ?>
                <option value="<?= $s ?>" <?= $s === $selectedSemester ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach ?>
        </select>
        <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
    </form>
</div>

<div id="gantt-container" style="width: 100%; height: auto;">
    <svg id="gantt-chart" viewBox="0 0 1000 500" preserveAspectRatio="xMinYMin meet" style="width: 100%; height: auto;"></svg>
</div>

<div id="statistics">
    <p class="lead">
        <?= lang('Number of courses in the selected semester', 'Anzahl der Lehrveranstaltungen im gewählten Semester') ?>:
        <b class="badge signal"><?= count($teaching) ?></b>
        <span class="text-muted">(<?= $all ?> <?= lang('total', 'gesamt') ?>)</span>
    </p>


    <h2>
        <i class="ph ph-table"></i>
        <?= lang('Teaching modules', 'Lehrveranstaltungen') ?>
    </h2>

    <table class="table">
        <thead>
            <tr>
                <th>Modul</th>
                <th><?= lang('Type', 'Art') ?></th>
                <th><?= lang('Start date', 'Beginn') ?></th>
                <th><?= lang('End date', 'Ende') ?></th>
                <th><?= lang('Affiliated', 'Affiliiert') ?></th>
                <th><?= lang('SWS (total)', 'SWS (gesamt)') ?></th>
                <th><?= lang('SWS', 'SWS') ?> (<?= $Settings->get('affiliation') ?>)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $counts = [
                'total' => 0,
                'affiliation' => 0
            ];
            foreach ($teaching as $t):
                $authors = DB::doc2Arr($t['authors']);
                $total = 0;
                $affiliation = 0;
                $affilated = false;
                foreach ($authors as $a) {
                    $aoi = $a['aoi'] ?? null;
                    if ($aoi === 'true' || $aoi === true || $aoi == "1") {
                        $affilated = true;
                    }
                    if (isset($a['sws']) && !empty($a['sws'])) {
                        $total += $a['sws'];
                        if ($affilated) {
                            $affiliation += floatval($a['sws']);
                        }
                    }
                }
                $counts['total'] += $total;
                $counts['affiliation'] += $affiliation;
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/activities/view/<?= $t['_id'] ?>">
                            <?= $t['module'] ?>
                        </a>
                    </td>
                    <td><?= $Document->translateCategory($t['category'] ?? '-') ?></td>
                    <td><?= date('d.m.Y', strtotime($t['start_date'])) ?></td>
                    <td><?= date('d.m.Y', strtotime($t['end_date'])) ?></td>
                    <td>
                        <?php if ($affilated): ?>
                            <i class="ph ph-check-circle text-primary"></i>
                        <?php else: ?>
                            <i class="ph ph-x-circle text-secondary"></i>
                        <?php endif ?>
                    <td><?= $total ?></td>
                    <td class="text-weight-bold"><?= $affiliation ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-end"><?= lang('Total', 'Gesamt') ?>:</th>
                <th><?= $counts['total'] ?></th>
                <th><?= $counts['affiliation'] ?></th>
            </tr>
    </table>

</div>


<?php
$timelineData = array_map(function ($t) use ($Document) {
    $authors = DB::doc2Arr($t['authors']);
    return [
        'title' => $t['module'] ?? '',
        'name' => $t['title'] ?? '',
        'start' => $t['start_date'],
        'end'   => $t['end_date'],
        'hasAoi' => array_filter($authors, fn($a) => !empty($a['aoi'])) ? true : false,
        'cat' => $Document->translateCategory($t['category'] ?? '-'),
    ];
}, $teaching);
usort($timelineData, function ($a, $b) {
    // sort by start and end date
    $startA = strtotime($a['start']);
    $startB = strtotime($b['start']);
    if ($startA === $startB) {
        return strtotime($a['end']) - strtotime($b['end']);
    }
    return $startA - $startB;
});

$uniques = array_unique(array_column($timelineData, 'title'));
$unique_number = count($uniques);

?>

<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>

<script>
    const data = <?= json_encode($timelineData) ?>;
    const unique_number = <?= $unique_number ?? 1 ?>;
    const divSelector = '#gantt-container';

    const margin = {
        top: 40,
        right: 10,
        bottom: 40,
        left: 100
    };
    const width = 1000;
    const height = (20 * unique_number) + margin.top + margin.bottom;

    const container = d3.select(divSelector);
    const svg = d3.select('#gantt-chart');

    svg.attr('width', width)
        .attr('height', height)
        .attr('viewBox', `0 0 ${width} ${height}`)


    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;

    const x = d3.scaleTime()
        .domain([new Date("<?= $start ?>"), new Date("<?= $end ?>")])
        .range([margin.left, innerWidth + margin.left]);

    const y = d3.scaleBand()
        .domain(data.map(d => d.title))
        .range([margin.top, innerHeight + margin.top])
        .padding(0.1);

    // Achsen
    svg.append('g')
        .attr('transform', `translate(0,${innerHeight + margin.top})`)
        .call(d3.axisBottom(x));

    svg.append('g')
        .attr('transform', `translate(${margin.left},0)`)
        .call(d3.axisLeft(y));

    // Balken
    const rect = svg.selectAll('rect')
        .data(data)
        .enter()
        .append('rect')
        .attr('x', d => x(new Date(d.start)))
        .attr('y', d => y(d.title))
        .attr('rx', 5)
        .attr('ry', 5)
        .attr('width', d => x(new Date(d.end)) - x(new Date(d.start)))
        .attr('height', y.bandwidth())
        .attr('fill', d => d.hasAoi ? 'var(--primary-color)' : 'var(--secondary-color)')
        .attr('opacity', 0.6)

    // Circles for data point less than 1 week
    const circle = svg.selectAll('circle')
        .data(data.filter(d => (new Date(d.end) - new Date(d.start)) < 7 * 24 * 60 * 60 * 1000))
        .enter()
        .append('circle')
        .attr('cx', d => x(new Date(d.start)) + (x(new Date(d.end)) - x(new Date(d.start))) / 2)
        .attr('cy', d => y(d.title) + y.bandwidth() / 2)
        .attr('r', 5)
        .attr('fill', d => d.hasAoi ? 'var(--primary-color)' : 'var(--secondary-color)')
        .attr('opacity', 0.6)
    // Tooltip


    function mouseover(d, i) {
        d3.select(this)
            .select('circle,rect')
            .transition()
            .duration(300)
            .style('opacity', 1)

        //Define and show the tooltip over the mouse location
        $(this).popover({
            placement: 'auto top',
            container: divSelector,
            mouseOffset: 10,
            followMouse: true,
            trigger: 'hover',
            html: true,
            content: function() {
                return `<b>${d.title ?? 'No title available'}:</b> <b class="text-primary">${d.cat}</b><br>
                ${d.name ?? 'No name available'}<br>
                <b>${lang('Start date', 'Beginn')}: </b>${d.start}<br>
                <b>${lang('End date', 'Ende')}: </b>${d.end}<br>
                <b>${lang('Affiliated', 'Affiliiert')}: </b>
                ${d.hasAoi ? '<i class="ph ph-check-circle text-primary"></i>' : '<i class="ph ph-x-circle text-secondary"></i>'}
                `
            }
        });
        $(this).popover('show');
    } //mouseoverChord

    //Bring all chords back to default opacity
    function mouseout(event, d) {
        d3.select(this).select('circle,rect')
            .transition()
            .duration(300)
            .style('opacity', .5)
        //Hide the tooltip
        $('.popover').each(function() {
            $(this).remove();
        });
    }
    circle.on("mouseover", mouseover)
        .on("mouseout", mouseout)
    rect.on("mouseover", mouseover)
        .on("mouseout", mouseout)
</script>