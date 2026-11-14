<?php
/** Give new initiative configurations a small starting form. */
require_once BASEPATH . '/setup/defaults.php';
setupDefaults($osiris, ['initiatives']);
echo '<p>' . e(lang('Initiative form configuration prepared. Existing settings and data are preserved.', 'Initiativen-Formularkonfiguration vorbereitet. Vorhandene Einstellungen und Daten bleiben erhalten.')) . '</p>';
