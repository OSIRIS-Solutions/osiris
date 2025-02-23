var activitiesTable = false,
    publicationTable = false,
    projectsExists = false,
    coauthorsExists = false,
    conceptsExists = false,
    collabExists = false,
    collabGraphExists = false,
    personsExists = false,
    wordcloudExists = false;

function navigate(key) {
    $('section').hide()
    $('section#' + key).show()

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    switch (key) {
        case 'publications':
            if (publicationTable) break;
            publicationTable = true;
            initActivities('#publication-table', {
                page: 'my-activities',
                display_activities: 'web',
                'authors.units': DEPT,
                type: 'publication'
            })
            // impactfactors('chart-impact', 'chart-impact-canvas', { user: {'$in': USERS} })
            // authorrole('chart-authors', 'chart-authors-canvas', { user: {'$in': USERS} })
            break;

        case 'activities':
            if (activitiesTable) break;
            activitiesTable = true;
            initActivities('#activities-table', {
                page: 'my-activities',
                display_activities: 'web',
                'authors.units': DEPT,
                type: { '$ne': 'publication' }
            })
            // activitiesChart('chart-activities', 'chart-activities-canvas', { user: {'$in': USERS} })
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
            collabGraph('#collabGraph', { dept: DEPT, single: true })
            break;

        case 'persons':
            if (personsExists) break;
            personsExists = true;
            userTable('#user-table', {
                filter: {
                    'units': DEPT_TREE,
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
                dept: DEPT,
            })
            break;

        case 'concepts':
            if (conceptsExists) break;
            conceptsExists = true;
            conceptTooltip()
            break;

        case 'wordcloud':
            if (wordcloudExists) break;
            wordcloudExists = true;
            wordcloud('#wordcloud-chart', { 'units': DEPT_TREE })
            break;
        default:
            break;
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
