<?php

/**
 * Page to see a journal
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal/view/<journal_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$data = DB::doc2Arr($data);
$label = $Settings->journalLabel();
$if_label = $Settings->impactLabel();
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
            <?= lang('journals.edit_label', replace: ['label' => $label]) ?>
        </a>
    <?php } ?>

    <?php if ($Settings->hasPermission('journals.edit') && !$Settings->featureEnabled('no-journal-metrics')) { ?>

        <?php if (count($data['issn'] ?? []) == 0) { ?>
            <a href="#/" class="btn disabled" data-toggle="tooltip" data-direction="bottom" data-title="<?= lang('journals.no_metrics_without_issn') ?>">
                <i class="ph ph-ranking"></i> <?= lang('journals.update_metrics') ?>
            </a>
        <?php } else { ?>
            <a href="#metrics-modal" class="btn primary">
                <i class="ph ph-ranking"></i> <?= lang('journals.update_metrics') ?>
            </a>

            <div class="modal" id="metrics-modal" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <a href="#/" class="close" role="button" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                        <h5 class="title"><?= lang('journals.update_metrics_view') ?></h5>
                        <p>
                            <i class="ph ph-warning text-signal"></i>
                            <?= lang('journals.this_will_update_the_metrics_for_this_label_and_overwrite_all_manual_change', replace: ['if_label' => $if_label, 'label' => $label]) ?>
                        </p>

                        <form action="<?= ROOTPATH ?>/crud/journal/update-metrics/<?= $id ?>" method="post">
                            <button class="btn primary"><i class="ph ph-arrows-clockwise"></i> <?= lang('journals.update_metrics') ?></button>
                        </form>
                    </div>
                </div>
            </div>

        <?php } ?>
    <?php } ?>
</div>


<table class="table" id="result-table">
    <tr>
        <td><?= lang('common.name') ?></td>
        <td class="font-weight-bold"><?= $data['journal'] ?></td>
    </tr>
    <tr>
        <td><?= lang('journals.abbreviated') ?></td>
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
                echo lang('common.no');
            } elseif ($data['oa'] > 1900) {
                echo lang('journals.since') . $data['oa'];
            } else {
                echo lang('common.yes');
            }
            ?>
        </td>
    </tr>
    <?php if (isset($data['wos'])) { ?>
        <tr>
            <td>Web of Science Links</td>
            <td>
                <?php foreach ($data['wos']['links'] as $link) { ?>
                    <a href="<?= $link['url'] ?>" target="_blank" rel="noopener noreferrer"><?= $link['type'] ?></a>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <td>
            <?= lang('common.categories') ?>
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
                echo lang('journals.no_categories_available');
            } else {
                echo '<div class="badges">';
                foreach ($categories as $cat) { ?>
                    <span class="badge">
                        <?= $cat['name'] ?? $cat ?>
                    </span>
            <?php
                }
                echo '</div>';
            }
            ?>
        </td>
    </tr>
    <?php
    $fields = $Settings->get('journal-data');
    $fields = DB::doc2Arr($fields);
    if (!empty($fields)):
        require_once BASEPATH . "/php/CustomFields.php";
        $CustomFields = new CustomFields($data);
    ?>
        <?php foreach ($fields as $f) { ?>
            <tr>
                <td>
                    <?= $CustomFields->name($f) ?>
                </td>
                <td>
                    <?= $CustomFields->value($f, '-') ?>
                </td>
            </tr>
        <?php } ?>
    <?php endif; ?>
</table>

<?php
if ($Settings->hasPermission('journals.edit')) { ?>

    <div class="modal" id="cat-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#/" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title"><?= lang('journals.edit_label_categories', replace: ['label' => $label]) ?></h5>

                <form action="<?= ROOTPATH ?>/crud/journal/update/<?= $id ?>" method="post">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                    <div id="category-form">
                        <?php if (empty($categories)) { ?>
                            <input type="text" class="form-control" name="values[categories][]" id="categories" placeholder="<?= lang('common.category') ?>" required list="categories-list">
                        <?php } else { ?>
                            <?php foreach ($categories as $cat) { ?>
                                <div class="input-group mb-10">
                                    <input type="text" class="form-control" name="values[categories][][name]" id="categories" placeholder="<?= lang('common.category') ?>" required list="categories-list" value="<?= $cat['name'] ?? $cat ?>">
                                    <div class="input-group-append">
                                        <button type="button" class="btn" onclick="$(this).closest('.input-group').remove()"><i class="ph ph-trash"></i></button>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <button type="button" class="btn" id="add-category" onclick="addCategory()"><i class="ph ph-plus"></i></button>
                    <br><br>
                    <button class="btn primary"><i class="ph ph-floppy-disk"></i> <?= lang('action.save') ?></button>
                </form>
                <datalist id="categories-list">
                    <?php foreach ($osiris->journals->distinct('categories.name') as $cat) { ?>
                        <option value="<?= $cat ?>"><?= $cat ?></option>
                    <?php } ?>
                </datalist>

                <script>
                    function addCategory() {
                        var input = `<div class="input-group mb-10">
                    <input type="text" class="form-control" name="values[categories][][name]" id="categories" placeholder="<?= lang('common.category') ?>" required list="categories-list">
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
    <?= lang('journals.connected_publications') ?>
</h3>

<!-- <canvas id="spark"></canvas> -->

<table class="table" id="publication-table">
    <thead>
        <th><?= lang('common.year') ?></th>
        <th><?= lang('common.publication') ?></th>
        <th>Link</th>
    </thead>
    <tbody>
    </tbody>
</table>
<script>
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
                "emptyTable": <?= json_encode(lang('journals.no_publications_available', replace: ['label' => $label]), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
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
    <?= lang('common.other_activities_statistics') ?>
</h3>

<table class="table" id="activity-table">
    <thead>
        <th><?= lang('common.activity') ?></th
            </thead>
    <tbody>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#activity-table').DataTable({
            ajax: {
                "url": ROOTPATH + '/api/activities',
                "data": {
                    "filter": {
                        journal_id: '<?= $id ?>',
                        type: {
                            '$ne': 'publication'
                        }
                    },
                    formatted: true
                }
            },
            language: {
                "emptyTable": <?= json_encode(lang('journals.no_other_activities_available', replace: ['label' => $label]), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
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
                        return `<a href="${ROOTPATH}/activities/view/${full.id}"><i class="ph ph-arrow-fat-line-right"></i></a>`;
                    },
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


<h3><?= $if_label ?></h3>
<?php
$impacts = DB::doc2Arr($data['impact'] ?? array());
?>

<div class="box">
    <div class="content">
        <style>
            .form-row {
                display: flex;
                gap: 2rem;
                align-items: center;
            }

            .form-row label {
                margin-bottom: 0;
                width: 5rem;
            }

            .form-row .form-control {
                flex: 1;
            }
        </style>
        <?php if ($Settings->hasPermission('journals.edit')) { ?>
            <div class="dropdown with-arrow float-right mb-20">
                <button class="btn osiris" data-toggle="dropdown" type="button" id="dropdown-2" aria-haspopup="true" aria-expanded="false">
                    <?= lang('journals.add_value') ?> <i class="ph ph-duotone ph-angle-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right w-200" aria-labelledby="dropdown-2">
                    <div class="content">
                        <form action="<?= ROOTPATH ?>/crud/journal/update/<?= $id ?>" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <div class="form-row">
                                <label for="year"><?= lang('common.year') ?></label>
                                <input type="number" min="1970" max="<?= CURRENTYEAR ?>" step="1" class="form-control" name="values[year]" id="year" value="<?= CURRENTYEAR - 1 ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="if"><?= $if_label ?></label>
                                <input type="number" min="0" max="300" step="0.001" class="form-control" name="values[if]" id="if">
                            </div>
                            <button class="btn block success mb-5"><i class="ph ph-check"></i> <?= lang('journals.add') ?></button>

                            <small class="text-muted">
                                <?= lang('journals.existing_years_will_be_overwritten_enter_0_to_remove') ?>
                            </small>
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
            <canvas id="chart-if" style="max-height: 300px;"></canvas>

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
                        label: '<?= e($if_label) ?>',
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
            <p><?= lang('journals.no_if_label_factors_available', replace: ['if_label' => $if_label]) ?></p>
        <?php } ?>


    </div>
</div>




<h3><?= lang('common.quartiles') ?></h3>
<?php
$metrics = DB::doc2Arr($data['metrics'] ?? array());
$quartiles = [];
foreach ($metrics as $metric) {
    if (isset($metric['quartile'])) {
        $quartiles[] = [
            'year' => $metric['year'],
            'quartile' => $metric['quartile'],
        ];
    }
}
?>

<div class="box">
    <div class="content">

        <?php if ($Settings->hasPermission('journals.edit')) { ?>
            <div class="dropdown with-arrow float-right mb-20">
                <button class="btn osiris" data-toggle="dropdown" type="button" id="dropdown-2" aria-haspopup="true" aria-expanded="false">
                    <?= lang('journals.add_quartile') ?> <i class="ph ph-duotone ph-angle-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right w-200" aria-labelledby="dropdown-2">
                    <div class="content">
                        <form action="<?= ROOTPATH ?>/crud/journal/update/<?= $id ?>" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <div class="form-row">
                                <label for="year"><?= lang('common.year') ?></label>
                                <input type="number" min="1970" max="<?= CURRENTYEAR ?>" step="1" class="form-control" name="values[year]" id="year" value="<?= CURRENTYEAR - 1 ?>" required>
                            </div>
                            <div class="form-row">
                                <label for="quartile"><?= lang('common.quartile') ?></label>
                                <select class="form-control" name="values[quartile]" id="quartile">
                                    <option>Q1</option>
                                    <option>Q2</option>
                                    <option>Q3</option>
                                    <option>Q4</option>
                                    <option value=""><?= lang('journals.not_available') ?></option>
                                </select>
                            </div>
                            <button class="btn block success mb-5"><i class="ph ph-check"></i> <?= lang('journals.add') ?></button>

                            <small class="text-muted">
                                <?= lang('journals.existing_years_will_be_overwritten_select_not_available_to_remove') ?>
                            </small>
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
            <canvas id="chart-quartiles" style="max-height: 300px;"></canvas>

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
            <p><?= lang('journals.no_quartiles_available') ?></p>
        <?php } ?>


    </div>
</div>

<?php if (!$Settings->featureEnabled('no-journal-metrics')) { ?>
    <h3><?= lang('journals.more_metrics') ?></h3>

    <?php
    $metrics = DB::doc2Arr($data['metrics'] ?? array());

    if (empty($metrics)) {
        echo '<p>' . lang('journals.no_metrics_available') . '</p>';
    } else { ?>
        <table class="table small">
            <thead>
                <th><?= lang('common.year') ?></th>
                <th>SJR</th>
                <th>IF (2Y)</th>
                <th>IF (3Y)</th>
                <th><?= lang('journals.best_quartile') ?></th>
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
            <h4 class="title"><?= lang('journals.cannot_delete_journal') ?></h4>
            <?= lang('journals.this_journal_cannot_be_deleted_because_there_are_n_activities_activities_as', replace: ['N_activities' => $N_activities]) ?>
        </div>
    <?php } else { ?>
        <div class="alert danger mt-20">
            <h4 class="title"><?= lang('journals.delete_this_journal') ?></h4>
            <p>
                <i class="ph ph-warning text-signal"></i>
                <?= lang('journals.this_will_delete_this_journal_permanently_this_action_cannot_be_undone') ?>
            </p>
            <form action="<?= ROOTPATH ?>/crud/journal/delete/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('journals.are_you_sure_you_want_to_delete_this_journal') ?>');">
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('journals.delete_journal') ?></button>
            </form>
        </div>
    <?php } ?>
<?php } ?>



<?php
if (isset($_GET['verbose'])) {
    dump($data, true);
}
?>
