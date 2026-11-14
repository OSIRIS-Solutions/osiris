<?php

/**
 * Edit details of a infrastructure
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.8
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . "/php/Organization.php";

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
    $formaction = ROOTPATH . "/crud/infrastructures/create";
    $url = ROOTPATH . "/infrastructures/view/*";
} else {
    $formaction = ROOTPATH . "/crud/infrastructures/update/" . $form['_id'];
    $url = ROOTPATH . "/infrastructures/view/" . $form['_id'];
}

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();


require_once BASEPATH . '/php/InfrastructureForms.php';
?>

<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= OSIRIS_BUILD ?>"></script>

<div class="container w-800">
<h1 class="title">
    <?php
    if (empty($form) || !isset($form['_id'])) {
        echo lang('infrastructures.new_infrastructure');
    } else {
        echo lang('infrastructures.edit_infrastructure');
    }
    ?>
</h1>

<form action="<?= $formaction ?>" method="post" class="form box padded">
    <input type="hidden" name="redirect" value="<?= $url ?>">

    <div id="data-modules" class="row row-eq-spacing">
        <?php InfrastructureForms::render($osiris, $Settings, DB::doc2Arr($form)); ?>
    </div>

    <h5>
        <?= lang('infrastructures.configure_statistics') ?>
    </h5>

    <div class="form-group">

        <label for="statistic_frequency">
            <?= lang('infrastructures.how_often_do_you_collect_statistics') ?>
        </label>
        <select name="values[statistic_frequency]" id="statistic_frequency" class="form-control">
            <option value="annual" <?= sel('statistic_frequency', 'annual') ?>><?= lang('infrastructures.annual') ?></option>
            <option value="quarterly" <?= sel('statistic_frequency', 'quarterly') ?>><?= lang('infrastructures.quarterly') ?></option>
            <option value="monthly" <?= sel('statistic_frequency', 'monthly') ?>><?= lang('common.monthly') ?></option>
            <option value="irregularly" <?= sel('statistic_frequency', 'irregularly') ?>><?= lang('infrastructures.irregularly') ?></option>
        </select>
        <small class="text-muted">
            <?= lang('infrastructures.no_matter_how_often_you_collect_statistics_they_will_always_be_summed_up_to') ?>
        </small>
    </div>

    <?php

    include_once BASEPATH . "/php/Vocabulary.php";
    $Vocabulary = new Vocabulary();

    $fields = $Vocabulary->getVocabulary('infrastructure-stats');
    if (empty($fields) || !is_array($fields) || empty($fields['values'])) {
        $fields = [
            [
                "id" => "internal",
                "en" => "Number of internal users",
                "de" => "Anzahl interner Nutzer/-innen"
            ],
            [
                "id" => "national",
                "en" => "Number of national users",
                "de" => "Anzahl nationaler Nutzer/-innen"
            ],
            [
                "id" => "international",
                "en" => "Number of international users",
                "de" => "Anzahl internationaler Nutzer/-innen"
            ],
            [
                "id" => "hours",
                "en" => "Number of hours used",
                "de" => "Anzahl der genutzten Stunden"
            ],
            [
                "id" => "accesses",
                "en" => "Number of accesses",
                "de" => "Anzahl der Nutzungszugriffe"
            ],
        ];
    } else {
        $fields = $fields['values'] ?? [];
    }
    ?>
    <?php
    $statistic_fields = DB::doc2Arr($form['statistic_fields'] ?? []);
    foreach ($fields as $field) { ?>
        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" id="checkbox-<?= $field['id'] ?>" value="<?= $field['id'] ?>" name="values[statistic_fields][]" <?= empty($statistic_fields) || in_array($field['id'], $statistic_fields) ? 'checked' : '' ?>>
                <label for="checkbox-<?= $field['id'] ?>"><?= lang($field['en'], $field['de']) ?></label>
            </div>
        </div>
    <?php } ?>

    <?php if ($Settings->featureEnabled('portal')) { ?>
        <h5>
            <?= lang('common.portal_settings') ?>
        </h5>

        <div class="form-group">
            <?php
            $public = $form['public'] ?? false;
            ?>
            <input type="hidden" name="values[public]" value="false">
            <div class="custom-checkbox">
                <input type="checkbox" id="public" name="values[public]" <?= ($public) ? 'checked' : '' ?> value="true">
                <label for="public">
                    <?= lang('infrastructures.show_this_infrastructure_in_the_public_portfolio') ?>
                </label>
            </div>
        </div>
    <?php } ?>
    

    <button type="submit" class="btn secondary"><?= lang('action.save') ?></button>
</form>
</div>