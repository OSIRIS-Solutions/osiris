<?php

/**
 * Page for admin dashboard for general settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 2.2.0
 * @todo This is still under implementation
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$rh = $Settings->get('resource-hub') ?? [];
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-link"></i>
        <?= lang('Resource Hub Settings', 'Ressourcen-Hub Einstellungen') ?>
    </h1>

    <?php if (!$Settings->featureEnabled('resource-hub')) { ?>
        <div class="alert signal">
            <?= lang(
                'The Resource Hub feature is not enabled. Please enable it in the "Features" section of the admin panel to use the hub.',
                'Die Ressourcen-Hub Funktion ist nicht aktiviert. Bitte aktiviere sie im Bereich "Funktionen" des Admin-Panels, um den Hub nutzen zu können.'
            ) ?>
        </div>
    <?php } ?>


    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

        <?php
        $label = $rh['label'] ?? [];
        ?>
        <div class="box padded">
            <h2 class="title">
                <?= lang('Label for Resource Hub', 'Bezeichnung für den Ressourcen-Hub') ?>
            </h2>
            <div class="row row-eq-spacing">
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="resource_hub_label" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (English) <img src="<?= ROOTPATH ?>/img/gb.svg" alt="EN" class="flag"></label>
                    <input name="general[resource-hub][label][en]" id="resource_hub_label" type="text" class="form-control" value="<?= e($label['en'] ?? 'Resource Hub') ?>">
                </div>
                <div class="col-md-6 mt-10 mt-md-0">
                    <label for="resource_hub_label_de" class="d-flex"><?= lang('Label', 'Bezeichnung') ?> (Deutsch) <img src="<?= ROOTPATH ?>/img/de.svg" alt="DE" class="flag"></label>
                    <input name="general[resource-hub][label][de]" id="resource_hub_label_de" type="text" class="form-control" value="<?= e($label['de'] ?? 'Ressourcen-Hub') ?>">
                </div>
            </div>
        </div>

        <!-- 
        TODO: add cards for ressource hub:
            each card should have an icon, a title, a quill text content, 
            and an optional link list, each also with an icon, a title, and a link.
            All need to support both English and German, so the title and content should be stored as an array with keys 'en' and 'de'.
        -->
            <div id="card-configuration" class="box padded">
            </div>

        <!-- 
        TODO: add an image map feature for the resource hub, where users can upload an image and define the position of the 
        defined cards on the image.
        -->
        <div id="image-map-configuration" class="box padded">
        </div>


        <button type="submit" class="btn success">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </form>
</div>