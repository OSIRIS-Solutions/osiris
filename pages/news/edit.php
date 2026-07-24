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
$news_lang = $Settings->get('news-language', 'both');

include_once BASEPATH . "/header-editor.php";
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$form_action = '/crud/news/create';
if (isset($news) && isset($news['_id'])) {
    $form_action = '/crud/news/update/' . e($news['_id']);
}
?>
<style>
    .suggestions {
        color: #464646;
        /* position: absolute; */
        margin: 10px auto;
        top: 100%;
        left: 0;
        max-height: 19.2rem;
        overflow: auto;
        bottom: -3px;
        width: 100%;
        box-sizing: border-box;
        min-width: 12rem;
        background-color: white;
        border: var(--border-width) solid #afafaf;
        /* visibility: hidden; */
        /* opacity: 0; */
        z-index: 100;
        -webkit-transition: opacity 0.4s linear;
        transition: opacity 0.4s linear;
    }

    .suggestions a {
        display: block;
        padding: 0.5rem;
        border-bottom: var(--border-width) solid #afafaf;
        color: #464646;
        text-decoration: none;
        width: 100%;
    }

    .suggestions a:hover {
        background-color: #f0f0f0;
    }
</style>

<h1>
    <i class="ph-duotone ph-megaphone"></i>
    <?= isset($news) && isset($news['_id']) ? lang('Edit news item', 'Nachricht bearbeiten') : lang('Create news item', 'Nachricht erstellen') ?>
</h1>

