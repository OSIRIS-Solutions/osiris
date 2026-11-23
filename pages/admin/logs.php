<?php

/**
 * Page for admins to see import logs
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/logs
 *
 * @package OSIRIS
 * @since 2.2.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . '/php/MyParsedown.php';
$parsedown = new Parsedown();
?>
<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-tag" aria-hidden="true"></i>
        <?= t('admin.import_logs') ?>
    </h1>

    <p>
        <?= t('admin.in_case_you_run_supported_import_or_other_automated_processes_you') ?>
    </p>

    <!-- load import logs from ./logs -->
    <?php
    // sort by modification time, newest first
    $logFiles = glob(BASEPATH . '/logs/*');
    usort($logFiles, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    if ($logFiles) {
        foreach ($logFiles as $logFile) {
            $fileContents = file_get_contents($logFile);
            // if file is markdown, parse it
            if (pathinfo($logFile, PATHINFO_EXTENSION) === 'md') {
                $fileContents = $parsedown->text($fileContents);
            } else {
                $fileContents = '<pre>' . htmlspecialchars($fileContents) . '</pre>';
            } ?>
            <details class="collapse-panel">
                <summary class="collapse-header justify-content-between d-flex align-items-center">
                    <b><?= basename($logFile) ?></b>
                    <date datetime="<?= date('c', filemtime($logFile)) ?>" class="badge">
                        <?= date('d.m.Y H:i', filemtime($logFile)) ?>
                    </date>
                </summary>
                <div class="collapse-content">
                    <?= $fileContents ?>
                </div>
            </details>
        <?php } ?>
    <?php
    } else {
        echo '<p>' . t('admin.no_import_logs_found') . '</p>';
    }
    ?>
</div>