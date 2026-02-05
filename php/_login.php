<?php
/**
 * Login and User Management
 * Refactored in v1.2.1 as helper functions for LDAPInterface
 * TODO: maybe remove entirely in next version
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once BASEPATH . '/php/LDAPInterface.php';
require_once BASEPATH . '/php/Groups.php';
require_once BASEPATH . '/php/DB.php';

function login($username, $password)
{
    // try to login via LDAP first
    $LDAP = new LDAPInterface();
    $login = $LDAP->login($username, $password);
    if ($login['success']) {
        return $login;
    } else {
        // try to login via guest accounts
        $guest = loginGuest($username, $password);
        if ($guest['success']) {
            return $guest;
        } else {
            return $login; // return LDAP login failure message
        }
    }
}

function loginGuest($username, $password)
{
    $DB = new DB();
    $osiris = $DB->db;
    $return = array("msg" => '', "success" => false);
    
    // find user and check password
    $USER = $osiris->guestAccounts->findOne(['username' => $username]);
    if (empty($USER)) {
        $return["msg"] = lang("Account not found or password incorrect.", "Account nicht gefunden oder Passwort falsch.");
        return $return;
    }
    if (!empty($USER['valid_until']) && $USER['valid_until'] < date('Y-m-d')) {
        $return["msg"] = lang("Account has expired.", "Account ist abgelaufen.");
        return $return;
    }
    if (empty($USER['password'])) {
        $return["msg"] = lang("User has no password.", "Benutzer hat kein Passwort.");
        return $return;
    }
    // check if password is correct
    if (!password_verify($password, $USER['password'])) {
        $return["msg"] = lang("Login failed.", "Anmeldung fehlgeschlagen.");
        return $return;
    }
    $_SESSION['username'] = $username;
    $_SESSION['loggedin'] = true;
    $return["success"] = true;
    return $return;
};


function getUser($name)
{
    $LDAP = new LDAPInterface();
    return $LDAP->fetchUser($name);
}

function getUsers()
{
    $LDAP = new LDAPInterface();
    return $LDAP->fetchUserActivity();
}

function newUser($username)
{
    $LDAP = new LDAPInterface();
    return $LDAP->newUser($username);
}