<form action="<?= ROOTPATH ?><?= $form_action ?>" method="post" enctype="multipart/form-data">
    <?php if ($news_lang == 'one') { ?>
        <div class="form-group">
            <label for="news-title" class="required"><?= lang('Title', 'Titel') ?></label>
            <input type="text" name="news[title]" id="news-title" class="form-control large" value="<?= $news['title'] ?? '' ?>" required>
        </div>
        <div class="form-group">
            <label for="news-teaser"><?= lang('Teaser', 'Teaser') ?></label>
            <textarea name="news[teaser]" id="news-teaser" class="form-control" rows="3"><?= $news['teaser'] ?? '' ?></textarea>
            <small class="text-muted"><?= lang('Optional short summary that will be shown in the news overview.', 'Optionale kurze Zusammenfassung, die in der Nachrichtenübersicht angezeigt wird.') ?></small>
        </div>
        <div class="form-group mb-0">
            <label for="content-editor"><?= lang('Content', 'Inhalt') ?></label>
            <div id="content-editor-quill"><?= $news['content'] ?? '' ?></div>
            <textarea name="news[content]" id="content-editor" class="d-none" readonly><?= $news['content'] ?? '' ?></textarea>
            <script>
                quillEditor('content-editor');
            </script>
        </div>

    <?php } else { ?>
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <fieldset class="h-full">
                    <legend class="d-flex">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></legend>

                    <div class="form-group">
                        <label for="news-title" class="required"><?= lang('Title', 'Titel') ?></label>
                        <input type="text" name="news[title]" id="news-title" class="form-control large" value="<?= $news['title'] ?? '' ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="news-teaser"><?= lang('Teaser', 'Teaser') ?></label>
                        <textarea name="news[teaser]" id="news-teaser" class="form-control" rows="3"><?= $news['teaser'] ?? '' ?></textarea>
                        <small class="text-muted"><?= lang('Optional short summary that will be shown in the news overview.', 'Optionale kurze Zusammenfassung, die in der Nachrichtenübersicht angezeigt wird.') ?></small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="content-editor"><?= lang('Content', 'Inhalt') ?></label>
                        <div id="content-editor-quill"><?= $news['content'] ?? '' ?></div>
                        <textarea name="news[content]" id="content-editor" class="d-none" readonly><?= $news['content'] ?? '' ?></textarea>
                        <script>
                            quillEditor('content-editor');
                        </script>
                    </div>
                </fieldset>
            </div>

            <div class="col-md-6">
                <fieldset class="h-full">
                    <legend class="d-flex">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></legend>

                    <div class="form-group">
                        <label for="news-title-de"><?= lang('Title', 'Titel') ?></label>
                        <input type="text" name="news[title_de]" id="news-title-de" class="form-control large" value="<?= $news['title_de'] ?? '' ?>">
                    </div>

                    <div class="form-group">
                        <label for="news-teaser-de"><?= lang('Teaser', 'Teaser') ?></label>
                        <textarea name="news[teaser_de]" id="news-teaser-de" class="form-control" rows="3"><?= $news['teaser_de'] ?? '' ?></textarea>
                        <small class="text-muted"><?= lang('Optional short summary that will be shown in the news overview.', 'Optionale kurze Zusammenfassung, die in der Nachrichtenübersicht angezeigt wird.') ?></small>
                    </div>

                    <div class="form-group mb-0">
                        <label for="content_de-editor"><?= lang('Content', 'Inhalt') ?></label>
                        <div id="content_de-editor-quill"><?= $news['content_de'] ?? '' ?></div>
                        <textarea name="news[content_de]" id="content_de-editor" class="d-none" readonly><?= $news['content_de'] ?? '' ?></textarea>
                        <script>
                            quillEditor('content_de-editor');
                        </script>
                    </div>
                </fieldset>
            </div>
        </div>
    <?php } ?>

    <!-- add activities -->

    <fieldset id="activities">
        <legend><?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?></legend>

        <ul>
            <?php foreach ($news['activities'] ?? [] as $res) {
                $doc = $DB->getActivity($res);
            ?>
                <li>
                    <?= $doc['rendered']['icon'] ?>
                    <?= $doc['rendered']['plain'] ?>
                    <input type="hidden" name="news[activities][]" value="<?= $res ?>">
                    <button class="btn link text-danger small" type="button" onclick="$(this).closest('li').remove()"><i class="ph ph-trash"></i></button>
                </li>
            <?php } ?>

        </ul>

        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search for Activity" onkeypress="if(event.keyCode==13){searchActivities();return false;}">
            <div class="input-group-append">
                <button class="btn secondary" type="button" onclick="searchActivities()"><?= lang('Search', 'Suchen') ?></button>
            </div>
        </div>

        <div class="suggestions" style="display:none;"></div>
    </fieldset>


    <fieldset>
        <legend><?= lang('Additional options', 'Weitere Optionen') ?></legend>

        <div class="form-group">
            <label for="type" class="required">
                <?= lang('Type', 'Typ') ?>
            </label>
            <select name="news[type]" id="type" class="form-control" required>
                <?php
                $vocab = $Vocabulary->getValues('news-category');
                $sel = $news['type'] ?? '';
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= $sel == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label for="news-date" class="required"><?= lang('Publication Date', 'Veröffentlichungsdatum') ?></label>
            <input type="date" name="news[date]" id="news-date" class="form-control w-auto" value="<?= $news['date'] ?? date('Y-m-d') ?>" required>
        </div>

        <div class="form-group">
            <label for="news-visibility" class="required"><?= lang('Visibility', 'Sichtbarkeit') ?></label>
            <select name="news[visibility]" id="news-visibility" class="form-control w-auto" required>
                <option value="internal" <?= (isset($news['visibility']) && $news['visibility'] == 'internal') ? 'selected' : '' ?>><?= lang('Internal', 'Intern') ?></option>
                <option value="public" <?= (isset($news['visibility']) && $news['visibility'] == 'public') ? 'selected' : '' ?>><?= lang('Public', 'Öffentlich') ?></option>
            </select>
            <small class="text-muted"><?= lang('Public news will made public via Portfolio if this feature is enabled. Internal news are only visible within OSIRIS.', 'Öffentliche Nachrichten werden über das Portfolio veröffentlicht, wenn diese Funktion aktiviert ist. Interne Nachrichten sind nur innerhalb von OSIRIS sichtbar.') ?></small>
        </div>

    </fieldset>

    <button type="submit" class="btn primary">
        <i class="ph ph-check"></i>
        <?= lang('Save', 'Speichern') ?>
    </button>
</form>


<script>
    function searchActivities() {
        const section = $('#activities')
        const val = section.find('input[type=text]').val()
        const suggest = section.find('.suggestions');
        suggest.empty().show();
        // prevent enter from submitting form
        $(section).closest('form').on('keypress', function(event) {
            if (event.keyCode == 13) {
                event.preventDefault();
            }
        })
        if (val.length < 3) {
            suggest.append(`<span >${lang('Please type at least 3 characters', 'Mindestens 3 Zeichen erforderlich')}</span>`)
            return;
        }
        $.get(ROOTPATH + '/api/activities-suggest/' + val, function(data) {
            console.log(data);
            if (data.count == 0) {
                suggest.append(`<span >${lang('Nothing found', 'Nichts gefunden')}</span>`)
                return;
            }
            data.data.forEach(function(d) {
                suggest.append(
                    `<a  data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                )
            })
            suggest.find('a')
                .on('click', function(event) {
                    event.preventDefault();
                    console.log(this);
                    const el = $('<li>')
                        .text($(this).text())
                    el.append(`<input type="hidden" name="news[activities][]" value="${$(this).data('id')}">`)
                    section.find('ul').append(el);
                })
            // $('#activity-suggest .suggest').html(data);
        })

    }
</script>