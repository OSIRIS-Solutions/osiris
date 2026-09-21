<?php
include_once BASEPATH . "/php/Nagoya.php";
$nagoya = $project['nagoya'] ?? [];
?>
<h1 class="mb-3"><?= lang('common.nagoya_evaluation') ?></h1>
<h2 class="subtitle">
    <a href="<?= ROOTPATH ?>/proposals/view/<?= $id ?>">
        <i class="ph ph-arrow-left"></i>
        <?= ($project['name'] ?? '') ?>
    </a>
</h2>

<div class="mb-20">
    <b><?= lang('common.current_status') ?>:</b><br>
    <?= Nagoya::badge(DB::doc2Arr($project), true) ?>

    <?php if (($nagoya['status'] ?? 'unknown') === 'researcher-input' && !($nagoya['review']['researcher-notified'] ?? false)) { ?>
        <!-- notify researcher that ABS check is complete -->
        <form action="<?= ROOTPATH ?>/crud/nagoya/notify-researchers" method="post" class="d-inline-block ml-10">
            <input type="hidden" name="project_id" value="<?= $id ?>">
            <button type="submit" class="btn success">
                <i class="ph ph-bell-ringing"></i>
                <?= lang('projects.notify_applicants_that_abs_review_is_complete') ?>
            </button>
        </form>
    <?php } ?>

</div>


<form method="post" action="<?= ROOTPATH ?>/crud/nagoya/review-abs-countries/<?= $id ?>">
    <table class="table">
        <thead>
            <tr>
                <th><?= lang('common.country') ?></th>
                <th><?= lang('projects.party_to_nagoya') ?></th>
                <th><?= lang('projects.own_abs_measures') ?></th>
                <th><?= lang('common.comment') ?></th>
                <th><?= lang('projects.decision') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($nagoya['countries'] ?? [] as $i => $c):
                $r = $c['review'] ?? [];
            ?>
                <tr>
                    <th>
                        <input type="hidden" name="id[]" value="<?= $c['id'] ?>">
                        <?= $DB->getCountry($c['code'], lang('common.field_name_language')) ?>
                    </th>
                    <td>
                        <select name="nagoyaParty[]" class="form-control form-control-sm">
                            <?php foreach (['unknown', 'yes', 'no'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($r['nagoyaParty'] ?? 'unknown') === $opt) ? 'selected' : ''; ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <select name="ownABSMeasures[]" class="form-control form-control-sm">
                            <?php foreach (['unknown', 'yes', 'no'] as $opt): ?>
                                <option value="<?= $opt ?>" <?= (($r['ownABSMeasures'] ?? 'unknown') === $opt) ? 'selected' : ''; ?>><?= ucfirst($opt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><textarea name="comment[]" rows="2" class="form-control form-control-sm"><?= e($r['comment'] ?? '') ?></textarea></td>
                    <td>
                        <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                        <?php if (!empty($r['reviewed_by'] ?? '')) { ?>
                            <small class="d-block text-muted">
                                <?= lang('projects.last_reviewed_by') ?><br>
                                <?= $DB->getNameFromId($r['reviewed_by']) ?>
                                <?php if (!empty($r['reviewed'] ?? '')) { ?>
                                    <?= lang('common.on') ?> <?= format_date($r['reviewed']) ?>
                                <?php } ?>
                            </small>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">
                    <label for="overallRationale"><strong><?= lang('common.overall_rationale_comments') ?></strong></label>
                    <textarea name="overallRationale" rows="4" class="form-control mb-3"><?= e($nagoya['absRationale'] ?? '') ?></textarea>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="mt-20">
        <button type="submit" class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('projects.save_review') ?>
        </button>
    </div>
</form>