<?php

/**
 * Page for admins to define keywords for activities and projects
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.6.0
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


?>

<form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
    <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/tags">
    <div class="box primary padded">
        <h2 class="title">
            <i class="ph ph-tag" aria-hidden="true"></i>
            <?= lang('Tags', 'Schlagwörter') ?>
        </h2>

        <p>
            <?= lang('Define tags that can be attached to activities and projects.', 'Definiere Schlagworte, die für Aktivitäten und Projekte verwendet werden können.') ?>
        </p>

        <!-- input for name of this keyword -->
        <div class="form-group">
            <label for="position">
                <h5><?= lang('Label', 'Bezeichnung') ?></h5>
            </label>

            <?php
            $label = $Settings->get('tags_label');
            ?>

            <div class="row row-eq-spacing my-0">
                <div class="col-md-6">
                    <label for="tags_label" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[tags_label][en]" id="tags_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Tags') ?>">
                </div>
                <div class="col-md-6">
                    <label for="tags_label_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[tags_label][de]" id="tags_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Schlagwörter') ?>">
                </div>
            </div>
        </div>

        <?php
        $keywords = DB::doc2Arr($Settings->get('tags', []));
        ?>
        <div class="form-group">
            <label for="tags" class="font-weight-bold">
                <?= lang('Defined list of tags', 'Definierte Liste von Schlagworten') ?>:
            </label>
            <small class="d-block text-muted">
                <?= lang('Define a list of tags, each tag should be seperated by a new line.', 'Definiere eine Liste von Schlagworten. Jedes Schlagwort sollte in einer neuen Zeile stehen.') ?>
            </small>
            <textarea name="general[tags]" id="tags" class="form-control" rows="10"><?= implode(PHP_EOL, $keywords) ?></textarea>
        </div>

        <button class="btn signal">
            <i class="ph ph-floppy-disk"></i>
            Save
        </button>
    </div>
</form>