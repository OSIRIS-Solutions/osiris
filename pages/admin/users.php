<?php

/**
 * Page for managing users with AUTH user management
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/users
 *
 * @package OSIRIS
 * @since 1.3.7
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$um = strtoupper(USER_MANAGEMENT);
?>

<style>
    .form-row.row-eq-spacing>[class^=col].floating-form {
        padding-left: unset;
    }
</style>
<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-users" aria-hidden="true"></i>
        <?= lang('Create new user', 'Nutzer anlegen') ?>
    </h1>

    <div class="mb-20">
        <span class="badge primary">
            <?= lang('You are using ', 'Du nutzt ') ?>
            <strong><?= $um ?></strong>
            <?= lang('for authentication', 'zur Authentifizierung') ?>
        </span>
    </div>


    <form action="<?= ROOTPATH ?>/crud/admin/add-user" method="post">

        <div class="form-row row-eq-spacing">
            <div class="col floating-form">
                <input class="form-control" type="text" id="username" name="username" required placeholder="username">
                <label class="required" for="username"><?= lang('Username', 'Nutzername') ?></label>
                <?php if ($um == 'AUTH') { ?>
                    <small class="text-muted">
                        <?= lang('Please choose a username without spaces or special characters', 'Bitte wähle einen Benutzernamen ohne Leerzeichen oder Sonderzeichen ') ?>
                    </small>
                <?php } elseif ($um == 'LDAP') { ?>
                    <small class="text-muted">
                        <?= lang('Please make sure that the username equals the username in LDAP (case-sensitive)', 'Vergewisser dich, dass der Benutzername mit dem Benutzernamen in LDAP übereinstimmt (Groß- und Kleinschreibung wird beachtet)') ?>
                    </small>
                <?php } elseif ($um == 'OAUTH2') { ?>
                    <small class="text-muted">
                        <?= lang('Please use the exact user name from the email address of the user (everything before @)', 'Bitte verwende den genauen Benutzernamen aus der E-Mail-Adresse des Benutzers (alles vor @)') ?>
                    </small>
                <?php } ?>
            </div>

            <?php if ($um == 'AUTH') { ?>
                <div class="col floating-form">
                    <input class="form-control" type="password" id="password" name="password" required placeholder="password">
                    <label class="required" for="password">Password</label>
                </div>
            <?php } ?>
        </div>


        <div class="form-row row-eq-spacing">
            <div class="col-sm-2 floating-form">
                <?php
                $title = $data['academic_title'] ?? '';
                ?>
                <select name="values[academic_title]" id="academic_title" class="form-control">
                    <option value="" <?= $title == '' ? 'selected' : '' ?>><?= lang('None', 'NA') ?></option>
                    <option value="Dr." <?= $title == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                    <option value="Prof. Dr." <?= $title == 'Prof. Dr.' ? 'selected' : '' ?>>Prof. Dr.</option>
                    <option value="PD Dr." <?= $title == 'PD Dr.' ? 'selected' : '' ?>>PD Dr.</option>
                    <option value="Prof." <?= $title == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                    <option value="PD" <?= $title == 'PD' ? 'selected' : '' ?>>PD</option>
                </select>
                <label for="academic_title">Title</label>
            </div>
            <div class="col-sm floating-form">
                <input type="text" name="values[first]" id="first" class="form-control" value="<?= $data['first'] ?? '' ?>" required placeholder="first name">
                <label class="required" for="first"><?= lang('First name', 'Vorname') ?></label>
            </div>
            <div class="col-sm floating-form">
                <input type="text" name="values[last]" id="last" class="form-control" value="<?= $data['last'] ?? '' ?>" required placeholder="last name">
                <label class="required" for="last"><?= lang('Last name', 'Nachname') ?></label>
            </div>
        </div>


        <h5><?= lang('Contact', 'Kontakt') ?></h5>
        <div class="form-row row-eq-spacing">

            <div class="col-sm floating-form">
                <input type="text" name="values[mail]" id="mail" class="form-control" value="<?= $data['mail'] ?? '' ?>" required placeholder="mail">
                <label for="mail" class="required">Mail</label>
            </div>
            <div class="col-sm floating-form">
                <input type="text" name="values[telephone]" id="telephone" class="form-control" value="<?= $data['telephone'] ?? '' ?>" placeholder="phone">
                <label for="telephone"><?= lang('Telephone', 'Telefon') ?></label>
            </div>

        </div>


        <div class="form-group">
            <h5><?= lang('Department', 'Abteilung') ?></h5>

            <?php
            $tree = $Groups->getHierarchyTree();
            ?>

            <div class="form-group">
                <select name="values[depts][]" id="dept" class="form-control" multiple="multiple" size="5">
                    <option value="">Unknown</option>
                    <?php
                    foreach ($tree as $d => $dept) { ?>
                        <option value="<?= $d ?>" <?= (in_array($d, $data['depts'] ?? [])) == $d ? 'selected' : '' ?>><?= $dept ?></option>
                    <?php } ?>
                </select>

                <script>
                    $(document).ready(function() {
                        $("#dept").selectize();
                    });
                </script>
            </div>
        </div>



        <div class="form-group">
            <span><?= lang('Gender', 'Geschlecht') ?>:</span>
            <?php
            $gender = $data['gender'] ?? 'n';
            ?>

            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-m" value="m" <?= $gender == 'm' ? 'checked' : '' ?>>
                <label for="gender-m"><?= lang('Male', 'Männlich') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-f" value="f" <?= $gender == 'f' ? 'checked' : '' ?>>
                <label for="gender-f"><?= lang('Female', 'Weiblich') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-d" value="d" <?= $gender == 'd' ? 'checked' : '' ?>>
                <label for="gender-d"><?= lang('Non-binary', 'Divers') ?></label>
            </div>
            <div class="custom-radio d-inline-block ml-10">
                <input type="radio" name="values[gender]" id="gender-n" value="n" <?= $gender == 'n' ? 'checked' : '' ?>>
                <label for="gender-n"><?= lang('Not specified', 'Nicht angegeben') ?></label>
            </div>

        </div>


        <div>
            <h5><?= lang('Roles', 'Rollen') ?></h5>
            <?php
            $req = $osiris->adminGeneral->findOne(['key' => 'roles']);
            $roles =  DB::doc2Arr($req['value'] ?? array('user', 'scientist', 'admin'));

            foreach ($roles as $role) {
                if ($role === 'user') continue;
            ?>
                <div class="form-group custom-checkbox d-inline-block mr-10">
                    <input type="checkbox" id="role-<?= $role ?>" value="1" name="values[roles][<?= $role ?>]" <?= ($data['roles'][$role] ?? false) ? 'checked' : '' ?>>
                    <label for="role-<?= $role ?>"><?= strtoupper($role) ?></label>
                </div>
            <?php
            }
            ?>


        </div>


        <button type="submit" class="btn success">
            <i class="ph ph-user-plus"></i>
            <?= lang('Create user', 'Benutzer anlegen') ?>
        </button>
    </form>

    <br><br>


    <?php if ($um == 'AUTH') {
        $token = $Settings->get('auth-token');
        if (!$Settings->get('auth-self-registration', true)) { ?>
            <div class="alert mb-20">
                <h5 class="title">
                    <?= lang('Self-registration is disabled', 'Selbstregistrierung ist deaktiviert') ?>
                </h5>
                <p>
                    <?= lang('Currently, self-registration is completely disabled. This means that only an admin can create user accounts. If you want to allow users to register, please enable self-registration and/or set an AUTH token.', 'Derzeit ist die Selbstregistrierung komplett deaktiviert. Das bedeutet, dass nur ein Admin Nutzerkonten erstellen kann. Wenn du Nutzern die Registrierung erlauben möchtest, aktiviere bitte die Selbstregistrierung und/oder setze unten ein AUTH-Token.') ?>
                </p>
                <a href="<?= ROOTPATH ?>/admin/authentication" class="btn">
                    <?= lang('Go to AUTH settings', 'Zu den AUTH-Einstellungen') ?>
                </a>
            </div>
        <?php } elseif (!empty($token)) { ?>
            <div class="box padded">
                <?= lang('To allow users to register, share the following token with them:', 'Um Nutzern die Registrierung zu ermöglichen, teile ihnen folgendes Token mit:') ?>
                <code id="auth-token" class="code"><?= $token ?></code>
                <button class="btn small ml-5" type="button" onclick="copyToClipboard('<?= $token ?>')" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>">
                    <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                </button>
                <br>
                <!-- or share the link -->
                <?= lang('or share the link', 'oder teile den Link') ?>
                <code id="auth-token" class="code"><?= $_SERVER['HTTP_HOST'] ?>/auth/new-user?token=<?= $token ?></code>
                <button class="btn small ml-5" type="button" onclick="copyToClipboard('<?= $_SERVER['HTTP_HOST'] ?>/auth/new-user?token=<?= $token ?>')" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>">
                    <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                </button>
            </div>

            <script>
                function copyToClipboard(text) {
                    navigator.clipboard.writeText(text)
                    toastSuccess('Token copied to clipboard.')
                }
            </script>
        <?php } else { ?>
            <div class="alert mb-20">
                <div class="title">
                    <?= lang('No AUTH token set', 'Kein AUTH-Token gesetzt') ?>
                </div>
                <p>
                    <?= lang('Currently, no AUTH token is set. This means that users can register without a token. If you want to restrict registration, please set an AUTH token below.', 'Derzeit ist kein AUTH-Token gesetzt. Das bedeutet, dass sich Nutzer ohne Token registrieren können. Wenn du die Registrierung einschränken möchtest, setze bitte unten ein AUTH-Token.') ?>
                </p>
                <a href="<?= ROOTPATH ?>/admin/authentication" class="btn">
                    <?= lang('Go to AUTH settings', 'Zu den AUTH-Einstellungen') ?>
                </a>
            </div>
        <?php } ?>

        </p>
    <?php } ?>

</div>