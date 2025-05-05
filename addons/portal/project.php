<?php

/**
 * Page to see details on a single project
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /project/<id>
 *
 * @package     OSIRIS
 * @since       1.2.2
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$project = $osiris->projects->findOne(['$or' => [['name' => $id], ['_id' => $DB->to_ObjectID($id)]]]);

include_once BASEPATH . "/php/Project.php";
$Project = new Project($project);
?>
<script>
    const id = '<?= $_GET['project'] ?? null ?>';
    const PROJECT = '<?= $project['name'] ?>';
    console.log(id);
    const PORTALPATH = '<?= PORTALPATH ?>';
</script>

<script src="<?= ROOTPATH ?>/js/projects.js"></script>

<style>
    @media (min-width: 768px) {

        #abstract figure {
            max-width: 100%;
            float: right;
            margin: 0 0 1rem 2rem;
        }
    }

    #abstract figure figcaption {
        font-size: 1.2rem;
        color: var(--muted-color);
        font-style: italic;
    }
</style>

<div class="container-lg mt-20">
    <h1>
        <?= $project['name'] ?>
    </h1>

    <h2 class="subtitle">
        <?= $project['title'] ?>
    </h2>

    <!-- abstract -->
    <div class="row row-eq-spacing">
        <div class="col-md-8">
            <?php if (!empty($project['abstract'])) { ?>
                <h2 class="title">
                    <?= lang('About this project', 'Über das Projekt') ?>
                </h2>
            <?php } ?>

            <?php if (!empty($project['image'] ?? '') && file_exists(ROOTPATH . '/uploads/' . $project['image'])) { ?>
                <img src="<?= ROOTPATH . '/uploads/' . $project['image'] ?>" alt="<?= $project['name'] ?>" class="img-fluid">
            <?php } ?>
            <div id="abstract">
                <?php
                // markdown support
                
                echo $project['abstract'] ?? '-';
                ?>
            </div>
            <?php if (!empty($project['website'] ?? null)) { ?>
                <a href="<?= $project['website'] ?>" target="_blank" class="btn secondary">
                    <i class="ph ph-arrow-square-out"></i>
                    <?= lang('Visit Website', 'Webseite besuchen') ?>
                </a>
            <?php } ?>

        </div>

        <div class="col-md-4">
            <h2>
                <?= lang('Details', 'Details') ?>
            </h2>
            <table class="table ">
                <tbody>
                    <tr>
                        <td>
                            <!-- timeline progress bar -->
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="key">Start</span>
                                    <b><?= $Project->getStartDate() ?></b>
                                </div>
                                <div>
                                    <span class="key"><?= lang('End', 'Ende') ?></span>
                                    <b><?= $Project->getEndDate() ?></b>
                                </div>
                            </div>
                            <div class="progress">
                                <?php
                                $progress = $Project->getProgress();
                                ?>

                                <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>


                            </div> <?php if ($progress == 100) { ?>
                                <small class="text-secondary">
                                    <?= lang('Completed', 'Abgeschlossen') ?>
                                </small>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Third-party funder', 'Drittmittelgeber') ?></span>
                            <b><?= $project['funder'] ?? '-' ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Funding organization', 'Förderorganisation') ?></span>
                            <b><?= $project['funding_organization'] ?? '-' ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Funding reference number(s)', 'Förderkennzeichen') ?></span>
                            <b><?= $Project->getFundingNumbers('<br>') ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Coordinator facility', 'Koordinator-Einrichtung') ?></span>
                            <b><?= $project['coordinator'] ?? '-' ?></b>
                        </td>
                    </tr>
                </tbody>
            </table>


        </div>
    </div>

    <div class="row row-eq-spacing">
        <div class="col-md-8">

            <!-- activities -->
            <?php

            $N = $osiris->activities->count([
                'projects' => $project['name'],
                'hide' => ['$ne' => true]
            ]);
            if ($N > 0) { ?>

                <h3>
                    <?= lang('Research Output', 'Forschungsergebnisse') ?>
                </h3>


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
                    $(document).ready(function() {
                        initActivities('#activities-table', {
                            page: 'activities',
                            display_activities: 'web',
                            json: JSON.stringify({
                                'projects': PROJECT,
                                'hide': {
                                    '$ne': true
                                }
                            })
                        })
                    });
                </script>
            <?php } ?>
        </div>

        <div class="col-md-4">
            <!-- team -->
            <h2 class="title">
                <?= lang('Team', 'Team') ?>
            </h2>
            <?php if (!empty($project['persons'] ?? array())) { ?>
                <table class="table ">
                    <tbody>
                        <?php
                        $persons = DB::doc2Arr($project['persons']);
                        // sort project team by role (custom order)
                        $roles = ['applicant', 'PI', 'Co-PI', 'worker', 'associate', 'student'];
                        usort($persons, function ($a, $b) use ($roles) {
                            return array_search($a['role'], $roles) - array_search($b['role'], $roles);
                        });

                        foreach ($persons as $person) {
                            $username = strval($person['user']);

                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">

                                        <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                                        <div class="">
                                            <h5 class="my-0">
                                                <a href="<?= ROOTPATH ?>/person/<?= $username ?>" class="colorless">
                                                    <?= $person['name'] ?>
                                                </a>
                                                <!-- dept -->

                                            </h5>
                                            <?= $Project->personRole($person['role']) ?>
                                            <?php
                                            $scientist = $DB->getPerson($username);
                                            if (!empty($scientist['units'])) {
                                                
                                            $i = 0;
                                            $units = $Groups->getPersonUnit($username, $project['start'], false, true);
                                            foreach ($units as $d) {
                                                $d = $d['unit'];
                                                $dept = $Groups->getGroup($d);
                                                if ($i++ > 0) echo ', ';
                                            ?>
                                                <br>
                                                <a href="<?= ROOTPATH ?>/groups/view/<?= $dept['id'] ?>" style="color:<?= $dept['color'] ?? 'inherit' ?>">
                                                    <?= $dept['name'] ?>
                                                </a>
                                            <?php } ?>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
            <?php } ?>
        </div>
    </div>

    <?php if (!empty($project['collaborators'] ?? [])) { ?>

        <script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>


        <h2 class="mb-0">
            <?= lang('Collaborators', 'Kooperationspartner') ?>
        </h2>


        <div class="row row-eq-spacing">

            <div class="col-lg-8">
                <div class="box mt-0">
                    <div id="map" class=""></div>
                </div>
                <p>
                    <i class="ph ph-fill ph-circle" style="color:#f78104"></i>
                    <?= lang('Coordinator', 'Koordinator') ?>
                    <br>
                    <i class="ph ph-fill ph-circle" style="color:#008083"></i>
                    Partner
                    <br>
                    <i class="ph ph-fill ph-circle" style="color:#cccccc"></i>
                    <?=lang('Accociated', 'Beteiligt')?>
                </p>
            </div>
            <div class="col-lg-4">
                <div style="max-height: 60rem; overflow-y:auto">

                    <table class="table ">
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

                                            <span data-toggle="tooltip" data-title="<?= $collab['type'] ?>" class="badge mr-10">
                                                <?= Project::getCollaboratorIcon($collab['type'], 'ph-fw ph-2x m-0') ?>
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
        </div>

        <script>
            // on load:
            $(document).ready(function() {
                var layout = {
                    mapbox: {
                        style: "open-street-map",
                        center: {
                            lat: <?= $institute['lat'] ?? 52 ?>,
                            lon: <?= $institute['lng'] ?? 10 ?>
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
                    // autosize:true
                };

                $.ajax({
                    type: "GET",
                    url: ROOTPATH + "/api/dashboard/collaborators",
                    data: {
                        project: PROJECT
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
            });
        </script>
    <?php } ?>



</div>

</div>