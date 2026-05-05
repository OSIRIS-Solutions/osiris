<?php

/**
 * Manage institute settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.2
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<form action="<?= ROOTPATH ?>/crud/admin/general" method="post" enctype="multipart/form-data">

    <div class="container w-400 mw-full">
        <h1>
            <i class="ph-duotone ph-image" aria-hidden="true"></i>
            <?= lang('Logo', 'Logo') ?>
        </h1>

        <div class="box padded">
            <b><?= lang('Current Logo', 'Derzeitiges Logo') ?>: <br></b>
            <?= $Settings->printLogo("img-fluid mt-20") ?>
        </div>

        <div class="custom-file mb-20" id="file-input-div">
            <input type="file" id="file-input" name="logo" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
            <label for="file-input"><?= lang('Upload a new logo', 'Lade ein neues Logo hoch') ?></label>
            <br><small class="text-danger">Max. 2 MB.</small>
        </div>

        <button class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </div>
</form>