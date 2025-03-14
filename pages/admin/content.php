<?php
/**
 * Overview file for managable content
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<h1>
    <?= lang('Manage content', 'Inhalte verwalten') ?>
</h1>

<div class="link-list">
    <a href="<?= ROOTPATH ?>/admin/fields" class="">
        <i class="ph ph-textbox text-secondary" aria-hidden="true"></i>
        <?= lang('Custom fields') ?>
    </a>
    <a href="<?= ROOTPATH ?>/admin/vocabulary" class="">
        <i class="ph ph-book-bookmark text-secondary" aria-hidden="true"></i>
        <?= lang('Vocabularies', 'Vokabular') ?>
    </a>
    <a href="<?= ROOTPATH ?>/admin/categories" class="">
        <i class="ph ph-bookmarks text-secondary" aria-hidden="true"></i>
        <?= lang('Activities', 'Aktivitäten') ?>
    </a>
    <a href="<?= ROOTPATH ?>/admin/projects" class="">
        <i class="ph ph-tree-structure text-secondary" aria-hidden="true"></i>
        <?= lang('Projects', 'Projekte') ?>
    </a>
    <a href="<?= ROOTPATH ?>/admin/persons" class="">
        <i class="ph ph-user text-secondary" aria-hidden="true"></i>
        <?= lang('People', 'Personen') ?>
    </a>
</div>