<?php

/**
 * Page to perform advanced activity search
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activities/search
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document(true);
$expert = isset($_GET['expert']) ;
?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/query-builder.default.min.css">
<script src="<?= ROOTPATH ?>/js/query-builder.standalone.js?v=date"></script>
<script src="<?= ROOTPATH ?>/js/datatables/jszip.min.js"></script>
<script src="<?= ROOTPATH ?>/js/datatables/dataTables.buttons.min.js"></script>
<script src="<?= ROOTPATH ?>/js/datatables/buttons.html5.min.js"></script>

<script>
    var RULES;
    var EXPERT = <?= $expert ? 'true' : 'false' ?>;
</script>



<div class="container">
    <a href="<?= ROOTPATH ?>/docs/search" class="btn tour float-sm-right"><i class="ph ph-question"></i> <?= lang('Manual', 'Anleitung') ?></a>
    <h1>
        <i class="ph ph-magnifying-glass-plus text-osiris"></i>
        <?= lang('Advanced activity search', 'Erweiterte Aktivitäten-Suche') ?>
    </h1>

    <div class="row row-eq-spacing mt-0">
        <div class="col-md-8">

            <div class="box">
                <div class="content">

                    <h3 class="title"><?= lang('Filter', 'Filtern') ?></h3>
                    <div id="builder" class="<?= $expert ? 'hidden' : '' ?>"></div>

                    <?php if ($expert) { ?>
                        <textarea name="expert" id="expert" cols="30" rows="5" class="form-control"></textarea>
                    <?php } ?>

                </div>

                <div class="content">
                    <!-- Aggregations -->
                    <a onclick="$('#aggregate-form').slideToggle()">
                        <?= lang('Aggregate', 'Aggregieren') ?> <i class="ph ph-caret-down"></i>
                    </a>

                    <div class="input-group" style="display:none;" id="aggregate-form">
                        <select name="aggregate" id="aggregate" class="form-control w-auto">
                            <option value=""><?= lang('Without aggregation (show all)', 'Ohne Aggregation (zeige alles)') ?></option>
                        </select>

                        <!-- remove aggregation -->
                        <div class="input-group-append">
                            <button class="btn text-danger" onclick="$('#aggregate').val(''); getResult()"><i class="ph ph-x"></i></button>
                        </div>
                    </div>
                </div>

                <div class="footer">

                    <div class="btn-toolbar">

                        <?php if ($expert) { ?>
                            <button class="btn secondary" onclick="run()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>

                            <script>
                                function run() {
                                    var rules = $('#expert').val()
                                    RULES = JSON.parse(decodeURI(rules))
                                    dataTable.ajax.reload()
                                }
                            </script>
                            <a class="btn osiris" href="?"><i class="ph ph-search-plus"></i> <?= lang('Sandbox mode', 'Baukasten-Modus') ?></a>

                        <?php } else { ?>
                            <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>
                            <a class="btn osiris" href="?expert"><i class="ph ph-search-plus"></i> <?= lang('Expert mode', 'Experten-Modus') ?></a>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <!-- User saved queries -->
            <div class="box">
                <div class="content">
                    <h3 class="title">
                        <?php if ($expert) { ?>
                            <?= lang('My expert queries', 'Meine Experten-Abfragen') ?>
                        <?php } else { ?>
                            <?= lang('My saved queries', 'Meine Abfragen') ?>
                        <?php } ?>
                    </h3>
                    <?php
                    $filter = ['user' => $_SESSION['username'], 'type' => ['$ne' => 'project']];
                    if (!$expert) {
                        $filter['$or'] = [
                            ['expert' => false],
                            ['expert' => ['$exists' => false]]
                        ];
                    } else {
                        $filter['expert'] = true;
                    }
                    $queries = $osiris->queries->find($filter)->toArray();
                    if (empty($queries)) {
                        echo '<p>' . lang('You have not saved any queries yet.', 'Du hast noch keine Abfragen gespeichert.') . '</p>';
                    } else { ?>
                        <div class="list-group" id="saved-queries">
                            <?php foreach ($queries as $query) { ?>
                                <!-- use rules (json)  -->
                                <div class="d-flex justify-content-between" id="query-<?= $query['_id'] ?>">
                                    <a onclick="applyFilter('<?= $query['_id'] ?>', '<?= $query['aggregate'] ?>')"><?= $query['name'] ?></a>
                                    <a onclick="deleteQuery('<?= $query['_id'] ?>')" class="text-danger"><i class="ph ph-x"></i></a>
                                </div>
                            <?php } ?>
                        </div>
                    <?php  } ?>

                    <script>
                        var queries = {};
                        <?php foreach ($queries as $query) { ?>
                            queries['<?= $query['_id'] ?>'] = '<?= $query['rules'] ?>';
                        <?php } ?>
                    </script>

                </div>
                <hr>
                <div class="content">
                    <!-- save current query -->
                    <div class="form-group" id="save-query">
                        <label for="query-name"><?= lang('Save query', 'Abfrage speichern') ?></label>
                        <input type="text" class="form-control" id="query-name" placeholder="<?= lang('Name of query', 'Name der Abfrage') ?>">
                        <button class="btn secondary mt-10" onclick="saveQuery()"><?= lang('Save query', 'Abfrage speichern') ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- copy to clipboard -->
    <script>
        function copyToClipboard() {
            var text = $('#result').text()
            navigator.clipboard.writeText(text)
            toastSuccess('Query copied to clipboard.')
        }
    </script>

    <div class="position-relative">
        <button class="btn secondary small position-absolute top-0 right-0 m-10" onclick="copyToClipboard()"><i class="ph ph-clipboard" aria-label="Copy to clipboard"></i></button>

        <pre id="result" class="code p-20"></pre>
    </div>

    <br>

    <table class="table" id="activity-table">
        <thead>
            <th><?= lang('Type', 'Typ') ?></th>
            <th><?= lang('Result', 'Ergebnis') ?></th>
            <th><?= lang('Count', 'Anzahl') ?></th>
            <th><?= lang('Year', 'Jahr') ?></th>
            <th><?= lang('Print', 'Print') ?></th>
            <th><?= lang('Type', 'Typ') ?></th>
            <th><?= lang('Subtype', 'Subtyp') ?></th>
            <th><?= lang('Title', 'Titel') ?></th>
            <th><?= lang('Authors', 'Autoren') ?></th>
            <th>Link</th>
        </thead>
        <tbody>
        </tbody>
    </table>

    <?php
    $categories = $osiris->adminCategories->distinct('id');
    $types = $osiris->adminTypes->distinct('id');
    ?>

    <script>
        // var mongo = $('#builder').queryBuilder('getMongo');
        const types = JSON.parse('<?= json_encode($categories) ?>')
        const subtypes = JSON.parse('<?= json_encode($types) ?>')

        const filters = [{
                id: 'type',
                label: lang('Category', 'Kategorie'),
                type: 'string',
                input: 'select',
                values: types
            },
            {
                id: 'subtype',
                label: lang('Type', 'Typ'),
                type: 'string',
                input: 'select',
                values: subtypes
            },
            <?php if ($Settings->featureEnabled('topics')) { ?> {
                    id: 'topics',
                    label: lang('Research Topics', 'Forschungsbereiche'),
                    type: 'string',
                    input: 'select',
                    values: <?= json_encode($osiris->topics->distinct('id')) ?>
                },
            <?php } ?> {
                id: 'title',
                label: lang('Title', 'Titel'),
                type: 'string'
            },
            {
                id: 'start_date',
                label: lang('Start date', 'Startdatum'),
                type: 'datetime',
                input: 'date'
            },
            {
                id: 'end_date',
                label: lang('End date', 'Enddatum'),
                type: 'datetime',
                input: 'date'
            },
            {
                id: 'abstract',
                label: lang('Abstract', 'Abstract'),
                type: 'string'
            },
            {
                id: 'authors.first',
                label: lang('Author (first name)', 'Autor (Vorname)'),
                type: 'string'
            },
            {
                id: 'authors.last',
                label: lang('Author (last name)', 'Autor (Nachname)'),
                type: 'string'
            },
            {
                id: 'authors.user',
                label: lang('Author (username)', 'Autor (Username)'),
                type: 'string'
            },
            {
                id: 'authors.position',
                label: lang('Author (position)', 'Autor (Position)'),
                type: 'string',
                input: 'select',
                values: ['first', 'middle', 'last', 'corresponding']
            },
            {
                id: 'authors.approved',
                label: lang('Author (approved)', 'Autor (Bestätigt)'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'authors.aoi',
                label: lang('Author (affiliated)', 'Autor (Affiliated)'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'journal',
                label: lang('Journal'),
                type: 'string'
            },
            {
                id: 'issn',
                label: lang('ISSN'),
                type: 'string'
            },
            {
                id: 'magazine',
                label: lang('Magazine', 'Magazin'),
                type: 'string'
            },
            {
                id: 'year',
                label: lang('Year', 'Jahr'),
                type: 'integer',
                default_value: <?= CURRENTYEAR ?>
            },
            {
                id: 'month',
                label: lang('Month', 'Monat'),
                type: 'integer'
            },
            {
                id: 'lecture_type',
                label: lang('Lecture type', 'Vortragstyp'),
                input: 'select',
                values: ['short', 'long', 'repetition']
            },
            {
                id: 'editor_type',
                label: lang('Editor type', 'Editortyp'),
                type: 'string'
            },
            {
                id: 'doi',
                label: lang('DOI'),
                type: 'string'
            },
            {
                id: 'link',
                label: lang('Link'),
                type: 'string'
            },
            {
                id: 'pubmed',
                label: lang('Pubmed-ID'),
                type: 'integer'
            },
            {
                id: 'pubtype',
                label: lang('Publication type', 'Publikationstyp'),
                type: 'string',
                input: 'select',
                values: ['article', 'book', 'chapter', 'preprint', 'magazine', 'dissertation', 'others']
            },
            {
                id: 'gender',
                label: lang('Gender', 'Geschlecht'),
                type: 'string',
                input: 'select',
                values: ['f', 'm', 'd']
            },
            {
                id: 'issue',
                label: lang('Issue'),
                type: 'string'
            },
            {
                id: 'volume',
                label: lang('Volume'),
                type: 'string'
            },
            {
                id: 'pages',
                label: lang('Pages', 'Seiten'),
                type: 'string'
            },
            {
                id: 'impact',
                label: lang('Impact factor'),
                type: 'double'
            },
            {
                id: 'book',
                label: lang('Book title', 'Buchtitel'),
                type: 'string'
            },
            {
                id: 'publisher',
                label: lang('Publisher', 'Verlag'),
                type: 'string'
            },
            {
                id: 'city',
                label: lang('Location (Publisher)', 'Ort (Verlag)'),
                type: 'string'
            },
            {
                id: 'edition',
                label: lang('Edition'),
                type: 'string'
            },
            {
                id: 'isbn',
                label: lang('ISBN'),
                type: 'string'
            },
            {
                id: 'doc_type',
                label: lang('Document type', 'Dokumententyp'),
                type: 'string'
            },
            {
                id: 'iteration',
                label: lang('Iteration (Misc)', 'Wiederholung (misc)'),
                type: 'string',
                input: 'select',
                values: ['once', 'annual']
            },
            {
                id: 'software_type',
                label: lang('Type of software', 'Art der Software'),
                type: 'string',
                input: 'select',
                values: ['software', 'database', 'dataset', 'webtool', 'report']
            },
            {
                id: 'software_venue',
                label: lang('Publication venue (Software)', 'Ort der Veröffentlichung (Software)'),
                type: 'string'
            },
            {
                id: 'version',
                label: lang('Version'),
                type: 'string'
            },
            // {
            //         id: 'affiliation',
            //         label: lang('Affiliation', ''),
            //         type: 'string'
            // },
            // {
            //     id: 'sws',
            //     label: lang('SWS'),
            //     type: 'string'
            // },
            {
                id: 'category',
                label: lang('Category (students/guests)', 'Kategorie (Studenten/Gäste)'),
                type: 'string',
                input: 'select',
                values: {
                    'guest scientist': lang('Guest Scientist', 'Gastwissenschaftler:in'),
                    'lecture internship': lang('Lecture Internship', 'Pflichtpraktikum im Rahmen des Studium'),
                    'student internship': lang('Student Internship', 'Schülerpraktikum'),
                    'other': lang('Other', 'Sonstiges'),
                    'doctoral thesis': lang('Doctoral Thesis', 'Doktorand:in'),
                    'master thesis': lang('Master Thesis', 'Master-Thesis'),
                    'bachelor thesis': lang('Bachelor Thesis', 'Bachelor-Thesis')
                }

            },
            {
                id: 'status',
                label: lang('Status (Thesis)'),
                type: 'string',
                input: 'select',
                values: ['in progress', 'completed', 'aborted']

            },
            {
                id: 'role',
                label: lang('Role (Reviews)', 'Rolle (Reviews)'),
                type: 'string',
                input: 'select',
                values: {
                    'review': 'Reviewer',
                    'editorial': 'Editorial board',
                    'grant-rev': 'Grant proposal',
                    'thesis-rev': 'Thesis review'
                }

            },
            {
                id: 'name',
                label: lang('Name of guest', 'Name des Gastes'),
                type: 'string'
            },
            {
                id: 'academic_title',
                label: lang('Academic title of guest', 'Akad. Titel des Gastes'),
                type: 'string'
            },
            {
                id: 'details',
                label: lang('Details (Students/guests)', 'Details (Studenten/Gäste)'),
                type: 'string'
            },
            {
                id: 'conference',
                label: lang('Conference', 'Konferenz'),
                type: 'string'
            },
            {
                id: 'location',
                label: lang('Location', 'Ort'),
                type: 'string'
            },
            {
                id: 'country',
                label: lang('Country', 'Land'),
                type: 'string'
            },
            {
                id: 'rendered.depts',
                label: lang('Department (abbr.)', 'Abteilung (Kürzel)'),
                type: 'string'
            },
            {
                id: 'open_access',
                label: lang('Open Access'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'oa_status',
                label: lang('Open Access Status'),
                type: 'string',
                values: ['gold', 'green', 'bronze', 'hybrid', 'open', 'closed'],
                input: 'select'
            },
            {
                id: 'epub',
                label: lang('Online ahead of print'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'correction',
                label: lang('Correction'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'invited_lecture',
                label: lang('Invited lecture'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'created_by',
                label: lang('Created by (Abbreviation)', 'Erstellt von (Kürzel)'),
                type: 'string'
            },
            {
                id: 'created',
                label: lang('Created at', 'Erstellt am'),
                type: 'datetime',
                input: 'date'
            },
            {
                id: 'updated_by',
                label: lang('Updated by (Abbreviation)', 'Aktualisiert von (Kürzel)'),
                type: 'string'
            },
            {
                id: 'imported',
                label: lang('Imported at', 'Importiert am'),
                type: 'datetime',
                input: 'date'
            },

            <?php
            foreach ($osiris->adminFields->find() as $field) {
                $f = [
                    'id' => $field['id'],
                    'label' => lang($field['name'], $field['name_de'] ?? null),
                    'type' => $field['format'] == 'int' ? 'integer' : $field['format']
                ];

                if ($field['format'] == 'boolean') {
                    $f['values'] =  [
                        'true' => 'yes',
                        'false' => 'no'
                    ];
                    $f['input'] = 'radio';
                }

                if ($field['format'] == 'list') {
                    $f['type'] = 'string';
                    $f['values'] =  $field['values'];
                    $f['input'] = 'select';
                }

                echo json_encode($f);
                echo ',';
            }
            ?>
        ];
        var mongoQuery = $('#builder').queryBuilder({
            filters: filters,
            'lang_code': lang('en', 'de'),
            'icons': {
                add_group: 'ph ph-plus-circle text-success',
                add_rule: 'ph ph-plus text-success',
                remove_group: 'ph ph-x-circle text-danger',
                remove_rule: 'ph ph-x text-danger',
                error: 'ph ph-warning text-danger',
            },
            allow_empty: true,
            default_filter: 'type'
        });

        var dataTable;

        filters.forEach(el => {
            console.log(el);
            if (el.type == 'string') {
                $('#aggregate').append(`<option value="${el.id}">${el.label}</option>`)
            }
        });

        function getResult() {
            var rules = $('#builder').queryBuilder('getMongo')
            RULES = rules
            dataTable.ajax.reload()
        }

        // function rulify(rules){
        //     // check if there are dot notated queries with more than one element
        //     // if so, convert to elemMatch
        //     for (const key in rules) {
        //         if (Object.prototype.hasOwnProperty.call(rules, key)) {
        //             const el = rules[key];
        //             // recursive
        //             if (typeof el === 'object') {
        //                 rules[key] = rulify(el)
        //             } else {
        //                 if (el.includes('.')) {
        //                     // check if there are more than one element
        //                     const parts = el.split('.')
        //                     if (parts.length > 1) {
        //                         // convert to elemMatch
        //                         const field = parts.shift()
        //                         const value = parts.join('.')
        //                         rules[key] = {
        //                             $elemMatch: {
        //                                 [field]: value
        //                             }
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }
        //     return rules
        // }


        $(document).ready(function() {
            var hash = window.location.hash.substr(1);
            if (hash !== undefined && hash != "") {
                try {
                    var rules = JSON.parse(decodeURI(hash))
                    RULES = rules;
                    $('#builder').queryBuilder('setRulesFromMongo', rules);
                } catch (SyntaxError) {
                    console.info('invalid hash')
                }
            }

            // on hash change
            // window.onhashchange = function() {
            //     var hash = window.location.hash.substr(1);
            //     if (hash !== undefined && hash != "") {
            //         try {
            //             var rules = JSON.parse(decodeURI(hash))
            //             RULES = rules;
            //             $('#builder').queryBuilder('setRulesFromMongo', rules);
            //         } catch (SyntaxError) {
            //             console.info('invalid hash')
            //         }
            //     }
            //     // remove aggregation
            //     $('#aggregate').val('')
            //     // run
            //     getResult()
            // }

            dataTable = $('#activity-table').DataTable({
                ajax: {
                    "url": ROOTPATH + '/api/activities',
                    data: function(d) {
                        // https://medium.com/code-kings/datatables-js-how-to-update-your-data-object-for-ajax-json-data-retrieval-c1ac832d7aa5
                        var rules = RULES
                        if (rules === null) rules = []
                        // console.log(rules);

                        // TODO: check if there are potential elemMatch queries
                        // rules = rulify(rules)

                        rules = JSON.stringify(rules)


                        $('#result').html(rules)
                        d.json = rules
                        d.formatted = true

                        var aggregate = $('#aggregate').val()
                        if (aggregate !== "") {
                            d.aggregate = aggregate
                        }

                        window.location.hash = rules
                    },
                },
                buttons: [{
                        extend: 'copyHtml5',
                        exportOptions: {
                            columns: [4]
                        },
                        className: 'btn small'
                    },
                    {
                        extend: 'excelHtml5',
                        exportOptions: {
                            columns: [3, 4, 5, 6, 7, 8]
                        },
                        className: 'btn small',
                        title: "OSIRIS Search"
                    },
                    {
                        extend: 'csvHtml5',
                        exportOptions: {
                            // columns: ':visible'
                            columns: [3, 4, 5, 6, 7, 8]
                        },
                        className: 'btn small',
                        title: "OSIRIS Search"
                    }
                ],
                dom: 'fBrtip',
                language: {
                    "zeroRecords": lang("No matching records found", 'Keine passenden Aktivitäten gefunden'),
                    "emptyTable": lang('No activities found for your filters.', 'Für diese Filter konnten keine Aktivitäten gefunden werden.'),
                },
                deferRender: true,
                responsive: true,
                // "pageLength": 5,
                columnDefs: [{
                        targets: 0,
                        data: 'icon',
                        defaultContent: ''
                    },
                    {
                        targets: 1,
                        data: 'activity',
                        defaultContent: ''
                    },
                    {
                        targets: 2,
                        data: 'count',
                        defaultContent: '-'
                    },
                    {
                        "targets": 9,
                        "data": "id",
                        "render": function(data, type, full, meta) {
                            if ($('#aggregate').val()) {
                                return ''
                                // const field = $('#aggregate').val()
                                // on click add filter to query builder
                                // return `<a onclick="$('#builder').queryBuilder('addRule', {id: '${field}', operator: 'equal', value: '${full.activity}'})"><i class="ph ph-magnifying-glass-plus"></a>`;
                            } else {
                                return `<a href="${ROOTPATH}/activities/view/${data}"><i class="ph ph-arrow-fat-line-right"></a>`;
                            }
                        },
                        sortable: false,
                        className: 'unbreakable',
                        defaultContent: ''
                    },
                    {
                        targets: 3,
                        data: 'year',
                        searchable: true,
                        visible: false,
                        defaultContent: ''
                    },
                    {
                        targets: 4,
                        data: 'print',
                        searchable: true,
                        visible: false,
                        defaultContent: ''
                    },
                    {
                        targets: 5,
                        data: 'type',
                        searchable: true,
                        visible: false,
                        defaultContent: ''
                    },
                    {
                        targets: 6,
                        data: 'subtype',
                        searchable: true,
                        visible: false,
                        defaultContent: ''
                    },
                    {
                        targets: 7,
                        data: 'title',
                        searchable: true,
                        visible: false,
                        defaultContent: ''
                    },
                    {
                        targets: 8,
                        data: 'authors',
                        searchable: true,
                        visible: false,
                        defaultContent: ''
                    },
                ]
            });

            // getResult()
        });

        function saveQuery() {
            if (EXPERT) {
                var rules =  $('#expert').val()
                rules = JSON.parse(rules)
            } else {
                var rules = $('#builder').queryBuilder('getRules')
            }
            var name = $('#query-name').val()
            if (name == "") {
                toastError('Please provide a name for your query.')
                return
            }
            var query = {
                name: name,
                rules: rules,
                user: '<?= $_SESSION['username'] ?>',
                created: new Date(),
                aggregate: $('#aggregate').val(),
                expert: EXPERT
            }
            $.post(ROOTPATH + '/crud/queries', query, function(data) {
                // reload
                queries[data.id] = JSON.stringify(rules)

                $('#saved-queries').append(`<a class="d-block" onclick="applyFilter(${data.id}, '${$('#aggregate').val()}')">${name}</a>`)
                $('#query-name').val('')
                toastSuccess('Query saved successfully.')

            })
        }

        function applyFilter(id, aggregate) {
            console.log((id));
            if (EXPERT) {
                applyFilterExpert(id, aggregate)
                return
            }
            var filter = queries[id];
            if (!filter) {
                toastError('Query not found.')
                return
            }
            $('#aggregate').val(aggregate)
            var parsedFilter = JSON.parse(filter, (key, value) => {
                if (typeof value === 'string' && /^\d+$/.test(value)) {
                    return parseInt(value);
                } else if (value === 'true') {
                    return true;
                } else if (value === 'false') {
                    return false;
                }
                return value;
            });
            $('#builder').queryBuilder('setRules', parsedFilter);
            var rules = $('#builder').queryBuilder('getMongo')
            RULES = rules
            dataTable.ajax.reload()
        }
        function applyFilterExpert(id, aggregate) {
            console.log((id));
            var filter = queries[id];
            if (!filter) {
                toastError('Query not found.')
                return
            }
            $('#aggregate').val(aggregate)
            $('#expert').val(filter)
           
            run();
        }


        function deleteQuery(id) {
            $.ajax({
                url: ROOTPATH + '/crud/queries',
                type: 'POST',
                data: {
                    id: id,
                    type: 'DELETE'
                },
                success: function(result) {
                    delete queries[id]
                    $('#query-' + id).remove()
                    toastSuccess('Query deleted successfully.')
                }
            });
        }
    </script>

</div>