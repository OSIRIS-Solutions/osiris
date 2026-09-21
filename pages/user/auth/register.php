<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= lang('common.back_to_login') ?>
</a>

<h1 class="card-title" id="auth-title"><?= lang('common.create_account') ?></h1>

<?php if (!empty($registrationRequiresToken)) { ?>
    <p class="card-copy">
        <?= lang('people.registration_is_protected_enter_the_auth_token_provided_by_your_administrat') ?>
    </p>

    <form action="<?= ROOTPATH ?>/auth/new-user" method="get">
        <div class="form-group">
            <label for="token"><?= lang('admin.auth_token') ?></label>
            <div class="input-wrap">
                <i class="ph ph-key" aria-hidden="true"></i>
                <input class="input" id="token" type="text" name="token" value="<?= e($_GET['token'] ?? '') ?>" required autofocus>
            </div>
        </div>
        <button class="submit" type="submit">
            <span><?= lang('action.continue') ?></span>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
<?php } else { ?>
    <p class="card-copy">
        <?= lang('people.create_your_personal_osiris_account_required_fields_are_marked_with_an_aste') ?>
    </p>

    <?php $registrationData = $_POST['values'] ?? []; ?>
    <form action="<?= ROOTPATH ?>/auth/new-user<?= !empty($registrationToken) ? '?token=' . urlencode($registrationToken) : '' ?>" method="post">
        <?php if (!empty($registrationToken)) { ?>
            <input type="hidden" name="token" value="<?= e($registrationToken) ?>">
        <?php } ?>

        <div class="form-grid form-grid--2">
            <div class="form-group">
                <label class="required" for="register-username"><?= lang('common.username_guest_account_add') ?></label>
                <div class="input-wrap">
                    <i class="ph ph-user" aria-hidden="true"></i>
                    <input class="form-control" id="register-username" type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label class="required" for="register-password"><?= lang('common.password') ?></label>
                <div class="input-wrap">
                    <i class="ph ph-lock-key" aria-hidden="true"></i>
                    <input class="form-control" id="register-password" type="password" name="password" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" aria-label="<?= e(lang('common.show_password')) ?>" aria-pressed="false" data-password-toggle>
                        <i class="ph ph-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="form-grid form-grid--name">
            <div class="form-group">
                <label for="academic-title"><?= lang('common.title') ?></label>
                <?php $academicTitle = $registrationData['academic_title'] ?? ''; ?>
                <select class="form-control" name="values[academic_title]" id="academic-title">
                    <?php foreach (['', 'Dr.', 'Prof. Dr.', 'PD Dr.', 'Prof.', 'PD'] as $titleOption) { ?>
                        <option value="<?= e($titleOption) ?>" <?= $academicTitle === $titleOption ? 'selected' : '' ?>><?= e($titleOption) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label class="required" for="first-name"><?= lang('common.name_first') ?></label>
                <input class="form-control" id="first-name" type="text" name="values[first]" value="<?= e($registrationData['first'] ?? '') ?>" autocomplete="given-name" required>
            </div>
            <div class="form-group">
                <label class="required" for="last-name"><?= lang('common.name_last') ?></label>
                <input class="form-control" id="last-name" type="text" name="values[last]" value="<?= e($registrationData['last'] ?? '') ?>" autocomplete="family-name" required>
            </div>
        </div>

        <div class="form-grid form-grid--2">
            <div class="form-group">
                <label class="required" for="register-mail"><?= lang('common.email_address') ?></label>
                <input class="form-control" id="register-mail" type="email" name="values[mail]" value="<?= e($registrationData['mail'] ?? '') ?>" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="telephone"><?= lang('common.telephone') ?></label>
                <input class="form-control" id="telephone" type="tel" name="values[telephone]" value="<?= e($registrationData['telephone'] ?? '') ?>" autocomplete="tel">
            </div>
        </div>

        <?php $gender = $registrationData['gender'] ?? 'n'; ?>
        <fieldset class="choice-group">
            <legend><?= lang('common.gender') ?></legend>
            <?php
            $genderOptions = [
                'm' => lang('common.gender_male'),
                'f' => lang('common.gender_female'),
                'd' => lang('common.gender_non_binary'),
                'n' => lang('common.gender_not_specified'),
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
            <span><?= lang('people.i_am_a_scientist') ?></span>
        </label>

        <?php if ($affiliation === 'LISI') { ?>
            <div class="demo">
                <strong>Demo:</strong>
                <?= lang('people.this_is_a_voluntary_demo_instance_please_review_the_privacy_policy_before_c') ?>
            </div>
        <?php } ?>

        <button class="submit register-submit" type="submit">
            <span><?= lang('common.create_account') ?></span>
            <i class="ph ph-user-plus" aria-hidden="true"></i>
        </button>
    </form>
<?php } ?>