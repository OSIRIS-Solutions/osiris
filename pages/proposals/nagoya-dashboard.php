<?php
// Expect: $projects, $cta, $countryStats, $Settings, $DB already defined
?>

<style>
    .dashboard .box {
        height: calc(100% - 2rem);
    }
</style>


<h1 class="mb-3">
    <i class="ph-duotone ph-globe-hemisphere-west"></i>
    <?= lang('Nagoya / ABS Compliance Dashboard', 'Nagoya / ABS Compliance Dashboard') ?>
</h1>

<p class="text-muted mb-20 font-size-14">
    <?= lang(
        'Overview of all Nagoya-relevant projects, current tasks for the ABS Compliance Team, and quick access to country and permit information.',
        'Übersicht über alle Nagoya-relevanten Projekte, aktuelle Aufgaben für das ABS-Compliance-Team und schnellen Zugriff auf Länder- und Genehmigungsinformationen.'
    ) ?>
</p>

<?php
$cntCountryReview  = count($cta['country_review_open']  ?? []);
$cntScopeMissing   = count($cta['scope_missing']        ?? []);
$cntScopeReview    = count($cta['scope_review_open']    ?? []);
$cntPermitsPending = count($cta['permits_pending']      ?? []);
$cntPermitsValid   = count($cta['permits_validation']   ?? []);
$totalProjects     = count($projects ?? []);
?>

<h2 class="mb-0">
    <i class="ph-duotone ph-list-checks"></i>
    <?= lang('Current tasks', 'Aktuelle Aufgaben') ?>
</h2>

<div class="row row-eq-spacing mt-0 dashboard">
    <!-- Country review open -->
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-map-trifold"></i>
                <?= lang('Country review open', 'Offene Länderprüfungen') ?>
            </h3>
            <p class="text-muted font-size-12 mb-10">
                <?= lang(
                    'Countries that still need ABS/Nagoya relevance decisions.',
                    'Länder, für die die ABS/Nagoya-Relevanz noch entschieden werden muss.'
                ) ?>
            </p>
            <div class="mb-10">
                <span class="badge <?= $cntCountryReview ? 'signal' : 'muted' ?>">
                    <?= $cntCountryReview ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('open country checks for projects', 'offene Länderprüfungen für Projekte') ?>
                </span>
            </div>
            <?php if ($cntCountryReview): ?>
                <ul class="list font-size-12 mb-0">
                    <?php foreach (array_slice($cta['country_review_open'], 0, 5) as $item):
                        $p   = $item['project'];
                        $c   = $item['country'];
                        $pid = (string)($p['_id'] ?? '');
                        $code = $c['code'] ?? '';
                    ?>
                        <li>
                            <a href="<?= e($item['url']) ?>">
                                <strong><?= e($p['name'] ?? '') ?></strong>
                            </a><br>
                            <span>
                                <i class="ph ph-globe"></i>
                                <?= $DB->getCountry($code, lang('name', 'name_de')) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($cntCountryReview > 5): ?>
                        <li class="text-muted">
                            <?= lang('…and more.', '…und weitere.') ?>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted font-size-12 mb-0">
                    <?= lang('No open country reviews at the moment.', 'Aktuell keine offenen Länderprüfungen.') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scope tasks -->
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-crosshair"></i>
                <?= lang('Scope & evaluation', 'Scope & Bewertung') ?>
            </h3>
            <p class="text-muted font-size-12 mb-10">
                <?= lang(
                    'Projects where scope information is missing or where scope needs to be evaluated by the ABS team.',
                    'Projekte, bei denen Scope-Informationen fehlen oder vom ABS-Team bewertet werden müssen.'
                ) ?>
            </p>

            <div class="mb-5">
                <span class="badge <?= $cntScopeMissing ? 'warning' : 'muted' ?>">
                    <?= $cntScopeMissing ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects with missing scope (researchers)', 'Projekte mit fehlendem Scope (Forschende)') ?>
                </span>
            </div>
            <div class="mb-10">
                <span class="badge <?= $cntScopeReview ? 'signal' : 'muted' ?>">
                    <?= $cntScopeReview ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects with scope ready for ABS review', 'Projekte mit Scope zur ABS-Prüfung bereit') ?>
                </span>
            </div>

            <?php if ($cntScopeMissing || $cntScopeReview): ?>
                <ul class="list font-size-12 mb-0">
                    <?php foreach (array_slice($cta['scope_review_open'] ?? [], 0, 3) as $item):
                        $p   = $item['project'];
                    ?>
                        <li>
                            <a href="<?= e($item['url']) ?>">
                                <strong><?= e($p['name'] ?? '') ?></strong>
                            </a><br>
                            <span class="badge tiny signal">
                                <?= lang('ABS review pending', 'ABS-Review offen') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>

                    <?php foreach (array_slice($cta['scope_missing'] ?? [], 0, 2) as $item):
                        $p   = $item['project'];
                    ?>
                        <li class="mb-5">
                            <a href="<?= e($item['url']) ?>">
                                <strong><?= e($p['name'] ?? '') ?></strong>
                            </a><br>
                            <span class="badge tiny warning">
                                <?= lang('waiting for scope from PI', 'wartet auf Scope vom PI') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted font-size-12 mb-0">
                    <?= lang('No open scope tasks at the moment.', 'Aktuell keine offenen Scope-Aufgaben.') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Permits tasks -->
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-file-text"></i>
                <?= lang('Permits', 'Genehmigungen') ?>
            </h3>
            <p class="text-muted font-size-12 mb-10">
                <?= lang(
                    'Projects with open permit processes and permits that still need ABS validation.',
                    'Projekte mit offenen Genehmigungsprozessen und Genehmigungen mit ausstehender ABS-Validierung.'
                ) ?>
            </p>

            <div class="mb-5">
                <span class="badge <?= $cntPermitsPending ? 'warning' : 'muted' ?>">
                    <?= $cntPermitsPending ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects with permits needed/requested', 'Projekte mit erforderlichen/beantragten Genehmigungen') ?>
                </span>
            </div>
            <div class="mb-10">
                <span class="badge <?= $cntPermitsValid ? 'signal' : 'muted' ?>">
                    <?= $cntPermitsValid ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects with granted permits to validate', 'Projekte mit zu validierenden Genehmigungen') ?>
                </span>
            </div>

            <?php if ($cntPermitsPending || $cntPermitsValid): ?>
                <ul class="list font-size-12 mb-0">
                    <?php foreach (array_slice($cta['permits_pending'] ?? [], 0, 3) as $item):
                        $p   = $item['project'];
                    ?>
                        <li>
                            <a href="<?= e($item['url']) ?>">
                                <strong><?= e($p['name'] ?? '') ?></strong>
                            </a><br>
                            <span class="badge tiny warning">
                                <?= lang('permits in progress', 'Genehmigungen in Bearbeitung') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>

                    <?php foreach (array_slice($cta['permits_validation'] ?? [], 0, 2) as $item):
                        $p   = $item['project'];
                    ?>
                        <li class="mb-5">
                            <a href="<?= e($item['url']) ?>">
                                <strong><?= e($p['name'] ?? '') ?></strong>
                            </a><br>
                            <span class="badge tiny signal">
                                <?= lang('validation by ABS team pending', 'Validierung durch ABS-Team offen') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted font-size-12 mb-0">
                    <?= lang('No open permit tasks at the moment.', 'Aktuell keine offenen Genehmigungsaufgaben.') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<hr class="my-20">

