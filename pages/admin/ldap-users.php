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
        <?= lang('Synchronizing users is currently only available for the LDAP Interface.', 'Nutzer-Synchronisation ist zurzeit nur mit der LDAP-Schnittstelle möglich.') ?>
    </div>
<?php
    return;
}
?>
<h1>
    <i class='ph-duotone ph-arrows-clockwise'></i>
    <?= lang('Synchronize users', 'Synchronisiere Nutzer:innen') ?>
</h1>

<p>
    <?= lang('You will see an overview on the next page where you can confirm any actions before they are applied.', 'Auf der nächsten Seite wird eine Übersicht angezeigt, in der du alle Aktionen bestätigen kannst, bevor sie ausgeführt werden.') ?>
</p>


<a class="btn primary" href="<?= ROOTPATH ?>/synchronize-users?action=synchronize">
    <?= lang('Start synchronization now', 'Starte die Synchronisation') ?>
</a>