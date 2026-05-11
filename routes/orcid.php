<?php

    Route::get('/orcid/validate(.*)', function () {
        include_once BASEPATH . "/php/init.php";
        include BASEPATH . "/header.php";
        include BASEPATH . "/pages/orcid/validate.php";
        include BASEPATH . "/footer.php";
    });

    Route::get('/orcid/import', function () {
        include_once BASEPATH . "/php/init.php";
        include BASEPATH . "/header.php";
        include BASEPATH . "/pages/orcid/import.php";
        include BASEPATH . "/footer.php";
    });


?>