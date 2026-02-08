<?php
if (!$Settings->hasPermission('proposals.finance')) {
    echo lang('You do not have permission to view financial statistics.', 'Sie haben keine Berechtigung, Finanzstatistiken einzusehen.');
    exit;
}

function fmt_eur($v)
{
    return number_format((float)$v, 0, ',', '.');
}
function fmt_pct($v)
{
    if ($v === null) return '—';
    return number_format($v * 100, 1, ',', '.') . ' %';
}
?>


<h1>
    <?= lang('Finance', 'Finanzen') ?>
</h1>

<?php
$fundingTotals = $osiris->proposals->aggregate([
    ['$match' => [
        'status' => 'approved'
    ]],

    ['$project' => [
        '_id' => 1,
        'name' => 1,
        'grant_income_proposed' => ['$ifNull' => ['$grant_income_proposed', 0]],
        'grant_income' => ['$ifNull' => ['$grant_income', 0]],
        'grant_sum_proposed' => ['$ifNull' => ['$grant_sum_proposed', 0]],
        'grant_sum' => ['$ifNull' => ['$grant_sum', 0]],
    ]],

    ['$group' => [
        '_id' => null,
        'projects' => ['$sum' => 1],

        'income_proposed' => ['$sum' => '$grant_income_proposed'],
        'income_approved' => ['$sum' => '$grant_income'],

        'sum_proposed' => ['$sum' => '$grant_sum_proposed'],
        'sum_approved' => ['$sum' => '$grant_sum'],
    ]]
])->toArray();

$totals = $fundingTotals[0] ?? null;
?>

<h2>
    <?= lang('Approved third-party funding', 'Bewilligte Drittmittel') ?>
</h2>

