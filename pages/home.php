<?php
// say good morning or good afternoon or good evening depending on the time of day
$hour = date('H');
if ($hour < 10) {
    $greeting = lang('Good morning,', 'Guten Morgen,');
    $icon = 'sun-horizon';
} elseif ($hour < 18) {
    $greeting = lang('Good afternoon,', 'Guten Tag,');
    $icon = 'sun';
} else {
    $greeting = lang('Good evening,', 'Guten Abend,');
    $icon = 'moon';
}
// random welcome message
$welcome_messages = [
    lang('Welcome back.', 'Willkommen zurück.'),
    lang('Good to see you again.', 'Schön, dich wiederzusehen.'),
    lang('Here is an overview of your research activities.', 'Hier findest du eine Übersicht deiner Forschungsaktivitäten.'),
    lang('Let\'s see what\'s new.', 'Schauen wir, was es Neues gibt.'),
    lang('Your dashboard is ready.', 'Dein Dashboard ist bereit.'),
    lang('Everything important at a glance.', 'Alles Wichtige auf einen Blick.'),
    lang('Here is what\'s happening in OSIRIS.', 'Hier siehst du, was in OSIRIS passiert.'),
    lang('Take a look at the current research activities.', 'Wirf einen Blick auf die aktuellen Forschungsaktivitäten.'),
    lang('Stay up to date with your research information.', 'Bleibe über deine Forschungsinformationen auf dem Laufenden.'),
    lang('Your latest updates are waiting for you.', 'Deine neuesten Aktualisierungen warten auf dich.'),
    lang('Let\'s continue where you left off.', 'Mach dort weiter, wo du aufgehört hast.'),
    lang('Welcome to your research dashboard.', 'Willkommen auf deinem Forschungs-Dashboard.'),
];
$welcome = $welcome_messages[array_rand($welcome_messages)];


$Q = CURRENTQUARTER - 1;
$Y = CURRENTYEAR;
if ($Q < 1) {
    $Q = 4;
    $Y -= 1;
}
$lastquarter = $Y . "Q" . $Q;

?>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>


<style>
    #home .row.row-eq-spacing .box {
        margin-top: 0;
    }

    h1 {
        font-size: 2.4rem;
        margin-bottom: 0;
    }

    h1 i.ph-moon {
        color: #7f8c8d;
    }

    h1 i.ph-sun-horizon {
        color: #f39c12;
    }

    h1 i.ph-sun {
        color: #f1c40f;
    }

    p.welcome-text {
        color: var(--muted-color);
        margin-top: 0;
    }

    .link-sm {
        font-size: 1.2rem;
        display: inline-flex;
        align-items: center;
    }

    .link-sm::after {
        content: '\E13A';
        font-family: var(--icon-font);
        font-size: 1.2em;
        margin-left: 0.25rem;
    }

    .widget-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        margin-top: 1rem;
    }

    .widget-header h2 {
        font-size: 1.8rem !important;
        margin: 0;
    }

    .timeline-empty {
        gap: 1rem;
        color: var(--muted-color, #6c757d);
        /* text-align: center; */
    }

    .link-block {
        display: block;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        transition: background-color 0.15s ease;
        color: inherit;
        margin: 0 -1rem;
    }

    .link-block:hover {
        background-color: var(--blue-color-10);
        text-decoration: none;
    }

    .link-block-title {
        font-weight: 500;
        font-family: var(--header-font);
        /* shorten */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .colleague {
        display: flex;
        align-items: center;
    }

    .colleague-img {
        /* clip image */
        width: 50px;
        height: 50px;
        border-radius: 50%;
        /* border-radius: var(--border-radius); */
        object-fit: cover;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    #announcement {
        /* display: flex;
        align-items: center;
        justify-content: space-between;*/
        /* // box mb-0 border-primary primary */
        background-image: url('<?= ROOTPATH ?>/img/sophie/sophie-banner-notification.png');
        background-size: contain;
        background-repeat: no-repeat;
        background-position: right;
        position: relative;
        padding: 1rem 20rem 1rem 1.5rem;
        --border-color: #3ca39d;
        --primary-color: #3ca39d;
        --primary-color-dark: #31716b;
        --primary-color-20: rgba(60, 163, 157, 0.2);
        --primary-color-30: rgba(60, 163, 157, 0.3);
    }

    .empty-state img {
        max-height: 12rem;
        opacity: 0.8;
    }

    .empty-state p {
        margin: 0;
        color: var(--muted-color);
        font-size: small;
    }

    #announcement .dismiss-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        color: var(--primary-color);
    }

    #announcement img#sophie-announcement {
        max-width: 120px;
        margin: -1rem 2rem -1rem 0;
    }

    #announcement h1,
    #announcement h2,
    #announcement h3,
    #announcement h4,
    #announcement h5,
    #announcement h6 {
        color: var(--primary-color);
        margin-top: 0;
    }

    #announcement h1 {
        font-size: 2.2rem;
    }

    #announcement h2 {
        font-size: 1.8rem;
    }

    #announcement h3 {
        font-size: 1.6rem;
    }

    #announcement p {
        background-color: #ffffffbf;
    }

    /* make sure that links in tables do not exceed the table width */
    .table a {
        word-break: break-word;
    }

    #home {
        margin-bottom: 2rem;
    }


    .conference-item {
        padding: 1.2rem 0;
        border-bottom: 1px solid var(--border-color, #ddd);
    }

    .conference-item:last-child {
        border-bottom: 0;
    }

    .conference-header {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
    }

    .conference-time {
        color: var(--muted-color);
    }

    .conference-link {
        margin-left: .25rem;
        opacity: .75;
    }

    .conference-actions {
        display: flex;
        gap: .6rem;
        white-space: nowrap;
        font-size: 1.2rem;
    }

    .conference-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem .9rem;
        color: var(--muted-color);
        font-size: 1rem;
    }

    .conference-meta span {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }

    .conference-footer {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .5rem;
    }

    .conference-status {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .55rem;
        border: 1px solid var(--border-color);
        border-radius: .5rem;
        color: var(--muted-color);
        text-decoration: none;
        cursor: pointer;
        font-size: 1rem;
        background: #fff;
    }

    .conference-status span {
        font-weight: 700;
    }

    .conference-status.active,
    .conference-status.primary {
        color: var(--blue-color);
        border-color: currentColor;
        background: rgba(0, 114, 188, .08);
    }

    .link-block .info {
        line-height: 1.2;
        display: block;
        margin-top: .25rem;
        color: var(--muted-color);
        font-size: 1.2rem;
    }



    .notification-list a {
        display: flex;
        align-items: center;
        transition: background-color 0.15s ease;
        padding: 0.5rem 1rem;
        background-color: white;
        border-radius: var(--border-radius);
        margin: 0 -.5rem;
    }

    .notification-list a:hover {
        background-color: var(--blue-color-10);
    }

    .notification-list a i {
        display: flex;
        -ms-flex-align: center;
        align-items: center;
        -ms-flex-pack: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        font-size: 2rem;
        margin-right: 1rem;
        color: var(--blue-color);
        background-color: var(--blue-color-20);
        border: none;
        border-radius: var(--border-radius);
        -webkit-transition: box-shadow 0.5s, background-color 0.5s, color 0.5s;
        transition: box-shadow 0.5s, background-color 0.5s, color 0.5s;
        flex-shrink: 0;
    }

    .notification-list a .index {
        margin-left: auto;
        background-color: var(--blue-color-20);
        color: var(--blue-color);
        border-radius: 999px;
        padding: 0 6px;
        font-size: 1.2rem;
        font-weight: bold;
        min-width: 2rem;
        text-align: center;
    }

    .news-item .badge.type {
        font-size: 1rem;
        font-weight: bold;
        /* float: right; */
    }

    <?php foreach ($Vocabulary->getValues('news-category') as $key => $val) {
        echo '.news-item .type.' . e($val['id']) . ' {
        background-color: ' . DB::$colors[$key] . '20;
        color: ' . DB::$colors[$key] . ';
    }
    ';
    } ?>.news-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        justify-content: space-between;
    }

    .img-teaser {
        width: 7rem;
        height: 7rem;
        object-fit: cover;
        border-radius: 8px;
        margin-top: .5rem;
    }
