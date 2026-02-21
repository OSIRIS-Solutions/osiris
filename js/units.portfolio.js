var activitiesTable = false,
    publicationTable = false,
    projectsExists = false,
    coauthorsExists = false,
    conceptsExists = false,
    collaboratorsExists = false,
    collabExists = false,
    personsExists = false,
    wordcloudExists = false;

const colMap = {
    'type': 2,
    'subtype': 3,
    'year': 4,
};

// check if BASE is defined else set to ROOTPATH
if (typeof BASE === 'undefined') {
    const BASE = ROOTPATH + '/preview';
}
$.extend(true, $.fn.dataTable.defaults, {
    layout: {
        // top1Start: '',
        topStart: 'search',
        topEnd: 'pageLength',
        bottomStart: 'paging',
        bottomEnd: 'info',
        // bottom1End: ''
    },
    lengthMenu: [5, 10, 25, 50, 100],
    buttons: null
});

function navigate(key) {
    console.log(key);
    $('section').hide()
    $('section#' + key).show()

    // $('.pills .btn').removeClass('active')
    // $('.pills .btn#btn-' + key).addClass('active')
    $('#group-pills a').removeClass('active')
    $('#group-pills a#btn-' + key).addClass('active')

    switch (key) {
        case 'publications':
            if (publicationTable) break;
            publicationTable = $('#publication-table').DataTable({
                "ajax": {
                    "url": ROOTPATH + '/portfolio/unit/' + DEPT + '/publications',
                    dataSrc: 'data'
                },
                "sort": false,
                "pageLength": 10,
                "lengthChange": false,
                "searching": true,
                "pagingType": "numbers",
                columnDefs: [{
                    targets: 0,
                    data: 'icon',
                    className: 'w-50'
                },
                {
                    targets: 1,
                    data: 'html',
                    render: function (data, type, row) {
                        // replace links to activities with links to the activity page
                        data = data.replace(/href='\/activity/g, "href='" + BASE + "/activity");
                        return data;
                    }
                },
                {
                    targets: 2,
                    data: 'type',
                    defaultContent: '',
                    visible: false,
                    searchable: true,
                },
                {
                    targets: 3,
                    data: 'subtype',
                    defaultContent: '',
                    visible: false,
                    searchable: true,
                },
                {
                    targets: 4,
                    data: 'year',
                    defaultContent: '',
                    visible: false,
                    searchable: true,
                }
                ],
            });

            // once publicationTable is initialized, set up the filters
            initFilters('publications');

            break;

        case 'activities':
            if (activitiesTable) break;
            activitiesTable = $('#activities-table').DataTable({
                "ajax": {
                    "url": ROOTPATH + '/portfolio/unit/' + DEPT + '/activities',
                    dataSrc: 'data'
                },
                "sort": false,
                "pageLength": 10,
                "lengthChange": false,
                "searching": true,
                "pagingType": "numbers",
                columnDefs: [{
                    targets: 0,
                    data: 'icon',
                    className: 'w-50'
                },
                {
                    targets: 1,
                    data: 'html',
                    render: function (data, type, row) {
                        // replace links to activities with links to the activity page
                        data = data.replace(/href='\/activity/g, "href='" + BASE + "/activity");
                        return data;
                    }
                },
                {
                    targets: 2,
                    data: 'type',
                    defaultContent: '',
                    visible: false,
                    searchable: true,
                },
                {
                    targets: 3,
                    data: 'subtype',
                    defaultContent: '',
                    visible: false,
                    searchable: true,
                },
                {
                    targets: 4,
                    data: 'year',
                    defaultContent: '',
                    visible: false,
                    searchable: true,
                }
                ],
            });
            break;

        case 'projects':
            if (projectsExists) break;
            projectsExists = true;
            // projectTimeline('#project-timeline', { user: {'$in': USERS} })
            $('#projects-table').DataTable({
                ajax: {
                    url: ROOTPATH + '/portfolio/unit/' + DEPT + '/projects',
                },
                type: 'GET',
                dom: 'frtipP',
                columns: [
                    {
                        data: 'name',
                        render: function (data, type, row) {
                            let teaser = lang(row.teaser_en ?? '', row.teaser_de ?? null);
                            if (teaser.length > 140) {
                                teaser = `<hr><p class="">${teaser}...<span class="link">Weiterlesen</span></p>`;
                            }
                            let start = new Date(row.start_date);
                            let end = new Date(row.end_date);

                            return `<a class="d-block w-full colorless" href="${BASE}/project/${row.id}">
                  <div style="display: none;">${row.name}</div>
                  <span class="float-right text-primary">${lang(row.type_details.name ?? '', row.type_details.name_de ?? null)}</span>
                  <h5 class="my-0 text-primary">
                      ${lang(row.name, row.name_de ?? null)}
                  </h5>
                  <small class="text-muted">
                      ${lang(row.title, row.title_de ?? null)}
                  </small>
                  <p class="d-flex justify-content-between">
                      <span class="text-secondary">${start.toLocaleDateString()} - ${end.toLocaleDateString()}</span>

                  </p>
                    ${teaser}
              </a>`;
                        }
                    },
                ]
            });
            break;
        case 'collaborators':
            if (collaboratorsExists) break;
            collaboratorsExists = true;
            collaboratorChart('#collaborators', {
                'dept': DEPT,
            });

        // case 'coauthors':
        //     if (coauthorsExists) break;
        //     coauthorsExists = true;
        //     coauthorNetwork('#chord', { user: {'$in': USERS} })
        //     break;

        case 'persons':
            if (personsExists) break;
            personsExists = true;
            // console.log(personsExists);
            return $('#users-table').DataTable({
                dom: 'frtipP',
                deferRender: true,
                responsive: true,
                "order": [
                    [1, 'asc'],
                ],

                paging: true,
                autoWidth: true,
                pageLength: 18,
            });
            break;

        case 'collab':
            if (collabExists) break;
            collabExists = true;
            collabChart('#collab-chart', {
                type: 'publication',
                dept: DEPT,
            })
            break;
        // case 'collaborators':
        //     if (collaboratorsExists) break;
        //     collaboratorsExists = true;
        //     break;

        case 'concepts':
            if (conceptsExists) break;
            conceptsExists = true;
            conceptTooltip()
            break;

        case 'wordcloud':
            if (wordcloudExists) break;
            wordcloudExists = true;
            wordcloud('#wordcloud-chart', { user: { '$in': USERS } })
            break;
        default:
            break;
    }

}

function filterTable(id, column, value) {
    var table = $(id).DataTable();
    console.log(column);
    if (column in colMap) {
        column = colMap[column];
    }
    table.column(column).search(value).draw();
}


// onload
$(document).ready(function () {
    if ($('#btn-general').length <= 0) {
        navigate('persons')
    }


    $(document).on('click', '.datatable-filter a', function (e) {
        e.preventDefault();
        // define variables
        const $section = $(this).closest('section');
        const tableId = $section.find('table').attr('id');
        const column = $(this).data('column');
        const value = $(this).data('value');

        // check if the filter is already active
        if ($(this).hasClass('active')) {
            // remove filter
            $(this).removeClass('active');
            filterTable('#' + tableId, column, '');
            return;
        }

        // remove active class from other filters in the same group
        $(this).closest('.datatable-filter').find('a.filter-item').removeClass('active');

        // add active class to the clicked filter
        $(this).addClass('active');

        // apply filter
        filterTable('#' + tableId, column, value);
    });
});



function initFilters(section) {
    const $section = $('section#' + section);
    const dataTable = $section.find('table').DataTable();
    dataTable.on('init', function () {
        // for each filter in the section, 
        // count the number of entries in the datatable 
        // and update the filter item
        $section.find('.datatable-filter').each(function (i, element) {
            const filter = $(element).find('a');
            filter.each(function (i, el) {
                let value = $(el).data('value')
                if (value === undefined || value === null) {
                    value = '';
                }
                const column = $(el).data('column');
                if (!(column in colMap)) {
                    return;
                }
                const columnIndex = colMap[column];
                const count = dataTable.column(columnIndex).data().filter(function (v, j) {
                    return v === value ? true : false;
                }).length;
                // console.log(count);
                if (count <= 0) {
                    $(el).addClass('hidden');
                } else {
                    $(el).removeClass('hidden');
                }
                $(el).append(` <span class="index">${count}</span>`)
            });
        }
        );
    });
}

function collaboratorChartLegacy(selector, data) {
            $.ajax({
                type: "GET",
                url: ROOTPATH + "/api/dashboard/collaborators",
                dataType: "json",
                data: data,
                success: function (response) {
                    if (response.count <= 1) {
                        $(selector).hide()
                        return
                    }
                    console.log(response);
                    var zoomlvl = 1;
                    switch (response.data.scope ?? 'international') {
                        case 'local':
                            zoomlvl = 5
                            break;
                        case 'national':
                            zoomlvl = 4
                            break;
                        case 'continental':
                            zoomlvl = 3
                            break;
                        case 'international':
                            zoomlvl = 1
                            break;
                        default:
                            break;
                    }
                    var layout = {
                        map: {
                            style: "open-street-map",
                            center: {
                                lat: 52,
                                lon: 10
                            },
                            zoom: zoomlvl
                        },
                        margin: {
                            r: 0,
                            t: 0,
                            b: 0,
                            l: 0
                        },
                        hoverinfo: 'text',
                        // autosize:true
                    };
                    var data = {
                        type: 'scattermap',
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
                        data.marker.size.push(item.count + 10)
                        data.marker.color.push(item.color ?? 'rgba(0, 128, 131, 0.9)')
                        data.lon.push(item.data.lng)
                        data.lat.push(item.data.lat)
                        data.text.push(`<b>${item.data.name}</b><br>${item.data.location}`)

                    });
                    console.log(data);

                    Plotly.newPlot('collaborator-map', [data], layout);
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }


function collaboratorChart(selector, data) {
            $.ajax({
                type: "GET",
                url: ROOTPATH + "/portfolio/unit/" + data.dept + "/collaborators-map",
                dataType: "json",
                // data: data,
                success: function (json) {
                    let $map = $(selector);
                    console.log(json);
                    const items = (json && json.data) ? json.data : [];
                    if (!items.length) {
                        $map.addClass("hidden");
                        return;
                    }
                    const trace = {
                        type: "scattermap",
                        mode: "markers",
                        hoverinfo: "text",
                        lon: [],
                        lat: [],
                        text: [],
                        marker: { size: [], color: [] }
                    };

                    items.forEach(item => {
                        const d = item.data || {};
                        if (!d || d.lng === "" || d.lat === "" || d.lng == null || d.lat == null) return;

                        const count = Number(item.count || 0);
                        trace.marker.size.push((count * 10) / 2 + 5);

                        let color = "#304cb2";
                        if (d.role === "coordinator" || d.current === true) {
                            color = "#B61F29";
                        }
                        trace.marker.color.push(color);

                        trace.lon.push(Number(d.lng));
                        trace.lat.push(Number(d.lat));

                        let text = `<b>${d.name || ""}</b>`;
                        text += `<br>${count} Projects`;
                        if (d.location) text += `<br>${d.location}`;
                        trace.text.push(text);
                    });

                    const validLons = trace.lon;
                    const validLats = trace.lat;
                    if (!validLons.length || !validLats.length) {
                        $map.addClass("hidden");
                        return;
                    }

                    const minLon = Math.min(...validLons) - 1;
                    const maxLon = Math.max(...validLons) + 1;
                    const minLat = Math.min(...validLats) - 1;
                    const maxLat = Math.max(...validLats) + 1;

                    const centerLon = (minLon + maxLon) / 2;
                    const centerLat = (minLat + maxLat) / 2;

                    const lonRange = maxLon - minLon;
                    const latRange = maxLat - minLat;
                    const maxRange = Math.max(lonRange, latRange);
                    const zoom = Math.log2(360 / maxRange) - 1;

                    const layout = {
                        map: {
                            style: "carto-positron",
                            center: { lon: centerLon, lat: centerLat },
                            zoom: zoom
                        },
                        margin: { r: 0, t: 0, b: 0, l: 0 },
                        showlegend: false,
                        hoverinfo: "text"
                    };

                    Plotly.newPlot($map[0], [trace], layout, { displayModeBar: false });
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }

function unpack(rows, key) {
            return rows.map(function (row) {
                return row[key];
            });
        }

function collaboratorChartCountries(selector, data) {
            $.ajax({
                type: "GET",
                url: ROOTPATH + "/portfolio/unit/" + data.dept + "/collaborators-by-country",
                dataType: "json",
                // data: data,
                success: function (json) {
                    let $map = $(selector);
                    const data = (json && json.data) ? json.data : [];
                    console.log(data);
                    if (!data.countries.length) {
                        $map.addClass("hidden");
                        return;
                    }
                    const countries = data.countries;
                    const trace = {
                        type: "choroplethmap",
                        locationmode: 'ISO-3',
                        locations: unpack(countries, 'iso3'),
                        z: unpack(countries, 'count'),
                        text: unpack(countries, 'label'),
                        hoverinfo: "text",
                        // colorscale: [
                        //     [0, "#f0f9e8"],
                        //     [0.5, "#bae4bc"],
                        //     [1, "#7bccc4"]
                        // ],
                        // colorbar: {
                        //     title: lang('Number of Projects', 'Anzahl Projekte'),
                        // }
                    };
                    console.log(trace);
                    const layout = {
                        map: {
                            style: "carto-positron"
                        },
                        margin: { r: 0, t: 0, b: 0, l: 0 },
                        // showlegend: false,
                        // hoverinfo: "text"
                    };
                    let traces = [trace];

                    Plotly.newPlot($map[0], traces, layout, { displayModeBar: false });
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }


function collabChart(selector, data) {
            $.ajax({
                type: "GET",
                url: ROOTPATH + "/api/dashboard/department-network",
                data: data,
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    // if (response.count <= 1) {
                    //     $('#collab').hide()
                    //     return
                    // }
                    var matrix = response.data.matrix;
                    var data = response.data.labels;

                    var labels = [];
                    var colors = [];
                    data = Object.values(data)
                    data.forEach(element => {
                        labels.push(element.id);
                        colors.push(element.color)
                    });


                    Chords(selector, matrix, labels, colors, data, links = false, useGradient = true, highlightFirst = false, type = 'publication');
                },
                error: function (response) {
                    console.log(response);
                }
            });
        }

