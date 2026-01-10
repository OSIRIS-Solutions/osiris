<?php

// define relative path to your root folder 
if (!defined('ROOTPATH'))
    define('ROOTPATH', '');

// define ADMIN user name
if (!defined('ADMIN'))
    define('ADMIN', '');

if (!defined('USER_MANAGEMENT'))
    define('USER_MANAGEMENT', 'AUTH');

if (!defined('LIVE'))
    define('LIVE', true);

// define LDAP connection
if (USER_MANAGEMENT == 'LDAP') {
    if (!defined('LDAP_IP'))
        die("Error in your CONFIG: USER_MANAGEMENT is set to LDAP, but LDAP_IP is not set.");
    if (!defined('LDAP_PORT'))
        die("Error in your CONFIG: USER_MANAGEMENT is set to LDAP, but LDAP_PORT is not set.");
    if (!defined('LDAP_USER'))
        die("Error in your CONFIG: USER_MANAGEMENT is set to LDAP, but LDAP_USER is not set.");
    if (!defined('LDAP_DOMAIN'))
        die("Error in your CONFIG: USER_MANAGEMENT is set to LDAP, but LDAP_DOMAIN is not set.");
    if (!defined('LDAP_PASSWORD'))
        die("Error in your CONFIG: USER_MANAGEMENT is set to LDAP, but LDAP_PASSWORD is not set.");
    if (!defined('LDAP_BASEDN'))
        die("Error in your CONFIG: USER_MANAGEMENT is set to LDAP, but LDAP_BASEDN is not set.");
    if (!defined('LDAP_FILTER'))
        // z.B. for filtering by group: (&(memberOf=CN=GroupName,OU=Groups,DC=example,DC=com)(objectClass=user))
        define('LDAP_FILTER', null);

    if (!defined('OPEN_LDAP'))
        define('OPEN_LDAP', false);
    if (!defined('LDAP_USE_SSL'))
        define('LDAP_USE_SSL', false);
    if (!defined('LDAP_USE_TLS'))
        define('LDAP_USE_TLS', false);
}

// define DB connection
if (!defined('DB_NAME'))
    define("DB_NAME", "osiris");
if (!defined('DB_STRING'))
    define("DB_STRING", "mongodb://localhost:27017/" . DB_NAME . "?retryWrites=true&w=majority");

// define API keys
if (!defined('WOS_STARTER_KEY'))
    define("WOS_STARTER_KEY", "wos starter key");

if (!defined('WOS_JOURNAL_INFO'))
    define("WOS_JOURNAL_INFO", 2021);

// not needed right now, but planned in the future
if (!defined('ORCID_APP_ID'))
    define("ORCID_APP_ID", null);
if (!defined('ORCID_SECRET_KEY'))
    define("ORCID_SECRET_KEY", null);

// if (!defined('$base'))
//     define('$base', $_GET['path']??(ROOTPATH.'/preview'));

if (!defined('CRON_SECRET'))
    define('CRON_SECRET', 'please-change-this-secret');