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
                <?= lang('reports.report_preview') ?>
            </div>
            <h1>
                <i class="ph-duotone ph-clipboard-text"></i>
                <?= $report['title'] ?? lang('common.untitled_report') ?>
            </h1>

            <form action="" method="get">
                <table class="table">
                    <tbody>
                        <?php if (!empty($report['description'])) { ?>
                            <tr>
                                <td colspan="2">
                                    <span class="key"><?= lang('common.description') ?></span>
                                    <?= $report['description'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('common.start_month_reports') ?></span>
                                <input type="number" class="form-control" name="startmonth" id="startmonth" value="<?= $report['start'] ?>" required>
                            </td>
                            <td>
                                <span class="key"><?= lang('common.start_year') ?></span>
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
                                    <?= lang('reports.update_preview') ?>
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
                <?= lang('reports.note_this_is_a_preview_of_the_report_the_actual_report_may_look_different_w') ?>
            </p>
        </div>


        <div class="col-md-3 d-none d-md-block">
            <nav class="on-this-page-nav">
                <div class="content">
                    <div class="title"><?= lang('common.on_this_page') ?></div>

                    <?php foreach ($Report->getHeaders() as $id => $header) { ?>
                        <a href="#<?= e($id) ?>"><?= $header ?></a>
                    <?php } ?>

                </div>
            </nav>
        </div>
    </div>
</div>