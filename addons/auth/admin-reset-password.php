
<h3>
    <?=lang('Password reset from', 'Passwort zurücksetzen von')?>
    <?=$person['displayname']?>
</h3>

<p>
    <?=lang('If you reset the password, a link will be created that the user can use to set a new password.', 'Wenn du das Passwort zurücksetzt, wird ein Link erstellt, mit dem der Nutzer ein neues Passwort festlegen kann.')?>
</p>

<form method="post" action="<?=ROOTPATH?>/auth/admin-reset-password">
    <input type="hidden" name="id" value="<?=$person['_id']?>">
    <button type="submit" class="btn btn-primary"><?=lang('Reset password', 'Passwort zurücksetzen')?></button>
</form>