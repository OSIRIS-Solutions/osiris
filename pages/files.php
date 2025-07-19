<?php
/**
 * DISCONTINUED! Please do not use any more.
 * 
 * 
 * Page to upload files for an activity
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/files/<activity_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$files = $doc['files'] ?? array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $target_dir = BASEPATH . "/uploads/";
    if (!is_writable($target_dir)) {
        printMsg("Upload directory is unwritable. Please contact admin.");
    }
    $target_dir .= "$id/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777);
        echo "<!-- The directory $target_dir was successfully created.-->";
    } else {
        echo "<!-- The directory $target_dir exists.-->";
    }


    if (isset($_FILES["file"])) {

        // $target_file = basename($_FILES["file"]["name"]);

        $filename = htmlspecialchars(basename($_FILES["file"]["name"]));
        $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES["file"]["size"];
        $filepath = ROOTPATH . "/uploads/$id/$filename";

        if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
            $errorMsg = match ($_FILES['file']['error']) {
                1 => lang('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'Die hochgeladene Datei überschreitet die Richtlinie upload_max_filesize in php.ini'),
                2 => lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt."),
                3 => lang('The uploaded file was only partially uploaded.', 'Die hochgeladene Datei wurde nur teilweise hochgeladen.'),
                4 => lang('No file was uploaded.', 'Es wurde keine Datei hochgeladen.'),
                6 => lang('Missing a temporary folder.', 'Der temporäre Ordner fehlt.'),
                7 => lang('Failed to write file to disk.', 'Datei konnte nicht auf die Festplatte geschrieben werden.'),
                8 => lang('A PHP extension stopped the file upload.', 'Eine PHP-Erweiterung hat den Datei-Upload gestoppt.'),
                default => lang('Something went wrong.', 'Etwas ist schiefgelaufen.') . " (" . $_FILES['file']['error'] . ")"
            };
            printMsg($errorMsg, "error");
        } else if ($filesize > 16000000) {
            printMsg(lang("File is too big: max 16 MB is allowed.", "Die Datei ist zu groß: maximal 16 MB sind erlaubt."), "error");
        } else if (file_exists($target_dir . $filename)) {
            printMsg(lang("Sorry, file already exists.", "Die Datei existiert bereits. Um sie zu überschreiben, muss sie zunächst gelöscht werden."), "error");
        } else if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_dir . $filename)) {
            printMsg(lang("The file $filename has been uploaded.", "Die Datei <q>$filename</q> wurde hochgeladen."), "success");
            $values = [
                "filename" => $filename,
                "filetype" => $filetype,
                "filesize" => $filesize,
                "filepath" => $filepath,
            ];

            $osiris->activities->updateOne(
                ['_id' => $mongoid],
                ['$push' => ["files" => $values]]
            );
            // $files[] = $values;
        } else {
            printMsg(lang("Sorry, there was an error uploading your file.", "Entschuldigung, aber es gab einen Fehler beim Dateiupload."), "error");
        }
    } else if (isset($_POST['delete'])) {
        $filename = $_POST['delete'];
        if (file_exists($target_dir . $filename)) {
            // Use unlink() function to delete a file
            if (!unlink($target_dir . $filename)) {
                printMsg("$filename cannot be deleted due to an error.", "error");
            } else {
                printMsg(lang("$filename has been deleted.", "$filename wurde gelöscht."), "success");
            }
        }

        $osiris->activities->updateOne(
            ['_id' => $mongoid],
            ['$pull' => ["files" => ["filename" => $filename]]]
        );
        // printMsg("File has been deleted from the database.", "success");
    }

    $doc = $osiris->activities->findOne(['_id' => $mongoid]);
    $files = $doc['files'] ?? array();
}
$Format = new Document(false);
$Format->setDocument($doc);
?>

<p class="lead">
    <span class="mr-10"><?= $Format->activity_icon() ?></span>
    <?php echo $Format->formatShort(); ?>
</p>

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
                            <form action="#" method="post">
                                <input type="hidden" name="delete" value="<?= $file['filename'] ?>">

                                <button class="btn link" type="submit">
                                    <i class="ph ph-trash text-danger"></i>
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
        <form action="#" method="post" enctype="multipart/form-data">
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