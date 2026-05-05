<?php

/**
 * Page for admin dashboard for general settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-gear"></i>
        <?= lang('General Settings', 'Allgemeine Einstellungen') ?>
    </h1>


    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

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

        <hr>
        <h5 class="mb-0">
            <?= lang('Print output settings', 'Einstellungen für die Print-Ausgabe') ?>
        </h5>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm-6">
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
            </div>

            <!-- render language -->
            <div class="col-sm-6">
                <?php
                $renderLang = $Settings->get('render_language', 'en');
                ?>
                <label for="render_language"><?= lang('Render language', 'Anzeigesprache') ?></label>
                <select class="form-control" name="general[render_language]" id="render_language">
                    <!-- <option value="both" <?= $renderLang == 'both' ? 'selected' : '' ?>><?= lang('Both languages', 'Beide Sprachen') ?></option> -->
                    <option value="en" <?= $renderLang == 'en' ? 'selected' : '' ?>><?= lang('English only', 'Nur Englisch') ?></option>
                    <option value="de" <?= $renderLang == 'de' ? 'selected' : '' ?>><?= lang('German only', 'Nur Deutsch') ?></option>
                </select>
            </div>
        </div>
        <p class="mt-5">
            <b>
                <i class="ph ph-warning"></i>
                <?= lang('Hint:', 'Hinweis:') ?>
            </b>
            <?= lang('you have to rerender all activities to see the changes. You can do this here:', 'Du musst alle Aktivitäten neu rendern, um die Änderungen zu sehen. Du kannst dies hier tun:') ?>
            <a href="<?= ROOTPATH ?>/rerender" class="">
                <?= lang('Render all activities', 'Alle Aktivitäten rendern') ?>.
            </a><br>
            <?= lang('This might take a while. Please be patient and do not reload the page.', 'Das Neu-Rendern kann eine Weile dauern. Bitte sei geduldig und lade die Seite nicht neu.') ?>
        </p>



        <hr>
        <h5 class="mb-0">
            <?= lang('ORCID Settings', 'ORCID Einstellungen') ?>
        </h5>

        <div class="form-group row row-eq-spacing">
            <?php
            $orcid = $Settings->get('orcid');
            ?>
            <div class="col-sm">
                <label for="client_id">Client ID</label>
                <input type="float" class="form-control" name="general[orcid][client_id]" value="<?= $orcid['client_id'] ?? '' ?>">
            </div>
            <div class="col-sm">
                <label for="client_secret">Client Secret</label>
                <input type="float" class="form-control" name="general[orcid][client_secret]" value="<?= $orcid['client_secret'] ?? '' ?>">
            </div>
        </div>

        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>

    </form>
</div>