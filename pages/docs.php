<?php

/**
 * Page to see the documentation
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /docs
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <?= lang('Documentation', 'Dokumentation') ?>
</h1>

<!-- wiki hint -->
<div class="alert">
    <?= lang('For detailed information on how to use OSIRIS, including administration and configuration, please visit our', 'Für detaillierte Informationen zur Nutzung von OSIRIS, einschließlich Administration und Konfiguration, besuche bitte unser') ?>
    <a href="https://wiki.osiris-app.de/" target="_blank">
        <i class="ph ph-book-open mr-5"></i>
        <?= lang('Wiki', 'Wiki') ?>
    </a>.
    <?=lang('Some useful links:', 'Einige nützliche Links:')?>
</div>

<div class="link-list" style="max-width:50rem">

    <a href="https://wiki.osiris-app.de/users/content/create_content/" target="_blank">
        <i class="ph mr-10 text-secondary ph-book-open"></i>
        <?= lang('Add activities', 'Aktivitäten hinzufügen') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/profile/scientist_view/" target="_blank">
        <i class="ph mr-10 text-secondary ph-calendar"></i>
        <?= lang('My year', 'Mein Jahr') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/activities/advanced-search/" target="_blank">
        <i class="ph mr-10 text-secondary ph-magnifying-glass-plus"></i>
        <?= lang('Advanced search', 'Erweiterte Suche') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/issues/" target="_blank">
        <i class="ph mr-10 text-secondary ph-warning"></i>
        <?= lang('Warnings', 'Warnungen') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/profile/start/" target="_blank">
        <i class="ph mr-10 text-secondary ph-user-list"></i>
        <?= lang('Profile editing', 'Profilbearbeitung') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/faq">
        <i class="ph mr-10 text-secondary ph-chat-dots"></i>
        FAQ
    </a>

    <a href="<?= ROOTPATH ?>/docs/api">
        <i class="ph mr-10 text-secondary ph-code"></i>
        <?= lang('API Docs') ?>
    </a>


    <a href="<?= ROOTPATH ?>/docs/portfolio">
        <i class="ph mr-10 text-secondary ph-globe"></i>
        <?= lang('Portfolio FAQ') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/portfolio-api">
        <i class="ph mr-10 text-secondary ph-code"></i>
        <?= lang('Portfolio API Docs') ?>
    </a>
</div>

<p>
    <?= lang('For more information, please refer to the', 'Für weitere Informationen siehe das') ?> <a href="https://wiki.osiris-app.de/" target="_blank"><?= lang('Wiki', 'Wiki') ?></a>.
</p>