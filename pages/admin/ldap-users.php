<?php

/**
 * Synchronize users from LDAP connection
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

if (strtoupper(USER_MANAGEMENT) !== 'LDAP') {
?>
    <div class="alert danger">
        <?= lang('admin.synchronizing_users_is_currently_only_available_for_the_ldap_interface') ?>
    </div>
<?php
    return;
}
?>
<h1>
    <i class='ph-duotone ph-arrows-clockwise'></i>
    <?= lang('common.synchronize_users') ?>
</h1>

<p>
    <?= lang('admin.you_will_see_an_overview_on_the_next_page_where_you_can_confirm_any_actions') ?>
</p>


<a class="btn primary" href="<?= ROOTPATH ?>/synchronize-users?action=synchronize">
    <?= lang('admin.start_synchronization_now') ?>
</a>