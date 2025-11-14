<?php

/**
 * Page to see all proposals
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /proposals
 *
 * @package     OSIRIS
 * @since       1.5.0
 * @category   Projects
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
require_once BASEPATH . "/php/Project.php";
$Project = new Project();
$Project->isProposal = true;

// $Format = new Document(true);
$form = $form ?? array();

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return htmlspecialchars($val);
    }
    return $val;
}

$filter = [];
$tagsEnabled = $Settings->featureEnabled('tags');
?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/projecttable.css">

<style>
    .index {
        /* color: transparent; */
        height: 1rem;
        width: 1rem;
        background-color: transparent;
        border-radius: 50%;
        display: inline-block;
        margin-left: .5rem;
    }

    .index.active {
        background-color: var(--secondary-color);
        box-shadow: 0 0 3px 0.2rem rgba(238, 114, 3, 0.6);
    }

    table.dataTable td.dt-control:before {
        display: inline-block;
        box-sizing: border-box;
        content: "";
        border-top: 5px solid transparent;
        border-left: 10px solid rgba(0, 0, 0, 0.5);
        border-bottom: 5px solid transparent;
        border-right: 0px solid transparent;
    }

    .dropdown-menu .item.active {
        background-color: var(--primary-color-20);
        color: var(--primary-color);
        font-weight: bold;
    }
</style>

<h1 class="mt-0">
    <i class="ph-duotone ph-tree-structure"></i>
    <?= lang('Project proposals', 'Projektanträge') ?>
</h1>

<button class="btn primary float-right" onclick="$('.filter-wrapper').slideToggle()">Filter <i class="ph ph-caret-down"></i></button>


<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/proposals/statistics" class="btn">
        <i class="ph ph-chart-line-up"></i>
        <?= lang('Statistics', 'Statistiken') ?>
    </a>
    <!-- 
    <a href="<?= ROOTPATH ?>/projects/search" class="btn">
        <i class="ph ph-magnifying-glass-plus"></i>
        <?= lang('Advanced search', 'Erweiterte Suche') ?>
    </a>
     -->

    <?php if ($Settings->canProposalsBeCreated()) { ?>
        <a href="<?= ROOTPATH ?>/proposals/new" class="">
            <i class="ph ph-plus"></i>
            <?= lang('Add new proposal', 'Neuen Antrag anlegen') ?>
        </a>
    <?php } else { ?>
        <a href="#" class="btn link disabled p-0" onclick="toastError('<?= lang('You are not authorized to add new proposals.', 'Du hast keine Berechtigung, neue Anträge zu erstellen.') ?>')">
            <i class="ph ph-plus"></i>
            <?= lang('Add new proposal', 'Neuen Antrag anlegen') ?>
        </a>
    <?php } ?>
</div>


