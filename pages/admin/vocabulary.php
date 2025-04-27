<?php

/**
 * Admin page for managing project vocabularies
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

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
$vocabularies = $Vocabulary->getVocabularies();
?>
<style>
    table.simple tr td {
        background-color: white;
    }
</style>


<h1>
    <i class="ph ph-book-bookmark text-primary"></i>
    <?= lang('Vocabulary', 'Vokabular') ?>
</h1>

<p>
    <?= lang('Vocabularies are used to manage lists of values for dropdowns and other selection fields.', 'Vokabulare werden verwendet, um Listen von Werten für Dropdowns und andere Auswahlmöglichkeiten zu verwalten.') ?>
</p>

<p>
    <i class="ph ph-warning text-signal"></i>
    <?= lang('Please be careful when editing vocabularies. As deleting values can have unintended consequences, it is only possible to inactivate them. The ID of a value that have been saved to the database cannot be changed.', 'Bitte sei bei der Bearbeitung von Vokabularen vorsichtig. Da das Löschen von Werten ungewollte Folgen haben kann, ist es nur möglich, sie zu inaktivieren. Die ID eines Wertes, der in der Datenbank gespeichert wurde, kann nicht geändert werden.') ?>
</p>


<div class="row row-eq-spacing">
    <div class="col-md-8">

        <?php foreach ($vocabularies as $vocab) { ?>

            <form action="<?= ROOTPATH ?>/crud/admin/vocabularies/<?= $vocab['id'] ?>" method="POST" class="box padded" id="vocabulary-<?= $vocab['id'] ?>">
                <h2 class="title">
                    <span class="badge primary float-sm-right">
                        <?= $vocab['category'] ?>
                    </span>
                    <?= lang($vocab['name'], $vocab['name_de'] ?? null) ?>
                </h2>

                <code class="code">
                    <?= $vocab['id'] ?>
                </code>

                <p class="text-secondary">
                    <?= lang($vocab['description'], $vocab['description_de'] ?? null) ?>
                </p>
                <table class="table simple">
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
                            <td class="w-50 bg-white"></td>
                            <td colspan="4" class="bg-white">
                                <button type="button" class="btn small primary" onclick="addRow(this)">
                                    <i class="ph ph-plus"></i>
                                    <?= lang('Add Value', 'Wert hinzufügen') ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>



                </table>
                <button type="submit" class="btn primary my-10">
                    <?= lang('Save', 'Speichern') ?>
                </button>
            </form>

        <?php } ?>

        <script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>

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
    </div>

    <div class="col-lg-3 d-none d-lg-block">
        <nav class="on-this-page-nav">
            <div class="content">
                <h4 class=""><?= lang('Content', 'Inhalt') ?></h4>
                <div class="list">
                    <?php 
                    $cat = '';
                    foreach ($vocabularies as $vocab) {
                        if ($cat != $vocab['category']) {
                            if ($cat != '') echo '<br>';
                            $cat = $vocab['category'];
                            ?>
                            <b>
                                <?= lang($cat) ?>
                            </b>
                        <?php } ?>
                        <a href="#vocabulary-<?= $vocab['id'] ?>">
                            <?= lang($vocab['name'], $vocab['name_de'] ?? null) ?>
                        </a>
                    <?php } ?>
                </div>
        </nav>
    </div>
</div>