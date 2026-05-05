<?php

/**
 * Manage infrastructures data fields
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$fields = file_get_contents(BASEPATH . '/data/infrastructure-fields.json');
$fields = json_decode($fields, true);

$custom_fields = $osiris->adminFields->find();

$data = $Settings->get('infrastructure-data');
if (!is_null($data)) {
    $data = DB::doc2Arr($data);
} else {
    $data = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data = array_column($data, 'id');
}
?>


<div class="container w-800 mw-full">
    <h2>
        <?= lang('Data fields for Infrastructures', 'Datenfelder für Infrastrukturen') ?>
    </h2>

    <p>
        <?= lang('Here you can manage the data fields for the infrastructures.', 'Hier kannst du die Datenfelder für die Infrastrukturen verwalten.') ?>
    </p>

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/infrastructures">
        <h3>
            <?= lang('Data fields', 'Datenfelder') ?>
        </h3>

        <table class="table w-auto small mb-10">
            <thead>
                <tr>
                    <th><?= lang('Active', 'Aktiv') ?></th>
                    <th><?= lang('Field name', 'Feldname') ?></th>
                </tr>
            </thead>
            <tbody id="data-fields">
                <?php foreach ($fields as $field) { ?>
                    <tr>
                        <td>
                            <!-- checkbox -->
                            <div class="custom-checkbox">
                                <input type="checkbox" name="general[infrastructure-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                <label for="field-<?= $field['id'] ?>"></label>
                            </div>
                        </td>
                        <td>
                            <b><?= lang($field['en'], $field['de'] ?? null) ?></b>
                            <?php if (!empty($field['kdsf'])) { ?>
                                <span class="badge kdsf">
                                    <?= $field['kdsf'] ?>
                                </span>
                            <?php } ?>

                            <?php if (isset($field['description'])) { ?>
                                <small class="d-block text-muted">
                                    <?= lang($field['description']['en'], $field['description']['de'] ?? null) ?>
                                </small>
                            <?php } ?>
                        </td>

                    </tr>
                <?php } ?>
                <?php if (!empty($custom_fields)) { ?>
                    <tr>
                        <td colspan="2">
                            <h5>
                                <?= lang('Custom fields', 'Benutzerdefinierte Felder') ?>
                            </h5>
                        </td>
                    </tr>
                    <?php foreach ($custom_fields as $field) { ?>
                        <tr>
                            <td>
                                <!-- checkbox -->
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="general[infrastructure-data][]" id="field-<?= $field['id'] ?>" value="<?= $field['id'] ?>" <?= in_array($field['id'], $data) ? 'checked' : '' ?>>
                                    <label for="field-<?= $field['id'] ?>"></label>
                                </div>
                            </td>
                            <td>
                                <b><?= e($field['name']) ?></b>
                            </td>
                        </tr>
                    <?php } ?>

                <?php } ?>
            </tbody>
        </table>

        <p class="text-muted">
            <?= lang('To add more fields to the <b>annual statistics</b>, you can update', 'Um weitere Felder zu der <b>Jahresstatistik</b> hinzuzufügen, kannst du') ?>
            <a href="<?= ROOTPATH ?>/admin/vocabulary#vocabulary-infrastructure-stats"><?= lang('the vocabulary for infrastructure statistics', 'das Vokabular für Infrastrukturstatistiken bearbeiten') ?></a>
            <?= lang('and add the fields you want to use there.', 'und dort die Felder hinzufügen, die du verwenden möchtest.') ?>
        </p>


        <button class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>

    </form>
</div>

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
            <div class="custom-checkbox">
                <input type="checkbox" name="values[${i}][inactive]" value="1" id="inactive-${random_id}">
                <label for="inactive-${random_id}">
                </label>
            </div>
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
