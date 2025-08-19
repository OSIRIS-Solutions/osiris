<?php

/**
 * Module helper page
 * 
 * This page shows an overview of all data fields that are available in the system.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <?= lang('Data field overview', 'Übersicht der Datenfelder') ?>
</h1>

<p>
    <?= lang('This page shows an overview of all data fields that are available in the system.', 'Diese Seite zeigt eine Übersicht aller Datenfelder, die im System verfügbar sind.') ?>
</p>

<?php include_once BASEPATH . '/header-editor.php'; ?>

<!-- search bar -->
<input type="search" id="search" class="form-control" placeholder="<?= lang('Search', 'Suche') ?>">
<br>

<table class="table" id="modules">
    <tbody>

        <?php

        // include_once BASEPATH . '/php/example-document.php';
        $Modules = new Modules();
        foreach ($Modules->all_modules as $key => $vals) {
            if ($key == 'journal') {
                // get any journal from collection
                $journal = $osiris->journals->findOne();
                $Modules->form['journal'] = $journal['journal'];
                $Modules->form['journal_id'] = strval($journal['_id']);
            } elseif ($key == 'teaching-course') {
                $module = $osiris->teaching->findOne();
                $Modules->form['module'] = $module['module'];
                $Modules->form['module_id'] = strval($module['_id']);
            } else {
                $Modules->set($vals['fields']);
            }
        ?>
            <tr>
                <td>
                    <div class="search-text">
                        <h4 class="mt-0">
                            <?= lang($vals['name'], $vals['name_de']) ?>
                            <span class="code font-size-16 border ml-10"><?= $key ?></span>
                        </h4>
                        <p class="text-muted ">
                            <?= lang($vals['description'] ?? '', $vals['description_de'] ?? null) ?>
                        </p>
                        <p>
                            <?= lang('Saved fields', 'Gespeicherte Felder') ?>:
                            <?php foreach ($vals['fields'] as $f => $_) { ?>
                                <code class="badge primary"><?= $f ?></code>
                            <?php } ?>
                        </p>
                    </div>
                    <div class="<?= $key == 'event-select' ? 'w-800' : '' ?> my-10 border p-10 rounded bg-light">
                        <?php
                        $Modules->print_module($key);
                        ?>
                    </div>
                </td>
            </tr>
        <?php } ?>


    </tbody>
</table>

<script>
    $(document).ready(function() {
        $('#search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('table#modules>tbody>tr').filter(function() {
                $(this).toggle($(this).find('.search-text').text().toLowerCase().indexOf(value) > -1)
            });
        });
    });
</script>