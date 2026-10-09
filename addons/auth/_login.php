<?php
function login($username, $password)
{
    global $osiris;
    $return = array("msg" => '', "success" => false);
    
    require_once BASEPATH . '/php/OAuth.php';
    if (osirisOAuthLinked($osiris->persons->findOne(['username' => $username]))) {
        $return['msg'] = lang('Please use single sign-on for this account.', 'Bitte nutze Single Sign-On für dieses Konto.');
        return $return;
    }

    // find user
    $USER = $osiris->accounts->findOne(['username' => $username]);

    if (empty($USER)) {
        $return["msg"] = "User not found.";
        return $return;
    }

    if (empty($USER['password'])) {
        $return["msg"] = "User has no password.";
        return $return;
    }

    // check if password is correct
    if (!password_verify($password, $USER['password'])) {
        $return["msg"] = "Login failed.";
        return $return;
    }

    $_SESSION['username'] = $username;
    $_SESSION['loggedin'] = true;

    $return["success"] = true;

    return $return;
};
