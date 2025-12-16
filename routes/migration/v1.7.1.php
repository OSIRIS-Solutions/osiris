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