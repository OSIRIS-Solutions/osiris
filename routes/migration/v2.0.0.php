<?php

include_once BASEPATH . "/php/Render.php";

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
echo "<p>Done. Updated documents: {$updated}</p>\n";


/* Create an index if it doesn't already exist. */
function ensureIndex($collection, array $keys, array $options = [])
{
    try {
        // Provide a deterministic name so we can manage it later
        if (empty($options['name'])) {
            $parts = [];
            foreach ($keys as $k => $v) $parts[] = $k . '_' . $v;
            $options['name'] = 'cp_' . implode('__', $parts);
        }

        $name = $collection->createIndex($keys, $options);
        echo "<p>OK  {$collection->getCollectionName()} -> {$name}</p>";
    } catch (Throwable $e) {
        echo "<p>ERR {$collection->getCollectionName()} -> " . $e->getMessage() . "</p>";
    }
}

echo "<p>Creating indexes for command palette search...</p>\n\n";

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

echo "<p>Done.</p>\n";
