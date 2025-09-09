<h1>
    <i class="ph ph-user-gear" aria-hidden="true"></i>
    <?= lang('Distribute roles', 'Rollen verteilen') ?>
</h1>

<p class="text-muted">
    <?= lang('You cannot assign the admin role here. This can only be done directly in the profile of the user.', 'Die Admin-Rolle kann hier nicht vergeben werden. Dies ist nur direkt im Profil des Nutzers möglich.') ?>
</p>

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
// $roles = array_merge($roles, ['admin']);


// get all active users
$users = DB::doc2Arr($osiris->persons->find(['is_active' => ['$in' => [1, true, '1']]], ['sort' => ['last' => 1], 'projection' => ['last' => 1, 'first' => 1, 'username' => 1, 'roles' => 1]]));
?>
<form action="<?= ROOTPATH ?>/crud/admin/update-user-roles" method="post">

    <table class="table small striped w-auto">
        <thead>
            <th><?= lang('User', 'Benutzer') ?></th>
            <?php foreach ($roles as $role) { ?>
                <th><?= ucfirst($role) ?></th>
            <?php } ?>
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
                    <?php foreach ($roles as $role) { ?>
                        <td>
                            <input type="checkbox" <?= in_array($role, $userroles) ? 'checked' : '' ?> name="roles[<?= $user['username'] ?>][]" value="<?= $role ?>" />
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
    </table>
    <button class="btn btn-primary">
        <i class="ph ph-save"></i>
        <?= lang('Save roles', 'Rollen speichern') ?>
    </button>
</form>