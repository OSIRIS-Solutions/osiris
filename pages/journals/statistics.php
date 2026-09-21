<?php

/**
 * Statistics for journals and their linked publications
 *
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @link        /journals/statistics
 *
 * @package     OSIRIS
 * @since       1.6.0
 *
 * @copyright   Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author      Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$label = $Settings->journalLabel();
$phrase = lang('common.in_the_reporting_year');
$time_frame = '';

// Journals do not have a reporting date. The selected period is applied to
// publications and other publication types linked through journal_id.
if (
    !empty($_GET['reportstart'])
    && !empty($_GET['reportend'])
    && DateTime::createFromFormat('Y-m-d', $_GET['reportstart'])
    && DateTime::createFromFormat('Y-m-d', $_GET['reportend'])
) {
    $reportstart = date('Y-m-d', strtotime($_GET['reportstart']));
    $reportend = date('Y-m-d', strtotime($_GET['reportend']));
    $reportyear = intval(date('Y', strtotime($reportend)));
    $phrase = lang('common.in_the_reporting_period');
    $time_frame = lang('common.reporting_period') . ': '
        . date('d.m.Y', strtotime($reportstart)) . ' – '
        . date('d.m.Y', strtotime($reportend));
} else {
    $reportyear = !empty($_GET['reportyear'])
        ? max(1900, min(CURRENTYEAR + 2, intval($_GET['reportyear'])))
        : CURRENTYEAR;
    $reportstart = $reportyear . '-01-01';
    $reportend = $reportyear . '-12-31';
    $time_frame = lang('common.reporting_year') . ': ' . $reportyear;
}

$report_end_year = intval(date('Y', strtotime($reportend)));
$metric_year = $report_end_year;

$publication_filter = [
    // 'type' => 'publication',
    'journal_id' => [
        '$exists' => true,
        '$nin' => ['', null],
    ],
    'start_date' => [
        '$gte' => $reportstart,
        '$lte' => $reportend,
    ],
];

// Group first to keep the amount of data transferred to PHP small. journal_id
// is intentionally stored as a string while journals._id is an ObjectId.
$activity_counts = $osiris->activities->aggregate([
    ['$match' => $publication_filter],
    [
        '$group' => [
            '_id' => '$journal_id',
            'activities' => ['$sum' => 1],
            'publications' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$type', 'publication']],
                        1,
                        0,
                    ],
                ],
            ],
            'affiliated' => [
                '$sum' => [
                    '$cond' => [
                        ['$eq' => ['$affiliated', true]],
                        1,
                        0,
                    ],
                ],
            ],
        ],
    ],
    ['$sort' => ['publications' => -1]],
])->toArray();

$journal_object_ids = [];
foreach ($activity_counts as $row) {
    $journal_id = strval($row['_id'] ?? '');
    if (preg_match('/^[a-f0-9]{24}$/i', $journal_id)) {
        $journal_object_ids[] = $DB->to_ObjectID($journal_id);
    }
}

$journal_documents = [];
if (!empty($journal_object_ids)) {
    $journal_documents = $osiris->journals->find([
        '_id' => ['$in' => $journal_object_ids],
    ])->toArray();
}

$journals_by_id = [];
foreach ($journal_documents as $journal) {
    $journal = DB::doc2Arr($journal);
    $journals_by_id[strval($journal['_id'])] = $journal;
}

$journals = [];
$missing_links = 0;
foreach ($activity_counts as $row) {
    $journal_id = strval($row['_id'] ?? '');
    if (!isset($journals_by_id[$journal_id])) {
        $missing_links += intval($row['publications'] ?? 0);
        continue;
    }

    $journal = $journals_by_id[$journal_id];
    $journal['publication_count'] = intval($row['publications'] ?? 0);
    $journal['activity_count'] = intval($row['activities'] ?? 0) - intval($row['publications'] ?? 0);
    $journal['affiliated_count'] = intval($row['affiliated'] ?? 0);
    $journals[] = $journal;
}

usort($journals, function ($a, $b) {
    return ($b['publication_count'] ?? 0) <=> ($a['publication_count'] ?? 0);
});

$activity_count = array_sum(array_column($journals, 'activity_count'));
$publication_count = array_sum(array_column($journals, 'publication_count'));
$affiliated_count = array_sum(array_column($journals, 'affiliated_count'));
$total_journals = $osiris->journals->count();
$used_journal_count = count($journals);
$average_publications = $used_journal_count > 0
    ? $publication_count / $used_journal_count
    : 0;

// Use one common metric year to keep journal comparisons meaningful. If the
// selected period is newer than the imported metrics, use the latest available
// year up to the end of the reporting period.
$available_metric_years = [];
foreach ($journals as $journal) {
    foreach (DB::doc2Arr($journal['metrics'] ?? []) as $metric) {
        $metric = DB::doc2Arr($metric);
        $year = intval($metric['year'] ?? 0);
        if ($year > 0 && $year <= $report_end_year) {
            $available_metric_years[$year] = true;
        }
    }
    foreach (DB::doc2Arr($journal['impact'] ?? []) as $impact) {
        $impact = DB::doc2Arr($impact);
        $year = intval($impact['year'] ?? 0);
        if ($year > 0 && $year <= $report_end_year) {
            $available_metric_years[$year] = true;
        }
    }
}
if (!empty($available_metric_years)) {
    $metric_year = max(array_keys($available_metric_years));
}

$number = function ($value, $decimals = 0) {
    return number_format(
        floatval($value),
        $decimals,
        lang('journals.text_ff40038939'),
        lang('journals.text_e6d7f55f20')
    );
};

$median = function ($values) {
    $values = array_values(array_filter($values, 'is_numeric'));
    if (empty($values)) return null;

    sort($values, SORT_NUMERIC);
    $count = count($values);
    $middle = intdiv($count, 2);

    if ($count % 2 === 1) return floatval($values[$middle]);
    return (floatval($values[$middle - 1]) + floatval($values[$middle])) / 2;
};

$add_distribution = function (&$distribution, $value, $publication_count = 0) {
    $value = trim(strval($value));
    if ($value === '') $value = lang('common.unknown');

    if (!isset($distribution[$value])) {
        $distribution[$value] = [
            'journals' => 0,
            'publications' => 0,
        ];
    }

    $distribution[$value]['journals']++;
    $distribution[$value]['publications'] += intval($publication_count);
};

$sort_distribution = function (&$distribution) {
    uasort($distribution, function ($a, $b) {
        $by_publications = ($b['publications'] ?? 0) <=> ($a['publications'] ?? 0);
        if ($by_publications !== 0) return $by_publications;
        return ($b['journals'] ?? 0) <=> ($a['journals'] ?? 0);
    });
};

$metric_for_year = function ($journal, $year) {
    foreach (DB::doc2Arr($journal['metrics'] ?? []) as $metric) {
        $metric = DB::doc2Arr($metric);
        if (intval($metric['year'] ?? 0) === intval($year)) {
            return $metric;
        }
    }
    return null;
};

$impact_for_year = function ($journal, $year) use ($metric_for_year) {
    foreach (DB::doc2Arr($journal['impact'] ?? []) as $impact) {
        $impact = DB::doc2Arr($impact);
        if (
            intval($impact['year'] ?? 0) === intval($year)
            && is_numeric($impact['impact'] ?? null)
        ) {
            return floatval($impact['impact']);
        }
    }

    $metric = $metric_for_year($journal, $year);
    if (is_numeric($metric['if_2y'] ?? null)) {
        return floatval($metric['if_2y']);
    }
    return null;
};

$quartile_for_year = function ($journal, $year) use ($metric_for_year) {
    $metric = $metric_for_year($journal, $year);
    $quartile = $metric['quartile'] ?? null;

    if (!empty($quartile)) {
        $quartile = strtoupper(strval($quartile));
        return str_starts_with($quartile, 'Q') ? $quartile : 'Q' . $quartile;
    }

    // Some imports store category-specific quartiles on categories instead of
    // the yearly metric. Use the best available quartile as a fallback.
    $quartiles = [];
    foreach (DB::doc2Arr($journal['categories'] ?? []) as $category) {
        $category = DB::doc2Arr($category);
        if (!is_array($category)) continue;
        $value = strtoupper(strval($category['quartile'] ?? ''));
        $value = intval(str_replace('Q', '', $value));
        if ($value >= 1 && $value <= 4) $quartiles[] = $value;
    }

    if (empty($quartiles)) return null;
    return 'Q' . min($quartiles);
};

$publisher_distribution = [];
$country_distribution = [];
$oa_distribution = [];
$category_distribution = [];
$quartile_distribution = [];
$impact_values = [];
$impact_weighted_sum = 0;
$impact_weight = 0;

foreach ($journals as &$journal) {
    $publications = intval($journal['publication_count'] ?? 0);

    $add_distribution(
        $publisher_distribution,
        $journal['publisher'] ?? '',
        $publications
    );
    $add_distribution(
        $country_distribution,
        $journal['country'] ?? '',
        $publications
    );

    $oa = $journal['oa'] ?? null;
    if ($oa === true || (is_numeric($oa) && intval($oa) <= $report_end_year)) {
        $oa_status = lang('journals.open_access');
    } elseif ($oa === false || (is_numeric($oa) && intval($oa) > $report_end_year)) {
        $oa_status = lang('journals.not_open_access');
    } else {
        $oa_status = lang('common.unknown');
    }
    $add_distribution($oa_distribution, $oa_status, $publications);

    $seen_categories = [];
    foreach (DB::doc2Arr($journal['categories'] ?? []) as $category) {
        $category = DB::doc2Arr($category);
        $category_name = is_array($category)
            ? trim(strval($category['name'] ?? $category[0] ?? ''))
            : trim(strval($category));
        if ($category_name === '' || isset($seen_categories[$category_name])) continue;
        $seen_categories[$category_name] = true;
        $add_distribution($category_distribution, $category_name, $publications);
    }

    $quartile = $quartile_for_year($journal, $metric_year);
    $journal['statistics_quartile'] = $quartile;
    $add_distribution(
        $quartile_distribution,
        $quartile ?? lang('journals.no_quartile'),
        $publications
    );

    $impact = $impact_for_year($journal, $metric_year);
    $journal['statistics_impact'] = $impact;
    if ($impact !== null) {
        $impact_values[] = $impact;
        $impact_weighted_sum += $impact * $publications;
        $impact_weight += $publications;
    }
}
unset($journal);

$sort_distribution($publisher_distribution);
$sort_distribution($country_distribution);
$sort_distribution($oa_distribution);
$sort_distribution($category_distribution);
$sort_distribution($quartile_distribution);

$impact_summary = null;
if (!empty($impact_values)) {
    $impact_summary = [
        'count' => count($impact_values),
        'average' => array_sum($impact_values) / count($impact_values),
        'median' => $median($impact_values),
        'minimum' => min($impact_values),
        'maximum' => max($impact_values),
        'weighted_average' => $impact_weight > 0
            ? $impact_weighted_sum / $impact_weight
            : null,
    ];
}

// Structured custom fields can be analysed without knowing their IDs upfront.
$custom_field_ids = DB::doc2Arr($Settings->get('journal-data') ?? []);
$custom_field_definitions = [];
if (!empty($custom_field_ids)) {
    foreach (
        $osiris->adminFields->find([
            'id' => ['$in' => $custom_field_ids],
        ]) as $field
    ) {
        $field = DB::doc2Arr($field);
        $custom_field_definitions[$field['id']] = $field;
    }
}

$numeric_custom_fields = [];
$categorical_custom_fields = [];

foreach ($custom_field_ids as $field_id) {
    if (!isset($custom_field_definitions[$field_id])) continue;

    $field = $custom_field_definitions[$field_id];
    $format = $field['format'] ?? 'string';
    $field_name = lang($field['name'] ?? $field_id, $field['name_de'] ?? null);

    if (in_array($format, ['int', 'float'])) {
        $values = [];
        $points = [];
        $publications_with_value = 0;

        foreach ($journals as $journal) {
            if (!is_numeric($journal[$field_id] ?? null)) continue;
            $value = floatval($journal[$field_id]);
            $values[] = $value;
            $points[] = [
                'journal' => $journal['abbr'] ?? $journal['journal'] ?? '',
                'value' => $value,
            ];
            $publications_with_value += intval($journal['publication_count'] ?? 0);
        }

        if (!empty($values)) {
            $numeric_custom_fields[] = [
                'id' => $field_id,
                'name' => $field_name,
                'format' => $format,
                'count' => count($values),
                'publications' => $publications_with_value,
                'average' => array_sum($values) / count($values),
                'median' => $median($values),
                'minimum' => min($values),
                'maximum' => max($values),
                'points' => $points,
            ];
        }
        continue;
    }

    if (!in_array($format, ['string', 'list', 'str-list', 'date', 'bool', 'bool-check'])) continue;

    $distribution = [];
    foreach ($journals as $journal) {
        if (!array_key_exists($field_id, $journal)) continue;
        $value = $journal[$field_id];

        if (in_array($format, ['bool', 'bool-check'])) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN)
                ? lang('common.yes')
                : lang('common.no');
        }

        $values = DB::doc2Arr($value);
        if (!is_array($values)) $values = [$values];
        $values = array_filter($values, function ($item) {
            return !is_array($item) && !is_object($item);
        });
        foreach (array_unique($values) as $item) {
            $add_distribution(
                $distribution,
                $item,
                intval($journal['publication_count'] ?? 0)
            );
        }
    }

    if (!empty($distribution)) {
        $sort_distribution($distribution);
        $categorical_custom_fields[] = [
            'id' => $field_id,
            'name' => $field_name,
            'distribution' => $distribution,
        ];
    }
}

$render_custom_field_value = function ($journal, $field) use ($number) {
    $field_id = $field['id'];
    $format = $field['format'] ?? 'string';

    if (!array_key_exists($field_id, $journal) || $journal[$field_id] === null) {
        return [
            'display' => '<span class="text-muted">–</span>',
            'order' => '',
        ];
    }

    $value = DB::doc2Arr($journal[$field_id]);

    if (in_array($format, ['int', 'float']) && is_numeric($value)) {
        return [
            'display' => $number($value, $format === 'int' ? 0 : 2),
            'order' => floatval($value),
        ];
    }

    if (in_array($format, ['bool', 'bool-check'])) {
        $enabled = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        return [
            'display' => $enabled
                ? '<span class="text-success">' . lang('common.yes') . '</span>'
                : '<span class="text-danger">' . lang('common.no') . '</span>',
            'order' => $enabled ? 1 : 0,
        ];
    }

    if (is_array($value)) {
        $items = array_filter($value, function ($item) {
            return !is_array($item) && !is_object($item) && strval($item) !== '';
        });
        $items = array_map(function ($item) {
            return e(strval($item));
        }, array_unique($items));

        return [
            'display' => empty($items)
                ? '<span class="text-muted">–</span>'
                : implode('<br>', $items),
            'order' => implode(', ', array_map('strip_tags', $items)),
        ];
    }

    $value = strval($value);
    if ($value === '') {
        return [
            'display' => '<span class="text-muted">–</span>',
            'order' => '',
        ];
    }

    if ($format === 'date' && strtotime($value) !== false) {
        return [
            'display' => e(date('d.m.Y', strtotime($value))),
            'order' => date('Y-m-d', strtotime($value)),
        ];
    }

    if ($format === 'url') {
        return [
            'display' => '<a href="' . e($value) . '" target="_blank" rel="noopener noreferrer">'
                . e($value) . '</a>',
            'order' => $value,
        ];
    }

    $plain_value = trim(strip_tags($value));
    return [
        'display' => e($plain_value),
        'order' => $plain_value,
    ];
};

$top_journals = array_slice($journals, 0, 15);
$top_journal_chart = array_reverse(array_map(function ($journal) {
    return [
        'name' => $journal['abbr'] ?? $journal['journal'] ?? '',
        'count' => intval($journal['publication_count'] ?? 0),
    ];
}, $top_journals));

$oa_chart = [];
foreach ($oa_distribution as $name => $counts) {
    $oa_chart[] = [
        'name' => $name,
        'count' => intval($counts['publications'] ?? 0),
    ];
}
?>

<style>
    .journal-stat-card {
        height: 100%;
        text-align: center;
    }

    .journal-stat-card .value {
        display: block;
        margin-bottom: .5rem;
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        color: var(--primary-color);
    }

    .journal-stat-card .label {
        color: var(--muted-color);
    }

    .journal-chart {
        min-height: 420px;
    }

    tfoot th {
        font-weight: 400 !important;
        border-top: var(--border-width) solid var(--border-color);
        color: var(--muted-color);
        background-color: var(--gray-color-very-light);
    }
</style>

<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>

<h1>
    <i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i>
    <?= lang('journals.label_statistics', replace: ['label' => $label]) ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/journals">
        <i class="ph ph-arrow-left"></i>
        <?= lang('journals.back_to_label', replace: ['label' => $label]) ?>
    </a>
</div>

<div class="alert signal">
    <i class="ph ph-info text-signal"></i>
    <?= lang('journals.publications_from_the_selected_reporting_period_are_combined_with_the_curre') ?>

    <div class="row position-relative mt-10">
        <div class="col-sm p-10">
            <form action="<?= ROOTPATH ?>/journals/statistics" method="get" class="d-flex align-items-baseline" style="grid-gap: 1rem;">
                <h6 class="m-0"><?= lang('common.change_reporting_year') ?>:</h6>
                <input type="number" name="reportyear" value="<?= $reportyear ?>" class="form-control w-auto d-inline-block" step="1" min="1900" max="<?= CURRENTYEAR + 2 ?>" required>
                <button class="btn signal filled" type="submit"><?= lang('action.update') ?></button>
            </form>
        </div>

        <div class="text-divider"><?= lang('common.or') ?></div>

        <div class="col-sm p-10">
            <form action="<?= ROOTPATH ?>/journals/statistics" method="get" class="d-flex align-items-baseline ml-20" style="grid-gap: 1rem;">
                <h6 class="m-0"><?= lang('common.change_reporting_period') ?>:</h6>
                <input type="date" name="reportstart" value="<?= $reportstart ?>" class="form-control w-auto d-inline-block" required>
                <input type="date" name="reportend" value="<?= $reportend ?>" class="form-control w-auto d-inline-block" required>
                <button class="btn signal filled" type="submit"><?= lang('action.update') ?></button>
            </form>
        </div>
    </div>
</div>

<style>
    .tiles .tile .value {
        font-size: 2.4rem;
        font-weight: 700;
        line-height: 1;
        color: var(--primary-color);
    }
</style>
<div id="statistics">
    <h2 class="text-decoration-underline"><?= $time_frame ?></h2>

    <div class="tiles">
        <div class="tile">
            <span class="value"><?= $number($publication_count) ?></span>
            <span class="label">
                <?= lang('journals.linked_publications') ?>
            <small><?=lang('journals.with')?><?= $number($affiliated_count) ?> <?= lang('common.affiliated') ?></small>
            </span>
        </div>
        <div class="tile">
            <span class="value"><?= $number($used_journal_count) ?></span>
            <span class="label">
                <?= lang('journals.label_used', replace: ['label' => $label]) ?>
                <small class="d-block">(<?= $number($total_journals) ?> <?= lang('common.total') ?>)</small>
            </span>
        </div>
        <div class="tile">
            <span class="value"><?= $number($average_publications, 1) ?></span>
            <span class="label"><?= lang('journals.publications_per_label', replace: ['label' => $label]) ?></span>
        </div>
        <div class="tile">
            <span class="value"><?= $number($activity_count) ?></span>
            <span class="label"><?= lang('journals.other_activity_types') ?></span>
        </div>
    </div>

    <?php if ($missing_links > 0) { ?>
        <p class="alert warning my-20">
            <?= lang('journals.missing_links_publications_reference_a_journal_that_no_longer_exists', replace: ['missing_links' => $missing_links]) ?>
        </p>
    <?php } ?>

    <?php if (empty($journals)) { ?>
        <p class="alert signal">
            <?= lang('journals.no_publications_linked_to_journals_were_found_for_this_reporting_period') ?>
        </p>
    <?php } else { ?>

        <h2><?= lang('journals.most_frequently_used_label', replace: ['label' => $label]) ?></h2>

        <div class="row row-eq-spacing">
            <div class="col-lg-7">
                <table class="table" id="journal-ranking-table">
                    <thead>
                        <tr>
                            <th><?= $label ?></th>
                            <th><?= lang('journals.publisher') ?></th>
                            <th><?= lang('journals.open_access') ?></th>
                            <th><?= lang('common.publications') ?></th>
                            <th><?= lang('common.other_activities_statistics') ?></th>
                            <th><?= lang('common.affiliated') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($journals as $journal) { ?>
                            <tr>
                                <td>
                                    <a href="<?= ROOTPATH ?>/journal/view/<?= e(strval($journal['_id'])) ?>">
                                        <b><?= e($journal['abbr'] ?? $journal['journal'] ?? '-') ?></b>
                                    </a>
                                    <?php if (!empty($journal['abbr']) && $journal['abbr'] !== ($journal['journal'] ?? null)) { ?>
                                        <small class="d-block text-muted"><?= e($journal['journal']) ?></small>
                                    <?php } ?>
                                </td>
                                <td><?= e($journal['publisher'] ?? '-') ?></td>
                                <td>
                                    <?php
                                    $oa = $journal['oa'] ?? null;
                                    if ($oa === true || (is_numeric($oa) && intval($oa) <= $report_end_year)) {
                                        echo lang('common.yes');
                                    } elseif ($oa === false || (is_numeric($oa) && intval($oa) > $report_end_year)) {
                                        echo lang('common.no');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <th><?= $number($journal['publication_count'] ?? 0) ?></th>
                                <th><?= $number($journal['activity_count'] ?? 0) ?></th>
                                <td><?= $number($journal['affiliated_count'] ?? 0) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-5">
                <div id="top-journals-chart" class="box p-5 m-0 journal-chart"></div>
            </div>
        </div>

        <br>
        <hr>

        <h2><?= lang('journals.journal_characteristics') ?></h2>

        <div class="row row-eq-spacing">
            <div class="col-lg-6">
                <h3><?= lang('journals.open_access') ?></h3>
                <table class="table w-auto" id="journal-oa-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.status') ?></th>
                            <th><?= $label ?></th>
                            <th><?= lang('common.publications') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($oa_distribution as $name => $counts) { ?>
                            <tr>
                                <td><?= e($name) ?></td>
                                <td><?= $number($counts['journals']) ?></td>
                                <td><?= $number($counts['publications']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-6">
                <div id="oa-chart" class="box p-5 m-0 journal-chart"></div>
            </div>
        </div>

        <div class="row row-eq-spacing">
            <div class="col-lg-6">
                <h3><?= lang('journals.publishers') ?></h3>
                <table class="table" id="journal-publisher-table">
                    <thead>
                        <tr>
                            <th><?= lang('journals.publisher') ?></th>
                            <th><?= $label ?></th>
                            <th><?= lang('common.publications') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($publisher_distribution as $name => $counts) { ?>
                            <tr>
                                <td><?= e($name) ?></td>
                                <td><?= $number($counts['journals']) ?></td>
                                <td><?= $number($counts['publications']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-6">
                <h3><?= lang('common.countries') ?></h3>
                <table class="table" id="journal-country-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.country') ?></th>
                            <th><?= $label ?></th>
                            <th><?= lang('common.publications') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($country_distribution as $name => $counts) { ?>
                            <tr>
                                <td><?= e($name) ?></td>
                                <td><?= $number($counts['journals']) ?></td>
                                <td><?= $number($counts['publications']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (!empty($category_distribution)) { ?>
            <h3><?= lang('common.categories') ?></h3>
            <p class="text-muted">
                <?= lang('journals.a_journal_may_occur_in_several_categories_the_totals_can_therefore_exceed_t') ?>
            </p>
            <table class="table" id="journal-category-table">
                <thead>
                    <tr>
                        <th><?= lang('common.category') ?></th>
                        <th><?= $label ?></th>
                        <th><?= lang('common.publications') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($category_distribution as $name => $counts) { ?>
                        <tr>
                            <td><?= e($name) ?></td>
                            <td><?= $number($counts['journals']) ?></td>
                            <td><?= $number($counts['publications']) ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>

        <br>
        <hr>

        <h2>
            <?= lang('journals.journal_metrics') ?>
            <small class="text-muted"><?= $metric_year ?></small>
        </h2>
        <p class="text-muted">
            <?= lang('journals.metrics_are_evaluated_for_metric_year_missing_values_are_not_included_in_av', replace: ['metric_year' => $metric_year]) ?>
        </p>

        <div class="row row-eq-spacing">
            <div class="col-lg-6">
                <h3><?= lang('common.quartiles') ?></h3>
                <table class="table w-auto" id="journal-quartile-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.quartile') ?></th>
                            <th><?= $label ?></th>
                            <th><?= lang('common.publications') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quartile_distribution as $quartile => $counts) { ?>
                            <tr>
                                <td><?= e($quartile) ?></td>
                                <td><?= $number($counts['journals']) ?></td>
                                <td><?= $number($counts['publications']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <div class="col-lg-6">
                <?php
                    $if_label = $Settings->impactLabel();
                ?>
                
                <h3><?= $if_label ?></h3>
                <?php if ($impact_summary === null) { ?>
                    <p class="alert secondary">
                        <?= lang('journals.no_if_label_values_are_available_for_metric_year', replace: ['if_label' => $if_label, 'metric_year' => $metric_year]) ?>
                        <!-- TODO: change name of metric here later -->
                    </p>
                <?php } else { ?>
                    <table class="table w-auto" id="journal-impact-table">
                        <tbody>
                            <tr>
                                <td><?= lang('journals.label_with_value', replace: ['label' => $label]) ?></td>
                                <th><?= $number($impact_summary['count']) ?></th>
                            </tr>
                            <tr>
                                <td><?= lang('journals.average_per_journal') ?></td>
                                <th><?= $number($impact_summary['average'], 2) ?></th>
                            </tr>
                            <tr>
                                <td><?= lang('common.median') ?></td>
                                <th><?= $number($impact_summary['median'], 2) ?></th>
                            </tr>
                            <tr>
                                <td><?= lang('journals.publication_weighted_average') ?></td>
                                <th><?= $number($impact_summary['weighted_average'], 2) ?></th>
                            </tr>
                            <tr>
                                <td><?= lang('journals.range') ?></td>
                                <th>
                                    <?= $number($impact_summary['minimum'], 2) ?>
                                    –
                                    <?= $number($impact_summary['maximum'], 2) ?>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                <?php } ?>
            </div>
        </div>

        <?php if (!empty($custom_field_definitions)) { ?>
            <br>
            <hr>

            <h2><?= lang('common.custom_fields') ?></h2>
            <p class="text-muted">
                <?= lang('journals.the_table_contains_the_current_custom_field_values_of_all_journals_used_in') ?>
            </p>

            <div class="overflow-x-auto">
                <table class="table" id="journal-custom-fields-table">
                    <thead>
                        <tr>
                            <th><?= $label ?></th>
                            <th><?= lang('common.publications') ?></th>
                            <?php foreach ($custom_field_ids as $field_id) {
                                if (!isset($custom_field_definitions[$field_id])) continue;
                                $field = $custom_field_definitions[$field_id];
                            ?>
                                <th>
                                    <?= e(lang($field['name'] ?? $field_id, $field['name_de'] ?? null)) ?>
                                    <small class="d-block text-muted"><?= e($field['format'] ?? 'string') ?></small>
                                </th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($journals as $journal) { ?>
                            <tr>
                                <td>
                                    <a href="<?= ROOTPATH ?>/journal/view/<?= e(strval($journal['_id'])) ?>">
                                        <b><?= e($journal['abbr'] ?? $journal['journal'] ?? '-') ?></b>
                                    </a>
                                    <?php if (!empty($journal['abbr']) && $journal['abbr'] !== ($journal['journal'] ?? null)) { ?>
                                        <small class="d-block text-muted"><?= e($journal['journal']) ?></small>
                                    <?php } ?>
                                </td>
                                <td data-order="<?= intval($journal['publication_count'] ?? 0) ?>">
                                    <?= $number($journal['publication_count'] ?? 0) ?>
                                </td>
                                <?php foreach ($custom_field_ids as $field_id) {
                                    if (!isset($custom_field_definitions[$field_id])) continue;
                                    $field = $custom_field_definitions[$field_id];
                                    $rendered = $render_custom_field_value($journal, $field);
                                ?>
                                    <td data-order="<?= e(strval($rendered['order'])) ?>">
                                        <?= $rendered['display'] ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($numeric_custom_fields)) { ?>
                <h3><?= lang('journals.distribution_of_numeric_fields') ?></h3>
                <p class="text-muted">
                    <?= lang('journals.each_point_represents_one_journal_values_from_fields_with_different_units_s') ?>
                </p>
                <div id="journal-custom-numeric-chart" class="box p-5 m-0 journal-chart"></div>

                <h3><?= lang('journals.summary_of_numeric_fields') ?></h3>
                <table class="table" id="journal-custom-numeric-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.field') ?></th>
                            <th><?= $label ?></th>
                            <th><?= lang('journals.linked_publications') ?></th>
                            <th><?= lang('journals.minimum') ?></th>
                            <th><?= lang('common.median') ?></th>
                            <th><?= lang('journals.average') ?></th>
                            <th><?= lang('journals.maximum') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($numeric_custom_fields as $field) { ?>
                            <tr>
                                <td><?= e($field['name']) ?></td>
                                <td><?= $number($field['count']) ?></td>
                                <td><?= $number($field['publications']) ?></td>
                                <td><?= $number($field['minimum'], $field['format'] === 'int' ? 0 : 2) ?></td>
                                <td><?= $number($field['median'], $field['format'] === 'int' ? 1 : 2) ?></td>
                                <td><?= $number($field['average'], $field['format'] === 'int' ? 1 : 2) ?></td>
                                <td><?= $number($field['maximum'], $field['format'] === 'int' ? 0 : 2) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

            <div class="row row-eq-spacing">

                <?php foreach ($categorical_custom_fields as $field) { ?>
                    <div class="col-md-6">
                        <h3><?= e($field['name']) ?></h3>
                        <table class="table journal-custom-categorical-table">
                            <thead>
                                <tr>
                                    <th><?= lang('common.value') ?></th>
                                    <th><?= $label ?></th>
                                    <th><?= lang('common.publications') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($field['distribution'] as $value => $counts) { ?>
                                    <tr>
                                        <td><?= e($value) ?></td>
                                        <td><?= $number($counts['journals']) ?></td>
                                        <td><?= $number($counts['publications']) ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>

<?php if (!empty($journals)) { ?>
    <script>
        $(document).ready(function() {
            $('#journal-ranking-table').DataTable({
                buttons: downloadTableButtons('<?= lang('journals.journal_ranking') . $time_frame ?>'),
                pageLength: 5,
                order: [
                    [3, 'desc']
                ],
            });

            if ($('#journal-custom-fields-table').length) {
                $('#journal-custom-fields-table').DataTable({
                    buttons: downloadTableButtons('<?= lang('journals.journal_custom_fields') . $time_frame ?>'),
                    pageLength: 10,
                    order: [
                        [1, 'desc']
                    ],
                    // scrollX: true,
                    responsive: false,
                });
            }

            if ($('#journal-custom-numeric-table').length) {
                $('#journal-custom-numeric-table').DataTable({
                    buttons: downloadTableButtons('<?= lang('journals.journal_custom_numeric_fields') . $time_frame ?>'),
                    paging: false,
                    searching: false,
                    info: false,
                    order: [
                        [0, 'asc']
                    ],
                });
            }

            $('.journal-custom-categorical-table').each(function() {
                $(this).DataTable({
                    buttons: downloadTableButtons('<?= lang('journals.journal_custom_categorical_field') . $time_frame ?>'),
                    pageLength: 10,
                    order: [
                        [2, 'desc']
                    ],
                });
            });

            [
                '#journal-publisher-table',
                '#journal-country-table',
                '#journal-category-table'
            ].forEach(function(selector) {
                if ($(selector).length) {
                    $(selector).DataTable({
                        buttons: downloadTableButtons('<?= lang('journals.journal_distribution') . $time_frame ?>'),
                        pageLength: 10,
                        order: [
                            [2, 'desc']
                        ],
                    });
                }
            });

            var topJournals = <?= json_encode($top_journal_chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            Plotly.newPlot('top-journals-chart', [{
                type: 'bar',
                orientation: 'h',
                x: topJournals.map(row => row.count),
                y: topJournals.map(row => row.name),
                marker: {
                    color: '#008084'
                },
                hovertemplate: '%{y}: %{x}<extra></extra>'
            }], {
                title: {
                    text: <?= json_encode(lang('journals.top_15_journals'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                },
                margin: {
                    t: 60,
                    r: 20,
                    b: 50,
                    l: 170
                },
                xaxis: {
                    title: lang('common.publications'),
                    rangemode: 'tozero'
                },
                height: 520
            }, {
                displayModeBar: false,
                responsive: true
            });

            var openAccess = <?= json_encode($oa_chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            Plotly.newPlot('oa-chart', [{
                type: 'pie',
                values: openAccess.map(row => row.count),
                labels: openAccess.map(row => row.name),
                textinfo: 'label+percent',
                hole: .45,
                marker: {
                    colors: ['#008084', '#ED6962', '#B6B6B6']
                }
            }], {
                title: {
                    text: <?= json_encode(lang('journals.publications_by_open_access_status'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                },
                margin: {
                    t: 60,
                    r: 20,
                    b: 20,
                    l: 20
                },
                height: 420,
                showlegend: false
            }, {
                displayModeBar: false,
                responsive: true
            });

            <?php if (!empty($numeric_custom_fields)) { ?>
                var numericCustomFields = <?= json_encode(array_map(function ($field) {
                                                return [
                                                    'name' => $field['name'],
                                                    'points' => $field['points'],
                                                ];
                                            }, $numeric_custom_fields), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

                Plotly.newPlot('journal-custom-numeric-chart', numericCustomFields.map(field => ({
                    type: 'box',
                    orientation: 'h',
                    name: field.name,
                    x: field.points.map(point => point.value),
                    text: field.points.map(point => point.journal),
                    boxpoints: 'all',
                    jitter: .35,
                    pointpos: 0,
                    marker: {
                        color: '#008084',
                        size: 7
                    },
                    hovertemplate: '%{text}<br>%{x}<extra>%{fullData.name}</extra>'
                })), {
                    margin: {
                        t: 30,
                        r: 30,
                        b: 60,
                        l: 220
                    },
                    height: Math.max(350, numericCustomFields.length * 90 + 120),
                    xaxis: {
                        title: lang('common.value'),
                        rangemode: 'tozero'
                    },
                    showlegend: false
                }, {
                    displayModeBar: false,
                    responsive: true
                });
            <?php } ?>
        });
    </script>
<?php } ?>