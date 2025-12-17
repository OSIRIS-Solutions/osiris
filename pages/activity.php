<?php

/**
 * Page to see details on one activity
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/view/<activity_id>
 *
 * @package     OSIRIS
 * @since       1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

use chillerlan\QRCode\{QRCode, QROptions};

include_once BASEPATH . "/php/Modules.php";

$Modules = new Modules($doc);

// check if this is an ongoing activity type
$ongoing = false;
$sws = false;
$supervisorThesis = false;

$typeArr = $Format->typeArr;
$upload_possible = $typeArr['upload'] ?? true;
$subtypeArr = $Format->subtypeArr;
$typeModules = DB::doc2Arr($subtypeArr['modules'] ?? array());
$typeFields = $Modules->getFields();

foreach ($typeModules as $m) {
    if (str_ends_with($m, '*')) $m = str_replace('*', '', $m);
    if ($m == 'date-range-ongoing') $ongoing = true;
    if ($m == 'supervisor') $sws = true;
    if ($m == 'supervisor-thesis') $supervisorThesis = true;
}

$projects = [];
if (isset($activity['projects']) && count($activity['projects']) > 0) {
    $projects = $osiris->projects->find(
        ['_id' => ['$in' => $activity['projects']]],
        ['projection' => ['_id' => 1, 'name' => 1, 'start' => 1, 'end' => 1, 'title' => 1, 'funder' => 1]]
    )->toArray();
}

$guests_involved = boolval($subtypeArr['guests'] ?? false);
$guests = $doc['guests'] ?? [];
// if ($guests_involved)
//     $guests = $osiris->guests->find(['activity' => $id])->toArray();

$edit_perm = ($user_activity || $Settings->hasPermission('activities.edit'));
$tagName = '';
if ($Settings->featureEnabled('tags')) {
    $tagName = $Settings->tagLabel();
}
?>

<?php if ($Settings->featureEnabled('quality-workflow', false) && ($user_activity || $Settings->hasPermission('workflows.view'))) { ?>
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

        // Nimm deinen bestehenden User-Kontext
        $units = DB::doc2Arr($USER['units'] ?? []);
        if (!empty($units)) {
            $units = array_column($units, 'unit');
        }
        $userCtx = [
            'username' => $_SESSION['username'] ?? null,
            'roles'    => $Settings->roles ?? [],
            'units'   => $units
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

        // render steps for modal and wf-mini
        // $all_steps = [];
        // foreach ($progress as $i => $s):
        //     $all_steps[] = [
        //         'id'       => $s['id'],
        //         'label'    => $s['label'],
        //         'index'    => intval($s['index'] ?? 0),
        //         'required' => !empty($s['required']),
        //         'orgScope' => ($s['orgScope'] ?? 'any'),
        //     ];
        // endforeach;

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

<?php
} ?>

<style>
    [class^="col-"] .box {
        margin: 0;
        /* height: 100%; */
    }

    .btn-toolbar {
        margin: 0 0 1rem;
        /* background-color: white;
        padding: .5rem;
        border-radius: .5rem; */
    }

    .filelink {
        display: block;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        color: inherit !important;
        padding: .5rem 1rem;
        margin: 0 0 1rem;
        background: white;
    }

    .filelink:hover {
        text-decoration: none;
        background-color: rgba(0, 110, 183, 0.05);
    }

    .show-on-hover:hover .invisible {
        visibility: visible !important;
    }

    .badge.block {
        display: block;
        text-align: center;
    }
</style>

