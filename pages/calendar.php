<?php

/**
 * Calendar overview page
 * Contains a calendar view of events, activities and conferences
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
if (!isset($_SESSION['username'])) {
    header('Location: ' . ROOTPATH . '/login');
    exit;
}

$user = $_SESSION['username'];

// get user
$person = $osiris->persons->findOne(['username' => $user]);

// get persons unit hierarchy
$units = [];
$tree = [];
if (isset($person['units'])) {
    $units = DB::doc2Arr($person['units'] ?? []);
    // filter units from the past
    $units = array_filter($units, function ($unit) {
        return !isset($unit['end']) || strtotime($unit['end']) > time();
    });
    $unit_ids = array_column($units, 'unit');

    $hierarchy = $Groups->getPersonHierarchyTree($unit_ids);
    $tree = $Groups->readableHierarchy($hierarchy);
    // remove first and then reverse 
    array_shift($tree);
    $tree = array_reverse($tree);
}
?>

<h1>
    <i class="ph-duotone ph-calendar"></i>
    <?= lang('Calendar', 'Kalender') ?>
</h1>

<div class="row row-eq-spacing-md">
    <div class="col-12 col-md-4">
        <h4 class="title"><?= lang('Filter', 'Filter') ?></h4>

        <div class="filter">
            <table id="filter-unit" class="table small simple">
                <tr class="active" style="--highlight-color: var(--primary-color);">
                    <td>
                        <a data-type="all" onclick="updateCalendar(this, '')" class="item d-block colorless" id="all-btn">
                            <span><?= lang('Only my own', 'Nur meine eigenen') ?></span>
                        </a>
                    </td>
                </tr>
                <?php foreach ($tree as $unit) { ?>
                    <tr style="--highlight-color: var(--secondary-color);">
                        <td>
                            <a data-type="<?= $unit['id'] ?>" onclick="updateCalendar(this, '<?= $unit['id'] ?>')" class="item d-block colorless" id="<?= $$unit['id'] ?>-btn">
                                <span><?= lang($unit['name_en'], $unit['name_de']) ?></span>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>

    </div>
    <div class="col-12 col-md-8">
        <div id="calendar"></div>
        <div id="legend">
            <div class="legend-item">
                <span class="legend-color conference"></span>
                Konferenz
            </div>
            <div class="legend-item">
                <span class="legend-color activity"></span>
                Aktivität
            </div>
            <div class="legend-item">
                <span class="legend-color project"></span>
                Projekt
            </div>
            <!-- guests -->
            <div class="legend-item">
                <span class="legend-color guest"></span>
                Gast
            </div>
        </div>

        <style>
            #legend {
                display: flex;
                gap: 1rem;
                margin-top: 1rem;
                font-family: Arial, sans-serif;
            }

            .legend-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .legend-color {
                width: 16px;
                height: 16px;
                display: inline-block;
                border-radius: 4px;
            }

            .legend-color.conference {
                background-color: #28a745;
            }

            .legend-color.research-trip {
                background-color: #ffc107;
            }

            .legend-color.activity {
                background-color: #007bff;
            }

            .legend-color.project {
                background-color: #dc3545;
            }

            .legend-color.guest {
                background-color: #6c757d;
            }
        </style>
    </div>
</div>
<style>
    :root {
        --fc-border-color: var(--border-color);
        --fc-daygrid-event-dot-width: 5px;
        --fc-today-bg-color: var(--secondary-color-20);
    }

    .fc-scrollgrid {
        background-color: white;
    }

    .toastui-calendar-layout {
        height: 50rem !important;
    }

    .calendar-tooltip {
        max-width: 300px;
    }

    .calendar-tooltip h3 {
        margin-bottom: 0.5rem;
        margin-top: 0;
        font-size: 1.8rem;
    }

    .badge.project {
        background-color: #dc3545;
        color: white;
    }

    .badge.activity {
        background-color: #007bff;
        color: white;
    }

    .badge.research_trip {
        background-color: #ffc107;
        color: black;
    }

    .badge.event {
        background-color: #28a745;
        color: white;
    }
</style>
<script src='<?= ROOTPATH ?>/js/fullcalendar.min.js'></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>

<script>

const FILTERS = {};
var Calendar = null;

function updateCalendar(el, unit) {
    // remove active class from all buttons
    $('#filter-unit tr.active').removeClass('active');
    // add active class to clicked button
    $(el).closest('tr').addClass('active');

    // update filter
    FILTERS.unit = unit;

    // refetch events
    Calendar.refetchEvents();
}

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        Calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: lang('en', 'de'),
            events: function(fetchInfo, successCallback, failureCallback) {
                // start and end as ISO dates
                var start = fetchInfo.startStr.split('T')[0];
                var end = fetchInfo.endStr.split('T')[0];
                
                var requestURL = ROOTPATH + '/api/calendar?start=' + start + '&end=' + end;
                if (FILTERS.unit) {
                    requestURL += '&unit=' + FILTERS.unit;
                }

                // Ajax-Request, um die Events vom Backend zu holen
                fetch(requestURL, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(fetchInfo);
                        if (data.status === 200) {
                            successCallback(data.data); // Nur die Events weitergeben
                        } else {
                            failureCallback('Fehler beim Laden der Events');
                        }
                    })
                    .catch(error => {
                        failureCallback(error);
                    });
            },
            eventDataTransform: function(eventData) {
                // Farben basierend auf dem Typ setzen
                if (eventData.type === 'event') {
                    eventData.backgroundColor = '#28a745'; // Grün
                    eventData.borderColor = '#1e7e34'; // Dunkler Grün
                } else if (eventData.type === 'research_trip') {
                    eventData.backgroundColor = '#ffc107'; // Gelb
                    eventData.borderColor = '#e0a800'; // Dunkler Gelb
                } else if (eventData.type === 'activity') {
                    eventData.backgroundColor = '#007bff'; // Blau
                    eventData.borderColor = '#0056b3'; // Dunkler Blau
                } else if (eventData.type === 'project') {
                    eventData.backgroundColor = '#dc3545'; // Rot
                    eventData.borderColor = '#bd2130'; // Dunkler Rot
                } else if (eventData.type === 'guest') {
                    eventData.backgroundColor = '#6c757d'; // Grau
                    eventData.borderColor = '#495057'; // Dunkler Grau
                }

                if (eventData.end) {
                    // add one day to end date, because fullcalendar is exclusive
                    let endDate = new Date(eventData.end);
                    endDate.setDate(endDate.getDate() + 1);
                    eventData.end = endDate.toISOString().split('T')[0];
                }
                return eventData;
            },
            eventClick: function(info) {
                // Popover auf das Event-Element anwenden
                const $el = $(info.el); // Das HTML-Element des Events

                // Vorherige Popovers entfernen, um Mehrfach-Instanzen zu verhindern
                $('.popover').remove();
                console.log(info.event);
                let date = info.event.start.toLocaleDateString();
                // add end date if available
                if (info.event.end) {
                    // use end date - 1 day
                    const end = info.event.end
                    end.setDate(end.getDate() - 1);
                    if (end.toLocaleDateString() !== date)
                        date += ' - ' + end.toLocaleDateString();
                }

                let type = info.event.extendedProps.type || 'na';
                let label = lang('Unknown', 'Nicht angegeben');
                let link = '#';

                switch (type) {
                    case 'event':
                        label = lang('Event', 'Veranstaltung');
                        link = ROOTPATH + '/conferences/view/' + info.event.id;
                        break;
                    case 'research_trip':
                        label = lang('Field research', 'Forschungsreise');
                        link = ROOTPATH + '/research-trips/' + info.event.id;
                        break;
                    case 'activity':
                        label = lang('Activity', 'Aktivität');
                        link = ROOTPATH + '/activities/view/' + info.event.id;
                        break;
                    case 'project':
                        label = lang('Project', 'Projekt');
                        link = ROOTPATH + '/projects/view/' + info.event.id;
                        break;
                    case 'guest':
                        label = lang('Guest', 'Gast');
                        link = ROOTPATH + '/guests/view/' + info.event.id;
                        break;
                }


                // Popover mit Bootstrap initialisieren
                $el.popover({
                    placement: 'auto',
                    container: '#calendar',
                    trigger: 'focus',
                    html: true,
                    content: `
                        <div class="calendar-tooltip">
                            <h3>${info.event.title}</h3>
                            <p><strong>${lang('Type', 'Typ')}:</strong> <span class="badge ${type}">${label}</span></p>
                            <p><strong>${lang('Date', 'Datum')}:</strong> ${date}</p>
                            <a class="btn small" href="${link}">${lang('More details', 'Mehr Details')}</a>
                            <button class="btn small" onclick="$('.popover').remove()">
                                Schließen
                            </button>
                        </div>`
                });

                // Popover anzeigen
                $el.popover('show');
            }
        });
        Calendar.render();
    });

</script>