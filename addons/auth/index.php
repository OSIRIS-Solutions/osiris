<?php

Route::get('/auth/new-user', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->get('auth-self-registration', true)) {
        header("Location: " . ROOTPATH . "/user/login");
        die;
    }
    $token = $_GET['token'] ?? null;
    $authToken = $Settings->get('auth-token');
    if (!empty($authToken) && !empty($token)) {
        // check if token is valid
        if ($token != $authToken) {
            $_SESSION['msg'] = lang('common.token_invalid');
            $_SESSION['msg_type'] = 'error';
        } else {
            $_SESSION['msg'] = lang('common.token_valid');
            $_SESSION['msg_type'] = 'success';
        }
    }

    include BASEPATH . "/header.php";
    if (!empty($authToken) && $token != $authToken) {
        include BASEPATH . "/addons/auth/auth-token.php";
    } else {
        include BASEPATH . "/addons/auth/add-user.php";
    }
    include BASEPATH . "/footer.php";
});


Route::get('/auth/forgot-password', function () {
    include_once BASEPATH . "/php/init.php";
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/profile/$_SESSION[username]");
        die;
    }
    $breadcrumb = [
        ['name' => lang('common.password_forgot')]
    ];
    include BASEPATH . "/header.php";

    include BASEPATH . "/addons/auth/forgot-password.php";
    include BASEPATH . "/footer.php";
});

Route::post('/auth/forgot-password', function () {
    include_once BASEPATH . "/php/init.php";
    include_once BASEPATH . "/php/MailSender.php";
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/profile/$_SESSION[username]");
        die;
    }

    if (isset($_POST['mail'])) {
        $user = $osiris->persons->findOne(['mail' => $_POST['mail']]);
        if (empty($user)) {
            $_SESSION['msg'] = lang('common.password_forgot_if_mail_correct');
            header("Location: " . ROOTPATH . "/user/login");
            die;
        }

        // check if user has recently requested a password reset
        $account = $osiris->accounts->findOne(['username' => $user['username']]);
        if (!empty($account) && isset($account['reset']) && $account['reset'] > time() - 10 * 60) {
            $_SESSION['msg'] = lang('common.password_forgot_please_wait');
            header("Location: " . ROOTPATH . "/auth/forgot-password");
            die;
        }

        // generate hash for password reset
        // improved hash thanks to Jonas, Felix and Anton from TU Darmstadt
        $hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $osiris->accounts->updateOne(
            ['username' => $user['username']],
            ['$set' => ['reset' => time(), 'hash' => $hash]]
        );


        $link = $_SERVER['HTTP_HOST'] . ROOTPATH . "/auth/reset-password?hash=$hash";
        // send mail
        sendMail(
            $user['mail'],
            lang('common.password_reset'),
            lang('common.password_reset_click_link') .
                "<br><a href='" . $link . "'>$link</a><br>" .
                lang('common.password_reset_if_not_requested')
        );

        $_SESSION['msg'] = lang('common.password_forgot_if_mail_correct');
        header("Location: " . ROOTPATH . "/user/login");
    }
});


Route::get('/user/password-reset/(.*)', function ($user_id) {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.password-reset')) {
        abortwith(403, lang('error.password_reset_no_permission'), '/profile/' . $user_id);
    }
    $person = $osiris->persons->findOne(['_id' => DB::to_ObjectID($user_id)]);
    $person = DB::doc2Arr($person);
    if (empty($person)) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $breadcrumb = [
        ['name' => lang('common.users'), 'path' => "/user/browse"],
        ['name' => $person['displayname'], 'path' => "/profile/$person[_id]"],
        ['name' => lang('common.password_reset')]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/addons/auth/admin-reset-password.php";
    include BASEPATH . "/footer.php";
});


Route::post('/auth/admin-reset-password', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->hasPermission('user.password-reset')) {
        abortwith(403, lang('error.password_reset_no_permission'), '/profile/' . $user_id);
    }
    if (!isset($_POST['id'])) {
        header("Location: " . ROOTPATH . "/user/browse");
        die;
    }
    $id = DB::to_ObjectID($_POST['id']);
    $hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $osiris->accounts->updateOne(
        ['username' => $osiris->persons->findOne(['_id' => $id])['username']],
        ['$set' => ['reset' => time(), 'hash' => $hash]]
    );

    $link = $_SERVER['HTTP_HOST'] . ROOTPATH . "/auth/reset-password?hash=$hash";

    // return the link to the admin to share it with the user
    include BASEPATH . "/header.php";
?>
    <div class="msg success">
        <?= lang('common.password_reset_link_share') ?>
        <br>
        <pre class="code box p-20"><?= $link ?></pre>
        <button class="btn primary">
            <span onclick="navigator.clipboard.writeText('<?= $link ?>')"><?= lang('common.copy_to_clipboard') ?></span>
        </button>
    </div>

    <p class="text-muted">
        <?= lang('common.password_reset_link_reminder') ?>
    </p>

