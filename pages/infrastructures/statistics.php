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

// today is the default reportdate
if (!isset($_GET['reportdate']) || empty($_GET['reportdate'])) {
    $reportdate = date('Y-m-d');
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
?>

<style>
    tfoot th {
        font-weight: 400 !important;
        border-top: 1px solid var(--border-color);
        color: var(--muted-color);
        background-color: var(--gray-color-very-light);
    }

    tfoot th:first-child {
        border-bottom-left-radius: var(--border-radius);
    }

    tfoot th:last-child {
        border-bottom-right-radius: var(--border-radius);
    }
</style>

<h1>
    <i class="ph ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('Statistics', 'Statistiken') ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/infrastructures">
        <i class="ph ph-arrow-left"></i>
        <?= lang('Back to Infrastructures', 'Zurück zu Infrastrukturen') ?>
    </a>
</div>


<div class="alert signal">
    <i class="ph ph-warning text-signal"></i>
    <?= lang('All of the following statistics are based on the reporting date.', 'Alle unten aufgeführten Statistiken basieren auf dem angegebenen Stichtag.') ?>

    <form action="<?= ROOTPATH ?>/infrastructures/statistics" method="get" class="d-flex align-items-baseline mt-10" style="grid-gap: 1rem;">
        <h6 class="mb-0 mt-5"><?= lang('Change Reporting Date', 'Stichtag ändern') ?>:</h6>
        <input type="date" name="reportdate" value="<?= $reportdate ?>" class="form-control w-auto d-inline-block" />
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
                        <?= $infrastructure['type'] ?? '-' ?>
                    </td>
                    <td>
                        <?= $infrastructure['access'] ?? '-' ?>
                    </td>
                    <td>
                        <?= $infrastructure['infrastructure_type'] ?? '-' ?>
                    </td>
                    <td>
                        <?= $infrastructure['description'] ?? '-' ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3>
        <?= lang('Users by year', 'Anzahl der Nutzer:innen nach Jahr') ?>
    </h3>


    <!-- Filter by Year -->
    <div class="d-flex align-items-center mb-5">
        <i class="ph ph-funnel text-primary"></i>
        <span class="px-5"><?= lang('Year', 'Jahr') ?>:</span>
        <input type="number" class="form-control w-100" placeholder="2021" onchange="filterByYear(this.value, '#user-stats')" />
    </div>

    <table class="table" id="user-stats">
        <thead>
            <tr>
                <th><?= lang('Name', 'Name') ?></th>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Year', 'Jahr') ?></th>
                <th class="text-right"><?= lang('Internal', 'Intern') ?></th>
                <th class="text-right"><?= lang('National', 'National') ?></th>
                <th class="text-right"><?= lang('International', 'International') ?></th>
                <th class="text-right"><?= lang('Total', 'Gesamt') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($infrastructures as $infrastructure) {
                $statistics = DB::doc2Arr($infrastructure['statistics'] ?? []);
                if (!empty($statistics)) {
                    usort($statistics, function ($a, $b) {
                        return $a['year'] <=> $b['year'];
                    });
                }
                foreach ($statistics as $yearstats) {
            ?>
                    <tr>
                        <td>
                            <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                                <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                            </a>
                        </td>
                        <td>
                            <?= $infrastructure['type'] ?? '-' ?>
                        </td>
                        <td>
                            <?= $yearstats['year'] ?? $reportdate ?>
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
                }
            } ?>
        </tbody>
    </table>


    <h3>
        <?= lang('Usage statistics by year', 'Nutzungsstatistiken nach Jahr') ?>
    </h3>

    <!-- Filter by Year -->
    <div class="d-flex align-items-center mb-5">
        <i class="ph ph-funnel text-primary"></i>
        <span class="px-5"><?= lang('Year', 'Jahr') ?>:</span>
        <input type="number" class="form-control w-100" placeholder="2021" onchange="filterByYear(this.value, '#action-stats')" />
    </div>

    <table class="table" id="action-stats">
        <thead>
            <tr>
                <th>Name</th>
                <th>Typ</th>
                <th>Jahr</th>
                <th class="text-right">Genutzte Stunden</th>
                <th class="text-right">Zugriffe</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($infrastructures as $infrastructure) {
                $statistics = DB::doc2Arr($infrastructure['statistics'] ?? []);
                if (!empty($statistics)) {
                    usort($statistics, function ($a, $b) {
                        return $a['year'] <=> $b['year'];
                    });
                }
                foreach ($statistics as $yearstats) {
            ?>
                    <tr>
                        <td>
                            <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                                <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
                            </a>
                        </td>
                        <td>
                            <?= $infrastructure['type'] ?? '-' ?>
                        </td>
                        <td>
                            <?= $yearstats['year'] ?? $reportdate ?>
                        </td>
                        <td class="text-right">
                            <?= $yearstats['hours'] ?? 0 ?>
                        </td>
                        <td class="text-right">
                            <?= $yearstats['accesses'] ?? 0 ?>
                        </td>
                    </tr>
            <?php
                }
            } ?>
        </tbody>
    </table>


    <h3>
        <?= lang('Personnel statistics', 'Personalstatistiken') ?>
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
                        <?= $infrastructure['type'] ?? '-' ?>
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
                        <?= $infrastructure['type'] ?? '-' ?>
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
        (<?= count($infrastructures) ?>)
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
                        <?= $infrastructure['type'] ?? '-' ?>
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
</script>