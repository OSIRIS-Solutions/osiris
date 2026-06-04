<?php

/**
 * Page to export report
 * 
 * Component of the controlling page.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /controlling
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$reports = $osiris->adminReports->find([], ['sort' => ['order' => 1]])->toArray();

?>


<div class="modal" id="order" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title">
                <i class="ph ph-list-numbers"></i>
                <?= lang('Change order', 'Reihenfolge ändern') ?>
            </h5>

            <style>
                tr.ui-sortable-helper {
                    background-color: white;
                    border: var(--border-width) solid var(--border-color);
                }
            </style>

            <form action="<?= ROOTPATH ?>/crud/reports/update-order" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/reports">

                <table class="table w-auto">
                    <tbody id="authors">
                        <?php foreach ($reports as $report) { ?>
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle cursor-pointer"></i>
                                </td>
                                <td>
                                    <input type="hidden" name="order[]" value="<?= $report['_id'] ?>">
                                    <?= $report['title'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
                <button class="btn secondary mt-20">
                    <i class="ph ph-check"></i>
                    <?= lang('Submit', 'Bestätigen') ?>
                </button>
            </form>
            <?php include_once BASEPATH . '/header-editor.php'; ?>
            <script>
                $(document).ready(function() {
                    $('#authors').sortable({
                        handle: ".handle",
                        // change: function( event, ui ) {}
                    });
                })
            </script>


        </div>
    </div>
</div>


<h1>
    <i class="ph-duotone ph-clipboard-text"></i>
    <?= lang('Reports', 'Berichte') ?>
</h1>

<?php if ($Settings->hasPermission('report.templates')) { ?>
    <div class="btn-toolbar mb-20">
        <a href="<?= ROOTPATH ?>/admin/reports" class="btn primary ">
            <i class="ph ph-edit"></i>
            <?= lang('Edit templates', 'Vorlagen bearbeiten') ?>
        </a>
        <a href="<?= ROOTPATH ?>/admin/export-design" class="btn ">
            <i class="ph ph-palette"></i>
            <?= lang('Export design', 'Export-Design') ?>
        </a>
        <a href="#order" class="btn " data-toggle="modal">
            <i class="ph ph-list-numbers"></i>
            <?= lang('Change order', 'Reihenfolge ändern') ?>
        </a>
    </div>
<?php } ?>

<?php
if (empty($reports)) {
    echo '<div class="alert alert-info">' . lang('No reports found.', 'Keine Berichte gefunden.') . '</div>';
} else foreach ($reports as $report) { ?>
    <details class="collapse-panel mb-20">
        <summary class="collapse-header">
            <?php if ($Settings->hasPermission('report.templates')) { ?>
                <a href="<?= ROOTPATH ?>/admin/reports/builder/<?= $report['_id'] ?>" class="btn btn-sm btn-secondary float-right" title="<?= lang('Edit report template', 'Report-Vorlage bearbeiten') ?>">
                    <i class="ph ph-pencil" aria-hidden="true"></i>
                </a>
            <?php } ?>
            <h3 class="m-0"><?= $report['title'] ?></h3>
            <p class="text-primary m-0"><?= $report['description'] ?? '' ?></p>
        </summary>
        <div class="collapse-content">
            <form action="<?= ROOTPATH ?>/reports" method="post">
                <input type="hidden" name="id" value="<?= $report['_id'] ?>">

                <div class="form-row row-eq-spacing">
                    <div class="col-sm">
                        <label for="format"><?= lang('Start year', 'Start-Jahr') ?></label>
                        <input type="number" class="form-control" name="startyear" id="startyear" value="<?= CURRENTYEAR ?>" required>
                    </div>
                    <div class="col-sm">
                        <label for="format"><?= lang('Start month', 'Start-Monat') ?></label>
                        <input type="number" class="form-control" name="startmonth" id="startmonth" value="<?= $report['start'] ?>" required>
                    </div>
                    <div class="col-sm">
                        <label for="format"><?= lang('Duration in month', 'Dauer in Monaten') ?></label>
                        <input type="number" class="form-control" name="duration" id="duration" value="<?= $report['duration'] ?>" required>
                    </div>
                </div>
                <?php
                $vars = DB::doc2Arr($report['variables'] ?? []);
                if (!empty($vars)) { ?>
                    <fieldset>
                        <legend><?= lang('Additional parameters', 'Zusätzliche Parameter') ?></legend>
                        <?php foreach ($vars as $var) {  ?>
                            <div class="form-group">
                                <label for="var[<?= ($var['key']) ?>]"><?= ($var['label'] ?? $var['key']) ?></label>
                                <input type="<?= ($var['type'] ?? 'text') ?>" class="form-control" value="<?= e($_GET['var'][$var['key']] ?? ($var['default'] ?? '')) ?>" name="var[<?= ($var['key']) ?>]">
                            </div>
                        <?php } ?>
                    </fieldset>
                <?php } ?>

                <div class="form-group">
                    <label for="format">Format</label>
                    <select name="format" id="format" class="form-control">
                        <option value="word">MS Word</option>
                        <option value="html">HTML</option>
                    </select>
                </div>

                <button class="btn" type="submit"><?= lang('Generate report', 'Report erstellen') ?></button>

            </form>
        </div>
    </details>
<?php } ?>


<!-- 
    LEGACY CODE
<div class="box secondary">
    <div class="content">

        <h5><?= lang('Export reports', 'Exportiere Berichte') ?></h5>

        <form action="<?= ROOTPATH ?>/reports/old" method="post">

            <div class="form-row row-eq-spacing-sm">
                <div class="col-sm">
                    <label class="required" for="start">
                        <?= lang('Beginning of report', 'Anfang des Reports') ?>
                    </label>
                    <input type="date" class="form-control" name="start" id="start" value="<?= CURRENTYEAR ?>-01-01" required>
                </div>
                <div class="col-sm">
                    <label class="required" for="end">
                        <?= lang('End of report', 'Ende des Reports') ?>
                    </label>
                    <input type="date" class="form-control" name="end" id="end" value="<?= CURRENTYEAR ?>-06-30" required>
                </div>
            </div>

            <div class="form-group">
                <label for="style">Report-Style</label>
                <select name="style" id="style" class="form-control">
                    <option value="research-report">Research report</option>
                    <option value="programm-budget">Programmbudget</option>
                </select>
            </div>

            <div class="form-group">
                <label for="format">Format</label>
                <select name="format" id="format" class="form-control">
                    <option value="word">MS Word</option>
                    <option value="html">HTML</option>
                </select>
            </div>

            <button class="btn" type="submit"><?= lang('Generate report', 'Report erstellen') ?></button>
        </form>

    </div>
</div> -->