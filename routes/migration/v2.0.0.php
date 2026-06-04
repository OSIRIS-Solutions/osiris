<?php
$coll = $osiris->persons;

$bulkSize = 500;
$ops = [];
$count = 0;
$updated = 0;

$cursor = $coll->find(
    [],
    [
        'projection' => [
            'names' => 1,
            'last' => 1,
            'first' => 1,
            'username' => 1,
            'orcid' => 1,
            'mail' => 1,
            'search_text' => 1
        ]
    ]
);

foreach ($cursor as $doc) {
    $arr = (array)$doc;

    $new = build_person_search_text($arr);

    // Skip empty strings
    if ($new === '') continue;

    // Skip if already up-to-date (optional)
    $old = $arr['search_text'] ?? '';
    if ($old === $new) continue;

    $ops[] = [
        'updateOne' => [
            ['_id' => $doc->_id],
            ['$set' => ['search_text' => $new]]
        ]
    ];
    $count++;

    if ($count % $bulkSize === 0) {
        $res = $coll->bulkWrite($ops);
        $updated += $res->getModifiedCount();
        $ops = [];
    }
}

// Flush remaining ops
if (!empty($ops)) {
    $res = $coll->bulkWrite($ops);
    $updated += $res->getModifiedCount();
}

migrationCard(
    'Person search index updated',
    'Personen-Suchindex aktualisiert',
    'Search text fields for persons were rebuilt where necessary. This improves person search and command palette results.',
    'Suchtexte für Personen wurden bei Bedarf neu aufgebaut. Das verbessert die Personensuche und die Treffer in der Command Palette.',
    $updated,
    'person records updated',
    'Personendatensätze aktualisiert'
);


// migrate cv dates
$users = $osiris->persons->find(['cv' => ['$exists' => true]])->toArray();
$cvUpdated = 0;
foreach ($users as $user) {
    $cv = $user['cv'];
    if (empty($cv)) continue;
    foreach ($cv as $i => $con) {
        $con = json_encode($con);
        $con = json_decode($con, true); // convert to array if it is still an object
        if (!isset($con['from']) || (is_array($con['from']) && array_key_exists('year', $con['from']) && is_null($con['from']['year']))) {
            $con['from'] = null;
        }
        if (!is_string($con['from'] ?? null) && isset($con['from']['year'])) {
            $con['from'] = ($con['from']['year'] ?? '') . '-' . str_pad(($con['from']['month'] ?? ''), 2, '0', STR_PAD_LEFT);
        }
        if (!isset($con['to']) || (is_array($con['to']) && array_key_exists('year', $con['to']) && is_null($con['to']['year']))) {
            $con['to'] = null;
        }
        if (!is_string($con['to'] ?? null) && isset($con['to']['year'])) {
            $con['to'] = ($con['to']['year'] ?? '') . '-' . str_pad(($con['to']['month'] ?? ''), 2, '0', STR_PAD_LEFT);
        }
        $cv[$i] = $con;
    }
    $osiris->persons->updateOne(
        ['_id' => $user['_id']],
        ['$set' => ['cv' => $cv]]
    );
    $cvUpdated++;
}
migrationCard(
    'CV date format migrated',
    'CV-Datumsformat migriert',
    'CV date entries were converted to the current OSIRIS format where needed.',
    'CV-Datumseinträge wurden bei Bedarf in das aktuelle OSIRIS-Format überführt.',
    $cvUpdated,
    'person records checked and updated',
    'Personendatensätze geprüft und aktualisiert'
);


# Migrate updated and updated_by fields based on history entries
$activities = $osiris->activities->find(['history' => ['$exists' => true]], ['projection' => ['history' => 1]]);
$N = 0;
foreach ($activities as $activity) {
    // add updated and updated_by based on history entries
    $history = $activity['history'];
    // get last history entry with type 'edited'
    $editDate = null;
    $editUser = null;
    foreach ($history as $entry) {
        if ($entry['type'] == 'edited' && ($editDate == null || $entry['date'] > $editDate)) {
            $editDate = $entry['date'];
            $editUser = $entry['user'];
        }
    }
    if ($editDate == null) continue;
    $osiris->activities->updateOne(
        ['_id' => $activity['_id']],
        ['$set' => [
            'updated' => $editDate,
            'updated_by' => $editUser
        ]]
    );
    $N++;
}

migrationCard(
    'Activity update metadata restored',
    'Aktualisierungsmetadaten für Aktivitäten wiederhergestellt',
    'The fields updated and updated_by were reconstructed from existing activity history entries.',
    'Die Felder updated und updated_by wurden aus bestehenden Änderungshistorien der Aktivitäten rekonstruiert.',
    $N,
    'activities updated',
    'Aktivitäten aktualisiert'
);

