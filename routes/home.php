<?php

Route::get('/home', function () {
    include_once BASEPATH . "/php/init.php";

    // get user info
    $user = $_SESSION['username'];
    $scientist = $DB->getPerson($user);

    $notifications = $DB->notifications();
    // check which features are enabled
    $hasNews = false;
    if (
        $Settings->featureEnabled('new-publications', true)
        || $Settings->featureEnabled('news', true)
        || ($Settings->featureEnabled('quarterly-reporting', true) && isset($notifications['approval']))
        || $Settings->featureEnabled('new-colleagues', true)
    ) {
        $hasNews = true;
    }

    $hasEvents = $Settings->featureEnabled('events', false);

    // include necessary classes
    include_once BASEPATH . "/php/Vocabulary.php";
    $Vocabulary = new Vocabulary();

    // if no dashboard widgets are enabled, redirect to profile
    if (!$hasNews && !$hasEvents) {
        include_once BASEPATH . "/php/Document.php";
        include_once BASEPATH . "/php/_achievements.php";

        $Format = new Document($user);

        if (empty($scientist)) {
            $_SESSION['msg'] = lang("User not found.", "Benutzer nicht gefunden.");
            $_SESSION['msg_type'] = "error";
            header("Location: " . ROOTPATH . "/user/browse");
            die;
        }
        $name = $scientist['displayname'];

        $breadcrumb = [
            ['name' => lang('Users', 'Personen'), 'path' => "/user/browse"],
            ['name' => $name]
        ];

        include BASEPATH . "/header.php";
        include BASEPATH . "/pages/profile.php";
    } else {
        $breadcrumb = [
            ['name' => lang('Home', 'Startseite')]
        ];
        include BASEPATH . "/header.php";
        include BASEPATH . "/pages/home.php";
    }
    include BASEPATH . "/footer.php";
});



Route::get('/hub', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('resource-hub')) {
        return abortwith(404, lang('Resource Hub is not enabled.', 'Ressourcen-Hub ist nicht aktiviert.'));
    }
    $breadcrumb = [
        ['name' => $Settings->resourceHubLabel()]
    ];
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/resource-hub.php";
    include BASEPATH . "/footer.php";
});
