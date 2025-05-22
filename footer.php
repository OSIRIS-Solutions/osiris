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
                    News & Help
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
                <h3>OSIRIS v<?= OSIRIS_VERSION ?></h3>
                <a href="<?= ROOTPATH ?>/about" class="">
                    <?= lang('About OSIRIS', 'Über OSIRIS') ?>
                </a>
                <a href="https://osiris-app.de" target="_blank" class="">
                    osiris-app.de
                    <i class="ph ph-arrow-square-out"></i>
                </a>
                <p>
                    <?= lang('OSIRIS is developed with', 'OSIRIS wird mit') ?> <i class="ph ph-heart" title="Für Leonie"></i> in Helmstedt<?= lang('.', ' entwickelt.') ?>
                </p>
            </div>
            <div class="col">
                <h3>Imprint</h3>

                <a href="<?= ROOTPATH ?>/impress"><?= lang('Impress', 'Impressum') ?></a>
                <a href="<?= ROOTPATH ?>/license"><?= lang('License', 'Lizenz') ?></a>
                <p>
                    <a href="https://osiris-solutions.de" target="_blank" rel="noopener noreferrer" class="colorless">&copy; OSIRIS Solutions GmbH <?=CURRENTYEAR?></a> 
                </p>
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