<?php

/**
 * Migration script for OSIRIS v1.6.2
 * 
 * Migrates infrastructure statistics to a new collection format.
 */

echo lang('I will now migrate the infrastructure statistics to a new more flexible format.', 'Ich werde nun die Infrastrukturdaten in ein neues, flexibleres Format migrieren.') . "<br>";

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
        echo lang('I have added guest role to your settings. This role will be automatically assigned to the new guest accounts. It does not have any special permissions by default.', 'Ich habe die Gast-Rolle zu deiner Konfiguration hinzugefügt. Diese Rolle wird automatisch den neuen Gastkonten zugewiesen. Sie hat standardmäßig keine besonderen Berechtigungen.') . "<br>";
    } else {
        echo lang('A guest role already exists in your settings. It will be automatically assigned to the new guest accounts.', 'Eine Gast-Rolle existiert bereits in deiner Konfiguration. Sie wird automatisch den neuen Gastkonten zugewiesen.') . "<br>";
    }
}
