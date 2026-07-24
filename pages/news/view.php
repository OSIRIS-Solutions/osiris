<?php

/**
 * News view page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /news/view/{id}
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
$news = DB::doc2Arr($news);
?>

<style>
    .news-content {
        margin-bottom: 1.5rem;
        font-size: 1.6rem;
    }

    .news-teaser {
        font-size: 1.6rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .metadata {
        border-top: 1px solid var(--border-color);
        padding-top: 1rem;
        display: flex;
        gap: 2rem;
        font-size: 1.2rem;
        color: var(--muted-color);
        margin-top: 1rem;
    }

    .news-image {
        width: 80rem;
        max-height: 30rem;
        object-fit: cover;
        border-radius: 8px;
        background-color: white;
    }

    <?php foreach ($Vocabulary->getValues('news-category') as $key => $val) {
        echo '.type.' . e($val['id']) . ' {
        background-color: ' . DB::$colors[$key] . '20;
        color: ' . DB::$colors[$key] . ';
    }
    ';
    } ?>
</style>

<?php
if ($Settings->hasPermission('news.edit')) { ?>
    <!-- Modal for updating the profile picture -->
    <div class="modal modal-lg" id="change-picture" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content mw-full">
                <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>

                <h2 class="title">
                    <?= lang('Change news image', 'Nachrichtenbild ändern') ?>
                </h2>

                <p>
                    <?= lang('The image should ideally be 800 x 300 pixels. The maximum file size is 2 MB.', 'Das Bild sollte idealerweise 800 x 300 Pixel groß sein. Die maximale Dateigröße beträgt 2 MB.') ?>
                </p>

                <form action="<?= ROOTPATH ?>/crud/news/upload-picture/<?= $id ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                    <div class="custom-file mb-20" id="file-input-div">
                        <input type="file" id="profile-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>" accept="image/*" required>
                        <label for="profile-input"><?= lang('Select new image', 'Wähle ein neues Bild') ?></label>
                        <br><small class="text-danger">Max. 2 MB.</small>
                    </div>

                    <script>
                        var uploadField = document.getElementById("profile-input");

                        uploadField.onchange = function() {
                            if (this.files[0].size > 2097152) {
                                toastError(lang("File is too large! Max. 2MB is supported!", "Die Datei ist zu groß! Max. 2MB werden unterstützt."));
                                this.value = "";
                            };
                        };
                    </script>
                    <button class="btn primary">
                        <i class="ph ph-upload"></i>
                        <?= lang('Upload', 'Hochladen') ?>
                    </button>
                </form>

                <hr>
                <form action="<?= ROOTPATH ?>/crud/news/upload-picture/<?= $id ?>" method="post">
                    <input type="hidden" name="delete" value="true">
                    <button class="btn danger">
                        <i class="ph ph-trash"></i>
                        <?= lang('Delete current picture', 'Aktuelles Bild löschen') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php } ?>



<div class="container w-800 mw-full">
    <div class="btn-toolbar">
        <span class="badge type <?= $news['type'] ?? 'other' ?> mr-10"><?= $Vocabulary->getValue('news-category', $news['type'] ?? 'other') ?></span>

        <?php if ($Settings->hasPermission('news.edit')) { ?>
            <a href="<?= ROOTPATH ?>/news/edit/<?= e($news['_id']) ?>" class="btn primary">
                <i class="ph ph-pencil"></i>
                <?= lang('Edit', 'Bearbeiten') ?>
            </a>
            <a href="#change-picture" class="btn">
                <i class="ph ph-image"></i>
                <?= lang('Change image', 'Bild ändern') ?>
            </a>
        <?php } ?>
        <?php if ($Settings->hasPermission('news.delete')) { ?>
            <form action="<?= ROOTPATH ?>/crud/news/delete" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to delete this news item?', 'Sind Sie sicher, dass Sie diese Nachricht löschen möchten?') ?>');" class="d-inline">
                <input type="hidden" name="id" value="<?= e($news['_id']) ?>">
                <button type="submit" class="btn danger">
                    <i class="ph ph-trash"></i>
                    <?= lang('Delete', 'Löschen') ?>
                </button>
            </form>
        <?php } ?>
    </div>

    <div class="image-wrapper" style="position: relative; margin-top: 1rem;">
        <?php
        DB::printLogo($news, 'news-image');
        ?>
    </div>

    <h1>
        <i class="ph-duotone ph-megaphone"></i>
        <?= e(lang($news['title'] ?? null, $news['title_de'] ?? null)) ?>
    </h1>

    <?php if (isset($news['teaser']) || isset($news['teaser_de'])) { ?>
        <div class="news-teaser">
            <?= lang($news['teaser'] ?? null, $news['teaser_de'] ?? null) ?>
        </div>
    <?php } ?>


    <div class="news-content">
        <?= lang($news['content'] ?? '', $news['content_de'] ?? null) ?>
    </div>

    <?php if (is_countable($news['activities'] ?? null) && count($news['activities']) > 0) { ?>
        <hr>
        <div class="activities">
            <h4><?= lang('Selected Research Activities', 'Ausgewählte Forschungsaktivitäten') ?></h4>
            <?php foreach ($news['activities'] as $i => $a) {
                $doc = $DB->getActivity($a);
                echo $doc['rendered']['web'] ?? '';
                if ($i < count($news['activities']) - 1) {
                    echo '<br>';
                }
            } ?>
        </div>
    <?php } ?>


    <div class="metadata">
        <div>
            <?= lang('Published on', 'Veröffentlicht am') ?>
            <?= date('d.m.Y', strtotime($news['date'])) ?>
        </div>
        <?php if (isset($news['created_by'])) { ?>
            <div>
                <?= lang('Created by', 'Erstellt von') ?>
                <a href="<?= ROOTPATH ?>/profile/<?= e($news['created_by']) ?>"><?= e($DB->getNameFromId($news['created_by'])) ?></a>
                <?= lang('on', 'am') ?>
                <?= date('d.m.Y', strtotime($news['created'])) ?>
            </div>
        <?php } ?>

        <?php if (isset($news['updated_by'])) { ?>
            <div>
                <?= lang('Last updated by', 'Aktualisiert von') ?>
                <a href="<?= ROOTPATH ?>/profile/<?= e($news['updated_by']) ?>"><?= e($DB->getNameFromId($news['updated_by'])) ?></a>
                <?= lang('on', 'am') ?>
                <?= date('d.m.Y', strtotime($news['updated'])) ?>
            </div>
        <?php } ?>

        <?php if (($news['visibility'] ?? '') === 'public') { ?>
            <div>
                <?= lang('Public', 'Öffentlich') ?>
            </div>
        <?php } else { ?>
            <div>
                <?= lang('Internal', 'Intern') ?>
            </div>
        <?php } ?>

    </div>


    <?php if (isset($_GET['verbose'])) {
        dump($news);
    } ?>

</div>