<?php

/**
 * Edit details of a organization
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
    $formaction = ROOTPATH . "/crud/organizations/create";
    $url = ROOTPATH . "/organizations/view/*";
} else {
    $formaction = ROOTPATH . "/crud/organizations/update/" . $form['_id'];
    $url = ROOTPATH . "/organizations/view/" . $form['_id'];
}

?>

<div class="container" style="max-width: 80rem;">
    <?php if (empty($form) || !isset($form['_id'])) { ?>

        <h1 class="title">
            <i class="ph ph-building-office" aria-hidden="true"></i>
            <?= lang('New Organization', 'Neue Organisation') ?>
        </h1>

    <?php } else { ?>
        <h1 class="title">
            <i class="ph ph-building-office" aria-hidden="true"></i>
            <?= lang('Edit Organization', 'Organisation bearbeiten') ?>
        </h1>
    <?php } ?>
    <form action="<?= $formaction ?>" method="post" class="form">
        <input type="hidden" name="redirect" value="<?= $url ?>">

        <div class="form-group">
            <label for="ror">
                <?= lang('ROR-ID') ?>
                <span class="badge kdsf">KDSF-B-15-1</span>
            </label>

            <div class="input-group">
                <input type="text" class="form-control" name="values[ror]" id="ror" value="<?= $form['ror'] ?? '' ?>">
                <div class="input-group-append" data-toggle="tooltip" data-title="<?= lang('Retreive updated information from ROR', 'Aktualisiere die Daten von ROR') ?>">
                    <button class="btn" type="button" onclick="getRORid($('#ror').val())"><i class="ph ph-arrows-clockwise"></i></button>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="name" class="required">
                <?= lang('Name of the organization', 'Name der Organisation') ?>
                <span class="badge kdsf">KDSF-B-15-2</span>
            </label>
            <input type="text" class="form-control" name="values[name]" id="name" required value="<?= $form['name'] ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="type" class="required">
                <?= lang('Type of organization', 'Art der Organisation') ?>
                <span class="badge kdsf">KDSF-B-15-4</span>
            </label>
            <select name="values[type]" id="type" class="form-control" required>
                <option value="" disabled <?= sel('type', '') ?>><?= lang('Select type', 'Art auswählen') ?></option>
                <option value="Education" <?= sel('type', 'Education') ?>><?= lang('Education', 'Bildung') ?></option>
                <option value="Funder" <?= sel('type', 'Funder') ?>><?= lang('Funder', 'Förderer') ?></option>
                <option value="Healthcare" <?= sel('type', 'Healthcare') ?>><?= lang('Healthcare', 'Gesundheitswesen') ?></option>
                <option value="Company" <?= sel('type', 'Company') ?>><?= lang('Company', 'Unternehmen') ?></option>
                <option value="Archive" <?= sel('type', 'Archive') ?>><?= lang('Archive', 'Archiv') ?></option>
                <option value="Nonprofit" <?= sel('type', 'Nonprofit') ?>><?= lang('Non-profit', 'Gemeinnützig') ?></option>
                <option value="Government" <?= sel('type', 'Government') ?>><?= lang('Government', 'Regierung') ?></option>
                <option value="Facility" <?= sel('type', 'Facility') ?>><?= lang('Facility', 'Einrichtung') ?></option>
                <option value="Other" <?= sel('type', 'Other') ?>><?= lang('Other', 'Sonstiges') ?></option>
            </select>
        </div>


        <div class="row row-eq-spacing">

            <div class="col-sm">
                <label for="location">
                    <?= lang('Location', 'Standort') ?>
                </label>
                <input type="text" class="form-control" name="values[location]" id="location" value="<?= $form['location'] ?? '' ?>">
            </div>

            <div class="col-sm">
                <label for="country">
                    <?= lang('Country', 'Land') ?>
                    <span class="badge kdsf">KDSF-B-15-3</span>
                </label>
                <select name="values[country]" id="country" class="form-control">
                    <option value="" disabled <?= sel('country', '') ?>><?= lang('Select country', 'Land auswählen') ?></option>
                    <?php foreach ($DB->getCountries(lang('name', 'name_de')) as $key => $value) { ?>
                        <option value="<?= $key ?>" <?= sel('country', $key) ?>><?= $value ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row row-eq-spacing mb-0">
            <div class="col-sm">
                <label for="lat">
                    <?= lang('Latitude', 'Breitengrad') ?>
                </label>
                <input type="number" class="form-control" name="values[lat]" id="lat" value="<?= $form['lat'] ?? '' ?>" step="0.00001">
            </div>
            <div class="col-sm">
                <label for="lng">
                    <?= lang('Longitude', 'Längengrad') ?>
                </label>
                <input type="number" class="form-control" name="values[lng]" id="lng" value="<?= $form['lng'] ?? '' ?>" step="0.00001">
            </div>
        </div>
        <small class="text-muted">
            <?=lang('Geographical coordinates are required to correctly display the organisation on a map.', 'Die geografischen Koordinaten werden benötigt, um die Organisation auf einer Karte korrekt darzustellen.')?>
        </small>
        <br>
        <br>

        <script>
            $('#ror').on('change', function() {
                const ror = $(this).val();
                $(this).removeClass('is-invalid');
                $('#submit').prop('disabled', false);
                if (!ror || ror.length === 0) {
                    return;
                }

                // check if ROR is missing the https://
                if (ror.match(/^0[a-z|0-9]{6}[0-9]{2}$/)) {
                    $(this).val('https://ror.org/' + ror);
                    toastWarning(lang('ROR ID is missing the URL prefix. We updated that for you, please check.', 'Der ROR-ID fehlte der URL-Präfix. Wir haben das für dich geändert, bitte überprüfe es.'));
                    return;
                }

                // check if ROR is valid
                const regex = /^(https?:\/\/)?(www\.)?ror\.org\/0[a-z|0-9]{6}[0-9]{2}$/;
                if (!regex.test(ror)) {
                    toastError(lang('Invalid ROR ID format. Please enter a valid ROR ID.', 'Ungültige ROR-ID, bitte überprüfen Sie die Eingabe.'));
                    $(this).addClass('is-invalid');
                    $('#submit').prop('disabled', true);
                    return;
                }

            });


            function getRORid(ror) {
                console.info('getRORid')
                if (!ror) {
                    toastError('Please provide a ROR ID')
                    return
                }
                var url = 'https://api.ror.org/organizations/' + ror.trim()
                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    beforeSend: function() {
                        $('.loader').addClass('show')
                    },
                    success: function(response) {
                        $('.loader').removeClass('show')
                        console.log(response);
                        if (response.id) {
                            let address = response.addresses[0] ?? {}
                            $('#name').val(response.name)
                            $('#type').val(response.types[0])
                            $('#location').val(address.city + ', ' + response.country.country_name)
                            $('#country').val(response.country.country_code)
                            $('#lat').val(address.lat)
                            $('#lng').val(address.lng)
                        } else {
                            toastError('ROR ID not found')
                        }
                    },
                    error: function(response) {
                        var errors = response.responseJSON.errors
                        if (errors) {
                            toastError(errors.join(', '))
                        } else {
                            toastError(response.responseText)
                        }
                        $('.loader').removeClass('show')
                    }
                })
            }
        </script>

        <button type="submit" class="btn secondary" id="submit"><?= lang('Save', 'Speichern') ?></button>
    </form>
</div>

<script src="<?= ROOTPATH ?>/js/collaborators.js?v=<?= CSS_JS_VERSION ?>"></script>