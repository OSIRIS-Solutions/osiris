var activitiesTable = false,
    publicationTable = false,
    projectsExists = false,
    coauthorsExists = false,
    spectrumExists = false,
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
                user: CURRENT_USER,
                type: 'publication'
            })
            impactfactors('chart-impact', 'chart-impact-canvas', { user: CURRENT_USER })
            authorrole('chart-authors', 'chart-authors-canvas', { user: CURRENT_USER })
            break;

        case 'activities':
            if (activitiesTable) break;
            activitiesTable = true;
            initActivities('#activities-table', {
                page: 'my-activities',
                user: CURRENT_USER,
                type: { '$ne': 'publication' }
            })
            activitiesChart('chart-activities', 'chart-activities-canvas', { user: CURRENT_USER })
            break;

        case 'projects':
            if (projectsExists) break;
            projectsExists = true;
            projectTimeline('#project-timeline', { user: CURRENT_USER })
            break;

        case 'coauthors':
            if (coauthorsExists) break;
            coauthorsExists = true;
            coauthorNetwork('#chord', { user: CURRENT_USER })
            break;

        case 'spectrum':
            if (spectrumExists) break;
            spectrumExists = true;
            spectrumTooltip()
            break;

        case 'wordcloud':
            if (wordcloudExists) break;
            wordcloudExists = true;
            wordcloud('#wordcloud-chart', { user: CURRENT_USER })
            break;
        case 'general':
            break;
        case 'news':
            $('section#news').show()
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
    if (hash) {
        hash = hash.replace('#section-', '')
        // check if hash is a valid section
        if ($(`section#${hash}`).length > 0) {
            navigate(hash);
        }
    }


    $("#toggle-show-5").click(function () {
        $('#chord').empty()
        $('#legend').empty()
        coauthorNetwork('#chord', { user: CURRENT_USER })
        $('#coauthor-network-info-5').show()
        $('#coauthor-network-info-all').hide()
        $(this).attr('disabled', true)
        $('#toggle-show-all').attr('disabled', false)
    });

    $("#toggle-show-all").click(function () {
        $('#chord').empty()
        $('#legend').empty()
        coauthorNetwork('#chord', { user: CURRENT_USER, all: true })
        $('#coauthor-network-info-5').hide()
        $('#coauthor-network-info-all').show()
        $(this).attr('disabled', true)
        $('#toggle-show-5').attr('disabled', false)
    });

    $("#toggle-wordcloud-activities").click(function () {
        $('#wordcloud-chart').empty()
        wordcloud('#wordcloud-chart', { user: CURRENT_USER })
        $(this).attr('disabled', true)
        $('#toggle-wordcloud-publications').attr('disabled', false)
    });

    $("#toggle-wordcloud-publications").click(function () {
        $('#wordcloud-chart').empty()
        wordcloud('#wordcloud-chart', { user: CURRENT_USER, type: 'publication' })
        $(this).attr('disabled', true)
        $('#toggle-wordcloud-activities').attr('disabled', false)
    });

});

function conferenceToggle(el, id, type = 'interests') {
    // ajax call to update user's conference interests
    $.ajax({
        url: ROOTPATH + '/ajax/conferences/toggle-interest',
        type: 'POST',
        data: { type: type, conference: id },
        success: function (data) {
            if (data) {
                if (type == 'dismissed') {
                    $(el).closest('tr').remove()
                    return
                }
                $('#conference-' + type).toggleClass('active')
                $(el).toggleClass('active')
                $(el).find('b').html(data)
            }

        }
    })
}
