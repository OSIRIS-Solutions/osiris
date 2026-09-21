<?php

/**
 * Page to show open issues
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /issues
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$Format = new Document($user);
$issues = $DB->getUserIssues($user);
?>

<style>
    .box:target {
        -moz-box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
        -webkit-box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
        box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
    }
</style>

<div class="modal" id="why-approval" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('error.why_do_i_have_to_confirm_my_authorships') ?></h5>
            <p>
                <?= lang('error.sometimes_other_scientists_or_members_of_the_institute_add_scientific_activ', replace: ['affiliation' => $Settings->get('affiliation')]) ?>
            </p>
            <p>
                <?= lang('error.but_i_have_already_confirmed_this_activity_once_that_might_be_because_as_so') ?>
            </p>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn secondary" role="button"><?= lang('common.understand') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="why-epub" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('error.why_do_i_have_to_review_online_ahead_of_print_articles') ?></h5>
            <p>
                <?= lang('error.online_ahead_of_print_means_that_the_publication_is_already_available_onlin') ?>
            </p>
            <p>
                <?= lang('error.these_publications_cannot_be_included_in_the_reports_they_are_included_in_o') ?>
            </p>
            <p>
                <?= lang('error.the_bibliographic_data_must_be_checked_again_the_check_mark_for_online_ahea') ?>
            </p>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn secondary" role="button"><?= lang('common.understand') ?></a>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="why-status" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#close-modal" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('error.why_do_i_have_to_review_these_activities') ?></h5>
            <p>
                <?= lang('error.in_order_to_ensure_that_the_correct_status_and_completion_date_is_always_in') ?>
            </p>
            <div class="text-right mt-20">
                <a href="#close-modal" class="btn secondary" role="button"><?= lang('common.understand') ?></a>
            </div>
        </div>
    </div>
</div>


<a target="_blank" href="https://wiki.osiris-app.de/users/issues/" class="btn tour float-right" id="">
    <i class="ph ph-lg ph-question mr-5"></i>
    <?= lang('common.read_the_docs') ?>
</a>
<h1 class="mt-0">
    <i class="ph ph-duotone ph-warning"></i>
    <?= lang('common.warnings') ?>
</h1>

<?php
$a = array_map(function ($a) {
    return empty($a) ? 0 : 1;
}, $issues);
if (array_sum($a) === 0) { ?>

    <div class="text-center">
        <img src="<?= ROOTPATH ?>/img/sophie/sophie-no-tasks.png" alt="" class="sophie-img w-300">
        <h2 class="mt-0"><?= lang('error.no_warnings') ?></h2>
        <p><?= lang('common.here_is_currently_nothing_that_requires_your_attention_great_work') ?></p>
    </div>
<?php
}
?>

<?php if ($Settings->featureEnabled('quality-workflow') && !empty($issues['rejected'])) { ?>
    <h4 class="mb-0">
        <?= lang('error.please_review_the_following_activities_that_were_rejected_in_the_quality_wo') ?>
    </h4>
    <p class="mt-0">
        <a href="<?= ROOTPATH ?>/docs/warnings#Überprüfung-abgelehnter-Aktivitäten"><?= lang('error.what_does_it_mean') ?></a>
    </p>

    <?php
    foreach ($issues['rejected'] as $doc) {
        $details = $doc['details'] ?? '';
        $comment = $details['comment'] ?? '';
        $doc = $DB->getActivity($doc['id']);
        $id = $doc['_id'];
        $type = $doc['type'];
        if (!isset($doc['rendered'])) continue;
    ?>
        <div id="tr-<?= $id ?>">
            <div class="box padded mt-0" id="<?= $id ?>">
                <p class="mt-0">
                    <b class="text-<?= $doc['type'] ?>">
                        <?= $doc['rendered']['icon'] ?>
                        <?= $doc['rendered']['subtype'] ?>
                    </b> <br>
                    <?= $doc['rendered']['web'] ?>
                    <hr>
                <blockquote class="alert danger without-icon">
                    <div class="title">
                        <?= lang('error.reason_for_rejection') ?>
                    </div>
                    <?= nl2br(e($comment)) ?>
                </blockquote>
                </p>

                <!-- reply and restart -->
                <div class="">
                    <a target="_self" href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn" data-toggle="tooltip" data-title="<?= lang('error.edit_activity') ?>">
                        <i class="ph ph-pencil-simple-line"></i>
                        <?= lang('error.edit_activity') ?>
                    </a>
                    <a target="_blank" href="<?= ROOTPATH ?>/activities/view/<?= $id ?>" class="btn" data-toggle="tooltip" data-title="<?= lang('common.view_activity') ?>">
                        <i class="ph ph-arrow-fat-line-right"></i>
                        <?= lang('common.view_activity') ?>
                    </a>
                    <button class="btn" data-toggle="tooltip" data-title="<?= lang('error.reply') ?>" onclick="$(this).next().toggle()">
                        <i class="ph ph-chat-dots"></i>
                        <?= lang('error.reply') ?>
                    </button>
                    <div class="mt-10" style="display:none">
                        <form action="<?= ROOTPATH ?>/crud/activities/workflow/reject-reply/<?= $id ?>" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <textarea name="comment" class="form-control small" rows="3" placeholder="<?= lang('common.your_reply_to_the_reviewer') ?>"></textarea>
                            <button class="btn small success mt-5" type="submit"><?= lang('common.send_reply') ?></button>
                            <button class="btn small mt-5" type="button" onclick="$(this).parent().hide()"><?= lang('action.cancel') ?></button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (!empty($issues['approval'])) { ?>
        <h4 class="mb-0">
            <?= lang('error.please_review_the_following_authorships') ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#Überprüfung-der-autorenschaft-nötig"><?= lang('error.what_does_it_mean') ?></a>
        </p>

        <div class="dropdown">
            <button class="btn mb-10 text-success" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-check"></i>
                <?= lang('error.approve_all') ?>
            </button>
            <div class="dropdown-menu w-300" aria-labelledby="dropdown-1">
                <div class="content">
                    <form action="<?= ROOTPATH ?>/crud/activities/approve-all" method="post">
                        <?= lang('error.i_confirm_that_i_am_the_author_of_all_of_the_following_publications_and_tha', replace: ['affiliation' => $Settings->get('affiliation')]) ?>
                        <button class="btn block success" type="submit"><?= lang('error.approve_all') ?></button>
                    </form>

                </div>
            </div>
        </div>


        <?php
        include_once BASEPATH . '/php/Modules.php';
        $Modules = new Modules();
        foreach ($issues['approval'] as $doc) {
            $doc = $DB->getActivity($doc);

            $id = $doc['_id'];
            $type = $doc['type'];
        ?>

            <div id="tr-<?= $id ?>">

                <div class="box mt-0" id="<?= $id ?>">
                    <div class="row py-10 px-20">
                        <div class="col-md-6">
                            <p class="mt-0">
                                <b class="text-<?= $doc['type'] ?>">
                                    <?= $doc['rendered']['icon'] ?>
                                    <?= $doc['rendered']['subtype'] ?>
                                </b> <br>
                                <?= $doc['rendered']['web'] ?>
                            </p>
                            <div class='' id="approve-<?= $id ?>">
                                <?php if (isset($updated_by) && !empty($updated_by)) { ?>
                                    <?= lang('error.please_confirm_possibly_again_that_you_are_the_author_and_all_details_are_c') ?>
                                <?php } else { ?>
                                    <?= lang('error.is_this_your_activity') ?>
                                <?php } ?>
                                <br>

                                <div class="btn-group mr-10">
                                    <button class="btn small text-success" onclick="_approve('<?= $id ?>', 1)" data-toggle="tooltip" data-title="<?= lang('error.yes_and_i_was_affiliated_to_the_affiliation', replace: ['affiliation' => $Settings->get('affiliation')]) ?>">
                                        <i class="ph ph-check ph-fw"></i>
                                    </button>
                                    <button class="btn small text-signal" onclick="_approve('<?= $id ?>', 2)" data-toggle="tooltip" data-title="<?= lang('error.yes_but_i_was_not_affiliated_to_the_affiliation', replace: ['affiliation' => $Settings->get('affiliation')]) ?>">
                                        <i class="ph ph-push-pin-slash ph-fw"></i>
                                    </button>
                                    <button class="btn small text-danger" onclick="_approve('<?= $id ?>', 3)" data-toggle="tooltip" data-title="<?= lang('error.no_this_is_not_me') ?>">
                                        <i class="ph ph-x ph-fw"></i>
                                    </button>
                                </div>

                                <?php if (!($doc['locked'] ?? false)) { ?>
                                    <a target="_self" href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn small text-secondary" data-toggle="tooltip" data-title="<?= lang('error.edit_activity') ?>">
                                        <i class="ph ph-pencil-simple-line"></i>
                                    </a>
                                <?php } ?>
                                <a target="_blank" href="<?= ROOTPATH ?>/activities/view/<?= $id ?>" class="btn small text-secondary" data-toggle="tooltip" data-title="<?= lang('common.view_activity') ?>">
                                    <i class="ph ph-arrow-fat-line-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <?php
                            if (isset($doc['history']) && !empty($doc['history'])) {
                                $hist = $doc['history'];
                                // get last element of history
                                $h = $hist[count($hist) - 1];
                            ?>
                                <span class="badge primary float-md-right"><?= date('d.m.Y', strtotime($h['date'])) ?></span>
                                <b class="d-block">
                                    <?php if ($h['type'] == 'created') {
                                        echo lang('common.created_by');
                                    } else if ($h['type'] == 'edited') {
                                        echo lang('common.edited_by');
                                    } else if ($h['type'] == 'imported') {
                                        echo lang('common.imported_by');
                                    } else {
                                        echo $h['type'] . lang('common.by');
                                    }
                                    if (isset($h['user']) && !empty($h['user'])) {
                                        echo '<a href="' . ROOTPATH . '/profile/' . $h['user'] . '">' . $DB->getNameFromId($h['user']) . '</a>';
                                    } else {
                                        echo "System";
                                    }
                                    ?>
                                </b>

                                <?php
                                if (isset($h['comment']) && !empty($h['comment'])) { ?>
                                    <blockquote class="alert signal without-icon">
                                        <div class="title">
                                            <?= lang('common.comment') ?>
                                        </div>
                                        <?= $h['comment'] ?>
                                    </blockquote>
                            <?php
                                }
                                // dump($h, true);

                                if (isset($h['changes']) && !empty($h['changes'])) {
                                    echo '<small class="font-weight-bold mt-10">' .
                                        lang('common.changes_to_the_activity') .
                                        '</small>';
                                    echo '<table class="table simple w-auto small border px-10">';
                                    foreach ($h['changes'] as $key => $change) {
                                        $before = $change['before'] ?? '<em>empty</em>';
                                        $after = $change['after'] ?? '<em>empty</em>';
                                        if ($before == $after) continue;
                                        if (empty($before)) $before = '<em>empty</em>';
                                        if (empty($after)) $after = '<em>empty</em>';
                                        echo '<tr>
                                        <td class="pl-0">
                                            <span class="key">' . $Modules->get_name($key) . '</span> 
                                            <span class="del">' . $before . '</span>
                                            <i class="ph ph-arrow-right mx-10"></i>
                                            <span class="ins">' . $after . '</span>
                                        </td>
                                    </tr>';
                                    }
                                    echo '</table>';
                                } else if (isset($h['data']) && !empty($h['data'])) {
                                    echo '<a class="font-weight-bold mt-10"  onclick="$(this).next().fadeToggle()">' .
                                        '<i class="ph ph-caret-down"></i> ' .
                                        lang('error.status_at_this_time_point') .
                                        '</a>';

                                    echo '<table class="table simple w-auto small border px-10" style="display:none";>';
                                    foreach ($h['data'] as $key => $datum) {
                                        echo '<tr>
                                        <td class="pl-0" style="font-size: .8em;">
                                            <span class="key">' . $Modules->get_name($key) . '</span> 
                                            ' . $datum . ' 
                                        </td>
                                    </tr>';
                                    }
                                    echo '</table>';
                                } else if ($h['type'] == 'edited') {
                                    echo lang('common.no_changes_tracked');
                                }
                            } else {
                                echo lang('common.no_history_available');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    <?php } ?>

    <?php if (!empty($issues['epub'])) { ?>
        <h4 class="mb-0">
            <?= lang('error.please_review_the_following_online_ahead_of_print_articles') ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#online-ahead-of-print"><?= lang('error.what_does_it_mean') ?></a>
        </p>

        <table class="table">
            <?php
            foreach ($issues['epub'] as $doc) {
                $doc = $DB->getActivity($doc);

                $id = $doc['_id'];
                $type = $doc['type'];
            ?>
                <tr id="tr-<?= $id ?>">
                    <td class="w-50"><?= $doc['rendered']['icon'] ?></td>
                    <td>
                        <?= $doc['rendered']['web'] ?>
                        <div class='' id="approve-<?= $id ?>">
                            <?= lang('error.this_publication_is_marked_as_online_ahead_of_print_is_it_still_not_officia') ?>
                            <br>
                            <form action="<?= ROOTPATH ?>/crud/activities/update/<?= $id ?>" method="post" class="d-inline mt-5">
                                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                                <input type="hidden" name="values[epub-delay]" value="<?= endOfCurrentQuarter(true) ?>" class="hidden">
                                <button class="btn small">
                                    <i class="ph ph-check"></i>
                                    <?= lang('error.yes_still_online_ahead_of_print_ask_again_later') ?>
                                </button>
                            </form>


                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>?epub=true" class="btn small">
                                <?= lang('error.no_longer_online_ahead_of_print_review') ?>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if (!empty($issues['status'])) { ?>
        <h4 class="mb-0">
            <?= lang('error.please_review_the_status_of_the_following_activities') ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#why-status"><?= lang('error.what_does_it_mean') ?></a>
        </p>

        <table class="table">
            <?php
            foreach ($issues['status'] as $doc) {
                $doc = $DB->getActivity($doc);

                $id = $doc['_id'];
                $status = $doc['status'];


            ?>
                <tr id="tr-<?= $id ?>">
                    <td class="w-50"><?= $doc['rendered']['icon'] ?></td>
                    <td>
                        <?= $doc['rendered']['web'] ?>
                        <div class='' id="approve-<?= $id ?>">

                            <?php if ($doc['status'] == 'in progress') { ?>
                                <?= lang('error.the_activity_has_ended_but_the_status_is_still_in_progress_please_confirm_i')  ?>
                            <?php } else { ?>
                                <?= lang('error.the_activity_has_officially_started_but_the_status_is_still_in_preparation')  ?>
                            <?php } ?>
                            <br>
                            <form action="<?= ROOTPATH ?>/crud/activities/update/<?= $id ?>" method="post" class="form-inline mt-5">
                                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                                <label class="required" for="end"><?= lang('error.ended_at_extend_until') ?>:</label>
                                <input type="date" class="form-control w-200" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? '') ?>" required>
                                <div>

                                    <div class="custom-radio d-inline-block">
                                        <input type="radio" name="values[status]" id="status-preparation" value="preparation" checked="checked" value="preparation" <?= $status == 'preparation' ? 'checked' : '' ?>>
                                        <label for="status-preparation"><?= lang('error.in_preparation') ?></label>
                                    </div>

                                    <div class="custom-radio d-inline">
                                        <input type="radio" name="values[status]" id="status-in-progress-<?= $id ?>" value="in progress" <?= $status == 'in progress' ? 'checked' : '' ?>>
                                        <label for="status-in-progress-<?= $id ?>"><?= lang('error.in_progress') ?></label>
                                    </div>

                                    <div class="custom-radio d-inline">
                                        <input type="radio" name="values[status]" id="status-completed-<?= $id ?>" value="completed" <?= $status == 'completed' ? 'checked' : '' ?>>
                                        <label for="status-completed-<?= $id ?>"><?= lang('common.completed') ?></label>
                                    </div>

                                    <div class="custom-radio mr-10 d-inline">
                                        <input type="radio" name="values[status]" id="status-aborted-<?= $id ?>" value="aborted" <?= $status == 'aborted' ? 'checked' : '' ?>>
                                        <label for="status-aborted-<?= $id ?>"><?= lang('error.aborted') ?></label>
                                    </div>
                                </div>
                                <button class="btn" type="submit"><?= lang('action.submit') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>



    <?php if (!empty($issues['openend'])) { ?>
        <h4 class="mb-0">
            <?= lang('error.do_you_still_work_on_the_following_activities') ?>
        </h4>
        <p class="mt-0">
            <a href="<?= ROOTPATH ?>/docs/warnings#open-end"><?= lang('error.what_does_it_mean') ?></a>
        </p>

        <table class="table">
            <?php
            foreach ($issues['openend'] as $doc) {
                $doc = $DB->getActivity($doc);

                $id = $doc['_id'];
            ?>
                <tr id="tr-<?= $id ?>">
                    <td class="w-50"><?= $doc['rendered']['icon'] ?></td>
                    <td>
                        <?= $doc['rendered']['web'] ?>
                        <div class='' id="approve-<?= $id ?>">

                            <form action="<?= ROOTPATH ?>/crud/activities/update/<?= $id ?>" method="post" class="d-inline mt-5">
                                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                                <input type="hidden" name="values[end-delay]" value="<?= endOfCurrentQuarter(true) ?>" class="hidden">
                                <button class="btn small text-success">
                                    <i class="ph ph-check"></i>
                                    <?= lang('error.yes_still_running') ?>
                                </button>
                            </form>

                            <a href="<?= ROOTPATH ?>/activities/edit/<?= $id ?>" class="btn small text-danger">
                                <i class="ph ph-x"></i>
                                <?= lang('error.no_edit') ?>
                            </a>

                        </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if ($Settings->hasPermission('projects.edit') || $Settings->hasPermission('projects.edit-own')) { ?>

        <?php if (!empty($issues['project-open']) || !empty($issues['project-end'])) {
            $projects = array_merge($issues['project-open'] ?? [], $issues['project-end'] ?? [])
        ?>
            <h4 class="">
                <?= lang('error.please_have_a_look_at_the_following_projects') ?>
            </h4>

            <table class="table">
                <?php
                if (isset($issues['project-open']))
                    foreach ($issues['project-open'] as $id) {
                        $doc = $osiris->projects->findOne(['_id' => DB::to_ObjectID($id)]);
                ?>
                    <tr id="tr-<?= $id ?>">
                        <td>
                            <?= lang('error.the_project') ?>
                            <b><?= $doc['name'] ?></b>
                            <?= lang('error.still_has_the_status_applied_is_this_correct') ?>
                            <div class='' id="approve-<?= $id ?>">

                                <form action="<?= ROOTPATH ?>/crud/projects/update/<?= $id ?>" method="post" class="d-inline mt-5">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                                    <input type="hidden" name="values[end-delay]" value="<?= endOfCurrentQuarter(true) ?>" class="hidden">
                                    <button class="btn small text-success">
                                        <i class="ph ph-check"></i>
                                        <?= lang('error.yes_ask_again_later') ?>
                                    </button>
                                </form>

                                <a href="<?= ROOTPATH ?>/projects/edit/<?= $id ?>" class="btn small text-danger">
                                    <i class="ph ph-edit"></i>
                                    <?= lang('error.no_edit') ?>
                                </a>

                            </div>
                        </td>
                    </tr>
                <?php } ?>

                <?php
                if (isset($issues['project-end']))
                    foreach ($issues['project-end'] as $id) {
                        $doc = $osiris->projects->findOne(['_id' => DB::to_ObjectID($id)]);
                ?>
                    <tr id="tr-<?= $id ?>">
                        <td>
                            <?= lang('error.the_project') ?>
                            <b><?= $doc['name'] ?></b>
                            <?= lang('error.has_ended_you_can_either_prolong_it_or_end_it') ?>
                            <div class='' id="approve-<?= $id ?>">


                                <form action="<?= ROOTPATH ?>/crud/projects/update/<?= $id ?>" method="post" class="form-inline mt-5">
                                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                                    <label class="required" for="end"><?= lang('error.ended_at_extend_until') ?>:</label>
                                    <input type="date" class="form-control w-200" name="values[end]" id="date_end" value="<?= valueFromDateArray($doc['end'] ?? '') ?>" required>
                                    <div>
                                        <select class="form-control" id="status-<?= $id ?>" name="values[status]" required>
                                            <option value="applied"><?= lang('projects.applied') ?></option>
                                            <option value="approved" selected><?= lang('projects.approved') ?></option>
                                            <option value="rejected"><?= lang('projects.rejected') ?></option>
                                            <option value="finished"><?= lang('projects.finished') ?></option>
                                        </select>
                                    </div>
                                    <button class="btn ml-10" type="submit"><?= lang('action.submit') ?></button>
                                </form>

                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </table>

        <?php } ?>



        <?php
        if (isset($issues['infrastructure'])) { ?>

            <h4 class="">
                <?= lang('error.please_have_a_look_at_the_following_infrastructures') ?>
            </h4>

            <table class="table">
                <?php

                foreach ($issues['infrastructure'] as $id => $timepoint) {
                    $doc = $osiris->infrastructures->findOne(['id' => $id]);
                ?>
                    <tr id="tr-<?= $id ?>">
                        <td>
                            <?= lang('error.please_update_the_statistics_of') ?>
                            <b><?= $doc['name'] ?></b>
                            <?= lang('error.from') ?>
                            <b><?= $timepoint ?></b>
                            <br>
                            <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $id ?>?edit-stats=<?= $timepoint ?>#statistics" target="_blank" rel="noopener noreferrer" class="btn small primary">
                                <i class="ph ph-calendar-plus"></i>
                                <?= lang('error.update_now') ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </table>

        <?php } ?>
    <?php } ?>

    <?php if (isset($issues['nagoya'])) { ?>
        <h4 class="">
            <?= lang('error.please_review_the_following_nagoya_protocol_submissions') ?>
        </h4>

        <table class="table">
            <?php
            foreach ($issues['nagoya'] as $project_id) {
                $project = $osiris->proposals->findOne(['_id' => DB::to_ObjectID($project_id)]);
            ?>
                <tr id="tr-<?= $project_id ?>">
                    <td>
                        <?= lang('error.the_nagoya_protocol_compliance_for_project') ?>
                        <b><?= $project['name'] ?></b>
                        <?= lang('error.requires_your_input') ?><br>
                        <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $project_id ?>" class="btn small primary">
                            <i class="ph ph-edit"></i>
                            <?= lang('error.provide_input_now') ?>
                        </a>
                        <a href="<?= ROOTPATH ?>/projects/view/<?= $project_id ?>" class="btn small">
                            <i class="ph ph-arrow-fat-line-right"></i>
                            <?= lang('error.view_project') ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } ?>