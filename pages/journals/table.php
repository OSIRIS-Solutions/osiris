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
        <?php if (!$Settings->featureEnabled('no-journal-metrics')) { ?>
            <a href="<?= ROOTPATH ?>/journal/metrics">
                <i class="ph ph-ranking"></i>
                <?= lang('Check metrics', 'Metriken prüfen') ?>
            </a>
        <?php } ?>
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
        dataTable = $('#result-table').DataTable({
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
                    defaultContent: '',
                    render: function(data, type, full, meta) {
                        return `${data}<br><small class="text-muted">${full.country ?? ''}</small>`;
                    }
                },
                {
                    targets: 2,
                    data: 'issn',
                    defaultContent: '',
                    render: function(data, type, full, meta) {
                        if (!data) return '';
                        if (Array.isArray(data)) {
                            return data.join('<br>');
                        }
                        return data;
                    },
                    className: 'unbreakable'
                },
                {
                    targets: 3,
                    data: 'open_access',
                    defaultContent: '-',
                    render: function(data, type, full, meta) {
                        if (data === 'Nein' || data == 'No' || data === 'false' || data === false)
                            return `<span class="text-danger">${lang('No', 'Nein')}</span>`;
                        if (data === 'Ja' || data == 'Yes' || data === 'true' || data === true)
                            return `<span class="text-success">${lang('Yes', 'Ja')}</span>`;
                        return data;
                    },
                    className: 'unbreakable'
                },
                {
                    type: 'natural',
                    targets: 4,
                    data: 'if',
                    defaultContent: '-',
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
                    data: 'count',
                    defaultContent: 0
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