<?php

/**
 * Page for reports
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/locking
 *
 * @package     OSIRIS
 * @since       1.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<style>
    .custom-radio input#open_access:checked~label::before {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }

    .custom-radio input#open_access-0:checked~label::before {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }
</style>


<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-lock"></i>
        <?= lang('activities.lock_a_period') ?>
    </h1>
    <p>
        <?= lang('activities.you_can_lock_a_period_once_a_report_has_been_generated_all_activities_that') ?>
    </p>

    <p>
        <?=lang('activities.the_following_roles_can_still_edit_or_delete_locked_activities')?>
        <br>
        <b><?=lang('action.edit')?>:</b>
        <?php
            $roles = $osiris->adminRights->find([
                'right' => 'activities.edit-locked',
                'value' => true
            ])->toArray();
            echo implode(', ', array_column($roles, 'role'));
        ?>
        <br>
        <b><?=lang('action.delete')?>:</b>
        <?php
            $roles = $osiris->adminRights->find([
                'right' => 'activities.delete-locked',
                'value' => true
            ])->toArray();
            echo implode(', ', array_column($roles, 'role'));
        ?>
    </p>



    <p>
        <?= lang('activities.activities_that_are_not_report_worthy_e_g_online_ahead_of_print_activities') ?>
    </p>

    <div class="box padded">
        <form action="<?= ROOTPATH ?>/crud/activities/lock" method="post">

            <div class="form-row row-eq-spacing">
                <div class="col-sm">
                    <label class="required" for="start">
                        <?= lang('activities.beginning') ?>
                    </label>
                    <input type="date" class="form-control" name="start" id="start" value="<?= CURRENTYEAR ?>-01-01" required>
                </div>
                <div class="col-sm">
                    <label class="required" for="end">
                        <?= lang('common.end') ?>
                    </label>
                    <input type="date" class="form-control" name="end" id="end" value="<?= CURRENTYEAR ?>-06-30" required>
                </div>
            </div>
            <div class="my-20">
                <span><?= lang('common.action') ?>:</span>

                <div class="custom-radio d-inline-block ml-10" style="--secondary-color: var(--danger-color);">
                    <input type="radio" name="action" id="action-lock" value="lock" checked="">
                    <label for="action-lock"><i class="ph ph-duotone ph-lock text-danger"></i> <?= lang('common.lock') ?></label>
                </div>
                <div class="custom-radio d-inline-block ml-10" style="--secondary-color: var(--success-color);">
                    <input type="radio" name="action" id="action-unlock" value="unlock">
                    <label for="action-unlock"><i class="ph ph-duotone ph-lock-open text-success"></i> <?= lang('common.unlock') ?></label>
                </div>
            </div>
            <button class="btn" type="submit"><?= lang('action.submit') ?></button>

        </form>
    </div>
</div>