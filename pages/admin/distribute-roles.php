<?php

/**
 * Admin page for distributing roles to users
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/roles/distribute
 *
 * @package     OSIRIS
 * @since       1.5.2
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>


<form action="<?= ROOTPATH ?>/crud/admin/update-user-roles" method="post">
    <div class="container w-800 mw-full">

        <h1>
            <i class="ph-duotone ph-shield-check" aria-hidden="true"></i>
            <?= lang('Distribute roles', 'Rollen verteilen') ?>
        </h1>

        <p>
            <i class="ph ph-warning text-signal"></i>
            <?= lang('You cannot assign the admin role here. This can only be done directly in the profile of the user.', 'Die Admin-Rolle kann hier nicht vergeben werden. Dies ist nur direkt im Profil des Nutzers möglich.') ?>
            <?= lang('All logged in users have the role <code>user</code>.', 'Alle angemeldeten Nutzer:innen haben die Rolle <code>user</code>.') ?>
        </p>

    </div>

    <?php
    // get all roles
    $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
    $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

    // if scientist is not in the roles, add them
    if (!in_array('scientist', $roles)) {
        $roles[] = 'scientist';
    }
    // sort admin last
    $roles = array_diff($roles, ['admin', 'user']);

    // get all active users
    $users = DB::doc2Arr($osiris->persons->find(['is_active' => ['$in' => [1, true, '1']]], ['sort' => ['last' => 1], 'projection' => ['last' => 1, 'first' => 1, 'username' => 1, 'roles' => 1, 'units' => 1]]));
    ?>

    <style>
        .table.sticky-head thead {
            position: sticky;
            top: 0;
            background: white;
            z-index: 1;
            top: 6rem;
        }
    </style>

    <table class="table hover small w-auto mx-auto sticky-head">
        <thead>
            <tr>
                <th><?= lang('User', 'Benutzer') ?></th>
                <th><?= lang('Units', 'Einheiten') ?></th>
                <?php foreach ($roles as $role) { ?>
                    <th><?= ucfirst($role) ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) {
                $userroles = DB::doc2Arr($user['roles'] ?? []);
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= $user['username'] ?>" target="_blank" rel="noopener noreferrer" class="colorless">
                            <?= $user['first'] ?? '' ?> <b><?= $user['last'] ?? $user['username'] ?></b>
                        </a>
                        <input type="hidden" name="roles[<?= $user['username'] ?>]" value=''>
                    </td>
                    <td>
                        <!-- info on Units -->
                        <?php
                        $units = $Groups->getPersonUnit($user, null, false, false);
                        if (!empty($units)) {
                            $units = array_column(DB::doc2Arr($units), 'unit');
                        ?>
                            <small class="d-block text-muted">
                                <?= implode(', ', $units) ?>
                            </small>
                        <?php } ?>
                    </td>
                    <?php foreach ($roles as $role) { ?>
                        <td>
                            <input type="checkbox" <?= in_array($role, $userroles) ? 'checked' : '' ?> name="roles[<?= $user['username'] ?>][]" value="<?= $role ?>" />
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="container w-800 mw-full">
        <button class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save roles', 'Rollen speichern') ?>
        </button>
    </div>
</form>