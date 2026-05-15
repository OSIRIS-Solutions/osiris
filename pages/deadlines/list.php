<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$user = $_SESSION['username'];

$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
$tagsEnabled = $Settings->featureEnabled('tags');

$deadlinesEnabled = $Settings->featureEnabled('deadlines', false);

$deadlineTypes = $Vocabulary->getValues('deadline-type');
$colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#343a40'];
$typeInfo = [];
foreach ($deadlineTypes as $v) {
    $typeInfo[$v['id']] = [
        'title' => lang($v['en'], $v['de'] ?? null),
        'color' => $colors[count($typeInfo) % count($colors)],
    ];
}
?>


<h1>
    <i class="ph-duotone ph-calendar"></i>
    <?= lang('Schedule', 'Termine') ?>
</h1>

<div class="pills d-inline-block font-size-16">
    <a href="<?= ROOTPATH ?>/conferences" class="btn">
        <i class="ph-duotone ph-calendar-dots"></i>
        <?= lang('Events', 'Events') ?>
    </a>
    <a href="#" class="btn active font-weight-bold">
        <i class="ph-duotone ph-flag-checkered"></i>
        <?= lang('Deadlines', 'Deadlines') ?>
    </a>
</div>

<?php if ($Settings->hasPermission('deadlines.edit')) { ?>
    <a href="<?= ROOTPATH ?>/deadlines/new" class="ml-20">
        <i class="ph ph-plus"></i>
        <?= lang('New deadline', 'Neue Deadline') ?>
    </a>
<?php } ?>



<?php
// deadlines max past 3 month
$deadlines = $osiris->deadlines->find(
    [],
    // ['start' => ['$gte' => date('Y-m-d', strtotime('-3 month'))]],
    ['sort' => ['start' => -1]]
)->toArray();
?>
<div class="box">
    <div class="content">
        <div class="btn-toolbar justify-content-between">
            <div id="event-selector"></div>
            <div>
                <div class="input-group small mr-10">
                    <div class="input-group-prepend">
                        <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) - 1).change()"><i class="ph ph-caret-left"></i></button>
                    </div>
                    <input type="number" class="form-control" id="activity-year" placeholder="<?= lang('Year', 'Jahr') ?>" value="<?= date('Y') ?>" onchange="eventTimeline()">
                    <div class="input-group-append">
                        <button class="btn" onclick="$('#activity-year').val(parseInt($('#activity-year').val()) + 1).change()"><i class="ph ph-caret-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="timeline"></div>