<?php if (empty($totals)) { ?>
    <p class="text-muted"><?= lang('No approved projects found.', 'Keine bewilligten Projekte gefunden.') ?></p>
<?php } else {

    $incomeDelta = $totals['income_approved'] - $totals['income_proposed'];
    $sumDelta    = $totals['sum_approved'] - $totals['sum_proposed'];

    $incomeCls = ($incomeDelta < 0) ? 'text-danger' : 'text-success';
    $sumCls    = ($sumDelta < 0) ? 'text-danger' : 'text-success';
?>

    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Metric', 'Kennzahl') ?></th>
                <th class="text-right"><?= lang('Proposed', 'Beantragt') ?> (EUR)</th>
                <th class="text-right"><?= lang('Approved', 'Bewilligt') ?> (EUR)</th>
                <th class="text-right"><?= lang('Delta', 'Delta') ?> (EUR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-weight-bold">
                    <?= lang('Third-party income', 'Drittmitteleinnahmen') ?>
                </td>
                <td class="text-right"><?= fmt_eur($totals['income_proposed']) ?></td>
                <td class="text-right"><?= fmt_eur($totals['income_approved']) ?></td>
                <td class="text-right <?= $incomeCls ?>">
                    <?= fmt_eur($incomeDelta) ?>
                </td>
            </tr>

            <tr>
                <td class="font-weight-bold">
                    <?= lang('Total project volume', 'Gesamtprojektvolumen') ?>
                </td>
                <td class="text-right"><?= fmt_eur($totals['sum_proposed']) ?></td>
                <td class="text-right"><?= fmt_eur($totals['sum_approved']) ?></td>
                <td class="text-right <?= $sumCls ?>">
                    <?= fmt_eur($sumDelta) ?>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-muted">
                    <?= lang(
                        'Based on approved projects only',
                        'Basierend ausschließlich auf bewilligten Projekten'
                    ) ?>
                    (<?= $totals['projects'] ?>)
                </td>
            </tr>
        </tfoot>
    </table>

<?php } ?>


<hr>
<!-- By year -->

<?php
$byApprovalYear = $osiris->proposals->find(
    [
        'status' => 'approved',
        'grant_sum' => ['$exists' => true],
    ],
    // Keep only the funding fields (defensive defaults)
    ['projection' => [
        '_id' => 1,
        'name' => 1,
        'approval_date' => 1,
        'start_date' => 1,
        'grant_income_proposed' => 1,
        'grant_income' => 1,
        'grant_sum_proposed' => 1,
        'grant_sum' => 1,
    ]]
)->toArray();

function rate_cls($r)
{
    if ($r === null) return 'text-muted';
    if ($r >= 0.95) return 'text-success';
    if ($r >= 0.75) return 'text-signal';
    return 'text-danger';
}

// ---- group proposals by approval year ----
$rows = [];
foreach ($byApprovalYear as $p) {

    // 1) Determine approval year
    $year = $p['approval_date'] ?? null;
    if (is_string($year) && strlen($year) >= 4) {
        $year = (int)substr($year, 0, 4);
    } else {
        $year = null;
    }

    // If not present, derive from start_date (or another date you have)
    if (empty($year)) {
        $d = $p['start_date'] ?? null;
        if (is_string($d) && strlen($d) >= 4) {
            $year = (int)substr($d, 0, 4);
        }
    }

    if (empty($year)) continue;

    $incomeProp = (float)($p['grant_income_proposed'] ?? 0);
    $incomeAppr = (float)($p['grant_income'] ?? 0);
    $sumProp    = (float)($p['grant_sum_proposed'] ?? 0);
    $sumAppr    = (float)($p['grant_sum'] ?? 0);

    if (!isset($rows[$year])) {
        $rows[$year] = [
            'year' => (int)$year,
            'projects' => [],
            'project_count' => 0,
            'income_proposed' => 0,
            'income_approved' => 0,
            'sum_proposed' => 0,
            'sum_approved' => 0,
        ];
    }

    $rows[$year]['projects'][] = [
        'id' => (string)($p['_id'] ?? ''),
        'name' => $p['name'] ?? '-',
        'income_proposed' => $incomeProp,
        'income_approved' => $incomeAppr,
        'sum_proposed' => $sumProp,
        'sum_approved' => $sumAppr,
    ];

    $rows[$year]['project_count']++;
    $rows[$year]['income_proposed'] += $incomeProp;
    $rows[$year]['income_approved'] += $incomeAppr;
    $rows[$year]['sum_proposed']    += $sumProp;
    $rows[$year]['sum_approved']    += $sumAppr;
}

// sort by year desc
krsort($rows);
?>

<h2>
    <?= lang('Funding by approval year', 'Drittmittel nach Bewilligungsjahr') ?>
</h2>

<?php if (empty($rows)) { ?>
    <p class="text-muted"><?= lang('No approved projects found.', 'Keine bewilligten Projekte gefunden.') ?></p>
<?php } else { ?>

    <table class="table" id="funding-by-approval-year">
        <thead>
            <tr>
                <th></th>
                <th style="width:90px;"><?= lang('Year', 'Jahr') ?></th>
                <th class="text-right"><?= lang('Projects', 'Projekte') ?></th>

                <th class="text-right"><?= lang('Income proposed', 'Einnahmen beantragt') ?> (EUR)</th>
                <th class="text-right"><?= lang('Income approved', 'Einnahmen bewilligt') ?> (EUR)</th>
                <th class="text-right"><?= lang('Approval rate', 'Bewilligungsquote') ?></th>

                <th class="text-right"><?= lang('Volume proposed', 'Volumen beantragt') ?> (EUR)</th>
                <th class="text-right"><?= lang('Volume approved', 'Volumen bewilligt') ?> (EUR)</th>
                <th class="text-right"><?= lang('Approval rate', 'Bewilligungsquote') ?></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($rows as $year => $r) {

                $incomeRate = ($r['income_proposed'] > 0) ? ($r['income_approved'] / $r['income_proposed']) : null;
                $sumRate    = ($r['sum_proposed'] > 0) ? ($r['sum_approved'] / $r['sum_proposed']) : null;

                $detailId = 'funding-details-' . (int)$year;
            ?>
                <tr>
                    <td>
                        <a onclick="$('#<?= $detailId ?>').toggle(); $(this).find('i').toggleClass('ph-magnifying-glass-plus ph-magnifying-glass-minus');">
                            <i class="ph ph-magnifying-glass-plus"></i>
                        </a>
                    </td>
                    <td class="font-weight-bold"><?= (int)$year ?></td>
                    <td class="text-right">
                        <?= (int)$r['project_count'] ?>
                    </td>

                    <td class="text-right"><?= fmt_eur($r['income_proposed']) ?></td>
                    <td class="text-right"><?= fmt_eur($r['income_approved']) ?></td>
                    <td class="text-right <?= rate_cls($incomeRate) ?>"><?= fmt_pct($incomeRate) ?></td>

                    <td class="text-right"><?= fmt_eur($r['sum_proposed']) ?></td>
                    <td class="text-right"><?= fmt_eur($r['sum_approved']) ?></td>
                    <td class="text-right <?= rate_cls($sumRate) ?>"><?= fmt_pct($sumRate) ?></td>
                </tr>
                <tr id="<?= $detailId ?>" style="display:none; margin-top:8px;">
                    <td colspan="9">
                        <table class="table small simple">
                            <thead>
                                <tr>
                                    <th><?= lang('Project', 'Projekt') ?></th>
                                    <th class="text-right"><?= lang('Income (proposed/approved)', 'Einnahmen (beantragt/bewilligt)') ?></th>
                                    <th class="text-right"><?= lang('Volume (proposed/approved)', 'Volumen (beantragt/bewilligt)') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($r['projects'] as $p) {
                                    $url = ROOTPATH . '/proposals/view/' . $p['id']; // adjust route if needed
                                ?>
                                    <tr>
                                        <td>
                                            <a href="<?= $url ?>">
                                                <?= e($p['name']) ?>
                                            </a>
                                        </td>
                                        <td class="text-right">
                                            <?= fmt_eur($p['income_proposed']) ?> / <?= fmt_eur($p['income_approved']) ?>
                                        </td>
                                        <td class="text-right">
                                            <?= fmt_eur($p['sum_proposed']) ?> / <?= fmt_eur($p['sum_approved']) ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>


<?php } ?>
<?php if (!empty($rows)) { ?>
    <div class="box padded">
        <h3 class="title text-center">
            <?= lang('Approved volume & approval rate by year', 'Bewilligtes Volumen & Bewilligungsquote nach Jahr') ?>
        </h3>
        <div id="approval-kpi-plot" style="height:380px;"></div>
    </div>

    <script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js"></script>
    <script>
        // Build chart rows from PHP $rows
        function approvalKPIplot() {
            const approvalRows = <?= json_encode(array_values(array_map(function ($r) {
                                        $incomeProp = (float)($r['income_proposed'] ?? 0);
                                        $incomeAppr = (float)($r['income_approved'] ?? 0);
                                        $sumProp    = (float)($r['sum_proposed'] ?? 0);
                                        $sumAppr    = (float)($r['sum_approved'] ?? 0);

                                        return [
                                            'year' => (int)$r['year'],
                                            'projects' => (int)($r['project_count'] ?? 0),
                                            'sumApproved' => $sumAppr,
                                            'sumRate' => ($sumProp > 0 ? $sumAppr / $sumProp : null),
                                            'incomeApproved' => $incomeAppr,
                                            'incomeRate' => ($incomeProp > 0 ? $incomeAppr / $incomeProp : null),
                                        ];
                                    }, $rows))); ?>;

            // Sort ascending for nicer reading
            approvalRows.sort((a, b) => a.year - b.year);

            const years = approvalRows.map(r => String(r.year));
            const incomeApproved = approvalRows.map(r => r.incomeApproved);
            const incomeRate = approvalRows.map(r => r.incomeRate === null ? null : r.incomeRate * 100);
            const volumeApproved = approvalRows.map(r => r.sumApproved);
            const rate = approvalRows.map(r => r.sumRate === null ? null : r.sumRate * 100);
            const projects = approvalRows.map(r => r.projects);

            // Bubble size scaling (keep it stable)
            const maxP = Math.max(1, ...projects);
            const bubbleSize = projects.map(p => 8 + (28 * (p / maxP))); // 8..36px
            let traces = [{
                    x: years,
                    y: volumeApproved,
                    type: 'bar',
                    name: lang('Approved volume', 'Volumen bewilligt'),
                    marker: {
                        color: OSIRIS_PRIMARY + 'CC'
                    },
                    hovertemplate: lang('Year', 'Jahr') + ' %{x}<br>' +
                        lang('Approved volume', 'Volumen bewilligt') + ': %{y:,.0f} €<extra></extra>'
                },
                {
                    x: years,
                    y: incomeApproved,
                    type: 'bar',
                    name: lang('Approved income', 'Einnahmen bewilligt'),
                    marker: {
                        color: OSIRIS_SUCCESS + 'CC'
                    },
                    hovertemplate: lang('Year', 'Jahr') + ' %{x}<br>' +
                        lang('Approved income', 'Einnahmen bewilligt') + ': %{y:,.0f} €<extra></extra>'
                },
                {
                    x: years,
                    y: rate,
                    type: 'scatter',
                    mode: 'lines+markers',
                    name: lang('Approval rate (volume)', 'Bewilligungsquote (Volumen)'),
                    yaxis: 'y2',
                    marker: {
                        size: 7,
                        color: OSIRIS_ACCENT
                    },
                    hovertemplate: lang('Year', 'Jahr') + ' %{x}<br>' +
                        lang('Approval rate', 'Bewilligungsquote') + ': %{y:.1f}%<extra></extra>'
                },
                {
                    x: years,
                    y: rate,
                    type: 'scatter',
                    mode: 'markers',
                    name: lang('Projects', 'Projekte'),
                    yaxis: 'y2',
                    marker: {
                        size: bubbleSize,
                        color: OSIRIS_SUCCESS,
                        opacity: 0.35,
                        line: {
                            width: 1,
                            color: OSIRIS_SUCCESS
                        }
                    },
                    customdata: projects,
                    hovertemplate: lang('Year', 'Jahr') + ' %{x}<br>' +
                        lang('Projects', 'Projekte') + ' : %{customdata}<extra></extra>'
                }
            ];

            const layout = {
                margin: {
                    l: 70,
                    r: 70,
                    t: 10,
                    b: 40
                },
                barmode: 'group',
                hovermode: 'x unified',
                legend: {
                    orientation: 'h',
                    x: 0,
                    y: -0.2
                },
                yaxis: {
                    title: 'EUR',
                    tickformat: ',.0f'
                },
                yaxis2: {
                    title: '%',
                    overlaying: 'y',
                    side: 'right',
                    rangemode: 'tozero',
                    ticksuffix: '%'
                },
                xaxis: {
                    title: lang('Approval year', 'Bewilligungsjahr'),
                    // no decimals
                    tickformat: 'd',
                    dtick: 1
                }
            };

            Plotly.newPlot('approval-kpi-plot', traces, layout, {
                responsive: true,
                displayModeBar: false
            });
        }
        approvalKPIplot();
    </script>
<?php } ?>



<?php
$filter_funding = ['grant_years' => ['$exists' => true, '$ne' => []]];
$fundingByYear = $osiris->proposals->aggregate([
    ['$match' => $filter_funding],
    // Keep only what we need
    ['$project' => [
        '_id' => 1,
        'name' => 1,
        'grant_years' => 1
    ]],

    // One row per year-entry
    ['$unwind' => '$grant_years'],

    // Normalize fields (defensive)
    ['$project' => [
        'project_id' => '$_id',
        'project_name' => '$name',
        'year' => ['$ifNull' => ['$grant_years.year', null]],
        'planned' => ['$ifNull' => ['$grant_years.planned', 0]],
        'spent' => ['$ifNull' => ['$grant_years.spent', 0]],
    ]],

    // Drop entries without year
    ['$match' => ['year' => ['$ne' => null]]],

    // Aggregate per year
    ['$group' => [
        '_id' => '$year',
        'planned' => ['$sum' => '$planned'],
        'spent' => ['$sum' => '$spent'],
        'project_count' => ['$sum' => 1],
    ]],

    // Sort by year
    ['$sort' => ['_id' => -1]],
])->toArray();
?>
<h2>
    <?= lang('Third-party funding (summary)', 'Drittmitteleinnahmen (Übersicht)') ?>
</h2>

<?php if (empty($fundingByYear)) { ?>
    <p class="text-muted"><?= lang('No funding information available.', 'Keine Drittmitteleinnahmen verfügbar.') ?></p>
<?php } else { ?>

    <table class="table" id="funding-by-year">
        <thead>
            <tr>
                <th style="width:90px;"><?= lang('Year', 'Jahr') ?></th>
                <th class="text-right"><?= lang('Planned', 'Soll') ?> (EUR)</th>
                <th class="text-right"><?= lang('Actual', 'Ist') ?> (EUR)</th>
                <th class="text-right"><?= lang('Delta', 'Delta') ?> (EUR)</th>
                <th class="text-right"><?= lang('Fulfillment', 'Erfüllung') ?></th>
                <th class="text-right"><?= lang('Number of projects', 'Anzahl Projekte') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fundingByYear as $row) {
                $year = $row['_id'];
                $planned = (float) ($row['planned'] ?? 0);
                $spent = (float) ($row['spent'] ?? 0);
                $delta = $spent - $planned;

                $fulfillment = ($planned > 0) ? round(($spent / $planned) * 100, 2) : 0;

                // Fulfillment thresholds (adjust if you want)
                if ($fulfillment > 110) $cls = 'text-danger';
                else if ($fulfillment < 70) $cls = 'text-signal';
                else $cls = 'text-success';

                // Delta color (your current logic: negative = danger)
                $deltaCls = ($delta < 0) ? 'text-danger' : 'text-success';

                $projectCount = (int)($row['project_count'] ?? count($projects));
            ?>
                <tr>
                    <td class="font-weight-bold"><?= $year ?></td>
                    <td class="text-right"><?= fmt_eur($planned) ?></td>
                    <td class="text-right"><?= fmt_eur($spent) ?></td>
                    <td class="text-right <?= $deltaCls ?>"><?= fmt_eur($delta) ?></td>
                    <td class="text-right <?= $cls ?>"><?= fmt_pct($fulfillment / 100) ?></td>
                    <td class="text-right">
                        <?= $projectCount ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $('#funding-by-year').DataTable({
                buttons: downloadTableButtons(),
                "order": [
                    [0, "desc"]
                ],
            });
        });
    </script>

<?php } ?>

<?php if (!empty($fundingByYear)) { ?>
    <div class="box padded">
        <h3 class="title text-center">
            <?= lang('Planned vs. Actual Funding', 'Geplante vs. tatsächliche Einnahmen') ?>
        </h3>
        <div id="funding-plot" style="height:340px;"></div>
    </div>
    <div class="box padded">
        <h3 class="title text-center">
            <?= lang('Funding Delta by Year', 'Einnahmen-Differenz pro Jahr') ?>
        </h3>
        <div id="delta-plot" style="height:340px;"></div>
    </div>

    <script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js"></script>
    <script>
        var fundingRows = <?= json_encode(array_map(function ($r) {
                                if (empty($r['_id']) || $r['_id'] < 1900) return null;
                                return [
                                    'year' => $r['_id'],
                                    'planned' => (float)($r['planned'] ?? 0),
                                    'spent' => (float)($r['spent'] ?? 0),
                                ];
                            }, $fundingByYear)); ?>;
        // Remove nulls (invalid years)
        fundingRows = fundingRows.filter(r => r !== null);

        const years = fundingRows.map(r => String(r.year));
        const planned = fundingRows.map(r => r.planned);
        const spent = fundingRows.map(r => r.spent);
        const delta = fundingRows.map(r => r.spent - r.planned);

        const data = [{
                x: years,
                y: planned,
                type: 'bar',
                marker: {
                    color: OSIRIS_PRIMARY + 'CC'
                },
                name: lang('Planned', 'Soll')
            },
            {
                x: years,
                y: spent,
                type: 'bar',
                marker: {
                    color: OSIRIS_ACCENT + 'CC'
                },
                name: lang('Actual', 'Ist')
            }
        ];

        const financeLayout = {
            barmode: 'group',
            margin: {
                l: 70,
                r: 70,
                t: 10,
                b: 40
            },
            yaxis: {
                title: 'EUR',
                tickformat: ',.0f'
            },
            legend: {
                orientation: 'h',
                x: 0,
                y: -0.2
            },
            xaxis: {
                title: lang('Year', 'Jahr'),
                // no decimals
                tickformat: 'd',
                dtick: 1
            },
            hovermode: 'x unified'
        };

        Plotly.newPlot('funding-plot', data, financeLayout, {
            responsive: true,
            displayModeBar: false
        });

        const deltaData = [{
            x: years,
            y: delta,
            type: 'bar',
            marker: {
                color: delta.map(v => v < 0 ? OSIRIS_DANGER : OSIRIS_SUCCESS)
            },
            name: lang('Delta', 'Delta')
        }];
        const deltaLayout = {
            margin: {
                l: 70,
                r: 70,
                t: 10,
                b: 40
            },
            yaxis: {
                title: 'EUR',
                tickformat: ',.0f'
            },
            legend: {
                orientation: 'h',
                x: 0,
                y: -0.2
            },
            hovermode: 'x unified'
        };
        Plotly.newPlot('delta-plot', deltaData, deltaLayout, {
            responsive: true,
            displayModeBar: false
        });
    </script>
<?php } ?>