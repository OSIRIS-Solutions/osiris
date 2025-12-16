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
    echo "<p>". lang(
        "No teaching modules found. No changes made.",
        "Keine Lehrmodule gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>". lang(
        "Transformed module numbers into strings for " . $updated . " out of " . $N_ . " teaching modules.",
        "Modulnummern für " . $updated . " von " . $N_ . " Lehrmodulen in Zeichenketten umgewandelt."
    ) . "</p>";
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
    echo "<p>". lang(
        "No persons found with created date in d.m.Y format. No changes made.",
        "Keine Personen mit Erstellungsdatum im Format d.m.Y gefunden. Es wurden keine Änderungen vorgenommen."
    ) . "</p>";
} else {
    echo "<p>". lang(
        "Transformed created dates from d.m.Y to Y-m-d for " . $updated . " out of " . $N_ . " persons.",
        "Erstellungsdaten von d.m.Y zu Y-m-d für " . $updated . " von " . $N_ . " Personen umgewandelt."
    ) . "</p>";
}