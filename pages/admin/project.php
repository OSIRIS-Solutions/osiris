<?php

/**
 * Admin page for project settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

if (empty($project)) {
    $route = ROOTPATH . '/crud/admin/projects/create';
} else {
    $route = ROOTPATH . '/crud/admin/projects/update/' . $project['_id'];
}

$finished_stages = 0;
if (isset($project['stage'])) {
    $finished_stages = $project['stage'];
}

$Project = new Project();

if (!isset($stage)) {
    $stage = '1';
}
$redirect = ROOTPATH . '/admin/projects/' . $stage . '/' . $type;
if ($stage == '2') {
    $redirect = ROOTPATH . '/admin/projects';
}

$process = $project['process'] ?? '';
$phases = [
    [
        'id' => 'proposed',
        'name' => 'Proposed',
        'name_de' => 'Beantragt',
        'color' => 'signal',
        'description' => 'If a new project proposal is entered, it is created in this phase.',
        'description_de' => 'Wird ein neues Projekt beantragt, so wird es in dieser Phase angelegt.',

    ],
    [
        'id' => 'approved',
        'name' => 'Approved',
        'name_de' => 'Bewilligt',
        'color' => 'success',
        'description' => 'If a project proposal is approved, it is moved to this phase.',
        'description_de' => 'Wird ein Projektantrag bewilligt, so wird es in diese Phase verschoben.',
    ],
    [
        'id' => 'rejected',
        'name' => 'Rejected',
        'name_de' => 'Abgelehnt',
        'color' => 'danger',
        'description' => 'If a project proposal is rejected, it is moved to this phase.',
        'description_de' => 'Wird ein Projektantrag abgelehnt, so wird es in diese Phase verschoben.',

    ]
];
if ($process == 'project') {
    // only projects
    $phases = [
        [
            'id' => 'project',
            'name' => 'Project',
            'name_de' => 'Projekt',
            'color' => 'primary',
            'description' => 'Here you can add data fields for projects of this type. Projects can be created directly.',
            'description_de' => 'Hier kannst du die Datenfelder für dein Projekt anlegen. Projekte können direkt angelegt werden.',
        ]
    ];
} elseif ($process == 'proposal') {
    // only proposals
    $phases[] = [
        'id' => 'project',
        'name' => 'Project',
        'name_de' => 'Projekt',
        'color' => 'primary',
        'description' => 'Once the proposal has been approved, a project can be created from the proposal. It is linked to the application and takes over data fields that have already been filled out in the proposal. You can define all data fields for this project here.',
        'description_de' => 'Nachdem der Antrag bewilligt wurde, kann aus dem Antrag heraus ein Projekt erstellt werden. Es ist mit dem Antrag verknüpft und übernimmt Datenfelder, die bereits im Antrag ausgefüllt wurden. Hier kannst du alle Datenfelder für dieses Projekt definieren.',
    ];
}

?>

<?php include_once BASEPATH . '/header-editor.php'; ?>
<script src="<?= ROOTPATH ?>/js/admin-categories.js?v=1"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>

<style>
    .required-badge {
        margin-bottom: 0.5rem;
        background: white;
        border: var(--border-width) solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 0.25rem 0.5rem;
        background-color: var(--danger-color-20);
        display: inline-block;
    }

    .checkbox-badge.custom-checkbox label:after {
        content: "\E182";
        position: absolute;
        display: none;
        left: 0.7rem;
        top: 0.5rem;
        width: 0.6rem;
        height: 1rem;
        border: unset;
        border-width: unset;
        -webkit-transform: unset;
        -ms-transform: unset;
        transform: unset;
        font-family: 'Phosphor';
        font-weight: bold;
        color: white;
    }

    .checkbox-badge.custom-checkbox input[type=checkbox]:checked~label:before {
        background-color: var(--signal-color);
        border-color: var(--signal-color);
    }

    .checkbox-badge input[type=checkbox]:checked~label {
        background-color: var(--signal-color-20);
    }


    .checkbox-badge.custom-checkbox.required-state label:after {
        content: '\E0AA';
    }

    .checkbox-badge.required-state input[type=checkbox]:checked~label {
        background-color: var(--danger-color-20);
    }

    .checkbox-badge.custom-checkbox.required-state input[type=checkbox]:checked~label:before {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }

    .kdsf {
        font-weight: bold;
        font-size: x-small;
        margin-left: 0.5rem;
        vertical-align: middle;

    }
</style>


<div class="modal" id="unique" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a href="#/" class="close" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <h5 class="title"><?= lang('common.id_must_be_unique') ?></h5>
            <p>
                <?= lang('admin.each_project_type_must_have_a_unique_id_with_which_it_is_linked_to_an_activ') ?>
            </p>
            <p>
                <?= lang('common.as_the_id_must_be_unique_the_following_previously_used_ids_and_keywords_new') ?>
            </p>
            <ul class="list" id="IDLIST">
                <?php foreach ($osiris->adminProjects->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('common.understand') ?></a>
            </div>
        </div>
    </div>
</div>

<h1>
    <?= lang('common.project_settings') ?>
    >
    <span class="text-primary">
        <?php if ($stage == '1') { ?>
            <?= lang('common.general') ?>
        <?php } else if ($stage == '2') { ?>
            <?= lang('admin.phases') ?>
        <?php } else if ($stage == '3') { ?>
            <?= lang('common.subprojects') ?>
        <?php } else { ?>
            <?= lang('common.new') ?>
        <?php } ?>
    </span>
</h1>

<form action="<?= $route ?>" method="post" id="project-form">
    <input type="hidden" class="hidden" name="redirect" value="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>">
    <input type="hidden" class="hidden" name="stage" value="<?= $stage ?>">


    <?php if ($stage == '1') {
        /**
         * First stage of this form: general settings
         */
        if (isset($type) && $type != 'new') { ?>
            <input type="hidden" name="original_id" value="<?= $type ?>">
        <?php }
        ?>
        <div class="box">
            <div class="content">
                <h2>
                    <?= lang('common.general_settings') ?>
                </h2>

                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="id" class="required">ID</label>
                        <input type="text" class="form-control" name="values[id]" required value="<?= $type == 'new' ? '' : $type ?>" data-value="<?= $type == 'new' ? '' : $type ?>" oninput="sanitizeID(this)">
                        <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('common.must_be_unqiue') ?></a></small>
                    </div>
                    <div class="col-sm">
                        <label for="icon" class="required element-time"><a href="https://phosphoricons.com/" class="link" target="_blank" rel="noopener noreferrer">Icon</a> </label>

                        <div class="input-group">
                            <input type="text" class="form-control" name="values[icon]" required value="<?= $project['icon'] ?? 'folder-open' ?>" onchange="iconTest(this.value)">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="ph ph-<?= $project['icon'] ?? 'folder-open' ?>" id="test-icon"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm">
                        <label for="color" class="required "><?= lang('common.color') ?></label>
                        <input type="color" class="form-control" name="values[color]" required value="<?= $project['color'] ?? '' ?>">
                    </div>
                </div>


                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="name" class="required ">Name (en)</label>
                        <input type="text" class="form-control" name="values[name]" required value="<?= $project['name'] ?? '' ?>">
                    </div>
                    <div class="col-sm">
                        <label for="name_de" class="">Name (de)</label>
                        <input type="text" class="form-control" name="values[name_de]" value="<?= $project['name_de'] ?? '' ?>">
                    </div>
                </div>

                <hr>

                <div class="custom-checkbox mb-10 danger">
                    <input type="checkbox" id="disable" value="true" name="values[disabled]" <?= ($project['disabled'] ?? false) ? 'checked' : '' ?>>
                    <label for="disable"><?= lang('common.deactivate') ?></label>
                </div>
                <span class="text-muted">
                    <?= lang('admin.deactivated_projects_are_retained_for_past_activities_but_no_new_ones_can_b') ?>
                </span>

            </div>
            <hr>
            <div class="content">
                <h5>
                    <?= lang('common.subprojects') ?>
                </h5>
                <div class="custom-checkbox my-10">
                    <input type="hidden" name="values[subprojects]" value="false">
                    <input type="checkbox" id="subprojects" value="true" name="values[subprojects]" <?= ($project['subprojects'] ?? false) ? 'checked' : '' ?>>
                    <label for="subprojects">
                        <?= lang('admin.this_type_of_project_can_have_subprojects') ?>
                    </label>
                </div>
                <span class="text-muted">
                    <?= lang('admin.subprojects_are_projects_that_are_linked_to_a_main_project_and_are_displaye') ?>
                </span>
            </div>
            <hr>
            <div class="content">

                <h5>
                    <?= lang('common.proposals') ?>
                </h5>

                <p class="text-muted">
                    <?= lang('admin.a_project_can_either_be_created_directly_or_go_through_an_submission_phase') ?>
                </p>


                <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="values[process]" id="proposal" value="proposal" required <?= $process == 'proposal' ? 'checked' : '' ?>>
                        <label for="proposal">
                            <?= lang('admin.all_projects_of_this_type_must_first_be_created_as_proposal') ?>
                        </label>
                    </div>
                </div>

                <!-- <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="values[process]" id="both" value="both" required <?= $process == 'proposal' ? 'checked' : '' ?>>
                        <label for="both">
                            <?= lang('admin.all_projects_of_this_type_can_be_created_directly_or_as_proposal') ?>
                        </label>
                    </div>
                </div> -->

                <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="values[process]" id="project" value="project" required <?= $process == 'project' ? 'checked' : '' ?>>
                        <label for="project">
                            <?= lang('admin.all_projects_of_this_type_can_be_created_directly_no_proposals_possible') ?>
                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="content">
                <h5>
                    <?= lang('admin.notifications') ?>
                </h5>

                <?= lang('admin.select_role_or_user_that_should_be_notified_when_new_proposals_projects_of') ?>
                <div class="form-group">
                    <?php
                    $notification = $project['notification_created'] ?? '';
                    ?>

                    <select name="values[notification_created]" id="notification" class="form-control">
                        <option value="" <?= empty($notification) ? 'selected' : '' ?>><?= lang('common.none') ?></option>
                        <option value="" disabled>--- <?= lang('common.roles') ?> ---</option>
                        <?php
                        foreach ($Settings->get('roles') as $role) { ?>
                            <option value="role:<?= $role ?>" <?= $notification  == ('role:' . $role) ? 'selected' : '' ?>><?= strtoupper($role) ?></option>
                        <?php } ?>
                        <option value="" disabled>--- <?= lang('admin.user') ?> ---</option>
                        <?php foreach ($osiris->persons->find([], ['sort' => ['last' => 1]]) as $u) { ?>
                            <option value="user:<?= $u['username'] ?>" <?= $notification  == ('user:' . $u['username']) ? 'selected' : '' ?>><?= $u['last'] ?>, <?= $u['first'] ?></option>
                        <?php } ?>
                    </select>
                    <div class="custom-checkbox mt-10">
                        <input type="hidden" name="values[notification_created_email]" value="0">
                        <input type="checkbox" id="notification_created_email" value="1" name="values[notification_created_email]" <?= ($project['notification_created_email'] ?? false) ? 'checked' : '' ?>>
                        <label for="notification_created_email"><?= lang('admin.per_mail') ?>*</label>
                    </div>
                </div>
                <hr>

                <?= lang('admin.select_role_or_user_that_should_be_notified_when_proposals_projects_of_this') ?>
                <div class="form-group">
                    <?php
                    $notification = $project['notification_changed'] ?? '';
                    ?>

                    <select name="values[notification_changed]" id="notification" class="form-control">
                        <option value="" <?= empty($notification) ? 'selected' : '' ?>><?= lang('common.none') ?></option>
                        <?php
                        foreach ($Settings->get('roles') as $role) { ?>
                            <option value="role:<?= $role ?>" <?= $notification == ('role:' . $role) ? 'selected' : '' ?>><?= strtoupper($role) ?></option>
                        <?php } ?>
                        <option value="" disabled>--- <?= lang('admin.user') ?> ---</option>
                        <?php foreach ($osiris->persons->find([], ['sort' => ['last' => 1]]) as $u) { ?>
                            <option value="user:<?= $u['username'] ?>" <?= $notification == ('user:' . $u['username']) ? 'selected' : '' ?>><?= $u['last'] ?>, <?= $u['first'] ?></option>
                        <?php } ?>
                    </select>
                    <div class="custom-checkbox mt-10">
                        <input type="hidden" name="values[notification_changed_email]" value="0">
                        <input type="checkbox" id="notification_changed_email" value="1" name="values[notification_changed_email]" <?= ($project['notification_changed_email'] ?? false) ? 'checked' : '' ?>>
                        <label for="notification_changed_email"><?= lang('admin.per_mail') ?>*</label>
                    </div>
                </div>

                <p>
                    * <?= lang('admin.before_enabling_emails_here_please_make_sure_that_email_settings_are_correc') ?>
                </p>
            </div>

        </div>


        <button type="submit" class="btn success">
            <?= lang('navigation.next') ?>
            <i class="ph ph-arrow-fat-line-right"></i>
        </button>

        <?php if ($stage <= $finished_stages) { ?>
            <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
                <?= lang('admin.skip') ?>
            </a>
        <?php } ?>


    <?php } else if ($stage == '2') {
        /**
         * Second stage of this form: phase data fields
         * 
         * If process is "project", skip this step
         */
    ?>

        <?php
        foreach ($phases as $phase) {
            $phase_id = $phase['id'];
            if (isset($project['phases']))
                foreach ($project['phases'] as $p) {
                    if ($p['id'] == $phase_id) {
                        $phase['modules'] = $p['modules'] ?? [];
                    }
                }
        ?>
            <div class="box phase" id="phase-<?= $phase_id ?>" data-id="<?= $phase_id ?>">
                <div class="content">
                    <!-- <b><?= lang('admin.data_fields_for') ?></b> -->
                    <code class="code float-right text-<?= $phase['color'] ?? 'muted' ?>"><?= $phase_id ?></code>
                    <h2 class="title">
                        <div class="badge <?= $phase['color'] ?? 'muted' ?>"><?= lang($phase['name'], $phase['name_de']) ?></div>
                    </h2>

                    <p>
                        <?= lang($phase['description'], $phase['description_de']) ?>
                    </p>

                    <p>
                        <b>
                            <?= lang('admin.required_fields') ?>
                        </b>
                        <br>
                        <span class="text-muted"><?= lang('admin.these_fields_are_always_required_and_cannot_be_deactivated') ?></span>
                    </p>

                    <div>

                        <?php
                        // get required fields
                        $fields = $Project->FIELDS;
                        $fields = array_filter(array_values($fields), function ($field) use ($phase_id) {
                            return array_key_exists($phase_id, $field['scope']);
                        });
                        $required_fields = array_filter($fields, function ($field) use ($phase_id) {
                            return $field['scope'][$phase_id] ?? false;
                        });
                        $optional_fields = array_filter($fields, function ($field) use ($phase_id) {
                            return !($field['scope'][$phase_id] ?? false);
                        });


                        foreach ($required_fields as $field) {
                            $kdsf = $field['kdsf'] ?? false;
                            if (empty($field)) $field = ['en' => $m, 'de' => null];
                        ?>
                            <div class="required-badge">
                                <i class="ph ph-asterisk text-danger"></i>
                                <?= lang($field['en'], $field['de']) ?>
                                <?php if ($kdsf) { ?>
                                    <small class="kdsf" data-toggle="tooltip" data-title="<?= $kdsf ?>">
                                        KDSF
                                    </small>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    </div>

                    <p>
                        <b>
                            <?= lang('admin.optional_fields') ?>
                        </b>
                        <br>
                        <span class="text-muted">
                            <?= lang('admin.you_can_mark_a_field_as_active_by_clicking_on_it_and_mark_it_as_required_by') ?>
                        </span>
                    </p>
                    <?php if ($phase_id == 'project' && $Settings->featureEnabled('portal')) { ?>
                        <p>
                            <b class="text-danger"><i class="ph ph-globe"></i> Portfolio</b>:
                            <?= lang('admin.if_you_want_this_type_of_project_to_be_visible_to_the_public_via_portfolio') ?>
                        </p>
                    <?php } else { ?>
                        <style>
                            .ph.ph-globe.portfolio {
                                display: none;
                            }
                        </style>
                    <?php } ?>


                    <?php
                    $modules = DB::doc2Arr($phase['modules'] ?? []);
                    $modules = array_column($modules, 'required', 'module');
                    $custom = false;
                    foreach ($optional_fields as $field) {
                        $kdsf = $field['kdsf'] ?? false;
                        $m = $field['id'];
                        // if ($m['required'] ?? false) continue;
                        $active = array_key_exists($m, $modules);
                        $required = $active && $modules[$m];
                        $value = $m . ($required ? '*' : '');
                        // $field = $Project->FIELDS[$m] ?? null;
                        if (empty($field)) $field = ['en' => $m, 'de' => null];
                        if (($field['custom'] ?? false) && !$custom) {
                            echo "<p>
                            <b>" . lang('common.custom_fields') . "</b>
                            <br>
                            <span class='text-muted'>" . lang('admin.these_fields_are_created_by_you_and_can_be_used_for_any_purpose') . "</span>
                            </p>";
                            $custom = true;
                        }
                    ?>
                        <div class="custom-checkbox checkbox-badge <?= $required ? 'required-state' : '' ?>">
                            <input type="checkbox"
                                id="module-<?= $phase_id ?>-<?= $m ?>"
                                data-attribute="<?= $m ?>"
                                value="<?= $value ?>"
                                name="phase[<?= $phase_id ?>][modules][]"
                                <?= $active ? 'checked' : '' ?>
                                onclick="toggleCheckboxStates(this)">
                            <label for="module-<?= $phase_id ?>-<?= $m ?>">
                                <?= lang($field['en'], $field['de']) ?>
                                <?php if ($kdsf) { ?>
                                    <small class="kdsf" data-toggle="tooltip" data-title="<?= $kdsf ?>">
                                        KDSF
                                    </small>
                                <?php } ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <a class="btn" href="<?= ROOTPATH ?>/admin/projects/1/<?= $type ?>">
            <?= lang('admin.back_without_saving') ?>
            <i class="ph ph-arrow-fat-line-left"></i>
        </a>
        <!-- <button type="submit" class="btn success">
            <?= lang('navigation.next') ?>
            <i class="ph ph-arrow-fat-line-right"></i>
        </button> -->
        <button type="submit" class="btn success" id="submitBtn"><?= lang('action.save') ?></button>

    <?php } ?>

    <!-- 
         -->
    <!-- 
    <?php if ($stage <= $finished_stages) { ?>
        <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
            <?= lang('admin.skip') ?>
        </a>
    <?php } ?> -->


    <script>
        function toggleCheckboxStates(el) {
            // if is checked but not .required-state, check still and set required state
            // else just uncheck and remove required state
            let parent = el.closest('.checkbox-badge');
            let val = el.getAttribute('data-attribute');
            console.log(el.checked);
            if (!el.checked && !parent.classList.contains('required-state')) {
                parent.classList.add('required-state');
                el.checked = true;
                el.value = val + '*';
            } else if (el.checked) {
                // parent.classList.add('required-state');
                el.checked = true;
                el.value = val;
            } else {
                parent.classList.remove('required-state');
                el.checked = false;
                el.value = val;
            }
        }
    </script>

</form>


<?php if ($stage == '1' && !empty($project)) {
    $member = $osiris->projects->count(['type' => $type]);
    $member += $osiris->proposals->count(['type' => $type]);
    if ($member == 0) { ?>
        <div class="alert danger mt-20">
            <form action="<?= ROOTPATH ?>/crud/admin/projects/delete/<?= $project['_id'] ?>" method="post">
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('action.delete') ?></button>
                <span class="ml-20"><?= lang('common.warning_cannot_be_undone') ?></span>
            </form>
        </div>
    <?php } else { ?>
        <div class="alert danger mt-20">
            <?= lang('admin.can_t_delete_project_type_member_proposals_and_or_projects_associated', replace: ['member' => $member]) ?><br>
            <a href='<?= ROOTPATH ?>/projects/search#{"$and":[{"type":"<?= $type ?>"}]}' target="_blank" class="text-danger">
                <i class="ph ph-search"></i>
                <?= lang('admin.view_projects') ?>
            </a>
        </div>
    <?php } ?>

<?php } ?>


<?php
// create time stamp
// $timestamp = time();
// dump($timestamp);
// $date = date('Y-m-d H:i:s', $timestamp);
// dump($date);
?>

<?php if (isset($_GET['verbose'])) {
    dump($project);
} ?>