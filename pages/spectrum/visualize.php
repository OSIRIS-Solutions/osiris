<?php

/**
 * Page to visualize the research spectrum of the institution as an interactive sunburst chart.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /spectrum/visualize
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>
<script src="<?= ROOTPATH ?>/js/d3.v7.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>

<?php

$yearFrom = isset($_GET['year_from']) && is_numeric($_GET['year_from']) ? (int)$_GET['year_from'] : null;
$yearTo   = isset($_GET['year_to']) && is_numeric($_GET['year_to']) ? (int)$_GET['year_to'] : null;

$match = [
    'type' => 'publication',
    'openalex.topics' => ['$exists' => true, '$ne' => []]
];

if ($yearFrom !== null || $yearTo !== null) {
    $match['year'] = [];
    if ($yearFrom !== null) $match['year']['$gte'] = $yearFrom;
    if ($yearTo !== null)   $match['year']['$lte'] = $yearTo;
}

$rows = $osiris->activities->aggregate([
    ['$match' => $match],
    ['$unwind' => '$openalex.topics'],
    ['$group' => [
        '_id' => [
            'domain_id' => '$openalex.topics.domain_id',
            'domain' => '$openalex.topics.domain',
            'field_id' => '$openalex.topics.field_id',
            'field' => '$openalex.topics.field',
            'subfield_id' => '$openalex.topics.subfield_id',
            'subfield' => '$openalex.topics.subfield',
            'topic_id' => '$openalex.topics.id',
            'topic' => '$openalex.topics.name'
        ],
        'count' => ['$sum' => 1]
    ]],
    ['$match' => ['count' => ['$gt' => 0]]],
    ['$sort' => ['count' => -1]]
])->toArray();

/**
 * Build hierarchical tree for D3 sunburst.
 * English comments as requested.
 */
$tree = [
    'name' => 'Research Spectrum',
    'children' => []
];

foreach ($rows as $row) {
    $r = $row['_id'];
    $count = (int)$row['count'];

    $domainId = (string)($r['domain_id'] ?? 'unknown');
    $domainName = (string)($r['domain'] ?? 'Unknown domain');
    $fieldId = (string)($r['field_id'] ?? 'unknown');
    $fieldName = (string)($r['field'] ?? 'Unknown field');
    $subfieldId = (string)($r['subfield_id'] ?? 'unknown');
    $subfieldName = (string)($r['subfield'] ?? 'Unknown subfield');
    $topicId = (string)($r['topic_id'] ?? 'unknown');
    $topicName = (string)($r['topic'] ?? 'Unknown topic');

    if (!isset($tree['children'][$domainId])) {
        $tree['children'][$domainId] = [
            'id' => $domainId,
            'name' => $domainName,
            'level' => 'domain',
            'children' => []
        ];
    }

    if (!isset($tree['children'][$domainId]['children'][$fieldId])) {
        $tree['children'][$domainId]['children'][$fieldId] = [
            'id' => $fieldId,
            'name' => $fieldName,
            'level' => 'field',
            'domain_id' => $domainId,
            'children' => []
        ];
    }

    if (!isset($tree['children'][$domainId]['children'][$fieldId]['children'][$subfieldId])) {
        $tree['children'][$domainId]['children'][$fieldId]['children'][$subfieldId] = [
            'id' => $subfieldId,
            'name' => $subfieldName,
            'level' => 'subfield',
            'domain_id' => $domainId,
            'children' => []
        ];
    }

    $tree['children'][$domainId]['children'][$fieldId]['children'][$subfieldId]['children'][$topicId] = [
        'id' => $topicId,
        'name' => $topicName,
        'level' => 'topic',
        'domain_id' => $domainId,
        'value' => $count,
        'url' => ROOTPATH . '/spectrum/topic/' . rawurlencode($topicId)
    ];
}

/**
 * Convert associative child maps to indexed arrays for JSON output.
 */
$tree['children'] = array_values(array_map(function ($domain) {
    $domain['children'] = array_values(array_map(function ($field) {
        $field['children'] = array_values(array_map(function ($subfield) {
            $subfield['children'] = array_values($subfield['children']);
            return $subfield;
        }, $field['children']));
        return $field;
    }, $domain['children']));
    return $domain;
}, $tree['children']));

$totalPublications = 0;
foreach ($rows as $row) {
    $totalPublications += (int)$row['count'];
}
?>

<h1>
    <i class="ph-duotone ph-chart-donut" aria-hidden="true"></i>
    <?= lang('Spectrum Visualization', 'Spektrum-Visualisierung') ?>
