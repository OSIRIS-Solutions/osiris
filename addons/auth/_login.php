<?php
function login($username, $password)
{
    global $osiris;
    $return = array("msg" => '', "success" => false);
    
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
