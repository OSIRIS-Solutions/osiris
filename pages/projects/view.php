<?php

/**
 * Page to see details on a single project
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /project/<id>
 *
 * @package     OSIRIS
 * @since       1.2.2
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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
$edit_perm = (($project['created_by'] ?? '') == $_SESSION['username'] || $Settings->hasPermission('projects.edit') || ($Settings->hasPermission('projects.edit-own') && $user_project));

$count_activities = $osiris->activities->count(['projects' => $project['_id']]);
$count_spectrum = 0;
if ($Settings->featureEnabled('spectrum')) {
    $count_spectrum = $osiris->activities->count(['projects' => $project['_id'], 'openalex.topics.id' => ['$exists' => true]]);
}

$institute = $Settings->get('affiliation_details');

if (empty($institute) || !isset($institute['lat']) || empty($institute['lat'])) {
    $institute = [
        'lat' => 52,
        'lng' => 10
    ];
}

$project_type = $Project->getProjectType($project['type'] ?? 'third-party');
$parent = [];
$is_subproject = $project['subproject'] ?? false;
if (isset($project['parent_id']) && $is_subproject) {
    $parent = $osiris->projects->findOne(['_id' => $project['parent_id']]);
    $project['collaborators'] = $parent['collaborators'] ?? [];
}

$has_subprojects = ($project_type['subprojects'] ?? false) && !$is_subproject;
$children =[];
if ($has_subprojects) {
    $children = $osiris->projects->find(['parent_id' => $project['_id']])->toArray();
}

$nagoyaRelevant = false;
// Nagoya information might be stored in proposal
if ($Settings->featureEnabled('nagoya') && isset($project['proposal_id'])) {
    // 
    $proposal = $osiris->proposals->findOne(['_id' => $project['proposal_id']]);
    $proposal = $DB->doc2Arr($proposal);
    $nagoyaRelevant = (isset($proposal['nagoya']) && ($proposal['nagoya']['enabled'] ?? false));

    if ($nagoyaRelevant) {
        require_once BASEPATH . "/php/Nagoya.php";
        $nagoya_status_color = Nagoya::statusColor($proposal['nagoya']['status'] ?? 'unknown');
    }
}

$collaborators = $Project->getCollaborators();
$scope = $Project->getScope($collaborators);
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
<script src="<?= ROOTPATH ?>/js/my-year.js?v=<?= OSIRIS_BUILD ?>"></script>
<script src="<?= ROOTPATH ?>/js/projects.js?v=<?= OSIRIS_BUILD ?>"></script>

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
        <?= lang('common.preview') ?>
    </a>
<?php } ?>

<div class="title mb-20">

    <b class="badge text-uppercase primary">
        <?php if ($is_subproject) { ?>
            <?= lang('common.subproject') ?>
        <?php } else { ?>
            <?= lang('common.project') ?>
        <?php } ?>
    </b>
    <h1 class="mt-0">
        <?php if (isset($project['acronym'])) { ?>
            <?= e($project['acronym']) ?> –
        <?php } ?>
        <?= e($project['name']) ?>
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
        <small><?= lang('projects.type_of_projects') ?>: </small>
        <br />
        <?= $Project->getType() ?>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= lang('projects.time_frame') ?>: </small>
        <br />
        <b><?= $Project->getDateRange() ?></b>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= lang('common.duration') ?>: </small>
        <br />
        <b><?= $Project->getDuration() ?> <?= lang('projects.month') ?></b>
    </div>

    <div class="mr-10 badge bg-white">
        <small><?= lang('projects.scope') ?>: </small>
        <br />
        <b>
            <?php
            if (!empty($collaborators)) {
                echo  $scope['scope'] . ' (' . $scope['region'] . ')';
            } else {
                echo lang('projects.no_collaborators');
            }
            ?>
        </b>
    </div>

    <?php if ($Settings->featureEnabled('portal')) { ?>
        <?php if ($project['public'] ?? true) { ?>
            <div class="mr-10 badge success" data-toggle="tooltip" data-title="<?= lang('projects.the_approved_project_is_shown_in_osiris_portfolio') ?>">
                <small><?= lang('common.visibility') ?>: </small>
                <br />
                <i class="ph ph-globe m-0"></i> <?= lang('common.shown') ?>
            </div>
        <?php } else { ?>
            <div class="mr-10 badge danger" data-toggle="tooltip" data-title="<?= lang('projects.this_project_is_not_shown_in_osiris_portfolio') ?>">
                <small><?= lang('common.visibility') ?>: </small>
                <br />
                <i class="ph ph-globe-x m-0"></i>
                <?= lang('common.not_shown') ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>

<!-- Nagoya information -->

<?php if ($nagoyaRelevant) { ?>
    <div class="nagoya-message">
        <?php
        $whoIsNext = Nagoya::whoIsNext($proposal);
        if ($whoIsNext === 'researcher-required' && $user_project) { ?>
            <div class="alert danger mt-20">
                <h5 class="title"><?= lang('common.nagoya_protocol_review') ?></h5>
                <?= lang('common.you_are_required_to_provide_additional_nagoya_protocol_information') ?>
                <br>
                <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>" class="btn danger">
                    <i class="ph ph-clipboard-text"></i>
                    <?= lang('common.provide_information') ?>
                </a>
            </div>
        <?php } ?>
    </div>
<?php } ?>


<!-- TAB AREA -->

<nav class="pills mt-20 mb-0" id="project-nav">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-tree-structure" aria-hidden="true"></i>
        <?= lang('projects.project') ?>
    </a>
    <?php if ($is_subproject) {
        // collaborators are inherited from parent project
        if (count($parent['collaborators'] ?? []) > 0) { ?>
            <a onclick="navigate('collabs')" id="btn-collabs" class="btn">
                <i class="ph ph-handshake" aria-hidden="true"></i>
                <?= lang('common.collaborators') ?>
                <span class="index"><?= count($project['collaborators'] ?? array()) ?></span>
            </a>
        <?php  }
    } elseif (count($project['collaborators'] ?? []) > 0) { ?>
        <a onclick="navigate('collabs')" id="btn-collabs" class="btn">
            <i class="ph ph-handshake" aria-hidden="true"></i>
            <?= lang('common.collaborators') ?>
            <span class="index"><?= count($project['collaborators'] ?? array()) ?></span>
        </a>
    <?php } elseif ($edit_perm) { ?>
        <a href="<?= ROOTPATH ?>/projects/collaborators/<?= $id ?>" id="btn-collabs" class="btn">
            <i class="ph ph-plus-circle" aria-hidden="true"></i>
            <?= lang('common.collaborators') ?>
        </a>
    <?php } else { ?>
        <a id="btn-collabs" class="btn disabled">
            <i class="ph ph-handshake" aria-hidden="true"></i>
            <?= lang('common.collaborators') ?>
            <span class="index">0</span>
        </a>
    <?php } ?>

    <?php if ($count_activities > 0) { ?>
        <a onclick="navigate('activities')" id="btn-activities" class="btn">
            <i class="ph ph-suitcase" aria-hidden="true"></i>
            <?= lang('common.activities') ?>
            <span class="index"><?= $count_activities ?></span>
        </a>
    <?php } elseif ($edit_perm || $Settings->hasPermission('projects.connect')) { ?>
        <a id="btn-activities" class="btn" href="#add-activity">
            <i class="ph ph-plus-circle" aria-hidden="true"></i>
            <?= lang('projects.connect_activities') ?>
        </a>
    <?php } else { ?>
        <a id="btn-activities" class="btn disabled">
            <i class="ph ph-suitcase" aria-hidden="true"></i>
            <?= lang('common.activities') ?>
            <span class="index">0</span>
        </a>
    <?php } ?>
    <?php if (($project_type['process'] ?? 'project') == 'proposal') { ?>
        <?php if (!isset($project['proposal_id'])) {
        } else if ($Settings->hasPermission('proposals.view') || ($edit_perm)) { ?>
            <a href="<?= ROOTPATH ?>/proposals/view/<?= $project['proposal_id'] ?>" class="btn">
                <i class="ph ph-link m-0"></i>
                <?= lang('common.proposal') ?>
            </a>
        <?php } else { ?>
            <button type="button" class="disabled btn" disabled>
                <i class="ph ph-link m-0"></i>
                <?= lang('common.proposal') ?>
            </button>
        <?php } ?>
        <?php if ($nagoyaRelevant) { ?>
            <button type="button" class="btn" onclick="navigate('nagoya')" id="nagoya-btn" style="--primary-color: var(--<?= $nagoya_status_color ?>-color);--primary-color-20: var(--<?= $nagoya_status_color ?>-color-20);">
                <span><?= Nagoya::icon($proposal) ?></span>
                <?= lang('common.nagoya_protocol') ?>
            </button>
        <?php } ?>

    <?php } ?>

    <?php
    $count_history = count($project['history'] ?? []);
    if ($count_history) :
    ?>
        <a onclick="navigate('history')" id="btn-history" class="btn">
            <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
            <?= lang('common.history') ?>
            <span class="index"><?= $count_history ?></span>
        </a>
    <?php endif; ?>


    <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
        <a onclick="navigate('raw-data')" id="btn-raw" class="btn">
            <i class="ph ph-code" aria-hidden="true"></i>
            <?= lang('common.raw_data')  ?>
        </a>
    <?php } ?>

</nav>


<section id="general">
    <div class="row row-eq-spacing mt-0">

        <div class="col-md-6">
            <h2>
                <?= lang('projects.project_details') ?>
            </h2>

            <div class="btn-toolbar mb-10">

                <?php if ($edit_perm) { ?>
                    <a href="<?= ROOTPATH ?>/projects/edit/<?= $id ?>" class="btn primary">
                        <i class="ph ph-edit"></i>
                        <?= lang('action.edit') ?>
                    </a>
                <?php } ?>

                <?php if ($Settings->hasPermission('projects.delete') || ($Settings->hasPermission('projects.delete-own') && $edit_perm)) { ?>

                    <div class="dropdown">
                        <button class="btn danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                            <i class="ph ph-trash"></i>
                            <span class="sr-only"><?= lang('action.delete') ?></span>
                            <i class="ph ph-caret-down" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdown-1">
                            <div class="content">
                                <b class="text-danger"><?= lang('common.attention') ?>!</b><br>
                                <small>
                                    <?= lang('projects.the_project_is_permanently_deleted_and_the_connection_to_all_associated_per') ?>
                                </small>
                                <form action="<?= ROOTPATH ?>/crud/projects/delete/<?= $project['_id'] ?>" method="post">
                                    <button class="btn btn-block danger" type="submit"><?= lang('common.delete_permanently') ?></button>
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
                                <span class="key"><?= lang('projects.parent_project') ?></span>
                                <?php
                                if (empty($parent)) {
                                    echo lang('projects.no_parent_project');
                                } else {
                                    $Parentproject->setProject($parent);
                                    echo $Parentproject->widgetLarge();
                                }
                                ?>

                            </td>
                        </tr>
                    <?php } ?>
                    <?php if (($project_type['subprojects'] ?? false) && !($is_subproject)) { ?>
                        <tr>
                            <td>
                                <span class="key">
                                    <?= lang('common.subprojects') ?>
                                </span>
                                <?php if (count($children ?? []) > 0) {
                                    $SubProjectClass = new Project();
                                    foreach ($children as $sub) {
                                        $SubProjectClass->setProject($sub);
                                        echo $SubProjectClass->widgetSubproject();
                                    }
                                } ?>
                                <?php if ($Settings->hasPermission('projects.add-subprojects')) { ?>
                                    <a href="<?= ROOTPATH ?>/projects/subproject/<?= $id ?>" id="btn-collabs" class="btn">
                                        <i class="ph ph-plus-circle" aria-hidden="true"></i>
                                        <?= lang('projects.add_subproject') ?>
                                    </a>
                                <?php } else { ?>
                                    <small class="text-muted">
                                        <?= lang('projects.you_do_not_have_permission_to_add_subprojects') ?>
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
                                        <?= lang('action.edit') ?>
                                    </a>
                                <?php }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <span class="key"><?= lang('common.created_by_view') ?></span>
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
                <?= lang('projects.project_staff') ?>
            </h2>


            <?php if ($edit_perm) { ?>
                <div class="btn-toolbar mb-10">
                    <a href="<?= ROOTPATH ?>/projects/persons/<?= $id ?>" class="btn primary">
                        <i class="ph ph-edit"></i>
                        <?= lang('action.edit') ?>
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
                                <?= lang('common.no_persons_connected') ?>
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
                <?= lang('common.units') ?>
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
                                <?= lang('common.no_units_connected') ?>
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
        <?= lang('common.collaborators') ?>
    </h2>

    <?php if ($edit_perm && !$is_subproject) { ?>
        <div class="btn-toolbar mb-10">
            <a href="<?= ROOTPATH ?>/projects/collaborators/<?= $id ?>" class="btn primary">
                <i class="ph ph-edit"></i>
                <?= lang('action.edit') ?>
            </a>
        </div>
    <?php } ?>

    <?php if ($is_subproject) { ?>
        <p class="text-primary">
            <i class="ph ph-info"></i>
            <?= lang('projects.based_on_parent_project') ?>
        </p>
    <?php } ?>


    <div class="row row-eq-spacing">
        <div class="col-md-4">

            <table class="table">
                <tbody>
                    <?php
                    include_once BASEPATH . '/php/Organization.php';
                    if (empty($collaborators)) {
                    ?>
                        <tr>
                            <td>
                                <?= lang('common.no_collaborators_connected') ?>
                            </td>
                        </tr>
                    <?php
                    } else foreach ($collaborators as $collab) {
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span data-toggle="tooltip" data-title="<?= $collab['type'] ?>" class="badge mr-10">
                                        <?= Organization::getIcon($collab['type'], 'ph-fw ph-2x m-0') ?>
                                    </span>
                                    <div class="">
                                        <a class="d-block font-weight-bold" href="<?= ROOTPATH ?>/organizations/view/<?= $collab['id'] ?>">
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
                    <?= lang('projects.based_on_partners') ?>
                </small>

                <h5 class="title mb-0">
                    Scope
                </h5>
                <?php
                echo  $scope['scope'] . ' (' . $scope['region'] . ')';
                ?>
            </div>
        </div>
        <style>
            .circle-icon {
                display: inline-block;
                width: 16px;
                height: 16px;
                border-radius: 50%;
                margin-right: .5rem;
                background-color: #000;
            }
        </style>
        <div class="col-md-8">
            <div class="box my-0">
                <div id="map" class=""></div>
            </div>
            <p>
                <span class="circle-icon" style="background-color:#f78104"></span>
                <?= lang('common.coordinator') ?>
                <br>
                <span class="circle-icon" style="background-color:#008083"></span>
                Partner
                <br>
                <span class="circle-icon" style="background-color:#a6b1b1"></span>
                <?= lang('projects.associated_partner') ?>
            </p>
        </div>
    </div>

    <script>
        const id = '<?= $_GET['project'] ?? null ?>';
        const collaborator_id = '<?= ($is_subproject) ? strval($project['parent_id']) : $id ?>';
        <?php if (empty($project['collaborators'] ?? array())) { ?>
            collabChart = true;
        <?php } ?>
    </script>


</section>

<section id="activities" style="display:none">

    <h2>
        <?= lang('common.connected_activities') ?>
        (<?= $count_activities ?>)
    </h2>

    <div class="btn-toolbar mb-10">
        <?php if ($edit_perm || $Settings->hasPermission('projects.connect')) { ?>
            <a href="#add-activity" class="btn primary">
                <i class="ph ph-plus"></i>
                <?= lang('common.connect_activities') ?>
            </a>
        <?php } ?>


        <div class="dropdown with-arrow btn-group ">
            <button class="btn primary" <?= $count_activities == 0 ? 'disabled' : '' ?> data-toggle="dropdown" type="button" id="download-btn" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-download"></i> Download
                <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="download-btn">
                <div class="content">
                    <form action="<?= ROOTPATH ?>/download" method="post">

                        <input type="hidden" name="filter[project]" value="<?= $id ?>">

                        <div class="form-group">

                            <?= lang('common.highlight') ?>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                                <label for="highlight-user"><?= lang('common.me') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                                <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang('common.authors') ?></label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="highlight" id="highlight-none" value="">
                                <label for="highlight-none"><?= lang('common.none_download') ?></label>
                            </div>

                        </div>


                        <div class="form-group">

                            <?= lang('common.file_format') ?>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-word" value="word" checked="checked">
                                <label for="format-word">Word</label>
                            </div>

                            <div class="custom-radio ml-10">
                                <input type="radio" name="format" id="format-bibtex" value="bibtex">
                                <label for="format-bibtex">BibTeX</label>
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
                        <input type="number" class="form-control" id="activity-year" placeholder="<?= lang('common.year') ?>" value="<?= date('Y') ?>" onchange="timelineChart({'projects':  PROJECT})">
                        <div class="input-group-append">
                            <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) + 1).change()"><i class="ph ph-caret-right"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="timeline"></div>
    </div>

    <div class="row row-eq-spacing">
        <div class="col-md">

            <div class="mt-20 w-full">
                <table class="table dataTable responsive" id="activities-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.type') ?></th>
                            <th><?= lang('common.activity') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

        </div>


        <?php
        if ($Settings->featureEnabled('spectrum') && $count_spectrum > 0) {
            $spectrum = $osiris->activities->aggregate([
                ['$match' => [
                    'projects' => $project['_id'],
                    'type' => 'publication',
                    'openalex.topics' => ['$exists' => true, '$ne' => []]
                ]],

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
            <div class="col-md">
                <h3>
                    <?= lang('common.research_spectrum') ?>
                </h3>
                <?php
                if (!empty($spectrum)) :
                    include_once BASEPATH . "/php/Spectrum.php";
                    Spectrum::render($spectrum, $count_spectrum, '', '{"projects":"'.$id.'"}');
                else : ?>
                    <p>
                        <?= lang('common.no_research_spectrum_is_assigned_to_this_unit') ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php } ?>
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
                    <?= lang('projects.edit_image') ?>
                </h5>

                <div class="form-group">
                    <p>
                        <?= lang('common.upload_an_image_e_g_logo_for_the_project_the_image_will_be_displayed_in_the') ?>
                    </p>
                    <!-- show current image if any -->
                    <?php if (!empty($project['image'])) : ?>
                        <img src="<?= ROOTPATH . '/uploads/' . $project['image'] ?>" alt="<?= $project['name'] ?>" class="w-400">
                    <?php endif; ?>

                    <form action="<?= ROOTPATH ?>/crud/projects/image/<?= $project['_id'] ?>" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                        <input type="hidden" name="type" value="image">
                        <div class="custom-file">
                            <input type="file" id="image" name="file" accept=".jpg,.png,.gif" data-default-value="<?= lang('common.no_image_uploaded') ?>">
                            <label for="image"><?= lang('common.upload_image') ?></label>
                        </div>
                        <button class="btn primary mt-20">
                            <i class="ph ph-check"></i>
                            <?= lang('action.submit') ?>
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
                    <?= lang('common.connect_activities') ?>
                </h5>

                <form action="<?= ROOTPATH ?>/crud/projects/connect-activities" method="post" class="">

                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                    <input type="hidden" name="project" value="<?= $id ?>">

                    <!-- input field with suggesting activities -->
                    <div class="form-group" id="activity-suggest">
                        <!-- <label for="activity-suggested"><?= lang('common.activity') ?></label> -->
                        <input type="text" name="activity-suggested" id="activity-suggested" class="form-control" required placeholder="...">
                        <div class="suggestions on-focus">
                            <div class="content"><?= lang('common.start_typing_to_search_for_activities') ?></div>
                        </div>
                    </div>
                    <input type="hidden" name="activity" id="activity-selected" required value="">

                    <button class="btn primary">
                        <i class="ph ph-check"></i>
                        <?= lang('action.submit') ?>
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
                                    `<a  data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
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
        <?= lang('common.history') ?>
    </h2>
    <p>
        <?= lang('common.history_of_changes_to_this_activity') ?>
    </p>

    <?php
    if (empty($project['history'] ?? [])) {
        echo lang('common.no_history_available');
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
                            echo lang('common.created_by');
                        } else if ($h['type'] == 'edited') {
                            echo lang('common.edited_by');
                        } else if ($h['type'] == 'imported') {
                            echo lang('common.imported_by');
                        } else {
                            echo $h['type'] . lang('common.by');
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
                            lang('common.changes_to_the_project') .
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
                            lang('common.status_at_this_time_point') .
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
                        echo lang('common.no_changes_tracked');
                    }
                    ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</section>


<!-- nagoya details -->
<?php if ($nagoyaRelevant) {
    $nagoya_perm = $Settings->hasPermission('nagoya.view');
?>
    <section id="nagoya" style="display: none;">
        <h2 class="title">
            <?= lang('common.nagoya_protocol') ?>
        </h2>
        <div class="box padded mt-0" id="nagoya-details" style="max-width: 90rem;">
            <?php
            include BASEPATH . "/pages/proposals/nagoya-proposal-dashboard.php";
            ?>
        </div>
    </section>
<?php } ?>


<!-- raw data -->
<section id="raw-data" style="display: none;">
    <h2 class="title">
        <?= lang('common.raw_data') ?>
    </h2>
    <p>
        <?= lang('common.raw_data_of_this_activity') ?>
    </p>

    <div class="box padded overflow-x-scroll">
        <pre><?= e(json_encode($project, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
    </div>

</section>