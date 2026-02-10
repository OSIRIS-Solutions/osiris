<?php

/** 
 * This file provides a template editor to create and edit reports.
 * A report may consists of text blocks (markdown), paragraphs with filtered activities, and tables with aggregated numbers.
 */

// report is defined in the controller

include_once BASEPATH . "/php/activity_fields.php";
$FIELDS = new ActivityFields();
$fields_aggregate = array_filter($FIELDS->fields, function ($f) {
    return !empty($f['module_of']) && in_array('aggregate', $f['usage']);
});
$fields_sort = array_filter($FIELDS->fields, function ($f) {
    return !empty($f['module_of']) && in_array('filter', $f['usage']);
});
?>

<style>
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
        margin-top: .5rem;
    }

    .step.is-collapsed .step-body {
        display: none;
    }

    .step.is-collapsed .collapse-btn i:before {
        content: "\e536";
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
</style>

<?php if (!empty($report) && isset($report['_id'])) { ?>
    <div class="btn-toolbox  float-right">
        <a href="<?= ROOTPATH ?>/admin/reports/preview/<?= $report['_id'] ?>" class="btn primary">
            <i class="ph ph-eye"></i>
            <?= lang('Preview', 'Vorschau') ?>
        </a>
        <!-- Help -->
        <a href="https://wiki.osiris-app.de/users/reporting/" class="btn tour" target="_blank">
            <i class="ph ph-question"></i>
            <?= lang('Help', 'Hilfe') ?>
        </a>
    </div>
<?php } ?>


<h1>
    <i class="ph-duotone ph-clipboard-text"></i>
    <?= lang('Report Builder', 'Berichtseditor') ?>
</h1>


<form action="<?= ROOTPATH ?>/crud/reports/update" method="post">
    <input type="hidden" name="id" value="<?= $report['_id'] ?>">
    <div class="form-group">
        <label for="title"><?= lang('Title', 'Titel') ?></label>
        <input type="text" class="form-control" name="title" value="<?= $report['title'] ?? '' ?>" required>
    </div>
    <div class="form-group">
        <label for="description"><?= lang('Description', 'Beschreibung') ?></label>
        <textarea type="text" class="form-control" name="description"><?= $report['description'] ?? '' ?></textarea>
    </div>

    <!-- start month and duration -->
    <div class="form-row row-eq-spacing">
        <div class="col-sm">
            <label for="start"><?= lang('Start month', 'Startmonat') ?></label>
            <input type="number" class="form-control" name="start" id="start" value="<?= $report['start'] ?? '' ?>" required>
        </div>
        <div class="col-sm">
            <label for="duration"><?= lang('Duration in months', 'Dauer in Monaten') ?></label>
            <input type="number" class="form-control" name="duration" id="duration" value="<?= $report['duration'] ?? '' ?>" required>
        </div>
    </div>

    <hr>

    <h3>
        <?= lang('Template building blocks', 'Template-Bausteine') ?>
    </h3>


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

    <!-- toolbar -->
    <div class="d-flex align-items-center gap-5 mb-10">
        <a href="#variables" class="btn">
            <i class="ph ph-code-block"></i>
            <?= lang('Variables', 'Variablen') ?>
        </a>

        <!-- collapse all -->
        <button type="button" class="btn ml-auto" onclick="$('.step').addClass('is-collapsed')">
            <i class="ph ph-arrows-in-line-vertical"></i>
            <?= lang('Collapse all', 'Alle einklappen') ?>
        </button>
        <button type="button" class="btn" onclick="$('.step').removeClass('is-collapsed')">
            <i class="ph ph-arrows-out-line-vertical"></i>
            <?= lang('Expand all', 'Alle ausklappen') ?>
        </button>
    </div>

    <div id="report">
        <!-- steps will be added here -->
    </div>

    <!-- dropdown to add stuff -->
    <div class="dropdown dropup">
        <button class="btn primary dropdown-toggle" type="button" id="addNewRowButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="ph ph-plus"></i>
            <?= lang('Add new block', 'Neuen Baustein hinzufügen') ?>
        </button>
        <div class="dropdown-menu" aria-labelledby="addNewRowButton">
            <a class="item" onclick="addRow('text')">
                <b class="text-primary d-block"><?= lang('Text', 'Text') ?></b>
                <small class="text-muted"><?= lang('A block that contains headings or paragraphs', 'Ein Block, der Überschriften oder Absätze enthält') ?></small>
            </a>
            <a class="item" onclick="addRow('activities')">
                <b class="text-primary d-block"><?= lang('Activities', 'Aktivitäten') ?></b>
                <small class="text-muted"><?= lang('A block that contains a list of activities', 'Ein Block, der eine Liste von Aktivitäten enthält') ?></small>
            </a>
            <a class="item" onclick="addRow('activities-field')">
                <b class="text-primary d-block"><?= lang('Activities (incl. additional Feld)', 'Aktivitäten (mit weiterem Feld)') ?></b>
                <small class="text-muted"><?= lang('A block that contains a table of activities with another field in a seperate column', 'Ein Block, der eine Tabelle von Aktivitäten mit einem weiteren Feld in einer separaten Spalte enthält') ?></small>
            </a>
            <a class="item" onclick="addRow('table')">
                <b class="text-primary d-block"><?= lang('Table', 'Tabelle') ?></b>
                <small class="text-muted"><?= lang('A block that contains a table of aggregated activities', 'Ein Block, der eine Tabelle von aggregierten Aktivitäten enthält') ?></small>
            </a>
            <a class="item" onclick="addRow('line')">
                <b class="text-primary d-block"><?= lang('Line', 'Linie') ?></b>
                <small class="text-muted"><?= lang('A simple line to divide content', 'Eine einfache Linie zur Trennung von Inhalten') ?></small>
            </a>
        </div>
    </div>

    <div class="mt-20">
        <button class="btn large success" type="submit">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save', 'Speichern') ?>
        </button>
    </div>
</form>



<!-- modules to copy -->
<div class="hidden" id="templates" style="display:none">
    <div class="step" id="text">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-text-t ph-fw text-secondary"></i>
            <span class="step-title"><?= lang('Text', 'Text') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <div class="step-body">
            <input type="hidden" class="hidden" name="values[*][type]" value="text">

            <select name="values[*][level]" class="form-control small w-auto step-level" required>
                <option value="h1"><?= lang('Heading 1', 'Überschrift 1') ?></option>
                <option value="h2"><?= lang('Heading 2', 'Überschrift 2') ?></option>
                <option value="h3"><?= lang('Heading 3', 'Überschrift 3') ?></option>
                <option value="p"><?= lang('Paragraph', 'Absatz') ?></option>
            </select>
            <!-- <div class="mt-10">
                <textarea type="text" class="form-control step-text" name="values[*][text]" placeholder="<?= lang('Content', 'Inhalt') ?>" required></textarea>
            </div> -->
            <div class="form-group lang-<?= lang('en', 'de') ?>">
                <div class="title-editor form-group"></div>
                <input type="text" class="form-control step-text hidden" name="values[*][text]" id="title" required value="">
            </div>

        </div>
    </div>

    <div class="step" id="activities">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-article ph-fw text-secondary"></i>
            <span class="step-title"><?= lang('Activities', 'Aktivitäten') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="$(this).closest('.step').remove()" title="Delete">
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
            <i class="ph ph-columns-plus-right ph-fw text-secondary"></i>
            <span class="step-title"><?= lang('Activities (incl. additional Field)', 'Aktivitäten (mit weiterem Feld)') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="$(this).closest('.step').remove()" title="Delete">
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
                    $fields_add = array_filter($fields_sort, function ($f) {
                        return $f['type'] !== 'boolean' && $f['type'] !== 'list' && !str_starts_with($f['id'], 'authors.');
                    });
                    foreach ($fields_add as $f) { ?>
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
            <i class="ph ph-table ph-fw text-secondary"></i>
            <span class="step-title"><?= lang('Table', 'Tabelle') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <div class="step-body">
            <input type="hidden" class="hidden" name="values[*][type]" value="table">
            <textarea type="text" class="form-control step-filter" name="values[*][filter]" placeholder="Filter" required>{}</textarea>

            <div class="form-row row-eq-spacing mt-10">
                <div class="col">
                    <label for="aggregate"><?= lang('First aggregation', 'Erste Aggregation') ?></label>
                    <select name="values[*][aggregate]" required class="form-control step-aggregate">
                        <?php foreach ($fields_aggregate as $f) { ?>
                            <option value="<?= e($f['id']) ?>"><?= $f['label'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col">
                    <label for="aggregate2"><?= lang('Second aggregation', 'Zweite Aggregation (optional)') ?></label>
                    <select name="values[*][aggregate2]" class="form-control step-aggregate2">
                        <option value=""><?= lang('Without second aggregation', 'Ohne zweite Aggregation') ?></option>
                        <?php foreach ($fields_aggregate as $f) { ?>
                            <option value="<?= e($f['id']) ?>"><?= $f['label'] ?></option>
                        <?php } ?>
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
                        <option value="aggregation-asc"><?= lang('Name of aggregation asc', 'Name der Aggregation aufsteigend') ?></option>
                        <option value="aggregation-desc"><?= lang('Name of aggregation desc', 'Name der Aggregation absteigend') ?></option>
                        <option value="count-asc"><?= lang('Count ascending', 'Anzahl aufsteigend') ?></option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="step" id="line">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-minus ph-fw text-secondary"></i>
            <span class="step-title"><?= lang('Line', 'Trennlinie') ?></span>
            <button type="button" class="btn link btn-icon collapse-btn" onclick="toggleStep(this)" title="Collapse/Expand">
                <i class="ph ph-arrows-in-line-vertical"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="duplicateStep(this)" title="Duplicate">
                <i class="ph ph-copy"></i>
            </button>
            <button type="button" class="btn link btn-icon" onclick="$(this).closest('.step').remove()" title="Delete">
                <i class="ph ph-trash" aria-label="Delete"></i>
            </button>
        </div>
        <input type="hidden" class="hidden" name="values[*][type]" value="line">
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

        // replace [*] → [varIndex]
        $tpl.find('input,select,textarea').each(function() {
            const name = $(this).attr('name');
            if (!name) return;
            $(this).attr('name', name.replace('[*]', '[' + templateIndex + ']'));
        });
        // prefill
        if (data) {
            $tpl.find('.step-text').val(data.text || '');
            $tpl.find('.step-level').val(data.level || 'p');
            $tpl.find('.step-filter').val(data.filter || '');
            $tpl.find('.step-timelimit').prop('checked', data.timelimit ? true : false);
            $tpl.find('.step-aggregate').val(data.aggregate || '');
            $tpl.find('.step-aggregate2').val(data.aggregate2 || '');
            $tpl.find('.step-field').val(data.field || '');
            // sort rows
            if (data.sort && Array.isArray(data.sort)) {
                data.sort.forEach(sortCriterion => {
                    addSortRow($tpl.find('.sort-rows'), sortCriterion);
                });
            } 
            if (data.table_sort && typeof data.table_sort === 'string') {
                $tpl.find('.step-select').val(data.table_sort);
            }
        }
        $('#report').append($tpl);

        if (type === 'text') {
            // init editor
            const editorId = 'title-editor-' + templateIndex;
            const editorInput = $tpl.find('.title-editor');
            if (data) {
                editorInput.html(data.text || '');
            }
            editorInput.attr('id', editorId);
            editorInput.next().attr('id', editorId + '-field');
            initQuill(editorInput.get(0), 'full');
        }

        templateIndex++;
    }


    // Toggle + Duplicate
    function toggleStep(btn) {
        $(btn).closest('.step').toggleClass('is-collapsed');
    }

    function duplicateStep(btn) {
        const $orig = $(btn).closest('.step');
        const $clone = $orig.clone(true, true);
        // re-index names (*) -> n
        $clone.html($clone.html().replace(/\[\*\]/g, '[' + templateIndex + ']'));
        templateIndex++;
        $('#report').append($clone);
    }

    // Add one sort row to the nearest .sort-rows container
    function addSortRow(elOrContainer, data) {
        const $container = $(elOrContainer).hasClass('sort-rows') ? $(elOrContainer) : $(elOrContainer).closest('.step-body').find('.sort-rows');
        const base = $container.data('name'); // e.g. values[*][sort]
        const idx = $container.children('.sort-row').length;
        const namePrefix = base.replace('*', getIndexFromContainer($container));
        // copy options from select fields
        const row = $(`
    <div class="sort-row d-flex align-items-center gap-5 mb-5">
      <select class="form-control small w-200 flex-grow-0" placeholder="field" name="${namePrefix}[${idx}][field]" required>
        <option value="" disabled selected><?= lang('Select field', 'Feld wählen') ?></option>
        <?php foreach ($fields_sort as $f) { ?>
            <option value="<?= e($f['id']) ?>"><?= $f['label'] ?></option>
        <?php } ?>
      </select>
      <select class="form-control small w-150 flex-grow-0" name="${namePrefix}[${idx}][dir]" required>
        <option value="asc">${lang('Ascending', 'Aufsteigend')}</option><option value="desc">${lang('Descending', 'Absteigend')}</option>
      </select>
      <button type="button" class="btn small link text-danger" title="Remove" onclick="$(this).closest('.sort-row').remove()">
        <i class="ph ph-x"></i>
      </button>
    </div>
  `);
        // <select class="form-control w-20" name="${namePrefix}[${idx}][nulls]">
        //         <option value="">nulls default</option>
        //         <option value="first">nulls first</option>
        //         <option value="last">nulls last</option>
        //       </select>
        $container.append(row);

        if (data) { // prefill
            row.find(`[name$="[field]"]`).val(data.field || '');
            row.find(`[name$="[dir]"]`).val((data.dir || 'asc').toLowerCase());
            row.find(`[name$="[nulls]"]`).val(data.nulls || '');
        }
    }

    // Helper: find the numeric index actually used in this block (replaces *)
    function getIndexFromContainer($container) {
        // Find any input name under step and extract [N]
        const $inp = $container.closest('.step').find('input,textarea,select').first();
        const m = ($inp.attr('name') || '').match(/\[(\d+)\]/);
        return m ? m[1] : n; // fallback
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
        var steps = <?= json_encode($steps) ?>;
        console.log(steps);
        // load existing steps
        steps.forEach(step => addRow(step.type, step));

        $('#report').sortable({
            handle: ".handle"
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
</script>