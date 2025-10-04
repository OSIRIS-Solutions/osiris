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
            <th>Aktion</th>
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
                <td class="unbreakable">
                    <a href="<?= ROOTPATH ?>/admin/workflows/<?= $workflow['id'] ?>">
                        <i class="ph ph-pencil"></i>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

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