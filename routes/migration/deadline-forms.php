<?php
/** Initialize the previous deadline form without replacing existing definitions. */
require_once BASEPATH . '/setup/defaults.php';
setupDefaults($osiris, ['deadlines']);
echo '<p>' . e(t('admin.deadline_form_configuration_prepared_existing_settings_and_data_ar')) . '</p>';
