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

    <div class="form-row row-eq-spacing">
        <div class="col-sm">
            <label for="journal" class="required"><?= lang('Name', 'Name') ?></label>
            <input type="text" name="values[journal]" id="journal" class="form-control" value="<?= $data['journal'] ?? '' ?>" required>
        </div>
        <div class="col-sm">
            <label for="abbr"><?= lang('Abbreviation', 'Abkürzung') ?></label>
            <input type="text" name="values[abbr]" id="abbr" class="form-control" value="<?= $data['abbr'] ?? '' ?>">
        </div>
    </div>
    <div class="form-group">
        <label for="issn"><?= lang('ISSN') ?></label>
        <?php
        $issn = "";
        if (isset($data['issn'])) {
            $issn = $data['issn'];
            try {
                $issn = DB::doc2Arr($issn);
            } catch (\Throwable $th) {
            }
            if (is_array($issn)) $issn = implode(' ', $issn);
        }
        ?>
        <input type="text" name="values[issn]" id="issn" class="form-control" value="<?= $issn ?>">
        <small>
            <?= lang('Multiple ISSNs can be separated by spaces.', 'Mehrere ISSNs können durch Leerzeichen getrennt werden.') ?>
        </small>
    </div>
    <div class="form-row row-eq-spacing">
        <div class="col-sm">
            <label for="publisher" class="required">Publisher</label>
            <input type="text" name="values[publisher]" id="publisher" class="form-control" value="<?= $data['publisher'] ?? '' ?>" required>
        </div>
        <div class="col-sm">
            <label for="oa">Open Access</label>
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


    <button type="submit" class="btn success">
        <i class="ph ph-floppy-disk"></i>
        <?= lang('Save', 'Speichern') ?>
    </button>
</form>