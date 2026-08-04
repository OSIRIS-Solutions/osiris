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
            <textarea name="pipeline" id="pipeline" cols="30" rows="10" class="form-control"></textarea>
            <br>
        </div>


        <div class="footer">
            <div class="btn-toolbar">
                <button class="btn secondary" onclick="getResult()"><i class="ph ph-magnifying-glass"></i> <?= lang('Apply', 'Anwenden') ?></button>
            </div>
            
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
    </script>

</div>