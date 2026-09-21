<?php
/**
 * Page to see details on a single project
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /project/<id>
 *
 * @package     OSIRIS
 * @since       1.7.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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
    <b class="mr-10"><?= lang('projects.abs_status') ?>:</b>
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
                lang('projects.country_review'),
                lang('projects.scope_analysis'),
                lang('projects.abs_evaluation'),
                lang('projects.permits_pending'),
                lang('projects.finalised'),
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
                <?= lang('common.countries') ?>
                (<?= lang('projects.thereof') ?> <?= $totalAbs ?> <?= lang('projects.abs_relevant') ?>)
            </li>
        <?php endif; ?>
        <?php if ($totalAbs > 0): ?>
            <li>
                <?= $scopeBlocks ?>
                <?= lang('common.sample_collection_s') ?>
                <?php if ($scopeBlocks > 0 && $absWithScope < $totalAbs): ?>
                    · <?= lang('projects.some_abs_countries_without_scope') ?>
                <?php endif; ?>
            </li>
        <?php endif; ?>
        <?php if ($totalPermits > 0): ?>
            <li>
                <?= $totalPermits ?>
                <?= lang('common.permit_s') ?>
                <?php if ($openPermits > 0): ?>
                    · <?= $openPermits ?> <?= lang('common.open') ?>
                <?php endif; ?>
                <?php if ($totalPermitDocs > 0): ?>
                    · <?= $totalPermitDocs ?> <?= lang('common.document_s') ?>
                <?php endif; ?>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Kontextabhängige Hinweise / Aktionen -->

<?php if ($user_project): ?>
    <?php if ($nagoyaStatus === 'researcher-input' && !$scopeComplete): ?>
        <div class="alert signal mt-20">
            <?= lang('projects.please_complete_the_nagoya_scope_information_so_that_the_abs_compliance_tea') ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>" class="btn signal mt-5">
                <i class="ph ph-crosshair"></i> <?= lang('projects.edit_scope_information') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'researcher-input' && $scopeComplete && !$scopeSubmitted): ?>
        <div class="alert info mt-20">
            <?= lang('projects.the_scope_information_is_complete_but_has_not_been_submitted_for_abs_review') ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>" class="btn primary mt-5">
                <i class="ph ph-paper-plane-tilt"></i>
                <?= lang('common.submit_scope_for_abs_review') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'awaiting-abs-evaluation'): ?>
        <div class="alert info mt-20" style="--icon: '\e2b8';">
            <?= lang('projects.you_have_submitted_the_scope_information_the_abs_compliance_team_is_now_eva') ?>
        </div>
    <?php elseif ($nagoyaStatus === 'permits-pending'): ?>
        <div class="alert warning mt-20" style="--icon: '\e198';">
            <?= lang('projects.there_are_pending_permits_related_to_the_nagoya_protocol') ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>" class="btn warning mt-5">
                <i class="ph ph-pencil"></i> <?= lang('projects.update_permit_information') ?>
            </a>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($nagoya_perm): ?>
    <?php if ($nagoyaStatus === 'abs-review' && $openCountryReviews > 0): ?>
        <div class="alert signal mt-20" style="--icon: '\e40c';">
            <?= lang('projects.there_are_countries_with_pending_abs_review_please_complete_the_country_rev') ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $proposal['_id'] ?>" class="btn signal mt-5">
                <i class="ph ph-pencil"></i> <?= lang('projects.review_countries') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'awaiting-abs-evaluation'): ?>
        <div class="alert signal mt-20" style="--icon: '\e198';">
            <?= lang('projects.the_scope_information_has_been_submitted_and_is_complete_please_perform_the') ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-evaluation/<?= $proposal['_id'] ?>" class="btn signal mt-5">
                <i class="ph ph-checks"></i> <?= lang('projects.open_abs_evaluation') ?>
            </a>
        </div>
    <?php elseif ($nagoyaStatus === 'permits-pending'): ?>
        <div class="alert warning mt-20" style="--icon: '\e198';">
            <?= lang('projects.there_are_pending_permits_related_to_the_nagoya_protocol_please_review_and') ?>
            <br>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>" class="btn warning mt-5">
                <i class="ph ph-pencil"></i> <?= lang('projects.review_permit_information') ?>
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
            <?= lang('projects.open_abs_evaluations') ?>
        </h4>
        <ul class="list-group">
            <?php foreach ($openCountries as $c): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= $DB->getCountry($c['code'], lang('common.field_name_language')) ?></strong>
                    </div>
                    <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($totalAbs > 0): ?>
        <h4 class="mt-20">
            <i class="ph-duotone ph-globe-stand"></i>
            <?= lang('projects.abs_relevant_countries') ?>
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
                        <strong><?= $DB->getCountry($c['code'], lang('common.field_name_language')) ?></strong>
                        <div class="small text-muted">
                            <?= $numGroups ?>
                            <?= lang('common.sample_collection_s') ?>
                            <?php if ($nagoya_perm && !empty($review['comment'])): ?>
                                · <?= e($review['comment']) ?>
                            <?php endif; ?>
                            <?php if ($permTotal > 0): ?>
                                <?= $permTotal ?> <?= lang('common.permit_s') ?>
                                <?php if ($permOpen > 0): ?>
                                    (<?= $permOpen ?> <?= lang('common.open') ?>)
                                <?php endif; ?>
                                <?php if ($permDocs > 0): ?>
                                    · <?= $permDocs ?> <?= lang('common.document_s') ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($labelABC): ?>
                            <div class="small mt-3">
                                <span class="text-muted ml-5">
                                    <?= lang('common.abs_classification_for_this_country') ?>: <?= Nagoya::ABCbadge($labelABC) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($permTotal > 0): ?>
                            <div class="small mt-3">
                                <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>/<?= urlencode($countryId) ?>">
                                    <i class="ph ph-arrow-up-right"></i>
                                    <?= lang('projects.open_permits_documents') ?>
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
            <?= lang('projects.countries_without_abs_obligations') ?>
        </h4>
        <ul class="list-group">
            <?php foreach ($nonAbsCountries as $c): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= $DB->getCountry($c['code'], lang('common.field_name_language')) ?></strong>
                    </div>
                    <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php else: ?>
    <p class="text-muted"><?= lang('projects.no_countries_specified_yet') ?></p>
<?php endif; ?>

<!-- Overall rationale -->
<?php if (!empty($proposal['nagoya']['absRationale'])): ?>
    <div class="mt-20">
        <h6><?= lang('common.overall_rationale_comments') ?></h6>
        <div class="p-10 bg-light border rounded">
            <?= nl2br(e($proposal['nagoya']['absRationale'])) ?>
        </div>
    </div>
<?php endif; ?>

<!-- Process history / quick links -->
<hr class="my-15">
<h5 class="mb-5"><?= lang('projects.abs_process_history_quick_links') ?></h5>
<ul class="horizontal small mb-0">
    <!-- edit countries button -->
    <?php if ($user_project || $nagoya_perm): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries-edit/<?= $proposal['_id'] ?>">
                <i class="ph ph-globe-stand"></i>
                <?= lang('projects.edit_countries') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($totalCountries > 0 && $nagoya_perm): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $proposal['_id'] ?>">
                <i class="ph ph-map-trifold"></i>
                <?= lang('projects.country_review_nagoya_proposal_dashboard') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($totalAbs > 0): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $proposal['_id'] ?>">
                <i class="ph ph-crosshair"></i>
                <?= lang('projects.scope_details') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($hasAbsLabel && $nagoya_perm): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-evaluation/<?= $proposal['_id'] ?>">
                <i class="ph ph-checks"></i>
                <?= lang('projects.abs_evaluation_a_b_c') ?>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($countriesWithPermits > 0): ?>
        <li>
            <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $proposal['_id'] ?>">
                <i class="ph ph-file-text"></i>
                <?= lang('projects.permits_documents') ?>
            </a>
        </li>
    <?php endif; ?>
</ul>