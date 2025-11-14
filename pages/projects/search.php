<?php

/**
 * Page to perform advanced project search
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
$expert = isset($_GET['expert']);
?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/query-builder.default.min.css">
<script src="<?= ROOTPATH ?>/js/query-builder.standalone.js?v=date"></script>

<script>
    var RULES;
    var EXPERT = <?= $expert ? 'true' : 'false' ?>;
</script>


<div class="container">
    <a href="https://wiki.osiris-app.de/users/activities/advanced-search/" class="btn tour float-sm-right" target="_blank"><i class="ph ph-question"></i> <?= lang('Manual', 'Anleitung') ?></a>
    <h1>
        <i class="ph-duotone ph-magnifying-glass-plus"></i>
        <?= lang('Advanced project search', 'Erweiterte Projekt-Suche') ?>
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
                    $queries = $osiris->queries->find(['user' => $_SESSION['username'], 'type' => 'project', 'expert' => $expert])->toArray();
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

    <table class="table" id="project-table">
        <thead>
            <th><?= lang('Type', 'Typ') ?></th>
            <th><?= lang('Result', 'Ergebnis') ?></th>
            <th><?= lang('Count', 'Anzahl') ?></th>
            <th>Link</th>
        </thead>
        <tbody>
        </tbody>
    </table>


    <script>
        const filters = [{
                id: 'abstract',
                label: lang('Abstract', 'Abstract'),
                type: 'string'
            },
            {
                id: 'collaborators.country',
                label: lang('Collaborators (Country)', 'Kooperationspartner (Land)'),
                type: 'string'
            },
            {
                id: 'collaborators.location',
                label: lang('Collaborators (Location)', 'Kooperationspartner (Ort)'),
                type: 'string'
            },
            {
                id: 'collaborators.name',
                label: lang('Collaborators (Name)', 'Kooperationspartner (Name)'),
                type: 'string'
            },
            {
                id: 'collaborators.role',
                label: lang('Collaborators (Role)', 'Kooperationspartner (Rolle)'),
                type: 'string',
                values: ['gold', 'green', 'bronze', 'hybrid', 'open', 'closed'],
                input: 'select'
            },
            {
                id: 'collaborators.ror',
                label: lang('Collaborators (ROR)', 'Kooperationspartner (ROR)'),
                type: 'string'
            },
            {
                id: 'collaborators.type',
                label: lang('Collaborators (Type)', 'Kooperationspartner (Typ)'),
                type: 'string',
                values: ['Education', 'Healthcare', 'Company', 'Archive', 'Nonprofit', 'Government', 'Facility', 'Other'],
                input: 'select'
            },
            {
                id: 'contact',
                label: lang('Contact', 'Kontakt'),
                type: 'string'
            },
            {
                id: 'coordinator',
                label: lang('Coordinator', 'Koordinator'),
                type: 'string'
            },
            {
                id: 'created_by',
                label: lang('Created by', 'Erstellt von'),
                type: 'string'
            },
            {
                id: 'created',
                label: lang('Created', 'Erstellt'),
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
                id: 'funder',
                label: lang('Funder', 'Förderer'),
                type: 'string',
                values: ['DFG', 'Bund', 'Bundesländer', 'Wirtschaft', 'EU', 'Stiftungen', 'Leibniz Wettbewerb', 'Sonstige Drittmittelgeber'],
                input: 'select'
            },
            {
                id: 'funding_number',
                label: lang('Funding number', 'Förderkennzeichen'),
                type: 'string'
            },
            {
                id: 'funding_organization',
                label: lang('Funding organization', 'Förderorganisation'),
                type: 'string'
            },
            {
                id: 'grant_income',
                label: lang('Grant income', 'Förderbetrag'),
                type: 'integer'
            },
            {
                id: 'grant_sum',
                label: lang('Grant sum', 'Förderbeträge'),
                type: 'integer'
            },
            {
                id: 'month',
                label: lang('Month', 'Monat'),
                type: 'integer'
            },
            {
                id: 'name',
                label: lang('Name', 'Name'),
                type: 'string'
            },
            {
                id: 'persons.name',
                label: lang('Persons (Name)', 'Personen (Name)'),
                type: 'string'
            },
            {
                id: 'persons.role',
                label: lang('Persons (Role)', 'Personen (Rolle)'),
                type: 'string',
                values: ['PI', 'applicant', 'worker', 'scholar', 'supervisor', 'coordinator', 'associate'],
                input: 'select'
            },
            {
                id: 'persons.user',
                label: lang('Persons (User)', 'Personen (Benutzer)'),
                type: 'string'
            },
            {
                id: 'public',
                label: lang('Public', 'Öffentlich'),
                type: 'boolean',
                values: {
                    'true': 'yes',
                    'false': 'no'
                },
                input: 'radio'
            },
            {
                id: 'purpose',
                label: lang('Purpose', 'Zweck'),
                type: 'string'
            },
            {
                id: 'role',
                label: lang('Role', 'Rolle'),
                type: 'string'
            },
            {
                id: 'start_date',
                label: lang('Start date', 'Startdatum'),
                type: 'datetime',
                input: 'date'
            },
            {
                id: 'status',
                label: lang('Status', 'Status'),
                type: 'string',
                values: ['applied', 'approved', 'rejected', 'finished'],
                input: 'select'
            },
            {
                id: 'title',
                label: lang('Title', 'Titel'),
                type: 'string'
            },
            {
                id: 'type',
                label: lang('Type', 'Typ'),
                type: 'string',
                values: ['Drittmittel', 'Stipendium', 'Eigenfinanziert', 'Teilprojekt', 'other'],
                input: 'select'
            },
            {
                id: 'updated_by',
                label: lang('Updated by', 'Aktualisiert von'),
                type: 'string'
            },
            {
                id: 'updated',
                label: lang('Updated', 'Aktualisiert'),
                type: 'datetime',
                input: 'date'
            },
            {
                id: 'website',
                label: lang('Website', 'Webseite'),
                type: 'string'
            },
            {
                id: 'year',
                label: lang('Year', 'Jahr'),
                type: 'integer'
            },


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

        // filters.forEach(el => {
        //     console.log(el);
        //     if (el.type == 'string') {
        //         $('#aggregate').append(`<option value="${el.id}">${el.label}</option>`)
        //     }
        // });

        function getResult() {
            var rules = $('#builder').queryBuilder('getMongo')
            RULES = rules
            dataTable.ajax.reload()
        }


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

            dataTable = $('#project-table').DataTable({
                ajax: {
                    "url": ROOTPATH + '/api/projects',
                    data: function(d) {
                        // https://medium.com/code-kings/datatables-js-how-to-update-your-data-object-for-ajax-json-data-retrieval-c1ac832d7aa5
                        var rules = RULES
                        if (rules === null) rules = []
                        // console.log(rules);

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
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: [3, 4, 5, 6, 7, 8]
                    },
                    className: 'btn small',
                    title: "OSIRIS Search"
                }, ],
                dom: 'fBrtip',
                language: {
                    "zeroRecords": lang("No matching records found", 'Keine passenden Projekte gefunden'),
                    "emptyTable": lang('No activities found for your filters.', 'Für diese Filter konnten keine Projekte gefunden werden.'),
                },
                deferRender: true,
                responsive: true,
                // "pageLength": 5,
                columnDefs: [{
                        targets: 0,
                        data: 'name',
                        defaultContent: ''
                    },
                    {
                        targets: 1,
                        data: 'title',
                        defaultContent: ''
                    },
                    {
                        targets: 2,
                        data: 'count',
                        defaultContent: '-'
                    },
                    {
                        "targets": 3,
                        "data": "id",
                        "render": function(data, type, full, meta) {
                            if ($('#aggregate').val()) {
                                return ''
                                // const field = $('#aggregate').val()
                                // on click add filter to query builder
                                // return `<a onclick="$('#builder').queryBuilder('addRule', {id: '${field}', operator: 'equal', value: '${full.project}'})"><i class="ph ph-magnifying-glass-plus"></a>`;
                            } else {
                                return `<a href="${ROOTPATH}/projects/view/${data}"><i class="ph ph-arrow-fat-line-right"></a>`;
                            }
                        },
                        sortable: false,
                        className: 'unbreakable',
                        defaultContent: ''
                    },
                ]
            });

            // getResult()
        });

        function saveQuery() {
            if (EXPERT) {
                var rules = $('#expert').val()
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
                expert: EXPERT,
                type: 'project'
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