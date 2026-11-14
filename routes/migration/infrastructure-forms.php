<?php
require_once BASEPATH . '/php/InfrastructureForms.php';
$fields = InfrastructureForms::legacyFields($osiris);
$osiris->adminGeneral->updateOne(
    ['id' => 'infrastructure-fields'],
    ['$setOnInsert' => ['fields' => $fields]],
    ['upsert' => true]
);
$updated = 0;
foreach ($osiris->infrastructures->find() as $original) {
    $original = DB::doc2Arr($original);
    $set = [];
    $unset = [];
    foreach (['name', 'subtitle', 'description'] as $field) {
        if (!array_key_exists($field, $original) && !array_key_exists($field . '_de', $original)) continue;
        $value = $original[$field] ?? '';
        if ($value instanceof Traversable) $value = iterator_to_array($value);
        $translations = is_array($value) ? $value : ['en' => (string) $value];
        if (empty($translations['de']) && !empty($original[$field . '_de'])) $translations['de'] = $original[$field . '_de'];
        if ($value !== $translations) $set[$field] = $translations;
        if (array_key_exists($field . '_de', $original)) $unset[$field . '_de'] = '';
    }
    if (!$set && !$unset) continue;
    $update = [];
    if ($set) $update['$set'] = $set;
    if ($unset) $update['$unset'] = $unset;
    $osiris->infrastructures->updateOne(['_id' => $original['_id']], $update);
    $updated++;
}
echo '<p>' . e(lang('Infrastructure forms prepared and content localized', 'Infrastrukturformulare vorbereitet und Inhalte lokalisiert')) . ': ' . $updated . '</p>';
