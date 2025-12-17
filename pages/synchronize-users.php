<?php

require_once BASEPATH . '/php/init.php';
require_once BASEPATH . '/php/_login.php';

$action = $_GET['action'] ?? 'view';

if ($action === 'view') {
?>

    <h1>
        <i class="ph ph-users"></i>
        <?= lang('User Management', 'Nutzerverwaltung') ?>
    </h1>

    <?= lang('Please find general settings on user data fields and LDAP attribute synchronization in the', 'Allgemeine Einstellungen zu Nutzerfeldern und LDAP-Attribut-Synchronisation findest du in den') ?> <a href="<?= ROOTPATH ?>/admin/persons"><?= lang('Person Settings', 'Nutzereinstellungen') ?></a>.

    <div class="box">
        <div class="content">
            <h2 class="title">
                <i class="ph-duotone ph-arrows-clockwise"></i>
                <?= lang('Synchronize users', 'Nutzer:innen synchronisieren') ?>
            </h2>
            <p>
                <?= lang('Here you can synchronize your users with your LDAP directory. You can choose to add new users, reactivate inactive users, or inactivate users that are no longer present in the LDAP directory.', 'Hier kannst du deine Nutzer:innen mit deinem LDAP-Verzeichnis synchronisieren. Du kannst wählen, ob du neue Nutzer:innen hinzufügen, inaktive Nutzer:innen reaktivieren oder Nutzer:innen, die nicht mehr im LDAP-Verzeichnis vorhanden sind, inaktivieren möchtest.') ?>
            </p>

            <a href="?action=synchronize" class="btn primary">
                <i class="ph ph-arrows-clockwise"></i>
                <?= lang('Start synchronization', 'Synchronisierung starten') ?>
            </a>
        </div>
    </div>

    <div class="box">
        <div class="content">
            <h2 class="title">
                <i class="ph-duotone ph-user-list text-secondary"></i>
                <?= lang('Attribute synchronization', 'Attribut-Synchronisation') ?>
            </h2>
            <p>
                <?= lang('You can synchronize user attributes from your LDAP directory to OSIRIS. This includes fields like email, telephone, and department.', 'Du kannst Nutzerattribute aus deinem LDAP-Verzeichnis mit OSIRIS synchronisieren. Dazu gehören Felder wie E-Mail, Telefon und Abteilung.') ?>
            </p>
            <!-- <a href="<?= ROOTPATH ?>/admin/persons#section-auth" class="btn primary">
                <i class="ph ph-user-list"></i>
                <?= lang('Attribute preview', 'Vorschau der Attribute') ?>
            </a> -->
            <form action="<?= ROOTPATH ?>/synchronize-attributes" method="post">
                <input type="hidden" name="preview" value="1">
                <button type="submit" class="btn primary">
                    <i class="ph ph-user-list"></i>
                    <?= lang('Attribute preview', 'Vorschau der Attribute') ?>
                </button>
            </form>
        </div>
    </div>

    <div class="box">
        <div class="content">
            <h2 class="title">
                <i class="ph-duotone ph-user-plus text-secondary"></i>
                <?= lang('Guest accounts', 'Gast-Accounts') ?>
            </h2>
            <p>
                <?= lang('You can add a guest account that allows temporary access to OSIRIS for users who are not in your LDAP directory.', 'Du kannst einen Gast-Account hinzufügen, der temporären Zugang zu OSIRIS für Nutzer:innen ermöglicht, die nicht in deinem LDAP-Verzeichnis sind.') ?>
            </p>
            <a href="<?= ROOTPATH ?>/admin/guest-account" class="btn primary">
                <i class="ph ph-user-plus"></i>
                <?= lang('Manage guest accounts', 'Gast-Accounts verwalten') ?>
            </a>
        </div>
    </div>

<?php
} elseif ($action === 'synchronize') {

    echo "<h1>" . lang('Synchronize users', 'Synchronisiere Nutzer:innen') . "</h1>";

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
        echo "<p>" . lang('No users found', 'Keine Nutzer:innen gefunden') . "</p>";
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
            <h2><?= lang('Guest accounts', 'Gast-Accounts') ?></h2>

            <?php
            // inactive guest accounts
            if (!empty($inactiveGuests)) {
            ?>
                <!-- list of inactive guest accounts -->
                <p>
                    <?= lang('The following guest accounts are <b>inactive</b> (valid until date in the past) and will be treated like regular inactive users during synchronization.', 'Die folgenden Gast-Accounts sind <b>inaktiv</b> (Gültig-bis-Datum in der Vergangenheit) und werden bei der Synchronisation wie reguläre inaktive Nutzer behandelt.') ?>
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
                    <?= lang('The following guest accounts are <b>active</b> and will be ignored during synchronization.', 'Die folgenden Gast-Accounts sind <b>aktiv</b> und werden bei der Synchronisation ignoriert.') ?>
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
            <h2><?= lang('Inactivated users', 'Inaktivierte Nutzer') ?></h2>
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
            <h2><?= lang('Reactivated users', ' Reaktivierte Nutzer') ?></h2>
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
            <h2><?= lang('New users', 'Neue Nutzer:innen') ?></h2>
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
                    <label for="blacklist-<?= $u ?>"><?= lang('Blacklist', 'Blacklist') ?></label>
                </div>
            <?php } ?>
        <?php
        }


        // unchanged users (as collapsed list)
        if (!empty($actions['unchanged'])) {
        ?>
            <h2><?= lang('Unchanged users', 'Unveränderte Nutzer') ?></h2>
            <p>
                <?= lang('The following users are unchanged and will not be affected by the synchronization.', 'Die folgenden Nutzer:innen sind unverändert und werden von der Synchronisation nicht betroffen sein.') ?>
            </p>
            <details class="collapse-panel mb-20">
                <summary class="collapse-header">
                    <?= lang('Click here to view unchanged users', 'Unveränderte Nutzer anzeigen') ?>
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
                    <?= lang('Blacklisted users', 'Nutzer auf der Blacklist') ?>
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

        <button type="submit" class="btn secondary"><?= lang('Synchronize', 'Synchronisieren') ?></button>
    </form>
<?php
}
