    <?php if ($error == 404) { ?>
        <!-- <img src="<?= ROOTPATH ?>/img/404.svg" alt="404 - Page not found" class="img-fluid m-auto d-block" style="max-width:80vw; max-height: 65vh;"> -->

        <div class="h-full position-relative text-center">
            <img src="<?= ROOTPATH ?>/img/sophie/sophie-404.png" alt="404 - Page not found" style="width: 100%; max-width: 70rem; margin: 0 auto; display: block;">
            <div class="">
                <h1 style="margin-top: -3rem;">
                    <?= lang('error.page_not_found') ?>
                </h1>
                <p>
                    <?= lang('error.page_not_found_message') ?>
                </p>
                <a href="<?= ROOTPATH ?>/" class="btn cta">
                    <?= lang('navigation.go_home') ?>
                </a>
            </div>
        </div>
    <?php } elseif ($error == 405) { ?>
        <div class="h-full position-relative text-center">
            <img src="<?= ROOTPATH ?>/img/sophie/sophie-405.png" alt="405 - Method not allowed" style="width: 100%; max-width: 70rem; margin: 0 auto; display: block;">
            <div class="">
                <h1 style="margin-top: -1rem;">
                    <?= lang('error.method_not_allowed') ?>
                </h1>
                <p>
                    <?= lang('error.method_not_allowed_message', ['method' => $_SERVER['REQUEST_METHOD']]) ?>
                </p>
                <a href="<?= ROOTPATH ?>/" class="btn cta">
                    <?= lang('navigation.go_home') ?>
                </a>
            </div>
        </div>
    <?php } else { ?>
        <?= $error ?>
    <?php } ?>