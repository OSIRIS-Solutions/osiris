<?php
function login($username, $password)
{
    global $osiris;
    $return = array("msg" => '', "success" => false);
    
    // find user
    $USER = $osiris->accounts->findOne(['username' => $username]);

    if (empty($USER)) {
        $return["msg"] = lang('Login failed. Please check your username and password.', 'Anmeldung fehlgeschlagen. Bitte prüfe deinen Nutzernamen und dein Passwort.');
        return $return;
    }

    if (empty($USER['password'])) {
        $return["msg"] = lang('Login failed. Please check your username and password.', 'Anmeldung fehlgeschlagen. Bitte prüfe deinen Nutzernamen und dein Passwort.');
        return $return;
    }

    // check if password is correct
    if (!password_verify($password, $USER['password'])) {
        $return["msg"] = lang('Login failed. Please check your username and password.', 'Anmeldung fehlgeschlagen. Bitte prüfe deinen Nutzernamen und dein Passwort.');
        return $return;
    }

    $_SESSION['username'] = $username;
    $_SESSION['loggedin'] = true;

    $return["success"] = true;

    return $return;
};
