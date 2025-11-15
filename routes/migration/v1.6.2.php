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