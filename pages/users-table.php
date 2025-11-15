<?php

/**
 * Page to browse all users
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /user/browse
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */


$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;

$data_fields = $Settings->get('person-data');
if (!is_null($data_fields)) {
    $data_fields = DB::doc2Arr($data_fields);
} else {
    $fields = file_get_contents(BASEPATH . '/data/person-fields.json');
    $fields = json_decode($fields, true);

    $data_fields = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data_fields = array_column($data_fields, 'id');
}

$active = function ($field) use ($data_fields) {
    return in_array($field, $data_fields);
};
$keyword_name = 'Keywords';
if ($active('keywords')) {
    $keyword_name = $Settings->get('staff-keyword-name', 'Keywords');
}

?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css?v=2">

<?php if ($Settings->featureEnabled('portal')) { ?>
    <a href="<?= ROOTPATH ?>/preview/persons" class="btn float-right"><i class="ph ph-eye"></i> <?= lang('Preview', 'Vorschau') ?></a>
<?php } ?>
<?php if ($Settings->hasPermission('user.synchronize') && strtoupper(USER_MANAGEMENT) === 'LDAP') { ?>
    <a href="<?= ROOTPATH ?>/synchronize-users" class="btn float-right"><i class="ph ph-sync"></i> <?= lang('Synchronize users', 'Nutzende synchronisieren') ?></a>
<?php } ?>

<h1>
    <i class="ph-duotone ph-student"></i>
    <?= lang('Users', 'Personen') ?>
</h1>

