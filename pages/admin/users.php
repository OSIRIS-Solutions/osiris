<?php

/**
 * Page for managing users with AUTH user management
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/users
 *
 * @package OSIRIS
 * @since 1.3.7
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<style>
    .form-row.row-eq-spacing>[class^=col].floating-form {
        padding-left: unset;
    }
</style>

<?php if (isset($_GET['success'])) { ?>
    <div class="alert signal">
        <div class="title">
            <?= lang('User added', 'Nutzer hinzugefügt') ?>
        </div>
        <p>
            <?= lang('The user was added successfully.', 'Der Nutzer wurde erfolgreich hinzugefügt.') ?>
            <br>
            <a href="<?= ROOTPATH ?>/profile/<?= $_GET['success'] ?>" class="btn">
                <?= lang('View user', 'Nutzer ansehen') ?>
            </a>
        </p>
    </div>
<?php } ?>



<h1><?= lang('User Management', 'Nutzermanagement') ?></h1>

<span class="badge primary">
    <?= lang('By', 'Mittels') ?>
    <?php if (strtoupper(USER_MANAGEMENT) == 'AUTH') { ?>
        <?= lang('AUTH', 'AUTH') ?>
    <?php } elseif (strtoupper(USER_MANAGEMENT) == 'LDAP') { ?>
        <?= lang('LDAP', 'LDAP') ?>
    <?php } elseif (strtoupper(USER_MANAGEMENT) == 'OAUTH2') { ?>
        <?= lang('OAUTH2', 'OAUTH2') ?>
    <?php } ?>
</span>

<form action="<?= ROOTPATH ?>/crud/admin/add-user" method="post" class="box padded">

    <h3 class="title">
        <?= lang('Create new user', 'Nutzer anlegen') ?>
    </h3>

    <div class="form-row row-eq-spacing">
        <div class="col floating-form">
            <input class="form-control" type="text" id="username" name="username" required placeholder="username">
            <label class="required" for="username"><?= lang('Username', 'Nutzername') ?></label>
            <?php if (strtoupper(USER_MANAGEMENT) == 'AUTH') { ?>
                <small class="text-muted">
                    <?= lang('Please choose a username without spaces or special characters', 'Bitte wähle einen Benutzernamen ohne Leerzeichen oder Sonderzeichen ') ?>
                </small>
            <?php } elseif (strtoupper(USER_MANAGEMENT) == 'LDAP') { ?>
                <small class="text-muted">
                    <?= lang('Please make sure that the username equals the username in LDAP (case-sensitive)', 'Vergewisser dich, dass der Benutzername mit dem Benutzernamen in LDAP übereinstimmt (Groß- und Kleinschreibung wird beachtet)') ?>
                </small>
            <?php } elseif (strtoupper(USER_MANAGEMENT) == 'OAUTH2') { ?>
                <small class="text-muted">
                    <?= lang('Please use the exact email address of the user so that OSIRIS can identify them later', 'Bitte verwende die genaue E-Mail-Adresse des Nutzers, damit OSIRIS ihn später identifizieren kann') ?>
                </small>
            <?php } ?>
        </div>

        <?php if (strtoupper(USER_MANAGEMENT) == 'AUTH') { ?>
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
                <!-- <option value="Prof. Dr." <?= $title == 'Prof. Dr.' ? 'selected' : '' ?>>Prof. Dr.</option> -->
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
            <?= lang('Select multiple with <kbd>Ctrl</kbd>.', 'Wähle mehrere mit <kbd>Strg</kbd>.') ?>

            <select name="values[depts][]" id="dept" class="form-control" multiple="multiple" size="5">
                <option value="">Unknown</option>
                <?php
                foreach ($tree as $d => $dept) { ?>
                    <option value="<?= $d ?>" <?= (in_array($d, $data['depts'] ?? [])) == $d ? 'selected' : '' ?>><?= $dept ?></option>
                <?php } ?>
            </select>

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

    <?php
    if ($Settings->get('affiliation') === 'LISI') {
    ?>
        <div class="alert signal mb-20">
            <div class="title">
                Demo
            </div>

            <?= lang('
            This OSIRIS instance is a demo with the fictional institute LISI. 
            The use of this app and therefore the provision of personal data is voluntary. 
            By using this site, you agree to our <a href="/impress" class="">privacy</a> policy.
            User accounts will be deleted by the admin after an unspecified amount of time. If you want me to actively delete your data, contact me.
            ', '
            Bei dieser OSIRIS-Instanz handelt es sich um eine Demo mit dem fiktiven Institut LISI. 
            Die Nutzung dieser App und somit auch der Bereitstellung von personenbezogenen Daten ist freiwillig. 
            Wenn du diese Seite nutzt, stimmst du damit unseren Richtlinien zum <a href="/impress" class="">Datenschutz</a> zu.
            Nutzeraccounts werden nach unbestimmter Zeit vom Admin gelöscht. Wenn ihr möchtet, dass ich eure Daten aktiv lösche, meldet euch bei mir.
            ') ?>
        </div>
    <?php
    }
    ?>


    <button type="submit" class="btn">Submit</button>
</form>