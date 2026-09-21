<?php

/**
 * The statistics of all projects
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

include_once BASEPATH . '/php/Project.php';
$Project = new Project();

$phrase = lang('common.in_the_reporting_year');
$time_frame = '';

// today is the default reportyear
if (isset($_GET['reportdate']) && !empty($_GET['reportdate'])) {
    $reportdate = $_GET['reportdate'];
    $reportyear = date('Y', strtotime($reportdate));
    $reportstart = date('Y-m-d', strtotime($reportdate));
    $reportend = date('Y-m-d', strtotime($reportdate));
    $phrase = lang('common.on_the_reporting_date');
    $time_frame = lang('projects.reporting_date') . ': ' . $reportdate;
} else if (isset($_GET['reportyear']) && !empty($_GET['reportyear'])) {
    $reportyear = intval($_GET['reportyear']);
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
    $reportdate = date('Y-m-d');
    $time_frame = lang('common.reporting_year') . ': ' . $reportyear;
} else {
    $reportyear = CURRENTYEAR;
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
    $reportdate = date('Y-m-d');
    $time_frame = lang('common.reporting_year') . ': ' . $reportyear;
}

// get all projects that are active in the reporting year
$filter = [
    'start_date' => ['$lte' => $reportend],
    'end_date' => ['$gte' => $reportstart],
    // 'status' => ['$nin' => ['rejected', 'applied']],
];

$projects  = $osiris->projects->find($filter)->toArray();
// $proposals = $osiris->proposals->find($filter)->toArray();

$all = $osiris->projects->count();
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

<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>

<h1>
    <i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('common.statistics') ?>
</h1>


<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/projects">
        <i class="ph ph-arrow-left"></i>
        <?= lang('projects.back_to_projects') ?>
    </a>
</div>


<div class="alert signal">
    <i class="ph ph-warning text-signal"></i>
    <?= lang('projects.all_of_the_following_statistics_are_based_on_the_selected_reporting_time') ?>

    <div class="row position-relative mt-10">
        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/projects/statistics" method="get" class="d-flex align-items-baseline" style="grid-gap: 1rem;">
                <h6 class="mb-0 mt-5"><?= lang('common.change_reporting_year') ?>:</h6>
                <input type="number" name="reportyear" value="<?= $reportyear ?>" class="form-control w-auto d-inline-block" step="1" min="1900" max="<?= CURRENTYEAR + 2 ?>" required />
                <button class="btn signal filled" type="submit"><?= lang('action.update') ?></button>
            </form>
        </div>

        <div class="text-divider"><?= lang('common.or') ?></div>

        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/projects/statistics" method="get" class="d-flex align-items-baseline ml-20" style="grid-gap: 1rem;">
                <h6 class="mb-0 mt-5"><?= lang('common.change_reporting_date') ?>:</h6>
                <input type="date" name="reportdate" value="<?= $reportdate ?>" class="form-control w-auto d-inline-block" required />
                <button class="btn signal filled" type="submit"><?= lang('action.update') ?></button>
            </form>
        </div>
    </div>
</div>

<br>
<div id="statistics">


    <h2 class="text-decoration-underline">
        <?= $time_frame ?>
    </h2>

    <p class="lead">
        <?= lang('projects.number_of_projects') ?> <?= $phrase ?>:
        <b class="badge signal"><?= count($projects) ?></b>
        <span class="text-muted">(<?= $all ?> <?= lang('common.total') ?>)</span>
    </p>

    <h2>
        <?= lang('projects.number_of_projects') ?> <?= $phrase ?>:
    </h2>

    <?php
    $projects_by_type = $osiris->projects->aggregate([
        [
            '$match' => $filter
        ],
        [
            '$group' => [
                '_id' => '$type',
                'count' => ['$sum' => 1]
            ]
        ],
        [
            '$sort' => [
                'count' => 1
            ]
        ]
    ])->toArray();

    $projects_created = $osiris->projects->aggregate([
        [
            '$match' => [
                'created' => [
                    '$gte' => $reportstart,
                    '$lte' => $reportend
                ]
            ]
        ],
        [
            '$group' => [
                '_id' => '$type',
                'count' => ['$sum' => 1]
            ]
        ],
    ])->toArray();
    $projects_created = array_column($projects_created, 'count', '_id');
    ?>

    <table class="table w-auto" id="projects-by-type-table">
        <thead>
            <tr>
                <th><?= lang('common.type') ?></th>
                <th><?= lang('common.count') ?></th>
                <th><?= lang('projects.created_in_time_frame') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects_by_type as $project): ?>
                <tr class="text-<?= $project['_id'] ?>">
                    <td><?= $Project->getType('', $project['_id']); ?></td>
                    <th><?= $project['count'] ?></th>
                    <th>
                        <?= $projects_created[$project['_id']] ?? 0 ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="1"><?= lang('common.total') ?></th>
                <th><?= count($projects) ?></th>
                <th><?= array_sum($projects_created) ?></th>
            </tr>
        </tfoot>
    </table>

    <br>
    <hr>


    <h2>
        <?= lang('projects.number_of_proposals') ?> <?= $phrase ?>:
    </h2>

    <?php
    $filterDates = [
        'submission_date' => ['$gte' => $reportstart, '$lte' => $reportend],
        'approval_date'   => ['$gte' => $reportstart, '$lte' => $reportend],
        'rejection_date'  => ['$gte' => $reportstart, '$lte' => $reportend],
    ];

    $pipeline = [
        ['$match' => [
            '$or' => [
                ['submission_date' => $filterDates['submission_date']],
                ['approval_date'   => $filterDates['approval_date']],
                ['rejection_date'  => $filterDates['rejection_date']],
            ]
        ]],
        ['$facet' => [
            'submitted' => [
                ['$match' => ['submission_date' => $filterDates['submission_date']]],
                ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]],
            ],
            'approved' => [
                ['$match' => ['approval_date' => $filterDates['approval_date']]],
                ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]],
            ],
            'rejected' => [
                ['$match' => ['rejection_date' => $filterDates['rejection_date']]],
                ['$group' => ['_id' => '$type', 'count' => ['$sum' => 1]]],
            ],
        ]]
    ];

    $result = $osiris->proposals->aggregate($pipeline)->toArray();
    $proposals_created = $result[0];

    $table = [];

    foreach (['submitted', 'approved', 'rejected'] as $status) {
        foreach ($proposals_created[$status] as $entry) {
            $type = $entry['_id'];
            $count = $entry['count'];
            if (!isset($table[$type])) {
                $table[$type] = ['submitted' => 0, 'approved' => 0, 'rejected' => 0];
            }
            $table[$type][$status] = $count;
        }
    }
    ?>
    <table class="table w-auto" id="proposals-by-type-table">
        <thead>
            <tr>
                <th><?= lang('common.type') ?></th>
                <th><?= lang('projects.submitted') ?></th>
                <th><?= lang('projects.approved_statistics') ?></th>
                <th><?= lang('common.rejected') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($table as $type => $counts): ?>
                <tr class="text-<?= $type ?>">
                    <td><?= $Project->getType('', $type); ?></td>
                    <th><?= $counts['submitted'] ?></th>
                    <th><?= $counts['approved'] ?></th>
                    <th><?= $counts['rejected'] ?></th>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="1"><?= lang('common.total') ?></th>
                <th><?= array_sum(array_column($table, 'submitted')) ?></th>
                <th><?= array_sum(array_column($table, 'approved')) ?></th>
                <th><?= array_sum(array_column($table, 'rejected')) ?></th>
            </tr>
    </table>

    <p class="text-muted">
        <i class="ph ph-info"></i>
        <?= lang('projects.the_list_shows_the_applications_that_had_the_respective_status_timestamp_in') ?>
    </p>

    <br>
    <hr>

    <?php
    $filter_collaborations = $filter;
    $filter_collaborations['collaborators'] = ['$exists' => true];
    $collaborations = $osiris->projects->aggregate([
        ['$match' => $filter_collaborations],
        ['$lookup' => [
            'from' => 'organizations',
            'localField' => 'collaborators.organization',
            'foreignField' => '_id',
            'as' => 'collaborators'
        ]],
        ['$project' => [
            'collaborators' => 1,
            '_id' => 0,
            'name' => 1,
        ]],
        ['$unwind' => '$collaborators'],
        ['$group' => [
            '_id' => '$collaborators._id',
            'name' => ['$first' => '$collaborators.name'],
            'type' => ['$first' => '$collaborators.type'],
            'location' => ['$first' => '$collaborators.location'],
            'count' => ['$sum' => 1],
            'projects' => ['$push' => '$name']
        ]],
        ['$sort' => ['name' => 1]]
    ])->toArray();
    $count_collab = count($collaborations);
    ?>

    <h2>
        <?= lang('common.cooperation_partners') ?>
        (<?= $count_collab ?>)
    </h2>

    <table class="table" id="collaborative-partners">
        <thead>
            <tr>
                <th><?= lang('common.name') ?></th>
                <th><?= lang('common.type') ?></th>
                <th><?= lang('common.location_edit') ?></th>
                <th><?= lang('projects.number_of_projects') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($collaborations as $project) { ?>
                <tr>
                    <td>
                        <?= $project['name'] ?>
                    </td>
                    <td>
                        <?= $project['type'] ?? '-' ?>
                    </td>
                    <td>
                        <?= $project['location'] ?? '-' ?>
                    </td>
                    <td>
                        <?= $project['count'] ?? '-' ?>
                        <a onclick="$(this).next().toggle()"><i class="ph ph-magnifying-glass-plus"></i></a>
                        <div class="collaborations-list" style="display: none;">
                            <?= implode(', ', DB::doc2Arr($project['projects'] ?? [])) ?>
                        </div>
                    </td>

                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3>
        <?= lang('projects.cooperation_partners_by_type') ?>
    </h3>

    <?php
    $collaborations_by_type = $osiris->projects->aggregate([
        ['$match' => $filter_collaborations],
        ['$lookup' => [
            'from' => 'organizations',
            'localField' => 'collaborators.organization',
            'foreignField' => '_id',
            'as' => 'collaborators'
        ]],
        ['$project' => [
            'collaborators' => 1,
            '_id' => 0,
        ]],
        ['$unwind' => '$collaborators'],
        // lower case grouping
        ['$addFields' => [
            'collaborators.type' => [
                '$toLower' => '$collaborators.type'
            ]
        ]],
        ['$group' => [
            '_id' => '$collaborators._id',
            'type' => ['$first' => '$collaborators.type']
        ]],
        ['$group' => [
            '_id' => '$type',
            'count' => ['$sum' => 1]
        ]],
        ['$project' => [
            'type' => '$_id',
            'count' => 1
        ]],
        ['$sort' => ['count' => -1]]
    ])->toArray();
    ?>

    <div class="row row-eq-spacing">
        <div class="col-md">

            <table class="table" id="collaborative-partners-by-type">
                <thead>
                    <tr>
                        <th><?= lang('common.type') ?></th>
                        <th><?= lang('projects.number_of_partners') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($collaborations_by_type as $project) {
                    ?>
                        <tr>
                            <td>
                                <?= ucfirst($project['type']) ?>
                            </td>
                            <td>
                                <?= $project['count'] ?>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?= lang('common.total') ?></th>
                        <th><?= $count_collab ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md">
            <!-- Donut chart -->
            <div id="donut-chart" class="box p-5 m-0"></div>

            <script>
                $(document).ready(function() {
                    var rows = <?= json_encode($collaborations_by_type) ?>;
                    var data = [{
                        type: 'pie',
                        values: rows.map(row => row.count),
                        labels: rows.map(row => row.type),
                        textinfo: 'label+percent',
                        insidetextorientation: 'radial',
                        hole: .4,
                        marker: {
                            colors: ['#008084', '#F08113', '#62A330', '#ED6962', '#F0D13C',
                                '#3B6FB6', '#9B59B6', '#5DADE2', '#8E8E38', '#E082AE',
                            ],
                        }
                    }];

                    var layout = {
                        title: {
                            text: <?= json_encode(lang('projects.cooperation_partners_by_type'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                            font: {
                                size: 20
                            }
                        },
                        // showlegend: true,
                        height: 500,
                        width: '100%',
                    };

                    Plotly.newPlot('donut-chart', data, layout);
                });
            </script>
        </div>
    </div>


    <h3>
        <?= lang('projects.cooperation_partners_by_country') ?>
    </h3>

    <?php
    $collaborations_by_country = $osiris->projects->aggregate([
        ['$match' => $filter_collaborations],
        ['$lookup' => [
            'from' => 'organizations',
            'localField' => 'collaborators.organization',
            'foreignField' => '_id',
            'as' => 'collaborators'
        ]],
        ['$project' => [
            'collaborators' => 1,
            '_id' => 0,
        ]],
        ['$unwind' => '$collaborators'],
        ['$group' => [
            '_id' => '$collaborators._id',
            'country' => ['$first' => '$collaborators.country']
        ]],
        ['$group' => [
            '_id' => '$country',
            'count' => ['$sum' => 1]
        ]],
        ['$project' => [
            'iso' => '$_id',
            'count' => 1
        ]],
        ['$sort' => ['iso' => 1]]
    ])->toArray();
    $collaborations_by_country = array_map(function ($project) use ($DB) {
        $country = $DB->getCountry($project['iso']);
        return [
            'iso' => $project['iso'],
            'iso3' => $country['iso3'],
            'count' => $project['count'],
            'country' => lang($country['name'], $country['name_de']),
        ];
    }, $collaborations_by_country);

    ?>

    <div class="row row-eq-spacing">
        <div class="col-md">

            <table class="table" id="collaborative-partners-by-country">
                <thead>
                    <tr>
                        <th><?= lang('common.country') ?></th>
                        <th><?= lang('projects.number_of_partners') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($collaborations_by_country as $project) { ?>
                        <tr>
                            <td>
                                <?= $project['country'] ?>
                            </td>
                            <td>
                                <?= $project['count'] ?>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?= lang('common.total') ?></th>
                        <th><?= $count_collab ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="col-md">
            <div id="map" class="box p-5 m-0"></div>
        </div>
    </div>

    <script>
        function unpack(rows, key) {
            return rows.map(function(row) {
                return row[key];
            });
        }

        var layout = {
            title: {
                text: <?= json_encode(lang('projects.cooperation_partners_by_country'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            },
            geo: {
                projection: {
                    type: 'robinson'
                }
            },
            margin: {
                t: 50,
                b: 10,
                l: 10,
                r: 10
            },
            height: 500,
            width: '100%',
        };

        $(document).ready(function() {
            $('#research-countries-table').DataTable({
                buttons: downloadTableButtons(),
                "order": [
                    [1, "desc"]
                ],
            });
            $('#collaborative-partners').DataTable({
                buttons: downloadTableButtons(),
                "order": [
                    [3, "desc"]
                ],
            });
            $('#collaborative-partners-by-country').DataTable({
                buttons: downloadTableButtons(),
                "order": [
                    [1, "desc"]
                ],
            });
            initDownloadTable('#projects-by-type-table', 'Projects by type, <?= $time_frame ?>');
            initDownloadTable('#proposals-by-type-table', 'Proposals by type, <?= $time_frame ?>');
            initDownloadTable('#collaborative-partners-by-type', 'Collaboration partners by type, <?= $time_frame ?>');


            var collaboratorRows = <?= json_encode($collaborations_by_country) ?>;
            console.log(collaboratorRows);
            var data = [{
                type: 'choropleth',
                locationmode: 'ISO-3',
                locations: unpack(collaboratorRows, 'iso3'),
                z: unpack(collaboratorRows, 'count'),
                text: unpack(collaboratorRows, 'country'),
                autocolorscale: false,
                colorscale: [
                    ['0.0', 'rgb(253.4, 229.8, 204.8)'],
                    ['1.0', '#008084']
                ],
                colorbar: {
                    len: 0.5,
                    title: <?= json_encode(lang('projects.number_ofpartners'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                },
            }];

            Plotly.newPlot("map", data, layout, {
                showLink: false
            });
        });


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


    <?php
    $filter_countries = $filter;
    $filter_countries['research-countries'] = ['$exists' => true];

    if ($osiris->projects->count($filter_countries) > 0) { ?>

        <h3>
            <?= lang('projects.research_in_and_about_countries') ?>
        </h3>

        <?php

        $research_countries = $osiris->projects->aggregate(
            [
                ['$match' => $filter_countries],
                ['$project' => ['research-countries' => 1, '_id' => 0]],
                ['$unwind' => '$research-countries'],
                ['$project' => ['iso' => '$research-countries.iso', 'role' => '$research-countries.role']],
                ['$project' => ['iso' => 1, 'in' => ['$cond' => [['$in' => ['$role', ['in', 'both']]], 1, 0]], 'about' => ['$cond' => [['$in' => ['$role', ['about', 'both']]], 1, 0]]]],
                ['$group' => ['_id' => '$iso', 'research_in' => ['$sum' => '$in'], 'research_about' => ['$sum' => '$about']]],
                ['$project' => ['_id' => 0, 'iso' => '$_id', 'research_in' => 1, 'research_about' => 1]],
                ['$sort' => ['iso' => 1]]
            ]
        )->toArray();

        foreach ($research_countries as &$project) {
            $country = $DB->getCountry($project['iso']);
            if (empty($country) || empty($country['name'])) {
                continue; // Skip if no country found
            }
            $project['iso3'] = $country['iso3'];
            $project['country'] = lang($country['name'], $country['name_de']);
        }
        $research_countries = array_filter($research_countries, function ($project) {
            return !empty($project['country']);
        });
        $research_countries = array_values($research_countries); // Re-index the array

        ?>

        <div class="row row-eq-spacing">
            <div class="col-md">

                <table class="table" id="research-countries-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.country') ?></th>
                            <th><?= lang('projects.research_in_the_country') ?></th>
                            <th><?= lang('projects.research_about_the_country') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $counts_in = array_sum(array_column($research_countries, 'research_in'));
                        $counts_about = array_sum(array_column($research_countries, 'research_about'));
                        foreach ($research_countries as $project) {
                        ?>
                            <tr>
                                <td>
                                    <?= $project['country'] ?>
                                </td>
                                <td>
                                    <?= $project['research_in'] ?>
                                </td>
                                <td>
                                    <?= $project['research_about'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th><?= lang('common.total') ?></th>
                            <th><?= $counts_in ?></th>
                            <th><?= $counts_about ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md">
                <div class="btn-toolbar">
                    <button class="btn" onclick="updateResearchMap('research_in');">
                        <i class="ph ph-globe"></i>
                        <?= lang('projects.research_in_countries') ?>
                    </button>
                    <button class="btn" onclick="updateResearchMap('research_about');">
                        <i class="ph ph-globe"></i>
                        <?= lang('projects.research_about_countries') ?>
                    </button>
                </div>
                <div id="map-research" class="box p-5 m-0"></div>
            </div>
        </div>

        <script>
            let researchCountries = <?= json_encode($research_countries) ?>;
            $(document).ready(function() {
                // research map
                var data = [{
                    type: 'choropleth',
                    locationmode: 'ISO-3',
                    locations: unpack(researchCountries, 'iso3'),
                    z: unpack(researchCountries, 'research_in'),
                    text: unpack(researchCountries, 'country'),
                    autocolorscale: false,
                    colorscale: [
                        ['0.0', 'rgb(253.4, 229.8, 204.8)'],
                        ['1.0', '#008084']
                    ],
                    colorbar: {
                        title: <?= json_encode(lang('projects.research_in'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                        len: 0.5
                    }
                }];
                layout.title = {
                    text: <?= json_encode(lang('projects.research_in_and_about_countries'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                };

                Plotly.newPlot("map-research", data, layout, {
                    showLink: false
                });
            });

            function updateResearchMap(mode) {
                var z = unpack(researchCountries, mode);
                var label = (mode === 'research_in') ? <?= json_encode(lang('projects.research_in'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> : <?= json_encode(lang('projects.research_about'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                console.log(mode);
                Plotly.update("map-research", {
                    z: [z],
                    colorbar: {
                        title: label
                    }
                });
            }
        </script>


    <?php } ?>
</div>