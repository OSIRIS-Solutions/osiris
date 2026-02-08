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
        return e($val);
    }
    if ($val instanceof MongoDB\Model\BSONArray) {
        return implode(',', DB::doc2Arr($val));
    }
    return $val;
}

function sel($index, $value)
{
    return strtolower(val($index)) == strtolower($value) ? 'selected' : '';
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
            <i class="ph-duotone ph-building-office" aria-hidden="true"></i>
            <?= lang('New Organisation', 'Neue Organisation') ?>
        </h1>

    <?php } else { ?>
        <h1 class="title">
            <i class="ph-duotone ph-building-office" aria-hidden="true"></i>
            <?= lang('Edit Organisation', 'Organisation bearbeiten') ?>
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
                    <button class="btn" type="button" onclick="updateOrgByROR($('#ror').val())"><i class="ph ph-arrows-clockwise"></i></button>
                </div>
            </div>
        </div>

        <h5>
            <?= lang('Basic Information', 'Grundinformationen') ?>
        </h5>

        <div class="form-group">
            <label for="name" class="required">
                <?= lang('Name of the organisation', 'Name der Organisation') ?>
                <span class="badge kdsf">KDSF-B-15-2</span>
            </label>
            <input type="text" class="form-control" name="values[name]" id="name" required value="<?= $form['name'] ?? '' ?>">
        </div>

        <div class="form-group">
            <label for="synonym-input"><?= lang('Synonyms / Alternative Names / Acronyms', 'Synonyme / alternative Namen / Akronyme') ?></label>

            <div id="list-widget" class="list-widget" data-name="values[synonyms][]">
                <input
                    id="synonym-input"
                    class="list-widget-input"
                    type="text"
                    autocomplete="off"
                    placeholder="Synonym eingeben und Enter drücken" />
            </div>
        </div>

        <div class="form-group">
            <label for="type" class="required">
                <?= lang('Type of organisation', 'Art der Organisation') ?>
                <span class="badge kdsf">KDSF-B-15-4</span>
            </label>
            <select name="values[type]" id="type" class="form-control" required>
                <option value="" disabled <?= sel('type', '') ?>><?= lang('Select type', 'Art auswählen') ?></option>
                <option value="education" <?= sel('type', 'education') ?>><?= lang('Education', 'Bildung') ?></option>
                <option value="funder" <?= sel('type', 'funder') ?>><?= lang('Funder', 'Förderer') ?></option>
                <option value="healthcare" <?= sel('type', 'healthcare') ?>><?= lang('Healthcare', 'Gesundheitswesen') ?></option>
                <option value="company" <?= sel('type', 'company') ?>><?= lang('Company', 'Unternehmen') ?></option>
                <option value="archive" <?= sel('type', 'archive') ?>><?= lang('Archive', 'Archiv') ?></option>
                <option value="nonprofit" <?= sel('type', 'nonprofit') ?>><?= lang('Non-profit', 'Gemeinnützig') ?></option>
                <option value="government" <?= sel('type', 'government') ?>><?= lang('Government', 'Regierung') ?></option>
                <option value="facility" <?= sel('type', 'facility') ?>><?= lang('Facility', 'Einrichtung') ?></option>
                <option value="other" <?= sel('type', 'other') ?>><?= lang('Other', 'Sonstiges') ?></option>
            </select>
        </div>


        <h5>
            <?= lang('Location Information', 'Standortinformationen') ?>
        </h5>
        <div class="row row-eq-spacing mt-0">

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


        <div class="row row-eq-spacing align-items-end">
            <div class="col-sm">
                <label for="lat">
                    <?= lang('Latitude', 'Breitengrad') ?>
                </label>
                <input type="number" class="form-control" name="values[lat]" id="lat" value="<?= $form['lat'] ?? '' ?>" step="any">
            </div>
            <div class="col-sm">
                <label for="lng">
                    <?= lang('Longitude', 'Längengrad') ?>
                </label>
                <input type="number" class="form-control" name="values[lng]" id="lng" value="<?= $form['lng'] ?? '' ?>" step="any">
            </div>
            <div class="col-sm">
                <button type="button" class="btn" onclick="getCoordinates('#location', '#country', '#lat', '#lng')">
                    <i class="ph ph-map-pin"></i>
                    <?= lang('Get coordinates by location', 'Koordinaten vom Standort ermitteln') ?>
                </button>
            </div>
        </div>

        <small class="text-muted">
            <?= lang('Geographical coordinates are required to correctly display the organisation on a map.', 'Die geografischen Koordinaten werden benötigt, um die Organisation auf einer Karte korrekt darzustellen.') ?>
        </small>
        <br><br>

        <script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= OSIRIS_BUILD ?>"></script>
        <script src="<?= ROOTPATH ?>/js/list-widget.js?v=<?= OSIRIS_BUILD ?>"></script>
        <script>
            $(function() {
                // Example init for this widget
                initListWidget($("#list-widget"), <?= json_encode($form['synonyms'] ?? []) ?>);
            });
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


            function updateOrgByROR(ror) {
                console.info('updateOrgByROR')
                if (!ror) {
                    toastError('Please provide a ROR ID')
                    return
                }
                var url = 'https://api.ror.org/v2/organizations/' + ror.trim()
                $.ajax({
                    type: "GET",
                    url: url,
                    dataType: "json",
                    beforeSend: function() {
                        $('.loader').addClass('show')
                    },
                    success: function(response) {
                        $('.loader').removeClass('show')
                        var org = translateROR(response)
                        if (org.ror) {
                            $('#name').val(org.name)
                            $('#type').val(org.type)
                            $('#location').val(org.location)
                            $('#country').val(org.country)
                            $('#lat').val(org.lat)
                            $('#lng').val(org.lng)
                            // update synonyms list widget
                            const synonyms = org.synonyms || []
                            const listWidget = $('#list-widget')
                            listWidget.find('.list-widget-item').remove()
                            initListWidget(listWidget, synonyms)
                            toastSuccess('Organization information updated from ROR')
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