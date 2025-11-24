<?php

/**
 * Manage guest account while in LDAP user management
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.2
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph-duotone ph-user-plus"></i>
    <?= lang('Manage guest accounts', 'Gast-Accounts verwalten') ?>
</h1>
<a href="<?= ROOTPATH ?>/admin/guest-account/add" class="btn primary">
    <i class="ph ph-user-plus"></i>
    <?= lang('Add guest account', 'Gast-Account hinzufügen') ?>
</a>

<?php
$accounts = $osiris->guestAccounts->aggregate([
    ['$sort' => ['valid_until' => 1]],
    // join with persons collection to get more info
    ['$lookup' => [
        'from' => 'persons',
        'localField' => 'username',
        'foreignField' => 'username',
        'as' => 'person_info'
    ]],
    // unwind person_info array
    ['$unwind' => [
        'path' => '$person_info',
        'preserveNullAndEmptyArrays' => true
    ]],
    // project desired fields
    ['$project' => [
        'username' => 1,
        'first' => '$person_info.first',
        'last' => '$person_info.last',
        'mail' => '$person_info.mail',
        'valid_until' => 1
    ]]

])->toArray();
if (empty($accounts)) {
    echo "<p>" . lang('No guest accounts found.', 'Keine Gast-Accounts gefunden.') . "</p>";
} else {
?>

    <table class="table" id="guest-accounts-table">
        <thead>
            <tr>
                <th><?= lang('Username', 'Benutzername') ?></th>
                <th><?= lang('First name', 'Vorname') ?></th>
                <th><?= lang('Last name', 'Nachname') ?></th>
                <th><?= lang('Mail', 'E-Mail') ?></th>
                <th><?= lang('Valid until', 'Gültig bis') ?></th>
                <th><?= lang('Actions', 'Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account) :
                $in_past = isset($account['valid_until']) && $account['valid_until'] < date('Y-m-d');
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= htmlspecialchars($account['username']) ?>">
                            <?= htmlspecialchars($account['username']) ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($account['first'] ?? '') ?></td>
                    <td><?= htmlspecialchars($account['last'] ?? '') ?></td>
                    <td><?= htmlspecialchars($account['mail'] ?? '') ?></td>
                    <td>
                        <?php if (empty($account['valid_until'] ?? '')) { ?>
                            <em><?= lang('Unlimited', 'Unbegrenzt') ?></em>
                        <?php } else { ?>
                            <span <?= $in_past ? 'class="text-danger"' : '' ?>><?= htmlspecialchars($account['valid_until']) ?></span>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-pencil"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-center" aria-labelledby="dropdown-1">
                                <form action="<?= ROOTPATH ?>/crud/admin/guest-account/update" method="post">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($account['username']) ?>">
                                    <div class="form-group">
                                        <label for="valid_until_<?= htmlspecialchars($account['username']) ?>"><?= lang('Valid until', 'Gültig bis') ?></label>
                                        <input type="date" id="valid_until_<?= htmlspecialchars($account['username']) ?>" name="valid_until" class="form-control" value="<?= isset($account['valid_until']) ? htmlspecialchars($account['valid_until']) : '' ?>">
                                    </div>
                                    <button type="submit" class="btn primary mt-10">
                                        <i class="ph ph-check"></i>
                                        <?= lang('Save', 'Speichern') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn small danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-trash"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-center" aria-labelledby="dropdown-1">
                                <form action="<?= ROOTPATH ?>/crud/admin/guest-account/delete" method="post" class="d-inline">
                                    <input type="hidden" name="username" value="<?= htmlspecialchars($account['username']) ?>">
                                    <small>
                                        <b><?= lang('Note:', 'Anmerkung:') ?></b>
                                        <?= lang('Only the user account will be deleted. The corresponding profile will remain in the system. If the corresponding user name has been added to LDAP, the user will be able to log in again via LDAP.', 'Es wird nur der Benutzer-Account gelöscht. Das zugehörige Profil bleibt im System erhalten. Wenn der entsprechende Benutzername in LDAP hinzugefügt wurde, kann sich der Benutzer wieder über LDAP anmelden.') ?>
                                    </small>
                                    <button type="submit" class="btn danger" title="<?= lang('Delete', 'Löschen') ?>">
                                        <i class="ph ph-trash"></i>
                                        <?= lang('Delete account', 'Account löschen') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
}
?>

<script>
    // DataTables
    $(document).ready(function() {
        $('#guest-accounts-table').DataTable();
    });
</script>