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
        'name' => t('common.name_first'),
        'example' => 'givenname', // Beispiel: "John"
    ],
    'last' => [
        'name' => t('common.name_last'),
        'example' => 'sn', // Beispiel: "Doe"
    ],
    'academic_title' => [
        'name' => t('admin.academic_title'),
        'example' => 'personalTitle', // Beispiel: "Dr."
    ],
    'mail' => [
        'name' => t('common.email'),
        'example' => 'mail', // Beispiel: "john.doe@example.com"
    ],
    'telephone' => [
        'name' => t('common.telephone'),
        'example' => 'telephonenumber', // Beispiel: "+1 555 123 456"
    ],
    'mobile' => [
        'name' => t('common.mobile'),
        'example' => 'mobile', // Beispiel: "+1 555 987 654"
    ],
    'position' => [
        'name' => t('common.position'),
        'example' => 'title', // Beispiel: "Software Engineer"
    ],
    'department' => [
        'name' => t('common.department'),
        'example' => 'department', // Beispiel: "IT Department"
    ], //description
    'is_active' => [
        'name' => t('common.active'),
        'example' => 'useraccountcontrol', // Beispiel: "512" (Aktiv) oder "514" (Deaktiviert)
    ],
    'room' => [
        'name' => t('common.room'),
        'example' => 'physicaldeliveryofficename', // Beispiel: "Room 101"
    ],
    'internal_id' => [
        'name' => t('common.internal_id'),
        'example' => 'objectsid', // Beispiel: "12345"
    ],
];
?>
<div class="container w-800 mw-full" id="custom-footer">
    <form action="<?= ROOTPATH ?>/synchronize-attributes" method="post">

        <h1>
            <i class="ph-duotone ph-user-switch" aria-hidden="true"></i>
            LDAP: <?= t('admin.attribute_synchronization') ?>
        </h1>

        <?php
        $last_sync = $osiris->system->findOne(['key' => 'ldap-sync']);
        $last_sync = $last_sync['value'] ?? null;
        ?>

        <p>
            <?= t('admin.last_synchronization') ?> <b><?= $last_sync ? format_date($last_sync) : t('common.never') ?></b>
        </p>

        <p>
            <?= t('admin.here_you_can_define_the_attributes_that_will_be_automatically_synchronized') ?>
        </p>

        <p class="text-danger">
            <i class="ph ph-warning"></i>
            <?= t('admin.please_note_that_the_synchronized_attributes_cannot_be_edited_within_osiris') ?>
        </p>

        <table class="table w-auto mb-20">
            <thead>
                <tr>
                    <th><?= t('admin.person_attribute_in_osiris') ?></th>
                    <th><?= t('admin.ldap_variable_leave_empty_to_manage_the_field_in_osiris') ?></th>
                    <th><?= t('common.example') ?></th>
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
                            <a onclick="$('#field-<?= $field ?>').val('<?= $f['example'] ?>')"><?= t('admin.take') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= t('admin.save_amp_preview') ?>
        </button>

    </form>

</div>