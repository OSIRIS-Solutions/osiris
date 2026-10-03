<?php

/**
 * Convert legacy bilingual content fields to language maps.
 *
 * The migration is idempotent: existing translations in the new structure win,
 * while legacy *_de values only fill an empty German translation.
 */

$toLanguageMap = static function ($value, $legacyGerman = null): array {
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value);
    }

    if (is_array($value)) {
        $translations = $value;
    } else {
        $translations = [];
        if (is_string($value) || is_numeric($value)) {
            $translations['en'] = (string) $value;
        }
    }

    if (
        (is_string($legacyGerman) || is_numeric($legacyGerman)) &&
        trim((string) $legacyGerman) !== '' &&
        trim((string) ($translations['de'] ?? '')) === ''
    ) {
        $translations['de'] = (string) $legacyGerman;
    }

    return $translations;
};

$updated = 0;
$unchanged = 0;

foreach ($osiris->groups->find() as $document) {
    $group = DB::doc2Arr($document);
    $set = [];
    $unset = [];

    foreach (['name', 'description'] as $field) {
        $legacyField = $field . '_de';
        if (!array_key_exists($field, $group) && !array_key_exists($legacyField, $group)) continue;

        $localizedValue = $toLanguageMap($group[$field] ?? null, $group[$legacyField] ?? null);
        if (($group[$field] ?? null) !== $localizedValue) {
            $set[$field] = $localizedValue;
        }
        if (array_key_exists($legacyField, $group)) {
            $unset[$legacyField] = '';
        }
    }

    if (isset($group['research'])) {
        $research = DB::doc2Arr($group['research']);
        $migratedResearch = [];
        foreach ($research as $item) {
            $item = DB::doc2Arr($item);
            foreach (['title', 'subtitle', 'info'] as $field) {
                $legacyField = $field . '_de';
                if (!array_key_exists($field, $item) && !array_key_exists($legacyField, $item)) continue;
                $item[$field] = $toLanguageMap($item[$field] ?? null, $item[$legacyField] ?? null);
                unset($item[$legacyField]);
            }
            $migratedResearch[] = $item;
        }
        if ($research !== $migratedResearch) {
            $set['research'] = $migratedResearch;
        }
    }

    if (empty($set) && empty($unset)) {
        $unchanged++;
        continue;
    }

    $update = [];
    if (!empty($set)) $update['$set'] = $set;
    if (!empty($unset)) $update['$unset'] = $unset;
    $osiris->groups->updateOne(['_id' => $group['_id']], $update);
    $updated++;
}

$osiris->groups->createIndex(['name.en' => 1]);
$osiris->groups->createIndex(['name.de' => 1]);

migrationCard(
    'Group localization migrated',
    'Gruppenlokalisierung migriert',
    "Group names, descriptions and research texts now use language maps. $unchanged documents were already up to date.",
    "Gruppennamen, Beschreibungen und Forschungstexte verwenden jetzt Sprachobjekte. $unchanged Dokumente waren bereits aktuell.",
    $updated
);

$updated = 0;
$unchanged = 0;
$researchLengthMismatches = 0;

foreach ($osiris->persons->find() as $document) {
    $person = DB::doc2Arr($document);
    $set = [];
    $unset = [];

    foreach (['research_profile', 'biography', 'education'] as $field) {
        $legacyField = $field . '_de';
        if (!array_key_exists($field, $person) && !array_key_exists($legacyField, $person)) continue;

        $localizedValue = $toLanguageMap($person[$field] ?? null, $person[$legacyField] ?? null);
        if (($person[$field] ?? null) !== $localizedValue) {
            $set[$field] = $localizedValue;
        }
        if (array_key_exists($legacyField, $person)) {
            $unset[$legacyField] = '';
        }
    }

    if (array_key_exists('research', $person) || array_key_exists('research_de', $person)) {
        $research = DB::doc2Arr($person['research'] ?? []);
        $researchGerman = DB::doc2Arr($person['research_de'] ?? []);
        if (!is_array($research)) $research = [];
        if (!is_array($researchGerman)) $researchGerman = [];

        if (array_key_exists('research_de', $person) && count($research) !== count($researchGerman)) {
            $researchLengthMismatches++;
        }

        $localizedResearch = [];
        $researchCount = max(count($research), count($researchGerman));
        for ($i = 0; $i < $researchCount; $i++) {
            $translations = $toLanguageMap($research[$i] ?? null, $researchGerman[$i] ?? null);
            $translations = array_filter(
                $translations,
                fn($translation) => (is_string($translation) || is_numeric($translation))
                    && trim((string) $translation) !== ''
            );
            if (!empty($translations)) $localizedResearch[] = $translations;
        }

        if ($research !== $localizedResearch) {
            $set['research'] = $localizedResearch;
        }
        if (array_key_exists('research_de', $person)) {
            $unset['research_de'] = '';
        }
    }

    if (empty($set) && empty($unset)) {
        $unchanged++;
        continue;
    }

    $update = [];
    if (!empty($set)) $update['$set'] = $set;
    if (!empty($unset)) $update['$unset'] = $unset;
    $osiris->persons->updateOne(['_id' => $person['_id']], $update);
    $updated++;
}

migrationCard(
    'Person profile localization migrated',
    'Lokalisierung der Personenprofile migriert',
    "Research interests, research profiles, biographies and education texts now use language maps. $unchanged documents were already up to date."
        . ($researchLengthMismatches > 0 ? " $researchLengthMismatches records had different EN/DE research list lengths; all existing entries were preserved by their position." : ''),
    "Forschungsinteressen, Forschungsprofile, Biografien und Ausbildungstexte verwenden jetzt Sprachobjekte. $unchanged Dokumente waren bereits aktuell."
        . ($researchLengthMismatches > 0 ? " Bei $researchLengthMismatches Datensätzen waren die EN/DE-Listen unterschiedlich lang; alle vorhandenen Einträge wurden anhand ihrer Position erhalten." : ''),
    $updated
);
