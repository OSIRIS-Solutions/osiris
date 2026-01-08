<?php

/**
 * Migration script for OSIRIS v1.7.1
 * Transforms teaching module numbers into strings
 */

$teaching = $osiris->teaching->find()->toArray();
$N_ = count($teaching);
$updated = 0;
foreach ($teaching as $module) {
    $moduleNumber = strval($module['module'] ?? '');
    if ($moduleNumber !== ($module['module'] ?? '')) {
        $updated++;
        $osiris->teaching->updateOne(
            ['_id' => $module['_id']],
            ['$set' => [
                'module' => $moduleNumber,
            ]]
        );
    }
}

if ($N_ == 0) {
    echo "<p>". lang(
        "No teaching modules found. No changes made.",
        "Keine Lehrmodule gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>". lang(
        "Transformed module numbers into strings for " . $updated . " out of " . $N_ . " teaching modules.",
        "Modulnummern für " . $updated . " von " . $N_ . " Lehrmodulen in Zeichenketten umgewandelt."
    ) . "</p>";
}


// try to fix created date for people from d.m.Y to Y-m-d
$persons = $osiris->persons->find(['created' => new MongoDB\BSON\Regex('^\d{1,2}\.\d{1,2}\.\d{4}$')])->toArray();
$N_ = count($persons);
$updated = 0;
foreach ($persons as $person) {
    $created = DateTime::createFromFormat('d.m.Y', $person['created']);
    if ($created !== false) {
        $updated++;
        $osiris->persons->updateOne(
            ['_id' => $person['_id']],
            ['$set' => [
                'created' => $created->format('Y-m-d'),
            ]]
        );
    }
}

if ($N_ == 0) {
    echo "<p>". lang(
        "No persons found with created date in d.m.Y format. No changes made.",
        "Keine Personen mit Erstellungsdatum im Format d.m.Y gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>". lang(
        "Transformed created dates from d.m.Y to Y-m-d for " . $updated . " out of " . $N_ . " persons.",
        "Erstellungsdaten von d.m.Y zu Y-m-d für " . $updated . " von " . $N_ . " Personen umgewandelt."
    ) . "</p>";
}


// migrate authors field to supervisors field in activities for activities with the supervisor module
$types = $osiris->adminTypes->find(['modules' => ['$in' => ['supervisor-thesis', 'supervisor-thesis*', 'supervisor', 'supervisor*']]], ['projection' => ['id' => 1]])->toArray();
$types = DB::doc2Arr($types);
$typeIds = array_column($types, 'id');

$activities = $osiris->activities->find(['subtype' => ['$in' => $typeIds], 'authors' => ['$exists' => true]])->toArray();
$N_ = count($activities);
$updated = 0;
foreach ($activities as $activity) {
    $authors = $activity['authors'] ?? [];
    if (!empty($authors)) {
        $updated++;
        $osiris->activities->updateOne(
            ['_id' => $activity['_id']],
            ['$set' => [
                'supervisors' => $authors,
            ], '$unset' => [
                'authors' => "",
            ]]
        );
    }
}

if ($N_ == 0) {
    echo "<p>". lang(
        "No activities found with authors field to migrate. No changes made.",
        "Keine Aktivitäten mit Autoren-Feld zum Migrieren gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>". lang(
        "Migrated authors field to supervisors field for " . $updated . " out of " . $N_ . " activities.",
        "Autoren-Feld für " . $updated . " von " . $N_ . " Aktivitäten in Betreuende-Feld migriert."
    ) . "</p>";
}

// append public_email, public_other_activities, public_teaching to person-data settings if not present
$Settings = new Settings();
$personData = DB::doc2Arr($Settings->get('person-data'));
$fieldsToAdd = ['public_email', 'public_other_activities', 'public_teaching'];
$updated = 0;
foreach ($fieldsToAdd as $field) {
    if (!in_array($field, $personData ?? [])) {
        $personData[] = $field;
        $updated++;
    }
}
if ($updated > 0) {
    $Settings->set('person-data', $personData);
    echo "<p>". lang(
        "Added " . $updated . " fields to person-data settings.",
        "Es wurden " . $updated . " Felder zu den Personendaten-Einstellungen hinzugefügt."
    ) . "</p>";
} 