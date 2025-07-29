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

<?php include_once BASEPATH . '/header-editor.php'; ?>
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
    <!-- features -->
    <a onclick="navigate('features')" id="btn-features" class="btn">
        <i class="ph ph-wrench" aria-hidden="true"></i>
        <?= lang('Features', 'Funktionen') ?>
    </a>
    <!-- institute -->
    <a onclick="navigate('institute')" id="btn-institute" class="btn">
        <i class="ph ph-building" aria-hidden="true"></i>
        <?= lang('Institute', 'Institut') ?>
    </a>
    <!-- addons -->
    <!-- <a onclick="navigate('addons')" id="btn-addons" class="btn">
        <i class="ph ph-plug" aria-hidden="true"></i>
        <?= lang('Addons', 'Addons') ?>
    </a> -->
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
    <a onclick="navigate('custom-footer')" id="btn-custom-footer" class="btn">
        <i class="ph ph-scales" aria-hidden="true"></i>
        <?= lang('Footer contents', 'Inhalte im Footer') ?>
    </a>
    <!-- export -->
    <!-- <a onclick="navigate('export')" id="btn-export" class="btn">
        <i class="ph ph-download" aria-hidden="true"></i>
        <?= lang('Export/Import', 'Export/Import') ?>
    </a> -->
    <!-- countries -->
    <a onclick="navigate('countries')" id="btn-countries" class="btn">
        <i class="ph ph-globe" aria-hidden="true"></i>
        <?= lang('Countries', 'Länder') ?>
    </a>

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

                <div class="form-group">
                    <!-- affiliation formatting -->
                    <?php
                    $format = $Settings->get('affiliation_format', 'bold');
                    ?>

                    <label for="affiliation_format"><?= lang('Affiliated authors formatting', 'Formatierung der affilierten Autor:innen') ?></label>
                    <select class="form-control" name="general[affiliation_format]" id="affiliation_format">
                        <option value="bold" <?= $format == 'bold' ? 'selected' : '' ?>><?= lang('Bold (default)', 'Fett (Standard)') ?></option>
                        <option value="italic" <?= $format == 'italic' ? 'selected' : '' ?>><?= lang('Italic', 'Kursiv') ?></option>
                        <option value="underline" <?= $format == 'underline' ? 'selected' : '' ?>><?= lang('Underline', 'Unterstrichen') ?></option>
                        <option value="bold-italic" <?= $format == 'bold-italic' ? 'selected' : '' ?>><?= lang('Bold and italic', 'Fett und kursiv') ?></option>
                        <option value="bold-underline" <?= $format == 'bold-underline' ? 'selected' : '' ?>><?= lang('Bold and underline', 'Fett und unterstrichen') ?></option>
                        <option value="italic-underline" <?= $format == 'italic-underline' ? 'selected' : '' ?>><?= lang('Italic and underline', 'Kursiv und unterstrichen') ?></option>
                        <option value="none" <?= $format == 'none' ? 'selected' : '' ?>><?= lang('None', 'Keine') ?></option>
                    </select>

                    <p class="mt-5">
                        <b>
                            <i class="ph ph-warning"></i>
                            <?= lang('Hint:', 'Hinweis:') ?>
                        </b>
                        <?= lang('you have to rerender all activities to see the changes. You can do this here:', 'Du musst alle Aktivitäten neu rendern, um die Änderungen zu sehen. Du kannst dies hier tun:') ?>
                        <a href="<?= ROOTPATH ?>/rerender" class="btn small primary">
                            <?= lang('Render all activities', 'Alle Aktivitäten rendern') ?>
                        </a>
                        <?= lang('This might take a while. Please be patient and do not reload the page.', 'Das kann eine Weile dauern. Bitte sei geduldig und lade die Seite nicht neu.') ?>
                    </p>
                </div>


                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('Save', 'Speichern') ?>
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
                        <label for="icon" class="required"><?= lang('Abbreviation', 'Kürzel') ?></label>
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
                    <label for="regex">
                        Regular Expression (Regex) <?= lang('for affilation', 'für Affilierung') ?>
                    </label>
                    <input type="text" class="form-control" name="general[regex]" value="<?= $Settings->getRegex(); ?>" style="font-family: monospace;">
                    <small class="text-muted">
                        <?= lang('This pattern is used to match the affiliation in online repositories such as CrossRef. If you leave this empty, the institute abbreviation is used as is.', 'Dieses Muster wird verwendet, um die Zugehörigkeit in Online-Repositorien wie CrossRef abzugleichen. Wenn Sie dieses Feld leer lassen, wird die Institutsabkürzung unverändert verwendet.') ?>
                        <!-- hint -->
                        <br>
                        <?= lang('As a reference, see', 'Als Referenz, siehe') ?> <a href="https://regex101.com/" target="_blank" rel="noopener noreferrer">Regex101</a> <?= lang('with flavour JavaScript', 'mit Flavour JavaScript') ?>.
                    </small>
                </div>
                <div class="form-group">
                    <label for="openalex">
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

                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('Save', 'Speichern') ?>
                </button>
            </div>


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


                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('Save', 'Speichern') ?>
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


                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('Save', 'Speichern') ?>
                </button>
                <!-- reset -->
                <button type="button" class="btn" onclick="resetColors()">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                    <?= lang('Reset to default colors', 'Setze Farben auf Standard zurück') ?>
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
    <?php
    $mail = $Settings->get('mail');
    ?>

    <!-- Email settings -->
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="box primary">

            <div class="content">
                <h2 class="title"><?= lang('Email Settings', 'E-Mail Einstellungen') ?></h2>

                <div class="form-group">
                    <label for="email"><?= lang('Sender address', 'Absender-Adresse') ?></label>
                    <input type="email" class="form-control" name="mail[email]" value="<?= $mail['email'] ?? 'no-reply@osiris-app.de' ?>">
                    <span class="text-muted">
                        <?= lang(
                            'This email address is used for sending notifications and as the default sender address. Defaults to no-reply@osiris-app.de',
                            'Diese E-Mail-Adresse wird für Benachrichtigungen und als Standard-Absenderadresse verwendet. Standardeinstellung ist no-reply@osiris-app.de'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('SMTP Server', 'SMTP-Server') ?></label>
                    <input type="text" class="form-control" name="mail[smtp_server]" value="<?= $mail['smtp_server'] ?? '' ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP server is used to send emails. If you do not provide a server, the default PHP mail function will be used.',
                            'Der SMTP-Server wird verwendet, um E-Mails zu senden. Falls kein Server angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('Port', 'Port') ?></label>
                    <input type="number" class="form-control" name="mail[smtp_port]" value="<?= $mail['smtp_port'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('Username', 'Benutzername') ?></label>
                    <input type="text" class="form-control" name="mail[smtp_user]" value="<?= $mail['smtp_user'] ?? '' ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The SMTP user is used to authenticate the SMTP server. If you do not provide a user, the default PHP mail function will be used.',
                            'Der Benutzername wird verwendet, um den SMTP-Server zu authentifizieren. Falls kein Benutzername angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('Password', 'Passwort') ?></label>
                    <input type="password" class="form-control" name="mail[smtp_password]" value="<?= $mail['smtp_password'] ?? '' ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The password is used to authenticate the SMTP server. If you do not provide a password, the default PHP mail function will be used.',
                            'Das Passwort wird verwendet, um den SMTP-Server zu authentifizieren. Falls kein Passwort angegeben wird, wird die Standard-PHP-Mail-Funktion verwendet.'
                        ) ?>
                    </span>
                </div>

                <div class="form-group">
                    <label for="email"><?= lang('Security Protocol', 'Sicherheitsprotokoll') ?></label>
                    <select class="form-control" name="mail[smtp_security]">
                        <option value="none" <?= ($mail['smtp_security'] ?? '') == 'none' ? 'selected' : '' ?>>None</option>
                        <option value="ssl" <?= ($mail['smtp_security'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                        <option value="tls" <?= ($mail['smtp_security'] ?? '') == 'tls' ? 'selected' : '' ?>>TLS</option>
                    </select>
                    <span class="text-muted">
                        <?= lang(
                            'The security protocol is used to encrypt the connection to the server.',
                            'Das Sicherheitsprotokoll wird verwendet, um die Verbindung zum Server zu verschlüsseln.'
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


<section id="countries" style="display: none">

    <div class="box padded">

        <a href="<?= ROOTPATH ?>/migrate/countries" class="btn primary">
            <?= lang('Update countries list', 'Länderliste aktualisieren') ?>
        </a>
        <br>
        <br>

        <!-- Show countries list -->
        <h2 class="title"><?= lang('Countries', 'Länder') ?></h2>
        <p>
            <?= lang('Here you can see the list of countries that are used in OSIRIS.', 'Hier kannst du die Liste der Länder sehen, die in OSIRIS verwendet werden.') ?>
        </p>

        <ul class="list">

            <?php foreach ($osiris->countries->find() as $c) { ?>
                <li><?= lang($c['name'], $c['name_de']) ?> (<?= $c['iso'] ?>)</li>
            <?php } ?>

        </ul>

        <p class="text-signal">
            <i class="ph ph-info"></i>
            <?= lang('The list of world countries is provided by', 'Die Liste der Weltländer wird zur Verfügung gestellt von') ?>
            <a href="https://stefangabos.github.io/world_countries/" target="_blank" rel="noopener noreferrer" class="colorless">Stefan Gabos' World Country List</a>.
            <?= lang('Please click on the button above to update automatically.', 'Bitte click auf den Knopf oben, um die Liste automatisch zu aktualisieren.') ?>
        </p>

    </div>
</section>


<section id="features" style="display: none;">

    <style>
        .table td.description {
            color: var(--muted-color);
            padding-top: 0;
            padding-left: 2rem;
            padding-right: 2rem;
        }

        .with-description td {
            border-bottom: 0;
        }
    </style>

    <form action="<?= ROOTPATH ?>/crud/admin/features" method="post" id="role-form">

        <div class="row row-eq-spacing mt-0">
            <div class="col-md-9">

                <div class="box px-20">
                    <h3 id="quarterly-reporting">
                        <?= lang('Quarterly reporting', 'Quartalsweise Berichterstattung') ?>
                    </h3>
                    <div class="form-group">

                        <p>
                            <?= lang('OSIRIS reminds users every 3 months to update their activities and submit them for reporting. They can check the data on the "My year" page and confirm the quarter. The controlling dashboard then provides an overview of all those who have not yet updated their data.', 'OSIRIS erinnert Nutzende alle 3 Monate daran, ihre Aktivitäten zu aktualisieren und für die Berichterstattung zu übermitteln. Dabei können sie auf der Seite "Mein Jahr" die Daten überprüfen und dann das Quartal bestätigen. Im Controlling-Dashbord gibt es dann eine Übersicht über alle Personen, die ihre Daten noch nicht aktualisiert haben.') ?>
                            <br>
                            <?= lang('If you do not wish to use this function, you can deactivate it here. Reminders will then no longer be sent to users and there will no longer be an option to confirm the data on the "My year" page.', 'Wenn ihr diese Funktion nicht nutzen wollt, könnt ihr sie hier deaktivieren. Es wird dann keine Erinnerung mehr an die Nutzenden geschickt und in der Seite "Mein Jahr" gibt es keine Möglichkeit mehr, die Daten zu bestätigen.') ?>
                        </p>

                        <?php
                        $quarterly = $Settings->featureEnabled('quarterly-reporting', true);
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="quarterly-reporting-true" value="1" name="values[quarterly-reporting]" <?= $quarterly ? 'checked' : '' ?>>
                            <label for="quarterly-reporting-true">
                                <?= lang('Enabled', 'Aktiviert') ?>
                            </label>
                        </div>
                        <div class="custom-radio">
                            <input type="radio" id="quarterly-reporting-false" value="0" name="values[quarterly-reporting]" <?= $quarterly ? '' : 'checked' ?>>
                            <label for="quarterly-reporting-false">
                                <?= lang('Disabled', 'Deaktiviert') ?>
                            </label>
                        </div>
                    </div>
                </div>


                <div class="box px-20">
                    <h3 id="journal-metrics">
                        <?= lang('Journals', 'Journale') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Automatic retrieval of journal metrics', 'Automatischer Download von Journal-Metriken') ?>
                        </label>
                        <?php
                        $journals = $Settings->featureEnabled('no-journal-metrics');
                        ?>

                        <p>
                            <?= lang('Please note: the metrics are obtained from Scimago and are based on Scopus. If you want to obtain other impact factors and quartiles, you can switch off the automatic import. However, you will then have to maintain the data manually.', 'Bitte beachten: die Metriken werden von Scimago bezogen und richten sich nach Scopus. Wenn ihr andere Impact Faktoren und Quartile beziehen wollt, könnt ihr den automatischen Import ausschalten. Dann müsst ihr die Daten aber händisch pflegen.') ?>
                        </p>
                        <div class="custom-radio">
                            <input type="radio" id="no-journal-metrics-false" value="0" name="values[no-journal-metrics]" <?= $journals ? '' : 'checked' ?>>
                            <label for="no-journal-metrics-false"><?= lang('Retrieve metrics automatically', 'Metriken automatisch abrufen') ?></label>
                        </div>
                        <div class="custom-radio">
                            <input type="radio" id="no-journal-metrics-true" value="1" name="values[no-journal-metrics]" <?= $journals ? 'checked' : '' ?>>
                            <label for="no-journal-metrics-true"><?= lang('Disable automatic retrieval', 'Automatischen Abruf deaktivieren') ?></label>
                        </div>

                    </div>
                </div>

                
                <div class="box px-20">
                    <h3 id="quality-control">
                        <?= lang('Quality control of activities', 'Qualitätsprüfung von Aktivitäten') ?>
                    </h3>
                    <div class="form-group">

                        <p>
                              
                    </p>

                        <?php
                        $quarterly = $Settings->featureEnabled('quality-control', false);
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="quality-control-true" value="1" name="values[quality-control]" <?= $quarterly ? 'checked' : '' ?>>
                            <label for="quality-control-true">
                                <?= lang('Enabled', 'Aktiviert') ?>
                            </label>
                        </div>
                        <div class="custom-radio">
                            <input type="radio" id="quality-control-false" value="0" name="values[quality-control]" <?= $quarterly ? '' : 'checked' ?>>
                            <label for="quality-control-false">
                                <?= lang('Disabled', 'Deaktiviert') ?>
                            </label>
                        </div>
                    </div>
                </div>


                <div class="box px-20">
                    <h3 id="guest-forms">
                        <?= lang('Guests', 'Gäste') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Guests can be registered in OSIRIS', 'Gäste können in OSIRIS angemeldet werden') ?>
                        </label>
                        <?php
                        $guests = $Settings->featureEnabled('guests');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="guests-true" value="1" name="values[guests]" <?= $guests ? 'checked' : '' ?>>
                            <label for="guests-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="guests-false" value="0" name="values[guests]" <?= $guests ? '' : 'checked' ?>>
                            <label for="guests-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>


                    <div class="form-group">
                        <label for="">
                            <?= lang('External guest forms to complete registration', 'Externe Gästeformulare, um die Registration abzuschließen') ?>
                        </label>
                        <?php
                        $guests = $Settings->featureEnabled('guest-forms');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="guest-forms-true" value="1" name="values[guest-forms]" <?= $guests ? 'checked' : '' ?>>
                            <label for="guest-forms-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>
                        <div class="custom-radio">
                            <input type="radio" id="guest-forms-false" value="0" name="values[guest-forms]" <?= $guests ? '' : 'checked' ?>>
                            <label for="guest-forms-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>
                        <div class="row mt-10">
                            <label for="guest-forms-server" class="w-150 col flex-reset"><?= lang('Server address', 'Server-Adresse') ?></label>
                            <input type="text" class="form-control small col" name="general[guest-forms-server]" id="guest-forms-server" value="<?= $Settings->get('guest-forms-server') ?>">
                        </div>
                        <div class="row mt-10">
                            <label for="guest-forms-secret-key" class="w-150 col flex-reset"><?= lang('Secret key') ?></label>
                            <input type="text" class="form-control small col" name="general[guest-forms-secret-key]" id="guest-forms-secret-key" value="<?= $Settings->get('guest-forms-secret-key') ?>">
                        </div>

                    </div>


                    <div class="form-group">
                        <label for="">
                            <?= lang('Send emails for guests', 'Sende Emails wegen Gästen') ?>
                        </label>
                        <?php
                        $guest_mails = $Settings->featureEnabled('guest-mails');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="guest-mails-true" value="1" name="values[guest-mails]" <?= $guest_mails ? 'checked' : '' ?>>
                            <label for="guest-mails-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="guest-mails-false" value="0" name="values[guest-mails]" <?= $guest_mails ? '' : 'checked' ?>>
                            <label for="guest-mails-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                        <small>
                            <?= lang(
                                'Please note that this feature will only work if mail support is enabled and mail account is properly configured.',
                                'Bitte beachte, dass diese Funktion nur funktioniert, wenn die E-Mail-Unterstützung aktiviert ist und das E-Mail-Konto richtig konfiguriert ist.'
                            ) ?>
                        </small>

                        <!-- <h6 class="mb-0"><?= lang('Configure email addresses', 'Email-Adressen konfigurieren') ?></h6>
            <small class="text-muted">
                <?= lang('Enter multiple addresses separated by comma.', 'Du kannst mehrere Adressen durch Komma getrennt angeben.') ?>
            </small>
            <div class="row row-eq-spacing">

                <?php foreach (
                    [
                        'register' => lang('When a guest is registered by the supervisor', 'Bei Anmeldung eines Gastes durch den Betreuer'),
                        'completed' => lang('When the guest has completed the online registration', 'Wenn der Gast die Online-Registrierung abgeschlossen hat'),
                        'expiration' => lang('If the guest\'s stay was longer than 7 days and the time is about to expire', 'Wenn die Laufzeit des Gastes länger als 7 Tage war und die Zeit bald abläuft'),
                        'adjustment' => lang('If the guest is canceled or the period is adjusted', 'Wenn der Gast abgesagt oder der Zeitraum angepasst wird'),
                    ] as $key => $name
                ) { ?>
                    <div class="col-md-6">
                        <label for="guest-mails-<?= $key ?>"><?= $name ?></label>
                        <input type="text" class="form-control small" name="general[guest-mails-<?= $key ?>]" id="guest-mails-<?= $key ?>" value="<?= $Settings->get('guest-mails-' . $key) ?>">
                        <small><?= lang('en', 'Nur in Verbindung mit Gästeformularen') ?></small>
                        <div>
                            <?php
                            $sp = $Settings->get('guest-mails-' . $key . '-supervisor');
                            ?>
                            
                            <?= lang('Include supervisor', 'Betreuende Person einschließen') ?>:
                            <input type="radio" name="general[guest-mails-<?= $key ?>-supervisor]" value="true" id="guest-mails-<?= $key ?>-supervisor-1" <?= $sp ? 'checked' : '' ?>>
                            <label for="guest-mails-<?= $key ?>-supervisor-1"><?= lang('Yes', 'Ja') ?></label>

                            <input type="radio" name="general[guest-mails-<?= $key ?>-supervisor]" value="false" id="guest-mails-<?= $key ?>-supervisor-0" <?= !$sp ? 'checked' : '' ?>>
                            <label for="guest-mails-<?= $key ?>-supervisor-0"><?= lang('No', 'Nein') ?></label>

                        </div>
                    </div>
                <?php } ?>

            </div> -->

                    </div>
                </div>


                <div class="box px-20">
                    <h3>
                        <?= lang('Reporting', 'Berichterstattung') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('IDA Integration') ?>
                        </label>
                        <?php
                        $ida = $Settings->featureEnabled('ida');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="ida-true" value="1" name="values[ida]" <?= $ida ? 'checked' : '' ?>>
                            <label for="ida-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="ida-false" value="0" name="values[ida]" <?= $ida ? '' : 'checked' ?>>
                            <label for="ida-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>

                </div>


                <div class="box px-20">
                    <h3 id="projects">
                        <?= lang('Projects', 'Projekte') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Projects in OSIRIS', 'Projekte in OSIRIS') ?>
                        </label>
                        <?php
                        $projects = $Settings->featureEnabled('projects');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="projects-true" value="1" name="values[projects]" <?= $projects ? 'checked' : '' ?>>
                            <label for="projects-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="projects-false" value="0" name="values[projects]" <?= $projects ? '' : 'checked' ?>>
                            <label for="projects-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>

                </div>


                <div class="box px-20">
                    <h3 id="teaching-modules">
                        <?= lang('Teaching modules', 'Lehrveranstaltungen') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Show Teaching modules in Sidebar', 'Zeige Lehrveranstaltungen in der Seitennavigation') ?>
                        </label>
                        <?php
                        $teachingModules = $Settings->featureEnabled('teaching-modules', true);
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="teaching-modules-true" value="1" name="values[teaching-modules]" <?= $teachingModules ? 'checked' : '' ?>>
                            <label for="teaching-modules-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="teaching-modules-false" value="0" name="values[teaching-modules]" <?= $teachingModules ? '' : 'checked' ?>>
                            <label for="teaching-modules-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>

                </div>


                <div class="box px-20">
                    <h3 id="calendar">
                        <?= lang('Calendar', 'Kalender') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Show the calendar in Sidebar', 'Zeige den Kalender in der Seitennavigation') ?>
                        </label>
                        <?php
                        $teachingModules = $Settings->featureEnabled('calendar', false);
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="calendar-true" value="1" name="values[calendar]" <?= $teachingModules ? 'checked' : '' ?>>
                            <label for="calendar-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="calendar-false" value="0" name="values[calendar]" <?= $teachingModules ? '' : 'checked' ?>>
                            <label for="calendar-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>

                </div>




                <div class="box px-20">
                    <h3 id="research-topics">
                        <?= lang('Research Topics', 'Forschungsbereiche') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Research Topics in OSIRIS', 'Forschungsbereiche in OSIRIS') ?>
                        </label>
                        <?php
                        $topics = $Settings->featureEnabled('topics');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="topics-true" value="1" name="values[topics]" <?= $topics ? 'checked' : '' ?>>
                            <label for="topics-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="topics-false" value="0" name="values[topics]" <?= $topics ? '' : 'checked' ?>>
                            <label for="topics-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>


                    <div class="form-group">
                        <label for="position">
                            <h5><?= lang('Label', 'Bezeichnung') ?></h5>
                        </label>

                        <?php
                        $label = $Settings->get('topics_label');
                        ?>


                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <label for="topics_label" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                <input name="general[topics_label][en]" id="topics_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Research topics') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="topics_label_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                <input name="general[topics_label][de]" id="topics_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Forschungsbereiche') ?>">
                            </div>
                        </div>
                    </div>

                    <?php
                    $n_topics = $osiris->topics->count();
                    $list_fields = $osiris->adminFields->find(['format' => 'list'])->toArray();
                    if ($n_topics == 0 && count($list_fields) > 0) { ?>
                        <div class="mb-20">
                            <a href="#migrate-topics" class="btn">
                                <?= lang('Migrate custom fields to topics', 'Custom Fields in Bereiche migrieren') ?>
                            </a>
                        </div>
                    <?php } ?>

                </div>



                <div class="box px-20">
                    <h3 id="infrastructures">
                        <?= $Settings->infrastructureLabel() ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <h5><?= lang('Infrastructures in OSIRIS', 'Infrastrukturen in OSIRIS') ?></h5>
                        </label>
                        <?php
                        $infrastructures = $Settings->featureEnabled('infrastructures');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="infrastructures-true" value="1" name="values[infrastructures]" <?= $infrastructures ? 'checked' : '' ?>>
                            <label for="infrastructures-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="infrastructures-false" value="0" name="values[infrastructures]" <?= $infrastructures ? '' : 'checked' ?>>
                            <label for="infrastructures-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>

                    <div class="form-group">
                        <label for="position">
                            <h5><?= lang('Label', 'Bezeichnung') ?></h5>
                        </label>

                        <?php
                        $label = $Settings->get('infrastructures_label');
                        ?>

                        <div class="row row-eq-spacing my-0">
                            <div class="col-md-6">
                                <label for="infrastructures_label" class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                <input name="general[infrastructures_label][en]" id="infrastructures_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Infrastructures') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="infrastructures_label_de" class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                <input name="general[infrastructures_label][de]" id="infrastructures_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Infrastrukturen') ?>">
                            </div>
                        </div>
                    </div>

                </div>



                <div class="box px-20">
                    <h3 id="concepts">
                        <?= lang('Concepts', 'Konzepte') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Show concepts', 'Zeige Konzepte') ?>
                        </label>
                        <?php
                        $concepts = $Settings->featureEnabled('concepts');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="concepts-true" value="1" name="values[concepts]" <?= $concepts ? 'checked' : '' ?>>
                            <label for="concepts-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="concepts-false" value="0" name="values[concepts]" <?= $concepts ? '' : 'checked' ?>>
                            <label for="concepts-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>
                </div>


                <div class="box px-20">
                    <h3 id="wordcloud">
                        <?= lang('Word cloud') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Show word clouds in user profiles', 'Zeige Word Clouds in Nutzerprofilen') ?>
                        </label>
                        <?php
                        $wordcloud = $Settings->featureEnabled('wordcloud');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="wordcloud-true" value="1" name="values[wordcloud]" <?= $wordcloud ? 'checked' : '' ?>>
                            <label for="wordcloud-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="wordcloud-false" value="0" name="values[wordcloud]" <?= $wordcloud ? '' : 'checked' ?>>
                            <label for="wordcloud-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>


                </div>


                <div class="box px-20">
                    <h3 id="portal">
                        <?= lang('OSIRIS Portfolio') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Portal previews and API', 'Portal-Vorschau und API') ?>
                        </label>
                        <?php
                        $portal = $Settings->featureEnabled('portal');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="portal-true" value="1" name="values[portal]" <?= $portal ? 'checked' : '' ?>>
                            <label for="portal-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="portal-false" value="0" name="values[portal]" <?= $portal ? '' : 'checked' ?>>
                            <label for="portal-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>
                </div>


                <div class="box px-20">
                    <h3 id="nagoya">
                        <?= lang('Nagoya Protocol Compliance') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Add Nagoya Protocol Compliance to projects', 'Füge Nagoya-Protokoll Compliance zu Projekten hinzu') ?>
                        </label>
                        <?php
                        $nagoya = $Settings->featureEnabled('nagoya');
                        ?>

                        <div class="custom-radio">
                            <input type="radio" id="nagoya-true" value="1" name="values[nagoya]" <?= $nagoya ? 'checked' : '' ?>>
                            <label for="nagoya-true"><?= lang('enabled', 'aktiviert') ?></label>
                        </div>

                        <div class="custom-radio">
                            <input type="radio" id="nagoya-false" value="0" name="values[nagoya]" <?= $nagoya ? '' : 'checked' ?>>
                            <label for="nagoya-false"><?= lang('disabled', 'deaktiviert') ?></label>
                        </div>

                    </div>
                </div>


                <div class="box px-20">
                    <h3 id="trips">
                        <?= lang('Research Trips', 'Forschungsreisen') ?>
                    </h3>
                    <div class="form-group">
                        <label for="">
                            <?= lang('Enable a module for analysing research trips', 'Aktiviere ein Modul, das Forschungsreisen analysieren kann') ?>
                        </label>

                        <p class="text-muted">
                            <?= lang('The add-on requires an activity type called <kbd>travel</kbd> that has the following data fields: <code class="code">status</code> and either <code class="code">countries</code> or <code class="code">country</code>.', 'Dieses Add-on benötigt einen Aktivitätstypen, dessen ID <kbd>travel</kbd> ist und der mindestens die folgenden Datenfelder hat: <code class="code">status</code> und <code class="code">countries</code> oder <code class="code">country</code>.') ?>
                        </p>
                        <?php
                        $trips = $Settings->featureEnabled('trips');

                        $travel_available = $osiris->adminTypes->count(['id' => 'travel']);
                        $modules_available = $osiris->adminTypes->count(['modules' => ['$in' => ['status', 'countries', 'country', 'status*',  'countries*', 'country*']]]);

                        if ($travel_available == 0) { ?>
                            <p>
                                <i class="ph ph-warning text-danger"></i>
                                <?= lang('The activity type <kbd>travel</kbd> is not available. Please create it first.', 'Der Aktivitätstyp <kbd>travel</kbd> ist nicht verfügbar. Bitte erstelle ihn zuerst.') ?>
                            </p>
                        <?php } else if ($modules_available == 0) { ?>
                            <p>
                                <i class="ph ph-warning text-danger"></i>
                                <?= lang('The activity type <kbd>travel</kbd> does not have the required data fields. Please add them first.', 'Der Aktivitätstyp <kbd>travel</kbd> hat nicht die erforderlichen Datenfelder. Bitte füge sie zuerst hinzu.') ?>
                            </p>
                        <?php } else { ?>
                            <p>
                                <i class="ph ph-seal-check text-success"></i>
                                <?= lang('The module is available and can be activated here.', 'Das Modul ist verfügbar und kann hier aktiviert werden.') ?>
                            </p>

                            <div class="custom-radio">
                                <input type="radio" id="trips-true" value="1" name="values[trips]" <?= $trips ? 'checked' : '' ?>>
                                <label for="trips-true"><?= lang('enabled', 'aktiviert') ?></label>
                            </div>

                            <div class="custom-radio">
                                <input type="radio" id="trips-false" value="0" name="values[trips]" <?= $trips ? '' : 'checked' ?>>
                                <label for="trips-false"><?= lang('disabled', 'deaktiviert') ?></label>
                            </div>
                        <?php } ?>


                    </div>
                </div>


            </div>
            <div class="col-lg-3 d-none d-lg-block">
                <nav class="on-this-page-nav">
                    <div class="content">
                        <div class="title"><?= lang('Activities', 'Aktivitäten') ?></div>

                        <a href="#quarterly-reporting">
                            <?= lang('Quarterly reporting', 'Quartalsweise Berichterstattung') ?>
                        </a>
                        <a href="#journal-metrics">
                            <?= lang('Journals', 'Journale') ?>
                        </a>
                        <a href="#quality-control">
                            <?= lang('Quality control', 'Qualitätskontrolle') ?>
                        </a>
                        <a href="#guest-forms">
                            <?= lang('Guests', 'Gäste') ?>
                        </a>
                        <a href="#projects">
                            <?= lang('Projects', 'Projekte') ?>
                        </a>
                        <a href="#teaching-modules">
                            <?= lang('Teaching modules', 'Lehrveranstaltungen') ?>
                        </a>
                        <a href="#calendar">
                            <?= lang('Calendar', 'Kalender') ?>
                        </a>
                        <a href="#research-topics">
                            <?= lang('Research Topics', 'Forschungsbereiche') ?>
                        </a>
                        <a href="#infrastructures">
                            <?= $Settings->infrastructureLabel() ?>
                        </a>
                        <a href="#concepts">
                            <?= lang('Concepts', 'Konzepte') ?>
                        </a>
                        <a href="#wordcloud">
                            <?= lang('Word cloud', 'Word Cloud') ?>
                        </a>
                        <a href="#portal">
                            <?= lang('OSIRIS Portfolio', 'OSIRIS Portfolio') ?>
                        </a>
                        <a href="#nagoya">
                            <?= lang('Nagoya Protocol Compliance') ?>
                        </a>
                        <a href="#trips">
                            <?= lang('Research Trips', 'Forschungsreisen') ?>
                        </a>
                        </ul>
                    </div>

                    <button class="btn success large">
                        <i class="ph ph-floppy-disk"></i>
                        Save
                    </button>
                </nav>

            </div>

        </div>

    </form>




    <?php if ($n_topics == 0 && count($list_fields) > 0) { ?>

        <div class="modal" id="migrate-topics" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="modal-title">
                        <?= lang('Migrate custom fields to research topics', 'Benutzerdefinierte Felder in Forschungsbereiche migrieren') ?>
                    </h5>

                    <form action="<?= ROOTPATH ?>/migrate/custom-fields-to-topics" method="post">
                        <div class="form-group ">
                            <label for="field"><?= lang('Select a field you want to use', 'Wähle ein Custom Field, dass du migrieren willst') ?></label>

                            <select name="field" id="field" class="form-control">
                                <?php foreach ($list_fields as $field) { ?>
                                    <option value="<?= $field['id'] ?>"><?= lang($field['name'], $field['name_de'] ?? null) ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <?= lang('The following will happen if you click on migrate:', 'Wenn du auf migrieren klickst, wird das Folgende passieren:') ?>

                        <ul class="list">
                            <li>
                                <?= lang('The selected custom field is used to create new research areas on this basis. Don\'t worry, you can still edit them later.', 'Das ausgewählte Custom Field wird genommen, um auf dieser Grundlage neue Forschungsbereiche anzulegen. Keine Sorge, du kannst sie später noch bearbeiten.') ?>
                            </li>
                            <li>
                                <?= lang('All activities for which the custom field was completed are assigned to the respective research areas.', 'Alle Aktivitäten, bei denen das Custom Field ausgefüllt war, werden den jeweiligen Forschungsbereichen zugeordnet.') ?>
                            </li>
                            <li>
                                <?= lang('The custom field is then deleted, i.e. the field itself, the assignment to forms and the values set for the activities are removed.', 'Das Custom Field wird daraufhin gelöscht, d.h. das Feld selbst, die Zuordnung zu Formularen und die gesetzten Werte bei den Aktivitäten werden entfernt.') ?>
                            </li>
                        </ul>

                        <button class="btn primary">
                            <?= lang('Migrate', 'Migrieren') ?>
                        </button>
                    </form>

                </div>
            </div>
        </div>

    <?php } ?>

    <style>
        .box>.form-group>label {
            font-weight: bold;
            display: block;
            margin-bottom: 0;
        }

        .box .custom-radio {
            display: inline-block;
            margin-right: 1rem;
        }

        .box small {
            color: var(--muted-color);
            display: block;
        }
    </style>

</section>

<style>
    #custom-footer .ql-editor p, #custom-footer .ql-editor ol, #custom-footer .ql-editor ul, #custom-footer .ql-editor pre, #custom-footer .ql-editor blockquote, #custom-footer .ql-editor h1, #custom-footer .ql-editor h2, #custom-footer .ql-editor h3, #custom-footer .ql-editor h4, #custom-footer .ql-editor h5, #custom-footer .ql-editor h6 {
        margin-bottom: 1rem;
    }
</style>

<section id="custom-footer" style="display: none;">
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="box primary">
            <div class="content">
                <h2 class="title"><?= lang('Footer contents', 'Inhalte im Footer') ?></h2>
                <p>
                    <?= lang('You can add custom link to the footer of your OSIRIS installation and manage general contents such as legal notice and privacy policy. This will be displayed on every page at the bottom.', 'Du kannst benutzerdefinierte Links zum Footer deiner OSIRIS-Installation hinzufügen und allgemeine Inhalte wie Impressum und Datenschutzerklärung verwalten. Diese werden auf jeder Seite am unteren Rand angezeigt.') ?>
                </p>
            </div>
            <hr>
            <div class="content">
                <h3><?= lang('Legal Notice', 'Impressum') ?></h3>
                <?php
                $impress = $Settings->get('impress');
                if (empty($impress)) {
                    $impress = file_get_contents(BASEPATH . '/pages/impressum.html');
                }
                ?>
                <div class="form-group">
                    <div>
                        <div class="form-group title-editor" id="impress-quill"><?= $impress ?></div>
                        <textarea class="form-control hidden" name="general[impress]" id="impress"><?= htmlspecialchars($impress) ?></textarea>
                    </div>

                    <script>
                        quillEditor('impress');
                    </script>
                </div>
            </div>
            <hr>
            <div class="content">
                <h3><?= lang('Privacy Policy', 'Datenschutzerklärung') ?></h3>
                <?php
                $privacy = $Settings->get('privacy');
                if (empty($privacy)) {
                    $privacy = file_get_contents(BASEPATH . '/pages/privacy.html');
                }
                ?>
                <div class="form-group">
                    <div>
                        <div class="form-group title-editor" id="privacy-quill"><?= $privacy ?></div>
                        <textarea class="form-control hidden" name="general[privacy]" id="privacy"><?= htmlspecialchars($privacy) ?></textarea>
                    </div>
                    <script>
                        quillEditor('privacy');
                    </script>
                </div>
            </div>
            <hr>
            <div class="content">
                <h3><?= lang('Links', 'Links') ?></h3>
                <p>
                    <?= lang('You can add links to external resources that are relevant for your users. They will appear in the footer section <q>Links</q>.', 'Du kannst Links zu externen Ressourcen hinzufügen, die für deine Nutzer:innen relevant sind. Sie werden im Footer im Bereich <q>Links</q> angezeigt.') ?>
                </p>

                <?php
                $links = $Settings->get('footer_links', []);
                ?>
                <!-- make sure empty links are saved too -->
                <input type="hidden" name="footer_links" value="">
                <table class="table simple" id="footer-links-table">
                    <thead>
                        <tr>
                            <th><?= lang('Title (EN)', 'Titel (EN)') ?></th>
                            <th><?= lang('Title (DE)', 'Titel (DE)') ?></th>
                            <th><?= lang('Link URL (complete)', 'Link-URL (vollständig)') ?></th>
                            <th><?= lang('Actions', 'Aktionen') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($links as $link): ?>
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="footer_links[name][]" value="<?= htmlspecialchars($link['name'] ?? '') ?>" placeholder="<?= lang('Link Name (EN)', 'Link-Name (EN)') ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="footer_links[name_de][]" value="<?= htmlspecialchars($link['name_de'] ?? '') ?>" placeholder="<?= lang('Link Name (DE)', 'Link-Name (DE)') ?>">
                                </td>
                                <td>
                                    <input type="url" class="form-control" name="footer_links[url][]" value="<?= htmlspecialchars($link['url'] ?? '') ?>" placeholder="<?= lang('Link URL (complete)', 'Link-URL (vollständig)') ?>">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash" title="<?= lang('Delete', 'Löschen') ?>"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">
                                <button type="button" class="btn btn-primary" onclick="addLink()"><?= lang('Add new link', 'Neuen Link hinzufügen') ?></button>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <script>
                    function addLink() {
                        const tbody = $('#footer-links-table tbody');
                        const newRow = `
                            <tr>
                                <td><input type="text" class="form-control" name="footer_links[name][]" placeholder="<?= lang('Link Name (EN)', 'Link-Name (EN)') ?>"></td>
                                <td><input type="text" class="form-control" name="footer_links[name_de][]" placeholder="<?= lang('Link Name (DE)', 'Link-Name (DE)') ?>"></td>
                                <td><input type="url" class="form-control" name="footer_links[url][]" placeholder="<?= lang('Link URL (complete)', 'Link-URL (vollständig)') ?>"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash" title="<?= lang('Delete', 'Löschen') ?>"></i></button></td>
                            </tr>`;
                        tbody.append(newRow);
                    }
                </script>

            </div>
        </div>
        <button class="btn success large mt-20">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
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