// Migrate OpenAlex IDs from 'openalex' field to 'openalex_id' and create indexes for command palette search
$activities = $osiris->activities->find(['openalex' => ['$exists' => true]], ['projection' => ['openalex' => 1]]);
$N = 0;
foreach ($activities as $activity) {
    $openalex = $activity['openalex'];
    $osiris->activities->updateOne(
        ['_id' => $activity['_id']],
        ['$set' => ['openalex_id' => $openalex], '$unset' => ['openalex' => '']]
    );
    $N++;
}
migrationCard(
    'OpenAlex IDs migrated',
    'OpenAlex-IDs migriert',
    'OpenAlex identifiers were moved to the new openalex_id field to make them consistent with the new data model.',
    'OpenAlex-IDs wurden in das neue Feld openalex_id verschoben, um sie mit dem neuen Datenmodell konsistent zu machen.',
    $N,
    'activities updated',
    'Aktivitäten aktualisiert'
);

/* Create an index if it doesn't already exist. */
function ensureIndex($collection, array $keys, array $options = [])
{
    try {
        if (empty($options['name'])) {
            $parts = [];
            foreach ($keys as $k => $v) $parts[] = $k . '_' . $v;
            $options['name'] = 'cp_' . implode('__', $parts);
        }

        $name = $collection->createIndex($keys, $options);

        echo '<li class="migration-ok">✓ ';
        echo e($collection->getCollectionName()) . ' → <code>' . e($name) . '</code>';
        echo '</li>';

        return true;
    } catch (Throwable $e) {
        echo '<li class="migration-error">✗ ';
        echo e($collection->getCollectionName()) . ' → ' . e($e->getMessage());
        echo '</li>';

        return false;
    }
}

echo '<div class="migration-card">';
echo '<h3>' . lang('Command palette search indexes', 'Suchindizes für die Command Palette') . '</h3>';
echo '<p class="migration-muted">' . lang(
    'OSIRIS is creating or confirming the indexes required for fast command palette search.',
    'OSIRIS erstellt oder bestätigt die Indizes, die für eine schnelle Suche in der Command Palette benötigt werden.'
) . '</p>';
echo '<ul class="migration-index-list">';

/* persons */
ensureIndex($osiris->persons, ['search_text' => 1]);
ensureIndex($osiris->persons, ['username' => 1]); // optional but usually helpful

/* projects */
ensureIndex($osiris->projects, ['acronym' => 1]);
ensureIndex($osiris->projects, ['name' => 1]);

/* infrastructures */
ensureIndex($osiris->infrastructures, ['name' => 1]);
ensureIndex($osiris->infrastructures, ['name_de' => 1]);

/* events */
ensureIndex($osiris->events, ['title' => 1]);
ensureIndex($osiris->events, ['title_full' => 1]);

/* journals */
ensureIndex($osiris->journals, ['journal' => 1]);
ensureIndex($osiris->journals, ['abbr' => 1]);
ensureIndex($osiris->journals, ['issn' => 1]); // for exact match boost

/* groups (units) */
ensureIndex($osiris->groups, ['id' => 1]);
ensureIndex($osiris->groups, ['name' => 1]);
ensureIndex($osiris->groups, ['name_de' => 1]);

/* organizations */
ensureIndex($osiris->organizations, ['name' => 1]);
ensureIndex($osiris->organizations, ['synonyms' => 1]); // multikey index (array)


echo '</ul>';
echo '</div>';

