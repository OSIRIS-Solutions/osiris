<?php

/**
 * Page for language settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 3.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-translate"></i>
        <?= lang('admin.languages') ?>
    </h1>

    <span class="text-muted">
        <?= lang('admin.language_description') ?>
    </span>


    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

        <?php
        $langs = $Settings->languages();
        if (!is_array($langs) || empty($langs)) {
            $langs = ['en', 'de'];
        }

        $repository = new LanguageOverrides($osiris, BASEPATH . '/lang');
        foreach ($LANGUAGES as $lang => $language_name) {
            require_once BASEPATH . '/php/LanguageOverrides.php';

            $catalogue = $repository->catalogue($lang);
            $overrides = $repository->all($lang);
        ?>
            <div class="box padded d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="title mt-0 font-size-18">
                        <?= $language_name ?>
                    </h2>
                    <div class="custom-checkbox mb-10">
                        <input type="checkbox" id="lang-select-<?= $lang ?>" name="general[languages][]" value="<?= $lang ?>" <?= in_array($lang, $langs) ? 'checked' : '' ?>>
                        <label for="lang-select-<?= $lang ?>"><?= translate('common.active') ?></label>
                    </div>

                    <div class="text-muted">
                        <?= translate('admin.language_override_count', ['count' => count($overrides)]) ?>
                    </div>
                </div>

                <a href="<?= ROOTPATH ?>/admin/language-override/<?= $lang ?>" class="btn primary">
                    <?= lang('admin.language_override') ?>
                </a>
            </div>
        <?php } ?>


        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>

    </form>
</div>