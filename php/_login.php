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
            if ($guest['code'] == 1) {
                // if guest account not found or password incorrect, return LDAP error message
                return $login;
            } else {
                // if guest account found but other error (e.g. expired), return guest error message
                return $guest;
            }
        }
    }
}

function loginGuest($username, $password)
{
    $DB = new DB();
    $osiris = $DB->db;
    $return = array("msg" => '', "success" => false, 'code' => 0);
    
    // find user and check password
    $USER = $osiris->guestAccounts->findOne(['username' => $username]);
    if (empty($USER)) {
        $return["msg"] = lang("Guest-Account not found or password incorrect.", "Gast-Account nicht gefunden oder Passwort falsch.");
        $return['code'] = 1;
        return $return;
    }
    if (!empty($USER['valid_until']) && $USER['valid_until'] < date('Y-m-d')) {
        $return["msg"] = lang("Guest-Account has expired.", "Gast-Account ist abgelaufen.");
        $return['code'] = 2;
        return $return;
    }
    if (empty($USER['password'])) {
        $return["msg"] = lang("Guest-Account has no password. Please contact the administrator.", "Gast-Account hat kein Passwort. Bitte kontaktieren Sie den Administrator.");
        $return['code'] = 3;
        return $return;
    }
    // check if password is correct
    if (!password_verify($password, $USER['password'])) {
        $return["msg"] = lang("Guest-Account not found or password incorrect.", "Gast-Account nicht gefunden oder Passwort falsch.");
        $return['code'] = 4;
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