<div class="row row-eq-spacing">
    <div class="col-lg-9">

        <table class="table cards w-full" id="user-table">
            <thead>
                <th><?= lang('Image', 'Bild') ?></th>
                <th></th>
                <th><?= lang('Units', 'Einheiten') ?></th>
                <th><?= lang('Active', 'Aktiv') ?></th>
                <th><?= lang('Names', 'Namen') ?></th>
                <th><?= lang('Research topics', 'Forschungsbereiche') ?></th>
                <th><?= lang('First name', 'Vorname') ?></th>
                <th><?= lang('Last name', 'Nachname') ?></th>
                <th><?= lang('Academic title', 'Akad. Titel') ?></th>
                <th><?= lang('Email', 'E-Mail') ?></th>
                <th><?= lang('Telephone', 'Telefon') ?></th>
                <th><?= lang('Position', 'Position') ?></th>
                <th><?= lang('ORCID', 'ORCID') ?></th>
                <th><?= lang('Username', 'Kürzel') ?></th>
                <th><?= $keyword_name ?></th>
            </thead>
            <tbody>

            </tbody>
        </table>

    </div>
    </style>

    <div class="col-lg-3 d-none d-lg-block">

        <div class="on-this-page-filter filters content" id="filters">

            <div class="title">Filter</div>

            <div id="active-filters"></div>

            <h6>
                <?= lang('By organisational unit', 'Nach Organisationseinheit') ?>
                <a class="float-right" onclick="filterUsers('#filter-unit .active', null, 2)"><i class="ph ph-x"></i></a>
            </h6>
            <div class="filter">
                <table id="filter-unit" class="table simple">
                    <?php foreach ($Departments as $id => $dept) { ?>
                        <tr <?= $Groups->cssVar($id) ?>>
                            <td>
                                <a data-type="<?= $id ?>" onclick="filterUsers(this, '<?= $id ?>', 2)" class="item d-block colorless" id="<?= $id ?>-btn">
                                    <span><?= $dept ?></span>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </div>


            <?php if ($topicsEnabled) { ?>
                <h6><?= $Settings->topicLabel() ?>
                    <a class="float-right" onclick="filterUsers('#filter-unit .active', null, 5)"><i class="ph ph-x"></i></a>
                </h6>

                <div class="filter">
                    <table id="filter-type" class="table small simple">
                        <?php foreach ($osiris->topics->find([], ['sort' => ['order' => 1]]) as $a) {
                            $id = $a['id'];
                        ?>
                            <tr style="--highlight-color:  <?= $a['color'] ?>;">
                                <td>
                                    <a data-type="<?= $id ?>" onclick="filterUsers(this, '<?= $id ?>', 5)" class="item" id="<?= $id ?>-btn">
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

            <?php if ($active('keywords')) {
                $keywords = $Settings->get('staff-keywords', []);
                if (!empty($keywords)) { ?>
                    <h6><?= $keyword_name ?>
                        <a class="float-right" onclick="filterUsers('#filter-keywords .active', null, 6)"><i class="ph ph-x"></i></a>
                    </h6>

                    <div class="filter">
                        <table id="filter-keywords" class="table small simple">
                            <?php foreach ($keywords as $kw) { ?>
                                <tr>
                                    <td>
                                        <a data-type="<?= $kw ?>" onclick="filterUsers(this, '<?= $kw ?>', 14)" class="item" id="<?= $kw ?>-btn">
                                            <span><?= $kw ?></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                <?php }
            } ?>


            <h6><?= lang('Active workers', 'Aktive Mitarbeitende') ?></h6>
            <div class="custom-switch">
                <input type="checkbox" id="active-switch" value="" onchange="filterActive(this)">
                <label for="active-switch"><?= lang('Include Inactive', 'Inkl. Inaktiv') ?></label>
            </div>
        </div>
    </div>
</div>



<script>
    const headers = [{
            title: lang('Image', 'Bild'),
            'key': 'img'
        },
        {
            title: '',
            'key': 'html'
        },
        {
            title: lang('Units', 'Einheiten'),
            'key': 'dept'
        },
        {
            title: lang('Active', 'Aktiv'),
            'key': 'active'
        },
        {
            title: lang('Names', 'Namen'),
            'key': 'names'
        },
        {
            title: lang('Research topics', 'Forschungsbereiche'),
            'key': 'topics'
        },
        {
            title: lang('First name', 'Vorname'),
            'key': 'first'
        },
        {
            title: lang('Last name', 'Nachname'),
            'key': 'last'
        },
        {
            title: lang('Academic title', 'Akad. Titel'),
            'key': 'academic_title'
        },
        {
            title: lang('Email', 'Email'),
            'key': 'mail'
        },
        {
            title: lang('Telephone', 'Telefon'),
            'key': 'telephone'
        },
        {
            title: lang('Position', 'Position'),
            'key': 'position'
        },
        {
            title: lang('ORCID', 'ORCID'),
            'key': 'orcid'
        },
        {
            title: lang('Username', 'Kürzel'),
            'key': 'username'
        },
        {
            title: lang('Keywords', 'Schlagwörter'),
            'key': 'keywords'
        }
    ]

    var dataTable;
    const activeFilters = $('#active-filters')
    $(document).ready(function() {
        dataTable = $('#user-table').DataTable({
            "ajax": {
                "url": ROOTPATH + '/api/users',
                "data": {
                    table: true,
                    subtitle: 'position'
                },
                dataSrc: 'data'
            },
            deferRender: true,
            responsive: true,
            language: {
                url: lang(null, ROOTPATH + '/js/datatables/de-DE.json')
            },
            paging: true,
            autoWidth: true,
            pageLength: 18,
            lengthMenu: [9, 18, 36, 72, 144],
            buttons: [
                // custom link button
                {
                    text: '<i class="ph ph-magnifying-glass-plus"></i> <?= lang('Advanced search', 'Erweiterte Suche') ?>',
                    className: 'btn small text-primary ',
                    action: function(e, dt, node, config) {
                        window.location.href = '<?= ROOTPATH ?>/user/search';
                    }
                },
                <?php if ($active('expertise')) { ?> {
                        text: '<i class="ph ph-barbell"></i> <?= lang('Expertise', 'Expertise') ?>',
                        className: 'btn small text-primary',
                        action: function(e, dt, node, config) {
                            window.location.href = '<?= ROOTPATH ?>/expertise';
                        }
                    },
                <?php } ?>
                <?php if ($active('keywords')) { ?> {
                        text: '<i class="ph ph-tag"></i> <?= $keyword_name ?>',
                        className: 'btn small text-primary',
                        action: function(e, dt, node, config) {
                            window.location.href = '<?= ROOTPATH ?>/keywords';
                        }
                    },
                <?php } ?> {
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [6, 7, 8, 9, 10, 11, 12, 2, 3, 4, 13],
                        format: {
                            header: function(html, index, node) {
                                return headers[index].title ?? '';
                            }
                        }
                    },
                    className: 'btn small ml-10',
                    title: 'OSIRIS Users',
                    text: '<i class="ph ph-file-xls"></i> Excel',
                },
                // pdf
                {
                    extend: 'pdfHtml5',
                    exportOptions: {
                        columns: [6, 7, 8, 9, 10, 11, 2]
                    },
                    className: 'btn small pdf-btn',
                    title: 'OSIRIS Users',
                    text: '<i class="ph ph-file-pdf"></i> PDF',
                    customize: function (doc) {
                        doc.defaultStyle = doc.defaultStyle || {};
                        doc.defaultStyle.fontSize = 8; // PDF body font size
                        doc.styles = doc.styles || {};
                        doc.styles.tableHeader = doc.styles.tableHeader || {};
                        doc.styles.tableHeader.fontSize = 9; // header font size
                    }
                }
            ],
            columnDefs: [{
                    targets: 0,
                    data: 'img',
                    title: lang('Image', 'Bild'),
                    searchable: false,
                    sortable: false,
                    visible: true
                },
                {
                    targets: 1,
                    data: 'html',
                    className: 'flex-grow-1',
                    searchable: false,
                },
                {
                    targets: 2,
                    data: 'dept',
                    title: lang('Dept.', 'Abteilung'),
                    searchable: true,
                    sortable: false,
                    visible: false
                },
                {
                    targets: 3,
                    data: 'active',
                    title: lang('Active', 'Aktiv'),
                    searchable: true,
                    sortable: false,
                    visible: false
                },
                {
                    target: 4,
                    data: 'names',
                    title: lang('Names', 'Namen'),
                    searchable: true,
                    visible: false
                },
                {
                    target: 5,
                    data: 'topics',
                    title: lang('Research topics', 'Forschungsbereiche'),
                    searchable: true,
                    visible: false
                },
                {
                    target: 6,
                    data: 'first',
                    title: lang('First name', 'Vorname'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 7,
                    data: 'last',
                    title: lang('Last name', 'Nachname'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 8,
                    data: 'academic_title',
                    title: lang('Academic title', 'Akad. Titel'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 9,
                    data: 'mail',
                    title: lang('Email', 'E-Mail'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 10,
                    data: 'telephone',
                    title: lang('Telephone', 'Telefon'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 11,
                    data: 'position',
                    title: lang('Position', 'Position'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 12,
                    data: 'orcid',
                    title: lang('ORCID', 'ORCID'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 13,
                    data: 'username',
                    title: lang('Username', 'Kürzel'),
                    visible: false,
                    defaultContent: ''
                },
                {
                    target: 14,
                    data: 'keywords',
                    title: '<?= $keyword_name ?>',
                    visible: false,
                    defaultContent: ''
                },
            ],
            "order": [
                [1, 'asc'],
            ],
        });

        var hash = readHash();

        $('#active-switch').prop('checked', hash.active === 'yes')
        filterActive()

        if (hash === undefined)
            return;

        if (hash.unit !== undefined) {
            filterUsers(document.getElementById(hash.unit + '-btn'), hash.unit, 2)
        }
        if (hash.topics !== undefined) {
            filterUsers(document.getElementById(hash.topics + '-btn'), hash.topics, 5)
        }
    });


    function filterUsers(btn, attr = null, column = 2) {
        var tr = $(btn).closest('tr')
        var table = tr.closest('table')
        $('#filter-' + column).remove()
        const field = headers[column]
        const hash = {}
        hash[field.key] = attr
        // escape parentheses
        attr = attr.replace(/[\(\)]/g, '\\$&')

        if (tr.hasClass('active') || attr === null) {
            hash[field.key] = null
            table.find('.active').removeClass('active')
            dataTable.columns(column).search("", true, false, true).draw();

        } else {

            table.find('.active').removeClass('active')
            tr.addClass('active')
            dataTable.columns(column).search(attr, true, false, true).draw();
            // indicator
            const filterBtn = $('<span class="badge" id="filter-' + column + '">')
            filterBtn.html(`<b>${field.title}:</b> <span>${attr}</span>`)
            const a = $('<a>')
            a.html('&times;')
            a.on('click', function() {
                filterUsers(btn, null, column);
            })
            filterBtn.append(a)
            activeFilters.append(filterBtn)
        }
        writeHash(hash)

    }

    function filterActive() {
        if ($('#active-switch').prop('checked')) {
            dataTable.columns(3).search("", true, false, true).draw();
        } else {
            dataTable.columns(3).search("yes", true, false, true).draw();
        }

        // write hash
        const hash = {
            active: $('#active-switch').prop('checked') ? 'yes' : null
        }
        writeHash(hash)
    }
</script>