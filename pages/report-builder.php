<?php

/** 
 * This file provides a template editor to create and edit reports.
 * A report may consists of text blocks (markdown), paragraphs with filtered activities, and tables with aggregated numbers.
 */

$aggregate_filter = fn($f) => !empty($f['module_of']) && in_array('aggregate', $f['usage']);
$sort_filter = fn($f) => !in_array($f['type'], ['boolean', 'list']) && !str_contains($f['id'], '.') && !empty($f['module_of']) && in_array('filter', $f['usage']);

$data_fields = [];

include_once BASEPATH . "/php/activity_fields.php";
$FIELDS = new ActivityFields();
$data_fields['activities']['aggregate'] = array_filter($FIELDS->fields, $aggregate_filter);
$data_fields['activities']['sort'] = array_filter($FIELDS->fields, $sort_filter);
$data_fields['activities']['add'] = array_filter($data_fields['activities']['sort'], $sort_filter);
$data_fields['activities']['sort'][] = [
    'id' => 'rendered.plain',
    'label' => lang('reports.alphabetically'),
    'type' => 'string'
];

// include_once BASEPATH . "/php/person_fields.php";
// $FIELDS = new PersonFields();
// $data_fields['persons']['aggregate'] = array_filter($FIELDS->fields, $aggregate_filter);
// $data_fields['persons']['sort'] = array_filter($FIELDS->fields, $sort_filter);
// $data_fields['persons']['add'] = array_filter($data_fields['persons']['sort'], $sort_filter);


if ($Settings->featureEnabled('projects')) {
    include_once BASEPATH . "/php/project_fields.php";
    foreach (['projects', 'proposals'] as $collection) {
        $FIELDS = new ProjectFields($collection);

        $data_fields[$collection]['aggregate'] = array_filter($FIELDS->fields, $aggregate_filter);
        $data_fields[$collection]['sort'] = array_filter($FIELDS->fields, $sort_filter);
        $data_fields[$collection]['add'] = array_filter($data_fields[$collection]['sort'], $sort_filter);
    }
}

if ($Settings->featureEnabled('events')) {
    include_once BASEPATH . "/php/event_fields.php";
    $FIELDS = new EventFields();

    $data_fields['conferences']['aggregate'] = array_filter($FIELDS->fields, $aggregate_filter);
    $data_fields['conferences']['sort'] = array_filter($FIELDS->fields, $sort_filter);
    $data_fields['conferences']['add'] = array_filter($data_fields['conferences']['sort'], $sort_filter);
}

// convert all into arrays for easier access in js
foreach ($data_fields as $key => $value) {
    foreach ($value as $usage => $fields) {
        $data_fields[$key][$usage] = array_values($fields);
    }
}


$report_id = $report['_id'] ?? null;


$collections = [
    'activities' => lang('common.activities'),
    // 'persons' => lang('common.persons')
];
if ($Settings->featureEnabled('events')) {
    $collections['conferences'] = lang('reports.events');
}
if ($Settings->featureEnabled('projects')) {
    $collections['projects'] = lang('common.projects');
    $collections['proposals'] = lang('common.proposals');
}
?>

