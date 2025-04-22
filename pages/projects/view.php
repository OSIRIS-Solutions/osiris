<?php

/**
 * Page to see details on a single project
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /project/<id>
 *
 * @package     OSIRIS
 * @since       1.2.2
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

require_once BASEPATH . "/php/Project.php";
$Project = new Project($project);

$type = $project['type'] ?? 'Drittmittel';

$user_project = false;
$user_role = null;
$persons = $project['persons'] ?? array();
foreach ($persons as $p) {
    if (strval($p['user']) == $_SESSION['username']) {
        $user_project = True;
        $user_role = $p['role'];
        break;
    }
}
$edit_perm = ($project['created_by'] == $_SESSION['username'] || $Settings->hasPermission('projects.edit') || ($Settings->hasPermission('projects.edit-own') && $user_project));

$N = $osiris->activities->count(['projects' => $project['name']]);

$institute = $Settings->get('affiliation_details');

if (empty($institute) || !isset($institute['lat']) || empty($institute['lat'])) {
    $institute = [
        'lat' => 52,
        'lng' => 10
    ];
}

?>
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'success') { ?>

    <!-- add another one -->
    <div class="alert success">
        <?= lang('The project has been updated successfully.', 'Das Projekt wurde erfolgreich aktualisiert.') ?>
        <a href="<?= ROOTPATH ?>/projects/new">+ <?= lang('Add another one', 'Füge noch eines hinzu') ?></a>
    </div>

<?php } ?>


<script>
    const PROJECT = '<?= $project['name'] ?>';
    const CURRENT_USER = '<?= $_SESSION['username'] ?>';
    const EDIT_PERM = <?= $edit_perm ? 'true' : 'false' ?>;
    var layout = {
        mapbox: {
            style: "open-street-map",
            center: {
                lat: <?= $institute['lat'] ?? 52 ?>,
                lon: <?= $institute['lng'] ?? 10 ?>
            },
            zoom: 1
        },

        margin: {
            r: 0,
            t: 0,
            b: 0,
            l: 0
        },
        hoverinfo: 'text',
        // autosize:true
    };
</script>

<script src="<?= ROOTPATH ?>/js/plotly-2.27.1.min.js" charset="utf-8"></script>
<script src="<?= ROOTPATH ?>/js/projects.js?v=<?= CSS_JS_VERSION ?>"></script>

<style>
    td .key {
        display: block;
        color: var(--muted-color);
        font-size: 1.2rem;
    }
</style>


<?php if ($Settings->featureEnabled('portal') && $project['public']) { ?>
    <a class="btn float-right" href="<?= ROOTPATH ?>/preview/project/<?= $id ?>">
        <i class="ph ph-eye ph-fw"></i>
        <?= lang('Preview', 'Vorschau') ?>
    </a>
<?php } ?>

<div class="title mb-20">
    <h1>

        <?= $project['name'] ?>
    </h1>

    <h2 class="subtitle">
        <?= $project['title'] ?>
    </h2>


    <!-- show research topics -->
    <?= $Settings->printTopics($project['topics'] ?? [], 'mb-20', true) ?>

    <div class="d-flex">

        <div class="mr-10 badge bg-white">
            <small><?= lang('Type of Projects', 'Art des Projekts') ?>: </small>
            <br />
            <?= $Project->getType() ?>
        </div>
        <div class="mr-10 badge bg-white">
            <small><?= lang('Current Status', 'Aktueller Status') ?>: </small>
            <br />
            <?= $Project->getStatus() ?>
        </div>

        <div class="mr-10 badge bg-white">
            <small><?= lang('Time frame', 'Zeitraum') ?>: </small>
            <br />
            <b><?= $Project->getDateRange() ?></b>
        </div>

        <div class="mr-10 badge bg-white">
            <small><?= lang('Duration', 'Dauer') ?>: </small>
            <br />
            <b><?= $Project->getDuration() ?> <?= lang('Month', 'Monate') ?></b>
        </div>

        <?php if ($Settings->featureEnabled('portal')) { ?>
            <?php if ($project['public']) { ?>
                <div class="mr-10 badge success" data-toggle="tooltip" data-title="<?= lang('The approved project is shown in OSIRIS Portfolio.', 'Das bewilligte Projekt wird in OSIRIS Portfolio gezeigt.') ?>">
                    <small><?= lang('Visibility', 'Sichtbarkeit') ?>: </small>
                    <br />
                    <i class="ph ph-globe m-0"></i> <?= lang('Shown', 'Gezeigt') ?>
                </div>
            <?php } else { ?>
                <div class="mr-10 badge danger" data-toggle="tooltip" data-title="<?= lang('This project is not shown in OSIRIS Portfolio.', 'Dieses Projekt wird nicht in OSIRIS Portfolio gezeigt.') ?>">
                    <small><?= lang('Visibility', 'Sichtbarkeit') ?>: </small>
                    <br />
                    <i class="ph ph-globe-x m-0"></i>
                    <?= lang('Not shown', 'Nicht gezeigt') ?>
                </div>
            <?php } ?>
        <?php } ?>
    </div>


    <!-- TAB AREA -->

    <nav class="pills mt-20 mb-0">
        <a onclick="navigate('general')" id="btn-general" class="btn active">
            <i class="ph ph-info" aria-hidden="true"></i>
            <?= lang('General', 'Allgemein') ?>
        </a>


        <?php if ($type == 'Teilprojekt') {
            // collaborators are inherited from parent project
        } elseif (count($project['collaborators'] ?? []) > 0) { ?>
            <a onclick="navigate('collabs')" id="btn-collabs" class="btn">
                <i class="ph ph-handshake" aria-hidden="true"></i>
                <?= lang('Collaborators', 'Kooperationspartner') ?>
                <span class="index"><?= count($project['collaborators'] ?? array()) ?></span>
            </a>
        <?php } else { ?>
            <a href="<?= ROOTPATH ?>/projects/collaborators/<?= $id ?>" id="btn-collabs" class="btn">
                <i class="ph ph-plus-circle" aria-hidden="true"></i>
                <?= lang('Collaborators', 'Kooperationspartner') ?>
            </a>
        <?php } ?>
        <?php if ($N > 0) { ?>
            <a onclick="navigate('activities')" id="btn-activities" class="btn">
                <i class="ph ph-suitcase" aria-hidden="true"></i>
                <?= lang('Activities', 'Aktivitäten') ?>
                <span class="index"><?= $N ?></span>
            </a>
        <?php } elseif ($edit_perm) { ?>
            <a id="btn-activities" class="btn" href="#add-activity">
                <i class="ph ph-plus-circle" aria-hidden="true"></i>
                <?= lang('Connect Activities', 'Aktivitäten verknüpfen') ?>
            </a>
        <?php } else { ?>
            <a id="btn-activities" class="btn disabled">
                <i class="ph ph-suitcase" aria-hidden="true"></i>
                <?= lang('Activities', 'Aktivitäten') ?>
                <span class="index">0</span>
            </a>
        <?php } ?>

        <!-- Public representation -->
        <a onclick="navigate('public')" id="btn-public" class="btn">
            <i class="ph ph-globe" aria-hidden="true"></i>
            <?= lang('Public representation', 'Öffentliche Darstellung') ?>
        </a>


        <?php if ($Settings->hasPermission('project.finance.see') || in_array($user_role, ['PI', 'applicant'])) { ?>
            <!-- PI and applicant can see -->
            <a onclick="navigate('finance')" id="btn-finance" class="btn">
                <i class="ph ph-money" aria-hidden="true"></i>
                <?= lang('Ressources', 'Ressourcen')  ?>
            </a>
        <?php } ?>

        <?php if ($Settings->hasPermission('raw-data') || isset($_GET['verbose'])) { ?>
            <a onclick="navigate('raw')" id="btn-raw" class="btn">
                <i class="ph ph-code" aria-hidden="true"></i>
                <?= lang('Raw data', 'Rohdaten')  ?>
            </a>
        <?php } ?>

    </nav>


    <section id="general">
        <div class="row row-eq-spacing mt-0">

            <div class="col-md-6">
                <h2>
                    <?= lang('Project details', 'Projektdetails') ?>
                </h2>

                <div class="btn-toolbar mb-10">

                    <?php if ($edit_perm) { ?>
                        <a href="<?= ROOTPATH ?>/projects/edit/<?= $id ?>" class="btn primary">
                            <i class="ph ph-edit"></i>
                            <?= lang('Edit', 'Bearbeiten') ?>
                        </a>
                        <a href="<?= ROOTPATH ?>/projects/public/<?= $id ?>" class="btn primary">
                            <i class="ph ph-edit"></i>
                            <?= lang('Public', 'Öffentlich') ?>
                        </a>
                        <!-- dropdown -->
                        <div class="dropdown">
                            <button class="btn primary" data-toggle="dropdown" type="button" id="dropdown-download" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-download"></i>
                                <?= lang('Download', 'Herunterladen') ?>
                                <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu p-10" aria-labelledby="dropdown-download">
                                <form action="<?= ROOTPATH ?>/projects/download/<?= $id ?>" method="post">
                                    <select name="format" id="download-format" class="form-control mb-10">
                                        <option value="docx">Word</option>
                                        <option value="json">JSON</option>
                                        <!-- <option value="csv">CSV</option> -->
                                    </select>
                                    <button class="btn primary" type="submit">
                                        <i class="ph ph-download"></i>
                                        <?= lang('Download', 'Herunterladen') ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($Settings->hasPermission('projects.delete') || ($Settings->hasPermission('projects.delete-own') && $edit_perm)) { ?>

                        <div class="dropdown">
                            <button class="btn danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                                <i class="ph ph-trash"></i>
                                <?= lang('Delete', 'Löschen') ?>
                                <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdown-1">
                                <div class="content">
                                    <b class="text-danger"><?= lang('Attention', 'Achtung') ?>!</b><br>
                                    <small>
                                        <?= lang(
                                            'The project is permanently deleted and the connection to all associated persons and activities is also removed. This cannot be undone.',
                                            'Das Projekt wird permanent gelöscht und auch die Verbindung zu allen zugehörigen Personen und Aktivitäten entfernt. Dies kann nicht rückgängig gemacht werden.'
                                        ) ?>
                                    </small>
                                    <form action="<?= ROOTPATH ?>/crud/projects/delete/<?= $project['_id'] ?>" method="post">
                                        <button class="btn btn-block danger" type="submit"><?= lang('Delete permanently', 'Permanent löschen') ?></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                </div>

                <table class="table">

                    <?php
                    $fields = $Project->getFieldsLegacy($project['type'] ?? 'Drittmittel');
                    // dump($fields);
                    $inherited = [];

                    if ($type == 'Teilprojekt') { #
                        $inherited = Project::INHERITANCE;
                        $fields = array_merge($fields, $inherited);
                    }
                    ?>

                    <tbody>
                        <?php if (!empty($project['parent'])) {
                            $Parentproject = new Project();
                        ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Parent project', 'Übergeordnetes Projekt') ?></span>
                                    <?php

                                    $parent = $osiris->projects->findOne(['name' => $project['parent']]);
                                    $Parentproject->setProject($parent);
                                    echo $Parentproject->widgetLarge();
                                    ?>

                                </td>
                            </tr>
                        <?php } ?>

                        <!-- Subprojects -->
                        <?php if (!isset($project['parent']) && $type == 'Drittmittel') { ?>
                            <tr>
                                <td>
                                    <span class="key">
                                        <?= lang('Subprojects', 'Teilprojekte') ?>
                                    </span>
                                    <?php if (count($project['subprojects'] ?? []) > 0) {
                                        $Subproject = new Project();
                                        foreach ($project['subprojects'] as $sub) {
                                            $sub = $osiris->projects->findOne(['name' => $sub]);
                                            $Subproject->setProject($sub);
                                            echo $Subproject->widgetSubproject();
                                        }
                                    } ?>
                                    <a href="<?= ROOTPATH ?>/projects/subproject/<?= $id ?>" id="btn-collabs" class="btn">
                                        <i class="ph ph-plus-circle" aria-hidden="true"></i>
                                        <?= lang('Add Subproject', 'Teilprojekt anlegen') ?>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php foreach ($fields as $key) {
                            if (!in_array($key, ['name', 'title', 'type', 'internal_number', 'contact', 'status', 'funder', 'funding_organization', 'funding_number', 'scholarship', 'university', 'purpose', 'role', 'coordinator', 'start', 'end', 'website', 'abstract', 'nagoya', 'countries'])) {
                                continue;
                            }
                            if ($key == 'nagoya' && !$Settings->featureEnabled('nagoya')) {
                                continue;
                            }
                        ?>
                            <tr>
                                <td>
                                    <?php if (in_array($key, $inherited)) { ?>
                                        <small class="badge muted float-right">Inherited</small>
                                    <?php } ?>

                                    <?php
                                    switch ($key) {
                                        case 'name': ?>
                                            <span class="key"><?= lang('Short title', 'Kurztitel') ?></span>
                                            <?= $project['name'] ?? '-' ?>
                                        <?php break;
                                        case 'title': ?>
                                            <span class="key"><?= lang('Full title of the project', 'Voller Titel des Projekts') ?></span>
                                            <?= $project['title'] ?? '-' ?>
                                        <?php break;
                                        case 'type': ?>
                                            <span class="key"><?= lang('Type of the project', 'Projekt-Typ') ?></span>
                                            <?= $project['type'] ?? '-' ?>
                                        <?php break;
                                        case 'internal_number': ?>
                                            <span class="key"><?= lang('Kostenträger') ?></span>
                                            <?= $project['internal_number'] ?? '-' ?>
                                        <?php break;
                                        case 'contact': ?>
                                            <span class="key"><?= lang('Applicant', 'Antragsteller:in') ?></span>
                                            <a href="<?= ROOTPATH ?>/profile/<?= $project['contact'] ?? '' ?>"><?= $DB->getNameFromId($project['contact'] ?? '') ?></a>
                                        <?php break;
                                        case 'status': ?>
                                            <span class="key"><?= lang('Status', 'Status') ?></span>
                                            <?= $Project->getStatus() ?>
                                        <?php break;
                                        case 'funder': ?>
                                            <span class="key"><?= lang('Third-party funder', 'Drittmittelgeber') ?></span>
                                            <?= $Project->getFunder() ?>
                                        <?php break;
                                        case 'funding_organization': ?>
                                            <span class="key"><?= lang('Funding organization', 'Förderorganisation') ?></span>
                                            <?= $project['funding_organization'] ?? '-' ?>
                                        <?php break;
                                        case 'funding_number': ?>
                                            <span class="key"><?= lang('Funding reference number(s)', 'Förderkennzeichen') ?></span>
                                            <?= $Project->getFundingNumbers('<br>') ?>
                                        <?php break;
                                        case 'scholarship': ?>
                                            <span class="key"><?= lang('Scholarship institution', 'Stipendiengeber') ?></span>
                                            <?= $project['scholarship'] ?? '-' ?>
                                        <?php break;
                                        case 'university': ?>
                                            <span class="key"><?= lang('Partner University', 'Partner-Universität') ?></span>
                                            <?= $project['university'] ?? '-' ?>
                                        <?php break;
                                        case 'purpose': ?>
                                            <span class="key"><?= lang('Purpose of the project', 'Zwecks des Projekts') ?></span>
                                            <?= $Project->getPurpose() ?>
                                        <?php break;
                                        case 'role': ?>
                                            <span class="key"><?= lang('Role of', 'Rolle von') ?> <?= $Settings->get('affiliation') ?></span>
                                            <?= $Project->getRole() ?>
                                        <?php break;
                                        case 'coordinator': ?>
                                            <span class="key"><?= lang('Coordinator facility', 'Koordinator-Einrichtung') ?></span>
                                            <?= $project['coordinator'] ?? '-' ?>
                                        <?php break;
                                        case 'start': ?>
                                            <span class="key">Projektbeginn</span>
                                            <?= Document::format_date($project['start'] ?? null) ?>
                                        <?php break;
                                        case 'end': ?>
                                            <span class="key">Projektende</span>
                                            <?= Document::format_date($project['end'] ?? null) ?>
                                        <?php break;
                                        case 'nagoya':
                                            $n = $project['nagoya'] ?? 'no';
                                        ?>
                                            <span class="key"><?= lang('Nagoya Protocol Compliance') ?></span>
                                            <?php if ($n == 'no') { ?>
                                                <span class="badge"><?= lang('Not relevant', 'Nicht relevant') ?></span>
                                            <?php } else { ?>
                                                <!-- <span class="badge danger"><?= lang('Relevant') ?></span>
                                                <br> -->
                                                <div class="alert signal">
                                                    <h6 class="title"><?= lang('Countries', 'Länder:') ?></h6>
                                                    <ul class="list signal mb-0">
                                                        <?php $lang = lang('name', 'name_de');
                                                        foreach ($project['nagoya_countries'] ?? [] as $c) { ?>
                                                            <li><?= $DB->getCountry($c, $lang) ?></li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            <?php } ?>

                                        <?php break;
                                        case 'countries': ?>
                                            <span class="key"><?= lang('Countries', 'Länder') ?></span>
                                            <ul class="list signal mb-0">
                                                <?php $lang = lang('name', 'name_de');
                                                foreach ($project['countries'] ?? [] as $c) {
                                                    $iso = $c;
                                                    $role = '';
                                                    if (isset($c['country'])) {
                                                        $iso = $c['country'];
                                                    }
                                                    if (isset($c['role'])) {
                                                        $role = ' (' . $c['role'] . ')';
                                                    }
                                                ?>
                                                    <li><?= $DB->getCountry($c, $lang) . $role ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php break;
                                        case 'website': ?>
                                            <span class="key"><?= lang('Project website', 'Webseite des Projekts') ?></span>
                                            <a href="<?= $project['website'] ?? '' ?>" target="_blank" rel="noopener noreferrer"> <?= $project['website'] ?? '-' ?></a>
                                        <?php break;
                                        case 'abstract': ?>
                                            <span class="key"><?= lang('Abstract', 'Kurzbeschreibung') ?></span>
                                            <?= $project['abstract'] ?? '-' ?>
                                    <?php break;
                                    }
                                    ?>

                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <span class="key">Zustimmung zur Internetpräsentation des bewilligten Vorhaben</span>
                                <?= bool_icon($project['public'] ?? false) ?>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <span class="key"><?= lang('Created by', 'Erstellt von') ?></span>
                                <?php if (!isset($project['created_by']) || $project['created_by'] == 'system') {
                                    echo 'System';
                                } else {
                                    echo $DB->getNameFromId($project['created_by']);
                                }
                                echo " (" . $project['created'] . ")";
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>

            <div class="col-md-6">

                <h2>
                    <?= lang('Project members', 'Projektmitarbeiter') ?> @
                    <?= $Settings->get('affiliation') ?>
                </h2>

                <?php if ($edit_perm) { ?>
                    <div class="modal" id="persons" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                                <h5 class="modal-title">
                                    <?= lang('Connect persons', 'Personen verknüpfen') ?>
                                </h5>
                                <div>
                                    <form action="<?= ROOTPATH ?>/crud/projects/update-persons/<?= $id ?>" method="post">

                                        <table class="table simple">
                                            <thead>
                                                <tr>
                                                    <th><?= lang('Project-ID', 'Projekt-ID') ?></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody id="project-list">
                                                <?php
                                                $persons = $project['persons'] ?? array();
                                                if (empty($persons)) {
                                                    $persons = [
                                                        ['user' => '', 'role' => '']
                                                    ];
                                                }
                                                $all_users = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['last' => 1]]);
                                                foreach ($persons as $i => $con) { ?>
                                                    <tr>
                                                        <td class="">
                                                            <select name="persons[<?= $i ?>][user]" id="persons-<?= $i ?>" class="form-control">
                                                                <?php
                                                                foreach ($all_users as $s) { ?>
                                                                    <option value="<?= $s['username'] ?>" <?= ($con['user'] == $s['username'] ? 'selected' : '') ?>>
                                                                        <?= "$s[last], $s[first] ($s[username])" ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="persons[<?= $i ?>][role]" id="persons-<?= $i ?>" class="form-control">
                                                                <?php if ($project['type'] == 'Stipendium') { ?>
                                                                    <option value="scholar" <?= $con['role'] == 'scholar' ? 'selected' : '' ?>><?= Project::personRole('scholar') ?></option>
                                                                    <option value="supervisor" <?= $con['role'] == 'supervisor' ? 'selected' : '' ?>><?= Project::personRole('supervisor') ?></option>
                                                                <?php } else { ?>
                                                                    <option value="applicant" <?= $con['role'] == 'applicant' ? 'selected' : '' ?>><?= Project::personRole('applicant') ?></option>
                                                                    <option value="PI" <?= $con['role'] == 'PI' ? 'selected' : '' ?>><?= Project::personRole('PI') ?></option>
                                                                    <option value="worker" <?= $con['role'] == 'worker' ? 'selected' : '' ?>><?= Project::personRole('worker') ?></option>
                                                                    <option value="coordinator" <?= $con['role'] == 'coordinator' ? 'selected' : '' ?>><?= Project::personRole('coordinator') ?></option>
                                                                <?php } ?>
                                                                <option value="associate" <?= $con['role'] == 'associate' ? 'selected' : '' ?>><?= Project::personRole('associate') ?></option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr id="last-row">
                                                    <td colspan="2">
                                                        <button class="btn" type="button" onclick="addProjectRow()"><i class="ph ph-plus"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                                                    </td>
                                                </tr>
                                            </tfoot>

                                        </table>

                                        <button class="btn primary mt-20">
                                            <i class="ph ph-check"></i>
                                            <?= lang('Submit', 'Bestätigen') ?>
                                        </button>
                                    </form>

                                    <script>
                                        var counter = <?= $i ?? 0 ?>;
                                        const tr = $('#project-list tr').first()

                                        function addProjectRow() {
                                            counter++;
                                            const row = tr.clone()
                                            row.find('select').first().attr('name', 'persons[' + counter + '][user]');
                                            row.find('select').last().attr('name', 'persons[' + counter + '][role]');
                                            $('#project-list').append(row)
                                        }
                                    </script>

                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>

                <div class="btn-toolbar mb-10">
                    <?php if ($edit_perm) { ?>
                        <a href="#persons" class="btn primary">
                            <i class="ph ph-edit"></i>
                            <?= lang('Edit', 'Bearbeiten') ?>
                        </a>
                    <?php } ?>
                </div>

                <table class="table">
                    <tbody>
                        <?php
                        if (empty($project['persons'] ?? array())) {
                        ?>
                            <tr>
                                <td>
                                    <?= lang('No persons connected.', 'Keine Personen verknüpft.') ?>
                                </td>
                            </tr>
                        <?php
                        } else foreach ($project['persons'] as $person) {
                            $username = strval($person['user']);

                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">

                                        <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                                        <div class="">
                                            <h5 class="my-0">
                                                <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="colorless">
                                                    <?= $person['name'] ?>
                                                </a>
                                            </h5>
                                            <?= Project::personRole($person['role']) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        } ?>

                    </tbody>
                </table>

                <h3>
                    <?= lang('Associated units', 'Zugehörige Organisationseinheiten') ?>
                </h3>
                <table class="table">
                    <tbody>
                        <?php
                        $units = $project['units'] ?? [];
                        // $tree =  $Groups->getPersonHierarchyTree($units);
                        if (!empty($units)) {
                            $hierarchy = $Groups->getPersonHierarchyTree($units);
                            $tree = $Groups->readableHierarchy($hierarchy);

                            foreach ($tree as $row) { ?>
                                <tr>
                                    <td style="padding-left: <?= ($row['indent'] * 2 + 2) . 'rem' ?>;">
                                        <a href="<?= ROOTPATH ?>/groups/view/<?= $row['id'] ?>">
                                            <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                        </a>
                                    </td>
                                </tr>
                        <?php }
                        }
                        ?>
                    </tbody>
                </table>


            </div>
        </div>

    </section>

    <section id="collabs" style="display:none">

        <h2>
            <?= lang('Collaborators', 'Kooperationspartner') ?>
        </h2>

        <?php if ($edit_perm) { ?>
            <div class="btn-toolbar mb-10">
                <a href="<?= ROOTPATH ?>/projects/collaborators/<?= $id ?>" class="btn primary">
                    <i class="ph ph-edit"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
                <?php if (empty($project['collaborators'] ?? array())) { ?>
                    <script>
                        collabChart = true;
                    </script>
                <?php } ?>

            </div>
        <?php } ?>

        <div class="row row-eq-spacing">
            <div class="col-lg-4">

                <table class="table">
                    <tbody>
                        <?php
                        if (empty($project['collaborators'] ?? array())) {
                        ?>
                            <tr>
                                <td>
                                    <?= lang('No collaborators connected.', 'Keine Partner verknüpft.') ?>
                                </td>
                            </tr>
                        <?php
                        } else foreach ($project['collaborators'] as $collab) {
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">

                                        <span data-toggle="tooltip" data-title="<?= $collab['type'] ?>" class="badge mr-10">
                                            <?= Project::getCollaboratorIcon($collab['type'], 'ph-fw ph-2x m-0') ?>
                                        </span>
                                        <div class="">
                                            <h5 class="my-0">
                                                <?= $collab['name'] ?>
                                            </h5>
                                            <?= $collab['location'] ?>
                                            <a href="<?= $collab['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php
                        } ?>

                    </tbody>
                </table>

                <div class="alert primary my-20">

                    <small class="text-muted float-right">
                        <?= lang('Based on partners', 'Basierend auf Partnern') ?>
                    </small>

                    <h5 class="title mb-0">
                        Scope
                    </h5>
                    <?php
                    $scope = $Project->getScope();

                    echo  $scope['scope'] . ' (' . $scope['region'] . ')';
                    ?>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="box my-0">
                    <div id="map" class=""></div>
                </div>
                <p>
                    <i class="ph ph-fill ph-circle" style="color:#f78104"></i>
                    <?= lang('Coordinator', 'Koordinator') ?>
                    <br>
                    <i class="ph ph-fill ph-circle" style="color:#008083"></i>
                    Partner
                </p>
            </div>
        </div>

        <script>
            const id = '<?= $_GET['project'] ?? null ?>';
            console.log(id);
        </script>


    </section>

    <section id="activities" style="display:none">

        <h2>
            <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
            (<?= $N ?>)
        </h2>

        <div class="btn-toolbar mb-10">
            <?php if ($edit_perm) { ?>
                <a href="#add-activity" class="btn primary">
                    <i class="ph ph-plus"></i>
                    <?= lang('Connect activities', 'Aktivitäten verknüpfen') ?>
                </a>
            <?php } ?>


            <div class="dropdown with-arrow btn-group ">
                <button class="btn primary" <?= $N == 0 ? 'disabled' : '' ?> data-toggle="dropdown" type="button" id="download-btn" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-download"></i> Download
                    <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="download-btn">
                    <div class="content">
                        <form action="<?= ROOTPATH ?>/download" method="post">

                            <input type="hidden" name="filter[project]" value="<?= $project['name'] ?>">

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
                            <button class="btn primary">Download</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="mt-20 w-full">
            <table class="table dataTable responsive" id="activities-table">
                <thead>
                    <tr>
                        <th><?= lang('Type', 'Typ') ?></th>
                        <th><?= lang('Activity', 'Aktivität') ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                </tbody>

            </table>
        </div>

    </section>

    <!-- Public representation -->
    <section id="public" style="display:none">

        <h2>
            <?= lang('Public representation', 'Öffentliche Darstellung') ?>
        </h2>

        <div class="btn-toolbar mb-10">
            <?php if ($edit_perm) { ?>
                <a href="<?= ROOTPATH ?>/projects/public/<?= $id ?>" class="btn primary">
                    <i class="ph ph-edit"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
            <?php } ?>
        </div>

        <table class="table">
            <tbody>
                <tr>
                    <td>
                        <?php if ($project['public']) { ?>
                            <a class="badge success" href="<?= PORTALPATH ?>/project/<?= $project['_id'] ?>">
                                <?= lang('Publicly shown', 'Öffentlich gezeigt') ?>
                            </a>
                        <?php } else { ?>
                            <span class="badge danger">
                                <?= lang('Not publicly shown', 'Nicht öffentlich gezeigt') ?>
                            </span>
                        <?php } ?>

                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Public title', 'Öffentlicher Titel') ?></span>
                        <?= lang($project['public_title'] ?? $project['name'] ?? '-', $project['public_title_de'] ?? null) ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Public subtitle', 'Öffentlicher Untertitel') ?></span>
                        <?= lang($project['public_subtitle'] ?? $project['title'] ?? '-', $project['public_subtitle_de'] ?? null) ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Public abstract', 'Öffentliche Kurzbeschreibung') ?></span>
                        <div class="abstract">
                            <?php if (lang('en', 'de') == 'de' && isset($project['public_abstract_de'])) { ?>
                                <?= $project['public_abstract_de'] ?>
                            <?php } else if (isset($project['public_abstract'])) { ?>
                                <?= $project['public_abstract'] ?>
                            <?php } else { ?>
                                <?= $project['abstract'] ?? '-' ?>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Public website', 'Öffentliche Webseite') ?></span>
                        <a href="<?= $project['website'] ?? '' ?>" target="_blank" rel="noopener noreferrer"> <?= $project['website'] ?? '-' ?></a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key"><?= lang('Public image', 'Öffentliches Bild') ?></span>

                        <?php if (!empty($project['public_image']) ?? '') { ?>
                            <img src="<?= ROOTPATH . '/uploads/' . $project['public_image'] ?>" alt="<?= $project['public_title'] ?>" class="img-fluid">
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>
                </tr>
            </tbody>
        </table>

    </section>



    <?php if ($Settings->hasPermission('project.finance.see') || in_array($user_role, ['PI', 'applicant'])) { ?>
        <section id="finance" style="display: none;">

            <h2 class="title">
                <?= lang('Finance data', 'Finanzen') ?>
            </h2>

            <div class="btn-toolbar mb-10">
                <?php if ($Settings->hasPermission('project.finance.edit')) { ?>
                    <a href="<?= ROOTPATH ?>/projects/finance/<?= $id ?>" class="btn primary">
                        <i class="ph ph-edit"></i>
                        <?= lang('Edit', 'Bearbeiten') ?>
                    </a>
                <?php } ?>
            </div>

            <table class="table">
                <!-- "grant_sum_proposed": 1000000, -->
                <tr>
                    <td>
                        <span class="key"><?= lang('Kostenträger') ?></span>
                        <?= $project['internal_number'] ?? '-' ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <span class="key">grant_sum_proposed</span>
                        <?= $project['grant_sum_proposed'] ?? '-' ?>
                    </td>
                </tr>

                <!-- "grant_income_proposed": 360000, -->
                <tr>
                    <td>
                        <span class="key">grant_income_proposed</span>
                        <?= $project['grant_income_proposed'] ?? '-' ?>
                    </td>
                </tr>

                <!-- "grant_sum": 1000000, -->
                <tr>
                    <td>
                        <span class="key">grant_sum</span>
                        <?= $project['grant_sum'] ?? '-' ?>
                    </td>
                </tr>

                <!-- "grant_income": 360000, -->
                <tr>
                    <td>
                        <span class="key">grant_income</span>
                        <?= $project['grant_income'] ?? '-' ?>
                    </td>
                </tr>

            </table>

            <!-- "ressources" -->
            <h3 class="title">
                <?= lang('Ressources', 'Ressourcen') ?>
            </h3>
            <table class="table">
                <tbody>
                    <?php
                    $res = $project['ressources'];
                    foreach (
                        [
                            'material' => lang('Material', 'Material'),
                            'personnel' => lang('Personnel', 'Personal'),
                            'room' => lang('Room', 'Raum'),
                            'other' => lang('Other', 'Sonstiges')
                        ] as $r => $h
                    ) { ?>
                        <tr>
                            <td>
                                <span class="key"><?= $h ?></span>
                                <?php if (($res[$r] ?? 'no') == 'yes') { ?>
                                    <span class="badge success">
                                        <?= lang('Yes', 'Ja') ?>
                                    </span>
                                    <br>
                                    <?= $res[$r . '_details'] ?>
                                <?php } else { ?>
                                    <span class="badge danger"><?= lang('No', 'Nein') ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>


            <!-- applied personnel and in-kind -->
            <h3 class="title">
                <?= lang('Applied personnel and in-kind', 'Angewandtes Personal und Sachmittel') ?>
            </h3>
            <table class="table">
                <tbody>
                    <tr>
                        <td>
                            <span class="key">
                                <?= lang('Personnel measures planned', 'Geplante Personalmaßnahmen') ?>
                            </span>
                            <?= $project['personnel'] ?? '-' ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class="key">
                                <?= lang('In-kind personnel', 'Umfang des geplanten eigenen Personaleinsatzes') ?>
                            </span>
                            <?= $project['in-kind'] ?? '-' ?>
                        </td>
                    </tr>
                </tbody>
            </table>

        </section>
    <?php } ?>



    <section id="raw" style="display:none">

        <h2 class="title">
            <?= lang('Raw data', 'Rohdaten') ?>
        </h2>

        <?= lang('Raw data as they are stored in the database.', 'Die Rohdaten, wie sie in der Datenbank gespeichert werden.') ?>

        <div class="box overflow-x-scroll">
            <?php
            dump($project, true);
            ?>
        </div>

    </section>



    <!-- Modal for cennecting activities -->


    <?php if ($edit_perm) { ?>
        <div class="modal" id="add-activity" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close" href="#close-modal">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="modal-title">
                        <?= lang('Connect activities', 'Aktivitäten verknüpfen') ?>
                    </h5>

                    <form action="<?= ROOTPATH ?>/crud/projects/connect-activities" method="post" class="">

                        <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                        <input type="hidden" name="project" value="<?= $project['name'] ?>">

                        <!-- input field with suggesting activities -->
                        <div class="form-group" id="activity-suggest">
                            <!-- <label for="activity-suggested"><?= lang('Activity', 'Aktivität') ?></label> -->
                            <input type="text" name="activity-suggested" id="activity-suggested" class="form-control" required placeholder="...">
                            <div class="suggestions on-focus">
                                <div class="content"><?= lang('Start typing to search for activities', 'Beginne zu tippen, um Aktivitäten zu suchen') ?></div>
                            </div>
                        </div>
                        <input type="hidden" name="activity" id="activity-selected" required value="">

                        <button class="btn primary">
                            <i class="ph ph-check"></i>
                            <?= lang('Submit', 'Bestätigen') ?>
                        </button>
                    </form>

                    <style>
                        .suggestions {
                            color: #464646;
                            /* position: absolute; */
                            margin: 10px auto;
                            top: 100%;
                            left: 0;
                            height: 19.2rem;
                            overflow: auto;
                            bottom: -3px;
                            width: 100%;
                            box-sizing: border-box;
                            min-width: 12rem;
                            background-color: white;
                            border: var(--border-width) solid #afafaf;
                            /* visibility: hidden; */
                            /* opacity: 0; */
                            z-index: 100;
                            -webkit-transition: opacity 0.4s linear;
                            transition: opacity 0.4s linear;
                        }

                        .suggestions a {
                            display: block;
                            padding: 0.5rem;
                            border-bottom: var(--border-width) solid #afafaf;
                            color: #464646;
                            text-decoration: none;
                            width: 100%;
                        }

                        .suggestions a:hover {
                            background-color: #f0f0f0;
                        }
                    </style>

                    <!-- script to handle auto suggest by ajax -->
                    <script>
                        $('#activity-suggested').on('input', function() {
                            // prevent enter from submitting form
                            $(this).closest('form').on('keypress', function(event) {
                                if (event.keyCode == 13) {
                                    event.preventDefault();
                                }
                            })
                            const val = $(this).val();
                            if (val.length < 3) return;
                            $.get('<?= ROOTPATH ?>/api/activities-suggest/' + val + '?exclude-project=' + PROJECT, function(data) {
                                $('#activity-suggest .suggestions').empty();
                                console.log(data);
                                data.data.forEach(function(d) {
                                    $('#activity-suggest .suggestions').append(
                                        `<a onclick="selectActivity(this)" data-id="${d.id.toString()}">${d.details.icon} ${d.details.plain}</a>`
                                    )
                                })
                                $('#activity-suggest .suggestions a')
                                    .on('click', function(event) {
                                        event.preventDefault();
                                        console.log(this);
                                        $('#activity-suggested').val($(this).text());
                                        $('#activity-selected').val($(this).data('id'));
                                        $('#activity-suggest .suggestions').empty();
                                    })
                                // $('#activity-suggest .suggest').html(data);
                            })
                        })
                    </script>
                </div>
            </div>
        </div>
    <?php } ?>


</div>