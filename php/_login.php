
<?php
// apt-get install php-ldap

if (!defined('LDAP_IP')) {
    if (file_exists('CONFIG.php')) {
        require_once 'CONFIG.php';
    } else {
        require_once 'CONFIG.default.php';
    }
}
if (!defined('LDAP_IP') || !defined('LDAP_PORT') || !defined('LDAP_USER') || !defined('LDAP_DOMAIN') || !defined('LDAP_BASEDN') || !defined('LDAP_PASSWORD')) {
    die("LDAP Settings are missing. Please enter details in CONFIG.php or use AUTH as USER_MANAGEMENT.");
}


require_once BASEPATH . '/php/Groups.php';

function LDAPconnect($username, $password)
{
    $ip = LDAP_IP;
    $ldap_port = LDAP_PORT;
    $dn = $username . LDAP_DOMAIN;
    // $base_dn = LDAP_BASEDN;

    $ldap_address = ($ldap_port == 636) ? "ldaps://" . $ip : "ldap://" . $ip;

    $connect = @ldap_connect($ldap_address . ":" . $ldap_port);
    if (!$connect) {
        return "Verbindung zum Server fehlgeschlagen.";
    }

    ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);
    ldap_set_option($connect, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);

    $bind = @ldap_bind($connect, $dn, $password);
    if (!$bind) {
        $error = ldap_error($connect);
        ldap_close($connect);
        return lang("Error while connecting to the LDAP server:", "Fehler bei der Verbindung mit dem LDAP-Server: ") . $error;
    }

    return $connect;
}

function login($username, $password)
{
    $return = array("msg" => '', "success" => false);

    $connect = LDAPconnect($username, $password);
    if (is_string($connect)) {
        $return['msg'] = $connect;
        return $return;
    }

    $fields = "(|(samaccountname=$username))";
    $base_dn = LDAP_BASEDN;
    $search = ldap_search($connect, $base_dn, $fields);
    if ($search === false) {
        $return['msg'] = "Login failed or user not found.";
    } else {
        $result = ldap_get_entries($connect, $search);

        $ldap_username = $result[0]['samaccountname'][0];
        $ldap_first_name = $result[0]['givenname'][0];
        $ldap_last_name = $result[0]['sn'][0];

        $_SESSION['username'] = $ldap_username;
        $_SESSION['name'] = $ldap_first_name . " " . $ldap_last_name;
        $_SESSION['loggedin'] = true;

        $return["status"] = true;

        ldap_close($connect);
    }

    return $return;
}

function getUser($name)
{
    $username = LDAP_USER;
    $password = LDAP_PASSWORD;
    $base_dn = LDAP_BASEDN;

    $connect = LDAPconnect($username, $password);
    if (is_string($connect)) {
        $return['msg'] = $connect;
        return $return;
    }

    if (!defined('OPEN_LDAP') || !OPEN_LDAP) {
        $fields = "(|(samaccountname=$name))";
    } else {
        $fields = "(|(uid=$name))";
    }

    $search = ldap_search($connect, $base_dn, $fields);
    if ($search === false) {
        return "Login fehlgeschlagen / Benutzer nicht vorhanden";
    }
    $result = ldap_get_entries($connect, $search);
    // dump(ldap_get_dn($connect, ldap_first_entry($connect, $search)));
    // dump(ldap_first_entry($connect, $search))['uid'];

    ldap_close($connect);
    return $result;
}

