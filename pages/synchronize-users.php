<?php

require_once BASEPATH . '/php/init.php';
require_once BASEPATH . '/php/_login.php';

echo "<h1>" . lang('Synchronize users', 'Synchronisiere Nutzer:innen') . "</h1>";

// link to synchronize attributes
echo "<p><a href='" . ROOTPATH . "/synchronize-attributes' class='btn primary float-right'> " . lang('Synchronize attributes', 'Synchronisiere Attribute') . "</a></p>";

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

$users = getUsers();
if (isset($users['msg'])) {
    echo "<div class='alert signal mb-10'>" . $users['msg'] . "</div>";
    unset($users['msg']);
}

if (empty($users)) {
    echo "<p>" . lang('No users found', 'Keine Nutzer:innen gefunden') . "</p>";
    return;
}

$removed = $osiris->persons->find(
    ['username' => ['$nin' => array_keys($users)], 'is_active' => ['$in' => [1, true, '1']]],
    ['projection' => ['username' => 1, 'is_active' => 1, 'displayname' => 1]]
);
$removed = array_column(iterator_to_array($removed), 'displayname', 'username');

$actions = [
    'blacklisted' => [],
    'inactivate' => [],
    'reactivate' => [],
    'add' => [],
    'delete' => $removed ?? [],
    'unchanged' => []
];
foreach ($users as $username => $active) {
    $exists = false;
    $dbactive = false;

    // first: check if user is in database
    $USER = $DB->getPerson($username);
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
        <details class="collapse-panel">
            <summary class="collapse-header">
                <?= lang('Unchanged users', 'Unveränderte Nutzer') ?>
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
