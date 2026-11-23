<?php

/**
 * Page to see the documentation
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /docs
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph-duotone ph-book-open"></i>
    <?= t('common.documentation') ?>
</h1>

<!-- wiki hint -->
<div class="alert">
    <?= t('common.for_detailed_information_on_how_to_use_osiris_including_administration_and') ?>
    <a href="https://wiki.osiris-app.de/" target="_blank">
        <i class="ph ph-book-open mr-5"></i>
        <?= t('common.wiki') ?>
    </a>.
    <?=t('common.some_useful_links')?>
</div>

<div class="link-list" style="max-width:50rem">

    <a href="https://wiki.osiris-app.de/users/content/create_content/" target="_blank">
        <i class="ph mr-10 text-secondary ph-book-open"></i>
        <?= t('common.add_activities') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/profile/scientist_view/" target="_blank">
        <i class="ph mr-10 text-secondary ph-calendar"></i>
        <?= t('common.my_year_docs') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/advanced-search/" target="_blank">
        <i class="ph mr-10 text-secondary ph-magnifying-glass-plus"></i>
        <?= t('navigation.advanced_search') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/issues/" target="_blank">
        <i class="ph mr-10 text-secondary ph-warning"></i>
        <?= t('common.warnings') ?>
    </a>

    <a href="https://wiki.osiris-app.de/users/profile/start/" target="_blank">
        <i class="ph mr-10 text-secondary ph-user-list"></i>
        <?= t('common.profile_editing') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/faq">
        <i class="ph mr-10 text-secondary ph-chat-dots"></i>
        FAQ
    </a>

    <a href="<?= ROOTPATH ?>/docs/api">
        <i class="ph mr-10 text-secondary ph-code"></i>
        <?= t('common.api_docs') ?>
    </a>


    <a href="<?= ROOTPATH ?>/docs/portfolio">
        <i class="ph mr-10 text-secondary ph-globe"></i>
        <?= t('common.portfolio_faq') ?>
    </a>

    <a href="<?= ROOTPATH ?>/docs/portfolio-api">
        <i class="ph mr-10 text-secondary ph-code"></i>
        <?= t('common.portfolio_api_docs') ?>
    </a>
</div>

<p>
    <?= t('common.for_more_information_please_refer_to_the') ?> <a href="https://wiki.osiris-app.de/" target="_blank"><?= t('common.wiki') ?></a>.
</p>