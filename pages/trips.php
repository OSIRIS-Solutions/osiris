<?php

/**
 * Page to analyse research trips
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /trips
 *
 * @package OSIRIS
 * @since 1.5.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$user = $user ?? $_SESSION['username'];

// - Wer (Gantt von Teaching), farblich markiert nach status
// - Anzahl der Tage/der Reisen/personen pro Land, filterbar nach status
// - Anzahl Tage pro Topic, filterbar nach status


$year = CURRENTYEAR;
if (isset($_GET['year'])) {
    $year = intval($_GET['year']);
}
$start = $year . "-01-01";
$end   = $year . "-12-31";


// Filter Lehrveranstaltungen im Semester
$filter = [
    'subtype' => 'travel',
    'year' => $year
];

$trips = $osiris->activities->find(
    $filter,
    [
        'projection' => [
            'id' => 1,
            'title' => 1,
            'start_date' => 1,
            'end_date' => 1,
            'status' => 1,
            'countries' => 1,
            'country' => 1,
            'authors' => 1,
            'topics' => 1
        ]
    ]
)->toArray();

$all = $osiris->activities->count(
    [
        'subtype' => 'travel'
    ]
);

$statuses = [
    'preparation' => lang('In Preparation', 'In Vorbereitung'),
    'in-progress' => lang('In Progress', 'Laufend'),
    'completed' => lang('Completed', 'Abgeschlossen'),
    'aborted' => lang('Cancelled', 'Abgebrochen'),
];


$countries = [];
$people = [];
$topics = [];
$timelineData = [];
$mapData = [];

$template = [];
foreach ($statuses as $key => $label) {
    $template[$key] = [
        'days' => 0,
        'people' => 0,
        'trips' => 0
    ];
}

// Unwind countries and authors
foreach ($trips as $trip) {
    // compatible with the country field
    if (!isset($trip['countries']) && isset($trip['country'])) {
        $trip['countries'] = [$trip['country']];
    }

    $status = str_replace(' ', '-', $trip['status']);

    $startDate = new DateTime($trip['start_date']);
    $endDate = new DateTime($trip['end_date']);
    $status = str_replace(' ', '-', $trip['status']);
    // Differenz berechnen
    $duration = $startDate->diff($endDate);
    // Tage holen
    $days = $duration->days + 1; // +1, wenn Start- und Endtag beide zählen sollen

    $authors = DB::doc2Arr($trip['authors']);
    $current_countries = [];
    foreach ($trip['countries'] as $country) {
        // $country = lang($country['name'], $country['name_de'] ?? null);
        $current_countries[] = $country;

        if (!isset($countries[$country])) {
            $countries[$country] = $template;
        }
        $countries[$country][$status]['days'] += $days;
        $countries[$country][$status]['trips'] += 1;
        $countries[$country][$status]['people'] += count($authors);
    }

    if (isset($trip['topics']) && !empty($trip['topics']))
        foreach ($trip['topics'] as $topic) {
            if (!isset($topics[$topic])) $topics[$topic] = $template;
            $topics[$topic][$status]['days'] += $days;
            $topics[$topic][$status]['people'] += count($authors);
            $topics[$topic][$status]['trips'] += 1;
        }

    foreach ($authors as $author) {
        if (empty($author['user'] ?? null)) continue;
        $person = $DB->getNameFromId($author['user']);
        $timelineData[] = [
            'title' => $author['user'],
            'name' => implode(', ', $current_countries),
            'start' => $trip['start_date'],
            'end'   => $trip['end_date'],
            'person' => $person,
            'cat' => $status,
            'days' => $days
        ];
        if (!isset($people[$person])) {
            $people[$person] = $template;
        }
        $people[$person][$status]['days'] += $days;
        $people[$person][$status]['trips'] += 1;
    }
}

// map data sums up the trips per country regardless of the status
foreach ($countries as $iso => $data) {
    $country = $DB->getCountry($iso);
    $mapData[] = [
        'iso3' => $country['iso3'],
        'days' => array_sum(array_column($data, 'days')),
        'people' => array_sum(array_column($data, 'people')),
        'trips' => array_sum(array_column($data, 'trips')),
        'name' => lang($country['name'], $country['name_de'] ?? null),
    ];
}


?>

<style>
    .preparation {
        color: var(--signal-color);
        width: 15rem;
    }

    .in-progress {
        color: var(--osiris-color);
        width: 15rem;
    }

    .completed {
        color: var(--success-color);
        width: 15rem;
    }

    .aborted {
        color: var(--danger-color);
        width: 15rem;
    }

    #legend span {
        color: white;
        padding: 0.25rem 0.8rem;
        border-radius: var(--border-radius);
        font-weight: bold;
    }

    #legend span.preparation {
        background-color: var(--signal-color-60);
    }

    #legend span.in-progress {
        background-color: rgba(247, 129, 4, 0.7);
    }

    #legend span.completed {
        background-color: var(--success-color-60);
    }

    #legend span.aborted {
        background-color: var(--danger-color-60);
    }

    #statistics span.people,
    #statistics span.trips,
    #statistics span.days {
        display: none;
    }

    #statistics.people span.people,
    #statistics.trips span.trips,
    #statistics.days span.days {
        display: inline-block;
    }
</style>

<div class="container">
    <h1><?= lang('Research trips', 'Forschungsreisen') ?></h1>

    <div class="btn-toolbar">
        <a class="btn" href="<?= ROOTPATH ?>/add-activity?type=travel">
            <i class="ph ph-plus-circle"></i>
            <?= lang('Add trip', 'Reise hinzufügen') ?>
        </a>
    </div>


    <!-- UI-Änderung -->
    <div class="alert signal">
        <i class="ph ph-warning text-signal"></i>
        <?= lang('Select a year to see all research trips.', 'Wähle ein Jahr, um alle Forschungsreisen anzuzeigen.') ?>


        <form method="get" class="d-flex align-items-baseline mt-10" style="grid-gap: 1rem;">
            <h6 class="mb-0 mt-5 w-200"><?= lang('Select year', 'Jahr auswählen') ?>:</h6>
            <input type="number" name="year" class="form-control" value="<?= $year ?>" min="2000" max="<?= CURRENTYEAR + 1 ?>" step="1" required>
            <button class="btn signal filled" type="submit"><?= lang('Update', 'Ändern') ?></button>
        </form>
    </div>

    <h3>
        <?= lang('Research trips in', 'Forschungsreisen in') ?> <?= $year ?>
        <span class="badge signal ml-10">
            <?= count($trips) ?>
            <?= lang('trips', 'Reisen') ?>
        </span>
    </h3>

    <div id="gantt-container" style="width: 100%; height: auto;">
        <div id="legend" class="text-center">
            <b><?= lang('Legend', 'Legende') ?>:</b>
            <?php foreach ($statuses as $key => $label) { ?>
                <span class="<?= $key ?>"><?= $label ?></span>
            <?php } ?>
        </div>

        <svg id="gantt-chart" viewBox="0 0 1000 500" preserveAspectRatio="xMinYMin meet" style="width: 100%; height: auto;"></svg>

    </div>

    <div id="statistics" class="days">

        <div class="pills">
            <button class="btn active" onclick="showData(this,'days')"><?= lang('Days', 'Tage') ?></button>
            <button class="btn" onclick="showData(this,'trips')"><?= lang('Trips', 'Reisen') ?></button>
            <button class="btn" onclick="showData(this,'people')"><?= lang('People', 'Personen') ?></button>
        </div>

        <h4><?= lang('Countries', 'Länder') ?></h4>

        <div class="row row-eq-spacing">
            <div class="col-md">
                <?php
                $numbers = [];
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= lang('Country', 'Land') ?></th>
                            <?php foreach ($statuses as $key => $name) {
                                $numbers[$key] = [
                                    'days' => 0,
                                    'people' => 0,
                                    'trips' => 0
                                ];
                                echo "<th>$name</th>";
                            } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($countries as $iso => $days):
                            $country = $DB->getCountry($iso);
                            $country = lang($country['name'], $country['name_de'] ?? null);

                        ?>
                            <tr>
                                <td><?= $country ?></td>
                                <?php foreach ($statuses as $key => $name) {
                                    $numbers[$key]['days'] += $days[$key]['days'] ?? 0;
                                    $numbers[$key]['people'] += $days[$key]['people'] ?? 0;
                                    $numbers[$key]['trips'] += $days[$key]['trips'] ?? 0;
                                    echo "<td class='$key'>";
                                    echo "<span class='days'>" . ($days[$key]['days'] ?? 0) . "</span>";
                                    echo "<span class='people'>" . ($days[$key]['people'] ?? 0) . "</span>";
                                    echo "<span class='trips'>" . ($days[$key]['trips'] ?? 0) . "</span>";
                                    echo "</td>";
                                } ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><?= lang('Sum', 'Summe') ?></td>
                            <?php foreach ($statuses as $key => $name) {
                                echo "<td class='$key'>";
                                echo "<span class='days'>" . ($numbers[$key]['days'] ?? 0) . "</span>";
                                echo "<span class='people'>" . ($numbers[$key]['people'] ?? 0) . "</span>";
                                echo "<span class='trips'>" . ($numbers[$key]['trips'] ?? 0) . "</span>";
                                echo "</td>";
                            } ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md">
                <div class="box p-5 m-0">
                    <div id="map"></div>
                </div>
            </div>
        </div>


        <h4><?= lang('People', 'Personen') ?></h4>
        <?php
        $numbers = [];
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= lang('Person', 'Person') ?></th>
                    <?php foreach ($statuses as $key => $name) {
                        $numbers[$key] = [
                            'days' => 0,
                            'people' => '-',
                            'trips' => 0
                        ];
                        echo "<th>$name</th>";
                    } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($people as $person => $days): ?>
                    <tr>
                        <td><?= $person ?></td>
                        <?php foreach ($statuses as $key => $name) {
                            $numbers[$key]['days'] += $days[$key]['days'] ?? 0;
                            $numbers[$key]['trips'] += $days[$key]['trips'] ?? 0;
                            echo "<td class='$key'>";
                            echo "<span class='days'>" . ($days[$key]['days'] ?? 0) . "</span>";
                            echo "<span class='people'>-</span>";
                            echo "<span class='trips'>" . ($days[$key]['trips'] ?? 0) . "</span>";
                            echo "</td>";
                        } ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><?= lang('Sum', 'Summe') ?></td>
                    <?php foreach ($statuses as $key => $name) {
                        echo "<td class='$key'>";
                        echo "<span class='days'>" . ($numbers[$key]['days'] ?? 0) . "</span>";
                        echo "<span class='people'>-</span>";
                        echo "<span class='trips'>" . ($numbers[$key]['trips'] ?? 0) . "</span>";
                        echo "</td>";
                    } ?>
                </tr>
            </tfoot>
        </table>


        <h4><?= $Settings->topicLabel() ?></h4>
        <?php
        $numbers = [];
        ?>
        <table class="table">
            <thead>
                <tr>
                    <th><?= $Settings->topicLabel() ?></th>
                    <?php foreach ($statuses as $key => $name) {
                        $numbers[$key] = [
                            'days' => 0,
                            'people' => 0,
                            'trips' => 0
                        ];
                        echo "<th>$name</th>";
                    } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topics as $topic => $days):
                    $topic = $osiris->topics->findOne(['id' => $topic]);
                ?>
                    <tr>
                        <td><?= lang($topic['name'], $topic['name_de'] ?? null) ?></td>
                        <?php foreach ($statuses as $key => $name) {
                            $numbers[$key]['days'] += $days[$key]['days'] ?? 0;
                            $numbers[$key]['people'] += $days[$key]['people'] ?? 0;
                            $numbers[$key]['trips'] += $days[$key]['trips'] ?? 0;
                            echo "<td class='$key'>";
                            echo "<span class='days'>" . ($days[$key]['days'] ?? 0) . "</span>";
                            echo "<span class='people'>" . ($days[$key]['people'] ?? 0) . "</span>";
                            echo "<span class='trips'>" . ($days[$key]['trips'] ?? 0) . "</span>";
                            echo "</td>";
                        } ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><?= lang('Sum', 'Summe') ?></td>
                    <?php foreach ($statuses as $key => $name) {
                        echo "<td class='$key'>";
                        echo "<span class='days'>" . ($numbers[$key]['days'] ?? 0) . "</span>";
                        echo "<span class='people'>" . ($numbers[$key]['people'] ?? 0) . "</span>";
                        echo "<span class='trips'>" . ($numbers[$key]['trips'] ?? 0) . "</span>";
                        echo "</td>";
                    } ?>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
    function showData(el, type) {
        // hide all spans
        $('#statistics span').hide();
        // show the selected type
        $('#statistics span.' + type).show();
        // remove active class from all buttons
        $('#statistics .btn').removeClass('active');
        // add active class to the selected button
        $(el).addClass('active');

        updateMap(type);
    }
</script>



<?php
usort($timelineData, function ($a, $b) {
    // sort by person alphabetically
    return strcmp($a['person'], $b['person']);
});

$uniques = array_unique(array_column($timelineData, 'title'));
$unique_number = count($uniques);

?>

<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>

<script>
    // Color scale
    function color(status) {
        switch (status) {
            case 'preparation':
                return 'var(--signal-color)';
            case 'in-progress':
                return 'var(--osiris-color)';
            case 'completed':
                return 'var(--success-color)';
            case 'aborted':
                return 'var(--danger-color)';
            default:
                return 'var(--muted-color)';
        }
    }

    function cat(status) {
        switch (status) {
            case 'preparation':
                return lang('In Preparation', 'In Vorbereitung');
            case 'in-progress':
                return lang('In Progress', 'Laufend');
            case 'completed':
                return lang('Completed', 'Abgeschlossen');
            case 'aborted':
                return lang('Cancelled', 'Abgebrochen');
            default:
                return lang('Unknown', 'Unbekannt');
        }
    }

    const data = <?= json_encode($timelineData) ?>;
    const unique_number = <?= $unique_number ?? 1 ?>;
    const divSelector = '#gantt-container';

    const margin = {
        top: 40,
        right: 10,
        bottom: 40,
        left: 100
    };
    const width = 1000;
    const height = (20 * unique_number) + margin.top + margin.bottom;

    const container = d3.select(divSelector);
    const svg = d3.select('#gantt-chart');

    svg.attr('width', width)
        .attr('height', height)
        .attr('viewBox', `0 0 ${width} ${height}`)

    const innerWidth = width - margin.left - margin.right;
    const innerHeight = height - margin.top - margin.bottom;

    const x = d3.scaleTime()
        .domain([new Date("<?= $year - 1 ?>-12-31"), new Date("<?= $end ?>")])
        .range([margin.left, innerWidth + margin.left]);

    const y = d3.scaleBand()
        .domain(data.map(d => d.title))
        .range([margin.top, innerHeight + margin.top])
        .padding(0.1);

    let x_bandwidth = innerWidth / 11
    // Achsen
    svg.append('g')
        .attr('transform', `translate(0,${innerHeight + margin.top})`)
        .call(
            d3.axisBottom(x)
            .tickFormat(d3.timeFormat("%B")) // Monatsname
        )
        .selectAll("text")
        .attr("text-anchor", "middle")
        .attr("dx", x_bandwidth / 2);

    let idToLabel = {};
    data.forEach(d => {
        idToLabel[d.title] = d.person;
    });

    svg.append('g')
        .attr('transform', `translate(${margin.left},0)`)
        // .call(d3.axisLeft(y));
        .call(d3.axisLeft(y)
            .tickFormat(d => idToLabel[d] || d) // Mapping anwenden
        );

    // Balken
    const rect = svg.selectAll('rect')
        .data(data)
        .enter()
        .append('rect')
        .attr('x', d => x(new Date(d.start)))
        .attr('y', d => y(d.title))
        .attr('rx', 5)
        .attr('ry', 5)
        .attr('width', d => x(new Date(d.end)) - x(new Date(d.start)))
        .attr('height', y.bandwidth())
        .attr('fill', d => color(d.cat))
        .attr('opacity', 0.6)

    // Circles for data point less than 1 week
    const circle = svg.selectAll('circle')
        .data(data.filter(d => (new Date(d.end) - new Date(d.start)) < 7 * 24 * 60 * 60 * 1000))
        .enter()
        .append('circle')
        .attr('cx', d => x(new Date(d.start)) + (x(new Date(d.end)) - x(new Date(d.start))) / 2)
        .attr('cy', d => y(d.title) + y.bandwidth() / 2)
        .attr('r', 5)
        // .attr('fill', d => d.hasAoi ? 'var(--success-color)' : 'var(--osiris-color)')
        .attr('opacity', 0.6)
    // Tooltip

    // mark today with a vertical line
    const today = new Date();
    const todayLine = svg.append('line')
        .attr('x1', x(today))
        .attr('y1', margin.top)
        .attr('x2', x(today))
        .attr('y2', innerHeight + margin.top)
        .attr('stroke', 'var(--danger-color)')
        .attr('stroke-width', 2)
        .attr('stroke-dasharray', '5,5');
    const todayLabel = svg.append('text')
        .attr('x', x(today))
        .attr('y', margin.top - 8)
        .attr("text-anchor", "middle")
        .attr("font-size", "1rem")
        .attr("fill", "var(--danger-color)")
        .text(lang('today', 'heute'))

    function mouseover(d, i) {
        d3.select(this)
            .select('circle,rect')
            .transition()
            .duration(300)
            .style('opacity', 1)

        //Define and show the tooltip over the mouse location
        $(this).popover({
            placement: 'auto top',
            container: divSelector,
            mouseOffset: 10,
            followMouse: true,
            trigger: 'hover',
            html: true,
            content: function() {
                let start = new Date(d.start);
                let end = new Date(d.end);
                return `
                <h5 class="m-0 text-primary">${d.name ?? 'No country available'}</h5>
                <b>${d.person ?? d.title}</b><br>
                <b>${lang('Status', 'Status')}: </b>${cat(d.cat)}<br>
                <b>${lang('Start date', 'Beginn')}: </b>${start.toLocaleDateString()}<br>
                <b>${lang('End date', 'Ende')}: </b>${end.toLocaleDateString()}<br>
                <b>${lang('Duration', 'Dauer')}: </b>${d.days} ${lang('days', 'Tage')}<br>
                `
            }
        });
        $(this).popover('show');
    } //mouseoverChord

    //Bring all chords back to default opacity
    function mouseout(event, d) {
        d3.select(this).select('circle,rect')
            .transition()
            .duration(300)
            .style('opacity', .5)
        //Hide the tooltip
        $('.popover').each(function() {
            $(this).remove();
        });
    }
    circle.on("mouseover", mouseover)
        .on("mouseout", mouseout)
    rect.on("mouseover", mouseover)
        .on("mouseout", mouseout)
</script>


<script src="<?= ROOTPATH ?>/js/plotly-3.0.1.min.js" charset="utf-8"></script>
<script>
    function unpack(rows, key) {
        return rows.map(function(row) {
            return row[key];
        });
    }

    var layout = {
        geo: {
            projection: {
                type: 'robinson'
            }
        },
        margin: {
            t: 50,
            b: 10,
            l: 10,
            r: 10
        },
        // height: 400,
        // width: '100%',
    };
    var collaboratorRows = <?= json_encode($mapData) ?>;
    $(document).ready(function() {
        var z = unpack(collaboratorRows, 'days');
        var data = [{
            type: 'choropleth',
            locationmode: 'ISO-3',
            locations: unpack(collaboratorRows, 'iso3'),
            z: z,
            text: unpack(collaboratorRows, 'country'),
            autocolorscale: false,
            colorscale: [
                ['0.0', 'rgb(253.4, 229.8, 204.8)'],
                ['1.0', '#008084']
            ],
            colorbar: {
                len: 0.5,
                title: lang('Days', 'Tage'),
                autotic: false,
            },
            zmin: 0,
            zmax: Math.max(...z),
        }];
        var config = {
            responsive: true,
            showLink: false
        }

        Plotly.newPlot("map", data, layout, config);
    });



    function updateMap(mode) {
        var z = unpack(collaboratorRows, mode);
        var label = '';
        switch (mode) {
            case 'days':
                label = lang('Days', 'Tage');
                break;
            case 'people':
                label = lang('People', 'Personen');
                break;
            case 'trips':
                label = lang('Trips', 'Reisen');
                break;
            default:
                label = lang('Unknown', 'Unbekannt');
        }
        console.log(mode);
        Plotly.update("map", {
            z: [z],
            colorbar: {
                title: label,
                len: 0.5
            },
            zmin: 0,
            zmax: Math.max(...z),
        });

    }

    function filterByYear(year, table) {
        var rows = document.querySelectorAll(table + ' tbody tr');
        if (year == '') {
            rows.forEach(function(row) {
                row.style.display = 'table-row';
            });
            return;
        }
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            if (cells[2].innerText == year) {
                row.style.display = 'table-row';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>