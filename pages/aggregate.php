

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

?>

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
                        if (empty($rules['rules'])) {
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
                                <a class="btn primary" onclick="applyFilter('<?= $query['_id'] ?>')"><?= lang('Apply filter', 'Filter anwenden') ?></a>

                                <table class="table simple my-10">

                                    <?php if ($query['user'] != $_SESSION['username']) { ?>
                                        <tr>
                                            <th><?= lang('Shared by', 'Geteilt von') ?>:</th>
                                            <td><?= $DB->getNameFromId($query['user']) ?></td>
                                        </tr>
                                    <?php } ?>

                                    <tr>
                                        <th style="vertical-align: baseline;"><?= lang('Rules', 'Regeln') ?>:</th>
                                        <td>
                                            <?= dump($rules) ?>
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
                    queries['<?= $query['_id'] ?>'] = '<?= $query['rules'] ?>';
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
            <textarea name="pipeline" id="pipeline" cols="30" rows="10" class="form-control"></textarea>
            <br>
        </div>


        <div class="footer">
            <div class="btn-toolbar">
                <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>
            </div>
            <a href="#saved-queries-modal" class="btn" role="button">
                <i class="ph ph-floppy-disk"></i> <?= lang('Saved queries', 'Gespeicherte Abfragen') ?>
            </a>
        </div>
    </div>


    <div class="box">
        <div class="content" id="data-statistics">
            -
        </div>
        <div class="footer">
            <div class="btn-toolbar">
                <button class="btn secondary" onclick="$('#data-raw').toggle()"><i class="ph ph-eye"></i> <?= lang('Show raw data', 'Rohdaten anzeigen') ?></button>
                <button class="btn secondary" onclick="downloadData()"><i class="ph ph-download"></i> <?= lang('Download data', 'Herunterladen') ?></button>
            </div>
        </div>
        <div class="content" id="data-raw" style="display: none;">
            -
        </div>
    </div>

    <script>
        // Funktion zum Darstellen der Ergebnisse in der data-box
        function initializeData(data) {
            const dataStatistics = document.getElementById('data-statistics');
            dataStatistics.innerHTML = '<p><?= lang('Results', 'Ergebnisse') ?>: ' + data.length + '</p>';

            const dataRaw = document.getElementById('data-raw');
            dataRaw.innerHTML = '';
            if (data.length === 0) {
                dataRaw.innerHTML = '<p><?= lang('No results found', 'Keine Ergebnisse gefunden') ?></p>';
                return;
            }
            cleanData = data.map(item => {
                const cleanedItem = {};
                for (const key in item) {
                    if (key !== 'rendered' && key !== 'history') { // Exclude the _id, rendered, and history fields
                        cleanedItem[key] = item[key];
                    }
                }
                return cleanedItem;
            });
            dataRaw.innerHTML = '<pre>' + JSON.stringify(cleanData, null, 2) + '</pre>';

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
            var dataRaw = document.getElementById('data-raw');
            if (dataRaw.innerHTML.trim() === '') {
                toastError(lang('No data to download', 'Keine Daten zum Herunterladen'));
                return;
            }
            var blob = new Blob([dataRaw.innerText], { type: 'application/json' });
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
            var rules = queries[id]
            if (typeof rules === 'string' && rules.length >= 2 && rules.startsWith('"') && rules.endsWith('"')) {
                rules = rules.slice(1, -1)
            }
            $('#pipeline').val(rules)
            toastSuccess(lang('Pipeline applied successfully.', 'Abfrage erfolgreich angewendet.'))
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