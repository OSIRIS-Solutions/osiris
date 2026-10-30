<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= lang('Back to login', 'Zurück zur Anmeldung') ?>
</a>

<h1 class="card-title" id="auth-title"><?= lang('Forgot password?', 'Passwort vergessen?') ?></h1>
<p class="card-copy">
    <?= lang(
        'Enter the email address associated with your account. We will send you a link to choose a new password.',
        'Gib die E-Mail-Adresse deines Accounts ein. Wir senden dir einen Link, über den du ein neues Passwort festlegen kannst.'
    ) ?>
</p>

<form action="<?= ROOTPATH ?>/auth/forgot-password" method="post">
    <div class="form-group">
        <label for="mail"><?= lang('Email address', 'E-Mail-Adresse') ?></label>
        <div class="input-wrap">
            <i class="ph ph-envelope-simple" aria-hidden="true"></i>
            <input class="form-control" id="mail" type="email" name="mail" value="<?= e($_POST['mail'] ?? '') ?>" autocomplete="email" required autofocus>
        </div>
    </div>

    <button class="submit" type="submit">
        <span><?= lang('Request reset link', 'Link zum Zurücksetzen anfordern') ?></span>
        <i class="ph ph-paper-plane-tilt" aria-hidden="true"></i>
    </button>
</form>
