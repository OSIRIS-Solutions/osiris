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
            <?= lang('Design Settings', 'Designeinstellungen') ?>
        </h1>

        <table class="table" id="design-table">
            <tr>
                <th colspan="2" class="border-top">
                    <?= lang('Colors', 'Farben') ?>
                </th>
            </tr>
            <tr>
                <td class="w-200">
                    <label for="color"><?= lang('Primary Color', 'Primärfarbe') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[colors][primary]" value="<?= $colors['primary'] ?? '#008083' ?>" id="primary-color">
                        <button type="button" class="btn ml-10" onclick="$('#primary-color').val('#008083')" data-toggle="tooltip" data-title="<?= lang('Reset to default', 'Auf Standard zurücksetzen') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <?= lang(
                            'The primary color is used for the main elements of the website.',
                            'Die Primärfarbe wird für die Hauptelemente der Website verwendet.'
                        ) ?>
                    </small>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="color"><?= lang('Secondary Color', 'Sekundärfarbe') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[colors][secondary]" value="<?= $colors['secondary'] ?? '#f78104' ?>" id="secondary-color">
                        <button type="button" class="btn ml-10" onclick="$('#secondary-color').val('#f78104')" data-toggle="tooltip" data-title="<?= lang('Reset to default', 'Auf Standard zurücksetzen') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <?= lang(
                            'The secondary color is used for highlighted elements of the website.',
                            'Die Sekundärfarbe wird für die hervorgehobenen Elemente der Website verwendet.'
                        ) ?>
                        <br>
                        <i class="ph ph-warning"></i>
                        <?= lang('If your institution has no secondary color, please set the secondary color to the same value as the primary color and do not select black or white.', 'Wenn deine Institution keine Sekundärfarbe hat, setze die Sekundärfarbe bitte auf den gleichen Wert wie die Primärfarbe und wähle nicht schwarz oder weiß aus.') ?>
                    </small>
                </td>
            </tr>


            <tr>
                <td>
                    <label for="color"><?= lang('Link Color', 'Linkfarbe') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[colors][link]" value="<?= $colors['link'] ?? '#0e7b96' ?>" id="link-color">
                        <button type="button" class="btn ml-10" onclick="$('#link-color').val('#0e7b96')" data-toggle="tooltip" data-title="<?= lang('Reset to default', 'Auf Standard zurücksetzen') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2">
                    <?= lang('Typography', 'Typografie') ?>
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
                    <label for="design_font_preset"><?= lang('Font', 'Schriftart') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][font_preset]" id="design_font_preset">
                        <option value="rubik" <?= $fontPreset == 'rubik' ? 'selected' : '' ?>>
                            Rubik (<?= lang('default', 'Standard') ?>)
                        </option>
                        <option value="tiktok" <?= $fontPreset == 'tiktok' ? 'selected' : '' ?>>
                            TikTok Sans
                        </option>
                        <option value="system" <?= $fontPreset == 'system' ? 'selected' : '' ?>>
                            <?= lang('System', 'System') ?>
                        </option>
                        <option value="custom" <?= $fontPreset == 'custom' ? 'selected' : '' ?>>
                            <?= lang('Custom*', 'Benutzerdefiniert*') ?>
                        </option>
                    </select>
                    <small class="text-muted d-block mt-5">
                        <?= lang(
                            '*Tip: Prefer variable fonts / a single CSS URL that includes italic + weights.',
                            '*Tipp: Am besten variable Fonts / eine CSS-URL, die Italic + Gewichte enthält.'
                        ) ?>
                    </small>
                </td>
            </tr>

            <!-- custom font details -->
            <tr class="design-font-custom-row">
                <td>
                    <label for="design_font_family"><?= lang('Font family name', 'Font-Familienname') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input
                            type="text"
                            class="form-control"
                            name="general[design][font_family]"
                            id="design_font_family"
                            value="<?= e($fontFamily) ?>"
                            placeholder="<?= lang("e.g. Rubik", "z.B. Rubik") ?>">
                    </div>
                    <small class="text-muted d-block mt-5">
                        <?= lang(
                            "Must match the font name used in the CSS (e.g. font-family: 'Rubik';).",
                            "Muss zum Namen im CSS passen (z.B. font-family: 'Rubik';)."
                        ) ?>
                    </small>
                </td>
            </tr>

            <tr class="design-font-custom-row">
                <td>
                    <label for="design_font_css_url"><?= lang('Font CSS URL', 'Font-CSS-URL') ?></label>
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
                        <?= lang(
                            'This will be inserted as a &lt;link rel="stylesheet"&gt; in the page header.',
                            'Wird als &lt;link rel="stylesheet"&gt; im Header eingebunden.'
                        ) ?>
                    </small>
                </td>
            </tr>
            <tr class="design-font-custom-row">
                <td>
                    <label for="design_font_headers"><?= lang('Use for headers as well', 'Auch für Überschriften verwenden') ?></label>
                </td>
                <td>
                    <?php $fontHeaders = $design['font_headers'] ?? 'no'; ?>
                    <select class="form-control" name="general[design][font_headers]" id="design_font_headers">
                        <option value="no" <?= $fontHeaders == 'no' ? 'selected' : '' ?>><?= lang('No (default)', 'Nein (Standard)') ?></option>
                        <option value="yes" <?= $fontHeaders == 'yes' ? 'selected' : '' ?>><?= lang('Yes', 'Ja') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= lang('Default font for headers is TikTok Sans.', 'Standard-Schriftart für Überschriften ist TikTok Sans.') ?>
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
                    <label for="design_header_font_preset"><?= lang('Header font', 'Überschriften-Schriftart') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][header_font_preset]" id="design_header_font_preset">
                        <option value="body" <?= $headerFontPreset == 'body' ? 'selected' : '' ?>>
                            <?= lang('Same as body', 'Wie Fließtext') ?>
                        </option>
                        <option value="tiktok" <?= $headerFontPreset == 'tiktok' ? 'selected' : '' ?>>
                            TikTok Sans (<?= lang('default', 'Standard') ?>)
                        </option>
                        <option value="rubik" <?= $headerFontPreset == 'rubik' ? 'selected' : '' ?>>
                            Rubik
                        </option>
                        <option value="system" <?= $headerFontPreset == 'system' ? 'selected' : '' ?>>
                            <?= lang('System', 'System') ?>
                        </option>
                        <option value="custom" <?= $headerFontPreset == 'custom' ? 'selected' : '' ?>>
                            <?= lang('Custom*', 'Benutzerdefiniert*') ?>
                        </option>
                    </select>

                    <small class="text-muted d-block mt-5">
                        <?= lang(
                            '*Tip: Prefer variable fonts / a single CSS URL that includes italic + weights.',
                            '*Tipp: Am besten variable Fonts / eine CSS-URL, die Italic + Gewichte enthält.'
                        ) ?>
                    </small>
                </td>
            </tr>

            <!-- HEADER CUSTOM FONT DETAILS (only if header_font_preset == custom) -->
            <tr class="design-header-font-custom-row">
                <td>
                    <label for="design_header_font_family"><?= lang('Header font family name', 'Header Font-Familienname') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input
                            type="text"
                            class="form-control"
                            name="general[design][header_font_family]"
                            id="design_header_font_family"
                            value="<?= e($headerFontFamily) ?>"
                            placeholder="<?= lang("e.g. Young Serif", "z.B. Young Serif") ?>">
                    </div>
                    <small class="text-muted d-block mt-5">
                        <?= lang(
                            "Must match the font name used in the CSS (e.g. font-family: 'Young Serif';).",
                            "Muss zum Namen im CSS passen (z.B. font-family: 'Young Serif';)."
                        ) ?>
                    </small>
                </td>
            </tr>

            <tr class="design-header-font-custom-row">
                <td>
                    <label for="design_header_font_css_url"><?= lang('Header font CSS URL', 'Header Font-CSS-URL') ?></label>
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
                        <?= lang(
                            'This will be inserted as a &lt;link rel="stylesheet"&gt; in the page header.',
                            'Wird als &lt;link rel="stylesheet"&gt; im Header eingebunden.'
                        ) ?>
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
                <td><?= lang('Preview', 'Vorschau') ?></td>
                <td>
                    <div id="design_font_preview" class="p-10 rounded bg-light">
                        <div class="mb-5" style="font-size: 20px; font-weight: 600;">
                            <?= lang('The quick brown fox', 'Franz jagt im komplett verwahrlosten Taxi quer durch Bayern') ?>
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
                    <?= lang('Display of elements', 'Darstellung von Elementen') ?>
                </th>
            </tr>
            <!-- border width -->
            <tr>
                <?php $borderWidth = $design['border_width'] ?? 'normal'; ?>
                <td>
                    <label for="design_border"><?= lang('Border width', 'Rahmenbreite') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][border_width]" id="design_border">
                        <option value="normal" <?= $borderWidth == 'normal' ? 'selected' : '' ?>><?= lang('Normal (default)', 'Normal (Standard)') ?></option>
                        <option value="thick" <?= $borderWidth == 'thick' ? 'selected' : '' ?>><?= lang('Thick', 'Dick') ?></option>
                        <option value="none" <?= $borderWidth == 'none' ? 'selected' : '' ?>><?= lang('None', 'Keine') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= lang(
                            'Please note that the border width of buttons and input fields is not adjusted.',
                            'Bitte beachte, dass die Rahmenbreite von Knöpfen und Eingabefeldern nicht angepasst wird.'
                        ) ?>
                    </small>
                </td>
            </tr>
            <!-- border color -->
            <tr>
                <?php $borderColor = $design['border_color'] ?? '#afafaf'; ?>
                <td>
                    <label for="design_border_color"><?= lang('Border color', 'Rahmenfarbe') ?></label>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="color" class="form-control w-200" name="general[design][border_color]" value="<?= $borderColor ?>" id="design_border_color">
                        <button type="button" class="btn ml-10" onclick="$('#design_border_color').val('#afafaf')" data-toggle="tooltip" data-title="<?= lang('Reset to default', 'Auf Standard zurücksetzen') ?>">
                            <i class="ph ph-arrow-counter-clockwise"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <?= lang('Please note that if no border is selected above, this setting will still be applied to buttons, input fields and tables.', 'Falls oben kein Rahmen ausgewählt ist, wird diese Einstellung trotzdem auf Knöpfe, Eingabefelder und Tabellen angewendet.') ?>
                    </small>
                </td>
            </tr>
            <tr>
                <?php $corners = $design['border_corners'] ?? 'rounded'; ?>
                <td>
                    <label for="design_corners"><?= lang('Corners', 'Ecken') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][border_corners]" id="design_corners">
                        <option value="rounded" <?= $corners == 'rounded' ? 'selected' : '' ?>><?= lang('Slightly rounded (default)', 'Leicht gerundet (Standard)') ?></option>
                        <option value="more-rounded" <?= $corners == 'more-rounded' ? 'selected' : '' ?>><?= lang('More rounded', 'Mehr abgerundet') ?></option>
                        <option value="very-rounded" <?= $corners == 'very-rounded' ? 'selected' : '' ?>><?= lang('Very rounded', 'Sehr abgerundet') ?></option>
                        <option value="sharp" <?= $corners == 'sharp' ? 'selected' : '' ?>><?= lang('Sharp', 'Eckig') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= lang(
                            'Adjust the roundness of elements throughout the website. This affects e.g. buttons, input fields and boxes.',
                            'Passe die Rundheit der Elemente auf der gesamten Website an. Davon betroffen sind z.B. Buttons, Eingabefelder und Boxen.'
                        ) ?>
                    </small>
                </td>
            </tr>
            <tr>
                <?php $boxShadow = $design['box_shadow'] ?? 'default'; ?>
                <td>
                    <label for="design_box_shadow"><?= lang('Box shadow', 'Box-Schatten') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][box_shadow]" id="design_box_shadow">
                        <option value="default" <?= $boxShadow == 'default' ? 'selected' : '' ?>><?= lang('Default', 'Standard') ?></option>
                        <option value="strong" <?= $boxShadow == 'strong' ? 'selected' : '' ?>><?= lang('Strong', 'Stark') ?></option>
                        <option value="disabled" <?= $boxShadow == 'disabled' ? 'selected' : '' ?>><?= lang('No shadow', 'Kein Schatten') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <?php $linkStyle = $design['link_style'] ?? 'default'; ?>
                <td>
                    <label for="design_link_style"><?= lang('Link style', 'Link-Stil') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][link_style]" id="design_link_style">
                        <option value="default" <?= $linkStyle == 'default' ? 'selected' : '' ?>><?= lang('No underline', 'Keine Unterstreichung') ?></option>
                        <option value="underline" <?= $linkStyle == 'underline' ? 'selected' : '' ?>><?= lang('Always Underline', 'Immer Unterstrichen') ?></option>
                        <option value="underline-hover" <?= $linkStyle == 'underline-hover' ? 'selected' : '' ?>><?= lang('Underline on hover', 'Unterstrichen beim Hover') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <?php $iconStyle = $design['icon_style'] ?? 'default'; ?>
                <td>
                    <label for="design_icon_style"><?= lang('Icon style', 'Icon-Stil') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][icon_style]" id="design_icon_style">
                        <option value="default" <?= $iconStyle == 'default' ? 'selected' : '' ?>><?= lang('Default', 'Standard') ?></option>
                        <option value="filled" <?= $iconStyle == 'filled' ? 'selected' : '' ?>><?= lang('Filled', 'Gefüllt') ?></option>
                        <option value="duotone" <?= $iconStyle == 'duotone' ? 'selected' : '' ?>><?= lang('Duotone', 'Zweifarbiger Stil') ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <?php $tableStriped = $design['table_striped'] ?? 'disabled'; ?>
                <td>
                    <label for="design_table_striped"><?= lang('Striped tables', 'Gestreifte Tabellen') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][table_striped]" id="design_table_striped">
                        <option value="disabled" <?= $tableStriped == 'disabled' ? 'selected' : '' ?>><?= lang('Disabled (default)', 'Deaktiviert (Standard)') ?></option>
                        <option value="enabled" <?= $tableStriped == 'enabled' ? 'selected' : '' ?>><?= lang('Enabled', 'Aktiviert') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= lang(
                            'Enable or disable striped tables throughout the website.',
                            'Aktiviere oder deaktiviere gestreifte Tabellen auf der gesamten Website.'
                        ) ?>
                    </small>
                </td>
            </tr>

            <!-- logo -->
            <tr>
                <th colspan="2">
                    <?= lang('Header', 'Kopfzeile') ?>
                </th>
            </tr>
            <tr>
                <?php $logoFilter = $design['logo_filter'] ?? 'none'; ?>
                <td>
                    <label for="design_logo_filter"><?= lang('OSIRIS Logo', 'OSIRIS Logo') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][logo_filter]" id="design_logo_filter">
                        <option value="none" <?= $logoFilter == 'none' ? 'selected' : '' ?>><?= lang('Default (orange)', 'Standard (orange)') ?></option>
                        <option value="grayscale" <?= $logoFilter == 'grayscale' ? 'selected' : '' ?>><?= lang('Grayscale', 'Graustufen') ?></option>
                        <option value="sepia" <?= $logoFilter == 'sepia' ? 'selected' : '' ?>><?= lang('Sepia', 'Sepia') ?></option>
                        <option value="black" <?= $logoFilter == 'black' ? 'selected' : '' ?>><?= lang('Black', 'Schwarz') ?></option>
                        <option value="green" <?= $logoFilter == 'green' ? 'selected' : '' ?>><?= lang('Green', 'Grün') ?></option>
                        <option value="red" <?= $logoFilter == 'red' ? 'selected' : '' ?>><?= lang('Red', 'Rot') ?></option>
                        <option value="blue" <?= $logoFilter == 'blue' ? 'selected' : '' ?>><?= lang('Blue', 'Blau') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= lang(
                            'The logo modification applies a color filter to the OSIRIS logo.',
                            'Die Logo-Modifikation wendet einen Farbfilter auf das OSIRIS-Logo an.'
                        ) ?>
                    </small>
                </td>
            </tr>
            <?php $navbarHeight = $design['navbar_height'] ?? 'default'; ?>
            <tr>
                <td>
                    <label for="design_navbar_height"><?= lang('Navbar Height', 'Navbar Höhe') ?></label>
                </td>
                <td>
                    <select class="form-control" name="general[design][navbar_height]" id="design_navbar_height">
                        <option value="narrow" <?= $navbarHeight == 'narrow' ? 'selected' : '' ?>><?= lang('narrow', 'schmal') ?></option>
                        <option value="default" <?= $navbarHeight == 'default' ? 'selected' : '' ?>><?= lang('default', 'Standard') ?></option>
                        <option value="wide" <?= $navbarHeight == 'wide' ? 'selected' : '' ?>><?= lang('wide', 'breit') ?></option>
                        <option value="none" <?= $navbarHeight == 'none' ? 'selected' : '' ?>><?= lang('no navbar (logos in footer)', 'keine Navbar (Logos im Footer)') ?></option>
                    </select>
                    <small class="text-muted">
                        <?= lang(
                            'Adjust the height of the navigation bar at the top of the page.',
                            'Es wird nur die Höhe der oberen Navigationsleiste angepasst, die die Logos enthält. Wenn "keine Navbar" ausgewählt ist, werden die Logos in den Footer verschoben.',
                        ) ?>
                    </small>
                </td>
            </tr>

        </table>

        <div class="bottom-buttons mt-10">
            <button class="btn primary">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save', 'Speichern') ?>
            </button>
        </div>
    </div>
</form>