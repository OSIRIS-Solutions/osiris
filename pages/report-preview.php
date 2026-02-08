<?php

/**
 * This is the preview for the report builder
 */
// markdown support
require_once BASEPATH . "/php/Report.php";

$Report = new Report($report);

$year = $_GET['year'] ?? CURRENTYEAR - 1;
$variables = $report['variables'] ?? [];

?>

<div class="container w-800 mw-full">


    <h1>
        <i class="ph-duotone ph-clipboard-text"></i>
        <?= lang('Report Preview', 'Berichtsvorschau') ?>
    </h1>

    <form action="" method="get">
        <table class="table">
            <tbody>
                <tr>
                    <td>
                        <span class="key"><?= lang('Title', 'Titel') ?></span>
                        <b><?= $report['title'] ?></b>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Description', 'Beschreibung') ?></span>
                        <?= $report['description'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Start year', 'Start-Jahr') ?></span>

                        <input type="number" class="form-control" name="year" id="year" value="<?= $year ?>" required>
                        <small class="text-muted">
                            <?= lang('Press enter to set a new start year', 'Drücke Enter, um ein anderes Startjahr zu wählen') ?>
                        </small>
                    </td>
                </tr>
                <?php foreach ($variables as $var) {  ?>
                    <tr>
                        <td>
                            <span class="key"><?= ($var['label'] ?? $var['key']) ?></span>
                            <input type="<?= ($var['type'] ?? 'text') ?>" class="form-control" value="<?= e($_GET['var'][$var['key']] ?? ($var['default'] ?? '')) ?>" name="var[<?= ($var['key']) ?>]">
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <button type="submit" class="btn btn-primary">
                            <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                            <?= lang('Update preview', 'Vorschau aktualisieren') ?>
                        </button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>



    <div class="box">
        <div class="content">
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
    </div>

</div>