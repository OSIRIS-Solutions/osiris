<?php
/**
 * Routing file for orcid
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       2.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julius Witte <julius.witte@osiris-solutions.de>
 * @license     MIT
 */

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

    Route::post('/orcid/import', function () {
        include_once BASEPATH . "/php/init.php";
        include BASEPATH . "/pages/orcid/import.php";
    });


?>