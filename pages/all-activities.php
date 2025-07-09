<?php

/**
 * Page to see all activities
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activities
 * @link /my-activities
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$user = $user ?? $_SESSION['username'];
$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
?>


<?php if (isset($_GET['user'])) { ?>
    <h1 class='m-0'>
        <i class="ph ph-folder-user"></i>

        <?= lang("Activities of ", "Aktivitäten von ") ?>
        <a href="<?= ROOTPATH ?>/profile/<?= $user ?>"><?= $DB->getNameFromId($user) ?></a>
    </h1>
    <a href="<?= ROOTPATH ?>/activities" class="btn small mb-10" id="user-btn">
        <i class="ph ph-book-open"></i>
        <?= lang('Show  all activities', "Zeige alle Aktivitäten") ?>
    </a>
<?php } elseif ($page == 'activities' || !$Settings->hasPermission('scientist')) { ?>
    <h1 class='m-0'>
        <i class="ph ph-book-open"></i>
        <?= lang("All activities", "Alle Aktivitäten") ?>
    </h1>

    <button class="btn primary float-right d-none d-md-inline-block" onclick="$('.filter-wrapper').slideToggle()">Filter <i class="ph ph-caret-down"></i></button>

    <div class="btn-toolbar justify-between">

        <a href="<?= ROOTPATH ?>/activities/statistics" class="btn">
            <i class="ph ph-chart-line-up"></i>
            <?= lang('Statistics', 'Statistiken') ?>
        </a>
        <a href="<?= ROOTPATH ?>/add-activity">
            <i class="ph ph-plus"></i>
            <?= lang('Add activity', 'Aktivität hinzufügen') ?>
        </a>

    </div>

<?php
} elseif ($page == 'my-activities') { ?>
    <h1 class='m-0'>
        <i class="ph ph-folder-user"></i>
        <?= lang("My activities", "Meine Aktivitäten") ?>
    </h1>
    <a href="<?= ROOTPATH ?>/activities" class="btn small mb-10" id="user-btn">
        <i class="ph ph-book-open"></i>
        <?= lang('Show  all activities', "Zeige alle Aktivitäten") ?>
    </a>
<?php } ?>

<style>
    /* under md */
    @media (max-width: 768px) {
        .filter-wrapper {
            display: none;
        }
    }
</style>

