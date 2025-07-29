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
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

if (isset($_GET['group'])) {
    $group = $osiris->groups->findOne(['id' => $_GET['group']]);
}
if (!isset($group) || empty($group)) {
    $group = $osiris->groups->findOne(['level' => 0]);
}
$id = $group['id'];

include_once BASEPATH . "/php/MyParsedown.php";
$parsedown = new Parsedown;

$level = $Groups->getLevel($id);


$parent = $osiris->groups->findOne(['id' => $group['parent']]);

$child_ids = $Groups->getChildren($id);
$persons = $osiris->persons->find(['units.unit' => ['$in' => $child_ids], 'is_active' => ['$ne'=>false]], ['sort' => ['last' => 1]])->toArray();

if (isset($group['head'])) {

    $head = $group['head'];
    if (is_string($head)) $head = [$head];
    else $head = DB::doc2Arr($head);


    usort($persons, function ($a, $b) use ($head) {
        return in_array($a['username'], $head)  ? -1 : 1;
    });
} else {
    $head = [];
}


$users = array_column($persons, 'username');

$show_general = (isset($group['description']) || isset($group['description_de']) || (isset($group['research']) && !empty($group['research'])));

$institute = $Settings->get('affiliation_details');

$children = $osiris->groups->find(['parent' => $id, 'hide' => ['$ne' => true]])->toArray();

// for all parents
$breadcrumb = [];
$parents = $Groups->getParents($id, true);
foreach ($parents as $p) {
    $dept = $Groups->getGroup($p);
    if ($p == $id)
        $breadcrumb[] = ['name' => $dept['name']];
    else
        $breadcrumb[] = ['name' => $dept['name'], 'path' => "/?group=" . $p];
}

