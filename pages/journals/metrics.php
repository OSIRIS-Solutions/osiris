<?php

/**
 * Page to see a journal
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /journal/view/<journal_id>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
?>

<h1>
    <i class="ph ph-ranking"></i>
    <?= lang('Journal metrics', 'Zeitschriftmetriken') ?>
</h1>

<p>
    <?= lang('This will check the metrics for all journals. This may take a while.', 'Dies wird die Metriken für alle Journale prüfen. Dies kann eine Weile dauern.') ?>
</p>

<?php
// check if the user has permission to edit journals
if (!$Settings->hasPermission('journals.edit')) {
    echo "<p class='alert alert-danger'>" . lang('You do not have permission to edit journals.', 'Sie haben keine Berechtigung, Journale zu bearbeiten.') . "</p>";
    die;
}
// check the latest year for which metrics are available in the OSIRIS API
$url = "https://osiris-app.de/api/v1";
$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Accept: application/json',
]);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
$result = json_decode($result, true);
$api_year = $result['latest_year'] ?? 'unknown';

$year = $_GET['year'] ?? $api_year;
?>
<p>
    <?= lang('Metrics are available until:', 'Metriken sind verfügbar bis:') ?>
    <strong class="highlight"><?= $api_year ?></strong>
</p>

<form action="#" method="get">
    <div class="form-group">
        <label for="year"><?= lang('Select year', 'Jahr auswählen') ?></label>
        <select name="year" id="year" class="form-control">
            <?php
            for ($i = $api_year; $i > 2000; $i--) {
                if ($i == $year) {
                    echo "<option value='$i' selected>$i</option>";
                    continue;
                }
                echo "<option value='$i'>$i</option>";
            }
            ?>
        </select>
    </div>
    <button type="submit" class="btn primary">
        <i class="ph ph-binoculars"></i>
        <?= lang('Check metrics', 'Metriken prüfen') ?>
</button>
</form>

<?php

if (isset($_GET['year'])) {

    $count = $osiris->journals->count([
        'metrics.year' => ['$ne' => intval($year)],
        'no_metrics' => ['$ne' => true]
    ]);
    if ($count == 0) {
        echo "<p class='alert signal mt-20'>" . lang('All journals from this year are up to date.', 'Alle Journale aus diesem Jahr sind bereits aktuell.') . "</p>";
    } else {
?>


        <div class="box padded">
            <p class="mt-5">
                <?= lang('We found', 'Wir haben') ?>
                <strong class="highlight" id="total"><?= $count ?></strong>
                <?= lang('journals that do not have metrics for this year. ', 'Journale gefunden, die noch keine Metriken für dieses Jahr haben. ') ?>
            </p>

            <button id="startBtn" onclick="startProcess()" class="btn primary">
                <i class="ph ph-cloud-arrow-down"></i>
                Metriken abrufen
            </button>
            <p id="status">Noch nicht gestartet</p>
            <progress id="progressBar" value="0" max="100" class="progessbar"></progress>
        </div>
        <script>
            function startProcess() {
                let year = $("#year").val();
                let total = $("#total").text();
                if (total == 0) {
                    $("#status").text("<?= lang('No journals found', 'Keine Journale gefunden') ?>");
                    return;
                }
                $("#startBtn").attr("disabled", true);
                $("#startBtn").text("<?= lang('Processing', 'Verarbeite') ?>...").addClass('loading');
                $.post(ROOTPATH + "/journal/metrics/update/" + year, function() {
                    const interval = setInterval(function() {
                        $.getJSON(ROOTPATH + "/journal/metrics/progress/" + year, function(data) {
                            let value = Math.round(((total - data.value) / total) * 100);
                            $("#status").text(data.message);
                            $("#progressBar").val(value);
                            if (data.done) {
                                clearInterval(interval);
                                // $("#startBtn").attr("disabled", false);
                                $("#startBtn").text("<?= lang('Done', 'Fertig') ?>").removeClass('loading');
                                $("#status").text("<?= lang('Done', 'Fertig') ?>");
                                $("#progressBar").val(100);
                            }
                        });
                    }, 1000);
                })
                .fail(function(err) {
                    $("#status").html("<b class='text-danger'><?= lang('Error:', 'Fehler:') ?></b> " + err.statusText);
                    $("#startBtn").attr("disabled", false).removeClass('loading');
                    $("#startBtn").text("<?= lang('Start', 'Starten') ?>");
                });
            };
        </script>
<?php
    }
}
?>