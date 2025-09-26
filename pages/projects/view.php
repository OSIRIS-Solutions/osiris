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

require_once BASEPATH . "/php/Project.php";
$Project = new Project($project);

$type = $project['type'] ?? 'third-party';

$user_project = false;
$user_role = null;
$persons = $project['persons'] ?? array();
foreach ($persons as $p) {
    if (strval($p['user']) == $_SESSION['username']) {
        $user_project = True;
        $user_role = $p['role'];
        break;
    }
}
$edit_perm = ($project['created_by'] == $_SESSION['username'] || $Settings->hasPermission('projects.edit') || ($Settings->hasPermission('projects.edit-own') && $user_project));

$N = $osiris->activities->count(['projects' => $project['_id']]);

$institute = $Settings->get('affiliation_details');

if (empty($institute) || !isset($institute['lat']) || empty($institute['lat'])) {
    $institute = [
        'lat' => 52,
        'lng' => 10
    ];
}

$project_type = $Project->getProjectType($project['type'] ?? 'third-party');
$parent = [];
if (isset($project['parent_id'])) {
    $parent = $osiris->projects->findOne(['_id' => $project['parent_id']]);
    $project['collaborators'] = $parent['collaborators'] ?? [];
}

$subproject = $project['subproject'] ?? false;
?>

<style>
    .pills {
        position: sticky;
        top: 8rem;
        z-index: 10;
    }
</style>

<script>
    const PROJECT = '<?= $id ?>';
    const CURRENT_USER = '<?= $_SESSION['username'] ?>';
    const EDIT_PERM = <?= $edit_perm ? 'true' : 'false' ?>;
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
</script>

<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>

<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<!-- // my year for the activity timeline -->
<script src="<?= ROOTPATH ?>/js/my-year.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/projects.js?v=<?= CSS_JS_VERSION ?>"></script>

<style>
    td .key {
        display: block;
        color: var(--muted-color);
        font-size: 1.2rem;
    }
</style>


<?php if ($Settings->featureEnabled('portal') && ($project['public'] ?? true)) { ?>
    <a class="btn float-right" href="<?= ROOTPATH ?>/preview/project/<?= $id ?>">
        <i class="ph ph-eye ph-fw"></i>
        <?= lang('Preview', 'Vorschau') ?>
    </a>
<?php } ?>

<div class="title mb-20">

    <b class="badge text-uppercase primary">
        <?php if ($subproject) { ?>
            <?= lang('Subproject', 'Teilprojekt') ?>
        <?php } else { ?>
            <?= lang('Project', 'Projekt') ?>
        <?php } ?>
    </b>
    <h1 class="mt-0">
        <?= $project['name'] ?>
    </h1>

    <h2 class="subtitle">
        <?= $project['title'] ?>
    </h2>
</div>

<!-- show research topics -->
<?php
$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
if ($topicsEnabled) {
    echo $Settings->printTopics($project['topics'] ?? [], 'mb-20', false);
}
?>


