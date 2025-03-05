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
$persons = DB::doc2Arr($infrastructure['persons'] ?? array());
foreach ($persons as $p) {
    if (strval($p['user']) == $_SESSION['username']) {
        $user_infrastructure = True;
        $user_role = $p['role'];
        break;
    }
}
$edit_perm = ($infrastructure['created_by'] == $_SESSION['username'] || $Settings->hasPermission('infrastructures.edit') || $user_project);

?>
<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>

<div class="infrastructure">

    <?php if ($Settings->hasPermission('infrastructures.edit')) { ?>
        <a href="<?= ROOTPATH ?>/infrastructures/edit/<?= $infrastructure['_id'] ?>" class="btn">
            <i class="ph ph-edit"></i>
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>
    <?php } ?>


    <h1 class="title">
        <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
    </h1>

    <h2 class="subtitle">
        <?= lang($infrastructure['subtitle'], $infrastructure['subtitle_de'] ?? null) ?>
    </h2>

    <p class="font-size-12 text-muted">
        <?= get_preview($infrastructure['description'], 400) ?>
    </p>

    <div class="d-flex mb-20">
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
    </div>
</div>

<hr>


<h2>
    <?= lang('Operating personnel', 'Betriebspersonal') ?>
</h2>

<?php if ($edit_perm) { ?>
    <div class="modal" id="persons" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="modal-title">
                    <?= lang('Connect persons', 'Personen verknüpfen') ?>
                </h5>
                <div>
                    <form action="<?= ROOTPATH ?>/crud/infrastructures/update-persons/<?= $id ?>" method="post">

                        <table class="table simple">
                            <thead>
                                <tr>
                                    <th><?= lang('Person', 'Person') ?></th>
                                    <th><?= lang('Role', 'Rolle') ?></th>
                                    <th><?= lang('Reporter*') ?></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="infrastructure-list">
                                <?php
                                if (empty($persons)) {
                                    $persons = [
                                        ['user' => '', 'role' => '']
                                    ];
                                }
                                $all_users = $osiris->persons->find(['username' => ['$ne' => null], 'last' => ['$ne' => '']], ['sort' => ['last' => 1]])->toArray();
                                foreach ($persons as $i => $con) { ?>
                                    <tr>
                                        <td class="">
                                            <select name="persons[<?= $i ?>][user]" id="persons-<?= $i ?>-user" class="form-control">
                                                <?php
                                                foreach ($all_users as $s) { ?>
                                                    <option value="<?= $s['username'] ?>" <?= ($con['user'] == $s['username'] ? 'selected' : '') ?>>
                                                        <?= "$s[last], $s[first] ($s[username])" ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="persons[<?= $i ?>][role]" id="persons-<?= $i ?>-role" class="form-control">
                                                <?php foreach ($Infra->roles as $role_id => $role) { ?>
                                                    <option value="<?= $role_id ?>" <?= ($con['role'] == $role_id ? 'selected' : '') ?>>
                                                        <?= $role ?>
                                                    </option>
                                                <?php } ?>

                                            </select>
                                        </td>
                                        <td>
                                            <select name="persons[<?= $i ?>][reporter]" id="persons-<?= $i ?>-reporter" class="form-control">
                                                <option value="0" <?= ($con['reporter'] == 0 ? 'selected' : '') ?>><?= lang('No', 'Nein') ?></option>
                                                <option value="1" <?= ($con['reporter'] == 1 ? 'selected' : '') ?>><?= lang('Yes', 'Ja') ?></option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr id="last-row">
                                    <td colspan="4">
                                        <button class="btn" type="button" onclick="addInfrastructureRow()"><i class="ph ph-plus"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                                    </td>
                                </tr>
                            </tfoot>

                        </table>

                        <small>
                            * <?= lang('Reporter are responsible for updating the statistics and will be asked by the system to do so once a year.', 'Die Berichterstatter sind für die Aktualisierung der Statistiken verantwortlich und werden vom System einmal im Jahr dazu aufgefordert.') ?>
                        </small>

                        <button class="btn primary mt-20">
                            <i class="ph ph-check"></i>
                            <?= lang('Submit', 'Bestätigen') ?>
                        </button>
                    </form>

                    <script>
                        var counter = <?= $i ?? 0 ?>;
                        const tr = $('#infrastructure-list tr').first()

                        function addInfrastructureRow() {
                            counter++;
                            const row = tr.clone()
                            row.find('select').each(function() {
                                const name = $(this).attr('name').replace(/\d+/, counter)
                                $(this).attr('name', name)
                            })
                            $('#infrastructure-list').append(row)
                        }
                    </script>

                </div>
            </div>
        </div>
    </div>
<?php } ?>

<div class="btn-toolbar">
    <?php if ($edit_perm) { ?>
        <a href="#persons" class="btn primary">
            <i class="ph ph-edit"></i>
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>
    <?php } ?>
</div>

<div class="row row-eq-spacing mb-0">

    <?php
    if (empty($persons)) {
    ?>
        <div class="col-md-6">
            <div class="alert secondary">
                <?= lang('No persons connected.', 'Keine Personen verknüpft.') ?>
            </div>
        </div>
    <?php
    } else foreach ($persons as $person) {
        if (empty($person['user'])) {
            continue;
        }
        $username = strval($person['user']);
    ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="d-flex align-items-center box p-10 mt-0">

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

                </div>
            </div>
        </div>
    <?php
    } ?>

</div>


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

<?php

if (empty($statistics)) {
    echo lang('No statistics available', 'Keine Statistiken verfügbar');
} else {
?>
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