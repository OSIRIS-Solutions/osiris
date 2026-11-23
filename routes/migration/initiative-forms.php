<?php
/** Give new initiative configurations a small starting form. */
require_once BASEPATH . '/setup/defaults.php';
setupDefaults($osiris, ['initiatives']);
echo '<p>' . e(t('admin.initiative_form_configuration_prepared_existing_settings_and_data')) . '</p>';
