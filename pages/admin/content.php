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

<div class="link-list w-600 mw-full">
    <a href="<?= ROOTPATH ?>/admin/categories" class="">
        <i class="ph ph-bookmarks text-secondary" aria-hidden="true"></i>
        <?= lang('Activities', 'Aktivitäten') ?>
        <br>
        <small class="text-muted"><?= lang('Manage activity types and categories', 'Verwalte Aktivitätstypen und Kategorien') ?></small>
    </a>
    <?php if ($Settings->featureEnabled('projects')) { ?>
        <a href="<?= ROOTPATH ?>/admin/projects" class="">
            <i class="ph ph-tree-structure text-secondary" aria-hidden="true"></i>
            <?= lang('Projects', 'Projekte') ?>
            <br>
            <small class="text-muted"><?= lang('Manage projects and proposals', 'Verwalte Projekte und Anträge') ?></small>
        </a>
    <?php } ?>
    <a href="<?= ROOTPATH ?>/admin/persons" class="">
        <i class="ph ph-user text-secondary" aria-hidden="true"></i>
        <?= lang('People', 'Personen') ?>
        <br>
        <small class="text-muted"><?= lang('Manage data of people and login', 'Verwalte Personendaten und Login-Informationen') ?></small>
    </a>
    <?php if ($Settings->featureEnabled('infrastructures')) { ?>
        <a href="<?= ROOTPATH ?>/admin/infrastructures" class="">
            <i class="ph ph-cube-transparent text-secondary" aria-hidden="true"></i>
            <?= lang('Infrastructures', 'Infrastrukturen') ?>
            <br>
            <small class="text-muted"><?= lang('Manage data of infrastructures', 'Verwalte Daten von Infrastrukturen') ?></small>
        </a>
    <?php } ?>
    <hr>
    <a href="<?= ROOTPATH ?>/admin/fields" class="">
        <i class="ph ph-textbox text-secondary" aria-hidden="true"></i>
        <?= lang('Custom fields', 'Benutzerdefinierte Felder') ?>
        <br>
        <small class="text-muted"><?= lang('Create your own data fields for activities and projects', 'Erstelle deine eigenen Datenfelder für Aktivitäten und Projekte') ?></small>
    </a>
    <a href="<?= ROOTPATH ?>/admin/vocabulary" class="">
        <i class="ph ph-book-bookmark text-secondary" aria-hidden="true"></i>
        <?= lang('Vocabularies', 'Vokabular') ?>
        <br>
        <small class="text-muted"><?= lang('Modify existing vocabularies for activities and projects', 'Bearbeite existierendes Vokabular für Aktivitäten und Projekte') ?></small>
    </a>
</div>