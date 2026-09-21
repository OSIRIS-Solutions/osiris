<?php

/**
 * Overview file for managable content
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<h1>
    <i class="ph-duotone ph-treasure-chest"></i>
    <?= lang('admin.manage_content') ?>
</h1>

<h4>
    <?= lang('admin.entities') ?>
</h4>
<div class="link-list w-600 mw-full">
    <a href="<?= ROOTPATH ?>/admin/categories" class="">
        <i class="ph-duotone ph-bookmarks text-secondary" aria-hidden="true"></i>
        <?= lang('common.activities') ?>
        <br>
        <small class="text-muted"><?= lang('admin.activities_description') ?></small>
    </a>
    <?php if ($Settings->featureEnabled('projects')) { ?>
        <a href="<?= ROOTPATH ?>/admin/projects" class="">
            <i class="ph-duotone ph-tree-structure text-secondary" aria-hidden="true"></i>
            <?= lang('common.projects') ?>
            <br>
            <small class="text-muted"><?= lang('admin.projects_description') ?></small>
        </a>
    <?php } ?>
    <a href="<?= ROOTPATH ?>/admin/persons" class="">
        <i class="ph-duotone ph-user text-secondary" aria-hidden="true"></i>
        <?= lang('common.people') ?>
        <br>
        <small class="text-muted"><?= lang('admin.manage_data_of_people_and_login') ?></small>
    </a>
    <?php if ($Settings->featureEnabled('infrastructures')) { ?>
        <a href="<?= ROOTPATH ?>/admin/infrastructures" class="">
            <i class="ph-duotone ph-cube-transparent text-secondary" aria-hidden="true"></i>
            <?= lang('common.infrastructures') ?>
            <br>
            <small class="text-muted"><?= lang('admin.infrastructures_description') ?></small>
        </a>
    <?php } ?>
</div>

<h4>
    <?= lang('admin.custom_data') ?>
</h4>
<div class="link-list w-600 mw-full">
    <a href="<?= ROOTPATH ?>/admin/fields" style="--secondary-color: var(--primary-color)">
        <i class="ph-duotone ph-textbox text-secondary" aria-hidden="true"></i>
        <?= lang('common.custom_fields') ?>
        <br>
        <small class="text-muted"><?= lang('admin.custom_fields_description') ?></small>
    </a>
    <a href="<?= ROOTPATH ?>/admin/vocabulary" style="--secondary-color: var(--primary-color)">
        <i class="ph-duotone ph-book-bookmark text-secondary" aria-hidden="true"></i>
        <?= lang('admin.vocabularies') ?>
        <br>
        <small class="text-muted"><?= lang('admin.vocabularies_description') ?></small>
    </a>
    <?php if ($Settings->featureEnabled('quality-workflow')) { ?>
        <a href="<?= ROOTPATH ?>/admin/workflows" style="--secondary-color: var(--primary-color)">
            <i class="ph-duotone ph-seal-check text-secondary" aria-hidden="true"></i>
            <?= lang('admin.quality_workflows') ?>
            <br>
            <small class="text-muted"><?= lang('admin.quality_workflows_description') ?></small>
        </a>
    <?php } ?>
    <?php if ($Settings->featureEnabled('tags')) { ?>
        <a href="<?= ROOTPATH ?>/admin/tags" style="--secondary-color: var(--primary-color)">
            <i class="ph-duotone ph-tag text-secondary" aria-hidden="true"></i>
            <?= lang('common.tags') ?>
            <br>
            <small class="text-muted"><?= lang('admin.tags_description') ?></small>
        </a>
    <?php } ?>

</div>

<!-- smaller section with links to helper tools -->
<h4>
    <?= lang('admin.helper_tools') ?>
</h4>
<div class="link-list w-600 mw-full">
    <a href="<?= ROOTPATH ?>/admin/module-helper" style="--secondary-color: var(--muted-color)">
        <i class="ph-duotone ph-textbox text-muted" aria-hidden="true"></i>
        <?= lang('admin.field_overview') ?>
    </a>

    <a href="<?= ROOTPATH ?>/admin/templates" style="--secondary-color: var(--muted-color)">
        <i class="ph-duotone ph-text-aa text-muted" aria-hidden="true"></i>
        <?= lang('admin.template_builder') ?>
    </a>
</div>