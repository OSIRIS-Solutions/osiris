<h1 class="card-title" id="auth-title"><?= lang('people.welcome_back') ?></h1>

<?php if ($userManagement === 'LDAP') { ?>
    <p class="card-copy">
        <?= e(lang('people.log_in_with_your_affiliation_account', replace: ['affiliation' => $affiliation])) ?>
    </p>
<?php } elseif ($userManagement === 'OAUTH') { ?>
    <p class="card-copy">
        <?= lang('people.use_your_institutional_account_to_continue_securely') ?>
    </p>
<?php } elseif ($userManagement === 'AUTH') { ?>
    <p class="card-copy">
        <?php if ($affiliation === 'LISI') { ?>
            <?= lang('people.log_in_with_your_demo_account') ?>
        <?php } else { ?>
            <?= lang('people.log_in_with_your_osiris_account') ?>
        <?php } ?>
    </p>
<?php } ?>

<?php if ($userManagement === 'AUTH' || $userManagement === 'LDAP') { ?>
    <form action="<?= ROOTPATH ?>/user/login" method="post">
        <input type="hidden" name="redirect" value="<?= e($redirectTarget) ?>">

        <div class="form-group">
            <label for="username"><?= lang('common.username_guest_account_add') ?></label>
            <div class="input-wrap">
                <i class="ph ph-user" aria-hidden="true"></i>
                <input class="form-control" id="username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" inputmode="text" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password"><?= lang('common.password') ?></label>
            <div class="input-wrap">
                <i class="ph ph-lock-key" aria-hidden="true"></i>
                <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
                <button class="password-toggle" type="button" aria-label="<?= e(lang('common.show_password')) ?>" aria-pressed="false" data-password-toggle>
                    <i class="ph ph-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="options">
            <label class="checkbox" for="stay_logged_in">
                <input id="stay_logged_in" type="checkbox" name="stay_logged_in" value="1" <?= !empty($_POST['stay_logged_in']) ? 'checked' : '' ?>>
                <span><?= lang('common.stay_logged_in') ?></span>
            </label>

            <?php if ($userManagement === 'AUTH') { ?>
                <a class="link" href="<?= ROOTPATH ?>/auth/forgot-password">
                    <?= lang('common.forgot_password') ?>
                </a>
            <?php } ?>
        </div>

        <button class="submit" type="submit" name="submit" value="1">
            <span><?= lang('header.login') ?></span>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </button>

        <?php if ($userManagement === 'AUTH' && $Settings->get('auth-self-registration', true)) { ?>
            <p class="secondary-action">
                <?= lang('people.new_to_osiris') ?>
                <a class="link" href="<?= ROOTPATH ?>/auth/new-user">
                    <?= lang('common.create_account') ?>
                </a>
            </p>
        <?php } ?>

        <?php if ($userManagement === 'AUTH' && $affiliation === 'LISI') { ?>
            <div class="demo">
                <strong>Demo:</strong>
                <?= lang('people.this_osiris_instance_belongs_to_the_fictional_lisi_institute') ?>
            </div>
        <?php } ?>
    </form>
<?php } elseif ($userManagement === 'OAUTH') { ?>
    <a class="submit" href="<?= ROOTPATH ?>/user/oauth">
        <span><?= e(lang('people.log_in_with_oauth', replace: ['oauth' => OAUTH])) ?></span>
        <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
    </a>
<?php } else { ?>
    <div class="alert" role="alert">
        <i class="ph ph-warning-circle" aria-hidden="true"></i>
        <div><?= lang('people.user_management_is_not_configured') ?></div>
    </div>
<?php } ?>
