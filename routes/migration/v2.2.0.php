<?php
include_once BASEPATH . "/php/Render.php";
$osiris->persons->createIndex(['current_units' => 1]);
$updated = renderCurrentUnits();

migrationCard(
    'Current Units updated',
    'Current units of users were updated.',
    'Die aktuellen Einheiten der Benutzer wurden aktualisiert.',
    $updated,
    'person records checked and updated',
    'Personendatensätze geprüft und aktualisiert'
);
