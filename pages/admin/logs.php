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
        <?= lang('Import Logs', 'Importprotokolle') ?>
    </h1>

    <p>
        <?= lang('In case you run supported import or other automated processes, you can view the logs here.', 'Falls du unterstützte Importe oder andere automatisierte Prozesse ausführst, kannst du die Protokolle hier einsehen.') ?>
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
        echo '<p>' . lang('No import logs found.', 'Keine Importprotokolle gefunden.') . '</p>';
    }
    ?>
</div>