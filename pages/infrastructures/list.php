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
$filter = [];
if (!$Settings->hasPermission('infrastructures.view') && !$Settings->hasPermission('infrastructures.edit')) {
    $filter['persons.user'] = $_SESSION['username'];
}
$infrastructures  = $osiris->infrastructures->find(
    $filter,
    ['sort' => ['end_date' => -1, 'start_date' => 1]]
)->toArray();


$data_fields = $Settings->get('infrastructure-data');
if (!is_null($data_fields)) {
    $data_fields = DB::doc2Arr($data_fields);
} else {
    $fields = file_get_contents(BASEPATH . '/data/infrastructure-fields.json');
    $fields = json_decode($fields, true);

    $data_fields = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data_fields = array_column($data_fields, 'id');
}

$active = function ($field) use ($data_fields) {
    return in_array($field, $data_fields);
};

$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
?>

<h1>
    <i class="ph-duotone ph-cube-transparent" aria-hidden="true"></i>
    <?= $Settings->infrastructureLabel() ?>
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
                    <th><?= $Settings->topicLabel() ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($infrastructures as $infra) { ?>
                    <tr>
                        <td>
                            <?php
                            $topics = '';
                            if ($topicsEnabled && !empty($infra['topics'])) {
                                $topics = '<span class="topic-icons float-right">';
                                foreach ($infra['topics'] as $topic) {
                                    $topics .= '<a href="' . ROOTPATH . '/topics/view/' . htmlspecialchars($topic) . '" class="topic-icon topic-' . htmlspecialchars($topic) . '"></a> ';
                                }
                                $topics .= '</span>';
                            }
                            echo $topics;
                            ?>
                            <h6 class="m-0">
                                <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                                    <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                </a>
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
                        <td>
                            <?= implode(', ', DB::doc2Arr($infra['topics'] ?? [])) ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (!$Settings->hasPermission('infrastructures.view') && !$Settings->hasPermission('infrastructures.edit')) {
            echo '<p class="text-muted">' . lang('You only have permission to view your own infrastructures.', 'Du hast nur die Berechtigung, deine eigenen Infrastrukturen zu sehen.') . '</p>';
        } ?>

    </div>

    <div class="col-3 filter-wrapper">

        <div class="filters content" id="filters">
            <div class="title">Filter</div>

            <div id="active-filters"></div>


            <?php if ($active('type')) { ?>
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
            <?php } ?>

            <?php if ($active('infrastructure_type')) { ?>
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
            <?php } ?>

            <?php if ($active('access')) { ?>
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
            <?php } ?>



            <?php if ($topicsEnabled && $active('topics')) { ?>
                <h6>
                    <?= $Settings->topicLabel() ?>
                    <a class="float-right" onclick="filterInfra('#filter-topics .active', null, 4)"><i class="ph ph-x"></i></a>
                </h6>

                <div class="filter">
                    <table id="filter-topics" class="table small simple">
                        <?php foreach ($osiris->topics->find([], ['sort' => ['order' => 1]]) as $a) {
                            $topic_id = $a['id'];
                        ?>
                            <tr style="--highlight-color:  <?= $a['color'] ?>;">
                                <td>
                                    <a data-type="<?= $topic_id ?>" onclick="filterInfra(this, '<?= $topic_id ?>', 4)" class="item" id="<?= $topic_id ?>-btn">
                                        <span style="color: var(--highlight-color)">
                                            <?= lang($a['name'], $a['name_en'] ?? null) ?>
                                        </span>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>

                </div>
            <?php } ?>


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
        },
        {
            key: 'topics',
            title: '<?= $Settings->topicLabel() ?>'
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
                targets: [1, 2, 3, 4],
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
                dataTable.search(decodeURIComponent(hash.search)).draw();
            }
            if (hash.page !== undefined) {
                dataTable.page(parseInt(hash.page) - 1).draw('page');
            }
            initializing = false;


            // count data for the filter and add it to the filter
            let all_filters = {
                1: '#filter-category',
                2: '#filter-type',
                3: '#filter-access',
                4: '#filter-topics'
            }

            for (const key in all_filters) {
                if (Object.prototype.hasOwnProperty.call(all_filters, key)) {
                    const element = all_filters[key];
                    const filter = $(element).find('a')
                    filter.each(function(i, el) {
                        let type = $(el).data('type')
                        const count = dataTable.column(key).data().filter(function(d) {
                            return d == type
                        }).length
                        // console.log(count);
                        $(el).append(` <em>${count}</em>`)
                    })
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