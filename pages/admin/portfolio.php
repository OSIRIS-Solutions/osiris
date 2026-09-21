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
            <?= lang('admin.portfolio_settings') ?>
        </h1>

        <!-- portfolio url -->
        <div class="form-group">
            <label for="portfolio_url">
                <?= lang('admin.portfolio_url') ?>
            </label>
            <input type="url" class="form-control" name="general[portfolio_url]" value="<?= $Settings->get('portfolio_url') ?>">
            <span class="text-muted">
                <?= lang('admin.the_portfolio_url_is_used_to_link_to_the_portfolio_from_various_places_in_o') ?>
            </span>
        </div>


        <!-- <h5>
            <?=lang('admin.memberships')?> in Portfolio
        </h5>

        <p>
            <?= lang('admin.you_can_specify_here_which_research_activities_should_be_shown_under_the_me') ?>
        </p>

        <p>
            <i class="ph ph-info"></i>
            <?=lang('admin.only_activity_types_that_are_generally_visible_in_portfolio_and_have_the_da')?>
        </p>

        <table class="table">
            <thead>
                <tr>
                    <th>
                        <?= lang('admin.activity_type') ?>
                    </th>
                    <th>
                        <?= lang('admin.membership_template') ?>
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
                <?= lang('admin.portfolio_workflow_visibility') ?>
            </h5>
            <?= lang('admin.you_can_specify_here_if_only_workflow_approved_activities_should_be_shown_i') ?>

            <div class="form-group">
                <?php
                $portfolio = $Settings->get('portfolio-workflow-visibility', 'all');
                ?>

                <div class="custom-radio">
                    <input type="radio" id="portfolio-workflow-visibility-approved" value="only-approved" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'only-approved' ? 'checked' : '' ?>>
                    <label for="portfolio-workflow-visibility-approved">
                        <?= lang('admin.only_approved_activities') ?>
                    </label>
                </div>

                <div class="custom-radio">
                    <input type="radio" id="portfolio-workflow-visibility-approved-or-empty" value="approved-or-empty" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'approved-or-empty' ? 'checked' : '' ?>>
                    <label for="portfolio-workflow-visibility-approved-or-empty">
                        <?= lang('admin.approved_activities_and_activities_without_workflow') ?>
                    </label>
                </div>

                <div class="custom-radio">
                    <input type="radio" id="portfolio-workflow-visibility-all" value="all" name="general[portfolio-workflow-visibility]" <?= $portfolio == 'all' ? 'checked' : '' ?>>
                    <label for="portfolio-workflow-visibility-all">
                        <?= lang('common.all_activities') ?>
                    </label>
                </div>
            </div>
        <?php } ?>

        <h5>
            <?= lang('admin.portfolio_api_key') ?>
        </h5>
        <div class="form-group">
            <input type="text" class="form-control" name="general[portfolio_apikey]" value="<?= $Settings->get('portfolio_apikey') ?>">
            <span class="text-muted">
                <?= lang('admin.the_portfolio_api_key_is_used_to_authenticate_the_portfolio_api_if_you_do_n') ?>
            </span>
        </div>

       <?php if ($Settings->featureEnabled('spectrum')) { ?>
         <h5>
            <?= lang('admin.research_spectrum_in_portfolio') ?>
        </h5>
        <div class="form-group">
            <div class="custom-radio">
                <input type="radio" id="portfolio-spectrum-visibility-enabled" value="enabled" name="features[portfolio-spectrum]" <?= $Settings->featureEnabled('portfolio-spectrum') ? 'checked' : '' ?>>
                <label for="portfolio-spectrum-visibility-enabled">
                    <?= lang('common.show_research_spectrum_in_portfolio') ?>
                </label>
            </div>

            <div class="custom-radio">
                <input type="radio" id="portfolio-spectrum-visibility-disabled" value="disabled" name="features[portfolio-spectrum]" <?= !$Settings->featureEnabled('portfolio-spectrum') ? 'checked' : '' ?>>
                <label for="portfolio-spectrum-visibility-disabled">
                    <?= lang('admin.do_not_show_research_spectrum_in_portfolio') ?>
                </label>
            </div>
        </div>
       <?php } ?>
       

        <h5>
            <?= lang('admin.generally_visible_activity_types') ?>
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
            <?= lang('admin.the_activity_types_listed_above_are_generally_visible_in_the_portfolio_you') ?>
            <a href="<?= ROOTPATH ?>/admin/categories" class="colorless text-decoration-underline">
                <?= lang('admin.activity_types_settings') ?>
            </a>.
        </p>

        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>
    </div>

</form>