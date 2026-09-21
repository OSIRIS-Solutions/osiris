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
    <?= lang('projects.abs_overview_for') ?>
    <?= e($countryName) ?>
    <span class="text-muted font-size-14">(<?= e($code) ?>)</span>
</h1>

<a href="<?= ROOTPATH ?>/nagoya"><i class="ph ph-arrow-left"></i> <?= lang('projects.back_to_dashboard') ?></a>

<p class="text-muted mb-0">
    <?= lang('projects.this_page_lists_all_nagoya_abs_relevant_projects_for_this_country_including') ?>
</p>
<div class="row row-eq-spacing dashboard mt-0">
    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-clipboard-text"></i>
                <?= lang('common.projects') ?>
            </h3>
            <p class="mb-5">
                <span class="badge"><?= $projectCount ?></span>
                <span class="text-muted font-size-12">
                    <?= lang('projects.projects_with_this_country_in_nagoya_scope') ?>
                </span>
            </p>
            <p class="font-size-12 text-muted mb-0">
                <?php if ($projectCount === 0): ?>
                    <?= lang('projects.no_projects_found_for_this_country') ?>
                <?php else: ?>
                    <?= lang('projects.click_on_a_project_name_to_open_the_nagoya_details') ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box padded">
            <h3 class="title">
                <i class="ph-duotone ph-squares-four"></i>
                <?= lang('projects.a_b_c_labels') ?>
            </h3>
            <p class="font-size-12 text-muted mb-10">
                <?= lang('projects.current_abs_classification_for_projects_in_this_country') ?>
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
                <?= lang('projects.permits_summary') ?>
            </h3>
            <p class="font-size-12 text-muted mb-10">
                <?= lang('projects.overview_of_all_permits_linked_to_projects_in_this_country') ?>
            </p>
            <ul class="list-unstyled font-size-12 mb-5">
                <li>
                    <span class="badge"><?= (int)$permitStats['total'] ?></span>
                    <?= lang('projects.permits_in_total') ?>
                </li>
                <li>
                    <span class="badge warning"><?= (int)$permitStats['needed'] + (int)$permitStats['requested'] ?></span>
                    <?= lang('projects.permits_needed_requested') ?>
                </li>
                <li>
                    <span class="badge success"><?= (int)$permitStats['granted'] ?></span>
                    <?= lang('projects.granted_permits') ?>
                </li>
                <li>
                    <span class="badge muted"><?= (int)$permitStats['notApplicable'] ?></span>
                    <?= lang('projects.marked_as_not_applicable') ?>
                </li>
            </ul>
            <p class="font-size-12 text-muted mb-0">
                <i class="ph ph-paperclip"></i>
                <?= (int)$permitStats['docs'] ?>
                <?= lang('projects.documents_uploaded_for_permits') ?>
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
            <?= lang('projects.projects_with_abs_relevance_in_this_country') ?>
        </h2>
        <?php if (!$projectCount): ?>
            <div class="box padded text-muted">
                <?= lang('projects.no_projects_found') ?>
            </div>
        <?php else: ?>
                <table class="table" id="nagoya-projects-overview-table">
                    <thead>
                        <tr>
                            <th><?= lang('common.project') ?></th>
                            <th><?= lang('common.label_nagoya_dashboard_country') ?></th>
                            <th><?= lang('common.nagoya_status') ?></th>
                            <th><?= lang('common.permits') ?></th>
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
                                                (<?= $permOpen ?> <?= lang('common.open') ?>)
                                            <?php endif; ?>
                                        </span>
                                        <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $idStr ?>/<?= urlencode($c['id'] ?? '') ?>" class="small">
                                            <i class="ph ph-arrow-up-right"></i>
                                            <?= lang('common.details') ?>
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
            <?= lang('projects.all_permits_for_this_country') ?>
        </h2>
        <?php if (empty($projectsForCountry) || $permitStats['total'] === 0): ?>
            <div class="box padded text-muted">
                <?= lang('projects.no_permits_recorded_for_this_country_yet_nagoya_dashboard_country') ?>
            </div>
        <?php else: ?>
            <table class="table table-sm font-size-12" id="nagoya-permits-overview-table">
                <thead>
                    <tr>
                        <th><?= lang('common.project') ?></th>
                        <th><?= lang('common.permit') ?></th>
                        <th><?= lang('common.status') ?></th>
                        <th><?= lang('common.docs') ?></th>
                        <th><?= lang('common.abs_check') ?></th>
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
                                $statusLabel = lang('common.needed');
                                $statusClass = 'badge tiny warning';
                            } elseif ($status === 'requested') {
                                $statusLabel = lang('common.requested');
                                $statusClass = 'badge tiny signal';
                            } elseif ($status === 'granted') {
                                $statusLabel = lang('common.granted');
                                $statusClass = 'badge tiny success';
                            } elseif ($status === 'not-applicable') {
                                $statusLabel = lang('common.not_applicable');
                                $statusClass = 'badge tiny muted';
                            } else {
                                $statusLabel = lang('common.unknown');
                                $statusClass = 'badge tiny muted';
                            }
                    ?>
                            <tr>
                                <td>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $idStr ?>/<?= urlencode($c['id'] ?? '') ?>">
                                        <?= e($p['name'] ?? '') ?>
                                    </a>
                                </td>
                                <td><?= e($name ?: lang('projects.unnamed')) ?></td>
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
                                                <?= lang('common.validated') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge tiny warning">
                                                <i class="ph ph-warning"></i>
                                                <?= lang('common.needs_check') ?>
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