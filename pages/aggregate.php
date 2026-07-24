<?php

$Format = new Document(true);
$expert = isset($_GET['expert']);
$expertQuery = $_GET['expert'] ?? '';

$collection = $_GET['collection'] ?? 'activities';


if ($collection == 'projects' || $collection == 'proposals') {
    include_once BASEPATH . "/php/project_fields.php";
    $FIELDS = new ProjectFields($collection);
    $defaultColumns = ['type', 'name', 'title'];
    $expertQuery = '{"funder": "EU"}';
} elseif ($collection == 'conferences') {
    include_once BASEPATH . "/php/event_fields.php";
    $FIELDS = new EventFields();
    $defaultColumns = ['id', 'title', 'start', 'location'];
    $defaultFilter = 'type';
    $expertQuery = '{"type": "international"}';
} elseif ($collection == 'journals') {
    include_once BASEPATH . "/php/journal_fields.php";
    $FIELDS = new JournalFields();
    $defaultColumns = ['id', 'journal', 'issn', 'publisher'];
    $defaultFilter = 'journal';
    $expertQuery = '{}';
} elseif ($collection == 'persons') {
    include_once BASEPATH . "/php/person_fields.php";
    $FIELDS = new PersonFields();
    $defaultColumns = ['id', 'username', 'first', 'last'];
    $defaultFilter = 'is_active';
    $expertQuery = '{"is_active": true}';
} else {
    include_once BASEPATH . "/php/activity_fields.php";
    $FIELDS = new ActivityFields();
    $defaultColumns = ['id', 'icon', 'web', 'year'];
}

$defaultColumns = ['id', 'name', 'title'];
?>



