<?php
include_once BASEPATH . "/php/Workflows.php";

$wf = $activity['workflow'] ?? null;

if ($wf) {
    $wf = DB::doc2Arr($wf);
    $tpl = $osiris->adminWorkflows->findOne(['id' => $wf['workflow_id']]) ?? [];
    $tpl = DB::doc2Arr($tpl);
    $progress = Workflows::view($tpl, $wf); // [{id,label,index,required,state}]
    $total = count($progress);
    $approved = array_sum(array_map(fn($s) => $s['state'] === 'approved' ? 1 : 0, $progress));

    // farblogik
    $isVerified = ($wf['status'] ?? '') === 'verified';
    $isRejected =  ($wf['status'] ?? '') === 'rejected'; // optional
    $barState   = $isVerified ? 'ok' : ($isRejected ? 'bad' : 'neutral');

    $rejectedStep = null;
    if ($isRejected && isset($wf['rejectedDetails']['stepId'])) {
        $rejectedStep = $wf['rejectedDetails']['stepId'];
    }

    // $progress = Workflows::view($tpl, $wf); // enthält id,label,index,required,state
    $currentIndex = Workflows::currentPhaseIndex($tpl, $wf);

    // Map für orgScope/role (Icons/Tooltips)
    $tplById = [];
    foreach (DB::doc2Arr($tpl['steps'] ?? []) as $ts) $tplById[$ts['id']] = $ts;


    $userCtx = [
        'username' => $_SESSION['username'] ?? null,
        'roles'    => $Settings->roles ?? [],
        'units'   => $user_units
    ];

    // Ermitteln, welche Steps in der aktuellen Phase vom User freigegeben werden dürfen
    $actionableIds = [];
    foreach ($progress as $s) {
        $isPendingCurrent = ($s['state'] === 'pending' && intval($s['index']) === $currentIndex);
        if ($isPendingCurrent && Workflows::canApprove(DB::doc2Arr($activity), $tpl, $wf, $s['id'], $userCtx)) {
            $actionableIds[] = $s['id'];
        }
    }
    // Sort to make sure approved steps come first, then by index
    usort($progress, function ($a, $b) use ($actionableIds) {
        // erst approved, dann index
        if ($a['state'] === 'approved' && $b['state'] !== 'approved') return -1;
        if ($a['state'] !== 'approved' && $b['state'] === 'approved') return 1;
        if ($a['index'] === $b['index']) {
            // check if user can approve
            if (in_array($a['id'], $actionableIds ?? [], true) && !in_array($b['id'], $actionableIds ?? [], true)) return -1;
            if (!in_array($a['id'], $actionableIds ?? [], true) && in_array($b['id'], $actionableIds ?? [], true)) return 1;
        }
        return $a['index'] <=> $b['index'];
    });
}
?>
<?php if (!empty($wf) && !empty($progress)): ?>
    <a href="#workflow-modal" id="wf-mini" class="<?= e($barState) ?> <?= !empty($actionableIds) ? 'has-action' : '' ?>" style="--workflow-width: <?= count($progress) * 5 ?>rem;">
        <b><?= e($tpl['name'] ?? $wf['workflow_id']) ?></b>
        <div class="track <?= e($barState) ?>">
            <div class="tick"></div>
            <?php foreach ($progress as $i => $s):
                $pct = $total > 1 ? ($i / ($total - 1)) * 100 : 0; // dot-position
                $cls = '';
                if ($s['state'] === 'approved') {
                    $cls = 'approved';
                } elseif ($s['id'] === $rejectedStep) {
                    $cls = 'rejected';
                } elseif ($s['state'] === 'pending' && intval($s['index']) === $currentIndex) {
                    $cls = 'current';
                } elseif ($s['state'] === 'pending' && intval($s['index']) > $currentIndex) {
                    $cls = 'future';
                }
            ?>
                <div class="dot <?= $cls ?>" style="left: <?= round($pct, 2) ?>%;" title="<?= e($s['label']) ?>">
                    <?php if ($s['state'] === 'approved'): ?><i class="ph ph-check" style="font-size:11px"></i>
                    <?php elseif ($s['id'] === $rejectedStep): ?><i class="ph ph-x" style="font-size:11px"></i>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="clickmask" id="wf-mini-open" aria-label="<?= lang('activities.show_workflow_details') ?>"></div>
        </div>
    </a>

    <?php if ($isRejected && $user_activity) { ?>
        <div class="alert info m-20">
            <?= lang('activities.your_activity_has_been_rejected_for_the_following_reason') ?>
            <pre class="m-0 text-primary"><?= e($wf['rejectedDetails']['comment'] ?? '') ?></pre>

            <?= lang('activities.you_can_update_your_activity_and_resubmit_it_for_review') ?>
            <form action="<?= ROOTPATH ?>/crud/activities/workflow/reject-reply/<?= $id ?>" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                <textarea name="comment" class="form-control small" rows="3" placeholder="<?= lang('common.your_reply_to_the_reviewer') ?>"></textarea>
                <button class="btn small success mt-5" type="submit"><?= lang('common.send_reply') ?></button>
                <button class="btn small mt-5" type="button" onclick="$(this).parent().hide()"><?= lang('action.cancel') ?></button>
            </form>
        </div>
    <?php } ?>



    <div class="modal" id="workflow-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#close-modal" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title text-center"><?= e($tpl['name'] ?? $wf['workflow_id']) ?></h5>

                <div class="quality-control" id="quality-control" style="--workflow-width: <?= count($progress ?? []) * 14 ?>rem">
                    <?php if (!$wf): ?>
                        <p class="text-muted"><?= lang('activities.no_workflow_attached') ?></p>
                    <?php else: ?>
                        <div class="wf-bar" id="wf-bar">
                            <?php foreach ($progress as $i => $s): ?>
                                <?php
                                $isApproved = ($s['state'] === 'approved');
                                $isCurrent  = ($s['state'] === 'pending' && intval($s['index']) === $currentIndex);
                                // $circleCls  = $isApproved ? 'approved' : ($isCurrent ? 'current' : 'future');
                                $ts = $tplById[$s['id']] ?? [];
                                $orgScope = $ts['orgScope'] ?? 'any';
                                $userCanApprove = in_array($s['id'], $actionableIds, true);
                                $cls = '';
                                if ($s['state'] === 'approved') {
                                    $cls = 'approved';
                                } elseif ($s['id'] === $rejectedStep) {
                                    $cls = 'rejected';
                                } elseif ($s['state'] === 'pending' && intval($s['index']) === $currentIndex) {
                                    $cls = 'current';
                                } elseif ($s['state'] === 'pending' && intval($s['index']) > $currentIndex) {
                                    $cls = 'future';
                                }
                                ?>
                                <div class="wf-step <?= $isCurrent ? 'current' : '' ?> <?= $cls ?>"
                                    data-step-id="<?= e($s['id']) ?>"
                                    data-index="<?= intval($s['index']) ?>"
                                    data-required="<?= !empty($s['required']) ? '1' : '0' ?>"
                                    <?= ($orgScope === 'same_org_only') ? 'title="' . lang('activities.restricted_to_reviewers_from_the_same_organizational_unit') . '"' : '' ?>>
                                    <div class="wf-circle <?= $cls ?> <?= $userCanApprove ? 'user-can-approve' : $orgScope ?>">
                                        <?php if ($isApproved): ?>
                                            <i class="ph ph-check wf-icon"></i>
                                        <?php elseif ($s['id'] === $rejectedStep): ?>
                                            <i class="ph ph-x wf-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wf-step-label"><?= e($s['label']) ?></div>
                                </div>
                                <?php if ($i < count($progress) - 1): ?>
                                    <div class="wf-line"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($actionableIds)): ?>
                            <div class="wf-actions" id="wf-actions">
                                <?php foreach ($actionableIds as $sid): ?>
                                    <?php $lbl = e($tplById[$sid]['label'] ?? $sid); ?>
                                    <div>
                                        <button class="btn text-success border-success btn-approve" data-step-id="<?= e($sid) ?>">
                                            <i class="ph ph-check"></i> <?= lang('common.approve') ?>: <?= $lbl ?>
                                        </button>
                                        <button class="btn text-danger border-danger btn-reject" data-step-id="<?= e($sid) ?>">
                                            <i class="ph ph-x"></i> <?= lang('activities.reject') ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>

                        <?php if ($isVerified): ?>
                            <p class="text-success">
                                <?= lang('activities.this_activity_has_been_verified') ?>
                            </p>
                        <?php elseif ($isRejected):
                            $rejectionComment = $wf['rejectedDetails']['comment'] ?? '';
                        ?>
                            <p class="text-danger">
                                <?= lang('activities.this_activity_has_been_rejected') ?>
                            </p>

                        <?php elseif (empty($actionableIds)): ?>
                            <p class="text-muted">
                                <?= lang('activities.you_cannot_approve_any_steps_at_the_moment') ?>
                            </p>
                        <?php endif; ?>


                        <?php
                        // show rejection details if exists and user can approve and the step was rejected
                        if (!empty($wf['rejectedDetails']) && (!empty($actionableIds) || $user_activity) && in_array($wf['rejectedDetails']['stepId'], $actionableIds)) { ?>
                            <h5 class="mb-0">
                                <?= lang('activities.rejection_in_this_step') ?>
                            </h5>
                            <div class="rejection-chat">
                                <div class="chat-bubble">
                                    <b><?= lang('activities.rejected_by') ?> <?= $DB->getNameFromId($wf['rejectedDetails']['by'] ?? '') ?></b>
                                    <div class="text-muted small">
                                        <?= date('d.m.Y', strtotime($wf['rejectedDetails']['at'] ?? '')); ?>
                                    </div>
                                    <div class="mt-5">
                                        <?= nl2br(e($wf['rejectedDetails']['comment'] ?? '')); ?>
                                    </div>
                                </div>
                                <?php if (!empty($wf['rejectedDetails']['reply'])) { ?>
                                    <div class="chat-bubble">
                                        <b><?= lang('activities.reply_by') ?> <?= $DB->getNameFromId($wf['rejectedDetails']['reply']['by'] ?? '') ?></b>
                                        <div class="text-muted small">
                                            <?= date('d.m.Y', strtotime($wf['rejectedDetails']['reply']['at'] ?? '')); ?>
                                        </div>
                                        <div class="mt-5">
                                            <?= nl2br(e($wf['rejectedDetails']['reply']['comment'] ?? '')); ?>
                                        </div>
                                    </div>
                                <?php } ?>

                                <!-- mark as resolved and delete rejectionDetails -->
                                <?php if (!empty($actionableIds) || $wf['rejectedDetails']['by'] == $_SESSION['username']) { ?>
                                    <form action="<?= ROOTPATH ?>/crud/activities/workflow/reject-resolve/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('activities.are_you_sure_you_want_to_mark_this_rejection_as_resolved_all_comments_will') ?>');">
                                        <button class="btn small mt-5" type="submit"><?= lang('activities.mark_as_resolved_and_delete_comments') ?></button>
                                    </form>
                                <?php } ?>

                            </div>

                        <?php } ?>
                    <?php endif; ?>

                    <?php if ($Settings->hasPermission('workflows.reset')) { ?>
                        <br>
                        <form action="<?= ROOTPATH ?>/crud/activities/workflow/reset/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('activities.are_you_sure_you_want_to_reset_this_workflow') ?>');">
                            <button class="btn danger mt-5" type="submit"><?= lang('activities.reset_workflow') ?></button>
                        </form>
                    <?php } ?>

                </div>
                <div class="text-right mt-20">
                    <a href="#close-modal" class="btn mr-5" role="button">Close</a>
                </div>
            </div>
        </div>
    </div>



    <style>
        .pills {
            top: 9rem;
        }
    </style>
