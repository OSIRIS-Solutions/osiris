<?php

/**
 * Page to see the evolution of the research spectrum of the institution over time.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /spectrum/evolution
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>
<style>
    .sunburst-tooltip {
        position: absolute;
        z-index: 1000;
        pointer-events: none;
        background: #fff;
        border: 1px solid var(--border-color, #ddd);
        border-radius: var(--border-radius, 0.5rem);
        padding: 0.75rem 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
        max-width: 320px;
        font-size: 1.2rem;
    }
</style>
<script src="<?= ROOTPATH ?>/js/d3.v7.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>

<?php

$level = $_GET['level'] ?? 'domain';
$mode = $_GET['mode'] ?? 'relative';

$allowedLevels = ['domain', 'field', 'subfield', 'topic'];
if (!in_array($level, $allowedLevels, true)) $level = 'domain';

$allowedModes = ['absolute', 'relative'];
if (!in_array($mode, $allowedModes, true)) $mode = 'relative';

$currentYear = (int)date('Y');
$yearFrom = isset($_GET['year_from']) && is_numeric($_GET['year_from']) ? (int)$_GET['year_from'] : $currentYear - 11;
$yearTo   = isset($_GET['year_to']) && is_numeric($_GET['year_to']) ? (int)$_GET['year_to'] : $currentYear - 1;

if ($yearFrom > $yearTo) {
    [$yearFrom, $yearTo] = [$yearTo, $yearFrom];
}

switch ($level) {
    case 'domain':
        $idField = '$openalex.topics.domain_id';
        $nameField = '$openalex.topics.domain';
        break;
    case 'field':
        $idField = '$openalex.topics.field_id';
        $nameField = '$openalex.topics.field';
        break;
    case 'subfield':
        $idField = '$openalex.topics.subfield_id';
        $nameField = '$openalex.topics.subfield';
        break;
    default:
        $idField = '$openalex.topics.id';
        $nameField = '$openalex.topics.name';
        break;
}

$match = [
    'type' => 'publication',
    'year' => ['$gte' => $yearFrom, '$lte' => $yearTo],
    'openalex.topics' => ['$exists' => true, '$ne' => []]
];

$rows = $osiris->activities->aggregate([
    ['$match' => $match],
    ['$unwind' => '$openalex.topics'],
    ['$group' => [
        '_id' => [
            'year' => '$year',
            'id' => $idField,
            'name' => $nameField,
            'domain_id' => '$openalex.topics.domain_id'
        ],
        'count' => ['$sum' => 1]
    ]],
    ['$sort' => ['_id.year' => 1, 'count' => -1]]
])->toArray();

/**
 * Build year-wise series.
 */
$years = range($yearFrom, $yearTo);
$seriesMap = [];
$yearTotals = array_fill_keys($years, 0);

foreach ($rows as $row) {
    $year = (int)$row['_id']['year'];
    $id = (string)($row['_id']['id'] ?? 'unknown');
    $name = (string)($row['_id']['name'] ?? 'Unknown');
    $domainId = (string)($row['_id']['domain_id'] ?? '0');
    $count = (int)$row['count'];

    if (!isset($seriesMap[$id])) {
        $seriesMap[$id] = [
            'id' => $id,
            'name' => $name,
            'domain_id' => $domainId,
            'values' => array_fill_keys($years, 0)
        ];
    }

    $seriesMap[$id]['values'][$year] = $count;
    $yearTotals[$year] += $count;
}

$series = array_values($seriesMap);

// Transform into chart rows: one row per year
$chartData = [];
foreach ($years as $year) {
    $row = ['year' => $year];
    foreach ($series as $s) {
        $value = $s['values'][$year] ?? 0;
        if ($mode === 'relative') {
            $total = $yearTotals[$year] ?? 0;
            $value = $total > 0 ? $value / $total : 0;
        }
        $row[$s['id']] = $value;
    }
    $chartData[] = $row;
}