function getUsers()
{
    $return = [];
    $username = LDAP_USER;
    $password = LDAP_PASSWORD;
    $base_dn = LDAP_BASEDN;

    $connect = LDAPconnect($username, $password);
    if (is_string($connect)) {
        $return['msg'] = $connect;
        return $return;
    }

    $res = array();
    $cookie = '';

    do {
        $filter = '(cn=*)';
        // overwrite filter if set in CONFIG
        if (defined('LDAP_FILTER') && !empty(LDAP_FILTER)) $filter = LDAP_FILTER;
        $attributes = ['samaccountname', 'useraccountcontrol', 'accountexpires'];

        $result = @ldap_search(
            $connect,
            $base_dn,
            $filter,
            $attributes,
            0,
            0,
            0,
            LDAP_DEREF_NEVER,
            [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 1000, 'cookie' => $cookie]]]
        );

        if ($result === false) {
            $error = ldap_error($connect);
            ldap_close($connect);
            return "Fehler bei der LDAP-Suche: " . $error;
        }

        $parseResult = ldap_parse_result($connect, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
        if ($parseResult === false) {
            $error = ldap_error($connect);
            ldap_close($connect);
            return "Fehler beim Parsen des LDAP-Ergebnisses: " . $error;
        }

        $entries = ldap_get_entries($connect, $result);
        if ($entries === false) {
            $error = ldap_error($connect);
            ldap_close($connect);
            return "Fehler beim Abrufen der LDAP-Einträge: " . $error;
        }

        if (!defined('OPEN_LDAP') || !OPEN_LDAP) {
            $key_user = 'samaccountname';
        } else {
            $key_user = 'uid';
        }
        
        $key_active = 'useraccountcontrol';
        $key_expires = 'accountexpires';

        foreach ($entries as $entry) {
            if (!isset($entry[$key_user][0])) {
                continue;
            }

            $accountControl = isset($entry[$key_active][0]) ? (int)$entry[$key_active][0] : 0;
            $accountExpires = isset($entry[$key_expires][0]) ? (int)$entry[$key_expires][0] : 0;
            
            $isDisabled = ($accountControl & 2); // 2 = ACCOUNTDISABLE
            $isExpired = ($accountExpires != 0 && $accountExpires <= time() * 10000000 + 116444736000000000);

            $active = !$isDisabled && !$isExpired;

            $res[$entry[$key_user][0]] = $active;
        }

        if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
            $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
        } else {
            $cookie = '';
        }
    } while (!empty($cookie));

    ldap_close($connect);
    return $res;
}


function getGroups($v)
{
    $m = array();
    if (preg_match('/CN=([^,]+)[,$]/', $v, $matches))
        return $matches[1];
    return false;
}

function newUser($username)
{
    $Groups = new Groups();
    // get user from ldap
    $ldap_users = getUser($username);
    if (empty($ldap_users) || $ldap_users['count'] == 0) return false;
    $ldap_user = $ldap_users[0];

    $person = array();
    if (!defined('OPEN_LDAP') || !OPEN_LDAP) {
        $keys = [
            "username" => "samaccountname",
            "first" => "givenname",
            "last" => "sn",
            "displayname" => "displayname",
            "formalname" => "cn",
            "department" => "department",
            "unit" => "description",
            "telephone" => "telephonenumber",
            "mail" => "mail"
        ];
    } else {
        $keys = [
            "username" => "uid",
            "first" => "givenname",
            "last" => "sn",
            "displayname" => "displayname",
            "formalname" => "cn",
            "department" => "department",
            "unit" => "description",
            "telephone" => "telephonenumber",
            "mail" => "mail"
        ];
    }
    
    foreach ($keys as $key => $name) {
        // dump($value, true);
        $person[$key] = $ldap_user[$name][0] ?? null;
    }
    $person['academic_title'] = null;
    $person['orcid'] = null;
    $person['depts'] = [];

    $departments = [];
    foreach ($Groups->groups as $D) {
        $departments[strtolower($D['id'])] = $D['id'];
        $departments[strtolower($D['name'])] = $D['id'];
    }
    if ($person['unit'] !== null) {
        $unit = strtolower($person['unit']);
        if (array_key_exists($unit, $departments))
            $person['depts'] = [$departments[$unit]];
        else {
            $unit = trim(explode('/', $unit)[0]);
            if (array_key_exists($unit, $departments))
                $person['depts'] = [$departments[$unit]];
        }
    }

    // $person['is_active'] = !str_contains($ldap_user['dn'], 'DeaktivierteUser');
    $accountControl = isset($result[0]['useraccountcontrol'][0]) ? (int)$result[0]['useraccountcontrol'][0] : 0;
    $person['is_active'] = !($accountControl & 2); // 2 entspricht ACCOUNTDISABLE

    $person['created'] = date('Y-m-d');
    $person['roles'] = [];

    return $person;
}
