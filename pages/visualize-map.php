<?php
    
/**
 * Page to visualize collaborators on a map
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /visualize/map
 *
 * @package OSIRIS
 * @since 1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$institute = $Settings->get('affiliation_details');
$lat = $institute['lat'] ?? 52;
$lng = $institute['lng'] ?? 10;
if (empty($lat) || empty($lng)) {
    $lat = 52;
    $lng = 10;
}
?>

<script src="<?=ROOTPATH?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>

<style>
    #map {
        height: calc(100vh - 26rem);
        min-height: 400px;
    }
</style>

<script>
    var layout = {
        mapbox: {
            style: "open-street-map",
            center: {
                lat: <?=($lat)?>,
                lon: <?=($lng)?>
            },
            zoom: 1
        },

        margin: {
            r: 0,
            t: 0,
            b: 0,
            l: 0
        },
        hoverinfo: 'text',
        autosize: true
    };
</script>

<?php if (isset($_GET['project'])) {
    $id = $_GET['project'];

    $mongo_id = $DB->to_ObjectID($id);
    $project = $osiris->projects->findOne(['_id' => $mongo_id]);

    require_once BASEPATH . "/php/Project.php";
    $Project = new Project($project);
?>

    <h1><?= lang('Project Map', 'Projekt-Karte') ?></h1>

    <div class="row row-eq-spacing">
        <div class="col-md-8">
            <div class="box my-0">
                <div id="map" class=""></div>
            </div>
        </div>
        <div class="col-md-4">
            <?= $Project->widgetSmall() ?>
            
            <h2>
                <?= lang('Collaborators', 'Kooperationspartner') ?>
            </h2>

            <table class="table">
                <tbody>
                    <?php
                    if (empty($project['collaborators'] ?? array())) {
                    ?>
                        <tr>
                            <td>
                                <?= lang('No collaborators connected.', 'Keine Partner verknüpft.') ?>
                            </td>
                        </tr>
                    <?php
                    } else foreach ($project['collaborators'] as $collab) {
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span data-toggle="tooltip" data-title="<?= $collab['type'] ?>" class="mr-10">
                                        <?= Project::getCollaboratorIcon($collab['type'], 'ph-fw ph-2x') ?>
                                    </span>
                                    <div class="">
                                        <h5 class="my-0">
                                            <?= $collab['name'] ?>
                                        </h5>
                                        <?= $collab['location'] ?>
                                        <a href="<?= $collab['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                    } ?>

                </tbody>
            </table>
        </div>
    </div>

    <script>
        const id = '<?= $_GET['project'] ?? null ?>';
        console.log(id);
        $.ajax({
            type: "GET",
            url: ROOTPATH + "/api/dashboard/collaborators",
            data: {
                project: id
            },
            dataType: "json",
            success: function(response) {
                console.log(response);

                var zoomlvl = 1;
                switch (response.data.scope ?? 'international') {
                    case 'local':
                        zoomlvl = 5
                        break;
                    case 'national':
                        zoomlvl = 4
                        break;
                    case 'continental':
                        zoomlvl = 3
                        break;
                    case 'international':
                        zoomlvl = 1
                        break;
                    default:
                        break;
                }
                layout.mapbox.zoom = zoomlvl;

                var data = response.data.collaborators
                data.type = 'scattermapbox'
                data.mode = 'markers'
                data.hoverinfo = 'text',

                Plotly.newPlot('map', [data], layout);
            },
            error: function(response) {
                console.log(response);
            }
        });
    </script>



<?php } else { ?>


    <div class="btn-toolbar float-right">
        <a href="<?= ROOTPATH ?>/projects" class="btn secondary">
            <i class="ph ph-tree-structure"></i>
            <?= lang('Go to all projects', 'Gehe zu allen Projekten') ?>
        </a>
    </div>
    <h1>
        <?= lang('Collaboration map', 'Kooperations-Karte') ?>
    </h1>

    <div id="map" class=""></div>
    <script>
        const id = '<?= $_GET['project'] ?? null ?>';
        console.log(id);
        $.ajax({
            type: "GET",
            url: ROOTPATH + "/api/dashboard/collaborators",
            dataType: "json",
            success: function(response) {
                var data = {
                    type: 'scattermapbox',
                    mode: 'markers',
                    hoverinfo: 'text',
                    lon: [],
                    lat: [],
                    text: [],
                    marker: {
                        size: [],
                        color: []
                    }
                }

                response.data.forEach(item => {
                    data.marker.size.push(item.count + 10)
                    data.marker.color.push(item.color ?? 'rgba(0, 128, 131, 0.7)')
                    data.lon.push(item.data.lng)
                    data.lat.push(item.data.lat)
                    data.text.push(`<b>${item.data.name}</b><br>${item.data.location}<br>${item.count} <?= lang('Projects', 'Projekte') ?>`)

                });
                console.log(data);

                Plotly.newPlot('map', [data], layout);
            },
            error: function(response) {
                console.log(response);
            }
        });
    </script>
<?php } ?>






<!-- 
<div id="myDiv"></div>

<script>
    var data = [{
        type: 'scattergeo',
        mode: 'markers',
        locations: ['FRA', 'DEU', 'RUS', 'ESP'],
        marker: {
            size: [20, 30, 15, 10],
            color: [10, 20, 40, 50],
            cmin: 0,
            cmax: 50,
            colorscale: 'Greens',
            colorbar: {
                title: 'Some rate',
                ticksuffix: '%',
                showticksuffix: 'last'
            },
            line: {
                color: 'black'
            }
        },
        name: 'europe data'
    }];



    Plotly.newPlot('myDiv', data, layout);
</script> -->