// Build trend table
$trendTable = [];
foreach ($series as $s) {
    $first = $s['values'][$yearFrom] ?? 0;
    $last = $s['values'][$yearTo] ?? 0;

    $sum = array_sum($s['values']);
    if ($sum === 0) continue;

    $delta = $last - $first;
    $deltaPct = $first > 0 ? (($last - $first) / $first) * 100 : null;

    $trendTable[] = [
        'id' => $s['id'],
        'name' => $s['name'],
        'domain_id' => $s['domain_id'],
        'total' => $sum,
        'start' => $first,
        'end' => $last,
        'delta' => $delta,
        'delta_pct' => $deltaPct
    ];
}

// Sort by total volume for now
usort($trendTable, function ($a, $b) {
    return $b['total'] <=> $a['total'];
});

// Limit visual clutter
$maxSeries = 20;
$topIds = array_column(array_slice($trendTable, 0, $maxSeries), 'id');

$chartSeries = array_values(array_filter($series, function ($s) use ($topIds) {
    return in_array($s['id'], $topIds, true);
}));

$chartDataFiltered = [];
foreach ($chartData as $row) {
    $newRow = ['year' => $row['year']];
    foreach ($chartSeries as $s) {
        $newRow[$s['id']] = $row[$s['id']] ?? 0;
    }
    $chartDataFiltered[] = $newRow;
}
?>

<h1>
    <i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('Spectrum Evolution', 'Entwicklung des Forschungs-Spektrums') ?>
</h1>

<p class="text-muted">
    <?= lang(
        'Shows how the thematic structure of publications changes over time. Only the 20 most common topics are shown.',
        'Zeigt, wie sich die thematische Struktur der Publikationen im Zeitverlauf verändert. Es werden nur die 20 häufigsten Themen gezeigt.'
    ) ?>
</p>

<form method="get" class="box mb-4">
    <div class="px-20">
        <div class="row row-eq-spacing align-items-end">
            <div class="col-md-3">
                <label class="form-label"><?= lang('Level', 'Ebene') ?></label>
                <select class="form-control" name="level">
                    <option value="domain" <?= $level === 'domain' ? 'selected' : '' ?>>Domain</option>
                    <option value="field" <?= $level === 'field' ? 'selected' : '' ?>>Field</option>
                    <option value="subfield" <?= $level === 'subfield' ? 'selected' : '' ?>>Subfield</option>
                    <option value="topic" <?= $level === 'topic' ? 'selected' : '' ?>>Topic</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label"><?= lang('Mode', 'Modus') ?></label>
                <select class="form-control" name="mode">
                    <option value="relative" <?= $mode === 'relative' ? 'selected' : '' ?>><?= lang('Relative share', 'Relativer Anteil') ?></option>
                    <option value="absolute" <?= $mode === 'absolute' ? 'selected' : '' ?>><?= lang('Absolute counts', 'Absolute Anzahl') ?></option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label"><?= lang('From year', 'Von Jahr') ?></label>
                <input type="number" class="form-control" name="year_from" value="<?= e($yearFrom) ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label"><?= lang('To year', 'Bis Jahr') ?></label>
                <input type="number" class="form-control" name="year_to" value="<?= e($yearTo) ?>">
            </div>

            <div class="col-md-2">
                <button class="btn primary block"><?= lang('Apply filter', 'Filter anwenden') ?></button>
            </div>
        </div>
    </div>
</form>



<div class="box mb-4">
    <div class="content">
        <div id="spectrum-evolution-chart"></div>

        <small class="text-muted">
            <?= lang(
                'In relative mode, the chart shows the share of each element in the annual research spectrum.
In absolute mode, it shows the number of thematic assignments per year.
Since OpenAlex assigns up to three topics per publication, these are topic assignments and not unique publication counts.',
                'Im relativen Modus zeigt die Grafik den Anteil eines Elements am jährlichen Forschungs-Spektrum.
Im absoluten Modus zeigt sie die Anzahl der thematischen Zuordnungen pro Jahr.
Da OpenAlex pro Publikation bis zu drei Themen zuordnet, handelt es sich um Themenzuordnungen und nicht um eindeutige Publikationszahlen.
'
            ) ?>
        </small>
    </div>
