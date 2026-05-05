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
    <a href="#workflow-modal" id="wf-mini" class="<?= htmlspecialchars($barState) ?> <?= !empty($actionableIds) ? 'has-action' : '' ?>" style="--workflow-width: <?= count($progress) * 5 ?>rem;">
        <b><?= htmlspecialchars($tpl['name'] ?? $wf['workflow_id']) ?></b>
        <div class="track <?= htmlspecialchars($barState) ?>">
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
                <div class="dot <?= $cls ?>" style="left: <?= round($pct, 2) ?>%;" title="<?= htmlspecialchars($s['label']) ?>">
                    <?php if ($s['state'] === 'approved'): ?><i class="ph ph-check" style="font-size:11px"></i>
                    <?php elseif ($s['id'] === $rejectedStep): ?><i class="ph ph-x" style="font-size:11px"></i>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="clickmask" id="wf-mini-open" aria-label="<?= lang('Show workflow details', 'Workflow-Details anzeigen') ?>"></div>
        </div>
    </a>

    <?php if ($isRejected && $user_activity) { ?>
        <div class="alert info m-20">
            <?= lang('Your activity has been rejected for the following reason:', 'Ihre Aktivität wurde aus folgendem Grund abgelehnt:') ?>
            <pre class="m-0 text-primary"><?= htmlspecialchars($wf['rejectedDetails']['comment'] ?? '') ?></pre>

            <?= lang('You can update your activity and resubmit it for review.', 'Sie können Ihre Aktivität aktualisieren und erneut zur Überprüfung einreichen.') ?>
            <form action="<?= ROOTPATH ?>/crud/activities/workflow/reject-reply/<?= $id ?>" method="post">
                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                <textarea name="comment" class="form-control small" rows="3" placeholder="<?= lang('Your reply to the reviewer', 'Deine Antwort an die Prüfer:in') ?>"></textarea>
                <button class="btn small success mt-5" type="submit"><?= lang('Send reply', 'Antwort senden') ?></button>
                <button class="btn small mt-5" type="button" onclick="$(this).parent().hide()"><?= lang('Cancel', 'Abbrechen') ?></button>
            </form>
        </div>
    <?php } ?>



    <div class="modal" id="workflow-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#close-modal" class="close" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title text-center"><?= htmlspecialchars($tpl['name'] ?? $wf['workflow_id']) ?></h5>

                <div class="quality-control" id="quality-control" style="--workflow-width: <?= count($progress ?? []) * 14 ?>rem">
                    <?php if (!$wf): ?>
                        <p class="text-muted"><?= lang('No workflow attached.', 'Kein Workflow verknüpft.') ?></p>
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
                                    data-step-id="<?= htmlspecialchars($s['id']) ?>"
                                    data-index="<?= intval($s['index']) ?>"
                                    data-required="<?= !empty($s['required']) ? '1' : '0' ?>"
                                    <?= ($orgScope === 'same_org_only') ? 'title="' . lang('Restricted to reviewers from the same organizational unit', 'Nur Prüfer*innen aus der gleichen Organisationseinheit') . '"' : '' ?>>
                                    <div class="wf-circle <?= $cls ?> <?= $userCanApprove ? 'user-can-approve' : $orgScope ?>">
                                        <?php if ($isApproved): ?>
                                            <i class="ph ph-check wf-icon"></i>
                                        <?php elseif ($s['id'] === $rejectedStep): ?>
                                            <i class="ph ph-x wf-icon"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="wf-step-label"><?= htmlspecialchars($s['label']) ?></div>
                                </div>
                                <?php if ($i < count($progress) - 1): ?>
                                    <div class="wf-line"></div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!empty($actionableIds)): ?>
                            <div class="wf-actions" id="wf-actions">
                                <?php foreach ($actionableIds as $sid): ?>
                                    <?php $lbl = htmlspecialchars($tplById[$sid]['label'] ?? $sid); ?>
                                    <div>
                                        <button class="btn text-success border-success btn-approve" data-step-id="<?= htmlspecialchars($sid) ?>">
                                            <i class="ph ph-check"></i> <?= lang('Approve', 'Freigeben') ?>: <?= $lbl ?>
                                        </button>
                                        <button class="btn text-danger border-danger btn-reject" data-step-id="<?= htmlspecialchars($sid) ?>">
                                            <i class="ph ph-x"></i> <?= lang('Reject', 'Zurückweisen') ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php endif; ?>

                        <?php if ($isVerified): ?>
                            <p class="text-success">
                                <?= lang('This activity has been verified.', 'Diese Aktivität wurde freigegeben.') ?>
                            </p>
                        <?php elseif ($isRejected):
                            $rejectionComment = $wf['rejectedDetails']['comment'] ?? '';
                        ?>
                            <p class="text-danger">
                                <?= lang('This activity has been rejected.', 'Diese Aktivität wurde zurückgewiesen.') ?>
                            </p>

                        <?php elseif (empty($actionableIds)): ?>
                            <p class="text-muted">
                                <?= lang('You cannot approve any steps at the moment.', 'Du kannst momentan keine Schritte freigeben.') ?>
                            </p>
                        <?php endif; ?>


                        <?php
                        // show rejection details if exists and user can approve and the step was rejected
                        if (!empty($wf['rejectedDetails']) && (!empty($actionableIds) || $user_activity) && in_array($wf['rejectedDetails']['stepId'], $actionableIds)) { ?>
                            <h5 class="mb-0">
                                <?= lang('Rejection in this step:', 'Zurückweisung in diesem Schritt:') ?>
                            </h5>
                            <div class="rejection-chat">
                                <div class="chat-bubble">
                                    <b><?= lang('Rejected by', 'Zurückgewiesen von') ?> <?= $DB->getNameFromId($wf['rejectedDetails']['by'] ?? '') ?></b>
                                    <div class="text-muted small">
                                        <?= date('d.m.Y', strtotime($wf['rejectedDetails']['at'] ?? '')); ?>
                                    </div>
                                    <div class="mt-5">
                                        <?= nl2br(htmlspecialchars($wf['rejectedDetails']['comment'] ?? '')); ?>
                                    </div>
                                </div>
                                <?php if (!empty($wf['rejectedDetails']['reply'])) { ?>
                                    <div class="chat-bubble">
                                        <b><?= lang('Reply by', 'Antwort von') ?> <?= $DB->getNameFromId($wf['rejectedDetails']['reply']['by'] ?? '') ?></b>
                                        <div class="text-muted small">
                                            <?= date('d.m.Y', strtotime($wf['rejectedDetails']['reply']['at'] ?? '')); ?>
                                        </div>
                                        <div class="mt-5">
                                            <?= nl2br(htmlspecialchars($wf['rejectedDetails']['reply']['comment'] ?? '')); ?>
                                        </div>
                                    </div>
                                <?php } ?>

                                <!-- mark as resolved and delete rejectionDetails -->
                                <?php if (!empty($actionableIds) || $wf['rejectedDetails']['by'] == $_SESSION['username']) { ?>
                                    <form action="<?= ROOTPATH ?>/crud/activities/workflow/reject-resolve/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to mark this rejection as resolved? All comments will be deleted.', 'Möchten Sie diese Zurückweisung wirklich als erledigt markieren? Alle Kommentare werden gelöscht.') ?>');">
                                        <button class="btn small mt-5" type="submit"><?= lang('Mark as resolved and delete comments', 'Als erledigt markieren und Kommentare löschen') ?></button>
                                    </form>
                                <?php } ?>

                            </div>

                        <?php } ?>
                    <?php endif; ?>

                    <?php if ($Settings->hasPermission('workflows.reset')) { ?>
                        <br>
                        <form action="<?= ROOTPATH ?>/crud/activities/workflow/reset/<?= $id ?>" method="post" onsubmit="return confirm('<?= lang('Are you sure you want to reset this workflow?', 'Möchten Sie diesen Workflow wirklich zurücksetzen?') ?>');">
                            <button class="btn danger mt-5" type="submit"><?= lang('Reset workflow', 'Workflow zurücksetzen') ?></button>
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
            const comment = prompt("<?= lang('Please enter a comment', 'Bitte Kommentar eingeben') ?>");
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