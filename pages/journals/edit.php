<?php

/**
 * Page to add or edit journal
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal/add
 * @link        /journal/edit/<journal_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$oa = $data['oa'] ?? false;
?>

<?php include_once BASEPATH . '/header-editor.php'; ?>

<div class="container w-600 mw-full">

    <h1>
        <i class="ph-duotone ph-stack-plus"></i>
        <?php
        $label = $Settings->journalLabel();
        if ($id === null || empty($data)) {
            echo lang("Add $label",  "$label hinzufügen");
        } else {
            echo $data['journal'];
        }
        ?>
    </h1>

    <?php
    if ($id === null || empty($data)) {
        $formaction = ROOTPATH . "/crud/journal/create";
        $url = ROOTPATH . "/journal/view/*";
    } else {
        $formaction = ROOTPATH . "/crud/journal/update/$id";
        $url = ROOTPATH . "/journal/view/$id";
    }
    ?>

    <form action="<?= $formaction ?>" method="post">
        <input type="hidden" class="hidden" name="redirect" value="<?= $url ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

        <div class="form-group floating-form">
            <input type="text" name="values[journal]" id="journal" class="form-control" value="<?= $data['journal'] ?? '' ?>" required placeholder="Journal name">
            <label for="journal" class="required"><?= lang('Name', 'Name') ?></label>
        </div>
        <div class="form-group floating-form">
            <input type="text" name="values[abbr]" id="abbr" class="form-control" value="<?= $data['abbr'] ?? '' ?>" placeholder="Abbreviation">
            <label for="abbr"><?= lang('Abbreviation', 'Abkürzung') ?></label>
        </div>

        <div class="form-group floating-form">

            <div id="list-widget" class="list-widget" data-name="values[issn][]">
                <input
                    id="synonym-input"
                    class="list-widget-input"
                    type="text"
                    autocomplete="off"
                    placeholder="ISSN eingeben und Enter drücken" />
            </div>

            <script src="<?= ROOTPATH ?>/js/list-widget.js?v=<?= OSIRIS_BUILD ?>"></script>
            <script>
                $(function() {
                    // Example init for this widget
                    initListWidget($("#list-widget"), <?= json_encode($data['issn'] ?? []) ?>, function(value) {
                        if (value === '') {
                            return false; // Prevent adding empty value
                        }
                        // Validate ISSN format (basic validation)
                        const issnPattern = /^\d{4}-\d{3}[\dX]$/;
                        if (!issnPattern.test(value)) {
                            console.log(value);
                            alert("Please enter a valid ISSN in the format XXXX-XXXX.");
                            return false; // Prevent adding invalid ISSN
                        }
                        return true; // Allow adding valid ISSN
                    });
                });
            </script>
        </div>
        <div class="form-group floating-form">
            <input type="text" name="values[publisher]" id="publisher" class="form-control" value="<?= $data['publisher'] ?? '' ?>" required placeholder="Test">
            <label for="publisher" class="required">Publisher</label>
        </div>

        <!-- country -->
        <div class="form-group floating-form">
            <select name="values[country]" class="form-control">
                <option value=""><?= lang('Select country', 'Land auswählen') ?></option>
                <?php
                $c = $form['country'] ?? '';
                foreach ($DB->getCountries(lang('name', 'name_de')) as $key => $value) { ?>
                    <option value="<?= $key ?>" <?= $c == $key ? 'selected' : '' ?>><?= $value ?></option>
                <?php } ?>
            </select>
            <label for="country"><?= lang('Country', 'Land') ?></label>
        </div>

        <div class="form-group">
            <label for="oa" class="floating-title">Open Access</label>
            <div class="d-flex gap-10">
                <select name="values[oa]" id="oa" class="form-control">
                    <option value="false" <?= $oa === false ? 'selected' : '' ?>>Not open access</option>
                    <option value="true" <?= $oa === true ? 'selected' : '' ?>>Always open access</option>
                    <option value="year" <?= !is_bool($oa) ? 'selected' : '' ?>>Open access since…</option>
                </select>

                <input type="number" name="values[oa_since]" id="oa_since"
                    class="form-control mt-1" placeholder="Year (e.g. 2018)" value="<?= $data['oa_since'] ?? (is_numeric($oa) ? $oa : '') ?>"
                    style="width: 150px;">
            </div>

            <script>
                $('#oa').on('change', function() {
                    if ($(this).val() === 'year') {
                        $('#oa_since').show();
                    } else {
                        $('#oa_since').hide();
                    }
                }).trigger('change');
            </script>
        </div>

        <?php
        $fields = $Settings->get('journal-data');
        $fields = DB::doc2Arr($fields);

        require_once BASEPATH . "/php/CustomFields.php";
        $CustomFields = new CustomFields($data);
        ?>

        <?php foreach ($fields as $f) { ?>
            <div class="form-group">
                <?php
                $CustomFields->custom_field($f, false);
                ?>
            </div>
        <?php } ?>


        <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </form>
</div>