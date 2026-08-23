<?php

/**
 * Resource Hub page
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /hub
 *
 * @package     OSIRIS
 * @since       1.2.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$rh = $Settings->get('resource-hub') ?? [];
?>

<h1>
    <i class="ph-duotone ph-link"></i>
    <?= $Settings->resourceHubLabel() ?>
</h1>

<?php if (empty($rh)) { ?>
    <div class="alert signal">
        <div class="title">
            <?= lang(
                'The Resource Hub is not configured.',
                'Der Ressourcen-Hub ist nicht konfiguriert.'
            ) ?>
        </div>
        <?php if ($Settings->hasPermission('admin.see')) { ?>
            <?= lang(
                'Please configure it in the "Resource Hub" section of the admin panel.',
                'Bitte konfiguriere ihn im Bereich "Ressourcen-Hub" des Admin-Panels.'
            ) ?>
        <?php } else { ?>
            <?= lang(
                'Please contact your administrator to configure the Resource Hub.',
                'Bitte kontaktiere deinen Administrator, um den Ressourcen-Hub zu konfigurieren.'
            ) ?>
        <?php } ?>
    </div>
<?php return;
} ?>

<!-- TODO: switch between cards and image-map -->


<div id="card-view" class="row row-eq-spacing">
    <!-- TODO: add cards according to configuration -->
</div>


<div id="img-map">
    <!-- TODO: implement image map view -->
</div>