<div class="modal" id="column-select-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('Select columns to display', 'Wähle Spalten zum Anzeigen aus') ?></h5>
            <style>
                .input-group-text {
                    background-color: var(--primary-color-30);
                    color: var(--primary-color);
                    border-color: var(--primary-color);
                }
            </style>
            <div class="input-group mb-20">
                <div class="input-group-prepend">
                    <span class="input-group-text">
                        <i class="ph ph-magnifying-glass"></i>
                    </span>
                </div>
                <input type="search" class="form-control border-primary" id="column-search" placeholder="<?= lang('Search fields...', 'Felder suchen...') ?>" oninput="filterColumns()">
            </div>

            <div id="column-select">
                <?php
                $selected = $_GET['columns'] ?? $defaultColumns;
                $fields = array_filter($FIELDS->fields, function ($f) {
                    return in_array('columns', $f['usage'] ?? []);
                });
                // sort by module_of
                usort($fields, function ($a, $b) use ($selected) {
                    if (in_array($a['id'], $selected)) {
                        return -1;
                    } elseif (in_array($b['id'], $selected)) {
                        return 1;
                    } elseif (in_array('general', $a['module_of'] ?? [])) {
                        return -1;
                    } elseif (in_array('general', $b['module_of'] ?? [])) {
                        return 1;
                    } elseif (count($b['module_of'] ?? []) == count($a['module_of'] ?? [])) {
                        return in_array($a['id'], $selected) ? -1 : 1;
                    }
                    return count($b['module_of'] ?? []) <=> count($a['module_of'] ?? []);
                });

                $icons = [];
                if ($collection == 'activities') {
                    $iconsRaw = $osiris->adminCategories->find([], ['projection' => ['icon' => 1, 'id' => 1, 'color' => 1, 'name' => 1]])->toArray();
                } else {
                    $iconsRaw = $osiris->adminProjects->find([], ['projection' => ['icon' => 1, 'id' => 1, 'color' => 1, 'name' => 1]])->toArray();
                }
                foreach ($iconsRaw as $icon) {
                    $iconClass = 'ph ph-cube';
                    $icons[$icon['id']] = '<span data-toggle="tooltip" data-title="' . ($icon['name'] ?? $icon['id']) . '"><i class="ph ph-' . ($icon['icon'] ?? $iconClass) . '" style="color:' . ($icon['color'] ?? '#6c757d') . '"></i></span>';
                }

                foreach ($fields as $field) {
                    $modules = $field['module_of'] ?? [];
                ?>
                    <div class="custom-checkbox checkbox-badge <?= empty($modules) ? 'text-muted' : '' ?>">
                        <input type="checkbox" class="form-check-input" data-column="<?= $field['id'] ?>" id="column-<?= str_replace('.', '-', $field['id']) ?>" <?= (in_array($field['id'], $selected) ? 'checked' : '') ?>>
                        <label class="form-check-label" for="column-<?= str_replace('.', '-', $field['id']) ?>" value="<?= $field['id'] ?>">
                            <?= $field['label'] ?>
                            <?php if ($field['custom'] ?? false) { ?>
                                <span data-toggle="tooltip" data-title="Custom field">
                                    <i class="ph ph-duotone ph-gear text-muted"></i>
                                </span>
                            <?php } ?>
                            <?php foreach ($modules as $key) {
                                if ($key == 'general') { ?>
                                    <span data-toggle="tooltip" data-title="<?= lang('General field', 'Generelles Datenfeld') ?>">
                                        <i class="ph ph-globe text-muted"></i>
                                    </span>
                            <?php
                                    continue;
                                }
                                echo $icons[$key] ?? '<span data-toggle="tooltip" data-title="' . $key . '"><i class="ph ph-question text-muted"></i></span>';
                            } ?>

                        </label>
                    </div>
                <?php } ?>
            </div>
            <div class="text-right mt-20">
                <a href="#/" class="btn mr-5" role="button"><?= lang('Close', 'Schließen') ?></a>
                <a class="btn secondary" role="button" onclick="getResult()"><?= lang('Apply', 'Anwenden') ?></a>
            </div>
            <script>
                function filterColumns() {
                    var input = document.getElementById("column-search");
                    var filter = input.value.toLowerCase();
                    var div = document.getElementById("column-select");
                    var labels = div.getElementsByTagName("label");

                    for (var i = 0; i < labels.length; i++) {
                        var label = labels[i];
                        var textValue = label.textContent || label.innerText;
                        if (textValue.toLowerCase().indexOf(filter) > -1) {
                            label.parentElement.style.opacity = "1";
                        } else {
                            label.parentElement.style.opacity = "0.3";
                        }
                    }
                }
            </script>
        </div>
    </div>
</div>


