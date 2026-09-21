<?php

/**
 * Admin page for managing project vocabularies
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

$categories = [
    'Project' => [
        'label' => lang('common.projects'),
        'icon' => 'ph ph-tree-structure'
    ],
    'Infrastructure' => [
        'label' => lang('common.infrastructures'),
        'icon' => 'ph ph-cube-transparent'
    ],
    'Event' => [
        'label' => lang('common.events'),
        'icon' => 'ph ph-calendar'
    ],
    'Activity' => [
        'label' => lang('common.activities'),
        'icon' => 'ph ph-folder'
    ],
];
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph ph-book-bookmark text-primary"></i>
        <?= lang('common.vocabulary') ?>
    </h1>

    <p>
        <?= lang('admin.vocabularies_are_used_to_manage_lists_of_values_for_dropdowns_and_other_sel') ?>
    </p>

    <input type="text" id="vocabulary-search" class="form-control" placeholder="<?= lang('admin.search_vocabularies') ?>">

    <div class="row row-eq-spacing mt-0">

        <div class="col-lg-9" id="vocabularies">
            <?php
            $cat = '';
            $icon = '';
            foreach ($vocabularies as $vocab) {
                if ($cat != $vocab['category']) {
                    $cat = $vocab['category'];
                    $category = $categories[$cat] ?? $cat;
                    $icon = $category['icon'] ?? 'ph ph-tag';
                    echo '<h2 class="title" id="vocabulary-' . $cat . '"><i class="' . $icon . ' text-primary"></i> ' . ($category['label'] ?? $category) . '</h2>';
                }
            ?>
                <a class="card mb-20" href="<?= ROOTPATH ?>/admin/vocabulary/<?= $vocab['id'] ?>">
                    <i class="ph-duotone ph-<?= $icon ?>" aria-hidden="true"></i>
                    <b><?= lang($vocab['name'], $vocab['name_de'] ?? null) ?></b>
                    <p>
                        <?= lang($vocab['description'], $vocab['description_de'] ?? null) ?>
                    </p>

                </a>
            <?php } ?>

            <?php include_once BASEPATH . '/header-editor.php'; ?>
        </div>

        <div class="col-lg-3 d-none d-lg-block">
            <nav class="on-this-page-nav">
                <div class="content">
                    <h4 class=""><?= lang('common.content') ?></h4>
                    <div class="list">
                        <?php
                        $cat = '';
                        foreach ($vocabularies as $vocab) {
                            if ($cat != $vocab['category']) {
                                $cat = $vocab['category'];
                                $category = $categories[$cat] ?? $cat;
                        ?>
                                <a href="#vocabulary-<?= $vocab['category'] ?>">
                                    <?= $category['label'] ?? $category ?>
                                </a>
                            <?php } ?>
                        <?php } ?>
                    </div>
            </nav>
        </div>
    </div>
</div>

<script>
    // filter vocabularies
    $(document).ready(function() {
        $('#vocabulary-search').on('input', function() {
            const query = $(this).val().toLowerCase();
            $('#vocabularies .card').each(function() {
                const name = $(this).find('b').text().toLowerCase();
                const description = $(this).find('p').text().toLowerCase();
                if (name.includes(query) || description.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
</script>