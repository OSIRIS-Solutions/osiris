
<h3>
    <?=lang('common.password_reset_from')?>
    <?=$person['displayname']?>
</h3>

<p>
    <?=lang('common.password_reset_instructions')?>
</p>

<form method="post" action="<?=ROOTPATH?>/auth/admin-reset-password">
    <input type="hidden" name="id" value="<?=$person['_id']?>">
    <button type="submit" class="btn btn-primary"><?=lang('common.password_reset')?></button>
</form>