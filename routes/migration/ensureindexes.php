<?php
    function ensureIndex($collection, array $keys, array $options = [])
    {
        try {
            if (empty($options['name'])) {
                $parts = [];
                foreach ($keys as $k => $v) $parts[] = $k . '_' . $v;
                $options['name'] = 'cp_' . implode('__', $parts);
            }

            $name = $collection->createIndex($keys, $options);

            echo '<tr class="migration-ok"><td class="text-success">✓</td><td> ';
            echo e($collection->getCollectionName()) . ' → <code>' . e($name) . '</code>';
            echo '</td></tr>';

            return true;
        } catch (Throwable $e) {
            echo '<tr class="migration-error"><td class="text-danger">✗</td><td> ';
            echo e($collection->getCollectionName()) . ' → ' . e($e->getMessage());
            echo '</td></tr>';

            return false;
        }
    }

    echo '<div class="migration-card">';
    echo '<h2>' . lang('Check Indexes', 'Indizes überprüfen') . '</h2>';
    echo '<p class="text-muted">' . lang(
        'OSIRIS is creating or confirming the indexes required for the database.',
        'OSIRIS erstellt oder bestätigt die Indizes, die für die Datenbank erforderlich sind.'
    ) . '</p>';
    echo '<table class="table">';
    ensureIndex($osiris->activities, ['rendered.plain' => 'text']);
    ensureIndex($osiris->activities, ['journal_id' => 1]);

    /* persons */
    ensureIndex($osiris->persons, ['search_text' => 1]);
    ensureIndex($osiris->persons, ['username' => 1]); // optional but usually helpful

    /* projects */
    ensureIndex($osiris->projects, ['acronym' => 1]);
    ensureIndex($osiris->projects, ['name' => 1]);

    /* infrastructures */
    ensureIndex($osiris->infrastructures, ['id' => 1]);
    ensureIndex($osiris->infrastructures, ['name' => 1]);
    ensureIndex($osiris->infrastructures, ['name_de' => 1]);

    /* events */
    ensureIndex($osiris->conferences, ['title' => 1]);
    ensureIndex($osiris->conferences, ['title_full' => 1]);

    /* activities */
    ensureIndex($osiris->activities, ['journal_id' => 1]);

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

    ensureIndex($osiris->activitiesConnections, ['source_id' => 1]);
    ensureIndex($osiris->activitiesConnections, ['target_id' => 1]);
    ensureIndex($osiris->news, ['activities' => 1]);
    ensureIndex($osiris->topics, ['id' => 1]);

    echo '</table>';
    echo '</div>';