<div class="">
    <h1>
        <i class="ph-duotone ph-magnifying-glass-plus"></i>
        <?= lang('Aggregation', 'Aggregation') ?>
    </h1>

    <div class="box">
        <div class="content mb-0">

            <h3 class="title"><?= lang('Choose collection', 'Sammlung auswählen') ?></h3>
            <!-- TODO choose collection -->
             <select id="collection" class="form-control w-auto">
                <option value="activities"><?= lang('Activities', 'Aktivitäten') ?></option>
                <option value="persons"><?= lang('Persons', 'Personen') ?></option>
                <option value="events"><?= lang('Events', 'Veranstaltungen') ?></option>
                <option value="journals"><?= lang('Journals', 'Zeitschriften') ?></option>
            </select>
            <br>
            <h3 class="title"><?= lang('Pipeline', 'Pipeline') ?></h3>
            <textarea name="expert" id="expert" cols="30" rows="5" class="form-control"><?= $expertQuery ?></textarea>

            
        </div>

        <div class="row position-relative">
            <div class="col">
                <div class="content">
                    <a href="#column-select-modal">
                        <i class="ph ph-columns-plus-right"></i>
                        <?= lang('Select Columns', 'Spalten auswählen') ?>
                    </a>
                    <!-- 
                    <div id="selected-columns">
                        <span class="badge">Webdarstellung</span>
                    </div> -->
                </div>
            </div>
            <div class="text-divider"><?= lang('OR', 'ODER') ?></div>
            <div class="col">
                <!-- Aggregations -->
                <div class="content">
                    <a onclick="$('#aggregate-form').slideToggle()">
                        <i class="ph ph-squares-four"></i>
                        <?= lang('Aggregate', 'Aggregieren') ?>
                    </a>

                    <div class="input-group" style="display:none;" id="aggregate-form">
                        <select name="aggregate" id="aggregate" class="form-control w-auto">
                            <option value=""><?= lang('Without aggregation (show all)', 'Ohne Aggregation (zeige alles)') ?></option>
                            <?php
                            $aggregate_filter = array_filter($FIELDS->fields, function ($f) {
                                return in_array('aggregate', $f['usage'] ?? []);
                            });
                            foreach ($aggregate_filter as $f) { ?>
                                <option value="<?= $f['id'] ?>"><?= $f['label'] ?></option>
                            <?php } ?>


                        </select>

                        <!-- remove aggregation -->
                        <div class="input-group-append">
                            <button class="btn text-danger" onclick="$('#aggregate').val(''); getResult()"><i class="ph ph-x"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="footer">

            <div class="btn-toolbar">

                <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>

                <a href="#saved-queries-modal" class="btn" role="button">
                    <i class="ph ph-floppy-disk"></i> <?= lang('Saved queries', 'Gespeicherte Abfragen') ?>
                </a>

                <a href="#filter-code" class="btn" role="button">
                    <i class="ph ph-code"></i> <?= lang('Show examples', 'Zeige Beispiele') ?>
                </a>

            </div>
        </div>
    </div>


    <table class="table" id="activity-table">
        <thead></thead>
        <tbody></tbody>
    </table>

    <script>
        // var mongo = $('#builder').queryBuilder('getMongo');

        var filters = <?= json_encode($filters) ?>;

        // clean up if filters is an object
        if (filters.constructor === Object) {
            filters = Object.values(filters)
        }

        // get fields
        var fields = <?= json_encode($fields) ?>;

        // clean up if fields is an object
        if (fields.constructor === Object) {
            fields = Object.values(fields)
        }

        function escapeRegex(s) {
            return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }


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
            default_filter: '<?= $defaultFilter ?>',
            operators: [
                'equal', 'not_equal', 'in', 'not_in', 'less', 'less_or_equal', 'greater', 'greater_or_equal',
                'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains',
                'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null',
                {
                    type: 'exists',
                    nb_inputs: 0,
                    apply_to: ['string', 'number', 'datetime', 'boolean']
                },
                {
                    type: 'not_exists',
                    nb_inputs: 0,
                    apply_to: ['string', 'number', 'datetime', 'boolean']
                },
                {
                    type: 'contains_i',
                    nb_inputs: 1,
                    apply_to: ['string']
                }
            ],
            lang: {
                operators: {
                    exists: lang('exists', 'existiert'),
                    not_exists: lang('not exists', 'existiert nicht'),
                    contains_i: lang('contains (ignore case)', 'enthält (Groß-/Kleinschreibung ignorieren)')
                }
            },
            mongoOperators: {
                exists: function() {
                    return {
                        $exists: true
                    };
                },
                not_exists: function() {
                    return {
                        $exists: false
                    };
                },
                contains_i: function(v) {
                    return {
                        $regex: escapeRegex(v),
                        $options: 'i'
                    };
                }
            }
        });

        var dataTable;

        function initializeTable(data) {
            // destroy existing table
            if ($.fn.DataTable.isDataTable('#activity-table')) {
                $('#activity-table').DataTable().clear().destroy();
                $('#activity-table thead').empty(); // Header leeren
                $('#activity-table tbody').empty(); // Daten leeren
            }

            // Extrahiere die Spaltennamen aus der API-Antwort
            const first_row = Object.keys(data[0]);

            // check for aggregation
            var aggregate = $('#aggregate').val()

            // Generiere dynamisch das thead
            const thead = document.querySelector('#activity-table thead');
            const headerRow = document.createElement('tr');

            var columns = [];

            if (aggregate !== "") {
                data = data.map(row => ({
                    value: row.value ?? '<em>' + lang('empty', 'leer') + '</em>',
                    count: row.count || 0
                }));

                // add aggregate column
                var th = document.createElement('th');
                th.textContent = lang('Activity', 'Aktivität');
                headerRow.appendChild(th);
                th = document.createElement('th');
                th.textContent = lang('Count', 'Anzahl');
                headerRow.appendChild(th);
                thead.appendChild(headerRow);

                columns = [{
                        data: 'value',
                        title: lang('Value', 'Wert')
                    },
                    {
                        data: 'count',
                        title: lang('Count', 'Anzahl')
                    }
                ]

            } else {
                var selected_columns = []
                var array_columns = {}
                $('#column-select input:checked').each(function() {
                    var id = $(this).data('column')
                    selected_columns.push(id)
                    var name = $(this).next('label').text()
                    const th = document.createElement('th');
                    th.textContent = name; // Optional: Titel formatieren
                    headerRow.appendChild(th);
                })
                thead.appendChild(headerRow);
                // Konfiguriere die Spalten für Datatables
                columns = selected_columns.map(function(field) {
                    // remove from selected columns
                    selected_columns = selected_columns.filter(column => column !== field);
                    // get name from `fields`
                    const filter = fields.find(f => f.id == field);
                    var r = {
                        data: field,
                        title: filter ? filter.label : field,
                        defaultContent: '-'
                    }
                    if (field == 'id') {
                        r.render = function(data, type, row, meta) {
                            return `<a href="<?= ROOTPATH ?>/<?= $collection ?>/view/${data}"><i class="ph ph-arrow-fat-line-right"></i></a>`
                        }
                    } else if (field == 'username') {
                        r.render = function(data, type, row, meta) {
                            return data ? `<a href="<?= ROOTPATH ?>/profile/${data}">${data}</a>` : '-';
                        }
                    } else if (array_columns[field]) {
                        var array_column = array_columns[field]
                        r.render = function(data, type, row, meta) {
                            if (Array.isArray(data)) {
                                data = data.map(item => item[array_column]).join(', ');
                            }
                            if (data === undefined || data === null) {
                                return '-'
                            }
                            if (Array.isArray(data[array_column])) {
                                return data[array_column].join(', ') ?? data;
                            }
                            return data[array_column] ?? data;
                        }
                    }
                    return r
                });
                if (selected_columns.length > 0) {
                    toastWarning(lang('The following columns are not found in the result and are not shown:', 'Die folgenden Spalten waren im Ergebnis komplett leer und werden nicht gezeigt:') + ' <strong>' + selected_columns.join(', ') + '</strong>');
                }

            }

            // Initialisiere Datatables
            $('#activity-table').DataTable({
                destroy: true, // Alte Tabelle entfernen, falls sie existiert
                data: data, // Daten direkt übergeben
                columns: columns, // Dynamisch generierte Spalten
                dom: 'fBrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: ':visible,:hidden' // Include hidden columns
                    },
                    className: 'btn small',
                    title: "OSIRIS Search",
                    text: '<i class="ph ph-file-xls"></i> Excel'
                }],
                initComplete: function() {
                    var tableWidth = $('#activity-table').width();
                    var containerWidth = $('#activity-table').parent().width();
                    if (tableWidth > containerWidth && !$('#activity-table').parent().hasClass('table-responsive')) {
                        $('#activity-table').wrap('<div class="table-responsive"></div>');
                    }
                }
            });

            // check if table exceeds the width of the container
        }

        // AJAX-Call zum Abrufen der Daten
        function getResult() {
            if (EXPERT) {
                var rules = $('#expert').val()
                if (rules == '') {
                    return
                }
                try {
                    var rules = JSON.parse(rules)
                } catch (SyntaxError) {
                    toastError(lang('Invalid JSON', 'Ungültiges JSON'))
                    return
                }
            } else {
                var rules = $('#builder').queryBuilder('getMongo')
            }
            if (rules === null) rules = []
            rules = JSON.stringify(rules)

            var data = {
                json: rules,
                formatted: true
            }

            var aggregate = $('#aggregate').val()
            if (aggregate !== "") {
                data.aggregate = aggregate
            }

            // columns
            var columns = []
            $('#column-select input:checked').each(function() {
                columns.push($(this).data('column'))
            })
            data.columns = columns

            window.location.hash = rules

            $('#result').html(rules)
            $.ajax({
                url: ROOTPATH + '/api/search/<?= $collection ?>',
                method: 'GET',
                data: data,
                success: function(response) {
                    console.log(response);
                    if (response.count > 0) {
                        initializeTable(response.data); // Tabelle initialisieren
                    } else {
                        // $('#activity-table').DataTable().destroy(); // Tabelle entfernen
                        $('#activity-table tbody').html('<tr><td colspan="10" class="text-center">Keine Daten gefunden</td></tr>'); // Keine Daten gefunden

                    }
                },
                error: function(xhr, status, error) {
                    toastError('Fehler beim Abrufen der API:', error);
                }
            });
        }

        $(document).ready(function() {
            <?php if ($preset_query) : ?>
                applyFilter('<?= $preset_query['_id'] ?>', '<?= $preset_query['aggregate'] ?>', '<?= implode(';', DB::doc2Arr($preset_query['columns'] ?? [])) ?>')
                return;
            <?php endif; ?>

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
            getResult()
        });


        function saveQuery() {
            // disable save button
            $('#save-query-button').prop('disabled', true);
            if (EXPERT) {
                var rules = $('#expert').val()
                rules = JSON.parse(rules)
            } else {
                var rules = $('#builder').queryBuilder('getRules')
            }
            var name = $('#query-name').val()
            if (name == "") {
                toastError('Please provide a name for your query.')
                $('#save-query-button').prop('disabled', false);
                return
            }

            var columns = []
            $('#column-select input:checked').each(function() {
                columns.push($(this).data('column'))
            })

            var query = {
                name: name,
                rules: rules,
                user: '<?= $_SESSION['username'] ?>',
                created: new Date(),
                aggregate: $('#aggregate').val(),
                columns: columns,
                expert: EXPERT,
                type: '<?= $collection ?>'
            }

            $.post(ROOTPATH + '/crud/queries', query, function(data) {
                // reload
                queries[data.id] = JSON.stringify(rules)

                $('#saved-queries').append(`<a class="d-block" onclick="applyFilter(${data.id}, '${$('#aggregate').val()}')">${name}</a>`)
                $('#query-name').val('')
                toastSuccess(lang('Query saved successfully. Please reload the page to see it completely.', 'Abfrage erfolgreich gespeichert. Lade die Seite neu, um sie vollständig anzuzeigen.'))
                $('#save-query-button').prop('disabled', false);
            })
        }

        function applyFilter(id, aggregate, columns) {
            console.log(columns);
            if (EXPERT) {
                applyFilterExpert(id, aggregate, columns)
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
            if (!columns) {
                columns = "<?= implode(';', $defaultColumns) ?>";
            }
            $('#column-select input').prop('checked', false)
            columns.split(';').forEach(column => {
                $('#column-' + column.replace('.', '-')).prop('checked', true)
            })


            $('#builder').queryBuilder('setRules', parsedFilter);
            // var rules = $('#builder').queryBuilder('getMongo')
            getResult()
        }

        function applyFilterExpert(id, aggregate) {
            var filter = queries[id];
            if (!filter) {
                toastError('Query not found.')
                return
            }
            $('#aggregate').val(aggregate)
            $('#expert').val(filter)

            getResult()
        }


        function deleteQuery(id) {
            $.ajax({
                url: ROOTPATH + '/crud/queries',
                type: 'POST',
                data: {
                    id: id,
                    action: 'DELETE'
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