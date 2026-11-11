<?php

/** Transitional migration: add phase fields while preserving legacy modules. */
require_once BASEPATH . '/php/Project.php';
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
        if (array_key_exists('fields', $phase)) {
            $existing++;
            continue;
        }
        $path = 'phases.' . $index . '.fields';
        $fields = $projectForms->fields($phase['id'], $phase['modules'] ?? []);
        $result = $osiris->adminProjects->updateOne(
            ['_id' => $projectType['_id'], $path => ['$exists' => false]],
            ['$set' => [$path => $fields]]
        );
        $created += $result->getModifiedCount();
    }
}

migrationCard(
    'Project phase forms prepared',
    'Formulare für Projektphasen vorbereitet',
    'The new fields configuration preserves sections, field order and required fields. German companion fields use localized modules; German translations become optional. Legacy modules and project/proposal data remain unchanged.',
    'Die neue fields-Konfiguration übernimmt Abschnitte, Feldreihenfolge und Pflichtfelder. Deutsche Zusatzfelder werden zu lokalisierten Modulen; deutsche Übersetzungen werden optional. Die bisherigen modules sowie Projekt- und Antragsdaten bleiben unverändert.',
    $created,
    'phase forms created',
    'Phasenformulare angelegt'
);
echo '<p>' . e(lang('Existing phase forms preserved', 'Bestehende Phasenformulare beibehalten')) . ': ' . $existing . '</p>';
