<?php

/**
 * Overview on workflows
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.2
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
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
        <?= lang('Add workflow', 'Workflow hinzufügen') ?>
    </a>
</div>

<table class="table" id="workflow-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th><?=lang('Steps', 'Schritte')?></th>
            <th># <?=lang('Activities', 'Aktivitäten')?></th>
            <th><?=lang('Action', 'Aktion')?></th>
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
        <?= lang('Modify applied workflows', 'Angewendete Workflows bearbeiten') ?>
    </h5>

    <p>
        <?= lang('If you change a workflow, this will not affect activities that have already been submitted or approved. You can remove or reset all workflows for these activities if needed.', 'Wenn du einen Workflow änderst, wirkt sich dies nicht auf Aktivitäten aus, die bereits eingereicht oder genehmigt wurden. Du kannst bei Bedarf alle Workflows für diese Aktivitäten entfernen oder zurücksetzen.') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/workflows/reset-action" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to apply this action to all selected activities? This action cannot be undone.', 'Bist du sicher, dass du diese Aktion auf alle ausgewählten Aktivitäten anwenden möchtest? Diese Aktion kann nicht rückgängig gemacht werden.') ?>');">
        <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
        <div class="form-group floating-form">
            <select name="action" class="form-control" required>
                <option value="remove"><?= lang('Remove all workflows', 'Alle Workflows entfernen') ?></option>
                <option value="reset"><?= lang('Reset all workflows to the first step', 'Alle Workflows auf den ersten Schritt zurücksetzen') ?></option>
            </select>
            <label><?= lang('Action', 'Aktion') ?></label>
        </div>
        <div class="form-group floating-form">
            <select name="activity" id="activity-type" class="form-control" required>
                <option value="all"><?= lang('All activities', 'Alle Aktivitäten') ?></option>
                <?php
                $activity_types = $osiris->adminCategories->find(['workflow' => ['$exists' => true]]);
                foreach ($activity_types as $atype) {
                ?>
                    <option value="<?= $atype['id'] ?>"><?= lang($atype['name'], $atype['name_de']?? null) ?></option>
                <?php } ?>
            </select>
            <label><?= lang('Activity category', 'Aktivitäts-Kategorie') ?></label>
        </div>
        <button class="btn warning" type="submit">
            <i class="ph ph-arrows-counter-clockwise"></i>
            <?= lang('Execute action', 'Aktion ausführen') ?>
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
                "emptyTable": "<?= lang('No workflows defined yet.', 'Es wurden noch keine Workflows definiert.') ?>"
            }
        });
    });
</script>