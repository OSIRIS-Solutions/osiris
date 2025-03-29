
<?php
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    define('BASEPATH', __DIR__ . '/../');
    include_once BASEPATH . '/CONFIG.php';
    include_once BASEPATH . '/php/LDAPInterface.php';

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
    $LDAP->synchronizeAttributes($ldapMappings, $osiris);

    echo "LDAP-Synchronisation abgeschlossen.\n";
}
?>