<?php

/**
 * The detail view of a topic
 * Created in cooperation with bicc
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.8
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<style>
    .topic-image {
        width: 100%;
        overflow: hidden;
        position: relative;
    }

    .topic-image img {
        width: 100%;
        height: auto;
    }
</style>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css">

<style>
    .table.cards#group-table {
        border: none;
        background: transparent;
        box-shadow: none;
    }

    .table.cards#group-table thead {
        display: none;
    }

    .table.cards#group-table tbody {
        display: flex;
        flex-grow: column;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .table.cards#group-table tr {
        width: 100%;
        margin: 0.5em;
        border: 1px solid var(--border-color);
        border-radius: 0.5em;
        box-shadow: var(--box-shadow);
        background: white;
    }

    .table.cards#group-table tr td {
        border: 0;
        box-shadow: none;
        width: 100%;
        height: 100%;
        display: block;
    }

    .table.cards#group-table tr td.inactive {
        opacity: 0.7;
        position: relative;
        padding-top: 1.8em;
    }

    .table.cards#group-table tr td.inactive::before {
        content: 'inactive';
        position: absolute;
        top: 0;
        left: 0;
        background-color: var(--border-color);
        color: white;
        border-bottom-right-radius: var(--border-radius);
        padding: 0.2em 0.5em;
        font-size: 1.2rem;
        font-weight: bold;
    }


    .table.cards#group-table tr td h5 {
        margin: 0;
    }

    .table.cards#group-table a.title {
        color: var(--highlight-color);
        font-size: 1.6rem;
    }


    @media (min-width: 768px) {
        .table.cards#group-table tbody tr {
            width: calc(50% - 1.4rem);
        }
    }

    @media (min-width: 1200px) {
        .table.cards#group-table tbody tr {
            width: calc(33.3% - 1.4rem);
        }
    }
</style>

<!-- all necessary javascript -->
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script>

<script>
    const TOPIC = '<?= $topic['id'] ?>';
</script>

<script src="<?= ROOTPATH ?>/js/my-year.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/topics.js?v=<?= CSS_JS_VERSION ?>"></script>

<div class="topic" style="--topic-color: <?= $topic['color'] ?? '#333333' ?>">

    <div class="topic-image">
        <?php if (!empty($topic['image'] ?? null)) : ?>
            <img src="<?= ROOTPATH . '/uploads/' . $topic['image'] ?>" alt="<?= $topic['name'] ?>">
        <?php else : ?>
            <img src="<?= ROOTPATH ?>/img/osiris-topic-banner-trans.png" alt="No topic image set" style="background: var(--topic-color);">
        <?php endif; ?>
        <?php if ($Settings->hasPermission('topics.edit')) { ?>
            <a href="#upload-image" class="btn circle position-absolute bottom-0 right-0 m-10"><i class="ph ph-edit"></i></a>
        <?php } ?>
    </div>

    <h1 class="title">
        <span class="topic-icon"></span>
        <?= lang($topic['name'], $topic['name_de'] ?? null) ?>
    </h1>

    <h2 class="subtitle">
        <?= lang($topic['subtitle'], $topic['subtitle_de'] ?? null) ?>
    </h2>

    <?php if ($Settings->hasPermission('topics.edit')) { ?>
        <a href="<?= ROOTPATH ?>/topics/edit/<?= $topic['_id'] ?>">
            <i class="ph ph-edit"></i>
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>
    <?php } ?>
</div>

<nav class="pills mt-20 mb-0">
    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-info" aria-hidden="true"></i>
        <?= lang('General', 'Allgemein') ?>
    </a>

    <?php
    $group_filter = [
        'topics' => $topic['id'],
        'is_active' => ['$ne' => false],
    ];
    $count_groups = $osiris->groups->count($group_filter);
    if ($count_groups > 0) { ?>
        <a onclick="navigate('groups')" id="btn-groups" class="btn">
            <i class="ph ph-users-three" aria-hidden="true"></i>
            <?= lang('Groups', 'Gruppen') ?>
            <span class="index"><?= $count_groups ?></span>
        </a>
    <?php } ?>

    <?php
    $person_filter = [
        'topics' => $topic['id'],
        'is_active' => ['$ne' => false],
    ];
    $count_persons = $osiris->persons->count($person_filter);
    if ($count_persons > 0) { ?>
        <a onclick="navigate('persons')" id="btn-persons" class="btn">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Persons', 'Personen') ?>
            <span class="index"><?= $count_persons ?></span>
        </a>
    <?php } ?>

    <?php
    $publication_filter = [
        'topics' => $topic['id'],
        'type' => 'publication'
    ];
    $count_publications = $osiris->activities->count($publication_filter);
    if ($count_publications > 0) { ?>
        <!-- <a onclick="navigate('publications')" id="btn-publications" class="btn">
            <i class="ph ph-books" aria-hidden="true"></i>
            <?= lang('Publications', 'Publikationen')  ?>
            <span class="index"><?= $count_publications ?></span>
        </a> -->
    <?php } ?>

    <?php
    $activities_filter = [
        'topics' => $topic['id'],
        // 'type' => ['$ne' => 'publication']
    ];
    $count_activities = $osiris->activities->count($activities_filter);

    if ($count_activities > 0) { ?>
        <a onclick="navigate('activities')" id="btn-activities" class="btn">
            <i class="ph ph-folders" aria-hidden="true"></i>
            <?= lang('Activities', 'Aktivitäten')  ?>
            <span class="index"><?= $count_activities ?></span>
        </a>
    <?php } ?>

    <?php if ($Settings->featureEnabled('projects')) { ?>
        <?php
        $project_filter = [
            'topics' => $topic['id']
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
    <?php if ($count_publications > 0) { ?>
        <a onclick="navigate('graph')" id="btn-graph" class="btn">
            <i class="ph ph-graph" aria-hidden="true"></i>
            <?= lang('Graph')  ?>
        </a>
        <a onclick="navigate('wordcloud')" id="btn-wordcloud" class="btn">
            <i class="ph ph-cloud" aria-hidden="true"></i>
            <?= lang('Word cloud')  ?>
        </a>
    <?php } ?>
</nav>


<section id="general">
    <p>
        <?= lang($topic['description'], $topic['description_de'] ?? null) ?>
    </p>

    <?php if ($Settings->hasPermission('topics.delete')) { ?>
        <br>
        <div class="alert danger mt-20">
            <a onclick="$('#delete').slideToggle()">
                <?= lang('Delete', 'Löschen') ?>
                <i class="ph ph-caret-down"></i>
            </a>

            <div id="delete" style="display: none;">
                <form action="<?= ROOTPATH ?>/crud/topics/delete/<?= $topic['_id'] ?>" method="post">
                    <p>
                        <?= lang(
                            'Do you really want to delete this topic? If you delete, it will be removed from all connected persons, activities and projects.',
                            'Möchten Sie diesen Bereich wirklich löschen? Falls du löscht wird er von allen verknüpften Elementen (Aktivitäten, Personen, Projekten) ebenfalls entfernt.'
                        ) ?>
                    </p>
                    <button type="submit" class="btn danger"><?= lang('Delete', 'Löschen') ?></button>
                </form>
            </div>
        </div>

    <?php } ?>

    <!-- modal -->
    <div id="upload-image" class="modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <h3 class="title"><?= lang('Upload Image', 'Bild hochladen') ?></h3>
                <form action="<?= ROOTPATH ?>/crud/topics/upload/<?= $topic['_id'] ?>" method="post" enctype="multipart/form-data">
                    <div class="custom-file">
                        <input type="file" id="image" name="file" accept=".jpg,.png,.gif" data-default-value="<?= lang('No image uploaded', 'Kein Bild hochgeladen') ?>">
                        <label for="image"><?= lang('Select image', 'Bild auswählen') ?></label>
                    </div>
                    <button type="submit" class="btn"><?= lang('Upload', 'Hochladen') ?></button>
                </form>
            </div>
        </div>
    </div>
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


<section id="groups" style="display: none;">

    <h3><?= lang('Organisational Units', 'Organisationseinheiten') ?></h3>

    <table class="table cards w-full" id="group-table">
        <thead>
            <th></th>
            <th></th>
        </thead>
        <tbody>

            <?php foreach ($osiris->groups->find($group_filter) as $group) {
                $inactive = $group['inactive'] ?? false;
            ?>
                <tr>
                    <td class="<?= $inactive ? 'inactive' : '' ?>" id="<?= $group['id'] ?>" <?= $Groups->cssVar($group['id']) ?>>
                        <span style="display:none">
                            <!-- hidden field for sorting based on level -->
                            <?= $inactive ? '100' : $Groups->getLevel($group['id']) ?>
                        </span>
                        <span class="badge dept-id float-md-right"><?= $group['id'] ?></span>
                        <span class="text-muted"><?= $group['unit'] ?></span>
                        <h5>
                            <a href="<?= ROOTPATH ?>/groups/view/<?= $group['id'] ?>" class="title">
                                <?= lang($group['name'], $group['name_de'] ?? null) ?>
                            </a>
                        </h5>

                        <?php if (!$inactive) { ?>

                            <div class="text-muted font-size-12">
                                <?php
                                $children = $Groups->getChildren($group['id']);
                                ?>
                                <?= $osiris->persons->count(['units.unit' => ['$in' => $children],  'is_active' => ['$ne' => false]]) ?> <?= lang('Coworkers', 'Mitarbeitende') ?>
                            </div>
                            <?php if (isset($group['head'])) {
                            ?>
                                <hr>
                                <div class="mb-0">
                                    <?php
                                    $heads = DB::doc2Arr($group['head']);
                                    if (is_string($heads)) $heads = [$heads];
                                    $heads = array_map([$DB, 'getNameFromId'], $heads);
                                    ?>
                                    <i class="ph ph-crown text-signal"></i>
                                    <?= implode(', ', $heads) ?>
                                </div>
                            <?php } ?>
                        <?php } ?>

                    </td>
                </tr>
            <?php } ?>
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
    <h2><?= lang('Activities', 'Aktivitäten') ?></h2>

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
    <div class="btn-toolbar justify-content-between">
        <div id="event-selector"></div>
        <div class="input-group small mr-10" style="min-width:inherit">
            <div class="input-group-prepend">
                <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) - 1); timelineChart()"><i class="ph ph-caret-left"></i></button>
            </div>
            <input type="number" class="form-control w-50" id="activity-year" placeholder="<?= lang('Year', 'Jahr') ?>" value="<?= date('Y') ?>" onchange="timelineChart()">
            <div class="input-group-append">
                <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) + 1); timelineChart()"><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
    </div>
    <div id="timeline" class="box mt-0"></div>

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

    </section>
<?php } ?>

<section id="wordcloud" style="display:none">
    <h3 class=""><?= lang('Word cloud') ?></h3>

    <p class="text-muted">
        <?= lang('Based on the title and abstract (if available) of publications in OSIRIS.', 'Basierend auf dem Titel und Abstract (falls verfügbar) von Publikationen in OSIRIS.') ?>
    </p>
    <div id="wordcloud-chart" style="max-width: 80rem" ;></div>
</section>

<section id="collab" style="display:none">

    <?php if ($level !== 0) { ?>

        <h3><?= lang('Collaboration with other groups', 'Zusammenarbeit mit anderen Gruppen') ?></h3>
        <p class="text-muted">
            <?= lang('Based on publications within the past 5 years.', 'Basierend auf Publikationen aus den vergangenen 5 Jahren.') ?>
        </p>
        <div id="collab-chart" style="max-width: 60rem"></div>

    <?php } ?>



</section>


<section id="graph" style="display:none">
    <h3><?= lang('Graph', 'Graph') ?></h3>

    <p class="text-muted m-0">
        <?= lang('Based on publications with associated affiliations.', 'Basierend auf affilierten Publikationen.') ?>
    </p>
    <div id="collabGraph" class="mw-full w-800"></div>

</section>


<!-- 
<script>
    userTable('#user-table', {
        filter: {
            topics: '<?= $topic['id'] ?>'
        }
    });
</script>

<script>
    initActivities('#activities-table', {
        page: 'all-activities',
        display_activities: 'web',
        filter: {
            topics: '<?= $topic['id'] ?>',
            type: {
                $ne: 'publication'
            }
        }
    });
</script>

<script>
    initActivities('#publication-table', {
        page: 'all-activities',
        display_activities: 'web',
        filter: {
            topics: '<?= $topic['id'] ?>',
            type: 'publication'
        }
    });
</script> -->