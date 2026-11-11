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
            ? $projectForms->upgradeFields(DB::doc2Arr($phase['fields']))
            : $projectForms->fields($phase['id'], $phase['modules'] ?? []);
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
    'Project phase forms prepared',
    'Formulare für Projektphasen vorbereitet',
    'The new fields configuration preserves sections, field order and required fields. German companion fields use localized modules; German translations become optional. Project forms always include persons-project; institute-role and project-date replace their legacy inputs. Legacy project modules remain unchanged.',
    'Die neue fields-Konfiguration übernimmt Abschnitte, Feldreihenfolge und Pflichtfelder. Deutsche Zusatzfelder werden zu lokalisierten Modulen; deutsche Übersetzungen werden optional. Projektformulare enthalten immer persons-project; institute-role und project-date ersetzen die bisherigen Eingaben. Die bisherigen Projekt-modules bleiben unverändert.',
    $created,
    'phase forms created',
    'Phasenformulare angelegt'
);
echo '<p>' . e(lang('Existing phase forms preserved', 'Bestehende Phasenformulare beibehalten')) . ': ' . $existing . '</p>';

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
echo '<p>' . e(lang('Activity university fields migrated', 'University-Felder in Aktivitäten umgestellt')) . ': ' . $activitiesUpdated . '</p>';

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
    'Project content localized', 'Projektinhalte lokalisiert',
    'Names, titles and abstracts now store English and German translations together. Existing translations are preserved.',
    'Namen, Titel und Zusammenfassungen speichern Englisch und Deutsch jetzt gemeinsam. Vorhandene Übersetzungen bleiben erhalten.',
    $contentUpdated, 'project and proposal records updated', 'Projekt- und Antragsdatensätze aktualisiert'
);
