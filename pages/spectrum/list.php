<?php

/**
 * Page to see the complete research spectrum of the institution, aggregated by OpenAlex topics.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /spectrum
 *
 * @package     OSIRIS
 * @since       2.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$level = $_GET['level'] ?? 'topic';
if (!in_array($level, ['domain', 'field', 'subfield', 'topic'])) {
    $level = 'topic';
}
switch ($level) {
    case 'domain':
        $groupField = '$openalex.topics.domain';
        $idField = '$openalex.topics.domain_id';
        break;
    case 'field':
        $groupField = '$openalex.topics.field';
        $idField = '$openalex.topics.field_id';
        break;
    case 'subfield':
        $groupField = '$openalex.topics.subfield';
        $idField = '$openalex.topics.subfield_id';
        break;
    default:
        $groupField = '$openalex.topics.name';
        $idField = '$openalex.topics.id';
}

$match = [
    'openalex.topics' => ['$exists' => true, '$ne' => []],
    'affiliated' => true
];

// Domain filter
if (!empty($_GET['domain'])) {
    $match['openalex.topics.domain_id'] = $_GET['domain'];
}

// Year filter (angenommen: publication_year gespeichert)
if (!empty($_GET['year_from']) || !empty($_GET['year_to'])) {

    $yearFilter = [];

    if (!empty($_GET['year_from'])) {
        $yearFilter['$gte'] = (int)$_GET['year_from'];
    }

    if (!empty($_GET['year_to'])) {
        $yearFilter['$lte'] = (int)$_GET['year_to'];
    }

    $match['year'] = $yearFilter;
}
$aggregation = [
    ['$match' => $match],
    ['$unwind' => '$openalex.topics']
];

if (!empty($_GET['domain'])) {
    $aggregation[] = ['$match' => ['openalex.topics.domain_id' => $_GET['domain']]];
}

$aggregation = array_merge($aggregation, [
    ['$match' => $match],
    ['$unwind' => '$openalex.topics'],
    ['$group' => [
        '_id' => [
            'id' => $idField,
            'name' => $groupField
        ],
        'count' => ['$sum' => 1],
        'avg_score' => ['$avg' => '$openalex.topics.score'],
        'topic' => ['$first' => '$openalex.topics'],
        'citation_sum' => ['$sum' => '$openalex.cited_by_count'],
        'citation_avg' => ['$avg' => '$openalex.cited_by_count']
    ]],
    ['$sort' => ['count' => -1]],
    ['$project' => [
        '_id' => 0,
        'id' => '$_id.id',
        'name' => '$_id.name',
        'count' => 1,
        'avg_score' => 1,
        'topic' => 1,
        'citation_sum' => 1,
        'citation_avg' => 1
    ]],
]);

$spectrum = $osiris->activities->aggregate($aggregation)->toArray();

// Determine max count for normalization
$maxCount = 0;
foreach ($spectrum as $s) {
    if ($s['count'] > $maxCount) $maxCount = $s['count'];
}

// Add normalized strength
foreach ($spectrum as &$s) {
    $s['normalized'] = $maxCount > 0 ? $s['count'] / $maxCount : 0;
}
unset($s);
?>

<style>
    .spectrum-bar {
        display: grid;
        grid-template-columns: 2fr 4fr 60px;
        align-items: center;
        margin-bottom: 8px;
    }

    .spectrum-bar .bar {
        background: #eee;
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }

    .spectrum-bar .fill {
        background: #2E7D5B;
        height: 100%;
    }

    .spectrum-bar .fill.fill-1 {
        background: var(--spectrum-1-color);
    }

    .spectrum-bar .fill.fill-2 {
        background: var(--spectrum-2-color);
    }

    .spectrum-bar .fill.fill-3 {
        background: var(--spectrum-3-color);
    }

    .spectrum-bar .fill.fill-4 {
        background: var(--spectrum-4-color);
    }

    .spectrum-bar .count {
        font-size: 0.9em;
        color: #666;
        margin-left: 1rem;
    }

    .level-buttons .btn {
        text-transform: capitalize;
    }

    
</style>
<div class="modal" id="what-is-spectrum" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <?php if (lang('en', 'de') == 'de') { ?>
                <h2>Forschungs-Spektrum – Methodik & Hintergrund</h2>
                <hr>
                <h3>Was zeigt das Forschungs-Spektrum?</h3>
                <p>Das Forschungs-Spektrum ist eine datenbasierte thematische Analyse wissenschaftlicher Publikationen.</p>
                <p>Es zeigt, welche thematischen Schwerpunkte im aktuellen Datensatz vertreten sind und wie stark diese relativ zueinander ausgeprägt sind.</p>
                <p>Die Analyse basiert auf Publikationsdaten aus <a href="https://help.openalex.org/hc/en-us/articles/24736129405719-Topics" target="_blank" rel="noopener noreferrer" class="link"><strong>OpenAlex</strong></a>.</p>
                <hr>
                <h4>Woher stammen die Themen (Topics)?</h4>
                <p>Für jede Publikation ordnet OpenAlex automatisch die <strong>bis zu drei dominantesten thematischen Zuordnungen</strong> zu.</p>
                <p>Diese Zuordnungen:</p>
                <ul>
                    <li>werden algorithmisch berechnet</li>
                    <li>basieren auf Zitationsnetzwerken und inhaltlichen Clustern</li>
                    <li>spiegeln dominante thematische Einbettungen wider</li>
                    <li>sind keine manuell vergebenen Schlagwörter</li>
                </ul>
                <p>In OSIRIS werden diese Zuordnungen unverändert übernommen und strukturiert dargestellt.</p>
                <hr>
                <h4>Wie sind die Themen strukturiert?</h4>
                <p>OpenAlex organisiert Themen hierarchisch:</p>
                <ol>
                    <li><strong>Domain</strong> (4 übergeordnete Bereiche)</li>
                    <li><strong>Field</strong></li>
                    <li><strong>Subfield</strong></li>
                    <li><strong>Topic (Schwerpunkt)</strong></li>
                </ol>
                <img src="img/openalex-topics.png" alt="OpenAlex Topics" class="w-400 mw-full">
                <p>In OSIRIS werden die vier Domains farblich unterschieden:</p>
                <ul>
                    <li style="--primary-color-dark: var(--spectrum-1-color);--primary-color-20: var(--spectrum-1-color-20);"><b class="badge primary">Life Sciences</b></li>
                    <li style="--primary-color-dark: var(--spectrum-2-color);--primary-color-20: var(--spectrum-2-color-20);"><b class="badge primary">Social Sciences</b></li>
                    <li style="--primary-color-dark: var(--spectrum-3-color);--primary-color-20: var(--spectrum-3-color-20);"><b class="badge primary">Physical Sciences</b></li>
                    <li style="--primary-color-dark: var(--spectrum-4-color);--primary-color-20: var(--spectrum-4-color-20);"><b class="badge primary">Health Sciences</b></li>
                </ul>
                <p>Diese Hierarchie ermöglicht sowohl eine grobe als auch eine fein granulare Analyse.</p>
                <hr>
                <h4>Wie wird die relative Stärke berechnet?</h4>
                <p>Die „Relative Stärke“ zeigt, wie stark ein Schwerpunkt im Vergleich zum aktuell stärksten Schwerpunkt vertreten ist.</p>
                <ul>
                    <li>Der Schwerpunkt mit den meisten Publikationen wird auf 100 % gesetzt.</li>
                    <li>Alle anderen Werte werden relativ dazu berechnet.</li>
                    <li>Die Werte sind filterabhängig (z. B. Zeitraum, Domain, Organisationseinheit).</li>
                </ul>
                <p>Es handelt sich um eine strukturelle Vergleichsgröße, nicht um eine qualitative Bewertung.</p>
                <hr>
                <h4>Für welche Objekte wird das Spektrum berechnet?</h4>
                <p>Das Forschungs-Spektrum kann aggregiert werden für:</p>
                <ul>
                    <li>Personen</li>
                    <li>Organisationseinheiten</li>
                    <li>Forschungsbereiche</li>
                    <li>Projekte</li>
                    <li>das gesamte Institut</li>
                </ul>
                <p>Die zugrunde liegenden Berechnungen sind identisch; lediglich die Aggregationsebene ändert sich.</p>
                <hr>
                <h4>Limitationen</h4>
                <ul>
                    <li>Pro Publikation werden maximal drei dominante Themen berücksichtigt.</li>
                    <li>Randthemen oder interdisziplinäre Nebenbezüge können dadurch unterrepräsentiert sein.</li>
                    <li>Zitationszahlen sind alters- und feldabhängig.</li>
                    <li>Die Analyse stellt keine Leistungs- oder Qualitätsbewertung dar.</li>
                    <li>Die Ergebnisse sind abhängig von der Datenbasis und deren Aktualisierungsstand.</li>
                </ul>
                <hr>
                <h4>Wichtiger Hinweis</h4>
                <p>Das Forschungs-Spektrum ist ein analytisches Instrument zur thematischen Einordnung von Publikationen.</p>
                <p>Es dient der strukturellen Übersicht und strategischen Analyse – nicht der Bewertung von Personen oder Organisationseinheiten.</p>
            <?php } else { ?>
                <h2>Research Spectrum – Methodology & Background</h2>
                <hr>
                <p>The Research Spectrum is a data-driven thematic analysis of scholarly publications.</p>
                <p>It shows which thematic focuses are represented in the current dataset and how strong they are relative to each other.</p>
                <p>The analysis is based on publication data from <strong>OpenAlex</strong>.</p>
                <hr>
                <h4>Where do the topics come from?</h4>
                <p>For each publication, OpenAlex automatically assigns the <strong>up to three dominant thematic classifications</strong>.</p>
                <p>These classifications:</p>
                <ul>
                    <li>are algorithmically determined</li>
                    <li>are based on citation networks and content clusters</li>
                    <li>reflect dominant thematic embeddings</li>
                    <li>are not manually assigned keywords</li>
                </ul>
                <p>In OSIRIS, these classifications are adopted unchanged and presented in a structured manner.</p>
                <hr>
                <h4>How are the topics structured?</h4>
                <p>OpenAlex organizes topics hierarchically:</p>
                <ol>
                    <li><strong>Domain</strong> (4 overarching areas)</li>
                    <li><strong>Field</strong></li>
                    <li><strong>Subfield</strong></li>
                    <li><strong>Topic (Focus)</strong></li>
                </ol>
                <img src="img/openalex-topics.png" alt="OpenAlex Topics" class="w-400 mw-full">
                <p>In OSIRIS, the four domains are color-coded:</p>
                <ul>
                    <li style="--primary-color-dark: var(--spectrum-1-color);--primary-color-20: var(--spectrum-1-color-20);"><b class="badge primary">Life Sciences</b></li>
                    <li style="--primary-color-dark: var(--spectrum-2-color);--primary-color-20: var(--spectrum-2-color-20);"><b class="badge primary">Social Sciences</b></li>
                    <li style="--primary-color-dark: var(--spectrum-3-color);--primary-color-20: var(--spectrum-3-color-20);"><b class="badge primary">Physical Sciences</b></li>
                    <li style="--primary-color-dark: var(--spectrum-4-color);--primary-color-20: var(--spectrum-4-color-20);"><b class="badge primary">Health Sciences</b></li>
                </ul>
                <p>This hierarchy allows for both a broad and a fine-grained analysis.</p>
                <hr>
                <h4>How is the relative strength calculated?</h4>
                <p>The "Relative Strength" shows how strongly a focus is represented compared to the currently strongest focus.</p>
                <ul>
                    <li>The focus with the most publications is set to 100%.</li>
                    <li>All other values are calculated relative to it.</li>
                    <li>The values are filter-dependent (e.g., time period, domain, organizational unit).</li>
                </ul>
                <p>It is a structural comparative figure, not a qualitative assessment.</p>
                <hr>
                <h4>For which objects is the spectrum calculated?</h4>
                <p>The Research Spectrum can be aggregated for:</p>
                <ul>
                    <li>Individuals</li>
                    <li>Organizational units</li>
                    <li>Research areas</li>
                    <li>Projects</li>
                    <li>the entire institution</li>
                </ul>
                <p>The underlying calculations are identical; only the level of aggregation changes.</p>
                <hr>
                <h4>Limitations</h4>
                <ul>
                    <li>A maximum of three dominant topics are considered per publication.</li>
                    <li>Peripheral topics or interdisciplinary side references may be underrepresented as a result.</li>
                    <li>Citation counts are age- and field-dependent.</li>
                    <li>The analysis does not represent a performance or quality assessment.</li>
                    <li>The results depend on the data basis and its update status.</li>
                </ul>
                <hr>
                <h4>Important Note</h4>
                <p>The Research Spectrum is an analytical tool for the thematic classification of publications.</p>
                <p>It serves structural overview and strategic analysis – not the evaluation of individuals or organizational units.</p>
            <?php } ?>

            <div class="text-right mt-20">
                <a href="#close-modal" class="btn mr-5" role="button">Close</a>
            </div>
        </div>
    </div>
</div>


<h1>
    <i class="ph-duotone ph-lightbulb" aria-hidden="true"></i>
    <?= lang('Research Spectrum', 'Forschungs-Spektrum') ?>
</h1>


<p class="text-muted">
    <?= lang('Data-driven thematic analysis of scholarly publications based on OpenAlex.', 'Datenbasierte thematische Analyse wissenschaftlicher Publikationen auf Basis von OpenAlex.') ?>
    <a href="#what-is-spectrum" class="" role="button">
        <i class="ph ph-info"></i>
        <?= lang('What is the Research Spectrum?', 'Was ist das Forschungs-Spektrum?') ?>
    </a>
</p>

<div class="btn-toolbar">
<a href="<?= ROOTPATH ?>/spectrum/visualize?<?= http_build_query($_GET) ?>" class="btn mb-4">
    <i class="ph ph-chart-donut"></i>
    <?= lang('Visualize spectrum', 'Spektrum visualisieren') ?>
</a>

<a href="<?= ROOTPATH ?>/spectrum/evolution?<?= http_build_query($_GET) ?>" class="btn mb-4">
    <i class="ph ph-chart-line"></i>
    <?= lang('Visualize evolution', 'Entwicklung visualisieren') ?>
</a>
</div>

<form method="get" class="box padded">

    <div class="btn-toolbar level-buttons">
        <b class="mr-20"><?= lang('Hierarchy level:', 'Hierarchieebene:') ?></b>
        <input type="submit" name="level" class="btn level-domain <?= $level == 'domain' ? 'primary active' : '' ?>" value="domain">
        <input type="submit" name="level" class="btn level-field <?= $level == 'field' ? 'primary active' : '' ?>" value="field">
        <input type="submit" name="level" class="btn level-subfield <?= $level == 'subfield' ? 'primary active' : '' ?>" value="subfield">
        <input type="submit" name="level" class="btn level-topic <?= $level == 'topic' ? 'primary active' : '' ?>" value="topic">
    </div>

    <div class="row row-eq-spacing align-items-end">

        <!-- Domain Filter -->
        <div class="col-md-4">
            <label class="form-label"><?= lang('Domain', 'Domain') ?></label>
            <select name="domain" class="form-control">
                <option value=""><?= lang('All domains', 'Alle Domains') ?></option>
                <option value="1" <?= ($_GET['domain'] ?? '') == '1' ? 'selected' : '' ?>>Life Sciences</option>
                <option value="2" <?= ($_GET['domain'] ?? '') == '2' ? 'selected' : '' ?>>Social Sciences</option>
                <option value="3" <?= ($_GET['domain'] ?? '') == '3' ? 'selected' : '' ?>>Physical Sciences</option>
                <option value="4" <?= ($_GET['domain'] ?? '') == '4' ? 'selected' : '' ?>>Health Sciences</option>
            </select>
        </div>

        <!-- Year From -->
        <div class="col-md-3">
            <label class="form-label"><?= lang('From year', 'Von Jahr') ?></label>
            <input type="number" name="year_from" class="form-control"
                value="<?= e($_GET['year_from'] ?? '') ?>">
        </div>

        <!-- Year To -->
        <div class="col-md-3">
            <label class="form-label"><?= lang('To year', 'Bis Jahr') ?></label>
            <input type="number" name="year_to" class="form-control"
                value="<?= e($_GET['year_to'] ?? '') ?>">
        </div>

        <div class="col-md-2">
            <button class="btn primary block">
                <?= lang('Apply filter', 'Filter anwenden') ?>
            </button>
        </div>

    </div>
</form>

<div class="spectrum-chart box padded">
    <?php foreach (array_slice($spectrum, 0, 10) as $s):
        $percent = round($s['normalized'] * 100);
        $name = $s['name'];
    ?>
        <div class="spectrum-bar">
            <a class="label" href="<?= ROOTPATH ?>/spectrum/<?= e($level) ?>/<?= e($s['id']) ?>"><?= e($name) ?></a>
            <div class="bar">
                <div class="fill fill-<?= e($s['topic']['domain_id']) ?>" style="width: <?= $percent ?>%"></div>
            </div>
            <div class="count"><?= $s['count'] ?></div>
        </div>
    <?php endforeach; ?>
</div>
<table class="table dataTable" id="spectrum-table">
    <thead>
        <tr>
            <th><?= lang('Research focus', 'Schwerpunkt') ?></th>
            <th><?= lang('Publications', 'Publikationen') ?></th>
            <th><?= lang('Share', 'Anteil') ?></th>
            <th><?= lang('Avg. topic score', 'Ø Topic-Score') ?></th>
            <th><?= lang('Avg. citations', 'Ø Zitationen') ?></th>
            <th><?= lang('Total citations', 'Gesamtzitationen') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $totalPublications = array_sum(array_column($spectrum, 'count'));

        foreach ($spectrum as $s):
            $share = $totalPublications > 0 ? $s['count'] / $totalPublications : 0;
        ?>
            <tr>
                <td><a href="<?= ROOTPATH ?>/spectrum/<?= e($level) ?>/<?= e($s['id']) ?>"><?= e($s['name']) ?></a></td>
                <td><?= $s['count'] ?></td>
                <td><?= round($share * 100, 1) ?> %</td>
                <td><?= round($s['avg_score'] * 100, 1) ?> %</td>
                <td><?= round($s['citation_avg'], 1) ?></td>
                <td><?= $s['citation_sum'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<script>
    var dataTable;
    $(document).ready(function() {
        dataTable = $('#spectrum-table').DataTable({
            "order": [
                [2, 'desc'],
            ],
        });
    });
</script>