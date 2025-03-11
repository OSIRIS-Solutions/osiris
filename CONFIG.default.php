<?php

// define relative path to your root folder 
define('ROOTPATH', '');

// define ADMIN user name
define('ADMIN', 'juk20');

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

// OAUTH user management:
// define('OAUTH', 'Microsoft');
// define('CLIENT_ID', 'DEINE_CLIENT_ID');
// define('CLIENT_SECRET', 'DEIN_CLIENT_SECRET');
// define('REDIRECT_URI', 'http://localhost/login-callback.php');
// define('AUTHORITY', 'https://login.microsoftonline.com/common');
// define('SCOPES', 'openid profile email User.Read');


// define DB connection
define("DB_NAME", "osiris");
define("DB_STRING", "mongodb://localhost:27017/" . DB_NAME . "?retryWrites=true&w=majority");

// define API keys
define("WOS_STARTER_KEY", "wos starter key");

define("WOS_JOURNAL_INFO", 2021);

// not needed right now, but planned in the future
define("ORCID_APP_ID", null);
define("ORCID_SECRET_KEY", null);
