// export { Graph }

function Graph(graph, selector, width = 800, height = 500) {

    // color Scale for ED Annotation
    // var color = d3.scaleOrdinal(d3.schemeCategory10);
    // color scale for the nodes depending on count
    var colors = d3.scaleSequential(d3.schemeCategory10)
        .domain([0, 100])
        .interpolator(d3.interpolateViridis);

    var zoom = d3.zoom()
        .scaleExtent([.2, 10])
        .on("zoom", function () {
            container.attr("transform", d3.event.transform)
        });

    const transform = d3.zoomIdentity.scale(0.5, .5 * width, .5 * height)
    // const svgdiv = d3.select(selector)
    // const svgdiv = d3.create('div')
    // var colors = d3.scaleOrdinal()
    //     .domain(d3.range(graph.nodes.length))
    //     .range(colors);

    const svg = d3.select(selector).append("svg")
        .attr("width", width)
        .attr("height", height)
        .attr('class', 'box')
        .call(zoom)
    // .call(zoom.transform, transform)

    const container = svg.append("g")
    // .attr("transform", transform);

    var adjlist = [];
    graph.links.forEach(function (d) {
        // if (d.source.id === undefined) {
        var strST = d.source + "-" + d.target;
        var strTS = d.target + "-" + d.source;
        // } else {
        //     var strST = d.source.id + "-" + d.target.id;
        //     var strTS = d.target.id + "-" + d.source.id;
        // }
        adjlist[strST] = true;
        adjlist[strTS] = true;
    });

    var nodeById = d3.map();

    graph.nodes.forEach(function (node) {
        nodeById.set(node.id, node);
    });

    var force = d3.forceSimulation(graph.nodes)
        .force("charge",  d3.forceManyBody().strength(-500).distanceMax(200).distanceMin(15))
        .force("link", d3.forceLink(graph.links).id(d => d.id).strength(1))
        .force("x", d3.forceX())
        .force("y", d3.forceY())
        .force('collide', d3.forceCollide((d) => Math.sqrt(d.value) * 4 + 4))
        .force("center", d3.forceCenter(width / 2, height / 2))
        .on("tick", ticked);


    function dragstarted(d) {
        d3.event.sourceEvent.stopPropagation();
        if (!d3.event.active) force.alphaTarget(0.3).restart();
        d.fx = d.x;
        d.fy = d.y;
    }

    function dragged(d) {
        d.fx = d3.event.x;
        d.fy = d3.event.y;
    }

    function dragended(d) {
        if (!d3.event.active) force.alphaTarget(0);
        d.fx = null;
        d.fy = null;
    }

    function dblclick(d) {
        if (!d3.event.active) force.alphaTarget(0.5);
        d.fx = null;
        d.fy = null;
    }

    const link = container.selectAll(".link")
        .data(graph.links).enter()
        .append('path')
        .attr('d', function (d) {
            return 'M ' + d.source.x + ' ' + d.source.y + ' L ' + d.target.x + ' ' + d.target.y
        })
        .attr("stroke-width", function (d) {
            return Math.sqrt(d.value);
        })
        .attr("stroke", "#999")
        .attr("stroke-opacity", 0.6)
        .attr('id', function (d, i) { return 'edgepath' + i })
        .attr("class", "link")

    var edgepaths = container
        .selectAll(".edgepath")
        .data(graph.links)
        .enter()
        .append("path")
        .attr("class", "edgepath")
        .attr("fill-opacity", 0)
        .attr("stroke-opacity", 0)
        .attr("id", (d, i) => "edgepath" + i)
        .style("pointer-events", "none");

    var edgelabels = container
        .selectAll(".edgelabel")
        .data(graph.links)
        .enter()
        .append("text")
        .style("pointer-events", "none")
        .attr("class", "edgelabel")
        .attr("id", (d, i) => "edgelabel" + i)
        .attr("font-size", 8)
        .attr('dy', -4)
        .style("opacity", 0)
        .attr("fill", "#999")


    edgelabels
        .append("textPath")
        .attr("xlink:href", (d, i) => "#edgepath" + i)
        .style("text-anchor", "middle")
        .style("pointer-events", "none")
        .attr("startOffset", "50%")
        .text((d) => (d.value));



    var node = container.selectAll(".node")
        .data(graph.nodes)
        .enter().append("g")
        .attr("class", "node")
        .call(d3.drag()
            .on("start", dragstarted)
            .on("drag", dragged)
            .on("end", dragended));

    node.append('rect')
        .style("stroke-width", "1")
        .style("stroke", "white")
        .attr('fill', (d) => (colors(d.value)))
        .attr("width", function (d) {
            if (d.group == 2) return 10;
            return Math.sqrt(d.value)*2 + 4;
        })
        .attr("height", function (d) {
            if (d.group == 2) return 10;
            return Math.sqrt(d.value)*2 + 4;
        })
        .attr("rx", (d) => (d.group == 2 ? 1: 100))
        .attr("ry", (d) => (d.group == 2 ? 1: 100))
        .attr("x", function (d) {
            if (d.group == 2) return -5;
            return -Math.sqrt(d.value)*2 / 2 - 2;
        })
        .attr("y", function (d) {
            if (d.group == 2) return -5;
            return -Math.sqrt(d.value)*2 / 2 - 2;
        });
        

    node.on("mouseover", focus).on("mouseout", unfocus);

    var nodelabel = node.append("text")
        .style("pointer-events", "none")
        .attr("class", "icons")
        .attr('text-anchor', 'left')
        .attr('dx', 15)
        .attr('dominant-baseline', 'middle')
        .style("font-family", "Rubik")
        .text(function (d) {
            if (d.group == 2) return null;
            return d.name
        })
    var nodenumber = node.append("text")
        .style("pointer-events", "none")
        .attr("class", "numbers")
        .attr('text-anchor', 'middle')
        .attr('dx', 0)
        .attr('dominant-baseline', 'middle')
        .style("font-family", "Rubik")
        .style("font-size", (d) => (Math.sqrt(d.value)*.8 +3))
        .style("font-weight", "bold")
        .style("fill", "white")
        .style("opacity", 0)
        .text(function (d) {
            if (d.group == 2) return null;
            return d.value
        })

    function ticked() {
        link.attr("d", function (d) {
            return "M" + d.source.x + "," + d.source.y + "L" + (d.target.x) + "," + (d.target.y);
        });

        node.attr("transform", function (d) {
            return "translate(" + d.x + "," + d.y + ")";
        });

        edgepaths.attr('d', (d) => {
            return "M" + d.source.x + "," + d.source.y + "L" + (d.target.x) + "," + (d.target.y);
        });

        edgelabels.attr('transform', (d) => {
            if (d.target.x < d.source.x) {
                var x = (d.target.x + d.source.x) / 2
                var y = (d.target.y + d.source.y) / 2
                return 'rotate(180, ' + x + ', ' + y + ')';
            }
            return 'rotate(0)';
        });


    };
    function focus(d) {
        var id = d3.select(d3.event.target).datum().id;
        var rea_id = d3.select(d3.event.target).datum().rea_id
        if (rea_id !== undefined) {
            $("#" + rea_id).siblings().addClass("fadeout")
        }
        node.style("opacity", function (o) {
            return neigh(id, o.id) ? 1 : 0.2;
        });
        nodelabel.style("opacity", function (o) {
            return neigh(id, o.id) ? 1 : 0;
        });
        nodenumber.style("opacity", function (o) {
            return neigh(id, o.id) ? 1 : 0;
        });

        link.style("opacity", function (o) {
            return o.source.id == id || o.target.id == id ? 1 : 0.1;
        });
        edgelabels.style("opacity", function (o) {
            return o.source.id == id || o.target.id == id ? 1 : 0.1;
        });
    }

    function unfocus() {
        $("tr").removeClass("fadeout")
        // node.style("opacity", function (o) {
        //     var lvl = o.level-1
        //     return 1 - (lvl*.2)
        // })
        link.style("opacity", 0.7);
        node.style("opacity", 1);
        nodelabel.style("opacity", function (o) {
            if (o.level > 1) return 0;
            return 1
        })
        edgelabels.style("opacity", 0)
        nodenumber.style("opacity", 0)

    }

    function neigh(a, b) {
        return a == b || adjlist[a + "-" + b];
    }
    // if ("0" != 0) {
    //     legend = svg.append("g")
    //         .attr("class", "legendLinear")
    //         .attr("transform", "translate(20,30)")

    //     legend.append("rect")
    //         .attr("x", -5)
    //         .attr("y", -20)
    //         .attr("width", 100)
    //         .attr("height", 135)
    //         .attr("fill", '#ffffff')
    //     legend
    //         .call(legendLinear);

    //     d3.selectAll(".swatch")
    //         .style("stroke", "black")
    // }

    // return svg.node()
}