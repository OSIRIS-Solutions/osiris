<?php

/**
 * The form to connect persons to an infrastructure
 * Created in cooperation with DSMZ
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


$persons = DB::doc2Arr($form['persons'] ?? array());
if (empty($persons)) {
    $persons = [
        ['user' => '', 'role' => '']
    ];
}
$all_users = $osiris->persons->find(['username' => ['$ne' => null], 'last' => ['$ne' => null]], ['sort' => ['last' => 1]])->toArray();

$start = $form['start_date'] ?? '';
$end = $form['end_date'] ?? '';
?>


<h2>
    <?= lang('Connect persons', 'Personen verknüpfen') ?>
</h2>
<form action="<?= ROOTPATH ?>/crud/infrastructures/update-persons/<?= $id ?>" method="post">

    <table class="table simple">
        <thead>
            <tr>
                <th>
                    <?= lang('Person', 'Person') ?><br>
                    <span class="badge kdsf m-0">
                        KDSF-B-13-16-A
                    </span>
                </th>
                <th>
                    <?= lang('Role', 'Rolle') ?>
                    <span class="badge kdsf m-0">
                        KDSF-B-13-16-E
                    </span>
                </th>
                <th>
                    <?= lang('Scope (FTE)', 'Umfang (VZÄ)') ?>
                    <span class="badge kdsf m-0">
                        KDSF-B-13-16-D
                    </span>
                </th>
                <th>
                    <?= lang('Start', 'Start') ?>
                    <span class="badge kdsf m-0">
                        KDSF-B-13-16-B
                    </span>
                </th>
                <th>
                    <?= lang('End', 'Ende') ?>
                    <span class="badge kdsf m-0">
                        KDSF-B-13-16-C
                    </span>
                </th>
                <th>
                    <?= lang('Reporter*') ?>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody id="infrastructure-list">
            <?php foreach ($persons as $i => $con) { ?>
                <tr>
                    <td class="">
                        <select name="persons[<?= $i ?>][user]" class="form-control person" required>
                            <?php
                            foreach ($all_users as $s) { ?>
                                <option value="<?= $s['username'] ?>" <?= ($con['user'] == $s['username'] ? 'selected' : '') ?>>
                                    <?= "$s[last], $s[first] ($s[username])" ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <select name="persons[<?= $i ?>][role]" id="persons-<?= $i ?>-role" class="form-control role" required>
                            <?php foreach ($Infra->roles as $role_id => $role) { ?>
                                <option value="<?= $role_id ?>" <?= ($con['role'] == $role_id ? 'selected' : '') ?>>
                                    <?= $role ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input type="number" name="persons[<?= $i ?>][fte]" id="persons-<?= $i ?>-fte" class="form-control" value="<?= $con['fte'] ?? 1 ?>" step="0.01" min="0" max="1">
                    </td>
                    <td>
                        <input type="date" name="persons[<?= $i ?>][start]" id="persons-<?= $i ?>-start" class="form-control start" value="<?= $con['start'] ?? $start ?>">
                    </td>
                    <td>
                        <input type="date" name="persons[<?= $i ?>][end]" id="persons-<?= $i ?>-end" class="form-control end" value="<?= $con['end'] ?? $end ?>">
                    </td>
                    <td>
                        <?php
                        $reporter = $con['reporter'] ?? 0;
                        ?>
                        <select name="persons[<?= $i ?>][reporter]" id="persons-<?= $i ?>-reporter" class="form-control" required>
                            <option value="0" <?= ($reporter == 0 ? 'selected' : '') ?>><?= lang('No', 'Nein') ?></option>
                            <option value="1" <?= ($reporter == 1 ? 'selected' : '') ?>><?= lang('Yes', 'Ja') ?></option>
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
                <td colspan="7">
                    <button class="btn" type="button" onclick="addInfrastructureRow()"><i class="ph ph-plus"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                </td>
            </tr>
        </tfoot>

    </table>

    <small>
        * <?= lang('Reporter are responsible for updating the statistics and will be asked by the system to do so once a year.', 'Die Berichterstatter sind für die Aktualisierung der Statistiken verantwortlich und werden vom System einmal im Jahr dazu aufgefordert.') ?>
    </small>
    <br>
    <button class="btn primary mt-20">
        <i class="ph ph-check"></i>
        <?= lang('Submit', 'Bestätigen') ?>
    </button>
</form>

<script>
    var counter = <?= $i ?? 0 ?>;
    var start = '<?= $start ?>'
    var end = '<?= $end ?>'
    const tr = $('#infrastructure-list tr').first()

    function addInfrastructureRow() {
        counter++;
        const row = tr.clone()
        row.find('select,input').each(function() {
            const name = $(this).attr('name').replace(/\d+/, counter)
            $(this).attr('name', name)
        })
        $('#infrastructure-list').append(row)
        // empty the values
        row.find('input').val('')
        row.find('select').val('')
        row.find('input.start').val(start)
        row.find('input.end').val(end)
    }
</script>