<?php
echo "<p>Migrating persons into new version.</p>";
$migrated = 0;

$accounts = $osiris->accounts->find([])->toArray();
foreach ($accounts as $account) {
    $user = $account['username'];
    // check if user exists
    $person = $osiris->persons->findOne(['username' => $user]);
    if (empty($person)) {
        echo $user;
    } else {
        unset($account['_id']);
        $updated = $osiris->persons->updateOne(
            ['username' => $user],
            ['$set' => $account]
        );
        $migrated += $updated->getModifiedCount();
    }
}

echo "<p>Migrated $migrated users.</p>";
