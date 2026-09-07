<h1>
    <?= lang('system.password_forgot') ?>
</h1>

<form action="#" method="post">
    <div class="form-group">
        <label for="mail" class="required">Mail</label>
        <input type="text" name="mail" id="mail" class="form-control" value="" required>
    </div>

    <button class="btn"><?= lang('system.password_reset') ?></button>
</form>