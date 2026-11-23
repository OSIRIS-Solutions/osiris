<?php
include_once BASEPATH . "/php/Render.php";
$osiris->persons->createIndex(['current_units' => 1]);
$updated = renderCurrentUnits();

migrationCard(
    t('admin.current_units_updated'),
    t('admin.current_units_of_users_were_updated'),
    $updated,
    t('admin.person_records_checked_and_updated')
);
