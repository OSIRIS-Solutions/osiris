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

function login($username, $password)
{
    $LDAP = new LDAPInterface();
    return $LDAP->login($username, $password);
}

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
