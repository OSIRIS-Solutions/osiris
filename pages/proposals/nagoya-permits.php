<?php
$nagoya      = $project['nagoya'] ?? [];
$countries   = DB::doc2Arr($nagoya['countries'] ?? []);
$absCountries = [];
$permitStats  = [
    'total'    => 0,
    'pending'  => 0,   // needed/requested
    'granted'  => 0,
    'na'       => 0,
];

foreach ($countries as $c) {
    if (!($c['abs'] ?? false)) continue;
    $cid   = $c['id'] ?? null;
    $eval  = DB::doc2Arr($c['evaluation'] ?? []);
    $perms = DB::doc2Arr($eval['permits'] ?? []);
    if (!$cid) continue;

    $permits = [];
    if (is_array($perms)) {
        foreach ($perms as $p) {
            $p = DB::doc2Arr($p);
            $name   = trim($p['name'] ?? '');
            $status = trim($p['status'] ?? '');
            $comment = trim($p['comment'] ?? '');
            $identifier = trim($p['identifier'] ?? '');

            if ($name === '' && $status === '' && $comment === '' && $identifier === '') {
                continue;
            }

            $permitStats['total']++;

            if ($status === 'granted') {
                $permitStats['granted']++;
            } elseif ($status === 'needed' || $status === 'requested') {
                $permitStats['pending']++;
            } elseif ($status === 'not-applicable') {
                $permitStats['na']++;
            }

            $permits[] = $p;
        }
    }

    $c['evaluation']['permits'] = $permits;
    $absCountries[] = $c;
}
$permitsRequired = $permitStats['total'] > 0;

// Dokumente für alle Nagoya-Permits dieses Proposals holen
$permitDocCounts = [];
$cursor = $osiris->uploads->find([
    'type' => 'nagoya-permit',
    'id'   => $id, // proposal ID
]);

foreach ($cursor as $doc) {
    $pid = $doc['permit_id'] ?? null;
    if (!$pid) continue;
    if (!isset($permitDocCounts[$pid])) {
        $permitDocCounts[$pid] = 0;
    }
    $permitDocCounts[$pid]++;
}
?>

