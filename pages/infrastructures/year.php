<?php

/**
 * Add/edit statistics of a year to an infrastructure
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$fields = $Vocabulary->getVocabulary('infrastructure-stats');
if (empty($fields) || !is_array($fields) || empty($fields['values'])) {
    $fields = [
        [
            "id"=> "internal",
            "en"=> "Number of internal users",
            "de"=> "Anzahl interner Nutzer/-innen"
        ],
        [
            "id"=> "national",
            "en"=> "Number of national users",
            "de"=> "Anzahl nationaler Nutzer/-innen"
        ],
        [
            "id"=> "international",
            "en"=> "Number of international users",
            "de"=> "Anzahl internationaler Nutzer/-innen"
        ],
        [
            "id"=> "hours",
            "en"=> "Number of hours used",
            "de"=> "Anzahl der genutzten Stunden"
        ],
        [
            "id"=> "accesses",
            "en"=> "Number of accesses",
            "de"=> "Anzahl der Nutzungszugriffe"
        ],
    ];
} else {
    $fields = $fields['values'] ?? [];
}

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
    <?= lang('common.statistics') ?>
    <?= $year ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/infrastructures/year/<?= $id ?>" method="post">
    <input type="hidden" name="values[year]" value="<?= $year ?>" />
    <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/infrastructures/view/<?= $id ?>" />

    <?php foreach ($fields as $key) { ?>
        <div class="form-group">
            <label for="<?= $key['id'] ?>"><?= lang($key['en'], $key['de']) ?>

            </label>
            <input type="number" class="form-control w-auto" name="values[<?= $key['id'] ?>]" id="<?= $key['id'] ?>" value="<?= $yearstats[$key['id']] ?? 0 ?>" />
        </div>
    <?php } ?>


    <button class="btn btn-primary">
        <i class="ph ph-save"></i>
        <?= lang('action.save') ?>
    </button>
</form>