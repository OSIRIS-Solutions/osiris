<?php
/** Initialize the event form or rename draft module IDs; event data and field properties stay unchanged. */
require_once BASEPATH . '/php/EventForms.php';
setupDefaults($osiris, ['events']);
$definition = $osiris->adminGeneral->findOne(['id' => 'event-fields']);
$fields = isset($definition['fields'])
    ? EventForms::upgradeFields(DB::doc2Arr($definition['fields']))
    : EventForms::defaults();
$osiris->adminGeneral->updateOne(
    ['id' => 'event-fields'],
    ['$set' => ['fields' => $fields]],
    ['upsert' => true]
);
echo '<p>' . e(t('admin.event_form_configuration_prepared_existing_field_settings_and_even')) . '</p>';
