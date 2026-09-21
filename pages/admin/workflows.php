<?php

/**
 * Overview on workflows
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

$workflows = $osiris->adminWorkflows->find()->toArray();
?>
<?php include_once BASEPATH . '/header-editor.php'; ?>

<h1>
    <i class="ph ph-seal-check"></i>
    Quality Workflows
</h1>

<div class="btn-toolbar">
    <a class="" href="<?= ROOTPATH ?>/admin/workflows/new">
        <i class="ph ph-plus-circle"></i>
        <?= lang('admin.add_workflow') ?>
    </a>
</div>

<table class="table" id="workflow-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th><?=lang('common.steps')?></th>
            <th># <?=lang('common.activities')?></th>
            <th><?=lang('common.action')?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($workflows as $workflow) { ?>
            <tr>
                <td>
                    <code class="code"><?= $workflow['id'] ?></code>
                </td>
                <td>
                    <?= $workflow['name'] ?>
                </td>
                <td>
                    <?= count($workflow['steps'] ?? []) ?>
                </td>
                <td>
                    <?php
                    $count = $osiris->activities->count(['workflow.workflow_id' => $workflow['id']]);
                    echo $count;
                    ?>
                </td>
                <td class="unbreakable">
                    <a href="<?= ROOTPATH ?>/admin/workflows/<?= $workflow['id'] ?>">
                        <i class="ph ph-pencil"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<article class="box padded">
    <h5 class="title">
        <?= lang('admin.modify_applied_workflows') ?>
    </h5>

    <p>
        <?= lang('admin.if_you_change_a_workflow_this_will_not_affect_activities_that_have_already') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/workflows/reset-action" method="post" onsubmit="return confirm('<?= lang('admin.are_you_sure_you_want_to_apply_this_action_to_all_selected_activities_this') ?>');">
        <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
        <div class="form-group floating-form">
            <select name="action" class="form-control" required>
                <option value="remove"><?= lang('admin.remove_all_workflows') ?></option>
                <option value="reset"><?= lang('admin.reset_all_workflows_to_the_first_step') ?></option>
            </select>
            <label><?= lang('common.action') ?></label>
        </div>
        <div class="form-group floating-form">
            <select name="activity" id="activity-type" class="form-control" required>
                <option value="all"><?= lang('common.all_activities') ?></option>
                <?php
                $activity_types = $osiris->adminCategories->find(['workflow' => ['$exists' => true]]);
                foreach ($activity_types as $atype) {
                ?>
                    <option value="<?= $atype['id'] ?>"><?= lang($atype['name'], $atype['name_de']?? null) ?></option>
                <?php } ?>
            </select>
            <label><?= lang('admin.activity_category') ?></label>
        </div>
        <button class="btn warning" type="submit">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <?= lang('admin.execute_action') ?>
        </button>
    </form>
</article>

<script>
    $(document).ready(function() {
        // Initialize sortable for the table
        $('#workflow-table').DataTable({
            "order": [
                [0, "asc"]
            ],
            "language": {
                "emptyTable": "<?= lang('admin.no_workflows_defined_yet') ?>"
            }
        });
    });
</script>