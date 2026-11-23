<h1 class="card-title" id="auth-title"><?= t('people.welcome_back') ?></h1>

<?php if ($userManagement === 'LDAP') { ?>
    <p class="card-copy">
        <?= e(t('people.log_in_with_your_affiliation_account', replace: ['affiliation' => $affiliation])) ?>
    </p>
<?php } elseif ($userManagement === 'OAUTH') { ?>
    <p class="card-copy">
        <?= t('people.use_your_institutional_account_to_continue_securely') ?>
    </p>
<?php } elseif ($userManagement === 'AUTH') { ?>
    <p class="card-copy">
        <?php if ($affiliation === 'LISI') { ?>
            <?= t('people.log_in_with_your_demo_account') ?>
        <?php } else { ?>
            <?= t('people.log_in_with_your_osiris_account') ?>
        <?php } ?>
    </p>
<?php } ?>

<?php if ($userManagement === 'AUTH' || $userManagement === 'LDAP') { ?>
    <form action="<?= ROOTPATH ?>/user/login" method="post">
        <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">

        <div class="form-group">
            <label for="username"><?= t('common.username_guest_account_add') ?></label>
            <div class="input-wrap">
                <i class="ph ph-user" aria-hidden="true"></i>
                <input class="form-control" id="username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" inputmode="text" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password"><?= t('common.password') ?></label>
            <div class="input-wrap">
                <i class="ph ph-lock-key" aria-hidden="true"></i>
                <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
                <button class="password-toggle" type="button" aria-label="<?= e(t('common.show_password')) ?>" aria-pressed="false" data-password-toggle>
                    <i class="ph ph-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="options">
            <label class="checkbox" for="stay_logged_in">
                <input id="stay_logged_in" type="checkbox" name="stay_logged_in" value="1" <?= !empty($_POST['stay_logged_in']) ? 'checked' : '' ?>>
                <span><?= t('common.stay_logged_in') ?></span>
            </label>

            <?php if ($userManagement === 'AUTH') { ?>
                <a class="link" href="<?= ROOTPATH ?>/auth/forgot-password">
                    <?= t('common.forgot_password') ?>
                </a>
            <?php } ?>
        </div>

        <button class="submit" type="submit" name="submit" value="1">
            <span><?= t('header.login') ?></span>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </button>

        <?php if ($userManagement === 'AUTH' && $Settings->get('auth-self-registration', true)) { ?>
            <p class="secondary-action">
                <?= t('people.new_to_osiris') ?>
                <a class="link" href="<?= ROOTPATH ?>/auth/new-user">
                    <?= t('common.create_account') ?>
                </a>
            </p>
        <?php } ?>

        <?php if ($userManagement === 'AUTH' && $affiliation === 'LISI') { ?>
            <div class="demo">
                <strong>Demo:</strong>
                <?= t('people.this_osiris_instance_belongs_to_the_fictional_lisi_institute') ?>
            </div>
        <?php } ?>
    </form>
<?php } elseif ($userManagement !== 'OAUTH') { ?>
    <div class="alert" role="alert">
        <i class="ph ph-warning-circle" aria-hidden="true"></i>
        <div><?= t('people.user_management_is_not_configured') ?></div>
    </div>
<?php } ?>


<?php if (osirisOAuthEnabled() || $userManagement === 'OAUTH') { ?>
    <?php if ($oauthError = osirisOAuthConfigurationError()) { ?>
        <div class="alert" role="alert">
            <i class="ph ph-warning-circle" aria-hidden="true"></i>
            <div><?= e($oauthError) ?></div>
        </div>
    <?php } else { ?>
        <?php if ($userManagement === 'AUTH') { ?>
            <div class="auth-divider"><span><?= t('common.or') ?></span></div>
            <!-- <p class="sso-caption"><?= t('people.use_single_sign_on') ?></p> -->
        <?php } ?>
        <a class="submit" href="<?= ROOTPATH ?>/user/oauth">
            <span><?= e(t('people.log_in_with_oauth', ['oauth' => osirisOAuthLabel()])) ?></span>
            <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
        </a>
    <?php } ?>
<?php } ?>