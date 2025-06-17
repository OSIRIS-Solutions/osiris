var activitiesTable = false,
    publicationTable = false,
    projectsExists = false,
    coauthorsExists = false,
    conceptsExists = false,
    collabExists = false,
    collabGraphExists = false,
    personsExists = false,
    wordcloudExists = false;


let activeCategories = new Set(); // Wird initial leer, also zeigt alles

// DataTables Filterfunktion registrieren
$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    const type = data[5]; // z. B. Spalte 1 ist der Typ (Publikation, Poster…)
    console.log(type);
    // Wenn keine Kategorien ausgewählt sind, alles zeigen
    if (activeCategories.size === 0) return true;

    return !activeCategories.has(type);
});

function navigate(key) {
    $('section').hide()
    $('section#' + key).show()

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    switch (key) {
        // case 'publications':
        //     if (publicationTable) break;
        //     publicationTable = initActivities('#publication-table', {
        //         filter: {
        //             topics: TOPIC,
        //             type: 'publication'
        //         }
        //     })
        //     break;

        case 'activities':
            if (activitiesTable) break;
            activitiesTable = initActivities('#activities-table', {
                filter: {
                    topics: TOPIC,
                }
            })
            timelineChart();
            break;

        case 'projects':
            // if (projectsExists) break;
            // projectsExists = true;
            // projectTimeline('#project-timeline', { user: {'$in': USERS} })
            break;

        // case 'coauthors':
        //     if (coauthorsExists) break;
        //     coauthorsExists = true;
        //     coauthorNetwork('#chord', { user: {'$in': USERS} })
        //     break;
        case 'graph':
            if (collabGraphExists) break;
            collabGraphExists = true;
            collabGraph('#collabGraph', { topics: TOPIC, single: true })
            break;

        case 'persons':
            if (personsExists) break;
            personsExists = true;
            userTable('#user-table', {
                filter: {
                    topics: TOPIC,
                    is_active: { '$ne': false }
                },
                subtitle: 'position',
            })
            break;

        case 'collab':
            if (collabExists) break;
            collabExists = true;
            collabChart('#collab-chart', {
                type: 'publication',
                topics: TOPIC
            })
            break;

        // case 'concepts':
        //     if (conceptsExists) break;
        //     conceptsExists = true;
        //     conceptTooltip()
        //     break;

        case 'wordcloud':
            if (wordcloudExists) break;
            wordcloudExists = true;
            wordcloud('#wordcloud-chart', { topics: TOPIC })
            break;
        default:
            break;
    }
}

// function filterActivities(activity){
//     if (!activitiesTable) return;
//     let column = 5;


//             dataTable.columns(column).search(activity, true, false, true).draw();

//     }

function timelineChart() {
    if (typeof timeline !== 'function') {
        console.error('Timeline function is not defined. Please ensure the timeline.js script is included.');
        return;
    }
    // current year and quarter
    // let date = new Date();
    let year = $('#activity-year').val();
    let currentYear = new Date().getFullYear();
    // check if year is a valid 4 digit number
    if (!/^\d{4}$/.test(year)) {
        toastError('Invalid year format. Please enter a valid 4-digit year.');
        return;
    }
    if (year > currentYear) {
        year = currentYear;
        $('#activity-year').val(year);
    }

    $('#timeline').empty()
    $('#event-selector').empty()

    let filter = {
        'topics': TOPIC,
        'start_date': {
            '$gte': `${year}-01-01`,
            '$lte': `${year}-12-31`
        },
    };
    // let quarter = Math.ceil((date.getMonth() + 1) / 3);
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/timeline",
        data: {
            filter: filter
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            let typeInfo = response.data.info;
            let events = response.data.events;
            let types = response.data.types;
            types.forEach(t => {
                let type = typeInfo[t];
                if (!type) return;
                let item = $('<small class="badge type-badge active ' + type.id + '" onclick="toggleTimelineActivity(\'' + type.id + '\')">' + lang(type.name, type.name_de ?? null) + '</small>');
                item.css('background-color', type.color);
                $('#event-selector').append(item);
            });
            timeline(year, 0, typeInfo, events);
        },
        error: function (response) {
            console.log(response);
        }
    });
}

function toggleTimelineActivity(type) {
    // check if type is active
    let active = ($('.badge.' + type).hasClass('active'));

    $('.badge.' + type).toggleClass('active');
    $('.event-circle.' + type).toggle();

    if (activitiesTable) {
        // activitiesTable.columns(5).search(type, true, false, true).draw();
        if (active) {
            activeCategories.add(type);
        } else {
            activeCategories.delete(type);
        }

        activitiesTable.draw(); // Tabelle neu zeichnen
    }
}


function collabChart(selector, data) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/department-network",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
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


function collabGraph(selector, data) {
    // coauthorNetwork(selector, data)
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/author-network",
        data: data,
        dataType: "json",
        success: function (response) {
            console.log(response);
            var matrix = response.data.matrix;
            var DEPTS = response.data.labels;

            var data = Object.values(DEPTS);
            var labels = data.map(item => item['name']);

            // var colors = []
            var links = []
            var depts_in_use = {};

            data.forEach(function (d, i) {
                // colors.push(d.dept.color ?? '#cccccc');
                var link = null
                if (i !== 0) link = ROOTPATH + "/profile/" + d.user
                links.push(link)

                if (d.dept.id && depts_in_use[d.dept.id] === undefined)
                    depts_in_use[d.dept.id] = d.dept;
            })

            Chords(selector, matrix, labels, null, data, links, false, null);


            var legend = d3.select('#legend')
                .append('div').attr('class', 'content')

            legend.append('div')
                .style('font-weight', 'bold')
                .attr('class', 'mb-5')
                .text(lang("Departments", "Abteilungen"))

            for (const dept in depts_in_use) {
                if (Object.hasOwnProperty.call(depts_in_use, dept)) {
                    const d = depts_in_use[dept];
                    var row = legend.append('div')
                        .attr('class', 'd-flex mb-5')
                        .style('color', d.color)
                    row.append('div')
                        .style('background-color', d.color)
                        .style("width", "2rem")
                        .style("height", "2rem")
                        .style("border-radius", ".5rem")
                        .style("display", "inline-block")
                        .style("margin-right", "1rem")
                    row.append('span').text(d.name)
                }
            }

        },
        error: function (response) {
            console.log(response);
        }
    });
    // $.ajax({
    //     type: "GET",
    //     url: ROOTPATH + "/api/dashboard/department-graph",
    //     data: data,
    //     dataType: "json",
    //     success: function (response) {
    //         console.log(response);
    //         Graph(response.data, selector, 800, 500);
    //     },
    //     error: function (response) {
    //         console.log(response);
    //     }
    // });
}
