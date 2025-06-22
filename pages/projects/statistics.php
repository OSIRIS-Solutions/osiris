<?php

/**
 * The statistics of all projects
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

include_once BASEPATH . '/php/Project.php';
$Project = new Project();

$phrase = lang('in the reporting year', 'im Reportjahr');
$time_frame = '';

// today is the default reportyear
if (isset($_GET['reportdate']) && !empty($_GET['reportdate'])) {
    $reportdate = $_GET['reportdate'];
    $reportyear = date('Y', strtotime($reportdate));
    $reportstart = date('Y-m-d', strtotime($reportdate));
    $reportend = date('Y-m-d', strtotime($reportdate));
    $phrase = lang('on the reporting date', 'am Stichtag');
    $time_frame = lang('Reporting date', 'Stichtag') . ': ' . $reportdate;
} else if (isset($_GET['reportyear']) && !empty($_GET['reportyear'])) {
    $reportyear = intval($_GET['reportyear']);
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
    $reportdate = date('Y-m-d');
    $time_frame = lang('Reporting year', 'Reportjahr') . ': ' . $reportyear;
} else {
    $reportyear = CURRENTYEAR;
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
    $reportdate = date('Y-m-d');
    $time_frame = lang('Reporting year', 'Reportjahr') . ': ' . $reportyear;
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

<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>

<h1>
    <i class="ph ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('Statistics', 'Statistiken') ?>
</h1>


<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/projects">
        <i class="ph ph-arrow-left"></i>
        <?= lang('Back to projects', 'Zurück zu Projekten') ?>
    </a>
</div>


<div class="alert signal">
    <i class="ph ph-warning text-signal"></i>
    <?= lang('All of the following statistics are based on the selected reporting time.', 'Alle unten aufgeführten Statistiken basieren auf dem angegebenen Zeitraum/punkt.') ?>

    <div class="row position-relative mt-10">
        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/projects/statistics" method="get" class="d-flex align-items-baseline" style="grid-gap: 1rem;">
                <h6 class="mb-0 mt-5"><?= lang('Change Reporting Year', 'Reportjahr ändern') ?>:</h6>
                <input type="number" name="reportyear" value="<?= $reportyear ?>" class="form-control w-auto d-inline-block" step="1" min="1900" max="<?= CURRENTYEAR + 2 ?>" required />
                <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
            </form>
        </div>

        <div class="text-divider"><?= lang('OR', 'ODER') ?></div>

        <div class="col-sm p-10">

            <form action="<?= ROOTPATH ?>/projects/statistics" method="get" class="d-flex align-items-baseline ml-20" style="grid-gap: 1rem;">
                <h6 class="mb-0 mt-5"><?= lang('Change Reporting Date', 'Stichtag ändern') ?>:</h6>
                <input type="date" name="reportdate" value="<?= $reportdate ?>" class="form-control w-auto d-inline-block" required />
                <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
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
        <?= lang('Number of projects', 'Anzahl der Projekte') ?> <?= $phrase ?>:
        <b class="badge signal"><?= count($projects) ?></b>
        <span class="text-muted">(<?= $all ?> <?= lang('total', 'gesamt') ?>)</span>
    </p>

    <h2>
        <?= lang('Number of projects', 'Anzahl der Projekte') ?> <?= $phrase ?>:
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

    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Count', 'Anzahl') ?></th>
                <th><?= lang('Created in time frame', 'Erstellt im Zeitraum') ?></th>
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
                <th colspan="1"><?= lang('Total', 'Gesamt') ?></th>
                <th><?= count($projects) ?></th>
                <th><?= array_sum($projects_created) ?></th>
            </tr>
        </tfoot>
    </table>

    <br>
    <hr>


    <h2>
        <?= lang('Number of proposals', 'Anzahl der Anträge') ?> <?= $phrase ?>:
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
    <table class="table w-auto">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Submitted', 'Eingereicht') ?></th>
                <th><?= lang('Approved', 'Genehmigt') ?></th>
                <th><?= lang('Rejected', 'Abgelehnt') ?></th>
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
                <th colspan="1"><?= lang('Total', 'Gesamt') ?></th>
                <th><?= array_sum(array_column($table, 'submitted')) ?></th>
                <th><?= array_sum(array_column($table, 'approved')) ?></th>
                <th><?= array_sum(array_column($table, 'rejected')) ?></th>
            </tr>
    </table>

    <p class="text-muted">
        <i class="ph ph-info"></i>
        <?= lang('The list shows the applications that had the respective status timestamp in the report period. For example, "Submitted" includes all applications that were submitted during the reporting period, regardless of whether they have already been approved or rejected. If they were approved or rejected in the period, they are also listed in the respective column.', 'In der Aufstellung sind jeweils die Anträge zu sehen, die den jeweiligen Status-Zeitstempel im Reportzeitraum hatten. Beispielsweise sind "Eingereicht" alle Anträge, die im Reportzeitraum eingereicht wurden, unabhängig davon, ob sie bereits genehmigt oder abgelehnt wurden. Wenn sie im Zeitraum genehmigt oder abgelehnt wurden, sind sie in der jeweiligen Spalte ebenfalls aufgeführt.') ?>
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
        <?= lang('Cooperation partners', 'Kooperationspartner') ?>
        (<?= $count_collab ?>)
    </h2>

    <table class="table" id="collaborative-partners">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Location', 'Standort') ?></th>
                <th><?= lang('Number of projects', 'Anzahl der Projekte') ?></th>
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
        <?= lang('Cooperation partners by type', 'Kooperationspartner nach Typ') ?>
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
                        <th><?= lang('Type', 'Typ') ?></th>
                        <th><?= lang('Number of partners', 'Anzahl der Partner') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($collaborations_by_type as $project) {
                    ?>
                        <tr>
                            <td>
                                <?= $project['type'] ?>
                            </td>
                            <td>
                                <?= $project['count'] ?>
                            </td>

                        </tr>
                    <?php } ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th><?= lang('Total', 'Gesamt') ?></th>
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
                            text: lang('Cooperation partners by type', 'Kooperationspartner nach Typ'),
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
        <?= lang('Cooperation partners by country', 'Kooperationspartner nach Land') ?>
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
                        <th><?= lang('Country', 'Land') ?></th>
                        <th><?= lang('Number of partners', 'Anzahl der Partner') ?></th>
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
                        <th><?= lang('Total', 'Gesamt') ?></th>
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
                text: lang('Cooperation partners by country', 'Kooperationspartner nach Land'),
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
                "order": [
                    [1, "desc"]
                ],
            });
            $('#collaborative-partners').DataTable({
                "order": [
                    [3, "desc"]
                ],
            });
            $('#collaborative-partners-by-country').DataTable({
                "order": [
                    [1, "desc"]
                ],
            });

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
            <?= lang('Research in and about countries', 'Forschung in und über Länder') ?>
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
                            <th><?= lang('Country', 'Land') ?></th>
                            <th><?= lang('Research in the country', 'Forschung in dem Land') ?></th>
                            <th><?= lang('Research about the country', 'Forschung über das Land') ?></th>
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
                            <th><?= lang('Total', 'Gesamt') ?></th>
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
                        <?= lang('Research in countries', 'Forschung in Ländern') ?>
                    </button>
                    <button class="btn" onclick="updateResearchMap('research_about');">
                        <i class="ph ph-globe"></i>
                        <?= lang('Research about countries', 'Forschung über Länder') ?>
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
                        title: lang('Research in', 'Forschung in')
                    }
                }];
                layout.title = {
                    text: lang('Research in and about countries', 'Forschung in und über Länder'),
                };

                Plotly.newPlot("map-research", data, layout, {
                    showLink: false
                });
            });

            function updateResearchMap(mode) {
                var z = unpack(researchCountries, mode);
                var label = (mode === 'research_in') ? lang('Research in', 'Forschung in') : lang('Research about', 'Forschung über');
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