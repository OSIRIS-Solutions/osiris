<?php

/**
 * Page to analyse patents
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /patents
 *
 * @package OSIRIS
 * @since 2.x.x
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$user = $user ?? $_SESSION['username'];

$filter = [
    'subtype' => 'patents',
];

$patents = $osiris->activities->find($filter)->toArray();

$all = $osiris->activities->count(
    [
        'subtype' => 'patents'
    ]
);

$statuses = [
    'preparation' => lang('Preparation', 'Vorbereitung'),
    'filed' => lang('Filed', 'Eingereicht'),
    'granted' => lang('Granted', 'Erteilt'),
    'expired' => lang('Expired', 'Abgelaufen'),
];

$patent_count_table = [];
foreach ($patents as $patent) {
    $status = $patent['patent-status'] ?? 'unknown';
    $year = $patent['year'] ?? null;
    if ($year) {
        if (!isset($patent_count_table[$year])) {
            $patent_count_table[$year] = [];
        }
        if (!isset($patent_count_table[$year][$status])) {
            $patent_count_table[$year][$status] = 0;
        }
        $patent_count_table[$year][$status]++;
        $years[$year] = $year;
    }
}
ksort($years);

?>

<div class="container">
    <h1>
        <i class="ph-duotone ph-airplane"></i>
        <?= lang('Patents', 'Patente') ?>
    </h1>

    <div class="btn-toolbar">
        <a class="btn" href="<?= ROOTPATH ?>/add-activity?type=patents">
            <i class="ph ph-plus-circle"></i>
            <?= lang('Add patent', 'Patent hinzufügen') ?>
        </a>
    </div>

    <div id="statistics">
        <h2><?= lang('Overview', 'Übersicht') ?></h2>

        <?php
        $numbers = [];
        ?>
        <table class="table">
            <thead>
                <th><?= lang('Year', 'Jahr') ?></th>
                <? foreach ($statuses as $status => $label): ?>
                    <th><a href='<?= ROOTPATH ?>/activities/search?expert#?query={"$and":[{"subtype":"patents"},{"patent-status":"<?= $status ?>"}]}'><?= $label ?></a></th>
                <? endforeach; ?>
                <th><?= lang('Total', 'Gesamt') ?></th>
            </thead>
            <tbody>
                <?php foreach ($years as $year): ?>
                <tr>
                    <td><?= $year ?></td>
                    <?php foreach ($statuses as $status => $label): ?>
                        <td><a href='<?= ROOTPATH ?>/activities/search?expert#?query={"$and":[{"subtype":"patents"},{"patent-status":"<?= $status ?>"},{"year":"<?= $year ?>"}]}'><?= $patent_count_table[$year][$status] ?? 0 ?></a></td>
                    <?php endforeach; ?>
                    <td><a href='<?= ROOTPATH ?>/activities/search?expert#?query={"$and":[{"subtype":"patents"},{"year":"<?= $year ?>"}]}'><?= array_sum($patent_count_table[$year] ?? []) ?></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td> <?= lang('Total', 'Gesamt') ?></td>
                    <?php foreach ($statuses as $status => $label): ?>
                        <td><a href='<?= ROOTPATH ?>/activities/search?expert#?query={"$and":[{"subtype":"patents"},{"patent-status":"<?= $status ?>"}]}'><?= array_sum(array_column($patent_count_table, $status)) ?></a></td>
                    <?php endforeach; ?>
                    <td><a href='<?= ROOTPATH ?>/activities/search?expert#?query={"$and":[{"subtype":"patents"}]}'><?= $all ?></a></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
