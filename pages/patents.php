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

$year = CURRENTYEAR;
if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
}
$start = $year . "-01-01";
$end   = $year . "-12-31";

$filter = [
    'subtype' => 'patents',
    'year' => $year
];

$patents = $osiris->activities->find($filter)->toArray();

$all = $osiris->activities->count(
    [
        'subtype' => 'patents'
    ]
);

$statuses = [
    'preparation' => lang('Preparation', 'Vorbereitung'),
    'in-progress' => lang('Filed', 'Eingereicht'),
    'completed' => lang('Granted', 'Erteilt'),
    'aborted' => lang('Expired', 'Abgelaufen'),
];

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
        <h2><?= lang('Overview', 'Übersicht') ?> <?= $year ?></h2>
        <?php
        $numbers = [];
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= lang('Patent Number', 'Patentnummer') ?></th>
                    <th><?= lang('Title', 'Titel') ?></th>
                    <th><?= lang('File date', 'Anmeldedatum') ?></th>
                    <th><?= lang('Publication date', 'Veröffentlichungsdatum') ?></th>
                    <th><?= lang('End date', 'Enddatum') ?></th>
                    <th><?= lang('Status', 'Status') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($patents as $patent):
                ?>
                    <tr>
                        <td><?= $patent['patent-number'] ?></td>
                        <td><?= $patent['title'] ?></td>
                        <td><?= $patent['start_date'] ?></td>
                        <td><?= $patent['patent-published'] ?></td>
                        <td><?= $patent['end_date'] ?></td>
                        <td><?= $patent['patent-status'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
