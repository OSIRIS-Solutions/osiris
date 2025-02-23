<?php
echo "<h1>Migrate to Version 1.3.X</h1>";

$json = file_get_contents(BASEPATH . "/settings.default.json");
$settings = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
// get custom settings
$file_name = BASEPATH . "/settings.json";
if (file_exists($file_name)) {
    $json = file_get_contents($file_name);
    $set = json_decode($json, true, 512, JSON_NUMERIC_CHECK);
    // replace existing keys with new ones
    $settings = array_merge($settings, $set);
}
// dump($settings, true);


echo "<p>Update general settings</p>";
$osiris->adminGeneral->deleteMany([]);

$osiris->adminGeneral->insertOne([
    'key' => 'affiliation',
    'value' => $settings['affiliation']
]);

$osiris->adminGeneral->insertOne([
    'key' => 'startyear',
    'value' => $settings['general']['startyear']
]);
$roles = $settings['roles']['roles'];
$osiris->adminGeneral->insertOne([
    'key' => 'roles',
    'value' => $roles
]);


echo "<p>Update Features</p>";
$osiris->adminFeatures->deleteMany([]);
foreach (["coins", "achievements", "user-metrics"] as $key) {
    $osiris->adminFeatures->insertOne([
        'feature' => $key,
        'enabled' => boolval(!$settings['general']['disable-' . $key])
    ]);
}


echo "<p>Update Rights and Roles</p>";


$osiris->adminRights->deleteMany([]);
$rights = $settings['roles']['rights'];
foreach ($rights as $right => $perm) {
    foreach ($roles as $n => $role) {
        $r = [
            'role' => $role,
            'right' => $right,
            'value' => $perm[$n]
        ];
        $osiris->adminRights->insertOne($r);
    }
}

echo "<p>Update Activity schema</p>";
$osiris->adminCategories->deleteMany([]);
$osiris->adminTypes->deleteMany([]);
foreach ($settings['activities'] as $type) {
    $t = $type['id'];
    $cat = [
        "id" => $type['id'],
        "icon" => $type['icon'],
        "color" => $type['color'],
        "name" => $type['name'],
        "name_de" => $type['name_de'],
        // "children" => $type['subtypes']
    ];
    $osiris->adminCategories->insertOne($cat);
    foreach ($type['subtypes'] as $s => $subtype) {
        $subtype['parent'] = $t;
        // dump($subtype, true);
        $osiris->adminTypes->insertOne($subtype);
    }
}

// set up indices
$indexNames = $osiris->adminCategories->createIndexes([
    ['key' => ['id' => 1], 'unique' => true],
]);


$osiris->groups->deleteMany([]);

// add institute as root level
$affiliation = $settings['affiliation'];
$dept = [
    'id' => $affiliation['id'],
    'color' => '#000000',
    'name' => $affiliation['name'],
    'parent' => null,
    'level' => 0,
    'unit' => 'Institute',
];
$osiris->groups->insertOne($dept);

// add departments as children
$depts = $settings['departments'];
foreach ($depts as $dept) {
    if ($dept['id'] == 'BIDB') $dept['id'] = 'BID';
    $dept['parent'] = $affiliation['id'];
    $dept['level'] = 1;
    $dept['unit'] = 'Department';
    $osiris->groups->insertOne($dept);
}

// migrate person affiliation
$persons = $osiris->persons->find([])->toArray();
foreach ($persons as $person) {
    // dump($person, true);
    // $dept = [$affiliation['id']];
    $depts = [];
    if (isset($person['dept']) && !empty($person['dept'])) {
        if ($person['dept'] === 'BIDB') $person['dept'] = 'BID';
        $depts[] = $person['dept'];
    }
    dump($depts);
    // die;
    $updated = $osiris->persons->updateOne(
        ['_id' => $person['_id']],
        ['$set' => ['depts' => $depts]]
    );
}
