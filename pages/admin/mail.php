<?php

/**
 * Manage email settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$mail = $Settings->get('mail');
?>

<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-envelope"></i>
        <?= lang('Email Settings', 'E-Mail Einstellungen') ?>
    </h1>

    <!-- Email settings -->
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/mail">

        <div class="form-group">
            <label for="email"><?= lang('Sender address', 'Absender-Adresse') ?></label>
            <input type="email" class="form-control" name="mail[email]" value="<?= $mail['email'] ?? 'no-reply@osiris-app.de' ?>">
            <span class="text-muted">
                <?= lang(
                    'This email address is used as the default sender address.',
                    'Diese E-Mail-Adresse wird als Standard-Absenderadresse verwendet.'
                ) ?>
            </span>
        </div>

        <div class="row row-eq-spacing">
            <div class="col-sm">
                <label for="email"><?= lang('SMTP Server', 'SMTP-Server') ?></label>
                <input type="text" class="form-control" name="mail[smtp_server]" value="<?= $mail['smtp_server'] ?? '' ?>">
                <span class="text-muted">
                    <?= lang(
                        'If set, SMTP will be used. If empty, OSIRIS will use the default PHP mail function.',
                        'Wenn gesetzt, wird SMTP verwendet. Wenn leer, nutzt OSIRIS die Standard-PHP-Mail-Funktion.'
                    ) ?>
                </span>
            </div>

            <div class="col-sm-2">
                <label for="email"><?= lang('Port', 'Port') ?></label>
                <input type="number" class="form-control" name="mail[smtp_port]" value="<?= $mail['smtp_port'] ?? '25' ?>">
            </div>
        </div>

        <h5>
            <?= lang('SMTP Authentication', 'SMTP-Authentifizierung') ?>
        </h5>
        <p class="text-muted m-0">
            <?= lang('If your SMTP server requires authentication, please provide the username and password here.', 'Wenn dein SMTP-Server eine Authentifizierung erfordert, gib hier bitte den Benutzernamen und das Passwort ein.') ?>
        </p>
        <div class="row row-eq-spacing">
            <div class="col-sm">
                <label for="email"><?= lang('Username', 'Benutzername') ?></label>
                <input type="text" class="form-control" name="mail[smtp_user]" value="<?= $mail['smtp_user'] ?? '' ?>">
            </div>

            <div class="col-sm">
                <label for="email"><?= lang('Password', 'Passwort') ?></label>
                <input type="password" class="form-control" name="mail[smtp_password]" value="<?= $mail['smtp_password'] ?? '' ?>">
            </div>
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
                    'Choose None if your internal SMTP relay does not use encryption.',
                    'Wähle None, wenn der interne SMTP-Relay keine Verschlüsselung verwendet.'
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

        <div class="bottom-buttons mb-20">
            <button class="btn success">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save', 'Speichern') ?>
            </button>
        </div>

    </form>
    <hr>
    <!-- Test Email Settings by sending a test mail -->
    <form action="<?= ROOTPATH ?>/crud/admin/mail-test" method="post">
        <div class="box padded mt-20">

            <h2 class="title">
                <i class="ph-duotone ph-paper-plane-tilt"></i>
                <?= lang('Test Email Settings', 'Teste E-Mail-Einstellungen') ?>
            </h2>

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

            <button class="btn blue">
                <i class="ph ph-paper-plane-tilt"></i>
                <?= lang('Send Test Email', 'Test-E-Mail senden') ?>
            </button>
        </div>
    </form>
</div>