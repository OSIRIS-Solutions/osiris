<?php

/**
 * Edit details of a infrastructure
 * Created in cooperation with DSMZ
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

include_once BASEPATH . "/php/Organization.php";

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return htmlspecialchars($val);
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
    $formaction = ROOTPATH . "/crud/infrastructures/create";
    $url = ROOTPATH . "/infrastructures/view/*";
} else {
    $formaction = ROOTPATH . "/crud/infrastructures/update/" . $form['_id'];
    $url = ROOTPATH . "/infrastructures/view/" . $form['_id'];
}

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
?>

<script src="<?= ROOTPATH ?>/js/quill.min.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= CSS_JS_VERSION ?>"></script>

<h3 class="title">
    <?php
    if (empty($form) || !isset($form['_id'])) {
        echo lang('New Infrastructure', 'Neue Infrastruktur');
    } else {
        echo lang('Edit Infrastructure', 'Infrastruktur bearbeiten');
    }
    ?>
</h3>

<form action="<?= $formaction ?>" method="post" class="form">
    <input type="hidden" name="redirect" value="<?= $url ?>">

    <div class="form-group floating-form">
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

    <div class="row row-eq-spacing">
        <div class="col-md-6">
            <label for="start_date" class="required">
                <?= lang('Start', 'Anfang') ?> <span class="badge kdsf">KDSF-B-13-3</span>
            </label>
            <input type="date" class="form-control" name="values[start_date]" id="start_date" required value="<?= $form['start_date'] ?? '' ?>">
        </div>
        <div class="col-md-6">
            <label for="end_date">
                <?= lang('End', 'Ende') ?> <span class="badge kdsf">KDSF-B-13-4</span>
            </label>
            <input type="date" class="form-control" name="values[end_date]" id="end_date" value="<?= $form['end_date'] ?? '' ?>">
        </div>
    </div>

    <div class="row row-eq-spacing mb-0">
        <div class="col-md-6">
            <fieldset>
                <legend class="d-flex"><?= lang('English', 'Englisch') ?> <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></legend>
                <div class="form-group">
                    <label for="name" class="required">
                        <?= lang('Title', 'Titel') ?> (EN)
                        <span class="badge kdsf">KDSF-B-13-2</span>
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


    <label for="description">
        <?= lang('Description', 'Beschreibung') ?>
        <span class="badge kdsf">KDSF-B-13-11</span>
    </label>
    <div class="form-group">
        <div id="description-quill"><?= $form['description'] ?? '' ?></div>
        <textarea name="values[description]" id="description" class="d-none" readonly><?= $form['description'] ?? '' ?></textarea>
        <script>
            quillEditor('description');
        </script>
    </div>


    <div class="row row-eq-spacing">
        <div class="col-md-6">
            <label for="type" class="required">
                <?= lang('Category', 'Kategorie') ?>
                <span class="badge kdsf">KDSF-B-13-5</span>
            </label>
            <select name="values[type]" id="type" class="form-control" required>
                <?php
                $vocab = $Vocabulary->getValues('infrastructure-category');
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= sel('type', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="infrastructure_type" class="required">
                <?= lang('Type of infrastructure', 'Art der Infrastruktur') ?>
                <span class="badge kdsf">KDSF-B-13-6</span>
            </label>
            <select name="values[infrastructure_type]" id="infrastructure_type" class="form-control" required>
                <?php
                $vocab = $Vocabulary->getValues('infrastructure-type');
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= sel('type', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>
    </div>


    <div class="row row-eq-spacing">
        <div class="col-md-6">
            <label for="access" class="required">
                <?= lang('User Access', 'Art des Zugangs') ?>
                <span class="badge kdsf">KDSF-B-13-7</span>
            </label>
            <select name="values[access]" id="access" class="form-control" required>
            <?php
                $vocab = $Vocabulary->getValues('infrastructure-access');
                foreach ($vocab as $v) { ?>
                    <option value="<?= $v['id'] ?>" <?= sel('type', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <h6>
        <?= lang('Collaborative research infrastructure', 'Verbundforschungsinfrastruktur') ?>
    </h6>

    <?php
    $collaborative = $form['collaborative'] ?? false;
    ?>


    <div class="form-group">
        <label for="collaborative">
            <?= lang('Is this a collaborative research infrastructure?', 'Ist dies eine Verbundforschungsinfrastruktur?') ?>
            <span class="badge kdsf">KDSF-B-13-12</span>
        </label>
        <div>
            <input type="radio" name="values[collaborative]" id="collaborative-yes" value="yes" <?= ($collaborative) ? 'checked' : '' ?>>
            <label for="collaborative-yes">Yes</label>
            <input type="radio" name="values[collaborative]" id="collaborative-no" value="no" <?= (!$collaborative) ? 'checked' : '' ?>>
            <label for="collaborative-no">No</label>
        </div>

        <div id="form-collaborative" style="display: <?= ($collaborative) ? 'block' : 'none' ?>;" class="box padded">

            <?php
            $collab = $form['collaborators'] ?? [];
            $institute = $Settings->get('affiliation_details');
            $institute_name = $institute['name'] ?? $institute['id'] ?? 'Your Institute';
            $coordinator_institute = $form['coordinator_institute'] ?? false;
            $coordinator_organization = $form['coordinator_organization'] ?? null;
            ?>
            <div class="form-group my-10">
                <label for="collaborators">
                    <?= lang('Cooperation Partners', 'Ko-Betreiber:innen') ?>
                    <span class="badge kdsf">KDSF-B-13-15</span>
                </label>
                <table class="table simple">
                    <thead>
                        <tr>
                            <th><?= lang('Name', 'Name') ?></th>
                            <th><?= lang('Coordinator', 'Koordinator') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="collaborators">
                        <tr>
                            <td>
                                <b><?= $institute_name ?></b><br>
                                <small class="text-muted"><?= lang('This is your institute', 'Dies ist dein Institut') ?></small>
                            </td>
                            <td>
                                <div class="custom-radio">
                                    <input type="radio" name="values[coordinator]" id="coordinator" value="0" <?= ($coordinator_institute) ? 'checked' : '' ?>>
                                    <label for="coordinator" class="empty"></label>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <?php
                        $collaborators = $form['collaborators'] ?? [];
                        foreach ($collaborators as $org_id) {
                            $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                            if (empty($collab)) continue;
                            $is_coord = ($coordinator_organization == $collab['_id']);
                        ?>
                            <tr data-row="<?= $org_id ?>">
                                <td>
                                    <?= $collab['name'] ?>
                                    <br>
                                    <small class="text-muted">
                                        <?= $collab['location'] ?? null ?>
                                    </small>
                                    <input type="hidden" name="values[collaborators][]" value="<?= $org_id ?>" class="form-control">
                                </td>
                                <td>
                                    <div class="custom-radio">
                                        <input type="radio" name="values[coordinator]" id="coordinator-<?= $org_id ?>" value="<?= $org_id ?>" <?= ($is_coord) ? 'checked' : '' ?> required>
                                        <label for="coordinator-<?= $org_id ?>" class="empty"></label>
                                    </div>
                                </td>
                                <td><button type="button" class="btn danger remove-collab" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <div class="form-group mt-20 box padded bg-light">
                    <label for="organization-search"><?= lang('Add Cooperation Partner', 'Ko-Betreiber:innen hinzufügen') ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="organization-search" onkeydown="handleKeyDown(event)" placeholder="<?= lang('Search for an organization', 'Suche nach einer Organisation') ?>" autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn" type="button" onclick="getOrganization($('#organization-search').val())"><i class="ph ph-magnifying-glass"></i></button>
                        </div>
                    </div>
                    <p id="search-comment"></p>
                    <table class="table simple">
                        <tbody id="organization-suggest">
                        </tbody>
                    </table>
                    <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                    <script>
                        function handleKeyDown(event) {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                getOrganization($('#organization-search').val());
                            }
                        }
                    </script>
                </div>

            </div>
        </div>

        <script>
            document.getElementById('collaborative-yes').addEventListener('change', function() {
                document.getElementById('form-collaborative').style.display = 'block';
                // enable required for all collaborators
                const inputs = document.querySelectorAll('#collaborators input[type="radio"]');
                inputs.forEach(input => {
                    input.required = true;
                });
            });
            document.getElementById('collaborative-no').addEventListener('change', function() {
                document.getElementById('form-collaborative').style.display = 'none';
                // disable required for all collaborators
                const inputs = document.querySelectorAll('#collaborators input[type="radio"]');
                inputs.forEach(input => {
                    input.required = false;
                });
            });
        </script>

    </div>

    <button type="submit" class="btn secondary"><?= lang('Save', 'Speichern') ?></button>
</form>