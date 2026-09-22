
<h1>
    <?=lang('ida.log_in_to_ida')?>
</h1>
<form action="<?= ROOTPATH ?>/ida/auth" method="POST" class="w-400 mw-full">
    <input type="hidden" name="redirect" value="<?= $_GET['redirect'] ?? $_SERVER['REQUEST_URI'] ?>">
    <div class="form-group">
        <label for="email"><?= lang('common.email') ?>: </label>
        <input class="form-control" id="email" type="text" name="email" placeholder="abc21" required />
    </div>
    <div class="form-group">
        <label for="password"><?= lang('common.password') ?>: </label>
        <input class="form-control" id="password" type="password" name="password" placeholder="your password" required />
    </div>

    <input class="btn secondary" type="submit" name="submit" value="<?= lang('people.log_in') ?>" />
</form>