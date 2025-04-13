
<?php
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    define('BASEPATH', __DIR__ . '/../');
    include_once BASEPATH . '/CONFIG.php';
    include_once BASEPATH . '/php/LDAPInterface.php';

    if (!function_exists('lang')) {
        function lang($text, $translation = null)
        {
            return $text;
        }
    }

    global $DB;
    $DB = new DB;

    global $osiris;
    $osiris = $DB->db;

    $settings = $osiris->adminGeneral->findOne(['key' => 'ldap_mappings']);
    $ldapMappings = DB::doc2Arr($settings['value'] ?? []);

    if (empty($ldapMappings)) {
        echo "No LDAP mappings found.\n";
        exit;
    }
    $LDAP = new LDAPInterface();
    $success = $LDAP->synchronizeAttributes($ldapMappings, $osiris);
    if ($success) {
        echo "User attributes synchronized successfully.\n";

        $osiris->system->updateOne(
            ['key' => 'ldap-sync'],
            ['$set' => ['value' => date('Y-m-d H:i:s')]],
            ['upsert' => true]
        );
    } else {
        echo "Failed to synchronize user attributes.\n";
    }
}
?>