</h1>

<p class="text-muted">
    <?= lang(
        'Interactive sunburst visualization of the thematic structure based on OpenAlex topics.',
        'Interaktive Sunburst-Visualisierung der thematischen Struktur auf Basis der OpenAlex-Themen.'
    ) ?>
</p>
<style>
    .funnel {
        height: 2.6rem;
        display: block;
        color: var(--blue-color);
    }
</style>
<form method="get" class="box mb-4">
    <div class="px-20">
        <div class="row row-eq-spacing align-items-end">
            <div class="col flex-grow-0">
                <span class="funnel"><i class="ph ph-funnel" aria-hidden="true"></i></span>
            </div>
            <div class="col">
                <label class="form-label"><?= lang('From year', 'Von Jahr') ?></label>
                <input type="number" class="form-control" name="year_from" value="<?= e($yearFrom) ?>">
            </div>
            <div class="col">
                <label class="form-label"><?= lang('To year', 'Bis Jahr') ?></label>
                <input type="number" class="form-control" name="year_to" value="<?= e($yearTo) ?>">
            </div>
            <div class="col flex-grow-0">
                <button class="btn primary block"><?= lang('Apply filter', 'Filter anwenden') ?></button>
            </div>
        </div>
    </div>
</form>

<p class="text-muted mb-0">
    <?= lang(
        'The visualization is based on the current filter selection. Click segments to zoom. Topic segments link to the detailed spectrum page. Clicking the center brings you back to the next higher level.',
        'Die Visualisierung basiert auf der aktuellen Filterauswahl. Segmente können angeklickt werden, um hinein zu zoomen. Themen-Segmente verlinken auf die jeweilige Detailseite. In der Mitte zu klicken bringt dich zurück zur nächst-höheren Ebene.'
    ) ?>
</p>

<div id="sunburst-chart"></div>
<p class="text-muted mb-0">
    <?= lang('Total topic assignments', 'Gesamte Themenzuordnungen') ?>:
    <strong><?= number_format($totalPublications, 0, ',', '.') ?></strong>
</p>

<style>
    #sunburst-chart {
        width: 100%;
        min-height: 760px;
    }

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

