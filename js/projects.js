
$.extend($.fn.DataTable.ext.classes, {
    sPaging: "pagination mt-10 ",
    sPageFirst: "direction ",
    sPageLast: "direction ",
    sPagePrevious: "direction ",
    sPageNext: "direction ",
    sPageButtonActive: "active ",
    sFilterInput: "form-control sm d-inline w-auto ml-10 ",
    sLengthSelect: "form-control sm d-inline w-auto",
    sInfo: "float-right text-muted",
    sLength: "float-right"
});



function navigate(key) {
    $('section').hide()
    $('section#' + key).show()

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    switch (key) {
        case 'activities':
            if (activitiesTable) break;
            initActivities()
            // activitiesChart()
            break;

        case 'collabs':
            if (collabChart) break;
            initCollabs()
            break;

        default:
            break;
    }
    // scroll to #project-nav 
    $('html, body').animate({
        scrollTop: $('#project-badges').offset().top
    }, 200);

}

var collabChart = false
function initCollabs() {
    collabChart = true
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/collaborators",
        data: {
            project: PROJECT
        },
        dataType: "json",
        success: function (response) {
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
            layout.mapbox.zoom = zoomlvl;

            var data = response.data.collaborators
            data.type = 'scattermapbox'
            data.mode = 'markers'
            data.hoverinfo = 'text',

                Plotly.newPlot('map', [data], layout);
        },
        error: function (response) {
            console.log(response);
        }
    });
}

var activitiesTable;
function initActivities() {
    activitiesTable = $('#activities-table').DataTable({
        "ajax": {
            "url": ROOTPATH + '/api/all-activities',
            "data": {
                page: 'activities',
                display_activities: 'web',
                // user: CURRENT_USER,
                filter: { 'projects': PROJECT }
            },
            dataSrc: 'data'
        },
        deferRender: true,
        pageLength: 5,
        columnDefs: [
            {
                targets: 0,
                data: 'icon',
                // className: 'w-50'
            },
            {
                targets: 1,
                data: 'activity'
            },
            {
                targets: 2,
                data: 'links',
                className: 'unbreakable'
            },
            {
                targets: 3,
                data: 'search-text',
                searchable: true,
                visible: false,
            },
            {
                targets: 4,
                data: 'start',
                searchable: true,
                visible: false,
            },
        ],
        "order": [
            [4, 'desc'],
            // [0, 'asc']
        ]
    });
}


