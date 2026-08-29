<h1 class="card-title" id="auth-title"><?= lang('Welcome back', 'Willkommen zurück') ?></h1>

<?php if ($userManagement === 'LDAP') { ?>
    <p class="card-copy">
        <?= e(lang(
            'Log in with your ' . $affiliation . ' account.',
            'Melde dich mit deinem ' . $affiliation . '-Benutzerkonto an.'
        )) ?>
    </p>
<?php } elseif ($userManagement === 'OAUTH') { ?>
    <p class="card-copy">
        <?= lang(
            'Use your institutional account to continue securely.',
            'Nutze das Benutzerkonto deiner Institution, um sicher fortzufahren.'
        ) ?>
    </p>
<?php } elseif ($userManagement === 'AUTH') { ?>
    <p class="card-copy">
        <?php if ($affiliation === 'LISI') { ?>
            <?= lang('Log in with your demo account.', 'Melde dich mit deinem Demo-Benutzerkonto an.') ?>
        <?php } else { ?>
            <?= lang('Log in with your OSIRIS account.', 'Melde dich mit deinem OSIRIS-Benutzerkonto an.') ?>
        <?php } ?>
    </p>
<?php } ?>

<?php if ($userManagement === 'AUTH' || $userManagement === 'LDAP') { ?>
    <form action="<?= ROOTPATH ?>/user/login" method="post">
        <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">

        <div class="form-group">
            <label for="username"><?= lang('Username', 'Nutzername') ?></label>
            <div class="input-wrap">
                <i class="ph ph-user" aria-hidden="true"></i>
                <input class="form-control" id="username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" inputmode="text" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password"><?= lang('Password', 'Passwort') ?></label>
            <div class="input-wrap">
                <i class="ph ph-lock-key" aria-hidden="true"></i>
                <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
                <button class="password-toggle" type="button" aria-label="<?= e(lang('Show password', 'Passwort anzeigen')) ?>" aria-pressed="false" data-password-toggle>
                    <i class="ph ph-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="options">
            <label class="checkbox" for="stay_logged_in">
                <input id="stay_logged_in" type="checkbox" name="stay_logged_in" value="1" <?= !empty($_POST['stay_logged_in']) ? 'checked' : '' ?>>
                <span><?= lang('Stay logged in', 'Eingeloggt bleiben') ?></span>
            </label>

            <?php if ($userManagement === 'AUTH') { ?>
                <a class="link" href="<?= ROOTPATH ?>/auth/forgot-password">
                    <?= lang('Forgot password?', 'Passwort vergessen?') ?>
                </a>
            <?php } ?>
        </div>

        <button class="submit" type="submit" name="submit" value="1">
            <span><?= lang('Log in', 'Anmelden') ?></span>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </button>

        <?php if ($userManagement === 'AUTH' && $Settings->get('auth-self-registration', true)) { ?>
            <p class="secondary-action">
                <?= lang('New to OSIRIS?', 'Neu bei OSIRIS?') ?>
                <a class="link" href="<?= ROOTPATH ?>/auth/new-user">
                    <?= lang('Create account', 'Account erstellen') ?>
                </a>
            </p>
        <?php } ?>

        <?php if ($userManagement === 'AUTH' && $affiliation === 'LISI') { ?>
            <div class="demo">
                <strong>Demo:</strong>
                <?= lang(
                    'This OSIRIS instance belongs to the fictional LISI institute.',
                    'Diese OSIRIS-Instanz gehört zum fiktiven Institut LISI.'
                ) ?>
            </div>
        <?php } ?>
    </form>
<?php } elseif ($userManagement === 'OAUTH') { ?>
    <a class="submit" href="<?= ROOTPATH ?>/user/oauth">
        <span><?= e(lang('Log in with ' . OAUTH, 'Mit ' . OAUTH . ' anmelden')) ?></span>
        <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
    </a>
<?php } else { ?>
    <div class="alert" role="alert">
        <i class="ph ph-warning-circle" aria-hidden="true"></i>
        <div><?= lang('User management is not configured.', 'Das User-Management ist nicht konfiguriert.') ?></div>
    </div>
<?php } ?>
