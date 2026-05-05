<?php

/**
 * Manage LDAP attribute synchronization
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$attributeMappings = [
    'first' => '',
    'last' => '',
    'academic_title' => '',
    'mail' => '',
    'telephone' => '',
    'mobile' => '',
    'position' => '',
    'department' => '',
    'is_active' => '',
    'room' => '',
    'internal_id' => '',
];

$config = $osiris->adminGeneral->findOne(['key' => 'ldap_mappings']);
$availableLdapFields = DB::doc2Arr($config['value'] ?? []);
$attributeMappings = array_merge($attributeMappings, $availableLdapFields ?? []);

$fields = [
    'first' => [
        'name' => lang('First Name', 'Vorname'),
        'example' => 'givenname', // Beispiel: "John"
    ],
    'last' => [
        'name' => lang('Last Name', 'Nachname'),
        'example' => 'sn', // Beispiel: "Doe"
    ],
    'academic_title' => [
        'name' => lang('Academic Title', 'Akademischer Titel'),
        'example' => 'personalTitle', // Beispiel: "Dr."
    ],
    'mail' => [
        'name' => lang('Email', 'E-Mail'),
        'example' => 'mail', // Beispiel: "john.doe@example.com"
    ],
    'telephone' => [
        'name' => lang('Telephone', 'Telefon'),
        'example' => 'telephonenumber', // Beispiel: "+1 555 123 456"
    ],
    'mobile' => [
        'name' => lang('Mobile', 'Mobil'),
        'example' => 'mobile', // Beispiel: "+1 555 987 654"
    ],
    'position' => [
        'name' => lang('Position', 'Position'),
        'example' => 'title', // Beispiel: "Software Engineer"
    ],
    'department' => [
        'name' => lang('Department', 'Abteilung'),
        'example' => 'department', // Beispiel: "IT Department"
    ], //description
    'is_active' => [
        'name' => lang('Active', 'Aktiv'),
        'example' => 'useraccountcontrol', // Beispiel: "512" (Aktiv) oder "514" (Deaktiviert)
    ],
    'room' => [
        'name' => lang('Room', 'Raum'),
        'example' => 'physicaldeliveryofficename', // Beispiel: "Room 101"
    ],
    'internal_id' => [
        'name' => lang('Internal ID', 'Interne ID'),
        'example' => 'objectsid', // Beispiel: "12345"
    ],
];
?>
<div class="container w-800 mw-full" id="custom-footer">
    <form action="<?= ROOTPATH ?>/synchronize-attributes" method="post">

        <h1>
            <i class="ph-duotone ph-user-switch" aria-hidden="true"></i>
            LDAP: <?= lang('Attribute synchronization', 'Attribut-Synchronisation') ?>
        </h1>

        <?php
        $last_sync = $osiris->system->findOne(['key' => 'ldap-sync']);
        $last_sync = $last_sync['value'] ?? null;
        ?>

        <p>
            <?= lang('Last synchronization:', 'Letzte Synchronisierung:') ?> <b><?= $last_sync ? format_date($last_sync) : lang('Never', 'Nie') ?></b>
        </p>

        <p>
            <?= lang('Here you can define the attributes that will be automatically synchronized with your LDAP instance.', 'Hier kannst du die Attribute festlegen, die automatisch mit deiner LDAP-Instanz synchronisiert werden sollen.') ?>
        </p>

        <p class="text-danger">
            <i class="ph ph-warning"></i>
            <?= lang('Please note that the synchronized attributes cannot be edited within OSIRIS anymore, except for units.', 'Bitte beachte, dass die synchronisierten Attribute nicht mehr in OSIRIS bearbeitet werden können, abgesehen von Einheiten.') ?>
        </p>

        <table class="table w-auto mb-20">
            <thead>
                <tr>
                    <th><?= lang('Person attribute in OSIRIS', 'Personen-Attribut in OSIRIS') ?></th>
                    <th><?= lang('LDAP variable (leave empty to manage the field in OSIRIS)', 'LDAP-Variable (leer lassen, um das Feld in OSIRIS zu managen)') ?></th>
                    <th><?= lang('Example', 'Beispiel') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attributeMappings as $field => $ldap_field):
                    $f = $fields[$field];
                ?>
                    <tr>
                        <td>
                            <?= e($f['name'] ?? $field) ?>
                        </td>
                        <td>
                            <input type="text" name="field[<?= $field ?>]" id="field-<?= $field ?>" value="<?= e($ldap_field) ?>" class="form-control">
                        </td>
                        <td class="text-muted">
                            <?= e($f['example']) ?>
                            <a onclick="$('#field-<?= $field ?>').val('<?= $f['example'] ?>')"><?= lang('Take', 'Übernehmen') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save &amp; Preview', 'Speichern und Vorschau zeigen') ?>
        </button>

    </form>

</div>