<?php

$Format = new Document(true);

?>

<div class="">
    <h1>
        <i class="ph-duotone ph-magnifying-glass-plus"></i>
        <?= lang('Aggregation', 'Aggregation') ?>
    </h1>

    <div class="box">
        <div class="content mb-0">

            <h3 class="title"><?= lang('Choose collection', 'Sammlung auswählen') ?></h3>
             <select id="collection" class="form-control w-auto">
                <option value="activities"><?= lang('Activities', 'Aktivitäten') ?></option>
                <option value="conferences"><?= lang('Conferences', 'Konferenzen') ?></option>
                <option value="countries"><?= lang('Countries', 'Länder') ?></option>
                <option value="events"><?= lang('Events', 'Veranstaltungen') ?></option>
                <option value="groups"><?= lang('Groups', 'Gruppen') ?></option>
                <option value="infrastructures"><?= lang('Infrastructures', 'Infrastrukturen') ?></option>
                <option value="journals"><?= lang('Journals', 'Zeitschriften') ?></option>
                <option value="organizations"><?= lang('Organizations', 'Organisationen') ?></option>          
                <option value="persons"><?= lang('Persons', 'Personen') ?></option>
                <option value="projects"><?= lang('Projects', 'Projekte') ?></option>
                <option value="proposals"><?= lang('Proposals', 'Vorschläge') ?></option>
            </select>
            <br>
            <h3 class="title"><?= lang('Pipeline', 'Pipeline') ?></h3>
            <textarea name="pipeline" id="pipeline" cols="30" rows="5" class="form-control"></textarea>
            <br>
        </div>


        <div class="footer">
            <div class="btn-toolbar">
                <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>
            </div>
        </div>
    </div>


    <table class="table" id="data-table">
        <thead></thead>
        <tbody></tbody>
    </table>

    <script>
        var dataTable;

        function initializeTable(data) {
            // destroy existing table
            if ($.fn.DataTable.isDataTable('#data-table')) {
                $('#data-table').DataTable().clear().destroy();
                $('#data-table thead').empty(); // Header leeren
                $('#data-table tbody').empty(); // Daten leeren
            }

            // Extrahiere die Spaltennamen aus der API-Antwort
            const first_row = Object.keys(data[0]);


            // Generiere dynamisch das thead
            const thead = document.querySelector('#data-table thead');
            const headerRow = document.createElement('tr');

            var columns = [];
        
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

        

            // Initialisiere Datatables
            $('#data-table').DataTable({
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
                    var tableWidth = $('#data-table').width();
                    var containerWidth = $('#data-table').parent().width();
                    if (tableWidth > containerWidth && !$('#data-table').parent().hasClass('table-responsive')) {
                        $('#data-table').wrap('<div class="table-responsive"></div>');
                    }
                }
            });

            // check if table exceeds the width of the container
        }

        // AJAX-Call zum Abrufen der Daten
        function getResult() {
            var pipeline = $('#pipeline').val()
            if (pipeline == '') {
                return
            }
            try {
                var pipeline = JSON.parse(pipeline)
            } catch (SyntaxError) {
                toastError(lang('Invalid JSON', 'Ungültiges JSON'))
                return
            }


            fetch(ROOTPATH + '/api/aggregate', {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    collection: $('#collection').val(),
                    pipeline: pipeline
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                initializeTable(data.results);
            })
            .catch(err => {
                console.error(err);
            });
        }

    </script>

</div>