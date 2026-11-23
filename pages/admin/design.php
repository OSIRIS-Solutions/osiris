<?php

/**
 * Admin page for managing design settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /admin/design
 *
 * @package     OSIRIS
 * @since       1.8.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$colors = $Settings->get('colors');
$design = $Settings->get('design');
?>

<style>
    #design-table td {
        vertical-align: top;
    }

    #design-table td:first-of-type {
        padding-left: 2rem;
    }

    #design-table td label {
        font-weight: bold;
        display: block;
    }

    #design-table th {
        font-size: 1.6rem;
        background-color: var(--secondary-color-20);
        padding-left: 2rem;
        /* padding-top: 2rem; */
    }
</style>
<form action="<?= ROOTPATH ?>/crud/admin/general" method="post" id="design-form">


    <div class="container w-800 mw-full">

        <h1>
            <i class="ph-duotone ph-palette"></i>
            <?= t('admin.design_settings') ?>
        </h1>

        <table class="table" id="design-table">
            <tr>
                <th colspan="2" class="border-top">
                    <?= t('admin.colors') ?>
                </th>
            </tr>
            <tr>
                <td class="w-200">
                    <label for="color"><?= t('admin.primary_color') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[colors][primary]" value="<?= $colors['primary'] ?? '#008083' ?>" id="primary-color">
                        <button type="button" class="btn ml-10" onclick="$('#primary-color').val('#008083')" data-toggle="tooltip" data-title="<?= t('admin.reset_to_default') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <?= t('admin.the_primary_color_is_used_for_the_main_elements_of_the_website') ?>
                    </small>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="color"><?= t('admin.secondary_color') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[colors][secondary]" value="<?= $colors['secondary'] ?? '#f78104' ?>" id="secondary-color">
                        <button type="button" class="btn ml-10" onclick="$('#secondary-color').val('#f78104')" data-toggle="tooltip" data-title="<?= t('admin.reset_to_default') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <?= t('admin.the_secondary_color_is_used_for_highlighted_elements_of_the_website') ?>
                        <br>
                        <i class="ph ph-warning"></i>
                        <?= t('admin.if_your_institution_has_no_secondary_color_please_set_the_secondary_color_t') ?>
                    </small>
                </td>
            </tr>


            <tr>
                <td>
                    <label for="color"><?= t('admin.link_color') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[colors][link]" value="<?= $colors['link'] ?? '#0e7b96' ?>" id="link-color">
                        <button type="button" class="btn ml-10" onclick="$('#link-color').val('#0e7b96')" data-toggle="tooltip" data-title="<?= t('admin.reset_to_default') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2">
                    <?= t('admin.typography') ?>
                </th>
            </tr>
            <?php
            $fontPreset = $design['font_preset'] ?? 'rubik';
            $fontFamily = $design['font_family'] ?? '';
            $fontCssUrl = $design['font_css_url'] ?? '';
            ?>
            <!-- font preset -->
            <tr>
                <td>
                    <label for="design_font_preset"><?= t('admin.font') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][font_preset]" id="design_font_preset">
                        <option value="rubik" <?= $fontPreset == 'rubik' ? 'selected' : '' ?>>
                            Rubik (<?= t('admin.default') ?>)
                        </option>
                        <option value="tiktok" <?= $fontPreset == 'tiktok' ? 'selected' : '' ?>>
                            TikTok Sans
                        </option>
                        <option value="system" <?= $fontPreset == 'system' ? 'selected' : '' ?>>
                            <?= t('admin.system') ?>
                        </option>
                        <option value="custom" <?= $fontPreset == 'custom' ? 'selected' : '' ?>>
                            <?= t('admin.custom') ?>
                        </option>
                    </select>
                    <small class="text-muted d-block mt-5">
                        <?= t('admin.tip_prefer_variable_fonts_a_single_css_url_that_includes_italic_weights') ?>
                    </small>
                </td>
            </tr>

            <!-- custom font details -->
            <tr class="design-font-custom-row">
                <td>
                    <label for="design_font_family"><?= t('admin.font_family_name') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input
                            type="text"
                            class="form-control"
                            name="general[design][font_family]"
                            id="design_font_family"
                            value="<?= e($fontFamily) ?>"
                            placeholder="<?= t('admin.e_g_rubik') ?>">
                    </div>
                    <small class="text-muted d-block mt-5">
                        <?= t('admin.must_match_the_font_name_used_in_the_css_e_g_font_family_rubik') ?>
                    </small>
                </td>
            </tr>

            <tr class="design-font-custom-row">
                <td>
                    <label for="design_font_css_url"><?= t('admin.font_css_url') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input
                            type="url"
                            class="form-control"
                            name="general[design][font_css_url]"
                            id="design_font_css_url"
                            value="<?= e($fontCssUrl) ?>"
                            placeholder="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,400;0,600;1,400;1,600&display=swap">
                    </div>
                    <small class="text-muted d-block mt-5">
                        <?= t('admin.this_will_be_inserted_as_a_lt_link_rel_stylesheet_gt_in_the_page_header') ?>
                    </small>
                </td>
            </tr>
            <tr class="design-font-custom-row">
                <td>
                    <label for="design_font_headers"><?= t('admin.use_for_headers_as_well') ?></label>
                </td>
                <td>
                    <?php $fontHeaders = $design['font_headers'] ?? 'no'; ?>
                    <select class="form-control" name="general[design][font_headers]" id="design_font_headers">
                        <option value="no" <?= $fontHeaders == 'no' ? 'selected' : '' ?>><?= t('admin.no_default') ?></option>
                        <option value="yes" <?= $fontHeaders == 'yes' ? 'selected' : '' ?>><?= t('common.yes') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= t('admin.default_font_for_headers_is_tiktok_sans') ?>
                    </small>
                </td>
            </tr>
            <?php
            // existing
            $fontPreset = $design['font_preset'] ?? 'rubik';
            $fontFamily = $design['font_family'] ?? '';
            $fontCssUrl = $design['font_css_url'] ?? '';

            // new header font settings
            $headerFontPreset = $design['header_font_preset'] ?? 'tiktok'; // 'body' | 'tiktok' | 'rubik' | 'system' | 'custom'
            $headerFontFamily = $design['header_font_family'] ?? '';
            $headerFontCssUrl = $design['header_font_css_url'] ?? '';
            ?>

            <!-- HEADER FONT PRESET -->
            <tr>
                <td>
                    <label for="design_header_font_preset"><?= t('admin.header_font') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][header_font_preset]" id="design_header_font_preset">
                        <option value="body" <?= $headerFontPreset == 'body' ? 'selected' : '' ?>>
                            <?= t('admin.same_as_body') ?>
                        </option>
                        <option value="tiktok" <?= $headerFontPreset == 'tiktok' ? 'selected' : '' ?>>
                            TikTok Sans (<?= t('admin.default') ?>)
                        </option>
                        <option value="rubik" <?= $headerFontPreset == 'rubik' ? 'selected' : '' ?>>
                            Rubik
                        </option>
                        <option value="system" <?= $headerFontPreset == 'system' ? 'selected' : '' ?>>
                            <?= t('admin.system') ?>
                        </option>
                        <option value="custom" <?= $headerFontPreset == 'custom' ? 'selected' : '' ?>>
                            <?= t('admin.custom') ?>
                        </option>
                    </select>

                    <small class="text-muted d-block mt-5">
                        <?= t('admin.tip_prefer_variable_fonts_a_single_css_url_that_includes_italic_weights') ?>
                    </small>
                </td>
            </tr>

            <!-- HEADER CUSTOM FONT DETAILS (only if header_font_preset == custom) -->
            <tr class="design-header-font-custom-row">
                <td>
                    <label for="design_header_font_family"><?= t('admin.header_font_family_name') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input
                            type="text"
                            class="form-control"
                            name="general[design][header_font_family]"
                            id="design_header_font_family"
                            value="<?= e($headerFontFamily) ?>"
                            placeholder="<?= t('admin.e_g_young_serif') ?>">
                    </div>
                    <small class="text-muted d-block mt-5">
                        <?= t('admin.must_match_the_font_name_used_in_the_css_e_g_font_family_young_serif') ?>
                    </small>
                </td>
            </tr>

            <tr class="design-header-font-custom-row">
                <td>
                    <label for="design_header_font_css_url"><?= t('admin.header_font_css_url') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input
                            type="url"
                            class="form-control"
                            name="general[design][header_font_css_url]"
                            id="design_header_font_css_url"
                            value="<?= e($headerFontCssUrl) ?>"
                            placeholder="https://fonts.googleapis.com/css2?family=Young+Serif&display=swap">
                    </div>
                    <small class="text-muted d-block mt-5">
                        <?= t('admin.this_will_be_inserted_as_a_lt_link_rel_stylesheet_gt_in_the_page_header') ?>
                    </small>
                </td>
            </tr>

            <script>
                (function() {
                    function toggleCustomRows(presetValue, selector) {
                        var show = (presetValue === 'custom');
                        document.querySelectorAll(selector).forEach(function(row) {
                            row.style.display = show ? '' : 'none';
                        });
                    }

                    // initial
                    var headerPresetEl = document.getElementById('design_header_font_preset');
                    if (headerPresetEl) {
                        toggleCustomRows(headerPresetEl.value, '.design-header-font-custom-row');
                        headerPresetEl.addEventListener('change', function() {
                            toggleCustomRows(this.value, '.design-header-font-custom-row');
                        });
                    }
                })();
            </script>
            <!-- preview -->
            <tr>
                <td><?= t('common.preview') ?></td>
                <td>
                    <div id="design_font_preview" class="p-10 rounded bg-light">
                        <div class="mb-5" style="font-size: 20px; font-weight: 600;">
                            <?= t('admin.the_quick_brown_fox') ?>
                        </div>
                        <div>
                            Regular •
                            <span style="font-style: italic;">Italic</span> •
                            <span style="font-weight: 700;">Bold</span>
                        </div>
                    </div>
                </td>
            </tr>

            <script>
                (function() {
                    function isCustom() {
                        return $('#design_font_preset').val() === 'custom';
                    }

                    function toggleCustomRows() {
                        $('.design-font-custom-row').toggle(isCustom());
                    }

                    function previewFontFamily() {
                        const preset = $('#design_font_preset').val();

                        const fallbacks = "system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif";
                        let family = fallbacks;

                        if (preset === 'rubik') family = "'Rubik', " + fallbacks;
                        if (preset === 'tiktok') family = "'TikTok Sans', " + fallbacks;
                        if (preset === 'custom') {
                            const custom = ($('#design_font_family').val() || '').trim();
                            family = custom ? ("'" + custom.replace(/'/g, "\\'") + "', " + fallbacks) : fallbacks;
                        }

                        $('#design_font_preview').css('font-family', family);
                    }

                    // init
                    toggleCustomRows();
                    previewFontFamily();

                    // events
                    $('#design_font_preset').on('change', function() {
                        toggleCustomRows();
                        previewFontFamily();
                    });

                    $('#design_font_family').on('input', previewFontFamily);
                })();
            </script>

            <tr>
                <th colspan="2">
                    <?= t('admin.display_of_elements') ?>
                </th>
            </tr>
            <!-- border width -->
            <tr>
                <?php $borderWidth = $design['border_width'] ?? 'normal'; ?>
                <td>
                    <label for="design_border"><?= t('admin.border_width') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][border_width]" id="design_border">
                        <option value="normal" <?= $borderWidth == 'normal' ? 'selected' : '' ?>><?= t('admin.normal_default') ?></option>
                        <option value="thick" <?= $borderWidth == 'thick' ? 'selected' : '' ?>><?= t('common.thick') ?></option>
                        <option value="none" <?= $borderWidth == 'none' ? 'selected' : '' ?>><?= t('common.none') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= t('admin.please_note_that_the_border_width_of_buttons_and_input_fields_is_not_adjust') ?>
                    </small>
                </td>
            </tr>
            <!-- border color -->
            <tr>
                <?php $borderColor = $design['border_color'] ?? '#b8bdc1'; ?>
                <td>
                    <label for="design_border_color"><?= t('common.border_color') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[design][border_color]" value="<?= $borderColor ?>" id="design_border_color">
                        <button type="button" class="btn ml-10" onclick="$('#design_border_color').val('#b8bdc1')" data-toggle="tooltip" data-title="<?= t('admin.reset_to_default') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <?= t('admin.please_note_that_if_no_border_is_selected_above_this_setting_will_still_be') ?>
                    </small>
                </td>
            </tr>
            <tr>
                <?php $corners = $design['border_corners'] ?? 'rounded'; ?>
                <td>
                    <label for="design_corners"><?= t('admin.corners') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][border_corners]" id="design_corners">
                        <option value="rounded" <?= $corners == 'rounded' ? 'selected' : '' ?>><?= t('admin.slightly_rounded_default') ?></option>
                        <option value="more-rounded" <?= $corners == 'more-rounded' ? 'selected' : '' ?>><?= t('admin.more_rounded') ?></option>
                        <option value="very-rounded" <?= $corners == 'very-rounded' ? 'selected' : '' ?>><?= t('admin.very_rounded') ?></option>
                        <option value="sharp" <?= $corners == 'sharp' ? 'selected' : '' ?>><?= t('admin.sharp') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= t('admin.adjust_the_roundness_of_elements_throughout_the_website_this_affects_e_g_bu') ?>
                    </small>
                </td>
            </tr>
            <tr>
                <?php $boxShadow = $design['box_shadow'] ?? 'default'; ?>
                <td>
                    <label for="design_box_shadow"><?= t('admin.box_shadow') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][box_shadow]" id="design_box_shadow">
                        <option value="default" <?= $boxShadow == 'default' ? 'selected' : '' ?>><?= t('admin.default_design') ?></option>
                        <option value="strong" <?= $boxShadow == 'strong' ? 'selected' : '' ?>><?= t('admin.strong') ?></option>
                        <option value="disabled" <?= $boxShadow == 'disabled' ? 'selected' : '' ?>><?= t('admin.no_shadow') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <?php $linkStyle = $design['link_style'] ?? 'default'; ?>
                <td>
                    <label for="design_link_style"><?= t('admin.link_style') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][link_style]" id="design_link_style">
                        <option value="default" <?= $linkStyle == 'default' ? 'selected' : '' ?>><?= t('admin.no_underline') ?></option>
                        <option value="underline" <?= $linkStyle == 'underline' ? 'selected' : '' ?>><?= t('admin.always_underline') ?></option>
                        <option value="underline-hover" <?= $linkStyle == 'underline-hover' ? 'selected' : '' ?>><?= t('admin.underline_on_hover') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <?php $iconStyle = $design['icon_style'] ?? 'default'; ?>
                <td>
                    <label for="design_icon_style"><?= t('admin.icon_style') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][icon_style]" id="design_icon_style">
                        <option value="default" <?= $iconStyle == 'default' ? 'selected' : '' ?>><?= t('admin.default_design') ?></option>
                        <option value="filled" <?= $iconStyle == 'filled' ? 'selected' : '' ?>><?= t('admin.filled') ?></option>
                        <option value="duotone" <?= $iconStyle == 'duotone' ? 'selected' : '' ?>><?= t('admin.duotone') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <?php $tableStriped = $design['table_striped'] ?? 'disabled'; ?>
                <td>
                    <label for="design_table_striped"><?= t('admin.striped_tables') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][table_striped]" id="design_table_striped">
                        <option value="disabled" <?= $tableStriped == 'disabled' ? 'selected' : '' ?>><?= t('admin.disabled_default') ?></option>
                        <option value="enabled" <?= $tableStriped == 'enabled' ? 'selected' : '' ?>><?= t('common.enabled') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= t('admin.enable_or_disable_striped_tables_throughout_the_website') ?>
                    </small>
                </td>
            </tr>

            <!-- logo -->
            <tr>
                <th colspan="2">
                    <?= t('admin.header') ?>
                </th>
            </tr>
            <tr>
                <?php $logoFilter = $design['logo_filter'] ?? 'none'; ?>
                <td>
                    <label for="design_logo_filter"><?= t('admin.osiris_logo') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][logo_filter]" id="design_logo_filter">
                        <option value="none" <?= $logoFilter == 'none' ? 'selected' : '' ?>><?= t('admin.default_orange') ?></option>
                        <option value="grayscale" <?= $logoFilter == 'grayscale' ? 'selected' : '' ?>><?= t('admin.grayscale') ?></option>
                        <option value="sepia" <?= $logoFilter == 'sepia' ? 'selected' : '' ?>><?= t('admin.sepia') ?></option>
                        <option value="black" <?= $logoFilter == 'black' ? 'selected' : '' ?>><?= t('admin.black') ?></option>
                        <option value="green" <?= $logoFilter == 'green' ? 'selected' : '' ?>><?= t('admin.green') ?></option>
                        <option value="red" <?= $logoFilter == 'red' ? 'selected' : '' ?>><?= t('admin.red') ?></option>
                        <option value="blue" <?= $logoFilter == 'blue' ? 'selected' : '' ?>><?= t('admin.blue') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= t('admin.the_logo_modification_applies_a_color_filter_to_the_osiris_logo') ?>
                    </small>
                </td>
            </tr>
            <?php $navbarHeight = $design['navbar_height'] ?? 'default'; ?>
            <tr>
                <td>
                    <label for="design_navbar_height"><?= t('admin.navbar_height') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][navbar_height]" id="design_navbar_height">
                        <option value="narrow" <?= $navbarHeight == 'narrow' ? 'selected' : '' ?>><?= t('admin.narrow') ?></option>
                        <option value="default" <?= $navbarHeight == 'default' ? 'selected' : '' ?>><?= t('admin.default') ?></option>
                        <option value="wide" <?= $navbarHeight == 'wide' ? 'selected' : '' ?>><?= t('admin.wide') ?></option>
                        <option value="none" <?= $navbarHeight == 'none' ? 'selected' : '' ?>><?= t('admin.no_navbar_logos_in_footer') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= t('admin.navbar_height_description') ?>
                    </small>
                </td>
            </tr>

        </table>

        <!-- custom CSS -->
        <div class="mt-20">
            <label for="design_custom_css"><?= t('admin.custom_css') ?></label>
            <textarea class="form-control text-monospace" name="general[design][custom_css]" id="design_custom_css" rows="10"><?= e($design['custom_css'] ?? '') ?></textarea>
            <small class="text-muted d-block mt-5">
                <?= t('admin.this_css_will_be_added_to_the_page_header_and_can_be_used_to_customize_the') ?>
            </small>
        </div>

        <div class="bottom-buttons mt-10">
            <button class="btn primary">
                <i class="ph ph-floppy-disk"></i>
                <?= t('action.save') ?>
            </button>
        </div>
    </div>
</form>
