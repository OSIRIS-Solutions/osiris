<?php

/**
 * Migration script for OSIRIS v1.7.0
 * Transforms Nagoya permit info in proposals to new format.
 */

// check if Nagoya is enabled
$nagoyaEnabled = $Settings->get('features.nagoya.enabled', false);


// transform nagoya info from proposals
$proposals = $osiris->proposals->find(['nagoya' => ['$exists' => true]])->toArray();
$N_ = count($proposals);

if ($N_ == 0 && !$nagoyaEnabled) {
    echo lang("No proposals with Nagoya info found and Nagoya feature is disabled. No changes made.", 
        "Keine Anträge mit Nagoya-Informationen gefunden und Nagoya-Funktion ist deaktiviert. Es wurden keine Änderungen vorgenommen."
    );
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
            'status' => (empty($countries) ? 'incomplete': 'abs-review')
        ];
        $osiris->proposals->updateOne(
            ['_id' => $proposal['_id']],
            ['$set' => [
                'nagoya' => $nagoya,
            ]]
        );
    }
    echo lang("Nagoya info transformed for " . count($proposals) . " proposals.", 
        "Nagoya-Informationen für " . count($proposals) . " Anträge wurden umgewandelt."
    );
}


// get queries without 'type' field
$queries = $osiris->queries->find(['type' => ['$exists' => false]])->toArray();
$N_ = count($queries);

if ($N_ == 0) {
    // echo lang("No queries without type field found. No changes made.", 
    //     "Keine Abfragen ohne Typ-Feld gefunden. Es wurden keine Änderungen vorgenommen."
    // );
} else {
    foreach ($queries as $query) {
        $osiris->queries->updateOne(
            ['_id' => $query['_id']],
            ['$set' => [
                'type' => 'activity',
            ]]
        );
    }
    echo lang("Type field added to " . count($queries) . " queries.", 
        "Typ-Feld zu " . count($queries) . " Abfragen hinzugefügt."
    );
}