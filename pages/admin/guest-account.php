<?php

/**
 * Manage guest account while in LDAP user management
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.2
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph-duotone ph-user-plus"></i>
    <?= lang('common.manage_guest_accounts') ?>
</h1>
<a href="<?= ROOTPATH ?>/admin/guest-account/add" class="btn primary">
    <i class="ph ph-user-plus"></i>
    <?= lang('admin.add_guest_account') ?>
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
    echo "<p>" . lang('admin.no_guest_accounts_found') . "</p>";
} else {
?>

    <table class="table" id="guest-accounts-table">
        <thead>
            <tr>
                <th><?= lang('common.username') ?></th>
                <th><?= lang('common.name_first') ?></th>
                <th><?= lang('common.name_last') ?></th>
                <th><?= lang('common.mail_guest_account_add') ?></th>
                <th><?= lang('common.valid_until') ?></th>
                <th class="w-100"><?= lang('common.actions') ?></th>
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
                            <em><?= lang('common.unlimited') ?></em>
                        <?php } else { ?>
                            <span <?= $in_past ? 'class="text-danger"' : '' ?>><?= e($account['valid_until']) ?></span>
                        <?php } ?>
                    </td>
                    <td class="w-200 nowrap">
                        <div class="dropdown">
                            <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-edit-<?= e($account['username']) ?>" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-pencil"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-edit-<?= e($account['username']) ?>">
                                <form action="<?= ROOTPATH ?>/crud/admin/guest-account/update" method="post">
                                    <input type="hidden" name="username" value="<?= e($account['username']) ?>">
                                    <div class="form-group">
                                        <label for="valid_until_<?= e($account['username']) ?>"><?= lang('common.valid_until') ?></label>
                                        <input type="date" id="valid_until_<?= e($account['username']) ?>" name="valid_until" class="form-control" value="<?= isset($account['valid_until']) ? e($account['valid_until']) : '' ?>">
                                    </div>
                                    <button type="submit" class="btn primary mt-10">
                                        <i class="ph ph-check"></i>
                                        <?= lang('action.save') ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <?php if (($account['valid_until'] ?? '') > date('Y-m-d') || empty($account['valid_until'])) { ?>
                            <div class="dropdown">
                                <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-link-<?= e($account['username']) ?>" aria-haspopup="true" aria-expanded="false">
                                    <i class="ph ph-link"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right p-10 w-400" aria-labelledby="dropdown-link-<?= e($account['username']) ?>">
                                    <?= lang('admin.generate_a_link_to_set_a_new_password_for_this_guest_account_the_link_will') ?>
                                    <form action="<?= ROOTPATH ?>/crud/admin/guest-account/generate-link" method="post" class="mt-10">
                                        <input type="hidden" name="username" value="<?= e($account['username']) ?>">
                                        <button type="submit" class="btn primary">
                                            <i class="ph ph-link"></i>
                                            <?= lang('admin.generate_link') ?>
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
                                        <b><?= lang('common.note') ?></b>
                                        <?= lang('admin.only_the_user_account_will_be_deleted_the_corresponding_profile_will_remain') ?>
                                    </small><br>
                                    <button type="submit" class="btn danger" title="<?= lang('action.delete') ?>">
                                        <i class="ph ph-trash"></i>
                                        <?= lang('admin.delete_account') ?>
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