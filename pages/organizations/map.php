<?php

/**
 * Page to visualize organizations on a map
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /organizations/map
 *
 * @package OSIRIS
 * @since 2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$institute = $Settings->get('affiliation_details');
$lat = $institute['lat'] ?? 52;
$lng = $institute['lng'] ?? 10;
if (empty($lat) || empty($lng)) {
    $lat = 52;
    $lng = 10;
}
?>

<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>

<style>
    #map {
        height: calc(100vh - 26rem);
        min-height: 400px;
    }
</style>

<script>
    var layout = {
        mapbox: {
            style: "open-street-map",
            center: {
                lat: <?= ($lat) ?>,
                lon: <?= ($lng) ?>
            },
            zoom: 1
        },

        margin: {
            r: 0,
            t: 0,
            b: 0,
            l: 0
        },
        hoverinfo: 'text',
        autosize: true
    };
</script>


<h1>
    <i class="ph-duotone ph-map-pin"></i>
    <?= lang('Organization map', 'Organisations-Karte') ?>
</h1>

<div id="map" class=""></div>
<script>
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/organizations",
        dataType: "json",
        success: function(response) {
            console.log(response);
            var data = {
                type: 'scattermapbox',
                mode: 'markers',
                hoverinfo: 'text',
                lon: [],
                lat: [],
                text: [],
                marker: {
                    size: [],
                    color: []
                }
            }

            response.data.forEach(item => {
                data.marker.size.push(10)
                data.marker.color.push(item.color ?? 'rgba(0, 128, 131, 0.7)')
                data.lon.push(item.lng)
                data.lat.push(item.lat)
                data.text.push(`<b>${item.name}</b><br>${item.location}`)

            });
            console.log(data);

            Plotly.newPlot('map', [data], layout);
        },
        error: function(response) {
            console.log(response);
        }
    });
</script>