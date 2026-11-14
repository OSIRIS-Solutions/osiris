<?php
/** Initialize the previous deadline form without replacing existing definitions. */
require_once BASEPATH . '/setup/defaults.php';
setupDefaults($osiris, ['deadlines']);
echo '<p>' . e(lang('Deadline form configuration prepared. Existing settings and data are preserved.', 'Deadline-Formularkonfiguration vorbereitet. Vorhandene Einstellungen und Daten bleiben erhalten.')) . '</p>';
