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
require_once BASEPATH . "/php/ProjectForms.php";
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

<div class="container w-800">
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
            $title = lang('projects.edit_subproject') . ': ' . (localized($form['name'] ?? '') ?: localized($form['title'] ?? ''));
            $phase = 'project';
        } else if ($is_subproject) {
            $formaction = ROOTPATH . "/crud/projects/create";
            $url = ROOTPATH . "/projects/view/*";
            $title = lang('projects.create_new_subproject') . ': ' . (localized($parent['name'] ?? '') ?: localized($parent['title'] ?? ''));
            $subtitle = lang('projects.this_project_is_a_subproject_of_another_project_that_means_you_cannot_chang');
            $phase = 'project';
        } else if (isset($from_proposal) && $from_proposal) {
            $formaction = ROOTPATH . "/crud/projects/create";
            $url = ROOTPATH . "/projects/view/" . $form['_id'];
            $title = lang('projects.create_new_project_from_proposal') . ': ' . (localized($form['name'] ?? '') ?: localized($form['title'] ?? ''));
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
        } elseif ($collection === 'projects' || $selected['process'] == 'project' || $status == 'project') {
            $formaction = ROOTPATH . "/crud/projects/update/" . $form['_id'];
            $url = ROOTPATH . "/projects/view/" . $form['_id'];
            $title = lang('projects.edit_project') . ': ' . (localized($form['name'] ?? '') ?: localized($form['title'] ?? ''));
            $phase = 'project';
        } else {
            $formaction = ROOTPATH . "/crud/proposals/update/" . $form['_id'];
            $url = ROOTPATH . "/proposals/view/" . $form['_id'];
            $title = lang('projects.edit_project_proposal') . ': <q>' . (localized($form['name'] ?? '') ?: localized($form['title'] ?? '')) . '</q>';
            $phase = $_GET['phase'] ?? $status;
        }


        // Withdrawals keep their separate reason form. Draft is reserved for
        // future internal approval; new proposals still start in proposed.
        if ($phase !== 'withdrawn') {
            $phaseError = null;
            try {
                $phaseDefinition = ProjectForms::phase($project_type, $phase);
            } catch (InvalidArgumentException $error) {
                $phaseError = lang('Unknown project phase.', 'Unbekannte Projektphase.');
            }
            if (!empty($phaseDefinition['planned']) && $status !== $phase) {
                $phaseError = lang('This phase is being prepared and is not yet available.', 'Diese Phase wird vorbereitet und ist noch nicht verfügbar.');
            }
            if (isset($phaseError)) {
                echo '<div class="alert danger" role="alert">' . e($phaseError) . '</div></div>';
                return;
            }
        }
        $fields = $phase === 'withdrawn' ? [] : ProjectForms::fields($project_type, $phase, $Project->FIELDS);
        $fields = array_values(array_filter($fields, fn($field) => ($field['id'] ?? '') !== 'tags' || $Settings->hasPermission('projects.tags')));
        $moduleForm = $form;
        $moduleForm['status'] = $phase;
        foreach ($fields as $field) {
            if (($field['type'] ?? 'field') === 'field' && in_array($field['id'] ?? '', ['name', 'title', 'abstract'], true)) {
                $moduleForm[$field['id']] = localized($form[$field['id']] ?? '', 'en');
            }
        }
        if ($is_subproject && $new_project) $moduleForm['persons'] = $parent['persons'] ?? [];
        $Modules = new Modules($moduleForm, false);
        $Modules->setFields($fields);
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
                    <textarea name="values[withdrawn_reason]" id="withdrawn_reason" class="form-control" rows="4"><?= e($form['withdrawn_reason'] ?? '') ?></textarea>
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


            <div id="data-modules" class="row row-eq-spacing">
                <?php $Modules->render_form($fields); ?>
            </div>

            <button class="btn secondary" type="button" id="submit-btn">
                <i class="ph ph-check"></i> <?= lang('action.save') ?>
            </button>

            <script>
                const form = document.getElementById('proposal-form');
                document.getElementById('submit-btn').addEventListener('click', () => {
                    if (form.reportValidity()) form.requestSubmit();
                });
            </script>
        </form>

    <?php } ?>
</div>
