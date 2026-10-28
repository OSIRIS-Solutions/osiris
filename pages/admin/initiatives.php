<?php

/**
 * Page for admin settings for initiatives
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/initiatives
 *
 * @package OSIRIS
 * @since 1.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-lightbulb"></i>
        <?= lang('Initiatives', 'Initiativen') ?>
    </h1>


    <?php if (!$Settings->featureEnabled('initiatives')) { ?>
        <div class="alert signal">
            <?= lang(
                'The Initiatives feature is not enabled. Please enable it in the <a href="' . ROOTPATH . '/admin/features#initiatives">Features</a> section of the admin panel to use initiatives.',
                'Die Initiativen-Funktion ist nicht aktiviert. Bitte aktiviere sie im Bereich <a href="' . ROOTPATH . '/admin/features#initiatives">"Funktionen"</a> des Admin-Panels, um Initiativen nutzen zu können.'
            ) ?>
        </div>
    <?php } ?>


    <form action="<?= ROOTPATH ?>/crud/admin/initiatives" method="post">


        <div class="form-group">
            <a class="btn secondary" href="<?= ROOTPATH ?>/admin/initiatives/fields">
                <?= lang('Manage Data Fields', 'Datenfelder verwalten') ?>
            </a>
        </div>

<!-- 
        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button> -->

    </form>
</div>