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
    <?= lang('projects.nagoya_abs_compliance_dashboard') ?>
</h1>

<p class="text-muted mb-20 font-size-14">
    <?= lang('projects.overview_of_all_nagoya_relevant_projects_current_tasks_for_the_abs_complian') ?>
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
    <?= lang('projects.current_tasks') ?>
</h2>

<div class="row row-eq-spacing mt-0 dashboard">
    <!-- Country review open -->
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-map-trifold"></i>
                <?= lang('projects.country_review_open') ?>
            </h3>
            <p class="text-muted font-size-12 mb-10">
                <?= lang('projects.countries_that_still_need_abs_nagoya_relevance_decisions') ?>
            </p>
            <div class="mb-10">
                <span class="badge <?= $cntCountryReview ? 'signal' : 'muted' ?>">
                    <?= $cntCountryReview ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects.open_country_checks_for_projects') ?>
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
                                <?= $DB->getCountry($code, lang('common.field_name_language')) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <?php if ($cntCountryReview > 5): ?>
                        <li class="text-muted">
                            <?= lang('projects.and_more') ?>
                        </li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted font-size-12 mb-0">
                    <?= lang('projects.no_open_country_reviews_at_the_moment') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scope tasks -->
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-crosshair"></i>
                <?= lang('projects.scope_evaluation') ?>
            </h3>
            <p class="text-muted font-size-12 mb-10">
                <?= lang('projects.projects_where_scope_information_is_missing_or_where_scope_needs_to_be_eval') ?>
            </p>

            <div class="mb-5">
                <span class="badge <?= $cntScopeMissing ? 'warning' : 'muted' ?>">
                    <?= $cntScopeMissing ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects.projects_with_missing_scope_researchers') ?>
                </span>
            </div>
            <div class="mb-10">
                <span class="badge <?= $cntScopeReview ? 'signal' : 'muted' ?>">
                    <?= $cntScopeReview ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects.projects_with_scope_ready_for_abs_review') ?>
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
                                <?= lang('projects.abs_review_pending') ?>
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
                                <?= lang('projects.waiting_for_scope_from_pi') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted font-size-12 mb-0">
                    <?= lang('projects.no_open_scope_tasks_at_the_moment') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Permits tasks -->
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-file-text"></i>
                <?= lang('common.permits') ?>
            </h3>
            <p class="text-muted font-size-12 mb-10">
                <?= lang('projects.projects_with_open_permit_processes_and_permits_that_still_need_abs_validat') ?>
            </p>

            <div class="mb-5">
                <span class="badge <?= $cntPermitsPending ? 'warning' : 'muted' ?>">
                    <?= $cntPermitsPending ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects.projects_with_permits_needed_requested') ?>
                </span>
            </div>
            <div class="mb-10">
                <span class="badge <?= $cntPermitsValid ? 'signal' : 'muted' ?>">
                    <?= $cntPermitsValid ?>
                </span>
                <span class="font-size-12 text-muted">
                    <?= lang('projects.projects_with_granted_permits_to_validate') ?>
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
                                <?= lang('projects.permits_in_progress') ?>
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
                                <?= lang('projects.validation_by_abs_team_pending') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="text-muted font-size-12 mb-0">
                    <?= lang('projects.no_open_permit_tasks_at_the_moment') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<hr class="my-20">

<h2 class="title">
    <i class="ph-duotone ph-clipboard-text"></i>
    <?= lang('projects.nagoya_relevant_projects') ?>
</h2>
<p class="text-muted font-size-12 mb-10">
    <?= lang('projects.all_projects_with_nagoya_abs_tracking_enabled_including_a_b_c_labels_and_pe') ?>
</p>

<?php if (!$totalProjects): ?>
    <div class="box padded text-muted">
        <?= lang('projects.no_projects_with_nagoya_information_found') ?>
    </div>
<?php else: ?>
    <table class="table small" id="nagoya-projects-overview-table">
        <thead>
            <tr>
                <th><?= lang('common.project') ?></th>
                <th><?= lang('common.nagoya_status') ?></th>
                <th><?= lang('common.label_nagoya_dashboard_country') ?></th>
                <th><?= lang('projects.countries_abs') ?></th>
                <th><?= lang('common.permits') ?></th>
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
                                    (<?= $permitOpen ?> <?= lang('common.open') ?>)
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
            <?= lang('projects.countries_overview') ?>
        </h2>
        <p class="text-muted font-size-12 mb-10">
            <?= lang('projects.quick_access_to_projects_per_country') ?>
        </p>

        <?php if (empty($countryStats)): ?>
            <div class="box padded text-muted">
                <?= lang('projects.no_countries_with_abs_relevant_projects_yet') ?>
            </div>
        <?php else: ?>
            <table class="table small" id="country-overview-table">
                <thead>
                    <tr>
                        <th><?= lang('common.country') ?></th>
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
                        $name      = $DB->getCountry($code, lang('common.field_name_language'));
                    ?>
                        <tr>
                            <td>
                                <a href="<?= ROOTPATH ?>/nagoya/country/<?= urlencode($code) ?>">
                                    <strong><?= e($name) ?></strong>
                                </a><br>
                                <small class="text-muted">
                                    <?= $projectsN ?> <?= lang('common.projects') ?>
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
                                            <i class="ph ph-file-text"></i> <?= $permOpen ?> <?= lang('projects.permits_open') ?>
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
            <?= lang('projects.permits_overview') ?>
        </h2>
        <p class="text-muted font-size-12 mb-10">
            <?= lang('projects.quick_access_to_all_permits_in_the_system') ?>
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
                <?= lang('projects.no_permits_found_in_the_system') ?>
            </div>
        <?php else: ?>

            <table class="table small" id="permits-overview-table">
                <thead>
                    <tr>
                        <th><?= lang('common.project') ?></th>
                        <th><?= lang('common.country') ?></th>
                        <th><?= lang('common.permit') ?></th>
                        <th><?= lang('common.status') ?></th>
                        <th><?= lang('projects.ircc') ?> / <?= lang('projects.identifier') ?></th>
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
                                $countryName = $DB->getCountry($perm['countryCode'] ?? '', lang('common.field_name_language'));
                                ?>
                                <i class="ph ph-globe"></i>
                                <?= e($countryName) ?>
                            </td>
                            <td class="font-size-12">
                                <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= e($pid) ?>/<?= urlencode($perm['countryCode'] ?? '') ?>#permit-<?= urlencode($perm['permitId'] ?? '') ?>">
                                    <?= e($perm['permitName'] ?? '–') ?>
                                </a>
                                <?php if (!empty($perm['checked'] ?? null)) { ?>
                                    <span data-toggle="tooltip" data-title="<?= lang('projects.validated_by_abs_team_nagoya_dashboard') ?>">
                                        <i class="ph-duotone ph-check-circle text-success"></i>
                                    </span>
                                <?php } else { ?>
                                    <span data-toggle="tooltip" data-title="<?= lang('projects.not_yet_validated_by_abs_team') ?>">
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