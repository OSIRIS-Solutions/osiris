<?php
require_once BASEPATH . "/php/Project.php";
$Project = new Project($project);


$status = $project['status'] ?? 'proposed';
$type = $project['type'] ?? 'third-party';

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
if ($user_project == false && $project['created_by'] == $_SESSION['username']) {
    $user_project = True;
}
$edit_perm = ($Settings->hasPermission('proposals.edit') || ($Settings->hasPermission('proposals.edit-own') && $user_project));
$status_perm = ($Settings->hasPermission('proposals.edit') || ($Settings->hasPermission('proposals.status-own') && $user_project));

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();
?>


<style>
    .badge.status {
        font-size: 2.2rem;
        text-align: center;
        font-weight: bold;
        border-radius: 1rem;
        padding: .5rem 1.5rem;
    }

    .badge.status.success {
        border: 1px solid var(--success-color);
    }

    .badge.status.signal {
        border: 1px solid var(--signal-color);
    }

    .badge.status.danger {
        border: 1px solid var(--danger-color);
    }
</style>


<div class="d-flex align-items-center justify-content-between">
    <div class="title">
        <b class="badge text-uppercase primary"><?= lang('Proposal', 'Antrag') ?></b>
        <h1 class="mt-0">
            <?= $project['name'] ?>
        </h1>

        <h2 class="subtitle">
            <?= $project['title'] ?>
        </h2>
    </div>
    <div class="status">

        <?php if ($status_perm) { ?>
            <?php if ($status == 'proposed') { ?>
                <div class="dropdown">
                    <button class="badge status signal text-uppercase cursor-pointer" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                        <i class="ph ph-edit" aria-hidden="true"></i>
                        <?= lang('Proposed', 'Beantragt') ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right w-250" aria-labelledby="dropdown-1">
                        <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>?phase=approved" class="item badge status success mb-5"><?= lang('Approved', 'Bewilligt') ?></a>
                        <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>?phase=rejected" class="item badge status danger"><?= lang('Rejected', 'Abgelehnt') ?></a>
                    </div>
                </div>
            <?php } else if ($status == 'approved') { ?>
                <span class="badge status success">
                    <i class="ph ph-check-circle" aria-hidden="true"></i>
                    <?= lang('Approved', 'Bewilligt') ?>
                </span>
            <?php } else { ?>
                <span class="badge status danger">
                    <i class="ph ph-x-circle" aria-hidden="true"></i>
                    <?= lang('Rejected', 'Abgelehnt') ?>
                </span>
            <?php } ?>


        <?php } else { ?>
            <div class="text-right">

            <?php
            switch ($status) {
                case 'proposed':
                    echo "<span class='badge status signal'>" . lang('Proposed', 'Beantragt') . "</span>";
                    break;
                case 'approved':
                    echo "<span class='badge status success'>" . lang('Approved', 'Bewilligt') . "</span>";
                    break;
                case 'rejected':
                    echo "<span class='badge status danger'>" . lang('Rejected', 'Abgelehnt') . "</span>";
                    break;
                default:
                    break;
            } ?>
            <br>
            <small class="text-muted">
                <?=lang('You don\t have permission<br>to change the status', 'Du hast keine Berechtigung,<br>um den Status zu ändern')?> 
            </small>
            </div>
      <?php  } ?>
    </div>
</div>



