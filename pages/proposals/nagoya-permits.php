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
            $identifier = trim($p['ircc'] ?? $p['identifier'] ?? '');

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

// Notizen für dieses Proposal
$permitNotes = DB::doc2Arr($nagoya['permitNotes'] ?? []);
$canAddNotes = true; // later
?>


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


<div class="row row-eq-spacing">

    <div class="col-md-8">

        <h2 class="title">
            <i class="ph-duotone ph-clipboard-text"></i>
            <?= lang('Permits by country', 'Genehmigungen nach Land') ?>
        </h2>
        <?php if ($permitsRequired): ?>
            <div id="nagoya-permits">

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
                                                <th><?= lang('IRCC / Permit number', 'IRCC / Genehmigungsnummer') ?></th>
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
                                                            <div class="font-size-12 text-muted">
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
    </div>
    <div class="col-md-4">
        <h2 class="title">
            <i class="ph-duotone ph-chats-circle"></i>
            <?= lang('Shared notes', 'Gemeinsame Notizen') ?>
        </h2>

        <?php if (!empty($permitNotes)): ?>
            <div class="box padded permit-notes-list mb-10" style="max-height: 60vh; overflow-y:auto;">
                <?php foreach (array_reverse($permitNotes) as $note): ?>
                    <div class="border rounded p-5 mb-5">
                        <div class="d-flex justify-content-between mb-5">
                            <strong><i class="ph-duotone ph-user text-primary"></i> <?= htmlspecialchars($DB->getNameFromId($note['by'] ?? '') ?: ($note['by'] ?? '')) ?></strong>
                            <span class="text-muted"><?= !empty($note['at']) ? format_date($note['at']) : '' ?></span>
                        </div>
                        <div class="">
                            <?= nl2br(htmlspecialchars($note['message'] ?? '')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="box padded text-muted">
                <?= lang('No notes added yet.', 'Noch keine Notizen vorhanden.') ?>
            </div>
        <?php endif; ?>

        <?php if ($canAddNotes): ?>
            <form method="post" action="<?= ROOTPATH ?>/crud/nagoya/add-permit-note/<?= $id ?>" class="box padded">
                <div class="form-group">
                    <label class="font-weight-bold small">
                        <?= lang('Add note', 'Notiz hinzufügen') ?>
                    </label>
                    <textarea
                        name="message"
                        rows="3"
                        class="form-control"
                        placeholder="<?= lang('Short note on communication, decisions or next steps…', 'Kurze Notiz zu Kommunikation, Entscheidungen oder nächsten Schritten…') ?>"></textarea>
                </div>
                <button type="submit" class="btn small primary">
                    <i class="ph ph-paper-plane-right"></i>
                    <?= lang('Save note', 'Notiz speichern') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>


</div>