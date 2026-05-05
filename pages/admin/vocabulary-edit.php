<?php

/**
 * Admin page for managing project vocabularies
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<style>
    table.simple tr td {
        background-color: white;
    }
</style>


<div class="container w-800 mw-full">

    <span class="badge primary">
        <?= $vocab['category'] ?>
    </span>

    <code class="code float-right">
        <?= $vocab['id'] ?>
    </code>
    <h1>
        <i class="ph ph-book-bookmark text-primary"></i>
        <?= lang($vocab['name'], $vocab['name_de'] ?? null) ?>
    </h1>

    <p>
        <i class="ph ph-warning text-signal"></i>
        <?= lang('Please be careful when editing vocabularies. As deleting values can have unintended consequences, it is only possible to inactivate them. The ID of a value that have been saved to the database cannot be changed.', 'Bitte sei bei der Bearbeitung von Vokabularen vorsichtig. Da das Löschen von Werten ungewollte Folgen haben kann, ist es nur möglich, sie zu inaktivieren. Die ID eines Wertes, der in der Datenbank gespeichert wurde, kann nicht geändert werden.') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/admin/vocabularies/<?= $vocab['id'] ?>" method="POST" id="vocabulary-<?= $vocab['id'] ?>">

        <p class="text-secondary">
            <?= lang($vocab['description'], $vocab['description_de'] ?? null) ?>
        </p>
        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>
                        <?= lang('ID') ?>
                    </th>
                    <th>
                        <?= lang('Value', "Wert") ?> (EN)
                    </th>
                    <th>
                        <?= lang('Value', "Wert") ?> (DE)
                    </th>
                    <th>
                        <?= lang('Inactive', 'Inaktiv') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ($vocab['values'] as $i => $v) {
                    $inactive = ($v['inactive'] ?? false) ? 'checked' : '';
                ?>
                    <tr>
                        <td class="w-50">
                            <i class="ph ph-dots-six-vertical text-muted handle"></i>
                        </td>
                        <td class="w-50">
                            <input type="hidden" name="values[<?= $i ?>][id]" value="<?= $v['id'] ?>">
                            <code class="code"><?= $v['id'] ?></code>
                        </td>
                        <td>
                            <input type="text" name="values[<?= $i ?>][en]" value="<?= $v['en'] ?>" class="form-control">
                        </td>
                        <td>
                            <input type="text" name="values[<?= $i ?>][de]" value="<?= $v['de'] ?>" class="form-control">
                        </td>
                        <td>
                            <!-- checkbox to inactivate, because deleting is dangerous -->
                            <div class="custom-checkbox">
                                <input type="checkbox" name="values[<?= $i ?>][inactive]" value="1" id="inactive-<?= $vocab['id'] ?>-<?= $i ?>" <?= $inactive ?>>
                                <label for="inactive-<?= $vocab['id'] ?>-<?= $i ?>">
                                </label>
                            </div>

                        </td>
                    </tr>
                <?php } ?>
            </tbody>

            <tfoot>
                <tr>
                    <td class="w-50"></td>
                    <td colspan="4">
                        <button type="button" class="btn small" onclick="addRow(this)">
                            <i class="ph ph-plus"></i>
                            <?= lang('Add Value', 'Wert hinzufügen') ?>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>

        <button type="submit" class="btn success mt-20">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </form>
</div>

<?php include_once BASEPATH . '/header-editor.php'; ?>

<script>
    function addRow(btn) {
        let table = btn.closest('table');
        let tbody = table.querySelector('tbody');
        let tr = document.createElement('tr');

        // generate random id for the checkbox
        let random_id = Math.random().toString(36).substring(7);

        // get the index of the last row, make sure to consider meanwhile deleted rows
        let last_row = tbody.querySelector('tr:last-child');
        let i = last_row ? parseInt(last_row.querySelector('input').name.match(/\[(\d+)\]/)[1]) + 1 : 0;

        tr.innerHTML = `
        <td class="w-50">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
        </td>
        <td>
            <input type="text" name="values[${i}][id]" value="" class="form-control">
        </td>
        <td>
            <input type="text" name="values[${i}][en]" value="" class="form-control">
        </td>
        <td>
            <input type="text" name="values[${i}][de]" value="" class="form-control">
        </td>
        <td>
            <button type="button" class="btn small outline" onclick="this.closest('tr').remove()">
                <i class="ph ph-trash" title="<?= lang('Remove', 'Entfernen') ?>"></i>
            </button>
        </td>
    `;
        tbody.appendChild(tr);
    }

    $(document).ready(function() {
        $('tbody').sortable({
            handle: ".handle",
        });
    });
</script>