<?php

/**
 * Page for admin dashboard for general settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
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
        <i class="ph-duotone ph-gear"></i>
        <?= lang('admin.general_settings') ?>
    </h1>


    <form action="<?= ROOTPATH ?>/crud/admin/general" method="post">

        <h5>
            <?= lang('admin.languages') ?>
        </h5>

        <?php
        $installed_lang = [
            'en',
            'de',
            'it',
            ];
        $langs = $Settings->languages();
        if (!is_array($langs) || empty($langs)){
            $langs = ['en', 'de'];
        }
        foreach ($installed_lang as $lang) { ?>
            <div class="custom-checkbox mb-5">
                <input type="checkbox" id="lang-select-<?= $lang ?>" name="general[languages][]" value="<?= $lang ?>" <?= in_array($lang, $langs) ? 'checked': '' ?>>
                <label for="lang-select-<?= $lang ?>"><?= lang('common.lang_'. $lang) ?></label>
            </div>
        <?php } ?>

        <span class="text-muted">
            <?= lang('admin.language_description') ?>
        </span>



        <hr>
        <h5>
            <?= lang('admin.general_charts') ?>
        </h5>


        <div class="form-group">
            <label for="name" class="required "><?= lang('admin.start_year') ?></label>
            <input type="year" class="form-control" name="general[startyear]" required value="<?= $Settings->get('startyear') ?? '2022' ?>">
            <span class="text-muted">
                <?= lang('admin.start_year_description') ?>
            </span>
        </div>

        <hr>
        <h5>
            <?= lang('admin.print_output_settings') ?>
        </h5>
        <div class="row row-eq-spacing mt-0">
            <div class="col-sm-6">
                <!-- affiliation formatting -->
                <?php
                $format = $Settings->get('affiliation_format', 'bold');
                ?>

                <label for="affiliation_format"><?= lang('admin.affiliated_authors_formatting') ?></label>
                <select class="form-control" name="general[affiliation_format]" id="affiliation_format">
                    <option value="bold" <?= $format == 'bold' ? 'selected' : '' ?>><?= lang('admin.bold_default') ?></option>
                    <option value="italic" <?= $format == 'italic' ? 'selected' : '' ?>><?= lang('admin.italic') ?></option>
                    <option value="underline" <?= $format == 'underline' ? 'selected' : '' ?>><?= lang('admin.underline') ?></option>
                    <option value="bold-italic" <?= $format == 'bold-italic' ? 'selected' : '' ?>><?= lang('admin.bold_and_italic') ?></option>
                    <option value="bold-underline" <?= $format == 'bold-underline' ? 'selected' : '' ?>><?= lang('admin.bold_and_underline') ?></option>
                    <option value="italic-underline" <?= $format == 'italic-underline' ? 'selected' : '' ?>><?= lang('admin.italic_and_underline') ?></option>
                    <option value="none" <?= $format == 'none' ? 'selected' : '' ?>><?= lang('common.none') ?></option>
                </select>
            </div>

            <!-- render language -->
            <div class="col-sm-6">
                <?php
                $renderLang = $Settings->get('render_language', 'en');
                ?>
                <label for="render_language"><?= lang('admin.render_language') ?></label>
                <select class="form-control" name="general[render_language]" id="render_language">
                    <!-- <option value="both" <?= $renderLang == 'both' ? 'selected' : '' ?>><?= lang('common.both_languages') ?></option> -->
                    <option value="en" <?= $renderLang == 'en' ? 'selected' : '' ?>><?= lang('admin.english_only') ?></option>
                    <option value="de" <?= $renderLang == 'de' ? 'selected' : '' ?>><?= lang('admin.german_only') ?></option>
                </select>
            </div>
        </div>
        <p class="mt-5">
            <b>
                <i class="ph ph-warning"></i>
                <?= lang('admin.hint') ?>
            </b>
            <?= lang('admin.chages_require_rerender') ?>
            <a href="<?= ROOTPATH ?>/rerender" class="">
                <?= lang('admin.render_all_activities') ?>.
            </a><br>
            <?= lang('admin.please_be_patient') ?>
        </p>

        <hr>
        <h5>
            <?= lang('admin.general_api') ?>
        </h5>
        <div class="form-group">
            <label for="apikey" class="d-flex justify-content-between"><?= lang('API-Key') ?> <?= badgeDeprecated() ?></label>
            <div class="input-group">
                <input type="text" class="form-control" name="general[apikey]" id="apikey" value="<?= $Settings->get('apikey') ?>">

                <div class="input-group-append">
                    <button type="button" class="btn" onclick="generateAPIkey()"><i class="ph ph-arrows-clockwise"></i> Generate</button>
                </div>
            </div>
            <span class="text-muted">
                <?= lang('admin.legacy_api_key_description', replace: ['rootpath' => ROOTPATH]) ?>
            </span>
        </div>

        <script>
            function generateAPIkey() {
                let length = 50;
                let result = '';
                const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                const charactersLength = characters.length;
                let counter = 0;
                while (counter < length) {
                    result += characters.charAt(Math.floor(Math.random() * charactersLength));
                    counter += 1;
                }
                $('#apikey').val(result)
            }
        </script>



        <button class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>

    </form>
</div>