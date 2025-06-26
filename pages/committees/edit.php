<?php

/**
 * Edit details of a committee
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.0
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
    $formaction = ROOTPATH . "/crud/committees/create";
    $url = ROOTPATH . "/committees/view/*";
} else {
    $formaction = ROOTPATH . "/crud/committees/update/" . $form['_id'];
    $url = ROOTPATH . "/committees/view/" . $form['_id'];
}

?>

<div class="container" style="max-width: 80rem;">
    <?php if (empty($form) || !isset($form['_id'])) { ?>

        <h1 class="title">
            <i class="ph ph-identification-card" aria-hidden="true"></i>
            <?= lang('New Committee', 'Neues Gremium') ?>
        </h1>

    <?php } else { ?>
        <h1 class="title">
            <i class="ph ph-identification-card" aria-hidden="true"></i>
            <?= lang('Edit Committee', 'Gremium bearbeiten') ?>
        </h1>
    <?php } ?>
    <form action="<?= $formaction ?>" method="post" class="form">
        <input type="hidden" name="redirect" value="<?= $url ?>">

        <div class="form-group">
            <label for="name" class="required">
                <?= lang('Name of the committee', 'Name des Gremiums') ?>
            </label>
            <input type="text" class="form-control" name="values[name]" id="name" required value="<?= $form['name'] ?? '' ?>">
        </div>
        <style>
            select,
            ::picker(select) {
                appearance: base-select;

            }

            option {
                color: blue
            }
        </style>
        <div class="form-group">
            <label for="type" class="required">
                <?= lang('Type of committee', 'Art des Gremiums') ?>
            </label>
            <select name="values[type]" id="type" class="form-control" required>
                <button>
        <selectedcontent></selectedcontent>
      </button>

                <option value="" disabled <?= sel('type', '') ?>><?= lang('Select type', 'Art auswählen') ?></option>
                <option value="editorial_board" <?= sel('type', 'editorial_board') ?>><?= lang('Editorial Board', 'Editorial Board') ?></option>
                <option value="scientific_board" <?= sel('type', 'scientific_board') ?>><?= lang('Scientific Board', 'Wissenschaftlicher Beirat') ?></option>
                <option value="jury" <?= sel('type', 'jury') ?>><?= lang('Jury', 'Jury') ?></option>
                <option value="committee" <?= sel('type', 'committee') ?>><?= lang('Committee', 'Ausschuss') ?></option>
                <option value="academy" <?= sel('type', 'academy') ?>><?= lang('Academy / Society', 'Akademie / Gesellschaft') ?></option>
                <option value="advisory_board" <?= sel('type', 'advisory_board') ?>><?= lang('Advisory Board', 'Beratungsgremium') ?></option>
                <option value="professional_body" <?= sel('type', 'professional_body') ?>><?= lang('Professional Body', 'Fachgesellschaft') ?></option>
                <option value="taskforce" <?= sel('type', 'taskforce') ?>><?= lang('Taskforce / Working group', 'Taskforce / Arbeitsgruppe') ?></option>
                <option value="panel" <?= sel('type', 'panel') ?>><?= lang('Panel', 'Panel') ?></option>
                <option value="other" <?= sel('type', 'other') ?>><?= lang('Other', 'Andere') ?></option>
            </select>
        </div>

        <!-- description -->
        <div class="form-group">
            <label for="description">
                <?= lang('Description', 'Beschreibung') ?>
            </label>
            <textarea class="form-control" name="values[description]" id="description" rows="3"><?= $form['description'] ?? '' ?></textarea>
        </div>


        <!-- organisation -->
        <div class="form-group">
            <label for="link">
                <?= lang('Link', 'Link') ?>
            </label>
            <input type="url" class="form-control" name="values[link]" id="link" value="<?= $form['link'] ?? '' ?>" placeholder="https://example.com">
        </div>


        <!-- active -->
        <div class="form-group">
            <div class="custom-checkbox">
                <input type="hidden" name="values[active]" value="0">
                <input type="checkbox" id="active" name="values[active]" value="1" <?= ($form['active'] ?? true) ? 'checked' : '' ?>>
                <label for="active"><?= lang('Active', 'Aktiv') ?></label>
            </div>
        </div>

        <!-- parent committee -->
        <div class="form-group">
            <label for="parent_committee">
                <?= lang('Parent Committee', 'Übergeordnetes Gremium') ?>
            </label>
            <select name="values[parent_committee]" id="parent_committee" class="form-control">
                <option value="" <?= sel('parent_committee', '') ?>><?= lang('None', 'Keines') ?></option>
                <?php
                $committees = $osiris->committees->find(['active' => true], ['sort' => ['name' => 1]])->toArray();
                foreach ($committees as $committee) {
                    if (isset($form['_id']) && $committee['_id'] == $form['_id']) {
                        continue; // skip self
                    }
                    echo '<option value="' . $committee['_id'] . '" ' . sel('parent_committee', $committee['_id']) . '>' . htmlspecialchars($committee['name']) . '</option>';
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn secondary" id="submit"><?= lang('Save', 'Speichern') ?></button>
    </form>
</div>