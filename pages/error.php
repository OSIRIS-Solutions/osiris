    <?php if ($error == 404) { ?>
        <!-- <img src="<?= ROOTPATH ?>/img/404.svg" alt="404 - Page not found" class="img-fluid m-auto d-block" style="max-width:80vw; max-height: 65vh;"> -->

        <div class="h-full position-relative text-center">
            <img src="<?= ROOTPATH ?>/img/sophie/sophie-404.png" alt="404 - Page not found" style="width: 100%; max-width: 70rem; margin: 0 auto; display: block;">
            <div class="">
                <h1 style="margin-top: -3rem;">
                    <?= lang('Page not found', 'Seite nicht gefunden') ?>
                </h1>
                <p>
                    <?= lang('The page you are looking for does not exist or has been moved.', 'Die gesuchte Seite existiert nicht oder wurde verschoben.') ?>
                </p>
                <a href="<?= ROOTPATH ?>/" class="btn cta">
                    <?= lang('Go to homepage', 'Zur Startseite') ?>
                </a>
            </div>
        </div>
    <?php } elseif ($error == 405) { ?>
        <div class="h-full position-relative text-center">
            <img src="<?= ROOTPATH ?>/img/sophie/sophie-405.png" alt="405 - Method not allowed" style="width: 100%; max-width: 70rem; margin: 0 auto; display: block;">
            <div class="">
                <h1 style="margin-top: -1rem;">
                    <?= lang('Method not allowed', 'Methode nicht erlaubt') ?>
                </h1>
                <p>
                    <?= lang('The method "' . $_SERVER['REQUEST_METHOD'] . '" is not allowed for the requested URL.', 'Die Methode "' . $_SERVER['REQUEST_METHOD'] . '" ist für die angeforderte URL nicht erlaubt.') ?>
                </p>
                <a href="<?= ROOTPATH ?>/" class="btn cta">
                    <?= lang('Go to homepage', 'Zur Startseite') ?>
                </a>
            </div>
        </div>
    <?php } else { ?>
        <?= $error ?>
    <?php } ?>