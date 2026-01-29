<?php

/**
 * The detail view of an organization
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
include_once BASEPATH . "/php/Organization.php";
include_once BASEPATH . "/php/Project.php";

$Project = new Project();

$edit_perm = ($organization['created_by'] == $_SESSION['username'] || $Settings->hasPermission('organizations.edit'));

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

                <form action="<?= ROOTPATH ?>/crud/organizations/upload-picture/<?= $organization['_id'] ?>" method="post" enctype="multipart/form-data">
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
                <form action="<?= ROOTPATH ?>/crud/organizations/upload-picture/<?= $organization['_id'] ?>" method="post">
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
            <a href="<?= ROOTPATH ?>/organizations/edit/<?= $organization['_id'] ?>" class="btn primary">
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
                        <td colspan="2">
                            <span class="key"><?= lang('ROR') ?></span>
                            <?php if (!empty($organization['ror'] ?? '')) { ?>
                                <a href="<?= $organization['ror'] ?>" target="_blank" rel="noopener noreferrer">
                                    <?= $organization['ror'] ?>
                                    <i class="ph ph-arrow-square-out"></i>
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

<h2>
    <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
</h2>
<!-- TODO: add download button -->
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
            $org_id = strval($organization['_id']);
            $activities = $osiris->activities->find([
                '$or' => [
                    ['organization' => $org_id],
                    ['organizations' => $org_id]
                ]
            ], ['projection' => ['rendered' => 1]])->toArray();
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

<?php if ($Settings->featureEnabled('projects')) { ?>
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
                $projects = $osiris->projects->find([
                    '$or' => [
                        ['collaborators.organization' => $organization['_id']],
                        ['funding_organization' => $organization['_id']],
                        ['university' => $organization['_id']],

                    ]
                ])->toArray();
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



<?php if ($Settings->featureEnabled('infrastructures')) { ?>
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
                $infrastructures = $osiris->infrastructures->find(['collaborators' => $organization['_id']])->toArray();
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

<?php if ($Settings->featureEnabled('teaching-modules', true)) { ?>
    <h2>
        <?= lang('Connected teaching modules', 'Verknüpfte Lehrveranstaltungen') ?>
    </h2>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="teaching-modules-table">
            <thead>
                <tr>
                    <th><?= lang('Module No.', 'Modulnummer') ?></th>
                    <th><?= lang('Title', 'Titel') ?></th>
                    <th><?= lang('Contact person', 'Ansprechperson') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                $teaching_modules = $osiris->teaching->find([
                    'organization' => $org_id
                ])->toArray();
                foreach ($teaching_modules as $module) {

                ?>
                    <tr>
                        <td>
                            <a href="<?= ROOTPATH ?>/teaching/view/<?= strval($module['_id']) ?>">
                                <?= htmlspecialchars($module['module']) ?>
                            </a>
                        </td>
                        <td>
                            <?= htmlspecialchars($module['title']) ?>
                        </td>
                        <td>
                            <?php if (isset($module['contact_person'])) { ?>
                                <a href="<?= ROOTPATH ?>/profile/<?= $module['contact_person'] ?>">
                                    <?= $DB->getNameFromId($module['contact_person'] ?? null) ?>
                                </a>
                            <?php } ?>
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
        <form action="<?= ROOTPATH ?>/crud/organizations/delete/<?= $organization['_id'] ?>" method="post">
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