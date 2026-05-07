<?php

    Route::get('/orcid(.*)', function () {
        include_once BASEPATH . "/php/init.php";
        include BASEPATH . "/header.php";
        include BASEPATH . "/pages/orcid.php";
        include BASEPATH . "/footer.php";
    });

    Route::post('/orcid', function () {
        include_once BASEPATH . "/php/init.php";
        include BASEPATH . "/header.php";
        include BASEPATH . "/pages/orcid.php";
        include BASEPATH . "/footer.php";
    });


?>