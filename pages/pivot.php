<?php

/**
 * Pivot tables and charts
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$mode = $_GET['mode'] ?? 'standard';
?>

<h1>
    <?= lang('Pivot tables and charts', 'Pivot-Tabellen und Diagramme') ?>
    <small class="badge danger float-right"><i class="ph ph-warning"></i> BETA</small>
</h1>

<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/pivottables-osiris.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js" integrity="sha512-XgJh9jgd6gAHu9PcRBBAp0Hda8Tg87zi09Q2639t0tQpFFQhGpeCgaiEFji36Ozijjx9agZxB0w53edOFGCQ0g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdn.plot.ly/plotly-basic-latest.min.js" charset="utf-8"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/plotly_renderers.min.js"></script> -->
<script src="<?= ROOTPATH ?>/js/plotly_renderers.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.de.min.js" integrity="sha512-/5FfiXxtbetIwiZLE5XG2SxaeF/KG2wgyhWpRnyDLAY0NsnerIqn7ZF0zXiE6Pp6Ov0dc4V4T4ZCquSVRkhT0w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->


<div class="pills">
    <a href="?mode=standard" class="btn <?= $mode == 'standard' ? 'active' : '' ?>">
        <?= lang('Standard', 'Standard') ?>
    </a>
    <a href="?mode=expanded" class="btn <?= $mode == 'expanded' ? 'active' : '' ?>">
        <?= lang('Expanded', 'Erweitert') ?>
    </a>
</div>
<?php if ($mode == 'standard') { ?>
    <p>
        <i class="ph ph-warning text-secondary"></i>
        In der Standardansicht lassen sich Einheiten und Positionen nicht getrennt aggregieren. Um Einheiten und Positionen getrennt zu zählen, nutze bitte die erweiterte Ansicht, die allerdings mit Duplikaten arbeitet.
    </p>
<?php } else { ?>
    <p>
        <i class="ph ph-warning text-secondary"></i>
        In der erweiterten Variante werden Listen wie zum Beispiel die Positionen und Einheiten getrennt. Dabei kommt es zu Duplikaten. Die einfache "Count"-Funktion liefert daher unter Umständen falsche Ergebnisse, da Aktivitäten mit mehrere Einheiten oder Positionen mehrfach gezählt werden. Bitte nutze die "Count Unique Values"-Funktion in Kombination mit "id", um korrekte Ergebnisse zu erhalten.
        Dadurch wird jede Aktivität nur einmal gezählt.
    </p>
<?php } ?>
<div id="pivot-container"></div>

<?php if ($mode == 'expanded') { ?>
    <script>
        $(document).ready(function() {
            $.ajax({
                url: ROOTPATH + '/api/pivot-data',
                type: "GET",
                dataType: "json",
                success: function(data) {
                    var renderers = $.extend({}, $.pivotUtilities.renderers, $.pivotUtilities.plotly_renderers);

                    let expandedData = [];

                    data.data.forEach(record => {
                        let units = record.units || ["None"];
                        let positions = record.affiliated_positions || ["None"];

                        units.forEach(unit => {
                            positions.forEach(position => {
                                expandedData.push({
                                    ...record,
                                    "Unit": unit, // Jedes Element einzeln
                                    "Affiliated Position": position
                                });
                            });
                        });
                    });

                    $("#pivot-container").pivotUI(expandedData, {
                        rows: ["Unit"], // Hier ist BID jetzt einzeln aggregierbar
                        cols: ["type"],
                        vals: ["id"], // Anzahl der Publikationen
                        aggregatorName: "Count Unique Values",
                        rendererName: "Heatmap",
                        renderers: renderers,
                        rendererOptions: {
                            heatmap: {
                                colorScaleGenerator: function(values) {
                                    return Plotly.d3.scale.linear()
                                        .domain([0, Math.max(...values)])
                                        .range(["#FFFFFF", "<?= $Settings->get('colors')['secondary'] ?? '#f78104' ?>"]);
                                }
                            }
                        },
                        hiddenAttributes: ["units", "affiliated_positions"],
                        unusedAttrsVertical: false,
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Fehler beim Laden der Daten:", error);
                }
            });
        });
    </script>

<?php } else { ?>
    <script>
        $(document).ready(function() {
            $('.loader').show();
            $.ajax({
                url: ROOTPATH + '/api/pivot-data', // Deine API, die Daten aus MongoDB liefert
                type: "GET",
                dataType: "json",
                success: function(data) {
                    var derivers = $.pivotUtilities.derivers;
                    var renderers = $.extend({}, $.pivotUtilities.renderers, $.pivotUtilities.plotly_renderers);

                    // let plotlyOptions = {
                    //     responsive: true
                    // };

                    // // Renderer-Funktion modifizieren
                    // Object.keys($.pivotUtilities.plotly_renderers).forEach(function(key) {
                    //     console.log(key);
                    //     let originalRenderer = $.pivotUtilities.plotly_renderers[key];
                    //     renderers[key] = function(pivotData, opts) {
                    //         opts = opts || {};
                    //         console.log(opts);
                    //         opts.plotlyConfig = plotlyOptions; // Hier die responsive-Einstellung setzen
                    //         return originalRenderer(pivotData, opts);
                    //     };
                    // });




                    $("#pivot-container").pivotUI(data.data, {
                        rows: ["year"], // Standardmäßige Zeilen
                        cols: ["type"], // Standardmäßige Spalten
                        aggregatorName: "Count",
                        vals: ["id"], // Werte, die aggregiert werden
                        rendererName: "Heatmap",
                        renderers: renderers,
                        rendererOptions: {
                            heatmap: {
                                colorScaleGenerator: function(values) {
                                    return Plotly.d3.scale.linear()
                                        .domain([0, Math.max(...values)])
                                        .range(["#FFFFFF", "<?= $Settings->get('colors')['secondary'] ?? '#f78104' ?>"]);
                                }
                            }
                        },
                        // menuLimit: 9999
                        unusedAttrsVertical: false,

                    }, false, lang('en', 'de'));
                    $('.loader').hide();
                },
                error: function(xhr, status, error) {
                    console.error("Fehler beim Laden der Daten:", error);
                    $('.loader').hide();
                }
            });
        });
    </script>
<?php } ?>