<?php if ($permitsRequired): ?>
    <div id="nagoya-permits">

        <!-- Header: Status + Statistiken -->
        <div class="d-flex justify-content-between align-items-center mb-10">
            <div>
                <h1 class="title mb-0">
                    <i class="ph-duotone ph-file-text"></i>
                    <?= lang('ABS permits overview', 'ABS-Genehmigungen – Übersicht') ?>
                </h1>
                <div class="small text-muted">
                    <?= lang(
                        'Overview of required and granted ABS permits across all ABS-relevant countries.',
                        'Übersicht der benötigten und erteilten ABS-Genehmigungen über alle ABS-relevanten Länder.'
                    ) ?>
                </div>
            </div>
            <div class="text-right small text-muted">
                <div>
                    <?= lang('Total permits:', 'Gesamtanzahl Genehmigungen:') ?>
                    <strong><?= $permitStats['total'] ?></strong>
                </div>
                <div>
                    <?= lang('Pending:', 'Ausstehend:') ?>
                    <small class="badge signal"><?= $permitStats['pending'] ?></small>
                    &nbsp;
                    <?= lang('Granted:', 'Erteilt:') ?>
                    <small class="badge success"><?= $permitStats['granted'] ?></small>
                </div>
            </div>
        </div>

        <!-- Hinweis für Forschende / ABS Team je nach Rolle -->
        <?php if (!$Settings->hasPermission('nagoya.view')): ?>
            <div class="alert info mb-15">
                <?= lang(
                    'You can help the ABS Compliance Team by uploading permit documents and keeping basic permit information up to date.',
                    'Du kannst das ABS-Compliance-Team unterstützen, indem du Genehmigungsdokumente hochlädst und grundlegende Informationen zu Genehmigungen aktuell hältst.'
                ) ?>
            </div>
        <?php elseif ($Settings->hasPermission('nagoya.view')): ?>
            <div class="alert signal mb-15 small">
                <?= lang(
                    'This section shows all permits per country. Use the ABS permit details view to complete and validate information.',
                    'Dieser Abschnitt zeigt alle Genehmigungen pro Land. Nutze die ABS-Genehmigungsansicht, um Informationen zu vervollständigen und zu validieren.'
                ) ?>
            </div>
        <?php endif; ?>

        <!-- Länder & Permit-Tabelle -->
        <?php if (empty($absCountries)): ?>
            <p class="text-muted">
                <?= lang('There are no ABS-related permits for this project yet.', 'Für dieses Projekt sind bisher keine ABS-relevanten Genehmigungen erfasst.') ?>
            </p>
        <?php else: ?>

            <?php foreach ($absCountries as $c):
                $cid   = $c['id'] ?? '';
                $code  = $c['code'] ?? '';
                $eval  = $c['evaluation'] ?? [];
                $permits = $eval['permits'] ?? [];
                $clabel = $eval['label'] ?? null;
            ?>
                <div class="box padded">
                    <h3 class="title">
                        <i class="ph-duotone ph-globe-stand"></i>
                        <?= $DB->getCountry($code, lang('name', 'name_de')) ?>
                    </h3>
                    <div class="d-flex justify-content-between align-items-center mb-10">
                        <div>
                            <div class="small text-muted mt-1">
                                <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $project['_id'] ?>/<?= urlencode($cid) ?>"
                                class="btn primary">
                                <i class="ph ph-clipboard-text"></i>
                                <?= lang('Manage permits', 'Genehmigungen verwalten') ?>
                            </a>

                            <?php if ($Settings->hasPermission('nagoya.view')): ?>
                                <a href="<?= ROOTPATH ?>/proposals/nagoya-evaluation/<?= $project['_id'] ?>#country-<?= htmlspecialchars($cid) ?>"
                                    class="btn">
                                    <i class="ph ph-scales"></i>
                                    <?= lang('Open ABS details', 'ABS-Details öffnen') ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (empty($permits)): ?>
                        <p class="text-muted small mb-0">
                            <?= lang('No permits recorded for this country yet.', 'Für dieses Land wurden noch keine Genehmigungen erfasst.') ?>
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table small simple mb-0">
                                <thead>
                                    <tr>
                                        <th><?= lang('Permit', 'Genehmigung') ?></th>
                                        <th><?= lang('Status', 'Status') ?></th>
                                        <th><?= lang('Identifier', 'Kennung') ?></th>
                                        <th><?= lang('Docs', 'Dokumente') ?></th>
                                        <?php if ($Settings->hasPermission('nagoya.view')): ?>
                                            <th><?= lang('ABS check', 'ABS-Prüfung') ?></th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($permits as $p):
                                        $pid       = $p['id'] ?? '';
                                        $status    = $p['status'] ?? '';
                                        $name      = $p['name'] ?? '';
                                        $identifier = $p['identifier'] ?? '';
                                        $checked   = !empty($p['checked']);
                                        $docCount   = $pid ? ($permitDocCounts[$pid] ?? 0) : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($name ?: lang('Unnamed permit', 'Unbenannte Genehmigung')) ?></strong>
                                                <?php if (!empty($p['comment'])): ?>
                                                    <div class="small text-muted">
                                                        <?= htmlspecialchars($p['comment']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small">
                                                <?php
                                                $statusLabel = '';
                                                $statusClass = 'badge muted';

                                                if ($status === 'needed') {
                                                    $statusLabel = lang('Needed', 'Erforderlich');
                                                    $statusClass = 'badge signal';
                                                } elseif ($status === 'requested') {
                                                    $statusLabel = lang('Requested', 'Beantragt');
                                                    $statusClass = 'badge signal';
                                                } elseif ($status === 'granted') {
                                                    $statusLabel = lang('Granted', 'Erteilt');
                                                    $statusClass = 'badge success';
                                                } elseif ($status === 'not-applicable') {
                                                    $statusLabel = lang('Not applicable', 'Nicht zutreffend');
                                                    $statusClass = 'badge muted';
                                                } else {
                                                    $statusLabel = lang('Unknown', 'Unbekannt');
                                                }
                                                ?>
                                                <span class="<?= $statusClass ?>"><?= $statusLabel ?></span>
                                            </td>
                                            <td class="small">
                                                <?= htmlspecialchars($identifier ?: '–') ?>
                                            </td>
                                            <td class="small">
                                                <?php if ($docCount > 0): ?>
                                                    <span class="badge tiny">
                                                        <i class="ph ph-paperclip"></i> <?= $docCount ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">–</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($Settings->hasPermission('nagoya.view')): ?>
                                                <td class="small">
                                                    <?php if ($status === 'granted'): ?>
                                                        <?php if ($checked): ?>
                                                            <small class="badge success">
                                                                <i class="ph ph-check"></i>
                                                                <?= lang('validated', 'validiert') ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <small class="badge signal">
                                                                <i class="ph ph-warning"></i>
                                                                <?= lang('needs check', 'Prüfung offen') ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">–</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

<?php else: ?>
    <!-- No permits required -->
    <div class="alert info mt-20">
        <?= lang(
            'No ABS permits are required for this project based on the current evaluation.',
            'Basierend auf der aktuellen Bewertung sind für dieses Projekt keine ABS-Genehmigungen erforderlich.'
        ) ?>
    </div>

<?php endif; ?>