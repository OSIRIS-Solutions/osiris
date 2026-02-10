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
                <th class="w-100"><?= lang('Actions', 'Aktionen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($accounts as $account) :
                $in_past = isset($account['valid_until']) && $account['valid_until'] < date('Y-m-d');
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/profile/<?= e($account['username']) ?>">
                            <?= e($account['username']) ?>
                        </a>
                    </td>
                    <td><?= e($account['first'] ?? '') ?></td>
                    <td><?= e($account['last'] ?? '') ?></td>
                    <td><?= e($account['mail'] ?? '') ?></td>
                    <td>
                        <?php if (empty($account['valid_until'] ?? '')) { ?>
                            <em><?= lang('Unlimited', 'Unbegrenzt') ?></em>
                        <?php } else { ?>
                            <span <?= $in_past ? 'class="text-danger"' : '' ?>><?= e($account['valid_until']) ?></span>
                        <?php } ?>
                    </td>
                    <td class="w-100">
                        <div class="dropdown">
                            <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-edit-<?= e($account['username']) ?>" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-pencil"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-edit-<?= e($account['username']) ?>">
                                <form action="<?= ROOTPATH ?>/crud/admin/guest-account/update" method="post">
                                    <input type="hidden" name="username" value="<?= e($account['username']) ?>">
                                    <div class="form-group">
                                        <label for="valid_until_<?= e($account['username']) ?>"><?= lang('Valid until', 'Gültig bis') ?></label>
                                        <input type="date" id="valid_until_<?= e($account['username']) ?>" name="valid_until" class="form-control" value="<?= isset($account['valid_until']) ? e($account['valid_until']) : '' ?>">
                                    </div>
                                    <button type="submit" class="btn primary mt-10">
                                        <i class="ph ph-check"></i>
                                        <?= lang('Save', 'Speichern') ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <?php if ($account['valid_until'] > date('Y-m-d') || empty($account['valid_until'])) { ?>
                            <div class="dropdown">
                                <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-link-<?= e($account['username']) ?>" aria-haspopup="true" aria-expanded="false">
                                    <i class="ph ph-link"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right p-10 w-400" aria-labelledby="dropdown-link-<?= e($account['username']) ?>">
                                    <?= lang('Generate a link to set a new password for this guest account. The link will be valid for 24 hours.', 'Generieren Sie einen Link, um ein neues Passwort für diesen Gast-Account festzulegen. Der Link ist 24 Stunden gültig.') ?>
                                    <form action="<?= ROOTPATH ?>/crud/admin/guest-account/generate-link" method="post" class="mt-10">
                                        <input type="hidden" name="username" value="<?= e($account['username']) ?>">
                                        <button type="submit" class="btn primary">
                                            <i class="ph ph-link"></i>
                                            <?= lang('Generate link', 'Link generieren') ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="dropdown">
                            <button class="btn small danger" data-toggle="dropdown" type="button" id="dropdown-delete-<?= e($account['username']) ?>" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-trash"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right w-400" aria-labelledby="dropdown-delete-<?= e($account['username']) ?>">
                                <form action="<?= ROOTPATH ?>/crud/admin/guest-account/delete" method="post" class="d-inline">
                                    <input type="hidden" name="username" value="<?= e($account['username']) ?>">
                                    <small>
                                        <b><?= lang('Note:', 'Anmerkung:') ?></b>
                                        <?= lang('Only the user account will be deleted. The corresponding profile will remain in the system. If the corresponding user name has been added to LDAP, the user will be able to log in again via LDAP. Otherwise, it will appear as removed in the LDAP synchronization, thus being deactivated by default. The "guest account" flag will also be removed.', 'Es wird nur der Benutzer-Account gelöscht. Das zugehörige Profil bleibt im System erhalten. Wenn der entsprechende Benutzername in LDAP hinzugefügt wurde, kann sich der Benutzer wieder über LDAP anmelden. Andernfalls erscheint er bei der LDAP-Synchronisation als entfernt und wird somit standardmäßig deaktiviert. Der Flag "Gästeaccount" wird ebenfalls entfern.') ?>
                                    </small><br>
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