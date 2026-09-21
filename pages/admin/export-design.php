<?php
$export = $Settings->get('export-design') ?? [];

$font = $export['font'] ?? [];
$headings = $export['headings'] ?? [];
$table = $export['table'] ?? [];
$page = $export['page'] ?? [];
$footer = $export['footer'] ?? [];
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
<form action="<?= ROOTPATH ?>/crud/admin/general" method="post" id="export-design-form">

    <div class="container w-800 mw-full">

        <h1>
            <i class="ph-duotone ph-file-doc"></i>
            <?= lang('admin.export_design') ?>
        </h1>

        <p class="text-muted">
            <?= lang('admin.configure_the_visual_appearance_of_generated_word_reports_cvs_and_exports') ?>
        </p>

        <table class="table" id="design-table">

            <tr>
                <th colspan="2">
                    <?= lang('admin.general_typography') ?>
                </th>
            </tr>

            <tr>
                <td class="w-200">
                    <label for="export-font-family"><?= lang('admin.font_family') ?></label>
                </td>
                <td>
                    <input type="text"
                           class="form-control w-300"
                           name="general[export-design][font][family]"
                           value="<?= e($font['family'] ?? 'Calibri') ?>"
                           id="export-font-family">

                    <small class="text-muted">
                        <?= lang('admin.the_font_must_be_available_on_the_computer_opening_the_word_document') ?>
                    </small>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="export-font-size"><?= lang('admin.base_font_size') ?></label>
                </td>
                <td>
                    <input type="number"
                           class="form-control w-100"
                           name="general[export-design][font][size]"
                           value="<?= e($font['size'] ?? 11) ?>"
                           id="export-font-size"
                           min="6"
                           max="24"
                           step="1">
                </td>
            </tr>

            <tr>
                <th colspan="2">
                    <?= lang('admin.headings') ?>
                </th>
            </tr>

            <?php for ($i = 1; $i <= 4; $i++): 
                $h = $headings['h' . $i] ?? [];
            ?>
                <tr>
                    <td>
                        <label><?= lang('common.heading') ?> <?= $i ?></label>
                    </td>
                    <td>
                        <div class="d-flex align-items-center flex-wrap gap-10">

                            <input type="number"
                                   class="form-control w-100"
                                   name="general[export-design][headings][h<?= $i ?>][size]"
                                   value="<?= e($h['size'] ?? match($i) {
                                       1 => 16,
                                       2 => 14,
                                       3 => 13,
                                       default => 12
                                   }) ?>"
                                   min="6"
                                   max="32"
                                   step="1"
                                   data-toggle="tooltip"
                                   data-title="<?= lang('admin.font_size') ?>">

                            <input type="color"
                                   class="form-control w-100"
                                   name="general[export-design][headings][h<?= $i ?>][color]"
                                   value="<?= e($h['color'] ?? '#000000') ?>"
                                   data-toggle="tooltip"
                                   data-title="<?= lang('common.color') ?>">

                            <label class="">
                                <input type="checkbox"
                                       name="general[export-design][headings][h<?= $i ?>][bold]"
                                       value="1"
                                       <?= !empty($h['bold']) || !isset($h['bold']) ? 'checked' : '' ?>>
                                <span></span>
                                <?= lang('admin.bold') ?>
                            </label>

                            <label class="">
                                <input type="checkbox"
                                       name="general[export-design][headings][h<?= $i ?>][numbered]"
                                       value="1"
                                       <?= !empty($h['numbered']) ? 'checked' : '' ?>>
                                <span></span>
                                <?= lang('admin.numbered') ?>
                            </label>

                        </div>
                    </td>
                </tr>
            <?php endfor; ?>

            <tr>
                <th colspan="2">
                    <?= lang('admin.tables') ?>
                </th>
            </tr>

            <tr>
                <td>
                    <label for="table-border-color"><?= lang('common.border_color') ?></label>
                </td>
                <td>
                    <input type="color"
                           class="form-control w-200"
                           name="general[export-design][table][borderColor]"
                           value="<?= e($table['borderColor'] ?? '#CCCCCC') ?>"
                           id="table-border-color">
                </td>
            </tr>

            <tr>
                <td>
                    <label for="table-border-size"><?= lang('admin.border_size') ?></label>
                </td>
                <td>
                    <select name="general[export-design][table][borderSize]" id="table-border-size" class="form-control w-200">
                        <option value="0" <?= (isset($table['borderSize']) && $table['borderSize'] == 0) ? 'selected' : '' ?>><?= lang('admin.no_borders') ?></option>
                        <option value="10" <?= (isset($table['borderSize']) && $table['borderSize'] == 10) ? 'selected' : '' ?>><?= lang('admin.thin') ?> (0.5pt)</option>
                        <option value="20" <?= (isset($table['borderSize']) && $table['borderSize'] == 20) ? 'selected' : '' ?>><?= lang('common.normal') ?> (1pt)</option>
                        <option value="30" <?= (isset($table['borderSize']) && $table['borderSize'] == 30) ? 'selected' : '' ?>><?= lang('admin.medium') ?> (1.5pt)</option>
                        <option value="40" <?= (isset($table['borderSize']) && $table['borderSize'] == 40) ? 'selected' : '' ?>><?= lang('common.thick') ?> (2pt)</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="table-cell-margin"><?= lang('admin.cell_padding') ?></label>
                </td>
                <td>
                    <input type="number"
                           class="form-control w-200"
                           name="general[export-design][table][cellMargin]"
                           value="<?= e($table['cellMargin'] ?? 80) ?>"
                           id="table-cell-margin"
                           min="0"
                           max="500"
                           step="10">
                </td>
            </tr>

            <tr>
                <th colspan="2">
                    <?= lang('admin.page_layout') ?>
                </th>
            </tr>

            <?php
            $margins = [
                'marginTop' => lang('admin.top_margin'),
                'marginRight' => lang('admin.right_margin'),
                'marginBottom' => lang('admin.bottom_margin'),
                'marginLeft' => lang('admin.left_margin'),
            ];
            ?>

            <?php foreach ($margins as $key => $label): ?>
                <tr>
                    <td>
                        <label for="page-<?= $key ?>"><?= $label ?></label>
                    </td>
                    <td>
                        <input type="number"
                               class="form-control w-150"
                               name="general[export-design][page][<?= $key ?>]"
                               value="<?= e($page[$key] ?? 1200) ?>"
                               id="page-<?= $key ?>"
                               min="0"
                               max="3000"
                               step="100">
                        <small class="text-muted">
                            <?= lang('admin.value_in_twips') ?>*
                        </small>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <th colspan="2">
                    <?= lang('admin.footer_export_design') ?>
                </th>
            </tr>

            <tr>
                <td>
                    <label for="footer-text"><?= lang('admin.footer_text') ?></label>
                </td>
                <td>
                    <input type="text"
                           class="form-control"
                           name="general[export-design][footer][text]"
                           value="<?= e($footer['text'] ?? 'Generated with OSIRIS') ?>"
                           id="footer-text">
                </td>
            </tr>

            <tr>
                <td>
                    <?= lang('admin.page_numbers') ?>
                </td>
                <td>
                    <label class="">
                        <input type="checkbox"
                               name="general[export-design][footer][pageNumbers]"
                               value="1"
                               <?= !empty($footer['pageNumbers']) || !isset($footer['pageNumbers']) ? 'checked' : '' ?>>
                        <span></span>
                        <?= lang('admin.show_page_numbers_in_footer') ?>
                    </label>
                </td>
            </tr>

        </table>

        <div class="text-right mt-20">
            <button type="submit" class="btn secondary">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('admin.save_settings') ?>
            </button>
        </div>

        <p class="text-muted">
            * <?= lang('admin.values_for_page_margins_are_in_twips_20_twips_correspond_to_1_pt_and_1440_t') ?>
        </p>

    </div>

</form>