
function navigate(key) {
    $('section').hide()
    $('section#' + key).show()

    $('.pills .btn').removeClass('active')
    $('.pills .btn#btn-' + key).addClass('active')

    switch (key) {
        case 'coauthors':
            coauthors()
            break;

        case 'concepts':
            conceptTooltip()
            break;
        default:
            break;
    }

    // save as hash
    window.location.hash = 'section-'+key
}

$(document).ready(function () {
    // get hash
    var hash = window.location.hash
    if (hash && hash.includes('#section-')) {
        navigate(hash.replace('#section-', ''))
    }
});

coauthorsExists = false;
function coauthors() {
    if (coauthorsExists) return;
    coauthorsExists = true
    $.ajax({
        type: "GET",
        url: ROOTPATH + "/api/dashboard/activity-authors",
        data: {
            activity: ACTIVITY_ID
        },
        dataType: "json",
        success: function (response) {
            console.log(response);
            var container = document.getElementById('chart-authors')
            if (response.count == 0) {
                container.classList.add('hidden')
                return;
            }
            var data = response.data;
            var ctx = document.getElementById('chart-authors-canvas')
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
            var legend = d3.select('#dept-legend')
                .append('div')
            // .attr('class', 'content')

            legend.append('h5')
                .attr('class', 'mt-0')
                .text(lang("Units", "Organisationseinheiten"))

            data.labels.forEach((label, i) => {
                var row = legend.append('div')
                    .attr('class', 'd-flex mb-5 mt-10')
                // .style('color', data.colors[i])
                row.append('div')
                    .style('background-color', data.colors[i])
                    .style("width", "2rem")
                    .style("height", "2rem")
                    .style("border-radius", ".5rem")
                    .style("display", "inline-block")
                    .style("margin-right", "1rem")
                row.append('span').text(label)
                    .style('font-weight', 'bold')
                    .attr('class', 'm-0')

                data.persons[i].forEach(name => {
                    legend.append('p')
                        .style("margin", "0 0 0 3rem")
                        .html(name)
                });
            });

            if (data.multi){
                legend.append('p')
                    .text(lang('* Multiple affiliations', '* Mehrere Zugehörigkeiten'))
                    .style('font-size', 'small')
            }
        },
        error: function (response) {
            console.log(response);
        }
    });
}


conceptTooltipExists = false;
function conceptTooltip() {
    if (conceptTooltipExists) return;
    conceptTooltipExists = true
    $('.concept').each(function () {
        console.log(this);
        var el = $(this)
        var data = {
            score: el.attr('data-score'),
            name: el.attr('data-name'),
            wikidata: el.attr('data-wikidata'),
        }
        el.popover({
            placement: 'auto bottom',
            container: '#concepts',
            mouseOffset: 10,
            // closeOnClickOutside: true,
            // followMouse: true,
            trigger: 'click',
            html: true,
            content: function () {
                var label = lang('Activities', 'Aktivitäten')
                if (data.count == 1) label = lang('Activity', 'Aktivität');
                return `<b>${data.name}</b><br>
                    Score: ${data.score} %</br>
                    <hr>
                    <a href="${ROOTPATH}/concepts/${data.name}"><i class="ph ph-arrow-right"></i> Concept page</a><br>
                    <a href="${data.wikidata}" target="_blank" rel="noopener noreferrer"><i class="ph ph-arrow-up-right"></i> Wikidata</a>
                    `;
            }
        });
    });
}

