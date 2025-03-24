<?php

/**
 * Manage person data
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>

<h1>
    <i class="ph ph-user text-primary"></i>
    <?= lang('People', 'Personen') ?>
</h1>

<p>
    <?= lang('Manage person data.', 'Personendaten verwalten.') ?>
</p>

<?php
$persons = $osiris->adminPersons->find();

?>


<!-- Settings for different user management styles -->
<?php
switch (strtoupper(USER_MANAGEMENT)) {
    case 'LDAP':

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
        <p class="text-primary mt-0">
            <?= lang('You are using the LDAP interface for your user management.', 'Ihr nutzt die LDAP-Schnittstelle fürs Nutzer-Management.') ?>
        </p>
        <?php
        break;
        // TODO: continue
        ?>
        <form action="<?= ROOTPATH ?>/synchronize-attributes" method="post" class="box primary padded">


            <h2 class="title">
                <?= lang('LDAP Settings', 'LDAP-Einstellungen') ?>
            </h2>

            <p>
                <?= lang('Here you can define the attributes that will be automatically synchronized with your LDAP instance.', 'Hier kannst du die Attribute festlegen, die automatisch mit deiner LDAP-Instanz synchronisiert werden sollen.') ?>
            </p>

            <p class="text-danger">
                <i class="ph ph-warning"></i>
                <?= lang('Please note that the synchronized attributes cannot be edited within OSIRIS anymore.', 'Bitte beachte, dass die synchronisierten Attribute nicht mehr in OSIRIS bearbeitet werden können.') ?>
            </p>

            <table class="table simple w-auto small mb-10">
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
                                <?= htmlspecialchars($f['name'] ?? $field) ?>
                            </td>
                            <td>
                                <input type="text" name="field[<?= $field ?>]" id="field-<?= $field ?>" value="<?= htmlspecialchars($ldap_field) ?>" class="form-control">
                            </td>
                            <td class="text-muted">
                                <?= htmlspecialchars($f['example']) ?>
                                <a onclick="$('#field-<?= $field ?>').val('<?= $f['example'] ?>')"><?= lang('Take', 'Übernehmen') ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn primary"><?= lang('Save &amp; Preview', 'Speichern und Vorschau zeigen') ?></button>

        </form>
    <?php
        break;

    case 'AUTH': ?>

        <!-- <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
                <div class="box primary padded">

                    <h2 class="title">
                        <?= lang('AUTH settings', 'AUTH-Einstellungen') ?>
                    </h2>

                    <input type="hidden" name="general[auth-self-registration]" value="0">
                    <div class="custom-checkbox">
                        <input type="checkbox" name="general[auth-self-registration]" id="auth-self-registration-1" value="1" <?= $Settings->get('auth-self-registration') ? 'checked' : '' ?>>
                        <label for="auth-self-registration-1"><?= lang('Allow users to create their own account', 'Erlaube Benutzern, ein eigenes Konto zu erstellen') ?></label>
                    </div>

                </div>
            </form> -->
    <?php
        break;

    case 'SSO': ?>

<?php
        break;

    default:
        break;
}
?>


<?php if (strtoupper(USER_MANAGEMENT) == 'LDAP') {
} ?>

<form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
    <div class="box primary padded">

        <h2 class="title">
            <?= lang('Staff settings', 'Einstellungen für Mitarbeitende') ?>
        </h2>

        <h5>
            <?= lang('Possible Positions', 'Mögliche Positionen') ?>
        </h5>

        <p>
            <?= lang('Define the fields that are used as position for the staff members.', 'Definiere die Felder, die für die Mitarbeitenden verwendet werden.') ?>
        </p>

        <?php
        $staff = $Settings->get('staff');
        $staffPos = $staff['positions'] ?? [];
        $staffFree = $staff['free'] ?? true;
        ?>


        <div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="staff[free]" id="free-1" value="1" <?= $staffFree ? 'checked' : '' ?>>
                <label for="free-1"><?= lang('Free field for positions', 'Freitextfeld für Positionen') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="staff[free]" id="free-0" value="0" <?= !$staffFree ? 'checked' : '' ?>>
                <label for="free-0"><?= lang('Select from list of positions', 'Wähle aus definierter Liste') ?></label>
            </div>
        </div>

        <br>
        <b>
            <?= lang('Defined list of positions', 'Definierte Liste möglicher Positionen') ?>:
        </b>

        <table class="table simple small my-20">
            <thead>
                <tr>
                    <th></th>
                    <th>Position (english)</th>
                    <th>Position (deutsch)</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="possible-positions">
                <?php foreach ($staffPos as $value) {
                    if ($value instanceof \MongoDB\BSON\Document) {
                        $value = DB::doc2Arr($value);
                    }
                    // dump type of value
                    if (is_array($value) || is_object($value)) {
                        $de = $value[1] ?? $value[0];
                        $en = $value[0];
                    } else {
                        $en = $value;
                        $de = $value;
                    }
                ?>
                    <tr>
                        <td class="w-50">
                            <i class="ph ph-dots-six-vertical text-muted handle"></i>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="staff[positions][]" value="<?= $en ?>" required>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="staff[positions_de][]" value="<?= $de ?>">
                        </td>
                        <td>
                            <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>
            <tfoot>
                <tr>
                    <td class="w-50 bg-white"></td>
                    <td colspan="3" class="bg-white">
                        <button class="btn" type="button" onclick="addValuesRow()"><i class="ph ph-plus-circle"></i></button>
                    </td>
                </tr>
            </tfoot>
        </table>


        <button class="btn signal">
            <i class="ph ph-floppy-disk"></i>
            Save
        </button>
    </div>
</form>


<script>
    $(document).ready(function() {
        $('#possible-positions').sortable({
            handle: ".handle",
        });
    });


    function addValuesRow() {
        $('#possible-positions').append(`
        <tr>
            <td class="w-50">
                <i class="ph ph-dots-six-vertical text-muted handle"></i>
            </td>
            <td>
                <input type="text" class="form-control" name="staff[positions][]" required>
            </td>
            <td>
                <input type="text" class="form-control" name="staff[positions_de][]">
            </td>
            <td>
                <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
            </td>
        </tr>
    `);
    }
</script>