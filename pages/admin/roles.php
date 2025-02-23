<?php

/**
 * Page for admin dashboard for role settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.2.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

// include BASEPATH . "/components/admin-nav.php";
?>

<?php
$json = file_get_contents(BASEPATH . "/roles.json");
$role_groups = json_decode($json, true, 512, JSON_NUMERIC_CHECK);

$req = $osiris->adminGeneral->findOne(['key' => 'roles']);
$roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

$rights = [];

foreach ($osiris->adminRights->find([]) as $row) {
    $rights[$row['right']][$row['role']] = $row['value'];
}

?>

<!-- modal to add and remove roles -->
<div class="modal" id="role-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= lang('Edit Roles', 'Rollen bearbeiten') ?></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= ROOTPATH ?>/crud/admin/roles" method="post">
                    <table class="table simple w-auto">
                        <thead>
                            <th><?= lang('Role', 'Rolle') ?></th>
                            <th><?= lang('Action', 'Aktion') ?></th>
                        </thead>
                        <tbody>
                            <?php foreach ($roles as $role) { ?>
                                <tr>
                                    <td>
                                        <input type="hidden" name="roles[]" value="<?= $role ?>">
                                        <?= strtoupper($role) ?>
                                    </td>
                                    <td>
                                        <?php if (!in_array($role, ['user', 'scientist', 'admin', 'editor'])) { ?>
                                            <button class="btn danger" role="button" onclick="$(this).closest('tr').remove()">
                                                <i class="ph ph-x"></i>
                                                <?= lang('Remove', 'Entfernen') ?>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn disabled" role="button" disabled>
                                                <i class="ph ph-x"></i>
                                                <?= lang('Remove', 'Entfernen') ?>
                                            </button>
                                        <?php } ?>

                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>

                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" name="role" class="form-control" placeholder="<?= lang('Role', 'Rolle') ?>" id="newrole">
                            <div class="input-group-append">
                                <button class="btn success" type="button" onclick="addRole()">
                                    <i class="ph ph-plus"></i>
                                    <?= lang('Add', 'Hinzufügen') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button class="btn success">
                        <i class="ph ph-floppy-disk"></i>
                        <?= lang('Save', 'Speichern') ?>
                    </button>
                </form>
                <script>
                    function addRole() {
                        var role = $('#newrole').val();
                        if (role) {
                            $('#role-modal tbody').append(`<tr>
                                <td>
                                    <input type="hidden" name="roles[]" value="${role.toLowerCase()}">
                                    ${role.toUpperCase()}
                                </td>
                                <td>
                                    <button class="btn danger" role="button" onclick="$(this).closest('tr').remove()">
                                        <i class="ph ph-x"></i>
                                        <?= lang('Remove', 'Entfernen') ?>
                                    </button>
                                </td>
                            </tr>`);
                            $('#newrole').val('');
                        }
                    }
                </script>
            </div>
        </div>
    </div>
</div>


<a class="btn float-right" href="#role-modal">
    <i class="ph ph-user-gear" aria-hidden="true"></i>
    <?= lang('Edit Roles', 'Rollen bearbeiten') ?>
</a>

<h1>
    <?= lang('Roles &amp; Rights', 'Rollen &amp; Rechte') ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/admin/roles" method="post" id="role-form">

    <style>
        .table.sticky-head thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
            top: 6rem;
        }
    </style>

    <table class="table my-20 sticky-head">

        <thead>
            <th></th>
            <?php foreach ($roles as $role) { ?>
                <th>
                    <input type="hidden" readonly name="roles[]" value="<?= $role ?>">
                    <?= strtoupper($role) ?>
                </th>
            <?php } ?>
        </thead>
        <tbody>
            <?php foreach ($role_groups as $group) {
            ?>
                <tr>
                    <th colspan="<?= count($roles) + 1 ?>">
                        <?= lang($group['en'], $group['de']) ?>
                    </th>
                </tr>
                <?php foreach ($group['fields'] as $field) {
                    $right = $field['id'];
                    $values = $rights[$right] ?? array();
                ?>
                    <tr>
                        <td class="pl-20">
                            <?= lang($field['en'], $field['de']) ?>
                            <code class="code font-size-12 text-muted"><?= $right ?></code>
                        </td>
                        <?php foreach ($roles as $role) {
                            $val = $values[$role] ?? false;
                        ?>
                            <td>
                                <input type="checkbox" <?= $val ? 'checked' : '' ?> onchange="$(this).next().val(Number(this.checked))">
                                <input type="hidden" name="values[<?= $right ?>][<?= $role ?>]" value="<?= $val ? 1 : 0 ?>">
                            </td>
                        <?php } ?>

                    </tr>
                <?php } ?>

            <?php } ?>
        </tbody>

    </table>

    <button class="btn success">
        <i class="ph ph-floppy-disk"></i>
        Save
    </button>


</form>