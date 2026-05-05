spectrumTooltipExists = false;

function navigate(key) {
    $('section').hide()
    $('section#' + key).show()

    $('#navigation .btn').removeClass('active')
    $('#navigation .btn#btn-' + key).addClass('active')

    switch (key) {
        case 'coauthors':
            coauthors()
            break;

        default:
            break;
    }

    // save as hash
    window.location.hash = 'section-' + key
}

$(document).ready(function () {
    // get hash
    var hash = window.location.hash
    if (hash && hash.includes('#section-')) {
        let key = hash.replace('#section-', '')
        // check if section exists
        if ($('section#' + key).length) {
            navigate(key)
        }
    }
});


function showCollaboratorChart(collab_type, button) {
    // check if chart already exists
    $('.collab-chart').hide();
    var chartContainer = $('#chart-' + collab_type);

    if (button) {
        $('#collab-type-filters .btn').removeClass('active')
        $(button).addClass('active')
    }


    chartContainer.show();
    if (chartContainer.length == 0 || chartContainer.hasClass('plotted')) return;

    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/activity-" + collab_type,
        data: {
            activity: ACTIVITY_ID
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            var container = document.getElementById('chart-' + collab_type)
            if (response.count == 0) {
                container.classList.add('hidden')
                return;
            }
            var data = response.data;
            var ctx = document.getElementById('chart-' + collab_type + '-canvas')
            var myChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: '# of Scientists',
                        data: data.y,
                        backgroundColor: data.colors,
                        borderColor: '#464646', //'',
                        borderWidth: 1,
                    }]
                },
                plugins: [ChartDataLabels],
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            display: true,
                        },
                        title: {
                            display: false,
                            text: 'Scientists approvation'
                        },
                        datalabels: {
                            color: 'black',
                            font: {
                                size: 20
                            }
                        }
                    },
                }
            });
            chartContainer.addClass('plotted');
        },
        error: function (response) {
            console.log(response);
        }
    });
}


function coauthors() {
    showCollaboratorChart('contributors');
}


function navigateCitation(id) {
    $('#citation-tabs .btn').removeClass('active');
    $('#btn-' + id).addClass('active');
    $('.citation-box').hide();
    $('#' + id + '-box').show();
}

function copyToClipboard(selector) {
    // check if navigator.clipboard is available
    if (!navigator.clipboard) {
        toastError(lang('This browser does not support copying to clipboard.', 'Dieser Browser unterstützt das Kopieren in die Zwischenablage nicht.'));
        return;
    }
    var text = $(selector).text()
    navigator.clipboard.writeText(text)
    toastSuccess(lang('Query copied to clipboard.', 'Abfrage in die Zwischenablage kopiert.'))
}

function fetchOpenAlex(doi) {
    // set button to loading state
    $('#openalex-refresh-button').prop('disabled', true).addClass('loading');
    $.ajax({
        type: "POST",
        url: ROOTPATH + "/api/openalex/enrich",
        data: {
            doi: doi
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            if (response.ok) {
            $('#openalex-refresh-button').hide()
            // insert text instead of button
            $('#openalex-refresh-button').after('<span class="text-success">' + lang('OpenAlex was queried. Refresh the page to see updated data.', 'OpenAlex wurde abgefragt. Aktualisiere die Seite, um die aktualisierten Daten zu sehen.') + '</span>')
            } else {
                $('#openalex-refresh-button').prop('disabled', false).removeClass('loading');
                toastError(lang('Failed to fetch data from OpenAlex.', 'Daten konnten nicht von OpenAlex abgerufen werden.'));
            }

        },
        error: function (response) {
            console.log(response);
        }
    });
}
