<?php

/**
 * Edit details of a topic
 * Created in cooperation with bicc
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.8
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return e($val);
    }
    if ($val instanceof MongoDB\Model\BSONArray) {
        return implode(',', DB::doc2Arr($val));
    }
    return $val;
}

function sel($index, $value)
{
    return val($index) == $value ? 'selected' : '';
}

$form = $GLOBALS['form'] ?? [];

if (empty($form) || !isset($form['_id'])) {
    $formaction = ROOTPATH . "/crud/topics/create";
    $url = ROOTPATH . "/topics/view/*";
} else {
    $formaction = ROOTPATH . "/crud/topics/update/" . $form['_id'];
    $url = ROOTPATH . "/topics/view/" . $form['_id'];
}

$topicLabel = $Settings->topicLabel();
?>

<?php include_once BASEPATH . '/header-editor.php'; ?>


<h3 class="title">
    <?php
    if (empty($form) || !isset($form['_id'])) {
        echo lang('New ' . $topicLabel, 'Neuer ' . $topicLabel);
    } else {
        echo lang('Edit ' . $topicLabel, $topicLabel . ' bearbeiten');
    }
    ?>
</h3>

<form action="<?= $formaction ?>" method="post" class="form">
    <input type="hidden" name="redirect" value="<?= $url ?>">

    <div class="row row-eq-spacing">
        <div class="col-md-6 floating-form">
            <?php if (empty($form)) { ?>
                <input type="text" id="id" class="form-control" name="values[id]" required value="<?= uniqid() ?>" placeholder="ID is a required field">
                <label for="id" class="required">ID</label>
                <small class="text-muted">
                    <?= lang('It it recommended to choose something short you can recognize.', 'Es wird empfohlen, etwas Kurzes, Wiedererkennbares zu nehmen.') ?>
                </small>
            <?php } else { ?>
                <p class="mt-0">
                    ID: <code class="code"><?= $form['id'] ?></code>
                </p>
                <small class="text-muted d-block">
                    <?= lang('ID cannot be changed.', 'Die ID kann nicht geändert werden.') ?>
                </small>
            <?php } ?>
        </div>
        <!-- <div class="col-md-5 floating-form">
            <input type="text" id="icon" class="form-control" name="values[icon]" value="<?= $form['icon'] ?? '' ?>" placeholder="icon from phosphor">
            <label for="icon">Icon</label>
            <small class="text-muted">
                From <a href="https://phosphoricons.com" target="_blank" rel="noopener noreferrer">Phosphoricons</a>
            </small>
        </div> -->
        <div class="col-md-6 floating-form">
            <input type="color" id="color" class="form-control" name="values[color]" value="<?= $form['color'] ?? '' ?>" placeholder="color">
            <label for="color"><?= lang('Color', 'Farbe') ?></label>
        </div>
    </div>
    <div class="row row-eq-spacing mb-0">
        <div class="col-md-6">
            <fieldset>
                <legend class="d-flex"><?= lang('English', 'Englisch') ?> <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></legend>
                <div class="form-group">
                    <label for="name" class="required">
                        <?= lang('Title', 'Titel') ?> (EN)
                    </label>
                    <input type="text" class="form-control large" name="values[name]" id="name" required value="<?= $form['name'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="subtitle">
                        <?= lang('Subtitle', 'Untertitel') ?> (EN)
                    </label>
                    <input type="text" class="form-control" name="values[subtitle]" id="subtitle" value="<?= $form['subtitle'] ?? ''  ?>">
                </div>

                <label for="description">
                    <?= lang('Description', 'Beschreibung') ?>
                </label>
                <div class="form-group">
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
                    <label for="name_de">
                        <?= lang('Title', 'Titel') ?> (DE)
                    </label>
                    <input type="text" class="form-control large" name="values[name_de]" id="name_de" value="<?= $form['name_de'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="name_de">
                        <?= lang('Subtitle', 'Untertitel') ?> (DE)
                    </label>
                    <input type="text" class="form-control" name="values[subtitle_de]" id="subtitle_de" value="<?= $form['subtitle_de'] ?? '' ?>">
                </div>


                <label for="description_de">
                    <?= lang('Description', 'Beschreibung') ?>
                </label>
                <div class="form-group">
                    <div id="description_de-quill"><?= $form['description_de'] ?? '' ?></div>
                    <textarea name="values[description_de]" id="description_de" class="d-none"><?= $form['description_de'] ?? '' ?></textarea>

                    <script>
                        quillEditor('description_de');
                    </script>
                </div>
            </fieldset>
        </div>
    </div>

    <!-- inactive -->
    <div class="form-group">
        <input type="hidden" name="values[inactive]" value="false">
        <div class="custom-switch">
            <input type="checkbox" id="inactive-check" <?= val('inactive') ? 'checked' : '' ?> name="values[inactive]" value="true">
            <label for="inactive-check">
                <?= lang('Mark as inactive', 'Als inaktiv markieren') ?>
            </label>
        </div>
    </div>

    <button type="submit" class="btn secondary"><?= lang('Save', 'Speichern') ?></button>
</form>



<?php if ($Settings->hasPermission('topics.delete')) { ?>
    <br>
    <div class="alert danger mt-20">
        <a onclick="$('#delete').slideToggle()">
            <?= lang('Delete', 'Löschen') ?>
            <i class="ph ph-caret-down"></i>
        </a>

        <div id="delete" style="display: none;">
            <form action="<?= ROOTPATH ?>/crud/topics/delete/<?= $topic['_id'] ?>" method="post">
                <p>
                    <?= lang(
                        'Do you really want to delete this ' . $topicLabel . '? If you delete, it will be removed from all connected persons, activities and projects.',
                        'Möchten Sie diesen ' . $topicLabel . ' wirklich löschen? Falls du löscht wird er von allen verknüpften Elementen (Aktivitäten, Personen, Projekten) ebenfalls entfernt.'
                    ) ?>
                </p>
                <button type="submit" class="btn danger"><?= lang('Delete', 'Löschen') ?></button>
            </form>
        </div>
    </div>

<?php } ?>