<div class="btn-toolbar">
    <?php if ($edit_perm) { ?>
        <?php
        if ($status == 'approved' && (!isset($project['project_id']) || empty($project['project_id']))) {
            // if project is not connected yet
        ?>
            <a href="<?= ROOTPATH ?>/projects/create-from-proposal/<?= $id ?>" class="btn primary">
                <i class="ph ph-plus"></i>
                <?= lang('Convert into project', 'In Projekt umwandeln') ?>
            </a>
        <?php } ?>


        <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>" class="btn primary">
            <i class="ph ph-edit"></i>
            <?= lang('Edit current state', 'Aktuellen Status bearbeiten') ?>
        </a>
        <!-- dropdown -->
        <div class="dropdown">
            <button class="btn primary" data-toggle="dropdown" type="button" id="dropdown-download" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-download"></i>
                <?= lang('Download', 'Herunterladen') ?>
                <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu p-10" aria-labelledby="dropdown-download">
                <form action="<?= ROOTPATH ?>/proposals/download/<?= $id ?>" method="post">
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


    <?php if ($Settings->hasPermission('proposals.delete') || ($Settings->hasPermission('proposals.delete-own') && $edit_perm)) { ?>

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
                    <form action="<?= ROOTPATH ?>/crud/proposals/delete/<?= $project['_id'] ?>" method="post">
                        <button class="btn btn-block danger" type="submit"><?= lang('Delete permanently', 'Permanent löschen') ?></button>
                    </form>
                </div>
            </div>
        </div>
    <?php } ?>
</div>


<section id="general">
    <?php
    $mentioned_fields = [];
    $phases = ['proposed'];
    ?>


    <div class="row row-eq-spacing mt-0">
        <div class="col-md-8">
            <div class="pills my-20" id="status-tabs">
                <button class="btn font-weight-bold active" style="--primary-color: var(--signal-color);--primary-color-20: var(--signal-color-20);" onclick="selectTab('proposal')" id="proposal-btn">
                    <i class="ph ph-file-text"></i>
                    <?= lang('Proposal', 'Antrag') ?>
                </button>
                <?php if ($status == 'approved') { ?>
                    <button class="btn font-weight-bold" style="--primary-color: var(--success-color);--primary-color-20: var(--success-color-20);" onclick="selectTab('approval')" id="approval-btn">
                        <i class="ph ph-check-circle"></i>
                        <?= lang('Approval', 'Bewilligung') ?>
                    </button>
                <?php } ?>
                <?php if ($status == 'rejected') { ?>
                    <button class="btn font-weight-bold" style="--primary-color: var(--danger-color);--primary-color-20: var(--danger-color-20);" onclick="selectTab('rejection')" id="rejection-btn">
                        <i class="ph ph-x-circle"></i>
                        <?= lang('Rejection', 'Ablehnungs') ?>
                    </button>
                <?php } ?>
                <?php if (isset($project['project_id']) && !empty($project['project_id'])) { ?>
                    <a href="<?= ROOTPATH ?>/projects/view/<?= $project['project_id'] ?>" class="btn font-weight-bold">
                        <i class="ph ph-link m-0"></i>
                        <?= lang('Project', 'Projekt') ?>
                    </a>
                <?php } ?>
                <?php if ($Settings->hasPermission('raw-data')) { ?>
                    <button class="btn" style="--primary-color: var(--muted-color);--primary-color-20: var(--muted-color-20);" onclick="selectTab('raw-data')" id="raw-data-btn">
                        <i class="ph ph-code"></i>
                        <?= lang('Raw data', 'Rohdaten') ?>
                    </button>
                <?php } ?>


            </div>

            <table class="table" id="proposal-details">
                <tbody>
                    <?php
                    $fields = $Project->getFields($type, 'proposed');
                    foreach ($fields as $f) {
                        $key = $f['module'];
                        if ($key == 'nagoya' && !$Settings->featureEnabled('nagoya')) {
                            continue;
                        }
                        if ($key == 'status') continue;
                        $mentioned_fields[] = $key;
                    ?>
                        <tr>
                            <td>
                                <?php
                                echo "<span class='key'>" . $Project->printLabel($key) . "</span>";
                                echo $Project->printField($key, $project[$key] ?? null);
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td>
                            <span class="key"><?= lang('Created by', 'Erstellt von') ?></span>
                            <?php if (!isset($project['created_by']) || $project['created_by'] == 'system') {
                                echo 'System';
                            } else {
                                echo $DB->getNameFromId($project['created_by']);
                            }
                            if (isset($project['created'])) {
                                $date = strtotime($project['created']);
                                echo " (" . date('d.m.Y', $date) . ")";
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if ($status == 'approved') { ?>
                <table class="table" id="approval-details" style="display:none;">
                    <tbody>
                        <?php
                        $fields = $Project->getFields($type, 'approved');
                        foreach ($fields as $f) {
                            $key = $f['module'];
                            if ($key == 'nagoya' && !$Settings->featureEnabled('nagoya')) {
                                continue;
                            }
                        ?>
                            <tr>
                                <td>
                                    <?php
                                    echo "<span class='key'>" . $Project->printLabel($key) . "</span>";
                                    echo $Project->printField($key, $project[$key] ?? null);
                                    ?>
                                </td>
                            </tr>
                        <?php } ?> <tr>
                            <td>
                                <span class="key"><?= lang('Updated by', 'Aktualisiert von') ?></span>
                                <?php if (!isset($project['updated_by']) || $project['updated_by'] == 'system') {
                                    echo 'System';
                                } else {
                                    echo $DB->getNameFromId($project['updated_by']);
                                }
                                if (isset($project['updated'])) {
                                    $date = strtotime($project['updated']);
                                    echo " (" . date('d.m.Y', $date) . ")";
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            <?php } ?>
            <?php if ($status == 'rejected') { ?>
                <table class="table" id="rejection-details" style="display:none;">
                    <tbody>
                        <?php
                        $fields = $Project->getFields($type, 'rejected');
                        foreach ($fields as $f) {
                            $key = $f['module'];
                            if ($key == 'nagoya' && !$Settings->featureEnabled('nagoya')) {
                                continue;
                            }
                        ?>
                            <tr>
                                <td>
                                    <?php
                                    echo "<span class='key'>" . $Project->printLabel($key) . "</span>";
                                    echo $Project->printField($key, $project[$key] ?? null);
                                    ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } ?>

            <div id="raw-data-details" style="display:none;">
                <div class="box overflow-x-auto mt-0">
                    <?php
                    dump($project);
                    ?>
                </div>
            </div>

            <script>
                // select tab function
                function selectTab(tab) {
                    $('#proposal-details').hide();
                    $('#approval-details').hide();
                    $('#rejection-details').hide();
                    $('#raw-data-details').hide();
                    $('#' + tab + '-details').show();

                    $('#status-tabs .btn').removeClass('active');
                    $('#' + tab + '-btn').addClass('active');
                }
            </script>

        </div>

        <div class="col-md-4">
            <br>
            <h3>
                <?= lang('Proposal members', 'Beteiligte Personen') ?>

                <?php if ($edit_perm) { ?>
                    <a href="#persons" data-toggle="tooltip" data-title="<?= lang('Edit persons', 'Personen bearbeiten') ?>">
                        <i class="ph ph-edit"></i>
                    </a>
                <?php } ?>
            </h3>

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
                                <form action="<?= ROOTPATH ?>/crud/proposals/update-persons/<?= $id ?>" method="post">

                                    <table class="table simple">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <?= lang('Person', 'Person') ?>
                                                </th>
                                                <th>
                                                    <?= lang('Role', 'Rolle') ?>
                                                </th>
                                                <th>
                                                    <?= lang('Units', 'Einheiten') ?>
                                                </th>
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
                                            $all_users = $osiris->persons->find(['username' => ['$ne' => null]], ['sort' => ['last' => 1]])->toArray();
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
                                                            <?php
                                                            $role = $con['role'] ?? '';
                                                            $vocab = $Vocabulary->getValues('project-person-role');
                                                            foreach ($vocab as $v) { ?>
                                                                <option value="<?= $v['id'] ?>" <?= $role == $v['id'] ? 'selected' : '' ?>><?= lang($v['en'], $v['de'] ?? null) ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                  <td>
                                                  <?php
                                                    $selected = DB::doc2Arr($con['units'] ?? []);
                                                    if (!is_array($selected)) $selected = [];
                                                    $person_units = $osiris->persons->findOne(['username' => $con['user']], ['units' => 1]);
                                                    $person_units = $person_units['units'] ?? [];
                                                    if (empty($person_units)) {
                                                        echo '<small class="text-danger">No units found</small>';
                                                    } else {
                                                        $person_units = array_column(DB::doc2Arr($person_units), 'unit');
                                                    ?>
                                                        <select class="form-control" name="persons[<?= $i ?>][units][]" id="units-<?= $i ?>" multiple style="height: <?= count($person_units) * 2 + 2 ?>rem">
                                                            <?php foreach ($person_units as $unit) { ?>
                                                                <option value="<?= $unit ?>" <?= (in_array($unit, $selected) ? 'selected' : '') ?>><?= $unit ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    <?php } ?>
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
                                        <?= $Project->personRole($person['role']) ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php
                    } ?>

                </tbody>
            </table>

            <h5>
                <?= lang('Associated units', 'Zugehörige Einheiten') ?>
            </h5>
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