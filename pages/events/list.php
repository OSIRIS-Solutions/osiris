<?php
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$user = $_SESSION['username'];

$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
?>


<h1><?= lang('Events') ?></h1>


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
                    defaultContent: '',
                },
                {
                    targets: 5,
                    data: 'topics',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (data.length === 0) return '';
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
                dataTable.search(hash.search).draw();
            }
            if (hash.page !== undefined) {
                dataTable.page(parseInt(hash.page) - 1).draw('page');
            }
            if (hash.start !== undefined) {
                filterEvents(document.getElementById(hash.start + '-btn'), hash.start, 2)
            }
            initializing = false;


            // count data for the filter and add it to the filter
            let all_filters = {
                4: '#filter-type',
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