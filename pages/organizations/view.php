<?php

/**
 * The detail view of an organization
 * Created in cooperation with DSMZ
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
include_once BASEPATH . "/php/Organization.php";
include_once BASEPATH . "/php/Project.php";

$Project = new Project();

$created_by = $organization['created_by'] ?? null;
$edit_perm = ($created_by == $_SESSION['username'] || $Settings->hasPermission('organizations.edit'));

$mongo_id = $organization['_id'];
$str_id = strval($mongo_id);


$activities = $osiris->activities->find([
    '$or' => [
        ['organization' => $str_id],
        ['organizations' => $str_id]
    ]
], ['projection' => ['rendered' => 1]])->toArray();

$count_spectrum = 0;
$spectrum_filter = null;
$projects = [];
if ($Settings->featureEnabled('spectrum')) {
    $spectrum_filter =  [
        'openalex.topics.id' => ['$exists' => true],
        'type' => 'publication',
        '$or' => [['organization' => $str_id], ['organizations' => $str_id]]
    ];
    if ($Settings->featureEnabled('projects')) {
        $projects = $osiris->projects->find([
            '$or' => [
                ['collaborators.organization' => $mongo_id],
                ['funding_organization' => $mongo_id],
                ['university' => $mongo_id],
            ]
        ])->toArray();
        $project_ids = array_map(function ($project) {
            return ($project['_id']);
        }, $projects);
        $spectrum_filter['$or'][] = ['projects' => ['$in' => $project_ids]];
    }
    $count_spectrum = $osiris->activities->count($spectrum_filter);
}
$infrastructures = [];
if ($Settings->featureEnabled('infrastructures')) {
    $infrastructures = $osiris->infrastructures->find(['collaborators' => $mongo_id])->toArray();
}
$teaching_modules = [];
if ($Settings->featureEnabled('teaching-modules', true)) {
    $teaching_modules = $osiris->teaching->find(['organization' => $str_id])->toArray();
}
?>
<style>
    .org-logo {
        max-width: 15rem;
        max-height: 10rem;
        object-fit: contain;
        border-radius: 8px;

        /* border: var(--border-width) solid var(--border-color); */
        background-color: white;
    }

    .org-logo-placeholder {
        width: 10rem;
        height: 10rem;
        border-radius: 8px;
        border: var(--border-width) solid var(--primary-color);
        background-color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-color);
    }

    .org-logo-placeholder i {
        font-size: 5rem;
    }

    .edit-picture {
        position: absolute;
        padding: 1rem;
        bottom: 0;
        right: 0;
        color: var(--muted-color);
        font-size: 1rem;
    }
</style>

