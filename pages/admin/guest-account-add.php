<?php include_once BASEPATH . '/header-editor.php'; ?>
<form action="<?= ROOTPATH ?>/crud/admin/add-user" method="post" class="box padded">

    <input type="hidden" name="guestaccount" value="1">
    <h3 class="title">
        <?= lang('Create new user', 'Nutzer anlegen') ?>
    </h3>

    <div class="form-row row-eq-spacing">
        <div class="col floating-form">
            <input class="form-control" type="text" id="username" name="username" required placeholder="username">
            <label class="required" for="username"><?= lang('Username', 'Nutzername') ?></label>
            <small class="text-muted">
                <?= lang('Please choose a username without spaces or special characters', 'Bitte wähle einen Benutzernamen ohne Leerzeichen oder Sonderzeichen ') ?>
            </small>
        </div>

        <div class="col floating-form">
            <input class="form-control" type="password" id="password" name="password" required placeholder="password">
            <label class="required" for="password">Password</label>
        </div>
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

    <!-- valid until -->
    <div class="form-group">
        <label for="valid-until"><?= lang('Valid until', 'Gültig bis') ?></label>
        <input type="date" id="valid-until" name="valid_until" class="form-control" value="<?= $data['valid_until'] ?? '' ?>">
        <small class="form-text text-muted"><?= lang('Leave empty for unlimited validity.', 'Leer lassen für unbegrenzte Gültigkeit.') ?></small>
    </div>

    <button type="submit" class="btn">Submit</button>
</form>