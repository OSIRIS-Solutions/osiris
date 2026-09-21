<?php
$nagoya     = $project['nagoya'] ?? [];
$countries  = DB::doc2Arr($nagoya['countries'] ?? []);
$absCountries = [];
$nonAbsCountries = [];

foreach ($countries as $c) {
    if ($c['abs'] ?? false) {
        $absCountries[] = $c;
    } else {
        $nonAbsCountries[] = $c;
    }
}
?>

<style>
    .table,
    .table th,
    .table td {
        border-color: var(--primary-color);
    }

    .table thead th {
        background-color: var(--primary-color-20);
    }

    .table tbody th {
        width: 20rem;
    }

    .box .header {
        cursor: pointer;
        background-color: var(--primary-color-20);
    }

    .box .header small {
        margin-left: auto;
    }

    .box .header h2 {
        margin: 0;
        padding: 1rem;
    }
</style>

<h1 class="title">
    <i class="ph-duotone ph-scales"></i>
    <?= lang('projects.abs_evaluation_per_country_a_b_c') ?>
</h1>
<p class="text-muted">
    <?= lang('projects.please_review_the_scope_and_abs_information_for_each_abs_relevant_country_a') ?>
</p>
<div class="mb-20">
    <b><?= lang('projects.current_nagoya_status') ?>:</b><br>
    <?= Nagoya::badge(DB::doc2Arr($project), true) ?>
</div>

