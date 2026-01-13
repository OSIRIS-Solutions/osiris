<?php

/**
 * Page to see details on a single unit
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /unit/<id>
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$baseUnit = false;
if ($id == '0') {
    // id
    $baseUnit = true;
}
$preselect = $open ?? $_GET['open'] ?? null;

?>
<div class="container">
    <style>
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

        .description img {
            max-width: 100%;
            height: auto;
        }


        .filter {
            overflow-y: auto;
            padding: .5rem 1rem;
            max-height: 100% !important;
            background-color: var(--box-bg-color);
            overflow-x: hidden;
        }

        .filter tr:hover {
            background-color: inherit;
        }

        .filter tr td {
            border-left: 3px solid transparent;
            border-bottom: none;
            border-radius: var(--border-radius);
        }

        .filter tr td span {
            position: relative;
            display: block;
            padding-left: 1rem;
        }

        .filter tr td span::before {
            content: "\ECE0";
            font-family: "phosphor";
            color: var(--muted-color);
            position: absolute;
            left: -1rem;
        }

        .filter tr td.openable span::before {
            content: "\E13A";
        }

        .filter tr td.level-0 {
            padding-left: 1rem !important;
        }

        .filter tr td.level-1 {
            padding-left: 2rem !important;
        }

        .filter tr td.level-2 {
            padding-left: 3rem !important;
        }

        .filter tr td.level-3 {
            padding-left: 4rem !important;
        }


        .filter tr td:hover {
            background: inherit;
            background: var(--primary-color-20);
        }

        .filter tr td span::before {
            color: var(--primary-color);
        }

        .filter tr td.active {
            background: var(--primary-color-20);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        .filter tr td span::before {
            color: var(--primary-color);
        }

        .filter tr td.open.openable span::before {
            content: "\E136";
        }

        /* #filter-column {
            transition: width 0.3s ease;
            width: 500px;
        }
        #filter-column.collapsed {
            display: none;
            width: 0;
        } */

        .scope-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            border: 1px solid var(--primary-color);
            background: transparent;
            color: var(--primary-color);
            font-weight: 500;
            font-size: 1.2rem;
            background: var(--primary-color-20);
        }

        .scope-chip:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            /* text-shadow bold hack */
            text-shadow: 0 0 1px white;
        }

        .scope-chip.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            /* text-shadow bold hack */
            text-shadow: 0 0 1px white;
        }

        #filter-column {
            max-width: 30rem;
            width: 30rem;
        }

        #filter-column #filter-toggle {
            background-color: var(--gray-color);
            width: 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.5s ease;
            font-size: .9em;
            position: relative;
        }


        #filter-column #filter-toggle span {
            /* writing-mode: vertical-rl;
            transform: rotate(180deg); */
            display: none;
            position: absolute;
            left: 100%;
            background-color: var(--primary-color);
            padding: .2rem .5rem;
            border-radius: .5rem;
            z-index: 10;
            margin-left: .5rem;
            font-size: 1.2rem;
            color: white;
            font-weight: bold;
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.2);
        }

        #filter-column.hidden-state #filter-toggle:hover span {
            display: block;
        }


        #filter-column.hidden-state #filter-toggle {
            background-color: var(--primary-color-20);
            width: 1.5rem;
        }

        #filter-column.hidden-state #filter-toggle:hover {
            background-color: var(--primary-color-30);
        }

        #filter-column.hidden-state #filter-toggle i {
            transform: rotate(180deg);
        }

        #filter-column {
            display: flex;
            max-height: 80vh;
        }

        #filter-column .filter {
            flex-grow: 1;
            overflow-y: auto;
        }

        #filter-column.hidden-state {
            width: 3rem;
        }

        #filter-column.hidden-state .filter {
            display: none;
        }
    </style>


    <?php if ($Portfolio->isPreview()) { ?>
        <!-- all necessary javascript -->
        <script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
        <script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
        <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
        <script src="<?= ROOTPATH ?>/js/popover.js"></script>

        <script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js" charset="utf-8"></script>

        <!-- all variables for this page -->
        <link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css">
        <script>
            const BASE = '<?= $base ?>';
            const DEPT = '<?= $id ?>';
        </script>
        <script src="<?= ROOTPATH ?>/js/units.portfolio.js?v=<?= CSS_JS_VERSION ?>"></script>

    <?php } ?>

    <script>
        function toggleUnitFilter() {
            const filterColumn = document.getElementById('filter-column');
            // const toggleColumn = document.getElementById('filter-toggle');
            if (!filterColumn.classList.contains('hidden-state')) {
                // filterColumn.style.display = 'block';
                // toggleColumn.style.display = 'none';
                filterColumn.classList.add('hidden-state');
            } else {
                // filterColumn.style.display = 'none';
                // toggleColumn.style.display = 'block';
                filterColumn.classList.remove('hidden-state');
            }
        }
    </script>
    <?php if ($baseUnit) { ?>
        <!-- filter by unit -->
        <!-- <button class="scope-chip" onclick="$('#filter-column').toggle(); $(this).addClass('active');">
            <i class="ph ph-list" aria-hidden="true"></i>
            <?= lang('Explore by unit', 'Erkunden nach Einheit') ?>
        </button> -->
        <!-- <button class="scope-chip" onclick="toggleUnitFilter();">
            <i class="ph ph-list" aria-hidden="true"></i>
            <?= lang('Explore by unit', 'Erkunden nach Einheit') ?>
        </button> -->
    <?php } ?>

    <div class="row row-eq-spacing">

        <div class="col-sm flex-grow-0 flex-reset <?= $baseUnit ? 'hidden-state' : '' ?>" id="filter-column">
            <div id="filter-toggle" onclick="toggleUnitFilter();">
                <i class="ph ph-caret-left" aria-hidden="true"></i>
                <span>
                    <?= lang('Explore by unit', 'Erkunden nach Einheit') ?>
                </span>
            </div>
            <?php
            $hierarchy = $Portfolio->build_unit_hierarchy($id);
            if (!empty($hierarchy) && is_array($hierarchy)): ?>
                <div class="filter">
                    <table id="filter-unit" class="table small simple">
                        <?php foreach ($hierarchy as $el): ?>
                            <?php
                            // Comments in English.
                            $hide = (bool)($el['hide'] ?? false);
                            $active = (bool)($el['active'] ?? false);
                            $open = (bool)($el['open'] ?? false);
                            $openable = (bool)($el['openable'] ?? false);

                            // Vue condition: !el.hide || el.active || el.open
                            if ($hide && !$active && !$open) {
                                continue;
                            }

                            $level = (int)($el['level'] ?? 0);

                            // Vue RouterLink target:
                            // id = (el.active ? el.parent : el.id)
                            $targetId = $active ? ($el['parent'] ?? $el['id'] ?? '') : ($el['id'] ?? '');

                            $classes = [];
                            $classes[] = 'level-' . $level;
                            if ($active) $classes[] = 'active';
                            if ($open) $classes[] = 'open';
                            if ($openable) $classes[] = 'openable';

                            $nameEn = $el['name'] ?? '';
                            $nameDe = $el['name_de'] ?? null;
                            $href = $base . '/group/' . urlencode((string)$targetId);
                            if ($el['level'] === 0 && $targetId === '') {
                                $href = '#';
                            }
                            ?>
                            <tr>
                                <td class="<?= htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8'); ?>">
                                    <a
                                        class="item d-block colorless"
                                        href="<?= $href ?>">
                                        <span><?= htmlspecialchars(lang($nameEn, $nameDe), ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-sm">

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

            <h2 class="unit-name"><?= lang($data['name'], $data['name_de'] ?? null) ?></h2>
            <h4 class="unit-type"><?= lang($data['unit']['name'] ?? '', $data['unit']['name_de'] ?? null) ?></h4>

            <style>
                nav#group-pills {
                    margin: 2rem 0;
                }

                nav#group-pills a {
                    position: relative;
                    color: #4e4e4e;
                    display: inline-block;
                    margin-right: 30px;
                    padding-top: 4px;
                    padding-bottom: 4px;
                    text-align: center;
                }

                nav#group-pills a:after {
                    content: "";
                    position: absolute;
                    width: 100%;
                    transform: scaleX(0);
                    height: 4px;
                    bottom: 0;
                    left: 0;
                    background-color: var(--primary-color);
                    transform-origin: bottom left;
                    transition: transform 0.25s ease-out;
                }

                nav#group-pills a:hover {
                    color: var(--primary-color);
                }

                nav#group-pills a:hover:after {
                    transform: scaleX(1);
                }

                nav#group-pills a.active {
                    color: var(--primary-color);
                    font-weight: 600;

                }

                nav#group-pills a.active:after {
                    transform: scaleX(1);
                    transform-origin: bottom left;
                }

                nav#group-pills i {
                    font-size: 3rem;
                    display: block;
                    margin-bottom: 10px;
                }

                nav#group-pills span {
                    position: absolute;
                    left: calc(50% + 6px);
                    top: 0px;
                    line-height: 24px;
                    height: 24px;
                    background-color: var(--box-bg-color);
                    font-size: 14px;
                    border-radius: 12px;
                    min-width: 3rem;
                    text-align: center;
                    padding: 0 5px;
                }
            </style>
            <nav id="group-pills">
                <a onclick="navigate('general')" id="btn-general" class="<?= empty($preselect) || $preselect === 'info' ? 'active' : '' ?>">
                    <i class="ph ph-info" aria-hidden="true"></i>
                    <?= lang('Info', 'Info') ?>
                </a>

                <?php if (!empty($data['research'] ?? null)) { ?>
                    <a onclick="navigate('research')" id="btn-research">
                        <i class="ph ph-lightbulb" aria-hidden="true"></i>
                        <?= lang('Research', 'Forschung') ?>
                    </a>
                <?php } ?>

                <a onclick="navigate('persons')" id="btn-persons" class="<?= $preselect === 'persons' ? 'active' : '' ?>">
                    <i class="ph ph-users" aria-hidden="true"></i>
                    <?= lang('Team', 'Team') ?>
                    <span class="index"><?= $numbers['persons'] ?></span>
                </a>

                <?php
                if ($numbers['publications'] > 0) { ?>
                    <a onclick="navigate('publications')" id="btn-publications" class="<?= $preselect === 'publications' ? 'active' : '' ?>">
                        <i class="ph ph-books" aria-hidden="true"></i>
                        <?= lang('Publications', 'Publikationen')  ?>
                        <span class="index"><?= $numbers['publications'] ?></span>
                    </a>
                <?php } ?>

                <?php
                if ($numbers['activities'] > 0) { ?>
                    <a onclick="navigate('activities')" id="btn-activities" class="<?= $preselect === 'activities' ? 'active' : '' ?>">
                        <i class="ph ph-briefcase" aria-hidden="true"></i>
                        <?= lang('Activities', 'Aktivitäten')  ?>
                        <span class="index"><?= $numbers['activities'] ?></span>
                    </a>
                <?php } ?>

                <?php
                if ($numbers['projects'] > 0) { ?>
                    <a onclick="navigate('projects')" id="btn-projects" class="<?= $preselect === 'projects' ? 'active' : '' ?>">
                        <i class="ph ph-tree-structure" aria-hidden="true"></i>
                        <?= lang('Projects', 'Projekte')  ?>
                        <span class="index"><?= $numbers['projects'] ?></span>
                    </a>
                <?php } ?>

                <?php
                if ($numbers['infrastructures'] > 0) { ?>
                    <a onclick="navigate('infrastructures')" id="btn-infrastructures" class="<?= $preselect === 'infrastructures' ? 'active' : '' ?>">
                        <i class="ph ph-cube" aria-hidden="true"></i>
                        <?= $Settings->infrastructureLabel() ?>
                        <span class="index"><?= $numbers['infrastructures'] ?></span>
                    </a>
                <?php } ?>
            </nav>


            <section id="general" <?= empty($preselect) || $preselect === 'info' ? '' : 'style="display:none"' ?> data-title="<?= lang('General information', 'Allgemeine Informationen') ?>">
                <!-- head -->
                <?php
                $head = $data['heads'] ?? [];
                if (is_string($head)) $head = [$head];
                else $head = Portfolio::doc2Arr($head);
                if (!empty($head)) { ?>
                    <div class="head">
                        <h5 class="mt-0"><?= lang($data['unit']['head'] ?? '', $data['unit']['head_de'] ?? null) ?></h5>
                        <div>
                            <?php foreach ($head as $h) { ?>
                                <a href="<?= $base ?>/person/<?= $h['id'] ?>" class="colorless d-flex align-items-center border bg-white p-10 rounded mt-10">
                                    <?= $h['img'] ?>
                                    <div class="ml-20">
                                        <h5 class="my-0">
                                            <?= $h['name'] ?>
                                        </h5>
                                        <small>
                                            <?= lang($h['position'], $h['position_de'] ?? null) ?>
                                        </small>
                                    </div>
                                </a>
                            <?php } ?>
                        </div>

                    </div>
                <?php } ?>



                <?php if (isset($data['description']) || isset($data['description_de'])) { ?>

                    <!-- <h5>
                        <?= lang('About', 'Information') ?>
                    </h5> -->
                    <div class="description">
                        <?= lang($data['description'] ?? '-', $data['description_de'] ?? null) ?>
                    </div>
                <?php } ?>





            </section>

            <section id="research" style="display:none;" data-title="<?= lang('Research topics', 'Forschungsschwerpunkte') ?>">

                <!-- <h3><?= lang('Research topics', 'Forschungsschwerpunkte') ?></h3> -->

                <?php if (isset($data['research']) && !empty($data['research'])) { ?>
                    <?php foreach ($data['research'] as $r) { ?>
                        <div class="box padded">
                            <h5 class="title">
                                <?= lang($r['title'], $r['title_de'] ?? null) ?>
                            </h5>
                            <div class="subtitle font-size-14 text-secondary">
                                <?= lang($r['subtitle'] ?? '', $r['subtitle_de'] ?? null) ?>
                            </div>

                            <div class="description">
                                <?= (lang($r['info'], $r['info_de'] ?? null)) ?>
                            </div>
                        </div>

                    <?php } ?>
                <?php } ?>

            </section>


            <section id="persons" <?= $preselect === 'persons' ? '' : 'style="display:none"' ?> data-title="<?= lang('Employees', 'Mitarbeitende Personen') ?>">

                <!-- <h3><?= lang('Employees', 'Mitarbeitende Personen') ?></h3> -->

                <table class="table cards w-full datatable" id="users-table" data-page-length="18">
                    <thead>
                        <th></th>
                        <th></th>
                    </thead>
                    <tbody>
                        <?php
                        $staff = $Portfolio->fetch_entity('unit', $id, 'staff');
                        foreach ($staff as $s) {
                        ?>
                            <tr>
                                <td><?= $s['img'] ?></td>
                                <td>
                                    <div class="w-full">
                                        <div style="display: none;"><?= $s['lastname'] ?></div>
                                        <h5 class="my-0">
                                            <a href="<?= $base ?>/person/<?= $s['id'] ?>">
                                                <?= ($s['academic_title'] ?? '') . ' ' . $s['displayname'] ?>
                                            </a>
                                        </h5>
                                        <small>
                                            <?= lang($s['position'] ?? '', $s['position_de'] ?? null) ?>
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>


            <section id="publications" <?= $preselect === 'publications' ? '' : 'style="display:none"' ?> data-title="<?= lang('Publications', 'Publikationen') ?>">

                <!-- <h2><?= lang('Publications', 'Publikationen') ?></h2> -->


                <table class="table datatable" id="publication-table"
                    data-table="publications"
                    data-tab="publications"
                    data-source="./publications.json"
                    data-page-length="20"
                    data-lang="<?= lang('en', 'de') ?>">
                    <thead>
                        <tr>
                            <th data-col="icon" data-orderable="false" data-searchable="false">Type</th>
                            <th data-col="html" data-search-col="search">Title</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </section>


            <section id="activities" <?= $preselect === 'activities' ? '' : 'style="display:none"' ?> data-title="<?= lang('Other activities', 'Andere Aktivitäten') ?>">


                <!-- <h2><?= lang('Other activities', 'Andere Aktivitäten') ?></h2> -->

                <div class="w-full">

                    <table class="table datatable" id="activities-table"
                        data-table="activities"
                        data-tab="activities"
                        data-source="./activities.json"
                        data-page-length="20"
                        data-lang="<?= lang('en', 'de') ?>">
                        <thead>
                            <tr>
                                <th data-col="icon" data-orderable="false" data-searchable="false">Type</th>
                                <th data-col="html" data-search-col="search">Title</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>


            </section>


            <section id="projects" <?= $preselect === 'projects' ? '' : 'style="display:none"' ?> data-title="<?= lang('Projects', 'Projekte') ?>">


                <?php if ($numbers['projects'] > 0) { ?>
                    <!-- collaborators -->
                    <div class="w-full">
                        <table class="table datatable responsive" id="projects-table"
                            data-table="projects"
                            data-tab="projects"
                            data-source="./projects.json"
                            data-page-length="8"
                            data-lang="<?= lang('en', 'de') ?>">
                            <thead>
                                <tr>
                                    <th data><?= lang('Project', 'Projekt') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>



                    <div id="collaborators">
                        <div class="">
                            <div id="collaborator-map"
                                class="portfolio-map map h-500 w-full"
                                data-source="./collaborators-map.json"
                                data-tab="projects"
                                data-context="unit"
                                data-lang="<?= lang('en', 'de') ?>">
                            </div>
                        </div>
                        <p>
                            <span style="color:var(--secondary-color)">&#9673;</span> <?= lang("This institution", "Diese Einrichtung") ?><br>
                            <span style="color:var(--primary-color)">&#9673;</span> <?= lang("Cooperation partner", "Kooperationspartner") ?>
                        </p>
                    </div>


                    <style>
                        #projects-table {
                            border: none;
                            background: transparent;
                            box-shadow: none;
                            display: block;
                        }

                        #projects-table thead {
                            display: none;
                        }

                        #projects-table tbody {
                            display: flex;
                            flex-grow: column;
                            flex-direction: row;
                            flex-wrap: wrap;
                        }

                        #projects-table tbody tr {
                            width: 100%;
                            margin: 0.5rem;
                            border: var(--border-width) solid var(--border-color);
                            border-radius: var(--border-radius);
                            box-shadow: var(--box-shadow);
                            background: var(--box-bg-color);
                            display: flex;
                            align-items: center;
                        }

                        #projects-table tbody tr td {
                            border: 0;
                            box-shadow: none;
                            width: 100%;
                            height: 100%;
                            display: block;
                        }

                        #projects-table tbody tr small,
                        #projects-table tbody tr p {
                            display: block;
                            margin: 0;
                        }

                        #projects-table tbody tr td {
                            display: flex;
                            /* align-items: center; */
                            border: 0;
                        }

                        @media (min-width: 768px) {
                            #projects-table tbody tr {
                                width: 48%;
                            }
                        }

                        /* 
@media (min-width: 1200px) {
  .table.cards tbody tr {
    width: calc(33.3% - 1rem);
  }
} */

                        span.link {
                            color: var(--primary-color);
                            cursor: pointer;
                            display: inline-block;
                        }

                        span.link::after {
                            content: " »";
                        }
                    </style>

                <?php } ?>


            </section>



            <section id="infrastructures" <?= $preselect === 'infrastructures' ? '' : 'style="display:none"' ?> data-title="<?= lang('Infrastructures', 'Infrastrukturen') ?>">

                <?php if ($numbers['infrastructures'] > 0) {
                    $infrastructures = $Portfolio->fetch_entity('infrastructures');
                ?>
                    <style>
                        .infra-card {
                            width: 100%;
                            margin: 0.5rem 0;
                            display: flex;
                            gap: 1rem;
                            padding: 1rem;
                        }

                        .infra-card img,
                        .infra-card .infrastructure-logo-placeholder {
                            width: 120px;
                            max-height: 120px;
                            flex-shrink: 0;
                            object-fit: contain;
                        }
                    </style>
                    <!-- infrastructures -->
                    <div class="w-full">
                        <table class="table datatable responsive" id="infrastructures-table"
                            data-lang="<?= lang('en', 'de') ?>">
                            <thead class="hidden">
                                <tr>
                                    <th data><?= lang('Infrastructure', 'Infrastruktur') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($infrastructures as $infra) { ?>
                                    <tr>
                                        <td>
                                            <div class="infra-card">

                                                <?php
                                                echo $infra['logo'] ?? '';
                                                ?>
                                                <div>
                                                    <h5 class="m-0">
                                                        <a href="<?= $base ?>/infrastructure/<?= $infra['id'] ?>" class="link">
                                                            <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                                        </a>
                                                    </h5>

                                                    <div class="text-muted mb-5">
                                                        <?php if (!empty($infra['subtitle'])) { ?>
                                                            <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                                                        <?php } ?>
                                                    </div>
                                                    <p>
                                                        <?php
                                                        $descr = lang($infra['description'], $infra['description_de'] ?? null);
                                                        if (!empty($descr)) {
                                                        ?>
                                                            <?= get_preview($descr, 300) ?>
                                                            <?php if (strlen($descr) > 300) { ?>
                                                                <a href="<?= $base ?>/infrastructure/<?= $infra['id'] ?>" class="link">
                                                                    <?= lang('Read more', 'Weiterlesen') ?>
                                                                </a>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </p>
                                                    <div>
                                                        <?= fromToYear($infra['start_date'], $infra['end_date'] ?? null, true) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                <?php } ?>
            </section>
        </div>

    </div>
</div>