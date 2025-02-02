
<?php

class LDAPInterface
{
    private $connection;
    private $bind;
    private $openldap = false;
    private $attributes = ['cn', 'mail', 'sAMAccountName', 'givenName', 'sn',  'ou', 'description'];
    private $userkey = 'sAMAccountName';

    public function __construct()
    {
        $this->openldap = defined('OPEN_LDAP') && OPEN_LDAP;
        if ($this->openldap) {
            $this->userkey = 'uid';
            $this->attributes = ['cn', 'mail', 'uid', 'givenName', 'sn', 'ou', 'employeetype'];
        }
        $this->connect();
    }

    private function connect()
    {
        $server = LDAP_IP;
        $port = LDAP_PORT;
        $useSSL = LDAP_USE_SSL;
        $useTLS = LDAP_USE_TLS;

        $protocol = $useSSL ? "ldaps://" : "ldap://";
        $this->connection = ldap_connect($protocol . $server . ':' . $port);

        if (!$this->connection) {
            throw new Exception("Could not connect to LDAP server.");
        }

        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->connection, LDAP_OPT_REFERRALS, 0);

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
        if (str_contains(LDAP_DOMAIN, '%s')) {
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

    public function fetchUsers()
    {
        // dynamically build search filter based on primary user id (samaccountname or uid)

        $return = [];
        $username = LDAP_USER;
        $password = LDAP_PASSWORD;
        $base_dn = LDAP_BASEDN;

        $res = array();
        $cookie = '';

        do {
            $filter = '(cn=*)';
            // overwrite filter if set in CONFIG
            if (defined('LDAP_FILTER') && !empty(LDAP_FILTER)) $filter = LDAP_FILTER;
            // $attributes = ['samaccountname', 'useraccountcontrol', 'accountexpires'];

            $result = @ldap_search(
                $this->connection,
                $base_dn,
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

            $key_active = 'useraccountcontrol';
            $key_expires = 'accountexpires';

            foreach ($entries as $entry) {
                if (!isset($entry[$this->userkey][0])) {
                    continue;
                }

                $accountControl = isset($entry[$key_active][0]) ? (int)$entry[$key_active][0] : 0;
                $accountExpires = isset($entry[$key_expires][0]) ? (int)$entry[$key_expires][0] : 0;

                $isDisabled = ($accountControl & 2); // 2 = ACCOUNTDISABLE
                $isExpired = ($accountExpires != 0 && $accountExpires <= time() * 10000000 + 116444736000000000);

                $active = !$isDisabled && !$isExpired;

                $res[$entry[$this->userkey][0]] = $active;
            }

            if (isset($controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'])) {
                $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
            } else {
                $cookie = '';
            }
        } while (!empty($cookie));

        return $res;
    }


    function login($username, $password)
    {
        $return = array("msg" => '', "success" => false);

        // first bind user account
        $bind = $this->bind($username, $password);
        if (!$bind){
            return $return;
        }

        // dynamically build search filter based on primary user id (samaccountname or uid)
        $fields = "(|(" . $this->userkey . "=" . $username . "))";
        $base_dn = LDAP_BASEDN;
        $search = ldap_search($this->connection, $base_dn, $fields);
        if ($search === false) {
            $return['msg'] = "Login failed or user not found.";
        } else {
            $result = ldap_get_entries($this->connection, $search);

            $ldap_username = $result[0][$this->userkey][0];
            $ldap_first_name = $result[0]['givenname'][0];
            $ldap_last_name = $result[0]['sn'][0];

            $_SESSION['username'] = $ldap_username;
            $_SESSION['name'] = $ldap_first_name . " " . $ldap_last_name;
            $_SESSION['loggedin'] = true;

            $return["status"] = true;
        }

        return $return;
    }

    public function close()
    {
        ldap_unbind($this->connection);
    }
}
