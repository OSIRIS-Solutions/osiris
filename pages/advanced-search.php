<?php

/**
 * Page to perform advanced activity search
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activities/search
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document(true);
$expert = isset($_GET['expert']);

$preset_query = null;
if (isset($_GET['query'])) {
    $preset_query = $osiris->queries->findOne(['_id' => DB::to_ObjectID($_GET['query'])]);
    if ($preset_query) {
        $expert = $preset_query['expert'] ?? false;
    }
}

$defaultColumns = ['id', 'name', 'title'];
$defaultFilter = 'type';
$expertQuery = '{"type": "publication"}';
if ($collection == 'projects' || $collection == 'proposals') {
    include_once BASEPATH . "/php/project_fields.php";
    $FIELDS = new ProjectFields($collection);
    $defaultColumns = ['type', 'name', 'title'];
    $expertQuery = '{"funder": "EU"}';
} elseif ($collection == 'conferences') {
    include_once BASEPATH . "/php/event_fields.php";
    $FIELDS = new EventFields();
    $defaultColumns = ['id', 'title', 'start', 'location'];
    $defaultFilter = 'type';
    $expertQuery = '{"type": "international"}';
} elseif ($collection == 'journals') {
    include_once BASEPATH . "/php/journal_fields.php";
    $FIELDS = new JournalFields();
    $defaultColumns = ['id', 'journal', 'issn', 'publisher'];
    $defaultFilter = 'oa';
    $expertQuery = '{}';
} elseif ($collection == 'persons') {
    include_once BASEPATH . "/php/person_fields.php";
    $FIELDS = new PersonFields();
    $defaultColumns = ['id', 'username', 'first', 'last'];
    $defaultFilter = 'is_active';
    $expertQuery = '{"is_active": true}';
} else {
    include_once BASEPATH . "/php/activity_fields.php";
    $FIELDS = new ActivityFields();
    $defaultColumns = ['id', 'icon', 'web', 'year'];
}

$field_by_id = array_column($FIELDS->fields, null, 'id');
$aggregation_function_labels = [
    'count' => lang('common.count'),
    'sum' => lang('common.sum'),
    'mean' => lang('search.mean'),
    'median' => lang('common.median')
];

$filters = array_filter($FIELDS->fields, function ($f) {
    return in_array('filter', $f['usage'] ?? []);
});

// convert into valid query-builder format
$filters = array_map(function ($f) {
    if (!isset($f['type'])) {
        dump($f);
    }
    if ($f['type'] == 'boolean') {
        $f['input'] = 'radio';
        $f['values'] = ['true' => lang('common.yes'), 'false' => lang('common.no')];
    }
    if ($f['type'] == 'list') {
        $f['type'] = 'string';
    }
    if (isset($f['usage'])) {
        unset($f['usage']);
    }
    return $f;
}, $filters);


function printRules($rules)
{
    $operators = [
        'equal' =>  '=',
        'not_equal' =>  '!=',
        'less' =>  '<',
        'less_or_equal' =>  '<=',
        'greater' =>  '>',
        'greater_or_equal' =>  '>=',
        'contains' => 'CONTAINS',
        'not_contains' => 'NOT CONTAINS',
        'begins_with' => 'BEGINS WITH',
        'ends_with' => 'ENDS WITH',
        'between' => 'BETWEEN',
        'not_between' => 'NOT BETWEEN',
        'is_empty' => 'IS EMPTY',
        'is_not_empty' => 'IS NOT EMPTY',
        'is_null' => 'IS NULL',
        'is_not_null' => 'IS NOT NULL',
        'in' => 'IN',
        'not_in' => 'NOT IN',
    ];
    foreach ($rules['rules'] as $key) {
        if (isset($key['id'])) {
            echo '<li><b class="text-primary">' . $key['id'] . '</b> <code class="code">' . ($operators[$key['operator']] ?? $key['operator']) . '</code> ';
            if (!empty($key['value'] ?? null)) {
                if (is_array($key['value'])) {
                    echo '<q>' . implode(', ', $key['value']) . '</q>';
                } else {
                    echo '<q>' . $key['value'] . '</q>';
                }
            }
            echo '</li>';
        } elseif (isset($key['rules'])) {
            echo '<li>';
            if (isset($key['condition'])) {
                echo '<b class="text-secondary">' . strtoupper($key['condition']) . '</b>';
            } else {
                echo '<b class="text-secondary">GROUP</b>';
            }
            echo '<ul>';
            printRules($key);
            echo '</ul>';
            echo '</li>';
        }
    }
}
?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/query-builder.default.min.css">
<script src="<?= ROOTPATH ?>/js/query-builder.standalone.js?v=date"></script>

<script>
    var RULES;
    var EXPERT = <?= $expert ? 'true' : 'false' ?>;
</script>


<div class="modal" id="saved-queries-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h2 class="title">
                <?php if ($expert) { ?>
                    <?= lang('search.expert_queries') ?>
                <?php } else { ?>
                    <?= lang('search.saved_queries') ?>
                <?php } ?>
            </h2>


            <div class="mb-20">
                <button class="btn" aria-expanded="true" onclick="$(this).next().slideToggle();">
                    <i class="ph ph-floppy-disk"></i> <?= lang('search.save_current_query') ?>
                </button>

                <div style="display:none;" class="box padded mt-10">
                    <input type="text" class="form-control" id="query-name" placeholder="<?= lang('search.name_of_query') ?>">
                    <button class="btn primary mt-10" onclick="saveQuery()"><?= lang('search.save_query') ?></button>
                </div>
            </div>

            <?php
            $filter = [
                '$or' => [
                    ['user' => $_SESSION['username']],
                    ['global' => true],
                    ['role' => ['$in' => $Settings->roles]]
                ],
                'type' => $collection
            ];
            if (!$expert) {
                $filter['expert'] = ['$ne' => true];
            } else {
                $filter['expert'] = true;
            }
            $queries = $osiris->queries->find($filter)->toArray();
            if (empty($queries)) {
                echo '<p>' . lang('search.you_have_not_saved_any_queries_yet') . '</p>';
            } else {
                // sort by created by current user first, then by created date
                usort($queries, function ($a, $b) {
                    if ($a['user'] == $_SESSION['username'] && $b['user'] != $_SESSION['username']) {
                        return -1;
                    } elseif ($a['user'] != $_SESSION['username'] && $b['user'] == $_SESSION['username']) {
                        return 1;
                    } else {
                        return strtotime($b['created']) <=> strtotime($a['created']);
                    }
                });
            ?>

                <input type="search" class="form-control mb-10" id="query-search" placeholder="<?= lang('search.search_saved_queries') ?>" oninput="$('#saved-queries details').each(function() {
                    var summary = $(this).find('summary').text().toLowerCase();
                    var filter = $('#query-search').val().toLowerCase();
                    if (summary.indexOf(filter) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });">
                <div class="collapse-group" id="saved-queries">
                    <?php foreach ($queries as $query) {
                        $rules = json_decode($query['rules'], true);
                        if (!$expert && empty($rules['rules'])) {
                            $rules = ['rules' => [['id' => 'No rules']]];
                        }
                        $query_id = strval($query['_id']);
                    ?>
                        <details id="query-<?= $query_id ?>" class="mb-10">
                            <summary class="collapse-header font-weight-bold d-flex justify-content-between align-items-center">
                                <?= $query['name'] ?>
                                <?php if ($query['global'] ?? false) { ?>
                                    <span class="badge badge-info"><i class="ph ph-globe"></i> <?= lang('search.global') ?></span>
                                <?php } elseif (isset($query['role'])) { ?>
                                    <span class="badge badge-secondary"><i class="ph ph-shield-checkered"></i> <?= lang('search.role') ?> <?= ucfirst($query['role']) ?></span>
                                <?php } ?>
                            </summary>
                            <div class="collapse-content">
                                <?php if ($Settings->hasPermission('queries.global') && !($query['global'] ?? false)) {
                                    $sharelink = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
                                    $sharelink .= ROOTPATH . '/' . $collection . '/search?query=' . $query_id;
                                ?>
                                    <div class="dropdown float-right">
                                        <button class="btn" data-toggle="dropdown" type="button" id="dropdown-<?= $query_id ?>" aria-haspopup="true" aria-expanded="false">
                                            <i class="ph ph-share-network"></i> <?= lang('common.share') ?>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-<?= $query_id ?>">
                                            <!-- share globally -->
                                            <div class="content">
                                                <!-- copy Link with ID to clipboard -->
                                                 <?=lang('search.sharable_link')?>
                                                <a class="" href="<?= $sharelink ?>" target="_blank">
                                                    <?= $sharelink ?>
                                                </a>
                                                <!-- <button class="btn link" onclick="copyQuery()" data-toggle="tooltip" data-title="<?= lang('search.copy_sharable_linkto_clipboard') ?>">
                                                    <i class="ph ph-copy"></i>
                                                </button> -->
                                                <hr>
                                                <button class="btn block mb-5" onclick="shareQuery('<?= $query['_id'] ?>', 'global')">
                                                    <i class="ph ph-globe"></i> <?= lang('search.share_globally') ?>
                                                </button>
                                                <hr>
                                                <select class="form-control mb-5" id="role-select-<?= $query_id ?>">
                                                    <?php foreach ($Settings->getRoles() as $role) { ?>
                                                        <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                                                    <?php } ?>
                                                </select>
                                                <button class="btn block" onclick="shareQuery('<?= $query_id ?>', 'role')">
                                                    <i class="ph ph-shield-checkered"></i> <?= lang('search.share_with_role') ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                        function copyQuery() {
                                            var url = '<?= $currentURL ?>?query=<?= $query_id ?>';
                                            navigator.clipboard.writeText(url);
                                            toastSuccess('<?= lang('search.sharable_link_copied_to_clipboard') ?>');
                                        }

                                        function shareQuery(id, type) {
                                            var data = {
                                                id: id,
                                                action: 'SHARE'
                                            };
                                            if (type == 'global') {
                                                data.global = true;
                                            } else if (type == 'role') {
                                                var role = $('#role-select-' + id).val();
                                                data.role = role;
                                            }
                                            $.post(ROOTPATH + '/crud/queries', data, function(response) {
                                                toastSuccess('<?= lang('search.query_shared_successfully') ?>');
                                            });
                                        }
                                    </script>
                                <?php } ?>
                                <a class="btn primary" onclick="applyFilter('<?= $query['_id'] ?>', '<?= $query['aggregate'] ?? '' ?>', '<?= implode(';', DB::doc2Arr($query['columns'] ?? [])) ?>', '<?= $query['aggregate_function'] ?? 'count' ?>', '<?= $query['aggregate_value'] ?? '' ?>')"><?= lang('common.apply_filter') ?></a>

                                <table class="table simple my-10">

                                    <?php if ($query['user'] != $_SESSION['username']) { ?>
                                        <tr>
                                            <th><?= lang('search.shared_by') ?>:</th>
                                            <td><?= $DB->getNameFromId($query['user']) ?></td>
                                        </tr>
                                    <?php } ?>

                                    <tr>
                                        <th style="vertical-align: baseline;"><?= lang('search.rules') ?>:</th>
                                        <?php if ($expert) { ?>
                                            <td>
                                                <?= dump($rules) ?>
                                            </td>
                                        <?php } else { ?>
                                            <td>
                                                <ul>
                                                    <?php printRules($rules); ?>
                                                </ul>
                                            </td>
                                        <?php } ?>
                                    </tr>

                                    <tr>
                                        <th><?= lang('search.aggregate') ?>:</th>
                                        <td>
                                            <?php if (isset($query['aggregate']) && !empty($query['aggregate'])) { ?>
                                                <?= $field_by_id[$query['aggregate']]['label'] ?? $query['aggregate'] ?>
                                                <?php
                                                $aggregation_function = $query['aggregate_function'] ?? 'count';
                                                echo ' – ' . ($aggregation_function_labels[$aggregation_function] ?? $aggregation_function);
                                                if ($aggregation_function !== 'count' && !empty($query['aggregate_value'])) {
                                                    echo ': ' . ($field_by_id[$query['aggregate_value']]['label'] ?? $query['aggregate_value']);
                                                }
                                                ?>
                                            <?php } else {
                                                echo lang('search.no_aggregation');
                                            } ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th><?= lang('common.columns') ?>:</th>
                                        <td>
                                            <?php if (isset($query['columns']) && !empty($query['columns'])) {
                                                $cols = DB::doc2Arr($query['columns']);
                                                // get labels from fields
                                                $colLabels = array_map(function ($c) use ($field_by_id) {
                                                    return $field_by_id[$c]['label'] ?? $c;
                                                }, $cols);
                                            ?>
                                                <?= implode(', ', $colLabels) ?>
                                            <?php } else {
                                                echo lang('search.default_columns');
                                            } ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th><?= lang('common.created') ?>:</th>
                                        <td><?= date('d.m.Y H:i', strtotime($query['created'])) ?></td>
                                    </tr>
                                </table>

                                <?php if ($query['user'] != $_SESSION['username']) { ?>
                                    <small class="text-muted"><?= lang('search.only_the_creator_of_the_query_can_delete_or_modify_it') ?></small>
                                <?php } else { ?>
                                    <a class="btn danger small text-right" onclick="deleteQuery('<?= $query['_id'] ?>')"><i class="ph ph-trash"></i> <?= lang('search.delete_query') ?></a>
                                <?php } ?>
                            </div>
                        </details>
                    <?php } ?>
                </div>
            <?php  } ?>

            <script>
                var queries = {};
                <?php foreach ($queries as $query) { ?>
                    queries['<?= $query['_id'] ?>'] = '<?= $query['rules'] ?>';
                <?php } ?>
            </script>

            <div class="text-right mt-20">
                <a href="#/" class="btn mr-5" role="button"><?= lang('action.close') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="filter-code" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('search.filter_code') ?></h5>

            <p>
                <?= lang('search.this_filter_is_needed_for_example_for_generating_report_templates') ?>
            </p>
            <!-- copy to clipboard -->
            <script>
                function copyResultToClipboard() {
                    var text = $('#result').text()
                    navigator.clipboard.writeText(text)
                    toastSuccess('Query copied to clipboard.')
                }
            </script>

            <div class="position-relative">
                <button class="btn secondary small position-absolute top-0 right-0 m-10" onclick="copyResultToClipboard()"><i class="ph ph-clipboard" aria-label="Copy to clipboard"></i></button>

                <pre id="result" class="code p-20"></pre>
            </div>
            <div class="text-right mt-20">
                <a href="#/" class="btn mr-5" role="button"><?= lang('action.close') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="column-select-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('search.select_columns_to_display') ?></h5>
            <style>
                .input-group-text {
                    background-color: var(--primary-color-30);
                    color: var(--primary-color);
                    border-color: var(--primary-color);
                }
            </style>
            <div class="input-group mb-20">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="ph ph-magnifying-glass"></i>
                    </span>
                </div>
                <input type="search" class="form-control border-primary" id="column-search" placeholder="<?= lang('search.search_fields') ?>" oninput="filterColumns()">
            </div>

            <div id="column-select">
                <?php
                $selected = $_GET['columns'] ?? $defaultColumns;
                $fields = array_filter($FIELDS->fields, function ($f) {
                    return in_array('columns', $f['usage'] ?? []);
                });
                // sort by module_of
                usort($fields, function ($a, $b) use ($selected) {
                    if (in_array($a['id'], $selected)) {
                        return -1;
                    } elseif (in_array($b['id'], $selected)) {
                        return 1;
                    } elseif (in_array('general', $a['module_of'] ?? [])) {
                        return -1;
                    } elseif (in_array('general', $b['module_of'] ?? [])) {
                        return 1;
                    } elseif (count($b['module_of'] ?? []) == count($a['module_of'] ?? [])) {
                        return in_array($a['id'], $selected) ? -1 : 1;
                    }
                    return count($b['module_of'] ?? []) <=> count($a['module_of'] ?? []);
                });

                $icons = [];
                if ($collection == 'activities') {
                    $iconsRaw = $osiris->adminCategories->find([], ['projection' => ['icon' => 1, 'id' => 1, 'color' => 1, 'name' => 1]])->toArray();
                } else {
                    $iconsRaw = $osiris->adminProjects->find([], ['projection' => ['icon' => 1, 'id' => 1, 'color' => 1, 'name' => 1]])->toArray();
                }
                foreach ($iconsRaw as $icon) {
                    $iconClass = 'ph ph-cube';
                    $icons[$icon['id']] = '<span data-toggle="tooltip" data-title="' . ($icon['name'] ?? $icon['id']) . '"><i class="ph ph-' . ($icon['icon'] ?? $iconClass) . '" style="color:' . ($icon['color'] ?? '#6c757d') . '"></i></span>';
                }

                foreach ($fields as $field) {
                    $modules = $field['module_of'] ?? [];
                ?>
                    <div class="custom-checkbox checkbox-badge <?= empty($modules) ? 'text-muted' : '' ?>">
                        <input type="checkbox" class="form-check-input" data-column="<?= $field['id'] ?>" id="column-<?= str_replace('.', '-', $field['id']) ?>" <?= (in_array($field['id'], $selected) ? 'checked' : '') ?>>
                        <label class="form-check-label" for="column-<?= str_replace('.', '-', $field['id']) ?>" value="<?= $field['id'] ?>">
                            <?= $field['label'] ?>
                            <?php if ($field['custom'] ?? false) { ?>
                                <span data-toggle="tooltip" data-title="Custom field">
                                    <i class="ph ph-duotone ph-gear text-muted"></i>
                                </span>
                            <?php } ?>
                            <?php foreach ($modules as $key) {
                                if ($key == 'general') { ?>
                                    <span data-toggle="tooltip" data-title="<?= lang('search.general_field') ?>">
                                        <i class="ph ph-globe text-muted"></i>
                                    </span>
                            <?php
                                    continue;
                                }
                                echo $icons[$key] ?? '<span data-toggle="tooltip" data-title="' . $key . '"><i class="ph ph-question text-muted"></i></span>';
                            } ?>

                        </label>
                    </div>
                <?php } ?>
            </div>
            <div class="text-right mt-20">
                <a href="#/" class="btn mr-5" role="button"><?= lang('action.close') ?></a>
                <a class="btn secondary" role="button" onclick="getResult()"><?= lang('common.apply') ?></a>
            </div>
            <script>
                function filterColumns() {
                    var input = document.getElementById("column-search");
                    var filter = input.value.toLowerCase();
                    var div = document.getElementById("column-select");
                    var labels = div.getElementsByTagName("label");

                    for (var i = 0; i < labels.length; i++) {
                        var label = labels[i];
                        var textValue = label.textContent || label.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            label.parentElement.style.opacity = "1";
                        } else {
                            label.parentElement.style.opacity = "0.3";
                        }
                    }
                }
            </script>
        </div>
    </div>
</div>


<div class="">
    <a href="https://wiki.osiris-app.de/users/advanced-search/" class="btn tour float-sm-right" target="_blank"><i class="ph ph-question"></i> <?= lang('search.manual') ?></a>
    <h1>
        <i class="ph-duotone ph-magnifying-glass-plus"></i>
        <?= lang('navigation.advanced_search') ?>
        <?= lang('search.in') ?> <?= $colName ?? $collection ?>
    </h1>

    <div class="box">
        <div class="content mb-0">

            <h3 class="title"><?= lang('search.filter') ?></h3>
            <div id="builder" class="<?= $expert ? 'hidden' : '' ?>"></div>

            <?php if ($expert) { ?>
                <textarea name="expert" id="expert" cols="30" rows="5" class="form-control"><?= $expertQuery ?></textarea>
            <?php } ?>

        </div>

        <div class="row position-relative">
            <div class="col">
                <div class="content">
                    <a href="#column-select-modal">
                        <i class="ph ph-columns-plus-right"></i>
                        <?= lang('search.select_columns') ?>
                    </a>
                    <!-- 
                    <div id="selected-columns">
                        <span class="badge">Webdarstellung</span>
                    </div> -->
                </div>
            </div>
            <div class="text-divider"><?= lang('common.or') ?></div>
            <div class="col">
                <!-- Aggregations -->
                <div class="content">
                    <a onclick="$('#aggregate-form').slideToggle()">
                        <i class="ph ph-squares-four"></i>
                        <?= lang('search.aggregate') ?>
                    </a>

                    <div class="input-group" style="display:none;" id="aggregate-form">
                        <select name="aggregate" id="aggregate" class="form-control w-auto" aria-label="<?= lang('search.group_by') ?>" onchange="updateAggregationControls()">
                            <option value=""><?= lang('search.without_aggregation_show_all') ?></option>
                            <?php
                            $aggregate_filter = array_filter($FIELDS->fields, function ($f) {
                                return in_array('aggregate', $f['usage'] ?? []);
                            });
                            foreach ($aggregate_filter as $f) { ?>
                                <option value="<?= $f['id'] ?>"><?= $f['label'] ?></option>
                            <?php } ?>


                        </select>

                        <select name="aggregate_function" id="aggregate-function" class="form-control w-auto" aria-label="<?= lang('search.calculation') ?>" onchange="updateAggregationControls()">
                            <?php foreach ($aggregation_function_labels as $function => $label) { ?>
                                <option value="<?= $function ?>"><?= $label ?></option>
                            <?php } ?>
                        </select>

                        <select name="aggregate_value" id="aggregate-value" class="form-control w-auto" aria-label="<?= lang('search.value_field') ?>" style="display:none;">
                            <option value=""><?= lang('search.select_numeric_value') ?></option>
                            <?php
                            $numeric_aggregate_fields = array_filter($FIELDS->fields, function ($f) {
                                return in_array($f['type'] ?? '', ['integer', 'double', 'number'], true)
                                    && !empty($f['usage'] ?? []);
                            });
                            foreach ($numeric_aggregate_fields as $f) { ?>
                                <option value="<?= $f['id'] ?>"><?= $f['label'] ?></option>
                            <?php } ?>
                        </select>

                        <!-- remove aggregation -->
                        <div class="input-group-append">
                            <button class="btn text-danger" onclick="clearAggregation()"><i class="ph ph-x"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="footer">

            <div class="btn-toolbar">
                <?php if ($expert) { ?>
                    <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('common.apply') ?></button>

                    <a class="btn osiris" href="?"><i class="ph ph-lego"></i> <?= lang('search.sandbox_mode') ?></a>

                <?php } else { ?>
                    <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('common.apply') ?></button>
                    <a class="btn osiris" href="?expert"><i class="ph ph-magnifying-glass-plus"></i> <?= lang('search.expert_mode') ?></a>
                <?php } ?>

                <a href="#saved-queries-modal" class="btn" role="button">
                    <i class="ph ph-floppy-disk"></i> <?= lang('search.saved_queries') ?>
                </a>

                <a href="#filter-code" class="btn" role="button">
                    <i class="ph ph-code"></i> <?= lang('search.show_filter') ?>
                </a>

            </div>
        </div>
    </div>


    <table class="table" id="activity-table">
        <thead></thead>
        <tbody></tbody>
    </table>

    <script>
        // var mongo = $('#builder').queryBuilder('getMongo');

        var filters = <?= json_encode($filters) ?>;

        // clean up if filters is an object
        if (filters.constructor === Object) {
            filters = Object.values(filters)
        }

        // get fields
        var fields = <?= json_encode($fields) ?>;

        // clean up if fields is an object
        if (fields.constructor === Object) {
            fields = Object.values(fields)
        }

        function escapeRegex(s) {
            return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }


        var mongoQuery = $('#builder').queryBuilder({
            filters: filters,
            'lang_code': lang('common.this_language'),
            'icons': {
                add_group: 'ph ph-plus-circle text-success',
                add_rule: 'ph ph-plus text-success',
                remove_group: 'ph ph-x-circle text-danger',
                remove_rule: 'ph ph-x text-danger',
                error: 'ph ph-warning text-danger',
            },
            allow_empty: true,
            default_filter: '<?= $defaultFilter ?>',
            operators: [
                'equal', 'not_equal', 'in', 'not_in', 'less', 'less_or_equal', 'greater', 'greater_or_equal',
                'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains',
                'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null',
                {
                    type: 'exists',
                    nb_inputs: 0,
                    apply_to: ['string', 'number', 'datetime', 'boolean']
                },
                {
                    type: 'not_exists',
                    nb_inputs: 0,
                    apply_to: ['string', 'number', 'datetime', 'boolean']
                },
                {
                    type: 'contains_i',
                    nb_inputs: 1,
                    apply_to: ['string']
                }
            ],
            lang: {
                operators: {
                    exists: <?= json_encode(lang('search.exists'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    not_exists: <?= json_encode(lang('search.not_exists'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    contains_i: <?= json_encode(lang('search.contains_ignore_case'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
                }
            },
            mongoOperators: {
                exists: function() {
                    return {
                        $exists: true
                    };
                },
                not_exists: function() {
                    return {
                        $exists: false
                    };
                },
                contains_i: function(v) {
                    return {
                        $regex: escapeRegex(v),
                        $options: 'i'
                    };
                }
            }
        });

        var dataTable;

        function updateAggregationControls() {
            const hasAggregation = $('#aggregate').val() !== '';
            const needsValue = hasAggregation && $('#aggregate-function').val() !== 'count';

            $('#aggregate-function').prop('disabled', !hasAggregation);
            $('#aggregate-value').prop('disabled', !needsValue).toggle(needsValue);
        }

        function clearAggregation() {
            $('#aggregate').val('');
            $('#aggregate-function').val('count');
            $('#aggregate-value').val('');
            updateAggregationControls();
            getResult();
        }

        function aggregationResultLabel() {
            const functionLabels = {
                count: lang('common.count'),
                sum: <?= json_encode(lang('common.sum'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                mean: <?= json_encode(lang('search.mean'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                median: <?= json_encode(lang('common.median'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
            };
            const aggregationFunction = $('#aggregate-function').val() || 'count';
            const valueLabel = $('#aggregate-value option:selected').text();

            if (aggregationFunction === 'count') {
                return functionLabels.count;
            }
            return functionLabels[aggregationFunction] + ': ' + valueLabel;
        }

        function initializeTable(data) {
            // destroy existing table
            if ($.fn.DataTable.isDataTable('#activity-table')) {
                $('#activity-table').DataTable().clear().destroy();
                $('#activity-table thead').empty(); // Header leeren
                $('#activity-table tbody').empty(); // Daten leeren
            }

            // Extrahiere die Spaltennamen aus der API-Antwort
            const first_row = Object.keys(data[0]);

            // check for aggregation
            var aggregate = $('#aggregate').val()

            // Generiere dynamisch das thead
            const thead = document.querySelector('#activity-table thead');
            const headerRow = document.createElement('tr');

            var columns = [];

            if (aggregate !== "") {
                const aggregationFunction = $('#aggregate-function').val() || 'count';
                data = data.map(row => ({
                    value: row.value ?? '<em>' + <?= json_encode(lang('search.empty'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + '</em>',
                    result: aggregationFunction === 'count' ? (row.count ?? 0) : (row.result ?? 0)
                }));

                // add aggregate column
                var th = document.createElement('th');
                th.textContent = $('#aggregate option:selected').text();
                headerRow.appendChild(th);
                th = document.createElement('th');
                th.textContent = aggregationResultLabel();
                headerRow.appendChild(th);
                thead.appendChild(headerRow);

                columns = [{
                        data: 'value',
                        title: lang('common.value')
                    },
                    {
                        data: 'result',
                        title: aggregationResultLabel()
                    }
                ]

            } else {
                var selected_columns = []
                var array_columns = {}
                $('#column-select input:checked').each(function() {
                    var id = $(this).data('column')
                    selected_columns.push(id)
                    var name = $(this).next('label').text()
                    const th = document.createElement('th');
                    th.textContent = name; // Optional: Titel formatieren
                    headerRow.appendChild(th);
                })
                thead.appendChild(headerRow);
                // Konfiguriere die Spalten für Datatables
                columns = selected_columns.map(function(field) {
                    // remove from selected columns
                    selected_columns = selected_columns.filter(column => column !== field);
                    // get name from `fields`
                    const filter = fields.find(f => f.id == field);
                    var r = {
                        data: field,
                        title: filter ? filter.label : field,
                        defaultContent: '-'
                    }
                    if (field == 'id') {
                        r.render = function(data, type, row, meta) {
                            if (type === 'export') {
                                return data;
                            }
                            return `<a href="<?= ROOTPATH ?>/<?= $collection ?>/view/${data}"><i class="ph ph-arrow-fat-line-right"></i></a>`
                        }
                    } else if (field == 'username') {
                        r.render = function(data, type, row, meta) {
                            if (type === 'export') {
                                return data;
                            }
                            return data ? `<a href="<?= ROOTPATH ?>/profile/${data}">${data}</a>` : '-';
                        }
                    } else if (array_columns[field]) {
                        var array_column = array_columns[field]
                        r.render = function(data, type, row, meta) {
                            if (Array.isArray(data)) {
                                data = data.map(item => item[array_column]).join(', ');
                            }
                            if (data === undefined || data === null) {
                                return '-'
                            }
                            if (Array.isArray(data[array_column])) {
                                return data[array_column].join(', ') ?? data;
                            }
                            return data[array_column] ?? data;
                        }
                    }
                    return r
                });
                if (selected_columns.length > 0) {
                    toastWarning(<?= json_encode(lang('search.the_following_columns_are_not_found_in_the_result_and_are_not_shown'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> + ' <strong>' + selected_columns.join(', ') + '</strong>');
                }

            }

            // Initialisiere Datatables
            $('#activity-table').DataTable({
                destroy: true, // Alte Tabelle entfernen, falls sie existiert
                data: data, // Daten direkt übergeben
                columns: columns, // Dynamisch generierte Spalten
                dom: 'fBrtip',
                buttons: downloadTableButtons('<?= $colName ?? $collection ?> Search', ':visible,:hidden', true),
                initComplete: function() {
                    var tableWidth = $('#activity-table').width();
                    var containerWidth = $('#activity-table').parent().width();
                    if (tableWidth > containerWidth && !$('#activity-table').parent().hasClass('table-responsive')) {
                        $('#activity-table').wrap('<div class="table-responsive"></div>');
                    }
                }
            });

            // check if table exceeds the width of the container
        }

        // AJAX-Call zum Abrufen der Daten
        function getResult() {
            if (EXPERT) {
                var rules = $('#expert').val()
                if (rules == '') {
                    return
                }
                try {
                    var rules = JSON.parse(rules)
                } catch (SyntaxError) {
                    toastError(<?= json_encode(lang('search.invalid_json'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                    return
                }
            } else {
                var rules = $('#builder').queryBuilder('getMongo')
            }
            if (rules === null) rules = []
            rules = JSON.stringify(rules)

            var data = {
                json: rules,
                formatted: true
            }

            var aggregate = $('#aggregate').val()
            if (aggregate !== "") {
                data.aggregate = aggregate
                data.aggregate_function = $('#aggregate-function').val() || 'count'
                if (data.aggregate_function !== 'count') {
                    data.aggregate_value = $('#aggregate-value').val()
                    if (!data.aggregate_value) {
                        toastWarning(<?= json_encode(lang('search.please_select_a_numeric_value_for_the_aggregation'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                        return
                    }
                }
            }

            // columns
            var columns = []
            $('#column-select input:checked').each(function() {
                columns.push($(this).data('column'))
            })
            data.columns = columns

            window.location.hash = rules

            $('#result').html(rules)
            $.ajax({
                url: ROOTPATH + '/api/search/<?= $collection ?>',
                method: 'GET',
                data: data,
                success: function(response) {
                    console.log(response);
                    if (response.count > 0) {
                        initializeTable(response.data); // Tabelle initialisieren
                    } else {
                        // $('#activity-table').DataTable().destroy(); // Tabelle entfernen
                        $('#activity-table tbody').html('<tr><td colspan="10" class="text-center">Keine Daten gefunden</td></tr>'); // Keine Daten gefunden

                    }
                },
                error: function(xhr, status, error) {
                    toastError('Fehler beim Abrufen der API:', error);
                }
            });
        }

        $(document).ready(function() {
            <?php if ($preset_query) : ?>
                applyFilter('<?= $preset_query['_id'] ?>', '<?= $preset_query['aggregate'] ?? '' ?>', '<?= implode(';', DB::doc2Arr($preset_query['columns'] ?? [])) ?>', '<?= $preset_query['aggregate_function'] ?? 'count' ?>', '<?= $preset_query['aggregate_value'] ?? '' ?>')
                return;
            <?php endif; ?>

            updateAggregationControls()

            var hash = window.location.hash.substr(1);
            if (hash !== undefined && hash != "") {
                try {
                    var rules = JSON.parse(decodeURI(hash))
                    RULES = rules;
                    $('#builder').queryBuilder('setRulesFromMongo', rules);
                } catch (SyntaxError) {
                    console.info('invalid hash')
                }
            }
            getResult()
        });


        function saveQuery() {
            // disable save button
            $('#save-query-button').prop('disabled', true);
            if (EXPERT) {
                var rules = $('#expert').val()
                rules = JSON.parse(rules)
            } else {
                var rules = $('#builder').queryBuilder('getRules')
            }
            var name = $('#query-name').val()
            if (name == "") {
                toastError('Please provide a name for your query.')
                $('#save-query-button').prop('disabled', false);
                return
            }

            var columns = []
            $('#column-select input:checked').each(function() {
                columns.push($(this).data('column'))
            })

            var query = {
                name: name,
                rules: rules,
                user: '<?= $_SESSION['username'] ?>',
                created: new Date(),
                aggregate: $('#aggregate').val(),
                aggregate_function: $('#aggregate-function').val() || 'count',
                aggregate_value: $('#aggregate-value').val(),
                columns: columns,
                expert: EXPERT,
                type: '<?= $collection ?>'
            }

            $.post(ROOTPATH + '/crud/queries', query, function(data) {
                // reload
                queries[data.id] = JSON.stringify(rules)

                $('#saved-queries').append(`<a class="d-block" onclick="applyFilter('${data.id}', '${$('#aggregate').val()}', '${columns.join(';')}', '${$('#aggregate-function').val() || 'count'}', '${$('#aggregate-value').val()}')">${name}</a>`)
                $('#query-name').val('')
                toastSuccess(<?= json_encode(lang('search.query_saved_successfully_please_reload_the_page_to_see_it_completely'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                $('#save-query-button').prop('disabled', false);
            })
        }

        function applyFilter(id, aggregate, columns, aggregateFunction = 'count', aggregateValue = '') {
            console.log(columns);
            if (EXPERT) {
                applyFilterExpert(id, aggregate, aggregateFunction, aggregateValue)
                return
            }
            var filter = queries[id];
            if (!filter) {
                toastError('Query not found.')
                return
            }
            $('#aggregate').val(aggregate)
            $('#aggregate-function').val(aggregateFunction || 'count')
            $('#aggregate-value').val(aggregateValue || '')
            updateAggregationControls()
            var parsedFilter = JSON.parse(filter, (key, value) => {
                if (typeof value === 'string' && /^\d+$/.test(value)) {
                    return parseInt(value);
                } else if (value === 'true') {
                    return true;
                } else if (value === 'false') {
                    return false;
                }
                return value;
            });
            if (!columns) {
                columns = "<?= implode(';', $defaultColumns) ?>";
            }
            $('#column-select input').prop('checked', false)
            columns.split(';').forEach(column => {
                $('#column-' + column.replace('.', '-')).prop('checked', true)
            })


            $('#builder').queryBuilder('setRules', parsedFilter);
            // var rules = $('#builder').queryBuilder('getMongo')
            getResult()
        }

        function applyFilterExpert(id, aggregate, aggregateFunction = 'count', aggregateValue = '') {
            var filter = queries[id];
            if (!filter) {
                toastError('Query not found.')
                return
            }
            $('#aggregate').val(aggregate)
            $('#aggregate-function').val(aggregateFunction || 'count')
            $('#aggregate-value').val(aggregateValue || '')
            updateAggregationControls()
            $('#expert').val(filter)

            getResult()
        }


        function deleteQuery(id) {
            $.ajax({
                url: ROOTPATH + '/crud/queries',
                type: 'POST',
                data: {
                    id: id,
                    action: 'DELETE'
                },
                success: function(result) {
                    delete queries[id]
                    $('#query-' + id).remove()
                    toastSuccess('Query deleted successfully.')
                }
            });
        }
    </script>

</div>
