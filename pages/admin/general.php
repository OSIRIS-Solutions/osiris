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
    <!-- portfolio -->
    <?php if ($Settings->featureEnabled('portal')) { ?>
        <a onclick="navigate('portfolio')" id="btn-portfolio" class="btn">
            <i class="ph ph-globe" aria-hidden="true"></i>
            <?= lang('Portfolio', 'Portfolio') ?>
        </a>
    <?php } ?>

    <!-- footer -->
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

                    <label for="affiliation_format"><?= lang('Affiliated authors formatting', 'Formatierung der affiliierten Autor:innen') ?></label>
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

                <hr>

                <h3 id="mail-digest">
                    <?= lang('Mail digest', 'E-Mail-Zusammenfassung') ?>
                </h3>

                <p>
                    <?= lang('Users can receive a daily, weekly or monthly email summary of their activities, depending on their settings. You can define the default mail digest frequency for them here.', 'Nutzende können eine tägliche, wöchentliche oder monatliche E-Mail-Zusammenfassung ihrer Aktivitäten erhalten, abhängig von ihren Einstellungen. Du kannst die standardmäßige E-Mail-Zusammenfassungsfrequenz für sie hier festlegen.') ?>
                </p>

                <p class="text-danger">
                    <i class="ph ph-warning"></i>
                    <?= lang('This setting requires additional configuration of a CRON job. Without this configuration, email digests will not be sent automatically.', 'Diese Einstellungen erfordern zusätzlich Konfiguration eines CRON-Jobs. Ohne diese Konfiguration werden die E-Mail-Zusammenfassungen nicht automatisch versendet.') ?>
                </p>

                <div class="form-group">
                    <?php
                    $digest = $Settings->get('mail-digest', 'none');
                    ?>

                    <div class="custom-radio">
                        <input type="radio" id="mail-digest-none" value="none" name="general[mail-digest]" <?= $digest == 'none' ? 'checked' : '' ?>>
                        <label for="mail-digest-none">
                            <?= lang('Disabled', 'Deaktiviert') ?>
                        </label>
                    </div>
                    <div class="custom-radio">
                        <input type="radio" id="mail-digest-daily" value="daily" name="general[mail-digest]" <?= $digest == 'daily' ? 'checked' : '' ?>>
                        <label for="mail-digest-daily">
                            <?= lang('Daily', 'Täglich') ?>
                        </label>
                    </div>
                    <div class="custom-radio">
                        <input type="radio" id="mail-digest-weekly" value="weekly" name="general[mail-digest]" <?= $digest == 'weekly' ? 'checked' : '' ?>>
                        <label for="mail-digest-weekly">
                            <?= lang('Weekly', 'Wöchentlich') ?>
                        </label>
                    </div>
                    <div class="custom-radio">
                        <input type="radio" id="mail-digest-monthly" value="monthly" name="general[mail-digest]" <?= $digest == 'monthly' ? 'checked' : '' ?>>
                        <label for="mail-digest-monthly">
                            <?= lang('Monthly', 'Monatlich') ?>
                        </label>
                    </div>
                    <small>
                        <?= lang('Note: Users can change their mail digest frequency in their profile settings. The default setting here is only used for new users and as a fallback if the user has not set a preference.', 'Hinweis: Nutzende können ihre E-Mail-Zusammenfassungsfrequenz in ihren Profileinstellungen ändern. Die hier festgelegte Standardeinstellung wird nur für neue Nutzende und als Fallback verwendet, wenn der Nutzende keine Präferenz festgelegt hat.') ?>
                    </small>
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


