<?php

/**
 * Page to log in
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


if (isset($_GET['redirect'])) { ?>

    <div class="alert danger">
        <h3 class="title"><?= lang('people.access_denied') ?></h3>
        <?= lang('people.you_need_to_log_in_to_access_this_page') ?>
    </div>

<?php
}

// check user management
if (!defined('USER_MANAGEMENT')) {
    die('USER_MANAGEMENT not defined in CONFIG.php');
}

$UM = strtoupper(USER_MANAGEMENT);
?>



<h1><?= lang('people.welcome') ?></h1>

<?php if ($UM == 'LDAP') { ?>
    <h5>
        <?= lang('people.please_log_in_with_your_affiliation_account', replace: ['affiliation' => $Settings->get('affiliation')]) ?>
    </h5>


    <form action="<?= ROOTPATH ?>/user/login" method="POST" class="w-400 mw-full">
        <input type="hidden" name="redirect" value="<?= $_GET['redirect'] ?? $_SERVER['REQUEST_URI'] ?>">
        <div class="form-group">
            <label for="username"><?= lang('people.user_name') ?>: </label>
            <input class="form-control" id="username" type="text" name="username" placeholder="abc21" required />
        </div>
        <div class="form-group">
            <label for="password"><?= lang('common.password') ?>: </label>
            <input class="form-control" id="password" type="password" name="password" placeholder="your password" required />
        </div>

        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" id="stay_logged_in" name="stay_logged_in" value="1">
                <label for="stay_logged_in"><?= lang('common.stay_logged_in') ?></label>
            </div>
        </div>

        <input class="btn secondary" type="submit" name="submit" value="<?= lang('people.log_in') ?>" />
    </form>


<?php } elseif ($UM == 'OAUTH') {
    if (!defined('OAUTH') || !defined('AUTHORITY') || !defined('CLIENT_ID') || !defined('REDIRECT_URI') || !defined('SCOPES')) {
        die('OAUTH not correctly defined in CONFIG.php');
    }
?>
    <a href="<?= ROOTPATH ?>/user/oauth" class="btn primary">
        <?= lang('people.log_in_with_your_oauth_account', replace: ['oauth' => OAUTH]) ?>
    </a>

<?php } elseif ($UM == 'AUTH') { ?>
    <h5>
        <?php
        if ($Settings->get('affiliation') === 'LISI') {
            echo lang('people.please_log_in_with_your_demo_account');
        } else {
            echo lang('people.please_log_in_with_your_osiris_account');
        }
        ?>
    </h5>


    <form action="<?= ROOTPATH ?>/user/login" method="POST" class="w-400 mw-full">
        <input type="hidden" name="redirect" value="<?= $_GET['redirect'] ?? $_SERVER['REQUEST_URI'] ?>">
        <div class="form-group">
            <label for="username"><?= lang('people.user_name') ?>: </label>
            <input class="form-control" id="username" type="text" name="username" placeholder="abc21" required />
        </div>
        <div class="form-group">
            <label for="password"><?= lang('common.password') ?>: </label>
            <input class="form-control" id="password" type="password" name="password" placeholder="your password" required />
        </div>

        <div class="form-group">
            <div class="custom-checkbox">
                <input type="checkbox" id="stay_logged_in" name="stay_logged_in" value="1">
                <label for="stay_logged_in"><?= lang('common.stay_logged_in') ?></label>
            </div>
        </div>

        <input class="btn secondary" type="submit" name="submit" value="<?= lang('people.log_in') ?>" />

        <hr>

        <a class='link d-block' href='<?= ROOTPATH ?>/auth/forgot-password'>
            <?= lang('people.forgot_password') ?>
        </a>
        <?php if ($Settings->get('auth-self-registration', true)) { ?>
            <a class='link' href='<?= ROOTPATH ?>/auth/new-user'>
                <?= lang('people.no_account_register_now') ?>
            </a>
        <?php } ?>

        <?php if ($Settings->get('affiliation') === 'LISI') { ?>
            <div class="alert signal mt-20">
                <div class="title">Demo</div>
                <?= lang('people.this_osiris_instance_is_a_demo_with_the_fictional_institute_lisi') ?>
            </div>
        <?php } ?>
    </form>

<?php } else { ?>
    <div class="alert danger">
        <?= lang('people.user_management_not_defined') ?>
    </div>
<?php } ?>