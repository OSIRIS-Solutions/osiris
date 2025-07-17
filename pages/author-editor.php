<?php

/**
 * Page to see all activities
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activities
 * @link /my-activities
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<?php include_once BASEPATH . '/header-editor.php'; ?>
<style>
    tr.ui-sortable-helper {
        background-color: white;
        border: 1px solid var(--border-color);
    }
</style>
<div class="content">

    <h1>
        <i class="ph ph-users"></i>
        <?php if ($role == 'authors') { ?>
            <?= lang('Edit authors', 'Bearbeite die Autoren') ?>
        <?php } else { ?>
            <?= lang('Edit editors', 'Bearbeite die Editoren') ?>
        <?php } ?>
    </h1>
    <form action="<?= ROOTPATH ?>/crud/activities/update-authors/<?= $id ?>" method="post">

        <table class="table">
            <thead>
                <tr>
                    <th></th>
                    <th><?= lang('Last name', 'Nachname') ?> <span class="text-danger">*</span></th>
                    <th><?= lang('First name', 'Vorname') ?></th>
                    <th>Position</th>
                    <th><?= $Settings->get('affiliation') ?></th>
                    <th><?= lang('Units', 'Einheiten') ?> *</th>
                    <th>Username</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="authors">
                <?php foreach ($form[$role] as $i => $author) { ?>
                    <tr>
                        <td>
                            <i class="ph ph-dots-six-vertical text-muted handle"></i>
                        </td>
                        <td>
                            <input name="authors[<?= $i ?>][last]" type="text" class="form-control" value="<?= $author['last'] ?>" required>
                        </td>
                        <td>
                            <input name="authors[<?= $i ?>][first]" type="text" class="form-control" value="<?= $author['first'] ?>">
                        </td>
                        <td>
                            <?php if (isset($author['position'])) { ?>
                                <select name="authors[<?= $i ?>][position]" class="form-control">
                                    <option value="first" <?= ($author['position'] == 'first' ? 'selected' : '') ?>>first</option>
                                    <option value="middle" <?= ($author['position'] == 'middle' ? 'selected' : '') ?>>middle</option>
                                    <option value="corresponding" <?= ($author['position'] == 'corresponding' ? 'selected' : '') ?>>corresponding</option>
                                    <option value="last" <?= ($author['position'] == 'last' ? 'selected' : '') ?>>last</option>
                                </select>
                            <?php } else { ?>
                                NA
                            <?php } ?>
                        </td>
                        <td>
                            <div class="custom-checkbox">
                                <input type="checkbox" id="checkbox-<?= $i ?>" name="authors[<?= $i ?>][aoi]" value="1" <?= (($author['aoi'] ?? 0) == '1' ? 'checked' : '') ?>>
                                <label for="checkbox-<?= $i ?>" class="blank"></label>
                            </div>
                        </td>
                        <td>
                            <?php
                            if ($author['aoi'] ?? 0) {
                                $selected = DB::doc2Arr($author['units'] ?? []);
                                if (!is_array($selected)) $selected = [];
                                $person_units = $osiris->persons->findOne(['username' => $author['user']], ['units' => 1]);
                                $person_units = $person_units['units'] ?? [];
                                if (empty($person_units)) {
                                    echo '<small class="text-danger">No units found</small>';
                                } else {
                                    $person_units = array_column(DB::doc2Arr($person_units), 'unit');
                                    foreach ($person_units as $unit) { ?>
                                        <div class="custom-checkbox mb-5">
                                            <input type="checkbox"
                                                   name="authors[<?= $i ?>][units][]"
                                                   id="unit-<?= $i ?>-<?= htmlspecialchars($unit) ?>"
                                                   value="<?= htmlspecialchars($unit) ?>"
                                                   <?= in_array($unit, $selected) ? 'checked' : '' ?>>
                                            <label for="unit-<?= $i ?>-<?= htmlspecialchars($unit) ?>">
                                                <?= htmlspecialchars($unit) ?>
                                            </label>
                                        </div>
                                    <?php }
                                }
                            } else { ?>
                                <small>
                                    <?= lang('Not applicable', 'Nicht zutreffend') ?>
                                </small>
                            <?php } ?>
                        </td>
                        <td>
                            <input name="authors[<?= $i ?>][user]" type="text" class="form-control" list="user-list" value="<?= $author['user'] ?>">
                            <input name="authors[<?= $i ?>][approved]" type="hidden" class="form-control" value="<?= $author['approved'] ?? 0 ?>">
                        </td>
                        <td>
                            <button class="btn text-danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr id="last-row">
                    <td></td>
                    <td colspan="7">
                        <button class="btn" type="button" onclick="addAuthorRow()"><i class="ph ph-plus"></i> <?= lang('Add author', 'Autor hinzufügen') ?></button>
                    </td>
                </tr>
            </tfoot>

        </table>
        <button class="btn secondary mt-20">
            <i class="ph ph-check"></i>
            <?= lang('Submit', 'Bestätigen') ?>
        </button>

        
<datalist id="user-list">
    <?php
    $all_users = $osiris->persons->find(['username' => ['$ne' => null]]);
    foreach ($all_users as $s) { ?>
        <option value="<?= $s['username'] ?>"><?= "$s[last], $s[first] ($s[username])" ?></option>
    <?php } ?>
</datalist>
    </form>

    <p>
        * <?= lang('In case you have edited the username or affiliation, please save once before editing this.', 'Falls du den Nutzernamen oder die Affiliation geändert hast, bitte zuerst einmal speichern, damit du die Einheiten bearbeiten kannst.') ?>
    </p>

</div>


<script>
    var counter = <?= $i ?>;

    function addAuthorRow() {
        counter++;
        var tr = $('<tr>')
        tr.append('<td><i class="ph ph-dots-six-vertical text-muted handle"></i></td>')
        tr.append('<td><input name="authors[' + counter + '][last]" type="text" class="form-control" required></td>')
        tr.append('<td><input name="authors[' + counter + '][first]" type="text" class="form-control"></td>')
        tr.append('<td><select name="authors[' + counter + '][position]" class="form-control"><option value="first">first</option><option value="middle" selected>middle</option><option value="corresponding">corresponding</option><option value="last">last</option></select></td>')
        tr.append('<td><div class="custom-checkbox"><input type="checkbox" id="checkbox-' + counter + '" name="authors[' + counter + '][aoi]" value="1"><label for="checkbox-' + counter + '" class="blank"></label></div></td>')
        tr.append('<td> <input name="authors[' + counter + '][user]" type="text" class="form-control" list="user-list"></td>')
        var btn = $('<button class="btn" type="button">').html('<i class="ph ph-trash"></i>').on('click', function() {
            $(this).closest('tr').remove();
        });
        tr.append($('<td>').append(btn))
        $('#authors').append(tr)
    }

    $(document).ready(function() {
        $('#authors').sortable({
            handle: ".handle",
            // change: function( event, ui ) {}
        });
    })
</script>