<?php
define('IDA_PATH', BASEPATH . '/addons/ida');


Route::get('/ida/auth', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('ida')) {
        abortwith(500, lang("The IDA module is not enabled.", "Das IDA Modul ist nicht aktiviert."), "/");
    }
    include BASEPATH . "/header.php";
    include IDA_PATH . "/pages/ida-login.php";
    include BASEPATH . "/footer.php";
}, 'login');

Route::post('/ida/auth', function () {

    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('ida')) {
        abortwith(500, lang("The IDA module is not enabled.", "Das IDA Modul ist nicht aktiviert."), "/");
    }

    require_once IDA_PATH . "/php/IDA.php";
    // Borsigstr3!?
    $IDA = new IDA($_POST['email'], $_POST['password']);
    if (!$IDA->is_authorized()) {
        include BASEPATH . "/header.php";
        printMsg($IDA->msg, 'error');
        include IDA_PATH . "/pages/ida-login.php";
        include BASEPATH . "/footer.php";
        die;
    }
    redirect("/ida/dashboard");
}, 'login');



Route::get('/ida/dashboard', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('ida')) {
        abortwith(500, lang("The IDA module is not enabled.", "Das IDA Modul ist nicht aktiviert."), "/");
    }
    require_once IDA_PATH . "/php/IDA.php";

    // init IDA and check authorization status
    $IDA = new IDA();
    if ($IDA->is_authorized()) {
        $dashboard = $IDA->dashboard();
    }
    if (!$IDA->is_authorized()) {
        include BASEPATH . "/header.php";
        printMsg($IDA->msg, 'error');
        include IDA_PATH . "/pages/ida-login.php";
        include BASEPATH . "/footer.php";
        die;
    }

    include BASEPATH . "/header.php";
    include IDA_PATH . "/pages/ida-dashboard.php";
    include BASEPATH . "/footer.php";
}, 'login');



Route::post('/ida/update-institute', function () {
    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('ida')) {
        abortwith(500, lang("The IDA module is not enabled.", "Das IDA Modul ist nicht aktiviert."), "/");
    }
    if (!isset($_POST['institute'])) {
        abortwith(500, lang("No institute selected.", "Kein Institut ausgewählt."), "/ida/dashboard");
    }
    $_SESSION['ida-institute_id'] = $_POST['institute'];
    redirect('/ida/dashboard');
});


Route::get('/ida/formular/(\d+)', function ($formular_id) {

    include_once BASEPATH . "/php/init.php";
    if (!$Settings->featureEnabled('ida')) {
        abortwith(500, lang("The IDA module is not enabled.", "Das IDA Modul ist nicht aktiviert."), "/");
    }

    require_once IDA_PATH . "/php/IDA.php";

    // init IDA and check authorization status
    $IDA = new IDA();
    if (!$IDA->is_authorized()) {
        include BASEPATH . "/header.php";
        printMsg($IDA->msg, 'error');
        include IDA_PATH . "/pages/ida-login.php";
        include BASEPATH . "/footer.php";
        die;
    }

    $formular = $IDA->formular($formular_id);
    include BASEPATH . "/header.php";

    if (!empty($IDA->msg)) {
        printMsg($IDA->msg, 'error');
    }
    if (!empty($formular)) {
        include IDA_PATH . "/pages/ida-formular.php";
    }

    include BASEPATH . "/footer.php";
}, 'login');
