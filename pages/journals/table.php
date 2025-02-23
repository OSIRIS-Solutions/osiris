<?php

/**
 * Page to browse through journals
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>


<h1 class="mt-0">
<i class="ph ph-stack text-primary"></i>
    <?= lang('Journals', 'Journale') ?>
</h1>

<?php
if ($Settings->hasPermission('journals.edit')) { ?>
    <div class="btn-toolbar mb-20">
        <a href="<?= ROOTPATH ?>/journal/add" class="btn primary">
            <i class="ph ph-stack-plus"></i>
            <?= lang('Add Journal', 'Journal hinzufügen') ?>
        </a>
        <a href="#check-metrics">
            <i class="ph ph-chart-line-up"></i>
            <?= lang('Check metrics', 'Metriken prüfen') ?>
        </a>
    </div>

    <!-- modal -->
    <div class="modal" id="check-metrics">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?= lang('Check metrics', 'Metriken prüfen') ?></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><?= lang('This will check the metrics for all journals. This may take a while.', 'Dies wird die Metriken für alle Journale prüfen. Dies kann eine Weile dauern.') ?></p>
                    <div class="alert danger font-weight-bold">
                        <?= lang('Attention: This will overwrite existing metrics.', 'Achtung: Dies wird bestehende Metriken überschreiben.') ?>
                    </div>
                    <p><?= lang('Do you want to continue?', 'Möchten Sie fortfahren?') ?></p>
                </div>
                <div class="modal-footer">
                    <a href="<?= ROOTPATH ?>/journal/check-metrics" class="btn primary"><?= lang('Check metrics', 'Metriken prüfen') ?></a>
                    <button type="button" class="btn secondary" data-dismiss="modal"><?= lang('Cancel', 'Abbrechen') ?></button>
                </div>
            </div>
        </div>
    </div>
<?php }
?>


<table class="table" id="result-table">
    <thead>
        <th>Journal name</th>
        <th>Publisher</th>
        <th>ISSN</th>
        <th>OA</th>
        <th><span data-toggle="tooltip" data-title="Latest impact factor if available">IF</span></th>
        <th><span data-toggle="tooltip" data-title="Publications, Reviews and Editorials"><?= lang('Activities', 'Aktivitäten') ?></span></th>
    </thead>
    <tbody>
    </tbody>
</table>


<script src="<?= ROOTPATH ?>/js/datatables/jquery.dataTables.naturalsort.js"></script>


<script>
    var dataTable;
    $(document).ready(function() {
        // dataTable = $('#result-table').DataTable({
        //     "order": [
        //         [0, 'asc'],
        //     ]
        // });
        $('#result-table').DataTable({
            ajax: ROOTPATH + '/api/journals',
            columnDefs: [{
                    "targets": 0,
                    "data": "name",
                    "render": function(data, type, full, meta) {
                        if (full.abbr && full.abbr != data) {
                            return `<a href="${ROOTPATH}/journal/view/${full.id}" class="font-weight-bold d-block">${full.abbr}</a>
                            <small class="text-muted">${data}</small>`;
                        }
                        return `<a href="${ROOTPATH}/journal/view/${full.id}" class="font-weight-bold d-block">${data}</a>`;
                    }
                },
                {
                    targets: 1,
                    data: 'publisher',
                    render: function(data, type, full, meta) {
                        return `${data}<br><small class="text-muted">${full.country ?? ''}</small>`;
                    }
                },
                {
                    targets: 2,
                    data: 'issn',
                    render: function(data, type, full, meta) {
                        return data.join('<br>');
                    },
                    className: 'unbreakable'
                },
                {
                    targets: 3,
                    data: 'open_access',
                    render: function(data, type, full, meta) {
                        if (data === 'Nein' || data == 'No') 
                            return `<span class="text-danger">${data}</span>`;
                        return `<span class="text-success">${data}</span>`;
                    },
                    className: 'unbreakable'
                },
                {
                    type: 'natural',
                    targets: 4,
                    data: 'if',
                    render: function(data, type, full, meta) {
                        if (!data) return '';
                        var impact = data.impact ?? 0;
                        if (data.year) {
                            return `<span data-toggle="tooltip" data-title="${data.year}">${impact}</span>`;
                        }
                        return impact;
                    }
                },
                {
                    type: 'natural',
                    targets: 5,
                    data: 'count'
                },
            ],
            "order": [
                [5, 'desc'],
            ],
            <?php if (isset($_GET['q'])) { ?> "oSearch": {
                    "sSearch": "<?= $_GET['q'] ?>"
                }
            <?php } ?>
        });

    });
</script>