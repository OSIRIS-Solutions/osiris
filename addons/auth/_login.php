<?php
function login($username, $password)
{
    global $osiris;
    $return = array("msg" => '', "success" => false);
    
    // find user
    $USER = $osiris->accounts->findOne(['username' => $username]);

    if (empty($USER)) {
        $return["msg"] = t('auth.login_failed_please_check_your_username_and_password');
        return $return;
    }

    if (empty($USER['password'])) {
        $return["msg"] = t('auth.login_failed_please_check_your_username_and_password');
        return $return;
    }

    // check if password is correct
    if (!password_verify($password, $USER['password'])) {
        $return["msg"] = t('auth.login_failed_please_check_your_username_and_password');
        return $return;
    }

    $_SESSION['username'] = $username;
    $_SESSION['loggedin'] = true;

    $return["success"] = true;

    return $return;
};
