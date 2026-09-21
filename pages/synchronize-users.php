<?php

require_once BASEPATH . '/php/init.php';
require_once BASEPATH . '/php/_login.php';

$action = $_GET['action'] ?? 'view';

if ($action === 'view') {
?>

    <h1>
        <i class="ph ph-users"></i>
        <?= lang('people.user_management') ?>
    </h1>

    <?= lang('people.please_find_general_settings_on_user_data_fields_and_ldap_attribute_synchro') ?> <a href="<?= ROOTPATH ?>/admin/persons"><?= lang('people.person_settings') ?></a>.

    <div class="box">
        <div class="content">
            <h2 class="title">
                <i class="ph-duotone ph-arrows-clockwise"></i>
                <?= lang('admin.synchronize_users') ?>
            </h2>
            <p>
                <?= lang('people.here_you_can_synchronize_your_users_with_your_ldap_directory_you_can_choose') ?>
            </p>

            <a href="?action=synchronize" class="btn primary">
                <i class="ph ph-arrows-clockwise"></i>
                <?= lang('people.start_synchronization') ?>
            </a>
        </div>
    </div>

    <div class="box">
        <div class="content">
            <h2 class="title">
                <i class="ph-duotone ph-user-list text-secondary"></i>
                <?= lang('admin.attribute_synchronization') ?>
            </h2>
            <p>
                <?= lang('people.you_can_synchronize_user_attributes_from_your_ldap_directory_to_osiris_this') ?>
            </p>
            <!-- <a href="<?= ROOTPATH ?>/admin/persons#section-auth" class="btn primary">
                <i class="ph ph-user-list"></i>
                <?= lang('people.attribute_preview') ?>
            </a> -->
            <form action="<?= ROOTPATH ?>/synchronize-attributes" method="post">
                <input type="hidden" name="preview" value="1">
                <button type="submit" class="btn primary">
                    <i class="ph ph-user-list"></i>
                    <?= lang('people.attribute_preview') ?>
                </button>
            </form>
        </div>
    </div>

    <div class="box">
        <div class="content">
            <h2 class="title">
                <i class="ph-duotone ph-user-plus text-secondary"></i>
                <?= lang('people.guest_accounts') ?>
            </h2>
            <p>
                <?= lang('people.you_can_add_a_guest_account_that_allows_temporary_access_to_osiris_for_user') ?>
            </p>
            <a href="<?= ROOTPATH ?>/admin/guest-account" class="btn primary">
                <i class="ph ph-user-plus"></i>
                <?= lang('common.manage_guest_accounts') ?>
            </a>
        </div>
    </div>

<?php
} elseif ($action === 'synchronize') {

    echo "<h1><i class='ph-duotone ph-arrows-clockwise'></i>" . lang('common.synchronize_users') . "</h1>";

    // get all users from LDAP
    $blacklist = [];
    $bl = $Settings->get('ldap-sync-blacklist');
    if (!empty($bl)) {
        $bl = explode(',', $bl);
        $blacklist = array_filter(array_map('trim', $bl));
        echo "<p> There are " . count($blacklist) . " usernames on your blacklist.</p>";
    } else {
        echo "<p>Your blacklist is empty, all users are synchronized.</p>";
    }
    $whitelist = [];
    $bl = $Settings->get('ldap-sync-whitelist');
    if (!empty($bl)) {
        $bl = explode(',', $bl);
        $whitelist = array_filter(array_map('trim', $bl));
        echo "<p> There are " . count($whitelist) . " usernames on your whitelist.</p>";
    } else {
        echo "<p>Your whitelist is empty, ignored users are not synchronized.</p>";
    }

    $guestAccounts = $osiris->guestAccounts->find([], ['projection' => ['username' => 1, 'valid_until' => 1]])->toArray();
    $activeGuests = [];
    $inactiveGuests = [];
    foreach ($guestAccounts as $ga) {
        if (!empty($ga['valid_until'] ?? null) && strtotime($ga['valid_until']) < time()) {
            $inactiveGuests[] = $ga['username'];
            continue;
        }
        $activeGuests[] = $ga['username'];
    }

    $users = getUsers();
    if (isset($users['msg'])) {
        echo "<div class='alert signal mb-10'>" . $users['msg'] . "</div>";
        unset($users['msg']);
    }

    if (empty($users)) {
        echo "<p>" . lang('people.no_users_found') . "</p>";
        return;
    }

    $usernames = array_column($users, 'username');
    $uniqueids = array_column($users, 'uniqueid');

    $removed = $osiris->persons->find(
        ['username' => ['$nin' => $usernames], 'uniqueid' => ['$nin' => $uniqueids], 'is_active' => ['$in' => [1, true, '1']]],
        ['projection' => ['username' => 1, 'is_active' => 1, 'displayname' => 1]]
    )->toArray();

    $actions = [
        'blacklisted' => [],
        'inactivate' => [],
        'reactivate' => [],
        'add' => [],
        'unchanged' => []
    ];

    foreach ($removed as $del) {
        $username = $del['username'];
        $name = $del['displayname'] ?? $username;
        if (in_array($username, $activeGuests)) {
            // ignore guest accounts
            continue;
        } elseif (in_array($username, $blacklist)) {
            $actions['blacklisted'][$username] = $name;
        } else {
            $actions['inactivate'][$username] = $name;
        }
    }

    foreach ($users as $user) {
        $username = $user['username'];
        $uniqueid = $user['uniqueid'] ?? null;
        $active = $user['is_active'] ?? false;
        $exists = false;
        $dbactive = false;

        // first: check if user is in database
        if (!empty($uniqueid)) {
            $USER = $DB->getPersonByUniqueID($uniqueid);
        }
        if (empty($USER)) {
            $USER = $DB->getPerson($username);
        }
        if (!empty($USER)) {
            if ($USER['is_active'])
                $dbactive = 'active';
            $exists = true;
            $name = $USER['displayname'];
        } else {
            $USER = newUser($username);
            $name = $USER['displayname'] ?? $username;
        }

        // check if username is on the blacklist
        if (in_array($username, $blacklist)) {
            $actions['blacklisted'][$username] = $name;
        } else if (!$active && $exists && $dbactive) {
            $actions['inactivate'][$username] = $name;
        } else if ($active && $exists && !$dbactive) {
            $actions['reactivate'][$username] = $name;
        } else if (!$exists) {
            $actions['add'][$username] = $name;
        } else {
            $actions['unchanged'][$username] = $name;
        }
    }
?>

    <form action="<?= ROOTPATH ?>/synchronize-users" method="post">


        <?php if (!empty($inactiveGuests) || !empty($activeGuests)) { ?>
            <h2><?= lang('people.guest_accounts') ?></h2>

            <?php
            // inactive guest accounts
            if (!empty($inactiveGuests)) {
            ?>
                <!-- list of inactive guest accounts -->
                <p>
                    <?= lang('people.the_following_guest_accounts_are_inactive_valid_until_date_in_the_past_and') ?>
                </p>
                <ul>
                    <?php
                    foreach ($inactiveGuests as $u) {
                        echo "<li>" . $DB->getNameFromId($u) . " ($u)</li>";
                    }
                    ?>
                </ul>
            <?php
            }

            // active guest accounts
            if (!empty($activeGuests)) {
            ?>
                <!-- list of active guest accounts -->
                <p>
                    <?= lang('people.the_following_guest_accounts_are_active_and_will_be_ignored_during_synchron') ?>
                </p>
                <ul>
                    <?php
                    foreach ($activeGuests as $u) {
                        echo "<li>" . $DB->getNameFromId($u) . " ($u)</li>";
                    }
                    ?>
                </ul>
            <?php
            }
            ?>

        <?php } ?>


        <?php
        // inactivated users
        if (!empty($actions['inactivate'])) {
            // interface to inactivate users
        ?>
            <h2><?= lang('people.inactivated_users') ?></h2>
            <!-- checkboxes -->
            <?php
            $inactivate = $actions['inactivate'];
            asort($inactivate);
            foreach ($inactivate as $u => $n) { ?>
                <div class="">
                    <input type="checkbox" name="inactivate[]" id="inactivate-<?= $u ?>" value="<?= $u ?>" checked>
                    <label for="inactivate-<?= $u ?>"><?= $n . ' (' . $u . ')' ?></label>
                </div>
            <?php } ?>
        <?php
        }

        // deleted users
        if (!empty($actions['reactivate'])) {
            // interface to reactivate users
        ?>
            <h2><?= lang('people.reactivated_users') ?></h2>
            <!-- checkboxes -->
            <?php
            $reactivate = $actions['reactivate'];
            asort($reactivate);
            foreach ($reactivate as $u => $n) { ?>
                <div class="">
                    <input type="checkbox" name="reactivate[]" id="reactivate-<?= $u ?>" value="<?= $u ?>">
                    <label for="reactivate-<?= $u ?>"><?= $n . ' (' . $u . ')' ?></label>

                </div>
            <?php } ?>
        <?php
        }


        // new users 
        if (!empty($actions['add'])) {
            // interface to add users
        ?>
            <h2><?= lang('people.new_users') ?></h2>
            <!-- checkboxes -->
            <?php
            $add = $actions['add'];
            asort($add);
            foreach ($add as $u => $n) { ?>
                <div>
                    <!-- radio check for add, blacklist and ignore -->
                    <input type="checkbox" name="add[]" id="add-<?= $u ?>" value="<?= $u ?>" checked>
                    <label for="add-<?= $u ?>"><?= $n . ' (' . $u . ')' ?></label>
                    <!-- add option for blacklist -->
                    <input type="checkbox" name="blacklist[]" id="blacklist-<?= $u ?>" value="<?= $u ?>" onclick="$('#add-<?= $u ?>').attr('checked', !$('#add-<?= $u ?>').attr('checked'))">
                    <label for="blacklist-<?= $u ?>"><?= lang('people.blacklist') ?></label>
                </div>
            <?php } ?>
        <?php
        }


        // unchanged users (as collapsed list)
        if (!empty($actions['unchanged'])) {
        ?>
            <h2><?= lang('people.unchanged_users') ?></h2>
            <p>
                <?= lang('people.the_following_users_are_unchanged_and_will_not_be_affected_by_the_synchroni') ?>
            </p>
            <details class="collapse-panel mb-20">
                <summary class="collapse-header">
                    <?= lang('people.click_here_to_view_unchanged_users') ?>
                </summary>
                <div class="collapse-content">
                    <ul>
                        <?php foreach ($actions['unchanged'] as $username => $name) {
                            echo "<li>$name ($username)</li>";
                        } ?>
                    </ul>
                </div>
            </details>
        <?php
        }

        // blacklisted users
        if (!empty($actions['blacklisted'])) {
        ?>
            <details class="collapse-panel">
                <summary class="collapse-header">
                    <?= lang('people.blacklisted_users') ?>
                </summary>
                <div class="collapse-content">
                    <ul>
                        <?php foreach ($actions['blacklisted'] as $username => $name) {
                            echo "<li>$name ($username)</li>";
                        } ?>
                    </ul>
                </div>
            </details>
        <?php } ?>

        <button type="submit" class="btn secondary"><?= lang('people.synchronize') ?></button>
    </form>
<?php
}
