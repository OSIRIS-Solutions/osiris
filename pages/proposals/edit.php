<?php

/**
 * Page to add new proposals
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /projects/new
 *
 * @package     OSIRIS
 * @since       1.5.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once BASEPATH . "/php/Project.php";
$Project = new Project($form ?? array());

$type = $type ?? $_GET['type'] ?? $form['type'] ?? null;

$form = $form ?? array();
$new_project = empty($form) || !isset($form['_id']);

// get current url without query string
$current_url = strtok($_SERVER["REQUEST_URI"], '?');

$edit_perm = false;
$status_perm = false;
$prefilled = [];
if (!$new_project) {
    // check edit permission
    $user_project = false;
    $user_role = null;
    $persons = $form['persons'] ?? array();
    foreach ($persons as $p) {
        if (strval($p['user']) == $_SESSION['username']) {
            $user_project = True;
            $user_role = $p['role'];
            break;
        }
    }
    if ($user_project == false && ($form['created_by'] ?? '') == $_SESSION['username']) {
        $user_project = True;
    }
    $edit_perm = ($Settings->hasPermission($collection . '.edit') || ($Settings->hasPermission($collection . '.edit-own') && $user_project));
    $status_perm = ($Settings->hasPermission($collection . '.edit') || ($Settings->hasPermission($collection . '.status-own') && $user_project));

    // check if status change is requested
    if (isset($_GET['phase']) && $_GET['phase'] != $form['status']) {
        // prefill form with old data
        if ($_GET['phase'] == 'approved') {
            if (isset($form['start_proposed'])) {
                $form['start'] = $form['start_proposed'];
                $prefilled[] = lang('common.start_view');
            }
            if (isset($form['end_proposed'])) {
                $form['end'] = $form['end_proposed'];
                $prefilled[] = lang('common.end');
            }
            if (isset($form['grant_sum_proposed'])) {
                $form['grant_sum'] = $form['grant_sum_proposed'];
                $prefilled[] = lang('projects.grant_sum_total');
            }
            if (isset($form['grant_income_proposed'])) {
                $form['grant_income'] = $form['grant_income_proposed'];
                $prefilled[] = lang('projects.grant_sum_institute');
            }
            if (isset($form['grant_subproject_proposed'])) {
                $form['grant_subproject'] = $form['grant_subproject_proposed'];
                $prefilled[] = lang('projects.grant_sum_subproject');
            }
        }
    }
}


function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return e($val);
    }
    if ($val instanceof MongoDB\Model\BSONArray) {
        return implode(',', DB::doc2Arr($val));
    }
    return $val;
}

function sel($index, $value)
{
    return val($index) == $value ? 'selected' : '';
}

// load defined vocabularies
include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$selected = [];
$project_types = $Project->getProjectTypes();
$collection = $collection ?? $selected['process'] ?? 'proposal';
if ($type) {
    $selected = $Project->getProjectType($type);
} else {
    foreach ($project_types as $pt) {
        if ($pt['process'] . 's' == $collection) {
            $selected = $pt;
            $type = $pt['id'];
            break;
        }
    }
}
if (empty($selected)) {
    $selected = [];
    $type = null;
}

$is_subproject = isset($form['parent_id']);
$parent = null;
if ($is_subproject) {
    $parent = $Project->getProject($form['parent_id']);
}
?>


<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= OSIRIS_BUILD ?>"></script>

<?php if (!empty($prefilled)) { ?>
    <script>
        toastInfo(
            <?= json_encode(lang('projects.the_following_fields_have_been_prefilled_from_the_proposed_data'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?> +
            '<br> - <?= implode("<br> - ", $prefilled) ?>');
    </script>
<?php } ?>

<style>
    .flag {
        margin-right: 1rem !important;
    }
</style>

<div class="container w-600">
    <?php
    if ($is_subproject) {
        // type cannot be changed for subprojects        
    } else if ($new_project || $type === null) { ?>
        <div class="select-btns">
            <?php
            foreach ($project_types as $pt) {
                // ensure that the user has permission to add this type of project
                if ($pt['process'] == 'proposal' && !$Settings->hasPermission('proposals.add')) {
                    // skip proposal type if user has no permission to add proposals
                    continue;
                }
                if ($pt['process'] == 'project' && !$Settings->hasPermission('projects.add')) {
                    // skip project type if user has no permission to add projects
                    continue;
                }
                $key = $pt['id'];
            ?>
                <a href="<?= $current_url ?>?type=<?= $key ?>" class="btn select <?= $type == $key ? 'active' : '' ?>" style="color: <?= $pt['color'] ?? 'var(--text-color)' ?>">
                    <i class="ph ph-<?= $pt['icon'] ?>"></i>
                    <?= lang($pt['name'], $pt['name_de']) ?>
                </a>
            <?php } ?>
        </div>
        <?php

        if (is_null($type) || empty($selected)) { ?>
            <div class="alert signal mt-10">
                <?= lang('projects.please_select_a_project_type_to_continue') ?>
            </div>
        <?php }
    }

    if ($type && !empty($selected)) {
        // type has been selected
        $project_type = $Project->getProjectType($type);

        $subtitle = '';
        $phase = 'proposed';
        $status = $form['status'] ?? 'proposed';
        if ($is_subproject && !empty($form['_id'] ?? null)) {
            $formaction = ROOTPATH . "/crud/projects/update/" . $form['_id'];
            $url = ROOTPATH . "/projects/view/" . $form['_id'];
            $title = lang('projects.edit_subproject') . ': ' . ($form['name'] ?? $form['title'] ?? '');
            $phase = 'project';
        } else if ($is_subproject) {
            $formaction = ROOTPATH . "/crud/projects/create";
            $url = ROOTPATH . "/projects/view/*";
            $title = lang('projects.create_new_subproject') . ': ' . ($parent['name'] ?? $parent['title'] ?? '');
            $subtitle = lang('projects.this_project_is_a_subproject_of_another_project_that_means_you_cannot_chang');
            $phase = 'project';
        } else if (isset($from_proposal) && $from_proposal) {
            $formaction = ROOTPATH . "/crud/projects/create";
            $url = ROOTPATH . "/projects/view/" . $form['_id'];
            $title = lang('projects.create_new_project_from_proposal') . ': ' . ($form['name'] ?? $form['title'] ?? '');
            $phase = 'project';
        } else if ($new_project && $selected['process'] == 'proposal') {
            $formaction = ROOTPATH . "/crud/proposals/create";
            $url = ROOTPATH . "/proposals/view/*";
            $title = lang('projects.new_project_proposal');
            $subtitle = lang('projects.this_type_of_project_must_first_be_created_as_a_project_proposal_and_conver');
        } elseif ($new_project && $selected['process'] == 'project') {
            $formaction = ROOTPATH . "/crud/projects/create";
            $url = ROOTPATH . "/projects/view/*";
            $title = lang('projects.new_project');
            $subtitle = lang('projects.this_type_of_project_is_created_directly_as_a_project');
            $phase = 'project';
        } elseif ($selected['process'] == 'project' || $status == 'project') {
            $formaction = ROOTPATH . "/crud/projects/update/" . $form['_id'];
            $url = ROOTPATH . "/projects/view/" . $form['_id'];
            $title = lang('projects.edit_project') . ': ' . ($form['name'] ?? $form['title'] ?? '');
            $phase = 'project';
        } else {
            $formaction = ROOTPATH . "/crud/proposals/update/" . $form['_id'];
            $url = ROOTPATH . "/proposals/view/" . $form['_id'];
            $title = lang('projects.edit_project_proposal') . ': <q>' . ($form['name'] ?? $form['title'] ?? '') . '</q>';
            $phase = $_GET['phase'] ?? $status;
        }


        $fields = $Project->getFields($type, $phase);
        $fields = array_column($fields, null, 'module');
        $field_keys = array_keys($fields);

        $required_fields = array_filter($fields, function ($field) {
            return $field['required'] ?? false;
        });
        $required_fields = array_column($required_fields, 'module');

        $req = function ($field) use ($required_fields) {
            return in_array($field, $required_fields) ? 'required' : '';
        };
        ?>

        <h3 class="title">
            <?= $title ?>
        </h3>
        <p class="text-muted mt-0">
            <?= $subtitle ?>
        </p>

        <?php if ($status == 'proposed' && $phase == 'approved') {
            if (!$status_perm) {
                echo '<p class="text-danger"><i class="ph ph-warning"></i>' . lang('projects.you_do_not_have_permission_to_edit_this_project') . '</p>';
                echo '</div>';
                return;
            }
        ?>
            <?= lang('projects.status_change') ?>:
            <span class="badge signal"><?= lang('projects.proposed') ?></span>
            <i class="ph ph-arrow-right"></i>
            <span class="badge success"><?= lang('projects.approved') ?></span>
            <p class="text-danger">
                <i class="ph ph-warning"></i>
                <?= lang('projects.after_saving_you_will_no_longer_be_able_to_change_the_status_or_update_the') ?>
            </p>
        <?php } else if ($status == 'proposed' && ($phase == 'rejected' || $phase == 'withdrawn')) {
            if (!$status_perm) {
                echo '<p class="text-danger"><i class="ph ph-warning"></i>' . lang('projects.you_do_not_have_permission_to_edit_this_project') . '</p>';
                echo '</div>';
                return;
            }
        ?>
            <?= lang('projects.status_change') ?>:
            <span class="badge signal"><?= lang('projects.proposed') ?></span>
            <i class="ph ph-arrow-right"></i>
            <?php if ($phase == 'rejected') { ?>
                <span class="badge danger"><?= lang('projects.rejected') ?></span>
            <?php } else if ($phase == 'withdrawn') { ?>
                <span class="badge muted"><?= lang('projects.withdrawn') ?></span>
            <?php } ?>
            <p class="text-danger">
                <i class="ph ph-warning"></i>
                <?= lang('projects.after_saving_you_will_no_longer_be_able_to_change_the_status_or_update_the') ?>
            </p>
        <?php } else if (!$new_project && $status == $phase) {
            if (!$edit_perm) {
                echo '<p class="text-danger"><i class="ph ph-warning"></i>' . lang('projects.you_do_not_have_permission_to_edit_this_project') . '</p>';
                echo '</div>';
                return;
            }
            echo lang('projects.you_edit_the_following_status') . ': ';
            echo $Project->getStatus($status);
        } ?>



        <form action="<?= $formaction ?>" method="post" id="proposal-form" class="box padded">
            <input type="hidden" class="hidden" name="redirect" value="<?= $url ?>">
            <input type="hidden" class="hidden" name="values[type]" value="<?= $type ?>">
            <input type="hidden" class="hidden" name="values[status]" value="<?= $phase ?>">

            <?php if ($phase == 'withdrawn') { ?>
                <div class="form-group">
                    <label for="withdrawn_reason"><?= lang('projects.reason_for_withdrawal') ?></label>
                    <textarea name="values[withdrawn_reason]" id="withdrawn_reason" class="form-control" rows="4"><?= val('withdrawn_reason') ?></textarea>
                </div>
                <p>
                    <i class="ph ph-warning text-danger"></i>
                    <?= lang('projects.you_are_about_to_withdraw_this_project_proposal_this_means_that_it_will_no') ?>
                </p>
            <?php } ?>


            <?php if ($is_subproject && empty($form['_id'] ?? null)) { ?>
                <input type="hidden" class="hidden" name="values[parent_id]" value="<?= $form['parent_id'] ?>">
                <?php if (isset($parent['proposal_id'])) { ?>
                    <!-- shared proposal -->
                    <input type="hidden" class="hidden" name="values[proposal_id]" value="<?= $parent['proposal_id'] ?>">
                <?php } ?>
            <?php } ?>

            <?php if (isset($from_proposal) && $from_proposal) { ?>
                <input type="hidden" class="hidden" name="values[proposal_id]" value="<?= $form['_id'] ?>">
            <?php } ?>


            <?php if (array_key_exists('submission_date', $fields)) { ?>
                <h5 class="mt-0">
                    <?= lang('projects.submission') ?>
                </h5>

                <div class="form-group floating-form">
                    <input type="date" class="form-control large" name="values[submission_date]" id="submission_date" value="<?= val('submission_date', date('Y-m-d')) ?>" required>
                    <label for="submission_date" class="required">
                        <?= lang('projects.date_of_submission') ?>
                    </label>
                </div>
            <?php } ?>

            <?php if (array_key_exists('approval_date', $fields)) { ?>
                <h5 class="mt-0">
                    <?= lang('common.approval') ?>
                </h5>

                <div class="form-group floating-form">
                    <input type="date" class="form-control large" name="values[approval_date]" id="approval_date" value="<?= val('approval_date', date('Y-m-d')) ?>" required>
                    <label for="approval_date" class="required">
                        <?= lang('projects.date_of_approval') ?>
                    </label>
                </div>
            <?php } ?>


            <?php if (array_key_exists('rejection_date', $fields)) { ?>
                <h5 class="mt-0">
                    <?= lang('common.rejection') ?>
                </h5>

                <div class="form-group floating-form">
                    <input type="date" class="form-control large" name="values[rejection_date]" id="rejection_date" value="<?= val('rejection_date', date('Y-m-d')) ?>" required>
                    <label for="rejection_date" class="required">
                        <?= lang('projects.date_of_rejection') ?>
                    </label>
                </div>
            <?php } ?>


            <?php if (array_key_exists('comment', $fields)) { ?>
                <div class="form-group floating-form">
                    <textarea name="values[comment]" id="comment" cols="30" rows="5" class="form-control" placeholder="Comment" <?= $req('comment') ?>><?= val('comment') ?></textarea>
                    <label for="comment <?= $req('comment') ?>">
                        <?= lang('common.comment') ?>
                    </label>
                </div>
            <?php } ?>


            <?php if (array_intersect(['name', 'name_de', 'title', 'title_de', 'start_proposed', 'start', 'purpose', 'internal_number'], $field_keys)) { ?>

                <h5>
                    <?= lang('projects.general_information') ?>
                </h5>


                <?php if (array_key_exists('acronym', $fields)) { ?>
                    <div class="form-group floating-form with-icon">
                        <input type="text" class="form-control" name="values[acronym]" id="acronym" value="<?= val('acronym') ?>" maxlength="100" placeholder="Short title" <?= $req('acronym') ?>>
                        <label for="acronym" class="<?= $req('acronym') ?>">
                            <?= lang('projects.acronym') ?>
                        </label>
                    </div>
                <?php } ?>


                <?php if (array_key_exists('name', $fields)) { ?>
                    <div class="form-group floating-form with-icon">
                        <input type="text" class="form-control" name="values[name]" id="name" value="<?= val('name') ?>" maxlength="100" placeholder="Short title" required>
                        <label for="name" class="required">
                            <?= lang('projects.short_title') ?>
                        </label>
                        <?php if (array_key_exists('name_de', $fields)) { ?>
                            <img src="<?= ROOTPATH ?>/img/GB.svg" alt="" class="flag form-icon">
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('name_de', $fields)) { ?>
                    <div class="form-group floating-form position-relative with-icon">
                        <input type="text" class="form-control" name="values[name_de]" id="name_de" value="<?= val('name_de') ?>" maxlength="100" placeholder="Kurztitel" <?= $req('name_de') ?>>
                        <label for="name_de" class="<?= $req('name_de') ?>">
                            <?= lang('projects.short_title_german') ?>
                        </label>
                        <img src="<?= ROOTPATH ?>/img/DE.svg" alt="" class="flag form-icon">
                    </div>
                <?php } ?>


                <?php if (array_key_exists('title', $fields)) { ?>
                    <div class="form-group with-icon">
                        <div class=" lang-<?= lang('common.this_language') ?>">
                            <label for="title" class="required floating-title">
                                <?= lang('projects.full_title_of_the_project') ?>
                            </label>

                            <div class="form-group title-editor" id="title-quill"><?= $form['title'] ?? '' ?></div>
                            <input type="text" class="form-control hidden" name="values[title]" id="title" value="<?= val('title') ?>">
                        </div>

                        <?php if (array_key_exists('title_de', $fields)) { ?>
                            <img src="<?= ROOTPATH ?>/img/GB.svg" alt="" class="flag form-icon top-0" style="transform:translate(0, 4rem)">
                        <?php } ?>

                        <script>
                            quillEditor('title');
                        </script>
                    </div>
                <?php } ?>
                <?php if (array_key_exists('title_de', $fields)) { ?>
                    <div class="form-group with-icon">
                        <div class=" lang-<?= lang('common.this_language') ?>">
                            <label for="title_de" class="floating-title <?= $req('title_de') ?>">
                                <?= lang('projects.full_title_of_the_project_german') ?>
                            </label>

                            <div class="form-group title-editor" id="title_de-quill"><?= $form['title_de'] ?? '' ?></div>
                            <input type="text" class="form-control hidden" name="values[title_de]" id="title_de" value="<?= val('title_de') ?>" <?= $req('title_de') ?>>
                        </div>
                        <img src="<?= ROOTPATH ?>/img/DE.svg" alt="" class="flag form-icon top-0" style="transform:translate(0, 4rem)">

                        <script>
                            quillEditor('title_de');
                        </script>
                    </div>
                <?php } ?>


                <?php if (array_key_exists('start_proposed', $fields)) { ?>

                    <div class="row row-eq-spacing mt-0 align-items-end ">
                        <div class="col-sm-4 floating-form">
                            <input type="date" class="form-control" name="values[start_proposed]" value="<?= valueFromDateArray(val('start_proposed')) ?>" id="start_proposed" required>

                            <label for="start_proposed" class="required">
                                <?= lang('projects.proposed_start_date') ?>
                            </label>
                        </div>
                        <div class="col-sm-4">
                            <span class="floating-title">
                                <?= lang('projects.shortcut_length') ?>
                            </span>
                            <div class="btn-group w-full">
                                <div class="btn small" onclick="timeframeProposed(36)"><?= lang('projects.3_yr') ?></div>
                                <div class="btn small" onclick="timeframeProposed(12)"><?= lang('projects.1_yr') ?></div>
                                <div class="btn small" onclick="timeframeProposed(6)"><?= lang('projects.6_mo') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-4 floating-form">
                            <input type="date" class="form-control" name="values[end_proposed]" value="<?= valueFromDateArray(val('end_proposed')) ?>" id="end_proposed" required>

                            <label for="end_proposed" class="required">
                                <?= lang('projects.proposed_end_date') ?>
                            </label>
                        </div>
                    </div>



                    <script>
                        function timeframeProposed(month) {
                            let startField = document.querySelector('#start_proposed');
                            let start = startField.valueAsDate;
                            if (start == '' || start === null) {
                                toastError(<?= json_encode(lang('projects.please_select_a_start_date_first'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                                return;
                            }

                            let end = new Date(start.setMonth(start.getMonth() + month));
                            end.setDate(end.getDate() - 1);
                            let endField = document.querySelector('#end_proposed');
                            endField.valueAsDate = end;
                        }
                    </script>

                <?php } ?>



                <?php if (array_key_exists('start', $fields)) { 
                    $start = null;
                    $end = null;
                    if (isset($form['start'])){
                        $start = valueFromDateArray($form['start']);
                    } else if (isset($form['start_date'])){
                        $start = $form['start_date'];
                    }
                    if (isset($form['end'])){
                        $end = valueFromDateArray($form['end']);
                    } else if (isset($form['end_date'])){
                        $end = $form['end_date'];
                    }
                    ?>
                    <div class="row row-eq-spacing mt-0 align-items-end ">
                        <div class="col-sm-4 floating-form">
                            <input type="date" class="form-control" name="values[start]" value="<?= $start ?>" id="start" required>

                            <label for="start" class="required">
                                <?= lang('projects.project_start') ?>
                            </label>
                        </div>
                        <div class="col-sm-4">
                            <span class="floating-title">
                                <?= lang('projects.shortcut_length') ?>
                            </span>
                            <div class="btn-group w-full">
                                <div class="btn small" onclick="timeframe(36)"><?= lang('projects.3_yr') ?></div>
                                <div class="btn small" onclick="timeframe(12)"><?= lang('projects.1_yr') ?></div>
                                <div class="btn small" onclick="timeframe(6)"><?= lang('projects.6_mo') ?></div>
                            </div>
                        </div>
                        <div class="col-sm-4 floating-form">
                            <input type="date" class="form-control" name="values[end]" value="<?= $end ?>" id="end" required>

                            <label for="end" class="required">
                                <?= lang('projects.project_end') ?>
                            </label>
                        </div>
                    </div>

                    <script>
                        function timeframe(month) {
                            let startField = document.querySelector('#start');
                            let start = startField.valueAsDate;
                            if (start == '' || start === null) {
                                toastError(<?= json_encode(lang('projects.please_select_a_start_date_first'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)
                                return;
                            }

                            let end = new Date(start.setMonth(start.getMonth() + month));
                            end.setDate(end.getDate() - 1);
                            let endField = document.querySelector('#end');
                            endField.valueAsDate = end;
                        }
                    </script>
                <?php } ?>


                <?php if (array_key_exists('purpose', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[purpose]" id="purpose" <?= $req('purpose') ?> autocomplete="off">
                            <?php
                            $vocab = $Vocabulary->getValues('project-purpose');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= sel('purpose', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="purpose" class="<?= $req('purpose') ?>">
                            <?= lang('projects.purpose_of_the_project') ?>
                        </label>
                    </div>
                <?php } ?>


                <?php if (array_key_exists('internal_number', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[internal_number]" id="internal_number" value="<?= val('internal_number') ?>" placeholder="1234" <?= $req('internal_number') ?>>

                        <label for="internal_number" class="<?= $req('internal_number') ?>">
                            <?= lang('common.internal_id') ?>
                        </label>
                    </div>
                <?php } ?>

            <?php } ?>




            <?php if (array_intersect(['scholar', 'supervisor', 'applicants'], $field_keys)) { ?>
                <h5>
                    <?= lang('common.persons') ?>
                </h5>

                <?php if (array_key_exists('applicants', $fields)) { ?>
                    <div class="data-module col-12" data-module="authors">
                        <label for="applicant" class="floating-title required">
                            <?= lang('projects.applicant_s') ?>
                        </label>
                        <div class="author-widget" id="author-widget">
                            <div class="author-list p-10" id="author-list">
                                <?php foreach ($form['applicants'] ?? array($_SESSION['username']) as $a) { ?>
                                    <div class='author'>
                                        <?= $DB->getNameFromId($a) ?>
                                        <input type='hidden' name='values[applicants][]' value='<?= $a ?>'>
                                        <a onclick='$(this).closest(".author").remove()'>&times;</a>
                                    </div>
                                <?php } ?>

                            </div>
                            <div class="footer">
                                <div class="input-group small d-inline-flex w-auto">
                                    <select class="form-control" id="add-author" autocomplete="off">
                                        <?php
                                        $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]);
                                        foreach ($userlist as $j) { ?>
                                            <option value="<?= $j['username'] ?>" <?= $j['username'] == ($user) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn secondary h-full" type="button" onclick="addAuthorDiv(event);">
                                            <i class="ph ph-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <small class="text-muted">
                            <?= lang('projects.more_persons_may_be_added_later') ?>
                        </small>
                    </div>
                    <script>
                        function addAuthorDiv(event) {
                            var input = $('#add-author')
                            var username = input.val()
                            var name = input.find('option:selected').text()
                            var el = $('#author-list')
                            var author = $('<div class="author">')
                                .html(name);
                            author.append('<input type="hidden" name="values[applicants][]" value="' + username + '">')
                            author.append('<a onclick="$(this).closest(\'.author\').remove()">&times;</a>')
                            author.appendTo(el)
                        }
                    </script>
                <?php } ?>


                <?php if (array_key_exists('scholar', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" id="scholar" name="values[scholar]" required autocomplete="off" <?= $req('scholar') ?>>
                            <?php
                            $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]);
                            foreach ($userlist as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == ($form['scholar'] ?? $user) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                        <label for="scholar" class="<?= $req('scholar') ?>">
                            <?= lang('projects.scholar') ?>
                        </label>
                    </div>
                <?php } ?>


                <?php if (array_key_exists('supervisor', $fields)) {
                    $selected = '';
                    if ($new_project) {
                        include_once BASEPATH . "/php/Groups.php";
                        // default: head of group
                        $dept = $USER['depts'] ?? [];
                        if (!empty($dept)) {
                            $Groups = new Groups();
                            $heads = $Groups->getGroup($dept[0])['head'] ?? array();
                            $selected = $heads[0] ?? '';
                        }
                    } else {
                        $selected = $form['supervisor'] ?? '';
                    }

                ?>
                    <div class="form-group floating-form">
                        <select class="form-control" id="supervisor" name="values[supervisor]" required autocomplete="off" <?= $req('supervisor') ?>>
                            <?php
                            $userlist = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['is_active' => -1, 'last' => 1]]);
                            foreach ($userlist as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == $selected ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                        <label for="supervisor" class="<?= $req('supervisor') ?>">
                            <?= lang('projects.supervisor') ?>
                        </label>
                    </div>
                <?php } ?>
            <?php } ?>



            <?php if (array_intersect(['scholarship', 'university'], $field_keys)) { ?>
                <h5>
                    <?= lang('projects.scholarship') ?>
                </h5>

                <?php if (array_key_exists('scholarship', $fields)) {
                    // scholarship is a synonym for funding_organization
                    $org_id = $form['funding_organization'] ?? ''; ?>

                    <a id="scholarship" class="box py-5 px-10 mt-0 d-block colorless" href="#scholarship-org-modal">
                        <label for="funding_organization" class="floating-title <?= $req('scholarship') ?>">
                            <?= lang('projects.scholarship_institution') ?>
                        </label>
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[funding_organization]" value="<?= $org_id ?>" <?= $req('funding_organization') ?> readonly />

                        <div id="scholarship-org-value">
                            <?php if (empty($org_id)) { ?>
                                <?= lang('error.organization_select_missing') ?>
                                <?php } else {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= $collab['name'] ?></b>
                                    <br><small class="text-muted"><?= $collab['location'] ?></small>
                                <?php } else { ?>
                                    <?= lang('projects.no_organization_selected') ?>
                                    <br><small class="text-muted"><?= $org_id ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>


                    <div class="modal" id="scholarship-org-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="scholarship-search"><?= lang('projects.search_scholarship_institutions') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="scholarship-search" onkeydown="selectOrgEvent(event, 'scholarship')" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'scholarship')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="scholarship-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="scholarship-org-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                                <p>
                                    <?php 
                                    if ($Settings->hasPermission('organizations.edit')) {
                                        lang('forms.organization_not_found_add_new', replace:[
                                            'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/organizations/new">' . lang('forms.organization_not_found_add_new_link') . '</a>')
                                        ]);
                                    } else { 
                                        lang('forms.organization_not_found_contact', replace:[
                                            'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/user/browse?permission=organizations.edit">' . lang('forms.organization_not_found_contact_link') . '</a>')
                                        ]);
                                    } 
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('university', $fields)) {
                    $org_id = $form['university'] ?? ''; ?>
                    <a id="university" class="box py-5 px-10 mt-0 d-block colorless" href="#university-org-modal">
                        <label for="university" class="floating-title <?= $req('university') ?>">
                            <?= lang('projects.partner_university') ?>
                        </label>
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[university]" value="<?= $org_id ?>" <?= $req('university') ?> readonly />

                        <div id="university-org-value">
                            <?php if (empty($org_id)) { ?>
                                <?= lang('error.organization_select_missing') ?>
                                <?php } else {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= $collab['name'] ?></b>
                                    <br><small class="text-muted"><?= $collab['location'] ?></small>
                                <?php } else { ?>
                                    <?= lang('error.organization_select_missing') ?>:
                                    <br><small class="text-muted"><?= $org_id ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>


                    <div class="modal" id="university-org-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="university-search"><?= lang('projects.search_for_partner_university') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="university-search" onkeydown="selectOrgEvent(event, 'university')" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'university')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="university-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="university-org-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                                 <p>
                                    <?php 
                                    if ($Settings->hasPermission('organizations.edit')) {
                                        lang('forms.organization_not_found_add_new', replace:[
                                            'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/organizations/new">' . lang('forms.organization_not_found_add_new_link') . '</a>')
                                        ]);
                                    } else { 
                                        lang('forms.organization_not_found_contact', replace:[
                                            'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/user/browse?permission=organizations.edit">' . lang('forms.organization_not_found_contact_link') . '</a>')
                                        ]);
                                    } 
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>



            <?php if (array_intersect(['funder', 'funding_organization', 'funding_program', 'funding_program_select', 'funding_number', 'role', 'coordinator', 'funding_type', 'joint_project', 'project_type'], $field_keys)) { ?>

                <h5 class="funding">
                    <?= lang('projects.funding') ?>
                </h5>
                <?php if (array_key_exists('funder', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[funder]" value="<?= val('funder') ?>" <?= $req('funder') ?> id="funder">
                            <?php
                            $vocab = $Vocabulary->getValues('funder');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= sel('funder', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="funder" class="<?= $req('funder') ?>">
                            <?= lang('projects.funder_category') ?>
                        </label>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('funding_organization', $fields)) {
                    $org_id = $form['funding_organization'] ?? '';
                ?>
                    <a id="funding" class="box py-5 px-10 mt-0 d-block colorless" href="#funding-org-modal">
                        <label for="funding_organization" class="floating-title <?= $req('funding_organization') ?>">
                            <?= lang('common.funding_organizations') ?>
                        </label>
                        <i class="ph ph-edit float-right"></i>
                        <input hidden readonly name="values[funding_organization]" value="<?= $org_id ?>" <?= $req('funding_organization') ?> readonly />

                        <div id="funding-org-value">
                            <?php if (empty($org_id)) { ?>
                                <?= lang('error.organization_select_missing') ?>
                                <?php } else {
                                $collab = $osiris->organizations->findOne(['_id' => $org_id]);
                                if (!empty($collab)) { ?>
                                    <b><?= $collab['name'] ?></b>
                                    <br><small class="text-muted"><?= $collab['location'] ?></small>
                                <?php } else { ?>
                                    <?= lang('error.organization_select_missing') ?>:
                                    <br><small class="text-muted"><?= $org_id ?></small>
                            <?php }
                            } ?>
                        </div>
                    </a>


                    <div class="modal" id="funding-org-modal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <label for="funding-search"><?= lang('projects.search_funding_organization') ?></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="funding-search" onkeydown="selectOrgEvent(event, 'funding')" placeholder="<?= lang('forms.search_for_organization') ?>" autocomplete="off">
                                    <div class="input-group-append">
                                        <button class="btn" type="button" onclick="selectOrgEvent(null, 'funding')"><i class="ph ph-magnifying-glass"></i></button>
                                    </div>
                                </div>
                                <p id="funding-search-comment"></p>
                                <table class="table simple">
                                    <tbody id="funding-org-suggest">
                                    </tbody>
                                </table>
                                <small class="text-muted">Powered by <a href="https://ror.org/" target="_blank" rel="noopener noreferrer">ROR</a></small>
                                 <p>
                                    <?php 
                                    if ($Settings->hasPermission('organizations.edit')) {
                                        lang('forms.organization_not_found_add_new', replace:[
                                            'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/organizations/new">' . lang('forms.organization_not_found_add_new_link') . '</a>')
                                        ]);
                                    } else { 
                                        lang('forms.organization_not_found_contact', replace:[
                                            'link' => new Html('<a target="_blank" href="' . ROOTPATH . '/user/browse?permission=organizations.edit">' . lang('forms.organization_not_found_contact_link') . '</a>')
                                        ]);
                                    } ?>
                                </p>
                            </div>
                        </div>
                    </div>


                <?php } ?>


                <?php if (array_key_exists('funding_program_select', $fields)) { ?>
                    <div class="form-group">
                        <div class="floating-form">
                        <select class="form-control" name="values[funding_program_select]" id="funding_program_select" <?= $req('funding_program_select') ?>>
                            <?php
                            if ($req('funding_program_select') == '') { ?>
                                <option value=""><?= lang('projects.select_funding_program') ?></option>
                            <?php }
                            $vocab = $Vocabulary->getValues('funding-program');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= sel('funding_program_select', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="funding_program_select" class="<?= $req('funding_program_select') ?>">
                            <?= lang('projects.funding_program') ?>
                        </label>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('funding_program', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[funding_program]" value="<?= val('funding_program') ?>" id="funding_program" placeholder="Funding program">
                        <label for="funding_program" class="<?= $req('funding_program') ?>">
                            <?= lang('projects.funding_program') ?>
                        </label>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('funding_number', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" name="values[funding_number]" value="<?= val('funding_number') ?>" id="funding_number" placeholder="ABC123">
                        <label for="funding_number" class="<?= $req('funding_number') ?>">
                            <?= lang('projects.funding_reference_number') ?>
                        </label>
                        <small class="text-muted"><?= lang('projects.multiple_seperated_by_comma') ?></small>
                    </div>
                <?php } ?>



                <?php if (array_key_exists('joint_project', $fields)) { ?>
                    <fieldset class="mt-20">
                        <legend class="font-size-14"><?= lang('projects.joint_project') ?></legend>

                        <b>
                            <?= lang('projects.is_this_project_part_of_a_joint_project_with_other_institutions') ?>
                        </b>
                        <div class="custom-radio d-inline-block mr-10 joint-project-input">
                            <input type="radio" id="joint_project_yes" name="values[joint_project]" value="true" <?= val('joint_project', false) ? 'checked' : '' ?>>
                            <label for="joint_project_yes">
                                <?= lang('common.yes') ?>
                            </label>
                        </div>
                        <div class="custom-radio d-inline-block mr-10 joint-project-input">
                            <input type="radio" id="joint_project_no" name="values[joint_project]" value="false" <?= !val('joint_project', false) ? 'checked' : '' ?>>
                            <label for="joint_project_no">
                                <?= lang('common.no') ?>
                            </label>
                        </div>

                        <div id="joint-project-yes" style="display: <?= val('joint_project', false) ? 'block' : 'none' ?>;">
                            <div class="form-group floating-form mt-20">
                                <input type="text" class="form-control" name="values[joint_project_identifier]" id="joint_project_identifier" value="<?= val('joint_project_identifier') ?>" placeholder="ABC123">
                                <label for="joint_project_identifier" class="">
                                    <?= lang('projects.identifier_of_the_joint_project') ?>
                                </label>
                            </div>
                            <div class="form-group floating-form">
                                <input type="text" class="form-control" name="values[joint_project_title]" id="joint_project_title" value="<?= val('joint_project_title') ?>" placeholder="">
                                <label for="joint_project_title" class="">
                                    <?= lang('projects.title_of_the_joint_project') ?>
                                </label>
                            </div>
                            <div class="form-group floating-form">
                                <!-- checkbox -->
                                <input type="hidden" name="values[joint_project_speaker]" value="false">
                                <div class="custom-checkbox">
                                    <input type="checkbox" id="joint_project_speaker" <?= val('joint_project_speaker', false) ? 'checked' : '' ?> name="values[joint_project_speaker]" value="true">
                                    <label for="joint_project_speaker">
                                        <?= lang('projects.speaker_coordinator_consortium_leader_role') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <script>
                        $('.joint-project-input input').on('change', function() {
                            if (this.value === 'true') {
                                $('#joint-project-yes').show();
                            } else {
                                $('#joint-project-yes').hide();
                            }
                        });
                    </script>
                <?php } ?>
                
                
                <div class="row row-eq-spacing">
                    <?php if (array_key_exists('role', $fields)) { ?>
                        <div class="col floating-form">
                            <select class="form-control" name="values[role]" id="role" <?= $req('role') ?>>
                                <?php
                                $vocab = $Vocabulary->getValues('project-institute-role');
                                foreach ($vocab as $v) { ?>
                                    <option value="<?= $v['id'] ?>" <?= sel('funder', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                                <?php } ?>
                            </select>
                            <label for="role" class="<?= $req('role') ?>">
                                <?= lang('projects.role_of') ?> <?= $Settings->get('affiliation') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (array_key_exists('coordinator', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" class="form-control" <?= $req('coordinator') ?> name="values[coordinator]" id="coordinator" value="<?= val('coordinator', $Settings->get('affiliation')) ?>" placeholder="Institute of XYZ">
                            <label for="coordinator" class="<?= $req('coordinator') ?>">
                                <?= lang('projects.coordinator_facility') ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>

                <?php if (array_key_exists('funding_type', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[funding_type]" id="funding_type" <?= $req('funding_type') ?>>
                            <?php
                            $vocab = $Vocabulary->getValues('funding-type');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= sel('funding_type', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="funding_type" class="<?= $req('funding_type') ?>">
                            <?= lang('projects.funding_type') ?>
                        </label>
                    </div>
                <?php } ?>


                <?php if (array_key_exists('project_type', $fields)) { ?>
                    <div class="form-group floating-form">
                        <select class="form-control" name="values[project_type]" id="project_type" <?= $req('project_type') ?>>
                            <?php
                            $vocab = $Vocabulary->getValues('project-type');
                            foreach ($vocab as $v) { ?>
                                <option value="<?= $v['id'] ?>" <?= sel('project_type', $v['id']) ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                            <?php } ?>
                        </select>
                        <label for="project_type" class="<?= $req('project_type') ?>">
                            <?= lang('projects.project_type') ?>
                        </label>
                    </div>
                    <?php } ?>
            <?php } ?>



            <?php if (array_intersect(['grant_sum_proposed', 'grant_income_proposed', 'grant_sum', 'grant_income'], $field_keys)) { ?>

                <h5>
                    <?= lang('projects.grant_sum') ?> in Euro
                </h5>

                <!-- <b>
                <?= lang('projects.proposed_grant') ?>
                </b> -->
                <div class="row row-eq-spacing mt-0">

                    <?php if (array_key_exists('grant_sum_proposed', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" step="1" class="form-control money-input" <?= $req('grant_sum_proposed') ?> name="values[grant_sum_proposed]" id="grant_sum_proposed" value="<?= val('grant_sum_proposed') ?>" placeholder="112345">
                            <label for="grant_sum_proposed" class="<?= $req('grant_sum_proposed') ?>">
                                <?= lang('projects.proposed_grant') ?> (<?= lang('common.total') ?>)
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (array_key_exists('grant_income_proposed', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" step="1" class="form-control money-input" <?= $req('grant_income_proposed') ?> name="values[grant_income_proposed]" id="grant_income_proposed" value="<?= val('grant_income_proposed') ?>" placeholder="112345">

                            <label for="grant_income_proposed" class="<?= $req('grant_income_proposed') ?>">
                                <?= lang('projects.proposed_grant_institute') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (array_key_exists('grant_subproject_proposed', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" step="1" class="form-control money-input" <?= $req('grant_subproject_proposed') ?> name="values[grant_subproject_proposed]" id="grant_subproject_proposed" value="<?= val('grant_subproject_proposed') ?>" placeholder="112345">

                            <label for="grant_subproject_proposed" class="<?= $req('grant_subproject_proposed') ?>">
                                <?= lang('projects.proposed_grant_subproject') ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>

                <div class="row row-eq-spacing mt-0">
                    <?php if (array_key_exists('grant_sum', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" step="1" class="form-control money-input" <?= $req('grant_sum') ?> name="values[grant_sum]" id="grant_sum" value="<?= val('grant_sum') ?>" placeholder="1234">
                            <label for="grant_sum" class="<?= $req('grant_sum') ?>">
                                <?= lang('projects.grant_sum_edit') ?> (<?= lang('common.total') ?>)
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (array_key_exists('grant_income', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" step="1" class="form-control money-input" <?= $req('grant_income') ?> name="values[grant_income]" id="grant_income" value="<?= val('grant_income') ?>" placeholder="1234">
                            <label for="grant_income" class="<?= $req('grant_income') ?>">
                                <?= lang('projects.grant_sum_institute_edit') ?>
                            </label>
                        </div>
                    <?php } ?>
                    <?php if (array_key_exists('grant_subproject', $fields)) { ?>
                        <div class="col floating-form">
                            <input type="text" step="1" class="form-control money-input" <?= $req('grant_subproject') ?> name="values[grant_subproject]" id="grant_subproject" value="<?= val('grant_subproject') ?>" placeholder="1234">
                            <label for="grant_subproject" class="<?= $req('grant_subproject') ?>">
                                <?= lang('projects.grant_sum_subproject_edit') ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>



            <?php if (array_intersect(['public', 'abstract', 'abstract_de', 'website'], $field_keys)) { ?>
                <h5>
                    <?= lang('projects.project_description') ?>
                </h5>

                <?php if (array_key_exists('public', $fields)) { ?>
                    <div class="form-group ">
                        <input type="hidden" name="values[public]" value="0">
                        <div class="custom-checkbox">
                            <input type="checkbox" id="public-check" <?= val('public', false) ? 'checked' : '' ?> name="values[public]">
                            <label for="public-check">
                                <?= lang('projects.approval_of_the_internet_presentation_of_the_approved_project') ?>
                            </label>
                        </div>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('abstract', $fields)) { ?>
                    <div class="form-group with-icon">
                        <div class=" lang-<?= lang('common.this_language') ?>">
                            <label for="abstract" class="floating-title  <?= $req('abstract') ?>">
                                <?= lang('projects.abstract_edit') ?>
                            </label>

                            <div class="form-group title-editor" id="abstract-quill"><?= $form['abstract'] ?? '' ?></div>
                            <textarea class="form-control hidden" name="values[abstract]" id="abstract" <?= $req('abstract') ?>><?= val('abstract') ?></textarea>
                        </div>

                        <?php if (array_key_exists('abstract_de', $fields)) { ?>
                            <img src="<?= ROOTPATH ?>/img/GB.svg" alt="" class="flag form-icon top-0" style="transform:translate(0, 4rem)">
                        <?php } ?>

                        <script>
                            quillEditor('abstract');
                        </script>
                    </div>
                <?php } ?>
                <?php if (array_key_exists('abstract_de', $fields)) { ?>
                    <div class="form-group with-icon">
                        <div class=" lang-<?= lang('common.this_language') ?>">
                            <label for="abstract_de" class="floating-title <?= $req('abstract_de') ?>">
                                <?= lang('projects.abstract_german') ?>
                            </label>

                            <div class="form-group title-editor" id="abstract_de-quill"><?= $form['abstract_de'] ?? '' ?></div>
                            <textarea class="form-control hidden" name="values[abstract_de]" id="abstract_de" <?= $req('abstract_de') ?>><?= val('abstract_de') ?></textarea>
                        </div>
                        <img src="<?= ROOTPATH ?>/img/DE.svg" alt="" class="flag form-icon top-0" style="transform:translate(0, 4rem)">

                        <script>
                            quillEditor('abstract_de');
                        </script>
                    </div>
                <?php } ?>


                <?php if (array_key_exists('website', $fields)) { ?>
                    <div class="form-group floating-form">
                        <input type="text" class="form-control" <?= $req('website') ?> name="values[website]" id="website" value="<?= val('website') ?>" placeholder="https://example.com">
                        <label for="website" class="<?= $req('website') ?>">
                            <?= lang('projects.project_website_edit') ?>
                        </label>
                        <small class="text-muted">
                            <?= lang('projects.please_enter_full_url_incl_http') ?>
                        </small>
                    </div>
                <?php } ?>

            <?php } ?>

            <?php if (array_key_exists('tags', $fields) && $Settings->featureEnabled('tags') && $Settings->hasPermission('projects.tags')) {
                $Settings->tagChooser($form['tags'] ?? []);
            } ?>

            <?php
            if (array_key_exists('kdsf-ffk', $fields)) {
                include_once BASEPATH . "/components/kdsf-ffk-select.php";
            }
            ?>

            <?php if (array_key_exists('countries', $fields)) {
                $countries = $form['countries'] ?? [];
            ?>
                <h5>
                    <?= lang('projects.countries_of_research') ?>
                </h5>

                <div class="author-widget" id="author-widget">
                    <div class="author-list p-10" id="author-list">
                        <?php
                        $lang = lang('common.field_name_language');
                        foreach ($countries as $iso) { ?>
                            <div class='author'>
                                <input type='hidden' name='values[countries][]' value='<?= $iso ?>'>
                                <?= $DB->getCountry($iso, $lang) ?>
                                <a onclick="$(this).closest('.author').remove()">&times;</a>
                            </div>
                        <?php } ?>

                    </div>
                    <div class="footer">
                        <div class="input-group sm d-inline-flex w-auto">
                            <select id="add-country">
                                <option value="" disabled checked><?= lang('common.please_select_a_country') ?></option>
                                <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $iso => $name) { ?>
                                    <option value="<?= $iso ?>"><?= $name ?></option>
                                <?php } ?>
                            </select>
                            <div class="input-group-append">
                                <button class="btn secondary h-full" type="button" onclick="addCountry(event);">
                                    <i class="ph ph-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <script>
                        function addCountry(event) {
                            var el = $('#add-country')
                            var data = el.val()
                            if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                                event.preventDefault();
                                if (data) {
                                    $('#author-list').append('<div class="author"><input type="hidden" name="values[countries][]" value="' + data + '">' + el.find('option:selected').text() + '<a onclick="$(this).closest(\'.author\').remove()">&times;</a></div>')
                                }
                                $(el).val('')
                                return false;
                            }
                        }
                    </script>

                </div>
            <?php } ?>


            <?php if (array_key_exists('research-countries', $fields)) {
                $countries = $form['research-countries'] ?? [];
            ?>
                <h5>
                    <?= lang('projects.countries_you_will_do_research_on_in') ?>
                </h5>


                <table class="table">
                    <thead>
                        <tr>
                            <th><?= lang('common.country') ?></th>
                            <th><?= lang('common.research') ?></th>
                            <th><?= lang('common.action') ?></th>
                        </tr>
                    </thead>
                    <tbody id="country-list">
                        <?php foreach ($countries as $country) {
                            if (empty($country) || !isset($country['iso'])) continue;
                            $iso = $country['iso'];
                            $role = $country['role'] ?? 'both';
                        ?>
                            <tr>
                                <td><?= $DB->getCountry($iso, lang('common.field_name_language')) ?></td>
                                <td><?= $role ?></td>
                                <td>
                                    <a onclick="$(this).closest('tr').remove()"><?= lang('action.remove') ?></a>
                                    <input type="text" name="values[research-countries][]" value="<?= $iso ?>;<?= $role ?>" hidden>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">
                                <div class="input-group small d-inline-flex w-auto">
                                    <select id="add-research-country" class="form-control">
                                        <option value="" disabled checked><?= lang('common.please_select_a_country') ?></option>
                                        <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $iso => $name) { ?>
                                            <option value="<?= $iso ?>"><?= $name ?></option>
                                        <?php } ?>
                                    </select>
                                    <select id="add-research-country-role" class="form-control">
                                        <option value="" disabled checked><?= lang('projects.please_select_a_role') ?></option>
                                        <option value="in"><?= lang('projects.research_in_this_country') ?></option>
                                        <option value="about"><?= lang('projects.research_about_this_country') ?></option>
                                        <option value="both"><?= lang('projects.both') ?></option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn secondary" type="button" onclick="addResearchCountry(event);">
                                            <i class="ph ph-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>

                <script>
                    function addResearchCountry(event) {
                        var el = $('#add-research-country')
                        var data = el.val()
                        var type = $('#add-research-country-role').val()
                        if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                            event.preventDefault();
                            if (data) {
                                let tr = $('<tr>')
                                tr.append('<td>' + el.find('option:selected').text() + '</td>')
                                tr.append('<td>' + type + '</td>')
                                tr.append('<td><a onclick="$(this).closest(\'tr\').remove()"><?= lang('action.remove') ?></a><input type="text" name="values[research-countries][]" value="' + data + ';' + type + '" hidden></td>')
                                $('#country-list').append(tr)
                            }
                            $(el).val('')
                            return false;
                        }
                    }
                </script>
            <?php } ?>


            <!-- if topics are registered, you can choose them here -->
            <?php if (array_key_exists('topics', $fields)) { ?>
                <?php $Settings->topicChooser(DB::doc2Arr($form['topics'] ?? [])) ?>
            <?php } ?>

            <?php
            $custom_fields = [];
            foreach ($osiris->adminFields->distinct('id') as $key) {
                if (array_key_exists($key, $fields)) {
                    $custom_fields[] = $key;
                }
            }
            if (!empty($custom_fields)) {
                require_once BASEPATH . "/php/Modules.php";
                $Modules = new Modules($form);

                echo "<h5>" . lang('projects.institutional_fields') . "</h5>";
                foreach ($custom_fields as $key) {
                    $Modules->custom_field($key, in_array($key, $required_fields));
                }
            }
            ?>

            <?php if (array_key_exists('nagoya', $fields) && $Settings->featureEnabled('nagoya')) { ?>
                <h5>
                    <?= lang('Nagoya Protocol') ?>
                </h5>
                <?php

                $nagoya = $form['nagoya'] ?? [];
                $countries = $nagoya['countries'] ?? [];
                $enabled = $nagoya['enabled'] ?? false;

                // if the form is not empty, and Nagoya was enabled just refer to the nagoya tab and do not show the fields here
                if (!empty($form) && $enabled && !empty($countries)) { ?>
                    <div class="alert alert-info">
                        <?= lang('projects.nagoya_protocol_settings_can_be_found_in_the_nagoya_protocol_tab_after_savi') ?>
                    </div>
                <?php
                } else {
                ?>

                    <div class="form-group">
                        <label for="nagoya">
                            <?= lang('projects.do_you_plan_to_collect_obtain_or_utilise_genetic_resources_biological_sampl') ?>
                        </label>
                        <div>
                            <input type="radio" name="values[nagoya]" id="nagoya-yes" value="yes" <?= ($enabled) ? 'checked' : '' ?>>
                            <label for="nagoya-yes">Yes</label>
                            <input type="radio" name="values[nagoya]" id="nagoya-no" value="no" <?= (!$enabled) ? 'checked' : '' ?>>
                            <label for="nagoya-no">No</label>
                        </div>

                        <!-- <small class="text-muted">
                            <?= lang('projects.if_you_answer_yes_you_will_be_prompted_to_list_all_countries_from_which_you') ?>
                        </small> -->

                        <div id="ressource-nagoya" style="display: <?= ($enabled) ? 'block' : 'none' ?>;">

                            <b>
                                <?= lang('projects.please_list_all_countries') ?>
                            </b>

                            <div class="author-widget" id="author-widget">
                                <div class="author-list p-10" id="nagoya-countries-list">
                                    <?php
                                    $lang = lang('common.field_name_language');
                                    foreach ($countries as $country) {
                                        $iso = $country['code'] ?? '';
                                    ?>
                                        <div class='author'>
                                            <input type='hidden' name='values[nagoya_countries][]' value='<?= $iso ?>'>
                                            <?= $DB->getCountry($iso, $lang) ?>
                                            <a onclick="$(this).closest('.author').remove()">&times;</a>
                                        </div>
                                    <?php } ?>

                                </div>
                                <div class="footer">
                                    <div class="input-group sm d-inline-flex w-auto">
                                        <select id="add-nagoya-country">
                                            <option value="" disabled checked><?= lang('common.please_select_a_country') ?></option>
                                            <?php foreach ($DB->getCountries(lang('common.field_name_language')) as $iso => $name) { ?>
                                                <option value="<?= $iso ?>"><?= $name ?></option>
                                            <?php } ?>
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn secondary h-full" type="button" onclick="addNagoyaCountry(event);">
                                                <i class="ph ph-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <script>
                                    function addNagoyaCountry(event) {
                                        var el = $('#add-nagoya-country')
                                        var data = el.val()
                                        if ((event.type == 'keypress' && event.keyCode == '13') || event.type == 'click') {
                                            event.preventDefault();
                                            if (data) {
                                                $('#nagoya-countries-list').append('<div class="author"><input type="hidden" name="values[nagoya_countries][]" value="' + data + '">' + el.find('option:selected').text() + '<a onclick="$(this).closest(\'.author\').remove()">&times;</a></div>')
                                            }
                                            $(el).val('')
                                            return false;
                                        }
                                    }
                                </script>

                            </div>
                        </div>


                        <script>
                            document.getElementById('nagoya-yes').addEventListener('change', function() {
                                document.getElementById('ressource-nagoya').style.display = 'block';
                            });
                            document.getElementById('nagoya-no').addEventListener('change', function() {
                                document.getElementById('ressource-nagoya').style.display = 'none';
                            });
                        </script>
                    </div>
                <?php } ?>
            <?php } ?>



            <?php if (array_intersect(['personnel', 'in-kind', 'ressources'], $field_keys)) { ?>
                <h5>
                    <?= lang('projects.resources_and_personnel') ?>
                </h5>

                <?php if (array_key_exists('personnel', $fields)) { ?>
                    <div class="form-group floating-form">
                        <textarea name="values[personnel]" id="personnel" cols="30" rows="2" class="form-control" placeholder="1 Doktorand:in"><?= val('personnel') ?></textarea>

                        <label for="personnel">
                            <?= lang('projects.personnel_measures_planned') ?>
                        </label>
                        <small class="text-muted">
                            <!-- Einstellungen/Verlängerungen in Personenmonaten & Kategorie -->
                            <?= lang('projects.hiring_extensions_in_person_months_category') ?>
                        </small>
                    </div>
                    <div class="form-group floating-form">
                        <textarea name="values[in-kind]" id="in-kind" cols="30" rows="2" class="form-control" placeholder="Antragsteller 5%"><?= val('in-kind') ?></textarea>

                        <label for="in-kind">
                            <?= lang('projects.in_kind_personnel') ?>
                        </label>
                        <small class="text-muted">
                            <?= lang('projects.informative_details_in_mentioning_the_collaborating_persons_e_g_applicant_1') ?>
                        </small>
                    </div>
                <?php } ?>

                <?php if (array_key_exists('ressources', $fields)) {
                    $res = $form['ressources'] ?? [];
                    $material = ($res['material'] ?? 'no') === 'yes';
                    $personnel = ($res['personnel'] ?? 'no') === 'yes';
                    $room = ($res['room'] ?? 'no') === 'yes';
                    $other = ($res['other'] ?? 'no') === 'yes';
                ?>
                    <div class="ressources">
                        <div class="form-group">
                            <label for="ressource1">
                                <?= lang('projects.additional_material_resources') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][material]" id="material-yes" value="yes" <?= $material ? 'checked' : '' ?>>
                                <label for="material-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][material]" id="material-no" value="no" <?= $material ? '' : 'checked' ?>>
                                <label for="material-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][material_details]" id="ressource-material" style="display: <?= $material ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['material_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('material-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-material').style.display = 'block';
                                });
                                document.getElementById('material-no').addEventListener('change', function() {
                                    document.getElementById('ressource-material').style.display = 'none';
                                });
                            </script>
                        </div>

                        <div class="form-group">
                            <label for="ressource2">
                                <?= lang('projects.additional_personnel_resources') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][personnel]" id="personnel-yes" value="yes" <?= $personnel ? 'checked' : '' ?>>
                                <label for="personnel-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][personnel]" id="personnel-no" value="no" <?= $personnel ? '' : 'checked' ?>>
                                <label for="personnel-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][personnel_details]" id="ressource-personnel" style="display: <?= $personnel ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['personnel_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('personnel-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-personnel').style.display = 'block';
                                });
                                document.getElementById('personnel-no').addEventListener('change', function() {
                                    document.getElementById('ressource-personnel').style.display = 'none';
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <label for="ressource3">
                                <?= lang('projects.additional_room_capacities') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][room]" id="room-yes" value="yes" <?= $room ? 'checked' : '' ?>>
                                <label for="room-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][room]" id="room-no" value="no" <?= $room ? '' : 'checked' ?>>
                                <label for="room-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][room_details]" id="ressource-room" style="display: <?= $room ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['room_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('room-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-room').style.display = 'block';
                                });
                                document.getElementById('room-no').addEventListener('change', function() {
                                    document.getElementById('ressource-room').style.display = 'none';
                                });
                            </script>

                        </div>
                        <div class="form-group">
                            <label for="ressource4">
                                <?= lang('projects.other_resources') ?>
                            </label>
                            <div>
                                <input type="radio" name="values[ressources][other]" id="other-yes" value="yes" <?= $other ? 'checked' : '' ?>>
                                <label for="other-yes"><?= lang('common.yes') ?></label>
                                <input type="radio" name="values[ressources][other]" id="other-no" value="no" <?= $other ? '' : 'checked' ?>>
                                <label for="other-no"><?= lang('common.no') ?></label>
                            </div>

                            <textarea type="text" class="form-control" name="values[ressources][other_details]" id="ressource-other" style="display: <?= $other ? 'block' : 'none' ?>;" placeholder="Details"><?= $res['other_details'] ?? '' ?></textarea>
                            <script>
                                document.getElementById('other-yes').addEventListener('change', function() {
                                    document.getElementById('ressource-other').style.display = 'block';
                                });
                                document.getElementById('other-no').addEventListener('change', function() {
                                    document.getElementById('ressource-other').style.display = 'none';
                                });
                            </script>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>

            <br>
            <button class="btn secondary" type="button" id="submit-btn">
                <i class="ph ph-check"></i> <?= lang('action.save') ?>
            </button>

            <script>
                let required_keys = <?= json_encode($required_fields) ?>;
                console.log(required_keys);

                let form = $('#proposal-form')
                $('#submit-btn').on('click', function(e) {
                    e.preventDefault();
                    if (validateForm()) {
                        // form.off('submit');
                        form.submit();
                    }
                });

                function validateForm() {
                    let errors = [];
                    required_keys.forEach(key => {
                        $(form).find(`[name='values[${key}]']`).each(function() {
                            if ($(this).val() == '') {
                                errors.push(key)
                                $(this).addClass('is-invalid')
                            } else {
                                $(this).removeClass('is-invalid')
                            }
                        })
                    });
                    console.log(errors);
                    if (errors.length > 0) {
                        let error_msg = <?= json_encode(lang('projects.please_fill_in_all_required_fields'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
                        error_msg += errors.join(', ')
                        toastError(error_msg);
                        return false;
                    } else {
                        return true;
                    }
                }
            </script>
        </form>

    <?php } ?>
</div>


<script>
    function selectOrgEvent(event = null, type = 'scholarship') {
        console.log(type);
        if (event === null || event.key === 'Enter') {
            if (event) event.preventDefault();

            SUGGEST = $('#' + type + '-org-suggest')
            INPUT = $('#' + type + '-search')
            SELECTED = $('#' + type + '-org-value')
            COMMENT = $('#' + type + '-search-comment')
            console.log(SUGGEST);
            window.createOrganizationTR = function(org) {
                // overwrite organisation function
                let id = cleanID(org.id)
                $('#' + type + '-org-value').html(
                    `<b>${org.name}</b> <br><small class="text-muted">${org.location}</small>`
                );
                $('#' + type + ' input').val(id);
                location.href = '#' + type;
            }

            getOrganization($('#' + type + '-search').val());
            return false;
        }
    }

    // $('.money-input').on('blur', function() {
    //     let value = $(this).val();

    //     // Nur Ziffern und Komma/Punkt behalten
    //     value = value.replace(/[^\d,\.]/g, '');

    //     // In float umwandeln
    //     const num = parseFloat(value.replace(/\./g, '').replace(',', '.'));

    //     if (!isNaN(num)) {
    //         // Formatiert anzeigen
    //         const formatted = num.toLocaleString('de-DE', {
    //             style: 'currency',
    //             currency: 'EUR'
    //         });
    //         $(this).val(formatted);
    //     } else {
    //         $(this).val('');
    //     }
    // });
</script>