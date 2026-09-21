<?php

/**
 * Migration script for OSIRIS v1.6.2
 * 
 * Migrates infrastructure statistics to a new collection format.
 */

echo lang('admin.i_will_now_migrate_the_infrastructure_statistics_to_a_new_more_flexible_for') . "<br>";

$osiris->infrastructureStats->deleteMany([]);
$infrastructures = $osiris->infrastructures->find([], ['statistics' => 1])->toArray();
// save in a seperate collection
foreach ($infrastructures as $infrastructure) {
    $stats = DB::doc2Arr($infrastructure['statistics'] ?? []);
    foreach ($stats as $stat) {
        $year = $stat['year'] ?? null;
        unset($stat['year']);
        foreach ($stat as $key => $value) {
            if (empty($value) || !is_numeric($value) || $value == 0) continue;
            $entry = [
                'infrastructure' => $infrastructure['id'],
                'year' => $year,
                'field' => $key,
                'value' => intval($value),
            ];
            $osiris->infrastructureStats->insertOne($entry);
        }
    }
}

if (strtoupper(USER_MANAGEMENT) == 'LDAP') {
    $roles = DB::doc2Arr($Settings->get('roles', []));
    if (!in_array('guest', $roles)) {
        $roles[] = 'guest';
        $osiris->adminGeneral->updateOne(
            ['key' => 'roles'],
            ['$set' => ['value' => $roles]]
        );
        echo lang('admin.i_have_added_guest_role_to_your_settings_this_role_will_be_automatically_as') . "<br>";
    } else {
        echo lang('admin.a_guest_role_already_exists_in_your_settings_it_will_be_automatically_assig') . "<br>";
    }
}
