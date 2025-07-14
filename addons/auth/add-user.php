<?php
$data = $_POST['values'] ?? [];
?>

<h1>
    <?= lang('Create new user', 'Nutzer anlegen') ?>
</h1>

<form action="#" method="post">

    <div class="form-row row-eq-spacing">
        <div class="col">
            <label class="required" for="username">Username </label>
            <input class="form-control" type="text" id="username" name="username" required>
        </div>
        <div class="col">
            <label class="required" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
    </div>


    <div class="form-row row-eq-spacing">
        <div class="col-sm-2">
            <?php
            $title = $data['academic_title'] ?? '';
            ?>

            <label for="academic_title">Title</label>
            <select name="values[academic_title]" id="academic_title" class="form-control">
                <option value="" <?= $title == '' ? 'selected' : '' ?>></option>
                <option value="Dr." <?= $title == 'Dr.' ? 'selected' : '' ?>>Dr.</option>
                <option value="Prof. Dr." <?= $title == 'Prof. Dr.' ? 'selected' : '' ?>>Prof. Dr.</option>
                <option value="PD Dr." <?= $title == 'PD Dr.' ? 'selected' : '' ?>>PD Dr.</option>
                <option value="Prof." <?= $title == 'Prof.' ? 'selected' : '' ?>>Prof.</option>
                <option value="PD" <?= $title == 'PD' ? 'selected' : '' ?>>PD</option>
                <!-- <option value="Prof. Dr." <?= $title == 'Prof. Dr.' ? 'selected' : '' ?>>Prof. Dr.</option> -->
            </select>
        </div>
        <div class="col-sm">
            <label class="required" for="first"><?= lang('First name', 'Vorname') ?></label>
            <input type="text" name="values[first]" id="first" class="form-control" value="<?= $data['first'] ?? '' ?>" required>
        </div>
        <div class="col-sm">
            <label class="required" for="last"><?= lang('Last name', 'Nachname') ?></label>
            <input type="text" name="values[last]" id="last" class="form-control" value="<?= $data['last'] ?? '' ?>" required>
        </div>
    </div>



    <div class="form-row row-eq-spacing">
        <div class="col-sm">
            <label for="telephone"><?= lang('Telephone', 'Telefon') ?></label>
            <input type="text" name="values[telephone]" id="telephone" class="form-control" value="<?= $data['telephone'] ?? '' ?>">
        </div>
        <div class="col-sm">
            <label for="mail" class="required">Mail</label>
            <input type="email" name="values[mail]" id="mail" class="form-control" value="<?= $data['mail'] ?? '' ?>" required>
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
        <div class="form-group custom-checkbox d-inline-block ml-10">
            <input type="checkbox" id="is_scientist" value="1" name="values[is_scientist]" <?= ($data['is_scientist'] ?? false) ? 'checked' : '' ?>>
            <label for="is_scientist"><?= lang('I am a scientist', 'Ich bin Wissenschaftler_in') ?></label>
        </div>

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