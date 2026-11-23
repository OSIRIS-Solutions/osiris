<?php $parallelAuth = strtoupper(USER_MANAGEMENT) === 'AUTH'; ?>
<h1 class="card-title" id="auth-title"><?= $parallelAuth ? t('auth.do_you_already_have_an_osiris_profile') : t('auth.create_an_osiris_profile') ?></h1>
<p class="card-copy"><?= t('auth.this_single_sign_on_account_is_not_linked_to_an_osiris_profile_yet') ?></p>
<?php if ($parallelAuth) { ?>
<p class="card-copy"><?= t('auth.if_you_already_have_an_osiris_account_log_in_to_it_first_and_link') ?></p>
<?php } else { ?>
<p class="card-copy"><?= t('auth.we_could_not_find_a_matching_osiris_profile_would_you_like_to_crea') ?></p>
<?php } ?>
<form action="<?= ROOTPATH ?>/user/oauth-register" method="post">
    <input type="hidden" name="csrf" value="<?= e($pending['csrf']) ?>">
    <?php if ($parallelAuth) { ?>
    <button class="submit" name="action" value="existing" type="submit"><?= t('auth.link_an_existing_account') ?></button>
    <?php } ?>
    <?php if ($Settings->get('auth-self-registration', true)) { ?>
        <p class="card-copy mt-20"><?= t('auth.if_you_do_not_have_an_osiris_account_yet_you_can_create_a_new_prof') ?></p>
        <button class="submit" name="action" value="new" type="submit"><?= t('auth.create_a_new_profile') ?></button>
    <?php } else { ?>
        <p class="card-copy"><?= t('auth.self_registration_is_disabled_please_contact_the_administrator') ?></p>
    <?php } ?>
    <p class="secondary-action"><button class="btn link text-muted mt-0" name="action" value="cancel" type="submit"><?= t('action.cancel') ?></button></p>
</form>
