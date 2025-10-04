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

<h4>
    <?= lang('Entities', 'Entitäten') ?>
</h4>
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
</div>

<h4>
    <?= lang('Custom data', 'Benutzerdefinierte Daten') ?>
</h4>
<div class="link-list w-600 mw-full">
    <a href="<?= ROOTPATH ?>/admin/fields" style="--secondary-color: var(--primary-color)">
        <i class="ph ph-textbox text-secondary" aria-hidden="true"></i>
        <?= lang('Custom fields', 'Benutzerdefinierte Felder') ?>
        <br>
        <small class="text-muted"><?= lang('Create your own data fields for activities and projects', 'Erstelle deine eigenen Datenfelder für Aktivitäten und Projekte') ?></small>
    </a>
    <a href="<?= ROOTPATH ?>/admin/vocabulary" style="--secondary-color: var(--primary-color)">
        <i class="ph ph-book-bookmark text-secondary" aria-hidden="true"></i>
        <?= lang('Vocabularies', 'Vokabular') ?>
        <br>
        <small class="text-muted"><?= lang('Modify existing vocabularies for activities and projects', 'Bearbeite existierendes Vokabular für Aktivitäten und Projekte') ?></small>
    </a>
    <?php if ($Settings->featureEnabled('quality-workflow')) { ?>
        <a href="<?= ROOTPATH ?>/admin/workflows" style="--secondary-color: var(--primary-color)">
            <i class="ph ph-seal-check text-secondary" aria-hidden="true"></i>
            <?= lang('Quality workflows', 'Qualitäts-Workflows') ?>
            <br>
            <small class="text-muted"><?= lang('Manage workflows to quality-check your activities', 'Verwalte Workflows, um Aktivitäten zu prüfen') ?></small>
        </a>
    <?php } ?>
</div>

<!-- smaller section with links to helper tools -->
<h4>
    <?= lang('Helper tools', 'Hilfswerkzeuge') ?>
</h4>
<div class="link-list w-600 mw-full">
    <a href="<?= ROOTPATH ?>/admin/module-helper" style="--secondary-color: var(--muted-color)">
        <i class="ph ph-textbox text-muted" aria-hidden="true"></i>
        <?= lang('Field overview', 'Datenfelder-Übersicht') ?>
    </a>

    <a href="<?= ROOTPATH ?>/admin/templates" style="--secondary-color: var(--muted-color)">
        <i class="ph ph-text-aa text-muted" aria-hidden="true"></i>
        <?= lang('Template builder', 'Template-Baukasten') ?>
    </a>
</div>