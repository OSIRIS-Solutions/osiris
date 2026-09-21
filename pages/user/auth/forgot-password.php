<a class="back-link" href="<?= ROOTPATH ?>/user/login">
    <i class="ph ph-arrow-left" aria-hidden="true"></i>
    <?= lang('common.back_to_login') ?>
</a>

<h1 class="card-title" id="auth-title"><?= lang('common.forgot_password') ?></h1>
<p class="card-copy">
    <?= lang('people.enter_the_email_address_associated_with_your_account_we_will_send_you_a_lin') ?>
</p>

<form action="<?= ROOTPATH ?>/auth/forgot-password" method="post">
    <div class="form-group">
        <label for="mail"><?= lang('common.email_address') ?></label>
        <div class="input-wrap">
            <i class="ph ph-envelope-simple" aria-hidden="true"></i>
            <input class="form-control" id="mail" type="email" name="mail" value="<?= e($_POST['mail'] ?? '') ?>" autocomplete="email" required autofocus>
        </div>
    </div>

    <button class="submit" type="submit">
        <span><?= lang('people.request_reset_link') ?></span>
        <i class="ph ph-paper-plane-tilt" aria-hidden="true"></i>
    </button>
</form>
