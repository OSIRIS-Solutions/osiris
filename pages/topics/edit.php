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

?>

<?php include_once BASEPATH . '/header-editor.php'; ?>


<h3 class="title">
    <?php
    if (empty($form) || !isset($form['_id'])) {
        echo lang('New Research Topic', 'Neuer Forschungsbereich');
    } else {
        echo lang('Edit Research Topic', 'Forschungsbereich bearbeiten');
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
                <small class="font-weight-bold">ID:</small><br>
                <?= $form['id'] ?>
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
            </fieldset>
        </div>
    </div>

    <hr>

    <h4>
        <?= lang('Description', 'Beschreibung') ?>
        in <span class="d-inline-flex"><?= lang('English', 'Englisch') ?> <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></span>
    </h4>
    <div class="form-group">
        <div id="description-quill"><?= $form['description'] ?? '' ?></div>
        <textarea name="values[description]" id="description" class="d-none" readonly><?= $form['description'] ?? '' ?></textarea>
        <script>
            quillEditor('description');
        </script>
    </div>


    <h4>
        <?= lang('Description', 'Beschreibung') ?>
        in <span class="d-inline-flex"><?= lang('German', 'Deutsch') ?> <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></span>
    </h4>
    <div class="form-group">
        <div id="description_de-quill"><?= $form['description_de'] ?? '' ?></div>
        <textarea name="values[description_de]" id="description_de" class="d-none"><?= $form['description_de'] ?? '' ?></textarea>

        <script>
            quillEditor('description_de');
        </script>
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