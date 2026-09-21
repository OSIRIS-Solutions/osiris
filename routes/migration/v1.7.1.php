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
    echo "<p>" . lang('admin.no_teaching_modules_found_no_changes_made') . "</p>";
} else {
    echo "<p>" . lang('admin.transformed_module_numbers_into_strings_for_updated_out_of_n_teaching_modul', replace: ['N' => $N_, 'updated' => $updated]) . "</p>";
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
    echo "<p>" . lang('admin.no_persons_found_with_created_date_in_d_m_y_format_no_changes_made') . "</p>";
} else {
    echo "<p>" . lang('admin.transformed_created_dates_from_d_m_y_to_y_m_d_for_updated_out_of_n_persons', replace: ['N' => $N_, 'updated' => $updated]) . "</p>";
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
    echo "<p>" . lang('admin.no_activities_found_with_authors_field_to_migrate_no_changes_made') . "</p>";
} else {
    echo "<p>" . lang('admin.migrated_authors_field_to_supervisors_field_for_updated_out_of_n_activities', replace: ['N' => $N_, 'updated' => $updated]) . "</p>";
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
        echo "<p>" . lang('admin.added_updated_fields_to_person_data_settings', replace: ['updated' => $updated]) . "</p>";
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
