
<h3>
    <?=t('auth.password_reset_from')?>
    <?=$person['displayname']?>
</h3>

<p>
    <?=t('auth.password_reset_instructions')?>
</p>

<form method="post" action="<?=ROOTPATH?>/auth/admin-reset-password">
    <input type="hidden" name="id" value="<?=$person['_id']?>">
    <button type="submit" class="btn btn-primary"><?=t('auth.password_reset')?></button>
</form>