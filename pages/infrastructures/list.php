<?php

/**
 * The overview of all infrastructures
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */



include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
$infrastructures  = $osiris->infrastructures->find(
    [],
    ['sort' => ['end_date' => -1, 'start_date' => 1]]
)->toArray();
?>

<h1>
    <i class="ph ph-cube-transparent" aria-hidden="true"></i>
    <?= lang('Infrastructures', 'Infrastrukturen') ?>
</h1>
<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/infrastructures/statistics" class="btn">
        <i class="ph ph-chart-line-up"></i>
        <?= lang('Statistics', 'Statistiken') ?>
    </a>
    <?php if ($Settings->hasPermission('infrastructures.edit')) { ?>
        <a href="<?= ROOTPATH ?>/infrastructures/new">
            <i class="ph ph-plus"></i>
            <?= lang('Add new infrastructure', 'Neue Infrastruktur anlegen') ?>
        </a>
    <?php } ?>
</div>

<div class="row row-eq-spacing">
    <div class="col order-last order-sm-first">

        <table class="table" id="infrastructure-table">
            <thead>
                <tr>
                    <th><?= lang('Name', 'Name') ?></th>
                    <th><?= lang('Category', 'Kategorie') ?></th>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Access', 'Zugang') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($infrastructures as $infra) { ?>
                    <tr>
                        <td>
                            <h6 class="m-0">
                                <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                                    <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                </a>
                                <br>
                            </h6>

                            <div class="text-muted mb-5">
                                <?php if (!empty($infra['subtitle'])) { ?>
                                    <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                                <?php } else { ?>
                                    <?= get_preview(lang($infra['description'], $infra['description_de'] ?? null), 300) ?>
                                <?php } ?>
                            </div>
                            <div>
                                <?= fromToYear($infra['start_date'], $infra['end_date'] ?? null, true) ?>
                            </div>
                        </td>
                        <td>
                            <?= $infra['type'] ?? '' ?>
                        </td>
                        <td>
                            <?= $infra['infrastructure_type'] ?? '' ?>
                        </td>
                        <td>
                            <?= $infra['access'] ?? '' ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="col-3 filter-wrapper">

        <div class="filters content" id="filters">
            <div class="title">Filter</div>

            <div id="active-filters"></div>
            <h6>
                <?= lang('By category', 'Nach Kategorie') ?>
                <a class="float-right" onclick="filterInfra('#filter-category .active', null, 1)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-category" class="table small simple">
                    <?php
                    $vocab = $vocab = $Vocabulary->getValues('infrastructure-category');
                    foreach ($vocab as $v) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $v['id'] ?>" onclick="filterInfra(this, '<?= $v['id'] ?>', 1)" class="item" id="<?= $v['id'] ?>-btn">
                                    <span>
                                        <?= lang($v['en'], $v['de'] ?? null) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <h6>
                <?= lang('By type', 'Nach Typ') ?>
                <a class="float-right" onclick="filterInfra('#filter-type .active', null, 2)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-type" class="table small simple">
                    <?php
                    $vocab = $Vocabulary->getValues('infrastructure-type');
                    foreach ($vocab as $v) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $v['id'] ?>" onclick="filterInfra(this, '<?= $v['id'] ?>', 2)" class="item" id="<?= $v['id'] ?>-btn">
                                    <span>
                                        <?= lang($v['en'], $v['de'] ?? null) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <h6>
                <?= lang('By access', 'Nach Zugang') ?>
                <a class="float-right" onclick="filterInfra('#filter-access .active', null, 3)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-access" class="table small simple">
                    <?php
                    $vocab = $Vocabulary->getValues('infrastructure-access');
                    foreach ($vocab as $v) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $v['id'] ?>" onclick="filterInfra(this, '<?= $v['id'] ?>', 3)" class="item" id="<?= $v['id'] ?>-btn">
                                    <span>
                                        <?= lang($v['en'], $v['de'] ?? null) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

        </div>
    </div>
</div>


<script>
    var dataTable;

    let headers = [{
            key: 'name',
            title: 'Name'
        },
        {
            key: 'category',
            title: 'Category'
        },
        {
            key: 'type',
            title: 'Type'
        },
        {
            key: 'access',
            title: 'Access'
        }
    ]
    const activeFilters = $('#active-filters')

    $(document).ready(function() {
        dataTable = new DataTable('#infrastructure-table', {
            responsive: true,
            language: {
                url: lang(null, ROOTPATH + '/js/datatables/de-DE.json')
            },
            columnDefs: [{
                targets: [1, 2, 3],
                visible: false
            }, ],
            paging: true,
            autoWidth: true,
            pageLength: 8,
        });

        // $('#infrastructure-table_wrapper').prepend($('.filters'))


        var initializing = true;
        dataTable.on('init', function() {

            var hash = readHash();
            if (hash.type !== undefined) {
                filterInfra(document.getElementById(hash.status + '-btn'), hash.status, 1)
            }
            if (hash.search !== undefined) {
                dataTable.search(hash.search).draw();
            }
            if (hash.page !== undefined) {
                dataTable.page(parseInt(hash.page) - 1).draw('page');
            }
            initializing = false;


            // count data for the filter and add it to the filter
            let all_filters = {
                1: '#filter-category',
                2: '#filter-type',
                3: '#filter-access'
            }

            for (const key in all_filters) {
                if (Object.prototype.hasOwnProperty.call(all_filters, key)) {
                    const element = all_filters[key];
                    dataTable.columns(key).data().each(function(data, index) {
                        const result = data.reduce((acc, item) => {
                            acc[item] = (acc[item] || 0) + 1;
                            return acc;
                        }, {});
                        $.each(result, function(key, value) {
                            // find button by content
                            var btn = $(element + ' a[data-type="' + key + '"]')
                            if (btn.length > 0) {
                                btn.append(' <em>' + value + '</em>')
                            }
                        });
                    });
                }
            }
        });


        dataTable.on('draw', function(e, settings) {
            if (initializing) return;
            var info = dataTable.page.info();
            console.log(settings.oPreviousSearch.sSearch);
            writeHash({
                page: info.page + 1,
                search: settings.oPreviousSearch.sSearch
            })
        });

    });



    function filterInfra(btn, filter = null, column = 1) {
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
            dataTable.column(column).search(filter, true, false, true).draw();
            const filterBtn = $('<span class="badge" id="filter-' + column + '">')
            filterBtn.html(`<b>${field.title}:</b> <span>${filter}</span>`)
            const a = $('<a>')
            a.html('&times;')
            a.on('click', function() {
                filterInfra(btn, null, column);
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }
        writeHash(hash)
    }

    // function sortTable(el, column, direction = 'asc') {
    //     $(el).closest('.dropdown-menu').find('.active').removeClass('active');
    //     $(el).addClass('active');

    //     dataTable.order([column, direction]).draw();
    //     return false;
    // }
</script>