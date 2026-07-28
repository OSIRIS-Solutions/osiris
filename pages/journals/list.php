<?php

/**
 * Page to browse through journals
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$label = $Settings->journalLabel();


$fields = $Settings->get('journal-data');
?>


<h1 class="mt-0">
    <i class="ph-duotone ph-stack"></i>
    <?= $label ?>
</h1>

<div class="btn-toolbar mb-20">
    <?php if ($Settings->hasPermission('journals.edit')) { ?>
        <a href="<?= ROOTPATH ?>/journal/add" class="btn primary">
            <i class="ph ph-stack-plus"></i>
            <?= lang("Add $label", "$label hinzufügen") ?>
        </a>
        <a href="<?= ROOTPATH ?>/journals/statistics" class="btn">
            <i class="ph ph-chart-line-up"></i>
            <?= lang('Statistics', 'Statistiken') ?>
        </a>
        <?php if (!$Settings->featureEnabled('no-journal-metrics')) { ?>
            <a href="<?= ROOTPATH ?>/journal/metrics">
                <i class="ph ph-ranking"></i>
                <?= lang('Check metrics', 'Metriken prüfen') ?>
            </a>
        <?php } ?>
    <?php } ?>
</div>


<table class="table" id="result-table">
    <thead>
        <th><?= $label ?> name</th>
        <th>Publisher</th>
        <th>ISSN</th>
        <th>OA</th>
        <th><span data-toggle="tooltip" data-title="Latest <?= $Settings->impactLabel() ?> if available"><?= $Settings->impactLabel() ?></span></th>
        <th><span data-toggle="tooltip" data-title="Publications, Reviews and Editorials"><?= lang('Activities', 'Aktivitäten') ?></span></th>
        <?php foreach ($fields as $f) {
            echo "<th>$f</th>";
        } ?>
    </thead>
    <tbody>
    </tbody>
</table>



<script>
    var dataTable;
    $(document).ready(function() {
        columns = <?= json_encode(range(0, 5 + count($fields))) ?>;
        dataTable = $('#result-table').DataTable({
            ajax: ROOTPATH + '/api/journals',
            buttons: downloadTableButtons('<?= $label ?>', columns, true),
            columnDefs: [{
                    "targets": 0,
                    "data": "name",
                    "render": function(data, type, full, meta) {
                        if (type === 'export') {
                            return data;
                        }
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
                        if (type === 'export') {
                            return data;
                        }
                        return `${data}<br><small class="text-muted">${full.country ?? ''}</small>`;
                    }
                },
                {
                    targets: 2,
                    data: 'issn',
                    defaultContent: '',
                    // visible: false,
                    // searchable: true,
                    render: function(data, type, full, meta) {
                        if (!data) return '';
                        if (Array.isArray(data)) {
                            return data.join(', ');
                        }
                        return data;
                    },
                    // className: 'unbreakable'
                },
                {
                    targets: 3,
                    data: 'open_access',
                    defaultContent: '-',
                    render: function(data, type, full, meta) {
                        if (type === 'export') {
                            return data;
                        }
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
                        if (type === 'export') {
                            return data;
                        }
                        if (!data) {
                            return type === 'sort' ? 0 : '-';
                        }
                        var impact = data.impact ?? 0;

                        // Used for sorting, but not displayed
                        if (type === 'sort' || type === 'type') {
                            return impact;
                        }

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
                <?php
                $i = 6;
                foreach ($fields as $f) {
                    echo "{ targets: $i, data: '$f', defaultContent: '', visible: false },";
                    $i++;
                } ?>
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