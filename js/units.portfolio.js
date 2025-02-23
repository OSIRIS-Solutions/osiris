var activitiesTable = false,
    publicationTable = false,
    projectsExists = false,
    coauthorsExists = false,
    conceptsExists = false,
    // collaboratorsExists = false,
    collabExists = false,
    personsExists = false,
    wordcloudExists = false;

function navigate(key) {
    console.log(key);
    $('section').hide()
    $('section#' + key).show()

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    switch (key) {
        case 'publications':
            if (publicationTable) break;
            publicationTable = true;
            initActivities('#publication-table', {
                type: 'publication',
                json: JSON.stringify({
                    'hide': { '$ne': true },
                    'authors.user': { '$in': USERS },
                    'authors.aoi': { '$in': [1, true, '1', 'true'] }
                })
            })
            // impactfactors('chart-impact', 'chart-impact-canvas', { user: {'$in': USERS} })
            // authorrole('chart-authors', 'chart-authors-canvas', { user: {'$in': USERS} })
            break;

        case 'activities':
            if (activitiesTable) break;
            activitiesTable = true;
            initActivities('#activities-table', {
                type: { '$in': ['poster', 'lecture', 'award', 'software'] },
                json: JSON.stringify({
                    'hide': { '$ne': true },
                    'authors.user': { '$in': USERS },
                    'authors.aoi': { '$in': [1, true, '1', 'true'] }
                })
            })
            // activitiesChart('chart-activities', 'chart-activities-canvas', { user: {'$in': USERS} })
            break;

        case 'projects':
            if (projectsExists) break;
            projectsExists = true;
            // projectTimeline('#project-timeline', { user: {'$in': USERS} })

            collaboratorChart('#collaborators', {
                'dept': DEPT,
            });
            break;

        // case 'coauthors':
        //     if (coauthorsExists) break;
        //     coauthorsExists = true;
        //     coauthorNetwork('#chord', { user: {'$in': USERS} })
        //     break;

        case 'persons':
            if (personsExists) break;
            personsExists = true;
            // console.log(personsExists);
            userTable('#user-table', {
                filter: { 'username': { '$in': USERS } },
                hide_usernames: true,
                subtitle: 'position',
                'path': PORTALPATH
            })
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

// onload
$(document).ready(function () {
    if ($('#btn-general').length <= 0) {
        navigate('persons')
    }
});


function collaboratorChart(selector, data) {
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/collaborators",
        dataType: "json",
        data: data,
        success: function(response) {
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
                mapbox: {
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
                data.marker.size.push(item.count + 10)
                data.marker.color.push(item.color ?? 'rgba(0, 128, 131, 0.7)')
                data.lon.push(item.data.lng)
                data.lat.push(item.data.lat)
                data.text.push(`<b>${item.data.name}</b><br>${item.data.location}`)

            });
            console.log(data);

            Plotly.newPlot('map', [data], layout);
        },
        error: function(response) {
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
