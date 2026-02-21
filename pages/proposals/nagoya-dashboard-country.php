<?php
// expects: $code, $countryName, $projectsForCountry, $labelCounts, $permitStats, $docsByPermitKey, $DB, $Settings
$projectCount = count($projectsForCountry);
?>
<style>
    .dashboard .box {
        height: calc(100% - 2rem);
    }
</style>


<h1 class="mb-3">
    <i class="ph-duotone ph-globe-stand"></i>
    <?= lang('ABS overview for', 'ABS-Übersicht für') ?>
    <?= e($countryName) ?>
    <span class="text-muted font-size-14">(<?= e($code) ?>)</span>
</h1>

<a href="<?= ROOTPATH ?>/nagoya"><i class="ph ph-arrow-left"></i> <?= lang('Back to dashboard', 'Zurück zum Dashboard') ?></a>

<p class="text-muted mb-0">
    <?= lang(
        'This page lists all Nagoya/ABS-relevant projects for this country, including A/B/C labels and permits. Use this overview for internal ABS checks and BfN reporting.',
        'Diese Seite listet alle Nagoya/ABS-relevanten Projekte für dieses Land auf, inkl. A/B/C-Labels und Genehmigungen. Nutze die Übersicht für interne ABS-Prüfungen und BfN-Reporting.'
    ) ?>
</p>
<div class="row row-eq-spacing dashboard mt-0">
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-clipboard-text"></i>
                <?= lang('Projects', 'Projekte') ?>
            </h3>
            <p class="mb-5">
                <span class="badge"><?= $projectCount ?></span>
                <span class="text-muted font-size-12">
                    <?= lang('projects with this country in Nagoya scope', 'Projekte mit diesem Land im Nagoya-Scope') ?>
                </span>
            </p>
            <p class="font-size-12 text-muted mb-0">
                <?php if ($projectCount === 0): ?>
                    <?= lang('No projects found for this country.', 'Keine Projekte für dieses Land gefunden.') ?>
                <?php else: ?>
                    <?= lang('Click on a project name to open the Nagoya details.', 'Klicke auf einen Projektnamen, um die Nagoya-Details zu öffnen.') ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-squares-four"></i>
                <?= lang('A/B/C labels', 'A/B/C-Labels') ?>
            </h3>
            <p class="font-size-12 text-muted mb-10">
                <?= lang(
                    'Current ABS classification for projects in this country.',
                    'Aktuelle ABS-Klassifikation der Projekte in diesem Land.'
                ) ?>
            </p>
            <div class="d-flex flex-wrap gap-10 flex-column text-center font-size-16">
                <span class="badge danger"><b>A:</b> <?= (int)$labelCounts['A'] ?></span>
                <span class="badge warning"><b>B:</b> <?= (int)$labelCounts['B'] ?></span>
                <span class="badge success"><b>C:</b> <?= (int)$labelCounts['C'] ?></span>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-file-text"></i>
                <?= lang('Permits summary', 'Genehmigungsübersicht') ?>
            </h3>
            <p class="font-size-12 text-muted mb-10">
                <?= lang(
                    'Overview of all permits linked to projects in this country.',
                    'Übersicht aller Genehmigungen, die mit Projekten in diesem Land verknüpft sind.'
                ) ?>
            </p>
            <ul class="list-unstyled font-size-12 mb-5">
                <li>
                    <span class="badge"><?= (int)$permitStats['total'] ?></span>
                    <?= lang('permits in total', 'Genehmigungen insgesamt') ?>
                </li>
                <li>
                    <span class="badge warning"><?= (int)$permitStats['needed'] + (int)$permitStats['requested'] ?></span>
                    <?= lang('permits needed/requested', 'erforderliche/beantragte Genehmigungen') ?>
                </li>
                <li>
                    <span class="badge success"><?= (int)$permitStats['granted'] ?></span>
                    <?= lang('granted permits', 'erteilte Genehmigungen') ?>
                </li>
                <li>
                    <span class="badge muted"><?= (int)$permitStats['notApplicable'] ?></span>
                    <?= lang('marked as not applicable', 'als nicht zutreffend markiert') ?>
                </li>
            </ul>
            <p class="font-size-12 text-muted mb-0">
                <i class="ph ph-paperclip"></i>
                <?= (int)$permitStats['docs'] ?>
                <?= lang('documents uploaded for permits.', 'Dokumente zu Genehmigungen hochgeladen.') ?>
            </p>
        </div>
    </div>
