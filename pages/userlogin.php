<?php

/**
 * Page to log in
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


if (isset($_GET['redirect'])) {?>

<div class="alert danger">
    <h3 class="title"><?= lang('Access denied', 'Zugriff verweigert') ?></h3>
    <?= lang('You need to log in to access this page.', 'Du musst dich einloggen, um auf diese Seite zuzugreifen.') ?>
</div>

<?php
}

// check user management
if (!defined('USER_MANAGEMENT')) {
    die('USER_MANAGEMENT not defined in CONFIG.php');
}

$UM = strtoupper(USER_MANAGEMENT);
?>



<h1><?= lang('Welcome!', 'Willkommen') ?></h1>

<?php if ($UM == 'LDAP') { ?>
    <h5>
        <?= lang('Please log-in with your ' . $Settings->get('affiliation') . '-Account.', 'Bitte melde dich mit deinem ' . $Settings->get('affiliation') . '-Benutzeraccount an.') ?>
    </h5>


    <form action="<?= ROOTPATH ?>/user/login" method="POST" class="w-400 mw-full">
        <input type="hidden" name="redirect" value="<?= $_GET['redirect'] ?? $_SERVER['REQUEST_URI'] ?>">
        <div class="form-group">
            <label for="username"><?= lang('User name', 'Nutzername') ?>: </label>
            <input class="form-control" id="username" type="text" name="username" placeholder="abc21" required />
        </div>
        <div class="form-group">
            <label for="password"><?= lang('Password', 'Passwort') ?>: </label>
            <input class="form-control" id="password" type="password" name="password" placeholder="your windows password" required />
        </div>
        <input class="btn secondary" type="submit" name="submit" value="<?= lang("Log-in", 'Einloggen') ?>" />
    </form>


<?php } elseif ($UM == 'OAUTH') {
    if (!defined('OAUTH') || !defined('AUTHORITY') || !defined('CLIENT_ID') || !defined('REDIRECT_URI') || !defined('SCOPES')) {
        die('OAUTH not correctly defined in CONFIG.php');
    }
?>
    <a href="<?=ROOTPATH?>/user/oauth" class="btn primary">
        <?= lang('Log-in with your ' . OAUTH . ' account', 'Mit deinem ' . OAUTH . '-Konto einloggen') ?>
    </a>

<?php } elseif ($UM == 'AUTH') { ?>
    <h5>
        <?php
        if ($Settings->get('affiliation') === 'LISI') {
            echo lang('Please log-in with your Demo account.', 'Bitte melde dich mit deinem Demo-Benutzeraccount an.');
        } else {
            echo lang('Please log-in with your OSIRIS account.', 'Bitte melde dich mit deinem OSIRIS-Benutzeraccount an.');
        }
        ?>
    </h5>


    <form action="<?= ROOTPATH ?>/user/login" method="POST" class="w-400 mw-full">
        <input type="hidden" name="redirect" value="<?= $_GET['redirect'] ?? $_SERVER['REQUEST_URI'] ?>">
        <div class="form-group">
            <label for="username"><?= lang('User name', 'Nutzername') ?>: </label>
            <input class="form-control" id="username" type="text" name="username" placeholder="abc21" required />
        </div>
        <div class="form-group">
            <label for="password"><?= lang('Password', 'Passwort') ?>: </label>
            <input class="form-control" id="password" type="password" name="password" placeholder="your windows password" required />
        </div>
        <input class="btn secondary" type="submit" name="submit" value="<?= lang("Log-in", 'Einloggen') ?>" />

        <hr>

        <a class='link d-block' href='<?= ROOTPATH ?>/auth/forgot-password'>
            <?= lang('Forgot password?', 'Password vergessen?') ?></a>
        <a class='link' href='<?= ROOTPATH ?>/auth/new-user'>
            <?= lang('No account? Register now', 'Noch keinen Account? Jetzt registrieren') ?>
        </a>

        <?php if ($Settings->get('affiliation') === 'LISI') { ?>
            <div class="alert signal mt-20">
                <div class="title">Demo</div>
                <?= lang(
                    'This OSIRIS instance is a demo with the fictional institute LISI.',
                    'Bei dieser OSIRIS-Instanz handelt es sich um eine Demo mit dem fiktiven Institut LISI.'
                ) ?>
            </div>
        <?php } ?>
    </form>

<?php } else { ?>
    <div class="alert danger">
        <?= lang('User management not defined.', 'User-Management nicht definiert.') ?>
    </div>
<?php } ?>