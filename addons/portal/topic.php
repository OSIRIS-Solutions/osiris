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
 * @since       1.7.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$numbers = $data['numbers'] ?? [
    'persons' => 0,
    'publications' => 0,
    'activities' => 0,
    'projects' => 0,
];
$baseUnit = false;
$preselect = $open ?? $_GET['open'] ?? null;

?>
<div class="container">

    <?php if ($Portfolio->isPreview()) { ?>
        <link rel="stylesheet" href="<?= ROOTPATH ?>/css/portal.css?v=<?= OSIRIS_BUILD ?>">
        <!-- all necessary javascript -->
        <script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
        <script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
        <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
        <script src="<?= ROOTPATH ?>/js/popover.js"></script>

        <script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js" charset="utf-8"></script>

        <!-- all variables for this page -->
        <script>
            const BASE = '<?= $base ?>';
            const DEPT = '<?= $id ?>';
        </script>
        <script src="<?= ROOTPATH ?>/js/topics.portfolio.js?v=<?= OSIRIS_BUILD ?>"></script>

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

    <div class="row row-eq-spacing">
        <?php
        $topics = $Portfolio->getTopics();
        $hierarchy = $Portfolio->build_unit_hierarchy(0);
        $topics_and_groups = !empty($topics) && !empty($hierarchy);
        ?>

        <div class="col-sm flex-grow-0 flex-reset <?= $baseUnit ? 'hidden-state' : '' ?>" id="filter-column">
            <div id="filter-toggle" onclick="toggleUnitFilter();">
                <i class="ph ph-caret-left" aria-hidden="true"></i>
                <span>
                    <?php if ($topics_and_groups) { ?>
                        <?= lang('Explore by topic & unit', 'Erkunden nach Schwerpunkt & Einheit') ?>
                    <?php } else if (!empty($topics)) { ?>
                        <?= lang('Explore by topic', 'Erkunden nach Schwerpunkt') ?>
                    <?php } else if (!empty($hierarchy)) { ?>
                        <?= lang('Explore by unit', 'Erkunden nach Einheit') ?>
                    <?php } ?>
                </span>
            </div>
            <div class="filter">
                <table id="filter-unit" class="table small simple">
                    <?php

                    if (!empty($topics) && is_array($topics)): ?>
                        <?php foreach ($topics as $el): ?>
                            <?php
                            $classes = [];
                            if ($el['id'] == $id) {
                                $classes[] = 'active';
                            }
                            ?>
                            <tr style="--primary-color: <?= $el['color'] ?? 'var(--primary-color)'; ?>;  --primary-color-20: <?= $el['color'] ? $el['color'] . '33' : 'var(--primary-color-20)'; ?>;">
                                <td class="<?= implode(' ', $classes); ?> topic">
                                    <a
                                        href="<?= $base . '/topic/' . urlencode((string)($el['id'] ?? '')); ?>">
                                        <span><?= lang($el['name'] ?? '', $el['name_de'] ?? null); ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif;

                    if (!empty($hierarchy) && !empty($topics)): ?>
                        <tr>
                            <td class="disabled">
                                <hr>
                            </td>
                        </tr>
                    <?php
                    endif;

                    if (!empty($hierarchy) && is_array($hierarchy)): ?>
                        <?php foreach ($hierarchy as $el): ?>
                            <?php
                            $hide = (bool)($el['hide'] ?? false);
                            $active = false;
                            $open = (bool)($el['open'] ?? false);
                            $openable = (bool)($el['openable'] ?? false);
                            // Vue condition: !el.hide || el.active || el.open
                            if ($hide && !$active && !$open) {
                                continue;
                            }

                            $level = (int)($el['level'] ?? 0);

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
                                <td class="<?= e(implode(' ', $classes)); ?>">
                                    <a
                                        class="item d-block colorless"
                                        href="<?= $href ?>">
                                        <span><?= e(lang($nameEn, $nameDe)); ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="col-sm">

            <h2 class="topic-name" style="color: <?= $data['color'] ?? 'inherit' ?>;">
                <i class="ph ph-arrow-circle-right" style="width:4rem"></i>
                <?= lang($data['name'], $data['name_de'] ?? null) ?>
            </h2>
            <h4 class="topic-type"><?= $Settings->topicLabel() ?></h4>

            <nav id="group-pills">
                <a onclick="navigate('general')" id="btn-general" class="<?= empty($preselect) || $preselect === 'info' ? 'active' : '' ?>">
                    <i class="ph ph-info" aria-hidden="true"></i>
                    <?= lang('Info', 'Info') ?>
                </a>

                <a onclick="navigate('persons')" id="btn-persons" class="<?= $preselect === 'persons' ? 'active' : '' ?>">
                    <i class="ph ph-users" aria-hidden="true"></i>
                    <?= lang('Team', 'Team') ?>
                    <span class="index"><?= $numbers['persons'] ?></span>
                </a>

                <?php
                if ($numbers['units'] > 0) { ?>
                    <a onclick="navigate('units')" id="btn-units" class="<?= $preselect === 'units' ? 'active' : '' ?>">
                        <i class="ph ph-users-three" aria-hidden="true"></i>
                        <?= lang('Units', 'Einheiten')  ?>
                        <span class="index"><?= $numbers['units'] ?></span>
                    </a>
                <?php } ?>

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
                                <a href="<?= $base ?>/person/<?= $h['id'] ?>" class="person-card">
                                    <?= $Portfolio->printProfilePicture($h['id'], null, 'profile-img') ?>
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

                    <div class="description">
                        <?= lang($data['description'] ?? '-', $data['description_de'] ?? null) ?>
                    </div>
                <?php } ?>

            </section>


            <section id="persons" <?= $preselect === 'persons' ? '' : 'style="display:none"' ?> data-title="<?= lang('Employees', 'Mitarbeitende Personen') ?>">

                <table class="table cards w-full datatable" id="users-table" data-page-length="18">
                    <thead>
                        <th></th>
                        <th></th>
                    </thead>
                    <tbody>
                        <?php
                        $staff = $Portfolio->fetch_entity('topic', $id, 'staff');
                        foreach ($staff as $s) {
                        ?>
                            <tr>
                                <td><?= $Portfolio->printProfilePicture($s['id'], null, 'profile-img') ?></td>
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



            <section id="units" <?= $preselect === 'units' ? '' : 'style="display:none"' ?> data-title="<?= lang('Units', 'Einheiten') ?>">

                <table class="table cards w-full datatable" id="units-table" data-page-length="18">
                    <thead>
                        <th></th>
                    </thead>
                    <tbody>
                        <?php
                        $staff = $Portfolio->fetch_entity('topic', $id, 'units');
                        foreach ($staff as $s) {
                        ?>
                            <tr>
                                <td>
                                    <div class="w-full">
                                        <h5 class="my-0">
                                            <a href="<?= $base ?>/group/<?= $s['id'] ?>">
                                                <?= lang($s['name'] ?? '', $s['name_de'] ?? null) ?>
                                            </a>
                                        </h5>
                                        <small>
                                            <?= lang($s['unit']['name'] ?? '', $s['unit']['name_de'] ?? null) ?>
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <section id="publications" <?= $preselect === 'publications' ? '' : 'style="display:none"' ?> data-title="<?= lang('Publications', 'Publikationen') ?>">

                <table class="table datatable" id="publication-table"
                    data-table="publications"
                    data-tab="publications"
                    data-source="./publications.json"
                    data-page-length="20"
                    data-lang="<?= lang('en', 'de') ?>">
                    <thead>
                        <tr>
                            <th data-col="icon" data-orderable="false" data-searchable="false"><?=lang('Type', 'Art')?></th>
                            <th data-col="html" data-search-col="search"><?= lang('Publication', 'Publikation') ?></th>
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
                                <th data-col="icon" data-orderable="false" data-searchable="false"><?=lang('Type', 'Art')?></th>
                                <th data-col="html" data-search-col="search"><?=lang('Activity', 'Aktivität')?></th>
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


                <?php } ?>


            </section>


        </div>

    </div>
</div>