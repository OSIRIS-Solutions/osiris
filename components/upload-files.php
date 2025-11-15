<?php
/**
 * Component to upload files for an activity
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/view/<activity_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$files = $doc['files'] ?? array();
?>

<div class="box">
    <div class="content">
        <div class="title">
            <?= lang('All files', 'Alle Dateien') ?>
        </div>
        <table class="table simple w-auto">
            <?php if (!empty($files)) : ?>
                <?php foreach ($files as $file) : ?>
                    <tr>
                        <td><?= $file['filename'] ?></td>
                        <td><?= $file['filetype'] ?></td>
                        <td>
                            <a href="<?= $file['filepath'] ?>"><i class="ph ph-download"></i></a>
                        </td>
                        <td>
                            <form action="<?=ROOTPATH?>/crud/activities/upload-files/<?=$id?>" method="post">
                                <input type="hidden" name="delete" value="<?= $file['filename'] ?>">

                                <button class="btn link" type="submit">
                                    <i class="ph-duotone ph-trash text-danger"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td><?= lang('No files uploaded', 'Noch keine Dateien hochgeladen') ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>


<div class="box">
    <div class="content">
        <div class="title">
            <?= lang('Add new file', 'Füge Datei hinzu') ?>
        </div>
        <form action="<?=ROOTPATH?>/crud/activities/upload-files/<?=$id?>" method="post" enctype="multipart/form-data">
            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
            <div class="custom-file mb-20" id="file-input-div" >
                <input type="file" id="file-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
                <label for="file-input"><?= lang('Append a file', 'Hänge eine Datei an') ?></label>
                <br><small class="text-danger">Max. 16 MB.</small>
            </div>
            <button class="btn secondary">
                <i class="ph ph-upload"></i>
                Upload
            </button>
        </form>
    </div>
</div>

<script>
    var uploadField = document.getElementById("file-input");

    uploadField.onchange = function() {
        if (this.files[0].size > 16777216 ) {
            toastError(lang("File is too large! Max. 16MB is supported!", "Die Datei ist zu groß! Max. 16MB werden unterstützt."));
            this.value = "";
        };
    };
</script>