<h1 class="card-title" id="auth-title"><?= lang('Link single sign-on', 'Single Sign-On verknüpfen') ?></h1>
<form action="<?= ROOTPATH ?>/user/oauth-link" method="post">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['oauth_link_csrf']) ?>">
    <?php if ($pendingLink) { ?>
        <p class="card-copy"><?= lang('Your existing profile and all assignments will be retained. Your email address will be replaced with:', 'Dein bestehendes Profil und alle Zuordnungen bleiben erhalten. Deine E-Mail-Adresse wird ersetzt durch:') ?></p>
        <p><strong><?= e($pendingLink['mail']) ?></strong></p>
        <p class="card-copy"><?= lang('After confirmation, you can only log in using single sign-on. Your OSIRIS password and password reset links will no longer work.', 'Nach der Bestätigung kannst du dich ausschließlich über Single Sign-On anmelden. Dein OSIRIS-Passwort und Links zum Zurücksetzen des Passworts funktionieren dann nicht mehr.') ?></p>
        <button class="submit" name="action" value="confirm" type="submit"><?= lang('Confirm switch to single sign-on', 'Wechsel zu Single Sign-On bestätigen') ?></button>
    <?php } else { ?>
        <p class="card-copy"><?= e(lang('Confirm your OSIRIS password, then log in with ' . osirisOAuthLabel() . '.', 'Bestätige dein OSIRIS-Passwort und melde dich anschließend mit ' . osirisOAuthLabel() . ' an.')) ?></p>
        <div class="form-group">
            <label for="link-password"><?= lang('OSIRIS password', 'OSIRIS-Passwort') ?></label>
            <input class="form-control" id="link-password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="submit" name="action" value="start" type="submit"><?= lang('Continue to single sign-on', 'Weiter zu Single Sign-On') ?></button>
    <?php } ?>
    <p class="secondary-action"><button class="btn" name="action" value="cancel" type="submit" formnovalidate><?= lang('Cancel', 'Abbrechen') ?></button></p>
</form>
