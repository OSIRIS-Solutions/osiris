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
    echo "<p>" . lang(
        "No teaching modules found. No changes made.",
        "Keine Lehrmodule gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>" . lang(
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
    echo "<p>" . lang(
        "No persons found with created date in d.m.Y format. No changes made.",
        "Keine Personen mit Erstellungsdatum im Format d.m.Y gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>" . lang(
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
    echo "<p>" . lang(
        "No activities found with authors field to migrate. No changes made.",
        "Keine Aktivitäten mit Autoren-Feld zum Migrieren gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>" . lang(
        "Migrated authors field to supervisors field for " . $updated . " out of " . $N_ . " activities.",
        "Autoren-Feld für " . $updated . " von " . $N_ . " Aktivitäten in Betreuende-Feld migriert."
    ) . "</p>";
}

// append public_email, public_other_activities, public_teaching to person-data settings if not present
$Settings = new Settings();

$data_fields = $Settings->get('person-data');
if (empty($data_fields)) {
    // do nothing because default settings will be used
} else {
    $data_fields = DB::doc2Arr($data_fields);
    $fieldsToAdd = ['public_email', 'public_other_activities', 'public_teaching'];
    $updated = 0;
    foreach ($fieldsToAdd as $field) {
        if (!in_array($field, $data_fields ?? [])) {
            $data_fields[] = $field;
            $updated++;
        }
    }
    if ($updated > 0) {
        $Settings->set('person-data', $data_fields);
        echo "<p>" . lang(
            "Added " . $updated . " fields to person-data settings.",
            "Es wurden " . $updated . " Felder zu den Personendaten-Einstellungen hinzugefügt."
        ) . "</p>";
    }
}



// $collabs = $osiris->projects->find(['collaborators' => ['$exists' => true, '$ne' => []]])->toArray();
// $N_ = count($collabs);
// echo "<p>". lang(
//     "Checking " . $N_ . " projects for missing organization IDs in collaborators.",
//     "Überprüfung von " . $N_ . " Projekten auf fehlende Organisations-IDs bei den Mitarbeitenden."
// ) . "</p>";
// foreach ($collabs as $project) {
//     $collaborators = $project['collaborators'] ?? [];
//     $updated = false;
//     foreach ($collaborators as &$collab) {
//         if (empty($collab['organization'] ?? null)){
//             echo ("Project " . $project['name'] . " collaborator missing organization ID");
//             dump($collab);
//         }
//     }
//     // if ($updated) {
//     //     $osiris->projects->updateOne(
//     //         ['_id' => $project['_id']],
//     //         ['$set' => [
//     //             'collaborators' => $collaborators,
//     //         ]]
//     //     );
//     // }
// }


$projects = $osiris->proposals->find(['grant_years' => ['$exists' => true]])->toArray();
$N_ = count($projects);
$updated = 0;
foreach ($projects as $project) {
    $grant_years = DB::doc2Arr($project['grant_years'] ?? array());
    if (is_array($grant_years) && isset($grant_years[0]['year'])) {
        // already migrated
        continue;
    }
    $new_grant_years = array();
    foreach ($grant_years as $year => $amount) {
        $new_grant_years[] = [
            'year' => $year,
            'planned' => $amount,
            'spent' => $amount
        ];
    }
    $osiris->proposals->updateOne(
        ['_id' => $project['_id']],
        ['$set' => [
            'grant_years' => $new_grant_years,
        ]]
    );
    $updated++;
}
if ($N_ == 0) {
    echo "<p>" . lang(
        "No proposals found with grant_years field. No changes made.",
        "Keine Anträge mit Drittmittel-Einnahmen gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>" . lang(
        "Transformed grant_years field for " . $updated . " out of " . $N_ . " proposals.",
        "Drittmittel-Einnahmen für " . $updated . " von " . $N_ . " Anträgen umgewandelt."
    ) . "</p>";
}

// give admin rights to see financial statistics to role 'admin'
$osiris->adminRights->insertOne([
    "role" => "admin",
    "right" => "proposals.finance",
    "value" => true
]);
echo "<p>" . lang(
    "Granted 'proposals.finance' right to 'admin' role.",
    "Recht 'proposals.finance' der Rolle 'admin' gewährt."
) . "</p>";
