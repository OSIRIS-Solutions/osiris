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
    'label' => lang('Alphabetically', 'Alphabetisch'),
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
    'activities' => lang('Activities', 'Aktivitäten'),
    // 'persons' => lang('Persons', 'Personen')
];
if ($Settings->featureEnabled('events')) {
    $collections['conferences'] = lang('Events', 'Ereignisse');
}
if ($Settings->featureEnabled('projects')) {
    $collections['projects'] = lang('Projects', 'Projekte');
    $collections['proposals'] = lang('Proposals', 'Anträge');
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
            <?= lang('Help', 'Hilfe') ?>
        </a>
    </div>
<?php } ?>

<div style="margin-left: 2.5rem;">

    <div class="eyebrow">
        <?= lang('Report Builder', 'Berichtseditor') ?>
    </div>
    <h1>
        <i class="ph-duotone ph-clipboard-text"></i>
        <?= $report['title'] ?? lang('Untitled Report', 'Unbenannter Bericht') ?>
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
                    <?= lang('Edit report settings', 'Berichtseinstellungen bearbeiten') ?>
                </button>
            <?php } ?>
            <a href="#variables" class="btn" data-toggle="modal">
                <i class="ph ph-code-block"></i>
                <?= lang('Variables', 'Variablen') ?>
            </a>

            <!-- collapse all -->
            <button type="button" class="btn ml-auto" onclick="$('#report .step').addClass('is-collapsed')">
                <i class="ph ph-arrows-in-line-vertical"></i>
                <?= lang('Collapse all', 'Alle einklappen') ?>
            </button>
            <button type="button" class="btn" onclick="$('#report .step').removeClass('is-collapsed')">
                <i class="ph ph-arrows-out-line-vertical"></i>
                <?= lang('Expand all', 'Alle ausklappen') ?>
            </button>
        </div>


        <div style="<?= isset($report['title']) ? 'display:none;' : '' ?>" id="report-settings" class="box padded mt-0">
            <h2 class="title">
                <?= lang('Report settings', 'Berichtseinstellungen') ?>
            </h2>
            <div class="form-group">
                <label for="title" class="required"><?= lang('Name of the report', 'Name des Berichts') ?></label>
                <input type="text" class="form-control" name="title" value="<?= $report['title'] ?? '' ?>" required>
            </div>
            <div class="form-group">
                <label for="description"><?= lang('Description', 'Beschreibung') ?></label>
                <textarea type="text" class="form-control" name="description"><?= $report['description'] ?? '' ?></textarea>
            </div>

            <!-- start month and duration -->
            <div class="form-row row-eq-spacing">
                <div class="col-sm">
                    <label for="start" class="required"><?= lang('Start month', 'Startmonat') ?></label>
                    <input type="number" class="form-control" name="start" id="start" value="<?= $report['start'] ?? '' ?>" required>
                </div>
                <div class="col-sm">
                    <label for="duration" class="required"><?= lang('Duration in months', 'Dauer in Monaten') ?></label>
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
                    <h5 class="title"><?= lang('Parameters (Variables)', 'Parameter (Variablen)') ?></h5>

                    <div id="vars-help" class="text-muted small mb-10">
                        <?= lang(
                            'Define variables here and use them anywhere in your template using {{vars.KEY}}. In filters: quote strings, do not quote numbers/booleans.',
                            'Definiere hier Variablen und nutze sie im Template mit {{vars.KEY}}. In Filtern: Strings in Anführungszeichen, Zahlen/Booleans ohne.'
                        ) ?>
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
                                <th style="width:18%"><?= lang('Key', 'Key') ?></th>
                                <th style="width:18%"><?= lang('Type', 'Typ') ?></th>
                                <th><?= lang('Label', 'Bezeichnung') ?></th>
                                <th style="width:22%"><?= lang('Default value', 'Standardwert') ?></th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody><!-- rows injected --></tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <button type="button" class="btn" onclick="addVarRow();">
                                        <i class="ph ph-plus"></i> <?= lang('Add variable', 'Variable hinzufügen') ?>
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
                        <b><?= lang('Tip', 'Tipp') ?>:</b>
                        <?= lang('You can use the following built-in variables for the reporting period:', 'Du kannst folgende vordefinierte Variablen für den Berichtszeitraum verwenden:') ?><br>
                        <span class="copy-to-clipboard">{{vars.startyear}}</span>: <?= lang('Start year of the reporting period', 'Startjahr des Berichtszeitraums') ?><br>
                        <span class="copy-to-clipboard">{{vars.endyear}}</span>: <?= lang('End year of the reporting period', 'Endjahr des Berichtszeitraums') ?><br>
                        <span class="copy-to-clipboard">{{vars.startmonth}}</span>: <?= lang('Start month of the reporting period (1-12)', 'Startmonat des Berichtszeitraums (1-12)') ?><br>
                        <span class="copy-to-clipboard">{{vars.endmonth}}</span>: <?= lang('End month of the reporting period (1-12)', 'Endmonat des Berichtszeitraums (1-12)') ?><br>
                    </p>

                    <script>
                        $('.copy-to-clipboard').on('click', function() {
                            const text = $(this).text();
                            navigator.clipboard.writeText(text).then(function() {
                                toastSuccess('<?= lang('Copied to clipboard', 'In die Zwischenablage kopiert') ?>: ' + text);
                            }, function(err) {
                                toastError('<?= lang('Could not copy text: ', 'Konnte Text nicht kopieren: ') ?>' + err);
                            });
                        });
                    </script>

                    <div class="modal-footer">
                        <!-- save -->
                        <button type="submit" class="btn success"><?= lang('Save', 'Speichern') ?></button>

                        <a href="#close-modal" class="btn mr-5" role="button"><?= lang('Close', 'Schließen') ?></a>
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
                <?= lang('Add new block', 'Neuen Baustein hinzufügen') ?>
            </button>
            <div class="dropdown-menu" aria-labelledby="addNewRowButton">
                <a class="item" onclick="addRow('text')">
                    <b class="text-primary d-block"><?= lang('Text', 'Text') ?></b>
                    <small class="text-muted"><?= lang('A block that contains headings or paragraphs', 'Ein Block, der Überschriften oder Absätze enthält') ?></small>
                </a>
                <a class="item" onclick="addRow('list')">
                    <b class="text-primary d-block"><?= lang('List', 'Liste') ?></b>
                    <small class="text-muted"><?= lang('A block that contains a list of items of different types', 'Ein Block, der eine Liste von Elementen unterschiedlicher Typen enthält') ?></small>
                </a>
                <a class="item" onclick="addRow('table')">
                    <b class="text-primary d-block"><?= lang('Table', 'Tabelle') ?></b>
                    <small class="text-muted"><?= lang('Aggregate information as a table containing number of items', 'Aggregiere Informationen in einer Tabelle, die die Anzahl der Elemente enthält') ?></small>
                </a>
                <a class="item" onclick="addRow('toc')">
                    <b class="text-primary d-block"><?= lang('Table of contents', 'Inhaltsverzeichnis') ?></b>
                    <small class="text-muted"><?= lang('A block that automatically generates a table of contents based on the headings in the report', 'Ein Block, der automatisch ein Inhaltsverzeichnis basierend auf den Überschriften im Bericht generiert') ?></small>
                </a>
                <a class="item" onclick="addRow('line')">
                    <b class="text-primary d-block"><?= lang('Line', 'Linie') ?></b>
                    <small class="text-muted"><?= lang('A simple line to divide content', 'Eine einfache Linie zur Trennung von Inhalten') ?></small>
                </a>
            </div>
        </div>

        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>

        <a href="<?= ROOTPATH ?>/admin/reports/preview/<?= $report_id ?>" class="btn" target="_blank">
            <i class="ph ph-eye"></i>
            <?= lang('Preview', 'Vorschau') ?>
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
                <p><?= lang('Text block without content', 'Textblock ohne Inhalt') ?></p>
            </div>
            <a data-toggle="modal" onclick="openTextEditor(this)">
                <i class="ph ph-pencil-simple-line"></i>
            </a>
        </div>
        <div class="step" style="display:none;">

            <div class="step-header">
                <i class="ph ph-text-t ph-fw text-secondary"></i>

                <select name="values[*][level]" class="form-control small w-auto step-level" required>
                    <option value="h1"><?= lang('Heading 1', 'Überschrift 1') ?></option>
                    <option value="h2"><?= lang('Heading 2', 'Überschrift 2') ?></option>
                    <option value="h3"><?= lang('Heading 3', 'Überschrift 3') ?></option>
                    <option value="h4"><?= lang('Heading 4', 'Überschrift 4') ?></option>
                    <option value="p"><?= lang('Paragraph', 'Absatz') ?></option>
                </select>

                <button type="button" class="btn small link text-danger ml-auto" onclick="$(this).closest('.step-container').remove()" title="Delete">
                    <i class="ph ph-trash" aria-label="Delete"></i>
                </button>
            </div>
            <div class="step-body">
                <input type="hidden" class="hidden" name="values[*][type]" value="text">


                <div class="form-group lang-<?= lang('en', 'de') ?> mb-0">
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
            <input type="text" name="values[*][title]" class="form-control small step-name" value="" placeholder="<?= lang('List of items', 'Liste von Elementen') ?>">
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
                <?= lang('Find filters in the <a href="' . ROOTPATH . '/activities/search" target="_blank">advanced search</a> and copy from "Show filter".', 'Filter findest du in der <a href="' . ROOTPATH . '/activities/search" target="_blank">erweiterten Suche</a> und kannst sie im Fenster "Zeige Filter" kopieren.') ?>
            </small>
            <textarea type="text" class="form-control step-filter" name="values[*][filter]" placeholder="Filter" required>{}</textarea>

            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                <label for="timelimit"><?= lang('Limit to reporting time', 'Auf den Berichtszeitraum beschränken') ?></label>
            </div>
            <div class="row row-eq-spacing my-0">
                <div class="col-sm">
                    <label class="label"><?= lang('Additional fields', 'Zusätzliche Felder') ?></label>
                    <div class="additional-fields" data-name="values[*][field]"><!-- rows injected by JS --></div>
                    <button type="button" class="btn small" onclick="addAdditionalField(this)"><?= lang('Add field', '+ Feld') ?></button>
                </div>
                <div class="col-sm">
                    <label class="label"><?= lang('Sorting', 'Sortierung') ?></label>
                    <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                    <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('Add criterion', '+ Kriterium') ?></button>
                </div>
            </div>
        </div>
    </div>


    <div class="step" id="activities">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-article ph-fw text-secondary"></i></a>
            <span class="step-title"><?= lang('Activities', 'Aktivitäten') ?></span>
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
                <?= lang('Find filters in the <a href="' . ROOTPATH . '/activities/search" target="_blank">advanced search</a> and copy from "Show filter".', 'Filter findest du in der <a href="' . ROOTPATH . '/activities/search" target="_blank">erweiterten Suche</a> und kannst sie von "Zeige Filter" kopieren.') ?>
            </small>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                <label for="timelimit"><?= lang('Limit to reporting time', 'Auf den Berichtszeitraum beschränken') ?></label>
            </div>
            <div class="mt-10">
                <label class="d-block mb-5"><?= lang('Sorting', 'Sortierung') ?></label>
                <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('Add criterion', '+ Kriterium') ?></button>
            </div>
        </div>
    </div>

    <div class="step" id="activities-field">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-columns-plus-right ph-fw text-secondary"></i></a>
            <span class="step-title"><?= lang('Activities (incl. additional Field)', 'Aktivitäten (mit weiterem Feld)') ?></span>
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
                <?= lang('Find filters in the <a href="' . ROOTPATH . '/activities/search" target="_blank">advanced search</a> and copy from "Show filter".', 'Filter findest du in der <a href="' . ROOTPATH . '/activities/search" target="_blank">erweiterten Suche</a> und kannst sie von "Zeige Filter" kopieren.') ?>
            </small>
            <div class="form-group">
                <label for="field"><?= lang('Additional field', 'Weiteres Feld') ?></label>
                <select name="values[*][field]" required class="form-control step-field">
                    <?php
                    foreach ($data_fields['activities']['add'] as $f) { ?>
                        <option value="<?= e($f['id']) ?>"><?= $f['label'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                <label for="timelimit"><?= lang('Limit to reporting time', 'Auf den Berichtszeitraum beschränken') ?></label>
            </div>
            <div class="mt-10">
                <label class="d-block mb-5"><?= lang('Sorting', 'Sortierung') ?></label>
                <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('Add criterion', '+ Kriterium') ?></button>
            </div>
        </div>
    </div>


    <div class="step" id="table">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <a onclick="toggleStep(this)"><i class="ph ph-table ph-fw text-secondary"></i></a>
            <input type="text" name="values[*][title]" class="form-control small step-name" value="" placeholder="<?= lang('Table', 'Tabelle') ?>">
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
                    <label for="aggregate" class="label"><i class="ph ph-columns-plus-left"></i> <?= lang('Rows', 'Zeilen') ?></label>
                    <select name="values[*][aggregate]" required class="form-control step-aggregate">
                        <option value=""><?= lang('Select field for the left column', 'Feld für die linke Spalte auswählen') ?></option>
                        <!-- options injected by JS -->
                    </select>
                </div>
                <div class="col">
                    <label for="aggregate2" class="label"><i class="ph ph-rows-plus-top"></i> <?= lang('Columns', 'Spalten (optional)') ?></label>
                    <select name="values[*][aggregate2]" class="form-control step-aggregate2">
                        <option value=""><?= lang('Choose field for the column header (optional)', 'Feld für die Spaltenüberschrift auswählen (optional)') ?></option>
                        <!-- options injected by JS -->
                    </select>
                </div>
            </div>
            <div class="form-row row-eq-spacing mt-10">
                <div class="col">
                    <input type="checkbox" name="values[*][timelimit]" value="1" checked class="step-timelimit">
                    <label for="timelimit"><?= lang('Limit to reporting time', 'Auf den Berichtszeitraum beschränken') ?></label>
                </div>
                <div class="col">
                    <!-- table_sort -->
                    <label for="field" class="d-inline-block"><?= lang('Sort by', 'Sortieren nach') ?>: </label>
                    <select name="values[*][table_sort]" required class="form-control step-select small d-inline-block w-auto">
                        <option value="count-desc"><?= lang('Count descending', 'Anzahl absteigend') ?></option>
                        <option value="count-asc"><?= lang('Count ascending', 'Anzahl aufsteigend') ?></option>
                        <option value="aggregation-asc"><?= lang('Name of aggregation asc', 'Zeilenbezeichnung aufsteigend') ?></option>
                        <option value="aggregation-desc"><?= lang('Name of aggregation desc', 'Zeilenbezeichnung absteigend') ?></option>
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
        <span class="step-title"><?= lang('Table of contents', 'Inhaltsverzeichnis') ?></span>

        <button type="button" class="btn small link text-danger" onclick="$(this).closest('.step').remove()" title="Delete">
            <i class="ph ph-trash" aria-label="Delete"></i>
        </button>
    </div>
    <small class="text-muted">
    <?= lang('Automatically generates a table of contents based on the headings in the report. You might be asked by Word to update the fields when opening the report. Just confirm with "Yes" and the table of contents will be updated.', 'Generiert automatisch ein Inhaltsverzeichnis basierend auf den Überschriften im Bericht. Beim Öffnen des Berichts könnte Word fragen, ob die Felder aktualisiert werden sollen. Einfach mit "Ja" bestätigen, dann wird das Inhaltsverzeichnis aktualisiert.') ?>
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
                <input class="form-control" name="variables[*][label]" placeholder="<?= lang('Department ID', 'Abteilungs-ID') ?>">
            </td>
            <td>
                <input class="form-control var-default" name="variables[*][default]" placeholder="">
                <small class="text-muted copy-token" style="cursor:pointer" title="<?= lang('Copy token', 'Token kopieren') ?>">
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
        <option value="" disabled selected><?= lang('Select field', 'Feld wählen') ?></option>
        ${options}
      </select>
      <select class="form-control small w-150 flex-grow-0" name="${namePrefix}[${idx}][dir]" required>
        <option value="asc">${lang('Ascending', 'Aufsteigend')}</option><option value="desc">${lang('Descending', 'Absteigend')}</option>
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
        <option value="" disabled selected><?= lang('Select field', 'Feld wählen') ?></option>
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
            if (!confirm(lang('Changing the collection will remove any additional fields, sorting criteria, and aggregations you have set up. Do you want to continue?', 'Wenn du die Sammlung änderst, werden alle zusätzlichen Felder, Sortierkriterien und Aggregationen entfernt, die du eingerichtet hast. Möchtest du fortfahren?'))) {
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