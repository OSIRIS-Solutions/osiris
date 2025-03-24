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
    <a class="btn" href="<?= ROOTPATH ?>/admin/project/new">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add category', 'Kategorie hinzufügen') ?>
    </a>

    <a class="btn" href="<?= ROOTPATH ?>/admin/vocabulary">
        <i class="ph ph-list"></i>
        <?= lang('Vocabulary', 'Vokabular') ?>
    </a>
</div>


<?php
$types = $osiris->adminProjects->find();
foreach ($types as $type) { ?>
    <div class="box px-20 py-10 mb-10">
        <h3 class="title" style="color: <?= $type['color'] ?? 'inherit' ?>">
            <i class="ph ph-<?= $type['icon'] ?? 'placeholder' ?> mr-10"></i>
            <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
        </h3>

        <p>
            <b>
                <?= lang('Phases', 'Phasen') ?>:
            </b>

            <?php foreach ($type['phases'] ?? [] as $phase) { ?>
                <span class="badge <?= $phase['color'] ?>">
                    <?= lang($phase['name'], $phase['name_de'] ?? $phase['name']) ?>
                </span>
            <?php } ?>
        </p>

        <a href="<?= ROOTPATH ?>/admin/projects/1/<?= $type['id'] ?>" class="link">
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>
    </div>
<?php } ?>