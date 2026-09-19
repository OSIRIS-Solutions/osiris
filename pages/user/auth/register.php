<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= lang('Back to login', 'Zurück zur Anmeldung') ?>
</a>

<h1 class="card-title" id="auth-title"><?= lang('Create account', 'Account erstellen') ?></h1>

<?php if (!empty($registrationRequiresToken)) { ?>
    <p class="card-copy">
        <?= lang(
            'Registration is protected. Enter the AUTH token provided by your administrator.',
            'Die Registrierung ist geschützt. Gib das AUTH-Token ein, das du von der Administration erhalten hast.'
        ) ?>
    </p>

    <form action="<?= ROOTPATH ?>/auth/new-user" method="get">
        <div class="form-group">
            <label for="token"><?= lang('AUTH token', 'AUTH-Token') ?></label>
            <div class="input-wrap">
                <i class="ph ph-key" aria-hidden="true"></i>
                <input class="input" id="token" type="text" name="token" value="<?= e($_GET['token'] ?? '') ?>" required autofocus>
            </div>
        </div>
        <button class="submit" type="submit">
            <span><?= lang('Continue', 'Weiter') ?></span>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
<?php } else { ?>
    <p class="card-copy">
        <?= lang(
            'Create your personal OSIRIS account. Required fields are marked with an asterisk.',
            'Erstelle deinen persönlichen OSIRIS-Account. Pflichtfelder sind mit einem Sternchen markiert.'
        ) ?>
    </p>

    <?php $registrationData = $_POST['values'] ?? []; ?>
    <form action="<?= ROOTPATH ?>/auth/new-user<?= !empty($registrationToken) ? '?token=' . urlencode($registrationToken) : '' ?>" method="post">
        <?php if (!empty($registrationToken)) { ?>
            <input type="hidden" name="token" value="<?= e($registrationToken) ?>">
        <?php } ?>

        <div class="form-grid form-grid--2">
            <div class="form-group">
                <label class="required" for="register-username"><?= lang('Username', 'Nutzername') ?></label>
                <div class="input-wrap">
                    <i class="ph ph-user" aria-hidden="true"></i>
                    <input class="form-control" id="register-username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="required" for="register-password"><?= lang('Password', 'Passwort') ?></label>
                <div class="input-wrap">
                    <i class="ph ph-lock-key" aria-hidden="true"></i>
                    <input class="form-control" id="register-password" type="password" name="password" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" aria-label="<?= e(lang('Show password', 'Passwort anzeigen')) ?>" aria-pressed="false" data-password-toggle>
                        <i class="ph ph-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-grid form-grid--name">
            <div class="form-group">
                <label for="academic-title"><?= lang('Title', 'Titel') ?></label>
                <?php $academicTitle = $registrationData['academic_title'] ?? ''; ?>
                <select class="form-control" name="values[academic_title]" id="academic-title">
                    <?php foreach (['', 'Dr.', 'Prof. Dr.', 'PD Dr.', 'Prof.', 'PD'] as $titleOption) { ?>
                        <option value="<?= e($titleOption) ?>" <?= $academicTitle === $titleOption ? 'selected' : '' ?>><?= e($titleOption) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label class="required" for="first-name"><?= lang('First name', 'Vorname') ?></label>
                <input class="form-control" id="first-name" type="text" name="values[first]" value="<?= e($registrationData['first'] ?? '') ?>" autocomplete="given-name" required>
            </div>
            <div class="form-group">
                <label class="required" for="last-name"><?= lang('Last name', 'Nachname') ?></label>
                <input class="form-control" id="last-name" type="text" name="values[last]" value="<?= e($registrationData['last'] ?? '') ?>" autocomplete="family-name" required>
            </div>
        </div>

        <div class="form-grid form-grid--2">
            <div class="form-group">
                <label class="required" for="register-mail"><?= lang('Email address', 'E-Mail-Adresse') ?></label>
                <input class="form-control" id="register-mail" type="email" name="values[mail]" value="<?= e($registrationData['mail'] ?? '') ?>" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="telephone"><?= lang('Telephone', 'Telefon') ?></label>
                <input class="form-control" id="telephone" type="tel" name="values[telephone]" value="<?= e($registrationData['telephone'] ?? '') ?>" autocomplete="tel">
            </div>
        </div>

        <?php $gender = $registrationData['gender'] ?? 'n'; ?>
        <fieldset class="choice-group">
            <legend><?= lang('Gender', 'Geschlecht') ?></legend>
            <?php
            $genderOptions = [
                'm' => lang('Male', 'Männlich'),
                'f' => lang('Female', 'Weiblich'),
                'd' => lang('Non-binary', 'Divers'),
                'n' => lang('Not specified', 'Nicht angegeben'),
            ];
            foreach ($genderOptions as $genderValue => $genderLabel) { ?>
                <label class="choice" for="gender-<?= $genderValue ?>">
                    <input id="gender-<?= $genderValue ?>" type="radio" name="values[gender]" value="<?= $genderValue ?>" <?= $gender === $genderValue ? 'checked' : '' ?>>
                    <span><?= $genderLabel ?></span>
                </label>
            <?php } ?>
        </fieldset>

        <label class="checkbox registration-checkbox" for="is-scientist">
            <input id="is-scientist" type="checkbox" value="1" name="values[is_scientist]" <?= !empty($registrationData['is_scientist']) ? 'checked' : '' ?>>
            <span><?= lang('I am a scientist', 'Ich bin Wissenschaftler:in') ?></span>
        </label>

        <?php if ($affiliation === 'LISI') { ?>
            <div class="demo">
                <strong>Demo:</strong>
                <?= lang(
                    'This is a voluntary demo instance. Please review the privacy policy before creating an account.',
                    'Dies ist eine freiwillig nutzbare Demo-Instanz. Bitte lies vor der Registrierung die Datenschutzerklärung.'
                ) ?>
            </div>
        <?php } ?>

        <button class="submit register-submit" type="submit">
            <span><?= lang('Create account', 'Account erstellen') ?></span>
            <i class="ph ph-user-plus" aria-hidden="true"></i>
        </button>
    </form>
<?php } ?>