<?php
    include BASEPATH . "/footer.php";
});


Route::get('/auth/reset-password', function () {
    include_once BASEPATH . "/php/init.php";

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/profile/$_SESSION[username]");
        die;
    }

    // check if hash is valid
    $hash = $_GET['hash'];
    $account = $osiris->accounts->findOne(['hash' => $hash]);
    if (empty($account)) {
        $_SESSION['msg'] = lang('common.password_reset_link_invalid');
        header("Location: " . ROOTPATH . "/auth/forgot-password");
        die;
    }

    // check if reset is still valid
    if ($account['reset'] < time() - 24 * 60 * 60) {
        // remove hash
        $osiris->accounts->updateOne(
            ['hash' => $hash],
            ['$unset' => ['hash' => '']]
        );
        $_SESSION['msg'] = lang('common.password_reset_link_expired');
        header("Location: " . ROOTPATH . "/auth/forgot-password");
        die;
    }

    $user = $osiris->persons->findOne(['username' => $account['username']]);
    $breadcrumb = [
        ['name' => lang('common.password_reset')]
    ];
    include BASEPATH . "/header.php";
?>
    <form action="#" method="post">
        <input type="hidden" name="hash" value="<?= $hash ?>">
        <div class="form-group">
            <label class="required" for="password"><?= lang('common.password_new') ?></label>
            <input class="form-control" type="password" id="password" name="password" required>
        </div>
        <button class="btn"><?= lang('common.password_reset') ?></button>
    </form>
<?php
    include BASEPATH . "/footer.php";
});

Route::post('/auth/reset-password', function () {
    include_once BASEPATH . "/php/init.php";

    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true  && isset($_SESSION['username']) && !empty($_SESSION['username'])) {
        header("Location: " . ROOTPATH . "/profile/$_SESSION[username]");
        die;
    }

    // check if hash and password are set
    if (!isset($_POST['hash']) || !isset($_POST['password'])) {
        $_SESSION['msg'] = lang('common.password_reset_link_invalid');
        header("Location: " . ROOTPATH . "/auth/forgot-password");
        die;
    }

    // check everything again, just to be sure
    $hash = $_POST['hash'];
    $account = $osiris->accounts->findOne(['hash' => $hash]);
    if (empty($account)) {
        $_SESSION['msg'] = lang('common.password_reset_link_invalid');
        header("Location: " . ROOTPATH . "/auth/forgot-password");
        die;
    }

    // check if reset is still valid
    if ($account['reset'] < time() - 24 * 60 * 60) {
        // remove hash
        $osiris->accounts->updateOne(
            ['hash' => $hash],
            ['$unset' => ['hash' => '']]
        );
        $_SESSION['msg'] = lang('common.password_reset_link_expired');
        header("Location: " . ROOTPATH . "/auth/forgot-password");
        die;
    }

    // reset password
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $osiris->accounts->updateOne(
        ['hash' => $hash],
        ['$set' => ['password' => $password], '$unset' => ['hash' => '']]
    );
    $_SESSION['msg'] = lang('common.password_reset_successful');
    header("Location: " . ROOTPATH . "/user/login");
    die;
});

Route::post('/auth/new-user', function () {
    include_once BASEPATH . "/php/init.php";

    if ((!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) && !$Settings->get('auth-self-registration', true)) {
        header("Location: " . ROOTPATH . "/user/login");
        die;
    }

    if ($osiris->persons->count(['username' => $_POST['username']]) > 0) {
        $msg = lang('error.username_already_taken');
        include BASEPATH . "/header.php";
        printMsg($msg, 'error');
        include BASEPATH . "/addons/auth/add-user.php";
        include BASEPATH . "/footer.php";
        die;
    }

    $person = $_POST['values'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    // move to a new collection
    $osiris->accounts->insertOne([
        'username' => $username,
        'password' => $hash
    ]);

    $person['username'] = $username;
    $person['displayname'] = "$person[first] $person[last]";
    $person['formalname'] = "$person[last], $person[first]";
    $person['first_abbr'] = "";
    foreach (explode(" ", $person['first']) as $name) {
        $person['first_abbr'] .= " " . $name[0] . ".";
    }
    $person['created'] = date('Y-m-d');
    $person['roles'] = [];
    if (boolval($person['is_scientist'] ?? false)) $person['roles'][] = 'scientist';

    $person['is_active'] = true;
    $osiris->persons->insertOne($person);

    $_SESSION['msg'] = lang('common.account_created_success');
    $_SESSION['msg_type'] = 'success';
    header("Location: " . ROOTPATH . "/user/login");
});
