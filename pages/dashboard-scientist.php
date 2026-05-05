<?php

/**
 * Page for dashboard (also shown to scientist)
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /dashboard
 *
 * @package     OSIRIS
 * @since       1.0 
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<h2>
    <?= lang('Publications in the last four quarters', 'Publikationen in den letzten vier Quartalen') ?>
</h2>

<div class="row row-eq-spacing">
    <div class="col-lg-8">

        <div class="box h-full">
            <div class="chart content">
                <h5 class="title text-center"><?= lang('Impact factors', 'Impact Factors') ?></h5>
                <canvas id="chart-impact" style="max-height: 30rem;"></canvas>
            </div>
        </div>

        <?php
        $x = [];
        for ($i = 1; $i < $max_impact; $i++) {
            $x[] = $i;
        }
        ?>

        <script>
            var ctx = document.getElementById('chart-impact')
            var labels = JSON.parse('<?= json_encode($x) ?>');
            var colors = [
                '#3fd7d995',
                '#00808395',
                '#03383995',
                '#04232395'
            ]
            var i = 0

            console.log(labels);
            var data = {
                type: 'bar',
                options: {
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                title: (items) => {
                                    if (!items.length) {
                                        return '';
                                    }
                                    const item = items[0];
                                    const x = item.parsed.x;
                                    const min = x;
                                    const max = x + 1;
                                    return `IF: ${min} - ${max}`;
                                }
                            }
                        }
                    },
                    responsive: true,
                    scales: {
                        x: {
                            type: 'linear',
                            ticks: {
                                stepSize: 1
                            },
                            stacked: true,
                            title: {
                                display: true,
                                text: lang('Impact factor', 'Impact factor')
                            },
                        },
                        y: {
                            stacked: true,
                            title: {
                                display: true,
                                text: lang('Number of publications', 'Anzahl Publikationen')
                            },
                            ticks: {
                                callback: function(value, index, ticks) {
                                    // only show full numbers
                                    if (Number.isInteger(value)) {
                                        return value
                                    }
                                    return "";
                                }
                            }
                        }
                    },
                },
                data: {
                    labels: labels,
                    datasets: [

                        <?php foreach ($quarters as $q => $data) {
                            $imp = [];
                            for ($i = 1; $i <= $max_impact; $i++) {
                                $imp[] = $data['impacts'][$i] ?? 0;
                            }
                        ?> {
                                label: '<?= $q ?>',
                                data: JSON.parse('<?= json_encode($imp) ?>'),
                                backgroundColor: colors[i++],
                                borderWidth: 1,
                                borderColor: '#464646',
                                borderRadius: 4
                            },
                        <?php } ?>

                    ],
                }
            }


            console.log(data);
            var myChart = new Chart(ctx, data);
        </script>
    </div>

    <div class="col-lg-4">
        <div class="box h-full">
            <div class="chart content">
                <h5 class="title text-center"><?= lang('Role of ' . $Settings->get('affiliation') . ' authors', 'Rolle der ' . $Settings->get('affiliation') . '-Autoren') ?></h5>
                <canvas id="chart-authors" style="max-height: 30rem;"></canvas>
            </div>
            <script>
                var ctx = document.getElementById('chart-authors')
                var myChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['<?= lang("First or last author", "Erst- oder Letztautor") ?>', '<?= lang("Middle authors", "Mittelautor") ?>'],
                        datasets: [{
                            label: '# of Scientists',
                            data: [<?= $authors['firstorlast'] ?>, <?= $authors['middle'] ?>],
                            backgroundColor: [
                                '#005f6095',
                                '#249ea095',
                            ],
                            borderColor: '#464646', //'',
                            borderWidth: 1,
                        }]
                    },
                    plugins: [ChartDataLabels],
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                display: true,
                            },
                            title: {
                                display: false,
                                text: 'Scientists approvation'
                            },
                            datalabels: {
                                color: 'black',
                                // anchor: 'end',
                                // align: 'end',
                                // offset: 10,
                                font: {
                                    size: 20
                                }
                            }
                        },
                    }
                });
            </script>
        </div>
    </div>

</div>

<h2>
    <?= lang('Development of activities by type', 'Entwicklung der Aktivitäten nach Art') ?>
</h2>

<div class="row row-eq-spacing mb-0">
    <?php foreach ($stats as $type => $vals) {

        $years = [];
        for ($i = $Settings->get('startyear'); $i <= CURRENTYEAR; $i++) {
            $years[] = strval($i);
        }
    ?>
        <div class="col-lg-4 col-md-6">

            <div class="box">
                <div class="chart content">
                    <h5 class="title text-center"><?= $Settings->getActivities($type)['name'] ?></h5>
                    <canvas id="chart-<?= $type ?>"></canvas>

                    <div class="mt-5 text-right">
                        <a href="<?= ROOTPATH ?>/add-activity?type=<?= $type ?>" class="btn small">
                            <i class="ph ph-plus"></i>
                            <?= lang('Add new', 'Neu anlegen') ?>
                        </a>
                    </div>

                </div>
            </div>
        </div>


        <script>
            var ctx = document.getElementById('chart-<?= $type ?>')
            var data = Object.assign({}, barChartConfig)
            var raw_data = Object.values(<?= json_encode($vals) ?>);
            data.data = {
                labels: <?= json_encode($years) ?>,
                datasets: [{
                    label: 'Activities',
                    data: raw_data,
                    parsing: {
                        yAxisKey: 'good'
                    },
                    backgroundColor: '<?= $Settings->getActivities($type)['color'] ?>95',
                    borderColor: '#464646',
                    borderWidth: 1,
                    borderRadius: 4
                }, ]
            }

            var myChart = new Chart(ctx, data);
        </script>
    <?php } ?>


</div>

<?php
// include BASEPATH ."/pages/visualize-departments.php";
?>


<a href="<?= ROOTPATH ?>/activities" class="btn select bg-white mr-20">
    <i class="ph ph-book-bookmark text-danger"></i>
    <?= lang('View all activites', 'Zeige alle Aktivitäten') ?>
</a>