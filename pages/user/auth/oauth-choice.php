<?php $parallelAuth = strtoupper(USER_MANAGEMENT) === 'AUTH'; ?>
<h1 class="card-title" id="auth-title"><?= $parallelAuth ? lang('Do you already have an OSIRIS profile?', 'Hast du bereits ein OSIRIS-Profil?') : lang('Create an OSIRIS profile?', 'OSIRIS-Profil erstellen?') ?></h1>
<p class="card-copy"><?= lang('This single sign-on account is not linked to an OSIRIS profile yet.', 'Dieses Single-Sign-On-Konto ist noch mit keinem OSIRIS-Profil verknüpft.') ?></p>
<?php if ($parallelAuth) { ?>
<p class="card-copy"><?= lang('If you already have an OSIRIS account, log in to it first and link single sign-on in your account settings. This keeps your existing information and assignments.', 'Wenn du bereits ein OSIRIS-Konto hast, melde dich zuerst dort an und verknüpfe Single Sign-On in den Account-Einstellungen. So bleiben deine bisherigen Informationen und Zuordnungen erhalten.') ?></p>
<?php } else { ?>
<p class="card-copy"><?= lang('We could not find a matching OSIRIS profile. Would you like to create one? If you previously used OSIRIS, contact the administrator to have your existing profile assigned before creating a second profile.', 'Wir haben kein passendes OSIRIS-Profil gefunden. Möchtest du eines erstellen? Wenn du OSIRIS bereits genutzt hast, kontaktiere vor dem Anlegen eines zweiten Profils die Administration, um dein bestehendes Profil zuzuordnen.') ?></p>
<?php } ?>
<form action="<?= ROOTPATH ?>/user/oauth-register" method="post">
    <input type="hidden" name="csrf" value="<?= e($pending['csrf']) ?>">
    <?php if ($parallelAuth) { ?>
    <button class="submit" name="action" value="existing" type="submit"><?= lang('Link an existing account', 'Bestehendes Konto verknüpfen') ?></button>
    <?php } ?>
    <?php if ($Settings->get('auth-self-registration', true)) { ?>
        <p class="card-copy mt-20"><?= lang('If you do not have an OSIRIS account yet, you can create a new profile.', 'Solltest du noch kein OSIRIS-Konto haben, kannst du ein neues Profil erstellen.') ?></p>
        <button class="submit" name="action" value="new" type="submit"><?= lang('Create a new profile', 'Neues Profil erstellen') ?></button>
    <?php } else { ?>
        <p class="card-copy"><?= lang('Self-registration is disabled. Please contact the administrator.', 'Die Selbstregistrierung ist deaktiviert. Bitte kontaktiere die Administration.') ?></p>
    <?php } ?>
    <p class="secondary-action"><button class="btn link text-muted mt-0" name="action" value="cancel" type="submit"><?= lang('Cancel', 'Abbrechen') ?></button></p>
</form>
