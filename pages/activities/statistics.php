<?php

/**
 * The statistics of all activities
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$time_frame = '';
$phrase = lang('in the reporting year', 'im Reportjahr');
// today is the default reportyear
if (isset($_GET['reportyear']) && !empty($_GET['reportyear'])) {
    $reportyear = intval($_GET['reportyear']);
    $time_frame = lang('Reporting year', 'Reportjahr') . ': ' . $reportyear;
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
} elseif (isset($_GET['reportstart']) && !empty($_GET['reportstart']) && isset($_GET['reportend']) && !empty($_GET['reportend'])) {
    $reportstart = $_GET['reportstart'];
    $reportend = $_GET['reportend'];
    $time_frame = lang('Reporting period', 'Reportzeitraum') . ': ' . date('d.m.Y', strtotime($reportstart)) . ' - ' . date('d.m.Y', strtotime($reportend));
    $reportyear = date('Y', strtotime($reportstart));
    $phrase = lang('in the reporting period', 'im Reportzeitraum');
} else {
    $reportyear = CURRENTYEAR;
    $time_frame = lang('Reporting year', 'Reportjahr') . ': ' . $reportyear;
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
}


$filter = [
    'affiliated' => true,
    'start_date' => ['$gte' => $reportstart],
    '$or' => [
        ['end_date' => ['$lte' => $reportend]],
        ['end_date' => null]
    ]
];

$activities  = $osiris->activities->find($filter)->toArray();

$all = $osiris->activities->count(['affiliated' => true]);
?>

<style>
    tfoot th {
        font-weight: 400 !important;
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
    <i class="ph ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('Statistics', 'Statistiken') ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/activities">
        <i class="ph ph-arrow-left"></i>
        <?= lang('Back to Activities', 'Zurück zu Aktivitäten') ?>
    </a>
</div>


<div class="alert signal">
    <i class="ph ph-warning text-signal"></i>
    <?= lang('All of the following statistics are based on the reporting period.', 'Alle unten aufgeführten Statistiken basieren auf dem angegebenen Reportzeitraum.') ?>


    <div class="row position-relative mt-10">
        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/activities/statistics" method="get" class="d-flex align-items-baseline" style="grid-gap: 1rem;">
                <h6 class="m-0"><?= lang('Change Reporting Year', 'Reportjahr ändern') ?>:</h6>
                <input type="number" name="reportyear" value="<?= $reportyear ?>" class="form-control w-auto d-inline-block" step="1" min="1900" max="<?= CURRENTYEAR + 2 ?>" />
                <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
            </form>
        </div>

        <div class="text-divider"><?= lang('OR', 'ODER') ?></div>

        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/activities/statistics" method="get" class="d-flex align-items-baseline ml-20" style="grid-gap: 1rem;">
                <h6 class="m-0"><?= lang('Change Reporting Period', 'Reportzeitraum ändern') ?>:</h6>
                <input type="date" name="reportstart" value="<?= $reportstart ?>" class="form-control w-auto d-inline-block" required />
                <input type="date" name="reportend" value="<?= $reportend ?>" class="form-control w-auto d-inline-block" required />
                <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
            </form>
        </div>
    </div>
</div>

<p class="text-muted">
    <?= lang('Only affiliated activities are counted (at least one author is affiliated with the institute).', 'Es werden nur affilierte Aktivitäten gezählt (mind. ein:e Autor:in ist mit dem Institut affiliert).') ?>
</p>

<br>
<div id="statistics">

    <h2 class="text-decoration-underline">
        <?= $time_frame ?>
    </h2>

    <p class="lead">
        <?= lang('Number of activities', 'Anzahl der Aktivitäten') ?> <?= $phrase ?>:
        <b class="badge signal"><?= count($activities) ?></b>
        <span class="text-muted">(<?= $all ?> <?= lang('total', 'gesamt') ?>)</span>
    </p>


    <h2>
        <?= lang('Activities', 'Aktivitäten') ?> <?= $phrase ?>:
    </h2>
    <p class="text-muted">
        <?= lang('Only activities with a start and end date in the reporting period and at least one affiliated author are counted.', 'Es werden nur Aktivitäten mit einem Start- und Enddatum im Reportzeitraum und mindestens einer/einem affilierten Autor/Autorin gezählt.') ?>
    </p>

    <?php
    $activities_by_type = $osiris->activities->aggregate([
        [
            '$match' => $filter
        ],
        [
            '$group' => [
                '_id' => '$subtype',
                'type' => ['$first' => '$type'],
                'count' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => [
                'type' => 1
            ]
        ]
    ])->toArray();
    ?>

    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Subtype', 'Untertyp') ?></th>
                <th><?= lang('Count', 'Anzahl') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities_by_type as $activity): ?>
                <tr class="text-<?= $activity['type'] ?>">
                    <td><?= $Settings->title($activity['type']); ?></td>
                    <td><?= $Settings->title($activity['type'], $activity['_id']) ?></td>
                    <th><?= $activity['count'] ?></th>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2"><?= lang('Total', 'Gesamt') ?></th>
                <th><?= count($activities) ?></th>
            </tr>
        </tfoot>
    </table>


    <h3>
        <?= lang('Activities that have started before the time frame', 'Aktivitäten, die vor dem Zeitraum gestartet sind') ?>
    </h3>
    <p class="text-muted">
        <?= lang('Only activities that have started before the reporting period but were still running are counted.', 'Es werden nur Aktivitäten gezählt, die vor dem Reportzeitraum gestartet sind, aber im Zeitraum immer noch liefen.') ?>
    </p>
    <?php
    $filter = [
        'affiliated' => true,
        'start_date' => ['$lt' => $reportstart],
        'end_date' => ['$gte' => $reportstart]
    ];
    $activities_by_type = $osiris->activities->aggregate([
        [
            '$match' => $filter
        ],
        [
            '$group' => [
                '_id' => '$subtype',
                'type' => ['$first' => '$type'],
                'count' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => [
                'type' => 1
            ]
        ]
    ])->toArray();
    ?>


    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Subtype', 'Untertyp') ?></th>
                <th><?= lang('Count', 'Anzahl') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($activities_by_type as $activity): ?>
                <tr class="text-<?= $activity['type'] ?>">
                    <td><?= $Settings->title($activity['type']); ?></td>
                    <td><?= $Settings->title($activity['type'], $activity['_id']) ?></td>
                    <th><?= $activity['count'] ?></th>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2"><?= lang('Total', 'Gesamt') ?></th>
                <th><?= count($activities) ?></th>
            </tr>
        </tfoot>
    </table>



    <br>
    <hr>

    <h2>
        <?= lang('Statistics on publications', 'Statistiken zu Publikationen') ?>
    </h2>

    <p class="text-muted">
        <?= lang('Only publications with a start and end date in the reporting period and at least one affiliated author are counted.', 'Es werden nur Publikationen mit einem Start- und Enddatum im Reportzeitraum und mindestens einer/einem affilierten Autor/Autorin gezählt.') ?>
    </p>

    <?php
    $filter = [
        'type' => 'publication',
        'start_date' => ['$gte' => $reportstart],
        'end_date' => ['$lte' => $reportend],
        // '$or' => [
        //     ['end_date' => ['$lte' => $reportend]],
        //     ['end_date' => null]
        // ],
        // 'affiliated' => true
    ];
    $publications = $osiris->activities->aggregate([
        [
            '$match' => $filter
        ],
        [
            '$group' => [
                '_id' => '$subtype',
                'count' => ['$sum' => 1],
                'affiliated' => ['$sum' => ['$cond' => [['$eq' => ['$affiliated', true]], 1, 0]]],
                // count epub = true
                'epub' => ['$sum' => ['$cond' => [['$eq' => ['$epub', true]], 1, 0]]],
                // count cooperative != leading or contributing
                'cooperative' => ['$sum' => ['$cond' => [['$ne' => ['$cooperative', 'leading']], 1, 0]]],
                // peer-reviewed = true
                'peer_reviewed' => ['$sum' => ['$cond' => [['$eq' => ['$peer_reviewed', true]], 1, 0]]],
            ]
        ],
        [
            '$sort' => [
                'count' => -1
            ]
        ]
    ])->toArray();
    ?>

    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Type of publication', 'Art der Publikation') ?></th>
                <th><?= lang('Count', 'Gesamt') ?></th>
                <th><?= lang('Count of affiliated', 'davon Affiliert') ?></th>
                <th><?= lang('Count of Online', 'davon Online') ?><sup>1</sup></th>
                <th><?= lang('Without external', 'ohne Externe') ?><sup>2</sup></th>
                <th><?= lang('Peer-reviewed') ?><sup>3</sup></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $counts = [
                'all' => 0,
                'affiliated' => 0,
                'epub' => 0,
                'cooperative' => 0,
                'peer_reviewed' => 0
            ];
            foreach ($publications as $publication):
                $counts['all'] += $publication['count'];
                $counts['affiliated'] += $publication['affiliated'] ?? 0;
                $counts['epub'] += $publication['epub'] ?? 0;
                $counts['cooperative'] += $publication['cooperative'] ?? 0;
                $counts['peer_reviewed'] += $publication['peer_reviewed'] ?? 0;
            ?>
                <tr class="text-<?= $publication['_id'] ?>">
                    <td><?= $Settings->title(null, $publication['_id']) ?></td>
                    <td><?= $publication['count'] ?></td>
                    <th>
                        <?= $publication['affiliated'] ?? 0 ?>
                    </th>
                    <td>
                        <?= $publication['epub'] ?? 0 ?>
                    </td>
                    <td>
                        <?= $publication['cooperative'] ?? 0 ?>
                    </td>
                    <td>
                        <?= $publication['peer_reviewed'] ?? 0 ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="1"><?= lang('Total', 'Gesamt') ?></th>
                <th><?= $counts['all'] ?></th>
                <th><?= $counts['affiliated'] ?></th>
                <th><?= $counts['epub'] ?></th>
                <th><?= $counts['cooperative'] ?></th>
                <th><?= $counts['peer_reviewed'] ?></th>
            </tr>
        </tfoot>
    </table>
    <p class="text-muted mt-0">
        <sup>1</sup>Online = Online ahead of print
        <br>
        <sup>2</sup><?= lang('External co-creators are persons who are not affiliated with the reporting institution via an employment relationship or a doctoral procedure.', 'Als externe Ko-Schöpfer/-innen gelten Personen, die nicht mit der berichtenden Einrichtung affiliiert sind über ein Beschäftigungsverhältnis oder ein Promotionsverfahren.') ?>
        <br>
        <sup>3</sup><?= lang('Peer-reviewed = Only if the <code>peer-reviewed</code> module is used.', 'Peer-reviewed = Nur gefüllt, wenn das <code>peer-reviewed</code>-Modul verwendet wird.') ?>
    </p>

    <h3>
        <?= lang('Number of Open Access publications', 'Anzahl der Open Access-Pubikationen') ?>
    </h3>

    <?php
    $filter = [
        'oa_status' => ['$ne' => null],
        'start_date' => ['$gte' => $reportstart],
        'end_date' => ['$lte' => $reportend],
        'affiliated' => true
    ];

    $oa_publications = $osiris->activities->aggregate([
        ['$match' => $filter],
        [
            '$group' => [
                '_id' => '$oa_status',
                'count' => ['$sum' => 1],
            ]
        ],
        ['$project' => ['_id' => 0, 'status' => '$_id', 'count' => 1]],
        ['$sort' => ['count' => -1]],
    ])->toArray();

    $count_all = $osiris->activities->count($filter);
    ?>

    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Status', 'Status') ?></th>
                <th><?= lang('Count', 'Anzahl') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($oa_publications as $oa): ?>
                <tr class="text-<?= $oa['status'] ?>">
                    <td>
                        <?php
                        switch ($oa['status']) {
                            case 'closed':
                                echo '<i class="icon-closed-access text-danger"></i> <span class="badge danger"> Closed Access</span>';
                                break;
                            case 'green':
                                echo '<i class="icon-open-access text-success"></i> 
                                        <span class="badge success">Green Open Access</span>';
                                break;
                            case 'gold':
                                echo '<i class="icon-open-access text-success"></i> 
                                        <span class="badge signal">Gold Open Access</span>';
                                break;
                            case 'hybrid':
                                echo '<i class="icon-open-access text-success"></i> 
                                        <span class="badge">Hybrid Open Access</span>';
                                break;
                            case 'bronze':
                                echo '<i class="icon-open-access text-success"></i> 
                                        <span class="badge secondary">Bronze Open Access</span>';
                                break;
                            case 'diamond':
                                echo '<i class="icon-open-access text-success"></i> 
                                        <span class="badge primary">Diamond Open Access</span>';
                                break;
                            default:
                                echo '<i class="icon-open-access text-success"></i> 
                                        <span class="badge muted">Open Access (Unknown Status)</span>';
                                break;
                        }
                        ?>

                    </td>
                    <th><?= $oa['count'] ?></th>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th><?= lang('Total', 'Gesamt') ?></th>
                <th><?= $count_all ?></th>
            </tr>
        </tfoot>
    </table>

</div>


<script>
    function filterByYear(year, table) {
        var rows = document.querySelectorAll(table + ' tbody tr');
        if (year == '') {
            rows.forEach(function(row) {
                row.style.display = 'table-row';
            });
            return;
        }
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells[2].innerText == year) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>