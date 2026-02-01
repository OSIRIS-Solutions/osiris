<?php

/**
 * The statistics of all infrastructures
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

include_once BASEPATH . '/php/Vocabulary.php';
$Vocabulary = new Vocabulary();


$additionalFields = [];
$fields = $Vocabulary->getVocabulary('infrastructure-stats');
if (empty($fields) || !is_array($fields) || empty($fields['values'])) {
} else {
    $fields = DB::doc2Arr($fields['values']);
    foreach ($fields as $field) {
        if ($field['id'] == 'internal' || $field['id'] == 'national' || $field['id'] == 'international' || $field['id'] == 'hours' || $field['id'] == 'accesses') {
            continue; // skip the default fields
        }
        $additionalFields[] = $field;
    }
}

// today is the default reportdate
if (!isset($_GET['reportdate']) || empty($_GET['reportdate'])) {
    $reportdate = (CURRENTYEAR - 1) . '-12-31'; // default to the last day of the previous year
} else {
    $reportdate = $_GET['reportdate'];
}

$filter = [
    'start_date' => ['$lte' => $reportdate],
    '$or' => [
        ['end_date' => ['$gte' => $reportdate]],
        ['end_date' => null]
    ]
];

$infrastructures  = $osiris->infrastructures->find($filter)->toArray();

$all = $osiris->infrastructures->count();

$year = intval($_GET['year'] ?? CURRENTYEAR - 1);
?>

<style>
    tfoot th {
        font-weight: 400 !important;
        border-top: var(--border-width) solid var(--border-color);
        color: var(--muted-color);
        background-color: var(--gray-color-very-light);
    }

    tfoot th:first-child {
        border-bottom-left-radius: var(--border-radius);
    }

    tfoot th:last-child {
        border-bottom-right-radius: var(--border-radius);
    }

    .description p {
        margin: 0;
    }
</style>

<h1>
    <i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('Statistics', 'Statistiken') ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/infrastructures">
        <i class="ph ph-arrow-left"></i>
        <?= lang('Back to Infrastructures', 'Zurück zu Infrastrukturen') ?>
    </a>
</div>


<div class="alert signal">
    <?= lang('All of the following statistics are based on the reporting date.', 'Alle unten aufgeführten Statistiken basieren auf dem angegebenen Stichtag.') ?>

    <form action="<?= ROOTPATH ?>/infrastructures/statistics" method="get" class="d-flex align-items-baseline mt-10" style="grid-gap: 1rem;">
        <h6 class="mb-0 mt-5"><?= lang('Change Reporting Date', 'Stichtag ändern') ?>:</h6>
        <input type="date" name="reportdate" value="<?= $reportdate ?>" class="form-control w-auto d-inline-block" />
        <h6 class="mb-0 mt-5"><?= lang('Change Year for Statistics', 'Jahr für Statistik ändern') ?>:</h6>
        <input type="number" name="year" value="<?= $year ?>" class="form-control w-100 d-inline-block" />
        <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
    </form>
</div>

<br>
<div id="statistics">
    <p class="lead">
        <?= lang('Number of infrastructures on the reporting date', 'Anzahl der Infrastrukturen zum Stichtag') ?>:
        <b class="badge signal"><?= count($infrastructures) ?></b>
        <span class="text-muted">(<?= $all ?> <?= lang('total', 'gesamt') ?>)</span>
    </p>

    <h3>
        <?= lang('List of research infrastructures', 'Liste bestehender Forschungsinfrastrukturen') ?>
    </h3>
    <table class="table" id="infrastructures">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Category', 'Kategorie') ?></th>
                <th><?= lang('Access Type', 'Art des Zugangs') ?></th>
                <th><?= lang('Type', 'Art') ?></th>
                <th><?= lang('Description', 'Beschreibung') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($infrastructures as $infrastructure) { ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                            <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                        </a>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-access', $infrastructure['access'] ?? '-') ?>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-type', $infrastructure['infrastructure_type'] ?? '-') ?>
                    </td>
                    <td class="font-size-12 description">
                        <?= $infrastructure['description'] ?? '-' ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3>
        <?= lang('Users in', 'Anzahl der Nutzer:innen in') ?> <?= $year ?>
    </h3>

    <table class="table" id="user-stats">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Type', 'Typ') ?></th>
                <th class="text-right"><?= lang('Internal', 'Intern') ?></th>
                <th class="text-right"><?= lang('National', 'National') ?></th>
                <th class="text-right"><?= lang('International', 'International') ?></th>
                <th class="text-right"><?= lang('Total', 'Gesamt') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stats = [
                'internal' => 0,
                'national' => 0,
                'international' => 0
            ];

            // 1) Build $group stage with one accumulator per field
            $group = ['_id' => ['infrastructure' => '$infrastructure']];
            foreach ($fields as $f) {
                $fid = $f['id'];
                // Sum only docs where field == $fid; coerce to number
                $group[$fid] = [
                    '$sum' => [
                        '$cond' => [
                            ['$eq' => ['$field', $fid]],
                            ['$toDouble' => ['$ifNull' => ['$value', 0]]],
                            0
                        ]
                    ]
                ];
            }

            $pipeline = [
                ['$match' => ['year' => intval($year)]],
                ['$group' => $group],
                ['$project' => array_merge(['_id' => 0, 'infrastructure' => '$_id.infrastructure'], array_fill_keys(array_column($fields, 'id'), 1))],
                ['$sort' => ['infrastructure' => -1]]
            ];

            $aggregations = $osiris->infrastructureStats->aggregate($pipeline)->toArray();
            foreach ($aggregations as $yearstats) {
                $infrastructure = $osiris->infrastructures->findOne(['id' => $yearstats['infrastructure']]);
                if (empty($infrastructure)) continue;
                $stats['internal'] += $yearstats['internal'] ?? 0;
                $stats['national'] += $yearstats['national'] ?? 0;
                $stats['international'] += $yearstats['international'] ?? 0;
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                            <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                        </a>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                    </td>
                    <td class="text-right">
                        <?= $yearstats['internal'] ?? 0 ?>
                    </td>
                    <td class="text-right">
                        <?= $yearstats['national'] ?? 0 ?>
                    </td>
                    <td class="text-right">
                        <?= $yearstats['international'] ?? 0 ?>
                    </td>
                    <td class="text-right">
                        <?= ($yearstats['internal'] ?? 0) + ($yearstats['national'] ?? 0) + ($yearstats['international'] ?? 0) ?>
                    </td>
                </tr>
            <?php
            } ?>
        </tbody>
        <tfoot>
            <tr>
                <th><?= lang('Total', 'Gesamt') ?></th>
                <th>-</th>
                <th class="text-right">
                    <?= $stats['internal'] ?>
                </th>
                <th class="text-right">
                    <?= $stats['national'] ?>
                </th>
                <th class="text-right">
                    <?= $stats['international'] ?>
                </th>
                <th class="text-right">
                    <?= $stats['internal'] + $stats['national'] + $stats['international'] ?>
                </th>
            </tr>
        </tfoot>
    </table>


    <h3>
        <?= lang('Usage statistics in', 'Nutzungsstatistiken in') ?> <?= $year ?>
    </h3>


    <table class="table" id="action-stats">
        <thead>
            <tr>
                <th>Name</th>
                <th>Typ</th>
                <th class="text-right">Genutzte Stunden</th>
                <th class="text-right">Zugriffe</th>
                <?php foreach ($additionalFields as $f) { ?>
                    <th class="text-right">
                        <?= lang($f['en'], $f['de'] ?? null) ?>
                    </th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
            <?php
            $stats = [
                'hours' => 0,
                'accesses' => 0
            ];
            foreach ($additionalFields as $f) {
                $stats[$f['id']] = 0;
            }
            foreach ($aggregations as $yearstats) {
                $infrastructure = $osiris->infrastructures->findOne(['id' => $yearstats['infrastructure']]);
                if (empty($infrastructure)) continue;
                $stats['hours'] += $yearstats['hours'] ?? 0;
                $stats['accesses'] += $yearstats['accesses'] ?? 0;
                foreach ($additionalFields as $f) {
                    $stats[$f['id']] += $yearstats[$f['id']] ?? 0;
                }
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                            <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                        </a>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                    </td>
                    <td class="text-right">
                        <?= $yearstats['hours'] ?? 0 ?>
                    </td>
                    <td class="text-right">
                        <?= $yearstats['accesses'] ?? 0 ?>
                    </td>
                    <?php foreach ($additionalFields as $f) { ?>
                        <td class="text-right">
                            <?= $yearstats[$f['id']] ?? 0 ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th><?= lang('Total', 'Gesamt') ?></th>
                <th>-</th>
                <th class="text-right">
                    <?= $stats['hours'] ?>
                </th>
                <th class="text-right">
                    <?= $stats['accesses'] ?>
                </th>
                <?php foreach ($additionalFields as $f) { ?>
                    <th class="text-right">
                        <?= $stats[$f['id']] ?>
                    </th>
                <?php } ?>
            </tr>
        </tfoot>
    </table>


    <h3>
        <?= lang('Personnel statistics', 'Personalstatistiken') ?>
        <?= lang('on the reporting date', 'am Stichtag') ?>
    </h3>

    <table class="table" id="person-stats">
        <thead>
            <tr>
                <th>Name</th>
                <th>Typ</th>
                <th>Umfang (VZÄ)</th>
                <th>
                    <?= lang('Contact person', 'Ansprechpartner') ?>
                </th>
                <th>
                    <?= lang('# Persons', '# Persons') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            $counts = [
                'fte' => 0,
                'total' => 0,
                'head' => 0,
                'persons' => 0
            ];
            foreach ($infrastructures as $infrastructure) {
                $persons = DB::doc2Arr($infrastructure['persons'] ?? []);
                $fte = 0;
                $head = [];
                foreach ($persons as $person) {
                    // check first if person is active at the reporting date
                    if (!empty($person['start']) && $person['start'] > $reportdate) {
                        continue;
                    }
                    if (!empty($person['end']) && $person['end'] < $reportdate) {
                        continue;
                    }
                    $fte += $person['fte'];
                    if ($person['role'] == 'head') {
                        $head[] = $person['name'];
                    }
                }
                $fte = number_format($fte, 2);
                $counts['fte'] += $fte;
                $counts['head'] += count($head);
                $counts['persons'] += count($persons);
                $counts['total'] += 1;
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                            <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                        </a>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                    </td>
                    <td>
                        <?= $fte ?>
                    </td>
                    <td>
                        <?= implode(', ', $head) ?>
                    </td>
                    <td>
                        <?= count($persons) ?>
                    </td>
                </tr>
            <?php
            }
            ?>
        <tfoot>
            <tr>
                <th>
                    <?= lang('Total', 'Gesamt') ?>
                    <?= $counts['total'] ?>
                </th>
                <th>-</th>
                <th>
                    <?= number_format($counts['fte'], 2) ?>
                </th>
                <th>
                    <?= $counts['head'] ?>
                </th>
                <th>
                    <?= $counts['persons'] ?>
                </th>
            </tr>
        </tfoot>
        </tbody>

    </table>
    <br>
    <hr>

    <h2>
        <?= lang('Collaborative research infrastructures', 'Verbundforschungsinfrastrukturen') ?>
    </h2>

    <?php
    $filter_collaborations = $filter;
    $filter_collaborations['collaborative'] = true;
    $collaborations = $osiris->infrastructures->aggregate([
        ['$match' => $filter_collaborations],
        ['$lookup' => [
            'from' => 'organizations',
            'localField' => 'coordinator_organization',
            'foreignField' => '_id',
            'as' => 'coordinator'
        ]]
    ])->toArray();

    $coordinators = array_sum(array_column($collaborations, 'coordinator_institute'));
    $inst = '<b>' . $Settings->get('affiliation') . '</b>';
    ?>

    <table class="table" id="collaborative-general">
        <tbody>
            <tr>
                <td>
                    <?= lang('Number of collaborative infrastructures on the reporting date', 'Anzahl der Verbundinfrastrukturen zum Stichtag') ?>
                    <br>
                    <b class="text-secondary"><?= count($collaborations) ?></b>
                </td>
            </tr>
            <tr>
                <td>
                    <?= lang('Number of collaborative infrastructures with a coordinator', 'Davon Anzahl der Verbundinfrastrukturen mit Koordinator') ?>
                    <br>
                    <b class="text-secondary"><?= $coordinators ?></b>
                </td>
            </tr>
        </tbody>
    </table>


    <h5>
        <?= lang('List of collaborative research infrastructures', 'Liste bestehender Verbundforschungsinfrastrukturen') ?>
    </h5>

    <table class="table" id="collaborations">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Coordinator', 'Koordinator') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($collaborations as $infrastructure) { ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                            <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                        </a>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                    </td>
                    <td>
                        <?php if (empty($infrastructure['coordinator_organization'])) {
                            echo $inst;
                        } else {
                            $coordinator = DB::doc2Arr($infrastructure['coordinator'] ?? []);
                            if (isset($coordinator[0]['name'])) {
                                $coordinator = $coordinator[0];
                                if (isset($coordinator['name'])) {
                                    echo '<a href="' . ROOTPATH . '/organizations/view/' . $coordinator['_id'] . '">' . lang($coordinator['name'], $coordinator['name_de'] ?? null) . '</a>';
                                }
                            } else {
                                echo lang('No coordinator', 'Kein Koordinator');
                            }
                        } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>




    <?php
    $collaborations = $osiris->infrastructures->aggregate([
        ['$match' => $filter_collaborations],
        ['$lookup' => [
            'from' => 'organizations',
            'localField' => 'collaborators',
            'foreignField' => '_id',
            'as' => 'collaborators'
        ]],
        ['$project' => [
            'collaborators' => 1,
            '_id' => 0,
            'name' => 1,
        ]],
        ['$unwind' => '$collaborators'],
        ['$group' => [
            '_id' => '$collaborators._id',
            'name' => ['$first' => '$collaborators.name'],
            'type' => ['$first' => '$collaborators.type'],
            'location' => ['$first' => '$collaborators.location'],
            'count' => ['$sum' => 1],
            'infrastructures' => ['$push' => '$name']
        ]],
        ['$sort' => ['name' => 1]]
    ])->toArray();
    ?>

    <h5>
        <?= lang('Cooperation partners', 'Kooperationspartner') ?>
        (<?= count($collaborations) ?>)
    </h5>

    <table class="table" id="collaborative-partners">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Location', 'Standort') ?></th>
                <th><?= lang('Number of infrastructures', 'Anzahl der Infrastrukturen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($collaborations as $infrastructure) { ?>
                <tr>
                    <td>
                        <?= $infrastructure['name'] ?>
                    </td>
                    <td>
                        <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                    </td>
                    <td>
                        <?= $infrastructure['location'] ?? '-' ?>
                    </td>
                    <td>
                        <?= $infrastructure['count'] ?? '-' ?>
                        <a onclick="$(this).next().toggle()"><i class="ph ph-magnifying-glass-plus"></i></a>
                        <div class="collaborations-list" style="display: none;">
                            <?= implode(', ', DB::doc2Arr($infrastructure['infrastructures'] ?? [])) ?>
                        </div>
                    </td>

                </tr>
            <?php } ?>
        </tbody>
    </table>


</div>





<script>
    function filterByYear(year, table) {
        var rows = document.querySelectorAll(table + ' tbody tr');
        if (year == '') {
            rows.forEach(function(row) {
                row.style.display = 'table-row';
            });
            return;
        }
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells[2].innerText == year) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }

    $(document).ready(function() {
        initDownloadTable('#infrastructures', lang('Infrastructures - Overview', 'Infrastrukturen - Übersicht') + ' <?= $reportdate ?>');
        initDownloadTable('#user-stats', lang('Infrastructures - User Statistics', 'Infrastrukturen - Nutzerstatistiken') + ' <?= $year ?>');
        initDownloadTable('#action-stats', lang('Infrastructures - Usage Statistics', 'Infrastrukturen - Nutzungsstatistiken') + ' <?= $year ?>');
        initDownloadTable('#person-stats', lang('Infrastructures - Personnel Statistics', 'Infrastrukturen - Personalstatistiken') + ' <?= $reportdate ?>');
        initDownloadTable('#collaborations', lang('Infrastructures - Collaborative Infrastructures', 'Infrastrukturen - Verbundinfrastrukturen') + ' <?= $reportdate ?>');
        initDownloadTable('#collaborative-partners', lang('Infrastructures - Collaborative Partners', 'Infrastrukturen - Kooperationspartner') + ' <?= $reportdate ?>');
    });
</script>