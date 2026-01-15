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
$numbers = $data['numbers'] ?? [
    'persons' => 0,
    'publications' => 0,
    'activities' => 0,
    'projects' => 0,
    'collaborators' => 0,
    'infrastructures' => 0,
];
?>
<div class="container">

    <?php if ($Portfolio->isPreview()) { ?>
        <link rel="stylesheet" href="<?= ROOTPATH ?>/css/portal.css?v=<?= CSS_JS_VERSION ?>">

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

    <div class="row row-eq-spacing">
        <?php
        $topics = $Portfolio->getTopics();
        $hierarchy = $Portfolio->build_unit_hierarchy($id);
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
                    <?php if (!empty($topics) && is_array($topics)): ?>
                        <?php foreach ($topics as $el): ?>
                            <?php
                            $classes = [];
                            if (isset($el['active']) && $el['active']) $classes[] = 'active';
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
                            $active = (bool)($el['active'] ?? false);
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
                                <td class="<?= htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8'); ?>">
                                    <a
                                        class="item d-block colorless"
                                        href="<?= $href ?>">
                                        <span><?= htmlspecialchars(lang($nameEn, $nameDe), ENT_QUOTES, 'UTF-8'); ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <div class="col-sm">

            <h2 class="unit-name"><?= lang($data['name'], $data['name_de'] ?? null) ?></h2>
            <h4 class="unit-type"><?= lang($data['unit']['name'] ?? '', $data['unit']['name_de'] ?? null) ?></h4>

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
                if ($numbers['collaborators'] > 0) { ?>
                    <a onclick="navigate('collaborators')" id="btn-collaborators" class="<?= $preselect === 'collaborators' ? 'active' : '' ?>">
                        <i class="ph ph-handshake" aria-hidden="true"></i>
                        <?= lang('Collaborators', 'Kooperationspartner')  ?>
                        <span class="index"><?= $numbers['collaborators'] ?></span>
                    </a>
                <?php } ?>

                <?php
                if (($numbers['infrastructures'] ?? 0) > 0) { ?>
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


                <!-- topics -->
                <?php
                $unit_topics = null;
                if ($baseUnit) {
                    $unit_topics = $topics ?? null;
                } else {
                    $unit_topics = $data['topics'] ?? null;
                }
                if (!empty($unit_topics)) { ?>
                    <h6 class="m-0"><?= $Settings->topicLabel() ?>:</h6>
                    <div class="topics">
                        <?php foreach ($unit_topics as $t) { ?>
                            <a href="<?= $base ?>/topic/<?= $t['id'] ?>" class="topic-badge" style="--primary-color: <?= $t['color'] ?? 'var(--primary-color)' ?>; --primary-color-20: <?= isset($t['color']) ? $t['color'] . '33' : 'var(--primary-color-20)' ?>">
                                <i class="ph ph-arrow-circle-right"></i>
                                <?= lang($t['name'], $t['name_de'] ?? null) ?>
                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (isset($data['description']) || isset($data['description_de'])) { ?>
                    <div class="description">
                        <?= lang($data['description'] ?? '-', $data['description_de'] ?? null) ?>
                    </div>
                <?php } ?>

            </section>

            <section id="research" style="display:none;" data-title="<?= lang('Research topics', 'Forschungsschwerpunkte') ?>">

                <!-- <h3><?= lang('Research topics', 'Forschungsschwerpunkte') ?></h3> -->

                <?php if (isset($data['research']) && !empty($data['research'])) {
                ?>
                    <?php foreach ($data['research'] as $r) { ?>
                        <div class="box padded">
                            <h5 class="title">
                                <?= lang($r['title'], $r['title_de'] ?? null) ?>
                            </h5>
                            <div class="subtitle font-size-14 text-secondary">
                                <?= lang($r['subtitle'] ?? '', $r['subtitle_de'] ?? null) ?>
                            </div>

                            <div class="description">
                                <?= (lang($r['info'] ?? '', $r['info_de'] ?? null)) ?>
                            </div>
                            <?php if (!empty($r['activities'] ?? null)) { ?>
                                <hr>
                                <h6 class="m-0"><?= lang('Related activities', 'Zugehörige Aktivitäten') ?>:</h6>
                                <table class="table simple">
                                    <tbody>
                                        <?php foreach ($r['activities'] as $a) { ?>
                                            <tr>
                                                <td class="w-50">
                                                    <?= $a['icon'] ?>
                                                </td>
                                                <td>
                                                    <a href="<?= $base ?>/activity/<?= $a['id'] ?>" class="colorless">
                                                        <?= $a['html'] ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
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
                            <th data-col="icon" data-orderable="false" data-searchable="false"><?= lang('Type', 'Art') ?></th>
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

                <?php } ?>


            </section>


            <section id="collaborators" <?= $preselect === 'collaborators' ? '' : 'style="display:none"' ?> data-title="<?= lang('Collaborators', 'Kooperationspartner') ?>">

                <?php if ($numbers['collaborators'] > 0) {
                ?>
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



            <section id="infrastructures" <?= $preselect === 'infrastructures' ? '' : 'style="display:none"' ?> data-title="<?= lang('Infrastructures', 'Infrastrukturen') ?>">

                <?php if ($numbers['infrastructures'] > 0) {
                    $infrastructures = $Portfolio->fetch_entity('infrastructures');
                ?>
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