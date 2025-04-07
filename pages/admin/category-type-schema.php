<?php

/**
 * Type schema
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . "/php/fields.php";
include_once BASEPATH . "/php/Modules.php";
$Modules = new Modules();
dump($type['modules']);

$existing_fields = array_column($FIELDS, null, 'id');
// dump($existing_fields);

$schema = [
    'type' => 'object',
    'properties' => [],
    'required' => ['type', 'subtype', 'year', 'month'],
];

$defaults = [
    'affiliated' => false,
    'created' => true,
    'created_by' => true,
    'updated' => true,
    'updated_by' => true,
    'type' => true,
    'subtype' => true
];

foreach ($defaults as $key => $value) {
    $schema['properties'][$key] = [
        'name' => $key,
        'type' => 'string',
    ];
    if ($value) {
        $schema['required'][] = $key;
    }
}

foreach ($type['modules'] as $key) {
    if (strpos($key, "*") !== false) {
        $key = str_replace("*", "", $key);
        $schema['required'][] = $key;
    }
    $fields = $Modules->all_modules[$key]['fields'];
    $fields = array_keys($fields);
    foreach ($fields as $f) {
        if (isset($existing_fields[$f])) {
            $schema['properties'][$f] = [
                'name' => $existing_fields[$f]['label'],
                'type' => $existing_fields[$f]['type'],
            ];
        } else {
            $schema['properties'][$f] = [
                'name' => $f,
                'type' => 'string',
            ];
        }
        // $FIELDS
    }
}
dump($schema);