<div class="content-container">
    <?php
    if (function_exists('printMsg') && (isset($_GET['msg']) || isset($_GET['error'])) || isset($_SESSION['msg'])) {
        printMsg();
    }
    ?>

    <?php

    if (isset($_GET['msg']) && $_GET['msg'] == 'add-success') { ?>


        <?php if ($Settings->featureEnabled('projects') && !empty($doc['projects'] ?? [])) { ?>
            <div class="alert success mb-20">
                <h3 class="title">
                    <?= lang('Projects connected', 'Projekte verknüpft') ?>
                </h3>
                <?= lang(
                    'This activity was automatically connected to projects based on funding numbers. You can add more projects or remove the existing ones.',
                    'Diese Aktivität wurde automatisch anhand von Fördernummern mit Projekten verknüpft. Du kannst weitere Projekte hinzufügen oder die bestehenden entfernen.'
                ) ?>
                <br>
                <a href="#projects" class="btn success">
                    <i class="ph ph-tree-structure"></i>
                    <?= lang('Projects', 'Projekte') ?>
                </a>
            </div>
        <?php } ?>

        <?php if ($upload_possible) { ?>
            <div class="alert signal mb-20">
                <h3 class="title">
                    <?= lang('For the good practice: ', 'Für die gute Praxis:') ?>
                </h3>
                <?= lang(
                    'Upload now all relevant files for this activity (e.g. as PDF) to have them available for documentation and exchange.',
                    'Lade jetzt die relevanten Dateien (z.B. PDF) hoch, um sie für die Dokumentation parat zu haben.'
                ) ?>
                <i class="ph ph-smiley"></i>
                <b><?= lang('Thank you!', 'Danke!') ?></b>
                <br>
                <a href="#upload-files" class="btn signal">
                    <i class="ph ph-upload"></i>
                    <?= lang('Upload files', 'Dateien hochladen') ?>
                </a>
            </div>
        <?php } ?>

    <?php } ?>

    <?php include_once BASEPATH . '/header-editor.php'; ?>

    <script>
        const ACTIVITY_ID = '<?= $id ?>';
        const TYPE = '<?= $doc['type'] ?>';
    </script>

    <script src="<?= ROOTPATH ?>/js/popover.js"></script>
    <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>

    <script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/activity.js?v=<?= CSS_JS_VERSION ?>"></script>


    <div class="btn-toolbar">
        <?php if ($doc['locked'] ?? false) { ?>
            <span class="badge danger cursor-default mr-10 border-danger" data-toggle="tooltip" data-title="<?= lang('This activity has been locked.', 'Diese Aktivität wurde gesperrt.') ?>">
                <i class="ph ph-lock text-danger"></i>
                <?= lang('Locked', 'Gesperrt') ?>
            </span>
        <?php } ?>

        <?php if ($Settings->hasPermission('activities.lock')) { ?>
            <input type="hidden" name="id" value="<?= $id ?>">
            <form action="<?= ROOTPATH ?>/crud/activities/<?= $id ?>/lock" method="post">
                <?php if ($doc['locked'] ?? false) { ?>
                    <button class="btn text-success border-success mr-10" type="submit">
                        <i class="ph ph-lock-open"></i>
                        <?= lang('Unlock', 'Entsperren') ?>
                    </button>
                <?php } else { ?>
                    <button class="btn text-danger border-danger mr-10" type="submit">
                        <i class="ph ph-lock"></i>
                        <?= lang('Lock', 'Sperren') ?>
                    </button>
                <?php } ?>
            </form>
        <?php } ?>

        <div class="btn-group">
            <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn text-primary border-primary">
                    <i class="ph ph-pencil-simple-line"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
            <?php } ?>
            <?php if (!in_array($doc['type'], ['publication'])) { ?>
                <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn text-primary border-primary">
                    <i class="ph ph-copy"></i>
                    <?= lang("Copy", "Kopie") ?>
                </a>
            <?php } ?>
            <?php if ($Settings->featureEnabled('tags')) { ?>
                <a href="#add-tags" class="btn text-primary border-primary">
                    <i class="ph ph-tag"></i>
                    <?= $tagName ?>
                </a>
            <?php } ?>

        </div>


        <div class="btn-group">
            <button class="btn text-primary border-primary" onclick="addToCart(this, '<?= $id ?>')">
                <i class="<?= (in_array($id, $cart)) ? 'ph ph-duotone ph-basket ph-basket-plus text-success' : 'ph ph-basket ph-basket-plus' ?>"></i>
                <?= lang('Collect', 'Sammeln') ?>
            </button>
            <div class=" dropdown with-arrow btn-group ">
                <button class="btn text-primary border-primary" data-toggle="dropdown" type="button" id="download-btn" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-download"></i> Download
                    <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="download-btn">
                    <div class="content">
                        <form action="<?= ROOTPATH ?>/download" method="post">

                            <input type="hidden" name="filter[id]" value="<?= $id ?>">

                            <div class="form-group">

                                <?= lang('Highlight:', 'Hervorheben:') ?>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="highlight" id="highlight-user" value="user" checked="checked">
                                    <label for="highlight-user"><?= lang('Me', 'Mich') ?></label>
                                </div>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="highlight" id="highlight-aoi" value="aoi">
                                    <label for="highlight-aoi"><?= $Settings->get('affiliation') ?><?= lang(' Authors', '-Autoren') ?></label>
                                </div>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="highlight" id="highlight-none" value="">
                                    <label for="highlight-none"><?= lang('None', 'Nichts') ?></label>
                                </div>

                            </div>


                            <div class="form-group">

                                <?= lang('File format:', 'Dateiformat:') ?>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="format" id="format-word" value="word" checked="checked">
                                    <label for="format-word">Word</label>
                                </div>

                                <div class="custom-radio ml-10">
                                    <input type="radio" name="format" id="format-bibtex" value="bibtex">
                                    <label for="format-bibtex">BibTex</label>
                                </div>

                            </div>
                            <button class="btn text-primary border-primary">Download</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($Settings->featureEnabled('portal')) { ?>
            <a class="btn text-primary border-primary ml-auto" href="<?= ROOTPATH ?>/preview/activity/<?= $id ?>">
                <i class="ph ph-eye ph-fw"></i>
                <?= lang('Preview', 'Vorschau') ?>
            </a>
        <?php } ?>
    </div>

    <!-- HEAD -->
    <div class="my-20 pt-20">

        <ul class="breadcrumb category" style="--highlight-color:<?= $Format->typeArr['color'] ?? '' ?>">
            <li><?= $Format->activity_type() ?></li>
            <!-- <span class='mr-10'><?= $Format->activity_icon(false) ?></span> -->
            <li><?= $Format->activity_subtype() ?></li>
        </ul>
        <h1 class="mt-10">
            <?= $Format->getTitle('web') ?>
        </h1>

        <p class="lead"><?= $Format->getSubtitle('web') ?></p>

    </div>

    <!-- check for basic things -->
    <?php
    if (!isset($doc['authors']) || empty($doc['authors'] )) {
        $doc['authors'] = [];
        if ((!isset($doc['editors']) || empty($doc['editors'])) && (!isset($doc['supervisors']) || empty($doc['supervisors']))) {
    ?>
            <div class="alert danger mb-20">
                <h3 class="title">
                    <?= lang('No authors or editors', 'Keine Autoren oder Herausgeber') ?>
                </h3>
                <p>
                    <?= lang(
                        'This activity has no authors or editors assigned. Please add at least one author or editor to this activity, otherwise it cannot be linked to persons.',
                        'Diese Aktivität hat keine Autoren oder Herausgeber zugeordnet. Bitte füge mindestens einen Autor, Herausgeber oder Betreuenden zu dieser Aktivität hinzu, ansonsten lässt sie sich nicht mit Personen verknüpfen.'
                    ) ?>
                </p>
            </div>
    <?php
        }
    }
    ?>

    <!-- check for date, at least month and year must be given -->
    <?php
    if (!isset($doc['year']) || empty($doc['year']) || !isset($doc['month']) || empty($doc['month'])) {
        // if no date is given, show an error
    ?>
        <div class="alert danger mb-20">
            <h3 class="title">
                <?= lang('No time specified', 'Keine Zeitangabe') ?>
            </h3>
            <p>
                <?= lang(
                    'This activity has no time specified. Please add at least month and year to this activity, otherwise it cannot be properly assigned to a quarter and will be falsely sorted and shown.',
                    'Diese Aktivität hat keine Zeitangabe. Bitte füge mindestens Monat und Jahr zu dieser Aktivität hinzu, ansonsten kann sie nicht richtig einem Quartal zugeordnet werden und wird falsch sortiert und angezeigt.'
                ) ?>
            </p>
        </div>
    <?php
    }
    ?>



    <!-- show research topics -->
    <?php if ($Settings->featureEnabled('topics')) {
        echo $Settings->printTopics($doc['topics'] ?? [], 'mb-20');
    } ?>


    <div class="d-flex">

        <div class="mr-10 badge bg-white">
            <small><?= lang('Date', 'Datum') ?>: </small>
            <br />
            <span class="badge"><?= $Format->format_date($doc) ?></span>
        </div>

        <div class="mr-10 badge bg-white">
            <small><?= $Settings->get('affiliation') ?>: </small>
            <br />
            <?php

            if ($doc['affiliated'] ?? true) { ?>
                <div class="badge success" data-toggle="tooltip" data-title="<?= lang('At least on author of this activity has an affiliation with the institute.', 'Mindestens ein Autor dieser Aktivität ist mit dem Institut affiliiert.') ?>">
                    <!-- <i class="ph ph-handshake m-0"></i> -->
                    <?= lang('Affiliated', 'Affiliiert') ?>
                </div>
            <?php } else { ?>
                <div class="badge danger" data-toggle="tooltip" data-title="<?= lang('None of the authors has an affiliation to the Institute.', 'Keiner der Autoren ist mit dem Institut affiliiert.') ?>">
                    <!-- <i class="ph ph-hand-x m-0"></i> -->
                    <?= lang('Not affiliated', 'Nicht affiliiert') ?>
                </div>
            <?php } ?>
        </div>

        <!-- cooperative -->
        <div class="mr-10 badge bg-white">
            <small><?= lang('Cooperation', 'Zusammenarbeit') ?>: </small>
            <br />
            <?php
            switch ($doc['cooperative'] ?? '-') {
                case 'individual': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Only one author', 'Nur ein Autor/eine Autorin') ?>">
                        <?= lang('Individual', 'Einzelarbeit') ?>
                    </span>
                <?php
                    break;
                case 'departmental': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from the same department of this institute', 'Autoren aus der gleichen Abteilung des Instituts') ?>">
                        <?= lang('Departmental', 'Abteilungsübergreifend') ?>
                    </span>
                <?php
                    break;
                case 'institutional': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from different departments but all from this institute', 'Autoren aus verschiedenen Abteilungen, aber alle vom Institut') ?>">
                        <?= lang('Institutional', 'Institutionell') ?>
                    </span>
                <?php
                    break;
                case 'contributing': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from different institutes with us being middle authors', 'Autoren aus unterschiedlichen Instituten mit uns als Mittelautoren') ?>">
                        <?= lang('Cooperative (Contributing)', 'Kooperativ (Beitragend)') ?>
                    </span>
                <?php
                    break;
                case 'leading': ?>
                    <span class="badge block primary" data-toggle="tooltip" data-title="<?= lang('Authors from different institutes with us being leading authors', 'Autoren aus unterschiedlichen Instituten mit uns als führenden Autoren') ?>">
                        <?= lang('Cooperative (Leading)', 'Kooperativ (Führend)') ?>
                    </span>
                <?php
                    break;
                default: ?>
                    <span class="badge block" data-toggle="tooltip" data-title="<?= lang('No author affiliated', 'Autor:innen sind nicht affiliiert') ?>">
                        <?= lang('None', 'Keine') ?>
                    </span>
            <?php
                    break;
            }
            ?>

        </div>

        <?php if ($doc['impact'] ?? false) { ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('Impact', 'Impact') ?>: </small>
                <br />
                <span class="badge danger"><?= $doc['impact'] ?></span>
            </div>
        <?php } ?>
        <?php if ($doc['quartile'] ?? false) { ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('Quartile', 'Quartil') ?>: </small>
                <br />
                <span class="quartile <?= $doc['quartile'] ?>"><?= $doc['quartile'] ?></span>
            </div>
        <?php } ?>

        <?php if (!empty($projects)) { ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('Projects', 'Projekte') ?>: </small>
                <br />
                <?php foreach ($projects as $p) { ?>
                    <a class="badge" href="<?= ROOTPATH ?>/projects/view/<?= $p['_id'] ?>"><?= $p['name'] ?></a>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($Settings->featureEnabled('portal')) {
            $doc['hide'] = $doc['hide'] ?? false;
            $visible_subtypes = $Settings->getActivitiesPortfolio(true);
        ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('Online Visibility', 'Online-Sichtbarkeit') ?>: </small>
                <br />
                <?php if (!in_array($doc['subtype'], $visible_subtypes)) { ?>
                    <span class="badge warning" data-toggle="tooltip" data-title="<?= lang('This activity subtype is not visible on the portal due to general settings of your institute.', 'Dieser Aktivitätstyp ist aufgrund genereller Instituts-Einstellungen im Portal nicht sichtbar.') ?>">
                        <i class="ph ph-eye-slash m-0"></i>
                        <?= lang('Activity type not visible', 'Aktivitätstyp nicht sichtbar') ?>
                    </span>
                <?php } else if ($edit_perm) { ?>
                    <div class="custom-switch">
                        <input type="checkbox" id="hide" <?= $doc['hide'] ? 'checked' : '' ?> name="values[hide]" onchange="hide()">
                        <label for="hide" id="hide-label">
                            <?= $doc['hide'] ? lang('Hidden', 'Versteckt') :  lang('Visible', 'Sichtbar')  ?>
                        </label>
                    </div>

                    <script>
                        function hide() {
                            $.ajax({
                                type: "POST",
                                url: ROOTPATH + "/crud/activities/hide",
                                data: {
                                    activity: ACTIVITY_ID
                                },
                                success: function(response) {
                                    var hide = $('#hide').prop('checked');

                                    $('#hide-label').text(hide ? '<?= lang('Hidden', 'Versteckt') ?>' : '<?= lang('Visible', 'Sichtbar') ?>');
                                    $('#highlight').prop('disabled', hide);
                                    if (hide) {
                                        $('#highlight').prop('checked', false);
                                        $('#highlight-label').text('<?= lang('Normal', 'Normal') ?>');
                                    }
                                    toastSuccess(lang('Visibility status changed', 'Sichtbarkeitsstatus geändert'))
                                },
                                error: function(response) {
                                    console.log(response);
                                }
                            });
                        }
                    </script>


                <?php } else { ?>
                    <?php if ($doc['hide']) { ?>
                        <span class="badge danger" data-toggle="tooltip" data-title="<?= lang('This activity is hidden on the portal.', 'Diese Aktivität ist auf dem Portal versteckt.') ?>">
                            <i class="ph ph-eye-slash"></i>
                            <?= lang('Hidden', 'Versteckt') ?>
                        </span>
                    <?php } else { ?>
                        <span class="badge success" data-toggle="tooltip" data-title="<?= lang('This activity is visible on the portal.', 'Diese Aktivität ist auf dem Portal sichtbar.') ?>">
                            <i class="ph ph-eye"></i>
                            <?= lang('Visible', 'Sichtbar') ?>
                        </span>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if ($DB->isUserActivity($doc, $_SESSION['username'], false)) {
            $disabled = $doc['hide'] ?? false;
            if ($disabled) {
                $highlighted = false;
            } else {
                $highlights = DB::doc2Arr($USER['highlighted'] ?? []);
                $highlighted = in_array($id, $highlights);
            }
        ?>
            <div class="mr-10 badge bg-white">
                <small><?= lang('Displayed in your profile', 'Darstellung in deinem Profil') ?>: </small>
                <br />
                <div class="custom-switch">
                    <input type="checkbox" id="highlight" <?= ($highlighted) ? 'checked' : '' ?> name="values[highlight]" onchange="fav()" <?= $disabled ? 'disabled' : '' ?>>
                    <label for="highlight" id="highlight-label">
                        <?= $highlighted ? lang('Highlighted', 'Hervorgehoben') : lang('Normal', 'Normal') ?>
                    </label>
                </div>
            </div>
            <script>
                function fav() {
                    $.ajax({
                        type: "POST",
                        url: ROOTPATH + "/crud/activities/fav",
                        data: {
                            activity: ACTIVITY_ID
                        },
                        dataType: "json",
                        success: function(response) {
                            var highlight = $('#highlight').prop('checked');
                            $('#highlight-label').text(highlight ? '<?= lang('Highlighted', 'Hervorgehoben') ?>' : '<?= lang('Normal', 'Normal') ?>');
                            toastSuccess(lang('Highlight status changed', 'Hervorhebungsstatus geändert'))
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });
                }
            </script>
        <?php } ?>

    </div>

    <!-- TAB AREA -->

    <nav class="pills mt-20 mb-0">
        <a onclick="navigate('general')" id="btn-general" class="btn active">
            <i class="ph ph-info" aria-hidden="true"></i>
            <?= lang('General', 'Allgemein') ?>
        </a>

        <?php if ($guests_involved) { ?>
            <a onclick="navigate('guests')" id="btn-guests" class="btn">
                <i class="ph ph-user-plus" aria-hidden="true"></i>
                <?= lang('Guests', 'Gäste') ?>
                <span class="index"><?= count($guests) ?></span>
            </a>
        <?php } ?>


        <?php if (count($doc['authors']) > 1) { ?>
            <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
                <i class="ph ph-users" aria-hidden="true"></i>
                <?= lang('Coauthors', 'Koautoren') ?>
                <span class="index"><?= count($doc['authors']) ?></span>
            </a>
        <?php } ?>

        <?php if ($Settings->featureEnabled('projects')) { ?>
            <?php
            $count_projects = count($doc['projects'] ?? []);
            if ($count_projects) :
            ?>
                <a onclick="navigate('projects')" id="btn-projects" class="btn">
                    <i class="ph ph-tree-structure" aria-hidden="true"></i>
                    <?= lang('Projects', 'Projekte') ?>
                    <span class="index"><?= $count_projects ?></span>
                </a>

            <?php else : ?>
                <a href="#projects" class="btn">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('Add projects', 'Projekt verknüpfen') ?>
                </a>
            <?php endif; ?>
        <?php } ?>

        <?php if ($Settings->featureEnabled('infrastructures')) { ?>
            <?php
            $count_infrastructures = count($doc['infrastructures'] ?? []);
            if ($count_infrastructures) :
            ?>
                <a onclick="navigate('infrastructures')" id="btn-infrastructures" class="btn">
                    <i class="ph ph-cube-transparent" aria-hidden="true"></i>
                    <?= $Settings->infrastructureLabel() ?>
                    <span class="index"><?= $count_infrastructures ?></span>
                </a>

            <?php else : ?>
                <a href="#infrastructures" class="btn">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('Add infrastructures', 'Infrastrukturen') ?>
                </a>
            <?php endif; ?>
        <?php } ?>

        <?php
        if ($upload_possible):
            $count_files = count($doc['files'] ?? []);
            if ($count_files) :
        ?>
                <a onclick="navigate('files')" id="btn-files" class="btn">
                    <i class="ph ph-files" aria-hidden="true"></i>
                    <?= lang('Files', 'Dateien') ?>
                    <span class="index"><?= $count_files ?></span>
                </a>

            <?php else : ?>
                <a href="#upload-files" class="btn">
                    <i class="ph ph-plus-circle"></i>
                    <?= lang('Upload files', 'Datei hochladen') ?>
                </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($Settings->featureEnabled('concepts')) { ?>
            <?php
            $count_concepts = count($doc['concepts'] ?? []);
            if ($count_concepts) :
            ?>
                <a onclick="navigate('concepts')" id="btn-concepts" class="btn">
                    <i class="ph ph-lightbulb" aria-hidden="true"></i>
                    <?= lang('Concepts', 'Konzepte') ?>
                    <span class="index"><?= $count_concepts ?></span>
                </a>
            <?php endif; ?>
        <?php } ?>


        <?php
        $count_history = count($doc['history'] ?? []);
        if ($count_history) :
        ?>
            <a onclick="navigate('history')" id="btn-history" class="btn">
                <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
                <?= lang('History', 'Historie') ?>
                <span class="index"><?= $count_history ?></span>
            </a>
        <?php endif; ?>

        <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
            <a onclick="navigate('raw')" id="btn-raw" class="btn">
                <i class="ph ph-code" aria-hidden="true"></i>
                <?= lang('Raw data', 'Rohdaten')  ?>
            </a>
        <?php } ?>

    </nav>



    <section id="raw" style="display:none">

        <h2 class="title">
            <?= lang('Raw data', 'Rohdaten') ?>
        </h2>

        <?= lang('Raw data as they are stored in the database.', 'Die Rohdaten, wie sie in der Datenbank gespeichert werden.') ?>

        <div class="box padded overflow-x-scroll">
            <pre><?= htmlspecialchars(json_encode($doc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>

    </section>

    <section id="general">
        <div class="row row-eq-spacing-lg">
            <div class="col-lg-6">

                <div class="btn-toolbar float-sm-right">
                    <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                        <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn secondary">
                            <i class="ph ph-pencil-simple-line"></i>
                            <?= lang('Edit', 'Bearbeiten') ?>
                        </a>
                    <?php } ?>


                    <?php if (!in_array($doc['type'], ['publication'])) { ?>
                        <a href="<?= ROOTPATH ?>/activities/copy/<?= $id ?>" class="btn secondary">
                            <i class="ph ph-copy"></i>
                            <?= lang("Add a copy", "Kopie anlegen") ?>
                        </a>
                    <?php } ?>


                    <?php if ($user_activity && $locked && empty($doc['end'] ?? null) && $ongoing) { ?>
                        <!-- End user activity even if activity is locked -->
                        <div class="dropdown">
                            <button class="btn secondary" data-toggle="dropdown" type="button" id="update-end-date" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-calendar-check"></i>
                                <?= lang('End activity', 'Beenden') ?> <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-center w-200" aria-labelledby="update-end-date">
                                <form action="<?= ROOTPATH . "/crud/activities/update/" . $id ?>" method="POST" class="content">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/" . $id ?>">
                                    <div class="form-group">
                                        <label for="date_end"><?= lang('Activity ended at:', 'Aktivität beendet am:') ?></label>
                                        <input type="date" class="form-control" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? null) ?>" required>
                                    </div>
                                    <button class="btn btn-block" type="submit"><?= lang('Save', 'Speichern') ?></button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>

                </div>

                <h2 class="mt-0">Details</h2>

                <script>
                    function copyToClipboard() {
                        var text = $('#formatted').text()
                        navigator.clipboard.writeText(text)
                        toastSuccess('Query copied to clipboard.')
                    }
                </script>
                <table class="table" id="detail-table">

                    <tr>
                        <td>
                            <button class="btn small float-right" onclick="copyToClipboard()" data-toggle="tooltip" data-title="<?= lang('Copy to clipboard', 'In die Zwischenablage kopieren') ?>">
                                <i class="ph ph-clipboard" aria-label="Copy to clipboard"></i>
                            </button>

                            <span class="key"><?= lang('Formatted entry', 'Formatierter Eintrag') ?></span>
                            <div id="formatted"><?= $doc['rendered']['print'] ?></div>
                        </td>
                    </tr>
                    <?php
                    $Format->usecase = "list";

                    $emptyModules = [];

                    foreach ($typeModules as $module) {
                        if (str_ends_with($module, '*')) $module = str_replace('*', '', $module);
                        if (in_array($module, ["semester-select", "event-select", "projects"])) continue;
                    ?>
                        <?php if ($module == 'teaching-course' && isset($doc['module_id'])) :
                            $module = $DB->getConnected('teaching', $doc['module_id']);
                            if (empty($module)) {
                                $emptyModules[] = 'teaching-course';
                                continue;
                            }
                        ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Teaching module', 'Lehrveranstaltung') ?></span>

                                    <a class="module " href="<?= ROOTPATH ?>/teaching#<?= $doc['module_id'] ?>">
                                        <h5 class="m-0"><span class="highlight-text"><?= $module['module'] ?></span> <?= $module['title'] ?></h5>
                                        <span class="text-muted-"><?= $module['affiliation'] ?></span>
                                    </a>
                                </td>
                            </tr>

                        <?php elseif ($module == 'journal' && isset($doc['journal_id'])) :
                            $journal = $DB->getConnected('journal', $doc['journal_id']);
                            if (empty($journal)) {
                                $emptyModules[] = 'journal';
                                continue;
                            }
                        ?>

                            <tr>
                                <td>
                                    <span class="key"><?= $Settings->journalLabel() ?></span>

                                    <a class="module " href="<?= ROOTPATH ?>/journal/view/<?= $doc['journal_id'] ?>">
                                        <h6 class="m-0"><?= $journal['journal'] ?></h6>
                                        <span class="float-right text-muted-"><?= $journal['publisher'] ?></span>
                                        <span class="text-muted-">
                                            ISSN: <?= print_list($journal['issn']) ?>
                                            <br>
                                            Impact:
                                            <?= $doc['impact'] ?? 'unknown' ?>
                                        </span>
                                    </a>
                                </td>
                            </tr>
                        <?php elseif ($module == 'conference' && isset($doc['conference_id'])) :
                            $conference = $DB->getConnected('conference', $doc['conference_id']);
                        ?>

                            <tr>
                                <td>
                                    <span class="key">Event</span>
                                    <?php if (empty($conference)) { ?>
                                        <div><?= $doc['conference'] ?? '' ?></div>
                                        <span class="text-danger">
                                            <?= lang('This event has been deleted.', 'Diese Veranstaltung wurde gelöscht.') ?>
                                        </span>
                                    <?php } else { ?>

                                        <div class="module ">
                                            <h6 class="m-0">
                                                <a href="<?= ROOTPATH ?>/conferences/view/<?= $doc['conference_id'] ?>">
                                                    <?= $conference['title'] ?>
                                                </a>
                                            </h6>
                                            <div class="text-muted mb-10"><?= $conference['title_full'] ?></div>
                                            <ul class="horizontal mb-0">
                                                <li>
                                                    <b><?= lang('Location', 'Ort') ?></b>: <?= $conference['location'] ?>
                                                </li>
                                                <li>
                                                    <b><?= lang('Date', 'Datum') ?></b>: <?= fromToDate($conference['start'], $conference['end']) ?>
                                                </li>
                                                <li>
                                                    <a href="<?= $conference['url'] ?>" target="_blank">
                                                        <i class="ph ph-link"></i>
                                                        <?= lang('Website', 'Website') ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php else :
                            $val = $Format->get_field($module);
                            if (empty($val) || $val == '-') {
                                $emptyModules[] = $module;
                                continue;
                            }
                        ?>

                            <tr>
                                <td>
                                    <span class="key"><?= $Modules->get_name($module) ?></span>
                                    <?= $Format->get_field($module) ?>
                                </td>
                            </tr>

                        <?php endif; ?>

                    <?php } ?>

                    <!-- tags -->
                    <?php if ($Settings->featureEnabled('tags') && $edit_perm) : ?>
                        <tr>
                            <td>
                                <?php if ($edit_perm && $Settings->hasPermission('activities.tags')) { ?>
                                    <a href="#add-tags" class="btn small float-right">
                                        <i class="ph ph-edit"></i>
                                        <?= lang('Edit', 'Bearbeiten') ?>
                                    </a>
                                <?php } ?>
                                <span class="key"><?= $tagName ?></span>
                                <p id="tag-list" class="mt-5">
                                    <?php
                                    $tags = $doc['tags'] ?? [];
                                    if (count($tags)) {
                                        foreach ($tags as $tag) {
                                    ?>
                                            <a class="badge primary" href="<?= ROOTPATH ?>/activities#tags=<?= urlencode($tag) ?>">
                                                <i class="ph ph-tag"></i>
                                                <?= $tag ?>
                                            </a>
                                    <?php }
                                    } else {
                                        echo lang('No ' . $tagName . ' assigned yet.', 'Noch keine ' . $tagName . ' vergeben.');
                                    }
                                    ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php
                    // check for empty modules and show a short info
                    if (count($emptyModules)) {
                        $emptyModules = array_unique($emptyModules);
                    ?>
                        <tr>
                            <td>
                                <span class="key text-danger"><?= lang('The following fields are not filled in', 'Die folgenden Felder sind nicht ausgefüllt') ?>:</span>
                                <?php foreach ($emptyModules as $key) { ?>
                                    <span class="badge mr-5 mb-5"><?= $Modules->get_name($key) ?></span>
                                <?php } ?>

                            </td>
                        </tr>
                    <?php } ?>



                    <?php if (($edit_perm) && isset($doc['comment'])) : ?>
                        <tr class="text-muted">
                            <td>
                                <span class="key" style="text-decoration: 1px dotted underline;" data-toggle="tooltip" data-title="<?= lang('Only visible for authors and editors.', 'Nur sichtbar für Autoren und Editor-MA.') ?>">
                                    <?= lang('Comment', 'Kommentar') ?>:
                                </span>

                                <?= $doc['comment'] ?>
                            </td>
                        </tr>
                    <?php endif; ?>


                </table>


                <div class="alert danger mt-20 py-20">
                    <h2 class="title">
                        <?= lang('Delete', 'Löschen') ?>
                    </h2>
                    <?php

                    // $in_quarter = inCurrentQuarter($doc['year'], $doc['month']);
                    if ($locked && !$Settings->hasPermission('activities.delete-locked')) : ?>
                        <p class="mt-0">
                            <?= lang(
                                'This activity has been locked because it was already used by reporters in a report. Due to the documentation and verification obligation, activities may not be easily changed or deleted after the report. However, if a change is necessary, please contact the responsible persons.',
                                'Diese Aktivität wurde gesperrt, da sie bereits von den Berichterstattenden in einem Report verwendet wurde. Wegen der Dokumentations- und Nachweispflicht dürfen Aktivitäten nach dem Report nicht mehr so einfach verändert oder gelöscht werden. Sollte dennoch eine Änderung notwendig sein, meldet euch bitte bei den Verantwortlichen.'
                            ) ?>
                        </p>
                    <?php
                    elseif ($Settings->hasPermission('activities.delete')) :
                    ?>
                        <p class="mt-0">
                            <?= lang('You have permission to delete this activity:', 'Du hast die nötigen Rechte, um diese Aktivität zu löschen:') ?>
                        </p>
                        <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" class="d-inline-block ml-auto">
                            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                            <button type="submit" class="btn danger">
                                <i class="ph ph-trash"></i>
                                <?= lang('Delete activity', 'Lösche Aktivität') ?>
                            </button>
                        </form>
                    <?php elseif (!$user_activity) : ?>

                        <p class="mt-0">
                            <?= lang(
                                'This is not your own activity. If for any reason you want it changed or deleted, please contact the creator of the activity or the controlling.',
                                'Dies ist nicht deine Aktivität. Wenn du aus irgendwelchen Gründen willst, dass sie verändert oder gelöscht wird, kontaktiere bitte den Urheber der Aktivität oder das Controlling.'
                            ) ?>
                        </p>

                    <?php else : ?>
                        <p class="mt-0">
                            <b>Info:</b>
                            <?= lang(
                                'This is your own activity and it has not been locked yet. You can delete it.',
                                'Dies ist deine eigene Aktivität und sie ist noch nicht gesperrt worden. Du kannst sie also löschen.'
                            ) ?>
                        </p>
                        <form action="<?= ROOTPATH ?>/crud/activities/delete/<?= $id ?>" method="post" class="d-inline-block ml-auto">
                            <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH . "/activities" ?>">
                            <button type="submit" class="btn danger">
                                <i class="ph ph-trash"></i>
                                <?= lang('Delete activity', 'Lösche Aktivität') ?>
                            </button>
                            <br>
                            <small class="text-danger">
                                <?= lang('Cannot be made undone.', 'Kann nicht rückgängig gemacht werden.') ?>
                            </small>
                        </form>
                    <?php endif; ?>

                </div>
            </div>


            <div class="col-lg-6">
                <?php
                $units = $doc['units'] ?? [];
                $authorModules = ['authors', 'author-table', 'scientist', 'supervisor', 'supervisor-thesis', 'editor'];
                foreach ($typeFields as $field_id => $props) {
                    if (!in_array($field_id, $authorModules)) {
                        continue;
                    }
                    $role = 'authors';
                    if ($field_id == 'supervisor' || $field_id == 'supervisor-thesis') {
                        $role = 'supervisors';
                    } elseif ($field_id == 'editor') {
                        $role = 'editors';
                    }
                ?>

                    <div class="btn-toolbar mb-10 float-sm-right">
                        <?php if (($edit_perm) && (!$locked || $Settings->hasPermission('activities.edit-locked'))) { ?>
                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/<?= $role ?>" class="btn secondary">
                                <i class="ph ph-pencil-simple-line"></i>
                                <?= lang("Edit", "Bearbeiten") ?>
                            </a>
                        <?php } ?>
                    </div>

                    <h2 class="mt-0">
                        <!-- <?php if ($role == 'authors') {
                                    echo lang('Author(s) / Responsible person', 'Autor(en) / Verantwortliche Person');
                                } elseif ($role == 'supervisors') {
                                    echo lang('Supervisor(s)', 'Betreuer');
                                } else {
                                    echo lang('Editor(s)', 'Herausgeber');
                                } ?> -->
                        <?= $Modules->get_name($field_id) ?>
                    </h2>


                    <table class="table mb-20">
                        <thead>
                            <tr>
                                <th><?= lang('Last', 'Nachname') ?></th>
                                <th><?= lang('First', 'Vorname') ?></th>

                                <?php if ($sws) : ?>
                                    <th>SWS</th>
                                <?php elseif ($supervisorThesis) : ?>
                                    <th><?= lang('Role', 'Rolle') ?></th>
                                <?php elseif ($role == 'authors') : ?>
                                    <th>Position</th>
                                <?php endif; ?>
                                <th>Unit</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody id="<?= $role ?>">
                            <?php foreach ($activity[$role] ?? [] as $i => $author) {
                            ?>
                                <tr>
                                    <td class="<?= (($author['aoi'] ?? 0) == '1' ? 'font-weight-bold' : '') ?>">
                                        <?php if (isset($author['user']) && !empty($author['user'])) { ?>
                                            <a href="<?= ROOTPATH ?>/profile/<?= $author['user'] ?>">
                                                <?= $author['last'] ?? '' ?>
                                            </a>
                                        <?php } else { ?>
                                            <?= $author['last'] ?? '' ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?= $author['first'] ?? '' ?>
                                    </td>
                                    <?php if ($sws) : ?>
                                        <td><?= $author['sws'] ?? 0 ?></td>
                                    <?php elseif ($supervisorThesis) : ?>
                                        <td><?= $Format->getSupervisorRole($author['role'] ?? 'other') ?></td>
                                    <?php elseif ($role == 'authors') : ?>
                                        <td><?= $author['position'] ?? '' ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <?php
                                        if (isset($author['units']) && !empty($author['units'])) {
                                            foreach ($author['units'] as $unit) {
                                                echo "<a href='" . ROOTPATH . "/groups/view/$unit' class='mr-10'>$unit</a>";
                                            }
                                        } ?>
                                    </td>
                                    <td>
                                        <?php if (isset($author['user']) && !empty($author['user'])) : ?>
                                            <span data-toggle="tooltip" data-title="<?= lang('Author approved activity?', 'Autor hat die Aktivität bestätigt?') ?>">
                                                <?= bool_icon($author['approved'] ?? 0) ?>
                                            </span>
                                        <?php elseif (!$user_activity) : ?>
                                            <div class="dropdown">
                                                <button class="btn small" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                                    <?= lang('Claim', 'Beanspruchen') ?>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right w-300" aria-labelledby="dropdown-1">
                                                    <div class="content">
                                                        <small class="d-block text-danger mb-10">
                                                            <?= lang(
                                                                'You claim that you are this author.<br> This activity will be added to your list and the author name will be added to your list of alternative names.',
                                                                'Du beanspruchst, dass du diese Person bist.<br> Du fügst diese Aktivität deiner Liste hinzu und den Namen zur Liste deiner alternativen Namen.'
                                                            ) ?>
                                                        </small>
                                                        <form action="<?= ROOTPATH ?>/crud/activities/claim/<?= $id ?>" method="post">
                                                            <input type="hidden" name="role" value="<?= $role ?>">
                                                            <input type="hidden" name="index" value="<?= $i ?>">
                                                            <input type="hidden" name="redirect" value="<?= ROOTPATH . "/activities/view/$id" ?>">
                                                            <button class="btn btn-block" type="submit"><?= lang('Claim', 'Beanspruchen') ?></button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>

                <h3>
                    <?= lang('Affiliated positions', 'Affiliierte Positionen') ?>
                </h3>

                <?php
                $positions = [
                    'first' => lang('First author', 'Erstautor:in'),
                    'last' => lang('Last author', 'Letztautor:in'),
                    'first_and_last' => lang('First and last author', 'Erst- und Letztautor:in'),
                    'first_or_last' => lang('First or last author', 'Erst- oder Letztautor:in'),
                    'middle' => lang('Middle author', 'Mittelautor:in'),
                    'single' => lang('One single affiliated author', 'Ein einzelner affiliierter Autor'),
                    'none' => lang('No author affiliated', 'Kein:e Autor:in affiliiert'),
                    'all' => lang('All authors affiliated', 'Alle Autoren affiliiert'),
                    'corresponding' => lang('Corresponding author', 'Korrespondierender Autor:in'),
                    'not_first' => lang('Not first author', 'Nicht Erstautor:in'),
                    'not_last' => lang('Not last author', 'Nicht letzter Autor:in'),
                    'not_middle' => lang('Not middle author', 'Nicht Mittelautor:in'),
                    'not_corresponding' => lang('Not corresponding author', 'Nicht korrespondierender Autor:in'),
                    'not_first_or_last' => lang('Not first or last author', 'Nicht Erst- oder Letztautor:in'),
                    'not_first_and_last' => lang('Not first and last author', 'Nicht Erst- und Letztautor:in'),
                    'unspecified' => lang('Unspecified (no position specified)', 'Unspezifiziert (keine Positionsangabe)'),
                ];
                ?>


                <?php foreach ($doc['affiliated_positions'] ?? [] as $key) { ?>
                    <span class="badge bg-white mr-5 mb-5"><?= $positions[$key] ?? $key ?></span>
                <?php } ?>
                <br>
                <small class="text-muted">
                    <?= lang('Automatically calculated', 'Automatisch berechnet') ?>
                </small>

                <h3>
                    <?= lang('Participating units', 'Beteiligte Einheiten') ?>
                </h3>
                <table class="table unit-table w-full">
                    <tbody>
                        <?php
                        if (!empty($units)) {
                            $hierarchy = $Groups->getPersonHierarchyTree($units);
                            $tree = $Groups->readableHierarchy($hierarchy);

                            foreach ($tree as $row) {
                                $dept = $Groups->getGroup($row['id']);
                        ?>
                                <tr>
                                    <td class="indent-<?= $row['indent'] ?>">
                                        <a href="<?= ROOTPATH ?>/groups/view/<?= $row['id'] ?>">
                                            <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            }
                        } else { ?>
                            <tr>
                                <td>
                                    <?= lang('No organisational unit connected', 'Keine Organisationseinheit verknüpft') ?>
                                </td>
                            </tr>
                        <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>



    <?php if ($Settings->featureEnabled('projects')) { ?>
        <div class="modal" id="projects" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title">
                        <?= lang('Connect projects', 'Projekte verknüpfen') ?>
                    </h5>
                    <div>
                        <?php
                        include BASEPATH . "/components/connect-projects.php";
                        ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>


    <?php if ($Settings->featureEnabled('projects')) { ?>
        <section id="projects" style="display: none;">
            <div class="btn-toolbar float-sm-right">
                <a href="#projects" class="btn secondary mr-5">
                    <i class="ph ph-tree-structure"></i>
                    <?= lang("Connect", "Verknüpfen") ?>
                </a>
            </div>

            <h2 class="title">
                <?= lang('Projects', 'Projekte') ?>
            </h2>

            <?php if (!empty($projects)) {

                require_once BASEPATH . "/php/Project.php";
                $Project = new Project();

                foreach ($projects as $project) {
                    $Project->setProject($project);
            ?>
                    <?= $Project->widgetSmall(true) ?>
                <?php } ?>

            <?php } else { ?>
                <?= lang('No projects connected.', 'Noch keine Projekte verknüpft.') ?>
            <?php } ?>

        </section>
    <?php } ?>




    <?php if ($Settings->featureEnabled('infrastructures')) { ?>
        <div class="modal" id="infrastructures" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title">
                        <?= lang('Connect ' . $Settings->infrastructureLabel(), $Settings->infrastructureLabel() . ' verknüpfen') ?>
                    </h5>
                    <div>
                        <?php
                        include BASEPATH . "/components/connect-infrastructures.php";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <section id="infrastructures" style="display: none;">
            <div class="btn-toolbar float-sm-right">
                <a href="#infrastructures" class="btn secondary mr-5">
                    <i class="ph ph-cube-transparent"></i>
                    <?= lang("Connect", "Verknüpfen") ?>
                </a>
            </div>

            <h2 class="title">
                <?= $Settings->infrastructureLabel() ?>
            </h2>

            <?php if (!empty($doc['infrastructures'] ?? '')) {
            ?>
                <table class="table">
                    <tbody>
                        <?php foreach ($doc['infrastructures'] as $infra_id) {
                            $infra = $osiris->infrastructures->findOne(['id' => $infra_id]);
                            if (empty($infra)) continue;
                        ?>
                            <tr>
                                <td>
                                    <h6 class="m-0">
                                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                                            <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                        </a>
                                        <br>
                                    </h6>

                                    <div class="text-muted mb-5">
                                        <?php if (!empty($infra['subtitle'])) { ?>
                                            <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                                        <?php } else { ?>
                                            <?= get_preview(lang($infra['description'], $infra['description_de'] ?? null), 300) ?>
                                        <?php } ?>
                                    </div>
                                    <div>
                                        <?= fromToYear($infra['start_date'], $infra['end_date'] ?? null, true) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                </table>

            <?php } else { ?>
                <?= lang('No infrastructures connected.', 'Noch keine Infrastrukturen verknüpft.') ?>
            <?php } ?>

        </section>
    <?php } ?>


    <?php
    if ($upload_possible):
    ?>

        <div class="modal" id="upload-files" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title">
                        <?= lang('Upload files', 'Dateien hochladen') ?>
                    </h5>
                    <div>
                        <?php
                        include BASEPATH . "/components/upload-files.php";
                        ?>
                    </div>
                </div>
            </div>
        </div>


        <section id="files" style="display: none;">
            <div class="btn-toolbar float-sm-right">
                <a href="#upload-files" class="btn secondary">
                    <i class="ph ph-upload"></i>
                    <?= lang('Upload', 'Hochladen') ?>
                </a>
            </div>

            <h2 class="title"><?= lang('Files', 'Dateien') ?></h2>

            <?php if (!empty($doc['files'])) : ?>
                <?php foreach ($doc['files'] as $file) : ?>
                    <a href="<?= $file['filepath'] ?>" target="_blank" class="filelink">
                        <i class="ph ph-<?= getFileIcon($file['filetype']) ?> mr-10 ph-2x text-osiris"></i>

                        <?= $file['filename'] ?>
                    </a>
                <?php endforeach; ?>
            <?php else : ?>
                <span class="text-signal"><?= lang('No files attached', 'Noch keine Dateien hochgeladen') ?></span>
            <?php endif; ?>
        </section>

    <?php endif; ?>


    <div class="modal" id="add-tags" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                    <span aria-hidden="true">&times;</span>
                </a>
                <h5 class="title">
                    <?= lang('Connect ' . $tagName, $tagName . ' verknüpfen') ?>
                </h5>
                <p>
                    <?= lang('Currently connected ', 'Zurzeit ausgewählte ') . $tagName ?>:
                    <?php
                    $tags = $doc['tags'] ?? [];
                    if (count($tags)) {
                        echo $Settings->printTags($tags, 'all-activities');
                    } else {
                        echo lang('No ' . $tagName . ' assigned yet.', 'Noch keine ' . $tagName . ' vergeben.');
                    }
                    ?>
                </p>

                <?php if ($edit_perm && $Settings->hasPermission('activities.tags')) { ?>
                    <form action="<?= ROOTPATH ?>/crud/activities/update-tags/<?= $id ?>" method="post">
                        <?php
                        $Settings->tagChooser($doc['tags'] ?? []);
                        ?>

                        <button type="submit" class="btn success">
                            <i class="ph ph-floppy-disk"></i>
                            <?= lang('Save', 'Speichern') ?>
                        </button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php if ($Settings->featureEnabled('concepts')) { ?>
        <section id="concepts" style="display:none">
            <?php if (isset($doc['concepts'])) :
            ?>

                <h3 class=""><?= lang('Concepts', 'Konzepte') ?></h3>
                <div class="box">
                    <div class="content">
                        <?php foreach ($doc['concepts'] as $concept) {
                            $score =  round($concept['score'] * 100);
                            // if ($concept['score'] < .3) continue;
                        ?><span class="concept" target="_blank" data-score='<?= $score ?>' data-name='<?= $concept['display_name'] ?>' data-wikidata='<?= $concept['wikidata'] ?>'>
                                <div role="progressbar" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100" style="--value: <?= $score ?>"></div>
                                <?= $concept['display_name'] ?>
                            </span><?php } ?>
                    </div>
                </div>
            <?php else : ?>
                <p>
                    <?= lang('No concepts are assigned to this activity.', 'Zu dieser Aktivität sind keine Konzepte zugewiesen.') ?>
                </p>
            <?php endif; ?>
        </section>
    <?php } ?>


    <section id="coauthors" style="display:none">
        <h2>
            <i class="ph ph-graph" aria-hidden="true"></i>
            <?= lang('Coauthors', 'Koautoren') ?>
        </h2>
        <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>/authors" class="btn secondary">
            <i class="ph ph-pencil-simple-line"></i>
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>
        <div class="row row-eq-spacing">
            <div class="col-md-6 flex-grow-0" style="max-width: 40rem">
                <div id="chart-authors">
                    <canvas id="chart-authors-canvas"></canvas>
                </div>
            </div>
            <div class="offset-1"></div>
            <div class="col-md-5">
                <div id="dept-legend"></div>
            </div>
        </div>
    </section>


    <!-- new section with history -->
    <section id="history" style="display: none;">
        <h2 class="title">
            <?= lang('History', 'Historie') ?>
        </h2>
        <p>
            <?= lang('History of changes to this activity.', 'Historie der Änderungen an dieser Aktivität.') ?>
        </p>

        <?php
        if (empty($doc['history'] ?? [])) {
            echo lang('No history available.', 'Keine Historie verfügbar.');
        } else {
            // require BASEPATH . "/php/TextDiff/TextDiff.php";
            // $latest = '';
        ?>
            <div class="history-list ">
                <?php foreach (($doc['history'] ?? []) as $h) {
                    if (!is_array($h)) continue;
                ?>
                    <div class="">
                        <small class="text-primary"><?= date('d.m.Y', strtotime($h['date'])) ?></small>
                        <h5 class="m-0">
                            <?php
                            echo Settings::getHistoryType($h['type']);
                            echo ' ';
                            if (isset($h['user']) && !empty($h['user'])) {
                                echo '<a href="' . ROOTPATH . '/profile/' . $h['user'] . '">' . $DB->getNameFromId($h['user']) . '</a>';
                            } else {
                                echo "System";
                            }
                            ?>
                        </h5>

                        <?php
                        if (isset($h['comment']) && !empty($h['comment'])) { ?>
                            <code><?= $h['comment'] ?></code>
                        <?php
                        }
                        if (isset($h['changes']) && !empty($h['changes'])) {
                            echo '<div class="font-weight-bold mt-10">' .
                                lang('Changes to the activity:', 'Änderungen an der Aktivität:') .
                                '</div>';
                            echo '<table class="table w-auto small">';
                            foreach ($h['changes'] as $key => $change) {
                                $before = $change['before'] ?? '<em>empty</em>';
                                $after = $change['after'] ?? '<em>empty</em>';
                                if ($before == $after) continue;
                                if (empty($before)) $before = '<em>empty</em>';
                                if (empty($after)) $after = '<em>empty</em>';
                                echo '<tr>
                                <td class="">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    <span class="del">' . $before . '</span>
                                    <i class="ph ph-arrow-right mx-10"></i>
                                    <span class="ins">' . $after . '</span>
                                </td>
                            </tr>';
                            }
                            echo '</table>';
                        } else  if (isset($h['data']) && !empty($h['data'])) {
                            echo '<div class="font-weight-bold mt-10">' .
                                lang('Status at this time point:', 'Status zu diesem Zeitpunkt:') .
                                '</div>';

                            echo '<table class="table w-auto small">';
                            foreach ($h['data'] as $key => $datum) {
                                echo '<tr>
                                <td class="">
                                    <span class="key">' . $Modules->get_name($key) . '</span> 
                                    ' . $datum . ' 
                                </td>
                            </tr>';
                            }
                            echo '</table>';
                        } else if ($h['type'] == 'edited') {
                            echo lang('No changes tracked.', 'Es wurden keine Änderungen verfolgt.');
                        }
                        ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </section>

    <?php if ($guests_involved) { ?>


        <?php if ($Settings->featureEnabled('guest-forms')) {

            $guest_server = $Settings->get('guest-forms-server');
            $url = $guest_server . "/a/" . $id;
        ?>
            <script src="<?= ROOTPATH ?>/js/papaparse.min.js"></script>
            <!-- modals -->
            <div class="modal" id="add-guests" tabindex="-1" role="dialog">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                            <span aria-hidden="true">&times;</span>
                        </a>
                        <h5 class="title">
                            <?= lang('Add guests', 'Gäste hinzufügen') ?>
                        </h5>
                        <div>
                            <h3>
                                <?= lang('Add guests to this activity', 'Füge Gäste zu dieser Aktivität hinzu') ?>
                            </h3>
                            <p>
                                <?= lang('You can add guests to this activity by entering their names and affiliations.', 'Du kannst Gäste zu dieser Aktivität hinzufügen, indem du ihre Namen und Zugehörigkeiten eingibst.') ?>
                            </p>

                            <form action="<?= ROOTPATH ?>/crud/activities/guests" method="post">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <table class="table mb-20">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th><?= lang('Last', 'Nachname') ?></th>
                                            <th><?= lang('First', 'Vorname') ?></th>
                                            <th><?= lang('Email') ?></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="guest-list">
                                        <?php foreach ($guests as $guest) { ?>
                                            <tr>
                                                <td>
                                                    <input type="text" name="guests[id][]" class="form-control disabled" required value="<?= $guest['id'] ?>" readonly>
                                                </td>
                                                <td>
                                                    <input type="text" name="guests[last][]" class="form-control" required value="<?= $guest['last'] ?>">
                                                </td>
                                                <td>
                                                    <input type="text" name="guests[first][]" class="form-control" required value="<?= $guest['first'] ?>">
                                                </td>
                                                <td>
                                                    <input type="email" name="guests[email][]" class="form-control" required value="<?= $guest['email'] ?>">
                                                </td>
                                                <td>
                                                    <button type="button" class="btn small link" id="remove-guest" onclick="$(this).closest('tr').remove()">
                                                        <i class="ph-duotone ph-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="5">
                                                <button type="button" class="btn small" id="add-guest" onclick="addGuestRow()">
                                                    <i class="ph ph-plus"></i>
                                                    <?= lang('Add guest', 'Gast hinzufügen') ?>
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <button type="submit" class="btn primary">
                                    <i class="ph ph-save"></i>
                                    <?= lang('Save guests', 'Gäste speichern') ?>
                                </button>

                            </form>

                            <div class="box">
                                <div class="content">
                                    <h3>
                                        <?= lang('Import guests from CSV', 'Gäste aus CSV importieren') ?>
                                    </h3>
                                    <p>
                                        <?= lang('You can import a list of guests from a CSV file.', 'Du kannst eine Liste von Gästen aus einer CSV-Datei importieren.') ?>
                                    </p>
                                    <div class="custom-file">
                                        <input type="file" id="guest-file">
                                        <label for="guest-file"><?= lang('Select file', 'Datei auswählen') ?></label>
                                    </div>
                                    <small>
                                        <?= lang('The file should contain columns for last name, first name and email address. A header row is required.', 'Die Datei sollte Spalten für Nachname, Vorname und E-Mail-Adresse enthalten. Eine Zeile mit Überschriften ist notwendig.') ?>
                                    </small>
                                </div>

                                <script>
                                    document.getElementById('guest-file').addEventListener('change', function(e) {
                                        var file = e.target.files[0];
                                        if (!file) return;
                                        Papa.parse(file, {
                                            header: true,
                                            complete: function(results) {
                                                console.log(results);
                                                results.data.forEach(function(raw) {
                                                    var row = {};
                                                    // try to find first and last name and email
                                                    ['first', 'first name', 'vorname', 'First name', 'First', 'Vorname', 'FIRST', 'FIRST NAME', 'VORNAME'].forEach(key => {
                                                        if (raw[key]) {
                                                            row.first = raw[key];
                                                            return
                                                        }
                                                    });
                                                    ['last', 'last name', 'nachname', 'Last name', 'Last', 'Nachname', 'LAST', 'LAST NAME', 'NACHNAME'].forEach(key => {
                                                        if (raw[key]) {
                                                            row.last = raw[key];
                                                            return
                                                        }
                                                    });
                                                    ['email', 'mail', 'Email', 'Mail', 'E-Mail'].forEach(key => {
                                                        if (raw[key]) {
                                                            row.email = raw[key];
                                                            return
                                                        }
                                                    });
                                                    if (!row.first && !row.last) {
                                                        ['name', 'Name', 'NAME'].forEach(key => {
                                                            if (raw[key]) {

                                                                // try last, first
                                                                var parts = raw[key].split(', ');
                                                                if (parts.length == 2) {
                                                                    row.last = parts[0];
                                                                    row.first = parts[1];
                                                                    return
                                                                }
                                                                // try first last
                                                                var parts = raw[key].split(' ');
                                                                if (parts.length == 2) {
                                                                    row.first = parts[0];
                                                                    row.last = parts[1];
                                                                    return
                                                                }
                                                            }
                                                        });
                                                    }


                                                    addGuestRow(row);
                                                });
                                            }
                                        });
                                    });
                                </script>

                            </div>

                            <script>
                                function addGuestRow(data = {}) {
                                    var row = document.createElement('tr');
                                    var id = Math.random().toString(36).substring(7);
                                    row.innerHTML = `
                                    <td>
                                        <input type="text" name="guests[id][]" class="form-control disabled" required readonly value="${id}">
                                    </td>
                                    <td>
                                        <input type="text" name="guests[last][]" class="form-control" required value="${data.last ?? ''}">
                                    </td>
                                    <td>
                                        <input type="text" name="guests[first][]" class="form-control" required value="${data.first ?? ''}">
                                    </td>
                                    <td>
                                        <input type="email" name="guests[email][]" class="form-control" required value="${data.email ?? ''}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn small link" id="remove-guest" onclick="$(this).closest('tr').remove()">
                                            <i class="ph-duotone ph-trash text-danger"></i>
                                        </button>
                                    </td>
                                `;
                                    document.getElementById('guest-list').appendChild(row);
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>

        <?php } ?>


        <section id="guests" style="display:none">

            <h2 class="title">
                <?= lang('Guests', 'Gäste') ?>
            </h2>

            <?php if ($Settings->featureEnabled('guest-forms')) {

            ?>
                <a href="#add-guests" class="btn primary">
                    <i class="ph ph-plus" aria-hidden="true"></i>
                    <?= lang('Add guests', 'Gäste hinzufügen') ?>
                </a>

            <?php } ?>

            <p>
                <?= lang('There are currently ' . count($guests) . ' guests involved in this activity.', 'Aktuell sind ' . count($guests) . ' Gäste an dieser Aktivität beteiligt.') ?>
            </p>

            <?php if ($user_activity || $Settings->hasPermission('guests.view')) {
                $new_guests = false;
            ?>

                <table class="table mb-20">
                    <thead>
                        <tr>
                            <th><?= lang('Last', 'Nachname') ?></th>
                            <th><?= lang('First', 'Vorname') ?></th>
                            <th><?= lang('Email') ?></th>
                            <th><?= lang('Status') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($guests as $guest) { ?>
                            <tr>
                                <td><?= $guest['last'] ?></td>
                                <td><?= $guest['first'] ?></td>
                                <td><?= $guest['email'] ?></td>
                                <td>
                                    <?php
                                    switch ($guest['status'] ?? 'new') {
                                        case 'pending':
                                            echo '<span class="badge primary">' . lang('Pending', 'Ausstehend') . '</span>';
                                            break;
                                        case 'approved':
                                            echo '<span class="badge success">' . lang('Approved', 'Bestätigt') . '</span>';
                                            break;
                                        case 'new':
                                            echo '<span class="badge signal">' . lang('New', 'Neu') . '</span>';
                                            $new_guests = true;
                                            break;
                                        default:
                                            echo '<span class="badge danger">' . lang('Unknown', 'Unbekannt') . '</span>';
                                            break;
                                    }
                                    ?>

                                    <!-- action buttons -->
                                    <!-- send mail -->
                                    <?php if (($guest['status'] ?? 'new') == 'new') { ?>
                                        <form action="<?= ROOTPATH ?>/crud/activities/guest-mail/<?= $id ?>" method="post" class="d-inline-block">
                                            <input type="hidden" name="guest" value="<?= $guest['id'] ?>">
                                            <button type="submit" class="btn small">
                                                <i class="ph ph-envelope" aria-hidden="true"></i>
                                                <?= lang('Send email', 'E-Mail senden') ?>
                                            </button>
                                        </form>
                                    <?php } ?>

                                    <!-- show qr code -->
                                    <button type="button" class="btn small" data-toggle="modal" data-target="qr-<?= $guest['id'] ?>">
                                        <i class="ph ph-qr-code" aria-hidden="true"></i>
                                        <?= lang('QR code', 'QR-Code') ?>
                                    </button>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- qr modals -->
                <?php foreach ($guests as $guest) {
                    $guest_server = $Settings->get('guest-forms-server');
                    $url = $guest_server . "/a/" . $id . "." . $guest['id'];
                    $options = new QROptions([]);

                    try {
                        $qr = (new QRCode($options))->render($url);
                    } catch (Throwable $e) {
                        $qr = '';
                        exit($e->getMessage());
                    }
                ?>
                    <div class="modal" id="qr-<?= $guest['id'] ?>" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <h5 class="title">
                                    <?= lang('QR code for ', 'QR-Code für ') . $guest['first'] . ' ' . $guest['last'] ?>
                                </h5>
                                <div>
                                    <div style="background-color: white; display: inline-block;">
                                        <img src="<?= $qr ?>" alt="QR code for <?= $guest['first'] . ' ' . $guest['last'] ?>" class="w-200">
                                    </div>
                                    <br>
                                    <b>Link:</b>
                                    <a href="<?= $url ?>" target="_blank"><?= $url ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($new_guests) { ?>
                    <!-- send email to all new guests -->
                    <form action="<?= ROOTPATH ?>/crud/activities/guest-mail/<?= $id ?>" method="post">
                        <button type="submit" class="btn primary">
                            <i class="ph ph-envelope" aria-hidden="true"></i>
                            <?= lang('Send email to new guests', 'Sende E-Mail an neue Gäste') ?>
                        </button>
                    </form>
                <?php } ?>


            <?php } else { ?>
                <p>
                    <?= lang('You do not have permission to view the list of guests. Only authors of the activity and users with the `guests.view` permission can view the list.', 'Du hast keine Berechtigung, die Liste der Gäste einzusehen. Nur Autor:innen der Aktivität und Personen mit der `guests.view`-Berechtigung können die Liste sehen.') ?>
                </p>
            <?php } ?>




        </section>
    <?php } ?>

</div>