<h1>
    <?= lang('auth.password_forgot') ?>
</h1>

<form action="#" method="post">
    <div class="form-group">
        <label for="mail" class="required"><?= lang('common.mail') ?></label>
        <input type="text" name="mail" id="mail" class="form-control" value="" required>
    </div>

    <button class="btn"><?= lang('auth.password_reset') ?></button>
</form>