<style>
    #report {
        min-height: 320px;
        border: 2px dashed #e5e7eb;
        border-radius: .5rem;
        padding: 1rem;
        margin: 2rem 0;
        /* background: #fff; */
    }

    .preview-content {
        cursor: pointer;
    }

    .preview-content h1,
    .preview-content h2,
    .preview-content h3,
    .preview-content h4 {
        display: flex;
        align-items: center;
    }

    .preview-content h1::before,
    .preview-content h2::before,
    .preview-content h3::before,
    .preview-content h4::before {
        content: "H1";
        /* font-family: var(--icon-font); */
        display: inline-block;
        color: var(--muted-color);
        margin-right: 1rem;
        background: var(--gray-color);
        width: 3rem;
        text-align: center;
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        font-size: .6em;
        font-weight: normal;
        font-family: monospace;
    }

    .preview-content h1 {
        font-size: 2.4rem;
    }


    .preview-content h2 {
        font-size: 2rem;
    }

    .preview-content h2::before {
        content: "H2";
    }

    .preview-content h3 {
        font-size: 1.6rem;
    }

    .preview-content h3::before {
        content: "H3";
    }

    .preview-content h4 {
        font-size: 1.4rem;
    }

    .preview-content h4::before {
        content: "H4";
    }

    .step {
        margin-bottom: 1rem;
        padding: 1rem;
        border: var(--border-width) solid var(--border-color);
        border-radius: var(--border-radius);
        background-color: white;
    }

    .step h4 {
        margin: 0;
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .handle {
        cursor: move;
        font-size: 2.2rem !important;

    }

    .dropdown-menu {
        padding: 10px;
    }

    .item {
        cursor: pointer;
    }

    .step {
        margin-bottom: .75rem;
        padding: .75rem;
        margin-left: 2.5rem;
        position: relative;
    }

    .step .step-header {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .step .step-title {
        font-weight: 600;
        margin-right: auto;
    }

    .step .step-body {
        margin-top: 1rem;
    }

    .step.is-collapsed .step-body {
        display: none;
    }

    .step.is-collapsed .collapse-btn i:before {
        content: "\e536";
    }

    .step .handle {
        position: absolute;
        left: -2.5rem;
    }

    .handle {
        cursor: move;
        font-size: 1.6rem !important;
    }

    .btn-icon {
        padding: .25rem .35rem;
    }

    .table#vars-table td {
        vertical-align: baseline !important;
    }

    .eyebrow {
        text-transform: uppercase;
        letter-spacing: .08em;
        color: var(--secondary-color);
        /* font-size: .85rem; */
        font-weight: 700;
        margin-bottom: -.5rem;
    }

    .editor-toolbar {
        font-weight: bold;
        position: sticky;
        bottom: 0;
        right: 0;
        z-index: 40;
        background-color: var(--muted-color-very-light);
        background-color: var(--gray-color-very-light);
        padding: 1rem 4.5rem 2rem;
        border-top: var(--border-width) solid var(--border-color);
        margin: 0 -2rem -2rem;
    }

    .step .step-name {
        font-weight: 600;
        font-size: 1.4rem;
        border: none;
        /* border-bottom: 1px solid var(--border-color); */
        /* border-radius: 0; */
        box-shadow: none;
        background-color: var(--body-color);
        width: 100%;
    }

    .step .step-name::placeholder {
        font-weight: 600;
        font-size: 1.4rem;
        color: var(--muted-color);
    }

    .step .label {
        font-weight: 600;
        display: block;
        margin-bottom: .25rem;
    }

    .line-step {
        display: flex;
        align-items: center;
    }

    .rule {
        margin: 1rem 0;
        width: 100%;
        margin-bottom: .75rem;
        padding: .75rem;
        margin-left: 2.5rem;
        position: relative;
    }

    .dragging {
        background-color: var(--muted-color-very-light);
        opacity: .9;
        border: none;
    }
</style>

<?php if (!empty($report) && isset($report_id)) { ?>
    <div class="btn-toolbox  float-right">
        <!-- Help -->
        <a href="https://wiki.osiris-app.de/users/reporting/" class="btn tour" target="_blank">
            <i class="ph ph-question"></i>
            <?= lang('reports.help') ?>
        </a>
    </div>
<?php } ?>

<div style="margin-left: 2.5rem;">

    <div class="eyebrow">
        <?= lang('reports.report_builder') ?>
    </div>
    <h1>
        <i class="ph-duotone ph-clipboard-text"></i>
        <?= $report['title'] ?? lang('common.untitled_report') ?>
    </h1>

</div>

<form action="<?= ROOTPATH ?>/crud/reports/update" method="post">
    <input type="hidden" name="id" value="<?= $report_id ?>">

    <div style="margin-left: 2.5rem;">

        <!-- toolbar -->
        <div class="d-flex align-items-center gap-5 my-10">

            <?php if (isset($report['title'])) { ?>
                <button type="button" class="btn" onclick="$('#report-settings').slideToggle()">
                    <i class="ph ph-edit"></i>
                    <?= lang('reports.edit_report_settings') ?>
                </button>
            <?php } ?>
            <a href="#variables" class="btn" data-toggle="modal">
                <i class="ph ph-code-block"></i>
                <?= lang('reports.variables') ?>
            </a>

            <!-- collapse all -->
            <button type="button" class="btn ml-auto" onclick="$('#report .step').addClass('is-collapsed')">
                <i class="ph ph-arrows-in-line-vertical"></i>
                <?= lang('reports.collapse_all') ?>
            </button>
            <button type="button" class="btn" onclick="$('#report .step').removeClass('is-collapsed')">
                <i class="ph ph-arrows-out-line-vertical"></i>
                <?= lang('reports.expand_all') ?>
            </button>
        </div>


        <div style="<?= isset($report['title']) ? 'display:none;' : '' ?>" id="report-settings" class="box padded mt-0">
            <h2 class="title">
                <?= lang('reports.report_settings') ?>
            </h2>
            <div class="form-group">
                <label for="title" class="required"><?= lang('reports.name_of_the_report') ?></label>
                <input type="text" class="form-control" name="title" value="<?= $report['title'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="description"><?= lang('common.description') ?></label>
                <textarea type="text" class="form-control" name="description"><?= $report['description'] ?? '' ?></textarea>
            </div>

            <!-- start month and duration -->
            <div class="form-row row-eq-spacing">
                <div class="col-sm">
                    <label for="start" class="required"><?= lang('common.start_month') ?></label>
                    <input type="number" class="form-control" name="start" id="start" value="<?= $report['start'] ?? '' ?>" required>
                </div>
                <div class="col-sm">
                    <label for="duration" class="required"><?= lang('reports.duration_in_months') ?></label>
                    <input type="number" class="form-control" name="duration" id="duration" value="<?= $report['duration'] ?? '' ?>" required>
                </div>
            </div>
        </div>



        <div class="modal" id="variables" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a href="#close-modal" class="close" role="button" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title"><?= lang('reports.parameters_variables') ?></h5>

                    <div id="vars-help" class="text-muted small mb-10">
                        <?= lang('reports.define_variables_here_and_use_them_anywhere_in_your_template_using_vars_key') ?>
                        <button type="button" class="btn link small" onclick="$('#vars-cheatsheet').toggle();">Cheatsheet</button>
                    </div>

                    <div id="vars-cheatsheet" class="card p-10 mb-10" style="display:none;">
                        <div class="small">
                            <strong>Text:</strong> <code>{{vars.orgName}}</code><br>
                            <strong>Filter (String):</strong> <code>{"units":"{{vars.orgId}}"}</code><br>
                            <strong>Filter (Number):</strong> <code>{"year":{{vars.year}}}</code><br>
                            <strong>Filter (Boolean):</strong> <code>{"peerReviewed":{{vars.peer}}}</code><br>
                        </div>
                    </div>

                    <table class="table mb-20" id="vars-table">
                        <thead>
                            <tr>
                                <th style="width:18%"><?= lang('reports.key') ?></th>
                                <th style="width:18%"><?= lang('common.type') ?></th>
                                <th><?= lang('common.label') ?></th>
                                <th style="width:22%"><?= lang('reports.default_value') ?></th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody><!-- rows injected --></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <button type="button" class="btn" onclick="addVarRow();">
                                        <i class="ph ph-plus"></i> <?= lang('reports.add_variable') ?>
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    <style>
                        .copy-to-clipboard {
                            cursor: pointer;
                            color: var(--muted-color);
                        }

                        .copy-to-clipboard:hover {
                            text-decoration: underline;
                        }
                    </style>

                    <p class="font-size-12">
                        <b><?= lang('reports.tip') ?>:</b>
                        <?= lang('reports.you_can_use_the_following_built_in_variables_for_the_reporting_period') ?><br>
                        <span class="copy-to-clipboard">{{vars.startyear}}</span>: <?= lang('reports.start_year_of_the_reporting_period') ?><br>
                        <span class="copy-to-clipboard">{{vars.endyear}}</span>: <?= lang('reports.end_year_of_the_reporting_period') ?><br>
                        <span class="copy-to-clipboard">{{vars.startmonth}}</span>: <?= lang('reports.start_month_of_the_reporting_period_1_12') ?><br>
                        <span class="copy-to-clipboard">{{vars.endmonth}}</span>: <?= lang('reports.end_month_of_the_reporting_period_1_12') ?><br>
                    </p>

                    <script>
                        $('.copy-to-clipboard').on('click', function() {
                            const text = $(this).text();
                            navigator.clipboard.writeText(text).then(function() {
                                toastSuccess('<?= lang('reports.copied_to_clipboard') ?>: ' + text);
                            }, function(err) {
                                toastError('<?= lang('reports.could_not_copy_text') ?>' + err);
                            });
                        });
                    </script>

                    <div class="modal-footer">
                        <!-- save -->
                        <button type="submit" class="btn success"><?= lang('action.save') ?></button>

                        <a href="#close-modal" class="btn mr-5" role="button"><?= lang('action.close') ?></a>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="report">
        <!-- steps will be added here -->
    </div>


    <div class="editor-toolbar">

        <!-- dropdown to add stuff -->
        <div class="dropdown dropup">
            <button class="btn primary dropdown-toggle mr-20" type="button" id="addNewRowButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-plus"></i>
                <?= lang('reports.add_new_block') ?>
            </button>
            <div class="dropdown-menu" aria-labelledby="addNewRowButton">
                <a class="item" onclick="addRow('text')">
                    <b class="text-primary d-block"><?= lang('reports.text') ?></b>
                    <small class="text-muted"><?= lang('reports.a_block_that_contains_headings_or_paragraphs') ?></small>
                </a>
                <a class="item" onclick="addRow('list')">
                    <b class="text-primary d-block"><?= lang('reports.list') ?></b>
                    <small class="text-muted"><?= lang('reports.a_block_that_contains_a_list_of_items_of_different_types') ?></small>
                </a>
                <a class="item" onclick="addRow('table')">
                    <b class="text-primary d-block"><?= lang('reports.table') ?></b>
                    <small class="text-muted"><?= lang('reports.aggregate_information_as_a_table_containing_number_of_items') ?></small>
                </a>
                <a class="item" onclick="addRow('toc')">
                    <b class="text-primary d-block"><?= lang('reports.table_of_contents') ?></b>
                    <small class="text-muted"><?= lang('reports.a_block_that_automatically_generates_a_table_of_contents_based_on_the_headi') ?></small>
                </a>
                <a class="item" onclick="addRow('line')">
                    <b class="text-primary d-block"><?= lang('reports.line') ?></b>
                    <small class="text-muted"><?= lang('reports.a_simple_line_to_divide_content') ?></small>
                </a>
            </div>
        </div>

        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('action.save') ?>
        </button>

        <a href="<?= ROOTPATH ?>/admin/reports/preview/<?= $report_id ?>" class="btn" target="_blank">
            <i class="ph ph-eye"></i>
            <?= lang('common.preview') ?>
        </a>
    </div>
</form>

<style>
    .preview {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .preview-content {
        flex-grow: 1;
    }

    .step-container {
        /* margin-bottom: 1rem; */
    }

    .preview-content h1 p,
    .preview-content h2 p,
    .preview-content h3 p,
    .preview-content h4 p {
        margin: 0;
    }
</style>

<!-- modules to copy -->
<div class="hidden" id="templates" style="display:none">
    <div id="text" class="step-container">
        <div class="preview">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>

            <div class="preview-content" onclick="openTextEditor(this)">
                <p><?= lang('reports.text_block_without_content') ?></p>
            </div>
            <a data-toggle="modal" onclick="openTextEditor(this)">
                <i class="ph ph-pencil-simple-line"></i>
            </a>
        </div>
        <div class="step" style="display:none;">

            <div class="step-header">
                <i class="ph ph-text-t ph-fw text-secondary"></i>

                <select name="values[*][level]" class="form-control small w-auto step-level" required>
                    <option value="h1"><?= lang('reports.heading_1') ?></option>
                    <option value="h2"><?= lang('reports.heading_2') ?></option>
                    <option value="h3"><?= lang('reports.heading_3') ?></option>
                    <option value="h4"><?= lang('reports.heading_4') ?></option>
                    <option value="p"><?= lang('common.paragraph') ?></option>
                </select>

                <button type="button" class="btn small link text-danger ml-auto" onclick="$(this).closest('.step-container').remove()" title="Delete">
                    <i class="ph ph-trash" aria-label="Delete"></i>
                </button>
            </div>
            <div class="step-body">
                <input type="hidden" class="hidden" name="values[*][type]" value="text">


                <div class="form-group lang-<?= lang('common.this_language') ?> mb-0">
                    <div class="title-editor form-group"></div>
                    <input type="text" class="form-control step-text hidden" name="values[*][text]" id="title" required value="">
                </div>

            </div>
        </div>
    </div>

    <div class="step" id="list">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-article ph-fw text-secondary"></i></a>
            <input type="text" name="values[*][title]" class="form-control small step-name" value="" placeholder="<?= lang('reports.list_of_items') ?>">
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon text-danger" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <div class="step-body">
            <div class="collection-options">
                <?php
                foreach ($collections as $col => $label) {
                ?>
                    <div class="pill-checkbox ">
                        <input type="radio" id="col-<?= $col ?>-*" value="<?= $col ?>" name="values[*][collection]" class="step-collection" required>
                        <label for="col-<?= $col ?>-*"><?= $label ?></label>
                    </div>
                <?php
                }
                ?>
            </div>

            <input type="hidden" class="hidden" name="values[*][type]" value="list">
            <label for="filter" class="label">Filter <a onclick="$(this).parent().next().toggle()" class="btn link small"><i class="ph ph-question"></i></a></label>
            <small style="display:none;">
                <?= lang('reports.find_filters_in_the_advanced_search_and_copy_from_show_filter', replace: ['rootpath' => ROOTPATH]) ?>
            </small>
            <textarea type="text" class="form-control step-filter" name="values[*][filter]" placeholder="Filter" required>{}</textarea>

            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                <label for="timelimit"><?= lang('reports.limit_to_reporting_time') ?></label>
            </div>
            <div class="row row-eq-spacing my-0">
                <div class="col-sm">
                    <label class="label"><?= lang('reports.additional_fields') ?></label>
                    <div class="additional-fields" data-name="values[*][field]"><!-- rows injected by JS --></div>
                    <button type="button" class="btn small" onclick="addAdditionalField(this)"><?= lang('reports.add_field') ?></button>
                </div>
                <div class="col-sm">
                    <label class="label"><?= lang('reports.sorting') ?></label>
                    <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                    <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('reports.add_criterion') ?></button>
                </div>
            </div>
        </div>
    </div>


    <div class="step" id="activities">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-article ph-fw text-secondary"></i></a>
            <span class="step-title"><?= lang('common.activities') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon text-danger" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <div class="step-body">
            <input type="hidden" class="hidden" name="values[*][type]" value="activities">
            <textarea type="text" class="form-control step-filter" name="values[*][filter]" placeholder="Filter" required>{}</textarea>
            <small>
                <?= lang('reports.find_filters_in_the_advanced_search_and_copy_from_show_filte_report_builder', replace: ['rootpath' => ROOTPATH]) ?>
            </small>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                <label for="timelimit"><?= lang('reports.limit_to_reporting_time') ?></label>
            </div>
            <div class="mt-10">
                <label class="d-block mb-5"><?= lang('reports.sorting') ?></label>
                <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('reports.add_criterion') ?></button>
            </div>
        </div>
    </div>

    <div class="step" id="activities-field">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-columns-plus-right ph-fw text-secondary"></i></a>
            <span class="step-title"><?= lang('reports.activities_incl_additional_field') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon text-danger" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <div class="step-body">
            <input type="hidden" class="hidden" name="values[*][type]" value="activities-field">
            <textarea type="text" class="form-control step-filter" name="values[*][filter]" placeholder="Filter" required>{}</textarea>
            <small>
                <?= lang('reports.find_filters_in_the_advanced_search_and_copy_from_show_filte_report_builder', replace: ['rootpath' => ROOTPATH]) ?>
            </small>
            <div class="form-group">
                <label for="field"><?= lang('reports.additional_field') ?></label>
                <select name="values[*][field]" required class="form-control step-field">
                    <?php
                    foreach ($data_fields['activities']['add'] as $f) { ?>
                        <option value="<?= e($f['id']) ?>"><?= $f['label'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                <label for="timelimit"><?= lang('reports.limit_to_reporting_time') ?></label>
            </div>
            <div class="mt-10">
                <label class="d-block mb-5"><?= lang('reports.sorting') ?></label>
                <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('reports.add_criterion') ?></button>
            </div>
        </div>
    </div>


    <div class="step" id="table">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-table ph-fw text-secondary"></i></a>
            <input type="text" name="values[*][title]" class="form-control small step-name" value="" placeholder="<?= lang('reports.table') ?>">
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon text-danger" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <div class="step-body">
            <div class="collection-options">
                <?php
                foreach ($collections as $col => $label) {
                ?>
                    <div class="pill-checkbox ">
                        <input type="radio" id="col-<?= $col ?>-*" value="<?= $col ?>" name="values[*][collection]" class="step-collection" required>
                        <label for="col-<?= $col ?>-*"><?= $label ?></label>
                    </div>
                <?php
                }
                ?>
            </div>
            <input type="hidden" class="hidden" name="values[*][type]" value="table">
            <textarea type="text" class="form-control step-filter" name="values[*][filter]" placeholder="Filter" required>{}</textarea>

            <div class="form-row row-eq-spacing mt-10">
                <div class="col">
                    <label for="aggregate" class="label"><i class="ph ph-columns-plus-left"></i> <?= lang('reports.rows') ?></label>
                    <select name="values[*][aggregate]" required class="form-control step-aggregate">
                        <option value=""><?= lang('reports.select_field_for_the_left_column') ?></option>
                        <!-- options injected by JS -->
                    </select>
                </div>
                <div class="col">
                    <label for="aggregate2" class="label"><i class="ph ph-rows-plus-top"></i> <?= lang('reports.columns') ?></label>
                    <select name="values[*][aggregate2]" class="form-control step-aggregate2">
                        <option value=""><?= lang('reports.choose_field_for_the_column_header_optional') ?></option>
                        <!-- options injected by JS -->
                    </select>
                </div>
            </div>
            <div class="form-row row-eq-spacing mt-10">
                <div class="col">
                    <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                    <label for="timelimit"><?= lang('reports.limit_to_reporting_time') ?></label>
                </div>
                <div class="col">
                    <!-- table_sort -->
                    <label for="field" class="d-inline-block"><?= lang('common.sort_by') ?>: </label>
                    <select name="values[*][table_sort]" required class="form-control step-select small d-inline-block w-auto">
                        <option value="count-desc"><?= lang('reports.count_descending') ?></option>
                        <option value="count-asc"><?= lang('reports.count_ascending') ?></option>
                        <option value="aggregation-asc"><?= lang('reports.name_of_aggregation_asc') ?></option>
                        <option value="aggregation-desc"><?= lang('reports.name_of_aggregation_desc') ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="line" class="line-step">
        <i class="ph ph-dots-six-vertical text-muted handle"></i>
        <div class="rule">
            <hr>
        </div>

        <button type="button" class="btn small link text-danger" onclick="$(this).closest('.line-step').remove()" title="Delete">
            <i class="ph ph-trash" aria-label="Delete"></i>
        </button>
        <input type="hidden" class="hidden" name="values[*][type]" value="line">
    </div>

    <div id="toc" class="step ">
        <i class="ph ph-dots-six-vertical text-muted handle"></i>
        <div class="d-flex d-align-center gap-10">
        <i class="ph ph-list ph-fw text-secondary"></i>
        <span class="step-title"><?= lang('reports.table_of_contents') ?></span>

        <button type="button" class="btn small link text-danger" onclick="$(this).closest('.step').remove()" title="Delete">
            <i class="ph ph-trash" aria-label="Delete"></i>
        </button>
    </div>
    <small class="text-muted">
    <?= lang('reports.automatically_generates_a_table_of_contents_based_on_the_headings_in_the_re') ?>
    </small>
        <input type="hidden" class="hidden" name="values[*][type]" value="toc">
    </div>


    <!-- Hidden template for one variable row -->
    <table id="vars-row-template" class="hidden">
        <tr class="var-row">
            <td>
                <input class="form-control var-key" name="variables[*][key]" placeholder="orgId" required>
                <small class="text-muted">[a-zA-Z0-9_]</small>
            </td>
            <td>
                <select class="form-control var-type" name="variables[*][type]">
                    <option value="string">string</option>
                    <option value="int">int</option>
                    <option value="float">float</option>
                    <option value="bool">bool</option>
                </select>
            </td>
            <td>
                <input class="form-control" name="variables[*][label]" placeholder="<?= lang('reports.department_id') ?>">
            </td>
            <td>
                <input class="form-control var-default" name="variables[*][default]" placeholder="">
                <small class="text-muted copy-token" style="cursor:pointer" title="<?= lang('reports.copy_token') ?>">
                    <i class="ph ph-copy"></i> <span class="token-text">{{vars.*}}</span>
                </small>
            </td>
            <td class="text-right">
                <button type="button" class="btn link" onclick="$(this).closest('tr').remove()">
                    <i class="ph ph-trash"></i>
                </button>
            </td>
        </tr>
    </table>

</div>


<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/reports.js"></script>

<script>
    let templateIndex = 0;

    function addRow(type, data) {
        const $tpl = $('#' + type).clone(true, true);
        // new id
        $tpl.attr('id', type + '-' + templateIndex);
        let collection = data && data.collection ? data.collection : 'activities';

        // replace [*] → [varIndex]
        $tpl.find('input,select,textarea').each(function() {
            const name = $(this).attr('name');
            if (!name) return;
            $(this).attr('name', name.replace('[*]', '[' + templateIndex + ']'));
        });
        $tpl.find('input,select,textarea').each(function() {
            const id = $(this).attr('id');
            if (!id) return;
            $(this).attr('id', id.replace('*', templateIndex));
        });
        $tpl.find('label').each(function() {
            const forAttr = $(this).attr('for');
            if (!forAttr) return;
            $(this).attr('for', forAttr.replace('*', templateIndex));
        });

        $tpl.find('.step-collection').prop('checked', false);
        $tpl.find(`.step-collection[value="${collection}"]`).prop('checked', true);

        if (type === 'table') {
            // inject options for aggregation
            const aggregateSelect = $tpl.find('.step-aggregate');
            const aggregate2Select = $tpl.find('.step-aggregate2');
            const options = buildOptions(collection, 'aggregate');
            aggregateSelect.append(options);
            aggregate2Select.append(options);
        }

        // prefill
        if (data) {
            $tpl.find('.step-text').val(data.text || '');
            $tpl.find('.step-level').val(data.level || 'p');
            $tpl.find('.step-filter').val(data.filter || '');
            $tpl.find('.step-timelimit').prop('checked', data.timelimit ? true : false);
            $tpl.find('.step-aggregate').val(data.aggregate || '');
            $tpl.find('.step-aggregate2').val(data.aggregate2 || '');
            $tpl.find('.step-name').val(data.title || '');
            // $tpl.find('.step-field').val(data.field || '');
            // sort rows
            if (data.sort && Array.isArray(data.sort) && data.sort.length > 0) {
                data.sort.forEach(sortCriterion => {
                    addSortRow($tpl.find('.sort-rows'), sortCriterion);
                });
            }
            if (data.field && Array.isArray(data.field) && data.field.length > 0) {
                data.field.forEach(field => {
                    addAdditionalField($tpl.find('.additional-fields'), field);
                });

            }
            if (data.table_sort && typeof data.table_sort === 'string') {
                $tpl.find('.step-select').val(data.table_sort);
            }
            if (data.text && type === 'text') {
                // set preview
                const preview = $tpl.find('.preview-content');
                const level = $tpl.find('.step-level').val();
                preview.html(`<${level}>${data.text}</${level}>`);
            }

            // if data exist: collapse step by default
            $tpl.addClass('is-collapsed');
        } else {
            // if no data: open step by default
            $tpl.removeClass('is-collapsed');
            // for text blocks: also open editor
            if (type === 'text') {
                $tpl.find('.step').show();
            }
        }
        $('#report').append($tpl);
        // close dropdown if open
        // check first if dropdown is open to avoid unnecessary DOM manipulation and reflow
        if ($('#addNewRowButton').hasClass('active')) {
            $('.dropdown').removeClass('show');
            $('.dropdown .btn').removeClass('active');
            // scroll to new step        
            $('html, body').animate({
                scrollTop: $tpl.offset().top - 100
            }, 100);
        }

        if (type === 'text') {
            // init editor
            const editorId = 'title-editor-' + templateIndex;
            const editorInput = $tpl.find('.title-editor');
            if (data) {
                editorInput.html(data.text || '');
            }
            editorInput.attr('id', editorId);
            editorInput.next().attr('id', editorId + '-field');
            var quill = new Quill(editorInput.get(0), {
                // modules: {
                //     toolbar: toolbar
                // },
                // formats: formats,
                // placeholder: '',
                theme: 'snow' // or 'bubble'
            });

            quill.on('text-change', function() {
                var str = ''
                editorInput.find('.ql-editor p').each(function(i, el) {
                    var el = $(el)
                    if (el.html() == '<br>') return;
                    var html = el.html()
                    if (str != '') str += "<br>"
                    str += html
                })
                editorInput.next().val(str)

                // change header in preview
                const preview = $tpl.find('.preview-content');
                const level = $tpl.find('.step-level').val();
                preview.html(`<${level}>${editorInput.find('.ql-editor').html()}</${level}>`);
            });

        }

        $tpl.find('.step-filter').on('input', function() {
            const isValid = validateFilterJSON($(this).val());
            $(this).toggleClass('is-invalid', !isValid);
        });

        templateIndex++;
    }

    function validateFilterJSON(str) {
        try {
            JSON.parse(str);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Toggle + Duplicate
    function toggleStep(btn) {
        $(btn).closest('.step').toggleClass('is-collapsed');
    }

    function duplicateStep(btn) {
        const $orig = $(btn).closest('.step');
        const $clone = $orig.clone(true, true);
        // assign new id to clone
        $clone.attr('id', $orig.attr('id') + '-copy-' + templateIndex);
        // re-index names (*) -> n
        $clone.find('input,select,textarea').each(function() {
            const name = $(this).attr('name');
            if (!name) return;
            $(this).attr('name', name.replace(/\[\d+\]/g, '[' + templateIndex + ']'));
        });
        $('#report').append($clone);
        if ($clone.find('.title-editor').length) {
            $clone.find('.title-editor').attr('id', 'title-editor-' + templateIndex);
            $clone.find('.title-editor').next().attr('id', 'title-editor-' + templateIndex + '-field');
            initQuill($clone.find('.title-editor').get(0), 'full');
        }
        templateIndex++;
    }

    // Add one sort row to the nearest .sort-rows container
    function addSortRow(elOrContainer, data) {
        const $container = $(elOrContainer).hasClass('sort-rows') ? $(elOrContainer) : $(elOrContainer).closest('.step-body').find('.sort-rows');
        const base = $container.data('name'); // e.g. values[*][sort]
        const idx = $container.children('.sort-row').length;
        const namePrefix = base.replace('*', getIndexFromContainer($container));

        // inject options based on selected collection
        const collection = $container.closest('.step-body').find('.step-collection:checked').val();
        const options = buildOptions(collection, 'sort', data);
        // copy options from select fields
        const row = $(`
    <div class="sort-row d-flex align-items-center gap-5 mb-5">
      <select class="form-control small w-200 flex-grow-0" placeholder="field" name="${namePrefix}[${idx}][field]" required>
        <option value="" disabled selected><?= lang('reports.select_field') ?></option>
        ${options}
      </select>
      <select class="form-control small w-150 flex-grow-0" name="${namePrefix}[${idx}][dir]" required>
        <option value="asc">${<?= json_encode(lang('reports.ascending'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>}</option><option value="desc">${<?= json_encode(lang('reports.descending'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>}</option>
      </select>
      <button type="button" class="btn small link text-danger" title="Remove" onclick="$(this).closest('.sort-row').remove()">
        <i class="ph ph-x"></i>
      </button>
    </div>
  `);

        $container.append(row);

        if (data) { // prefill
            row.find(`[name$="[field]"]`).val(data.field || '');
            row.find(`[name$="[dir]"]`).val((data.dir || 'asc').toLowerCase());
            row.find(`[name$="[nulls]"]`).val(data.nulls || '');
        }
    }

    function addAdditionalField(elOrContainer, data) {
        const $elOrContainer = $(elOrContainer);
        const $container = $elOrContainer.hasClass('additional-fields') ? $elOrContainer : $elOrContainer.closest('.step-body').find('.additional-fields');
        const base = $container.data('name'); // e.g. values[*][field]
        const idx = $container.children('.additional-field-row').length;
        const namePrefix = base.replace('*', getIndexFromContainer($container));
        // inject options based on selected collection
        const collection = $container.closest('.step-body').find('.step-collection:checked').val();
        const options = buildOptions(collection, 'add', data);

        // copy options from select fields
        const row = $(`
    <div class="additional-field-row d-flex align-items-center gap-5 mb-5">
      <select class="form-control small w-200 flex-grow-0" placeholder="field" name="${namePrefix}[${idx}]" required>
        <option value="" disabled selected><?= lang('reports.select_field') ?></option>
        ${options}
      </select>
      <button type="button" class="btn small link text-danger" title="Remove" onclick="$(this).closest('.additional-field-row').remove()">
        <i class="ph ph-x"></i>
      </button>
    </div>
  `);
        $container.append(row);
        if (data) { // prefill
            row.find('select').val(data || '');
        }
    }

    // Helper: find the numeric index actually used in this block (replaces *)
    function getIndexFromContainer($container) {
        // Find any input name under step and extract [N]
        const $inp = $container.closest('.step').find('input,textarea,select').first();
        const m = ($inp.attr('name') || '').match(/\[(\d+)\]/);
        return m ? m[1] : 0; // fallback
    }


    // Variables UI state
    let varIndex = 0;

    // Add one row (optionally with data)
    function addVarRow(data) {
        const $tpl = $('#vars-row-template').find('tr').clone();
        // replace [*] → [varIndex]
        $tpl.find('input,select').each(function() {
            const name = $(this).attr('name');
            if (!name) return;
            $(this).attr('name', name.replace('[*]', '[' + varIndex + ']'));
        });
        // prefill
        if (data) {
            $tpl.find('.var-key').val(data.key || '');
            $tpl.find('.var-type').val(data.type || 'string');
            $tpl.find('[name$="[label]"]').val(data.label || '');
            $tpl.find('.var-default').val(data.default ?? '');
        }
        // token preview + copy
        const keyForToken = data?.key || 'KEY';
        $tpl.find('.token-text').text(`{{vars.${keyForToken}}}`);
        $tpl.find('.var-key').on('input', function() {
            const k = $(this).val() || 'KEY';
            $(this).closest('tr').find('.token-text').text(`{{vars.${k}}}`);
        });
        $tpl.find('.copy-token').on('click', function() {
            const t = $(this).find('.token-text').text();
            navigator.clipboard?.writeText(t);
        });
        $('#vars-table tbody').append($tpl);

        console.log($tpl);
        varIndex++;
    }


    $(document).ready(function() {
        var steps = <?= json_encode($steps ?? []) ?>;
        // load existing steps
        steps.forEach(step => addRow(step.type, step));

        $('#report').sortable({
            handle: ".handle",
            // add classes for styling during drag (optional)
            start: function(e, ui) {
                ui.item.addClass('dragging');
            },
            stop: function(e, ui) {
                ui.item.removeClass('dragging');
            }
        });
    });



    // Load existing variables from PHP
    // $(function() {
    const existing = <?= json_encode($report['variables'] ?? []) ?>;
    if (existing.length) {
        existing.forEach(v => addVarRow(v));
    }

    // simple validation before submit
    $('form').on('submit', function(e) {
        let ok = true,
            seen = {};
        $('#vars-table .var-row').each(function() {
            const key = $(this).find('.var-key').val().trim();
            if (!/^[a-zA-Z0-9_]+$/.test(key)) {
                ok = false;
                $(this).find('.var-key').addClass('is-invalid');
            }
            if (seen[key]) {
                ok = false;
                $(this).find('.var-key').addClass('is-invalid');
            }
            seen[key] = 1;
            // optional: cast default preview by type
        });
        if (!ok) {
            e.preventDefault();
            alert('Please fix variable keys (unique, [a-zA-Z0-9_]).');
        }
        // });
    });

    // when changing the collection: 
    // check if sort and additional fields are filled in, 
    // if yes, hint to user that they will be lost if they change the collection
    $(document).on('click', '.step-collection', function(event) {
        console.log('Collection change detected');
        // disable default behavior of radio button to allow reverting change if user cancels
        const $step = $(this).closest('.step');
        const hasSort = $step.find('.sort-rows .sort-row').length > 0;
        const hasFields = $step.find('.additional-fields .additional-field-row').length > 0;
        const hasAggregations = $step.find('.step-aggregate').val() || $step.find('.step-aggregate2').val();
        console.log(hasAggregations);
        if (hasSort || hasFields || hasAggregations) {
            if (!confirm(<?= json_encode(lang('reports.changing_the_collection_will_remove_any_additional_fields_sorting_criteria'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)) {
                // make sure radio button does not change
                event.preventDefault();
                return;
            } else {
                // remove sort and additional fields
                $step.find('.sort-rows').empty();
                $step.find('.additional-fields').empty();
            }
        }
        if ($step.find('.step-aggregate')) {
            // remove aggregation options except placeholder
            $step.find('.step-aggregate option:not(:first)').remove();
            $step.find('.step-aggregate2 option:not(:first)').remove();

            // populate new aggregation options based on new collection
            const collection = $(this).val();
            console.log(collection);
            const options = buildOptions(collection, 'aggregate');
            $step.find('.step-aggregate').append(options);
            $step.find('.step-aggregate2').append(options);
        }
    });


    function buildOptions(collection, subset = 'add', selected = '') {
        const FIELDS = <?= json_encode($data_fields) ?>;
        if (!FIELDS[collection]) return '';
        return FIELDS[collection][subset].map(f => `<option value="${f.id}" ${f.id === selected ? 'selected' : ''}>${f.label}</option>`).join('');
    }

    function openTextEditor(btn) {
        // toggle editor visibility
        const $step = $(btn).closest('.step-container');
        $step.find('.step').slideToggle()
    }
</script>