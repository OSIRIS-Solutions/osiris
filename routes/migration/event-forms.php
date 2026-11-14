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
echo '<p>' . e(lang('Event form configuration prepared. Existing field settings and event data are preserved.', 'Event-Formularkonfiguration vorbereitet. Vorhandene Feldeinstellungen und Eventdaten bleiben erhalten.')) . '</p>';
