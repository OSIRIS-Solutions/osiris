$(function () {
    const source = document.getElementById('initiative-statistics-data');
    if (!source) return;
    const data = JSON.parse(source.textContent);
    const selector = document.getElementById('statistics-content-type');
    const chart = document.getElementById('initiative-statistics-chart');
    const empty = document.getElementById('initiative-statistics-chart-empty');
    function drawChart() {
        const type = selector.value;
        const rows = data.rows.filter(row => row.counts[type] > 0)
            .sort((a, b) => b.counts[type] - a.counts[type]).slice(0, 20).reverse();
        chart.hidden = rows.length === 0;
        empty.hidden = rows.length !== 0;
        if (!rows.length) return;
        const color = getComputedStyle(document.body).getPropertyValue('--link-color').trim() || '#008083';
        Plotly.react(chart, [{
            type: 'bar', orientation: 'h',
            x: rows.map(row => row.counts[type]), y: rows.map((row, index) => String(index)),
            customdata: rows.map(row => [row.name, row.parent]),
            marker: { color }, hovertemplate: '%{customdata[0]}<br>%{x}<extra></extra>'
        }], {
            height: Math.max(280, rows.length * 38 + 80),
            margin: { l: 20, r: 30, t: 20, b: 50 },
            paper_bgcolor: 'transparent', plot_bgcolor: 'transparent',
            font: { family: getComputedStyle(document.body).fontFamily, color: getComputedStyle(document.body).color },
            xaxis: { title: data.types[type].label, rangemode: 'tozero', dtick: Math.max(...rows.map(row => row.counts[type])) <= 5 ? 1 : undefined, tickformat: ',d' },
            yaxis: { automargin: true, tickmode: 'array', tickvals: rows.map((row, index) => String(index)), ticktext: rows.map(row => row.parent ? `${row.name} (${row.parent})` : row.name) }
        }, { responsive: true, displaylogo: false });
    }
    selector.value = Object.keys(data.types).find(type => data.rows.some(row => row.counts[type] > 0)) || selector.value;
    selector.addEventListener('change', drawChart);
    drawChart();
    $('#initiative-statistics-overview').DataTable({ order: [], buttons: downloadTableButtons(data.title) });
    const evidence = document.getElementById('initiative-statistics-evidence');
    let detailTable;
    evidence.addEventListener('toggle', () => {
        if (!evidence.open) return;
        if (!detailTable) detailTable = $('#initiative-statistics-details').DataTable({ order: [], buttons: downloadTableButtons(data.title) });
        else detailTable.columns.adjust();
    });
});
