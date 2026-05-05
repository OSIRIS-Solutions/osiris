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
});

function deadlineTimeline(options) {
    // English comments only.
    // options = {
    //   divSelector: '#deadline-timeline',
    //   typeInfo: { submission: { color:'#C75B12', icon:'paper-plane-tilt' }, ... },
    //   deadlines: [{ id, title, type, due_time (unix seconds), url }, ...],
    //   monthsAhead: 6,
    //   startLabel: 'Today', endLabel: '+6 months',
    // }

    const divSelector = options.divSelector ?? '#deadline-timeline';
    const typeInfo = options.typeInfo ?? {};
    const deadlines = options.deadlines ?? [];
    const monthsAhead = options.monthsAhead ?? 6;

    const startLabel = options.startLabel ?? 'Today';
    const endLabel = options.endLabel ?? `+${monthsAhead} months`;

    const radius = 6;
    const margin = { top: 18, right: 6, bottom: 22, left: 5 };
    const width = 500;
    const height = 64;

    // Clear previous render
    d3.select(divSelector).selectAll('*').remove();

    const svg = d3.select(divSelector)
        .append('svg')
        .attr('viewBox', `0 0 ${width} ${height}`);

    const innerW = width - margin.left - margin.right;
    const y = Math.round(height / 2);

    // Domain: now -> now + X months
    const now = new Date();
    const end = new Date(now);
    end.setMonth(end.getMonth() + monthsAhead);

    const scale = d3.scaleTime()
        .domain([now, end])
        .range([0, innerW])
        .clamp(true);
    const colorScale = d3.scaleLinear()
        .domain([0, monthsAhead / 2, monthsAhead])
        .range([
            "#E74C3C",  // rot
            "#F39C12",  // orange
            "#2BB8A6"   // türkis
        ]);

    // Main group
    const g = svg.append('g')
        .attr('transform', `translate(${margin.left}, 0)`);

    // add arrow head to the end of the line
    svg.append('defs').append('marker')
        .attr('id', 'arrowhead')
        .attr('viewBox', '-0 -5 10 10')
        .attr('refX', 5)
        .attr('refY', 0)
        .attr('markerWidth', 6)
        .attr('markerHeight', 6)
        .attr('orient', 'auto')
        .append('path')
        .attr('d', 'M0,-5L10,0L0,5')
        .attr('fill', 'currentColor');

    // Baseline
    let baseline = g.append('line')
        .attr('x1', 0)
        .attr('x2', innerW)
        .attr('y1', y)
        .attr('y2', y)
        .attr('stroke', 'currentColor')
        .attr('opacity', 0.35)
        .attr('stroke-width', 2)
        .attr('marker-end', 'url(#arrowhead)');

    // Start & end labels
    g.append('text')
        .attr('x', 0)
        .attr('y', y + 18)
        .attr('font-size', 11)
        .attr('opacity', 0.7)
        .text(startLabel);

    g.append('text')
        .attr('x', innerW)
        .attr('y', y + 18)
        .attr('text-anchor', 'end')
        .attr('font-size', 11)
        .attr('opacity', 0.7)
        .text(endLabel);

    // Sort deadlines by date 
    // deadlines: [{"title":"ERC Grant Deadline","date":"2026-03-31","type":"submission","id":"69a8765184c04d85e3082772"}]       
    const items = deadlines
        .filter(d => d?.date)
        .map(d => ({ ...d, _date: new Date(d.date) }))
        .sort((a, b) => a._date - b._date);

        if (items.length === 0) {
            // No deadlines - show a placeholder text
            g.append('text')
                .attr('x', innerW / 2)
                .attr('y', y-5)
                .attr('text-anchor', 'middle')
                .attr('font-size', 12)
                .attr('opacity', 0.5)
                .text('No upcoming deadlines');
            return;
        }

    // Optional: small "collision" offset when multiple points are very close.
    // This keeps "all on the line", but alternates a tiny vertical offset.
    const minPx = 10; // points closer than this get a small offset
    let lastX = -Infinity;
    let flip = 1;

    function computeColor(d) {
        const monthsAway = (d._date - now) / (1000 * 60 * 60 * 24 * 30);
        return colorScale(monthsAway);
    }

    function computeOffset(x) {
        if (Math.abs(x - lastX) < minPx) {
            flip *= -1;
            return flip * 6; // subtle offset
        }
        lastX = x;
        flip = 1;
        return 0;
    }

    const dots = g.append('g')
        .selectAll('g.dot')
        .data(items)
        .enter()
        .append('g')
        .attr('class', 'dot')
        .attr('transform', d => {
            const x = scale(d._date);
            const dy = computeOffset(x);
            return `translate(${x}, ${y + dy})`;
        })
        .style('cursor', 'pointer');

    // Hover popover
    function mouseover(d, i) {
        d3.select(this).select('circle')
            .transition().duration(150)
            .attr('r', radius + 2)
            .style('opacity', 1);

        $(this).popover({
            placement: 'auto top',
            container: divSelector,
            trigger: 'hover',
            html: true,
            content: function () {
                const info = typeInfo[d.type] ?? {};
                const color = computeColor(d);
                const icon = `<i class="ph ph-flag" style="color:${color}"></i>`;
                const title = d.title ?? 'No title';
                const dateStr = d._date?.toLocaleDateString?.() ?? '';
                const typeStr = info ? `<span style="color:${color}; font-weight:600;">${lang(info.en ?? d.type, info.de ?? null)}</span><br>` : '';
                return `${icon} <b>${title}</b><br>${typeStr} <span style="opacity:.7;">${dateStr}</span>`;
            }
        });
        $(this).popover('show');
    }

    function mouseout() {
        d3.select(this).select('circle')
            .transition().duration(150)
            .attr('r', radius)
            .style('opacity', 0.85);

        $('.popover').remove();
    }

    dots.on('mouseover', mouseover)
        .on('mouseout', mouseout)
        .on('click', (d) => {
            let url = ROOTPATH + '/deadlines/view/' + d.id;
            window.location.href = url;
        });

    // Dot circles
    dots.append('circle')
        .attr('r', radius)
        .attr('fill', d => computeColor(d))
        .attr('stroke', '#fff')
        .attr('stroke-width', 2)
        .style('opacity', 0.85)
        .attr('class', d => `deadline-dot ${d.type ?? ''}`);

    // Optional: show "today" marker
    g.append('line')
        .attr('x1', 0)
        .attr('y1', y - 5)
        .attr('x2', 0)
        .attr('y2', y + 5)
        .attr('stroke', 'currentColor')
        .attr('stroke-width', 2)
        .attr('opacity', 0.25);

}

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
