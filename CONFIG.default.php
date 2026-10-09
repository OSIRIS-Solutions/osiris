<?php

// define relative path to your root folder 
define('ROOTPATH', '');

// define ADMIN user name
define('ADMIN', 'juk20');

// set system to maintenance mode
define('MAINTENANCE', false);
// if Live is set to false, the system will indicate "Testsystem" in the header
define('LIVE', true);

// possible values are 'AUTH', 'LDAP' or 'OAUTH'
define('USER_MANAGEMENT', 'AUTH');

// LDAP user management:
// define("LDAP_IP", "100.10.100.0");
// define("LDAP_PORT", 389);
// define("LDAP_USER", "osiris");
// define("LDAP_DOMAIN", "@domain.local");
// define("LDAP_PASSWORD", "ldap_password");
// define("LDAP_BASEDN", "OU=Users,OU=DSMZ,DC=dsmz,DC=local");
// define("LDAP_FILTER", "(&(objectClass=user)(samaccountname=*))");
// define('OPEN_LDAP', false);
// define('LDAP_BIND_METHOD', 'username'); // <-- 'dn' or 'username' or 'userPrincipalName'

// OAuth configuration: with USER_MANAGEMENT='AUTH', defining OAUTH enables
// the additional SSO login. USER_MANAGEMENT='OAUTH' keeps standalone SSO.
// OAUTH is the login button label, e.g. Keycloak or Life Science Login.
// Parallel linking and SSO registration use the existing AUTH registration settings.
// define('OAUTH', 'Microsoft'); // login button label
// define('CLIENT_ID', 'DEINE_CLIENT_ID');
// define('CLIENT_SECRET', 'DEIN_CLIENT_SECRET');
// define('REDIRECT_URI', 'http://localhost/user/oauth-callback');
// define('AUTHORITY', 'https://login.microsoftonline.com/common');
// define('SCOPES', 'openid profile email User.Read');
// Keycloak / Generic: AUTHORITY is the endpoint prefix ending in /protocol/openid-connect.
// Example: http://localhost:8080/realms/osiris/protocol/openid-connect
// Keycloak / Life Science Login scopes: openid profile email


// define DB connection
define("DB_NAME", "osiris");

// check if OSIRIS_DB_HOST (Docker) is defined, else opt out to localhost
$host_db = getenv('OSIRIS_DB_HOST') ?? "localhost";
if (empty($host_db)) {
    $host_db = "localhost";
}
define("DB_HOST", $host_db);

define("DB_STRING", "mongodb://" . DB_HOST . ":27017/" . DB_NAME . "?retryWrites=true&w=majority");

// define CRON secret for cron jobs
define('CRON_SECRET', 'your_cron_secret');

// not needed right now, but planned in the future
define("ORCID_APP_ID", null);
define("ORCID_SECRET_KEY", null);

