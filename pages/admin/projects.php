<?php

/**
 * Overview file for project settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <?= lang('Project Settings', 'Projekt-Einstellungen') ?>
</h1>


<div class="btn-toolbar">
    <a class="btn" href="<?= ROOTPATH ?>/admin/projects/new">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add category', 'Kategorie hinzufügen') ?>
    </a>

    <a class="btn" href="<?= ROOTPATH ?>/admin/vocabulary">
        <i class="ph ph-list"></i>
        <?= lang('Vocabulary', 'Vokabular') ?>
    </a>
</div>


<div class="link-list">
    <?php
    $types = $osiris->adminProjects->find();
    foreach ($types as $type) { ?>
        <a class="" href="<?= ROOTPATH ?>/admin/projects/1/<?= $type['id'] ?>" style="--secondary-color: <?= $type['color'] ?? '#000' ?>">
            <b style="color: <?= $type['color'] ?? 'inherit' ?>">
                <i class="ph ph-<?= $type['icon'] ?? 'placeholder' ?> mr-10"></i>
                <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
            </b>

            <?php if (isset($type['disabled']) && $type['disabled']) { ?>
                <small class="badge danger ml-20">
                    <i class="ph ph-x-circle" aria-hidden="true"></i>
                    <?= lang('Deactivated', 'Deaktiviert') ?>
                </small>
            <?php } ?>
        </a>
    <?php } ?>
</div>