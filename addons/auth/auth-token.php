<?php
    
/**
 * Page for AUTH token registration
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph ph-user-plus" aria-hidden="true"></i>
    <?= lang('common.register') ?>
</h1>

<p>
    <?= lang('auth.register_contact_admin') ?>
</p>


<form action="#" method="get">
    <div class="form-group">
        <label for="token"><?= lang('auth.auth_token') ?></label>
        <input type="text" class="form-control" name="token" id="token" value="<?= $_GET['token'] ?? '' ?>" required>
    </div>

    <button type="submit" class="btn primary">
        <?= lang('action.continue') ?>
    </button>
</form>