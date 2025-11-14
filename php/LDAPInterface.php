<?php

require_once 'Groups.php';

class LDAPInterface
{
    public $attributes = [];

    private $connection;
    private $bind;
    private $openldap = false;
    private $userkey = 'samaccountname';
    private $uniqueid = 'objectguid';

    private $keys = [
        "username" => "samaccountname",
        "first" => "givenname",
        "last" => "sn",
        "displayname" => "cn",
        "unit" => "description",
        "telephone" => "telephonenumber",
        "mail" => "mail",
        "uniqueid" => "objectguid",
        // "academic_title" => "title",
        "room" => "physicaldeliveryofficename",
        "distinguishedName" => "dn",
        "userPrincipalName" => "userPrincipalName",
    ];

    public function __construct()
    {
        $this->openldap = defined('OPEN_LDAP') && OPEN_LDAP;
        if ($this->openldap) {
            $this->userkey = 'uid';
            $this->keys = [
                "username" => "uid",
                "first" => "givenname",
                "last" => "sn",
                "displayname" => "cn",
                "unit" => "department",
                "telephone" => "telephonenumber",
                "mail" => "mail",
                "uniqueid" => "entryuuid",
                "position" => "title",
                "room" => "roomnumber",
                "distinguishedName" => "dn",
                // "academic_title" => "title",
            ];
            $this->uniqueid = 'entryuuid';
        }

        $this->attributes = array_values($this->keys);
        $this->connect();
    }

    private function connect()
    {
        $server = LDAP_IP;
        $port = LDAP_PORT;
        $useSSL = defined('LDAP_USE_SSL') ? LDAP_USE_SSL : false;
        $useTLS = defined('LDAP_USE_TLS') ? LDAP_USE_TLS : false;

        $protocol = $useSSL ? "ldaps://" : "ldap://";
        $this->connection = ldap_connect($protocol . $server . ':' . $port);

        if (!$this->connection) {
            throw new Exception("Could not connect to LDAP server.");
        }

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);
        ldap_set_option($this->connection, LDAP_OPT_NETWORK_TIMEOUT, 5); // 5 Sekunden

