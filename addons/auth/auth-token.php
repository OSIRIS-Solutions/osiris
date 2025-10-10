<?php
    
?>

<h1>
    <i class="ph ph-user-plus" aria-hidden="true"></i>
    <?= lang('Register', 'Registrieren') ?>
</h1>

<p>
    <?= lang('To register, please contact the administrator.', 'Um dich zu registrieren, kontaktiere bitte den Administrator.') ?>
</p>


<form action="#" method="get">
    <div class="form-group">
        <label for="token"><?= lang('AUTH Token', 'AUTH-Token') ?></label>
        <input type="text" class="form-control" name="token" id="token" value="<?= $_GET['token'] ?? '' ?>" required>
    </div>

    <button type="submit" class="btn primary">
        <?= lang('Continue', 'Weiter') ?>
    </button>
</form>