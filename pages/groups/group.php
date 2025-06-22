<?php

/**
 * Page to view a selected group
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /groups/view/<id>
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$level = $Groups->getLevel($id);

$children = $Groups->getChildren($group['id']);

$persons = $Groups->getAllPersons($children);

if (isset($group['head'])) {
    $head = $group['head'];
    if (is_string($head)) $head = [$head];
    else $head = DB::doc2Arr($head);
} else {
    $head = [];
}

$users = array_column($persons, 'username');

$show_general = (isset($group['description']) || isset($group['description_de']) || (isset($group['research']) && !empty($group['research'])));

$edit_perm = ($Settings->hasPermission('units.add') || $Groups->editPermission($id));

$count_activities = 0;
$count_projects = 0;
$count_publications = 0;
$count_wordcloud = 0;
?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css?v=2">

<!-- all necessary javascript -->
<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script>
<!-- <script src="<?= ROOTPATH ?>/js/d3-graph.js"></script> -->

<!-- all variables for this page -->
<script>
    const USERS = <?= json_encode($users) ?>;
    const DEPT_TREE = <?= json_encode($children) ?>;
    const DEPT = '<?= $id ?>';
</script>
<!-- // my year for the activity timeline -->
<script src="<?= ROOTPATH ?>/js/my-year.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/units.js?v=<?= CSS_JS_VERSION ?>"></script>


<style>
    .dept-icon {
        border-radius: 10rem;
        color: white;
        width: 1.6em;
        height: 1.6em;
        display: inline-block;
        background-color: var(--highlight-color);
        text-align: center;
    }

    .dept-icon i.ph {
        margin: 0;
    }

    h1 {
        color: var(--highlight-color);
    }

    blockquote {
        font-style: italic;
        border-left: 5px solid var(--secondary-color);
        padding-left: 1rem;
        margin: 1rem 0;
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


<div <?= $Groups->cssVar($id) ?> class="">
    <div class="btn-toolbar">

        <?php if ($edit_perm) { ?>
            <div class="btn-group">
                <a class="btn" href="<?= ROOTPATH ?>/groups/edit/<?= $id ?>">
                    <i class="ph ph-note-pencil ph-fw"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
                <!-- <a class="btn" href="#add-person-modal">
                    <i class="ph ph-user-plus ph-fw"></i>
                    <?= lang('Add person', 'Person hinzufügen') ?>
                </a> -->
                <a href="<?= ROOTPATH ?>/groups/new?parent=<?= $id ?>" class="btn">
                    <i class="ph ph-plus-circle ph-fw"></i>
                    <?= lang('Add child unit', 'Untereinheit hinzufügen') ?>
                </a>
            </div>
        <?php } ?>


        <?php if ($Settings->featureEnabled('portal')) { ?>
            <div class="btn-group">
                <a class="btn" href="<?= ROOTPATH ?>/preview/group/<?= $id ?>">
                    <i class="ph ph-eye ph-fw"></i>
                    <?= lang('Preview', 'Vorschau') ?>
                </a>
            </div>
        <?php } ?>
    </div>

    <h1>
        <?= lang($group['name'] ?? '-', $group['name_de'] ?? null) ?>
    </h1>
    <h3 class="subtitle">
        <?= $Groups->getUnit($group['unit'] ?? null, 'name') ?>
        <b class="badge pill primary font-size-12 ml-10">
            Level <?= $Groups->getLevel($id) ?>
        </b>
    </h3>

    <?= $Settings->printTopics($group['topics'] ?? [], 'mt-10'); ?>



    <!-- TAB AREA -->

    <nav class="pills mt-20 mb-0">
        <a onclick="navigate('general')" id="btn-general" class="btn active">
            <i class="ph ph-info" aria-hidden="true"></i>
            <?= lang('General', 'Allgemein') ?>
        </a>
        <?php if (!empty($group['research'] ?? null)) { ?>

            <a onclick="navigate('research')" id="btn-research" class="btn <?= !$show_general ? 'active' : '' ?>">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('Research', 'Forschung') ?>
            </a>
        <?php } ?>


        <a onclick="navigate('persons')" id="btn-persons" class="btn <?= !$show_general ? 'active' : '' ?>">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Persons', 'Personen') ?>
            <span class="index"><?= count($users) ?></span>
        </a>

        <?php if ($level !== 0) { ?>
            <a onclick="navigate('graph')" id="btn-graph" class="btn">
                <i class="ph ph-graph" aria-hidden="true"></i>
                <?= lang('Graph')  ?>
            </a>
        <?php } ?>

        <?php if ($level !== 0) { ?>

            <?php
            $publication_filter = [
                'units' => $id,
                'type' => 'publication'
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
            $activities_filter = [
                'units' => $id,
                'type' => ['$ne' => 'publication']
            ];
            $count_activities = $osiris->activities->count($activities_filter);

            if ($count_activities > 0) { ?>
                <a onclick="navigate('activities')" id="btn-activities" class="btn">
                    <i class="ph ph-briefcase" aria-hidden="true"></i>
                    <?= lang('Activities', 'Aktivitäten')  ?>
                    <span class="index"><?= $count_activities ?></span>
                </a>
            <?php } ?>

            <?php if ($Settings->featureEnabled('projects')) { ?>
                <?php
                $project_filter = [
                    '$or' => array(
                        ['contact' => ['$in' => $users]],
                        ['persons.user' => ['$in' => $users]]
                    ),
                    "status" => ['$nin' => ["rejected", "applied"]]
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


            <?php if ($Settings->featureEnabled('wordcloud')) { ?>
                <?php
                $count_wordcloud = $osiris->activities->count([
                    'title' => ['$exists' => true],
                    'authors.user' => ['$in' => $users],
                    'type' => 'publication'
                ]);
                if ($count_wordcloud > 0) { ?>
                    <a onclick="navigate('wordcloud')" id="btn-wordcloud" class="btn">
                        <i class="ph ph-cloud" aria-hidden="true"></i>
                        <?= lang('Word cloud')  ?>
                    </a>
                <?php } ?>
            <?php } ?>

            <a onclick="navigate('collab')" id="btn-collab" class="btn">
                <i class="ph ph-users-three" aria-hidden="true"></i>
                <?= lang('Other units', 'Andere Einheiten')  ?>
            </a>

        <?php } ?>

    </nav>

    <section id="general">
        <!-- head -->

        <?php
        $children = $osiris->groups->find(['parent' => $id], ['sort' => ['order' => 1]])->toArray();

        if ($edit_perm) { ?>
            <script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
            <!-- reorder modal -->
            <div id="reorder-modal" class="modal">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2><?= lang('Reorder child units', 'Untereinheiten neu anordnen') ?></h2>
                        <form action="<?= ROOTPATH ?>/crud/groups/reorder/<?= $id ?>" method="post">
                            <p>
                                <?= lang('Drag and drop to reorder', 'Ziehen und Ablegen zum Neuanordnen') ?>
                            </p>
                            <ul id="reorder-list" class="list">
                                <?php foreach ($children as $child) { ?>
                                    <li class="cursor-pointer">
                                        <input type="hidden" name="order[]" value="<?= $child['_id'] ?>">
                                        <?= $child['name'] ?>
                                    </li>
                                <?php } ?>
                            </ul>
                            <button type="submit" class="btn"><?= lang('Save', 'Speichern') ?></button>
                        </form>
                        <script>
                            $('#reorder-list').sortable({
                                // handle: ".handle",
                                // change: function( event, ui ) {}
                            });
                        </script>
                    </div>
                </div>
            </div>

        <?php } ?>

        <div class="row row-eq-spacing">
            <div class="col-md-3">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Parent unit', 'Übergeordnete Einheit') ?></span>
                                <?php if ($group['parent']) { ?>
                                    <a href="<?= ROOTPATH ?>/groups/view/<?= $group['parent'] ?>"><?= $Groups->getName($group['parent']) ?></a>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                        </tr>
                        <tr>
                            <td>

                                <span class="key"><?= lang('Child units', 'Untereinheiten') ?></span>

                                <?php if (!empty($children)) { ?>
                                    <ul class="list">
                                        <?php foreach ($children as $child) { ?>
                                            <li>
                                                <a href="<?= ROOTPATH ?>/groups/view/<?= $child['id'] ?>"><?= lang($child['name'], $child['name_de'] ?? null) ?></a><br>
                                                <small class="text-muted"><?= $child['unit'] ?></small>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                    <?php if ($edit_perm) { ?>
                                        <a href="#reorder-modal" class="btn primary small" id="reorder">
                                            <i class="ph ph-sort-ascending"></i>
                                            <?= lang('Reorder child units', 'Untereinheiten neu anordnen') ?>
                                        </a>
                                    <?php } ?>
                                <?php } else { ?>
                                    -
                                <?php } ?>

                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col">
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
                    <style>
                        #description img {
                            width: 100%;
                            max-width: 80rem;
                        }
                    </style>
                    <h5>
                        <?= lang('About', 'Information') ?>
                    </h5>
                    <div id="description">
                        <?= lang($group['description'] ?? '-', $group['description_de'] ?? null) ?>
                    </div>
                <?php } ?>
            </div>
        </div>

    </section>


    <section id="research" style="display:none;">

        <h3><?= lang('Research interests', 'Forschungsinteressen') ?></h3>

        <?php if ($edit_perm) { ?>
            <a class="btn" href="<?= ROOTPATH ?>/groups/public/<?= $id ?>">
                <i class="ph ph-note-pencil ph-fw"></i>
                <?= lang('Edit', 'Bearbeiten') ?>
            </a>
        <?php } ?>

        <?php if (isset($group['research']) && !empty($group['research'])) { ?>
            <?php foreach ($group['research'] as $r) { ?>
                <div class="box">
                    <div class="content">
                        <h5 class="title">
                            <?= lang($r['title'], $r['title_de'] ?? null) ?>
                            <br>
                            <small class="text-muted"><?= lang($r['subtitle'] ?? '', $r['subtitle_de'] ?? null) ?></small>
                        </h5>
                        <?= lang($r['info'], $r['info_de'] ?? null) ?>
                    </div>
                    <?php if (!empty($r['projects'] ?? null)) {
                        echo '<hr>';
                        echo '<div class="content">';
                        echo '<h2>' . lang('Selected Projects', 'Ausgewählte Projekte') . '</h2>';
                        foreach ($r['projects'] as $a) {
                            echo $a;
                        }
                        echo '</div>';
                    } ?>

                    <?php if (!empty($r['activities'] ?? null)) {
                        echo '<hr>';
                        echo '<div class="content">';
                        echo '<h2>' . lang('Selected Research Activities', 'Ausgewählte Forschungsaktivitäten') . '</h2>';
                        foreach ($r['activities'] as $a) {
                            $doc = $DB->getActivity($a);
                            echo $doc['rendered']['web'];
                        }
                        echo '</div>';
                    } ?>

                </div>

            <?php } ?>
        <?php } ?>

    </section>


    <section id="persons" style="display: none;">

        <h3><?= lang('Employees', 'Mitarbeitende Personen') ?></h3>

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

        <h2><?= lang('Publications', 'Publikationen') ?></h2>

        <div class="mt-20 w-full">
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
        <h2><?= lang('Other activities', 'Andere Aktivitäten') ?></h2>

        <style>
            .type-badge {
                opacity: 0.6;
                cursor: pointer;
                margin-right: .5rem;
                color: white;
                text-decoration: line-through;
            }

            .type-badge.active {
                opacity: .8;
                text-decoration: none;
            }

            .type-badge:hover,
            .type-badge.active:hover {
                opacity: 1;
            }
        </style>
        <div class="box">
            <div class="content">
                <div class="btn-toolbar justify-content-between">
                    <div id="event-selector"></div>
                    <div>
                        <div class="input-group small mr-10">
                            <div class="input-group-prepend">
                                <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) - 1); timelineChart()"><i class="ph ph-caret-left"></i></button>
                            </div>
                            <input type="number" class="form-control" id="activity-year" placeholder="<?= lang('Year', 'Jahr') ?>" value="<?= date('Y') ?>" onchange="timelineChart()">
                            <div class="input-group-append">
                                <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) + 1); timelineChart()"><i class="ph ph-caret-right"></i></button>
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



    <?php if ($Settings->featureEnabled('projects')) { ?>
        <section id="projects" style="display:none">

            <?php
            if ($count_projects > 0) {
                $projects = $osiris->projects->find($project_filter, ['sort' => ["start" => -1, "end" => -1]]);

                $ongoing = [];
                $past = [];

                require_once BASEPATH . "/php/Project.php";
                $Project = new Project();
                foreach ($projects as $project) {
                    $Project->setProject($project);
                    if ($Project->inPast()) {
                        $past[] = $Project->widgetSmall();
                    } else {
                        $ongoing[] = $Project->widgetSmall();
                    }
                }
            ?>
                <?php if (!empty($ongoing)) { ?>
                    <h3><?= lang('Ongoing projects', 'Laufende Projekte') ?></h3>
                    <div class="row row-eq-spacing my-0">

                        <?php foreach ($ongoing as $html) { ?>
                            <div class="col-md-6">
                                <?= $html ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (!empty($past)) { ?>
                    <h3><?= lang('Past projects', 'Vergangene Projekte') ?></h3>
                    <div class="row row-eq-spacing my-0">

                        <?php foreach ($past as $html) { ?>
                            <div class="col-md-6">
                                <?= $html ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

            <?php } ?>

            <!-- <h3 class="title">
            <?= lang('Timeline of all approved projects', 'Zeitstrahl aller bewilligten Projekte') ?>
        </h3>
        <div class="box">
            <div class="content">
                <div id="project-timeline"></div>
            </div>
        </div> -->
        </section>
    <?php } ?>

    <?php if ($Settings->featureEnabled('wordcloud')) { ?>
        <section id="wordcloud" style="display:none">
            <h3 class=""><?= lang('Word cloud') ?></h3>

            <p class="text-muted">
                <?= lang('Based on the title and abstract (if available) of publications in OSIRIS.', 'Basierend auf dem Titel und Abstract (falls verfügbar) von Publikationen in OSIRIS.') ?>
            </p>
            <div id="wordcloud-chart" style="max-width: 80rem" ;></div>
        </section>
    <?php } ?>


    <section id="collab" style="display:none">

        <?php if ($level !== 0) { ?>

            <h3><?= lang('Collaboration with other groups', 'Zusammenarbeit mit anderen Gruppen') ?></h3>
            <p class="text-muted">
                <?= lang('Based on publications within the past 5 years.', 'Basierend auf Publikationen aus den vergangenen 5 Jahren.') ?>
            </p>
            <div id="collab-chart" style="max-width: 60rem"></div>

        <?php } ?>



    </section>

    <?php if ($level !== 0) { ?>

        <section id="graph" style="display:none">
            <h3><?= lang('Graph', 'Graph') ?></h3>

            <p class="text-muted m-0">
                <?= lang('Based on publications with associated affiliations.', 'Basierend auf affilierten Publikationen.') ?>
            </p>
            <div id="collabGraph" class="mw-full w-800"></div>

        </section>
    <?php } ?>

</div>

<?php if (!$show_general) { ?>
    <script>
        navigate('persons');
    </script>
<?php } ?>


<?php

if (isset($_GET['verbose'])) {
    dump($group, true);
}
?>