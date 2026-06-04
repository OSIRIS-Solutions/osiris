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
            <?= lang('Export Design', 'Export-Design') ?>
        </h1>

        <p class="text-muted">
            <?= lang(
                'Configure the visual appearance of generated Word reports, CVs and exports.',
                'Konfiguriere das Aussehen generierter Word-Berichte, Lebensläufe und Exporte.'
            ) ?>
        </p>

        <table class="table" id="design-table">

            <tr>
                <th colspan="2">
                    <?= lang('General typography', 'Allgemeine Typografie') ?>
                </th>
            </tr>

            <tr>
                <td class="w-200">
                    <label for="export-font-family"><?= lang('Font family', 'Schriftart') ?></label>
                </td>
                <td>
                    <input type="text"
                           class="form-control w-300"
                           name="general[export-design][font][family]"
                           value="<?= e($font['family'] ?? 'Calibri') ?>"
                           id="export-font-family">

                    <small class="text-muted">
                        <?= lang(
                            'The font must be available on the computer opening the Word document.',
                            'Die Schriftart muss auf dem Computer verfügbar sein, auf dem das Word-Dokument geöffnet wird.'
                        ) ?>
                    </small>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="export-font-size"><?= lang('Base font size', 'Basis-Schriftgröße') ?></label>
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
                    <?= lang('Headings', 'Überschriften') ?>
                </th>
            </tr>

            <?php for ($i = 1; $i <= 4; $i++): 
                $h = $headings['h' . $i] ?? [];
            ?>
                <tr>
                    <td>
                        <label><?= lang('Heading', 'Überschrift') ?> <?= $i ?></label>
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
                                   data-title="<?= lang('Font size', 'Schriftgröße') ?>">

                            <input type="color"
                                   class="form-control w-100"
                                   name="general[export-design][headings][h<?= $i ?>][color]"
                                   value="<?= e($h['color'] ?? '#000000') ?>"
                                   data-toggle="tooltip"
                                   data-title="<?= lang('Color', 'Farbe') ?>">

                            <label class="">
                                <input type="checkbox"
                                       name="general[export-design][headings][h<?= $i ?>][bold]"
                                       value="1"
                                       <?= !empty($h['bold']) || !isset($h['bold']) ? 'checked' : '' ?>>
                                <span></span>
                                <?= lang('Bold', 'Fett') ?>
                            </label>

                            <label class="">
                                <input type="checkbox"
                                       name="general[export-design][headings][h<?= $i ?>][numbered]"
                                       value="1"
                                       <?= !empty($h['numbered']) ? 'checked' : '' ?>>
                                <span></span>
                                <?= lang('Numbered', 'Nummeriert') ?>
                            </label>

                        </div>
                    </td>
                </tr>
            <?php endfor; ?>

            <tr>
                <th colspan="2">
                    <?= lang('Tables', 'Tabellen') ?>
                </th>
            </tr>

            <tr>
                <td>
                    <label for="table-border-color"><?= lang('Border color', 'Rahmenfarbe') ?></label>
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
                    <label for="table-border-size"><?= lang('Border size', 'Rahmenstärke') ?></label>
                </td>
                <td>
                    <select name="general[export-design][table][borderSize]" id="table-border-size" class="form-control w-200">
                        <option value="0" <?= (isset($table['borderSize']) && $table['borderSize'] == 0) ? 'selected' : '' ?>><?= lang('No borders', 'Keine Rahmen') ?></option>
                        <option value="10" <?= (isset($table['borderSize']) && $table['borderSize'] == 10) ? 'selected' : '' ?>><?= lang('Thin', 'Dünn') ?> (0.5pt)</option>
                        <option value="20" <?= (isset($table['borderSize']) && $table['borderSize'] == 20) ? 'selected' : '' ?>><?= lang('Normal', 'Normal') ?> (1pt)</option>
                        <option value="30" <?= (isset($table['borderSize']) && $table['borderSize'] == 30) ? 'selected' : '' ?>><?= lang('Medium', 'Mittel') ?> (1.5pt)</option>
                        <option value="40" <?= (isset($table['borderSize']) && $table['borderSize'] == 40) ? 'selected' : '' ?>><?= lang('Thick', 'Dick') ?> (2pt)</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="table-cell-margin"><?= lang('Cell padding', 'Zellabstand') ?></label>
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
                    <?= lang('Page layout', 'Seitenlayout') ?>
                </th>
            </tr>

            <?php
            $margins = [
                'marginTop' => lang('Top margin', 'Oberer Rand'),
                'marginRight' => lang('Right margin', 'Rechter Rand'),
                'marginBottom' => lang('Bottom margin', 'Unterer Rand'),
                'marginLeft' => lang('Left margin', 'Linker Rand'),
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
                            <?= lang('Value in twips.', 'Wert in Twips.') ?>*
                        </small>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr>
                <th colspan="2">
                    <?= lang('Footer', 'Fußzeile') ?>
                </th>
            </tr>

            <tr>
                <td>
                    <label for="footer-text"><?= lang('Footer text', 'Fußzeilentext') ?></label>
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
                    <?= lang('Page numbers', 'Seitennummern') ?>
                </td>
                <td>
                    <label class="">
                        <input type="checkbox"
                               name="general[export-design][footer][pageNumbers]"
                               value="1"
                               <?= !empty($footer['pageNumbers']) || !isset($footer['pageNumbers']) ? 'checked' : '' ?>>
                        <span></span>
                        <?= lang('Show page numbers in footer', 'Seitennummern in der Fußzeile anzeigen') ?>
                    </label>
                </td>
            </tr>

        </table>

        <div class="text-right mt-20">
            <button type="submit" class="btn secondary">
                <i class="ph ph-floppy-disk"></i>
                <?= lang('Save settings', 'Einstellungen speichern') ?>
            </button>
        </div>

        <p class="text-muted">
            * <?= lang('Values for page margins are in twips. 20 twips correspond to 1 pt and 1440 twips correspond to 1 inch.', 'Werte für Seitenränder sind in Twips. 20 twips entsprechen einem pt und 1440 twips entsprechen einem Inch.') ?>
        </p>

    </div>

</form>