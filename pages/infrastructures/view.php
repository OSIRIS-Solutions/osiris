<?php

/**
 * The detail view of an infrastructure
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

$user_infrastructure = false;
$user_role = null;
$reporter = false;
$persons = DB::doc2Arr($infrastructure['persons'] ?? array());
foreach ($persons as $p) {
    if (strval($p['user']) == $_SESSION['username']) {
        $user_infrastructure = True;
        $user_role = $p['role'];
        $reporter = $p['reporter'] ?? false;
        break;
    }
}
$edit_perm = ($infrastructure['created_by'] == $_SESSION['username'] || $Settings->hasPermission('infrastructures.edit') || $reporter);

?>
<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>

<style>
    .inactive {
        color: var(--muted-color);
        opacity: 0.7;
        transition: opacity 0.3s, color 0.3s;

    }

    .inactive:hover {
        opacity: 1;
        color: var(--text-color);
    }
</style>

<div class="infrastructure">

    <h1 class="title">
        <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
    </h1>

    <h2 class="subtitle">
        <?= lang($infrastructure['subtitle'], $infrastructure['subtitle_de'] ?? null) ?>
    </h2>

    <p class="font-size-12 text-muted">
        <?= get_preview($infrastructure['description'], 400) ?>
    </p>

    <div class="d-flex mb-20 align-items-center">
        <div class="mr-10 badge bg-white">
            <small>ID: </small>
            <br />
            <span class="badge"><?= $infrastructure['id'] ?? '-' ?></span>
        </div>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Start date', 'Anfangsdatum') ?>: </small>
            <br />
            <span class="badge"><?= format_date($infrastructure['start_date']) ?></span>
        </div>
        <div class="mr-10 badge bg-white">
            <small><?= lang('End date', 'Enddatum') ?>: </small>
            <br />
            <?php if (!empty($infrastructure['end_date'])) {
                echo '<span class="badge signal">' . format_date($infrastructure['end_date']) . '</span>';
            } else {
                echo '<span class="badge primary">' . lang('Open', 'Offen') . '</span>';
            } ?>
        </div>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Type', 'Typ') ?>: </small>
            <br />
            <span class="badge"><?= $infrastructure['type'] ?? '-' ?></span>
        </div>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Type of infrastructure', 'Art der Infrastruktur') ?>: </small>
            <br />
            <span class="badge"><?= $infrastructure['infrastructure_type'] ?? '-' ?></span>
        </div>
        <div class="mr-10 badge bg-white">
            <small><?= lang('User Access', 'Art des Zugangs') ?>: </small>
            <br />
            <span class="badge"><?= $infrastructure['access'] ?? '-' ?></span>
        </div>
        <?php if ($Settings->hasPermission('infrastructures.edit')) { ?>
            <a href="<?= ROOTPATH ?>/infrastructures/edit/<?= $infrastructure['_id'] ?>" class="btn h-full">
                <i class="ph ph-edit"></i>
                <span class="sr-only"><?= lang('Edit', 'Bearbeiten') ?></span>
            </a>
        <?php } ?>

    </div>

    <?php if ($infrastructure['collaborative'] ?? false) { ?>
        <div class="p-10 rounded" style="background-color: var(--success-color-20); border: 1px solid var(--success-color);">
            <h5 class="m-0 text-success">
                <i class="ph ph-handshake"></i>
                <?= lang('Collaborative research infrastructure', 'Verbundforschungsinfrastruktur') ?>
            </h5>

            <p>
                <b><?= lang('Coordinator', 'Koordinator-Einrichtung') ?>:</b><br>
                <?php if ($infrastructure['coordinator_institute']) { ?>
                    <b class="badge success filled"><?= $Settings->get('affiliation') ?></b>
                <?php } else {
                    $org = $osiris->organizations->findOne(['_id' => $infrastructure['coordinator_organization']]);
                ?>
                    <a class="badge success" href="<?= ROOTPATH ?>/organizations/view/<?= $org['_id'] ?>">
                        <b><?= $org['name'] ?? '-' ?></b>
                        (<?= $org['location'] ?>)
                    </a>
                <?php } ?>
            </p>

            <p class="mb-10">
                <b><?= lang('Partner organizations', 'Partnerorganisationen') ?>:</b><br>
                <?php if (!empty($infrastructure['collaborators'])) {
                    foreach ($infrastructure['collaborators'] as $org) {
                        $org = $osiris->organizations->findOne(['_id' => $org]);
                        if ($org) {
                            echo '<a class="badge mr-10" href="'.ROOTPATH.'/organizations/view/' . $org['_id'] . '"><b>' . $org['name'] . '</b> (' . $org['location'] . ')</a>';
                        }
                    }
                } else { ?>
                    <span class="badge secondary"><?= lang('No partner organizations', 'Keine Partnerorganisationen') ?></span>
                <?php } ?>
            </p>
        </div>
    <?php } ?>
</div>

<hr>

<h2>
    <?= lang('Operating personnel', 'Betriebspersonal') ?>
    <?php if ($edit_perm) { ?>
        <a href="<?= ROOTPATH ?>/infrastructures/persons/<?= $id ?>" class="font-size-16">
            <i class="ph ph-edit"></i>
            <span class="sr-only"><?= lang('Edit', 'Bearbeiten') ?></span>
        </a>
    <?php } ?>
</h2>


<div class="row row-eq-spacing mb-0">

    <?php
    if (empty($persons)) {
    ?>
        <div class="col-md-6">
            <div class="alert secondary mb-20">
                <?= lang('No persons connected.', 'Keine Personen verknüpft.') ?>
            </div>
        </div>
    <?php
    } else foreach ($persons as $person) {
        if (empty($person['user'])) {
            continue;
        }
        $username = strval($person['user']);
        $past = '';
        if ($person['end'] && strtotime($person['end']) < time()) {
            $past = 'inactive';
        }
    ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="d-flex align-items-center box p-10 mt-0 <?= $past ?>">

                <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                <div>
                    <h5 class="my-0">
                        <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                            <?= $person['name'] ?? $username ?>
                        </a>
                    </h5>
                    <?= $Infra->getRole($person['role'] ?? '') ?>
                    <?php if ($person['reporter'] ?? false) { ?>
                        <span class="primary ml-5" data-toggle="tooltip" data-title="<?= lang('Reporter', 'Berichterstatter') ?>">
                            <i class="ph ph-clipboard-text"></i>
                        </span>
                    <?php } ?>
                    <br>

                    <?= fromToYear($person['start'], $person['end'] ?? null, true) ?>

                </div>
            </div>
        </div>
    <?php
    } ?>

</div>

<hr>

<h2>
    <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
</h2>

<small>
    <?= lang('You can connect an activity to an infrastructure on the activity page itself.', 'Du kannst eine Aktivität auf der Aktivitätsseite mit einer Infrastruktur verbinden.') ?>
</small>

<div class="mt-20 w-full">
    <table class="table dataTable responsive" id="activities-table">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Activity', 'Aktivität') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<script>
    $('#activities-table').DataTable({
        "ajax": {
            "url": ROOTPATH + '/api/all-activities',
            "data": {
                page: 'activities',
                display_activities: 'web',
                filter: {
                    'infrastructures': '<?= $infrastructure['id'] ?>'
                }
            },
            dataSrc: 'data'
        },
        deferRender: true,
        pageLength: 5,
        columnDefs: [{
                targets: 0,
                data: 'icon',
                // className: 'w-50'
            },
            {
                targets: 1,
                data: 'activity'
            },
            {
                targets: 2,
                data: 'links',
                className: 'unbreakable'
            },
            {
                targets: 3,
                data: 'search-text',
                searchable: true,
                visible: false,
            },
            {
                targets: 4,
                data: 'start',
                searchable: true,
                visible: false,
            },
        ],
        "order": [
            [4, 'desc'],
            // [0, 'asc']
        ]
    });
</script>


<hr>

<h2>
    <?= lang('Statistics', 'Statistiken') ?>
</h2>

<?php

$statistics = DB::doc2Arr($infrastructure['statistics'] ?? []);
if (!empty($statistics)) {
    usort($statistics, function ($a, $b) {
        return $a['year'] <=> $b['year'];
    });
    $years = array_column((array) $statistics, 'year');
}
?>

<?php if ($reporter || $Settings->hasPermission('infrastructures.statistics')) { ?>
    <form action="<?= ROOTPATH ?>/infrastructures/year/<?= $infrastructure['_id'] ?>" method="get" class="d-inline">
        <div class="input-group w-auto d-inline-flex">
            <input type="number" class="form-control w-100" placeholder="Year" name="year" required step="1" min="1900" max="<?= CURRENTYEAR + 1 ?>" value="<?= CURRENTYEAR - 1 ?>">
            <div class="input-group-append">
                <button class="btn">
                    <i class="ph ph-calendar-plus"></i>
                    <?= lang('Edit year statistics', 'Jahresstatistik bearbeiten') ?>
                </button>
            </div>
        </div>
    </form>
<?php } ?>


<?php if (empty($statistics)) { ?>
    <div class="alert secondary my-20 w-md-half">
        <?= lang('No persons connected.', 'Keine Personen verknüpft.') ?>
    </div>
<?php } else { ?>
    <div class="box padded mb-0">
        <h5 class="title font-size-16">
            <?= lang('Number of users by year', 'Anzahl der Nutzer/-innen nach Jahr') ?>
        </h5>
        <canvas id="chart-users" style="height: 30rem; max-height:30rem;"></canvas>
    </div>

    <script>
        var barChartConfig = {
            type: 'bar',
            data: [],
            options: {
                plugins: {
                    title: {
                        display: false,
                        text: 'Chart'
                    },
                },
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                    }
                }
            },

        };
        var ctx = document.getElementById('chart-users')
        var data = Object.assign({}, barChartConfig)
        var raw_data = Object.values(<?= json_encode($statistics) ?>);
        console.log(raw_data);
        data.data = {
            labels: <?= json_encode($years) ?>,
            datasets: [{
                    label: 'Internal users',
                    data: raw_data,
                    parsing: {
                        yAxisKey: 'internal',
                        xAxisKey: 'year'
                    },
                    backgroundColor: 'rgba(236, 175, 0, 0.7)',
                    borderColor: 'rgba(236, 175, 0, 1)',
                    borderWidth: 3
                },
                {
                    label: 'National users',
                    data: raw_data,
                    parsing: {
                        yAxisKey: 'national',
                        xAxisKey: 'year'
                    },
                    backgroundColor: 'rgba(247, 129, 4, 0.7)',
                    borderColor: 'rgba(247, 129, 4, 1)',
                    borderWidth: 3
                },
                {
                    label: 'International users',
                    data: raw_data,
                    parsing: {
                        yAxisKey: 'international',
                        xAxisKey: 'year'
                    },
                    backgroundColor: 'rgba(233, 87, 9, 0.7)',
                    borderColor: 'rgba(233, 87, 9, 1)',
                    borderWidth: 3
                },
            ],
        }


        console.log(data);
        var myChart = new Chart(ctx, data);
    </script>

    <div class="row row-eq-spacing mt-0">
        <div class="col-md-6">
            <div class="box padded">
                <h5 class="title font-size-16">
                    <?= lang('Number of hours by year', 'Anzahl der Stunden nach Jahr') ?>
                </h5>
                <canvas id="chart-hours" style="height: 30rem; max-height:30rem;"></canvas>
            </div>

            <script>
                var lineChartConfig = {
                    type: 'line',
                    data: [],
                    options: {
                        plugins: {
                            title: {
                                display: false,
                                text: 'Chart'
                            },
                            legend: {
                                display: false,
                            }
                        },
                        responsive: true,
                        scales: {
                            y: {
                                min: 0
                            }
                        }
                    },

                };
                var ctx = document.getElementById('chart-hours')
                var data = Object.assign({}, lineChartConfig)
                var raw_data = Object.values(<?= json_encode($statistics) ?>);
                console.log(raw_data);
                data.data = {
                    labels: <?= json_encode($years) ?>,
                    datasets: [{
                        label: 'Hours',
                        data: raw_data,
                        parsing: {
                            yAxisKey: 'hours',
                            xAxisKey: 'year'
                        },
                        backgroundColor: 'rgba(247, 129, 4, 0.7)',
                        borderColor: 'rgba(247, 129, 4, 1)',
                        borderWidth: 3
                    }, ],
                }

                var hoursChart = new Chart(ctx, data);
            </script>
        </div>
        <div class="col-md-6">


            <div class="box padded">
                <h5 class="title font-size-16">
                    <?= lang('Number of accesses by year', 'Anzahl der Zugriffe nach Jahr') ?>
                </h5>
                <canvas id="chart-accesses" style="height: 30rem; max-height:30rem;"></canvas>

            </div>

            <script>
                var lineChartConfig = {
                    type: 'line',
                    data: [],
                    options: {
                        plugins: {
                            title: {
                                display: false,
                                text: 'Chart'
                            },
                            legend: {
                                display: false,
                            }
                        },
                        responsive: true,
                        scales: {
                            y: {
                                min: 0
                            }
                        }
                    },

                };
                var ctx = document.getElementById('chart-accesses')
                var data = Object.assign({}, lineChartConfig)
                var raw_data = Object.values(<?= json_encode($statistics) ?>);
                console.log(raw_data);
                data.data = {
                    labels: <?= json_encode($years) ?>,
                    datasets: [{
                        label: 'Accesses',
                        data: raw_data,
                        parsing: {
                            yAxisKey: 'accesses',
                            xAxisKey: 'year'
                        },
                        backgroundColor: 'rgba(233, 87, 9, 0.7)',
                        borderColor: 'rgba(233, 87, 9, 1)',
                        borderWidth: 3
                    }, ],
                }

                var accessesChart = new Chart(ctx, data);
            </script>

        </div>
    </div>
<?php
}
?>


<?php if ($Settings->hasPermission('infrastructures.delete')) { ?>

    <button class="btn danger" type="button" id="delete-infrastructure" aria-haspopup="true" aria-expanded="false" onclick="$(this).next().slideToggle()">
        <i class="ph ph-trash"></i>
        <?= lang('Delete', 'Löschen') ?>
        <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
    </button>
    <div aria-labelledby="delete-infrastructure" style="display: none;">
        <div class="my-20">
            <b class="text-danger"><?= lang('Attention', 'Achtung') ?>!</b><br>
            <small>
                <?= lang(
                    'The infrastructure is permanently deleted and the connection to all associated persons and activities is also removed. This cannot be undone.',
                    'Die Infrastruktur wird permanent gelöscht und auch die Verbindung zu allen zugehörigen Personen und Aktivitäten entfernt. Dies kann nicht rückgängig gemacht werden.'
                ) ?>
            </small>
            <form action="<?= ROOTPATH ?>/crud/infrastructures/delete/<?= $infrastructure['_id'] ?>" method="post">
                <button class="btn btn-block danger" type="submit"><?= lang('Delete permanently', 'Permanent löschen') ?></button>
            </form>
        </div>
    </div>
<?php } ?>


<?php if (isset($_GET['verbose'])) {
    dump($infrastructure, true);
} ?>