</div>

<hr class="my-20">

<div class="row row-eq-spacing">
    <!-- Projects table -->
    <div class="col-md-7">
        <h2 class="title mb-10">
            <i class="ph-duotone ph-clipboard-text"></i>
            <?= lang('Projects with ABS relevance in this country', 'Projekte mit ABS-Relevanz in diesem Land') ?>
        </h2>
        <?php if (!$projectCount): ?>
            <div class="box padded text-muted">
                <?= lang('No projects found.', 'Keine Projekte gefunden.') ?>
            </div>
        <?php else: ?>
                <table class="table" id="nagoya-projects-overview-table">
                    <thead>
                        <tr>
                            <th><?= lang('Project', 'Projekt') ?></th>
                            <th><?= lang('Label', 'Label') ?></th>
                            <th><?= lang('Nagoya status', 'Nagoya-Status') ?></th>
                            <th><?= lang('Permits', 'Genehmigungen') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projectsForCountry as $entry):
                            $p     = $entry['project'];
                            $c     = $entry['country'];
                            $eval  = $entry['evaluation'];
                            $perms = $entry['permits'] ?? [];

                            $idStr   = (string)($p['_id'] ?? '');
                            $label   = $eval['label'] ?? ($p['nagoya']['labelABC'] ?? ($p['nagoya']['label'] ?? null));

                            $permTotal = count($perms);
                            $permOpen  = 0;
                            foreach ($perms as $perm) {
                                if (in_array($perm['status'] ?? '', ['needed', 'requested'])) {
                                    $permOpen++;
                                }
                            }
                        ?>
                            <tr>
                                <td class="font-size-12">
                                    <a href="<?= ROOTPATH ?>/proposals/view/<?= $idStr ?>#nagoya">
                                        <strong><?= e($p['name'] ?? '') ?></strong>
                                    </a><br>
                                    <span class="text-muted">
                                        <?= e($p['id'] ?? '') ?>
                                    </span>
                                </td>
                                <td class="font-size-12">
                                    <?php
                                    if ($label === 'A') {
                                        echo '<span class="badge danger">A</span>';
                                    } elseif ($label === 'B') {
                                        echo '<span class="badge warning">B</span>';
                                    } elseif ($label === 'C') {
                                        echo '<span class="badge success">C</span>';
                                    } else {
                                        echo '<span class="badge muted">–</span>';
                                    }
                                    ?>
                                </td>
                                <td class="font-size-12">
                                    <?= Nagoya::badge(DB::doc2Arr($p), false) ?>
                                </td>
                                <td class="font-size-12">
                                    <?php if ($permTotal): ?>
                                        <span class="badge <?= $permOpen ? 'signal' : 'success' ?>">
                                            <?= $permTotal ?>
                                            <?php if ($permOpen): ?>
                                                (<?= $permOpen ?> <?= lang('open', 'offen') ?>)
                                            <?php endif; ?>
                                        </span>
                                        <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $idStr ?>/<?= urlencode($c['id'] ?? '') ?>" class="small">
                                            <i class="ph ph-arrow-up-right"></i>
                                            <?= lang('Details', 'Details') ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">–</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        <?php endif; ?>
    </div>

    <!-- Flat permits table -->
    <div class="col-md-5">
        <h2 class="title mb-10">
            <i class="ph-duotone ph-file-text"></i>
            <?= lang('All permits for this country', 'Alle Genehmigungen für dieses Land') ?>
        </h2>
        <?php if (empty($projectsForCountry) || $permitStats['total'] === 0): ?>
            <div class="box padded text-muted">
                <?= lang('No permits recorded for this country yet.', 'Für dieses Land sind noch keine Genehmigungen hinterlegt.') ?>
            </div>
        <?php else: ?>
            <table class="table table-sm font-size-12" id="nagoya-permits-overview-table">
                <thead>
                    <tr>
                        <th><?= lang('Project', 'Projekt') ?></th>
                        <th><?= lang('Permit', 'Genehmigung') ?></th>
                        <th><?= lang('Status', 'Status') ?></th>
                        <th><?= lang('Docs', 'Dokumente') ?></th>
                        <th><?= lang('ABS check', 'ABS-Prüfung') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projectsForCountry as $entry):
                        $p     = $entry['project'];
                        $c     = $entry['country'];
                        $perms = $entry['permits'] ?? [];
                        $idStr = (string)($p['_id'] ?? '');
                        foreach ($perms as $perm):
                            $pid       = $perm['id'] ?? null;
                            $status    = $perm['status'] ?? '';
                            $checked   = !empty($perm['checked']);
                            $name      = $perm['name'] ?? '';
                            $key       = $pid ? ($idStr . ':' . $pid) : null;
                            $docCount  = $key && isset($docsByPermitKey[$key]) ? ($docsByPermitKey[$key]['count'] ?? 0) : 0;

                            // small status badge
                            if ($status === 'needed') {
                                $statusLabel = lang('Needed', 'Erforderlich');
                                $statusClass = 'badge tiny warning';
                            } elseif ($status === 'requested') {
                                $statusLabel = lang('Requested', 'Beantragt');
                                $statusClass = 'badge tiny signal';
                            } elseif ($status === 'granted') {
                                $statusLabel = lang('Granted', 'Erteilt');
                                $statusClass = 'badge tiny success';
                            } elseif ($status === 'not-applicable') {
                                $statusLabel = lang('Not applicable', 'Nicht zutreffend');
                                $statusClass = 'badge tiny muted';
                            } else {
                                $statusLabel = lang('Unknown', 'Unbekannt');
                                $statusClass = 'badge tiny muted';
                            }
                    ?>
                            <tr>
                                <td>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $idStr ?>/<?= urlencode($c['id'] ?? '') ?>">
                                        <?= e($p['name'] ?? '') ?>
                                    </a>
                                </td>
                                <td><?= e($name ?: lang('Unnamed', 'Unbenannt')) ?></td>
                                <td><span class="<?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                <td>
                                    <?php if ($docCount > 0): ?>
                                        <span class="badge tiny">
                                            <i class="ph ph-paperclip"></i> <?= $docCount ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">–</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($status === 'granted'): ?>
                                        <?php if ($checked): ?>
                                            <span class="badge tiny success">
                                                <i class="ph ph-check"></i>
                                                <?= lang('validated', 'validiert') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge tiny warning">
                                                <i class="ph ph-warning"></i>
                                                <?= lang('needs check', 'Prüfung offen') ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">–</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php endforeach;
                    endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>


<script>
    $(document).ready(function() {
        if ($('#nagoya-permits-overview-table tbody tr').length > 1) {
            $('#nagoya-permits-overview-table').DataTable({
                "order": [[ 1, "desc" ]],
                "pageLength": 10,
                "layout": {
                    "topEnd": null
                }
            });
        }
        if ($('#nagoya-projects-overview-table tbody tr').length > 1) {
            $('#nagoya-projects-overview-table').DataTable({
                "order": [[ 1, "desc" ]],
                "pageLength": 10,
                "layout": {
                    "topEnd": null
                }
            });
        }
    });
</script>