$_SESSION['last_group'] = $id;
?>
<div class="container">
    <style>
        .filter {
            overflow-y: auto;
            padding: 1rem 2rem;
            max-height: 100%;
        }

        .filter tr td {
            border-left: 3px solid transparent;
            border-bottom: none;
        }

        .filter tr td:hover {
            border-left-color: var(--primary-color);
        }

        .filter tr td.active {
            background: var(--primary-color-20);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }


        #research p,
        #general p {
            text-align: justify;
        }

        @media (min-width: 768px) {

            #research figure,
            #general .head {
                max-width: 100%;
                float: right;
                margin: 0 0 1rem 2rem;
            }
        }

        #research figure figcaption {
            font-size: 1.2rem;
            color: var(--muted-color);
            font-style: italic;
        }
    </style>


    <!-- all necessary javascript -->
    <script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/popover.js"></script>

    <script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>


    <!-- <script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?=CSS_JS_VERSION?>"></script> -->
    <!-- <script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script> -->

    <!-- all variables for this page -->

    <link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css">
    <script>
        const USERS = <?= json_encode($users) ?>;
        const DEPT_TREE = <?= json_encode($child_ids) ?>;
        const DEPT = '<?= $id ?>';
        const PORTALPATH = '<?=PORTALPATH?>';
    </script>
    <script src="<?= ROOTPATH ?>/js/units.portfolio.js?v=<?=CSS_JS_VERSION?>"></script>


    <style>
        .unit-name {
            margin: 0;
        }

        .unit-type {
            margin: 0 0 1rem 0;
            font-size: 1.4rem;
            color: var(--primary-color);
        }
    </style>

    <h2 class="unit-name"><?= $group['name'] ?></h2>
    <h4 class="unit-type"><?= $Groups->getUnit($group['unit'] ?? null, 'name') ?></h4>

    <!-- TAB AREA -->
    <style>
        .pills.small .btn,
        .pills.small .badge {
            font-size: 1.2rem;
        }

        .pills.small .index {
            font-size: 1rem;
        }
    </style>

    <nav class="pills small mb-10">
        <?php if ($show_general) { ?>
            <a onclick="navigate('general')" id="btn-general" class="btn active">
                <i class="ph ph-info" aria-hidden="true"></i>
                <?= lang('Info', 'Info') ?>
            </a>
        <?php } ?>

        <?php if (!empty($group['research'] ?? null)) { ?>
            <a onclick="navigate('research')" id="btn-research" class="btn">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('Research', 'Forschung') ?>
            </a>
        <?php } ?>



        <a onclick="navigate('persons')" id="btn-persons" class="btn <?= !$show_general ? 'active' : '' ?>">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Team', 'Team') ?>
            <span class="index"><?= count($users) ?></span>
        </a>

        <?php
        $publication_filter = [
            'authors.user' => ['$in' => $users],
            'authors.aoi' => [ '$in'=> [1, true, '1', 'true'] ],
            'type' => 'publication',
            'hide' => ['$ne' => true]
        ];
        $count_publications = $osiris->activities->count($publication_filter);

        if ($count_publications > 0) { ?>
            <a onclick="navigate('publications')" id="btn-publications" class="btn">
                <i class="ph ph-books" aria-hidden="true"></i>
                <?= lang('Publications', 'Publikationen')  ?>
                <span class="index"><?= $count_publications ?></span>
            </a>
        <?php } ?>

        <?php
        // TODO: configurable filters
        $activities_filter = [
            'authors.user' => ['$in' => $users],
            'authors.aoi' => [ '$in'=> [1, true, '1', 'true'] ],
            'type' => ['$in' => ['poster', 'lecture', 'award', 'software']],
            'hide' => ['$ne' => true]
        ];
        $count_activities = $osiris->activities->count($activities_filter);

        if ($count_activities > 0) { ?>
            <a onclick="navigate('activities')" id="btn-activities" class="btn">
                <i class="ph ph-briefcase" aria-hidden="true"></i>
                <?= lang('Activities', 'Aktivitäten')  ?>
                <span class="index"><?= $count_activities ?></span>
            </a>
        <?php } ?>

        <?php
        $membership_filter = [
            'authors.user' => ['$in' => $users],
            'subtype' => ['$in' => $Settings->continuousTypes]
        ];
        $count_memberships = 0; //$osiris->activities->count($membership_filter); TODO
        if ($count_memberships > 0) { ?>
            <a onclick="navigate('memberships')" id="btn-memberships" class="btn">
                <i class="ph ph-user-list" aria-hidden="true"></i>
                <?= lang('Committee work', 'Gremienarbeit')  ?>
                <span class="index"><?= $count_memberships ?></span>
            </a>
        <?php } ?>

        <?php if ($Settings->featureEnabled('projects')) { ?>
            <?php
            $project_filter = [
                'persons.user' => ['$in' => $users],
                "public" => true,
                // "status" => ['$ne' => "rejected"]
            ];

            $count_projects = $osiris->projects->count($project_filter);
            if ($count_projects > 0) { ?>
                <a onclick="navigate('projects')" id="btn-projects" class="btn">
                    <i class="ph ph-tree-structure" aria-hidden="true"></i>
                    <?= lang('Projects', 'Projekte')  ?>
                    <span class="index"><?= $count_projects ?></span>
                </a>
            <?php } ?>

        <?php } ?>


    </nav>


    <section id="general">
        <!-- head -->
        <?php
        $head = $group['head'] ?? [];
        if (is_string($head)) $head = [$head];
        else $head = DB::doc2Arr($head);

        usort($persons, function ($a, $b) use ($head) {
            return in_array($a['username'], $head)  ? -1 : 1;
        });
        if (!empty($head)) { ?>
            <div class="head">
                <h5 class="mt-0"><?= $Groups->getUnit($group['unit'] ?? null, 'head') ?></h5>
                <div>
                    <?php foreach ($head as $h) { ?>
                        <a href="<?= ROOTPATH ?>/profile/<?= $h ?>" class="colorless d-flex align-items-center border bg-white p-10 rounded mt-10">
                            <?= $Settings->printProfilePicture($h, 'profile-img small mr-20') ?>
                            <div class="">
                                <h5 class="my-0">
                                    <?= $DB->getNameFromId($h) ?>
                                </h5>
                            </div>
                        </a>
                    <?php } ?>
                </div>

            </div>
        <?php } ?>



        <?php if (isset($group['description']) || isset($group['description_de'])) { ?>

            <h5>
                <?= lang('About', 'Information') ?>
            </h5>
            <?= $parsedown->text(lang($group['description'] ?? '-', $group['description_de'] ?? null)) ?>

        <?php } ?>





    </section>

    <section id="research" style="display:none;">

        <h3><?= lang('Research interests', 'Forschungsinteressen') ?></h3>

        <?php if (isset($group['research']) && !empty($group['research'])) { ?>
            <?php foreach ($group['research'] as $r) { ?>
                <div class="box">
                    <h5 class="header">
                        <?= lang($r['title'], $r['title_de'] ?? null) ?>
                    </h5>
                    <div class="content">
                        <?= $parsedown->text(lang($r['info'], $r['info_de'] ?? null)) ?>
                    </div>
                </div>

            <?php } ?>
        <?php } ?>

    </section>


    <section id="persons" style="display: none;">

        <!-- <h3><?= lang('Employees', 'Mitarbeitende Personen') ?></h3> -->

        <table class="table cards w-full" id="user-table">
            <thead>
                <th></th>
                <th></th>
            </thead>
            <tbody>
            </tbody>
        </table>
    </section>


    <section id="publications" style="display:none">

        <!-- <h2><?= lang('Publications', 'Publikationen') ?></h2> -->

        <div class="w-full">
            <table class="table dataTable responsive" id="publication-table">
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


    <section id="activities" style="display:none">


        <!-- <h2><?= lang('Other activities', 'Andere Aktivitäten') ?></h2> -->

        <div class="w-full">
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


    <?php if ($Settings->featureEnabled('projects')) { ?>
        <section id="projects" style="display:none">


            <?php if ($count_projects > 0) { ?>
                <!-- collaborators -->
                <h1>
                    <?= lang('Projects', 'Projekte') ?>
                </h1>


                <div id="collaborators">

                    <b>
                        <?= lang('Cooperation partners', 'Kooperationspartner') ?>
                    </b>
                    <div class="box mt-0 ">
                        <div id="map" class="h-300"></div>
                    </div>
                </div>


                <?php
                $projects = $osiris->projects->find($project_filter, ['sort' => ["start" => -1, "end" => -1]]);

                $ongoing = [];
                $past = [];

                require_once BASEPATH . "/php/Project.php";
                $Project = new Project();
                foreach ($projects as $project) {
                    $Project->setProject($project);
                    if ($Project->inPast()) {
                        $past[] = $Project->widgetPortal();
                    } else {
                        $ongoing[] = $Project->widgetPortal();
                    }
                }
                $i = 0;
                // $breakpoint = ceil($count_projects / 2);
                ?>
                <?php if (!empty($ongoing)) { ?>

                    <div class="row row-eq-spacing my-0">
                        <?php foreach ($ongoing as $html) { ?>
                            <div class="col-md-6">
                                <?= $html ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (!empty($past)) { ?>
                    <h2><?= lang('Past projects', 'Vergangene Projekte') ?></h2>

                    <div class="row row-eq-spacing my-0">
                        <?php foreach ($past as $html) { ?>
                            <div class="col-md-6">
                                <?= $html ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>



            <?php } ?>


        </section>
    <?php } ?>


</div>