<?php

/**
 * Admin page for managing countries
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/countries
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<div class="container w-800 mw-full">

    <a href="<?= ROOTPATH ?>/migrate/countries" class="btn primary">
        <?= lang('Update countries list', 'Länderliste aktualisieren') ?>
    </a>
    <br>
    <br>

    <!-- Show countries list -->
    <h1>
        <i class="ph-duotone ph-globe-hemisphere-west"></i>
        <?= lang('Countries', 'Länder') ?>
    </h1>
    <p>
        <?= lang('Here you can see the list of countries that are used in OSIRIS.', 'Hier kannst du die Liste der Länder sehen, die in OSIRIS verwendet werden.') ?>
    </p>

    <ul class="list">
        <?php foreach ($osiris->countries->find() as $c) { ?>
            <li><?= lang($c['name'], $c['name_de']) ?> (<?= $c['iso'] ?>)</li>
        <?php } ?>
    </ul>

    <p class="text-signal">
        <i class="ph ph-info"></i>
        <?= lang('The list of world countries is provided by', 'Die Liste der Weltländer wird zur Verfügung gestellt von') ?>
        <a href="https://stefangabos.github.io/world_countries/" target="_blank" rel="noopener noreferrer" class="colorless">Stefan Gabos' World Country List</a>.
        <?= lang('Please click on the button above to update automatically.', 'Bitte click auf den Knopf oben, um die Liste automatisch zu aktualisieren.') ?>
    </p>

</div>