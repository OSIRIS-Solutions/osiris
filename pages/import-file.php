<?php
/**
 * Page to import files
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /import
 *
 * @package     OSIRIS
 * @since       1.2.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once BASEPATH . '/vendor/autoload.php';
require_once BASEPATH . '/php/Document.php';
$Format = new Document();

use RenanBr\BibTexParser\Exception\ExceptionInterface;
use RenanBr\BibTexParser\Exception\ParserException;
use RenanBr\BibTexParser\Exception\ProcessorException;
use RenanBr\BibTexParser\Listener;
use RenanBr\BibTexParser\Parser;
use RenanBr\BibTexParser\Processor;

use \LibRIS\RISReader;

?>
<!-- ONLY FOR POST!!! -->
<h1>
    <i class="ph-duotone ph-upload"></i>
    Import
</h1>

<div class="box box-signal">
    <div class="content">
        <h2 class="title">
            <?= lang('Import activities from file', 'Importiere Aktivitäten aus einer Datei') ?>
        </h2>
        <form action="<?= ROOTPATH ?>/crud/import/file" method="post" enctype="multipart/form-data">
            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
            <div class="custom-file mb-20" id="file-input-div" >
                <input type="file" id="file-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
                <label for="file-input"><?= lang('Upload a BibTeX file', 'Lade eine BibTeX-Datei hoch') ?></label>
                <br><small class="text-danger">Max. 16 MB.</small>
            </div>

            <div class="form-group">
                <label for="">Format:</label>
                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" name="format" id="format-bibtex" value="bibtex" checked="checked">
                    <label for="format-bibtex">BibTeX</label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" name="format" id="format-nbib" value="nbib">
                    <label for="format-nbib">NBIB (Pubmed)</label>
                </div>

                <div class="custom-radio d-inline-block ml-10">
                    <input type="radio" name="format" id="format-ris" value="ris">
                    <label for="format-ris">RIS</label>
                </div>
            </div>

            <button class="btn secondary">
                <i class="ph ph-upload"></i>
                Upload
            </button>
        </form>
    </div>
</div>



<?php

// $target_dir = BASEPATH . "/uploads/";
// if (!is_writable($target_dir)) {
//     printMsg("Upload directory is unwritable. Please contact admin.");
// }
// $target_dir .= "$id/";
// if (!file_exists($target_dir)) {
//     mkdir($target_dir, 0777);
//     echo "<!-- The directory $target_dir was successfully created.-->";
// } else {
//     echo "<!-- The directory $target_dir exists.-->";
// }


if (isset($_FILES["file"])) {

    // $target_file = basename($_FILES["file"]["name"]);

    // $filename = e(basename($_FILES["file"]["name"]));
    // $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $filesize = $_FILES["file"]["size"];
    // $filepath = ROOTPATH . "/uploads/$id/$filename";

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
    }

    // $content = file_get_contents($_FILES["file"]["tmp_name"]);

    if ($_POST['format'] == 'bibtex') {
        $listener = new Listener();
        $listener->addProcessor(new Processor\NamesProcessor());
        $listener->addProcessor(new Processor\DateProcessor());


        $parser = new Parser();
        $parser->addListener($listener);

        try {
            // ... parser and listener configuration
            $parser->parseFile($_FILES["file"]["tmp_name"]);
            $entries = $listener->export();
        } catch (ParserException $exception) {
            echo "The BibTeX isn't valid";
        } catch (ProcessorException $exception) {
            echo "Listener's processors aren't able to handle data found";
        } catch (ExceptionInterface $exception) {
            echo "Alternatively, you can use this exception to catch all of them at once";
        }
    } elseif ($_POST['format'] == 'ris' || $_POST['format'] == 'nbib') {

        $reader = new RISReader();
        $reader->parseFile($_FILES["file"]["tmp_name"]);

        $reader->printRecords();

        $entries = $reader->getRecords();
    }


    $publicationTypes = [
        'article' => 'article',
        'book' => 'book',
        'booklet' => 'magazine',
        'inbook' => 'chapter',
        'conference' => 'article',
        'proceedings' => 'article',
        'incollection' => 'chapter',
        'mastersthesis' => 'thesis',
        'phdthesis' => 'thesis'
    ];

?>

    <table class="table">
        <tbody>
            <?php
            foreach ($entries as $entry) {
                $entry['authors'] = $entry['author'] ?? array();
                unset($entry['author']);
                unset($entry['abstract']);
                unset($entry['_original']);

                if (array_key_exists($entry['type'], $publicationTypes)) {
                    $entry['pubtype'] = $publicationTypes[$entry['type']];
                    $entry['type'] = 'publication';
                }

                $dataString = str_replace("'", "\'", serialize($entry));
                $Format->setDocument($entry);
            ?>
                <tr>

                    <td>
                        <?= $Format->formatShort(false) ?>
                    </td>
                    <td>
                        <form action="<?= ROOTPATH ?>/add-activity" method="post" target="_blank">

                            <input type="hidden" name="form" value='<?= $dataString ?>'>
                            <button class="btn"><i class="ph ph-plus"></i></button>
                        </form>
                    </td>
                </tr>
            <?php
            }
            ?>

        </tbody>
    </table>

<?php

}




?>