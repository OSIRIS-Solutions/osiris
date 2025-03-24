<?php

/**
 * Admin page for project settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

if (empty($project)) {
    $route = ROOTPATH . '/admin/project/create';
}

$finished_stages = 0;
if (isset($project['stage'])) {
    $finished_stages = $project['stage'];
}

$Project = new Project();

if (!isset($stage)) {
    $stage = '1';
}
$redirect = ROOTPATH . '/admin/projects/' . $stage . '/' . $type;
if ($stage == '3') {
    $redirect = ROOTPATH . '/admin/projects';
}

$phases = $project['phases'] ?? [];

?>

<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
<script src="<?= ROOTPATH ?>/js/admin-categories.js?v=1"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js" integrity="sha512-tBzZQxySO5q5lqwLWfu8Q+o4VkTcRGOeQGVQ0ueJga4A1RKuzmAu5HXDOXLEjpbKyV7ow9ympVoa6wZLEzRzDg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/flowchart/1.18.0/flowchart.min.js" integrity="sha512-FX1RpRt8RDEtTiFbDxg4u62QUJXhVE+cVE1mBD0iSOpj/ZZ/VNyZKlwhBT39QMcP5KEYS3yU8wh6Qpa57qVnbg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->




<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('ID must be unique', 'Die ID muss einzigartig sein.') ?></h5>
            <p>
                <?= lang('Each project type must have a unique ID with which it is linked to an activity.', 'Jeder Projekttyp muss eine einzigartige ID haben, mit der er zu einer Aktivität verknüpft wird.') ?>
            </p>
            <p>
                <?= lang('As the ID must be unique, the following previously used IDs and keywords (new) cannot be used as IDs:', 'Da die ID einzigartig sein muss, können folgende bereits verwendete IDs und Schlüsselwörter (new) nicht als ID verwendet werden:') ?>
            </p>
            <ul class="list" id="IDLIST">
                <?php foreach ($osiris->adminProjects->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>

<h1>
    <?= lang('Project Settings', 'Projekt-Einstellungen') ?>
    >
    <span class="text-primary">
        <?php if ($stage == '1') { ?>
            <?= lang('General', 'Allgemein') ?>
        <?php } else if ($stage == '2') { ?>
            <?= lang('Phases', 'Phasen') ?>
        <?php } else if ($stage == '3') { ?>
            <?= lang('Data fields', 'Datenfelder') ?>
        <?php } else if ($stage == '4') { ?>
            <?= lang('Subprojects', 'Teilprojekte') ?>
        <?php } else { ?>
            <?= lang('New', 'Neu') ?>
        <?php } ?>
    </span>
</h1>

<form action="<?= ROOTPATH ?>/crud/admin/projects/update/<?= $project['_id'] ?>" method="post" id="project-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>">
    <input type="hidden" class="hidden" name="stage" value="<?= $stage ?>">


    <?php if ($stage == '1') {
        /**
         * First stage of this form: general settings
         */
        if (isset($type) && $type != 'new') { ?>
            <input type="hidden" name="original_id" value="<?= $type ?>">
        <?php }
        ?>
        <div class="box">
            <div class="content">
                <h2>
                    <?= lang('General settings', 'Allgemeine Einstellungen') ?>
                </h2>

                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="id" class="required">ID</label>
                        <input type="text" class="form-control" name="values[id]" required value="<?= $type == 'new' ? '' : $type ?>" oninput="sanitizeID(this)">
                        <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('Must be unqiue', 'Muss einzigartig sein') ?></a></small>
                    </div>
                    <div class="col-sm">
                        <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>

                        <div class="input-group">
                            <input type="text" class="form-control" name="values[icon]" required value="<?= $project['icon'] ?? 'placeholder' ?>" onchange="iconTest(this.value)">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="ph ph-<?= $project['icon'] ?? 'placeholder' ?>" id="test-icon"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm">
                        <label for="color" class="required "><?= lang('Color', 'Farbe') ?></label>
                        <input type="color" class="form-control" name="values[color]" required value="<?= $project['color'] ?? '' ?>">
                    </div>
                </div>


                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="name" class="required ">Name (en)</label>
                        <input type="text" class="form-control" name="values[name]" required value="<?= $project['name'] ?? '' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="name_de" class="">Name (de)</label>
                        <input type="text" class="form-control" name="values[name_de]" value="<?= $project['name_de'] ?? '' ?>">
                    </div>
                </div>

                <hr>

                <div class="custom-checkbox mb-10 danger">
                    <input type="checkbox" id="disable" value="true" name="values[disabled]" <?= ($project['disabled'] ?? false) ? 'checked' : '' ?>>
                    <label for="disable"><?= lang('Deactivate', 'Deaktivieren') ?></label>
                </div>
                <span class="text-muted">
                    <?= lang('Deactivated projects are retained for past activities, but no new ones can be added.', 'Deaktivierte Projektkategorien bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
                </span>

                <hr>

                <h5>
                    <?= lang('Phases', 'Phasen') ?>
                </h5>

                <p class="text-muted">
                    <?= lang('A project can be divided into several phases, e.g. proposed or accepted. Each phase can have different data fields. Here you can set the number of phases that can be further defined in the next step.', 'Ein Projekt kann in mehrere Phasen unterteilt werden, z. B. beantragt oder angenommen. Jede Phase kann unterschiedliche Datenfelder haben. Hier kannst du die Anzahl der Phasen festlegen, die im nächsten Schritt weiter definiert werden können.') ?>
                </p>

                <p>
                    <b><?= lang('Reuse existing phases', 'Phasen wiederverwenden') ?></b>:

                    <b class="text-danger">TODO</b>
                </p>

                <table class="table simple small">
                    <thead>
                        <tr>
                            <th></th>
                            <th>ID</th>
                            <th>Name (english)</th>
                            <th>Name (deutsch)</th>
                            <th><?= lang('Color', 'Farbe') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="possible-values">
                        <?php if (!empty($phases)) { ?>
                            <?php foreach ($project['phases'] as $ph) {
                                // check first if this phase is already in use
                                $n_phase = $osiris->projects->count(['type' => $type, 'status' => $ph['id']]);
                                $row_id = $ph['id'];
                            ?>
                                <tr>
                                    <td class="w-50">
                                        <i class="ph ph-dots-six-vertical text-muted handle"></i>
                                    </td>
                                    <td>
                                        <code class="code phase-id">
                                            <?= $ph['id'] ?>
                                        </code>
                                        <input type="hidden" name="phasedef[<?= $row_id ?>][id]" value="<?= $ph['id'] ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="phasedef[<?= $row_id ?>][en]" value="<?= $ph['name'] ?? '' ?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="phasedef[<?= $row_id ?>][de]" value="<?= $ph['name_de'] ?? '' ?>">
                                    </td>
                                    <td>
                                        <?php
                                        $color = $ph['color'] ?? 'muted';
                                        ?>

                                        <div class="input-group">
                                            <select name="phasedef[<?= $row_id ?>][color]" class="form-control color-select color" onchange="colorSelect(this)">
                                                <option value="muted" <?= $color == 'muted' ? 'selected' : '' ?>>muted</option>
                                                <option value="signal" <?= $color == 'signal' ? 'selected' : '' ?>>signal</option>
                                                <option value="success" <?= $color == 'success' ? 'selected' : '' ?>>success</option>
                                                <option value="danger" <?= $color == 'danger' ? 'selected' : '' ?>>danger</option>
                                                <option value="primary" <?= $color == 'primary' ? 'selected' : '' ?>>primary</option>
                                                <option value="secondary" <?= $color == 'secondary' ? 'selected' : '' ?>>secondary</option>
                                            </select>
                                            <div class="input-group-append">
                                                <span class="input-group-text">
                                                    <i class="ph ph-fill ph-circle text-<?= $color ?>" id="test-icon"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($n_phase == 0) { ?>
                                            <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                                        <?php } else { ?>
                                            <span class="text-danger"><?= lang('Used in', 'Verwendet in') ?> <?= $n_phase ?> <?= lang('activities', 'Aktivitäten') ?></span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>

                        <?php } ?>

                    </tbody>
                </table>
                <button class="btn" type="button" onclick="addValuesRow()">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('Add phase', 'Phase hinzufügen') ?>
                </button>


                <script>
                    function addValuesRow() {
                        var row_id = Math.random().toString(36).substring(7);
                        $('#possible-values').append(`
                            <tr>
                                <td class="w-50">
                                    <i class="ph ph-dots-six-vertical text-muted handle"></i>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="phasedef[${row_id}][id]" value="" required oninput="sanitizeID(this, '.phase-id')">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="phasedef[${row_id}][en]" value="" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="phasedef[${row_id}][de]" value="" required>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <select name="phasedef[${row_id}][color]" class="form-control color-select color" onchange="colorSelect(this)">
                                            <option value="muted">muted</option>
                                            <option value="signal">signal</option>
                                            <option value="success">success</option>
                                            <option value="danger">danger</option>
                                            <option value="primary">primary</option>
                                            <option value="secondary">secondary</option>
                                        </select>
                                        <div class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="ph ph-fill ph-circle text-muted" id="test-icon"></i>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></a>
                                </td>
                            </tr>
                        `);
                    }
                    $('#possible-values').sortable({
                        handle: ".handle",
                        // change: function( event, ui ) {}
                    });

                    function colorSelect(el) {
                        let val = el.value;
                        $(el).next().find('i').removeClass().addClass('ph ph-fill ph-circle text-' + val);
                    }
                </script>

            </div>

        </div>

        <button type="button" class="btn success" onclick="phasesNotEmpty()">
            <?= lang('Next', 'Weiter') ?>
            <i class="ph ph-arrow-fat-line-right"></i>
        </button>

        <button type="submit" class="hidden" id="submit-stage-1"></button>

        <?php if ($stage <= $finished_stages) { ?>
            <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
                <?= lang('Skip', 'Überspringen') ?>
            </a>
        <?php } ?>

        <script>
            function phasesNotEmpty() {
                if ($('#possible-values tr').length == 0) {
                    toastError('<?= lang('Please add at least one phase.', 'Bitte füge mindestens eine Phase hinzu.') ?>');
                } else {
                    // click submit button and validate form
                    $('#submit-stage-1').click();
                }
            }
        </script>


    <?php } else if ($stage == '3') {
        /**
         * Second stage of this form: phase order
         */
    ?>

        <!-- one phase can lead to x others -->
        <!-- only one phase can be the first phase -->
        <div class="row row-eq-spacing">
            <div class="col-md-6">
                <div class="box padded mt-0">

                    <div id="phase-list" class="phase-list">
                        <?php
                        foreach ($phases as $phase) {
                            $phase_id = $phase['id'];
                            $prev = $phase['previous'] ?? '';
                        ?>
                            <div class="phase-item form-group">
                                <label for="previous-<?= $phase_id ?>" class="font-weight-bold"><?= lang($phase['name'], $phase['name_de']) ?></label>
                                <select class="previous-select form-control" data-id="<?= $phase_id ?>" name="previous[<?= $phase_id ?>]" id="previous-<?= $phase_id ?>">
                                    <option value=""><?= lang('None (First Phase)', 'Keine (Erste Phase)') ?></option>
                                    <?php foreach ($phases as $p) {
                                        if ($p['id'] == $phase_id) continue;
                                    ?>
                                        <option value="<?= $p['id'] ?>" <?= ($p['id'] == $prev) ? 'selected' : '' ?>><?= lang($p['name'], $p['name_de']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <a href="#/" class="btn" onclick="save(false)">
                        <i class="ph ph-question"></i>
                        <?= lang('Check', 'Überprüfen') ?>
                    </a>
                </div>


                <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage - 1 ?>/<?= $id ?>" class="btn">
                    <?= lang('Back', 'Zurück') ?>
                </a>

                <button type="button" class="btn success" onclick="save(true)">
                    <?= lang('Next', 'Weiter') ?>
                    <i class="ph ph-arrow-fat-line-right"></i>
                </button>

                <?php if ($stage <= $finished_stages) { ?>
                    <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
                        <?= lang('Skip', 'Überspringen') ?>
                    </a>
                <?php } ?>

            </div>
            <div class="col-md-6">

                <style>
                    svg#graph {
                        width: 100%;
                        height: 400px;
                        border: 1px solid var(--border-color);
                        background-color: white;
                        border-radius: var(--border-radius);
                    }
                </style>
                <svg id="graph"></svg>
            </div>
        </div>

        <script>
            const PHASES = <?= json_encode($phases) ?>;

            function validatePhaseGraph(phases) {
                let graph = new Map();
                let reverseGraph = new Map();
                let startNodes = new Set();
                let allNodes = new Set();

                // check if only one phase is empty
                var firsts = $('.previous-select').filter(function() {
                    return $(this).val() == '';
                });
                if (firsts.length != 1) {
                    toastError('<?= lang('Exactly one phase must be the first phase, indicated by the lack of a previous phase.', 'Nur eine Phase kann die erste Phase sein, was durch "Keine" vorherige Phase gekennzeichnet ist.') ?>');
                    return false;
                }

                // Graphen erstellen (normal & umgekehrt)
                phases.forEach(phase => {
                    let id = phase.id;
                    let pred = phase.previous;

                    allNodes.add(id);
                    if (!graph.has(id)) graph.set(id, []);
                    if (!reverseGraph.has(id)) reverseGraph.set(id, []);

                    if (pred == null) {
                        startNodes.add(id);
                    }

                    // previous.forEach(pred => {
                    if (!graph.has(pred)) graph.set(pred, []);
                    graph.get(pred).push(id);

                    if (!reverseGraph.has(id)) reverseGraph.set(id, []);
                    reverseGraph.get(id).push(pred);
                    // });
                });

                console.log(graph);
                // 1. **Überprüfung auf unerreichbare Knoten**
                function findReachableNodes(startNodes, graph) {
                    let visited = new Set();
                    let stack = [...startNodes];

                    while (stack.length > 0) {
                        let node = stack.pop();
                        if (!visited.has(node)) {
                            visited.add(node);
                            if (graph.has(node)) {
                                stack.push(...graph.get(node));
                            }
                        }
                    }
                    return visited;
                }

                let reachableNodes = findReachableNodes(startNodes, graph);
                let unreachableNodes = [...allNodes].filter(n => !reachableNodes.has(n));
                console.log(unreachableNodes);
                if (unreachableNodes.length > 0) {
                    toastError("Fehler: Folgende Phasen sind unerreichbar: " + unreachableNodes.join(", "));
                    return false;
                }

                // 2. **Überprüfung auf Zyklen**
                function hasCycle(graph) {
                    let visited = new Set();
                    let stack = new Set();

                    function visit(node) {
                        if (stack.has(node)) return true; // Zyklus gefunden
                        if (visited.has(node)) return false;

                        visited.add(node);
                        stack.add(node);
                        for (let neighbor of (graph.get(node) || [])) {
                            if (visit(neighbor)) return true;
                        }
                        stack.delete(node);
                        return false;
                    }

                    for (let node of allNodes) {
                        if (visit(node)) {
                            return true;
                        }
                    }
                    return false;
                }

                if (hasCycle(graph)) {
                    toastError("Fehler: Der Phasen-Graph enthält einen Zyklus!");
                    return false;
                }

                return true;
            }

            function save(submit = false) {
                let phases = [];
                $("#phase-list .phase-item .previous-select").each(function(index) {
                    let phaseId = $(this).data('id');
                    let dependencies = $(this).val() || null;
                    phases.push({
                        id: phaseId,
                        position: index,
                        previous: dependencies
                    });
                });

                drawGraph(phases);

                if (validatePhaseGraph(phases)) {


                    if (submit) {
                        $('#project-form').submit();
                    } else {
                        toastSuccess("Validierung erfolgreich!");
                    }
                }

            };

            function drawGraph(phases) {
                const svg = d3.select("#graph")
                    .attr("width", 600)
                    .attr("height", 400);

                svg.selectAll("*").remove(); // Vorherige Inhalte löschen

                // Knoten (Phasen) und Links (Verbindungen) aus den gespeicherten Daten extrahieren
                const nodes = PHASES.map(phase => ({
                    id: phase.id,
                    name: phase.name,
                    color: phase.color,
                    x: Math.random() * 600,
                }));
                const links = phases
                    .filter(phase => phase.previous) // Nur Phasen mit Vorgänger
                    .map(phase => ({
                        source: phase.previous,
                        target: phase.id
                    }));

                // which node is the first one?
                const startNodes = phases.filter(phase => !phase.previous).map(phase => phase.id);
                nodes.forEach(node => {
                    if (startNodes.includes(node.id)) {
                        node.startnode = true;
                    }
                });

                // Erstelle eine D3-Simulation für die Knoten und Kanten
                const simulation = d3.forceSimulation(nodes)
                    .force("link", d3.forceLink(links).id(d => d.id).distance(100))
                    .force("charge", d3.forceManyBody().strength(-100))
                    .force("center", d3.forceCenter(300, 200));

                // Pfeile (Links) hinzufügen
                const link = svg.selectAll("line")
                    .data(links)
                    .enter().append("line")
                    .attr("stroke", "#999")
                    .attr("stroke-width", 2)
                    .attr("marker-end", "url(#arrow)");

                // Pfeilspitzen definieren
                svg.append("defs").append("marker")
                    .attr("id", "arrow")
                    .attr("viewBox", "0 -5 10 10")
                    .attr("refX", 20)
                    .attr("refY", 0)
                    .attr("markerWidth", 6)
                    .attr("markerHeight", 6)
                    .attr("orient", "auto")
                    .append("path")
                    .attr("d", "M0,-5L10,0L0,5")
                    .attr("fill", "#999");

                // Knoten hinzufügen
                const node = svg.selectAll("circle")
                    .data(nodes)
                    .enter().append("circle")
                    .attr("r", 15)
                    .attr("fill", d => `var(--${d.color}-color)`)
                    .call(d3.drag()
                        .on("start", (event, d) => {
                            if (!event.active) simulation.alphaTarget(0.3).restart();
                            d.fx = d.x;
                            d.fy = d.y;
                        })
                        .on("drag", (event, d) => {
                            d.fx = event.x;
                            d.fy = event.y;
                        })
                        .on("end", (event, d) => {
                            if (!event.active) simulation.alphaTarget(0);
                            d.fx = null;
                            d.fy = null;
                        })
                    );

                // Labels für Knoten hinzufügen
                const labels = svg.selectAll("text")
                    .data(nodes)
                    .enter().append("text")
                    .attr("text-anchor", "middle")
                    .style("font-size", "12px")
                    .style("font-weight", "bold")
                    .attr("dy", 28)
                    .text(d => d.name);

                // Simulations-Update
                simulation.on("tick", () => {
                    link.attr("x1", d => d.source.x)
                        .attr("y1", d => d.source.y)
                        .attr("x2", d => d.target.x)
                        .attr("y2", d => d.target.y);

                    node.attr("cx", d => d.x).attr("cy", d => d.y);
                    labels.attr("x", d => d.x).attr("y", d => d.y);
                });
            }


            $(document).ready(function() {
                drawGraph(phases);
            });
        </script>


    <?php } else if ($stage == '2') {
        /**
         * Second stage of this form: phase data fields
         */
    ?>

        <?php
        foreach ($phases as $phase) {
            $phase_id = $phase['id'];
        ?>

            <div class="box phase" id="phase-<?= $phase_id ?>" data-id="<?= $phase_id ?>">
                <div class="content">
                    <b>Phase</b>
                    <code class="code float-right text-<?=$phase['color'] ?? 'muted'?>"><?= $phase_id ?></code>
                    <h2 class="title">
                        <div class="badge <?=$phase['color'] ?? 'muted'?>"><?= lang($phase['name'], $phase['name_de']) ?></div>
                    </h2>
                    <!-- disabled -->
                    <!-- <div class="custom-checkbox mb-10 danger">
                        <input type="checkbox" id="disable-<?= $phase_id ?>" value="true" name="phase[<?= $phase_id ?>][disabled]" <?= ($phase['disabled'] ?? false) ? 'checked' : '' ?>>
                        <label for="disable-<?= $phase_id ?>"><?= lang('Deactivate', 'Deaktivieren') ?></label>
                    </div>
                    <span class="text-muted">
                        <?= lang('Deactivated phases are retained for past activities, but no new ones can be added.', 'Deaktivierte Phasen bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
                    </span> -->


                    <?php if ($Settings->featureEnabled('portal')) { ?>
                        <div class="my-20">
                            <input type="hidden" name="phase[<?= $phase_id ?>][portfolio]" value="">
                            <div class="custom-checkbox">
                                <input type="checkbox" id="portfolio-question" value="1" name="phase[<?= $phase_id ?>][portfolio]" <?= ($phase['portfolio'] ?? false) ? 'checked' : '' ?>>
                                <label for="portfolio-question">
                                    <?= lang('This phase of a project should be visible in OSIRIS Portfolio.', 'Diese Phase eines Projekts sollte in OSIRIS Portfolio sichtbar sein.') ?>
                                </label>
                            </div>
                        </div>
                    <?php } ?>
                </div>

                <hr>

                <div class="content">
                    <label for="module" class="font-weight-bold"><?= lang('Data fields', 'Datenfelder') ?> *</label>
                    <br>
                    <?php
                    foreach ($Project->FIELDS as $m) {
                        if ($m['required'] ?? false) continue;
                        $modules = DB::doc2Arr($phase['modules'] ?? []);
                    ?>
                        <div class="custom-checkbox checkbox-badge">
                            <input type="checkbox" id="module-<?= $phase_id ?>-<?= $m['id'] ?>" value="<?= $m['id'] ?>" name="phase[<?= $phase_id ?>][modules][]" <?= (in_array($m['id'], $modules)) ? 'checked' : '' ?>>
                            <label for="module-<?= $phase_id ?>-<?= $m['id'] ?>">
                                <?= lang($m['en'], $m['de']) ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>

            </div>


        <?php } ?>

        <p>
            * <?= lang('Name, title, status, time and applicant are always a required part of the form and cannot be deactivated.', 'Name, Titel, Status, Zeitrahmen und Antragsteller:in des Projektes sind immer Teil des Formulars sowie Pflichfelder und können hier deshalb nicht ausgeschaltet werden.') ?>
        </p>

        <a class="btn" href="<?= ROOTPATH ?>/admin/projects/1/<?= $type ?>">
            <?= lang('Back without saving', 'Zurück ohne zu speichern') ?>
            <i class="ph ph-arrow-fat-line-left"></i>
        </a>

        <button type="submit" class="btn success">
            <?= lang('Next', 'Weiter') ?>
            <i class="ph ph-arrow-fat-line-right"></i>
        </button>

        <?php if ($stage <= $finished_stages) { ?>
            <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
                <?= lang('Skip', 'Überspringen') ?>
            </a>
        <?php } ?>


    <?php } else if ($stage == '4') {
        /**
         * Third stage of this form: subprojects
         */

        $subprojects = $project['has_subprojects'] ?? false;
    ?>


        <div class="box">
            <div class="content">
                <h2 class="title">
                    <?= lang('Subprojects', 'Teilprojekte') ?>
                </h2>
                <div class="custom-checkbox">
                    <input type="checkbox" id="subprojects" value="true" name="values[has_subprojects]" <?= ($project['has_subprojects'] ?? false) ? 'checked' : '' ?>>
                    <label for="subprojects">
                        <?= lang('This type of project can have subprojects.', 'Diese Art von Projekt kann Teilprojekte haben.') ?>
                    </label>
                </div>
                <span class="text-muted">
                    <?= lang('Subprojects are projects that are linked to a main project and are displayed in the project overview.', 'Teilprojekte sind Projekte, die mit einem Hauptprojekt verknüpft sind und in der Projektübersicht angezeigt werden.') ?>
                </span>

                <h5>
                    <?= lang('Inherited data fields from main project', 'Datenfelder, die vom Hauptprojekt übernommen werden') ?>
                </h5>
                <p>
                    <?= lang('These data fields cannot be edited in the project itself but are always copied from the parent project.', 'Diese Datenfelder können nicht im Teilprojekt selbst bearbeitet werden, sondern werden immer aus dem übergeordneten Projekt übernommen.') ?>
                </p>

                <div class="">
                    <?php
                    $modules = [];
                    foreach ($phases as $phase) {
                        $modules = array_merge($modules, DB::doc2Arr($phase['modules'] ?? []));
                    }
                    $modules = array_unique($modules);
                    foreach ($Project->FIELDS as $m) {
                        // if ($m['required'] ?? false) continue;
                        $inherits = DB::doc2Arr($project['inherits'] ?? []);
                        if (!in_array($m['id'], $modules)) continue;
                    ?>
                        <div class="custom-checkbox checkbox-badge">
                            <input type="checkbox" id="subprojects-<?= $m['id'] ?>" value="<?= $m['id'] ?>" name="values[inherits][]" <?= (in_array($m['id'], $inherits)) ? 'checked' : '' ?>>
                            <label for="subprojects-<?= $m['id'] ?>">
                                <?= lang($m['en'], $m['de']) ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
                <p>
                    <i class="ph ph-info text-signal"></i>
                    <?= lang('Please note that name, title, status, and time are always a required part of the form.', 'Zur Information: Name, Titel, Status und Zeitrahmen des Projektes sind immer Teil des Formulars sowie Pflichfelder.') ?>
                </p>

            </div>
        </div>



        <a class="btn" href="<?= ROOTPATH ?>/admin/projects/3/<?= $type ?>">
            <?= lang('Back without saving', 'Zurück ohne zu speichern') ?>
            <i class="ph ph-arrow-fat-line-left"></i>
        </a>

        <button class="btn success" id="submitBtn"><?= lang('Save', 'Speichern') ?></button>

    <?php } ?>
</form>


<?php if ($stage == '1' && !empty($project)) {
    $member = $osiris->projects->count(['type' => $type]);
    if ($member == 0) { ?>
        <div class="alert danger mt-20">
            <form action="<?= ROOTPATH ?>/crud/types/delete/<?= $type ?>" method="post">
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
                <span class="ml-20"><?= lang('Warning! Cannot be undone.', 'Warnung, kann nicht rückgängig gemacht werden!') ?></span>
            </form>
        </div>
    <?php } else { ?>
        <div class="alert danger mt-20">
            <?= lang("Can\'t delete type: $member activities associated.", "Kann Typ nicht löschen: $member Aktivitäten zugeordnet.") ?><br>
            <a href='<?= ROOTPATH ?>/activities/search#{"$and":[{"type":"<?= $type ?>"}]}' target="_blank" class="text-danger">
                <i class="ph ph-search"></i>
                <?= lang('View activities', 'Aktivitäten zeigen') ?>
            </a>
        </div>
    <?php } ?>

<?php } ?>