<?php

/**
 * Page to see and edit custom fields
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/fields
 *
 * @package     OSIRIS
 * @since       1.3.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$fields = $osiris->adminFields->find()->toArray();
?>
<?php include_once BASEPATH . '/header-editor.php'; ?>
<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-textbox"></i>
        <?= lang('Custom fields', 'Benutzerdefinierte Felder') ?>
    </h1>

    <div class="btn-toolbar">
        <a class="btn" href="<?= ROOTPATH ?>/admin/fields/new">
            <i class="ph ph-plus-circle"></i>
            <?= lang('Add field', 'Feld hinzufügen') ?>
        </a>
    </div>

    <table class="table" id="field-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Format</th>
                <th>Name</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fields as $field) { ?>
                <tr>
                    <td>
                        <code class="code"><?= $field['id'] ?></code>
                    </td>
                    <td>
                        <?= $field['format'] ?>
                    </td>
                    <td>
                        <?= lang($field['name'], $field['name_de']) ?>
                    </td>
                    <td>
                        <a href="<?= ROOTPATH ?>/admin/fields/<?= $field['id'] ?>" class="">
                            <i class="ph ph-pencil" aria-label="<?= lang('Edit', 'Bearbeiten') ?>"></i>   
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- <script src="<?= ROOTPATH ?>/js/datatables/dataTables.scroller.js"></script> -->
<!-- <script src="<?= ROOTPATH ?>/js/datatables/scroller.dataTables.js"></script> -->

<script>
    $(document).ready(function() {
        // Initialize sortable for the table
        $('#field-table').DataTable({
            "order": [
                [0, "asc"]
            ],
            //         scrollCollapse: true,
            // scroller: true,
            // scrollY: 500,
            // "columnDefs": [{
            //     "targets": [0, 1],
            //     "orderable": false
            // }],
            "language": {
                "emptyTable": "<?= lang('No custom fields defined yet.', 'Es wurden noch keine benutzerdefinierten Felder definiert.') ?>"
            }
        });
    });
</script>