<?php

/**
 * Add/edit statistics of a year to an infrastructure
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$year = intval($_GET['year'] ?? CURRENTYEAR);

$statistics = DB::doc2Arr($form['statistics'] ?? []);

$yearstats = array_filter($statistics, function ($stat) use ($year) {
    return $stat['year'] == $year;
});

$yearstats = array_values($yearstats);

if (empty($yearstats)) {
    $yearstats = [
        'year' => $year,
        'internal' => 0,
        'national' => 0,
        'international' => 0,
        'hours' => 0,
        'accesses' => 0
    ];
} else {
    $yearstats = $yearstats[0];
}

?>

<h1>
    <?= lang('Statistics', 'Statistiken') ?>
    <?= $year ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/infrastructures/year/<?= $id ?>" method="post">
    <input type="hidden" name="values[year]" value="<?= $year ?>" />
    <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/infrastructures/view/<?= $id ?>" />

    <div class="form-group">
        <label for="internal"><?= lang('Number of internal users', 'Anzahl interner Nutzer/-innen') ?>
            <span class="badge kdsf">KDSF-B-13-8-B</span>
        </label>
        <input type="number" class="form-control w-auto" name="values[internal]" id="internal" value="<?= $yearstats['internal'] ?>" />
    </div>
    <div class="form-group">
        <label for="national"><?= lang('Number of national users', 'Anzahl nationaler Nutzer/-innen') ?>
            <span class="badge kdsf">KDSF-B-13-8-C</span>
        </label>
        <input type="number" class="form-control w-auto" name="values[national]" id="national" value="<?= $yearstats['national'] ?>" />
    </div>
    <div class="form-group">
        <label for="international"><?= lang('Number of international users', 'Anzahl internationaler Nutzer/-innen') ?>
            <span class="badge kdsf">KDSF-B-13-8-D</span>
        </label>
        <input type="number" class="form-control w-auto" name="values[international]" id="international" value="<?= $yearstats['international'] ?>" />
    </div>

    <div class="form-group">
        <?= lang('Number of hours used', 'Anzahl der genutzten Stunden') ?>
        <span class="badge kdsf">KDSF-B-13-9-B</span>
        </label>
        <input type="number" class="form-control w-auto" name="values[hours]" id="hours" value="<?= $yearstats['hours'] ?>" />
    </div>

    <div class="form-group">
        <label for="accesses">
            <?= lang('Number of accesses', 'Anzahl der Nutzungszugriffe') ?>
            <span class="badge kdsf">KDSF-B-13-10-B</span>
        </label>
        <input type="number" class="form-control w-auto" name="values[accesses]" id="accesses" value="<?= $yearstats['accesses'] ?>" />
    </div>


    <button class="btn btn-primary">
        <i class="ph ph-save"></i>
        <?= lang('Save', 'Speichern') ?>
    </button>
</form>