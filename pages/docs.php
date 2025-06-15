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

<?php if (lang('en', 'de') == 'de') { ?>
    <div class="alert signal">
Um die Lesbarkeit zu erhöhen, wird in der Dokumentation das generische Maskulin verwendet. Es sind generell immer alle Geschlechter angesprochen.
</div>
<?php } ?>


<div class="link-list" style="max-width:50rem">

    <a href="<?= ROOTPATH ?>/docs/add-activities">
    <i class="ph mr-10 text-secondary ph-book-open"></i>
        <?= lang('Add activities', 'Aktivitäten hinzufügen') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/my-year">
    <i class="ph mr-10 text-secondary ph-calendar"></i>
        <?= lang('My year', 'Mein Jahr') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/search">
    <i class="ph mr-10 text-secondary ph-magnifying-glass-plus"></i>
        <?= lang('Advanced search', 'Erweiterte Suche') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/warnings">
    <i class="ph mr-10 text-secondary ph-warning"></i>
        <?= lang('Warnings', 'Warnungen') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/profile">
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
    <?= lang('The following docs are currently under construction:', 'Die folgenden Docs sind zurzeit in Arbeit:') ?>
</p>

<ul class="list">
    <li><?= lang('Advanced search', 'Erweiterte Suche') ?></li>
    <li><?= lang('Download functions', 'Download-Funktionen') ?></li>
    <li><?= lang('Visualisations', 'Visualisierungen') ?></li>
    <li><?= lang('Improvement of FAQ', 'Erweiterung des FAQ') ?></li>
    <li><?= lang('Translation in english! Sorry...', 'Übersetzungen ins Englische') ?></li>
</ul>