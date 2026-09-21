<?php

/**
 * Page for admins to define keywords for activities and projects
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.6.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>
<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-tag" aria-hidden="true"></i>
        <?= lang('common.tags') ?>
    </h1>

    <p>
        <?= lang('admin.define_tags_that_can_be_attached_to_activities_and_projects') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/tags">

        <!-- input for name of this keyword -->
        <div class="form-group">
            <label for="position">
                <h5><?= lang('common.label') ?></h5>
            </label>

            <?php
            $label = $Settings->get('tags_label');
            ?>

            <div class="row row-eq-spacing my-0">
                <div class="col-md-6">
                    <label for="tags_label" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[tags_label][en]" id="tags_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Tags') ?>">
                </div>
                <div class="col-md-6">
                    <label for="tags_label_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[tags_label][de]" id="tags_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Schlagwörter') ?>">
                </div>
            </div>
        </div>

        <?php
        $keywords = DB::doc2Arr($Settings->get('tags', []));
        ?>
        <div class="form-group">
            <label for="tags" class="font-weight-bold">
                <?= lang('admin.defined_list_of_tags') ?>:
            </label>
            <small class="d-block text-muted">
                <?= lang('admin.define_a_list_of_tags_each_tag_should_be_seperated_by_a_new_line') ?>
            </small>
            <textarea name="general[tags]" id="tags" class="form-control" rows="10"><?= implode(PHP_EOL, $keywords) ?></textarea>
        </div>

        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('common.save_changes') ?>
        </button>
    </form>
</div>