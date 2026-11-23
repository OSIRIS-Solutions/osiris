<?php $registrationIsSso = $registrationIsSso ?? false; ?>
<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= t('common.back_to_login') ?>
</a>

<h1 class="card-title" id="auth-title"><?= t('common.create_account') ?></h1>

<?php if (!empty($registrationRequiresToken)) { ?>
    <p class="card-copy">
        <?= t('people.registration_is_protected_enter_the_auth_token_provided_by_your_administrat') ?>
    </p>

    <form action="<?= ROOTPATH ?>/auth/new-user" method="get">
        <div class="form-group">
            <label for="token"><?= t('admin.auth_token') ?></label>
            <div class="input-wrap">
                <i class="ph ph-key" aria-hidden="true"></i>
                <input class="input" id="token" type="text" name="token" value="<?= e($_GET['token'] ?? '') ?>" required autofocus>
            </div>
        </div>
        <button class="submit" type="submit">
            <span><?= t('action.continue') ?></span>
            <i class="ph ph-arrow-right" aria-hidden="true"></i>
        </button>
    </form>
<?php } else { ?>
    <p class="card-copy">
        <?= t('people.create_your_personal_osiris_account_required_fields_are_marked_with_an_aste') ?>
    </p>

    <?php $registrationData = $registrationData ?? ($_POST['values'] ?? []); ?>
    <form action="<?= ROOTPATH ?><?= e($registrationAction ?? '/auth/new-user') ?>" method="post">
        <?php if ($registrationIsSso) { ?>
            <input type="hidden" name="csrf" value="<?= e($registrationCsrf) ?>">
            <input type="hidden" name="action" value="create">
        <?php } ?>
        <?php if (!$registrationIsSso && !empty($registrationToken)) { ?>
            <input type="hidden" name="token" value="<?= e($registrationToken) ?>">
        <?php } ?>

        <div class="form-grid <?= $registrationIsSso ? '' : 'form-grid--2' ?>">
            <div class="form-group">
                <label class="required" for="register-username"><?= t('common.username_guest_account_add') ?></label>
                <div class="input-wrap">
                    <i class="ph ph-user" aria-hidden="true"></i>
                    <input class="form-control" id="register-username" type="text" name="username" value="<?= e($registrationUsername ?? ($_POST['username'] ?? '')) ?>" autocomplete="username" required autofocus>
                </div>
            </div>
            <?php if (!$registrationIsSso) { ?>
            <div class="form-group">
                <label class="required" for="register-password"><?= t('common.password') ?></label>
                <div class="input-wrap">
                    <i class="ph ph-lock-key" aria-hidden="true"></i>
                    <input class="form-control" id="register-password" type="password" name="password" autocomplete="new-password" required>
                    <button class="password-toggle" type="button" aria-label="<?= e(t('common.show_password')) ?>" aria-pressed="false" data-password-toggle>
                        <i class="ph ph-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <?php } ?>
        </div>

        <div class="form-grid form-grid--name">
            <div class="form-group">
                <label for="academic-title"><?= t('common.title') ?></label>
                <?php $academicTitle = $registrationData['academic_title'] ?? ''; ?>
                <select class="form-control" name="values[academic_title]" id="academic-title">
                    <?php foreach (['', 'Dr.', 'Prof. Dr.', 'PD Dr.', 'Prof.', 'PD'] as $titleOption) { ?>
                        <option value="<?= e($titleOption) ?>" <?= $academicTitle === $titleOption ? 'selected' : '' ?>><?= e($titleOption) ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label class="required" for="first-name"><?= t('common.name_first') ?></label>
                <input class="form-control" id="first-name" type="text" name="values[first]" value="<?= e($registrationData['first'] ?? '') ?>" autocomplete="given-name" required>
            </div>
            <div class="form-group">
                <label class="required" for="last-name"><?= t('common.name_last') ?></label>
                <input class="form-control" id="last-name" type="text" name="values[last]" value="<?= e($registrationData['last'] ?? '') ?>" autocomplete="family-name" required>
            </div>
        </div>

        <div class="form-grid form-grid--2">
            <div class="form-group">
                <label class="required" for="register-mail"><?= t('common.email_address') ?></label>
                <input class="form-control" id="register-mail" type="email" name="values[mail]" value="<?= e($registrationData['mail'] ?? '') ?>" autocomplete="email" <?= $registrationIsSso && $registrationMailLocked ? 'readonly' : '' ?> required>
            </div>
            <div class="form-group">
                <label for="telephone"><?= t('common.telephone') ?></label>
                <input class="form-control" id="telephone" type="tel" name="values[telephone]" value="<?= e($registrationData['telephone'] ?? '') ?>" autocomplete="tel">
            </div>
        </div>

        <?php $gender = $registrationData['gender'] ?? 'n'; ?>
        <fieldset class="choice-group">
            <legend><?= t('common.gender') ?></legend>
            <?php
            $genderOptions = [
                'm' => t('common.gender_male'),
                'f' => t('common.gender_female'),
                'd' => t('common.gender_non_binary'),
                'n' => t('common.gender_not_specified'),
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
            <span><?= t('people.i_am_a_scientist') ?></span>
        </label>

        <?php if ($registrationIsSso) { ?>
            <div class="form-group">
                <label for="registration-units"><?= t('people.organisational_units') ?></label>
                <select class="form-control" id="registration-units" name="units[]" multiple size="4">
                    <?php foreach ($Groups->groups as $group) { if (!empty($group['inactive'])) continue; ?>
                        <option value="<?= e((string) $group['id']) ?>" <?= in_array((string) $group['id'], $registrationUnits, true) ? 'selected' : '' ?>><?= e($group[t('common.field_name_language')] ?? $group['name']) ?></option>
                    <?php } ?>
                </select>
                <small><?= t('people.you_can_select_several_units_if_you_select_i_am_a_scientist_these_affiliations_are_re') ?></small>
            </div>
            <?php if ($registrationTokenRequired) { ?>
                <div class="form-group">
                    <label class="required" for="sso-registration-token"><?= t('admin.auth_token') ?></label>
                    <input class="form-control" id="sso-registration-token" name="token" type="password" value="<?= e($registrationToken) ?>" autocomplete="off" required>
                    <small><?= t('people.enter_the_registration_token_provided_by_your_administrator') ?></small>
                </div>
            <?php } ?>
        <?php } ?>

        <?php if ($affiliation === 'LISI') { ?>
            <div class="demo">
                <strong>Demo:</strong>
                <?= t('people.this_is_a_voluntary_demo_instance_please_review_the_privacy_policy_before_c') ?>
            </div>
        <?php } ?>

        <button class="submit register-submit" type="submit">
            <span><?= t('common.create_account') ?></span>
            <i class="ph ph-user-plus" aria-hidden="true"></i>
        </button>
        <?php if ($registrationIsSso) { ?>
            <p class="secondary-action"><button class="btn" type="submit" name="action" value="cancel" formnovalidate><?= t('action.cancel') ?></button></p>
        <?php } ?>
    </form>
<?php } ?>