<?php endif; ?>


<script>
    (function() {
        const activityId = <?= json_encode((string)$activity['_id']) ?>;

        $(document).on('click', '.btn-approve', function() {
            const stepId = $(this).data('step-id');
            const $btns = $('.btn-approve,.btn-reject').prop('disabled', true);
            $.post('<?= ROOTPATH ?>/crud/activities/workflow/approve/' + encodeURIComponent(activityId), {
                    stepId
                },
                function(res) {
                    if (res.status === 'ok') {
                        location.reload(); // mehrere parallele Phasen sauber neu berechnen
                    } else {
                        alert(res.error || 'Error');
                        $btns.prop('disabled', false);
                    }
                }, 'json'
            ).fail(function(xhr) {
                alert(xhr.responseJSON?.error || xhr.statusText);
                $btns.prop('disabled', false);
            });
        });

        $(document).on('click', '.btn-reject', function() {
            const stepId = $(this).data('step-id');
            const comment = prompt("<?= lang('activities.please_enter_a_comment') ?>");
            if (comment === null) return;
            const $btns = $('.btn-approve,.btn-reject').prop('disabled', true);
            $.post('<?= ROOTPATH ?>/crud/activities/workflow/reject/' + encodeURIComponent(activityId), {
                    stepId,
                    comment
                },
                function(res) {
                    if (res.status === 'ok') {
                        location.reload();
                    } else {
                        alert(res.error || 'Error');
                        $btns.prop('disabled', false);
                    }
                }, 'json'
            ).fail(function(xhr) {
                alert(xhr.responseJSON?.error || xhr.statusText);
                $btns.prop('disabled', false);
            });
        });
    })();
</script>