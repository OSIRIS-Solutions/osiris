<?php

/** 
 * This file provides a template editor to create and edit reports.
 * A report may consists of text blocks (markdown), paragraphs with filtered activities, and tables with aggregated numbers.
 */

// report is defined in the controller

include_once BASEPATH . "/php/fields.php";
$FIELDS = new Fields();
$fields = array_filter($FIELDS->fields, function ($f) {
    return !empty($f['module_of']) && in_array('aggregate', $f['usage']);
});
?>

<style>
    .step {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid var(--border-color);
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
    <i class="ph ph-report"></i>
    <?= lang('Report Builder', 'Berichtseditor') ?>
</h1>


<!-- modules to copy -->
<div class="hidden" id="templates" style="display:none">
    <div class="step" id="text">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-text-t text-secondary"></i>
            <span class="step-title"><?= lang('Text', 'Text') ?></span>
            <select name="values[*][level]" class="form-control small w-auto d-inline-block ml-10" required>
                <option value="h1"><?= lang('Heading 1', 'Überschrift 1') ?></option>
                <option value="h2"><?= lang('Heading 2', 'Überschrift 2') ?></option>
                <option value="h3"><?= lang('Heading 3', 'Überschrift 3') ?></option>
                <option value="p"><?= lang('Paragraph', 'Absatz') ?></option>
            </select>
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

            <div class="mt-10">
                <textarea type="text" class="form-control" name="values[*][text]" placeholder="<?= lang('Content', 'Inhalt') ?>" required></textarea>
            </div>
        </div>
    </div>

    <div class="step" id="activities">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-text-t text-secondary"></i>
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
            <textarea type="text" class="form-control" name="values[*][filter]" placeholder="Filter" required></textarea>
            <small>
                <?= lang('Find filters in the <a href="' . ROOTPATH . '/activities/search" target="_blank">advanced search</a> and copy from "Show filter".', 'Filter findest du in der <a href="' . ROOTPATH . '/activities/search" target="_blank">erweiterten Suche</a> und kannst sie von "Zeige Filter" kopieren.') ?>
            </small>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked>
                <label for="timelimit"><?= lang('Limit to reporting time', 'Auf den Berichtszeitraum beschränken') ?></label>
            </div>
            <div class="mt-10">
                <label class="d-block mb-5"><?= lang('Sorting', 'Sortierung') ?></label>
                <div class="sort-rows" data-name="values[*][sort]"><!-- rows injected by JS --></div>
                <button type="button" class="btn small" onclick="addSortRow(this)"><?= lang('Add criterion', '+ Kriterium') ?></button>
            </div>
        </div>
    </div>

    <div class="step" id="activities-impact">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-text-t text-secondary"></i>
            <span class="step-title"><?= lang('Activities (incl. Impact)', 'Aktivitäten (mit Impact)') ?></span>
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
            <input type="hidden" class="hidden" name="values[*][type]" value="activities-impact">
            <textarea type="text" class="form-control" name="values[*][filter]" placeholder="Filter" required></textarea>
            <small>
                <?= lang('Find filters in the <a href="' . ROOTPATH . '/activities/search" target="_blank">advanced search</a> and copy from "Show filter".', 'Filter findest du in der <a href="' . ROOTPATH . '/activities/search" target="_blank">erweiterten Suche</a> und kannst sie von "Zeige Filter" kopieren.') ?>
            </small>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked>
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
            <i class="ph ph-text-t text-secondary"></i>
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
            <textarea type="text" class="form-control" name="values[*][filter]" placeholder="Filter" required></textarea>

            <div class="form-row row-eq-spacing mt-10">
                <div class="col">
                    <label for="aggregate"><?= lang('First aggregation', 'Erste Aggregation') ?></label>
                    <select name="values[*][aggregate]" required class="form-control">
                        <?php foreach ($fields as $f) { ?>
                            <option value="<?= htmlspecialchars($f['id']) ?>"><?= $f['label'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col">
                    <label for="aggregate2"><?= lang('Second aggregation', 'Zweite Aggregation (optional)') ?></label>
                    <select name="values[*][aggregate2]" class="form-control">
                        <option value=""><?= lang('Without second aggregation', 'Ohne zweite Aggregation') ?></option>
                        <?php foreach ($fields as $f) { ?>
                            <option value="<?= htmlspecialchars($f['id']) ?>"><?= $f['label'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="mt-10">
                <input type="checkbox" name="values[*][timelimit]" value="1" checked>
                <label for="timelimit"><?= lang('Limit to reporting time', 'Auf den Berichtszeitraum beschränken') ?></label>
            </div>
        </div>
    </div>

    <div class="step" id="line">
        <div class="step-header">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <i class="ph ph-text-t text-secondary"></i>
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

</div>


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

    <!-- toolbar -->
    <div class="text-right mb-10">
        <!-- collapse all -->
        <button type="button" class="btn small" onclick="$('.step').addClass('is-collapsed')">
            <i class="ph ph-arrows-in-line-vertical"></i>
            <?= lang('Collapse all', 'Alle einklappen') ?>
        </button>
        <button type="button" class="btn small" onclick="$('.step').removeClass('is-collapsed')">
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
            <a class="item" onclick="addRow('activities-impact')">
                <b class="text-primary d-block"><?= lang('Activities (incl. Impact)', 'Aktivitäten (mit Impact)') ?></b>
                <small class="text-muted"><?= lang('A block that contains a table of activities with impact in a seperate column', 'Ein Block, der eine Tabelle von Aktivitäten mit Impact in einer separaten Spalte enthält') ?></small>
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

<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/reports.js"></script>

<script>
    var n = 0;

    function addRow(type) {
        var tr = $('#' + type).clone();
        tr.html(tr.html().replace(/\*/g, n));
        n++;
        $('#report').append(tr);
    }


    // Toggle + Duplicate
    function toggleStep(btn) {
        $(btn).closest('.step').toggleClass('is-collapsed');
    }

    function duplicateStep(btn) {
        const $orig = $(btn).closest('.step');
        const $clone = $orig.clone(true, true);
        // re-index names (*) -> n
        $clone.html($clone.html().replace(/\[\*\]/g, '[' + n + ']'));
        n++;
        $('#report').append($clone);
    }

    // Add one sort row to the nearest .sort-rows container
    function addSortRow(elOrContainer, data) {
        const $container = $(elOrContainer).hasClass('sort-rows') ? $(elOrContainer) : $(elOrContainer).closest('.step-body').find('.sort-rows');
        const base = $container.data('name'); // e.g. values[*][sort]
        const idx = $container.children('.sort-row').length;
        const namePrefix = base.replace('*', getIndexFromContainer($container));
        const row = $(`
    <div class="sort-row d-flex align-items-center gap-5 mb-5">
      <input class="form-control w-40" placeholder="field" name="${namePrefix}[${idx}][field]">
      <select class="form-control w-20" name="${namePrefix}[${idx}][dir]">
        <option value="asc">asc</option><option value="desc">desc</option>
      </select>
      <select class="form-control w-20" name="${namePrefix}[${idx}][nulls]">
        <option value="">nulls default</option>
        <option value="first">nulls first</option>
        <option value="last">nulls last</option>
      </select>
      <button type="button" class="btn link btn-icon" title="Remove" onclick="$(this).closest('.sort-row').remove()">
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

    // Helper: find the numeric index actually used in this block (replaces *)
    function getIndexFromContainer($container) {
        // Find any input name under step and extract [N]
        const $inp = $container.closest('.step').find('input,textarea,select').first();
        const m = ($inp.attr('name') || '').match(/\[(\d+)\]/);
        return m ? m[1] : n; // fallback
    }


    $(document).ready(function() {
        var steps = <?= json_encode($steps) ?>;
        console.log(steps);
        steps.forEach(step => {
            var tr = $('#' + step.type).clone();

            // replace * with n
            tr.html(tr.html().replace(/\*/g, n));
            n++;

            tr.find('input, textarea, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    var parts = name.split('[');
                    if (parts.length < 3) return;
                    var key = parts[2].replace(']', '');
                    // checkboxes and selected
                    if ($(this).attr('type') == 'checkbox') {
                        $(this).prop('checked', step[key] ? true : false);
                    }
                    // select
                    else if ($(this).is('select') && step[key]) {
                        $(this).find('option[value="' + step[key] + '"]').prop('selected', true);
                    } else if (step[key]) {
                        $(this).val(step[key]);
                    }
                }
            });

            $('#report').append(tr);
        });
        $('#report').sortable({
            handle: ".handle"
        });

        // For each loaded step: if step.sort exists -> generate rows
        steps.forEach((step, i) => {
            const $block = $('#report .step').eq(i);
            if (step.sort && Array.isArray(step.sort)) {
                const $rows = $block.find('.sort-rows');
                step.sort.forEach(rule => addSortRow($rows, rule));
            }
        });
    });
</script>