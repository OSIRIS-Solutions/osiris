<?php

/**
 * Footer component
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

</div>

<footer class="page-footer">

    <div class="link-parade">
        <div class="row">
            <div class="col">
                <h3 class="title">
                    <?= lang('News & Help', 'Neuigkeiten & Hilfe') ?>
                </h3>

                <a href="<?= ROOTPATH ?>/new-stuff" class="">
                    <?= lang('News', 'Neuigkeiten') ?>
                </a>

                <a href="<?= ROOTPATH ?>/docs" class="">
                    <?= lang('Documentation', 'Dokumentation') ?>
                </a>

                <a href="https://github.com/OSIRIS-Solutions/osiris/issues" target="_blank" class="">
                    <?= lang('Report an issue', "Problem melden") ?>
                    <i class="ph ph-arrow-square-out"></i>
                </a>
            </div>
            <div class="col">
                <h3>OSIRIS <small class="text-monospace">v<?= OSIRIS_VERSION ?>-<?=OSIRIS_BUILD?></small></h3>
                <a href="https://osiris-app.de" target="_blank" class="">
                    <?= lang('About OSIRIS', 'Über OSIRIS') ?>
                    <i class="ph ph-arrow-square-out"></i>
                </a>
                <a href="<?= ROOTPATH ?>/license"><?= lang('License', 'Lizenz') ?></a>
                <p>
                    <?= lang('Developed with', 'Mit') ?> <i class="ph ph-heart text-danger" title="Für Leonie"></i> <?= lang('by', 'entwickelt durch') ?>
                    <a href="https://osiris-solutions.de" target="_blank" rel="noopener noreferrer" class="colorless">&copy; OSIRIS Solutions GmbH <?= CURRENTYEAR ?></a>
                </p>
            </div>
            <div class="col">
                <h3>Links</h3>
                <a href="<?= ROOTPATH ?>/impress"><?= lang('Impress', 'Impressum') ?></a>
                <a href="<?= ROOTPATH ?>/privacy"><?= lang('Privacy Policy', 'Datenschutz') ?></a>
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