<form method="post" action="<?= ROOTPATH ?>/crud/nagoya/evaluate-abs/<?= $id ?>">

    <?php if (empty($absCountries)): ?>
        <div class="alert info">
            <?= lang('projects.there_are_currently_no_abs_relevant_countries_for_this_project') ?>
        </div>
    <?php else: ?>

        <?php foreach ($absCountries as $c):
            $cid   = $c['id'] ?? '';
            $code  = $c['code'] ?? '';
            $scope = $c['scope']['groups'] ?? [];
            $review = $c['review'] ?? [];
            $eval   = $c['evaluation'] ?? [];
            $label  = $eval['label'] ?? null;
            $permits = $eval['permits'] ?? [];
            if (empty($permits)) {
                // ensure at least one empty row for UI
                $permits = [
                    ['name' => '', 'status' => '', 'comment' => '']
                ];
            }
        ?>
            <div class="box" id="country-<?= e($cid) ?>">
                <div class="header" onclick="$(this).toggleClass('open').next('.content').toggleClass('hidden');">
                    <h2>
                        <i class="ph-duotone ph-globe-stand"></i>
                        <?= $DB->getCountry($code, lang('common.field_name_language')) ?>
                    </h2>
                </div>

                <div class="content">
                    <h4><?= lang('projects.country_review_nagoya_evaluation') ?></h4>

                    <?php if (isset($review['reviewed_by'])) { ?>
                        <small class="text-muted">
                            <?= lang('projects.review_of_countries_as_part_of_the_abs_evaluation_process_was_conducted_by') ?>
                            <strong><?= e($DB->getNameFromId($review['reviewed_by'] ?? null)) ?></strong>
                            <?= lang('common.on') ?> <?= format_date($review['reviewed'] ?? '') ?>
                        </small>
                    <?php } ?>
                    <div class="mb-10">
                        <strong><?= lang('projects.nagoya_party') ?>:</strong>
                        <?php
                        $nagoyaParty = $review['nagoyaParty'] ?? 'unknown';
                        if ($nagoyaParty === 'yes') {
                            echo '<span class="badge success">' . lang('common.yes') . '</span>';
                        } elseif ($nagoyaParty === 'no') {
                            echo '<span class="badge danger">' . lang('common.no') . '</span>';
                        } else {
                            echo '<span class="badge muted">' . lang('common.unknown') . '</span>';
                        }
                        ?>
                    </div>
                    <div class="mb-10">
                        <strong><?= lang('projects.own_abs_measures_nagoya_evaluation') ?>:</strong>
                        <?php
                        $ownABSMeasures = $review['ownABSMeasures'] ?? 'unknown';
                        if ($ownABSMeasures === 'yes') {
                            echo '<span class="badge success">' . lang('common.yes') . '</span>';
                        } elseif ($ownABSMeasures === 'no') {
                            echo '<span class="badge danger">' . lang('common.no') . '</span>';
                        } else {
                            echo '<span class="badge muted">' . lang('common.unknown') . '</span>';
                        }
                        ?>
                    </div>
                    <div class="small text-muted">
                        <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                    </div>
                    <div>
                        <strong><?= lang('common.comment') ?>:</strong><br>
                        <span><?= nl2br(e($review['comment'] ?? lang('projects.no_comment_provided'))) ?></span>
                    </div>
                </div>

                <hr>

                <div class="content">
                    <!-- Scope overview (read-only) -->
                    <?php if (empty($scope)): ?>
                        <p class="text-muted small">
                            <?= lang('projects.no_scope_information_provided_yet_for_this_country') ?>
                        </p>
                    <?php else: ?>
                        <div class="mb-10">
                            <h4 class="mb-5"><?= lang('projects.scope_overview') ?></h4>

                            <?php if (!empty($review['reviewed_by'])): ?>
                                <p class="font-size-12 text-muted">
                                    <?= lang('projects.country_review_by') ?>
                                    <?= e($DB->getNameFromId($review['reviewed_by']) ?? $review['reviewed_by']) ?>
                                    <?php if (!empty($review['reviewed'])): ?>
                                        <?= lang('common.on') ?> <?= format_date($review['reviewed']) ?>
                                    <?php endif; ?>
                                </p>
                            <?php endif; ?>

                            <?php foreach ($scope as $i => $g): ?>
                                <table class="table small mb-20">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="text-primary">
                                                <?= lang('common.sample_collection') ?> <?= $i + 1 ?>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <?php if (!empty($g['geo'])): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('common.geographical_scope') ?>:</th>
                                                <td><?= nl2br(e($g['geo'])) ?></td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if (!empty($g['temporal']) || !empty($g['temporal_ongoing'])): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('common.temporal_scope') ?>:</th>
                                                <td>
                                                    <?php if (!empty($g['temporal'])): ?>
                                                        <?= e($g['temporal']) ?>
                                                    <?php endif; ?>
                                                    <?php if (!empty($g['temporal_ongoing'])): ?>
                                                        <em><?= lang('projects.ongoing_planned') ?></em>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php
                                        $mat = DB::doc2Arr($g['material'] ?? []);
                                        $util = DB::doc2Arr($g['utilization'] ?? []);
                                        ?>
                                        <?php if (!empty($mat)): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('common.material_scope') ?>:</th>
                                                <td>
                                                    <?= e(implode(', ', $mat)) ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>

                                        <?php if (!empty($util)): ?>
                                            <tr class="mb-2">
                                                <th><?= lang('projects.utilization_scope_nagoya_evaluation') ?>:</th>
                                                <td>
                                                    <?= e(implode(', ', $util)) ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>

                            <?php
                            $atk_used    = $c['scope']['atk_used'] ?? false;
                            $atk_details = $c['scope']['atk_details'] ?? '';
                            $notes       = $c['scope']['notes'] ?? '';
                            ?>
                            <?php if ($atk_used || $atk_details): ?>
                                <div class="mb-5">
                                    <strong><?= lang('common.associated_traditional_knowledge_atk') ?>:</strong><br>
                                    <?php if ($atk_used): ?>
                                        <span class="badge signal">
                                            <?= lang('projects.atk_involved') ?>
                                        </span><br>
                                    <?php endif; ?>
                                    <?php if ($atk_details): ?>
                                        <span>
                                            <?= nl2br(e($atk_details)) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($notes): ?>
                                <div>
                                    <strong><?= lang('projects.additional_notes') ?>:</strong><br>
                                    <?= nl2br(e($notes)) ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <hr class="my-10">

                        <!-- Country-level evaluation (A/B/C + rationale + permits) -->
                        <input type="hidden" name="evaluation[<?= e($cid) ?>][country_id]" value="<?= e($cid) ?>">

                        <div class="form-group mb-10">
                            <label class="font-weight-bold required">
                                <?= lang('projects.classification_for_this_country_a_b_c') ?>
                            </label>
                            <div class="mt-5 small">
                                <label class="d-block">
                                    <input type="radio" name="evaluation[<?= e($cid) ?>][label]" value="A" <?= $label === 'A' ? 'checked' : '' ?>>
                                    <strong>A</strong> – <?= lang('projects.in_scope_of_eu_regulation_nagoya_protocol') ?>
                                </label>
                                <label class="d-block mt-5">
                                    <input type="radio" name="evaluation[<?= e($cid) ?>][label]" value="B" <?= $label === 'B' ? 'checked' : '' ?>>
                                    <strong>B</strong> – <?= lang('projects.in_scope_of_national_abs_measures_only') ?>
                                </label>
                                <label class="d-block mt-5">
                                    <input type="radio" name="evaluation[<?= e($cid) ?>][label]" value="C" <?= $label === 'C' ? 'checked' : '' ?>>
                                    <strong>C</strong> – <?= lang('projects.out_of_scope') ?>
                                </label>
                            </div>
                        </div>

                        <div class="form-group mb-10">
                            <label class="font-weight-bold required">
                                <?= lang('projects.rationale_for_this_country') ?>
                            </label>
                            <small class="d-block text-muted mb-5">
                                <?= lang('projects.please_briefly_justify_the_a_b_c_classification_for_this_country_e_g_type_o') ?>
                            </small>
                            <textarea
                                name="evaluation[<?= e($cid) ?>][rationale]"
                                rows="3"
                                class="form-control"><?= e($eval['rationale'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">
                                <?= lang('projects.abs_permits_for_this_country') ?>
                            </label>
                            <small class="d-block text-muted mb-5">
                                <?= lang('projects.list_any_required_or_already_obtained_abs_permits_you_can_use_free_text_nam') ?>
                            </small>

                            <table class="table table-sm mb-5 nagoya-permits-table" data-country="<?= e($cid) ?>">
                                <thead>
                                    <tr>
                                        <th><?= lang('projects.permit_name_nagoya_evaluation') ?></th>
                                        <th><?= lang('common.status') ?></th>
                                        <th><?= lang('common.comment') ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($permits as $pi => $p): ?>
                                        <tr>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="evaluation[<?= e($cid) ?>][permits][<?= $pi ?>][name]"
                                                    class="form-control form-control-sm"
                                                    value="<?= e($p['name'] ?? '') ?>">
                                            </td>
                                            <td>
                                                <?php $status = $p['status'] ?? ''; ?>
                                                <select
                                                    name="evaluation[<?= e($cid) ?>][permits][<?= $pi ?>][status]"
                                                    class="form-control form-control-sm">
                                                    <option value=""><?= lang('projects.select') ?></option>
                                                    <option value="needed" <?= $status === 'needed'   ? 'selected' : '' ?>><?= lang('common.needed') ?></option>
                                                    <option value="requested" <?= $status === 'requested' ? 'selected' : '' ?>><?= lang('common.requested') ?></option>
                                                    <option value="granted" <?= $status === 'granted'  ? 'selected' : '' ?>><?= lang('common.granted') ?></option>
                                                    <option value="not-applicable" <?= $status === 'not-applicable' ? 'selected' : '' ?>><?= lang('common.not_applicable') ?></option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    name="evaluation[<?= e($cid) ?>][permits][<?= $pi ?>][comment]"
                                                    class="form-control form-control-sm"
                                                    value="<?= e($p['comment'] ?? '') ?>">
                                            </td>
                                            <td class="text-right">
                                                <button type="button" class="btn small text-danger remove-permit-row">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <button type="button"
                                class="btn small outline add-permit-row"
                                data-country="<?= e($cid) ?>">
                                <i class="ph ph-plus"></i>
                                <?= lang('common.add_permit') ?>
                            </button>
                        </div>

                        <?php if (!empty($eval['by']) && !empty($eval['at'])): ?>
                            <div class="small text-muted mt-5">
                                <?= lang('projects.last_evaluation_for_this_country_by') ?>
                                <?= e($DB->getNameFromId($eval['by']) ?? $eval['by']) ?>
                                <?= lang('common.on') ?> <?= format_date($eval['at']) ?>
                            </div>
                        <?php endif; ?>
                        </div>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="mt-20">
        <button type="submit" class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('projects.save_abs_evaluation') ?>
        </button>
    </div>
</form>

<script>
    // simple permit row handling (no dependencies beyond jQuery)
    // comments in English for consistency with your codebase

    $(function() {
        $('.add-permit-row').on('click', function() {
            var cid = $(this).data('country');
            var $table = $('.nagoya-permits-table[data-country="' + cid + '"]');
            var $tbody = $table.find('tbody');
            var rows = $tbody.find('tr');
            var next = rows.length;

            // clone last row as template
            // var $tmpl = rows.last().clone();

            var $tmpl = $(`
                <tr>
                    <td>
                        <input
                            type="text"
                            name="evaluation[${cid}][permits][${next}][name]"
                            class="form-control form-control-sm"
                            value="">
                    </td>
                    <td>
                        <select
                            name="evaluation[${cid}][permits][${next}][status]"
                            class="form-control form-control-sm">
                            <option value="">– select –</option>
                            <option value="needed">Needed</option>
                            <option value="requested">Requested</option>
                            <option value="granted">Granted</option>
                            <option value="not-applicable">Not applicable</option>
                        </select>
                    </td>
                    <td>
                        <input
                            type="text"
                            name="evaluation[${cid}][permits][${next}][comment]"
                            class="form-control form-control-sm"
                            value="">
                    </td>
                    <td class="text-right">
                        <button type="button" class="btn small text-danger remove-permit-row">
                            <i class="ph ph-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
            // clear values
            $tmpl.find('input').val('');
            $tmpl.find('select').val('');

            // update name indices
            $tmpl.find('[name]').each(function() {
                this.name = this.name.replace(/\[permits]\[\d+]/, '[permits][' + next + ']');
            });

            $tbody.append($tmpl);
        });

        $(document).on('click', '.remove-permit-row', function() {
            var $tbody = $(this).closest('tbody');
            var rows = $tbody.find('tr');
            if (rows.length <= 1) {
                // keep at least one row
                $(this).closest('tr').find('input').val('');
                $(this).closest('tr').find('select').val('');
                return;
            }
            $(this).closest('tr').remove();

            // reindex names to keep them compact
            $tbody.find('tr').each(function(idx) {
                $(this).find('[name]').each(function() {
                    this.name = this.name.replace(/\[permits]\[\d+]/, '[permits][' + idx + ']');
                });
            });
        });
    });
</script>