<?php
if ($edit_perm) { ?>
    <!-- Modal for updating the profile picture -->
    <div class="modal modal-lg" id="change-picture" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content w-600 mw-full">
                <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>

                <h2 class="title">
                    <?= lang('Change organization logo', 'Organisations-Logo ändern') ?>
                </h2>

                <form action="<?= ROOTPATH ?>/crud/organizations/upload-picture/<?= $mongo_id ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                    <div class="custom-file mb-20" id="file-input-div">
                        <input type="file" id="profile-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>" accept="image/*" required>
                        <label for="profile-input"><?= lang('Select new logo', 'Wähle ein neues Logo') ?></label>
                        <br><small class="text-danger">Max. 2 MB.</small>
                    </div>

                    <script>
                        var uploadField = document.getElementById("profile-input");

                        uploadField.onchange = function() {
                            if (this.files[0].size > 2097152) {
                                toastError(lang("File is too large! Max. 2MB is supported!", "Die Datei ist zu groß! Max. 2MB werden unterstützt."));
                                this.value = "";
                            };
                        };
                    </script>
                    <button class="btn primary">
                        <i class="ph ph-upload"></i>
                        <?= lang('Upload', 'Hochladen') ?>
                    </button>
                </form>

                <hr>
                <form action="<?= ROOTPATH ?>/crud/organizations/upload-picture/<?= $mongo_id ?>" method="post">
                    <input type="hidden" name="delete" value="true">
                    <button class="btn danger">
                        <i class="ph ph-trash"></i>
                        <?= lang('Delete current picture', 'Aktuelles Bild löschen') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>



<div class="organization">
    <div class="d-flex align-items-center mb-20">
        <div class="position-relative mr-20">
            <?php
            Organization::printLogo($organization, 'org-logo', lang('Logo of', 'Logo von ') . ' ' . $organization['name'], $organization['type'] ?? '');
            ?>

            <?php if ($edit_perm) { ?>
                <a href="#change-picture" class="edit-picture"><i class="ph ph-edit"></i></a>
            <?php } ?>
        </div>
        <h1 class="title">
            <?= $organization['name'] ?>
        </h1>
    </div>
    <div class="btn-toolbar">
        <?php if ($Settings->hasPermission('organizations.edit')) { ?>
            <a href="<?= ROOTPATH ?>/organizations/edit/<?= $mongo_id ?>" class="btn primary">
                <i class="ph ph-edit"></i>
                <?= lang('Edit organization', 'Organisation bearbeiten') ?>
            </a>
        <?php } ?>
    </div>

    <div class="row row-eq-spacing">
        <div class="col-md-6">

            <table class="table">
                <tbody>
                    <tr>
                        <td colspan="2">
                            <span class="key"><?= lang('Type', 'Typ') ?></span>
                            <div class="d-flex justify-content-between align-items-center">
                                <?= ucfirst($organization['type']) ?>
                                <?= Organization::getIcon($organization['type'], 'ph-fw ph-2x m-0') ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="key"><?= lang('Name', 'Name') ?></span>
                            <?= $organization['name'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="key"><?= lang('Synonyms / Alternative Names / Acronyms', 'Synonyme / alternative Namen / Akronyme') ?></span>
                            <?= !empty($organization['synonyms']) ? implode(', ', DB::doc2Arr($organization['synonyms'])) : '-' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Location', 'Ort') ?></span>
                            <?= $organization['location'] ?? '-' ?>
                        </td>
                        <td>
                            <span class="key"><?= lang('Country', 'Land') ?></span>
                            <?php if (!empty($organization['country'] ?? '')) { ?>
                                <?= $DB->getCountry($organization['country'], lang('name', 'name_de')) ?>
                            <?php } else { ?>
                                -
                            <?php } ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Latitude', 'Breitengrad') ?></span>
                            <?= $organization['lat'] ?? '-' ?>
                        </td>
                        <td>
                            <span class="key"><?= lang('Longitude', 'Längengrad') ?></span>
                            <?= $organization['lng'] ?? '-' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key"><?= lang('ROR') ?></span>
                            <?php if (!empty($organization['ror'] ?? '')) { ?>
                                <a href="<?= $organization['ror'] ?>" target="_blank" rel="noopener noreferrer">
                                    <i class="ph ph-arrow-square-out"></i>
                                    <?= $organization['ror'] ?>
                                </a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>

                        <td>
                            <span class="key"><?= lang('URL') ?></span>
                            <?php if (!empty($organization['url'] ?? '')) {
                                $url = $organization['url'];
                                if (isset($url['value'])) {
                                    $url = $url['value'];
                                }
                                if (!str_starts_with($url, 'http')) {
                                    $url = 'http://' . $url;
                                }
                            ?>
                                <a href="<?= $url ?>" target="_blank" rel="noopener noreferrer" class="short-link">
                                    <i class="ph ph-arrow-square-out"></i>
                                    <?= $url ?>
                                </a>
                            <?php } else { ?>
                                -
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <?php if (!empty($organization['lat'] ?? null) && !empty($organization['lng'] ?? null)) { ?>
                <div class="map-container mb-20" style="height: 300px;">
                    <div id="map" style="height: 100%;"></div>
                </div>

                <script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js" charset="utf-8"></script>
                <script>
                    var layout = {
                        title: '',
                        showlegend: false,
                        geo: {
                            scope: 'world',
                            showcountries: true,
                            showland: true,
                            showocean: true,
                            bgcolor: '#f1f1f1',
                            countrycolor: '#afafaf',
                            landcolor: '#ffffff',
                            oceancolor: '#e0e0e0',
                            subunitcolor: '#afafaf',
                            coastlinecolor: '#afafaf',
                            countrywidth: 1,
                            subunitwidth: 1,
                            resolution: 110,
                            // framewidth: 0,
                            framecolor: '#afafaf',
                            projection: {
                                type: 'natural earth'
                            },
                            // center: {
                            //     lon: <?= $organization['lng'] ?>,
                            //     lat: <?= $organization['lat'] ?>
                            // },
                        },
                        margin: {
                            r: 0,
                            t: 0,
                            b: 0,
                            l: 0
                        },
                        // no borders, no background
                        paper_bgcolor: 'transparent',
                        plot_bgcolor: 'transparent',
                    };
                    var data = [{
                        type: 'scattergeo',
                        locationmode: 'country names',
                        lat: [<?= $organization['lat'] ?>],
                        lon: [<?= $organization['lng'] ?>],
                        hoverinfo: 'text',
                        text: ['<?= addslashes(lang($organization['name'], $organization['name_de'] ?? null)) ?>'],
                        mode: 'markers',
                        marker: {
                            size: 10,
                            color: '#d62728',
                            line: {
                                width: 1,
                                color: 'rgba(68, 68, 68, 0.6)'
                            }
                        }
                    }];

                    Plotly.newPlot("map", data, layout, {
                        showLink: false
                    });
                </script>

            <?php } ?>

        </div>

    </div>
</div>


<style>
    .module {
        border: none;
        box-shadow: none;
        padding: 0;
        margin: 0;
    }

    a.module:hover {
        box-shadow: none;
        transform: none;
        color: var(--primary-color);
    }
</style>


<?php
if ($Settings->featureEnabled('spectrum') && $count_spectrum > 0) {
    $spectrum = $osiris->activities->aggregate([
        ['$match' => $spectrum_filter],

        // total number of matched activities
        ['$unwind' => '$openalex.topics'],

        // group by topic id
        ['$group' => [
            '_id' => '$openalex.topics.id',
            'count' => ['$sum' => 1],
            'sumScore' => ['$sum' => '$openalex.topics.score'],
            'topic' => ['$first' => '$openalex.topics']
        ]],

        // compute averages + share
        ['$addFields' => [
            'avg_score' => ['$divide' => ['$sumScore', '$count']],
            'share' => ['$divide' => ['$count', $count_spectrum]],
            // optional combined weight (tweakable)
            'weight' => ['$multiply' => [
                ['$divide' => ['$count', $count_spectrum]],
                ['$divide' => ['$sumScore', $count_spectrum]]
            ]]
        ]],

        // filter noise
        ['$match' => ['share' => ['$gte' => 0.05]]],

        ['$sort' => ['weight' => -1]],
        ['$limit' => 25]
    ])->toArray();
?>
    <div id="spectrum-container">
        <h2>
            <?= lang('Associated Research Spectrum', 'Assoziiertes Forschungs-Spektrum') ?>
        </h2>
        <?php
        if (!empty($spectrum)) :
            include_once BASEPATH . "/php/Spectrum.php";
            Spectrum::render($spectrum, $count_spectrum);
        else : ?>
            <p>
                <?= lang('No Research Spectrum is assigned to this organization.', 'Zu dieser Organisation ist kein Forschungs-Spektrum zugewiesen.') ?>
            </p>
        <?php endif; ?>
    </div>
<?php } ?>


<?php if (!empty($activities)) { ?>

    <h2>
        <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
    </h2>
    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="activities-table">
            <thead>
                <tr>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Activity', 'Aktivität') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($activities as $doc) {
                ?>
                    <tr>
                        <td class="w-50">
                            <?= $doc['rendered']['icon'] ?>
                        </td>
                        <td>
                            <?= $doc['rendered']['web'] ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        $('#activities-table').DataTable({
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [{
                "targets": 0,
                "orderable": false
            }]
        });
    </script>
<?php } ?>



<?php if ($Settings->featureEnabled('projects') && !empty($projects)) { ?>
    <h2>
        <?= lang('Connected projects', 'Verknüpfte Projekte') ?>
    </h2>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="projects-table">
            <thead>
                <tr>
                    <th class="w-100"><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Project', 'Projekt') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($projects as $project) {
                    $Project->setProject($project);
                ?>
                    <tr>
                        <td>
                            <?= $Project->getType('ph-fw ph-2x m-0') ?>
                        </td>
                        <td>
                            <?= $Project->widgetSmall() ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        $('#projects-table').DataTable({});
    </script>
<?php } ?>



<?php if ($Settings->featureEnabled('infrastructures') && !empty($infrastructures)) { ?>
    <h2>
        <?= lang('Connected infrastructures', 'Verknüpfte Infrastrukturen') ?>
    </h2>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="infrastructures-table">
            <thead>
                <tr>
                    <th><?= $Settings->infrastructureLabel() ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($infrastructures as $infra) {
                ?>
                    <tr>
                        <td>
                            <h6 class="m-0">
                                <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                                    <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                </a>
                                <br>
                            </h6>

                            <div class="text-muted mb-5">
                                <?php if (!empty($infra['subtitle'])) { ?>
                                    <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                                <?php } else { ?>
                                    <?= get_preview(lang($infra['description'], $infra['description_de'] ?? null), 300) ?>
                                <?php } ?>
                            </div>
                            <div>
                                <?= fromToYear($infra['start_date'], $infra['end_date'] ?? null, true) ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        $('#infrastructures-table').DataTable({});
    </script>
<?php } ?>

<?php if ($Settings->featureEnabled('teaching-modules', true) && !empty($teaching_modules)) { ?>
    <h2>
        <?= lang('Connected teaching modules', 'Verknüpfte Lehrveranstaltungen') ?>
    </h2>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="teaching-modules-table">
            <thead>
                <tr>
                    <th><?= lang('Module No.', 'Modulnummer') ?></th>
                    <th><?= lang('Title', 'Titel') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($teaching_modules as $module) {

                ?>
                    <tr>
                        <td>
                            <a href="<?= ROOTPATH ?>/teaching/view/<?= strval($module['_id']) ?>">
                                <?= e($module['module']) ?>
                            </a>
                        </td>
                        <td>
                            <?= e($module['title']) ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <script>
        $('#teaching-modules-table').DataTable({});
    </script>
<?php } ?>


<?php if ($Settings->hasPermission('organizations.delete')) { ?>
    <button type="button" class="btn danger mt-20" id="delete-organization" onclick="$('#delete-organization-confirm').toggle();$(this).toggle();">
        <i class="ph ph-trash"></i>
        <?= lang('Delete organization', 'Organisation löschen') ?>
    </button>

    <div class="mt-20 alert danger" style="display: none;" id="delete-organization-confirm">
        <form action="<?= ROOTPATH ?>/crud/organizations/delete/<?= $str_id ?>" method="post">
            <h4 class="title">
                <?= lang('Delete organization', 'Organisation löschen') ?>
            </h4>
            <p>
                <?= lang('Are you sure you want to delete this organization?', 'Sind Sie sicher, dass Sie diese Organisation löschen möchten?') ?>
            </p>
            <button type="submit" class="btn danger">
                <?= lang('Delete', 'Löschen') ?>
            </button>
        </form>
    </div>
<?php } ?>



<?php if (isset($_GET['verbose'])) {
    dump($organization, true);
} ?>