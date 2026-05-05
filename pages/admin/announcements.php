<?php

/**
 * Admin page for managing announcements
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/announcements
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
    <div class="container w-800 mw-full">
        <?php
        $announcement = $Settings->get('announcement');
        if (empty($announcement)) {
            $announcement = [
                'en' => '',
                'de' => '',
                'expires' => '',
                'active' => false,
                // 'updated_at' => null
            ];
        }
        ?>

        <h1>
            <i class="ph-duotone ph-megaphone"></i>
            <?= lang('Announcement', 'Ankündigung') ?>
        </h1>

        <p class="text-muted">
            <?= lang(
                'Announcements are displayed in the news section of user profiles. Use them for important information such as maintenance or specific notices.',
                'Ankündigungen erscheinen im News-Bereich der Nutzerprofile. Nutze sie für wichtige Infos wie Wartungen oder spezifische Hinweise.'
            ) ?>
        </p>

        <input type="hidden" name="general[announcement][updated_at]" value="<?= date('Y-m-d H:i:s') ?>">

        <div class="row row-eq-spacing my-0">
            <div class="col-md-6">
                <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                <div class="form-group mb-0">
                    <div id="announcement-editor-quill"><?= $announcement['en'] ?? '' ?></div>
                    <textarea name="general[announcement][en]" id="announcement-editor" class="d-none" readonly><?= $announcement['en'] ?? '' ?></textarea>
                    <script>
                        quillEditor('announcement-editor');
                    </script>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                <div class="form-group mb-0">
                    <div id="announcement_de-editor-quill"><?= $announcement['de'] ?? '' ?></div>
                    <textarea name="general[announcement][de]" id="announcement_de-editor" class="d-none" readonly><?= $announcement['de'] ?? '' ?></textarea>
                    <script>
                        quillEditor('announcement_de-editor');
                    </script>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="announcement_expires"><?= lang('Expires at', 'Läuft ab am') ?></label>
            <input type="datetime-local" class="form-control" name="general[announcement][expires]" id="announcement_expires" value="<?= !empty($announcement['expires']) ? date('Y-m-d\TH:i', strtotime($announcement['expires'])) : '' ?>">
            <small class="text-muted">
                <?= lang('The announcement will automatically disappear after this date or when you manually set it to inactive.', 'Die Ankündigung verschwindet automatisch nach diesem Datum oder wenn du sie manuell auf inaktiv setzt.') ?>
            </small>
        </div>
        <div class="form-group">
            <input type="hidden" name="general[announcement][active]" value="0">
            <label for="announcement_active"><input type="checkbox" class="form-check-input" name="general[announcement][active]" id="announcement_active" <?= !empty($announcement['active']) ? 'checked' : '' ?>>
                <?= lang('Active', 'Aktiv') ?></label>
            <small class="text-muted">
                <?= lang('If the announcement is not active, it will not be shown on the website, but you can still save it for later use.', 'Wenn die Ankündigung nicht aktiv ist, wird sie nicht auf der Website angezeigt, aber du kannst sie trotzdem für die spätere Verwendung speichern.') ?>
            </small>
        </div>
        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </div>
</form>