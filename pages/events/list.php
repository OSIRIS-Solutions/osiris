<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$user = $_SESSION['username'];

$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
$tagsEnabled = $Settings->featureEnabled('tags');
?>


<h1>
    <i class="ph-duotone ph-calendar-dots"></i>
    <?= lang('Events') ?>
</h1>


<!-- modal for adding conference -->
<?php if ($Settings->hasPermission('conferences.edit')) { ?>
    <div class="btn-toolbar">
        <a href="<?= ROOTPATH ?>/conferences/new" class="">
            <i class="ph ph-plus"></i>
            <?= lang('Add event', 'Event hinzufügen') ?>
        </a>
    </div>
<?php } ?>


<!-- 
<p class="text-muted mt-0">
    <small> <?= lang('Events were added by users of the OSIRIS system.', 'Events wurden von Nutzenden des OSIRIS-Systems angelegt.') ?></small>
</p> -->

<?php
// conferences max past 3 month
$conferences = $osiris->conferences->find(
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
                    <th><?= lang('Location', 'Ort') ?></th>
                    <th><?= lang('Start', 'Anfang') ?></th>
                    <th><?= lang('End', 'Ende') ?></th>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= $Settings->topicLabel() ?></th>
                    <th><?= $Settings->tagLabel() ?></th>
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
                <?= lang('By type', 'Nach Typ') ?>
                <a class="float-right" onclick="filterEvents('#filter-type .active', null, 4)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-type" class="table small simple">
                    <?php
                    $vocab = $vocab = $Vocabulary->getValues('event-type');
                    foreach ($vocab as $v) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $v['id'] ?>" onclick="filterEvents(this, '<?= $v['id'] ?>', 4)" class="item" id="<?= $v['id'] ?>-btn">
                                    <span>
                                        <?= lang($v['en'], $v['de'] ?? null) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>


            <?php if ($topicsEnabled) { ?>
                <h6>
                    <?= $Settings->topicLabel() ?>
                    <a class="float-right" onclick="filterEvents('#filter-topics .active', null, 5)"><i class="ph ph-x"></i></a>
                </h6>

                <div class="filter">
                    <table id="filter-topics" class="table small simple">
                        <?php foreach ($osiris->topics->find([], ['sort' => ['order' => 1]]) as $a) {
                            $topic_id = $a['id'];
                        ?>
                            <tr style="--highlight-color:  <?= $a['color'] ?>;">
                                <td>
                                    <a data-type="<?= $topic_id ?>" onclick="filterEvents(this, '<?= $topic_id ?>', 5)" class="item" id="<?= $topic_id ?>-btn">
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

            <?php if ($tagsEnabled) { ?>
                <h6>
                    <?= $Settings->tagLabel() ?>
                    <a class="float-right" onclick="filterEvents('#filter-tags .active', null, 6)"><i class="ph ph-x"></i></a>
                </h6>
                <div class="filter" style="max-height: 15rem; overflow-y: auto;">
                    <table id="filter-tags" class="table small simple">
                        <?php
                        $keywords = DB::doc2Arr($Settings->get('tags', []));
                        foreach ($keywords as $tag) {
                            $tagId = preg_replace('/[^a-z0-9]+/i', '-', strtolower($tag));
                        ?>
                            <tr>
                                <td>
                                    <a data-type="<?= $tag ?>" onclick="filterEvents(this, '<?= $tag ?>', 6)" class="item" id="tag-<?= $tagId ?>-btn">
                                        <span><?= $tag ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            <?php } ?>

            <!-- filter by year -->
            <h6>
                <?= lang('By year', 'Nach Jahr') ?>
                <a class="float-right" onclick="filterEvents('#filter-year .active', null, 2)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-year" class="table small simple">
                    <?php
                    $years = [];
                    foreach ($conferences as $c) {
                        $year = date('Y', strtotime($c['start']));
                        if (!in_array($year, $years)) {
                            $years[] = $year;
                        }
                    }
                    rsort($years);
                    foreach ($years as $y) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $y ?>" onclick="filterEvents(this, '<?= $y ?>', 2)" class="item" id="<?= $y ?>-btn">
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
<script src="<?= ROOTPATH ?>/js/my-year.js?v=<?= CSS_JS_VERSION ?>"></script>
<script>
    const topicsEnabled = <?= $topicsEnabled ? 'true' : 'false' ?>;

    var dataTable;
    var rootpath = '<?= ROOTPATH ?>'

    let headers = [{
            'key': 'title',
            'title': lang('Title', 'Titel')
        },
        {
            'key': 'location',
            'title': lang('Location', 'Ort')
        },
        {
            'key': 'start',
            'title': lang('Start', 'Anfang')
        },
        {
            'key': 'end',
            'title': lang('End', 'Ende')
        },
        {
            'key': 'type',
            'title': lang('Type', 'Typ')
        },
        {
            title: '<?= $Settings->topicLabel() ?>',
            key: 'topics'
        },
        {
            title: '<?= $Settings->tagLabel() ?>',
            key: 'tags'
        }
    ]


    function renderTopic(data) {
        let topics = '';
        if (topicsEnabled && data && data.length > 0) {
            topics = '<span class="topic-icons d-inline-flex">'
            data.forEach(function(topic) {
                topics += `<a href="<?= ROOTPATH ?>/topics/view/${topic}" class="topic-icon topic-${topic}"></a> `
            })
            topics += '</span>'
        }
        return topics;
    }


    const activeFilters = $('#active-filters')
    $(document).ready(function() {
        dataTable = $('#result-table').DataTable({
            "ajax": {
                "url": rootpath + '/api/conferences',
                dataSrc: 'data'
            },
            responsive: true,
            autoWidth: true,
            deferRender: true,
            language: {
                url: lang(null, ROOTPATH + '/js/datatables/de-DE.json')
            },
            buttons: [
                {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6],
                        format: {
                            header: function(html, index, node) {
                                return headers[index].title ?? '';
                            }
                        }
                    },
                    className: 'btn small',
                    title: function() {
                        var filters = []
                        activeFilters.find('.badge').find('span').each(function(i, el) {
                            filters.push(el.innerHTML)
                        })
                        console.log(filters);
                        if (filters.length == 0) return "OSIRIS All Events";
                        return 'OSIRIS Events ' + filters.join('_')
                    },
                    text: '<i class="ph ph-file-xls"></i> Export'
                },
            ],
            dom: 'fBrtip',
            columnDefs: [{
                    targets: 0,
                    data: 'title',
                    searchable: true,
                    render: function(data, type, row) {
                        return `<a href="${rootpath}/conferences/view/${row.id}" class="font-weight-bold">${row.title}</a>
                        ${renderTopic(row.topics)}
                        <br>
                        ${row.title_full ?? ''}
                        `;
                    }
                },
                {
                    targets: 1,
                    data: 'location',
                    searchable: true,
                },
                {
                    targets: 2,
                    data: 'start',
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
                    targets: 3,
                    data: 'end',
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
                    targets: 4,
                    data: 'type',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                },
                {
                    targets: 5,
                    data: 'topics',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (data === undefined || data.length === 0) return '';
                        return data.join(' ');
                    }
                },
                {
                    target: 6,
                    data: 'tags',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: '<?= $Settings->tagLabel() ?>',
                    render: function(data, type, row) {
                        if (data === undefined || data.length === 0) return '';
                        return data.join(' ');
                    }
                }
            ],
            "order": [
                [2, 'desc']
            ],
        });

        var initializing = true;
        dataTable.on('init', function() {

            var hash = readHash();
            if (hash.type !== undefined) {
                filterEvents(document.getElementById(hash.status + '-btn'), hash.status, 1)
            }
            if (hash.search !== undefined) {
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


            // count data for the filter and add it to the filter
            let all_filters = {
                4: '#filter-type',
                5: '#filter-topics',
                6: '#filter-tags',
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
            console.log(settings.oPreviousSearch.sSearch);
            writeHash({
                page: info.page + 1,
                search: settings.oPreviousSearch.sSearch
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
            url: ROOTPATH + "/api/dashboard/event-timeline",
            data: {
                year: year
            },
            dataType: "json",
            success: function(response) {
                let events = response.data.events;
                if (events.length === 0) {
                    $(selector).html('<div class="content text-muted text-center">' + lang('No activities found for this year.', 'Keine Aktivitäten für dieses Jahr gefunden.') + '</div>');
                    return;
                }
                let typeInfo = {}
                let colors = ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#343a40'];
                for (const type of response.data.types) {
                    typeInfo[type] = {
                        title: type,
                        color: colors[Object.keys(typeInfo).length % colors.length],
                    }
                }
                timeline(year, 0, typeInfo, events, clickEvent = function(data) {
                    location.href = ROOTPATH + '/conferences/view/' + data.id;
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