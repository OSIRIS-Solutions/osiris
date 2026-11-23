<h1 class="card-title" id="auth-title"><?= t('people.link_single_sign_on') ?></h1>
<form action="<?= ROOTPATH ?>/user/oauth-link" method="post">
    <input type="hidden" name="csrf" value="<?= e($_SESSION['oauth_link_csrf']) ?>">
    <?php if ($pendingLink) { ?>
        <p class="card-copy"><?= t('auth.your_existing_profile_and_all_assignments_will_be_retained_your_em') ?></p>
        <p><strong><?= e($pendingLink['mail']) ?></strong></p>
        <p class="card-copy"><?= t('auth.after_confirmation_you_can_log_in_using_single_sign_on_or_your_exi') ?></p>
        <button class="submit" name="action" value="confirm" type="submit"><?= t('auth.confirm_link_to_single_sign_on') ?></button>
    <?php } else { ?>
        <p class="card-copy"><?= e(t('auth.confirm_your_osiris_password_then_log_in_with_oauth', ['oauth' => osirisOAuthLabel()])) ?></p>
        <div class="form-group">
            <label for="link-password"><?= t('auth.osiris_password') ?></label>
            <input class="form-control" id="link-password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="submit" name="action" value="start" type="submit"><?= t('auth.continue_to_single_sign_on') ?></button>
    <?php } ?>
    <p class="secondary-action"><button class="btn" name="action" value="cancel" type="submit" formnovalidate><?= t('action.cancel') ?></button></p>
</form>
