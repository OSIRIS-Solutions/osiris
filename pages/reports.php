<?php

/**
 * Page to export report
 * 
 * Component of the controlling page.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /controlling
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<?php if ($Settings->hasPermission('report.templates')) { ?>
    <a href="<?= ROOTPATH ?>/admin/reports" class="btn primary ">
        <i class="ph ph-edit"></i>
        <?= lang('Edit templates', 'Vorlagen bearbeiten') ?>
    </a>
<?php } ?>

<h1>
    <i class="ph ph-clipboard-text"></i>
    <?= lang('Reports', 'Berichte') ?>
</h1>

<?php
$reports = $osiris->adminReports->find();

if (empty($reports)) {
    echo '<div class="alert alert-info">' . lang('No reports found.', 'Keine Berichte gefunden.') . '</div>';
} else foreach ($reports as $report) { ?>
    <div class="box">
        <div class="content">
            <h3><?= $report['title'] ?></h3>
            <p class="text-primary"><?= $report['description'] ?? '' ?></p>
        </div>
        <hr>
        <div class="content">
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
    </div>
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