        if ($useTLS && !$useSSL) {
            if (!ldap_start_tls($this->connection)) {
                throw new Exception("Could not start TLS connection.");
            }
        }
    }

    public function bind($username, $password)
    {
        if (!defined('LDAP_DOMAIN')) {
            throw new Exception("LDAP_DOMAIN is not defined.");
        }
        if (str_starts_with($username, 'cn=')) {
            $dn = $username;
        } else if (str_contains(LDAP_DOMAIN, '%s')) {
            $dn = sprintf(LDAP_DOMAIN, $username);
        } else {
            $dn = $username . LDAP_DOMAIN;
        }
        $this->bind = @ldap_bind($this->connection, $dn, $password);

        if (!$this->bind) {
            $error = ldap_error($this->connection);
            echo lang("Error while connecting to the LDAP server:", "Fehler bei der Verbindung mit dem LDAP-Server: ") . $error;
            return false;
        }
        return true;
    }

    public function fetchUser($username)
    {
        if (!$this->bind(LDAP_USER, LDAP_PASSWORD)) {
            echo "Internal error: cannot search LDAP.";
            return null;
        }
        // dynamically build search filter based on primary user id (samaccountname or uid)
        $userSearchFilter = "(" . $this->userkey . "=%s)";
        $searchFilter = sprintf($userSearchFilter, $username);
        $searchBase = LDAP_BASEDN;
        $searchResult = ldap_search($this->connection, $searchBase, $searchFilter, $this->attributes);

        if (!$searchResult) {
            return null;
        }

        $entries = ldap_get_entries($this->connection, $searchResult);

        if ($entries["count"] > 0) {
            return $entries[0];
        }
        return null;
    }

    public function fetchUsers($filter = '(cn=*)', array $attributes = [])
    {
        if (!$this->bind(LDAP_USER, LDAP_PASSWORD)) {
            echo "Internal error: cannot search LDAP.";
            return null;
        }
        $res = array();
        $cookie = '';

        if (!empty($attributes)){
            // add missing attributes to the attributes array
            foreach ($attributes as $key) {
                if (!in_array($key, $this->attributes)) {
                    $this->attributes[] = $key;
                }
            }
        }

        do {
            // $filter = '(cn=*)';
            // overwrite filter if set in CONFIG
            if (defined('LDAP_FILTER') && !empty(LDAP_FILTER)) $filter = LDAP_FILTER;

            $result = @ldap_search(
                $this->connection,
                LDAP_BASEDN,
                $filter,
                $this->attributes,
                0,
                0,
                0,
                LDAP_DEREF_NEVER,
                [['oid' => LDAP_CONTROL_PAGEDRESULTS, 'value' => ['size' => 1000, 'cookie' => $cookie]]]
            );

            if ($result === false) {
                $error = ldap_error($this->connection);
                return "Fehler bei der LDAP-Suche: " . $error;
            }

            $parseResult = ldap_parse_result($this->connection, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
            if ($parseResult === false) {
                $error = ldap_error($this->connection);
                return "Fehler beim Parsen des LDAP-Ergebnisses: " . $error;
            }

            $entries = ldap_get_entries($this->connection, $result);
            if ($entries === false) {
                $error = ldap_error($this->connection);
                return "Fehler beim Abrufen der LDAP-Einträge: " . $error;
            }
            foreach ($entries as $entry) {
                if (!isset($entry[$this->userkey][0])) {
                    continue;
                }
                if (!empty($attributes)) {
                    if (!in_array($this->userkey, $attributes)) {
                        // Add the user key to the attributes array
                        $attributes[] = $this->userkey;
                    }
                    $entry = array_filter($entry, function ($key) use ($attributes) {
                        // Filter out the keys that are not in the attributes array
                        return in_array($key, $attributes);
                    }, ARRAY_FILTER_USE_KEY);
                }

                $res[] = $entry;
            }

            if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
            } else {
                $cookie = '';
            }
        } while (!empty($cookie));

        // $res = array_map([$this, 'cleanLdapEntry'], array_slice($res, 0, $res['count']));
        return $res;
    }

    public function fetchUserActivity()
    {
        // dynamically build search filter based on primary user id (samaccountname or uid)

        if (!$this->bind(LDAP_USER, LDAP_PASSWORD)) {
            echo "Internal error: cannot search LDAP.";
            return null;
        }

        $entries = $this->fetchUsers();
        if (empty($entries)) {
            return null;
        }

        $key_active = 'useraccountcontrol';
        $key_expires = 'accountexpires';

        $res = array();
        foreach ($entries as $entry) {
            $accountControl = isset($entry[$key_active][0]) ? (int)$entry[$key_active][0] : 0;
            $accountExpires = isset($entry[$key_expires][0]) ? (int)$entry[$key_expires][0] : 0;

            $isDisabled = ($accountControl & 2); // 2 = ACCOUNTDISABLE
            $isExpired = ($accountExpires != 0 && $accountExpires <= time() * 10000000 + 116444736000000000);

            $active = !$isDisabled && !$isExpired;

            $username = $entry[$this->userkey][0] ?? null;
            $uniqueid = $entry[$this->uniqueid][0] ?? null;
            if ($this->uniqueid == 'objectguid') {
                $uniqueid = $this->convertObjectGUID($uniqueid);
            }
            $res[] = [
                'username' => $username,
                'uniqueid' => $uniqueid,
                'is_active' => $active
            ];
        }

        return $res;
    }


    function login($username, $password)
    {
        $return = array("msg" => '', "success" => false, 'uniqueid' => null);
        if (empty($username) || empty($password)) {
            $return['msg'] = lang("Please enter your username and password.", "Bitte geben Sie Ihren Benutzernamen und Ihr Passwort ein.");
            return $return;
        }
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
            $return['msg'] = lang("You are already logged in.", "Sie sind bereits angemeldet.");
            return $return;
        }

        // Step 1: bind mit Admin für Suche
        if (!$this->bind(LDAP_USER, LDAP_PASSWORD)) {
            $return['msg'] = "Internal error: cannot search LDAP.";
            return $return;
        }

        // Step 2: Suche nach dem Benutzer (um Daten zu holen)
        $filter = "(" . $this->userkey . "=" . ldap_escape($username, "", LDAP_ESCAPE_FILTER) . ")";
        $search = ldap_search($this->connection, LDAP_BASEDN, $filter, $this->attributes);
        if ($search === false) {
            $return['msg'] = lang("Error while searching for the user in LDAP.", "Fehler bei der Suche nach dem Benutzer in LDAP.");
            return $return;
        }

        $result = ldap_get_entries($this->connection, $search);
        if ($result === false || $result['count'] == 0) {
            $return['msg'] = lang("User not found in LDAP.", "Benutzer nicht in LDAP gefunden.");
            return $return;
        }
        $result = $result[0];
        if (empty($result[$this->userkey][0])) {
            $return['msg'] = lang("User not found in LDAP or LDAP misconfigured.", "Benutzer nicht in LDAP gefunden oder LDAP falsch konfiguriert.");
            return $return;
        }
        $ldap_username = $result[$this->userkey][0];
        $ldap_first_name = $result['givenName'][0] ?? '';
        $ldap_last_name = $result['sn'][0] ?? '';
        $ldap_uniqueid = $result[$this->uniqueid][0] ?? null;
        if ($this->uniqueid == 'objectguid') {
            $ldap_uniqueid = $this->convertObjectGUID($ldap_uniqueid);
        }
        $return['uniqueid'] = $ldap_uniqueid;

        $bindIdentifier = null;
        $bindMethod = defined('LDAP_BIND_METHOD') ? LDAP_BIND_METHOD : 'username';
        switch ($bindMethod) {
            case 'username':
                $bindIdentifier = $username;
                break;

            case 'dn':
                $bindIdentifier = $result['dn'] ?? null; 
                break;

            case 'userPrincipalName':
                $bindIdentifier = $result['userPrincipalName'][0] ?? null;
                break;

            default:
                $return['msg'] = "Internal error: invalid LDAP_BIND_METHOD.";
                return $return;
        }

        // Step 3: User-Bind (zum Prüfen der Credentials)
        if (empty($bindIdentifier) || !$this->bind($bindIdentifier, $password)) {
            $return['msg'] = lang("Login failed. Please check your username and password.", "Anmeldung fehlgeschlagen. Bitte überprüfen Sie Ihren Benutzernamen und Ihr Passwort.");
            return $return;
        }

        $this->close();

        $_SESSION['username'] = $ldap_username;
        $_SESSION['name'] = $ldap_first_name . " " . $ldap_last_name;
        $_SESSION['loggedin'] = true;

        $return["success"] = true;

        return $return;
    }

    public function newUser($username)
    {
        $Groups = new Groups();

        $user = $this->fetchUser($username);
        if (empty($user) || $user['count'] == 0) {
            return null;
        }

        $units = [];
        $person = [];

        foreach ($this->keys as $key => $name) {
            // dump($value, true);
            if ($key == 'unit' && isset($user[$name])) {
                $units = $user[$name];
            } else {
                $person[$key] = $user[$name][0] ?? null;
            }
        }

        $person['units'] = [];
        if (!empty($units)) {
            unset($units['count']);
            foreach ($units as $unit) {
                $unit = $Groups->findGroup($unit);
                if (!empty($unit) && !empty($unit['id'])) {
                    $person['units'][] = [
                        'id' => uniqid(),
                        'unit' => $unit['id'],
                        'start' => null,
                        'end' => null,
                        'scientific' => true
                    ];
                }
            }
        }

        $person['orcid'] = null;
        $person['formalname'] = $person['last'] . ', ' . $person['first'];
        $person['academic_title'] = null;
        $accountControl = isset($user['useraccountcontrol'][0]) ? (int)$user['useraccountcontrol'][0] : 0;
        $person['is_active'] = boolval(!($accountControl & 2)); // 2 entspricht ACCOUNTDISABLE

        $person['created'] = date('Y-m-d');
        $person['roles'] = [];

        $person['uniqueid'] = $user[$this->uniqueid][0] ?? null;
        if ($this->uniqueid == 'objectguid') {
            $person['uniqueid'] = $this->convertObjectGUID($person['uniqueid']);
        }

        return $person;
    }

    public function synchronizeAttributes(array $ldapMappings, $osiris, $verbose = false)
    {
        $Groups = new Groups();
        $users = $this->fetchUsers('(cn=*)', array_values($ldapMappings));

        if (!is_array($users)) {
            echo "Fehler beim Abrufen der Benutzer aus LDAP.";
            return false;
        }

        foreach ($users as $entry) {
            $username = $entry[$this->userkey][0] ?? null;
            if (!$username) continue;

            // check if user already exists
            $user = $osiris->persons->findOne(['username' => $username], ['projection' => ['_id' => 1]]);
            if (empty($user)) {
                // check if user already exists by uniqueid
                $uniqueid = $entry[$this->uniqueid][0] ?? null;
                if ($this->uniqueid == 'objectguid') {
                    $uniqueid = $this->convertObjectGUID($uniqueid);
                }
                if (!empty($uniqueid)) {
                    $user = $osiris->persons->findOne(['uniqueid' => $uniqueid], ['projection' => ['_id' => 1, 'username' => 1]]);
                }
                if (!empty($user)) {
                    // user exists, but username is different
                    $username = $user['username'];
                }
            }
            if (empty($user)) {
                // if ($verbose) {
                    echo "Skipping user: " . $username . "<br>";
                // }
                continue;
            }

            echo $username .' ';

            $userData = [];

            foreach ($ldapMappings as $osirisKey => $ldapKey) {
                if (empty($ldapKey)) {
                    $userData[$osirisKey] = null;
                } else {
                    $userData[$osirisKey] = $entry[$ldapKey][0] ?? null;
                }
            }

            // $userData['username'] = $username;
            $userData['uniqueid'] = $entry[$this->uniqueid][0] ?? null;
            $userData['updated'] = date('Y-m-d');

            // update units
            if (isset($ldapMappings['department']) && !empty($ldapMappings['department'] ?? null)) {
                $updatedUnits = [];
                // first get the current units
                $currentUnits = [];
                $currentUser = $osiris->persons->findOne(['username' => $username], ['projection' => ['units' => 1]]);
                if (!empty($currentUser)) {
                    foreach ($currentUser['units'] as $unit) {
                        $group = $Groups->getGroup($unit['unit']);
                        if (!empty($group)) {
                            $u = [
                                'id' => $unit['id'],
                                'unit' => $group['id'],
                                'start' => $unit['start'],
                                'end' => $unit['end'] ?? null,
                                'scientific' => $unit['scientific'] ?? null,
                            ];
                            $currentUnits[] = $u;
                            $updatedUnits[$group['id']] = $u;
                        }
                    }
                }

                // then get the new units
                $ldapUnits = $entry[$ldapMappings['department']];
                $newUnits = [];
                if (!empty($ldapUnits)) {
                    unset($ldapUnits['count']);
                    foreach ($ldapUnits as $unit) {
                        $unit = $Groups->findGroup($unit);
                        if (!empty($unit) && !empty($unit['id'])) {
                            $newUnits[] = [
                                'id' => uniqid(),
                                'unit' => $unit['id'],
                                'start' => $unit['start'] ?? null,
                                'end' => $unit['end'] ?? null,
                                'scientific' => $unit['scientific'] ?? null,
                            ];
                        }
                    }
                }

                /**
                 * if a new unit is found: add new unit with todays time stamp as start
                 * if unit is not found anymore: end unit with todays time stamp
                 * if everything is the same: do not touch it
                 */

                $new = array_column($newUnits, 'unit');
                $old = array_column($currentUnits, 'unit');

                // 1. check if something changed at all
                if (array_diff($new, $old) || array_diff($old, $new)) {
                    // 2. check if a new unit is found
                    if (!empty($new)) {
                        // check if the unit is already in the current units
                        foreach ($new as $newUnit) {
                            if (!in_array($newUnit, $old)) {
                                // unit is new
                                $updatedUnits[$newUnit] = [
                                    'id' => uniqid(),
                                    'unit' => $newUnit,
                                    'start' => date('Y-m-d'),
                                    'end' => null,
                                    'scientific' => true
                                ];
                            }
                        }
                    }
                    // 3. check if a unit is not found anymore
                    if (!empty($old)) {
                        // check if the unit is not in the new units
                        foreach ($old as $oldUnit) {
                            if (!in_array($oldUnit, $new)) {
                                // unit is not found anymore
                                if (isset($updatedUnits[$oldUnit])) {
                                    $updatedUnits[$oldUnit]['end'] = date('Y-m-d');
                                }
                            }
                        }
                    }

                    if (!empty($updatedUnits)) {
                        $userData['units'] = array_values($updatedUnits);
                        // var_dump($userData['units']);
                    }
                }
            }

            if (isset($ldapMappings['is_active'])) {
                $accountControl = isset($entry[$ldapMappings['is_active']][0]) ? (int)$entry[$ldapMappings['is_active']][0] : 0;
                $userData['is_active'] = boolval(!($accountControl & 2)); // 2 entspricht ACCOUNTDISABLE
                if ($userData['is_active']){
                    echo " (active)";
                } else {
                    echo " (inactive)";
                }
            } else {
                $userData['is_active'] = true;
            }

            // Update in der Datenbank speichern
            // Beispiel mit MongoDB:
            $osiris->persons->updateOne(
                ['username' => $username],
                ['$set' => $userData],
                // ['upsert' => true]
            );
            echo "<br>";
        }

        return true;
    }

    public function convertObjectGUID($bin)
    {
        if (empty($bin)) {
            return null;
        }
        if (strlen($bin) != 16) {
            return $bin;
        }
        // Convert binary GUID to hex string
        $hex = bin2hex($bin);
        return vsprintf('%s%s%s%s-%s%s-%s%s-%s%s-%s%s%s%s%s%s', str_split($hex, 2));
    }

    public function close()
    {
        ldap_unbind($this->connection);
    }
}