<div class="row row-eq-spacing">
    <div class="col order-last order-sm-first">

        <table class="table dataTable" id="result-table" style="width:100%">
            <thead>
                <tr>
                    <th><?= lang('Quarter', 'Quartal') ?></th>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Activity', 'Aktivität') ?></th>
                    <th></th>
                    <th><?= lang('Print', 'Print') ?></th>
                    <th>Start</th>
                    <th><?= lang('End', 'Ende') ?></th>
                    <th><?= lang('Units', 'Einheiten') ?></th>
                    <th><?= lang('Online ahead of print') ?></th>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Subtype', 'Subtyp') ?></th>
                    <th><?= lang('Title', 'Titel') ?></th>
                    <th><?= lang('Authors', 'Autoren') ?></th>
                    <th><?= lang('Year', 'Jahr') ?></th>
                    <th><?= $Settings->topicLabel() ?></th>
                    <th><?= lang('Affiliated', 'Affiliert') ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="col-md-3 filter-wrapper">

        <div class="filters content" id="filters">
            <!-- <div class="content" > -->
            <div class="title">Filter</div>

            <!-- <div id="searchpanes"></div> -->

            <div id="active-filters"></div>


            <h6>
                <?= lang('By type', 'Nach Typ') ?>
                <a class="float-right" onclick="filterActivities('#filter-type .active', null, 7)"><i class="ph ph-x"></i></a>
            </h6>
            <style>
                .filter {
                    overflow-x: hidden;
                }

                .filter tr td .submenu a {
                    padding: .2rem 1rem .2rem 2.5rem;
                    font-size: small;
                    color: var(--highlight-color);
                    font-weight: normal;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    overflow: hidden;
                }

                .filter tr td .submenu a.active {
                    font-weight: bold;
                }

            </style>
            <div class="filter" style="max-height: 22rem;">
                <table id="filter-type" class="table small simple">
                    <?php foreach ($Categories->categories as $a) {
                        $id = $a['id'];
                    ?>
                        <tr style="--highlight-color:  <?= $a['color'] ?>;">
                            <td>
                                <a data-type="<?= $id ?>" onclick="filterActivities(this, '<?= $id ?>', 9)" class="item" id="<?= $id ?>-btn">
                                    <span class="text-<?= $id ?>">
                                        <span class="mr-5"><?= $Settings->icon($id, null, false) ?> </span>
                                        <?= $Settings->title($id, null) ?>
                                    </span>
                                </a>
                                <?php
                                $subtypes = $a['children'] ?? [];
                                if (count($subtypes) > 1) {
                                ?>

                                    <div class="submenu" style="display: none;">
                                        <?php
                                        foreach ($subtypes as $subtype) {
                                            $subid = $subtype['id'];
                                        ?>
                                            <a data-type="<?= $subid ?>" onclick="filterSubtype(this, '<?= $subid ?>')" class="item subitem" id="<?= $subid ?>-sub-btn">
                                                <span class="text-<?= $subid ?>">
                                                    <span class="mr-5"> <i class="ph ph-<?= $subtype['icon'] ?>"></i> </span>
                                                    <?= lang($subtype['name'], $subtype['name_de']) ?>
                                                </span>
                                            </a>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table>

            </div>

            <h6>
                <?= lang('By affiliation', 'Nach Zugehörigkeit') ?>
                <a class="float-right" onclick="filterActivities('#filter-affiliated .active', null, 15)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-affiliated" class="table small simple">
                    <tr style="--highlight-color: var(--success-color);">
                        <td>
                            <a data-type="yes" onclick="filterActivities(this, 'yes', 15)" class="item" id="yes-affiliated-btn">
                                <span class="text-success">
                                    <span class="mr-5"><i class="ph ph-push-pin"></i></span>
                                    <?= lang('Affiliated', 'Affiliert') ?>
                                </span>
                            </a>
                        </td>
                    </tr>
                    <tr style="--highlight-color: var(--danger-color);">
                        <td>
                            <a data-type="no" onclick="filterActivities(this, 'no', 15)" class="item" id="no-affiliated-btn">
                                <span class="text-danger">
                                    <span class="mr-5"><i class="ph ph-push-pin-slash"></i></span>
                                    <?= lang('Not affiliated', 'Nicht affiliert') ?>
                                </span>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <?php if ($topicsEnabled) { ?>
                <h6><?= $Settings->topicLabel() ?></h6>

                <div class="filter">
                    <table id="filter-topics" class="table small simple">
                        <?php foreach ($osiris->topics->find([], ['sort' => ['order' => 1]]) as $a) {
                            $id = $a['id'];
                        ?>
                            <tr style="--highlight-color:  <?= $a['color'] ?>;">
                                <td>
                                    <a data-type="<?= $id ?>" onclick="filterActivities(this, '<?= $id ?>', 14)" class="item" id="<?= $id ?>-btn">
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

            <h6>
                <?= lang('By organisational unit', 'Nach Organisationseinheit') ?>
                <a class="float-right" onclick="filterActivities('#filter-unit .active', null, 7)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-unit" class="table small simple">
                    <?php foreach ($Departments as $id => $dept) { ?>
                        <tr <?= $Groups->cssVar($id) ?>>
                            <td>
                                <a data-type="<?= $id ?>" onclick="filterActivities(this, '<?= $id ?>', 7)" class="item colorless" id="<?= $id ?>-btn">
                                    <span><?= $dept ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <h6>
                <?= lang('By time', 'Nach Zeitraum') ?>
                <a class="float-right" onclick="resetTime()"><i class="ph ph-x"></i></a>
            </h6>

            <div class="input-group">
                <div class="input-group-prepend">
                    <label for="filter-from" class="input-group-text w-50"><?= lang('From', 'Von') ?></label>
                </div>
                <input type="date" name="from" id="filter-from" class="form-control">
            </div>
            <div class="input-group mt-10">
                <div class="input-group-prepend">
                    <label for="filter-from" class="input-group-text w-50"><?= lang('To', 'Bis') ?></label>
                </div>
                <input type="date" name="to" id="filter-to" class="form-control">
            </div>


            <h6><?= lang('More', 'Weiteres') ?></h6>
            <div class="custom-switch">
                <input type="checkbox" id="epub-switch" value="" onchange="filterEpub(this)">
                <label for="epub-switch"><?= lang('without Online ahead of print', 'ohne <em>Online ahead of print</em>') ?></label>
            </div>

        </div>
    </div>
</div>
<!-- </div> -->

<script>
    var dataTable;
    var topicsEnabled = <?= $topicsEnabled ? 'true' : 'false' ?>;

    const minEl = document.querySelector('#filter-from');
    const maxEl = document.querySelector('#filter-to');

    const activeFilters = $('#active-filters')
    const headers = [{
            title: lang('Quarter', 'Quartal'),
            'key': 'quarter'
        },
        {
            title: lang('Type', 'Typ'),
            'key': 'type'
        },
        {
            title: lang('Activity', 'Aktivität'),
            'key': 'activity'
        },
        {
            title: '',
            'key': 'links'
        },
        {
            title: lang('Print', 'Print'),
            'key': 'search-text'
        },
        {
            title: lang('Start', 'Start'),
            'key': 'start'
        },
        {
            title: lang('End', 'Ende'),
            'key': 'end'
        },
        {
            title: lang('Units', 'Einheiten'),
            'key': 'unit'
        },
        {
            title: lang('Online ahead of print'),
            'key': 'epub'
        },
        {
            title: lang('Type', 'Typ'),
            'key': 'type'
        },
        {
            title: lang('Subtype', 'Subtyp'),
            'key': 'subtype'
        },
        {
            title: lang('Title', 'Titel'),
            'key': 'title'
        },
        {
            title: lang('Authors', 'Autoren'),
            'key': 'authors'
        },
        {
            title: lang('Year', 'Jahr'),
            'key': 'year'
        },
        {
            title: lang('Research topics', 'Forschungsbereiche'),
            'key': 'topics'
        },
        {
            title: lang('Affiliated', 'Affiliert'),
            'key': 'affiliated'
        }
    ]

    $(document).ready(function() {
        dataTable = $('#result-table').DataTable({
            "ajax": {
                "url": ROOTPATH + '/api/all-activities',
                "data": {
                    "page": '<?= $page ?>',
                    'display_activities': '<?= $USER['display_activities'] ?>',
                    'user': '<?= $user ?>'
                },
                dataSrc: 'data'
            },
            deferRender: true,
            responsive: true,
            language: {
                url: lang(null, ROOTPATH + '/js/datatables/de-DE.json')
            },
            buttons: [
                // custom link button
                {
                    text: '<i class="ph ph-magnifying-glass-plus"></i> <?= lang('Advanced search', 'Erweiterte Suche') ?>',
                    className: 'btn small text-primary mr-10',
                    action: function(e, dt, node, config) {
                        window.location.href = '<?= ROOTPATH ?>/activities/search';
                    }
                },
                {
                    extend: 'copyHtml5',
                    exportOptions: {
                        columns: [4]
                    },
                    className: 'btn small',
                    text: '<i class="ph ph-clipboard"></i> <?= lang('Copy', 'Kopieren') ?>',
                },
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [0, 4, 5, 6, 9, 10, 11, 12, 13]
                    },
                    className: 'btn small',
                    title: function() {
                        var filters = []
                        activeFilters.find('.badge').find('span').each(function(i, el) {
                            filters.push(el.innerHTML)
                        })
                        console.log(filters);
                        if (filters.length == 0) return "OSIRIS All Activities";
                        return 'OSIRIS ' + filters.join('_')
                    },
                    text: '<i class="ph ph-file-xls"></i> <?= lang('Excel', 'Excel') ?>',
                },
                {
                    extend: 'csvHtml5',
                    exportOptions: {
                        // columns: ':visible'
                        columns: [0, 5, 6, 9, 10, 11, 12, 13]
                    },
                    className: 'btn small',
                    title: function() {
                        var filters = []
                        activeFilters.find('.badge').find('span').each(function(i, el) {
                            filters.push(el.innerHTML)
                        })
                        console.log(filters);
                        if (filters.length == 0) return "OSIRIS All Activities";
                        return 'OSIRIS ' + filters.join('_')
                    },
                    text: '<i class="ph ph-file-csv"></i> <?= lang('CSV', 'CSV') ?>',
                },

            ],
            dom: 'fBrtip',
            // dom: '<"dtsp-dataTable"frtip>',
            columnDefs: [{
                    targets: 0,
                    data: "quarter",
                    searchPanes: {
                        show: false
                    }
                },
                {
                    targets: 1,
                    data: 'icon'
                },
                {
                    targets: 2,
                    data: 'activity',
                    render: function(data, type, row) {
                        var text = data;
                        if (topicsEnabled && row.topics && row.topics.length > 0) {
                            text = '<span class="float-right topic-icons">'
                            row.topics.forEach(function(topic) {
                                text += `<a href="<?= ROOTPATH ?>/topics/view/${topic}" class="topic-icon topic-${topic}"></a> `
                            })
                            text += '</span>' + data
                        }
                        return text;
                    }
                },
                {
                    targets: 3,
                    data: 'links',
                    sortable: false,
                    className: 'unbreakable',
                },
                {
                    targets: 4,
                    data: 'search-text',
                    searchable: true,
                    visible: false,
                    searchPanes: {
                        show: false
                    },
                },
                {
                    targets: 5,
                    header: 'Start',
                    name: 'start',
                    data: 'start',
                    searchable: true,
                    visible: false,
                    searchPanes: {
                        show: true,
                        name: 'start',
                        header: 'Start'
                    },
                },
                {
                    targets: 6,
                    data: 'end',
                    searchable: true,
                    visible: false,
                    searchPanes: {
                        show: true,
                        name: 'end',
                        header: 'End'
                    },
                },
                {
                    targets: 7,
                    data: 'departments',
                    searchable: true,
                    visible: false,
                    // searchPanes: {
                    //     name: 'units',
                    //     header: lang('Organizational Units', 'Organisationseinheiten'),
                    //     orthogonal: 'sp'
                    // }
                },
                {
                    targets: 8,
                    data: 'epub',
                    visible: false,
                    // searchPanes: {
                    //     name: 'epub',
                    //     header: 'Online ahead of print',
                    //     initCollapsed: true
                    // }
                },
                {
                    targets: 9,
                    data: 'raw_type',
                    searchable: true,
                    visible: false,
                },
                {
                    targets: 10,
                    data: 'raw_subtype',
                    searchable: true,
                    visible: false,
                },
                {
                    targets: 11,
                    data: 'title',
                    searchable: true,
                    visible: false,
                },
                {
                    targets: 12,
                    data: 'authors',
                    searchable: true,
                    visible: false,
                },
                {
                    targets: 13,
                    data: 'year',
                    searchable: true,
                    visible: false,
                    // searchPanes: {
                    //     show: true,
                    //     name: 'year',
                    //     header: lang('Year', 'Jahr')
                    // },
                },
                {
                    targets: 14,
                    data: 'topics',
                    searchable: true,
                    visible: false,
                    render: function(data, type, row) {
                        if (data.length == 0 || !topicsEnabled) return ''
                        return data.join(', ')
                        // return `<a href="<?= ROOTPATH ?>/topics/view/${row.topics}">${data}</a>`
                    }

                },
                {
                    targets: 15,
                    data: 'affiliated',
                    visible: false,
                    render: function(data, type, row) {
                        return data ? 'yes' : 'no'
                    }
                }
            ],
            "order": [
                [5, 'desc'],
                [1, 'asc']
            ],
            <?php if (isset($_GET['q'])) { ?> "oSearch": {
                    "sSearch": "<?= $_GET['q'] ?>"
                }
            <?php } ?>

        });



        // Custom range filtering function
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            var min = null,
                max = null;
            if (minEl.value !== null && minEl.value !== '')
                min = new Date(minEl.value);
            if (maxEl.value !== null && maxEl.value !== '')
                max = new Date(maxEl.value);

            var minDate = new Date(data[5]);
            var maxDate = new Date(data[6]);

            if (
                (min === null && max === null) ||
                (min === null && minDate <= max) ||
                (min <= minDate && max === null) ||
                (min < maxDate && minDate < max)) {
                return true;
            }

            return false;
        });

        <?php if (isset($_GET['type'])) { ?>
            window.location.hash = "type=<?= $_GET['type'] ?>";
        <?php } ?>


        var initializing = true;
        dataTable.on('init', function() {

            var hash = readHash();
            console.log(hash);
            if (hash.type !== undefined) {
                filterActivities(document.getElementById(hash.type + '-btn'), hash.type, 1)
            }
            if (hash.unit !== undefined) {
                filterActivities(document.getElementById(hash.unit + '-btn'), hash.unit, 7)
            }
            if (hash.affiliated !== undefined) {
                filterActivities(document.getElementById(hash.affiliated + '-affiliated-btn'), hash.affiliated, 15)
            }

            if (hash.start !== undefined) {
                minEl.value = hash.start
                minEl.dispatchEvent(new Event('input'));
            }
            if (hash.end !== undefined) {
                maxEl.value = hash.end
                maxEl.dispatchEvent(new Event('input'));
            }

            if (hash.epub !== undefined) {
                $('#epub-switch').prop('checked', true)
                filterEpub()
            }

            if (hash.time !== undefined) {
                resetTime()
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
                9: '#filter-type',
                7: '#filter-unit',
                15: '#filter-affiliated',
                14: '#filter-topics',
            }

            for (const key in all_filters) {
                if (Object.prototype.hasOwnProperty.call(all_filters, key)) {
                    const element = all_filters[key];
                    const filter = $(element).find('a:not(.subitem)');
                    filter.each(function(i, el) {
                        let type = $(el).data('type')
                        // console.log(type);
                        if (key == 15 && type == 'yes') {
                            type = true
                        } else if (key == 15 && type == 'no') {
                            type = false
                        }
                        const count = dataTable.column(key).data().filter(function(d) {
                            if ((key == 7 || key == 14) && d instanceof Array) {
                                return d.includes(type)
                            }
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






    function filterEpub() {
        if ($('#epub-switch').prop('checked')) {
            dataTable.columns(8).search("false", true, false, true).draw();
        } else {
            dataTable.columns(8).search("", true, false, true).draw();
        }
        writeHash({
            epub: $('#epub-switch').prop('checked')
        })
    }

    function filterActivities(btn, activity = null, column = 1) {
        // var inSubmenu = ($(btn).closest('.submenu').length > 0)
        console.log(btn, activity, column);
        var tr = $(btn).closest('tr')
        var submenu = tr.find('.submenu')
        var table = tr.closest('table')
        $('#filter-' + column).remove()
        const field = headers[column]
        const hash = {}
        hash[field.key] = activity

        if (tr.hasClass('active') || activity === null) {
            hash[field.key] = null
            table.find('.active').removeClass('active')
            dataTable.columns(column).search("", true, false, true).draw();
            submenu.slideUp()
        } else {

            table.find('.active').removeClass('active')

            tr.addClass('active')
            dataTable.columns(column).search(activity, true, false, true).draw();
            // indicator
            const filterBtn = $('<span class="badge" id="filter-' + column + '">')
            filterBtn.html(`<b>${field.title}:</b> <span>${activity}</span>`)
            const a = $('<a>')
            a.html('&times;')
            a.on('click', function() {
                filterActivities(btn, null, column);
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)

            table.find('.submenu').slideUp()
            submenu.slideDown()
        }
        // if key was type, we need to reset subtype
        if (field.key == 'type') {
            dataTable.columns(10).search("", true, false, true).draw();
            table.find('.submenu > a').removeClass('active')
            hash['subtype'] = null
        }
        writeHash(hash)
    }

    function filterSubtype(btn, subtype) {
        // filterActivities(btn, subtype, 10)
        var active = $(btn).hasClass('active')
        var column = 10;

        if (active) {
            dataTable.columns(column).search("", true, false, true).draw();
            $(btn).removeClass('active')
        } else {
            dataTable.columns(column).search(subtype, true, false, true).draw();
            $(btn).closest('table').find('.submenu > a').removeClass('active')
            $(btn).addClass('active')
        }
    }

    // Changes to the inputs will trigger a redraw to update the table
    minEl.addEventListener('input', function() {
        dataTable.draw();
        writeHash({
            start: minEl.value
        })

        $('#filter-5').remove()
        if (minEl.value != '') {
            const filterBtn = $('<span class="badge" id="filter-5">')
            filterBtn.html(`<b>Start:</b> <span>${minEl.value}</span>`)
            const a = $('<a>')
            a.html('&times;')
            a.on('click', function() {
                minEl.value = ''
                minEl.dispatchEvent(new Event('input'));
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }
    });
    maxEl.addEventListener('input', function() {
        dataTable.draw();
        writeHash({
            end: maxEl.value
        })

        $('#filter-6').remove()
        if (maxEl.value != '') {
            const filterBtn = $('<span class="badge" id="filter-6">')
            filterBtn.html(`<b>End:</b> <span>${maxEl.value}</span>`)
            const a = $('<a>')
            a.html('&times;')
            a.on('click', function() {
                maxEl.value = ''
                maxEl.dispatchEvent(new Event('input'));
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }

    });

    function resetTime() {
        minEl.value = ""
        maxEl.value = ""
        dataTable.draw();
        writeHash({
            time: null
        })
    }
</script>