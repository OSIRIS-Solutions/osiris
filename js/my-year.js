function timeline(year, quarter, typeInfo, events, clickEvent = false) {
    var radius = 3,
        distance = 12,
        divSelector = '#timeline'

    var margin = {
        top: 8,
        right: 25,
        bottom: 30,
        left: 25
    },
        width = 600,
        // height = (distance * types.length) + margin.top + margin.bottom;
        height = distance + margin.top + margin.bottom;


    var svg = d3.select(divSelector).append('svg')
        .attr("viewBox", `0 0 ${width} ${height}`)

    width = width - margin.left - margin.right
    height = height - margin.top - margin.bottom;

    var timescale = d3.scaleTime()
        .domain([new Date(year, 0, 1), new Date(year, 12, 1)])
        .range([0, width]);

    // var types = Object.keys(typeInfo)
    // let ordinalScale = d3.scaleOrdinal()
    //     .domain(types.reverse())
    //     .range(Array.from({
    //         length: types.length
    //     }, (x, i) => i * (height / (types.length - 1))));


    // let axisLeft = d3.axisLeft(ordinalScale);
    // svg.append('g').attr('class', 'axes')
    //     .attr('transform', `translate(${margin.left}, ${margin.top})`)
    //     .call(axisLeft);

    var axisBottom = d3.axisBottom(timescale)
        .ticks(12)
    // .tickPadding(5).tickSize(20);
    svg.append('g').attr('class', 'axes')
        .attr('transform', `translate(${margin.left}, ${height + margin.top + radius * 2})`)
        .call(axisBottom);

    if (quarter > 0) {
        var quarterBox = svg.append('g')
            .attr('transform', `translate(${margin.left}, ${height + margin.top + radius * 2})`)
            // .selectAll("g")
            .append('rect')
            .style("fill", 'rgb(236, 175, 0)')
            // .attr('height', height+margin.top+radius*4)
            .attr('height', 8)
            .attr('width', function (d, i) {
                return width / 4
            })
            .style('opacity', .2)
            .attr('x', (d) => {
                var Q = quarter * 3 - 2;
                var date = new Date(`${year}-${Q}-01`)
                return timescale(date)
            })
            // .attr('y', radius*-2)
            .attr('y', 0)
    }

    d3.selectAll("g>.tick>text")
        .each(function (d, i) {
            d3.select(this).style("font-size", "8px");
        });

    // var Tooltip = d3.select(divSelector)
    //     .append("div")
    //     .style("opacity", 0)
    //     .attr("class", "tooltip")
    //     .style("background-color", "white")
    //     .style("border", "solid")
    //     .style("border-width", "2px")
    //     .style("border-radius", "5px")
    //     .style("padding", "5px")


    function mouseover(d, i) {

        d3.select(this)
            .select('circle,rect')
            .transition()
            .duration(300)
            .style('opacity', 1)

        //Define and show the tooltip over the mouse location
        $(this).popover({
            placement: 'auto top',
            container: divSelector,
            mouseOffset: 10,
            followMouse: true,
            trigger: 'hover',
            html: true,
            content: function () {
                var icon = '';
                if (typeInfo[d.type]) {
                    if (typeInfo[d.type].icon) {
                        icon = `<i class="ph ph-${typeInfo[d.type].icon}" style="color:${typeInfo[d.type].color}"></i>`
                    } else {
                        icon = `<b style="color:${typeInfo[d.type].color}"><i class="ph ph-calendar"></i> ${d.type}</b><br>`; // Fallback icon
                    }
                }
                return `${icon} ${d.title ?? 'No title available'}`
            }
        });
        $(this).popover('show');
    } //mouseoverChord

    //Bring all chords back to default opacity
    function mouseout(event, d) {
        d3.select(this).select('circle,rect')
            .transition()
            .duration(300)
            .style('opacity', .5)
        //Hide the tooltip
        $('.popover').each(function () {
            $(this).remove();
        });
    }

    var dots = svg.append('g')
        .attr('transform', `translate(${margin.left}, ${margin.top})`)
        .selectAll("g")
        .data(events)
        .enter().append("g")
        .attr('transform', function (d, i) {
            var date = new Date(d.starting_time * 1000)
            var x = timescale(date)
            // var y = ordinalScale(d.type) //(typeInfo[d.type]['index'] * -(radius * 2)) + radius
            var y = 1
            return `translate(${x}, ${y})`
        })

    dots.on("mouseover", mouseover)
        // .on("mousemove", mousemove)
        .on("mouseout", mouseout)

    if (clickEvent) {
        dots.on("click", clickEvent)
            .style("cursor", "pointer")
    }

    if (quarter > 0) {
        dots.on("click", (d) => {
            // $('tr.active').removeClass('active')
            var element = document.getElementById("tr-" + d.id);
            // element.className="active"
            var headerOffset = 60;
            var elementPosition = element.getBoundingClientRect().top;
            var offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: "smooth"
            });
        });
    }
    // .style("stroke", "gray")

    var circle = dots.append('circle')
        .style("fill", function (d, i) {
            if (d.ending_time !== undefined) return 'transparent'
            return typeInfo[d.type]['color']
        })
        .attr("r", radius)
        .attr('cy', (d) => Math.random() * distance - distance / 2)
        .style('opacity', .6)
        .attr('class', (d) => `event-circle ${d.type}`);

    var lines = dots.append('rect')
        .style("fill", function (d, i) {
            if (d.ending_time === undefined) return 'transparent'
            return typeInfo[d.type]['color']
        })
        .attr('height', radius * 2)
        .attr('width', function (d, i) {
            if (d.ending_time === undefined) return 0
            // console.log(d);
            var date = new Date(d.starting_time * 1000)
            var x1 = timescale(date)
            var date = new Date(d.ending_time * 1000)
            var x2 = timescale(date)
            return Math.max(radius * 2, x2 - x1) // Ensure width is not negative
        })
        .style('opacity', .6)
        .attr('rx', 3)
        // .attr('y', -radius)
        .attr('y', (d) => Math.random() * distance - distance / 2)
}