</style>

<main id="home">

    <!-- Welcome Section -->
    <div class="welcome">
        <h1>
            <?= $greeting ?> <?= $USER['first'] ?? $USER['displayname'] ?? 'User' ?>!
            <i class="ph-duotone ph-<?= $icon ?>"></i>
        </h1>

        <p class="welcome-text">
            <?= $welcome ?>
        </p>
    </div>
    <!-- End Welcome Section -->


    <div class="row row-eq-spacing">
        <div class="col">
            <!-- Announcement Section -->
            <?php
            $announcement_active = $Settings->isAnnouncementActive();
            if ($announcement_active) {
                $announcement = $Settings->get('announcement'); ?>
                <section>
                    <div class="box position-relative" id="announcement" data-announcement-version="<?= strtotime($announcement['updated_at'] ?? 'now') ?>">
                        <?php
                        $en = trim($announcement['en'] ?? null);
                        $de = trim($announcement['de'] ?? null);
                        if (!empty(strip_tags($en)) && !empty(strip_tags($de))) {
                            echo  lang($en, $de);
                        } elseif (!empty(strip_tags($en))) {
                            echo $en;
                        } elseif (!empty(strip_tags($de))) {
                            echo $de;
                        }
                        ?>
                        <button class="btn primary small" onclick="dismissAnnouncement()">
                            <i class="ph ph-x-circle"></i>
                            <?= lang('Don’t show again', 'Nicht mehr anzeigen') ?>
                        </button>
                        <button class="btn outline small" onclick="dismissAnnouncementSession()">
                            <i class="ph ph-clock"></i>
                            <?= lang('Remind me later', 'Später erinnern') ?>
                        </button>
                    </div>
                    <script>
                        function dismissAnnouncement() {
                            fetch('<?= ROOTPATH ?>/crud/users/dismiss-announcement', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            }).then(() => {
                                document.getElementById('announcement').remove();
                            });
                        }

                        function dismissAnnouncementSession() {
                            const announcementVersion = $('#announcement').data('announcement-version');
                            localStorage.setItem('osiris_hide_announcement_hidden_' + announcementVersion, Date.now() + 24 * 60 * 60 * 1000);
                            document.getElementById('announcement').remove();
                        }
                        $(document).ready(function() {
                            const announcementVersion = $('#announcement').data('announcement-version');
                            const hideUntil = localStorage.getItem('osiris_hide_announcement_hidden_' + announcementVersion);
                            if (hideUntil && Date.now() < hideUntil) {
                                $('#announcement').remove();
                            }
                        });
                    </script>
                </section>
            <?php

            }

            ?>


            <?php if ($Settings->featureEnabled('events', false)) {
                $title = lang('Upcoming events', 'Kommende Ereignisse');
                if ($Settings->featureEnabled('deadlines', false)) {
                    $title = lang('Upcoming events & deadlines', 'Kommende Ereignisse & Fristen');
                }
            ?>
                <div class="box padded">
                    <div class="widget-header">
                        <h2>
                            <i class="ph-duotone ph-calendar"></i>
                            <?= $title ?>
                        </h2>
                        <a href="<?= ROOTPATH ?>/events" class="link-sm">
                            <?= lang('View all', 'Zeige alle') ?>
                        </a>
                    </div>
                    <div id="timeline"></div>
                </div>
                <script>
                    function renderTimeline(apiData, divSelector = '#timeline') {
                        apiData = apiData.data;
                        const events = apiData.events || [];
                        const types = apiData.types || [];
                        const info = apiData.info || {};
                        const today = new Date();

                        const data = events.map(d => ({
                            ...d,
                            _date: new Date(d.starting_time * 1000),
                            _end: d.ending_time ? new Date(d.ending_time * 1000) : null
                        })).sort((a, b) => a._date - b._date);
                        if (!data.length) {
                            d3.select(divSelector)
                                .html(`
                                    <div class="timeline-empty">
                                            <p>${lang('There are no events in the next 6 months.', 'In den nächsten 6 Monaten steht aktuell nichts an.')}</p>
                                    </div>
                                `);
                            return;
                        }

                        const typeInfoAll = info || {};
                        const typeInfo = {
                            ...(typeInfoAll.event || {}),
                            ...(typeInfoAll.deadline || {})
                        };

                        const margin = {
                            top: 30,
                            right: 30,
                            bottom: 45,
                            left: 30
                        };
                        const width = 900;
                        const height = 140;
                        const radius = 7;

                        // const minDate = d3.min(data, d => d._date);
                        // const maxDate = d3.max(data, d => d._end || d._date);
                        // maxDate in 6 months
                        const minDate = d3.timeDay.offset(new Date(), -3);
                        const maxDate = d3.timeDay.offset(new Date(), 180);

                        const x = d3.scaleTime()
                            .domain([
                                today,
                                d3.timeDay.offset(maxDate, 7)
                            ])
                            .range([margin.left, width - margin.right]);

                        const color = d3.scaleOrdinal()
                            .domain(types)
                            .range(['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f43f5e', '#6366f1']);

                        function computeColor(d) {
                            if (d.category === 'deadline') return '#d97706';
                            return color(d.type);
                        }

                        const svg = d3.select(divSelector)
                            .html('')
                            .append('svg')
                            .attr('viewBox', `0 0 ${width} ${height}`)
                            .attr('width', '100%')
                            .attr('height', height);

                        // Axis line
                        svg.append('line')
                            .attr('x1', margin.left)
                            .attr('x2', width - margin.right)
                            .attr('y1', 80)
                            .attr('y2', 80)
                            .attr('stroke', '#cbd5e1')
                            .attr('stroke-width', 2);

                        // Today marker
                        svg.append('line')
                            .attr('x1', x(today))
                            .attr('x2', x(today))
                            .attr('y1', 55)
                            .attr('y2', 105)
                            .attr('stroke', '#94a3b8')
                            .attr('stroke-dasharray', '4 4');

                        svg.append('text')
                            .attr('x', x(today))
                            .attr('y', 48)
                            .attr('text-anchor', 'middle')
                            .attr('fill', '#64748b')
                            .attr('font-size', 12)
                            .text(lang('Today', 'Heute'));

                        // Month labels
                        const monthFormat = d => {
                            const month = d3.timeFormat('%b')(d);
                            const year = d3.timeFormat('%Y')(d);

                            return d.getMonth() === 0 ? `${month} ${year}` : month;
                        };

                        svg.append('g')
                            .attr('class', 'timeline-axis')
                            .attr('transform', `translate(0, 80)`)
                            .call(
                                d3.axisBottom(x)
                                .ticks(d3.timeMonth.every(1))
                                .tickFormat(monthFormat)
                                .tickSize(35)
                            )
                            .call(g => g.select('.domain').remove())
                            .call(g => g.selectAll('line')
                                .attr('stroke', '#cbd5e1')
                            )
                            .call(g => g.selectAll('text')
                                .attr('fill', '#64748b')
                                .attr('font-size', 12)
                                .attr('dy', '1.2em')
                            );


                        const items = svg.selectAll('.timeline-item')
                            .data(data)
                            .enter()
                            .append('g')
                            .attr('class', d => `timeline-item ${d.category}`)
                            .attr('transform', d => `translate(${x(d._date)}, 80)`)
                            .style('cursor', 'pointer')
                            .on('mouseover', mouseover)
                            .on('mouseout', mouseout)
                            .on('click', (d) => {
                                window.location.href = d.category === 'event' ?
                                    `/conferences/view/${d.id}` :
                                    `/deadlines/view/${d.id}`;
                            });

                        // Event duration line
                        items.filter(d => d.category === 'event' && d._end)
                            .append('line')
                            .attr('x1', 0)
                            .attr('x2', d => Math.max(12, x(d._end) - x(d._date)))
                            .attr('y1', 0)
                            .attr('y2', 0)
                            .attr('stroke', d => computeColor(d))
                            .attr('stroke-width', 5)
                            .attr('stroke-linecap', 'round')
                            .style('opacity', 0.25);

                        // Events: circle
                        items.filter(d => d.category === 'event')
                            .append('circle')
                            .attr('r', radius)
                            .attr('fill', d => computeColor(d))
                            .style('opacity', 0.85);

                        // Deadlines: diamond
                        items.filter(d => d.category === 'deadline')
                            .append('path')
                            .attr('d', d3.symbol().type(d3.symbolDiamond).size(170))
                            .attr('fill', d => computeColor(d))
                            .style('opacity', 0.9);

                        // Decide which labels should be visible
                        const sortedForLabels = [...data].sort((a, b) => {
                            if (a.category === b.category) return a._date - b._date;
                            return a.category === 'deadline' ? -1 : 1;
                        });

                        const visibleLabels = new Set();
                        const labelPositions = [];
                        const minLabelDistance = 110;

                        sortedForLabels.forEach(d => {
                            const pos = x(d._date);
                            const overlaps = labelPositions.some(p => Math.abs(p - pos) < minLabelDistance);

                            if (!overlaps) {
                                visibleLabels.add(d.id);
                                labelPositions.push(pos);
                            }
                        });

                        // Labels for selected items only
                        items.append('text')
                            .attr('class', 'timeline-label')
                            .attr('y', d => d.category === 'deadline' ? 28 : -18)
                            .attr('text-anchor', 'middle')
                            .attr('font-size', 11)
                            .attr('fill', '#334155')
                            .text(d => truncate(d.title, 24))
                            .style('display', d => visibleLabels.has(d.id) ? null : 'none');

                        function truncate(str, max) {
                            return str && str.length > max ? str.substring(0, max - 1) + '…' : str;
                        }

                        function mouseover(d, i) {
                            const marker = d3.select(this).select('circle,path');

                            marker.transition().duration(150)
                                .attr('transform', d.category === 'deadline' ? 'scale(1.25)' : null)
                                .attr('r', d.category === 'event' ? radius + 2 : null)
                                .style('opacity', 1);

                            $(this).popover({
                                placement: 'auto top',
                                container: divSelector,
                                trigger: 'hover',
                                html: true,
                                content: function() {
                                    const info = typeInfo[d.type] ?? {};
                                    const color = computeColor(d);
                                    const iconClass = d.category === 'deadline' ? 'ph ph-flag' : 'ph ph-calendar';
                                    const icon = `<i class="${iconClass}" style="color:${color}"></i>`;
                                    const title = d.title ?? 'No title';
                                    const dateStr = formatDateRange(d);
                                    const typeStr = info ?
                                        `<span style="color:${color}; font-weight:600;">${lang(info.en ?? d.type, info.de ?? null)}</span><br>` :
                                        '';

                                    return `${icon} <b>${title}</b><br>${typeStr}<span style="opacity:.7;">${dateStr}</span>`;
                                }
                            });

                            $(this).popover('show');
                        }

                        function mouseout(d, i) {
                            const marker = d3.select(this).select('circle,path');

                            marker.transition().duration(150)
                                .attr('transform', null)
                                .attr('r', d.category === 'event' ? radius : null)
                                .style('opacity', d.category === 'deadline' ? 0.9 : 0.85);

                            $('.popover').remove();
                        }

                        function formatDateRange(d) {
                            const start = d._date.toLocaleDateString();
                            if (!d._end) return start;

                            const end = d._end.toLocaleDateString();
                            return start === end ? start : `${start} – ${end}`;
                        }
                    }

                    $(document).ready(function() {
                        $.getJSON('<?= ROOTPATH ?>/api/dashboard/upcoming-events', function(apiData) {
                            console.log(apiData);
                            renderTimeline(apiData, '#timeline');
                        });
                    });
                </script>
            <?php } ?>



            <?php if (($Settings->featureEnabled('quarterly-reporting', true) && $Settings->hasPermission('report.dashboard'))) { ?>

                <section class="home-widget- stack">

                    <?php if (($Settings->featureEnabled('quarterly-reporting', true) && $Settings->hasPermission('report.dashboard'))) {
                        $n_scientists = $osiris->persons->count(["roles" => 'scientist', "is_active" => true]);
                        $n_approved = $osiris->persons->count(["roles" => 'scientist', "is_active" => true, "approved" => $lastquarter]);
                        $progress = ($n_scientists > 0) ? ($n_approved / $n_scientists) * 100 : 0;
                    ?>
                        <div class="box padded">
                            <div class="widget-header">
                                <h2>
                                    <i class="ph-duotone ph-chart-pie"></i>
                                    <?= lang('Last quarter', 'Vergangenes Quartal') ?>
                                </h2>
                                <button class="btn small" onclick="loadModal('components/controlling-approved', {q: '<?= $Q ?>', y: '<?= $Y ?>'})">
                                    <i class="ph ph-magnifying-glass-plus"></i> <?= lang('Details') ?>
                                </button>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-10 font-size-12">
                                <span class="text-muted">
                                    <?= lang(
                                        "$n_approved of $n_scientists scientists have approved their activities.",
                                        "$n_approved von $n_scientists Wissenschaftler:innen haben ihre Aktivitäten freigegeben."
                                    ) ?>
                                </span>
                                <b class="badge success"><?= $lastquarter ?></b>
                            </div>
                        </div>
                    <?php } ?>
                </section>
            <?php } ?>


            <div class="row row-eq-spacing">
                <style>
                    .status-cards {
                        display: flex;
                        gap: 1rem;
                        flex-wrap: wrap;
                    }

                    .status-cards a {
                        flex: 1;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }

                    .status-cards div {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 3rem;
                        height: 3rem;
                        font-size: 1.5rem;
                        color: var(--blue-color);
                        background-color: var(--blue-color-20);
                        border-radius: var(--border-radius);
                        position: relative;
                    }

                    .status-cards .index {
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        background-color: var(--danger-color);
                        color: white;
                        border-radius: 999px;
                        padding: 0 6px;
                        font-size: 1rem;
                        font-weight: bold;
                    }
                </style>


                <div class="col">
                    <div class="box padded">
                        <div class="widget-header">
                            <h2>
                                <i class="ph-duotone ph-clipboard-text"></i>
                                <?= lang('My tasks', 'Meine Aufgaben') ?>
                            </h2>
                        </div>
                        <?php
                        if ($has_notifications) { ?>
                            <div class="status-cards">
                                <?php
                                if (isset($notifications['activity'])) {
                                    $n_issues = $notifications['activity']['count'];
                                ?>
                                    <a href="<?= ROOTPATH ?>/issues">
                                        <div>
                                            <i class="ph ph-bell" aria-hidden="true"></i>
                                            <span class="index danger issue-counter"><?= $n_issues ?></span>
                                        </div>
                                        <?= lang('Issues', 'Hinweise') ?>
                                    </a>
                                <?php } ?>

                                <?php if (isset($notifications['approval'])) {
                                    $quarter = $notifications['approval']['key'];
                                ?>
                                    <a href="<?= ROOTPATH ?>/my-year/<?= $_SESSION['username'] ?>?quarter=<?= $quarter ?>">
                                        <div>
                                            <i class="ph ph-calendar-check" aria-hidden="true"></i>
                                        </div>
                                        <?= lang('Quarterly approval', 'Quartalsfreigabe') ?>
                                    </a>
                                <?php } ?>

                                <?php if (isset($notifications['queue'])) {
                                    $queue = $notifications['queue']['count'];
                                ?>
                                    <a href="<?= ROOTPATH ?>/queue/user">
                                        <div>
                                            <i class="ph ph-queue" aria-hidden="true"></i>
                                            <span class="index queue-counter"><?= $queue ?></span>
                                        </div>
                                        <?= lang('To review', 'Zu überprüfen') ?>
                                    </a>
                                <?php } ?>


                                <?php if ($notifications['reviews'] > 0) { ?>
                                    <a href="<?= ROOTPATH ?>/workflow-reviews">
                                        <div>
                                            <i class="ph ph-highlighter" aria-hidden="true"></i>
                                            <span class="index review-counter">0</span>
                                        </div>
                                        <?= lang('Reviews', 'Überprüfungen') ?>
                                    </a>
                                <?php } ?>

                                <?php if (isset($notifications['messages'])) {
                                    $n_messages = count($notifications['messages']);
                                ?>
                                    <a href="<?= ROOTPATH ?>/messages">
                                        <div>
                                            <i class="ph ph-envelope" aria-hidden="true"></i>
                                            <span class="index info message-counter"><?= $n_messages ?></span>
                                        </div>
                                        <?= lang('Messages', 'Nachrichten') ?>
                                    </a>
                                <?php } ?>

                            </div>

                        <?php } else { ?>
                            <div class="empty-state">
                                <div class="text-center">
                                    <img src="<?= ROOTPATH ?>/img/sophie/sophie-no-tasks.png" alt="" class="sophie-img">
                                    <p><?= lang('Here is currently nothing that requires your attention. Great work!', 'Hier gibt es momentan nichts, was deine Aufmerksamkeit erfordert. Großartige Arbeit!') ?></p>
                                </div>
                            </div>
                        <?php } ?>

                    </div>

                    <div class="box padded">
                        <div class="widget-header">
                            <h2>
                                <i class="ph-duotone ph-link"></i>
                                <?= lang('Quick links', 'Schnellzugriff') ?>
                            </h2>
                        </div>
                        <div class="notification-list">
                            <!-- Add Activities, projects and favorites here -->
                            <a href="<?= ROOTPATH ?>/add-activity" class="cta with-icon <?= $pageactive('add-activity') ?>">
                                <i class="ph ph-plus-circle mr-10" aria-hidden="true"></i>
                                <?= lang('Add activity', 'Aktivität hinzuf.') ?>
                            </a>
                            <?php if ($Settings->featureEnabled('projects') && $Settings->hasPermission('projects.add')) { ?>
                                <?php if ($Settings->canProposalsBeCreated()) { ?>
                                    <a href="<?= ROOTPATH ?>/proposals/new">
                                        <i class="ph ph-tree-structure"></i>
                                        <?= lang('Add project proposal', 'Projektantrag hinzuf.') ?>
                                    </a>
                                <?php } else if ($Settings->canProjectsBeCreated()) { ?>
                                    <a href="<?= ROOTPATH ?>/projects/new">
                                        <i class="ph ph-tree-structure"></i>
                                        <?= lang('Add project', 'Projekt hinzufügen') ?>
                                    </a>
                                <?php } ?>
                            <?php }
                            // Favorites from sidebar
                            echo $Sidebar->renderFav();
                            ?>
                        </div>
                        <?php
                        $links = $Settings->get('footer_links');
                        if (is_countable($links) && count($links) > 0) { ?>
                            <hr>
                            <div class="widget-header">
                                <h2>
                                    <i class="ph-duotone ph-link-simple-horizontal"></i>
                                    <?= lang('Useful links', 'Nützliche Links') ?>
                                </h2>
                            </div>
                            <div class="notification-list">
                                <?php
                                foreach ($links as $link) {
                                    if (isset($link['url']) && isset($link['name'])) { ?>
                                        <a href="<?= $link['url'] ?>" target="_blank" rel="noopener noreferrer">
                                            <i class="ph ph-arrow-square-out" aria-hidden="true"></i>
                                            <?= lang($link['name'], $link['name_de'] ?? null) ?>
                                        </a>
                                <?php }
                                } ?>
                            </div>
                        <?php } ?>


                    </div>
                </div>

                <?php if ($Settings->featureEnabled('events', true)) { ?>
                    <section class="col">
                        <div class="box padded">
                            <div class="widget-header">
                                <h2>
                                    <i class="ph-duotone ph-calendar-dots"></i>
                                    <?= lang('Events', 'Events') ?>
                                </h2>
                                <a href="<?= ROOTPATH ?>/conferences" class="link-sm">
                                    <?= lang('View all', 'Zeige alle') ?>
                                </a>
                            </div>

                            <?php
                            // conferences max past 3 month
                            $conferences = $osiris->conferences->find(
                                [
                                    '$or' => [
                                        ['end' => ['$gte' => date('Y-m-d', strtotime('-3 days'))], 'start' => ['$lte' => date('Y-m-d', strtotime('+6 month'))]],
                                        [
                                            'start' => ['$gte' => date('Y-m-d', strtotime('-6 month'))],
                                            '$or' => [
                                                ['participants' => $user],
                                                ['interests' => $user]
                                            ]
                                        ]
                                    ],
                                    'dismissed' => ['$ne' => $user]
                                ],
                                ['sort' => ['start' => 1]]
                            )->toArray();
                            if (count($conferences) == 0) { ?>
                                <div class="empty-state">
                                    <div class="text-center">
                                        <img src="<?= ROOTPATH ?>/img/sophie/sophie-no-events.png" alt="" class="sophie-img">
                                        <p><?= lang('Currently there are no upcoming events. Check back later!', 'Aktuell gibt es keine bevorstehenden Events. Schau später wieder vorbei!') ?></p>
                                    </div>
                                </div>
                            <?php } else {
                                $n_events = count($conferences);
                                foreach ($conferences as $n => $c) {
                                    $future = strtotime($c['end']) > time();

                                    $interests = DB::doc2Arr($c['interests'] ?? []);
                                    $participants = DB::doc2Arr($c['participants'] ?? []);

                                    $interest = in_array($user, $interests);
                                    $participate = in_array($user, $participants);

                                    $interestTooltip = $interest
                                        ? lang('Click to remove interest', 'Klicken um Interesse zu entfernen')
                                        : lang('Click to show interest', 'Klicken um Interesse zu zeigen');

                                    $participateTooltip = $participate
                                        ? lang('Click to remove participation', 'Klicken um Teilnahme zu entfernen')
                                        : lang('Click to show participation', 'Klicken um Teilnahme zu zeigen');
                                ?>

                                    <div class="conference-item <?= $n > 3 ? 'hidden' : '' ?>">

                                        <div class="conference-header">
                                            <div>
                                                <small class="conference-time">
                                                    <?= $future ? time_elapsed_string($c['start']) : time_elapsed_string($c['end']) ?>
                                                </small>
                                            </div>

                                            <div class="conference-actions">
                                                <a href="<?= ROOTPATH ?>/conference/ics/<?= $c['_id'] ?>"
                                                    data-toggle="tooltip"
                                                    data-title="<?= lang('Add to calendar', 'Zum Kalender hinzufügen') ?>">
                                                    <i class="ph ph-calendar-plus"></i>
                                                </a>

                                                <a class="text-danger"
                                                    onclick="conferenceToggle(this, '<?= $c['_id'] ?>', 'dismissed')"
                                                    data-toggle="tooltip"
                                                    data-title="<?= lang('Dismiss', 'Verwerfen') ?>">
                                                    <i class="ph ph-x"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <a class="link-block" href="<?= ROOTPATH ?>/conferences/view/<?= $c['_id'] ?>">
                                            <div class="link-block-title">
                                                <?= $c['title'] ?>
                                            </div>
                                            <div class="conference-meta">
                                                <span>
                                                    <i class="ph ph-calendar-blank"></i>
                                                    <?= fromToDate($c['start'], $c['end']) ?>
                                                </span>

                                                <?php if (!empty($c['location'] ?? null)) { ?>
                                                    <span>
                                                        <i class="ph ph-map-pin"></i>
                                                        <?= $c['location'] ?>
                                                    </span>
                                                <?php } ?>
                                            </div>
                                        </a>

                                        <div class="conference-footer">
                                            <?php if ($future) { ?>

                                                <a class="conference-status <?= $interest ? 'active' : '' ?>"
                                                    onclick="conferenceToggle(this, '<?= $c['_id'] ?>', 'interests')"
                                                    data-toggle="tooltip"
                                                    data-title="<?= $interestTooltip ?>">
                                                    <i class="<?= $interest ? 'ph-duotone' : 'ph' ?> ph-star"></i>
                                                    <span class="conference-count"><?= count($interests) ?></span>
                                                    <?= lang('Interested', 'Interessiert') ?>
                                                </a>

                                                <a class="conference-status <?= $participate ? 'active' : '' ?>"
                                                    onclick="conferenceToggle(this, '<?= $c['_id'] ?>', 'participants')"
                                                    data-toggle="tooltip"
                                                    data-title="<?= $participateTooltip ?>">
                                                    <i class="<?= $participate ? 'ph-duotone' : 'ph' ?> ph-check-circle"></i>
                                                    <span class="conference-count"><?= count($participants) ?></span>
                                                    <?= lang('Participants', 'Teilnehmer') ?>
                                                </a>

                                            <?php } else { ?>

                                                <a class="conference-status primary"
                                                    href="<?= ROOTPATH ?>/add-activity?conference=<?= $c['_id'] ?>">
                                                    <i class="ph ph-plus-circle"></i>
                                                    <?= lang('Add contribution', 'Beitrag hinzufügen') ?>
                                                </a>

                                            <?php } ?>
                                        </div>

                                    </div>

                                <?php } ?>

                                <?php if ($n_events > 4) { ?>
                                    <div class="text-center mt-10">
                                        <a onclick="$('.conference-item.hidden').removeClass('hidden'); $(this).parent().remove();" class="font-size-12">
                                            <?= lang('View all', 'Alle anzeigen') ?> (<?= $n_events - 4 ?> <?=lang('more', 'weitere')?>)
                                        </a>
                                    </div>
                                    <?php } ?>

                            <?php } ?>

                        </div>

                        <?php if ($Settings->featureEnabled('deadlines', false)) {
                        ?>

                            <div class="box padded">

                                <div class="widget-header">
                                    <h2>
                                        <i class="ph-duotone ph-flag"></i>
                                        <?= lang('Upcoming Deadlines', 'Bevorstehende Fristen') ?>
                                    </h2>
                                    <a href="<?= ROOTPATH ?>/deadlines" class="link-sm">
                                        <?= lang('View all', 'Zeige alle') ?>
                                    </a>
                                </div>
                                <?php
                                $deadlines = $osiris->deadlines->find(
                                    [
                                        '$and' => [
                                            ['date' => ['$gte' => date('Y-m-d', strtotime('-3 days'))]],
                                            ['date' => ['$lte' => date('Y-m-d', strtotime('+12 month'))]]
                                        ],
                                        // 'dismissed' => ['$ne' => $user],
                                        'roles' => ['$in' => $Settings->roles]
                                    ],
                                    ['sort' => ['date' => 1], 'projection' => [
                                        '_id' => 0,
                                        'id' => ['$toString' => '$_id'],
                                        'date' => 1,
                                        'type' => 1,
                                        'title' => 1
                                    ]]
                                )->toArray();
                                $deadlineTypes = $Vocabulary->getValues('deadline-type');
                                $deadlineTypes = array_column($deadlineTypes, null, 'id');
                                
                                $n_deadlines = count($deadlines);

                                if ($n_deadlines == 0) { ?>
                                    <div class="empty-state">
                                        <div class="text-center">
                                            <img src="<?= ROOTPATH ?>/img/sophie/sophie-no-deadlines.png" alt="" class="sophie-img">
                                            <p><?= lang('Currently there are no upcoming deadlines. Check back later!', 'Aktuell gibt es keine bevorstehenden Fristen. Schau später wieder vorbei!') ?></p>
                                        </div>
                                    </div>
                                <?php } else { 
                                    foreach ($deadlines as $n => $d) {
                                        $typeInfo = $deadlineTypes[$d['type']] ?? null;
                                    ?>
                                        <a href="<?= ROOTPATH ?>/deadlines/view/<?= $d['id'] ?>" class="link-block deadline <?= $n > 5 ? 'hidden': '' ?>">
                                            <div class="link-block-title">
                                                <?= $d['title'] ?>
                                            </div>
                                            <?php if ($typeInfo) { ?>
                                                <div class="info">
                                                    <?= time_elapsed_string($d['date']) ?>
                                                    &#x2219;
                                                    <?= lang($typeInfo['en'] ?? $d['type'], $typeInfo['de'] ?? null) ?>
                                                </div>
                                            <?php } ?>
                                        </a>
                                    <?php } ?>

                                    <?php if ($n_deadlines > 6) { ?>
                                        <div class="text-center mt-10">
                                            <a onclick="$('.deadline.hidden').removeClass('hidden'); $(this).parent().remove();" class="font-size-12">
                                                <?= lang('View all', 'Alle anzeigen') ?> (<?= $n_deadlines - 6 ?> <?=lang('more', 'weitere')?>)
                                            </a>
                                        </div>
                                        <?php } ?>

                                <?php } ?>
                            </div>
                        <?php } ?>
                    </section>
                <?php } ?>
            </div>

        </div>

        <?php if ($hasNews) { ?>
            <div class="col-md-6 col-lg-4">


                <?php if ($Settings->featureEnabled('quarterly-reporting', true) && isset($notifications['approval'])) {
                ?>
                    <section class="home-widget-">
                        <div class="box padded">
                            <div class="d-flex align-items-center">
                                <div>
                                    <b>
                                        <?= lang("You can now approve the past quarter", "Du kannst jetzt das vergangene Quartal freigeben") ?>
                                    </b>
                                    <p class="text-muted my-5 font-size-12">
                                        <?= lang(
                                            'To complete the quarterly review, please confirm that all activities from the previous quarter have been entered and are up to date.',
                                            'Für den Quartalsabschluss brauchen wir seine Bestätigung, dass alle Aktivitäten aus dem vergangenen Quartal erfasst und aktuell sind.'
                                        ) ?>
                                    </p>
                                    <a class="btn success filled" href="<?= ROOTPATH ?>/my-year/<?= $_SESSION['username'] ?>?quarter=<?= $quarter ?>">
                                        <?= lang('Review & Approve', 'Überprüfen & Freigeben') ?>
                                    </a>
                                </div>

                                <img src="<?= ROOTPATH ?>/img/sophie/sophie-checklist.png" class="w-100">
                            </div>
                        </div>
                    </section>
                <?php } ?>

                <?php if ($Settings->featureEnabled('news', true)) { ?>
                    <div class="box padded">
                        <div class="widget-header">
                            <h2>
                                <i class="ph-duotone ph-megaphone"></i>
                                <?= lang('News', 'News') ?>
                            </h2>
                            <a href="<?= ROOTPATH ?>/news" class="link-sm">
                                <?= lang('View all', 'Zeige alle') ?>
                            </a>
                        </div>
                        <?php foreach ($osiris->news->find(['date' => ['$lte' => date('Y-m-d')]], ['sort' => ['date' => -1], 'limit' => 4]) as $news) { ?>
                            <a href="<?= ROOTPATH ?>/news/view/<?= e($news['_id']) ?>" class="link-block news-item">
                                <div>
                                    <div class="badge type <?= $news['type'] ?? 'other' ?>"><?= $Vocabulary->getValue('news-category', $news['type'] ?? 'other') ?></div>
                                    <div class="link-block-title">
                                        <?= e(lang($news['title'] ?? '', $news['title_de'] ?? null)) ?>
                                    </div>

                                    <div class="info">
                                        <?php if (!empty($news['created_by'] ?? null)) { ?>
                                            <span class="mr-10">
                                                <i class="ph ph-user"></i>
                                                <?= $DB->getNameFromId($news['created_by']) ?>
                                            </span>
                                        <?php } ?>
                                        <span>
                                            <i class="ph ph-calendar-blank"></i>
                                            <?= time_elapsed_string($news['date']) ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if (!empty($news['image'])) { ?>
                                    <div>
                                        <?php DB::printLogo($news, 'news-image img-teaser') ?>
                                    </div>
                                <?php } ?>

                            </a>
                        <?php } ?>
                    </div>
                <?php } ?>


                <?php if ($Settings->featureEnabled('new-colleagues')) { ?>
                    <!-- Show new users -->
                    <div class="box padded">
                        <div class="widget-header">
                            <h2>
                                <i class="ph-duotone ph-user"></i>
                                <?= lang('New Colleagues', 'Neue Kolleg:innen') ?>
                            </h2>
                            <a href="<?= ROOTPATH ?>/user/browse" class="link-sm">
                                <?= lang('View all', 'Zeige alle') ?>
                            </a>
                        </div>
                        <?php
                        $new_colleagues = $osiris->persons->find(
                            ['created' => ['$exists' => true], 'is_active' => ['$ne' => false]],
                            [
                                'sort' => ['created' => -1],
                                'limit' => 3,
                                'projection' => ['username' => 1, 'displayname' => 1, 'position' => 1, 'position_de' => 1, 'created' => 1]
                            ]
                        )->toArray();
                        ?>
                        <?php foreach ($new_colleagues as $colleague) { ?>
                            <a class="colleague link-block" href="<?= ROOTPATH ?>/profile/<?= $colleague['username'] ?>">
                                <?= $Settings->printProfilePicture($colleague['username'], 'colleague-img') ?>
                                <div>
                                    <div class="link-block-title">
                                        <?= $colleague['displayname'] ?? $colleague['username'] ?>
                                    </div>
                                    <div class="info">
                                        <?php if (!empty($colleague['position'] ?? null) || !empty($colleague['position_de'] ?? null)) { ?>
                                            <?= lang($colleague['position'] ?? '', $colleague['position_de'] ?? null) ?>
                                            &#x2219;
                                        <?php } ?>
                                        <?= lang('added ', 'hinzugefügt ') . time_elapsed_string($colleague['created']) ?>
                                    </div>
                                </div>
                            </a>
                        <?php } ?>
                        </table>
                    </div>
                <?php } ?>


                <?php if ($Settings->featureEnabled('new-publications', true)) { ?>
                    <section class="home-widget-">
                        <div class="box padded">
                            <div class="widget-header">
                                <h2>
                                    <i class="ph-duotone ph-newspaper"></i>
                                    <?= lang('Newest publications', 'Neueste Veröffentlichungen') ?>
                                </h2>
                                <a href="<?= ROOTPATH ?>/activities#type=publication" class="link-sm">
                                    <?= lang('View all', 'Zeige alle') ?>
                                </a>
                            </div>
                            <?php
                            $pubs = $osiris->activities->find(
                                ['authors.aoi' => true, 'type' => 'publication'],
                                [
                                    'sort' => ['start_date' => -1],
                                    'limit' => 5,
                                    'projection' => ['html' => '$rendered.title', 'date' => '$start_date', 'authors' => 1, 'editors' => 1, '_id' => 1, 'icon' => '$rendered.icon']
                                ]
                            )->toArray();
                            ?>
                            <table class="font-size-12">
                                <?php foreach ($pubs as $doc) {
                                    $authors = [];
                                    // take first 2 authors or editors, if no authors are available
                                    foreach (['authors', 'editors'] as $role) {
                                        if (isset($doc[$role]) && !empty($doc[$role])) {
                                            foreach ($doc[$role] as $i => $author) {
                                                if (isset($author['last'])) {
                                                    $authors[] = Document::abbreviateAuthor($author['last'] ?? '', $author['first'] ?? '', false);
                                                }
                                            }
                                            break;
                                        }
                                    }
                                    if (count($authors) == 0) {
                                        $authorStr = lang('Unknown author', 'Unbekannte:r Autor:in');
                                    } elseif (count($authors) == 2) {
                                        $authorStr = implode(' and ', $authors);
                                    } else if (count($authors) > 2) {
                                        $authorStr = implode(', ', array_slice($authors, 0, 2));
                                        $authorStr .= ' et al.';
                                    } else {
                                        $authorStr = $authors[0];
                                    }

                                ?>
                                    <a href="<?= ROOTPATH ?>/activities/view/<?= $doc['_id'] ?>" class="link-block">
                                        <div class="link-block-title">
                                            <?= $doc['html'] ?>
                                        </div>
                                        <div class="info">
                                            <?= $doc['icon'] ?> <?= $authorStr ?> &#x2219; <?= format_date($doc['date'], 'M Y') ?>
                                        </div>
                                    </a>
                                <?php } ?>

                            </table>
                        </div>
                    </section>
                <?php } ?>

            </div>
        <?php } ?>

    </div>
</main>




<script>
    function conferenceToggle(el, id, type = 'interests') {
        const $el = $(el);

        $.ajax({
            url: ROOTPATH + '/ajax/conferences/toggle-interest',
            type: 'POST',
            data: {
                type: type,
                conference: id
            },
            success: function(data) {
                if (data === false || data === null) return;

                if (type === 'dismissed') {
                    $el.closest('.conference-item').slideUp(150, function() {
                        $(this).remove();
                    });
                    return;
                }

                const isActive = !$el.hasClass('active');
                $el.toggleClass('active', isActive);

                $el.find('.conference-count').text(data);

                const $icon = $el.find('i');

                $icon.toggleClass('ph-duotone', isActive);
                $icon.toggleClass('ph', !isActive);

            }
        });
    }
</script>