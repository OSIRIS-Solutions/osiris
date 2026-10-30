<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= lang('Back to login', 'Zurück zur Anmeldung') ?>
</a>

<h1 class="card-title" id="auth-title"><?= lang('Choose a new password', 'Neues Passwort festlegen') ?></h1>
<p class="card-copy">
    <?= lang(
        'Choose a secure password for your OSIRIS account.',
        'Lege ein sicheres Passwort für deinen OSIRIS-Account fest.'
    ) ?>
</p>

<form action="<?= ROOTPATH ?>/auth/reset-password" method="post">
    <input type="hidden" name="hash" value="<?= e($resetHash) ?>">
    <div class="form-group">
        <label for="new-password"><?= lang('New password', 'Neues Passwort') ?></label>
        <div class="input-wrap">
            <i class="ph ph-lock-key" aria-hidden="true"></i>
            <input class="form-control" id="new-password" type="password" name="password" autocomplete="new-password" required autofocus>
            <button class="password-toggle" type="button" aria-label="<?= e(lang('Show password', 'Passwort anzeigen')) ?>" aria-pressed="false" data-password-toggle>
                <i class="ph ph-eye" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <button class="submit" type="submit">
        <span><?= lang('Save new password', 'Neues Passwort speichern') ?></span>
        <i class="ph ph-check" aria-hidden="true"></i>
    </button>
</form>
