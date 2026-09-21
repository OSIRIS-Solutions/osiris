<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= lang('common.back_to_login') ?>
</a>

<h1 class="card-title" id="auth-title"><?= lang('people.choose_a_new_password') ?></h1>
<p class="card-copy">
    <?= lang('people.choose_a_secure_password_for_your_osiris_account') ?>
</p>

<form action="<?= ROOTPATH ?>/auth/reset-password" method="post">
    <input type="hidden" name="hash" value="<?= e($resetHash) ?>">
    <div class="form-group">
        <label for="new-password"><?= lang('auth.password_new') ?></label>
        <div class="input-wrap">
            <i class="ph ph-lock-key" aria-hidden="true"></i>
            <input class="form-control" id="new-password" type="password" name="password" autocomplete="new-password" required autofocus>
            <button class="password-toggle" type="button" aria-label="<?= e(lang('common.show_password')) ?>" aria-pressed="false" data-password-toggle>
                <i class="ph ph-eye" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <button class="submit" type="submit">
        <span><?= lang('people.save_new_password') ?></span>
        <i class="ph ph-check" aria-hidden="true"></i>
    </button>
</form>
