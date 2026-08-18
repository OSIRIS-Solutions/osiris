

<?php

/**
 * Page to perform advanced aggregations
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activities/search
 *
 * @package OSIRIS
 * @since 2.1 
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @author		Julius Witte <julius.witte@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document(true);

$collections = [
    "activities" => lang('Activities', 'Aktivitäten'),
    "conferences" => lang('Conferences', 'Konferenzen'),
    "countries" => lang('Countries', 'Länder'),
    "events" => lang('Events', 'Veranstaltungen'),
    "groups" => lang('Groups', 'Gruppen'),
    "infrastructures" =>  lang('Infrastructures', 'Infrastrukturen'),
    "journals" => lang('Journals', 'Zeitschriften'),
    "organizations" => lang('Organizations', 'Organisationen'),
    "persons" => lang('Persons', 'Personen'),
    "projects" => lang('Projects', 'Projekte'),
    "proposals" => lang('Proposals', 'Vorschläge')
];

?>

<link rel="stylesheet" href="<?= ROOTPATH ?>/css/usertable.css?v=<?= OSIRIS_BUILD ?>">

<!-- MODALS -->

<div class="modal" id="saved-queries-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h2 class="title">
                <?= lang('Saved pipelines', 'Gespeicherte Abfragen') ?>
            </h2>


            <div class="mb-20">
                <button class="btn" aria-expanded="true" onclick="$(this).next().slideToggle();">
                    <i class="ph ph-floppy-disk"></i> <?= lang('Save current pipeline', 'Aktuelle Abfrage speichern') ?>
                </button>

                <div style="display:none;" class="box padded mt-10">
                    <input type="text" class="form-control" id="pipeline-name" placeholder="<?= lang('Name of pipeline', 'Name der Abfrage') ?>">
                    <button class="btn primary mt-10" onclick="savePipeline()"><?= lang('Save pipeline', 'Abfrage speichern') ?></button>
                </div>
            </div>

            <?php
            $filter = [
                '$or' => [
                    ['user' => $_SESSION['username']],
                    ['global' => true],
                    ['role' => ['$in' => $Settings->roles]]
                ],
                'type' => "aggregation"
            ];
            $queries = $osiris->queries->find($filter)->toArray();
            if (empty($queries)) {
                echo '<p>' . lang('You have not saved any pipelines yet.', 'Du hast noch keine Abfragen gespeichert.') . '</p>';
            } else {
                // sort by created by current user first, then by created date
                usort($queries, function ($a, $b) {
                    if ($a['user'] == $_SESSION['username'] && $b['user'] != $_SESSION['username']) {
                        return -1;
                    } elseif ($a['user'] != $_SESSION['username'] && $b['user'] == $_SESSION['username']) {
                        return 1;
                    } else {
                        return strtotime($b['created']) <=> strtotime($a['created']);
                    }
                });
            ?>

                <input type="search" class="form-control mb-10" id="pipeline-search" placeholder="<?= lang('Search saved pipelines...', 'Gespeicherte Abfragen suchen...') ?>" oninput="$('#saved-queries details').each(function() {
                    var summary = $(this).find('summary').text().toLowerCase();
                    var filter = $('#pipeline-search').val().toLowerCase();
                    if (summary.indexOf(filter) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });">
                <div class="collapse-group" id="saved-queries">
                    <?php foreach ($queries as $query) {
                        $rules = json_decode($query['rules'], true);
                        $collection = $query['collection'];
                        if (empty($rules)) {
                            $rules = ['rules' => [['id' => 'No rules']]];
                        }
                        $query_id = strval($query['_id']);
                    ?>
                        <details id="pipeline-<?= $query_id ?>" class="mb-10">
                            <summary class="collapse-header font-weight-bold d-flex justify-content-between align-items-center">
                                <?= $query['name'] ?>
                                <?php if ($query['global'] ?? false) { ?>
                                    <span class="badge badge-info"><i class="ph ph-globe"></i> <?= lang('Global', 'Global') ?></span>
                                <?php } elseif (isset($query['role'])) { ?>
                                    <span class="badge badge-secondary"><i class="ph ph-shield-checkered"></i> <?= lang('Role:', 'Rolle:') ?> <?= ucfirst($query['role']) ?></span>
                                <?php } ?>
                            </summary>
                            <div class="collapse-content">
                                <div class="dropdown float-right">
                                    <button class="btn" data-toggle="dropdown" type="button" id="dropdown-pipeline-<?= $query_id ?>" aria-haspopup="true" aria-expanded="false">
                                        <i class="ph ph-share-network"></i> <?= lang('Share', 'Teilen') ?>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-pipeline-<?= $query_id ?>">
                                        <!-- share globally -->
                                        <div class="content">
                                            <!-- copy Link with ID to clipboard -->
                                            <button class="btn block mb-5" onclick="sharePipeline('<?= $query['_id'] ?>', 'global')">
                                                <i class="ph ph-globe"></i> <?= lang('Share globally', 'Global teilen') ?>
                                            </button>
                                            <hr>
                                            <select class="form-control mb-5" id="role-select-<?= $query_id ?>">
                                                <?php foreach ($Settings->getRoles() as $role) { ?>
                                                    <option value="<?= $role ?>"><?= ucfirst($role) ?></option>
                                                <?php } ?>
                                            </select>
                                            <button class="btn block" onclick="sharePipeline('<?= $query_id ?>', 'role')">
                                                <i class="ph ph-shield-checkered"></i> <?= lang('Share with role', 'Mit Rolle teilen') ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function sharePipeline(id, type) {
                                        var data = {
                                            id: id,
                                            action: 'SHARE'
                                        };
                                        if (type == 'global') {
                                            data.global = true;
                                        } else if (type == 'role') {
                                            var role = $('#role-select-' + id).val();
                                            data.role = role;
                                        }
                                        $.post(ROOTPATH + '/crud/queries', data, function(response) {
                                            toastSuccess('<?= lang('Pipeline shared successfully.', 'Pipeline erfolgreich geteilt.') ?>');
                                        });
                                    }
                                </script>
                                <a class="btn primary" onclick="applyFilter('<?= $query['_id'] ?>')"><?= lang('Apply pipeline', 'Pipeline anwenden') ?></a>

                                <table class="table simple my-10">

                                    <?php if ($query['user'] != $_SESSION['username']) { ?>
                                        <tr>
                                            <th><?= lang('Shared by', 'Geteilt von') ?>:</th>
                                            <td><?= $DB->getNameFromId($query['user']) ?></td>
                                        </tr>
                                    <?php } ?>

                                    <tr>
                                        <th style="vertical-align: baseline;"><?= lang('Collection', 'Sammlung') ?>:</th>
                                        <td>
                                            <?= $collections[$collection] ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="vertical-align: baseline;"><?= lang('Pipeline', 'Pipeline') ?>:</th>
                                        <td>
                                            <?= var_export($rules) ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th><?= lang('Created', 'Erstellt') ?>:</th>
                                        <td><?= date('d.m.Y H:i', strtotime($query['created'])) ?></td>
                                    </tr>
                                </table>

                                <?php if ($query['user'] != $_SESSION['username']) { ?>
                                    <small class="text-muted"><?= lang('Only the creator of the pipeline can delete or modify it.', 'Nur der Ersteller der Abfrage kann sie löschen oder bearbeiten.') ?></small>
                                <?php } else { ?>
                                    <a class="btn danger small text-right" onclick="deletePipeline('<?= $query['_id'] ?>')"><i class="ph ph-trash"></i> <?= lang('Delete Pipeline', 'Abfrage löschen') ?></a>
                                <?php } ?>
                            </div>
                        </details>
                    <?php } ?>
                </div>
            <?php  } ?>

            <script>
                var queries = {};
                <?php foreach ($queries as $query) { ?>
                    queries['<?= $query['_id'] ?>'] = {'rules': <?=$query['rules'] ?>, 'collection': '<?= $query['collection'] ?>'};
                <?php } ?>
            </script>

            <div class="text-right mt-20">
                <a href="#/" class="btn mr-5" role="button"><?= lang('Close', 'Schließen') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="example-pipelines-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h2 class="title">
                <?= lang('Example pipelines', 'Beispiel Pipelines') ?>
            </h2>
                <?php
                $exampleQueries = json_decode(file_get_contents(BASEPATH . '/data/aggregation-pipelines.json'), true);
                if (empty($exampleQueries)) {
                    echo '<p>' . lang('No example pipelines available.', 'Keine Beispiel Pipelines verfügbar.') . '</p>';
                } else {
                    // sort by name
                    usort($exampleQueries, function ($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                ?>
                <input type="search" class="form-control mb-10" id="example-pipeline-search" placeholder="<?= lang('Search example pipelines...', 'Beispiel Pipeline suchen...') ?>" oninput="$('#example-pipelines-modal details').each(function() {
                    var summary = $(this).find('summary').text().toLowerCase();
                    var filter = $('#example-pipeline-search').val().toLowerCase();
                    if (summary.indexOf(filter) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });">
                <div class="collapse-group" id="example-pipelines-list">
                    <?php foreach ($exampleQueries as $query) {
                        $rules = $query['rules'];
                        $collection = $query['collection'];
                        if (empty($rules)) {
                            $rules = ['rules' => [['id' => 'No rules']]];
                        };
                    ?>
                        <details id="pipeline-<?= $query['name'] ?>" class="mb-10">
                            <summary class="collapse-header font-weight-bold d-flex justify-content-between align-items-center">
                                <?= $query['name'] ?>
                            </summary>
                            <div class="collapse-content">

                                <a class="btn primary" onclick="applyExamplePipeline('<?= $query['name'] ?>')"><?= lang('Apply pipeline', 'Pipeline anwenden') ?></a>

                                <table class="table simple my-10">
                                    <tr>
                                        <th style="vertical-align: baseline;"><?= lang('Collection', 'Sammlung') ?>:</th>
                                        <td>
                                            <?= $collections[$collection] ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="vertical-align: baseline;"><?= lang('Pipeline', 'Pipeline') ?>:</th>
                                        <td>
                                            <?= var_export(json_encode($rules)) ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </details>
                    <?php } ?>
                </div>
            <?php  } ?>

            <script>
                var exampleQueries = {}
                <?php foreach ($exampleQueries as $query) { ?>
                    exampleQueries['<?= $query['name'] ?>'] = {'rules': <?= json_encode($query['rules'])?>, 'collection': '<?= $query['collection'] ?>'};
                <?php } ?>
            </script>

            <div class="text-right mt-20">
                <a href="#/" class="btn mr-5" role="button"><?= lang('Close', 'Schließen') ?></a>
            </div>
        </div>
    </div>
</div>

<!-- MAIN -->

<div class="">
    <h1>
        <i class="ph-duotone ph-magnifying-glass-plus"></i>
        <?= lang('Aggregation', 'Aggregation') ?>
    </h1>

    <div class="box">
        <div class="content mb-0">
            
            <h3 class="title"><?= lang('Choose collection', 'Sammlung auswählen') ?></h3>
             <select id="collection" class="form-control w-auto">
                <option value="activities"><?= $collections["activities"] ?></option>
                <option value="conferences"><?= $collections["conferences"] ?></option>
                <option value="countries"><?= $collections["countries"] ?></option>
                <option value="events"><?= $collections["events"] ?></option>
                <option value="groups"><?= $collections["groups"] ?></option>
                <option value="infrastructures"><?= $collections["infrastructures"] ?></option>
                <option value="journals"><?= $collections["journals"] ?></option>
                <option value="organizations"><?= $collections["organizations"] ?></option>          
                <option value="persons"><?= $collections["persons"] ?></option>
                <option value="projects"><?= $collections["projects"] ?></option>
                <option value="proposals"><?= $collections["proposals"] ?></option>
            </select>
            <br>
            <h3 class="title"><?= lang('Pipeline', 'Pipeline') ?></h3>
            <textarea name="pipeline" id="pipeline" cols="30" rows="10" class="form-control"></textarea>
            <br>
        </div>


        <div class="footer">
            <div class="btn-toolbar">
                <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>
                <a href="#saved-queries-modal" class="btn" role="button">
                    <i class="ph ph-floppy-disk"></i> <?= lang('Saved queries', 'Gespeicherte Abfragen') ?>
                </a>
                <a href="#example-pipelines-modal" class="btn" role="button">
                    <i class="ph ph-code"></i> <?= lang('Show examples', 'Zeige Beispiele') ?>
                </a>
            </div>
            
        </div>
    </div>

    <table class="table full-cards" id="data-table" style="display: none;">
        <thead>
            <th>Results</th>
        </thead>
        <tbody></tbody>
    </table>

    <script>
        var latestAggregateResults = [];

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function buildRowsTable(rows) {
            if (!rows.length) {
                return '<p>-</p>';
            }

            var html = '<table class="table simple"><tbody>';
            rows.forEach(function(row) {
                html += '<tr>' +
                    '<th style="width: 35%; vertical-align: top;">' + escapeHtml(row.key) + '</th>' +
                    '<td style="vertical-align: top; white-space: pre-wrap;">' + (row.isHtml ? row.value : escapeHtml(row.value)) + '</td>' +
                    '</tr>';
            });
            html += '</tbody></table>';
            return html;
        }

        function buildNestedDetails(title, tableHtml) {
            return '<details class="nested-table-details">' +
                '<summary>' + escapeHtml(title) + '</summary>' +
                '<div>' + tableHtml + '</div>' +
                '</details>';
        }

        function formatSimpleValue(value) {
            return value === null || value === undefined ? '-' : String(value);
        }

        function isObjectValue(value) {
            return value !== null && typeof value === 'object';
        }

        function isEmptyObject(value) {
            return isObjectValue(value) && !Array.isArray(value) && Object.keys(value).length === 0;
        }

        function buildValueRow(key, value) {
            if (isObjectValue(value)) {
                return {
                    key: key,
                    value: buildNestedDetails(lang('Show nested table', 'Untertabelle anzeigen'), buildNestedTable(value)),
                    isHtml: true
                };
            }

            return {
                key: key,
                value: formatSimpleValue(value)
            };
        }

        function wrapTopLevelView(content, index) {
            var label = lang('Result', 'Ergebnis') + ' #' + (index + 1);
            return '<div class="w-full" style="width: 100%;">' +
                '<span class="text-muted">' + escapeHtml(label) + '</span>' +
                content +
                '</div>';
        }

        function toggleExpandedRows(containerId, trigger) {
            var container = document.getElementById(containerId);
            if (!container) {
                return false;
            }
            previewId = 'preview-' + containerId;
            var previewContainer = document.getElementById(previewId);

            var isHidden = container.style.display === 'none' || container.style.display === '';
            if (isHidden) {
                container.style.display = 'block';
                previewContainer.style.display = 'none';
                trigger.textContent = trigger.getAttribute('data-less-label') || lang('Show less', 'Weniger anzeigen');
            } else {
                container.style.display = 'none';
                previewContainer.style.display = 'block';
                trigger.textContent = trigger.getAttribute('data-more-label') || lang('Show more', 'Mehr anzeigen');
            }

            return false;
        }

        function buildNestedTable(value) {
            if (!isObjectValue(value)) {
                return formatSimpleValue(value);
            }

            if (Array.isArray(value)) {
                if (value.length === 0) {
                    return '-';
                }

                var arrayRows = value.map(function(item, index) {
                    return buildValueRow('[' + index + ']', item);
                });

                return buildRowsTable(arrayRows);
            }

            var keys = Object.keys(value);
            if (keys.length === 0) {
                return '-';
            }

            var objectRows = keys.map(function(key) {
                return buildValueRow(key, value[key]);
            });

            return buildRowsTable(objectRows);
        }

        function getTopLevelRows(value) {
            if (value === null || value === undefined) {
                return [{ key: '-', value: '-' }];
            }

            if (!isObjectValue(value)) {
                return [{ key: '-', value: String(value) }];
            }

            var keys = Object.keys(value);
            if (keys.length === 0) {
                return [{ key: '-', value: '-' }];
            }

            return keys.map(function(key) {
                var current = value[key];
                if (!isObjectValue(current)) {
                    return {
                        key: key,
                        value: formatSimpleValue(current)
                    };
                }

                if (isEmptyObject(current)) {
                    return {
                        key: key,
                        value: '-'
                    };
                }

                var childTable = buildNestedTable(current);
                return {
                    key: key,
                    value: buildNestedDetails(lang('Show nested table', 'Untertabelle anzeigen') + ': ' + Object.keys(current).length, childTable),
                    isHtml: true
                };
            });
        }

        function buildDataViews(item, index) {
            // Format data for display, including nested tables
            var allRows = getTopLevelRows(item);
            // Slicing for preview and expandable content
            var numberOfPreviewRows = 4;
            var previewRows = allRows.slice(0, numberOfPreviewRows);
            var isExpandable = allRows.length > numberOfPreviewRows;
            var previewHtml = buildRowsTable(previewRows);

            if (!isExpandable) {
                return wrapTopLevelView(previewHtml, index);
            }

            var detailsId = 'json-remaining-' + index;
            var summaryText = lang('Show more', 'Mehr anzeigen') + ' (' + (allRows.length  - numberOfPreviewRows) + ')';
            var lessText = lang('Show less', 'Weniger anzeigen');
            var content = 
                '<a href="#/" class="btn small text-primary float-md-right" data-more-label="' + escapeHtml(summaryText) + '" data-less-label="' + escapeHtml(lessText) + '" onclick="return toggleExpandedRows(\'' + detailsId + '\', this);">' + escapeHtml(summaryText) + '</a>' +
                '<div id="preview-' + detailsId + '">' + previewHtml + '</div>' +
                '<div id="' + detailsId + '" style="display: none;">' + buildRowsTable(allRows) + '</div>';

            return wrapTopLevelView(content, index);
        }

        // Funktion zum Darstellen der Ergebnisse in der data-box
        function initializeData(data) {
            if ($.fn.DataTable.isDataTable('#data-table')) {
                $('#data-table').DataTable().clear().destroy();
                $('#data-table thead').empty();
                $('#data-table tbody').empty();
            }

            // remove unnecessary keys from the data
            const cleanData = data.map(item => {
                const cleanedItem = {};
                for (const key in item) {
                    // remove rendered and history keys from the item
                    if (key !== 'rendered' && key !== 'history') {
                        cleanedItem[key] = item[key];
                    }
                }
                return cleanedItem;
            });

            latestAggregateResults = cleanData;
            
            // just one column with the results
            const columns = [{
                        data: 'results',
                        title: lang('Results', 'Ergebnisse')
                    }
                ];
            
            // prepare data for DataTable
            data = cleanData.map((item, index) => {
                return {
                    results: buildDataViews(item, index)
                };
            });

            $('#data-table').DataTable({
                destroy: true,
                data: data,
                columns: columns,
                dom: 'fBrtip',
                columnDefs: [{
                    targets: 0,
                    orderable: false
                }],
                stripeClasses: [],
                buttons: [{
                    extend: 'excelHtml5',
                    exportOptions: {
                        columns: ':visible,:hidden'
                    },
                    className: 'btn small',
                    title: "OSIRIS Search",
                    text: '<i class="ph ph-file-xls"></i> Excel'
                },
                {
                    text: '<i class="ph ph-brackets-curly"></i> <?= lang('Download Raw Data', 'Rohdaten herunterladen') ?>',
                    className: 'btn small',
                    action: function(e, dt, node, config) {
                        downloadData();
                    }
                }
                ],
                createdRow: function(row) {
                    $('td', row).css('vertical-align', 'top');
                },
                initComplete: function() {
                    $('#data-table thead').hide();
                    var tableWidth = $('#data-table').width();
                    var containerWidth = $('#data-table').parent().width();
                    if (tableWidth > containerWidth && !$('#data-table').parent().hasClass('table-responsive')) {
                        $('#data-table').wrap('<div class="table-responsive"></div>');
                    }
                }
            });

            $('#data-table').show();
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
                    return response.json().then(errData => {
                        throw new Error(errData.error || 'Error');
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log(data);
                initializeData(data.results);
            })
            .catch(err => {
                console.log(err);
            });
        }

        function downloadData() {
            if (!Array.isArray(latestAggregateResults) || latestAggregateResults.length === 0) {
                toastError(lang('No data to download', 'Keine Daten zum Herunterladen'));
                return;
            }
            var exportData = JSON.stringify(latestAggregateResults, null, 2);
            var blob = new Blob([exportData], { type: 'application/json' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'data.json';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

         function savePipeline() {
            // disable save button
            $('#save-pipeline-button').prop('disabled', true);

            var rules = $('#pipeline').val()

            var name = $('#pipeline-name').val()
            if (name == "") {
                toastError('Please provide a name for your pipeline.')
                $('#save-pipeline-button').prop('disabled', false);
                return
            }

            var pipeline = {
                name: name,
                rules: rules,
                user: '<?= $_SESSION['username'] ?>',
                created: new Date(),
                type: 'aggregation',
                collection: $('#collection').val(),
            }

            $.post(ROOTPATH + '/crud/queries', pipeline, function(data) {
                // reload
                queries[data.id] = JSON.stringify(rules)

                $('#saved-pipeline').append(`<a class="d-block" onclick="applyFilter(${data.id}, '${$('#pipeline').val()}')">${name}</a>`)
                $('#pipeline-name').val('')
                toastSuccess(lang('Pipeline saved successfully. Please reload the page to see it completely.', 'Pipeline erfolgreich gespeichert. Lade die Seite neu, um sie vollständig anzuzeigen.'))
                $('#save-pipeline-button').prop('disabled', false);
            })
        }

        function applyFilter(id) {
            var rules = queries[id].rules
            var collection = queries[id].collection
            if (typeof rules === 'string' && rules.length >= 2 && rules.startsWith('"') && rules.endsWith('"')) {
                rules = rules.slice(1, -1)
            }
            $('#collection').val(collection)
            $('#pipeline').val(rules)
            window.location.href = "#close-modal"
            toastSuccess(lang('Pipeline applied successfully.', 'Abfrage erfolgreich angewendet.'))
            getResult()
        }

        function applyExamplePipeline(name) {
            var rules = JSON.stringify(exampleQueries[name]['rules'])
            var collection = exampleQueries[name]['collection']
            $('#collection').val(collection)
            if (typeof rules === 'string' && rules.length >= 2 && rules.startsWith('"') && rules.endsWith('"')) {
                rules = rules.slice(1, -1)
            }
            $('#pipeline').val(rules)
            window.location.href = "#close-modal"
            toastSuccess(lang('Example pipeline applied successfully.', 'Beispiel Pipeline erfolgreich angewendet.'))
            getResult()
        }

        function deletePipeline(id) { 
            $.ajax({
                url: ROOTPATH + '/crud/queries',
                type: 'POST',
                data: {
                    id: id,
                    action: 'DELETE'
                },
                success: function(result) {
                    delete queries[id]
                    $('#pipeline-' + id).remove()
                    toastSuccess('Pipeline deleted successfully.')
                }
            });
        }
    </script>

</div>