<h2 class="title">
    <i class="ph-duotone ph-clipboard-text"></i>
    <?= lang('Nagoya-relevant projects', 'Nagoya-relevante Projekte') ?>
</h2>
<p class="text-muted font-size-12 mb-10">
    <?= lang(
        'All projects with Nagoya/ABS tracking enabled, including A/B/C labels and permit status.',
        'Alle Projekte mit aktivierter Nagoya/ABS-Verfolgung, inkl. A/B/C-Labels und Genehmigungsstatus.'
    ) ?>
</p>

<?php if (!$totalProjects): ?>
    <div class="box padded text-muted">
        <?= lang('No projects with Nagoya information found.', 'Keine Projekte mit Nagoya-Informationen gefunden.') ?>
    </div>
<?php else: ?>
    <table class="table small" id="nagoya-projects-overview-table">
        <thead>
            <tr>
                <th><?= lang('Project', 'Projekt') ?></th>
                <th><?= lang('Nagoya status', 'Nagoya-Status') ?></th>
                <th><?= lang('Label', 'Label') ?></th>
                <th><?= lang('Countries (ABS)', 'Länder (ABS)') ?></th>
                <th><?= lang('Permits', 'Genehmigungen') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($projects as $p):
                $p = DB::doc2Arr($p);
                $idStr   = (string)($p['_id'] ?? '');
                $nagoya  = $p['nagoya'] ?? [];
                $countries = $nagoya['countries'] ?? [];
                $labelABC  = $nagoya['label'] ?? '';

                $absCountries = 0;
                $permitTotal  = 0;
                $permitOpen   = 0;
                foreach ($countries as $c) {
                    if ($c['abs'] ?? false) {
                        $absCountries++;
                    }
                    foreach ($c['evaluation']['permits'] ?? [] as $perm) {
                        $permitTotal++;
                        if (in_array($perm['status'] ?? '', ['needed', 'requested'])) {
                            $permitOpen++;
                        }
                    }
                }
            ?>
                <tr>
                    <td>
                        <a href="<?= ROOTPATH ?>/proposals/view/<?= $idStr ?>#nagoya">
                            <strong><?= e($p['name'] ?? '') ?></strong>
                        </a><br>
                        <span class="text-muted font-size-12">
                            <?= e($p['id'] ?? '') ?>
                        </span>
                    </td>
                    <td class="font-size-12">
                        <?= Nagoya::badge($p, false) ?>
                    </td>
                    <td class="font-size-12">
                        <?= Nagoya::ABCbadge($labelABC) ?>
                    </td>
                    <td class="font-size-12">
                        <?php if ($absCountries): ?>
                            <span class="badge">
                                <i class="ph ph-globe"></i> <?= $absCountries ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                    <td class="font-size-12">
                        <?php if ($permitTotal): ?>
                            <span class="badge <?= $permitOpen ? 'signal' : 'success' ?>">
                                <?= $permitTotal ?>
                                <?php if ($permitOpen): ?>
                                    (<?= $permitOpen ?> <?= lang('open', 'offen') ?>)
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">–</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="row row-eq-spacing">
    <!-- Country overview (BfN entry point) -->
    <div class="col-md-4">
        <h2 class="title">
            <i class="ph-duotone ph-globe-stand"></i>
            <?= lang('Countries overview', 'Länderübersicht') ?>
        </h2>
        <p class="text-muted font-size-12 mb-10">
            <?= lang(
                'Quick access to projects per country.',
                'Schneller Zugriff auf Projekte pro Land.'
            ) ?>
        </p>

        <?php if (empty($countryStats)): ?>
            <div class="box padded text-muted">
                <?= lang('No countries with ABS-relevant projects yet.', 'Noch keine Länder mit ABS-relevanten Projekten.') ?>
            </div>
        <?php else: ?>
            <table class="table small" id="country-overview-table">
                <thead>
                    <tr>
                        <th><?= lang('Country', 'Land') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // sort countries by number of projects desc
                    $stats = array_values($countryStats);
                    usort($stats, function ($a, $b) {
                        return ($b['projects'] ?? 0) <=> ($a['projects'] ?? 0);
                    });
                    foreach ($stats as $cs):
                        $code      = $cs['code'];
                        $projectsN = $cs['projects'] ?? 0;
                        $labels    = $cs['labels'] ?? ['A' => 0, 'B' => 0, 'C' => 0];
                        $permOpen  = $cs['permits_pending'] ?? 0;
                        $name      = $DB->getCountry($code, lang('name', 'name_de'));
                    ?>
                        <tr>
                            <td>
                                <a href="<?= ROOTPATH ?>/nagoya/country/<?= urlencode($code) ?>">
                                    <strong><?= e($name) ?></strong>
                                </a><br>
                                <small class="text-muted">
                                    <?= $projectsN ?> <?= lang('projects', 'Projekte') ?>
                                    <?php if ($labels['A'] ?? 0): ?>
                                        · <span class="badge tiny danger">A: <?= $labels['A'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($labels['B'] ?? 0): ?>
                                        · <span class="badge tiny warning">B: <?= $labels['B'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($labels['C'] ?? 0): ?>
                                        · <span class="badge tiny success">C: <?= $labels['C'] ?></span>
                                    <?php endif; ?>
                                    <?php if ($permOpen): ?>
                                        · <span class="badge tiny signal">
                                            <i class="ph ph-file-text"></i> <?= $permOpen ?> <?= lang('permits open', 'Genehmigungen offen') ?>
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Permits overview -->
    <div class="col-md-8">
        <h2 class="title">
            <i class="ph-duotone ph-certificate"></i>
            <?= lang('Permits overview', 'Genehmigungsübersicht') ?>
        </h2>
        <p class="text-muted font-size-12 mb-10">
            <?= lang(
                'Quick access to all permits in the system.',
                'Schneller Zugriff auf alle vorhandenen Genehmigungen.'
            ) ?>
        </p>
        <?php

        $permits = $osiris->proposals->aggregate(
            [
                ['$match' => ['nagoya.countries.evaluation.permits' => ['$exists' => true, '$ne' => []]]],
                ['$project' => ['name' => 1, 'nagoya.countries' => 1]],
                ['$unwind' => '$nagoya.countries'],
                ['$unwind' => '$nagoya.countries.evaluation.permits'],
                ['$project' => [
                    '_id' => 0,
                    'projectId' => ['$toString' => '$_id'],
                    'projectName' => '$name',
                    'countryId' => '$nagoya.countries.id',
                    'countryCode' => '$nagoya.countries.code',
                    'permitId' => '$nagoya.countries.evaluation.permits.id',
                    'permitName' => '$nagoya.countries.evaluation.permits.name',
                    'status' => '$nagoya.countries.evaluation.permits.status',
                    'ircc' => '$nagoya.countries.evaluation.permits.ircc',
                    'irccLink' => '$nagoya.countries.evaluation.permits.ircc_link',
                    'identifier' => '$nagoya.countries.evaluation.permits.identifier',
                    'checked' => '$nagoya.countries.evaluation.permits.checked',
                ]]
            ]
        )->toArray();
        if (empty($permits)):
        ?>
            <div class="box padded text-muted">
                <?= lang('No permits found in the system.', 'Keine Genehmigungen im System gefunden.') ?>
            </div>
        <?php else: ?>

            <table class="table small" id="permits-overview-table">
                <thead>
                    <tr>
                        <th><?= lang('Project', 'Projekt') ?></th>
                        <th><?= lang('Country', 'Land') ?></th>
                        <th><?= lang('Permit', 'Genehmigung') ?></th>
                        <th><?= lang('Status', 'Status') ?></th>
                        <th><?= lang('IRCC', 'IRCC') ?> / <?= lang('Identifier', 'Kennung') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($permits as $perm):
                        $pid = $perm['projectId'] ?? null;
                    ?>
                        <tr>
                            <td>
                                <a href="<?= ROOTPATH ?>/proposals/view/<?= e($pid) ?>#nagoya">
                                    <strong><?= e($perm['projectName'] ?? '') ?></strong>
                                </a>
                            </td>
                            <td class="font-size-12">
                                <?php
                                $countryName = $DB->getCountry($perm['countryCode'] ?? '', lang('name', 'name_de'));
                                ?>
                                <i class="ph ph-globe"></i>
                                <?= e($countryName) ?>
                            </td>
                            <td class="font-size-12">
                                <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= e($pid) ?>/<?= urlencode($perm['countryCode'] ?? '') ?>#permit-<?= urlencode($perm['permitId'] ?? '') ?>">
                                    <?= e($perm['permitName'] ?? '–') ?>
                                </a>
                                <?php if (!empty($perm['checked'] ?? null)) { ?>
                                    <span data-toggle="tooltip" data-title="<?= lang('Validated by ABS team', 'Vom ABS-Team validiert') ?>">
                                        <i class="ph-duotone ph-check-circle text-success"></i>
                                    </span>
                                <?php } else { ?>
                                    <span data-toggle="tooltip" data-title="<?= lang('Not yet validated by ABS team', 'Noch nicht vom ABS-Team validiert') ?>">
                                        <i class="ph-duotone ph-clock text-muted"></i>
                                    </span>
                                <?php } ?>
                                
                            </td>
                            <td class="font-size-12">
                                <?= Nagoya::permitStatusBadge($perm['status'] ?? '') ?>
                            </td>
                            <td class="font-size-12">
                                <?php if (!empty($perm['irccLink'] ?? '')) { ?>
                                    <a href="<?= e($perm['irccLink']) ?>" target="_blank" rel="noopener noreferrer" class="link">
                                        <?= e($perm['ircc'] ?? '–') ?>
                                    </a>
                                <?php } elseif (!empty($perm['ircc'] ?? '')) { ?>
                                    <?= e($perm['ircc']) ?>
                                <?php } else { ?>
                                    <?= e($perm['identifier'] ?? '–') ?>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        if ($('#nagoya-projects-overview-table tbody tr').length > 1) {
            $('#nagoya-projects-overview-table').DataTable({
                "order": [
                    [1, "desc"]
                ],
                "pageLength": 10,
                "layout": {
                    "topEnd": null
                }
            });
        }
        if ($('#country-overview-table tbody tr').length > 1) {
            $('#country-overview-table').DataTable({
                "order": [
                    [1, "desc"]
                ],
                "pageLength": 10,
                "layout": {
                    "topEnd": null
                }
            });
        }
        if ($('#permits-overview-table tbody tr').length > 1) {
            $('#permits-overview-table').DataTable({
                "order": [
                    [2, "desc"]
                ],
                "pageLength": 10,
                "layout": {
                    "topEnd": null
                }
            });
        }
    });
</script>