<div class="d-flex" id="project-badges">

    <div class="mr-10 badge bg-white">
        <small><?= lang('Type of Projects', 'Art des Projekts') ?>: </small>
        <br />
        <?= $Project->getType() ?>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= lang('Time frame', 'Zeitraum') ?>: </small>
        <br />
        <b><?= $Project->getDateRange() ?></b>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= lang('Duration', 'Dauer') ?>: </small>
        <br />
        <b><?= $Project->getDuration() ?> <?= lang('Month', 'Monate') ?></b>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= lang('Scope', 'Reichweite') ?>: </small>
        <br />
        <b>
            <?php
            if (!empty(($project['collaborators'] ?? []))) {
                $scope = $Project->getScope();
                echo  $scope['scope'] . ' (' . $scope['region'] . ')';
            } else {
                echo lang('No collaborators', 'Keine Partner');
            }
            ?>
        </b>
    </div>

    <?php if ($Settings->featureEnabled('portal')) { ?>
        <?php if ($project['public'] ?? true) { ?>
            <div class="mr-10 badge success" data-toggle="tooltip" data-title="<?= lang('The approved project is shown in OSIRIS Portfolio.', 'Das bewilligte Projekt wird in OSIRIS Portfolio gezeigt.') ?>">
                <small><?= lang('Visibility', 'Sichtbarkeit') ?>: </small>
                <br />
                <i class="ph ph-globe m-0"></i> <?= lang('Shown', 'Gezeigt') ?>
            </div>
        <?php } else { ?>
            <div class="mr-10 badge danger" data-toggle="tooltip" data-title="<?= lang('This project is not shown in OSIRIS Portfolio.', 'Dieses Projekt wird nicht in OSIRIS Portfolio gezeigt.') ?>">
                <small><?= lang('Visibility', 'Sichtbarkeit') ?>: </small>
                <br />
                <i class="ph ph-globe-x m-0"></i>
                <?= lang('Not shown', 'Nicht gezeigt') ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>


<!-- TAB AREA -->

<nav class="pills mt-20 mb-0" id="project-nav">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-tree-structure" aria-hidden="true"></i>
        <?= lang('Project', 'Projektdetails') ?>
    </a>
    <?php if ($subproject) {
        // collaborators are inherited from parent project
        if (count($parent['collaborators'] ?? []) > 0) { ?>
            <a onclick="navigate('collabs')" id="btn-collabs" class="btn">
                <i class="ph ph-handshake" aria-hidden="true"></i>
                <?= lang('Collaborators', 'Kooperationspartner') ?>
                <span class="index"><?= count($project['collaborators'] ?? array()) ?></span>
            </a>
        <?php  }
    } elseif (count($project['collaborators'] ?? []) > 0) { ?>
        <a onclick="navigate('collabs')" id="btn-collabs" class="btn">
            <i class="ph ph-handshake" aria-hidden="true"></i>
            <?= lang('Collaborators', 'Kooperationspartner') ?>
            <span class="index"><?= count($project['collaborators'] ?? array()) ?></span>
        </a>
    <?php } elseif ($edit_perm) { ?>
        <a href="<?= ROOTPATH ?>/projects/collaborators/<?= $id ?>" id="btn-collabs" class="btn">
            <i class="ph ph-plus-circle" aria-hidden="true"></i>
            <?= lang('Collaborators', 'Kooperationspartner') ?>
        </a>
    <?php } else { ?>
        <a id="btn-collabs" class="btn disabled">
            <i class="ph ph-handshake" aria-hidden="true"></i>
            <?= lang('Collaborators', 'Kooperationspartner') ?>
            <span class="index">0</span>
        </a>
    <?php } ?>

    <?php if ($N > 0) { ?>
        <a onclick="navigate('activities')" id="btn-activities" class="btn">
            <i class="ph ph-suitcase" aria-hidden="true"></i>
            <?= lang('Activities', 'Aktivitäten') ?>
            <span class="index"><?= $N ?></span>
        </a>
    <?php } elseif ($edit_perm || $Settings->hasPermission('projects.connect')) { ?>
        <a id="btn-activities" class="btn" href="#add-activity">
            <i class="ph ph-plus-circle" aria-hidden="true"></i>
            <?= lang('Connect Activities', 'Aktivitäten verknüpfen') ?>
        </a>
    <?php } else { ?>
        <a id="btn-activities" class="btn disabled">
            <i class="ph ph-suitcase" aria-hidden="true"></i>
            <?= lang('Activities', 'Aktivitäten') ?>
            <span class="index">0</span>
        </a>
    <?php } ?>
    <?php if (($project_type['process'] ?? 'project') == 'proposal') { ?>
        <?php if (!isset($project['proposal_id'])) {
        } else if ($Settings->hasPermission('proposals.view') || ($edit_perm)) { ?>
            <a href="<?= ROOTPATH ?>/proposals/view/<?= $project['proposal_id'] ?>" class="btn">
                <i class="ph ph-link m-0"></i>
                <?= lang('Proposal', 'Antrag') ?>
            </a>
        <?php } else { ?>
            <button type="button" class="disabled btn" disabled>
                <i class="ph ph-link m-0"></i>
                <?= lang('Proposal', 'Antrag') ?>
            </button>
        <?php } ?>
    <?php } ?>

    <?php
    $count_history = count($project['history'] ?? []);
    if ($count_history) :
    ?>
        <a onclick="navigate('history')" id="btn-history" class="btn">
            <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
            <?= lang('History', 'Historie') ?>
            <span class="index"><?= $count_history ?></span>
        </a>
    <?php endif; ?>


    <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
        <a onclick="navigate('raw-data')" id="btn-raw" class="btn">
            <i class="ph ph-code" aria-hidden="true"></i>
            <?= lang('Raw data', 'Rohdaten')  ?>
        </a>
    <?php } ?>

</nav>


<section id="general">
    <div class="row row-eq-spacing mt-0">

        <div class="col-md-6">
            <h2>
                <?= lang('Project details', 'Projektdetails') ?>
            </h2>

            <div class="btn-toolbar mb-10">

                <?php if ($edit_perm) { ?>
                    <a href="<?= ROOTPATH ?>/projects/edit/<?= $id ?>" class="btn primary">
                        <i class="ph ph-edit"></i>
                        <?= lang('Edit', 'Bearbeiten') ?>
                    </a>
                <?php } ?>

                <?php if ($Settings->hasPermission('projects.delete') || ($Settings->hasPermission('projects.delete-own') && $edit_perm)) { ?>

                    <div class="dropdown">
                        <button class="btn danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-trash"></i>
                            <span class="sr-only"><?= lang('Delete', 'Löschen') ?></span>
                            <i class="ph ph-caret-down" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdown-1">
                            <div class="content">
                                <b class="text-danger"><?= lang('Attention', 'Achtung') ?>!</b><br>
                                <small>
                                    <?= lang(
                                        'The project is permanently deleted and the connection to all associated persons and activities is also removed. This cannot be undone.',
                                        'Das Projekt wird permanent gelöscht und auch die Verbindung zu allen zugehörigen Personen und Aktivitäten entfernt. Dies kann nicht rückgängig gemacht werden.'
                                    ) ?>
                                </small>
                                <form action="<?= ROOTPATH ?>/crud/projects/delete/<?= $project['_id'] ?>" method="post">
                                    <button class="btn btn-block danger" type="submit"><?= lang('Delete permanently', 'Permanent löschen') ?></button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <table class="table">
                <tbody>
                    <?php if (!empty($project['parent_id'])) {
                        $Parentproject = new Project();
                    ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Parent project', 'Übergeordnetes Projekt') ?></span>
                                <?php
                                if (empty($parent)) {
                                    echo lang('No parent project', 'Kein übergeordnetes Projekt');
                                } else {
                                    $Parentproject->setProject($parent);
                                    echo $Parentproject->widgetLarge();
                                }
                                ?>

                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (($project_type['subprojects'] ?? false) && !($subproject)) { ?>
                        <tr>
                            <td>
                                <span class="key">
                                    <?= lang('Subprojects', 'Teilprojekte') ?>
                                </span>
                                <?php if (count($project['subprojects'] ?? []) > 0) {
                                    $Subproject = new Project();
                                    foreach ($project['subprojects'] as $sub) {
                                        $sub = $osiris->projects->findOne(['_id' => $sub]);
                                        if (empty($sub)) continue;
                                        $Subproject->setProject($sub);
                                        echo $Subproject->widgetSubproject();
                                    }
                                } ?>
                                <?php if ($Settings->hasPermission('projects.add-subprojects')) { ?>
                                    <a href="<?= ROOTPATH ?>/projects/subproject/<?= $id ?>" id="btn-collabs" class="btn">
                                        <i class="ph ph-plus-circle" aria-hidden="true"></i>
                                        <?= lang('Add Subproject', 'Teilprojekt anlegen') ?>
                                    </a>
                                <?php } else { ?>
                                    <small class="text-muted">
                                        <?= lang('You do not have permission to add subprojects.', 'Du hast keine Berechtigung, Teilprojekte anzulegen.') ?>
                                    </small>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php
                    $fields = $Project->getFields($type, 'project');
                    foreach ($fields as $module) {
                        $key = $module['module'];
                        $value = $project[$key] ?? null;
                        if (!array_key_exists($key, $Project->FIELDS)) {
                            continue;
                        }
                        if ($key == 'nagoya' && !$Settings->featureEnabled('nagoya')) {
                            continue;
                        }
                        if ($key == 'topics' && !$Settings->featureEnabled('topics')) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td>
                                <?php
                                echo "<span class='key'>" . $Project->printLabel($key) . "</span>";
                                echo $Project->printField($key, $project[$key] ?? null);

                                if ($key == 'image' && $edit_perm) { ?>
                                    <br>
                                    <a href="#edit-image" data-toggle="modal">
                                        <i class="ph ph-image"></i>
                                        <?= lang('Edit', 'Bearbeiten') ?>
                                    </a>
                                <?php }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Created by', 'Erstellt von') ?></span>
                            <?php if (!isset($project['created_by']) || $project['created_by'] == 'system') {
                                echo 'System';
                            } else {
                                echo $DB->getNameFromId($project['created_by']);
                            }
                            echo " (" . $project['created'] . ")";
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div class="col-md-6">

            <h2>
                <?= lang('Project staff', 'Projektmitarbeitende') ?>
            </h2>


            <?php if ($edit_perm) { ?>
                <div class="btn-toolbar mb-10">
                    <a href="<?= ROOTPATH ?>/projects/persons/<?= $id ?>" class="btn primary">
                        <i class="ph ph-edit"></i>
                        <?= lang('Edit', 'Bearbeiten') ?>
                    </a>
                </div>
            <?php } ?>

            <table class="table">
                <tbody>
                    <?php
                    if (empty($project['persons'] ?? array())) {
                    ?>
                        <tr>
                            <td>
                                <?= lang('No persons connected.', 'Keine Personen verknüpft.') ?>
                            </td>
                        </tr>
                    <?php
                    } else foreach ($project['persons'] as $person) {
                        $username = strval($person['user']);

                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">

                                    <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                                    <div>
                                        <h5 class="my-0">
                                            <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="">
                                                <?= $person['name'] ?? $username ?>
                                            </a>
                                        </h5>
                                        <?= $Project->personRole($person['role']) ?>
                                        <br>
                                        <?= fromToYear($person['start'] ?? $project['start'], $person['end'] ?? $project['end']) ?>
                                    </div>

                                </div>
                            </td>
                        </tr>
                    <?php
                    } ?>

                </tbody>
            </table>

            <h2>
                <?= lang('Units', 'Einheiten') ?>
            </h2>
            <table class="table unit-table w-full">
                <tbody>
                    <?php
                    $units = DB::doc2Arr($project['units'] ?? []);
                    // $tree =  $Groups->getPersonHierarchyTree($units);
                    if (!empty($units)) {
                        $hierarchy = $Groups->getPersonHierarchyTree($units);
                        $tree = $Groups->readableHierarchy($hierarchy);

                        foreach ($tree as $row) { ?>
                            <tr>
                                <td class="indent-<?= ($row['indent']) ?>">
                                    <a href="<?= ROOTPATH ?>/groups/view/<?= $row['id'] ?>">
                                        <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                    </a>
                                </td>
                            </tr>
                    <?php }
                    } else { ?>
                        <tr>
                            <td>
                                <?= lang('No units connected.', 'Keine Einheiten verknüpft.') ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


        </div>
    </div>

</section>

<section id="collabs" style="display:none">

    <h2>
        <?= lang('Collaborators', 'Kooperationspartner') ?>
    </h2>

    <?php if ($edit_perm && !$subproject) { ?>
        <div class="btn-toolbar mb-10">
            <a href="<?= ROOTPATH ?>/projects/collaborators/<?= $id ?>" class="btn primary">
                <i class="ph ph-edit"></i>
                <?= lang('Edit', 'Bearbeiten') ?>
            </a>
        </div>
    <?php } ?>

    <?php if ($subproject) { ?>
        <p class="text-primary">
            <i class="ph ph-info"></i>
            <?= lang('Based on parent project', 'Basierend auf dem übergeordneten Projekt') ?>
        </p>
    <?php } ?>


    <div class="row row-eq-spacing">
        <div class="col-lg-4">

            <table class="table">
                <tbody>
                    <?php
                    include_once BASEPATH . '/php/Organization.php';
                    if (empty($project['collaborators'] ?? array())) {
                    ?>
                        <tr>
                            <td>
                                <?= lang('No collaborators connected.', 'Keine Partner verknüpft.') ?>
                            </td>
                        </tr>
                    <?php
                    } else foreach ($project['collaborators'] as $collab) {
                        if (isset($collab['organization']) && is_array($collab['organization'])) {
                            $collab['organization'] = $collab['organization']['_id'];
                        }
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span data-toggle="tooltip" data-title="<?= $collab['type'] ?>" class="badge mr-10">
                                        <?= Organization::getIcon($collab['type'], 'ph-fw ph-2x m-0') ?>
                                    </span>
                                    <div class="">
                                        <a class="d-block font-weight-bold" href="<?= ROOTPATH ?>/organizations/view/<?= $collab['organization'] ?>">
                                            <?= $collab['name'] ?>
                                        </a>
                                        <?= $collab['location'] ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                    } ?>

                </tbody>
            </table>

            <div class="alert primary my-20">

                <small class="text-muted float-right">
                    <?= lang('Based on partners', 'Basierend auf Partnern') ?>
                </small>

                <h5 class="title mb-0">
                    Scope
                </h5>
                <?php
                $scope = $Project->getScope();
                echo  $scope['scope'] . ' (' . $scope['region'] . ')';
                ?>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="box my-0">
                <div id="map" class=""></div>
            </div>
            <p>
                <i class="ph ph-fill ph-circle" style="color:#f78104"></i>
                <?= lang('Coordinator', 'Koordinator') ?>
                <br>
                <i class="ph ph-fill ph-circle" style="color:#008083"></i>
                Partner
            </p>
        </div>
    </div>

    <script>
        const id = '<?= $_GET['project'] ?? null ?>';
        console.log(id);
        const collaborator_id = '<?= ($subproject) ? strval($project['parent_id']) : $id ?>';
        <?php if (empty($project['collaborators'] ?? array())) { ?>
            collabChart = true;
        <?php } ?>
    </script>


</section>

<section id="activities" style="display:none">

    <h2>
        <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
        (<?= $N ?>)
    </h2>

    <div class="btn-toolbar mb-10">
        <?php if ($edit_perm || $Settings->hasPermission('projects.connect')) { ?>
            <a href="#add-activity" class="btn primary">
                <i class="ph ph-plus"></i>
                <?= lang('Connect activities', 'Aktivitäten verknüpfen') ?>
            </a>
        <?php } ?>


        <div class="dropdown with-arrow btn-group ">
            <button class="btn primary" <?= $N == 0 ? 'disabled' : '' ?> data-toggle="dropdown" type="button" id="download-btn" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-download"></i> Download
                <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="download-btn">
                <div class="content">
                    <form action="<?= ROOTPATH ?>/download" method="post">

                        <input type="hidden" name="filter[project]" value="<?= $project['name'] ?>">

                        <div class="form-group">

                            <?= lang('Highlight:', 'Hervorheben:') ?>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                                <label for="highlight-user"><?= lang('Me', 'Mich') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang(' Authors', '-Autoren') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-none" value="">
                                <label for="highlight-none"><?= lang('None', 'Nichts') ?></label>
                            </div>

                        </div>


                        <div class="form-group">

                            <?= lang('File format:', 'Dateiformat:') ?>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-word" value="word" checked="checked">
                                <label for="format-word">Word</label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-bibtex" value="bibtex">
                                <label for="format-bibtex">BibTex</label>
                            </div>

                        </div>
                        <button class="btn primary">Download</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="box">
        <div class="content">
            <div class="btn-toolbar justify-content-between">
                <div id="event-selector"></div>
                <div>
                    <div class="input-group small mr-10">
                        <div class="input-group-prepend">
                            <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) - 1).change()"><i class="ph ph-caret-left"></i></button>
                        </div>
                        <input type="number" class="form-control" id="activity-year" placeholder="<?= lang('Year', 'Jahr') ?>" value="<?= date('Y') ?>" onchange="timelineChart({'projects':  PROJECT})">
                        <div class="input-group-append">
                            <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) + 1).change()"><i class="ph ph-caret-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="timeline"></div>
    </div>

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

</section>


<?php if ($edit_perm) { ?>
    <!-- Modal for public image -->
    <div class="modal" id="edit-image" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="modal-title">
                    <?= lang('Edit image', 'Bild bearbeiten') ?>
                </h5>

                <div class="form-group">
                    <p>
                        <?= lang('Upload an image (e.g.) Logo for the project. The image will be displayed in the metadata.', 'Lade ein Bild (z.B. ein Logo) für das Projekt hoch, das bei den Metadaten auf der Projektseite gezeigt wird.') ?>
                    </p>
                    <!-- show current image if any -->
                    <?php if (!empty($project['image'])) : ?>
                        <img src="<?= ROOTPATH . '/uploads/' . $project['image'] ?>" alt="<?= $project['name'] ?>" class="w-400">
                    <?php endif; ?>

                    <form action="<?= ROOTPATH ?>/crud/projects/image/<?= $project['_id'] ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                        <input type="hidden" name="type" value="image">
                        <div class="custom-file">
                            <input type="file" id="image" name="file" accept=".jpg,.png,.gif" data-default-value="<?= lang('No image uploaded', 'Kein Bild hochgeladen') ?>">
                            <label for="image"><?= lang('Upload image', 'Bild hochladen') ?></label>
                        </div>
                        <button class="btn primary mt-20">
                            <i class="ph ph-check"></i>
                            <?= lang('Submit', 'Bestätigen') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<?php if ($edit_perm || $Settings->hasPermission('projects.connect')) { ?>
    <!-- Modal for connecting activities -->
    <div class="modal" id="add-activity" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="modal-title">
                    <?= lang('Connect activities', 'Aktivitäten verknüpfen') ?>
                </h5>

                <form action="<?= ROOTPATH ?>/crud/projects/connect-activities" method="post" class="">

                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                    <input type="hidden" name="project" value="<?= $id ?>">

                    <!-- input field with suggesting activities -->
                    <div class="form-group" id="activity-suggest">
                        <!-- <label for="activity-suggested"><?= lang('Activity', 'Aktivität') ?></label> -->
                        <input type="text" name="activity-suggested" id="activity-suggested" class="form-control" required placeholder="...">
                        <div class="suggestions on-focus">
                            <div class="content"><?= lang('Start typing to search for activities', 'Beginne zu tippen, um Aktivitäten zu suchen') ?></div>
                        </div>
                    </div>
                    <input type="hidden" name="activity" id="activity-selected" required value="">

                    <button class="btn primary">
                        <i class="ph ph-check"></i>
                        <?= lang('Submit', 'Bestätigen') ?>
                    </button>
                </form>

                <style>
                    .suggestions {
                        color: #464646;
                        /* position: absolute; */
                        margin: 10px auto;
                        top: 100%;
                        left: 0;
                        height: 19.2rem;
                        overflow: auto;
                        bottom: -3px;
                        width: 100%;
                        box-sizing: border-box;
                        min-width: 12rem;
                        background-color: white;
                        border: var(--border-width) solid #afafaf;
                        /* visibility: hidden; */
                        /* opacity: 0; */
                        z-index: 100;
                        -webkit-transition: opacity 0.4s linear;
                        transition: opacity 0.4s linear;
                    }

                    .suggestions a {
                        display: block;
                        padding: 0.5rem;
                        border-bottom: var(--border-width) solid #afafaf;
                        color: #464646;
                        text-decoration: none;
                        width: 100%;
                    }

                    .suggestions a:hover {
                        background-color: #f0f0f0;
                    }
                </style>

                <!-- script to handle auto suggest by ajax -->
                <script>
                    $('#activity-suggested').on('input', function() {
                        // prevent enter from submitting form
                        $(this).closest('form').on('keypress', function(event) {
                            if (event.keyCode == 13) {
                                event.preventDefault();
                            }
                        })
                        const val = $(this).val();
                        if (val.length < 3) return;
                        $.get('<?= ROOTPATH ?>/api/activities-suggest/' + val + '?exclude-project=' + PROJECT, function(data) {
                            $('#activity-suggest .suggestions').empty();
                            console.log(data);
                            data.data.forEach(function(d) {
                                $('#activity-suggest .suggestions').append(
                                    `<a onclick="selectActivity(this)" data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                                )
                            })
                            $('#activity-suggest .suggestions a')
                                .on('click', function(event) {
                                    event.preventDefault();
                                    console.log(this);
                                    $('#activity-suggested').val($(this).text());
                                    $('#activity-selected').val($(this).data('id'));
                                    $('#activity-suggest .suggestions').empty();
                                })
                            // $('#activity-suggest .suggest').html(data);
                        })
                    })
                </script>
            </div>
        </div>
    </div>
<?php } ?>



<!-- new section with history -->
<section id="history" style="display: none;">
    <h2 class="title">
        <?= lang('History', 'Historie') ?>
    </h2>
    <p>
        <?= lang('History of changes to this activity.', 'Historie der Änderungen an dieser Aktivität.') ?>
    </p>

    <?php
    if (empty($project['history'] ?? [])) {
        echo lang('No history available.', 'Keine Historie verfügbar.');
    } else {
    ?>
        <div class="history-list">
            <?php foreach (($project['history']) as $h) {
                if (!isset($h['type'])) continue;
            ?>
                <div class="box p-20">
                    <span class="badge primary float-md-right"><?= date('d.m.Y', strtotime($h['date'])) ?></span>
                    <h5 class="m-0">
                        <?php if ($h['type'] == 'created') {
                            echo lang('Created by ', 'Erstellt von ');
                        } else if ($h['type'] == 'edited') {
                            echo lang('Edited by ', 'Bearbeitet von ');
                        } else if ($h['type'] == 'imported') {
                            echo lang('Imported by ', 'Importiert von ');
                        } else {
                            echo $h['type'] . lang(' by ', ' von ');
                        }
                        if (isset($h['user']) && !empty($h['user'])) {
                            echo '<a href="' . ROOTPATH . '/profile/' . $h['user'] . '">' . $DB->getNameFromId($h['user']) . '</a>';
                        } else {
                            echo "System";
                        }
                        ?>
                    </h5>

                    <?php
                    if (isset($h['changes']) && !empty($h['changes'])) {
                        echo '<div class="font-weight-bold mt-10">' .
                            lang('Changes to the project:', 'Änderungen am Projekt:') .
                            '</div>';
                        echo '<table class="table simple w-auto small border px-10">';
                        foreach ($h['changes'] as $key => $change) {
                            $before = $change['before'] ?? '<em>empty</em>';
                            $after = $change['after'] ?? '<em>empty</em>';
                            if ($before == $after) continue;
                            if (empty($before)) $before = '<em>empty</em>';
                            if (empty($after)) $after = '<em>empty</em>';
                            echo '<tr>
                                <td class="pl-0">
                                    <span class="key">' . $Project->printLabel($key) . '</span> 
                                    <span class="del">' . $before . '</span>
                                    <i class="ph ph-arrow-right mx-10"></i>
                                    <span class="ins">' . $after . '</span>
                                </td>
                            </tr>';
                        }
                        echo '</table>';
                    } else  if (isset($h['data']) && !empty($h['data'])) {
                        echo '<div class="font-weight-bold mt-10">' .
                            lang('Status at this time point:', 'Status zu diesem Zeitpunkt:') .
                            '</div>';

                        echo '<table class="table simple w-auto small border px-10">';
                        foreach ($h['data'] as $key => $datum) {
                            echo '<tr>
                                <td class="pl-0">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    ' . $datum . ' 
                                </td>
                            </tr>';
                        }
                        echo '</table>';
                    } else if ($h['type'] == 'edited') {
                        echo lang('No changes tracked.', 'Es wurden keine Änderungen verfolgt.');
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>

<!-- raw data -->
<section id="raw-data" style="display: none;">
    <h2 class="title">
        <?= lang('Raw data', 'Rohdaten') ?>
    </h2>
    <p>
        <?= lang('Raw data of this activity.', 'Rohdaten dieser Aktivität.') ?>
    </p>
    <div class="box overflow-x-auto mt-0">
        <?php
        dump($project);
        ?>
    </div>

</section>