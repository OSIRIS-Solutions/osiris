<?php

/**
 * This is the preview for the report builder
 */
// markdown support
require_once BASEPATH . "/php/Report.php";

$Report = new Report($report);

$year = $_GET['year'] ?? CURRENTYEAR - 1;
$variables = $report['variables'] ?? [];

$headers = [];
?>

<div class="container ">
    <div class="row row-eq-spacing">
        <div class="col-md w-800 mw-full flex-grow-0 flex-reset">
            <div class="eyebrow">
                <?= lang('Report Preview', 'Berichtsvorschau') ?>
            </div>
            <h1>
                <i class="ph-duotone ph-clipboard-text"></i>
                <?= $report['title'] ?? lang('Untitled Report', 'Unbenannter Bericht') ?>
            </h1>

            <form action="" method="get">
                <table class="table">
                    <tbody>
                        <?php if (!empty($report['description'])) { ?>
                            <tr>
                                <td colspan="2">
                                    <span class="key"><?= lang('Description', 'Beschreibung') ?></span>
                                    <?= $report['description'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Start month', 'Start-Monat') ?></span>
                                <input type="number" class="form-control" name="startmonth" id="startmonth" value="<?= $report['start'] ?>" required>
                            </td>
                            <td>
                                <span class="key"><?= lang('Start year', 'Start-Jahr') ?></span>
                                <input type="number" class="form-control" name="year" id="year" value="<?= $year ?>" required>
                            </td>
                        </tr>
                        <?php foreach ($variables as $var) {  ?>
                            <tr>
                                <td colspan="2">
                                    <span class="key"><?= ($var['label'] ?? $var['key']) ?></span>
                                    <input type="<?= ($var['type'] ?? 'text') ?>" class="form-control" value="<?= e($_GET['var'][$var['key']] ?? ($var['default'] ?? '')) ?>" name="var[<?= ($var['key']) ?>]">
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right">
                                <button type="submit" class="btn primary">
                                    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                                    <?= lang('Update preview', 'Vorschau aktualisieren') ?>
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </form>



            <div class="box padded">
                <?php
                $Report->setYear($year);
                $vars = [];
                foreach ($variables as $var) {
                    $vars[$var['key']] = $_GET['var'][$var['key']] ?? ($var['default'] ?? null);
                }
                $Report->setVariables($vars);
                echo $Report->getReport();
                ?>
            </div>

            <p class="text-muted">
                <?= lang('<b>Note:</b> This is a preview of the report. The actual report may look different when exported. The selected UI language affects the language in the report.', '<b>Hinweis:</b> Dies ist eine Vorschau des Berichts. Der tatsächliche Bericht kann nach dem Export anders aussehen. Die ausgewählte UI-Sprache wirkt sich auf die Sprache im Report aus.') ?>
            </p>
        </div>


        <div class="col-md-3 d-none d-md-block">
            <nav class="on-this-page-nav">
                <div class="content">
                    <div class="title"><?= lang('On this page', 'Auf dieser Seite') ?></div>

                    <?php foreach ($Report->getHeaders() as $id => $header) { ?>
                        <a href="#<?= e($id) ?>"><?= $header ?></a>
                    <?php } ?>

                </div>
            </nav>
        </div>
    </div>
</div>