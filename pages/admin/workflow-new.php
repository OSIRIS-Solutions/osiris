<?php 
/**
 * Admin Workflow Page for creating a new workflow
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('common.id_must_be_unique') ?></h5>

            <p>
                <?= lang('admin.the_id_is_used_internally_to_save_this_workflow_and_associate_activities_to') ?>
            </p>
            <p>
                <?= lang('common.as_the_id_must_be_unique_the_following_previously_used_ids_and_keywords_new') ?>
            </p>
            <ul class="list" id="used-ids">
                <?php foreach ($osiris->adminWorkflows->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('common.understand') ?></a>
            </div>
        </div>
    </div>
</div>


<form action="<?= ROOTPATH ?>/crud/workflows/create" method="post" id="group-form">

    <div class="box padded">
        <h4 class="title">
            <?= lang('admin.new_workflow') ?>
        </h4>

        <div class="form-group">
            <label for="id" class="required">ID</label>
            <input type="text" class="form-control" name="values[id]" id="id" value="<?= $form['id'] ?? '' ?>" <?= !empty($form) ? 'disabled' : '' ?> oninput="sanitizeID(this, '#used-ids li')" required>

            <small>
                <a href="#unique"><i class="ph ph-info"></i>
                    <?= lang('common.important_must_be_unique') ?>
                </a>
            </small>
        </div>

        <div class="form-group">
            <label for="name" class="required "><?= lang('common.name_of_the_workflow') ?></label>
            <input type="text" class="form-control" name="values[name]" required value="<?= $form['name'] ?? '' ?>" maxlength="30">
            <small class="form-text text-muted"><?= lang('common.max_30_characters') ?></small>
        </div>

        <h5><?= lang('common.steps') ?></h5>

        <p class="text-danger">
            <?= lang('admin.steps_can_be_defined_after_you_have_saved_the_workflow_once') ?>
        </p>

        <button type="submit" class="btn success" id="submitBtn">
            <i class="ph ph-check"></i> <?= lang('action.save') ?>
        </button>

    </div>
</form>


<?php include_once BASEPATH . '/header-editor.php'; ?>
<script>

</script>