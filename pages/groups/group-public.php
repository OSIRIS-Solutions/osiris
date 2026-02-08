<?php

/**
 * Page to edit external view on group
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /groups/public/:name
 *
 * @package     OSIRIS
 * @since       1.3.5
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$form = $form ?? array();

$level = 0;

$formaction = ROOTPATH;
$formaction .= "/crud/groups/update/" . $form['_id'];
$btntext = '<i class="ph ph-check"></i> ' . lang("Update", "Aktualisieren");
$url = ROOTPATH . "/groups/public/" . $form['id'];
$title = lang('Edit group: ', 'Gruppe bearbeiten: ') . $id;

$level = $Groups->getLevel($id);


function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return e($val);
    }
    return $val;
}

function sel($index, $value)
{
    return val($index) == $value ? 'selected' : '';
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

    .form-control.large {
        font-weight: bold;
    }
</style>

<?php include_once BASEPATH . '/header-editor.php'; ?>

<h3 class="title">
    <?= $title ?>
</h3>

<form action="<?= $formaction ?>" method="post" id="group-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">

    <fieldset>
        <legend>
            <?= lang('Visibility on Website', 'Darstellung auf der Webseite') ?>
        </legend>

        <div class="form-group">
            <input type="hidden" name="values[hide]" value="0">
            <div class="custom-switch">
                <input type="checkbox" id="hide-check" <?= val('hide') ? 'checked' : '' ?> name="values[hide]" value="1" onchange="toggleVisibility()">
                <label for="hide-check">
                    <?= lang('Hide group from public view', 'Gruppe nicht öffentlich anzeigen') ?>
                </label>
            </div>
        </div>
    </fieldset>


    <div class="row row-eq-spacing mb-0">
        <div class="col-md-6">
            <fieldset>
                <legend class="d-flex"><?= lang('English', 'Englisch') ?> <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></legend>
                <div class="form-group">
                    <label for="name" class="required">
                        <?= lang('Full Name', 'Voller Name') ?> (EN)
                    </label>
                    <input type="text" class="form-control large" name="values[name]" id="name" required value="<?= val('name') ?>">
                </div>

                <div class="form-group">
                    <label for="description"><?= lang('Description', 'Beschreibung') ?> (EN)</label>

                    <div id="description-quill"><?= $form['description'] ?? '' ?></div>
                    <textarea name="values[description]" id="description" class="d-none" readonly><?= $form['description'] ?? '' ?></textarea>
                    <script>
                        quillEditor('description');
                    </script>
                </div>
            </fieldset>
        </div>
        <div class="col-md-6">
            <fieldset>
                <legend class="d-flex"><?= lang('German', 'Deutsch') ?> <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></legend>
                <div class="form-group">
                    <label for="name_de" class="required">
                        <?= lang('Full Name', 'Voller Name') ?> (DE)
                    </label>
                    <input type="text" class="form-control large" name="values[name_de]" id="name_de" required value="<?= val('name_de') ?>">
                </div>
                <div class="form-group">
                    <label for="description_de"><?= lang('Description', 'Beschreibung') ?> (DE)</label>

                    <div id="description_de-quill"><?= $form['description_de'] ?? '' ?></div>
                    <textarea name="values[description_de]" id="description_de" class="d-none" readonly><?= $form['description_de'] ?? '' ?></textarea>
                    <script>
                        quillEditor('description_de');
                    </script>
                </div>
            </fieldset>
        </div>
    </div>


    <h3><?= lang('Research interest', 'Forschungsinteressen') ?></h3>
    <div id="research-list">
        <?php
        if (isset($form['research']) && !empty($form['research'])) {

            foreach ($form['research'] as $i => $con) { ?>

                <div class="box padded">

                    <div class="row row-eq-spacing my-0">
                        <div class="col-md-6">
                            <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                            <div class="form-group floating-form">
                                <input name="values[research][<?= $i ?>][title]" type="text" class="form-control large" value="<?= e($con['title'] ?? '') ?>" placeholder="Title" required>
                                <label for="values[research][<?= $i ?>][title]" class="required"><?=lang('Title', 'Titel')?></label>
                            </div>
                            <div class="form-group floating-form">
                                <input name="values[research][<?= $i ?>][subtitle]" type="text" class="form-control" value="<?= e($con['subtitle'] ?? '') ?>" placeholder="Subtitle">
                                <label for="values[research][<?= $i ?>][subtitle]"><?=lang('Subtitle', 'Untertitel')?></label>
                            </div>
                            <div class="form-group mb-0">
                                <div id="info-<?=$i?>-quill"><?= $con['info'] ?? '' ?></div>
                                <textarea name="values[research][<?= $i ?>][info]" id="info-<?=$i?>" class="d-none" readonly><?= $con['info'] ?? '' ?></textarea>
                                <script>
                                    quillEditor('info-<?=$i?>');
                                </script>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                            <div class="form-group floating-form">
                                <input name="values[research][<?= $i ?>][title_de]" type="text" class="form-control large" value="<?= e($con['title_de'] ?? '') ?>" placeholder="Title">
                                <label for="values[research][<?= $i ?>][title_de]"><?=lang('Title', 'Titel')?></label>
                            </div>
                            <div class="form-group floating-form">
                                <input name="values[research][<?= $i ?>][subtitle_de]" type="text" class="form-control" value="<?= e($con['subtitle_de'] ?? '') ?>" placeholder="Subtitle">
                                <label for="values[research][<?= $i ?>][subtitle_de]"><?=lang('Subtitle', 'Untertitel')?></label>
                            </div>
                            <div class="form-group mb-0">
                                <div id="info_de-<?=$i?>-quill"><?= $con['info_de'] ?? '' ?></div>
                                <textarea name="values[research][<?= $i ?>][info_de]" id="info_de-<?=$i?>" class="d-none" readonly><?= $con['info_de'] ?? '' ?></textarea>
                                <script>
                                    quillEditor('info_de-<?=$i?>');
                                </script>
                            </div>

                        </div>
                    </div>

                    <div id="activities-<?= $i ?>">
                        <h5><?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?></h5>

                        <ul>
                            <?php foreach ($con['activities'] ?? [] as $res) {
                                $doc = $DB->getActivity($res);
                            ?>
                                <li>
                                    <?= $doc['rendered']['icon'] ?>
                                    <?= $doc['rendered']['plain'] ?>
                                    <input type="hidden" name="values[research][<?= $i ?>][activities][]" value="<?= $res ?>">
                                </li>
                            <?php } ?>

                        </ul>

                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search for Activity">
                            <div class="input-group-append">
                                <button class="btn secondary" type="button" onclick="searchActivities('<?= $i ?>')"><?= lang('Search', 'Suchen') ?></button>
                            </div>
                        </div>

                        <div class="suggestions" style="display:none;"></div>

                    </div>

                    <button class="btn danger small my-10" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
                </div>
        <?php }
        } ?>

    </div>
    <button class="btn" type="button" onclick="addResearchrow(event, '#research-list')"><i class="ph ph-plus text-success"></i> <?= lang('Add entry', 'Eintrag hinzufügen') ?></button>


    <script>
        function searchActivities(index) {
            const section = $('#activities-' + index)
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
            $.get('<?= ROOTPATH ?>/api/activities-suggest/' + val, function(data) {
                console.log(data);
                if (data.count == 0) {
                    suggest.append(`<span >${lang('Nothing found', 'Nichts gefunden')}</span>`)
                    return;
                }
                data.data.forEach(function(d) {
                    suggest.append(
                        `<a onclick="selectActivity(this)" data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                    )
                })
                suggest.find('a')
                    .on('click', function(event) {
                        event.preventDefault();
                        console.log(this);
                        const el = $('<li>')
                            .text($(this).text())
                        el.append(`<input type="hidden" name="values[research][${index}][activities][]" value="${$(this).data('id')}">`)
                        section.find('ul').append(el);
                    })
                // $('#activity-suggest .suggest').html(data);
            })

        }




        var i = <?= $i ?? 0 ?>

        var CURRENTYEAR = <?= CURRENTYEAR ?>;

        function addResearchrow(evt, parent) {
            i++;
            var el = `
            <div class="box padded">
                <div class="row row-eq-spacing my-0">
                    <div class="col-md-6">
                        <h5 class="mt-0 ">English <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></h5>
                        <div class="form-group">
                            <input name="values[research][${i}][title]" type="text" class="form-control large" value="" placeholder="Title" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5 class="mt-0 ">Deutsch <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></h5>
                        <div class="form-group">
                            <input name="values[research][${i}][title_de]" type="text" class="form-control large" value="" placeholder="Title">
                        </div>
                    </div>
                </div>
                ${lang('Please save once to add more information.', 'Bitte speichere einmal, um weitere Informationen hinzuzufügen.')}<br>
                <button class="btn danger" type="button" onclick="$(this).closest('.box').remove()"><i class="ph ph-trash"></i> ${lang('Delete', 'Löschen')}</button>
            </div>

                `;
            $(parent).append(el);
        }

        function toggleVisibility() {
            var hide = $('#hide-check').prop('checked');
            if (hide) {
                $('#research').hide();
            } else {
                $('#research').show();
            }
        }
        toggleVisibility();
    </script>



    <button class="btn secondary" type="submit" id="submit-btn">
        <i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?>
    </button>

</form>