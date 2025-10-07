<?php

/** 
 * This file provides a template editor to create and edit reports.
 * A report may consists of text blocks (markdown), paragraphs with filtered activities, and tables with aggregated numbers.
 */

// report is defined in the controller

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
<div class="hidden">
    <div class="step" id="text">
        <i class="ph ph-dots-six-vertical text-muted handle"></i>
        <i class="ph ph-text-t text-secondary ph-2x"></i>
        <select name="values[*][level]" class="form-control small w-auto d-inline-block ml-10" required>
            <option value="h1"><?= lang('Heading 1', 'Überschrift 1') ?></option>
            <option value="h2"><?= lang('Heading 2', 'Überschrift 2') ?></option>
            <option value="h3"><?= lang('Heading 3', 'Überschrift 3') ?></option>
            <option value="p"><?= lang('Paragraph', 'Absatz') ?></option>
        </select>
                <button type="button" class="btn link float-right" onclick="$(this).closest('.step').remove()"><i class="ph ph-trash" aria-label="Delete"></i></button>

        <input type="hidden" class="hidden" name="values[*][type]" value="text">

        <div class="mt-10">
            <textarea type="text" class="form-control" name="values[*][text]" placeholder="<?= lang('Content', 'Inhalt') ?>" required></textarea>
        </div>
    </div>
    <div class="step" id="activities">
        <h4>
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <?= lang('Activities', 'Aktivitäten') ?>
            <button type="button" class="btn link" onclick="$(this).closest('.step').remove()"><i class="ph ph-trash" aria-label="Delete"></i></button>
        </h4>
        <input type="hidden" class="hidden" name="values[*][type]" value="activities">
        <textarea type="text" class="form-control" name="values[*][filter]" placeholder="Filter" required></textarea>
        <div class="mt-10">
            <input type="checkbox" name="values[*][timelimit]" value="1">
            <label for="timelimit"><?= lang('Limit to selected time frame', 'Auf den gewählten Zeitraum beschränken') ?></label>
        </div>
    </div>
    <div class="step" id="table">
        <h4>
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <?= lang('Table', 'Tabelle') ?>
            <button type="button" class="btn link" onclick="$(this).closest('.step').remove()"><i class="ph ph-trash" aria-label="Delete"></i></button>
        </h4>
        <input type="hidden" class="hidden" name="values[*][type]" value="table">
        <textarea type="text" class="form-control" name="values[*][filter]" placeholder="Filter" required></textarea>

        <div class="form-row row-eq-spacing mt-10">
            <div class="col">
                <label for="aggregate"><?= lang('First aggregation', 'Erste Aggregation') ?></label>
                <input type="text" class="form-control" name="values[*][aggregate]" required>
            </div>
            <div class="col">
                <label for="aggregate2"><?= lang('Second aggregation', 'Zweite Aggregation (optional)') ?></label>
                <input type="text" class="form-control" name="values[*][aggregate2]">
            </div>
        </div>
        <div class="mt-10">
            <input type="checkbox" name="values[*][timelimit]" value="1">
            <label for="timelimit"><?= lang('Limit to selected time frame', 'Auf den gewählten Zeitraum beschränken') ?></label>
        </div>
    </div>
    <div class="step" id="line">
        <h4 class="m-0">
            <i class="ph ph-dots-six-vertical text-muted handle"></i>
            <?= lang('Line', 'Trennlinie') ?>
            <button type="button" class="btn link" onclick="$(this).closest('.step').remove()"><i class="ph ph-trash" aria-label="Delete"></i></button>
        </h4>
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
    <div id="report">
        <!-- steps will be added here -->
    </div>

    <!-- dropdown to add stuff -->
    <div class="dropdown">
        <button class="btn secondary dropdown-toggle" type="button" id="addNewRowButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <?= lang('Add', 'Hinzufügen') ?>
        </button>
        <div class="dropdown-menu" aria-labelledby="addNewRowButton">
            <a class="item" onclick="addRow('text')"><?= lang('Text', 'Text') ?></a>
            <a class="item" onclick="addRow('activities')"><?= lang('Activities', 'Aktivitäten') ?></a>
            <a class="item" onclick="addRow('table')"><?= lang('Table', 'Tabelle') ?></a>
            <a class="item" onclick="addRow('line')"><?= lang('Line', 'Linie') ?></a>
        </div>
    </div>

    <button class="btn success" type="submit"><?= lang('Save', 'Speichern') ?></button>
</form>

<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/reports.js"></script>

<script>
    var n = 0;
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
                    if ($(this).attr('type') == 'checkbox' && step[key]) {
                        $(this).prop('checked', true);
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
            handle: ".handle",
            // change: function( event, ui ) {}
        });
    })

    function addRow(type) {
        var tr = $('#' + type).clone();
        tr.html(tr.html().replace(/\*/g, n));
        n++;
        $('#report').append(tr);
    }
</script>