<script>
    const spectrumTree = <?= json_encode($tree, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    (function() {
        if (!spectrumTree || !spectrumTree.children || spectrumTree.children.length === 0) return;

        const container = document.getElementById('sunburst-chart');
        const width = container.clientWidth || 760;
        const size = Math.min(width, 760);
        const radius = size / 8 - 6;

        const domainColors = {
            "1": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-1-color').trim() || '#2E7D5B',
            "2": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-2-color').trim() || '#4C5C8A',
            "3": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-3-color').trim() || '#2F5D8A',
            "4": getComputedStyle(document.documentElement).getPropertyValue('--spectrum-4-color').trim() || '#1F7A8C'
        };

        const root = d3.hierarchy(spectrumTree)
            .sum(d => d.value || 0)
            .sort((a, b) => b.value - a.value);

        d3.partition()
            .size([2 * Math.PI, root.height + 1])(root);

        root.each(d => d.current = d);

        const arc = d3.arc()
            .startAngle(d => d.x0)
            .endAngle(d => d.x1)
            .padAngle(d => Math.min((d.x1 - d.x0) / 2, 0.005))
            .padRadius(radius * 1.5)
            .innerRadius(d => d.y0 * radius)
            .outerRadius(d => Math.max(d.y0 * radius, d.y1 * radius - 1));

        const svg = d3.select(container)
            .append("svg")
            .attr("viewBox", [-size / 2, -size / 2, size, size])
            .style("width", "100%")
            .style("height", "auto")
            .style("font", "14px sans-serif");

        const tooltip = d3.select("body")
            .append("div")
            .attr("class", "sunburst-tooltip")
            .style("opacity", 0);

        function getNodeColor(d) {
            const domainId = d.data.domain_id || d.data.id || null;
            return domainColors[domainId] || '#999';
        }

        const path = svg.append("g")
            .selectAll("path")
            .data(root.descendants().slice(1))
            .join("path")
            .attr("fill", d => getNodeColor(d))
            .attr("fill-opacity", d => arcVisible(d.current) ? (d.children ? 0.75 : 0.55) : 0)
            .attr("pointer-events", d => arcVisible(d.current) ? "auto" : "none")
            .attr("d", d => arc(d.current))
            .style("cursor", d => d.data.level === 'topic' ? 'pointer' : 'zoom-in')
            .on("click", clicked)
            .on("mousemove", function(event, d) {
                const pct = ((d.value / root.value) * 100).toFixed(1);
                tooltip
                    .style("opacity", 1)
                    .html(`
                    <strong>${d.data.name}</strong><br>
                    ${d.data.level ? '<span class="text-muted">' + d.data.level + '</span><br>' : ''}
                    <?= lang('Assignments', 'Zuordnungen') ?>: ${d.value}<br>
                    <?= lang('Share', 'Anteil') ?>: ${pct} %
                `)
                    .style("left", (event.pageX + 12) + "px")
                    .style("top", (event.pageY + 12) + "px");

            })
            .on("mouseleave", function() {
                tooltip.style("opacity", 0);
            });

        path.filter(d => d.children)
            .style("cursor", "pointer");

        const label = svg.append("g")
            .attr("pointer-events", "none")
            .attr("text-anchor", "middle")
            .selectAll("text")
            .data(root.descendants().slice(1))
            .join("text")
            .attr("class", "sunburst-label")
            .attr("dy", "0.35em")
            .attr("fill-opacity", d => +labelVisible(d.current))
            .attr("transform", d => labelTransform(d.current))
            .style("font-size", "10px")
            .style("fill", "#222")
            .style('pointer-events', 'none')
            .text(d => shortenLabel(d.data.name, 13));

        const parent = svg.append("circle")
            .datum(root)
            .attr("r", radius)
            .attr("fill", "none")
            .attr("pointer-events", "all")
            .on("click", clicked);

        svg.append("text")
            .attr("class", "sunburst-center-label")
            .attr("y", -8)
            .text("OSIRIS")
            .style("font-size", "15px")
            .style("font-weight", "600")
            .style("fill", '#333')
            .style("text-anchor", "middle");

        svg.append("text")
            .attr("class", "sunburst-center-label")
            .attr("y", 14)
            .style("font-size", "12px")
            .style("font-weight", "400")
            .text("Spectrum")
            .style("fill", '#333')
            .style("text-anchor", "middle");

        function clicked(event, p) {
            if (p.data.level === 'topic' && p.data.url) {
                window.location.href = p.data.url;
                return;
            }

            parent.datum(p.parent || root);

            root.each(d => d.target = {
                x0: Math.max(0, Math.min(1, (d.x0 - p.x0) / (p.x1 - p.x0))) * 2 * Math.PI,
                x1: Math.max(0, Math.min(1, (d.x1 - p.x0) / (p.x1 - p.x0))) * 2 * Math.PI,
                y0: Math.max(0, d.y0 - p.depth),
                y1: Math.max(0, d.y1 - p.depth)
            });

            const t = svg.transition().duration(750);

            path.transition(t)
                .tween("data", d => {
                    const i = d3.interpolate(d.current, d.target);
                    return t => d.current = i(t);
                })
                .filter(function(d) {
                    return +this.getAttribute("fill-opacity") || arcVisible(d.target);
                })
                .attr("fill-opacity", d => arcVisible(d.target) ? (d.children ? 0.75 : 0.55) : 0)
                .attr("pointer-events", d => arcVisible(d.target) ? "auto" : "none")
                .attrTween("d", d => () => arc(d.current));

            label.filter(function(d) {
                    return +this.getAttribute("fill-opacity") || labelVisible(d.target);
                })
                .transition(t)
                .attr("fill-opacity", d => +labelVisible(d.target))
                .attrTween("transform", d => () => labelTransform(d.current));

        }

        function arcVisible(d) {
            return d.y1 <= 4 && d.y0 >= 1 && d.x1 > d.x0;
        }

        function labelVisible(d) {
            return d.y1 <= 4 && d.y0 >= 1 && (d.y1 - d.y0) * (d.x1 - d.x0) > 0.045;
        }

        function labelTransform(d) {
            const x = (d.x0 + d.x1) / 2 * 180 / Math.PI;
            const y = (d.y0 + d.y1) / 2 * radius;
            return `rotate(${x - 90}) translate(${y},0) rotate(${x < 180 ? 0 : 180})`;
        }

        function shortenLabel(text, max = 18) {
            if (!text) return "";
            if (text.length <= max) return text;
            return text.slice(0, max - 1) + "…";
        }

        let svgNode = svg.node();
        let divSelector = "#sunburst-chart";
        registerDownloadHandlers(svgNode, divSelector);
    })();
</script>