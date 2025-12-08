<?php
include_once BASEPATH . "/php/Nagoya.php";
$nagoya = $project['nagoya'] ?? [];
?>
<h1 class="mb-3"><?= lang('Nagoya Evaluation', 'Nagoya-Bewertung') ?></h1>
<h2 class="subtitle">
    <a href="<?= ROOTPATH ?>/proposals/view/<?= $id ?>">
        <i class="ph ph-arrow-left"></i>
        <?= ($project['name'] ?? '') ?>
    </a>
</h2>

<div class="mb-20">
    <b><?= lang('Current Status', 'Aktueller Status') ?>:</b><br>
    <?= Nagoya::badge(DB::doc2Arr($project), true) ?>

    <?php if (($nagoya['status'] ?? 'unknown') === 'researcher-input' && !($nagoya['review']['researcher-notified'] ?? false)) { ?>
        <!-- notify researcher that ABS check is complete -->
        <form action="<?= ROOTPATH ?>/crud/nagoya/notify-researchers" method="post" class="d-inline-block ml-10">
            <input type="hidden" name="project_id" value="<?= $id ?>">
            <button type="submit" class="btn success">
                <i class="ph ph-bell-ringing"></i>
                <?= lang('Notify applicants that ABS review is complete', 'Antragstellende über abgeschlossene ABS-Bewertung benachrichtigen') ?>
            </button>
        </form>
    <?php } ?>

</div>


<form method="post" action="<?= ROOTPATH ?>/crud/nagoya/review-abs-countries/<?= $id ?>">
    <table class="table">
        <thead>
            <tr>
                <th><?= lang('Country', 'Land') ?></th>
                <th><?= lang('Party to Nagoya?', 'Nagoya-Protokoll?') ?></th>
                <th><?= lang('Own ABS measures?', 'Eigene ABS-Maßnahmen?') ?></th>
                <th><?= lang('Comment', 'Kommentar') ?></th>
                <th><?= lang('Decision', 'Entscheidung') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($nagoya['countries'] ?? [] as $i => $c):
                $r = $c['review'] ?? [];
            ?>
                <tr>
                    <th>
                        <input type="hidden" name="id[]" value="<?= $c['id'] ?>">
                        <?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?>
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
                    <td><textarea name="comment[]" rows="2" class="form-control form-control-sm"><?= htmlspecialchars($r['comment'] ?? '') ?></textarea></td>
                    <td>
                        <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                        <?php if (!empty($r['reviewed_by'] ?? '')) { ?>
                            <small class="d-block text-muted">
                                <?= lang('Last reviewed by', 'Zuletzt bewertet von') ?><br>
                                <?= $DB->getNameFromId($r['reviewed_by']) ?>
                                <?php if (!empty($r['reviewed'] ?? '')) { ?>
                                    <?= lang('on', 'am') ?> <?= format_date($r['reviewed']) ?>
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
                    <label for="overallRationale"><strong><?= lang('Overall rationale / comments', 'Gesamtbegründung / Kommentare') ?></strong></label>
                    <textarea name="overallRationale" rows="4" class="form-control mb-3"><?= htmlspecialchars($nagoya['absRationale'] ?? '') ?></textarea>
                </td>
            </tr>
        </tfoot>
    </table>

    <div class="mt-20">
        <button type="submit" class="btn primary">
            <i class="ph ph-floppy-disk"></i>
            <?= lang('Save review', 'Bewertung speichern') ?>
        </button>
    </div>
</form>