<section id="portfolio" style="display: none;">

    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <div class="box primary">

            <div class="content">
                <h2 class="title"><?= lang('Portfolio Settings', 'Portfolio Einstellungen') ?></h2>

                <?php if ($Settings->featureEnabled('quality-workflow')) { ?>
                    <?= lang('You can specify here, if only workflow-approved activities should be shown in the portfolio.', 'Hier kannst du festlegen, ob nur workflow-genehmigte Aktivitäten im Portfolio angezeigt werden sollen.') ?>
                <?php } ?>

                <div class="form-group">
                    <?php
                    $portfolio = $Settings->get('portfolio-workflow-visibility', 'all');
                    ?>

                    <div class="custom-radio">
                        <input type="radio" id="portfolio-workflow-visibility-approved" value="only-approved" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'only-approved' ? 'checked' : '' ?>>
                        <label for="portfolio-workflow-visibility-approved">
                            <?= lang('Only approved activities', 'Nur genehmigte Aktivitäten') ?>
                        </label>
                    </div>

                    <div class="custom-radio">
                        <input type="radio" id="portfolio-workflow-visibility-approved-or-empty" value="approved-or-empty" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'approved-or-empty' ? 'checked' : '' ?>>
                        <label for="portfolio-workflow-visibility-approved-or-empty">
                            <?= lang('Approved activities and activities without workflow', 'Genehmigte Aktivitäten und Aktivitäten ohne Workflow') ?>
                        </label>
                    </div>

                    <div class="custom-radio">
                        <input type="radio" id="portfolio-workflow-visibility-all" value="all" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'all' ? 'checked' : '' ?>>
                        <label for="portfolio-workflow-visibility-all">
                            <?= lang('All activities', 'Alle Aktivitäten') ?>
                        </label>
                    </div>
                </div>

                <h5>
                    <?= lang('Portfolio-API Key') ?>
                </h5>
                <div class="form-group">
                    <input type="text" class="form-control" name="general[portfolio_apikey]" value="<?= $Settings->get('portfolio_apikey') ?>">
                    <span class="text-muted">
                        <?= lang(
                            'The portfolio API key is used to authenticate the portfolio API. If you do not provide an API key, the portfolio API will be open to anyone.',
                            'Der Portfolio-API-Schlüssel wird verwendet, um die Portfolio-API zu authentifizieren. Falls kein API-Schlüssel angegeben wird, ist die Portfolio-API für jeden offen.'
                        ) ?>
                    </span>
                </div>

                <h5>
                    <?= lang('Generally visible activity types', 'Allgemein sichtbare Aktivitätstypen') ?>
                </h5>

                <ul class="list">
                    <?php foreach ($osiris->adminTypes->find(['portfolio' => 1], ['sort' => ['parent' => 1, 'order' => 1]]) as $type) { ?>
                        <li>
                            <i class="ph ph-<?= $type['icon'] ?> ph-fw text-<?= $type['parent'] ?>"></i>
                            <?= lang($type['name'], $type['name_de']) ?>
                        </li>
                    <?php } ?>
                </ul>
                <p class="text-muted">
                    <?= lang('The activity types listed above are generally visible in the portfolio. You can manage the activity types in the', 'Die oben aufgeführten Aktivitätstypen sind generell im Portfolio sichtbar. Du kannst die Aktivitätstypen im') ?>
                    <a href="<?= ROOTPATH ?>/admin/categories" class="colorless text-decoration-underline">
                        <?= lang('activity types settings', 'Einstellungen der Aktivitätstypen') ?>
                    </a>.
                </p>

                <button class="btn primary">
                    <i class="ph ph-floppy-disk"></i>
                    <?= lang('Save', 'Speichern') ?>
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
        <?php
        function renderCheckbox($feature, $default = false)
        {
            global $Settings;
            $enabled = $Settings->featureEnabled($feature, $default);
        ?>
            <div class="custom-radio">
                <input type="radio" id="<?= $feature ?>-true" value="1" name="values[<?= $feature ?>]" <?= $enabled ? 'checked' : '' ?>>
                <label for="<?= $feature ?>-true">
                    <?= lang('Enabled', 'Aktiviert') ?>
                </label>
            </div>
            <div class="custom-radio">
                <input type="radio" id="<?= $feature ?>-false" value="0" name="values[<?= $feature ?>]" <?= $enabled ? '' : 'checked' ?>>
                <label for="<?= $feature ?>-false">
                    <?= lang('Disabled', 'Deaktiviert') ?>
                </label>
            </div>
        <?php
        }

        function badgeDeprecated()
        { ?>
            <span class="badge danger" data-toggle="tooltip" data-title="<?= lang('This feature is deprecated and is currently not maintained.', 'Diese Funktion ist veraltet und wird aktuell nicht gepflegt.') ?>">
                <i class="ph ph-warning"></i>
                <?= lang('Deprecated', 'Veraltet') ?>
            </span>
        <?php
        }

        function badgeBeta()
        { ?>
            <span class="badge signal" data-toggle="tooltip" data-title="<?= lang('This is a beta feature and may not work as expected. Use at your own risk.', 'Dies ist eine Beta-Funktion und funktioniert möglicherweise nicht wie erwartet. Nutzung auf eigene Gefahr.') ?>">
                <i class="ph ph-flask"></i>
                <?= lang('Beta', 'Beta') ?>
            </span>
        <?php
        }
        ?>

        <style>
            #features-settings-page label.label {
                font-weight: bold;
                display: block;
            }

            #features-settings-page .on-this-page-nav a {
                padding-left: 1rem;
            }

            #features-settings-page .on-this-page-nav a.submenu {
                font-size: 1.2rem;
                padding-top: 0;
                padding-left: 3rem;
            }

            #features-settings-page p.description {
                font-size: 1.2rem;
                color: var(--muted-color-dark);
            }
        </style>
        <div class="row row-eq-spacing mt-0" id="features-settings-page">
            <div class="col-md-9">

                <!-- Core Features Section -->

                <div class="box" id="core-features">
                    <h3 class="header">
                        <?= lang('Core Features', 'Kernfunktionen') ?>
                    </h3>

                    <div class="content">
                        <h4 id="portal">
                            <?= lang('OSIRIS Portfolio') ?>
                        </h4>

                        <p class="description">
                            <?= lang('The OSIRIS Portfolio is a public-facing website that showcases the research activities of your institute. If you enable Portfolio here, you will be able to manage public visibility settings of user profiles, activities and more. Furthermore you enable the Portfolio-API, which will deliver only selected information.', 'Das OSIRIS-Portfolio ist eine öffentlich zugängliche Website, die die Forschungsaktivitäten deines Instituts präsentiert. Wenn du das Portfolio hier aktivierst, kannst du die Sichtbarkeitseinstellungen von Nutzerprofilen, Aktivitäten und mehr verwalten. Außerdem wird die Portfolio-API aktiviert, die nur die ausgewählten Informationen bereitstellt.') ?>
                        </p>

                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Portfolio previews and API', 'Portfolio-Vorschau und API') ?>
                            </label>
                            <?php
                            renderCheckbox('portal');
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="projects">
                            <?= lang('Projects and Proposals', 'Projekte und Anträge') ?>
                        </h4>

                        <p class="description">
                            <?= lang('OSIRIS is able to manage complete project life cycles, from proposal submission to project reporting. By enabling this feature, you can create and manage projects and proposals within OSIRIS. It is possible to define your own project types and manage data fields.', 'OSIRIS kann komplette Projektlebenszyklen verwalten, von der Antragstellung bis zum Projektbericht. Durch die Aktivierung dieser Funktion kannst du Projekte und Anträge innerhalb von OSIRIS erstellen und verwalten. Es ist möglich, eigene Projekttypen zu definieren und Datenfelder zu verwalten.') ?>
                        </p>

                        <div class="form-group">
                            <?php
                            renderCheckbox('projects');
                            ?>
                        </div>

                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Add Nagoya Protocol Compliance to proposals', 'Füge Nagoya-Protokoll Compliance zu Anträgen hinzu') ?>
                            </label>
                            <?php
                            renderCheckbox('nagoya');
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="teaching-modules">
                            <?= lang('Teaching modules', 'Lehrveranstaltungen') ?>
                        </h4>
                        <p class="description">
                            <?= lang('It is possible to centrally manage teaching modules (e.g. at universities) and add them to activities, such as lectures or seminars. By enabling this feature, you can create and manage teaching modules within OSIRIS. To use teaching modules within activities, use the teaching module datafield.', 'Es ist möglich, Lehrveranstaltungen (z.B. an Universitäten) zentral zu verwalten und sie Aktivitäten wie Vorlesungen oder Seminaren hinzuzufügen. Durch die Aktivierung dieser Funktion kannst du Lehrveranstaltungen innerhalb von OSIRIS erstellen und verwalten. Um Lehrveranstaltungen in Aktivitäten zu verwenden, nutze das Datenfeld für Lehrveranstaltungen.') ?>
                        </p>
                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Show Teaching modules in Sidebar', 'Zeige Lehrveranstaltungen in der Seitennavigation') ?>
                            </label>
                            <?php
                            renderCheckbox('teaching-modules', true);
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="research-topics">
                            <?= lang('Research Topics', 'Forschungsbereiche') ?>
                        </h4>
                        <div class="form-group">
                            <?php
                            renderCheckbox('topics');
                            ?>
                        </div>
                        <div class="form-group">
                            <?php
                            $label = $Settings->get('topics_label');
                            ?>
                            <div class="row row-eq-spacing my-0">
                                <div class="col-md-6">
                                    <label for="topics_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                    <input name="general[topics_label][en]" id="topics_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Research topics') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="topics_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
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
                    <hr>
                    <div class="content">
                        <h4 id="infrastructures">
                            <?= lang('Infrastructures in OSIRIS', 'Infrastrukturen in OSIRIS') ?>
                        </h4>
                        <div class="form-group">
                            <?php
                            renderCheckbox('infrastructures');
                            ?>
                        </div>
                        <div class="form-group">
                            <?php
                            $label = $Settings->get('infrastructures_label');
                            ?>

                            <div class="row row-eq-spacing my-0">
                                <div class="col-md-6">
                                    <label for="infrastructures_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                    <input name="general[infrastructures_label][en]" id="infrastructures_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Infrastructures') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="infrastructures_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                    <input name="general[infrastructures_label][de]" id="infrastructures_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Infrastrukturen') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="calendar">
                            <?= lang('Calendar and Events', 'Kalender und Events') ?>
                        </h4>
                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Enable central event management', 'Aktiviere das zentrale Event-Management') ?>
                            </label>
                            <?php
                            renderCheckbox('events', true);
                            ?>
                        </div>
                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Show the calendar in Sidebar', 'Zeige den Kalender in der Seitennavigation') ?>
                            </label>
                            <?php
                            renderCheckbox('calendar', false);
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="tags">
                            <?= lang('Tags', 'Schlagwörter') ?>
                        </h4>
                        <p class="description">
                            <?= lang('Tags can be used to label and categorize activities, projects and events. By enabling this feature, you can create and manage tags within OSIRIS. Once activated, you can manage tags in the content section of the admin panel.', 'Schlagwörter können verwendet werden, um Aktivitäten, Projekte und Events zu kennzeichnen und zu kategorisieren. Durch die Aktivierung dieser Funktion kannst du Schlagwörter innerhalb von OSIRIS erstellen und verwalten. Nach der Aktivierung kannst du Schlagwörter im Inhalte-Bereich des Admin-Panels verwalten.') ?>
                        </p>
                        <div class="form-group">
                            <?php
                            renderCheckbox('tags');
                            ?>
                        </div>

                        <div class="form-group">
                            <?php
                            $label = $Settings->get('tags_label');
                            ?>
                            <div class="row row-eq-spacing my-0">
                                <div class="col-md-6">
                                    <label for="tags_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                    <input name="general[tags_label][en]" id="tags_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Tags') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="tags_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                    <input name="general[tags_label][de]" id="tags_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Schlagwörter') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="trips">
                            <?= lang('Research Trips', 'Forschungsreisen') ?>
                        </h4>
                        <div class="form-group">
                            <label for="" class="label">
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
                    <hr>
                    <div class="content">
                        <h4 id="wordcloud">
                            <?= lang('Word Clouds', 'Word Clouds') ?>
                        </h4>
                        <div class="form-group">
                            <?php
                            renderCheckbox('wordcloud');
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Reporting & Quality Features Section -->

                <div class="box" id="reporting-quality-features">
                    <h3 class="header">
                        <?= lang('Reporting & Quality', 'Reporting & Qualität') ?>
                    </h3>
                    <div class="content">
                        <h4 id="quarterly-reporting">
                            <?= lang('Quarterly reporting', 'Quartalsweise Berichterstattung') ?>
                        </h4>
                        <div class="form-group">

                            <p class="description">
                                <?= lang('OSIRIS reminds users every 3 months to update their activities and submit them for reporting. They can check the data on the "My year" page and confirm the quarter. The controlling dashboard then provides an overview of all those who have not yet updated their data.', 'OSIRIS erinnert Nutzende alle 3 Monate daran, ihre Aktivitäten zu aktualisieren und für die Berichterstattung zu übermitteln. Dabei können sie auf der Seite "Mein Jahr" die Daten überprüfen und dann das Quartal bestätigen. Im Controlling-Dashbord gibt es dann eine Übersicht über alle Personen, die ihre Daten noch nicht aktualisiert haben.') ?>
                                <br>
                                <?= lang('If you do not wish to use this function, you can deactivate it here. Reminders will then no longer be sent to users and there will no longer be an option to confirm the data on the "My year" page.', 'Wenn ihr diese Funktion nicht nutzen wollt, könnt ihr sie hier deaktivieren. Es wird dann keine Erinnerung mehr an die Nutzenden geschickt und in der Seite "Mein Jahr" gibt es keine Möglichkeit mehr, die Daten zu bestätigen.') ?>
                            </p>

                            <?php
                            renderCheckbox('quarterly-reporting', true);
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="quality-workflow">
                            <?= lang('Quality workflows of activities', 'Qualitäts-Workflows von Aktivitäten') ?>
                        </h4>
                        <div class="form-group">
                            <p class="description">
                                <?= lang('You can enable a quality workflow for activities. This means that users can submit their activities for review and an admin or editor can approve or reject them. This is useful if you want to ensure that only verified activities are visible in the system.', 'Du kannst einen Qualitäts-Workflow für Aktivitäten aktivieren. Das bedeutet, dass Nutzende ihre Aktivitäten zur Überprüfung einreichen können und ein Admin oder Editor diese dann genehmigen oder ablehnen kann. Das ist nützlich, wenn du sicherstellen möchtest, dass nur verifizierte Aktivitäten im System sichtbar sind.') ?>
                            </p>
                            <?php
                            renderCheckbox('quality-workflow', false);
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="journal-metrics">
                            <?= lang('Journals', 'Journale') ?>
                        </h4>
                         <div class="form-group">
                            <?php
                            $label = $Settings->get('journals_label');
                            ?>

                            <div class="row row-eq-spacing my-0">
                                <div class="col-md-6">
                                    <label for="journals_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                                    <input name="general[journals_label][en]" id="journals_label" type="text" class="form-control" value="<?= htmlspecialchars($label['en'] ?? 'Journals') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="journals_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                                    <input name="general[journals_label][de]" id="journals_label_de" type="text" class="form-control" value="<?= htmlspecialchars($label['de'] ?? 'Journale') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Disable automatic retrieval of journal metrics', 'Verhindere den automatischen Download von Journal-Metriken') ?>
                            </label>
                            <?php
                            renderCheckbox('no-journal-metrics', false);
                            ?>
                            <p class="description">
                                <?= lang('Please note: the metrics are obtained from Scimago and are based on Scopus. If you want to obtain other impact factors and quartiles, you can switch off the automatic import. However, you will then have to maintain the data manually.', 'Bitte beachten: die Metriken werden von Scimago bezogen und richten sich nach Scopus. Wenn ihr andere Impact Faktoren und Quartile beziehen wollt, könnt ihr den automatischen Import ausschalten. Dann müsst ihr die Daten aber händisch pflegen.') ?>
                            </p>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="drafts">
                            <?= lang('Drafts', 'Entwürfe') ?>
                        </h4>
                        <div class="form-group">
                            <p class="description">
                                <?= lang('You can enable drafts for activities. This means that users can save their activities as drafts and complete them later.', 'Du kannst Entwürfe für Aktivitäten aktivieren. Das bedeutet, dass Nutzende ihre Aktivitäten als Entwürfe speichern und später vervollständigen können. ') ?>
                            </p>
                            <?php
                            renderCheckbox('drafts', false);
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4>
                            <?= lang('IDA Integration', 'IDA-Integration') ?>
                        </h4>
                        <?= badgeDeprecated() ?>
                        <p class="description">
                            <?= lang('IDA is an information system for data collection and evaluation used by the Leibniz Association. In theory, OSIRIS has an interface to IDA, but due to frequent changes to the IDA API, it does not function reliably and is no longer maintained. If the pact query stabilizes over several years, we will resume maintenance of the interface.', 'IDA ist ein Informationssystem zur Datenerfassung und Auswertung der Leibniz-Gemeinschaft. Theoretisch hat OSIRIS eine Schnittstelle zu IDA, die jedoch aufgrund der häufigen Änderungen der IDA-API nicht zuverlässig funktioniert und auch nicht mehr gepflegt wird. Sollte sich die Paktabfrage über mehrere Jahre stabilisieren, werden wir die Schnittstelle wieder pflegen.') ?>
                        </p>
                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Enable integration with the IDA tool', 'Aktiviere die Integration mit dem IDA-Tool') ?>
                            </label>

                            <?php
                            renderCheckbox('ida');
                            ?>
                        </div>
                    </div>
                </div>


                <!-- Imports & External Features Section -->

                <div class="box" id="imports-external-features">
                    <h3 class="header">
                        <?= lang('Imports & External Features', 'Importe & Externe Funktionen') ?>
                    </h3>
                    <div class="content">
                        <h4 id="imports">
                            <?= lang('Imports', 'Importe') ?>
                        </h4>
                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Allow user import from Google Scholar', 'Import von Nutzerdaten aus Google Scholar erlauben') ?>
                            </label>
                            <?php
                            renderCheckbox('googlescholar', true);
                            ?>
                        </div>


                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('Allow user import from OpenAlex', 'Import von Nutzerdaten aus OpenAlex erlauben') ?>
                            </label>
                            <?php
                            renderCheckbox('openalex', true);
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="concepts">
                            <?= lang('Concepts', 'Konzepte') ?>
                        </h4>

                        <?= badgeDeprecated() ?>

                        <p class="description">
                            <?= lang('Concepts originate from OpenAlex and are a way to semantically describe research topics. However, they have been recently deprecated in favor of Topics in OpenAlex. We plan to further develop the Topics feature in OSIRIS in the future. Read more about Concepts in OpenAlex <a href="https://docs.openalex.org/api-entities/topics" target="_blank" rel="noopener noreferrer" class="colorless text-decoration-underline">here</a>.', 'Konzepte stammen aus OpenAlex und sind eine Möglichkeit, Forschungsthemen semantisch zu beschreiben. Sie wurden jedoch kürzlich zugunsten von Themen in OpenAlex veraltet. Wir planen, die Themen-Funktion in OSIRIS in Zukunft weiterzuentwickeln. Mehr über Konzepte in OpenAlex erfährst du <a href="https://docs.openalex.org/api-entities/topics" target="_blank" rel="noopener noreferrer" class="colorless text-decoration-underline">hier</a>.') ?>
                        </p>

                        <div class="form-group">
                            <?php
                            renderCheckbox('concepts');
                            ?>
                        </div>
                    </div>
                </div>



                <div class="box" id="guest-management-features">
                    <h3 class="header">
                        <?= lang('People and Guests', 'Personen und Gäste') ?>
                    </h3>

                    <div class="content">
                        <h4 id="new-colleagues">
                            <?= lang('New Colleagues', 'Neue Kolleg:innen') ?>
                        </h4>

                        <div class="form-group mt-10">
                            <label for="" class="label">
                                <?= lang('Show new colleagues in the news section of peoples profile page', 'Zeige neue Kolleg:innen im News-Bereich der Personen-Profilseite') ?>
                            </label>
                            <?php
                            renderCheckbox('new-colleagues');
                            ?>
                        </div>
                    </div>
                    <hr>
                    <div class="content">
                        <h4 id="guest-forms">
                            <?= lang('Guest forms', 'Gästeformulare') ?>
                        </h4>

                        <?= badgeBeta() ?>

                        <div class="form-group mt-10">
                            <label for="" class="label">
                                <?= lang('Guests can be registered in OSIRIS', 'Gäste können in OSIRIS angemeldet werden') ?>
                            </label>
                            <?php
                            renderCheckbox('guests');
                            ?>
                        </div>


                        <div class="form-group">
                            <label for="" class="label">
                                <?= lang('External guest forms to complete registration', 'Externe Gästeformulare, um die Registration abzuschließen') ?>
                            </label>
                            <?php
                            renderCheckbox('guest-forms');
                            ?>

                            <div class="row mt-10">
                                <label for="guest-forms-server" class="w-150 col flex-reset"><?= lang('Server address', 'Server-Adresse') ?></label>
                                <input type="text" class="form-control small col" name="general[guest-forms-server]" id="guest-forms-server" value="<?= $Settings->get('guest-forms-server') ?>">
                            </div>
                            <div class="row mt-10">
                                <label for="guest-forms-secret-key" class="w-150 col flex-reset"><?= lang('Secret key') ?></label>
                                <input type="text" class="form-control small col" name="general[guest-forms-secret-key]" id="guest-forms-secret-key" value="<?= $Settings->get('guest-forms-secret-key') ?>">
                            </div>

                        </div>

                    </div>
                </div>
            </div>


            <div class="col-lg-3 d-none d-lg-block">
                <nav class="on-this-page-nav">
                    <div class="content">
                        <div class="title"><?= lang('Features', 'Funktionen') ?></div>

                        <a href="#core-features"><?= lang('Core Features', 'Kernfunktionen') ?></a>
                        <a href="#portal" class="submenu"><?= lang('OSIRIS Portfolio') ?></a>
                        <a href="#projects" class="submenu"><?= lang('Projects and Proposals', 'Projekte und Anträge') ?></a>
                        <a href="#teaching-modules" class="submenu"><?= lang('Teaching modules', 'Lehrveranstaltungen') ?></a>
                        <a href="#research-topics" class="submenu"><?= lang('Research Topics', 'Forschungsbereiche') ?></a>
                        <a href="#infrastructures" class="submenu"><?= lang('Infrastructures', 'Infrastrukturen') ?></a>
                        <a href="#calendar" class="submenu"><?= lang('Calendar and Events', 'Kalender und Events') ?></a>
                        <a href="#tags" class="submenu"><?= lang('Tags', 'Schlagwörter') ?></a>
                        <a href="#trips" class="submenu"><?= lang('Research Trips', 'Forschungsreisen') ?></a>
                        <a href="#wordcloud" class="submenu"><?= lang('Word Clouds', 'Word Clouds') ?></a>

                        <a href="#reporting-quality-features"><?= lang('Reporting & Quality', 'Reporting & Qualität') ?></a>
                        <a href="#quarterly-reporting" class="submenu"><?= lang('Quarterly reporting', 'Quartalsweise Berichterstattung') ?></a>
                        <a href="#quality-workflow" class="submenu"><?= lang('Quality workflows', 'Qualitäts-Workflows') ?></a>
                        <a href="#journal-metrics" class="submenu"><?= lang('Journals', 'Journale') ?></a>
                        <a href="#drafts" class="submenu"><?= lang('Drafts', 'Entwürfe') ?></a>
                        <a href="#ida" class="submenu"><?= lang('IDA Integration', 'IDA-Integration') ?></a>

                        <a href="#imports-external-features"><?= lang('Imports & External Features', 'Importe & Externe Funktionen') ?></a>
                        <a href="#imports" class="submenu"><?= lang('Imports', 'Importe') ?></a>
                        <a href="#concepts" class="submenu"><?= lang('Concepts', 'Konzepte') ?></a>

                        <a href="#guest-management-features"><?= lang('People and Guests', 'Personen und Gäste') ?></a>
                        <a href="#new-colleagues" class="submenu"><?= lang('New Colleagues', 'Neue Kolleg:innen') ?></a>
                        <a href="#guest-forms" class="submenu"><?= lang('Guest forms', 'Gästeformulare') ?></a>
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
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#!">
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
    #custom-footer .ql-editor p,
    #custom-footer .ql-editor ol,
    #custom-footer .ql-editor ul,
    #custom-footer .ql-editor pre,
    #custom-footer .ql-editor blockquote,
    #custom-footer .ql-editor h1,
    #custom-footer .ql-editor h2,
    #custom-footer .ql-editor h3,
    #custom-footer .ql-editor h4,
    #custom-footer .ql-editor h5,
    #custom-footer .ql-editor h6 {
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