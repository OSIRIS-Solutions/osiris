<?php

/**
 * Footer component
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>
<?php if (!isset($no_container)) { ?>
    </div>
<?php } ?>

<footer class="page-footer">
    <div class="logo-parade pb-0" style="display: var(--footer-logo-display, none);">
        <a href="<?= ROOTPATH ?>/" class="footer-brand">
            <img src="<?= ROOTPATH ?>/img/logo.svg" alt="OSIRIS">
        </a>

        <a href="<?= $Settings->get('affiliation_details')['link'] ?? '#' ?>" class="footer-brand" target="_blank">
            <?= $Settings->printLogo("") ?>
        </a>
    </div>
    <hr style="display: var(--footer-logo-display, none);">

    <div class="link-parade pt-0">
        <div class="row">
            <div class="col">
                <h3 class="title">
                    <?= lang('navigation.news_and_help') ?>
                </h3>

                <a href="<?= ROOTPATH ?>/new-stuff" class="">
                    <?= lang('navigation.news') ?>
                </a>

                <a href="<?= ROOTPATH ?>/docs" class="">
                    <?= lang('navigation.documentation') ?>
                </a>

                <!-- accessibility -->
                <a href="<?= ROOTPATH ?>/accessibility" class="">
                    <?= lang('navigation.accessibility') ?>
                </a>

                <a href="https://github.com/OSIRIS-Solutions/osiris/issues" target="_blank" class="">
                    <?= lang('navigation.report_an_issue') ?>
                    <i class="ph ph-arrow-square-out"></i>
                </a>
            </div>
            <div class="col">
                <h3>OSIRIS <small class="text-monospace">v<?= OSIRIS_VERSION ?>-<?= OSIRIS_BUILD ?> <?= defined('OSIRIS_BETA') && OSIRIS_BETA ? '<span class="badge signal">Beta</span>' : '' ?></small></h3>
                <a href="https://osiris-app.de" target="_blank" class="">
                    <?= lang('navigation.about_osiris') ?>
                    <i class="ph ph-arrow-square-out"></i>
                </a>
                <a href="<?= ROOTPATH ?>/license"><?= lang('navigation.license') ?></a>
                <p>
                    <?= lang('navigation.footer_love', replace:[
                            'love' => new Html('<i class="ph ph-heart text-danger" title="Für Leonie"></i>'),
                            'author' => new Html('<a href="https://osiris-solutions.de" target="_blank" rel="noopener noreferrer" class="colorless">&copy; OSIRIS Solutions GmbH '. CURRENTYEAR . '</a>')
                    ]) ?>
                </p>
            </div>
            <div class="col">
                <h3>Links</h3>
                <a href="<?= ROOTPATH ?>/impress"><?= lang('navigation.impress') ?></a>
                <a href="<?= ROOTPATH ?>/privacy"><?= lang('navigation.privacy_policy') ?></a>
                <?php
                $links = $Settings->get('footer_links');
                if (!empty($links)) {
                    foreach ($links as $link) {
                        if (isset($link['url']) && isset($link['name'])) {
                            echo '<a href="' . $link['url'] . '" target="_blank" rel="noopener noreferrer">' . lang($link['name'], $link['name_de'] ?? null) . '<i class="ph ph-arrow-square-out"></i></a>';
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>

</footer>


</div>
<!-- Content wrapper end -->

</div>
<!-- Page wrapper end -->

</body>

</html>