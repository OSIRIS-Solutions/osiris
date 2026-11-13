<?php
/** Initialize the previous deadline form as the default; existing configurations and data stay intact. */
require_once BASEPATH . '/php/DeadlineForms.php';
$osiris->adminGeneral->updateOne(
    ['id' => 'deadline-fields'],
    ['$setOnInsert' => ['fields' => DeadlineForms::defaults()]],
    ['upsert' => true]
);
echo '<p>' . e(lang('Deadline form configuration prepared. Existing settings and data are preserved.', 'Deadline-Formularkonfiguration vorbereitet. Vorhandene Einstellungen und Daten bleiben erhalten.')) . '</p>';
