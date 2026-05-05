<?php

/**
 * Component to add new research data connections.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activity
 *
 * @package OSIRIS
 * @since 1.2.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<form action="<?= ROOTPATH ?>/crud/activities/update-tags/<?= $id ?>" method="post">

    <table class="table simple">
        <thead>
            <tr>
                <th><?= lang('Entity', 'Entität') ?></th>
                <th><?= lang('Name') ?></th>
                <th><?= lang('Link') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="connections">
            <?php
            $cons =  $doc['connections'] ?? [];
            if (empty($cons)) {
                $cons = [['entity' => '', 'name' => '', 'link' => '']];
            }
            foreach ($cons as $i => $con) { ?>
                <tr>
                    <td>
                        <input name="connections[<?= $i ?>][entity]" type="text" class="form-control" value="<?= $con['entity'] ?? '' ?>" required list="entity-list">
                    </td>
                    <td>
                        <input name="connections[<?= $i ?>][name]" type="text" class="form-control" value="<?= $con['name'] ?? '' ?>">
                    </td>
                    <td>
                        <input name="connections[<?= $i ?>][link]" type="text" class="form-control" value="<?= $con['link'] ?? '' ?>">
                    </td>
                    <td>
                        <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr id="last-row">
                <td></td>
                <td colspan="6">
                    <button class="btn" type="button" onclick="addConnectRow()"><i class="ph ph-plus"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                </td>
            </tr>
        </tfoot>

    </table>

    <datalist id="entity-list">
        <?php
        foreach ($osiris->activities->distinct('connections.entity') as $s) { ?>
            <option><?= $s ?></option>
        <?php } ?>
    </datalist>
    <button class="btn secondary mt-20">
        <i class="ph ph-check"></i>
        <?= lang('Submit', 'Bestätigen') ?>
    </button>
</form>


<script>
    var counter = <?= $i ?? 0 ?>;

    function addConnectRow() {
        counter++;
        var tr = $('<tr>')
        tr.append('<td><input name="connections[' + counter + '][entity]" type="text" class="form-control" required list="entity-list"></td>')
        tr.append('<td><input name="connections[' + counter + '][name]" type="text" class="form-control"></td>')
        tr.append('<td><input name="connections[' + counter + '][link]" type="text" class="form-control"></td>')
        var btn = $('<button class="btn danger" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
            $(this).closest('tr').remove();
        });
        tr.append($('<td>').append(btn))
        $('#connections').append(tr)
    }
</script>