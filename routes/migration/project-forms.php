<?php

/** Transitional migration: add phase fields while preserving legacy modules. */
require_once BASEPATH . '/php/Project.php';

require_once BASEPATH . '/php/FormBuilder.php';
require_once BASEPATH . '/php/Language.php';
require_once BASEPATH . '/php/ProjectFormMigration.php';

$Project = new Project();
$migrationLanguage = new Language(BASEPATH . '/lang');
$migrationLanguage->useDatabase($osiris);
$projectForms = new ProjectFormMigration($Project->FIELDS, $migrationLanguage);
$created = 0;
$existing = 0;

foreach ($osiris->adminProjects->find() as $projectType) {
    foreach ($projectType['phases'] ?? [] as $index => $phase) {
        $phase = DB::doc2Arr($phase);
        $hasFields = array_key_exists('fields', $phase);
        $fields = $hasFields
            ? $projectForms->upgradeFields(DB::doc2Arr($phase['fields']), $phase['id'])
            : $projectForms->fields($phase['id'], $phase['modules'] ?? []);
        // Apply the new default during migration, including earlier migrated forms.
        $comments = array_filter($fields, fn($field) => ($field['type'] ?? 'field') === 'field' && ($field['id'] ?? '') === 'comment');
        $fields = array_values(array_filter($fields, fn($field) => ($field['type'] ?? 'field') !== 'field' || ($field['id'] ?? '') !== 'comment'));
        $fields = array_merge($fields, array_values($comments));
        if ($hasFields && json_encode($fields) === json_encode($phase['fields'])) {
            $existing++;
            continue;
        }
        $path = 'phases.' . $index . '.fields';
        $result = $osiris->adminProjects->updateOne(
            ['_id' => $projectType['_id']],
            ['$set' => [$path => $fields]]
        );
        $created += $result->getModifiedCount();
    }
}

migrationCard(
    t('admin.project_phase_forms_prepared'),
    t('admin.the_new_fields_configuration_preserves_sections_field_order_and_re'),
    $created,
    t('admin.phase_forms_created')
);
echo '<p>' . e(t('admin.existing_phase_forms_preserved')) . ': ' . $existing . '</p>';

$activitiesUpdated = 0;
foreach ($osiris->adminTypes->find() as $activityType) {
    $modules = DB::doc2Arr($activityType['modules'] ?? []);
    $fields = DB::doc2Arr($activityType['fields'] ?? []);
    $hasUniversity = in_array('university', $modules, true) || in_array('university*', $modules, true);
    foreach ($fields as $field) {
        if (($field['type'] ?? 'field') === 'field' && ($field['id'] ?? '') === 'university') $hasUniversity = true;
    }
    if (!$hasUniversity) continue;
    $fields = $projectForms->activityFields($modules, $fields);
    $modules = array_map(fn($id) => match ($id) {
        'university' => 'publisher', 'university*' => 'publisher*', default => $id,
    }, $modules);
    $osiris->adminTypes->updateOne(['_id' => $activityType['_id']], ['$set' => ['modules' => $modules, 'fields' => $fields]]);
    $activitiesUpdated++;
}
echo '<p>' . e(t('admin.activity_university_fields_migrated')) . ': ' . $activitiesUpdated . '</p>';

// Migrate document content alongside the form configuration.
$contentUpdated = 0;
foreach (['projects', 'proposals'] as $collection) {
    foreach ($osiris->$collection->find() as $document) {
        $original = DB::doc2Arr($document);
        $set = [];
        $unset = [];
        foreach (['name', 'title', 'abstract'] as $field) {
            if (!array_key_exists($field, $original) && !array_key_exists($field . '_de', $original)) continue;
            $value = $original[$field] ?? '';
            if ($value instanceof Traversable) $value = iterator_to_array($value);
            $translations = is_array($value) ? $value : ['en' => (string) $value];
            if (empty($translations['de']) && !empty($original[$field . '_de'])) $translations['de'] = $original[$field . '_de'];
            if ($value !== $translations) $set[$field] = $translations;
            if (array_key_exists($field . '_de', $original)) $unset[$field . '_de'] = '';
        }
        if (empty($set) && empty($unset)) continue;
        $update = [];
        if ($set) $update['$set'] = $set;
        if ($unset) $update['$unset'] = $unset;
        $osiris->$collection->updateOne(['_id' => $original['_id']], $update);
        $contentUpdated++;
    }
}
migrationCard(
    t('admin.project_content_localized'),
    t('admin.names_titles_and_abstracts_now_store_english_and_german_translatio'),
    $contentUpdated,
    t('admin.project_and_proposal_records_updated')
);