</div>
<div class="row row-eq-spacing">
    <div class="col-lg-9 order-last order-sm-first">

        <table class="table" id="result-table">
            <thead>
                <tr>
                    <th><?= lang('Title', 'Titel') ?></th>
                    <th><?= lang('Date', 'Datum') ?></th>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Relevance', 'Relevanz') ?></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="col-lg-3 filter-wrapper">

        <div class="filters content" id="filters">
            <div class="title">Filter</div>

            <div id="active-filters"></div>


            <h6>
                <?= lang('By relevance', 'Nach Relevanz') ?>
                <a class="float-right" onclick="filterEvents('#filter-relevance .active', null, 3)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-relevance" class="table small simple">
                    <tr>
                        <td>
                            <a data-type="relevant" onclick="filterEvents(this, 'relevant', 3)" class="item" id="relevant-btn">
                                <span>
                                    <?= lang('Only relevant to your roles', 'Nur relevant für deine Rollen') ?>
                                </span>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <h6>
                <?= lang('By type', 'Nach Typ') ?>
                <a class="float-right" onclick="filterEvents('#filter-type .active', null, 2)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-type" class="table small simple">
                    <?php
                    foreach ($typeInfo as $i => $v) { ?>
                        <tr style="--highlight-color: <?= $v['color'] ?>;">
                            <td>
                                <a data-type="<?= $i ?>" onclick="filterEvents(this, '<?= $i ?>', 2)" class="item" id="<?= $i ?>-btn">
                                    <span style="color: <?= $v['color'] ?>;">
                                        <?= $v['title'] ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>



            <!-- filter by year -->
            <h6>
                <?= lang('By year', 'Nach Jahr') ?>
                <a class="float-right" onclick="filterEvents('#filter-year .active', null, 1)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-year" class="table small simple">
                    <?php
                    $years = [];
                    foreach ($deadlines as $c) {
                        $year = date('Y', strtotime($c['year']));
                        if (!in_array($year, $years)) {
                            $years[] = $year;
                        }
                    }
                    rsort($years);
                    foreach ($years as $y) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $y ?>" onclick="filterEvents(this, '<?= $y ?>', 1)" class="item" id="<?= $y ?>-btn">
                                    <span>
                                        <?= $y ?>
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


<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<!-- // my year for the activity timeline -->
<script src="<?= ROOTPATH ?>/js/my-year.js?v=<?= OSIRIS_BUILD ?>"></script>
<script>
    var dataTable;
    var rootpath = '<?= ROOTPATH ?>'

    let headers = [{
            'key': 'title',
            'title': lang('Title', 'Titel')
        },
        {
            'key': 'date',
            'title': lang('Date', 'Datum')
        },
        {
            'key': 'type',
            'title': lang('Type', 'Typ')
        },
        {
            'key': 'relevance',
            'title': lang('Relevance', 'Relevanz')
        },
    ]


    var typeInfo = <?= json_encode($Vocabulary->getValues('deadline-type')) ?>;
    // in case it is an object convert it to array
    if (typeof typeInfo === 'object' && !Array.isArray(typeInfo)) {
        typeInfo = Object.values(typeInfo);
    }
    // convert to object with id as key
    typeInfo = typeInfo.reduce(function(obj, item) {
        obj[item.id] = {
            title: lang(item.en, item.de ?? null),
        };
        return obj;
    }, {});


    const activeFilters = $('#active-filters')
    $(document).ready(function() {
        dataTable = $('#result-table').DataTable({
            "ajax": {
                "url": rootpath + '/api/deadlines',
                dataSrc: 'data'
            },
            responsive: true,
            autoWidth: true,
            deferRender: true,
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [0, 1, 2, 3],
                },
                className: 'btn small',
                title: function() {
                    var filters = []
                    activeFilters.find('.badge').find('span').each(function(i, el) {
                        filters.push(el.innerHTML)
                    })
                    console.log(filters);
                    if (filters.length == 0) return "OSIRIS All Deadlines";
                    return 'OSIRIS Deadlines ' + filters.join('_')
                },
                text: '<i class="ph ph-file-xls"></i> Export'
            }, ],
            dom: 'fBrtip',
            columnDefs: [{
                    targets: 0,
                    data: 'title',
                    searchable: true,
                    render: function(data, type, row) {
                        return `<a href="${rootpath}/deadlines/view/${row.id}" class="font-weight-bold">${row.title}</a>
                        `;
                    }
                },
                {
                    targets: 1,
                    data: 'date',
                    searchable: true,
                    render: function(data, type, row) {
                        // formatted date
                        var date = new Date(data);

                        return `
                        <span class="d-none">${date.getTime()}</span>
                        ${date.toLocaleDateString('de-DE')}
                        `;
                    }
                },
                {
                    targets: 2,
                    data: 'type',
                    defaultContent: '',
                },
                {
                    targets: 3,
                    data: 'relevant',
                    render: function(data, type, row) {
                        if (data) {
                            return `<span class="hidden">relevant</span><i class="ph ph-check-circle text-success"></i>`;
                        } else {
                            return `<i class="ph ph-x-circle text-muted"></i>`;
                        }
                    }
                },
            ],
            "order": [
                [1, 'desc']
            ],
        });

        var initializing = true;
        dataTable.on('init', function() {

            var hash = readHash();
            if (hash.type !== undefined) {
                filterEvents(document.getElementById(hash.status + '-btn'), hash.status, 1)
            }
            if (hash.search !== undefined && hash.search !== 'undefined') {
                dataTable.search(decodeURIComponent(hash.search)).draw();
            }
            if (hash.page !== undefined) {
                dataTable.page(parseInt(hash.page) - 1).draw('page');
            }
            if (hash.start !== undefined) {
                filterEvents(document.getElementById(hash.start + '-btn'), hash.start, 2)
            }
            if (hash.tags !== undefined) {
                // url decode and find tag button
                hash.tags = decodeURIComponent(hash.tags);
                var tagId = 'tag-' + hash.tags.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '-btn';
                var tag = document.getElementById(tagId);
                if (tag) {
                    tag = tag.getAttribute('data-type')
                    filterEvents(document.getElementById(tagId), tag, 6)
                }
            }
            initializing = false;
            filterEvents(document.getElementById('relevant-btn'), 'relevant', 3)

            // count data for the filter and add it to the filter
            let all_filters = {
                2: '#filter-type',
            }

            for (const key in all_filters) {
                if (Object.prototype.hasOwnProperty.call(all_filters, key)) {
                    const element = all_filters[key];
                    const filter = $(element).find('a')
                    filter.each(function(i, el) {
                        let type = $(el).data('type')
                        const count = dataTable.column(key).data().filter(function(d) {
                            if (key == 5 || key == 6) {
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
            writeHash({
                page: info.page + 1,
                search: dataTable.search(),
            })
        });

        eventTimeline();

    });


    function eventTimeline(filter = {}, props = {}) {
        if (typeof timeline !== 'function') {
            console.error('Timeline function is not defined. Please ensure the timeline.js script is included.');
            return;
        }

        let selector = props.timelineSelector || '#timeline';
        let eventSelector = props.eventSelector || '#event-selector';
        let yearSelector = props.yearSelector || '#activity-year';

        // check if selector exists
        if (!$(selector).length || !$(yearSelector).length) {
            console.error('Timeline selector or year selector not found.');
            return;
        }
        if (eventSelector && !$(eventSelector).length) {
            eventSelector = null; // if eventSelector is not found, set it to null
        }

        // current year and quarter
        // let date = new Date();
        let year = $(yearSelector).val();
        let currentYear = new Date().getFullYear();
        // check if year is a valid 4 digit number
        if (!/^\d{4}$/.test(year)) {
            toastError('Invalid year format. Please enter a valid 4-digit year.');
            return;
        }
        if (year > currentYear) {
            year = currentYear;
            $(yearSelector).val(year);
        }

        $(selector).empty()
        if (eventSelector) {
            $(eventSelector).empty()
        }
        // let quarter = Math.ceil((date.getMonth() + 1) / 3);
        $.ajax({
            type: "GET",
            url: ROOTPATH + "/api/dashboard/deadline-timeline",
            data: {
                year: year
            },
            dataType: "json",
            success: function(response) {
                let events = response.data.events;
                if (events.length === 0) {
                    $(selector).html('<div class="content text-muted text-center">' + lang('No deadlines found for this year.', 'Keine Fristen für dieses Jahr gefunden.') + '</div>');
                    return;
                }
                let typeInfo = JSON.parse(JSON.stringify(<?= json_encode($typeInfo) ?>));
                console.log(typeInfo);
                timeline(year, 0, typeInfo, events, clickEvent = function(data) {
                    location.href = ROOTPATH + '/deadlines/view/' + data.id;
                });
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function filterEvents(btn, filter = null, column = 1) {
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
                filterEvents(btn, null, column);
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }
        writeHash(hash)
    }
</script>