<h1>
    <?= t('auth.password_forgot') ?>
</h1>

<form action="#" method="post">
    <div class="form-group">
        <label for="mail" class="required"><?= t('common.mail') ?></label>
        <input type="text" name="mail" id="mail" class="form-control" value="" required>
    </div>

    <button class="btn"><?= t('auth.password_reset') ?></button>
</form>