<?php

/**
 * Page to see a journal
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal/view/<journal_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$label = $Settings->journalLabel();
?>

<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>

<h2 class="mt-0">
    <i class="ph-duotone ph-stack"></i>
    <?= $data['journal'] ?>
</h2>
<div class="btn-toolbar mb-20">
    <?php if ($Settings->hasPermission('journals.edit')) { ?>
        <a href="<?= ROOTPATH ?>/journal/edit/<?= $id ?>" class="btn primary">
            <i class="ph ph-edit"></i>
            <?= lang("Edit $label", " $label bearbeiten") ?>
        </a>
    <?php } ?>

    <?php if ($Settings->hasPermission('journals.edit') && !$Settings->featureEnabled('no-journal-metrics')) { ?>

        <?php if (count($data['issn'] ?? []) == 0) { ?>
            <a href="#/" class="btn disabled" data-toggle="tooltip" data-direction="bottom" data-title="<?= lang('No metrics without ISSN.', 'Keine Metriken ohne ISSN.') ?>">
                <i class="ph ph-ranking"></i> <?= lang('Update Metrics', 'Metriken aktualisieren') ?>
            </a>
        <?php } else { ?>
            <a href="#metrics-modal" class="btn primary">
                <i class="ph ph-ranking"></i> <?= lang('Update Metrics', 'Metriken aktualisieren') ?>
            </a>

            <div class="modal" id="metrics-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <a href="#/" class="close" role="button" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                        <h5 class="title"><?= lang('Update metrics', 'Metriken aktualisieren') ?></h5>
                        <p>
                            <i class="ph ph-warning text-signal"></i>
                            <?= lang("This will update the metrics for this $label and overwrite all manual changes to impact factors, categories and quartiles.", "Dadurch werden die Metriken für diese $label aktualisiert und alle manuellen Änderungen an Impact-Faktoren, Kategorien und Quartilen überschrieben.") ?>
                        </p>

                        <form action="<?= ROOTPATH ?>/crud/journal/update-metrics/<?= $id ?>" method="post">
                            <button class="btn primary"><i class="ph ph-arrows-clockwise"></i> <?= lang('Update Metrics', 'Metriken aktualisieren') ?></button>
                        </form>
                    </div>
                </div>
            </div>

        <?php } ?>
    <?php } ?>
</div>


<table class="table" id="result-table">
    <tr>
        <td>ID</td>
        <td><?= $data['_id'] ?></td>
    </tr>
    <tr>
        <td><?= $label ?></td>
        <td><?= $data['journal'] ?></td>
    </tr>
    <tr>
        <td><?= lang('Abbreviated', 'Abgekürzt') ?></td>
        <td><?= $data['abbr'] ?></td>
    </tr>
    <tr>
        <td>Publisher</td>
        <td><?= $data['publisher'] ?? '' ?></td>
    </tr>
    <tr>
        <td>ISSN</td>
        <td><?= implode('<br>', DB::doc2Arr($data['issn'])) ?></td>
    </tr>
    <tr>
        <td>Open Access</td>
        <td>
            <?php
            if (!($data['oa'] ?? false)) {
                echo lang('No', 'Nein');
            } elseif ($data['oa'] > 1900) {
                echo lang('since ', 'seit ') . $data['oa'];
            } else {
                echo lang('Yes', 'Ja');
            }
            ?>
        </td>
    </tr>
    <?php if (isset($data['wos'])) { ?>
        <tr>
            <td>Web of Science Links</td>
            <td>
                <?php foreach ($data['wos']['links'] as $link) { ?>
                    <a href="<?= $link['url'] ?>" target="_blank" rel="noopener noreferrer" class="badge secondary"><?= $link['type'] ?></a>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td>
            <?= lang('Catergories', 'Kategorien') ?>
            <?php if ($Settings->hasPermission('journals.edit')) { ?>

                <a aria-haspopup="true" aria-expanded="false" href="#cat-modal" data-toggle="modal">
                    <i class="ph ph-edit"></i>
                </a>
            <?php } ?>

        </td>
        <td>
            <?php
            $categories = $data['categories'] ?? [];
            if (empty($categories)) {
                echo lang('No categories available.', 'Keine Kategorien verfügbar.');
            } else {
                echo '<ul class="list">';
                foreach ($categories as $cat) { ?>
                    <li>
                        <?= $cat['name'] ?? $cat ?>
                    </li>
            <?php
                }
                echo '</ul>';
            }
            ?>
        </td>
    </tr>
</table>

<?php
if ($Settings->hasPermission('journals.edit')) { ?>

    <div class="modal" id="cat-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#/" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title"><?= lang("Edit $label categories", "$label-Kategorien bearbeiten") ?></h5>

                <form action="<?= ROOTPATH ?>/crud/journal/update/<?= $id ?>" method="post">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                    <div id="category-form">
                        <?php if (empty($categories)) { ?>
                            <input type="text" class="form-control" name="values[categories][]" id="categories" placeholder="<?= lang('Category', 'Kategorie') ?>" required list="categories-list">
                        <?php } else { ?>
                            <?php foreach ($categories as $cat) { ?>
                                <div class="input-group mb-10">
                                    <input type="text" class="form-control" name="values[categories][][name]" id="categories" placeholder="<?= lang('Category', 'Kategorie') ?>" required list="categories-list" value="<?= $cat['name'] ?? $cat ?>">
                                    <div class="input-group-append">
                                        <button type="button" class="btn" onclick="$(this).closest('.input-group').remove()"><i class="ph ph-trash"></i></button>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <button type="button" class="btn" id="add-category" onclick="addCategory()"><i class="ph ph-plus"></i></button>
                    <br><br>
                    <button class="btn primary"><i class="ph ph-floppy-disk"></i> <?= lang('Save', 'Speichern') ?></button>
                </form>
                <datalist id="categories-list">
                    <?php foreach ($osiris->journals->distinct('categories.name') as $cat) { ?>
                        <option value="<?= $cat ?>"><?= $cat ?></option>
                    <?php } ?>
                </datalist>

                <script>
                    function addCategory() {
                        var input = `<div class="input-group mb-10">
                    <input type="text" class="form-control" name="values[categories][][name]" id="categories" placeholder="<?= lang('Category', 'Kategorie') ?>" required list="categories-list">
                    <div class="input-group-append">
                        <button type="button" class="btn" onclick="$(this).closest('.input-group').remove()"><i class="ph ph-trash"></i></button>
                    </div>
                </div>`;
                        $('#category-form').append(input);
                    }
                </script>
            </div>
        </div>
    </div>

<?php }
?>


<h3>
    <?= lang("Connected Publications", "Verknüpfte Publikationen") ?>
</h3>

<!-- <canvas id="spark"></canvas> -->

<table class="table" id="publication-table">
    <thead>
        <th><?= lang('Year', 'Jahr') ?></th>
        <th><?= lang('Publication', 'Publikation') ?></th>
        <th>Link</th>
    </thead>
    <tbody>
    </tbody>
</table>
<script>
    var dataTable;

    $(document).ready(function() {
        $('#publication-table').DataTable({
            ajax: {
                "url": ROOTPATH + '/api/activities',
                "data": {
                    "filter": {
                        journal_id: '<?= $id ?>',
                        type: 'publication'
                    },
                    formatted: true
                }
            },
            language: {
                "zeroRecords": "No matching records found",
                "emptyTable": lang("No publications available for this <?= $label ?>.", "Für dieses <?= $label ?> sind noch keine Publikationen verfügbar."),
            },
            "pageLength": 5,
            columnDefs: [{
                    targets: 0,
                    data: 'year'
                },
                {
                    targets: 1,
                    data: 'activity'
                },
                {
                    "targets": 2,
                    "data": "name",
                    "render": function(data, type, full, meta) {
                        return `<a href="${ROOTPATH}/activities/view/${full.id}"><i class="ph ph-arrow-fat-line-right"></a>`;
                    }
                },
            ],
            "order": [
                [0, 'desc'],
            ],
            <?php if (isset($_GET['q'])) { ?> "oSearch": {
                    "sSearch": "<?= $_GET['q'] ?>"
                }
            <?php } ?>
        });
    });
</script>


<h3>
    <?= lang('Peer-Reviews & Editorial board memberships', 'Peer-Reviews & Mitglieder des Editorial Board') ?>
</h3>

<table class="table" id="review-table">
    <thead>
        <th>Name</th>
        <th><?= lang('Reviewer count', 'Anzahl Reviews') ?></th>
        <th><?= lang('Editor activity', 'Editoren-Tätigkeit') ?></th>
    </thead>
    <tbody>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#review-table').DataTable({
            ajax: {
                "url": ROOTPATH + '/api/reviews',
                "data": {
                    "filter": {
                        journal_id: '<?= $id ?>'
                    }
                }
            },
            language: {
                "zeroRecords": "No matching records found",
                "emptyTable": lang("No reviews/editorials available for this <?= $label ?>.", "Für dieses <?= $label ?> sind noch keine Reviews/Editorials verfügbar."),
            },
            "pageLength": 5,
            columnDefs: [{
                    "targets": 2,
                    "data": "Editor",
                    "render": function(data, type, full, meta) {
                        var res = []
                        full.Editorials.forEach(el => {
                            res.push(`${el.date} (${el.details == '' ? 'Editor' : el.details})`)
                        });
                        return res.join('<br>');
                    }
                },
                {
                    targets: 1,
                    data: 'Reviewer'
                },
                {
                    "targets": 0,
                    "data": "Name",
                    "render": function(data, type, full, meta) {
                        return `<a href="${ROOTPATH}/profile/${full.User}">${data}</a>`;
                    }
                },
            ],
            "order": [
                [1, 'desc'],
            ],
        });
    });
</script>



<h3><?= lang('Impact factors', 'Impact-Faktoren') ?></h3>
<?php
$impacts = DB::doc2Arr($data['impact'] ?? array());
?>

<div class="box">
    <div class="content">

        <?php if ($Settings->hasPermission('journals.edit')) { ?>
            <div class="dropdown with-arrow float-right mb-20">
                <button class="btn osiris" data-toggle="dropdown" type="button" id="dropdown-2" aria-haspopup="true" aria-expanded="false">
                    <?= lang('Add IF', 'Füge IF hinzu') ?> <i class="ph ph-duotone ph-angle-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-2">
                    <div class="content">
                        <form action="<?= ROOTPATH ?>/crud/journal/update/<?= $id ?>" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <div class="form-group">
                                <label for="year"><?= lang('Year', 'Jahr') ?></label>
                                <input type="number" min="1970" max="<?= CURRENTYEAR ?>" step="1" class="form-control" name="values[year]" id="year" value="<?= CURRENTYEAR - 1 ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="if"><?= lang('Impact') ?></label>
                                <input type="number" min="0" max="300" step="0.001" class="form-control" name="values[if]" id="if">
                            </div>
                            <button class="btn block"><i class="ph ph-check"></i> <?= lang('Add', 'Hinzuf.') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>


        <?php
        if (!empty($impacts)) {
            sort($impacts);
            $years = array_column((array) $impacts, 'year');
        ?>
            <canvas id="chart-if" style="max-height: 400px;"></canvas>

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
                            legend: {
                                display: false,
                            }
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
                var ctx = document.getElementById('chart-if')
                var data = Object.assign({}, barChartConfig)
                var raw_data = Object.values(<?= json_encode($impacts) ?>);
                console.log(raw_data);
                data.data = {
                    labels: <?= json_encode($years) ?>,
                    datasets: [{
                        label: 'Impact factor',
                        data: raw_data,
                        parsing: {
                            yAxisKey: 'impact',
                            xAxisKey: 'year'
                        },
                        backgroundColor: 'rgba(236, 175, 0, 0.7)',
                        borderColor: 'rgba(236, 175, 0, 1)',
                        borderWidth: 3
                    }, ],
                }


                console.log(data);
                var myChart = new Chart(ctx, data);
            </script>
        <?php } else { ?>
            <p><?= lang('No impact factors available.', 'Keine Impact Faktoren verfügbar.') ?></p>
        <?php } ?>


    </div>
</div>




<h3><?= lang('Quartiles', 'Quartile') ?></h3>
<?php
$metrics = DB::doc2Arr($data['metrics'] ?? array());
$quartiles = [];
foreach ($metrics as $metric) {
    if (isset($metric['quartile'])) {
        $quartiles[] = [
            'year' => $metric['year'],
            'quartile' => $metric['quartile'],
            // 'quartile' => str_replace('Q', '', $metric['quartile'])
        ];
    }
}
// $quartiles = array_column($metrics, 'quartile', 'year');
?>

<div class="box">
    <div class="content">

        <?php if ($Settings->hasPermission('journals.edit')) { ?>
            <div class="dropdown with-arrow float-right mb-20">
                <button class="btn osiris" data-toggle="dropdown" type="button" id="dropdown-2" aria-haspopup="true" aria-expanded="false">
                    <?= lang('Add quartile', 'Füge Quartil hinzu') ?> <i class="ph ph-duotone ph-angle-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-2">
                    <div class="content">
                        <form action="<?= ROOTPATH ?>/crud/journal/update/<?= $id ?>" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <div class="form-group">
                                <label for="year"><?= lang('Year', 'Jahr') ?></label>
                                <input type="number" min="1970" max="<?= CURRENTYEAR ?>" step="1" class="form-control" name="values[year]" id="year" value="<?= CURRENTYEAR - 1 ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="quartile"><?= lang('Quartile', 'Quartil') ?></label>
                                <select class="form-control" name="values[quartile]" id="quartile">
                                    <option>Q1</option>
                                    <option>Q2</option>
                                    <option>Q3</option>
                                    <option>Q4</option>
                                    <option value=""><?= lang('Not available', 'Nicht verfügbar') ?></option>
                                </select>
                            </div>
                            <button class="btn block"><i class="ph ph-check"></i> <?= lang('Add', 'Hinzuf.') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>


        <?php
        if (!empty($quartiles)) {
            sort($quartiles);
            $years = array_column((array) $quartiles, 'year');
        ?>
            <canvas id="chart-quartiles" style="max-height: 400px;"></canvas>

            <script>
                var ctx = document.getElementById('chart-quartiles')

                var raw_data = Object.values(<?= json_encode($quartiles) ?>);
                console.log(raw_data);
                var data = {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($years) ?>,
                        datasets: [{
                            label: 'Quartiles',
                            data: raw_data,
                            parsing: {
                                yAxisKey: 'quartile',
                                xAxisKey: 'year'
                            },
                            backgroundColor: (ctx, value) => {
                                if (ctx.type !== 'data') return 'white';
                                let raw = ctx.raw ?? ctx.dataset[ctx.dataIndex].raw ?? ctx.dataset.data[ctx.dataIndex].raw ?? ctx.dataset.data[ctx.dataIndex];
                                let q = raw.quartile
                                if (q == 'Q1') {
                                    return '#63a308';
                                } else if (q == 'Q2') {
                                    return '#008083';
                                } else if (q == 'Q3') {
                                    return '#ECAF00';
                                } else if (q == 'Q4') {
                                    return '#B61F29';
                                } else {
                                    return '#878787';
                                }
                            },
                            borderColor: (ctx) => {
                                if (ctx.type !== 'data') return '#afafaf';
                                return 'transparent'
                            },
                            borderWidth: 3,
                            stepped: 'middle',
                            pointRadius: 8,
                        }, ],
                    },
                    options: {
                        responsive: true,
                        interaction: {
                            intersect: false,
                            axis: 'x'
                        },
                        plugins: {
                            title: {
                                display: false,
                            },
                            legend: {
                                display: false,
                            },
                        },
                        scales: {
                            x: {
                                stacked: true,
                            },
                            y: {
                                stacked: true,
                                labels: ['Q1', 'Q2', 'Q3', 'Q4'],
                                type: 'category',
                            }
                        }
                    }
                }


                console.log(data);
                var myChart = new Chart(ctx, data);
            </script>
        <?php } else { ?>
            <p><?= lang('No quartiles available.', 'Keine Quartile verfügbar.') ?></p>
        <?php } ?>


    </div>
</div>

<?php if (!$Settings->featureEnabled('no-journal-metrics')) { ?>
    <h3><?= lang('More Metrics', 'Weitere Metriken') ?></h3>

    <?php
    $metrics = DB::doc2Arr($data['metrics'] ?? array());

    if (empty($metrics)) {
        echo '<p>' . lang('No metrics available.', 'Keine weiteren Metriken verfügbar.') . '</p>';
    } else { ?>
        <table class="table small">
            <thead>
                <th><?= lang('Year', 'Jahr') ?></th>
                <th>SJR</th>
                <th>IF (2Y)</th>
                <th>IF (3Y)</th>
                <th><?= lang('Best Quartile', 'Bestes Quartil') ?></th>
            </thead>
            <tbody>
                <?php
                foreach ($metrics as $metric) {
                    echo '<tr>';
                    echo '<th>' . $metric['year'] . '</th>';
                    echo '<td>' . ($metric['sjr'] ?? '-') . '</td>';
                    echo '<td>' . ($metric['if_2y'] ?? '-') . '</td>';
                    echo '<td>' . ($metric['if_3y'] ?? '-') . '</td>';
                    echo '<td>';
                    if (isset($metric['quartile'])) {
                        echo '<span class="quartile ' . $metric['quartile'] . '">' . $metric['quartile'] . '</span>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    <?php } ?>
<?php } ?>

<?php if ($Settings->hasPermission('journals.delete')) { 
    $N_activities = $osiris->activities->count(['journal_id' => strval($id)]);
    if ($N_activities > 0) { ?>
        <div class="alert signal mt-20">
            <h4 class="title"><?= lang('Cannot delete journal', 'Journal kann nicht gelöscht werden') ?></h4>
                <?= lang("This journal cannot be deleted because there are $N_activities activities associated with it. Please reassign or delete these activities first.", "Dieses Journal kann nicht gelöscht werden, da $N_activities Aktivitäten damit verknüpft sind. Bitte weisen Sie diese Aktivitäten zuerst neu zu oder löschen Sie sie.") ?>
        </div>
    <?php } else { ?>
   <div class="alert danger mt-20">
    <h4 class="title"><?= lang('Delete this journal', 'Dieses Journal löschen') ?></h4>
    <p>
        <i class="ph ph-warning text-signal"></i>
        <?= lang("This will delete this journal permanently. This action cannot be undone.", "Dadurch wird dieses Journal dauerhaft gelöscht. Dieser Vorgang kann nicht rückgängig gemacht werden.") ?>
    </p>
    <form action="<?= ROOTPATH ?>/crud/journal/delete/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to delete this journal?', 'Sind Sie sicher, dass Sie dieses Journal löschen möchten?') ?>');">
        <button class="btn danger"><i class="ph ph-trash"></i> <?=  lang('Delete journal', 'Journal löschen') ?></button>
    </form>
   </div>
   <?php } ?>
<?php } ?>



<?php
if (isset($_GET['verbose'])) {
    dump($data, true);
}
?>