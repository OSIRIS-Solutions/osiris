<?php

/**
 * Manage portfolio settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.8.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
    <div class="container w-800 mw-full">

        <h1>
            <i class="ph-duotone ph-globe"></i>
            <?= lang('Portfolio Settings', 'Portfolio Einstellungen') ?>
        </h1>

        <!-- portfolio url -->
        <div class="form-group">
            <label for="portfolio_url">
                <?= lang('Portfolio URL', 'Portfolio URL') ?>
            </label>
            <input type="url" class="form-control" name="general[portfolio_url]" value="<?= $Settings->get('portfolio_url') ?>">
            <span class="text-muted">
                <?= lang(
                    'The portfolio URL is used to link to the portfolio from various places in OSIRIS. Make sure to include the full URL (e.g. https://research.institute.edu). If you do not provide a URL, Portfolio will try to use relative links.',
                    'Die Portfolio-URL wird verwendet, um von verschiedenen Stellen in OSIRIS auf das Portfolio zu verlinken. Achte darauf, die vollständige URL anzugeben (z.B. https://portfolio.institute.de). Falls keine URL angegeben wird, versucht das Portfolio, relative Links zu verwenden.'
                ) ?>
            </span>
        </div>


        <!-- <h5>
            <?=lang('Memberships', 'Mitgliedschaften')?> in Portfolio
        </h5>

        <p>
            <?= lang('You can specify here, which research activities should be shown under the memberships section in the portfolio page of a person. By default, this section will not be visible.', 'Hier kannst du festlegen, welche Forschungsaktivitäten unter der Rubrik Mitgliedschaften auf der Portfolio-Seite einer Person angezeigt werden sollen. Standardmäßig ist diese Rubrik nicht sichtbar.') ?>
        </p>

        <p>
            <i class="ph ph-info"></i>
            <?=lang('Only activity types that are generally visible in Portfolio and have the "date-range-ongoing" field qualify for memberships.', 'Nur Aktivitätstypen, die generell im Portfolio sichtbar sind und über das Feld "date-range-ongoing" verfügen, kommen für Mitgliedschaften in Frage.')?>
        </p>

        <table class="table">
            <thead>
                <tr>
                    <th>
                        <?= lang('Activity Type', 'Aktivitätstyp') ?>
                    </th>
                    <th>
                        <?= lang('Membership Template', 'Mitgliedschafts-Vorlage') ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $filter = [
                    'portfolio' => ['$in' => [1, true]], 
                    '$or' => [
                        ['fields.id' => 'date-range-ongoing'],
                        ['modules' => 'date-range-ongoing'],
                        ['modules' => 'date-range-ongoing*']
                    ]
                    ];
                $activities = $osiris->adminTypes->find($filter, ['sort' => ['parent' => 1, 'order' => 1]]);
                foreach ($activities as $key) { ?>
                    <tr>
                        <td>
                            <i class="ph ph-<?= $key['icon'] ?> ph-fw text-<?= $key['parent'] ?>"></i>
                            <?= lang($key['name'], $key['name_de']) ?>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="memberships[<?= $key['id'] ?>]" value="<?= $key['membership_template'] ?? '' ?>">
                        </td>
                    </tr>
                <?php } ?>
                
            </tbody>
        </table> -->

        <?php if ($Settings->featureEnabled('quality-workflow')) { ?>
            <h5>
                <?= lang('Portfolio Workflow Visibility', 'Sichtbarkeit im Portfolio-Workflow') ?>
            </h5>
            <?= lang('You can specify here, if only workflow-approved activities should be shown in the portfolio.', 'Hier kannst du festlegen, ob nur workflow-genehmigte Aktivitäten im Portfolio angezeigt werden sollen.') ?>

            <div class="form-group">
                <?php
                $portfolio = $Settings->get('portfolio-workflow-visibility', 'all');
                ?>

                <div class="custom-radio">
                    <input type="radio" id="portfolio-workflow-visibility-approved" value="only-approved" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'only-approved' ? 'checked' : '' ?>>
                    <label for="portfolio-workflow-visibility-approved">
                        <?= lang('Only approved activities', 'Nur genehmigte Aktivitäten') ?>
                    </label>
                </div>

                <div class="custom-radio">
                    <input type="radio" id="portfolio-workflow-visibility-approved-or-empty" value="approved-or-empty" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'approved-or-empty' ? 'checked' : '' ?>>
                    <label for="portfolio-workflow-visibility-approved-or-empty">
                        <?= lang('Approved activities and activities without workflow', 'Genehmigte Aktivitäten und Aktivitäten ohne Workflow') ?>
                    </label>
                </div>

                <div class="custom-radio">
                    <input type="radio" id="portfolio-workflow-visibility-all" value="all" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'all' ? 'checked' : '' ?>>
                    <label for="portfolio-workflow-visibility-all">
                        <?= lang('All activities', 'Alle Aktivitäten') ?>
                    </label>
                </div>
            </div>
        <?php } ?>

        <h5>
            <?= lang('Portfolio-API Key', 'Portfolio-API-Schlüssel') ?>
        </h5>
        <div class="form-group">
            <input type="text" class="form-control" name="general[portfolio_apikey]" value="<?= $Settings->get('portfolio_apikey') ?>">
            <span class="text-muted">
                <?= lang(
                    'The portfolio API key is used to authenticate the portfolio API. If you do not provide an API key, the portfolio API will be open to anyone.',
                    'Der Portfolio-API-Schlüssel wird verwendet, um die Portfolio-API zu authentifizieren. Falls kein API-Schlüssel angegeben wird, ist die Portfolio-API für jeden offen.'
                ) ?>
            </span>
        </div>

        <h5>
            <?= lang('Generally visible activity types', 'Allgemein sichtbare Aktivitätstypen') ?>
        </h5>

        <ul class="list">
            <?php foreach ($osiris->adminTypes->find(['portfolio' => ['$in' => [1, true]]], ['sort' => ['parent' => 1, 'order' => 1]]) as $type) { ?>
                <li>
                    <a href="<?= ROOTPATH ?>/admin/types/<?= $type['id'] ?>" class="colorless">
                        <i class="ph ph-<?= $type['icon'] ?> ph-fw text-<?= $type['parent'] ?>"></i>
                        <?= lang($type['name'], $type['name_de']) ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
        <p class="text-muted">
            <?= lang('The activity types listed above are generally visible in the portfolio. You can manage the activity types in the', 'Die oben aufgeführten Aktivitätstypen sind generell im Portfolio sichtbar. Du kannst die Aktivitätstypen im') ?>
            <a href="<?= ROOTPATH ?>/admin/categories" class="colorless text-decoration-underline">
                <?= lang('activity types settings', 'Einstellungen der Aktivitätstypen') ?>
            </a>.
        </p>

        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </div>

</form>