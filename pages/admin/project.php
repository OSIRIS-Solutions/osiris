<?php

/**
 * Admin page for project settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
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

    .kdsf-icon {
        width: 1.4em;
        height: 1.4em;
        margin-left: 0.5rem;
        vertical-align: middle;
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
            <h5 class="title"><?= lang('ID must be unique', 'Die ID muss einzigartig sein.') ?></h5>
            <p>
                <?= lang('Each project type must have a unique ID with which it is linked to an activity.', 'Jeder Projekttyp muss eine einzigartige ID haben, mit der er zu einer Aktivität verknüpft wird.') ?>
            </p>
            <p>
                <?= lang('As the ID must be unique, the following previously used IDs and keywords (new) cannot be used as IDs:', 'Da die ID einzigartig sein muss, können folgende bereits verwendete IDs und Schlüsselwörter (new) nicht als ID verwendet werden:') ?>
            </p>
            <ul class="list" id="IDLIST">
                <?php foreach ($osiris->adminProjects->distinct('id') as $k) { ?>
                    <li><?= $k ?></li>
                <?php } ?>
                <li>new</li>
            </ul>
            <div class="text-right mt-20">
                <a href="#/" class="btn secondary" role="button"><?= lang('I understand', 'Ich verstehe') ?></a>
            </div>
        </div>
    </div>
</div>

<h1>
    <?= lang('Project Settings', 'Projekt-Einstellungen') ?>
    >
    <span class="text-primary">
        <?php if ($stage == '1') { ?>
            <?= lang('General', 'Allgemein') ?>
        <?php } else if ($stage == '2') { ?>
            <?= lang('Phases', 'Phasen') ?>
        <?php } else if ($stage == '3') { ?>
            <?= lang('Subprojects', 'Teilprojekte') ?>
        <?php } else { ?>
            <?= lang('New', 'Neu') ?>
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
                    <?= lang('General settings', 'Allgemeine Einstellungen') ?>
                </h2>

                <div class="row row-eq-spacing">
                    <div class="col-sm">
                        <label for="id" class="required">ID</label>
                        <input type="text" class="form-control" name="values[id]" required value="<?= $type == 'new' ? '' : $type ?>" data-value="<?= $type == 'new' ? '' : $type ?>" oninput="sanitizeID(this)">
                        <small><a href="#unique"><i class="ph ph-info"></i> <?= lang('Must be unqiue', 'Muss einzigartig sein') ?></a></small>
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
                        <label for="color" class="required "><?= lang('Color', 'Farbe') ?></label>
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
                    <label for="disable"><?= lang('Deactivate', 'Deaktivieren') ?></label>
                </div>
                <span class="text-muted">
                    <?= lang('Deactivated projects are retained for past activities, but no new ones can be added.', 'Deaktivierte Projektkategorien bleiben erhalten für vergangene Aktivitäten, es können aber keine neuen hinzugefügt werden.') ?>
                </span>

            </div>
            <hr>
            <div class="content">
                <h5>
                    <?= lang('Subprojects', 'Teilprojekte') ?>
                </h5>
                <div class="custom-checkbox my-10">
                    <input type="hidden" name="values[subprojects]" value="false">
                    <input type="checkbox" id="subprojects" value="true" name="values[subprojects]" <?= ($project['subprojects'] ?? false) ? 'checked' : '' ?>>
                    <label for="subprojects">
                        <?= lang('This type of project can have subprojects.', 'Diese Art von Projekt kann Teilprojekte haben.') ?>
                    </label>
                </div>
                <span class="text-muted">
                    <?= lang('Subprojects are projects that are linked to a main project and are displayed in the project overview.', 'Teilprojekte sind Projekte, die mit einem Hauptprojekt verknüpft sind und in der Projektübersicht angezeigt werden.') ?>
                </span>
            </div>
            <hr>
            <div class="content">

                <h5>
                    <?= lang('Proposals', 'Anträge') ?>
                </h5>

                <p class="text-muted">
                    <?= lang(
                        'A project can either be created directly or go through an submission phase first. Depending on the type of project, it makes sense to only include new projects as proposals or to omit the proposal phase completely. Financial data is not available for projects.',
                        'Ein Projekt kann entweder direkt angelegt werden oder durchläuft zuerst eine Antragsphase. Je nach Art des Projekts ist es sinnvoll, neue Projekte nur als Antrag aufzunehmen oder die Anträge komplett wegzulassen. Finanzdaten sind allerdings für Projekte nicht verfügbar.'
                    ) ?>
                </p>


                <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="values[process]" id="proposal" value="proposal" required <?= $process == 'proposal' ? 'checked' : '' ?>>
                        <label for="proposal">
                            <?= lang('All projects of this type must first be created as proposal', 'Alle Projekte dieser Art müssen zuerst als Antrag angelegt werden') ?>
                        </label>
                    </div>
                </div>

                <!-- <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="values[process]" id="both" value="both" required <?= $process == 'proposal' ? 'checked' : '' ?>>
                        <label for="both">
                            <?= lang('All projects of this type can be created directly or as proposal', 'Alle Projekte dieser Art können entweder direkt oder als Antrag angelegt werden') ?>
                        </label>
                    </div>
                </div> -->

                <div class="form-group">
                    <div class="custom-radio">
                        <input type="radio" name="values[process]" id="project" value="project" required <?= $process == 'project' ? 'checked' : '' ?>>
                        <label for="project">
                            <?= lang('All projects of this type can be created directly, no proposals possible', 'Alle Projekte dieser Art werden direkt angelegt, keine Anträge möglich') ?>
                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="content">
                <h5>
                    <?= lang('Notifications', 'Benachrichtigungen') ?>
                </h5>

                <?= lang('Select role or user that should be notified when new proposals/projects of this type are <b>created</b>.', 'Wähle die Rolle oder den Benutzer, der benachrichtigt werden soll, wenn neue Anträge/Projekte dieses Typs <b>erstellt</b> werden.') ?>
                <div class="form-group">
                    <?php
                    $notification = $project['notification_created'] ?? '';
                    ?>

                    <select name="values[notification_created]" id="notification" class="form-control">
                        <option value="" <?= empty($notification) ? 'selected' : '' ?>><?= lang('None', 'Keine') ?></option>
                        <option value="" disabled>--- <?= lang('Roles', 'Rollen') ?> ---</option>
                        <?php
                        foreach ($Settings->get('roles') as $role) { ?>
                            <option value="role:<?= $role ?>" <?= $notification  == ('role:' . $role) ? 'selected' : '' ?>><?= strtoupper($role) ?></option>
                        <?php } ?>
                        <option value="" disabled>--- <?= lang('User', 'Nutzende') ?> ---</option>
                        <?php foreach ($osiris->persons->find([], ['sort' => ['last' => 1]]) as $u) { ?>
                            <option value="user:<?= $u['username'] ?>" <?= $notification  == ('user:' . $u['username']) ? 'selected' : '' ?>><?= $u['last'] ?>, <?= $u['first'] ?></option>
                        <?php } ?>
                    </select>
                    <div class="custom-checkbox mt-10">
                        <input type="hidden" name="values[notification_created_email]" value="0">
                        <input type="checkbox" id="notification_created_email" value="1" name="values[notification_created_email]" <?= ($project['notification_created_email'] ?? false) ? 'checked' : '' ?>>
                        <label for="notification_created_email"><?= lang('Per Mail', 'Via Email') ?>*</label>
                    </div>
                </div>
                <hr>

                <?= lang('Select role or user that should be notified when proposals/projects of this type are <b>changed</b>.', 'Wähle die Rolle oder den Benutzer, der benachrichtigt werden soll, wenn Anträge/Projekte dieses Typs <b>bearbeitet</b> werden.') ?>
                <div class="form-group">
                    <?php
                    $notification = $project['notification_changed'] ?? '';
                    ?>

                    <select name="values[notification_changed]" id="notification" class="form-control">
                        <option value="" <?= empty($notification) ? 'selected' : '' ?>><?= lang('None', 'Keine') ?></option>
                        <?php
                        foreach ($Settings->get('roles') as $role) { ?>
                            <option value="role:<?= $role ?>" <?= $notification == ('role:' . $role) ? 'selected' : '' ?>><?= strtoupper($role) ?></option>
                        <?php } ?>
                        <option value="" disabled>--- <?= lang('User', 'Nutzende') ?> ---</option>
                        <?php foreach ($osiris->persons->find([], ['sort' => ['last' => 1]]) as $u) { ?>
                            <option value="user:<?= $u['username'] ?>" <?= $notification == ('user:' . $u['username']) ? 'selected' : '' ?>><?= $u['last'] ?>, <?= $u['first'] ?></option>
                        <?php } ?>
                    </select>
                    <div class="custom-checkbox mt-10">
                        <input type="hidden" name="values[notification_changed_email]" value="0">
                        <input type="checkbox" id="notification_changed_email" value="1" name="values[notification_changed_email]" <?= ($project['notification_changed_email'] ?? false) ? 'checked' : '' ?>>
                        <label for="notification_changed_email"><?= lang('Per Mail', 'Via Email') ?>*</label>
                    </div>
                </div>

                <p>
                    * <?= lang('Before enabling emails here, please make sure that email settings are correctly set up and working in the general settings. If not, it may lead to problems.', 'Bevor du hier E-Mails aktivierst, stelle bitte sicher, dass die E-Mail-Einstellungen in den allgemeinen Einstellungen korrekt eingerichtet und funktionsfähig sind. Andernfalls kann es zu Problemen kommen.') ?>
                </p>
            </div>

        </div>


        <button type="submit" class="btn success">
            <?= lang('Next', 'Weiter') ?>
            <i class="ph ph-arrow-fat-line-right"></i>
        </button>

        <?php if ($stage <= $finished_stages) { ?>
            <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
                <?= lang('Skip', 'Überspringen') ?>
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
                    <!-- <b><?= lang('Data fields for', 'Datenfelder für') ?></b> -->
                    <code class="code float-right text-<?= $phase['color'] ?? 'muted' ?>"><?= $phase_id ?></code>
                    <h2 class="title">
                        <div class="badge <?= $phase['color'] ?? 'muted' ?>"><?= lang($phase['name'], $phase['name_de']) ?></div>
                    </h2>

                    <p>
                        <?= lang($phase['description'], $phase['description_de']) ?>
                    </p>

                    <p>
                        <b>
                            <?= lang('Required fields', 'Pflichtfelder') ?>
                        </b>
                        <br>
                        <span class="text-muted"><?= lang('These fields are always required and cannot be deactivated.', 'Diese Felder sind immer erforderlich und können nicht deaktiviert werden.') ?></span>
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
                                        <!-- <img src="<?= ROOTPATH ?>/img/kdsf-icon.svg" alt="KDSF" class="kdsf-icon"> -->
                                        KDSF
                                    </small>
                                <?php } ?>
                            </div>
                        <?php } ?>

                    </div>

                    <p>
                        <b>
                            <?= lang('Optional fields', 'Optionale Felder') ?>
                        </b>
                        <br>
                        <span class="text-muted">
                            <?= lang('You can mark a field as active by clicking on it and mark it as required by clicking again. Required fields are then marked in red with an asterisk (*).', 'Du kannst ein Feld als aktiv markieren, indem du darauf klickst, und es als erforderlich markieren, indem du erneut darauf klickst. Erforderliche Felder sind dann mit einem Sternchen (*) in rot gekennzeichnet.') ?>
                        </span>
                    </p>
                    <?php if ($phase_id == 'project' && $Settings->featureEnabled('portal')) { ?>
                        <p>
                            <b class="text-danger"><i class="ph ph-globe"></i> Portfolio</b>:
                            <?= lang('If you want this type of project to be visible to the public via Portfolio or the Portfolio API, you must activate the "Consent to public presentation" field. If the corresponding check mark is set in the form, the project becomes publicly visible.', 'Wenn du möchtest, dass diese Art von Projekt für die Öffentlichkeit über Portfolio oder die Portfolio-API sichtbar ist, musst du das Feld "Zustimmung zu Öffentlichen Präsentation" aktivieren. Wenn der entsprechende Haken im Formular gesetzt wird, wird das Projekt öffentlich sichtbar.') ?>
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
                            <b>" . lang('Custom Fields', 'Benutzerdefinierte Felder') . "</b>
                            <br>
                            <span class='text-muted'>" . lang('These fields are created by you and can be used for any purpose.', 'Diese Felder wurden von dir und können für beliebige Zwecke verwendet werden.') . "</span>
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
                                        <!-- <img src="<?= ROOTPATH ?>/img/kdsf-icon.svg" alt="KDSF" class="kdsf-icon"> -->
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
            <?= lang('Back without saving', 'Zurück ohne zu speichern') ?>
            <i class="ph ph-arrow-fat-line-left"></i>
        </a>
        <!-- <button type="submit" class="btn success">
            <?= lang('Next', 'Weiter') ?>
            <i class="ph ph-arrow-fat-line-right"></i>
        </button> -->
        <button type="submit" class="btn success" id="submitBtn"><?= lang('Save', 'Speichern') ?></button>

    <?php } ?>

    <!-- 
         -->
    <!-- 
    <?php if ($stage <= $finished_stages) { ?>
        <a href="<?= ROOTPATH ?>/admin/projects/<?= $stage + 1 ?>/<?= $id ?>" class="btn link">
            <?= lang('Skip', 'Überspringen') ?>
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
                <button class="btn danger"><i class="ph ph-trash"></i> <?= lang('Delete', 'Löschen') ?></button>
                <span class="ml-20"><?= lang('Warning! Cannot be undone.', 'Warnung, kann nicht rückgängig gemacht werden!') ?></span>
            </form>
        </div>
    <?php } else { ?>
        <div class="alert danger mt-20">
            <?= lang("Can't delete project type: $member proposals and/or projects associated.", "Kann Typ nicht löschen: $member Anträge und/oder Projekte zugeordnet.") ?><br>
            <a href='<?= ROOTPATH ?>/projects/search#{"$and":[{"type":"<?= $type ?>"}]}' target="_blank" class="text-danger">
                <i class="ph ph-search"></i>
                <?= lang('View projects', 'Projekte zeigen') ?>
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