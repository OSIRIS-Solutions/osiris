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
        <?= lang('Authentication', 'Authentifizierung') ?>
    </h1>
    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">
        <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/authentication">

        <h5>
            <?= lang('Self-registration', 'Selbstregistrierung') ?>
        </h5>
        <p class="text-muted">
            <?= lang('If enabled, users can create their own account. Please note that this option is not recommended for publicly available production instances, as it may lead to unauthorized access. If you want users to create their own profile, please set up a registration token below and share it only with authorized individuals.', 'Wenn aktiviert, können Nutzer:innen ein eigenes Konto erstellen. Bitte beachte, dass diese Option für öffentlich verfügbare Produktionsinstanzen nicht empfohlen wird, da sie zu unautorisiertem Zugriff führen kann. Wenn du möchtest, dass Nutzer:innen ihr eigenes Profil erstellen können, richte bitte ein Registrierungstoken ein und teile es nur mit autorisierten Personen.') ?>
        </p>
        <input type="hidden" name="general[auth-self-registration]" value="0">
        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" name="general[auth-self-registration]" id="auth-self-registration-1" value="1" <?= $Settings->get('auth-self-registration', true) ? 'checked' : '' ?>>
                <label for="auth-self-registration-1"><?= lang('Allow users to create their own account', 'Erlaube Benutzern, ein eigenes Konto zu erstellen') ?></label>
            </div>
        </div>

        <hr>

        <h5>
            <?= lang('Authentication token', 'Authentifizierungs-Token') ?>
        </h5>
        <p class="text-muted">
            <?= lang(
                'Here you can generate a so-called AUTH token that users can use to register. Only share this token with people who are allowed to register! If you change the token, anyone who has the old token will lose the ability to register. Only the currently stored token is valid. If no token is stored, registration without a token is possible.',
                'Du kannst hier ein sogenanntes AUTH-Token generieren, das Nutzende verwenden können, um sich zu registrieren. Teile dieses Token nur mit Personen, die sich registrieren dürfen! Wenn du das Token änderst, verlieren alle Personen, die das alte Token haben, die Möglichkeit, sich zu registrieren. Es gilt immer nur das aktuell hinterlegte Token. Wenn kein Token hinterlegt ist, ist eine Registrierung ohne Token möglich.'
            ) ?>
        </p>

        <div class="form-group">
            <label for="auth-token"><?= lang('AUTH Token', 'AUTH-Token') ?></label>
            <button class="btn small ml-5" type="button" onclick="copyToClipboard()" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>">
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
            <?= lang('Save', 'Speichern') ?>
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