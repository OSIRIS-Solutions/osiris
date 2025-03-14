<?php

/**
 * Page to add new projects
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /projects/new
 *
 * @package     OSIRIS
 * @since       1.2.2
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once BASEPATH . "/php/Project.php";
$Project = new Project();

$form = $form ?? array();
$type = $type ?? $_GET['type'] ?? $form['type'] ?? 'Drittmittel';
$fields = $Project->getFields($type);

$new_project = empty($form) || !isset($form['_id']);

if ($new_project) {
    $formaction = ROOTPATH . "/crud/projects/create";
    $url = ROOTPATH . "/projects/view/*";
} else {
    $formaction = ROOTPATH . "/crud/projects/update/" . $form['_id'];
    $url = ROOTPATH . "/projects/view/" . $form['_id'];
}

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


// load defined vocabularies
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

?>

<script src="<?= ROOTPATH ?>/js/quill.min.js?v=<?= CSS_JS_VERSION ?>"></script>

<div class="container w-600">

    <?php if ($new_project) { ?>
        <h3 class="title">
            <?= lang('Add new project', 'Neues Projekt') ?>
        </h3>
    <?php } elseif ($type == 'Teilprojekt') { ?>
        <h3 class="title">
            <?= lang('Add subproject', 'Teilprojekt hinzufügen') ?>
        </h3>
    <?php } else { ?>
        <h3 class="title">
            <?= lang('Edit project', 'Projekt bearbeiten') ?>
        </h3>
    <?php } ?>


    <!-- only new projects can be changed -->
    <?php if ($new_project) { ?><?php } ?>
    <?php
    // get current url without query string
    $current_url = strtok($_SERVER["REQUEST_URI"], '?');
    ?>


    <?php if ($type != 'Teilprojekt') { ?>
        <div class="select-btns">
            <a href="<?= $current_url ?>?type=Drittmittel" class="btn select text-danger <?= $type == 'Drittmittel' ? 'active' : '' ?>">
                <i class="ph ph-hand-coins"></i>
                <?= lang('Third-party funded', 'Drittmittelprojekt') ?>
            </a>
            <!-- <a href="<?= $current_url ?>?type=Eigenfinanziert" class="btn select text-signal <?= $type == 'Eigenfinanziert' ? 'active' : '' ?>">
            <i class="ph ph-piggy-bank"></i>
            <?= lang('Self-funded', 'Eigenfinanziert') ?>
        </a> -->
            <a href="<?= $current_url ?>?type=Stipendium" class="btn select text-success <?= $type == 'Stipendium' ? 'active' : '' ?>">
                <i class="ph ph-tip-jar"></i>
                <?= lang('Scholarship', 'Stipendium') ?>
            </a>
        </div>
    <?php } ?>

    <form action="<?= $formaction ?>" method="post" id="project-form">
        <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">
        <input type="hidden" class="hidden" name="values[type]" value="<?= $type ?>">

        <div class="box padded" id="">
            <?php if ($type == 'Teilprojekt') { ?>
                <!-- add parent project info -->
                <input type="hidden" class="hidden" name="values[parent]" value="<?= $form['parent'] ?>">
                <input type="hidden" class="hidden" name="values[parent_id]" value="<?= $form['parent_id'] ?>">

                <h6 class="mt-0">
                    <?= lang('Parent project', 'Übergeordnetes Projekt') ?>
                </h6>

                <?php
                $Project->setProjectById($form['parent_id']);
                echo $Project->widgetLarge($user = null, $external = true);
                ?>
                <br>

            <?php } ?>

            <h6 class="mt-0">
                <?= lang('General information', 'Allgemeine Informationen') ?>
            </h6>

            <div class="form-group floating-form">
                <input type="text" class="form-control" name="values[name]" id="name" required value="<?= val('name') ?>" maxlength="30" placeholder="Short title">
                <label for="project" class="required">
                    <?= lang('Short title', 'Kurztitel') ?>
                </label>
            </div>

            <div class="form-group">
                <div class=" lang-<?= lang('en', 'de') ?>">
                    <label for="title" class="required floating-title">
                        <?php if ($type == 'Teilprojekt') {
                            echo lang('Full title of the subproject / work package', 'Voller Titel des Teilprojektes / Arbeitspaketes');
                        } else {
                            echo lang('Full title of the project', 'Voller Titel des Projekts');
                        } ?>
                    </label>

                    <div class="form-group title-editor" id="title-editor"><?= $form['title'] ?? '' ?></div>
                    <input type="text" class="form-control hidden" name="values[title]" id="title" required value="<?= val('title') ?>">
                </div>

                <script>
                    initQuill(document.getElementById('title-editor'));
                </script>
            </div>


            <?php if (in_array('time', $fields)) { ?>
                <div class="row row-eq-spacing mt-0 align-items-end ">
                    <div class="col-sm-4 floating-form">
                        <input type="date" class="form-control" name="values[start]" value="<?= valueFromDateArray(val('start')) ?>" id="start" required>

                        <label for="start" class="required">
                            Projektbeginn
                        </label>
                    </div>
                    <div class="col-sm-4">
                        <span class="floating-title">
                            <?= lang('Shortcut Length', 'Schnell-Auswahl Laufzeit') ?>
                        </span>
                        <div class="btn-group w-full">
                            <div class="btn" onclick="timeframe(36)"><?= lang('3 yr', '3 J') ?></div>
                            <!-- <div class="btn"><?= lang('2 yr', '2 J') ?></div> -->
                            <div class="btn" onclick="timeframe(12)"><?= lang('1 yr', '1 J') ?></div>
                            <div class="btn" onclick="timeframe(6)"><?= lang('6 mo', '6 Mo') ?></div>
                        </div>
                    </div>
                    <div class="col-sm-4 floating-form">
                        <input type="date" class="form-control" name="values[end]" value="<?= valueFromDateArray(val('end')) ?>" id="end" required>

                        <label for="end" class="required">
                            Projektende
                        </label>
                    </div>
                </div>

            <?php } ?>


            <script>
                function timeframe(month) {
                    let startField = document.querySelector('#start');
                    let start = startField.valueAsDate;
                    if (start == '' || start === null) {
                        toastError(lang('Please select a start date first.', 'Bitte wähle zuerst ein Startdatum.'))
                        return;
                    }

                    let end = new Date(start.setMonth(start.getMonth() + month));
                    end.setDate(end.getDate() - 1);
                    let endField = document.querySelector('#end');
                    endField.valueAsDate = end;
                }
            </script>


            <?php if (in_array('purpose', $fields)) { ?>
                <div class="form-group floating-form">
                    <select class="form-control" name="values[purpose]" id="purpose">
                        <?php
                        $vocab = $Vocabulary->getValues('project-purpose');
                        foreach ($vocab as $v) { ?>
                            <option value="<?= $v['id'] ?>" <?= sel('funder', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                        <?php } ?>
                    </select>
                    <label for="purpose">
                        <?= lang('Purpose of the project', 'Zwecks des Projekts') ?>
                    </label>
                </div>
            <?php } ?>


            <?php if (in_array('internal_number', $fields)) { ?>
                <div class="form-group floating-form">
                    <input type="number" class="form-control" name="values[internal_number]" id="internal_number" value="<?= val('internal_number') ?>" placeholder="1234">

                    <label for="internal_number">
                        <?= lang('Kostenträger') ?>
                    </label>
                </div>
            <?php } ?>


            <?php if (array_intersect(['contact', 'scholar', 'supervisor'], $fields)) { ?>

                <h6>
                    <?= lang('Persons', 'Personen') ?>
                </h6>


                <?php if (in_array('contact', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" id="username" name="values[contact]" required autocomplete="off">
                            <?php
                            $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ["last" => 1]]);
                            foreach ($userlist as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == ($form['contact'] ?? $user) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                        <label class="required element-author" for="username">
                            <?= lang('Applicant', 'Antragstellende Person') ?>
                        </label>
                        <small class="text-muted">
                            <?= lang('More persons may be added later', 'Weitere Personen können später hinzugefügt werden') ?>
                        </small>
                    </div>
                <?php } ?>


                <?php if (in_array('scholar', $fields)) { ?>
                    <div class="form-group floating-form">
                        <label class="required element-author" for="username">
                            <?= lang('Scholar', 'Stipendiat:in') ?>
                        </label>
                        <select class="form-control" id="username" name="values[scholar]" required autocomplete="off">
                            <?php
                            $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ["last" => 1]]);
                            foreach ($userlist as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == ($form['scholar'] ?? $user) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>


                <?php if (in_array('supervisor', $fields)) {

                    $selected = '';
                    if ($new_project) {
                        include_once BASEPATH . "/php/Groups.php";
                        // default: head of group
                        $dept = $USER['depts'] ?? [];
                        if (!empty($dept)) {
                            $Groups = new Groups();
                            $heads = $Groups->getGroup($dept[0])['head'] ?? array();
                            $selected = $heads[0] ?? '';
                        }
                    } else {
                        $selected = $form['supervisor'] ?? '';
                    }

                ?>
                    <div class="form-group floating-form">
                        <select class="form-control" id="username" name="values[supervisor]" required autocomplete="off">
                            <?php
                            $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ["last" => 1]]);
                            foreach ($userlist as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == $selected ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                        <label class="required element-author" for="username">
                            <?= lang('Supervisor', 'Betreuende Person') ?>
                        </label>
                    </div>
                <?php } ?>

            <?php } ?>



            <?php if (array_intersect(['funder', 'funding_organization', 'funding_number', 'role', 'coordinator'], $fields)) { ?>

                <h6>
                    <?= lang('Funding', 'Förderung') ?>
                </h6>
                <?php if (in_array('funder', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[funder]" value="<?= val('funder') ?>" required id="funder">
                            <?php
                            $vocab = $Vocabulary->getValues('funder');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= sel('funder', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="funder" class="required">
                            <?= lang('Third-party funder', 'Drittmittelgeber') ?>
                        </label>
                    </div>
                <?php } ?>

                <?php if (in_array('funding_organization', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[funding_organization]" value="<?= val('funding_organization') ?>" id="funding_organization" required placeholder="DFG">

                        <label for="funding_organization" class="required">
                            <?= lang('Funding organization', 'Zuwendungsgeber') ?>
                            <!-- Förderorganisation laut KDSF -->
                        </label>
                    </div>
                <?php } ?>

                <?php if (in_array('funding_number', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[funding_number]" value="<?= val('funding_number') ?>" id="funding_number" placeholder="ABC123">
                        <label for="funding_number">
                            <?= lang('Funding reference number', 'Förderkennzeichen') ?>
                        </label>
                        <small class="text-muted"><?= lang('Multiple seperated by comma', 'Mehrere durch Komma getrennt') ?></small>
                    </div>
                <?php } ?>



                <div class="row row-eq-spacing">
                    <?php if (in_array('role', $fields)) { ?>
                        <div class="col floating-form">
                            <select class="form-control" name="values[role]" id="role">
                                <?php
                                $vocab = $Vocabulary->getValues('project-institute-role');
                                foreach ($vocab as $v) { ?>
                                    <option value="<?= $v['id'] ?>" <?= sel('funder', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                                <?php } ?>
                            </select>
                            <label for="role">
                                <?= lang('Role of', 'Rolle von') ?> <?= $Settings->get('affiliation') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (in_array('coordinator', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" class="form-control" name="values[coordinator]" id="coordinator" value="<?= val('coordinator', $Settings->get('affiliation')) ?>" placeholder="DSMZ">
                            <label for="coordinator">
                                <?= lang('Coordinator facility', 'Koordinator-Einrichtung') ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>




            <?php if (array_intersect(['scholarship', 'university'], $fields)) { ?>
                <h6>
                    <?= lang('Scholarship', 'Stipendium') ?>
                </h6>
                <div class="row row-eq-spacing mt-0">
                    <?php if (in_array('scholarship', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" class="form-control" name="values[scholarship]" id="scholarship" value="<?= val('scholarship') ?>" placeholder="DAAD">
                            <label for="scholarship">
                                <?= lang('Scholarship institution', 'Stipendiengeber') ?>
                            </label>
                        </div>
                    <?php } ?>

                    <?php if (in_array('university', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" class="form-control" name="values[university]" id="university" value="<?= val('university') ?>" placeholder="TU Braunschweig">
                            <label for="university">
                                <?= lang('Partner University', 'Partner-Universität') ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>


            <?php if (array_intersect(['grant_sum_proposed', 'grant_income_proposed', 'grant_subproject_proposed'], $fields)) { ?>

                <h6>
                    <?= lang('Proposed grant sum', 'Beantragte Fördersumme') ?> in EURO
                </h6>
                <div class="row row-eq-spacing mt-0">

                    <?php if (in_array('grant_sum_proposed', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="number" step="1" class="form-control" name="values[grant_sum_proposed]" id="grant_sum_proposed" value="<?= val('grant_sum_proposed') ?>" placeholder="112345">
                            <label for="grant_sum_proposed">
                                <?= lang('Total', 'Insgesamt') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (in_array('grant_income_proposed', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="number" step="1" class="form-control" name="values[grant_income_proposed]" id="grant_income_proposed" value="<?= val('grant_income_proposed') ?>" placeholder="112345">

                            <label for="grant_income_proposed">
                                <?= lang('Institute', 'Institut') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (in_array('grant_subproject_proposed', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="number" step="1" class="form-control" name="values[grant_subproject_proposed]" id="grant_subproject_proposed" value="<?= val('grant_subproject_proposed') ?>" placeholder="1234">

                            <label for="grant_subproject_proposed">
                                <?= lang('Subproject', 'Teilprojekt') ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>



            <?php if (array_intersect(['grant_sum', 'grant_income', 'grant_subproject'], $fields)) { ?>

                <h6>
                    <?= lang('Grant sum', 'Bewilligungssumme') ?> in EURO
                </h6>
                <div class="row row-eq-spacing mt-0">

                    <?php if (in_array('grant_sum', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="number" step="1" class="form-control" name="values[grant_sum]" id="grant_sum" value="<?= val('grant_sum') ?>" placeholder="1234">
                            <label for="grant_sum">
                                <?= lang('Total', 'Insgesamt') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (in_array('grant_income', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="number" step="1" class="form-control" name="values[grant_income]" id="grant_income" value="<?= val('grant_income') ?>" placeholder="1234">
                            <label for="grant_income">
                                <?= lang('Institute', 'Institut') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (in_array('grant_subproject', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="number" step="1" class="form-control" name="values[grant_subproject]" id="grant_subproject" value="<?= val('grant_subproject') ?>" placeholder="1234">
                            <label for="grant_subproject">
                                <?= lang('Subproject', 'Teilprojekt') ?>
                            </label>
                        </div>
                    <?php } ?>

                </div>
            <?php } ?>



            <?php if (array_intersect(['public', 'abstract', 'website'], $fields)) { ?>
                <h6>
                    <?= lang('Outreach') ?>
                </h6>

                <?php if (in_array('public', $fields)) { ?>
                    <div class="form-group ">
                        <div class="custom-checkbox">
                            <input type="checkbox" id="public-check" <?= val('public', false) ? 'checked' : '' ?> name="values[public]">
                            <label for="public-check">
                                <?= lang('Approval of the internet presentation of the approved project', 'Zustimmung zur Internetpräsentation des bewilligten Vorhabens') ?>
                            </label>
                        </div>
                    </div>
                <?php } ?>

                <?php if (in_array('abstract', $fields)) { ?>
                    <div class="form-group floating-form">
                        <textarea name="values[abstract]" id="abstract" cols="30" rows="5" class="form-control" placeholder="Abstract"><?= val('abstract') ?></textarea>
                        <label for="abstract" class="">
                            <?= lang('Abstract', 'Kurzbeschreibung') ?>
                        </label>
                    </div>
                <?php } ?>


                <?php if (in_array('website', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[website]" id="website" value="<?= val('website') ?>" placeholder="https://example.com">
                        <label for="website">
                            <?= lang('Project website', 'Webseite des Projekts') ?>
                        </label>
                        <small class="text-muted">
                            <?= lang('Please enter full ULR (incl. http...)', 'Bitte vollständige URL angeben (inkl. http...)') ?>
                        </small>
                    </div>
                <?php } ?>

            <?php } ?>


            <?php
            if (in_array('kdsf-ffk', $fields)) {
                include_once BASEPATH . "/components/kdsf-ffk-select.php";
            }
            ?>

            <?php if (in_array('countries', $fields)) {
                $countries = $form['countries'] ?? [];
                include_once BASEPATH . "/php/Country.php";
            ?>

                <b>
                    <?= lang('Countries you will do research on/in:', 'Länder über/in denen Forschung betrieben wird:') ?>
                </b>


                <table class="table">
                    <thead>
                        <tr>
                            <th><?= lang('Country', 'Land') ?></th>
                            <th><?= lang('Role', 'Rolle') ?></th>
                            <th><?= lang('Action', 'Aktion') ?></th>
                        </tr>
                    </thead>
                    <tbody id="country-list">
                        <?php foreach ($countries as $country) {
                            if (empty($country) || !isset($country['iso'])) continue;
                            $iso = $country['iso'];
                            $role = $country['role'] ?? 'both';
                        ?>
                            <tr>
                                <td><?= Country::get($iso) ?></td>
                                <td><?= $role ?></td>
                                <td>
                                    <a onclick="$(this).closest('tr').remove()"><?= lang('Remove', 'Entfernen') ?></a>
                                    <input type="text" name="values[countries][]" value="<?= $iso ?>;<?= $role ?>" hidden>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <div class="input-group small d-inline-flex w-auto">
                                    <select id="add-country" class="form-control">
                                        <option value="" disabled checked><?= lang('Please select a country', 'Bitte wähle ein Land aus') ?></option>
                                        <?php foreach (Country::COUNTRIES as $iso => $name) { ?>
                                            <option value="<?= $iso ?>"><?= $name ?></option>
                                        <?php } ?>
                                    </select>
                                    <select id="add-country-role" class="form-control">
                                        <option value="" disabled checked><?= lang('Please select a role', 'Bitte wähle einen Typ aus') ?></option>
                                        <option value="source"><?= lang('Source', 'Quellland') ?></option>
                                        <option value="target"><?= lang('Target', 'Zielland') ?></option>
                                        <option value="both"><?= lang('Both', 'Beide') ?></option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn secondary" type="button" onclick="addCountry(event);">
                                            <i class="ph ph-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <script>
                    function addCountry(event) {
                        var el = $('#add-country')
                        var data = el.val()
                        var type = $('#add-country-role').val()
                        if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                            event.preventDefault();
                            if (data) {
                                let tr = $('<tr>')
                                tr.append('<td>' + el.find('option:selected').text() + '</td>')
                                tr.append('<td>' + type + '</td>')
                                tr.append('<td><a onclick="$(this).closest(\'tr\').remove()"><?= lang('Remove', 'Entfernen') ?></a><input type="text" name="values[countries][]" value="' + data + ';' + type + '" hidden></td>')
                                $('#country-list').append(tr)
                            }
                            $(el).val('')
                            return false;
                        }
                    }
                </script>
            <?php } ?>



            <?php if (in_array('nagoya', $fields) && $Settings->featureEnabled('nagoya')) {
                $countries = $form['nagoya_countries'] ?? [];
                $nagoya = $form['nagoya'] ?? 'no';
                include_once BASEPATH . "/php/Country.php";
            ?>

                <h6>
                    <?= lang('Nagoya Protocol') ?>
                </h6>

                <div class="form-group">
                    <label for="nagoya">
                        <?= lang('
                            Do you plan to collect or receive genetic resources (biological samples) from outside of Germany over the course of this project?
                            ', 'Planst du, im Rahmen dieses Projekts genetische Ressourcen (biologische Proben) von außerhalb Deutschlands zu sammeln oder zu erhalten? ') ?>
                    </label>
                    <div>
                        <input type="radio" name="values[nagoya]" id="nagoya-yes" value="yes" <?= ($nagoya == 'yes') ? 'checked' : '' ?>>
                        <label for="nagoya-yes">Yes</label>
                        <input type="radio" name="values[nagoya]" id="nagoya-no" value="no" <?= ($nagoya == 'no') ? 'checked' : '' ?>>
                        <label for="nagoya-no">No</label>
                    </div>

                    <div id="ressource-nagoya" style="display: <?= ($nagoya == 'yes') ? 'block' : 'none' ?>;">

                        <b>
                            <?= lang('Please list all countries:', 'Liste bitte alle Länder auf:') ?>
                        </b>

                        <div class="author-widget" id="nagoya-country-widget">
                            <div class="author-list p-10" id="nagoya-country-list">
                                <?php foreach ($countries as $iso) { ?>
                                    <div class='author'>
                                        <input type='hidden' name='values[nagoya_countries][]' value='<?= $iso ?>'>
                                        <?= Country::get($iso) ?>
                                        <a onclick="$(this).closest('.author').remove()">&times;</a>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="footer">
                                <div class="input-group small d-inline-flex w-auto">
                                    <select id="add-nagoya-country" class="form-control">
                                        <option value="" disabled checked><?= lang('Please select a country', 'Bitte wähle ein Land aus') ?></option>
                                        <?php foreach (Country::COUNTRIES as $iso => $name) { ?>
                                            <option value="<?= $iso ?>"><?= $name ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn secondary" type="button" onclick="addNagoyaCountry(event);">
                                            <i class="ph ph-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <script>
                                function addNagoyaCountry(event) {
                                    var el = $('#add-nagoya-country')
                                    var data = el.val()
                                    if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                                        event.preventDefault();
                                        if (data) {
                                            $('#nagoya-country-list').append('<div class="author"><input type="hidden" name="values[nagoya_countries][]" value="' + data + '">' + el.find('option:selected').text() + '<a onclick="$(this).closest(\'.author\').remove()">&times;</a></div>')
                                        }
                                        $(el).val('')
                                        return false;
                                    }
                                }
                            </script>

                        </div>
                    </div>

                    <script>
                        document.getElementById('nagoya-yes').addEventListener('change', function() {
                            document.getElementById('ressource-nagoya').style.display = 'block';
                        });
                        document.getElementById('nagoya-no').addEventListener('change', function() {
                            document.getElementById('ressource-nagoya').style.display = 'none';
                        });
                    </script>

                </div>
            <?php } ?>



            <?php if (array_intersect(['personnel', 'in-kind', 'ressources'], $fields)) { ?>
                <h6>
                    <?= lang('Resources and Personnel', 'Ressourcen und Personal') ?>
                </h6>

                <?php if (in_array('personnel', $fields)) { ?>
                    <div class="form-group floating-form">
                        <textarea name="values[personnel]" id="personnel" cols="30" rows="2" class="form-control" placeholder="1 Doktorand:in"><?= val('personnel') ?></textarea>

                        <label for="personnel">
                            <?= lang('Personnel measures planned', 'Geplante Personalmaßnahmen') ?>
                        </label>
                        <small class="text-muted">
                            Einstellungen/Verlängerungen in Personenmonaten & Kategorie
                        </small>
                    </div>
                    <div class="form-group floating-form">
                        <textarea name="values[in-kind]" id="in-kind" cols="30" rows="2" class="form-control" placeholder="Antragsteller 5%"><?= val('in-kind') ?></textarea>

                        <label for="in-kind">
                            <?= lang('In-kind personnel', 'Umfang des geplanten eigenen Personaleinsatzes') ?>
                        </label>
                        <small class="text-muted">
                            Nachrichtliche Angaben in % unter Nennung der mitarbeitenden Personen (z.B. Antragsteller 10%, ABC 15%, etc.)
                        </small>
                    </div>
                <?php } ?>

                <?php if (in_array('ressources', $fields)) {
                    $res = $form['ressources'] ?? [];
                ?>
                    <!-- each: Sachmittel, Personalmittel, Raumkapitäten, sonstige Ressourcen -->
                    <div class="ressources">
                        <div class="form-group">
                            <label for="ressource1">
                                <?= lang('Additional material resources', 'Zusätzliche Sachmittel') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][material]" id="material-yes" value="yes" <?= ($res['material'] ?? false) ? 'checked' : '' ?>>
                                <label for="material-yes">Yes</label>
                                <input type="radio" name="values[ressources][material]" id="material-no" value="no" <?= ($res['material'] ?? false) ? '' : 'checked' ?>>
                                <label for="material-no">No</label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][material_details]" id="ressource-material" style="display: <?= ($res['material'] ?? false) ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['material_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('material-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-material').style.display = 'block';
                                });
                                document.getElementById('material-no').addEventListener('change', function() {
                                    document.getElementById('ressource-material').style.display = 'none';
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label for="ressource2">
                                <?= lang('Additional personnel resources', 'Zusätzliche Personalmittel') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][personnel]" id="personnel-yes" value="yes" <?= ($res['personnel'] ?? false) ? 'checked' : '' ?>>
                                <label for="personnel-yes">Yes</label>
                                <input type="radio" name="values[ressources][personnel]" id="personnel-no" value="no" <?= ($res['personnel'] ?? false) ? '' : 'checked' ?>>
                                <label for="personnel-no">No</label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][personnel_details]" id="ressource-personnel" style="display: <?= ($res['personnel'] ?? false) ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['personnel_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('personnel-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-personnel').style.display = 'block';
                                });
                                document.getElementById('personnel-no').addEventListener('change', function() {
                                    document.getElementById('ressource-personnel').style.display = 'none';
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <label for="ressource3">
                                <?= lang('Additional room capacities', 'Zusätzliche Raumkapazitäten') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][room]" id="room-yes" value="yes" <?= ($res['room'] ?? false) ? 'checked' : '' ?>>
                                <label for="room-yes">Yes</label>
                                <input type="radio" name="values[ressources][room]" id="room-no" value="no" <?= ($res['room'] ?? false) ? '' : 'checked' ?>>
                                <label for="room-no">No</label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][room_details]" id="ressource-room" style="display: <?= ($res['room'] ?? false) ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['room_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('room-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-room').style.display = 'block';
                                });
                                document.getElementById('room-no').addEventListener('change', function() {
                                    document.getElementById('ressource-room').style.display = 'none';
                                });
                            </script>

                        </div>
                        <div class="form-group">
                            <label for="ressource4">
                                <?= lang('Other resources', 'Sonstige Ressourcen') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][other]" id="other-yes" value="yes" <?= ($res['other'] ?? false) ? 'checked' : '' ?>>
                                <label for="other-yes">Yes</label>
                                <input type="radio" name="values[ressources][other]" id="other-no" value="no" <?= ($res['other'] ?? false) ? '' : 'checked' ?>>
                                <label for="other-no">No</label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][other_details]" id="ressource-other" style="display: <?= ($res['other'] ?? false) ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['other_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('other-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-other').style.display = 'block';
                                });
                                document.getElementById('other-no').addEventListener('change', function() {
                                    document.getElementById('ressource-other').style.display = 'none';
                                });
                            </script>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

            <!-- if topics are registered, you can choose them here -->
            <?php $Settings->topicChooser(DB::doc2Arr($form['topics'] ?? [])) ?>

            <button class="btn secondary" type="submit" id="submit-btn">
                <i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?>
            </button>
        </div>
    </form>
</div>