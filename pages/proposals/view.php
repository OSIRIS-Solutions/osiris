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
$edit_perm = ($project['created_by'] == $_SESSION['username'] || $Settings->hasPermission('projects.edit') || ($Settings->hasPermission('projects.edit-own') && $user_project));

?>

<b class="badge text-uppercase primary"><?= lang('Proposal', 'Antrag') ?></b>
<h1 class="mt-0">
    <?= $project['name'] ?>
</h1>

<h2 class="subtitle">
    <?= $project['title'] ?>
</h2>

<div class="btn-toolbar">
    <?php if ($edit_perm) { ?>
        <?php if ($status == 'proposed') { ?>
            <div class="dropdown">
                <button class="btn signal font-weight-bold text-uppercase" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                <i class="ph ph-edit" aria-hidden="true"></i>
                    <?= lang('Proposed', 'Beantragt') ?>
                </button>
                <div class="dropdown-menu dropdown-menu-left" aria-labelledby="dropdown-1">
                    <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>?phase=approved" class="item font-size-14 badge success mb-5"><?= lang('Approved', 'Bewilligt') ?></a>
                    <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>?phase=rejected" class="item font-size-14 badge danger"><?= lang('Rejected', 'Abgelehnt') ?></a>
                    <div class="content">
                        <i class="ph ph-warning text-signal"></i>
                        <?= lang('You can no longer change the details of the application once you change the status.', 'Du kannst die Details des Antrages nicht mehr ändern, sobald du den Status änderst.') ?>
                    </div>
                </div>
            </div>
        <?php } else if ($status == 'approved') { ?>
            <span class="badge success border-success font-size-18">
                <i class="ph ph-check-circle" aria-hidden="true"></i>
                <?= lang('Approved', 'Bewilligt') ?>
            </span>

            <?php
            // check if project is available already
            if (!isset($project['project_id']) || empty($project['project_id'])) { ?>
                <a href="<?= ROOTPATH ?>/projects/create-from-proposal/<?= $id ?>" class="btn primary">
                    <i class="ph ph-plus"></i>
                    <?= lang('Create project', 'Projekt erstellen') ?>
                </a>
            <?php } else { ?>
                <a href="<?= ROOTPATH ?>/projects/view/<?= $project['project_id'] ?>" class="btn primary">
                    <i class="ph ph-link m-0"></i>
                    <?= lang('Project', 'Projekt') ?>
                </a>
            <?php } ?>
        <?php } else { ?>
            <span class="badge danger border-danger font-size-18">
                <i class="ph ph-x-circle" aria-hidden="true"></i>
                <?= lang('Rejected', 'Abgelehnt') ?>
            </span>
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
    <?php } else {
        switch ($status) {
            case 'proposed':
                echo "<span class='badge signal'>" . lang('Proposed', 'Beantragt') . "</span>";
                break;
            case 'approved':
                echo "<span class='badge success'>" . lang('Approved', 'Bewilligt') . "</span>";
                break;
            case 'rejected':
                echo "<span class='badge danger'>" . lang('Rejected', 'Abgelehnt') . "</span>";
                break;
            default:
                break;
        }
    } ?>
</div>


<section id="general">
    <div class="row row-eq-spacing mt-0">
        <div class="col-md-6">
            <h2>
                <?= lang('Proposal details', 'Antragdetails') ?>
            </h2>

            <div class="btn-toolbar mb-10">

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

            <table class="table">
                <tbody>
                    <?php
                    foreach ($project as $key => $value) {
                        if (!array_key_exists($key, $Project->FIELDS)) {
                            continue;
                        }
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
                <?= lang('Proposal members', 'Antragsmitarbeiter') ?> @
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
                                <form action="<?= ROOTPATH ?>/crud/proposals/update-persons/<?= $id ?>" method="post">

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
                                                            <option value="applicant" <?= $con['role'] == 'applicant' ? 'selected' : '' ?>><?= Project::personRole('applicant') ?></option>
                                                            <option value="PI" <?= $con['role'] == 'PI' ? 'selected' : '' ?>><?= Project::personRole('PI') ?></option>
                                                            <option value="worker" <?= $con['role'] == 'worker' ? 'selected' : '' ?>><?= Project::personRole('worker') ?></option>
                                                            <option value="coordinator" <?= $con['role'] == 'coordinator' ? 'selected' : '' ?>><?= Project::personRole('coordinator') ?></option>
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

<?php
if (isset($_GET['verbose'])) {
    dump($project);
}
?>