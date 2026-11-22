<?php

/**
 * News add page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @link        /news/add
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
require_once BASEPATH . '/php/NewsEditorial.php';
$news_lang = $Settings->get('news-language', 'one');

include_once BASEPATH . "/header-editor.php";
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$form_action = '/crud/news/create';
if (isset($news) && isset($news['_id'])) {
    $form_action = '/crud/news/update/' . e($news['_id']);
}
?>
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">

<?php include_once BASEPATH . '/components/news-editor-style.php'; ?>

<h1>
    <i class="ph-duotone ph-megaphone"></i>
    <?= isset($news) && isset($news['_id']) ? lang('news.edit_news_item') : lang('common.create_news_item') ?>
</h1>

<form action="<?= ROOTPATH ?><?= $form_action ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token('news')) ?>">
    <?php if (isset($news['_id'])) { ?>
        <input type="hidden" name="news[edit_token]" value="<?= e(NewsEditorial::editToken(DB::doc2Arr($news), 'original')) ?>">
    <?php } ?>
    <?php if ($news_lang == 'one') { ?>
        <div class="box padded">
            <h2 class="title"><?= lang('news.news_content') ?></h2>
            <div class="form-group">
                <label for="news-title" class="required"><?= lang('common.title') ?></label>
                <input type="text" name="news[title]" id="news-title" class="form-control large" value="<?= $news['title'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="news-teaser"><?= lang('news.teaser') ?></label>
                <textarea name="news[teaser]" id="news-teaser" class="form-control" rows="3"><?= $news['teaser'] ?? '' ?></textarea>
                <small class="text-muted"><?= lang('news.optional_short_summary_that_will_be_shown_in_the_news_overview') ?></small>
            </div>
            <div class="form-group mb-0">
                <label for="content-editor"><?= lang('common.content') ?></label>
                <div id="content-editor-quill"><?= $news['content'] ?? '' ?></div>
                <textarea name="news[content]" id="content-editor" class="d-none" readonly><?= $news['content'] ?? '' ?></textarea>
                <script>
                    quillEditor('content-editor');
                </script>
            </div>
        </div>
    <?php } else { ?>
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="box padded h-full">
                    <h2 class="title d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h2>

                    <div class="form-group">
                        <label for="news-title" class="required"><?= lang('common.title') ?></label>
                        <input type="text" name="news[title]" id="news-title" class="form-control large" value="<?= $news['title'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="news-teaser"><?= lang('news.teaser') ?></label>
                        <textarea name="news[teaser]" id="news-teaser" class="form-control" rows="3"><?= $news['teaser'] ?? '' ?></textarea>
                        <small class="text-muted"><?= lang('news.optional_short_summary_that_will_be_shown_in_the_news_overview') ?></small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="content-editor"><?= lang('common.content') ?></label>
                        <div id="content-editor-quill"><?= $news['content'] ?? '' ?></div>
                        <textarea name="news[content]" id="content-editor" class="d-none" readonly><?= $news['content'] ?? '' ?></textarea>
                        <script>
                            quillEditor('content-editor');
                        </script>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="box padded h-full">
                    <h2 class="title d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h2>

                    <div class="form-group">
                        <label for="news-title-de"><?= lang('common.title') ?></label>
                        <input type="text" name="news[title_de]" id="news-title-de" class="form-control large" value="<?= $news['title_de'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="news-teaser-de"><?= lang('news.teaser') ?></label>
                        <textarea name="news[teaser_de]" id="news-teaser-de" class="form-control" rows="3"><?= $news['teaser_de'] ?? '' ?></textarea>
                        <small class="text-muted"><?= lang('news.optional_short_summary_that_will_be_shown_in_the_news_overview') ?></small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="content_de-editor"><?= lang('common.content') ?></label>
                        <div id="content_de-editor-quill"><?= $news['content_de'] ?? '' ?></div>
                        <textarea name="news[content_de]" id="content_de-editor" class="d-none" readonly><?= $news['content_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('content_de-editor');
                        </script>
                    </div>
                </div>
            </div>
        </div>
        <br>
    <?php } ?>

    <div class="box padded" id="activities">
        <h2 class="title"><?= lang('news.connected_people_and_research') ?></h2>
        <?php include BASEPATH . '/pages/news/connections-editor.php'; ?>
    </div>


    <?php
    $outletSelectionMode = 'edit';
    include BASEPATH . '/pages/news/outlets.php';
    ?>

    <div class="box padded">
        <h2 class="title"><?= lang('news.additional_options') ?></h2>

        <div class="form-group">
            <label for="type" class="required">
                <?= lang('common.type') ?>
            </label>
            <select name="news[type]" id="type" class="form-control w-auto" required>
                <?php
                $vocab = $Vocabulary->getValues('news-category');
                $sel = $news['type'] ?? '';
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= $sel == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="news-date" class="required"><?= lang('news.publication_date') ?></label>
            <input type="date" name="news[date]" id="news-date" class="form-control w-auto" value="<?= $news['date'] ?? date('Y-m-d') ?>" required>
        </div>

        <?php include BASEPATH . '/pages/news/publication.php'; ?>

        <?php if ($Settings->featureEnabled('topics')) { ?>
            <?php $Settings->topicChooser(DB::doc2Arr($news['topics'] ?? []), 'news[topics]') ?>
        <?php } ?>


    </div>

    <button type="submit" class="btn primary">
        <i class="ph ph-check"></i>
        <?= lang('action.save') ?>
    </button>
</form>