</div>


<div class="box mb-4">
    <div class="content">
        <h3><?= lang('Heatmap', 'Heatmap') ?></h3>
        <div id="spectrum-evolution-heatmap"></div>
    </div>
</div>



<h3><?= lang('Trend overview', 'Trendübersicht') ?></h3>
<small class="text-muted">
    <?= lang(
        'The table summarizes the change between the first and last year of the selected period.',
        'Die Tabelle fasst die Veränderung zwischen dem ersten und letzten Jahr des gewählten Zeitraums zusammen.'
    ) ?>
</small>

<table class="table dataTable" id="spectrum-trend-table">
    <thead>
        <tr>
            <th><?= lang('Element', 'Element') ?></th>
            <th><?= lang('Start', 'Start') ?></th>
            <th><?= lang('End', 'Ende') ?></th>
            <th><?= lang('Change', 'Veränderung') ?></th>
            <th><?= lang('Total', 'Gesamt') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($trendTable as $row): ?>
            <tr>
                <td><?= e($row['name']) ?></td>
                <td><?= $row['start'] ?></td>
                <td><?= $row['end'] ?></td>
                <td>
                    <?= $row['delta'] > 0 ? '+' : '' ?><?= $row['delta'] ?>
                    <?php if ($row['delta_pct'] !== null): ?>
                        <span class="text-muted">
                            (<?= $row['delta_pct'] > 0 ? '+' : '' ?><?= round($row['delta_pct'], 1) ?> %)
                        </span>
                    <?php endif; ?>
                </td>
                <td><?= $row['total'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
    const spectrumEvolutionData = <?= json_encode($chartDataFiltered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const spectrumEvolutionSeries = <?= json_encode($chartSeries, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const spectrumEvolutionMode = <?= json_encode($mode) ?>;

    const domainColors = {
        "1": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-1-color').trim() || '#2E7D5B',
        "2": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-2-color').trim() || '#4C5C8A',
        "3": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-3-color').trim() || '#2F5D8A',
        "4": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-4-color').trim() || '#1F7A8C',
        "0": '#999999'
    };

    (function() {
        if (!spectrumEvolutionData || !spectrumEvolutionData.length || !spectrumEvolutionSeries.length) return;

        const container = d3.select("#spectrum-evolution-chart");
        const width = container.node().clientWidth || 1000;
        const height = 520;
        const margin = {
            top: 20,
            right: 180,
            bottom: 40,
            left: 60
        };

        const svg = container.append("svg")
            .attr("width", width)
            .attr("height", height);

        const chartWidth = width - margin.left - margin.right;
        const chartHeight = height - margin.top - margin.bottom;

        const g = svg.append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);

        const keys = spectrumEvolutionSeries.map(d => d.id);

        const metaById = {};
        spectrumEvolutionSeries.forEach(s => {
            metaById[s.id] = s;
        });

        const stacked = d3.stack().keys(keys)(spectrumEvolutionData);

        const x = d3.scaleLinear()
            .domain(d3.extent(spectrumEvolutionData, d => d.year))
            .range([0, chartWidth]);

        const y = d3.scaleLinear()
            .domain([0, d3.max(stacked[stacked.length - 1], d => d[1])])
            .nice()
            .range([chartHeight, 0]);

        const area = d3.area()
            .x(d => x(d.data.year))
            .y0(d => y(d[0]))
            .y1(d => y(d[1]))
            .curve(d3.curveMonotoneX);

        g.append("g")
            .attr("transform", `translate(0,${chartHeight})`)
            .call(d3.axisBottom(x).tickFormat(d3.format("d")));

        g.append("g")
            .call(d3.axisLeft(y).ticks(6).tickFormat(d => {
                if (spectrumEvolutionMode === 'relative') return Math.round(d * 100) + '%';
                return d;
            }));

        const tooltip = d3.select("body")
            .append("div")
            .attr("class", "sunburst-tooltip")
            .style("opacity", 0);

        g.selectAll(".layer")
            .data(stacked)
            .enter()
            .append("path")
            .attr("class", "layer")
            .attr("fill", d => {
                const domainId = metaById[d.key]?.domain_id || "0";
                return domainColors[domainId] || '#999999';
            })
            .attr("fill-opacity", 0.8)
            .attr("d", area)
            .on("mousemove", function(event, d) {
                //change opacity of current hovered layer
                d3.select(this).attr("fill-opacity", 1);
                const name = metaById[d.key]?.name || d.key;
                tooltip
                    .style("opacity", 1)
                    .html(`<strong>${name}</strong>`)
                    .style("left", (event.pageX + 12) + "px")
                    .style("top", (event.pageY + 12) + "px");
            })
            .on("mouseleave", function() {
                tooltip.style("opacity", 0);
                d3.select(this).attr("fill-opacity", 0.8);
            });

        // Legend
        const legend = svg.append("g")
            .attr("transform", `translate(${width - margin.right + 20}, ${margin.top})`);

        spectrumEvolutionSeries.forEach((s, i) => {
            const row = legend.append("g")
                .attr("transform", `translate(0, ${i * 22})`);

            row.append("rect")
                .attr("width", 14)
                .attr("height", 14)
                .attr("rx", 3)
                .attr("fill", domainColors[s.domain_id] || '#999999');

            row.append("text")
                .attr("x", 22)
                .attr("y", 11)
                .style("font-size", "12px")
                .text(shortenLabel(s.name, 26));
        });

        function shortenLabel(text, max = 24) {
            if (!text) return '';
            if (text.length <= max) return text;
            return text.slice(0, max - 1) + '…';
        }
    })();


    (function() {
        if (!spectrumEvolutionData || !spectrumEvolutionData.length || !spectrumEvolutionSeries.length) return;

        const container = d3.select("#spectrum-evolution-heatmap");
        const width = container.node().clientWidth || 1100;

        const rowHeight = 28;
        const margin = {
            top: 50,
            right: 20,
            bottom: 40,
            left: 200
        };
        const years = spectrumEvolutionData.map(d => d.year);
        const series = spectrumEvolutionSeries;

        const height = margin.top + margin.bottom + rowHeight * series.length;

        const svg = container.append("svg")
            .attr("width", width)
            .attr("height", height);

        const chartWidth = width - margin.left - margin.right;
        const chartHeight = height - margin.top - margin.bottom;

        const g = svg.append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);

        // Flatten chart data into heatmap cells
        const cells = [];
        series.forEach(s => {
            spectrumEvolutionData.forEach(row => {
                cells.push({
                    id: s.id,
                    name: s.name,
                    domain_id: s.domain_id,
                    year: row.year,
                    value: row[s.id] || 0
                });
            });
        });

        // Sort rows by total volume descending
        const totals = {};
        series.forEach(s => {
            totals[s.id] = d3.sum(spectrumEvolutionData, d => d[s.id] || 0);
        });

        const sortedSeries = [...series].sort((a, b) => totals[b.id] - totals[a.id]);

        const x = d3.scaleBand()
            .domain(years)
            .range([0, chartWidth])
            .padding(0.04);

        const y = d3.scaleBand()
            .domain(sortedSeries.map(d => d.id))
            .range([0, chartHeight])
            .padding(0.08);

        const maxValue = d3.max(cells, d => d.value) || 1;

        const color = d3.scaleSequential()
            .domain([0, maxValue])
            .interpolator(d3.interpolateYlGnBu);

        // Axes
        g.append("g")
            .attr("transform", `translate(0,${chartHeight})`)
            .call(d3.axisBottom(x).tickFormat(d3.format("d")));

        g.append("g")
            .attr("class", "y-axis")
            .call(
                d3.axisLeft(y)
                .tickFormat(id => {
                    const s = sortedSeries.find(x => x.id === id);
                    return shortenLabel(s ? s.name : id, 30);
                })
            );
        g.selectAll(".tick text")
            .style("text-anchor", "end")
            .attr("dx", "-1.3em");
        // Tooltip
        const tooltip = d3.select("body")
            .append("div")
            .attr("class", "sunburst-tooltip")
            .style("opacity", 0);

        g.selectAll("rect.heat-cell")
            .data(cells)
            .enter()
            .append("rect")
            .attr("class", "heat-cell")
            .attr("x", d => x(d.year))
            .attr("y", d => y(d.id))
            .attr("rx", 4)
            .attr("ry", 4)
            .attr("width", x.bandwidth())
            .attr("height", y.bandwidth())
            .attr("fill", d => color(d.value))
            .on("mousemove", function(event, d) {
                const valueText = spectrumEvolutionMode === 'relative' ?
                    `${(d.value * 100).toFixed(1)} %` :
                    d.value;

                tooltip
                    .style("opacity", 1)
                    .html(`
                    <strong>${d.name}</strong><br>
                    ${d.year}<br>
                    <?= lang('Value', 'Wert') ?>: ${valueText}
                `)
                    .style("left", (event.pageX + 12) + "px")
                    .style("top", (event.pageY + 12) + "px");
            })
            .on("mouseleave", function() {
                tooltip.style("opacity", 0);
            });

        // Optional row labels with domain-colored marker
        const labelLayer = svg.append("g")
            .attr("transform", `translate(0,${margin.top})`);


        labelLayer.selectAll("rect.row-marker")
            .data(sortedSeries)
            .enter()
            .append("rect")
            .attr("x", margin.left - 12)
            .attr("y", d => y(d.id) + 5)
            .attr("width", 6)
            .attr("height", Math.max(12, y.bandwidth() - 10))
            .attr("rx", 3)
            .attr("fill", d => domainColors[d.domain_id] || '#999999');

        // Color legend
        const legendWidth = 220;
        const legendHeight = 12;

        const defs = svg.append("defs");
        const gradient = defs.append("linearGradient")
            .attr("id", "heatmap-gradient")
            .attr("x1", "0%")
            .attr("x2", "100%");

        d3.range(0, 1.01, 0.1).forEach(t => {
            gradient.append("stop")
                .attr("offset", `${t * 100}%`)
                .attr("stop-color", color(t * maxValue));
        });

        const legend = svg.append("g")
            .attr("transform", `translate(${width - legendWidth - 20}, 10)`);

        legend.append("rect")
            .attr("width", legendWidth)
            .attr("height", legendHeight)
            .attr("rx", 6)
            .attr("fill", "url(#heatmap-gradient)");

        const legendScale = d3.scaleLinear()
            .domain([0, maxValue])
            .range([0, legendWidth]);

        legend.append("g")
            .attr("transform", `translate(0,${legendHeight})`)
            .call(
                d3.axisBottom(legendScale)
                .ticks(4)
                .tickFormat(d => spectrumEvolutionMode === 'relative' ?
                    `${Math.round(d * 100)}%` :
                    d
                )
            );

        function shortenLabel(text, max = 30) {
            if (!text) return '';
            if (text.length <= max) return text;
            return text.slice(0, max - 1) + '…';
        }
    })();
</script>



<script>
    $(document).ready(function() {
        $('#spectrum-trend-table').DataTable({
            order: [
                [4, 'desc']
            ]
        });
    });
</script>