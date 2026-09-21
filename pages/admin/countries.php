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
        <?= lang('admin.update_countries_list') ?>
    </a>
    <br>
    <br>

    <!-- Show countries list -->
    <h1>
        <i class="ph-duotone ph-globe-hemisphere-west"></i>
        <?= lang('common.countries') ?>
    </h1>
    <p>
        <?= lang('admin.here_you_can_see_the_list_of_countries_that_are_used_in_osiris') ?>
    </p>

    <ul class="list">
        <?php foreach ($osiris->countries->find() as $c) { ?>
            <li><?= lang($c['name'], $c['name_de']) ?> (<?= $c['iso'] ?>)</li>
        <?php } ?>
    </ul>

    <p class="text-signal">
        <i class="ph ph-info"></i>
        <?= lang('admin.the_list_of_world_countries_is_provided_by') ?>
        <a href="https://stefangabos.github.io/world_countries/" target="_blank" rel="noopener noreferrer" class="colorless">Stefan Gabos' World Country List</a>.
        <?= lang('admin.please_click_on_the_button_above_to_update_automatically') ?>
    </p>

</div>