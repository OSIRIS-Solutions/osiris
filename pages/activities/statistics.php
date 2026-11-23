<?php

/**
 * The statistics of all activities
 * Created in cooperation with DSMZ
 *
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 *
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$time_frame = '';
$phrase = t('common.in_the_reporting_year');
// today is the default reportyear
if (isset($_GET['reportyear']) && !empty($_GET['reportyear'])) {
    $reportyear = intval($_GET['reportyear']);
    $time_frame = t('common.reporting_year') . ': ' . $reportyear;
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
} elseif (isset($_GET['reportstart']) && !empty($_GET['reportstart']) && isset($_GET['reportend']) && !empty($_GET['reportend'])) {
    $reportstart = $_GET['reportstart'];
    $reportend = $_GET['reportend'];
    $time_frame = t('common.reporting_period') . ': ' . date('d.m.Y', strtotime($reportstart)) . ' - ' . date('d.m.Y', strtotime($reportend));
    $reportyear = date('Y', strtotime($reportstart));
    $phrase = t('common.in_the_reporting_period');
} else {
    $reportyear = CURRENTYEAR;
    $time_frame = t('common.reporting_year') . ': ' . $reportyear;
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
        border-top: var(--border-width) solid var(--border-color);
        color: var(--muted-color);
        background-color: var(--gray-color-very-light);
    }

    tfoot th:first-child {
        border-bottom-left-radius: var(--border-radius);
    }

    tfoot th:last-child {
        border-bottom-right-radius: var(--border-radius);
    }

    div.dt-container div.dt-layout-full {
        width: auto;
    }
</style>

<h1>
    <i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i>
    <?= t('common.statistics') ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/activities">
        <i class="ph ph-arrow-left"></i>
        <?= t('activities.back_to_activities') ?>
    </a>
</div>


<div class="alert signal">
    <i class="ph ph-warning text-signal"></i>
    <?= t('activities.all_of_the_following_statistics_are_based_on_the_reporting_period') ?>


    <div class="row position-relative mt-10">
        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/activities/statistics" method="get" class="d-flex align-items-baseline" style="grid-gap: 1rem;">
                <h6 class="m-0"><?= t('common.change_reporting_year') ?>:</h6>
                <input type="number" name="reportyear" value="<?= $reportyear ?>" class="form-control w-auto d-inline-block" step="1" min="1900" max="<?= CURRENTYEAR + 2 ?>" />
                <button class="btn signal filled" type="submit"><?= t('action.update') ?></button>
            </form>
        </div>

        <div class="text-divider"><?= t('common.or') ?></div>

        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/activities/statistics" method="get" class="d-flex align-items-baseline ml-20" style="grid-gap: 1rem;">
                <h6 class="m-0"><?= t('common.change_reporting_period') ?>:</h6>
                <input type="date" name="reportstart" value="<?= $reportstart ?>" class="form-control w-auto d-inline-block" required />
                <input type="date" name="reportend" value="<?= $reportend ?>" class="form-control w-auto d-inline-block" required />
                <button class="btn signal filled" type="submit"><?= t('action.update') ?></button>
            </form>
        </div>
    </div>
</div>

<p class="text-muted">
    <?= t('activities.only_affiliated_activities_are_counted_at_least_one_author_is_affiliated_wi') ?>
</p>

<div class="row row-eq-spacing">

    <div id="statistics" class="col-md-12 col-lg-8 col-xl-9">

        <h2 class="text-decoration-underline">
            <?= $time_frame ?>
        </h2>

        <p class="lead">
            <?= t('activities.number_of_activities') ?> <?= $phrase ?>:
            <b class="badge signal"><?= count($activities) ?></b>
            <span class="text-muted">(<?= $all ?> <?= t('common.total') ?>)</span>
        </p>


        <h2 id="activities-by-type">
            <?= t('common.activities') ?> <?= $phrase ?>:
        </h2>
        <p class="text-muted">
            <?= t('activities.only_activities_with_a_start_and_end_date_in_the_reporting_period_and_at_le') ?>
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

        <table class="table w-auto" id="activities-by-type-table">
            <thead>
                <tr>
                    <th><?= t('common.category') ?></th>
                    <th><?= t('common.type') ?></th>
                    <th><?= t('common.count') ?></th>
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
                    <th colspan="2"><?= t('common.total') ?></th>
                    <th><?= count($activities) ?></th>
                </tr>
            </tfoot>
        </table>


        <h3 id="activities-started-before">
            <?= t('activities.activities_that_have_started_before_the_time_frame') ?>
        </h3>
        <p class="text-muted">
            <?= t('activities.only_activities_that_have_started_before_the_reporting_period_but_were_stil') ?>
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


        <table class="table w-auto" id="activities-started-before-table">
            <thead>
                <tr>
                    <th><?= t('common.type') ?></th>
                    <th><?= t('activities.subtype_statistics') ?></th>
                    <th><?= t('common.count') ?></th>
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
                    <th colspan="2"><?= t('common.total') ?></th>
                    <th><?= count($activities) ?></th>
                </tr>
            </tfoot>
        </table>



        <br>
        <hr>

        <h2 id="statistics-on-publications">
            <?= t('activities.statistics_on_publications') ?>
        </h2>

        <p class="text-muted">
            <?= t('activities.only_publications_with_a_start_and_end_date_in_the_reporting_period_and_at') ?>
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

        <table class="table w-auto" id="publications-by-type-table">
            <thead>
                <tr>
                    <th><?= t('activities.type_of_publication') ?></th>
                    <th><?= t('activities.count') ?></th>
                    <th><?= t('activities.count_of_affiliated') ?></th>
                    <th><?= t('activities.count_of_online') ?><sup>1</sup></th>
                    <th><?= t('activities.without_external') ?><sup>2</sup></th>
                    <th><?= t('activities.peer_reviewed_label') ?><sup>3</sup></th>
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
                    <th colspan="1"><?= t('common.total') ?></th>
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
            <sup>2</sup><?= t('activities.external_co_creators_are_persons_who_are_not_affiliated_with_the_reporting') ?>
            <br>
            <sup>3</sup><?= t('activities.peer_reviewed_only_if_the_peer_reviewed_module_is_used') ?>
        </p>

        <h3 id="oa-publications">
            <?= t('activities.number_of_open_access_publications') ?>
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

        <table class="table w-auto" id="oa-publications-table">
            <thead>
                <tr>
                    <th><?= t('common.status') ?></th>
                    <th><?= t('common.count') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($oa_publications as $oa): ?>
                    <tr class="text-<?= $oa['status'] ?>">
                        <td>
                            <?php
                            switch ($oa['status']) {
                                case 'closed':
                                    echo '<i class="icon-closed-access"></i> <span class="badge danger"> Closed Access</span>';
                                    break;
                                case 'green':
                                    echo '<i class="icon-open-access"></i>
                                        <span class="badge success">Green Open Access</span>';
                                    break;
                                case 'gold':
                                    echo '<i class="icon-open-access"></i>
                                        <span class="badge signal">Gold Open Access</span>';
                                    break;
                                case 'hybrid':
                                    echo '<i class="icon-open-access"></i>
                                        <span class="badge">Hybrid Open Access</span>';
                                    break;
                                case 'bronze':
                                    echo '<i class="icon-open-access"></i>
                                        <span class="badge secondary">Bronze Open Access</span>';
                                    break;
                                case 'diamond':
                                    echo '<i class="icon-open-access"></i>
                                        <span class="badge primary">Diamond Open Access</span>';
                                    break;
                                default:
                                    echo '<i class="icon-open-access"></i>
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
                    <th><?= t('common.total') ?></th>
                    <th><?= $count_all ?></th>
                </tr>
            </tfoot>
        </table>

    </div>


    <div class="col-lg-3 d-none d-lg-block">
        <nav class="on-this-page-nav">
            <div class="content">
                <div href="#statistics" class="title"><?= t('common.statistics') ?></div>

                <a href="#activities-by-type">
                    <?= t('activities.activities_by_type') ?>
                </a>
                <a href="#activities-started-before">
                    <?= t('activities.activities_started_before') ?>
                </a>
                <a href="#statistics-on-publications">
                    <?= t('activities.statistics_on_publications') ?>
                </a>
                <a href="#oa-publications">
                    <?= t('activities.open_access_publications') ?>
                </a>
            </div>

        </nav>

    </div>
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

    let tables = {
        '#activities-by-type-table': <?= json_encode(t('activities.activities_by_type'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + ', <?= $time_frame ?>',
        '#publications-by-type-table': <?= json_encode(t('activities.publications_by_type'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + ', <?= $time_frame ?>',
        '#oa-publications-table': <?= json_encode(t('activities.oa_publications'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + ', <?= $time_frame ?>',
        '#activities-started-before-table': <?= json_encode(t('activities.activities_started_before'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + ', <?= $time_frame ?>'
    };

    for (const [selector, name] of Object.entries(tables)) {
        initDownloadTable(selector, name + ' <?= $reportyear ?>');
    }
</script>