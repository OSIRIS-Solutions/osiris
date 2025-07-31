<?php

/**
 * Page to import activities
 * 
 * e.g. from file or Google Scholar
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /expertise
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$googlescholar = $Settings->featureEnabled('googlescholar', true);
$openalex = $Settings->featureEnabled('openalex', true);

?>

<h1>
    <i class="ph ph-upload text-osiris"></i>
    Import
</h1>

<?php if (!$googlescholar && !$openalex) { ?>
    <div class="alert danger">
        <h2 class="title">
            <?= lang('Import not available', 'Import nicht verfügbar') ?>
        </h2>
        <p>
            <?= lang('The import feature is not available for your institute.', 'Die Import-Funktion ist für dein Institut nicht verfügbar.') ?>
        </p>
    </div>
<?php } ?>


<!-- import from OpenAlex -->
<?php
if ($openalex) {
    $affiliation = $Settings->get('affiliation_details');
    if (!empty($affiliation['openalex'] ?? null)) { ?>
        <div class="box">

            <div class="content">
                <b class="badge success">
                    RECOMMENDED
                </b>
                <h2 class="title mt-10">OpenAlex Import</h2>
                <p>
                    <?= lang(
                        'You can import data from OpenAlex! This method is very reliable, so we recommend it.',
                        'Du kannst Publikationen von OpenAlex importieren! Da diese Methode sehr zuverlässig ist, empfehlen wir sie.'
                    ) ?>
                </p>
                <p>
                    <b>
                        <?= lang(
                            '
            How you can find your OpenAlex ID:',
                            'Wie du deine OpenAlex-ID herausfindest:'
                        ) ?>
                    </b>
                </p>

                <ol class="list success">
                    <li>
                        <?= lang('Go to OpenAlex and search for your name or for one of your publications.', 'Gehe zu OpenAlex und suche nach deinem Namen oder nach einer deiner Publikationen.') ?>
                    </li>
                    <li>
                        <?= lang('Click on one of your publications. A side window will open showing the details. In the list of authors, you click on your name.', 'Klicke auf eine deiner Publikationen, woraufhin sich eine Seitenleiste mit den Details öffnet. Dort klickst du auf deinen Namen in der Autorenliste.') ?>
                    </li>
                    <li>
                        <?= lang('You are now on your OpenAlex profile page. To import all the publications shown there into OSIRIS at once, you need the OpenAlex ID, which is the last part of the URL. It starts with `a` followed by numbers. Copy it into the field below and start the import.', 'Nun bist du auf deiner OpenAlex-Profilseite. Um alle dort gezeigten Publikationen mit einmal in OSIRIS zu importieren brauchst du die OpenAlex-ID, die der letzte Teil der URL ist. Sie beginnt mit einem `a` gefolgt von Zahlen. Kopiere sie in das Feld unten und starte mit dem Import.') ?>
                    </li>

                    <form action="<?= ROOTPATH ?>/import/openalex" method="get">
                        <div class="form-group">
                            <label for="openalex-id">OpenAlex ID</label>
                            <input type="text" name="openalex-id" id="openalex-id" class="form-control" required>
                        </div>
                        <button type="submit" class="btn">Import</button>
                    </form>
            </div>


        </div>

    <?php } else { ?>
        <div class="box">
            <div class="content">
                <h2 class="title">OpenAlex Import</h2>
                <p>
                    <?= lang(
                        'Your Institute must add the institutional OpenAlex ID in their general settings to use this feature.',
                        'Dein Institut muss die institutionelle OpenAlex-ID in den allgemeinen Einstellungen hinterlegen, um dieses Feature zu nutzen.'
                    ) ?>
                </p>
            </div>
        </div>
<?php }
} ?>

<?php
if ($googlescholar) {
    if (!empty($USER['google_scholar'] ?? null)) { ?>

        <div class="box">
            <div class="content">
                <h2 class="title">Google Scholar Import</h2>
                <p>
                    <?= lang(
                        'You can import data from your Google scholar account',
                        'Du kannst Publikationen von deinem Google Scholar-Account importieren'
                    ) ?>:
                </p>
                <p class="mt-0 font-size-16 font-weight-bold">
                    Account-ID: <a href="https://scholar.google.com/citations?user=<?= $USER['google_scholar'] ?>"><?= $USER['google_scholar'] ?></a>
                </p>

                <p class="font-size-12 text-muted">
                    <?= lang('Please note that only the 100 latest entries can be imported.', 'Bitte beachte, dass nur die 100 neusten Einträge importiert werden können.') ?>
                </p>

                <form action="<?= ROOTPATH ?>/import/googlescholar/<?= $USER['google_scholar'] ?>" method="get">
                    <button type="submit" class="btn">Import</button>
                </form>
            </div>
        </div>

    <?php } else { ?><!-- if empty(USER[googlescholar]) -->
        <div class="box">
            <div class="content">
                <h2 class="title">Google Scholar Import</h2>
                <p>
                    <?= lang(
                        'You must connect a google scholar account to your profile to use this feature.',
                        'Du musst einen Google Scholar-Account in deinem Profil hinterlegen, um dieses Feature zu nutzen.'
                    ) ?>
                </p>

                <a href="<?= ROOTPATH ?>/user/edit/<?= $_SESSION['username'] ?>" class="btn"><?= lang('Update Profile', 'Profil bearbeiten') ?></a>

            </div>
        </div>

<?php }
}
?>





<!-- 

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
                    <input type="radio" name="format" id="format-bibtex" value="bibtex" checked="checked" >
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
 -->