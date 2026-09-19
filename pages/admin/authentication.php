<?php

/**
 * Admin page for managing authentication settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/authentication
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<div class="container w-800 mw-full" id="custom-authentication">
    <h1>
        <i class="ph-duotone ph-lock" aria-hidden="true"></i>
        <?= lang('admin.authentication') ?>
    </h1>
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/authentication">

        <h5>
            <?= lang('admin.self_registration') ?>
        </h5>
        <p class="text-muted">
            <?= lang('admin.self_registration_description_long') ?>
        </p>
        <input type="hidden" name="general[auth-self-registration]" value="0">
        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" name="general[auth-self-registration]" id="auth-self-registration-1" value="1" <?= $Settings->get('auth-self-registration', true) ? 'checked' : '' ?>>
                <label for="auth-self-registration-1"><?= lang('admin.self_registration_description') ?></label>
            </div>
        </div>

        <hr>

        <h5>
            <?= lang('admin.authentication_token') ?>
        </h5>
        <p class="text-muted">
            <?= lang('admin.authentication_token_description') ?>
        </p>

        <div class="form-group">
            <label for="auth-token"><?= lang('admin.auth_token') ?></label>
            <button class="btn small ml-5" type="button" onclick="copyToClipboard()" data-toggle="tooltip" data-title="<?= lang('common.copy_to_clipboard') ?>">
                <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
            </button>
            <div class="input-group">
                <input type="text" class="form-control" name="general[auth-token]" id="auth-token" value="<?= $Settings->get('auth-token') ?>">

                <div class="input-group-append">
                    <button type="button" class="btn" onclick="generateAUTHtoken()"><i class="ph ph-arrows-clockwise"></i> Generate</button>
                </div>
            </div>
        </div>

        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>

    </form>
</div>


<script>
    function copyToClipboard() {
        var text = $('#auth-token').val()
        navigator.clipboard.writeText(text)
        toastSuccess('Token copied to clipboard.')
    }

    function generateAUTHtoken() {
        let length = 50;
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        let counter = 0;
        while (counter < length) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
            counter += 1;
        }
        $('#auth-token').val(result)
    }
</script>