<?php
// Migrate to 1.4.0

echo "<p>Migrating account settings if necessary</p>";

// empty accounts 
// $osiris->accounts->deleteMany([]);
// use password hashes to encrypt passwords
$cursor = $osiris->persons->find(['password' => ['$exists' => true]]);
foreach ($cursor as $doc) {
    $hash = password_hash($doc['password'], PASSWORD_DEFAULT);
    // remove existing password
    $osiris->accounts->deleteOne(['username' => $doc['username']]);
    // move to a new collection
    $osiris->accounts->insertOne([
        'username' => $doc['username'],
        'password' => $hash
    ]);

    // remove password from persons
    $osiris->persons->updateOne(
        ['_id' => $doc['_id']],
        ['$unset' => ['password' => '']]
    );
}

$cursor = $osiris->projects->find(['start_date' => ['$exists' => false]]);
foreach ($cursor as $doc) {
    $osiris->projects->updateOne(
        ['_id' => $doc['_id']],
        ['$set' => ['start_date' => format_date($doc['start'] ?? '', 'Y-m-d'), 'end_date' => format_date($doc['end'] ?? '', 'Y-m-d')]]
    );
}
echo "<p>Migrated project date time for better search.</p>";

$cursor = $osiris->activities->find(['start_date' => ['$exists' => false]]);
foreach ($cursor as $doc) {
    $start = valueFromDateArray($doc['start'] ?? $doc);
    if (array_key_exists('end', DB::doc2Arr($doc)) && is_null($doc['end'])) {
        $end = null;
    } else {
        $end = valueFromDateArray($doc['end'] ?? $doc['start'] ?? $doc);
    }
    $osiris->activities->updateOne(
        ['_id' => $doc['_id']],
        ['$set' => ['start_date' => $start, 'end_date' => $end]]
    );
}
echo "<p>Migrated activity date time for better search.</p>";

// migrate person socials
$cursor = $osiris->persons->find(['socials' => ['$exists' => false]]);
$available = [
    'twitter' => 'https://twitter.com/',
    'webpage' => '',
    'linkedin' => 'https://www.linkedin.com/in/',
    'researchgate' => 'https://www.researchgate.net/profile/'
];
foreach ($cursor as $doc) {
    $socials = [];
    foreach ($available as $key => $url) {
        if ($key == 'twitter') {
            $key = 'X';
        }
        if (isset($doc[$key])) {
            $socials[$key] = $url . $doc[$key];
        }
    }
    if (empty($socials)) continue;
    $osiris->persons->updateOne(
        ['_id' => $doc['_id']],
        ['$set' => ['socials' => $socials]]
    );
    $osiris->persons->updateOne(
        ['_id' => $doc['_id']],
        ['$unset' => ['twitter' => '', 'webpage' => '', 'linkedin' => '', 'researchgate' => '']]
    );
}

echo "<p>Migrated socials.</p>";

$cursor = $osiris->persons->find(['depts'=> ['$exists' => true]]);
foreach ($cursor as $person) {
    $depts = DB::doc2Arr($person['depts'] ?? []);
    if (empty($depts)) continue;

    // make sure that only the lowest level of the hierarchy is in the list
    foreach ($depts as $dept) {
        // check if parent is already in the list
        $parents = $Groups->getParents($dept, true);
        // remove last element
        array_pop($parents);
        foreach ($parents as $parent) {
            if (in_array($parent, $depts)) {
                $key = array_search($parent, $depts);
                unset($depts[$key]);
            }
        }
    }
    $depts = array_values($depts);

    $units = [];
    // find the unit with the highest level
    foreach ($depts as $dept) {
        // check if parent is already in the list
        $parents = $Groups->getParents($dept, true);
        $parents = array_column($parents, 'id');

        $units[] = [
            'id' => uniqid(),
            'unit' => $dept,
            'start' => null,
            'end' => null,
            'scientific' => true
        ];
    }

    $osiris->persons->updateOne(
        ['_id' => $person['_id']],
        ['$set' => ['units' => $units]]
    );
}

// Precalculate activities: find all activities without units and calculate the units based on the authors    
$activities = $osiris->activities->find(['authors' => ['$exists' => true]]);


foreach ($activities as $doc) {
    // dump($doc['rendered']['print'], true);
    $units = [];
    $startdate = strtotime($doc['start_date']);

    $authors = $doc['authors'] ?? [];

    foreach ($authors as $i => $author) {
        if (!($author['aoi'] ?? false) || !isset($author['user'])) continue;
        $user = $author['user'];
        $person = $DB->getPerson($user);
        if (isset($person['units']) && !empty($person['units'])) {
            $u = DB::doc2Arr($person['units']);
            // filter units that have been active at the time of activity
            $u = array_filter($u, function ($unit) use ($startdate) {
                if (!$unit['scientific']) return false; // we are only interested in scientific units
                if (empty($unit['start'])) return true; // we have basically no idea when this unit was active
                return strtotime($unit['start']) <= $startdate && (empty($unit['end']) || strtotime($unit['end']) >= $startdate);
            });
            $u = array_column($u, 'unit');
            $authors[$i]['units'] = $u;
            $units = array_merge($units, $u);
        }
    }
    $units = array_unique($units);
    foreach ($units as $unit) {
        $units = array_merge($units, $Groups->getParents($unit, true));
    }
    $units = array_unique($units);
    $osiris->activities->updateOne(
        ['_id' => $doc['_id']],
        ['$set' => ['authors' => $authors, 'units' => $units]]
    );
}
