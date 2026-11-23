<?php

/**
 * Manage email settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$mail = $Settings->get('mail');
?>

<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-envelope"></i>
        <?= t('admin.email_settings_mail') ?>
    </h1>

    <!-- Email settings -->
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/mail">

        <div class="form-group">
            <label for="email"><?= t('admin.sender_address') ?></label>
            <input type="email" class="form-control" name="mail[email]" value="<?= $mail['email'] ?? 'no-reply@osiris-app.de' ?>">
            <span class="text-muted">
                <?= t('admin.this_email_address_is_used_as_the_default_sender_address') ?>
            </span>
        </div>

        <div class="row row-eq-spacing">
            <div class="col-sm">
                <label for="email"><?= t('admin.smtp_server') ?></label>
                <input type="text" class="form-control" name="mail[smtp_server]" value="<?= $mail['smtp_server'] ?? '' ?>">
                <span class="text-muted">
                    <?= t('admin.if_set_smtp_will_be_used_if_empty_osiris_will_use_the_default_php_mail_func') ?>
                </span>
            </div>

            <div class="col-sm-2">
                <label for="email"><?= t('admin.port') ?></label>
                <input type="number" class="form-control" name="mail[smtp_port]" value="<?= $mail['smtp_port'] ?? '25' ?>">
            </div>
        </div>

        <h5>
            <?= t('admin.smtp_authentication') ?>
        </h5>
        <p class="text-muted m-0">
            <?= t('admin.if_your_smtp_server_requires_authentication_please_provide_the_username_and') ?>
        </p>
        <div class="row row-eq-spacing">
            <div class="col-sm">
                <label for="email"><?= t('common.username') ?></label>
                <input type="text" class="form-control" name="mail[smtp_user]" value="<?= $mail['smtp_user'] ?? '' ?>">
            </div>

            <div class="col-sm">
                <label for="email"><?= t('common.password') ?></label>
                <input type="password" class="form-control" name="mail[smtp_password]" value="<?= $mail['smtp_password'] ?? '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email"><?= t('admin.security_protocol') ?></label>
            <select class="form-control" name="mail[smtp_security]">
                <option value="none" <?= ($mail['smtp_security'] ?? '') == 'none' ? 'selected' : '' ?>>None</option>
                <option value="ssl" <?= ($mail['smtp_security'] ?? '') == 'ssl' ? 'selected' : '' ?>>SSL</option>
                <option value="tls" <?= ($mail['smtp_security'] ?? '') == 'tls' ? 'selected' : '' ?>>TLS</option>
            </select>
            <span class="text-muted">
                <?= t('admin.choose_none_if_your_internal_smtp_relay_does_not_use_encryption') ?>
            </span>
        </div>

        <hr>

        <h3 id="mail-digest">
            <?= t('admin.mail_digest') ?>
        </h3>

        <p>
            <?= t('admin.users_can_receive_a_daily_weekly_or_monthly_email_summary_of_their_activiti') ?>
        </p>

        <p class="text-danger">
            <i class="ph ph-warning"></i>
            <?= t('admin.this_setting_requires_additional_configuration_of_a_cron_job_without_this_c') ?>
        </p>

        <div class="form-group">
            <?php
            $digest = $Settings->get('mail-digest', 'none');
            ?>

            <div class="custom-radio">
                <input type="radio" id="mail-digest-none" value="none" name="general[mail-digest]" <?= $digest == 'none' ? 'checked' : '' ?>>
                <label for="mail-digest-none">
                    <?= t('common.disabled') ?>
                </label>
            </div>
            <div class="custom-radio">
                <input type="radio" id="mail-digest-daily" value="daily" name="general[mail-digest]" <?= $digest == 'daily' ? 'checked' : '' ?>>
                <label for="mail-digest-daily">
                    <?= t('admin.daily') ?>
                </label>
            </div>
            <div class="custom-radio">
                <input type="radio" id="mail-digest-weekly" value="weekly" name="general[mail-digest]" <?= $digest == 'weekly' ? 'checked' : '' ?>>
                <label for="mail-digest-weekly">
                    <?= t('admin.weekly') ?>
                </label>
            </div>
            <div class="custom-radio">
                <input type="radio" id="mail-digest-monthly" value="monthly" name="general[mail-digest]" <?= $digest == 'monthly' ? 'checked' : '' ?>>
                <label for="mail-digest-monthly">
                    <?= t('common.monthly') ?>
                </label>
            </div>
            <small>
                <?= t('admin.note_users_can_change_their_mail_digest_frequency_in_their_profile_settings') ?>
            </small>
        </div>

        <div class="bottom-buttons mb-20">
            <button class="btn success">
                <i class="ph ph-floppy-disk"></i>
                <?= t('action.save') ?>
            </button>
        </div>

    </form>
    <hr>
    <!-- Test Email Settings by sending a test mail -->
    <form action="<?= ROOTPATH ?>/crud/admin/mail-test" method="post">
        <div class="box padded mt-20">

            <h2 class="title">
                <i class="ph-duotone ph-paper-plane-tilt"></i>
                <?= t('admin.test_email_settings') ?>
            </h2>

            <div class="form-group">
                <label for="email"><?= t('admin.test_email_address') ?></label>
                <input type="email" class="form-control" name="email" required>
                <span class="text-muted">
                    <?= t('admin.this_email_address_is_used_to_send_a_test_email_to_check_the_email_settings') ?>
                </span>
            </div>

            <button class="btn blue">
                <i class="ph ph-paper-plane-tilt"></i>
                <?= t('admin.send_test_email') ?>
            </button>
        </div>
    </form>
</div>