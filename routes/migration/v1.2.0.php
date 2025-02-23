<?php
echo "<h1>Migrate to Version 1.2.X</h1>";
$osiris->teachings->drop();
$osiris->miscs->drop();
$osiris->posters->drop();
$osiris->publications->drop();
$osiris->lectures->drop();
$osiris->reviews->drop();
$osiris->lecture->drop();

$users = $osiris->users->find([])->toArray();

$person_keys = [
    "first",
    "last",
    "academic_title",
    "displayname",
    "formalname",
    "names",
    "first_abbr",
    "department",
    "unit",
    "telephone",
    "mail",
    "dept",
    "orcid",
    "gender",
    "google_scholar",
    "researchgate",
    "twitter",
    "webpage",
    "expertise",
    "updated",
    "updated_by",
];

$account_keys = [
    "is_active",
    "maintenance",
    "hide_achievements",
    "hide_coins",
    "display_activities",
    "lastlogin",
    "created",
    "approved",
];

$osiris->persons->deleteMany([]);
$osiris->accounts->deleteMany([]);
$osiris->achieved->deleteMany([]);

foreach ($users as $user) {
    $user = iterator_to_array($user);
    $username = strtolower($user['username']);

    $person = ["username" => $username];
    foreach ($person_keys as $key) {
        if (!array_key_exists($key, $user)) continue;
        $person[$key] = $user[$key];
        unset($user[$key]);
    }
    $osiris->persons->insertOne($person);

    $account = ["username" => $username];
    foreach ($account_keys as $key) {
        if (!array_key_exists($key, $user)) continue;
        if ($key)
            $account[$key] = $user[$key];
        unset($user[$key]);
    }
    $roles = [];
    foreach (['editor', 'admin', 'leader', 'controlling', 'scientist'] as $role) {
        if ($user['is_' . $role] ?? false) {
            if ($role == 'controlling') $role = 'editor';
            $roles[] = $role;
        }
    }
    $account['roles'] = $roles;

    $osiris->accounts->insertOne($account);

    if (isset($user['achievements'])) {
        foreach ($user['achievements'] as $ac) {
            $ac['username'] = $username;
            $osiris->achieved->insertOne($ac);
        }
        unset($user['achievements']);
    }
}
echo "Migrated " . count($users) . " users into a new format.<br> Migration successful. You might close this window now.";
