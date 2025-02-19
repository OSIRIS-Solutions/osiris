<?php

/**
 * Page for admin dashboard for general settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.1.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$affiliation = $Settings->get('affiliation_details');

?>

<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
<script src="<?= ROOTPATH ?>/js/general-settings.js"></script>

<h1 class="mt-0">
    <i class="ph ph-gear text-primary"></i>
    <?= lang('General Settings', 'Allgemeine Einstellungen') ?>
</h1>

<!-- pills -->

<nav class="pills mt-20 mb-0">

    <a onclick="navigate('general')" id="btn-general" class="btn active">
        <i class="ph ph-gear" aria-hidden="true"></i>
        <?= lang('General', 'Allgemein') ?>
    </a>
    <!-- institute -->
    <a onclick="navigate('institute')" id="btn-institute" class="btn">
        <i class="ph ph-building" aria-hidden="true"></i>
        <?= lang('Institute', 'Institut') ?>
    </a>
    <!-- staff -->
    <a onclick="navigate('staff')" id="btn-staff" class="btn">
        <i class="ph ph-person" aria-hidden="true"></i>
        <?= lang('Staff', 'Mitarbeitende') ?>
    </a>
    <!-- logo -->
    <a onclick="navigate('logo')" id="btn-logo" class="btn">
        <i class="ph ph-image" aria-hidden="true"></i>
        <?= lang('Logo', 'Logo') ?>
    </a>
    <!-- colors -->
    <a onclick="navigate('colors')" id="btn-colors" class="btn">
        <i class="ph ph-palette" aria-hidden="true"></i>
        <?= lang('Colors', 'Farben') ?>
    </a>
    <!-- email -->
    <a onclick="navigate('email')" id="btn-email" class="btn">
        <i class="ph ph-envelope" aria-hidden="true"></i>
        <?= lang('Email', 'E-Mail') ?>
    </a>
    <!-- export -->
    <!-- <a onclick="navigate('export')" id="btn-export" class="btn">
        <i class="ph ph-download" aria-hidden="true"></i>
        <?= lang('Export/Import', 'Export/Import') ?>
    </a> -->

</nav>


<section id="general">

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="box primary">


            <div class="content">
                <h2 class="title"><?= lang('General Settings', 'Allgemeine Einstellungen') ?></h2>

                <div class="form-group">
                    <label for="name" class="required "><?= lang('Start year', 'Startjahr') ?></label>
                    <input type="year" class="form-control" name="general[startyear]" required value="<?= $Settings->get('startyear') ?? '2022' ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The start year defines the beginning of many charts in OSIRIS. It is possible to add activities that occured befor that year though.',
                            'Das Startjahr bestimmt den Anfang vieler Abbildungen in OSIRIS. Man kann jedoch auch Aktivitäten hinzufügen, die vor dem Startjahr geschehen sind.'
                        ) ?>
                    </span>
                </div>
                <div class="form-group">
                    <label for="apikey"><?= lang('API-Key') ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="general[apikey]" id="apikey" value="<?= $Settings->get('apikey') ?>">

                        <div class="input-group-append">
                            <button type="button" class="btn" onclick="generateAPIkey()"><i class="ph ph-arrows-clockwise"></i> Generate</button>
                        </div>
                    </div>
                    <span class="text-danger">
                        <?= lang(
                            'If you do not provide an API key, the REST-API will be open to anyone.',
                            'Falls kein API-Key angegeben wird, ist die REST-API für jeden offen.'
                        ) ?>
                    </span>

                </div>
                <script>
                    function generateAPIkey() {
                        let length = 50;
                        let result = '';
                        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                        const charactersLength = characters.length;
                        let counter = 0;
                        while (counter < length) {
                            result += characters.charAt(Math.floor(Math.random() * charactersLength));
                            counter += 1;
                        }
                        $('#apikey').val(result)
                    }
                </script>
                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    Save
                </button>

            </div>
        </div>
    </form>

</section>


<section id="institute" style="display: none;">

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="box primary">


            <div class="content">
                <h2 class="title">Institut</h2>

                <div class="row row-eq-spacing">
                    <div class="col-sm-2">
                        <label for="icon" class="required">ID</label>
                        <input type="text" class="form-control" name="general[affiliation][id]" required value="<?= $affiliation['id'] ?>">
                    </div>
                    <div class="col-sm">
                        <label for="name" class="required ">Name</label>
                        <input type="text" class="form-control" name="general[affiliation][name]" required value="<?= $affiliation['name'] ?? '' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="link" class="required ">Link</label>
                        <input type="text" class="form-control" name="general[affiliation][link]" required value="<?= $affiliation['link'] ?? '' ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="openalex-id">
                        OpenAlex ID
                    </label>
                    <input type="text" class="form-control" name="general[affiliation][openalex]" value="<?= $affiliation['openalex'] ?? '' ?>">
                    <small class="text-primary">
                        <?= lang('Needed for OpenAlex imports!', 'Diese ID ist notwendig um OpenAlex-Importe zu ermöglichen!') ?>
                    </small>
                </div>
                <div class="row row-eq-spacing">
                    <div class="col-sm-2">
                        <label for="ror">ROR (inkl. URL)</label>
                        <input type="text" class="form-control" name="general[affiliation][ror]" value="<?= $affiliation['ror'] ?? 'https://ror.org/' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="location">Location</label>
                        <input type="text" class="form-control" name="general[affiliation][location]" value="<?= $affiliation['location'] ?? '' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="country">Country Code (2lttr)</label>
                        <input type="text" class="form-control" name="general[affiliation][country]" value="<?= $affiliation['country'] ?? 'DE' ?>">
                    </div>
                </div>
                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="lat">Latitude</label>
                        <input type="float" class="form-control" name="general[affiliation][lat]" value="<?= $affiliation['lat'] ?? '' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="lng">Longitude</label>
                        <input type="float" class="form-control" name="general[affiliation][lng]" value="<?= $affiliation['lng'] ?? '' ?>">
                    </div>
                </div>

                <button class="btn signal">
                    <i class="ph ph-floppy-disk"></i>
                    Save
                </button>
            </div>


        </div>
    </form>
</section>


<section id="staff" style="display: none;">

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
                        <td colspan="4">
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

</section>


<section id="logo" style="display: none;">

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post" enctype="multipart/form-data">

        <div class="box primary">
            <div class="content">
                <h2 class="title">Logo</h2>

                <b><?= lang('Current Logo', 'Derzeitiges Logo') ?>: <br></b>
                <div class="w-300 mw-full my-20">

                    <?= $Settings->printLogo("img-fluid") ?>
                </div>

                <div class="custom-file mb-20" id="file-input-div">
                    <input type="file" id="file-input" name="logo" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
                    <label for="file-input"><?= lang('Upload a new logo', 'Lade ein neues Logo hoch') ?></label>
                    <br><small class="text-danger">Max. 2 MB.</small>
                </div>


                <button class="btn signal">
                    <i class="ph ph-floppy-disk"></i>
                    Save
                </button>
            </div>
        </div>
    </form>
</section>


<section id="colors" style="display: none;">
    <!-- Color settings -->
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post" id="colors-form">
        <?php
        $colors = $Settings->get('colors');
        ?>

        <div class="box primary">

            <div class="content">
                <h2 class="title"><?= lang('Color Settings', 'Farbeinstellungen') ?></h2>

                <div class="form-group">
                    <label for="color"><?= lang('Primary Color', 'Primärfarbe') ?></label>
                    <input type="color" class="form-control" name="general[colors][primary]" value="<?= $colors['primary'] ?? '#008083' ?>" id="primary-color">
                    <span class="text-muted">
                        <?= lang(
                            'The primary color is used for the main elements of the website.',
                            'Die Primärfarbe wird für die Hauptelemente der Website verwendet.'
                        ) ?>
                    </span>
                </div>
                <div class="form-group">
                    <label for="color"><?= lang('Secondary Color', 'Sekundärfarbe') ?></label>
                    <input type="color" class="form-control" name="general[colors][secondary]" value="<?= $colors['secondary'] ?? '#f78104' ?>" id="secondary-color">
                    <span class="text-muted">
                        <?= lang(
                            'The secondary color is used for the secondary elements of the website.',
                            'Die Sekundärfarbe wird für die sekundären Elemente der Website verwendet.'
                        ) ?>
                    </span>
                </div>

                <!-- reset -->
                <button type="button" class="btn danger" onclick="resetColors()">
                    <i class="ph ph-trash"></i>
                    <?= lang('Reset to default colors', 'Setze Farben auf Standard zurück') ?>
                </button>

                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    Save
                </button>

                <script>
                    function resetColors() {
                        $('#primary-color').val('#008083');
                        $('#secondary-color').val('#f78104');
                    }
                </script>
            </div>
        </div>
    </form>
</section>


<section id="email" style="display: none;">

    <!-- Email settings -->
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="box primary">

            <div class="content">
                <h2 class="title"><?= lang('Email Settings', 'E-Mail Einstellungen') ?></h2>

                <div class="form-group">
                    <label for="email"><?= lang('Email address', 'E-Mail-Adresse') ?></label>
                    <input type="email" class="form-control" name="mail[email]" value="<?= $Settings->get('email') ?>">
                    <span class="text-muted">
                        <?= lang(
                            'This email address is used for sending notifications and as the default sender address.',
                            'Diese E-Mail-Adresse wird für Benachrichtigungen und als Standard-Absenderadresse verwendet.'
                        ) ?>

                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('SMTP Server', 'SMTP-Server') ?></label>
                    <input type="text" class="form-control" name="mail[smtp_server]" value="<?= $Settings->get('smtp_server') ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP server is used to send emails. If you do not provide a server, the default PHP mail function will be used.',
                            'Der SMTP-Server wird verwendet, um E-Mails zu senden. Falls kein Server angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('SMTP Port', 'SMTP-Port') ?></label>
                    <input type="number" class="form-control" name="mail[smtp_port]" value="<?= $Settings->get('smtp_port') ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP port is used to send emails. If you do not provide a port, the default PHP mail function will be used.',
                            'Der SMTP-Port wird verwendet, um E-Mails zu senden. Falls kein Port angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('SMTP User', 'SMTP-Benutzer') ?></label>
                    <input type="text" class="form-control" name="mail[smtp_user]" value="<?= $Settings->get('smtp_user') ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP user is used to authenticate the SMTP server. If you do not provide a user, the default PHP mail function will be used.',
                            'Der SMTP-Benutzer wird verwendet, um den SMTP-Server zu authentifizieren. Falls kein Benutzer angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('SMTP Password', 'SMTP-Passwort') ?></label>
                    <input type="password" class="form-control" name="mail[smtp_password]" value="<?= $Settings->get('smtp_password') ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP password is used to authenticate the SMTP server. If you do not provide a password, the default PHP mail function will be used.',
                            'Das SMTP-Passwort wird verwendet, um den SMTP-Server zu authentifizieren. Falls kein Passwort angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('SMTP Security', 'SMTP-Sicherheit') ?></label>
                    <select class="form-control" name="general[smtp_security]">
                        <option value="none" <?= $Settings->get('smtp_security') == 'none' ? 'selected' : '' ?>>None</option>
                        <option value="ssl" <?= $Settings->get('smtp_security') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="tls" <?= $Settings->get('smtp_security') == 'tls' ? 'selected' : '' ?>>TLS</option>
                    </select>
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP security is used to encrypt the connection to the SMTP server. If you do not provide a security, the default PHP mail function will be used.',
                            'Die SMTP-Sicherheit wird verwendet, um die Verbindung zum SMTP-Server zu verschlüsseln. Falls keine Sicherheit angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <button class="btn info">
                    <i class="ph ph-floppy-disk"></i>
                    Save
                </button>
            </div>
        </div>
    </form>

    <!-- Test Email Settings by sending a test mail -->
    <form action="<?= ROOTPATH ?>/crud/admin/mail-test" method="post">
        <div class="box primary">

            <div class="content">
                <h2 class="title"><?= lang('Test Email Settings', 'Teste E-Mail-Einstellungen') ?></h2>

                <div class="form-group">
                    <label for="email"><?= lang('Test Email address', 'Test-E-Mail-Adresse') ?></label>
                    <input type="email" class="form-control" name="email" required>
                    <span class="text-muted">
                        <?= lang(
                            'This email address is used to send a test email to check the email settings.',
                            'Diese E-Mail-Adresse wird verwendet, um eine Test-E-Mail zu senden und die E-Mail-Einstellungen zu überprüfen.'
                        ) ?>
                    </span>
                </div>

                <button class="btn info">
                    <i class="ph ph-mail-send"></i>
                    Send Test Email
                </button>
            </div>
        </div>
    </form>
</section>


<section id="export" style="display: none;">

    <!-- 
<div class="box primary">
    
    <div class="content">
            <h2 class="title"><?= lang('Export/Import Settings', 'Exportiere und importiere Einstellungen') ?></h2>

        <a href="<?= ROOTPATH ?>/settings.json" download='settings.json' class="btn"><?= lang('Download current settings', 'Lade aktuelle Einstellungen herunter') ?></a>
    </div>
    <hr>
    <div class="content">
        <form action="<?= ROOTPATH ?>/crud/admin/reset-settings" method="post" enctype="multipart/form-data">
            <div class="custom-file mb-20" id="settings-input-div">
                <input type="file" id="settings-input" name="settings" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
                <label for="settings-input"><?= lang('Upload settings (as JSON)', 'Lade Einstellungen hoch (als JSON)') ?></label>
            </div>
            <button class="btn danger">Upload & Replace</button>
        </form>
    </div>
    <hr>
    <div class="content">
        <form action="<?= ROOTPATH ?>/crud/admin/reset-settings" method="post">
            <button class="btn danger">
                <?= lang('Reset all settings to the default value.', 'Setze alle Einstellungen auf den Standardwert zurück.') ?>
            </button>
        </form>
    </div>

</div> -->
</section>