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
?>
<script>
    const PROJECT = '<?= $id ?>';
    const $base = '<?= $base ?>';
</script>
<?php if ($Portfolio->isPreview()) { ?>
    <script src="<?= ROOTPATH ?>/js/projects.js"></script>
<?php } ?>


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

    .on-this-page-nav {
        z-index: 10;
        top: 0;
    }
</style>

<?php if ($Portfolio->isPreview()) { ?>
    <!-- adjust style for a top margin of 4rem for all links and fixed -->
    <style>
        .content-wrapper {
            scroll-padding-top: 6rem;

        }

        .on-this-page-nav {
            top: 9rem !important;
        }
    </style>
    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/portal.css?v=<?= CSS_JS_VERSION ?>">
<?php } ?>


<section class="container-lg mt-20">
    <h1>
        <?= lang($data['name'], $data['name_de'] ?? null) ?>
    </h1>

    <h2 class="subtitle">
        <?= lang($data['title'], $data['title_de'] ?? null) ?>
    </h2>

    <!-- abstract -->
    <div class="row row-eq-spacing">
        <div class="col-sm-8 order-sm-first order-last" id="about">
            <?php if (!empty($data['abstract'])) { ?>
                <h2 class="title">
                    <?= lang('About this project', 'Über das Projekt') ?>
                </h2>
            <?php } ?>

            <?php if (!empty($data['image'] ?? '') && file_exists(ROOTPATH . '/uploads/' . $data['image'])) { ?>
                <img src="<?= ROOTPATH . '/uploads/' . $data['image'] ?>" alt="<?= $data['name'] ?>" class="img-fluid">
            <?php } ?>
            <div id="abstract">
                <?= lang($data['abstract'] ?? '-', $data['abstract_de'] ?? null) ?>
            </div>
            <?php if (!empty($data['website'] ?? null)) { ?>
                <a href="<?= $data['website'] ?>" target="_blank" class="btn secondary">
                    <i class="ph ph-arrow-square-out"></i>
                    <?= lang('Visit Website', 'Webseite besuchen') ?>
                </a>
            <?php } ?>


            <h2 class="title" id="team">
                <?= lang('Team', 'Team') ?>
            </h2>
            <?php if (!empty($data['persons'] ?? array())) { ?>
                <div class="row row-eq-spacing">
                    <?php
                    $persons = DB::doc2Arr($data['persons']);
                    foreach ($persons as $person) {
                    ?>
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center box mt-0 p-10" style="height: calc(100% - 2rem);">

                                <?= $person['img'] ?>
                                <div class="">
                                    <h5 class="my-0">
                                        <a href="<?= $base ?>/person/<?= $person['id'] ?>" class="colorless">
                                            <?= $person['name'] ?>
                                        </a>
                                    </h5>
                                    <?= lang($person['role']['en'] ?? '', $person['role']['de'] ?? null) ?>
                                    <?php
                                    if (!empty($person['depts'])) {
                                        foreach ($person['depts'] as $d => $dept) {
                                    ?>
                                            <br>
                                            <a href="<?= $base ?>/group/<?= $d ?>">
                                                <?= lang($dept['en'] ?? '', $dept['de'] ?? null) ?>
                                            </a>
                                        <?php } ?>
                                    <?php } ?>

                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>

            <!-- activities -->
            <?php
            if ($data['activities'] > 0) { ?>

                <h3 class="title mt-40" id="research-output">
                    <?= lang('Research Output', 'Forschungsergebnisse') ?>
                </h3>

                <div class="mt-20 w-full">
                    <table class="table datatable responsive" id="activities-table"
                        data-table="activities"
                        data-source="./all-activities.json"
                        data-lang="<?= lang('en', 'de') ?>">
                        <thead>
                            <tr>
                                <th data-col="icon" data-orderable="false" data-searchable="false">Type</th>
                                <th data-col="html" data-search-col="search">Title</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>

                    </table>
                </div>

                <?php if ($Portfolio->isPreview()) { ?>
                    <script>
                        $(document).ready(function() {
                            $('#activities-table').DataTable({
                                "ajax": {
                                    "url": ROOTPATH + '/portfolio/project/' + PROJECT + '/activities',
                                    dataSrc: 'data'
                                },
                                "sort": false,
                                "pageLength": 6,
                                "lengthChange": false,
                                "searching": false,
                                "pagingType": "numbers",
                                columnDefs: [{
                                        targets: 0,
                                        data: 'icon',
                                        className: 'w-50'
                                    },
                                    {
                                        targets: 1,
                                        data: 'html',
                                        render: function(data, type, row) {
                                            // replace links to activities with links to the activity page
                                            data = data.replace(/href='\/activity/g, "href='<?= $base ?>/activity");
                                            return data;
                                        }
                                    },
                                ],
                            });
                        });
                    </script>
                <?php } ?>
            <?php } ?>



            <?php if (!empty($data['collaborators'] ?? [])) { ?>

                <?php if ($Portfolio->isPreview()) { ?>
                    <script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>
                <?php } ?>

                <div id="collaborators" class="mt-40">
                    <a class="btn primary float-right" href="#cooperation-partners">Zeige Liste</a>

                    <h2>
                        <?= lang('Collaborators', 'Kooperationspartner') ?>
                        (<?= count($data['collaborators']) ?>)
                    </h2>


                    <div class="box mt-0">
                        <!-- <div id="map" class="portfolio-map"></div> -->
                        <div id="collaborator-map"
                            class="portfolio-map map h-500 w-full"
                            data-source="./collaborators-map.json"
                            data-context="project"
                            data-lang="<?= lang('en', 'de') ?>">
                        </div>
                    </div>
                    <p>
                        <i class="ph ph-duotone ph-circle" style="color:#f78104"></i>
                        <?= lang('Coordinator', 'Koordinator') ?>
                        <br>
                        <i class="ph ph-duotone ph-circle" style="color:#008083"></i>
                        Partner
                        <br>
                        <i class="ph ph-duotone ph-circle" style="color:#cccccc"></i>
                        <?= lang('Accociated', 'Beteiligt') ?>
                    </p>

                    <div class="modal" id="cooperation-partners" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <h5 class="title">Liste der Kooperationspartner</h5>
                                <div style="max-height: 60rem; overflow-y:auto">
                                    <table class="table ">
                                        <tbody>
                                            <?php
                                            if (empty($data['collaborators'] ?? array())) {
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?= lang('No collaborators connected.', 'Keine Partner verknüpft.') ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            } else {

                                                // order by role: coordinator, partner, others
                                                usort($data['collaborators'], function ($a, $b) {
                                                    $order = ['coordinator' => 1, 'partner' => 2];
                                                    $a_order = $order[$a['role']] ?? 3;
                                                    $b_order = $order[$b['role']] ?? 3;
                                                    return $a_order - $b_order;
                                                });
                                                foreach ($data['collaborators'] as $collab) {
                                                ?>
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">

                                                                <span title="<?= $collab['role'] ?>" class="mr-10">
                                                                    <?php
                                                                    $color = '#cccccc';
                                                                    if ($collab['role'] == 'coordinator') {
                                                                        $color = '#f78104';
                                                                    } elseif ($collab['role'] == 'partner') {
                                                                        $color = '#008083';
                                                                    }
                                                                    ?>
                                                                    <i class="ph ph-duotone ph-circle ph-2x" style="color:<?= $color ?>"></i>
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
                                                }
                                            } ?>

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <?php if ($Portfolio->isPreview()) { ?>
                    <script>
                        // on load:
                        $(document).ready(function() {
                            var layout = {
                                mapbox: {
                                    style: "carto-positron",
                                    center: {
                                        lat: 52,
                                        lon: 10,
                                    },
                                    zoom: 1,
                                    showlegend: false,
                                    // bounds: {
                                    // },
                                },
                                geo: {
                                    'scope': 'world',
                                    'showland': true,

                                },

                                margin: {
                                    r: 0,
                                    t: 0,
                                    b: 0,
                                    l: 0,
                                },
                                hoverinfo: "text",
                                // autosize:true
                            };
                            try {
                                $.get(ROOTPATH + '/portfolio/project/' + PROJECT + '/collaborators-map', function(response) {
                                    console.log(response);
                                    var data = {
                                        type: "scattermapbox",
                                        mode: "markers",
                                        hoverinfo: "text",
                                        lon: [],
                                        lat: [],
                                        text: [],
                                        marker: {
                                            size: [],
                                            color: [],
                                            // symbol: 'circle'
                                        },
                                    };

                                    console.log(response.data);

                                    response.data.forEach((item) => {
                                        data.marker.size.push((item.count * 10) / 2 + 5);
                                        var color = "#cccccc";
                                        if (item.data.role && item.data.role == "coordinator") {
                                            color = "#f78104";
                                        } else if (item.data.role && item.data.role == "partner") {
                                            color = "#008083";
                                        }
                                        data.marker.color.push(color);
                                        // data.marker.symbol.push("marker");
                                        data.lon.push(item.data.lng);
                                        data.lat.push(item.data.lat);
                                        var text = `<b>${item.data.name}</b>`;
                                        if (PROJECT && !item.data.current) {
                                            text += `<br>${item.count} ${lang(
                                            "Projects",
                                            "Projekte"
                                        )}`;
                                        }
                                        if (item.data.location) {
                                            text += `<br>${item.data.location}`;
                                        }
                                        data.text.push(text);
                                    });

                                    // Filter out empty strings and convert to numbers
                                    const validLons = data.lon.filter((lon) => lon !== "").map(Number);
                                    const validLats = data.lat.filter((lat) => lat !== "").map(Number);

                                    const minLon = Math.min(...validLons) - 1;
                                    const maxLon = Math.max(...validLons) + 1;
                                    const minLat = Math.min(...validLats) - 1;
                                    const maxLat = Math.max(...validLats) + 1;

                                    // Calculate center
                                    layout.mapbox.center.lon = (minLon + maxLon) / 2;
                                    layout.mapbox.center.lat = (minLat + maxLat) / 2;

                                    const lonRange = maxLon - minLon;
                                    const latRange = maxLat - minLat;
                                    const maxRange = Math.max(lonRange, latRange);
                                    const zoom = Math.log2(360 / maxRange) - 1; // Adjust -1 based on desired initial zoom level

                                    layout.mapbox.zoom = zoom;

                                    Plotly.newPlot("collaborator-map", [data], layout);

                                });
                            } catch (error) {
                                console.error("Error fetching collaborators map data:", error);
                                document.getElementById("collaborator-map").innerHTML = "<p>Error loading map data.</p>";
                            }
                        });
                    </script>
                <?php } ?>
            <?php } ?>

        </div>

        <div class="col-md-4 position-relative">
            <h2>
                <?= lang('Details', 'Details') ?>
            </h2>
            <table class="table ">
                <tbody>
                    <tr>
                        <td>
                            <!-- timeline progress bar -->
                            <?php
                            $progress = 0;
                            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                                $start = strtotime($data['start_date']);
                                $end = strtotime($data['end_date']);
                                $now = time();

                                if ($now < $start) {
                                    $progress = 0;
                                } elseif ($now > $end) {
                                    $progress = 100;
                                } else {
                                    $progress = round((($now - $start) / ($end - $start)) * 100);
                                }
                            }

                            ?>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="key">Start</span>
                                    <b><?= format_date($data['start_date']) ?></b>
                                </div>
                                <div>
                                    <span class="key"><?= lang('End', 'Ende') ?></span>
                                    <b><?= format_date($data['end_date']) ?></b>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div> <?php if ($progress == 100) { ?>
                                <small class="text-secondary">
                                    <?= lang('Completed', 'Abgeschlossen') ?>
                                </small>
                            <?php } ?>
                        </td>
                    </tr>

                    <!-- topics -->
                    <?php if (!empty($data['topics'])) { ?>
                        <tr>
                            <td>
                                <span class="key"><?= $Settings->topicLabel() ?></span>
                                <div class="topics">
                                    <?php foreach ($data['topics'] as $t) { ?>
                                        <a href="<?= $base ?>/topic/<?= $t['id'] ?>" class="topic-badge" style="--primary-color: <?= $t['color'] ?? 'var(--primary-color)' ?>; --primary-color-20: <?= isset($t['color']) ? $t['color'] . '33' : 'var(--primary-color-20)' ?>">
                                            <i class="ph ph-arrow-circle-right"></i>
                                            <?= lang($t['name'], $t['name_de'] ?? null) ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td>
                            <span class="key"><?= lang('Project type', 'Projekttyp') ?></span>
                            <b><?= lang($data['type'] ?? '-') ?></b>
                        </td>
                    </tr>
                    <?php if (isset($data['funder'])): ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Third-party funder', 'Drittmittelgeber') ?></span>
                                <b><?= $data['funder'] ?? '-' ?></b>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($data['funding_organization'])): ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Funding organization', 'Förderorganisation') ?></span>
                                <b><?= $data['funding_organization'] ?? '-' ?></b>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($data['funding_number'])): ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Funding reference number(s)', 'Förderkennzeichen') ?></span>
                                <b><?= implode(', ', $data['funding_number'] ?? []) ?></b>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($data['coordinator'])): ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Coordinator facility', 'Koordinator-Einrichtung') ?></span>
                                <b><?= $data['coordinator'] ?? '-' ?></b>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if (isset($data['img'])) {
                        if ($Portfolio->isPreview()) {
                            $img_path = $data['img'];
                        } else {
                            $ext = pathinfo($data['img'], PATHINFO_EXTENSION);
                            $img_path = './image.' . $ext;
                        }
                    ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Project logo', 'Projektlogo') ?></span>
                                <div>
                                    <img src="<?= $img_path ?>" class="img-fluid" alt="<?= lang('Project image', 'Projektbild') ?>">
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


            <!-- on this page navigation -->

            <nav class="on-this-page-nav">
                <div class="content">
                    <div class="title"><?= lang('On this page', 'Auf dieser Seite') ?></div>

                    <a href="#about">
                        <?= lang('About this project', 'Über das Projekt') ?>
                    </a>
                    <a href="#team">
                        <?= lang('Team', 'Team') ?>
                    </a>
                    <?php if ($data['activities'] > 0) { ?>
                        <a href="#research-output">
                            <?= lang('Research Output', 'Forschungsergebnisse') ?>
                        </a>
                    <?php } ?>
                    <?php if (!empty($data['collaborators'] ?? [])) { ?>
                        <a href="#collaborators">
                            <?= lang('Collaborators', 'Kooperationspartner') ?>
                        </a>
                    <?php } ?>

                </div>
            </nav>

        </div>
    </div>

</section>