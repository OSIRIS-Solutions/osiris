<?php

/**
 * Routing file for the database migration
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


Route::get('/aggregate', function () {
    include_once BASEPATH . "/php/init.php";
    include BASEPATH . "/header.php";
    include BASEPATH . "/pages/aggregate.php";
    include BASEPATH . "/footer.php";
});
