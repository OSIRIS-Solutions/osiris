<?php

/**
 * The detail view of an infrastructure
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
include_once BASEPATH . "/php/Organization.php";


$edit_perm = ($infrastructure['created_by'] == $_SESSION['username'] || $Settings->hasPermission('infrastructures.edit'));

$user_infrastructure = false;
$user_role = null;
$reporter = false;
$persons = DB::doc2Arr($infrastructure['persons'] ?? array());
foreach ($persons as $p) {
    if (strval($p['user']) == $_SESSION['username']) {
        $user_infrastructure = True;
        $user_role = $p['role'];
        $reporter = $p['reporter'] ?? false;
        if ($Settings->hasPermission('infrastructures.edit-own')) $edit_perm = true;

        break;
    }
}

include_once BASEPATH . '/php/Vocabulary.php';
$Vocabulary = new Vocabulary();

$data_fields = $Settings->get('infrastructure-data');
if (!is_null($data_fields)) {
    $data_fields = DB::doc2Arr($data_fields);
} else {
    $fields = file_get_contents(BASEPATH . '/data/infrastructure-fields.json');
    $fields = json_decode($fields, true);

    $data_fields = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data_fields = array_column($data_fields, 'id');
}

$active = function ($field) use ($data_fields) {
    return in_array($field, $data_fields);
};


// $statistics

$stat_frequency = $infrastructure['statistic_frequency'] ?? 'annual';

$statistic_fields = DB::doc2Arr($infrastructure['statistic_fields'] ?? ['internal', 'national', 'international', 'hours', 'accesses']);
$fields = $Vocabulary->getVocabulary('infrastructure-stats');
$fields = DB::doc2Arr($fields['values'] ?? []);
$fields = array_filter($fields, function ($field) use ($statistic_fields) {
    return in_array($field['id'], $statistic_fields);
});
// get statistics ordered by year desc that are in the selected fields
$statistics = $osiris->infrastructureStats->find(
    [
        'infrastructure' => $infrastructure['id'],
        'field' => ['$in' => $statistic_fields]
    ],
    [
        'sort' => ['year' => -1]
    ]
)->toArray();
?>
<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>

<style>
    .inactive {
        color: var(--muted-color);
        opacity: 0.7;
        transition: opacity 0.3s, color 0.3s;

    }

    .inactive:hover {
        opacity: 1;
        color: var(--text-color);
    }
</style>

<div class="infrastructure container">

    <h1 class="title">
        <?= lang($infrastructure['name'], $infrastructure['name_de'] ?? null) ?>
    </h1>

    <h2 class="subtitle">
        <?= lang($infrastructure['subtitle'], $infrastructure['subtitle_de'] ?? null) ?>
    </h2>


    <!-- show research topics -->
    <?php
    $topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
    if ($topicsEnabled) {
        echo $Settings->printTopics($infrastructure['topics'] ?? [], 'mb-20', false);
    }
    ?>

    <p class="font-size-12 text-muted">
        <?= $infrastructure['description'] ?>
    </p>

    <?php if ($edit_perm) { ?>
        <a href="<?= ROOTPATH ?>/infrastructures/edit/<?= $infrastructure['_id'] ?>" class="">
            <i class="ph ph-edit"></i>
            <span><?= lang('Edit', 'Bearbeiten') ?></span>
        </a>
    <?php } ?>
    <table class="table mt-10 small">
        <tr>
            <td>
                <span class="key">ID: </span>
                <?= $infrastructure['id'] ?? '-' ?>
            </td>
        </tr>
        <tr>
            <td>
                <span class="key"><?= lang('Start date', 'Anfangsdatum') ?>: </span>
                <?= format_date($infrastructure['start_date']) ?>
            </td>
        </tr>
        <tr>
            <td>
                <span class="key"><?= lang('End date', 'Enddatum') ?>: </span>
                <?php if (!empty($infrastructure['end_date'])) {
                    echo '<span class="badge signal">' . format_date($infrastructure['end_date']) . '</span>';
                } else {
                    echo '<span class="badge primary">' . lang('Open', 'Offen') . '</span>';
                } ?>
            </td>
        </tr>
        <?php if ($active('type')) { ?>
            <tr>
                <td>
                    <span class="key"><?= lang('Type', 'Typ') ?>: </span>
                    <?= $Vocabulary->getValue('infrastructure-category', $infrastructure['type'] ?? '-') ?>
                </td>
            </tr>
        <?php } ?>
        <?php if ($active('infrastructure_type')) { ?>
            <tr>
                <td>
                    <span class="key"><?= lang('Type of infrastructure', 'Art der Infrastruktur') ?>: </span>
                    <?= $Vocabulary->getValue('infrastructure-type', $infrastructure['infrastructure_type'] ?? '-') ?>
                </td>
            </tr>
        <?php } ?>
        <?php if ($active('access')) { ?>
            <tr>
                <td>
                    <span class="key"><?= lang('User Access', 'Art des Zugangs') ?>: </span>
                    <?= $Vocabulary->getValue('infrastructure-access', $infrastructure['access'] ?? '-') ?>
                </td>
            </tr>
        <?php } ?>
        <?php if ($active('collaborative') && $infrastructure['collaborative'] ?? false) { ?>
            <tr>
                <td>
                    <span class="key"><?= lang('Collaborative infrastructure', 'Verbundinfrastruktur') ?>: </span>
                    <a href="#collaborative" class="badge success"><?= count($infrastructure['collaborators'] ?? []) ?> <?= lang('partners', 'Partner') ?></a>
                </td>
            </tr>
        <?php } ?>

        <?php
        // check if user has custom fields
        $custom_fields = $osiris->adminFields->find()->toArray();
        if (!empty($custom_fields)) {
            foreach ($custom_fields as $field) {
                if ($active($field['id']) && isset($infrastructure[$field['id']])) { ?>
                    <tr>
                        <td>
                            <span class="key"><?= lang($field['name'], $field['name_de'] ?? null) ?></span>
                            <?= $infrastructure[$field['id']] ?>
                        </td>
                    </tr>
        <?php }
            }
        } ?>
    </table>



    <h2>
        <i class="ph ph-users text-primary"></i>
        <?= lang('Operating personnel', 'Betriebspersonal') ?>
        <?php if ($edit_perm) { ?>
            <a href="<?= ROOTPATH ?>/infrastructures/persons/<?= $id ?>" class="font-size-16">
                <i class="ph ph-edit"></i>
                <span class="sr-only"><?= lang('Edit', 'Bearbeiten') ?></span>
            </a>
        <?php } ?>
    </h2>


    <div class="row row-eq-spacing mb-0">

        <?php
        if (empty($persons)) {
        ?>
            <div class="col-md-6">
                <div class="alert primary mb-20">
                    <?= lang('No persons connected.', 'Keine Personen verknüpft.') ?>
                </div>
            </div>
        <?php
        } else foreach ($persons as $person) {
            if (empty($person['user'])) {
                continue;
            }
            $username = strval($person['user']);
            $past = '';
            if ($person['end'] && strtotime($person['end']) < time()) {
                $past = 'inactive';
            }
        ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="d-flex align-items-center box p-10 mt-0 <?= $past ?>">

                    <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                    <div>
                        <h5 class="my-0">
                            <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                                <?= $person['name'] ?? $username ?>
                            </a>
                        </h5>
                        <?= $Infra->getRole($person['role'] ?? '') ?>
                        <?php if ($person['reporter'] ?? false) { ?>
                            <span class="primary ml-5" data-toggle="tooltip" data-title="<?= lang('Reporter', 'Berichterstatter') ?>">
                                <i class="ph ph-clipboard-text"></i>
                            </span>
                        <?php } ?>
                        <br>

                        <?= fromToYear($person['start'], $person['end'] ?? null, true) ?>

                    </div>
                </div>
            </div>
        <?php
        } ?>

    </div>

    <hr>

    <h2>
        <i class="ph ph-book-bookmark text-primary"></i>
        <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
    </h2>

    <small>
        <?= lang('You can connect an activity to an infrastructure on the activity page itself.', 'Du kannst eine Aktivität auf der Aktivitätsseite mit einer Infrastruktur verbinden.') ?>
    </small>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="activities-table">
            <thead>
                <tr>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Activity', 'Aktivität') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
    <script>
        $('#activities-table').DataTable({
            "ajax": {
                "url": ROOTPATH + '/api/all-activities',
                "data": {
                    page: 'activities',
                    display_activities: 'web',
                    filter: {
                        'infrastructures': '<?= $infrastructure['id'] ?>'
                    }
                },
                dataSrc: 'data'
            },
            deferRender: true,
            pageLength: 5,
            columnDefs: [{
                    targets: 0,
                    data: 'icon',
                    // className: 'w-50'
                },
                {
                    targets: 1,
                    data: 'activity'
                },
                {
                    targets: 2,
                    data: 'links',
                    className: 'unbreakable'
                },
                {
                    targets: 3,
                    data: 'search-text',
                    searchable: true,
                    visible: false,
                },
                {
                    targets: 4,
                    data: 'start',
                    searchable: true,
                    visible: false,
                },
            ],
            "order": [
                [4, 'desc'],
            ]
        });
    </script>


    <hr>

    <h2 id="statistics">
        <i class="ph ph-chart-line-up text-primary"></i>
        <?= lang('Statistics', 'Statistiken') ?>
    </h2>

    <?php if ($reporter || $Settings->hasPermission('infrastructures.statistics') || $edit_perm) {

        $kdsf_mapping = [
            'internal' => 'KDSF-B-13-8-B',
            'national' => 'KDSF-B-13-8-C',
            'international' => 'KDSF-B-13-8-D',
            'hours' => 'KDSF-B-13-9-B',
            'accesses' => 'KDSF-B-13-10-B',
        ];

    ?>

        <button type="button" class="btn" id="add-stat-btn" onclick="$('#infra-stat-edit-box').toggleClass('hidden');">
            <i class="ph ph-plus"></i>
            <?= lang('Add ' . $stat_frequency . ' statistics', ucfirst('' . $stat_frequency . ' Statistik hinzufügen')) ?>
        </button>

        <div class="box padded small hidden" id="infra-stat-edit-box">
            <form action="<?= ROOTPATH ?>/crud/infrastructures/stats/<?= $id ?>" method="post">
                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/infrastructures/view/<?= $id ?>" />
                <div class="form-group d-flex align-items-center mr-20 mb-10">
                    <?php
                    switch ($stat_frequency) {
                        case 'annual': ?>
                            <label for="year" class="w-300 font-weight-bold"><?= lang('Year', 'Jahr') ?>:</label>
                            <input type="number" name="year" id="add-stat-year" class="form-control w-200" value="<?= CURRENTYEAR - 1 ?>" min="1900" max="<?= CURRENTYEAR + 1 ?>" />
                        <?php
                            break;
                        case 'monthly': ?>
                            <label for="month" class="w-300 font-weight-bold"><?= lang('Month', 'Monat') ?>:</label>
                            <input type="month" name="month" id="add-stat-month" class="form-control w-200" value="<?= date('Y-m', strtotime('-1 month')) ?>" />
                        <?php
                            break;
                        case 'quarterly':
                        ?>
                            <label for="quarter" class="w-300 font-weight-bold"><?= lang('Quarter', 'Quartal') ?>:</label>
                            <select name="quarter" id="add-stat-quarter" class="form-control w-200">
                                <?php
                                $current_year = date('Y');
                                for ($y = $current_year; $y >= $current_year - 10; $y--) {
                                    for ($q = 1; $q <= 4; $q++) {
                                        $selected = ($y == $current_year && $q == ceil(date('n') / 3) - 1) ? 'selected' : '';
                                        echo "<option value=\"{$y}-Q{$q}\" {$selected}>{$y} - Q{$q}</option>";
                                    }
                                }
                                ?>
                            </select>
                        <?php
                            break;
                        case 'irregularly': ?>
                            <label for="date" class="w-300 font-weight-bold"><?= lang('Date', 'Datum') ?>:</label>
                            <input type="date" name="date" id="add-stat-date" class="form-control w-200" value="<?= date('Y-m-d') ?>" />
                    <?php
                            break;
                    }
                    ?>
                </div>

                <?php foreach ($fields as $key) { ?>
                    <div class="form-group d-flex align-items-center mr-20 mb-10">
                        <label for="<?= $key['id'] ?>" class="w-300 font-weight-bold">
                            <?= lang($key['en'], $key['de']) ?>:
                        </label>
                        <input type="number" class="form-control w-200" name="values[<?= $key['id'] ?>]" id="<?= $key['id'] ?>" value="0" />

                        <?php if (!empty($kdsf_mapping[$key['id']])): ?>
                            <span class="badge kdsf"><?= $kdsf_mapping[$key['id']] ?? '' ?></span>
                        <?php endif; ?>
                    </div>
                <?php } ?>
                <!-- comment -->
                <div class="form-group d-flex align-items-center mr-20 mb-10">
                    <label for="comment" class="w-300 font-weight-bold"><?= lang('Comment', 'Kommentar') ?>:</label>
                    <input type="text" name="comment" id="comment" class="form-control w-400" />
                </div>

                <small class="text-muted">
                    <?= lang('If you fill in statistics for a period that already exists, the existing entry will be overwritten. If the value is 0, the corresponding statistics will be deleted.', 'Wenn du eine Statistik für einen Zeitraum ausfüllst, der bereits existiert, wird der vorhandene Eintrag überschrieben. Wenn der Wert 0 beträgt, wird die entsprechende Statistik gelöscht.') ?>
                </small>
                <br>

                <button class="btn btn-primary">
                    <i class="ph ph-save"></i>
                    <?= lang('Save', 'Speichern') ?>
                </button>
            </form>
        </div>
    <?php } ?>


    <?php if (empty($statistics)) { ?>
        <div class="alert primary my-20 w-md-half">
            <?= lang('No statistics found.', 'Keine Statistiken vorhanden.') ?>
        </div>
    <?php } else {
    ?>
        <a href="#detailed-example-modal" class="btn primary" role="button">
            <i class="ph ph-eye"></i>
            <?= lang('Show detailed statistics', 'Detaillierte Statistiken anzeigen') ?>
        </a>

        <div class="modal" id="detailed-example-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#close-modal" class="close" role="button" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title"><?= lang('Detailed statistics', 'Detaillierte Statistiken') ?></h5>

                    <table class="table" id="detailed-statistics">
                        <thead>
                            <tr>
                                <th><?= lang('Year', 'Jahr') ?></th>
                                <?php if ($stat_frequency == 'monthly') { ?>
                                    <th><?= lang('Month', 'Monat') ?></th>
                                <?php } elseif ($stat_frequency == 'quarterly') { ?>
                                    <th><?= lang('Quarter', 'Quartal') ?></th>
                                <?php } elseif ($stat_frequency == 'irregularly') { ?>
                                    <th><?= lang('Date', 'Datum') ?></th>
                                <?php } ?>
                                <th><?= lang('Field', 'Feld') ?></th>
                                <th class="text-right"><?= lang('Value', 'Wert') ?></th>
                                <th><?= lang('Entered by', 'Eingegeben von') ?></th>
                                <th><?= lang('Comment', 'Kommentar') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $f = array_column($fields, null, 'id');
                            foreach ($statistics as $stat) { ?>
                                <tr>
                                    <th><?= $stat['year'] ?></th>
                                    <?php if ($stat_frequency == 'monthly') { ?>
                                        <td><?= $stat['month'] ?? '' ?></td>
                                    <?php } elseif ($stat_frequency == 'quarterly') { ?>
                                        <td><?= $stat['quarter'] ?? '' ?></td>
                                    <?php } elseif ($stat_frequency == 'irregularly') { ?>
                                        <td><?= $stat['date'] ?? '' ?></td>
                                    <?php } ?>
                                    <td>
                                        <?php
                                        $field = $f[$stat['field']] ?? null;
                                        if ($field) {
                                            echo lang($field['en'], $field['de'] ?? null);
                                        } else {
                                            echo $stat['field'] ?? '';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-right"><?= number_format($stat['value'] ?? 0, 0, ',', '.') ?></td>
                                    <td>
                                        <small class="text-muted">
                                            <?= $stat['created_by'] ?? '' ?>
                                            <?= !empty($stat['updated_by']) ? ' | ' . $stat['updated_by'] : '' ?>
                                        </small>
                                    </td>
                                    <td><?= htmlspecialchars($stat['comment'] ?? '') ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>

                    <script>
                        $('#detailed-statistics').DataTable({
                            deferRender: true,
                            pageLength: 10,
                            "order": [
                                [0, 'desc']
                            ]
                        });
                    </script>

                    <div class="text-right mt-20">
                        <a href="#close-modal" class="btn mr-5" role="button">Close</a>
                    </div>
                </div>
            </div>
        </div>



        <?php

        // 1) Build $group stage with one accumulator per field
        $group = ['_id' => ['year' => '$year']];
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

        // 2) Robust year handling: if "year" is missing (irregular), derive from "date"
        $pipeline = [
            ['$match' => ['infrastructure' => $infrastructure['id']]],
            // sum by year and field
            ['$group' => $group],
            // shape output
            ['$project' => array_merge(['_id' => 0, 'year' => '$_id.year'], array_fill_keys(array_column($fields, 'id'), 1))],
            ['$sort' => ['year' => -1]]
        ];

        $aggregated = $osiris->infrastructureStats->aggregate($pipeline);
        ?>

        <!-- table with all yearly statistics ordered by year -->
        <div class="table-responsive-not">
            <table class="table small my-20" id="yearly-statistics">
                <thead>
                    <tr>
                        <th><?= lang('Year', 'Jahr') ?></th>
                        <?php foreach ($fields as $field) { ?>
                            <th class="text-right"><?= lang($field['en'], $field['de'] ?? null) ?></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($aggregated as $stat) { ?>
                        <tr>
                            <th><?= $stat['year'] ?></th>
                            <?php foreach ($fields as $field) { ?>
                                <td class="text-right"><?= number_format($stat[$field['id']] ?? 0, 0, ',', '.') ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>


        <div class="box">
            <div class="chart content text-center">
                <h5 class="title mb-0">
                    <?= lang('Infrastructure statistics over time', 'Infrastrukturstatistiken im Zeitverlauf') ?>
                </h5>

                <div id="chart-infrastructure-stats"></div>
            </div>
        </div>

        <script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js" charset="utf-8"></script>
        <script>
            // Load & render
            async function loadStats() {
                //   const infra = $('#infra-select').val();
                const infra = '<?= $infrastructure['id'] ?>';

                const params = new URLSearchParams({
                    infrastructure: infra,
                });

                const res = await fetch(`${ROOTPATH}/api/infrastructure/stats?` + params.toString());
                const json = await res.json();
                const traces = json.data || [];

                // Define a color palette (modify hex values as you like)
                let palette = ['#008083', '#f78104', '#63a308', '#B61F29', '#ECAF00', '#06667d', '#b3b3b3'];

                // Apply colors to each trace (cycles through the palette)
                traces.forEach((trace, i) => {
                    trace.line = trace.line || {};
                    // Only override color if not already set by the API
                    trace.line.color = trace.line.color || palette[i % palette.length];

                    // If markers exist, set their color too
                    if (trace.marker) {
                        trace.marker.color = trace.marker.color || trace.line.color;
                    }
                });

                // Use colorway in layout as a fallback/default palette
                Plotly.newPlot('chart-infrastructure-stats', traces, {
                    paper_bgcolor: 'transparent',
                    plot_bgcolor: 'transparent',
                    colorway: palette,
                    // legend to bottom
                    legend: {
                        orientation: 'h',
                        y: -0.2,
                    },
                    height: 300,
                    margin: {
                        t: 25,
                        r: 20,
                        l: 40,
                        b: 20
                    },
                });
            }

            $(document).ready(loadStats);
        </script>
    <?php
    }
    ?>


    <?php if ($active('collaborative') && $infrastructure['collaborative'] ?? false) { ?>
        <hr>
        <div id="collaborative">
            <h2>
                <i class="ph ph-handshake text-primary"></i>
                <?= lang('Collaborative research infrastructure', 'Verbundforschungsinfrastruktur') ?>
            </h2>

            <h5>
                <?= lang('Coordinator', 'Koordinator-Einrichtung') ?>
            </h5>
            <table class="table">

                <tbody>
                    <tr>
                        <td>
                            <?php if ($infrastructure['coordinator_institute']) {
                                $org = $Settings->get('affiliation_details');
                            ?>
                                <div class="d-flex align-items-center">
                                    <span class="badge mr-10 success">
                                        <i class="ph ph-heart ph-fw ph-2x m-0"></i>
                                    </span>
                                    <div>
                                        <b><?= $org['name'] ?></b>
                                        <br>
                                        <?= $org['location'] ?? '' ?>
                                        <?php if (isset($org['ror'])) { ?>
                                            <a href="<?= $org['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                        <?php } ?>
                                        <br>
                                        <small class="text-success"><?= lang('This is your own organization.', 'Dies ist deine eigene Organisation.') ?></small>
                                    </div>
                                </div>

                            <?php } else {
                                $org = $osiris->organizations->findOne(['_id' => $infrastructure['coordinator_organization']]);
                            ?>
                                <div class="d-flex align-items-center">
                                    <span data-toggle="tooltip" data-title="<?= $org['type'] ?>" class="badge mr-10">
                                        <?= Organization::getIcon($org['type'], 'ph-fw ph-2x m-0') ?>
                                    </span>
                                    <div>
                                        <a href="<?= ROOTPATH ?>/organizations/view/<?= $org['_id'] ?>" class="link font-weight-bold colorless">
                                            <?= $org['name'] ?>
                                        </a><br>
                                        <?= $org['location'] ?>
                                        <?php if (isset($org['ror'])) { ?>
                                            <a href="<?= $org['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                        <?php } ?>

                                    </div>
                                </div>
                            <?php } ?>
                        </td>
                    </tr>

                </tbody>
            </table>

            <h5>
                <?= lang('Partners', 'Partner') ?>
            </h5>
            <table class="table">

                <tbody>
                    <?php if (!$infrastructure['coordinator_institute']) {
                        $org = $Settings->get('affiliation_details');
                    ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge mr-10 success">
                                        <i class="ph ph-heart ph-fw ph-2x m-0"></i>
                                    </span>
                                    <div>
                                        <b><?= $org['name'] ?></b>
                                        <br>
                                        <?= $org['location'] ?? '' ?>
                                        <?php if (isset($org['ror'])) { ?>
                                            <a href="<?= $org['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                        <?php } ?>
                                        <br>
                                        <small class="text-success"><?= lang('This is your own organization.', 'Dies ist deine eigene Organisation.') ?></small>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    <?php }
                    ?>
                    <?php if (empty($infrastructure['collaborative'])) { ?>
                        <tr>
                            <td colspan="2">
                                <?= lang('No partners connected.', 'Keine Partner verknüpft.') ?>
                            </td>
                        </tr>
                        <?php } else foreach ($infrastructure['collaborators'] as $org) {

                        $org = $osiris->organizations->findOne(['_id' => $org]);
                        if ($org) { ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span data-toggle="tooltip" data-title="<?= $org['type'] ?>" class="badge mr-10">
                                            <?= Organization::getIcon($org['type'], 'ph-fw ph-2x m-0') ?>
                                        </span>
                                        <div class="">
                                            <a href="<?= ROOTPATH ?>/organizations/view/<?= $org['_id'] ?>" class="link font-weight-bold colorless">
                                                <?= $org['name'] ?>
                                            </a><br>
                                            <?= $org['location'] ?>
                                            <?php if (isset($org['ror'])) { ?>
                                                <a href="<?= $org['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                            <?php } ?>

                                        </div>
                                    </div>
                                </td>
                            </tr>
                    <?php  }
                    }  ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <br>


    <?php if ($Settings->hasPermission('infrastructures.delete')) { ?>

        <button class="btn danger" type="button" id="delete-infrastructure" aria-haspopup="true" aria-expanded="false" onclick="$(this).next().slideToggle()">
            <i class="ph ph-trash"></i>
            <?= lang('Delete', 'Löschen') ?>
            <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
        </button>
        <div aria-labelledby="delete-infrastructure" style="display: none;">
            <div class="my-20">
                <b class="text-danger"><?= lang('Attention', 'Achtung') ?>!</b><br>
                <small>
                    <?= lang(
                        'The infrastructure is permanently deleted and the connection to all associated persons and activities is also removed. This cannot be undone.',
                        'Die Infrastruktur wird permanent gelöscht und auch die Verbindung zu allen zugehörigen Personen und Aktivitäten entfernt. Dies kann nicht rückgängig gemacht werden.'
                    ) ?>
                </small>
                <form action="<?= ROOTPATH ?>/crud/infrastructures/delete/<?= $infrastructure['_id'] ?>" method="post">
                    <button class="btn btn-block danger" type="submit"><?= lang('Delete permanently', 'Permanent löschen') ?></button>
                </form>
            </div>
        </div>
    <?php } ?>


    <?php if (isset($_GET['verbose'])) {
        dump($infrastructure, true);
    } ?>
</div>

<?php if (isset($_GET['edit-stats'])) {
    $timeparam = $_GET['edit-stats'];
?>
    <script>
        // scroll to statistics edit box
        document.getElementById('infra-stat-edit-box').classList.remove('hidden');
        document.getElementById('infra-stat-edit-box').scrollIntoView();
        // prefill time parameters
        <?php
        switch ($stat_frequency) {
            case 'annual':
                echo "document.getElementById('add-stat-year').value = '$timeparam';";
                break;
            case 'monthly':
                echo "document.getElementById('add-stat-month').value = '$timeparam';";
                break;
            case 'quarterly':
                echo "document.getElementById('add-stat-quarter').value = '$timeparam';";
                break;
            case 'irregularly':
                echo "document.getElementById('add-stat-date').value = '$timeparam';";
                break;
        }
        ?>
    </script>
<?php } ?>