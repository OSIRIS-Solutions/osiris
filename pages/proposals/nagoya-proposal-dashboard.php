<?php
/**
 * Page to see details on a single project
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /project/<id>
 *
 * @package     OSIRIS
 * @since       1.7.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

// to be included within a proposal and project view page
if (!isset($proposal) && isset($project)) {
    $proposal = $project;
}

$nagoya         = DB::doc2Arr($proposal['nagoya'] ?? []);
$nagoyaStatus         = $nagoya['status'] ?? 'unknown';
$scopeSubmitted = !empty($nagoya['scopeSubmitted']);

$countries        = DB::doc2Arr($nagoya['countries'] ?? []);
$absCountries     = [];
$nonAbsCountries  = [];
$openCountries    = [];
$openCountries    = [];
$scopeBlocks      = 0;
$absWithScope     = 0;
$openCountryReviews = 0;

// NEW: permit/doc stats
$totalPermits        = 0;
$openPermits         = 0;
$totalPermitDocs     = 0;
$countriesWithPermits = 0;
$hasAbsLabel         = false;

foreach ($countries as $c) {
    $eval    = DB::doc2Arr($c['evaluation'] ?? []);
    $permits = DB::doc2Arr($eval['permits'] ?? []);

    if (!empty($eval['label'])) {
        $hasAbsLabel = true;
    }

    if (!empty($permits)) {
        $countriesWithPermits++;
        foreach ($permits as $perm) {
            $totalPermits++;
            $st = $perm['status'] ?? '';
            if (in_array($st, ['needed', 'requested'])) {
                $openPermits++;
            }
            $docs = DB::doc2Arr($perm['docs'] ?? []);
            $totalPermitDocs += count($docs);
        }
    }
    if (is_null($c['abs'] ?? null)) {
        // skip countries with undefined ABS relevance
        $openCountryReviews++;
        $openCountries[] = $c;
    } elseif ($c['abs'] ?? false) {
        $absCountries[] = $c;
        $groups = DB::doc2Arr($c['scope']['groups'] ?? []);
        $scopeBlocks += count($groups);
        if (!empty($groups)) {
            $absWithScope++;
        }
    } else {
        $nonAbsCountries[] = $c;
    }
}

$totalCountries = count($countries);
$totalAbs       = count($absCountries);
$totalNonAbs    = count($nonAbsCountries);
$scopeComplete  = Nagoya::scopeComplete($nagoya);

// NEW: simple 5-step progress
// 1: Countries reviewed, 2: Scope submitted/ABS evaluation, 3: ABS evaluation, 4: Permits pending, 5: Finalised
$stepsTotal = 5;
$stepsDone  = 0;

if ($totalCountries > 0) {
    $stepsDone = 1; // countries done
}
if ($nagoyaStatus == 'researcher-input') {
    $stepsDone = 2; // countries reviewed
}
if ($nagoyaStatus === 'awaiting-abs-evaluation') {
    $stepsDone = 3; // scope submitted / awaiting ABS evaluation
}
if ($nagoyaStatus === 'permits-pending') {
    $stepsDone = 4; // still in ABS review
}
if (in_array($nagoyaStatus, ['compliant', 'out-of-scope', 'not-relevant'])) {
    $stepsDone = 5; // finalised / permits handled / process closed
}

$progressPercent = max(0, min(100, round($stepsDone / $stepsTotal * 100)));
?>

<!-- Header: Status + Summary -->
<div class="text-center font-size-18 my-10">
    <b class="mr-10"><?= lang('ABS status', 'ABS-Status') ?>:</b>
    <?= Nagoya::badge($proposal, true) ?>
</div>

<!-- Progress bar -->
<?php if ($stepsDone > 0): ?>
    <style>
        .wf-bar {
            margin-top: 4rem;
            margin-bottom: 6rem;
        }

        .wf-step-label {
            white-space: unset;
            text-align: center;
            line-height: 1.2;
            text-overflow: inherit;
        }
    </style>
    <div class="wf-bar" id="wf-bar">
        <?php foreach (
            [
                lang('Country Review', 'Länder-Bewertung'),
                lang('Scope Analysis', 'Scope-Analyse'),
                lang('ABS evaluation', 'ABS-Bewertung'),
                lang('Permits pending', 'Genehmigungen ausstehend'),
                lang('Finalised', 'Abgeschlossen'),
            ] as $index => $key
        ) { ?>
            <div class="wf-step <?= ($index + 1) < $stepsDone ? 'done' : (($index + 1) == $stepsDone ? 'current' : 'future') ?>">
                <div class="wf-circle <?= ($index + 1) < $stepsDone ? 'approved done' : (($index + 1) == $stepsDone ? 'current' : 'future any') ?>">
                    <?php if (($index + 1) < $stepsDone) { ?>
                        <i class="ph ph-check"></i>
                    <?php } ?>
                </div>
                <div class="wf-step-label"><?= $key ?></div>
            </div>
            <?php if ($index < $stepsTotal - 1) { ?>
                <div class="wf-line"></div>
            <?php } ?>
        <?php } ?>

    </div>
<?php endif; ?>


<div class="text-center my-20">

    <ul class="font-size-12 horizontal text-muted">
        <?php if ($totalCountries > 0): ?>
            <li>
                <?= $totalCountries ?>
                <?= lang('Countries', 'Länder') ?>
                (<?= lang('thereof', 'davon') ?> <?= $totalAbs ?> <?= lang('ABS-relevant', 'ABS-relevant') ?>)
            </li>
        <?php endif; ?>
        <?php if ($totalAbs > 0): ?>
            <li>
                <?= $scopeBlocks ?>
                <?= lang('Sample collection(s)', 'Probensammlung(en)') ?>
                <?php if ($scopeBlocks > 0 && $absWithScope < $totalAbs): ?>
                    · <?= lang('some ABS countries without scope', 'einige ABS-Länder ohne Scope') ?>
                <?php endif; ?>
            </li>
        <?php endif; ?>
        <?php if ($totalPermits > 0): ?>
            <li>
                <?= $totalPermits ?>
                <?= lang('Permit(s)', 'Genehmigung(en)') ?>
                <?php if ($openPermits > 0): ?>
                    · <?= $openPermits ?> <?= lang('open', 'offen') ?>
                <?php endif; ?>
                <?php if ($totalPermitDocs > 0): ?>
                    · <?= $totalPermitDocs ?> <?= lang('document(s)', 'Dokument(e)') ?>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Kontextabhängige Hinweise / Aktionen -->

<?php if ($user_project): ?>
    <?php if ($nagoyaStatus === 'researcher-input' && !$scopeComplete): ?>
        <div class="alert signal mt-20">
            <?= lang(
                'Please complete the Nagoya scope information so that the ABS Compliance Team can review this project.',
                'Bitte vervollständige die Scope-Informationen zum Nagoya-Protokoll, damit das ABS-Compliance-Team diesen Antrag prüfen kann.'
            ) ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>" class="btn signal mt-5">
                <i class="ph ph-crosshair"></i> <?= lang('Edit scope information', 'Scope-Informationen bearbeiten') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'researcher-input' && $scopeComplete && !$scopeSubmitted): ?>
        <div class="alert info mt-20">
            <?= lang(
                'The scope information is complete but has not been submitted for ABS review yet.',
                'Die Scope-Informationen sind vollständig, wurden aber noch nicht zur ABS-Prüfung eingereicht.'
            ) ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>" class="btn primary mt-5">
                <i class="ph ph-paper-plane-tilt"></i>
                <?= lang('Submit scope for ABS review', 'Scope-Analyse zur ABS-Prüfung einreichen') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'awaiting-abs-evaluation'): ?>
        <div class="alert info mt-20" style="--icon: '\e2b8';">
            <?= lang(
                'You have submitted the scope information. The ABS Compliance Team is now evaluating the project.',
                'Du hast die Scope-Informationen eingereicht. Das ABS-Compliance-Team bewertet nun den Antrag.'
            ) ?>
        </div>
    <?php elseif ($nagoyaStatus === 'permits-pending'): ?>
        <div class="alert warning mt-20" style="--icon: '\e198';">
            <?= lang(
                'There are pending permits related to the Nagoya Protocol.',
                'Es gibt ausstehende Genehmigungen im Zusammenhang mit dem Nagoya-Protokoll.'
            ) ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>" class="btn warning mt-5">
                <i class="ph ph-pencil"></i> <?= lang('Update permit information', 'Genehmigungsinformationen aktualisieren') ?>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($nagoya_perm): ?>
    <?php if ($nagoyaStatus === 'abs-review' && $openCountryReviews > 0): ?>
        <div class="alert signal mt-20" style="--icon: '\e40c';">
            <?= lang(
                'There are countries with pending ABS review. Please complete the country review.',
                'Es gibt Länder mit ausstehender ABS-Bewertung. Bitte schließe die Länderprüfung ab.'
            ) ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $proposal['_id'] ?>" class="btn signal mt-5">
                <i class="ph ph-pencil"></i> <?= lang('Review countries', 'Länder prüfen') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'awaiting-abs-evaluation'): ?>
        <div class="alert signal mt-20" style="--icon: '\e198';">
            <?= lang(
                'The scope information has been submitted and is complete. Please perform the ABS evaluation (A/B/C classification).',
                'Die Scope-Informationen wurden eingereicht und sind vollständig. Bitte führe die ABS-Bewertung (A/B/C-Klassifikation) durch.'
            ) ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-evaluation/<?= $proposal['_id'] ?>" class="btn signal mt-5">
                <i class="ph ph-checks"></i> <?= lang('Open ABS evaluation', 'ABS-Bewertung öffnen') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'permits-pending'): ?>
        <div class="alert warning mt-20" style="--icon: '\e198';">
            <?= lang(
                'There are pending permits related to the Nagoya Protocol. Please review and update the permit information if necessary.',
                'Es gibt ausstehende Genehmigungen im Zusammenhang mit dem Nagoya-Protokoll. Bitte überprüfe und aktualisiere die Genehmigungsinformationen bei Bedarf.'
            ) ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>" class="btn warning mt-5">
                <i class="ph ph-pencil"></i> <?= lang('Review permit information', 'Genehmigungsinformationen prüfen') ?>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- Countries and scope overview -->

<?php if (!empty($countries)): ?>
    <?php
    if (!empty($openCountries)): ?>
        <h4 class="mt-20">
            <i class="ph-duotone ph-globe-stand"></i>
            <?= lang('Open ABS evaluations', 'Offene ABS-Bewertungen') ?>
        </h4>
        <ul class="list-group">
            <?php foreach ($openCountries as $c): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?></strong>
                    </div>
                    <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($totalAbs > 0): ?>
        <h4 class="mt-20">
            <i class="ph-duotone ph-globe-stand"></i>
            <?= lang('ABS-relevant countries', 'ABS-relevante Länder') ?>
        </h4>
        <ul class="list-group mb-15">
            <?php foreach ($absCountries as $c):
                $review     = $c['review'] ?? [];
                $scope      = DB::doc2Arr($c['scope']['groups'] ?? []);
                $numGroups  = count($scope);
                $eval       = DB::doc2Arr($c['evaluation'] ?? []);
                $permits    = DB::doc2Arr($eval['permits'] ?? []);
                $permTotal  = count($permits);
                $permOpen   = 0;
                $permDocs   = 0;
                foreach ($permits as $perm) {
                    if (in_array($perm['status'] ?? '', ['needed', 'requested'])) {
                        $permOpen++;
                    }
                    $permDocs += count(DB::doc2Arr($perm['docs'] ?? []));
                }
                $labelABC = $eval['label'] ?? null;
                $countryId = $c['id'] ?? null;
            ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?></strong>
                        <div class="small text-muted">
                            <?= $numGroups ?>
                            <?= lang('Sample collection(s)', 'Probensammlung(en)') ?>
                            <?php if ($nagoya_perm && !empty($review['comment'])): ?>
                                · <?= htmlspecialchars($review['comment']) ?>
                            <?php endif; ?>
                            <?php if ($permTotal > 0): ?>
                                <?= $permTotal ?> <?= lang('Permit(s)', 'Genehmigung(en)') ?>
                                <?php if ($permOpen > 0): ?>
                                    (<?= $permOpen ?> <?= lang('open', 'offen') ?>)
                                <?php endif; ?>
                                <?php if ($permDocs > 0): ?>
                                    · <?= $permDocs ?> <?= lang('document(s)', 'Dokument(e)') ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($labelABC): ?>
                            <div class="small mt-3">
                                <span class="text-muted ml-5">
                                    <?= lang('ABS classification for this country', 'ABS-Klassifikation für dieses Land') ?>: <?= Nagoya::ABCbadge($labelABC) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($permTotal > 0): ?>
                            <div class="small mt-3">
                                <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>/<?= urlencode($countryId) ?>">
                                    <i class="ph ph-arrow-up-right"></i>
                                    <?= lang('Open permits & documents', 'Genehmigungen & Dokumente öffnen') ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($totalNonAbs > 0): ?>
        <h4 class="mt-10">
            <i class="ph-duotone ph-globe-stand"></i>
            <?= lang('Countries without ABS obligations', 'Länder ohne ABS-Verpflichtungen') ?>
        </h4>
        <ul class="list-group">
            <?php foreach ($nonAbsCountries as $c): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?></strong>
                    </div>
                    <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php else: ?>
    <p class="text-muted"><?= lang('No countries specified yet.', 'Noch keine Länder angegeben.') ?></p>
<?php endif; ?>

<!-- Overall rationale -->
<?php if (!empty($proposal['nagoya']['absRationale'])): ?>
    <div class="mt-20">
        <h6><?= lang('Overall rationale / comments', 'Gesamtbegründung / Kommentare') ?></h6>
        <div class="p-10 bg-light border rounded">
            <?= nl2br(htmlspecialchars($proposal['nagoya']['absRationale'])) ?>
        </div>
    </div>
<?php endif; ?>

<!-- Process history / quick links -->
<hr class="my-15">
<h5 class="mb-5"><?= lang('ABS process history & quick links', 'ABS-Prozessverlauf & Schnellzugriff') ?></h5>
<ul class="horizontal small mb-0">
    <!-- edit countries button -->
    <?php if ($user_project || $nagoya_perm): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries-edit/<?= $proposal['_id'] ?>">
                <i class="ph ph-globe-stand"></i>
                <?= lang('Edit countries', 'Länder bearbeiten') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($totalCountries > 0 && $nagoya_perm): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $proposal['_id'] ?>">
                <i class="ph ph-map-trifold"></i>
                <?= lang('Country review', 'Länderprüfung') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($totalAbs > 0): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>">
                <i class="ph ph-crosshair"></i>
                <?= lang('Scope details', 'Scope-Details') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($hasAbsLabel && $nagoya_perm): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-evaluation/<?= $proposal['_id'] ?>">
                <i class="ph ph-checks"></i>
                <?= lang('ABS evaluation (A/B/C)', 'ABS-Bewertung (A/B/C)') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($countriesWithPermits > 0): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>">
                <i class="ph ph-file-text"></i>
                <?= lang('Permits & documents', 'Genehmigungen & Dokumente') ?>
            </a>
        </li>
    <?php endif; ?>
</ul>