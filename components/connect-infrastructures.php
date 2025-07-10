<?php

/**
 * Component to connect infrastructures to activities.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activity
 *
 * @package OSIRIS
 * @since 1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

 $permission = $Settings->hasPermission('infrastructures.edit');
$filter = [];
if (!$permission) {
    $filter = ['persons.user' => $user];
}
 $infrastructures = $osiris->infrastructures->find(
     $filter,
     ['sort' => ['end_date' => -1, 'start_date' => 1], 'infrastructureion' => ['id' => 1, 'name' => 1]]
 )->toArray();
?>

<form action="<?= ROOTPATH ?>/crud/activities/update-infrastructure-data/<?= $id ?>" method="post">

    <table class="table simple">
        <thead>
            <tr>
                <th><?= $Settings->infrastructureLabel() ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="infrastructure-list">
            <?php
            if (!isset($doc['infrastructures']) || empty($doc['infrastructures'])) {
                $doc['infrastructures'] = [''];
            }
            foreach ($doc['infrastructures'] as $i => $con) { ?>
                <tr>
                    <td class="w-full">
                        <select name="infrastructures[<?= $i ?>]" id="infrastructures-<?= $i ?>" class="form-control" required>
                            <option value="" disabled <?= empty($con) ? 'selected' : '' ?>>-- <?= lang('Please select an infrastructure', 'Bitte wähle eine Infrastruktur aus') ?> --</option>
                            <?php
                            foreach ($infrastructures as $s) { ?>
                                <option <?= $con == $s['id'] ? 'selected' : '' ?> value="<?=$s['id']?>"><?= $s['name'] ?></option>
                            <?php } ?>
                        </select>
                    </td>

                    <td>
                        <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr id="last-row">
                <td colspan="2">
                    <button class="btn small" type="button" onclick="addInfrastructureRow()"><i class="ph ph-plus text-success"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                </td>
            </tr>
        </tfoot>

    </table>
<?php if (!$permission) { ?>
    
    <p>
        <?= lang('Note: only infrastructures in which you participate are shown here.', 'Bemerkung: hier werden nur Infrastrukturen gezeigt, an denen du beteiligt bist.') ?>
        <a href="<?= ROOTPATH ?>/infrastructures" class="link"><?= lang('See all', 'Zeige alle') ?></a>
    </p>
<?php } ?>

    <button class="btn secondary">
        <i class="ph ph-check"></i>
        <?= lang('Submit', 'Bestätigen') ?>
    </button>
</form>


<script>
    var infrastructureCounter = <?= $i ?? 0 ?>;
    const infraTr = $('#infrastructure-list tr').first()

    function addInfrastructureRow() {
        infrastructureCounter++;
        const row = infraTr.clone()
        row.find('select').first().attr('name', 'infrastructures[' + infrastructureCounter + ']');
        $('#infrastructure-list').append(row)
    }
</script>