<div class="row row-eq-spacing">
    <div class="col order-last order-sm-first">

        <div class="dropdown float-right">
            <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-sort-ascending"></i>
                <?= lang('Sort', 'Sortieren') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-1">
                <a class="item" onclick="sortTable(this, 3, 'asc')">Start date (ASC)</a>
                <a class="item active" onclick="sortTable(this, 3, 'desc')">Start date (DESC)</a>
                <a class="item" onclick="sortTable(this, 4, 'asc')">End date (ASC)</a>
                <a class="item" onclick="sortTable(this, 4, 'desc')">End date (DESC)</a>
                <a class="item" onclick="sortTable(this, 0, 'asc')">Name (ASC)</a>
                <a class="item" onclick="sortTable(this, 0, 'desc')">Name (DESC)</a>
            </div>
        </div>

        <table class="table cards w-full" id="project-table">
            <thead>
                <th>Project</th>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">
                        <i class="ph ph-spinner-third text-muted"></i>
                        <?= lang('Loading proposals', 'Lade Projektanträge') ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if (!$Settings->hasPermission('proposals.view')) { ?>
            <p class="text-muted">
                <?= lang('Only your own proposals are shown, since you are not authorized to view all proposals.', 'Es werden nur deine eigenen Anträge angezeigt, da du nicht berechtigt bist, alle Anträge zu sehen.') ?>
            </p>
        <?php } ?>
    </div>

    <div class="col-3 filter-wrapper">

        <div class="filters content" id="filters">
            <!-- <div class="content" > -->
            <div class="title">Filter</div>

            <!-- <div id="searchpanes"></div> -->

            <div id="active-filters"></div>

            <h6>
                <?= lang('By type', 'Nach Projekttyp') ?>
                <a class="float-right" onclick="filterProjects('#filter-type .active', null, 1)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-type" class="table small simple">
                    <?php
                    $vocab = $Project->getProjectTypes();
                    foreach ($vocab as $v) {
                        if ($v['process'] == 'project') continue;
                    ?>
                        <tr style="--highlight-color: <?= $v['color'] ?>;">
                            <td>
                                <a data-type="<?= $v['id'] ?>" onclick="filterProjects(this, '<?= $v['id'] ?>', 1)" class="item" id="<?= $v['id'] ?>-btn" style="color:var(--highlight-color);">
                                    <span>
                                        <i class="ph ph-<?= $v['icon'] ?>"></i>&nbsp;
                                        <?= lang($v['name'], $v['name_de'] ?? null) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>


            <h6>
                <?= lang('By status', 'Nach Status') ?>
                <a class="float-right" onclick="filterProjects('#filter-status .active', null, 7)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-status" class="table small simple">
                    <tr style="--highlight-color: var(--success-color)">
                        <td>
                            <a data-type="approved" onclick="filterProjects(this, 'approved', 7)" class="item text-success"><?= lang('approved', 'bewilligt') ?></a>
                        </td>
                    </tr>
                    <tr style="--highlight-color: var(--signal-color)">
                        <td>
                            <a data-type="proposed" onclick="filterProjects(this, 'proposed', 7)" class="item text-signal"><?= lang('applied', 'beantragt') ?></a>
                        </td>
                    </tr>
                    <tr style="--highlight-color: var(--danger-color)">
                        <td>
                            <a data-type="rejected" onclick="filterProjects(this, 'rejected', 7)" class="item text-danger"><?= lang('rejected', 'abgelehnt') ?></a>
                        </td>
                    </tr>
                    <tr style="--highlight-color: var(--muted-color)">
                        <td>
                            <a data-type="withdrawn" onclick="filterProjects(this, 'withdrawn', 7)" class="item text-muted"><?= lang('withdrawn', 'zurückgezogen') ?></a>
                        </td>
                    </tr>
                </table>
            </div>


            <h6>
                <?= lang('By funder', 'Nach Zuwendungsgeber') ?>
                <a class="float-right" onclick="filterProjects('#filter-funder .active', null, 2)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-funder" class="table small simple">
                    <?php foreach ($Vocabulary->getValues('funder') as $v) { ?>
                        <tr>
                            <td>
                                <a data-type="<?= $v['id'] ?>" onclick="filterProjects(this, '<?= $v['id'] ?>', 2)" class="item" id="<?= $v['id'] ?>-btn" style="color:inherit;">
                                    <span>
                                        <?= lang($v['en'], $v['de'] ?? null) ?>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>


            <?php if ($tagsEnabled) { ?>
                <h6>
                    <?= $Settings->tagLabel() ?>
                    <a class="float-right" onclick="filterProjects('#filter-tags .active', null, 13)"><i class="ph ph-x"></i></a>
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
                                    <a data-type="<?= $tag ?>" onclick="filterProjects(this, '<?= $tag ?>', 13)" class="item" id="<?= $tagId ?>-btn">
                                        <span><?= $tag ?></span>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            <?php } ?>

            <h6>
                <?= lang('By organisational unit', 'Nach Organisationseinheit') ?>
                <a class="float-right" onclick="filterProjects('#filter-units .active', null, 8)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-units" class="table small simple">
                    <?php foreach ($Departments as $dept_id => $dept) { ?>
                        <tr <?= $Groups->cssVar($dept_id) ?>>
                            <td>
                                <a data-type="<?= $dept_id ?>" onclick="filterProjects(this, '<?= $dept_id ?>', 8)" class="item colorless" id="<?= $dept_id ?>-btn">
                                    <span><?= $dept ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <!-- <h6>
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
            </div> -->

            <?php if ($Settings->featureEnabled('topics')) { ?>
                <h6>
                    <?= $Settings->topicLabel() ?>
                    <a class="float-right" onclick="filterProjects('#filter-topics .active', null, 9)"><i class="ph ph-x"></i></a>
                </h6>

                <div class="filter">
                    <table id="filter-topics" class="table small simple">
                        <?php foreach ($osiris->topics->find([], ['sort' => ['order' => 1]]) as $a) {
                            $topic_id = $a['id'];
                        ?>
                            <tr style="--highlight-color:  <?= $a['color'] ?>;">
                                <td>
                                    <a data-type="<?= $topic_id ?>" onclick="filterProjects(this, '<?= $topic_id ?>', 9)" class="item" id="<?= $topic_id ?>-btn">
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

    const minEl = document.querySelector('#filter-from');
    const maxEl = document.querySelector('#filter-to');

    const activeFilters = $('#active-filters')
    const headers = [{
            title: lang('Project', 'Projekt'),
            key: 'name'
        },
        {
            title: lang('Type', 'Typ'),
            key: 'type'
        },
        {
            title: lang('Funder', 'Mittelgeber'),
            key: 'funder'
        },
        {
            title: lang('Start date', 'Startdatum'),
            key: 'start_date'
        },
        {
            title: lang('End date', 'Enddatum'),
            key: 'end_date'
        },
        {
            title: lang('Role', 'Rolle'),
            key: 'role'
        },
        {
            title: lang('Applicant', 'Antragsteller'),
            key: 'applicant'
        },
        {
            title: lang('Status', 'Status'),
            key: 'status'
        },
        {
            title: lang('Units', 'Einheiten'),
            key: 'units'
        },
        {
            title: lang('Topics', 'Themen'),
            key: 'topics'
        },
        {
            title: lang('Funding organization', 'Förderorganisation'),
            key: 'funding_organization'
        },
        {
            title: lang('Project', 'Projekt'),
            key: 'name'
        },
        {
            title: lang('Title', 'Titel'),
            key: 'title'
        },
        {
            title: '<?= $Settings->tagLabel() ?>',
            key: 'tags'
        }
    ]


    function renderType(data) {
        <?php
        $vocab = $Project->getProjectTypes();
        foreach ($vocab as $v) { ?>
            if (data == '<?= $v['id'] ?>' || data == '<?= $v['id'] ?>') {
                return `<span class="badge" style="color: <?= $v['color'] ?>">
                            <i class="ph ph-<?= $v['icon'] ?>"></i>&nbsp;<?= lang($v['name'], $v['name_de'] ?? null) ?>
                        </span>`
            }
        <?php } ?>
        // Legacy types
        if (data == 'Eigenfinanziert' || data == 'self-funded') {
            return `<span class="badge text-signal">
                        <i class="ph ph-piggy-bank"></i>&nbsp;${lang('Self-funded', 'Eigenfinanziert')}
                        </span>`
        }
        if (data == 'Stipendium' || data == 'stipendiate') {
            return `<span class="badge text-success no-wrap">
                        <i class="ph ph-tip-jar"></i>&nbsp;${lang('Stipendiate', 'Stipendium')}
                        </span>`
        }
        if (data == 'Drittmittel' || data == 'third-party') {
            return `<span class="badge text-danger">
                        <i class="ph ph-hand-coins"></i>&nbsp;${lang('Third-party funded', 'Drittmittel')}
                        </span>`
        }
        if (data == 'Teilprojekt' || data == 'subproject') {
            return `<span class="badge text-danger">
                        <i class="ph ph-hand-coins"></i>&nbsp;${lang('Subproject', 'Teilprojekt')}
                        </span>`
        }
        return data;
    }

    function renderFunder(row) {
        if (!row.funder && row.scholarship) return row.scholarship;
        return row.funder ?? '';
    }

    function renderRole(data) {
        if (data == 'coordinator') {
            return `<span class="badge text-signal">
        <i class="ph ph-crown"></i>
        ${lang('Coordinator', 'Koordinator')}
        </span>`
        }
        if (data == 'associated') {
            return `<span class="badge text-success">
        <i class="ph ph-address-book"></i>
        ${lang('Associated', 'Beteiligt')}
        </span>`
        }
        return `<span class="badge text-muted">
        <i class="ph ph-handshake"></i>
        ${lang('Partner')}
        </span>`
    }

    function renderContact(row) {
        if (!row.contact && row.supervisor)
            return `<a href="<?= ROOTPATH ?>/profile/${row.supervisor}">${row.applicant}</a>`;
        if (!row.contact)
            return row.applicant;
        return `<a href="<?= ROOTPATH ?>/profile/${row.contact}">${row.applicant}</a>`
    }

    function renderStatus(data) {
        switch (data) {
            case 'approved':
                return `<span class='badge success'>${lang('approved', 'bewilligt')}</span>`;
            case 'finished':
                return `<span class='badge success filled'>${lang('finished', 'abgeschlossen')}</span>`;
            case 'applied':
            case 'proposed':
                return `<span class='badge signal'>${lang('proposed', 'beantragt')}</span>`;
            case 'rejected':
                return `<span class='badge danger'>${lang('rejected', 'abgelehnt')}</span>`;
            case 'withdrawn':
                return `<span class='badge muted'>${lang('withdrawn', 'zurückgezogen')}</span>`;
            case 'expired':
                return `<span class='badge dark'>${lang('expired', 'abgelaufen')}</span>`;
        }
    }

    function renderTopic(data) {
        let topics = '';
        if (data && data.length > 0) {
            topics = '<span class="float-right topic-icons">'
            data.forEach(function(topic) {
                topics += `<a href="<?= ROOTPATH ?>/topics/view/${topic}" class="topic-icon topic-${topic}"></a> `
            })
            topics += '</span>'
        }
        return topics;
    }

    function renderDate(data) {
        var start = data.start_date;
        // format from ISO to MM/YYYY
        if (start) {
            start = new Date(start).toLocaleDateString('de-DE', {
                month: 'short',
                year: 'numeric'
            });
        }
        var end = data.end_date;
        if (end) {
            end = new Date(end).toLocaleDateString('de-DE', {
                month: 'short',
                year: 'numeric'
            });
            return `${start} - ${end}`;
        } else {
            return start;
        }
    }

    $(document).ready(function() {
        dataTable = new DataTable('#project-table', {
            ajax: {
                url: '<?= ROOTPATH ?>/api/proposals',
                // add data to the request
                data: {
                    json: '<?= json_encode($filter) ?>',
                    // formatted: true
                },
            },
            type: 'GET',
            deferRender: true,
            responsive: true,
            language: {
                url: lang(null, ROOTPATH + '/js/datatables/de-DE.json')
            },
            buttons: [{
                extend: 'excelHtml5',
                exportOptions: {
                    columns: [10, 1, 2, 9, 3, 4, 5, 6, 7],
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
                    if (filters.length == 0) return "OSIRIS All Projects";
                    return 'OSIRIS Projects ' + filters.join('_')
                },
                text: '<i class="ph ph-file-xls"></i> Export'
            }, ],
            dom: 'fBrtip',
            columnDefs: [{
                    target: 0,
                    data: 'name',
                    render: function(data, type, row) {
                        console.log(row);
                        let persons = '';
                        if (row.persons && row.persons.length > 0) {
                            persons = row.persons.map(a => a.name).join(', ')
                        }

                        // row.persons.map(a => a.name).join(', ')
                        return `
                        ${renderTopic(row.topics)}
                        <div class="d-flex flex-column h-full">
                        <h4 class="m-0">
                            <a href="<?= ROOTPATH ?>/proposals/view/${row._id['$oid']}">${data}</a>
                        </h4>
                       
                        <div class="flex-grow-1">
                         <p class="text-muted mt-0">${renderDate(row)}</p>
                        
                        ${persons}

                        </div>
                        <hr />
                        
                        <div class="d-flex justify-content-between">
                            ${renderType(row.type)}
                            ${renderStatus(row.status ?? 'proposed')}
                        </div>
                    </div>
                        `
                    }
                },
                {
                    target: 1,
                    data: 'type',
                    searchable: true,
                    visible: false,
                    header: lang('Type', 'Typ')
                },
                {
                    target: 2,
                    data: 'funder',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Funder', 'Drittmmittelgeber'),
                    render: (data, type, row) => renderFunder(row)
                },
                {
                    target: 3,
                    data: 'start_date',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Start date', 'Startdatum')
                },
                {
                    target: 4,
                    data: 'end_date',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('End date', 'Enddatum')
                },
                {
                    target: 5,
                    data: 'role',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Role', 'Rolle')
                },
                {
                    target: 6,
                    data: 'applicant',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Applicant', 'Antragsteller')
                },
                {
                    target: 7,
                    data: 'status',
                    searchable: true,
                    visible: false,
                    header: lang('Status', 'Status'),
                    defaultContent: 'proposed'
                },
                {
                    target: 8,
                    data: 'units',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Units', 'Einheiten')
                },
                {
                    target: 9,
                    data: 'topics',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Topics', 'Forschungsbereiche'),
                    render: (data, type, row) => {
                        if (Array.isArray(data)) {
                            return data.join(', ')
                        }
                    }
                },
                {
                    target: 10,
                    data: 'funder',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Funding organization', 'Förderorganisation')
                },
                {
                    target: 11,
                    data: 'name',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: lang('Project', 'Projekt')
                },
                {
                    target: 12,
                    data: 'title',
                    searchable: false,
                    visible: false,
                    defaultContent: '',
                    header: lang('Title', 'Titel')
                },
                {
                    target: 13,
                    data: 'tags',
                    searchable: true,
                    visible: false,
                    defaultContent: '',
                    header: '<?= $Settings->tagLabel() ?>',
                }
            ],
            order: [
                [3, 'desc']
            ],
            paging: true,
            autoWidth: true,
            pageLength: 8,
        });

        // $('#project-table_wrapper').prepend($('.filters'))


        var initializing = true;
        dataTable.on('init', function() {

            var hash = readHash();
            console.log(hash);
            if (hash.status !== undefined) {
                filterProjects(document.getElementById(hash.status + '-btn'), hash.status, 7)
            }
            if (hash.units !== undefined) {
                filterProjects(document.getElementById(hash.unit + '-btn'), hash.unit, 8)
            }
            if (hash.role !== undefined) {
                filterProjects(document.getElementById(hash.role + '-btn'), hash.role, 5)
            }
            if (hash.topics !== undefined) {
                filterProjects(document.getElementById(hash.topics + '-btn'), hash.topics, 9)
            }
            if (hash.tags !== undefined) {
                var tagId = hash.tags.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '-btn';
                var tag = document.getElementById(tagId).getAttribute('data-type');
                filterProjects(document.getElementById(tagId), tag, 13)
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
                1: '#filter-type',
                2: '#filter-funder',
                7: '#filter-status',
                8: '#filter-units',
                9: '#filter-topics'
            }
            for (const key in all_filters) {
                if (Object.prototype.hasOwnProperty.call(all_filters, key)) {
                    const element = all_filters[key];
                    const filter = $(element).find('a')
                    filter.each(function(i, el) {
                        let type = $(el).data('type')
                        const count = dataTable.column(key).data().filter(function(d) {
                            if (key == 8 || key == 9) {
                                return d.includes(type)
                            }
                            return d == type
                        }).length
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


    function filterProjects(btn, activity = null, column = 1) {
        console.log(column);
        var tr = $(btn).closest('tr')
        var table = tr.closest('table')
        $('#filter-' + column).remove()
        const field = headers[column]
        const hash = {}
        hash[field.key] = activity

        if (tr.hasClass('active') || activity === null) {
            hash[field.key] = null
            table.find('.active').removeClass('active')
            dataTable.columns(column).search("", true, false, true).draw();

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
                filterProjects(btn, null, column);
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }
        writeHash(hash)

    }

    function sortTable(el, column, direction = 'asc') {
        $(el).closest('.dropdown-menu').find('.active').removeClass('active');
        $(el).addClass('active');

        dataTable.order([column, direction]).draw();
        return false;
    }
</script>