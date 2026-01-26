<?php

/**
 * Display all documents in the system.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.8.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<style>
    #uploadsTable .badge {
        font-weight: 500;
    }

    #uploadsTable td {
        vertical-align: middle;
    }

    #uploadsTable .btn-group .btn {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }
</style>
<h1>
    <i class="ph-duotone ph-files"></i>
    <?= lang("Documents", "Dokumente") ?>
</h1>

<div class="row row-eq-spacing">
    <div class="col">
        <table id="uploadsTable" class="table table-hover align-middle">
            <thead>
                <tr>
                    <th><?= lang('File', 'Datei') ?></th>
                    <th><?= lang('Linked to', 'Verknüpft mit') ?></th>
                    <th class="text-end"><?= lang('Actions', 'Aktionen') ?></th>
                    <th><?= lang('Document type', 'Dokumententyp') ?></th>
                    <th><?= lang('File type', 'Dateityp') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $doc):
                    $file_url = ROOTPATH . '/uploads/' . $doc['_id'] . '.' . $doc['extension'];

                    $id = DB::to_ObjectID($doc['id']);
                    $icon = null;
                    $con = null;

                    $vocabs = [
                        'activities' => 'activity-document-types',
                        'nagoya-permit' => 'nagoya-document-types',
                        'proposals' => 'proposal-document-types',
                    ];
                    if (!isset($vocabs[$doc['type']])) continue;

                    switch ($doc['type']) {
                        case 'activities':
                            $con = $osiris->activities->findOne(['_id' => $id], ['projection' => ['name' => '$rendered.title', 'type' => 1, 'icon' => '$rendered.icon']]);
                            $icon = $con['icon'];
                            break;
                        case 'proposals':
                        case 'nagoya-permit':
                            $icon = '<i class="ph ph-tree-structure"></i>';
                            $con = $osiris->proposals->findOne(['_id' => $id], ['projection' => ['name' => 1, 'type' => 1]]);
                            break;
                        default:
                            continue 2;
                    }

                    $label = $Vocabulary->getValue($vocabs[$doc['type']], $doc['name'] ?? '', lang('Other', 'Sonstiges'));

                    $uploader = $DB->getNameFromId($doc['uploaded_by']);
                    $date = !empty($doc['uploaded']) ? date('d.m.Y', strtotime($doc['uploaded'])) : '';
                    $size = number_format((int)($doc['size'] ?? 0), 0, ',', '.');
                    $filename = $doc['filename'] ?? '';
                    $desc = trim($doc['description'] ?? '');
                    $entityType = ucfirst($con['type'] ?? $doc['type']);
                    $entityName = $con['name'] ?? lang('Unknown', 'Unbekannt');
                ?>
                    <tr>
                        <!-- FILE -->
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="pt-5 font-size-18">
                                    <i class="ph ph-<?= getFileIcon($doc['extension'] ?? '') ?> text-muted"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-10">
                                        <a href="<?= $file_url ?>" class="badge primary" target="_blank" rel="noopener">
                                            <?= $label ?>
                                            <i class="ph ph-download ml-5 font-size-16"></i>
                                        </a>
                                    </div>

                                    <?php if ($desc !== ''): ?>
                                        <div class="text-muted font-size-12"><?= nl2br(htmlspecialchars($desc)) ?></div>
                                    <?php endif; ?>

                                    <div class="text-muted font-size-12 mt-5">
                                        <?= lang('File name', 'Dateiname') ?>: <?= htmlspecialchars($filename) ?> <br>
                                        <?= lang('Uploaded by', 'Hochgeladen von') ?> <?= htmlspecialchars($uploader) ?>
                                        <?= lang('on', 'am') ?> <?= htmlspecialchars($date) ?>
                                        · <?= htmlspecialchars($size) ?> <?= lang('Bytes', 'Bytes') ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- LINKED ENTITY -->
                        <td>
                            <div class="d-flex align-items-center gap-10">
                                <div class="pt-5 font-size-18">
                                    <?= $icon ?>
                                </div>
                                <div>
                                    <div class="text-muted font-size-12"><?= ($entityType) ?></div>
                                    <div class="">
                                        <a href="<?= ROOTPATH ?>/activities/view/<?= $con['_id'] ?? '' ?>">
                                            <?= get_preview($entityName, 100) ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- ACTIONS -->
                        <td class="text-end">
                            <div class="btn-group">
                                <a class="btn small" href="<?= $file_url ?>" target="_blank" rel="noopener">
                                    <i class="ph ph-arrow-square-out"></i>
                                </a>
                                <a class="btn small" href="<?= $file_url ?>" download>
                                    <i class="ph ph-download"></i>
                                </a>
                            </div>
                        </td>
                        <td><?= $label ?></td>
                        <td><?= strtoupper($doc['extension'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="col-lg-3 d-none d-lg-block">
        <div class="filters content" id="filters">
            <div class="title">Filter</div>

            <div id="active-filters"></div>
            <h6>
                <?= lang('By document type', 'Nach Dokumententyp') ?>
                <a class="float-right" onclick="filterDataTable('#filter-category .active', null, 3)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-category" class="table small simple">
                </table>
            </div>

            <h6>
                <?= lang('By file type', 'Nach Dateityp') ?>
                <a class="float-right" onclick="filterDataTable('#filter-type .active', null, 4)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-type" class="table small simple">
                </table>
            </div>
        </div>
    </div>
</div>


<script>
    const headers = [
        { key: 'file', title: '<?= lang("File", "Datei") ?>' },
        { key: 'linked_to', title: '<?= lang("Linked to", "Verknüpft mit") ?>' },
        { key: 'actions', title: '<?= lang("Actions", "Aktionen") ?>' },
        { key: 'document_type', title: '<?= lang("Document type", "Dokumententyp") ?>' },
        { key: 'file_type', title: '<?= lang("File type", "Dateityp") ?>' },
    ];
    let dataTable = $('#uploadsTable').DataTable({
        pageLength: 25,
        columns: [{
                orderable: true
            },
            {
                orderable: true
            },
            {
                orderable: false,
                searchable: false
            },
            {
                visible: false,
                searchable: true
            },
            {
                visible: false,
                searchable: true
            },
        ],
        order: [
            [0, 'asc']
        ],
    });

    createFilterTable(3, '#filter-category');
    createFilterTable(4, '#filter-type');


    function createFilterTable(columnIndex, filter) {
        // get unique values from the specified column
        var uniqueValues = {};
        dataTable.column(columnIndex).data().each(function(value, index) {
            if (value in uniqueValues) {
                uniqueValues[value]++;
            } else {
                uniqueValues[value] = 1;
            }
        });
        console.log(uniqueValues);
        // sort by number of entries descending
        uniqueValues = Object.fromEntries(
            Object.entries(uniqueValues).sort(([, a], [, b]) => b - a)
        );

        // create table rows for each unique value
        var filterTable = $(filter);
        $.each(uniqueValues, function(value, count) {
            var row = $('<tr></tr>');
            var cell = $('<td></td>');
            var link = $('<a href="#" class="filter-link"></a>');
            link.html(value + ` <span class="index">${count}</span>`);
            link.on('click', function(e) {
                e.preventDefault();
                filterDataTable(this, value, columnIndex);
            });
            cell.append(link);
            row.append(cell);
            filterTable.append(row);
        });

    }

    
    const activeFilters = $('#active-filters')
    function filterDataTable(btn, filter = null, column = 1) {
        var tr = $(btn).closest('tr')
        var table = tr.closest('table')
        $('#filter-' + column).remove()
        const field = headers[column]
        const hash = {}
        hash[field.key] = filter

        if (tr.hasClass('active') || filter === null) {
            hash[field.key] = null
            table.find('.active').removeClass('active')
            dataTable.columns(column).search("", true, false, true).draw();
        } else {
            table.find('.active').removeClass('active')
            tr.addClass('active')
            
            
            let searchValue = filter;
            let regex = false;
            let smart = false;
            if (column == 9 || column == 10) {
                searchValue = '^' + filter + '$';
                regex = true;
                smart = false;
            }
                console.log(searchValue);

            dataTable.column(column).search(searchValue, regex, smart).draw();

            const filterBtn = $('<span class="badge" id="filter-' + column + '">')
            filterBtn.html(`<b>${field.title}:</b> <span>${filter}</span>`)
            const a = $('<a>')
            a.html('&times;')
            a.on('click', function() {
                filterDataTable(btn, null, column);
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }
        writeHash(hash)
    }

</script>