// add news feature and write news for OSIRIS 2.0.0 release
$news = $osiris->news;
// only add news if collection is empty to avoid duplicates on multiple runs
if ($news->countDocuments() === 0) {
    $new = [
        "title" => "OSIRIS 2.0 – Focus on User Experience",
        "title_de" => "OSIRIS 2.0 – Fokus auf User Experience",
        "content" => "<p>The most noticeable change is the new home page. Instead of landing directly on your profile, OSIRIS now greets you with a dashboard that brings together important information—such as tasks, notifications, news, events, and deadlines—in one central location. The goal is to provide a quick overview of what’s important right now and what’s coming up next.</p><p></p><p>The activity pages have also been fundamentally redesigned. Content is now organized by topic, making it much easier to grasp. At the same time, existing workflows remain intact: users can switch to the classic view at any time and set it as the default permanently. The new interface is complemented by a redesigned sidebar with favorites and the new Command Palette, which enables quick navigation via keyboard.</p><p></p><p>With the Research Spectrum, OSIRIS also introduces a new way to analyze publications by topic and highlight key research areas. This is complemented by the integration of citation data and new visualizations that can illustrate trends over time.</p><p></p><p>In addition, version 2.0 includes many other improvements, including news and announcements, centralized deadlines, a completely redesigned admin interface, enhanced reporting features, more consistent permissions, and numerous minor optimizations throughout the system. And last but not least, Sophie the little owl is now here to guide you through many areas of OSIRIS and help you find your way when you need it most.</p><p></p><p>Please check out the <a href=\"/new-stuff\" rel=\"noopener noreferrer\" target=\"_blank\">Release Notes</a> for more details on the new features and improvements. Thank you for using OSIRIS!</p>",
        "content_de" => "<p>Die auffälligste Änderung ist die neue Startseite. Statt direkt im eigenen Profil zu landen, begrüßt dich OSIRIS nun mit einem Dashboard, das wichtige Informationen wie Aufgaben, Benachrichtigungen, News, Events und Deadlines an einem zentralen Ort zusammenführt. Ziel ist es, einen schnellen Überblick darüber zu geben, was gerade wichtig ist und was als Nächstes ansteht.</p><p></p><p>Auch die Aktivitätsseiten wurden grundlegend überarbeitet. Inhalte werden nun thematisch gegliedert dargestellt und sind dadurch deutlich leichter zu erfassen. Gleichzeitig bleiben bestehende Arbeitsweisen erhalten: Wer möchte, kann jederzeit zur klassischen Ansicht wechseln und diese auch dauerhaft als Standard festlegen. Ergänzt wird die neue Oberfläche durch eine überarbeitete Seitenleiste mit Favoriten sowie die neue Command Palette, die eine schnelle Navigation per Tastatur ermöglicht.</p><p></p><p>Mit dem Forschungs-Spektrum führt OSIRIS außerdem eine neue Möglichkeit ein, Publikationen thematisch auszuwerten und Forschungsschwerpunkte sichtbar zu machen. Ergänzt wird dies durch die Integration von Zitationsdaten sowie neue Visualisierungen, die Entwicklungen über die Zeit hinweg darstellen können.</p><p></p><p>Daneben enthält Version 2.0 viele weitere Verbesserungen, darunter News und Ankündigungen, zentrale Deadlines, ein vollständig überarbeitetes Admin-Interface, erweiterte Berichtsfunktionen, konsistentere Berechtigungen sowie zahlreiche kleinere Optimierungen im gesamten System. Und nicht zuletzt begleitet euch ab sofort Sophie, die kleine Eule, durch viele Bereiche von OSIRIS und hilft dort, wo Orientierung besonders wichtig ist.</p><p></p><p>Bitte schaue dir die <a href=\"/new-stuff\" rel=\"noopener noreferrer\" target=\"_blank\">Release Notes</a> für weitere Details zu den neuen Funktionen und Verbesserungen an. Vielen Dank, dass Ihr OSIRIS verwendet!</p>",
        "date" => date('Y-m-d'),
        "visibility" => "internal",
        "activities" => [],
        "teaser" => "With version 2.0, OSIRIS features a new home screen, a modernized user interface, and numerous features that make day-to-day work more organized and enjoyable.",
        "teaser_de" => "Mit Version 2.0 erhält OSIRIS eine neue Startseite, eine modernisierte Nutzeroberfläche und zahlreiche Funktionen, die den Arbeitsalltag übersichtlicher und angenehmer machen.",
        "type" => "system"
    ];
    // check if file exists and is readable
    if (is_readable(BASEPATH . '/img/news/sophie-osiris-2.0.png')) {
    $file = file_get_contents(BASEPATH . '/img/news/sophie-osiris-2.0.png');
    // encode image
    $file = base64_encode($file);
    $img = new MongoDB\BSON\Binary($file, MongoDB\BSON\Binary::TYPE_GENERIC);
    $new['image'] = [
        'data' => $img,
        'type' => 'image/png',
        'extension' => 'png',
        'uploaded_by' => 'system',
        'uploaded' => date('Y-m-d')
    ];
    }
    $news->insertOne($new);
}

echo '<div class="migration-card success">';
echo '<h3 class="migration-ok">✓ ' . lang('Migration completed', 'Migration abgeschlossen') . '</h3>';
echo '<p>' . lang(
    'All required migration steps have finished. OSIRIS is ready to use with the updated database structure.',
    'Alle notwendigen Migrationsschritte wurden abgeschlossen. OSIRIS kann mit der aktualisierten Datenbankstruktur verwendet werden.'
) . '</p>';
echo '</div>';
echo '</div>';
