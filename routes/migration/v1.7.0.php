<?php

/**
 * Migration script for OSIRIS v1.7.0
 * Transforms Nagoya permit info in proposals to new format.
 */

// check if Nagoya is enabled
$nagoyaEnabled = $Settings->get('features.nagoya.enabled', false);


// transform nagoya info from proposals
$proposals = $osiris->proposals->find(['nagoya' => ['$in' => ['yes', 'no']]])->toArray();
$N_ = count($proposals);

if ($N_ == 0 && !$nagoyaEnabled) {
    echo "<p>". lang(
        "No proposals with Nagoya info found and Nagoya feature is disabled. No changes made.",
        "Keine Anträge mit Nagoya-Informationen gefunden und Nagoya-Funktion ist deaktiviert. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    foreach ($proposals as $proposal) {
        $enabled = ($proposal['nagoya'] == 'yes');
        $countries = [];
        foreach ($proposal['nagoya_countries'] ?? [] as $iso) {
            $countries[] = [
                'id' => uniqid(),
                'code' => $iso,
                'abs' => null
            ];
        }
        $nagoya = [
            'enabled' => $enabled,
            'countries' => $countries,
            'status' => (empty($countries) ? 'incomplete' : 'abs-review')
        ];
        $osiris->proposals->updateOne(
            ['_id' => $proposal['_id']],
            ['$set' => [
                'nagoya' => $nagoya,
            ]]
        );
    }
    echo "<p>". lang(
        "Nagoya info transformed for " . count($proposals) . " proposals.",
        "Nagoya-Informationen für " . count($proposals) . " Anträge wurden umgewandelt."
    ) . "</p>";
}


// get queries without 'type' field
$queries = $osiris->queries->find()->toArray();
$N_ = count($queries);

foreach ($queries as $query) {
    $type = $query['type'] ?? 'activities';
    if ($type == 'activity') {
        $type = 'activities';
    } elseif ($type == 'project') {
        $type = 'projects';
    } elseif ($type == 'proposal') {
        $type = 'proposals';
    }
    $osiris->queries->updateOne(
        ['_id' => $query['_id']],
        ['$set' => [
            'type' => $type,
        ]]
    );
}
echo "<p>" . lang(
    "Type field added to " . $N_ . " queries.",
    "Typ-Feld zu " . $N_ . " Abfragen hinzugefügt."
) . "</p>";