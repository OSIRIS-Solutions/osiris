<?php
require_once BASEPATH . "/php/Project.php";
$Project = new Project($project);
$Project->isProposal = true;

$project = DB::doc2Arr($project);

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

$documents = $osiris->uploads->find(['type' => 'proposals', 'id' => $id])->toArray();

$connected_project = $osiris->projects->findOne(['_id' => DB::to_ObjectID($id)]);

$nagoyaRelevant = ($Settings->featureEnabled('nagoya') && $Project->isNagoyaRelevant());

if ($nagoyaRelevant) {
    require_once BASEPATH . "/php/Nagoya.php";
    $nagoya_status_icon = Nagoya::badge($project, true);
    $nagoya_status_color = Nagoya::statusColor($project['nagoya']['status'] ?? 'unknown');
}
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

    .badge.status.muted {
        border: 1px solid var(--muted-color);
    }
</style>
<script src="<?= ROOTPATH ?>/js/projects.js?v=<?= CSS_JS_VERSION ?>"></script>

<div class="proposal <?= $status ?>">

    <div class="d-flex align-items-center justify-content-between">
        <div class="title">
            <b class="badge text-uppercase primary"><?= lang('Proposal', 'Antrag') ?></b>
            <h1 class="mt-0">
                <?= $project['name'] ?>
            </h1>

            <h2 class="subtitle">
                <?= $project['title'] ?>
            </h2>

            <?php if ($status == 'withdrawn') { ?>
                <p class="text-danger">
                    <?= lang('This proposal has been withdrawn with the following reason:', 'Dieser Antrag wurde zurückgezogen mit folgendem Grund:') ?>
                    <br>
                    <b><?= $project['withdrawn_reason'] ?? lang('No reason given', 'Kein Grund angegeben') ?></b>
                </p>
            <?php } ?>

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
                            <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>?phase=rejected" class="item badge status danger mb-5"><?= lang('Rejected', 'Abgelehnt') ?></a>
                            <a href="<?= ROOTPATH ?>/proposals/edit/<?= $id ?>?phase=withdrawn" class="item badge status muted"><?= lang('Withdrawn', 'Zurückgezogen') ?></a>
                        </div>
                    </div>
                <?php } else if ($status == 'approved') { ?>
                    <span class="badge status success">
                        <i class="ph ph-check-circle" aria-hidden="true"></i>
                        <?= lang('Approved', 'Bewilligt') ?>
                    </span>
                <?php } else if ($status == 'rejected') { ?>
                    <span class="badge status danger">
                        <i class="ph ph-x-circle" aria-hidden="true"></i>
                        <?= lang('Rejected', 'Abgelehnt') ?>
                    </span>
                <?php } else if ($status == 'withdrawn') { ?>
                    <span class="badge status muted">
                        <i class="ph ph-x-circle" aria-hidden="true"></i>
                        <?= lang('Withdrawn', 'Zurückgezogen') ?>
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
                        <?= lang('You don\t have permission<br>to change the status', 'Du hast keine Berechtigung,<br>um den Status zu ändern') ?>
                    </small>
                </div>
            <?php  } ?>
        </div>
    </div>



    <div class="btn-toolbar">
        <?php if ($edit_perm) { ?>
            <?php
            if ($status == 'approved' && (empty($connected_project) || !$connected_project)) {
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

        <?php if (
            $Settings->hasPermission('proposals.delete') || ($Settings->hasPermission('proposals.delete-own') && $edit_perm)
        ) { ?>

            <div class="dropdown">
                <button class="btn danger" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
                    <i class="ph ph-trash"></i>
                    <?= lang('Delete', 'Löschen') ?>
                    <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdown-1">
                    <div class="content">
                        <?php if (!empty($connected_project)) { ?>
                            <b>
                                <?= lang(
                                    'Deleting this proposal is not possible while it is connected to a project. Delete the connected project first.',
                                    'Das Löschen dieses Antrags ist nicht möglich, solange er mit einem Projekt verbunden ist. Lösche zuerst das verbundene Projekt.'
                                ) ?>
                            </b>
                        <?php } else { ?>
                            <b class="text-danger"><?= lang('Attention', 'Achtung') ?>!</b><br>
                            <small>
                                <?= lang(
                                    'The proposal is permanently deleted and the connection to all associated persons, documents, etc. is also removed. This cannot be undone.',
                                    'Der Antrag wird permanent gelöscht und auch die Verbindung zu allen zugehörigen Personen, Dokumenten usw. entfernt. Dies kann nicht rückgängig gemacht werden.'
                                ) ?>
                            </small>
                            <form action="<?= ROOTPATH ?>/crud/proposals/delete/<?= $project['_id'] ?>" method="post">
                                <button class="btn btn-block danger" type="submit"><?= lang('Delete permanently', 'Permanent löschen') ?></button>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>



    <nav class="pills mt-20" id="nav-tabs">
        <button class="btn font-weight-bold active" id="general-btn" onclick="navigate('general')">
            <i class="ph ph-file-text"></i>
            <?= lang('Proposal', 'Antragsdetails') ?>
        </button>
        <?php if (!empty($connected_project)) { ?>
            <a href="<?= ROOTPATH ?>/projects/view/<?= $connected_project['_id'] ?>" class="btn font-weight-bold">
                <i class="ph ph-link m-0"></i>
                <?= lang('Project', 'Projekt') ?>
            </a>
        <?php } ?>

        <?php
        $count_history = count($project['history'] ?? []);
        if ($count_history) :
        ?>
            <button onclick="navigate('history')" id="btn-history" class="btn">
                <i class="ph ph-clock-counter-clockwise" aria-hidden="true"></i>
                <?= lang('History', 'Historie') ?>
                <span class="index"><?= $count_history ?></span>
            </button>
        <?php endif; ?>
        <?php if ($Settings->hasPermission('raw-data')) { ?>
            <button class="btn" style="--primary-color: var(--muted-color);--primary-color-20: var(--muted-color-20);" onclick="navigate('raw-data')" id="raw-data-btn">
                <i class="ph ph-code"></i>
                <?= lang('Raw data', 'Rohdaten') ?>
            </button>
        <?php } ?>
    </nav>


    <?php if ($nagoyaRelevant) { ?>
        <div class="nagoya-message">
            <?php
            $whoIsNext = Nagoya::whoIsNext($project);
            if ($whoIsNext === 'abs-team' && $Settings->hasPermission('nagoya.view')) { ?>
                <div class="alert signal mt-20">
                    <h5 class="title"><?= lang('Nagoya Protocol review', 'Nagoya-Protokoll Bewertung') ?></h5>
                    <?= lang('You can now review the Nagoya Protocol compliance of this proposal.', 'Sie können nun die Nagoya-Protokoll Konformität dieses Antrags bewerten.') ?>
                    <br>
                    <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $id ?>" class="btn signal small">
                        <i class="ph ph-clipboard-text"></i>
                        <?= lang('Review Nagoya Protocol compliance', 'Nagoya-Protokoll Bewertung durchführen') ?>
                    </a>
                </div>
            <?php } elseif ($whoIsNext === 'researcher' && $user_project) { ?>
                <!-- <div class="alert signal mt-20">
                    <h5 class="title"><?= lang('Nagoya Protocol review', 'Nagoya-Protokoll Bewertung') ?></h5>
                    <?= lang('You can already provide additional Nagoya Protocol information.', 'Sie können bereits zusätzliche Nagoya-Protokoll Informationen bereitstellen.') ?>
                    <br>
                    <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $id ?>" class="btn signal small">
                        <i class="ph ph-clipboard-text"></i>
                        <?= lang('Provide information', 'Informationen bereitstellen') ?>
                    </a>
                </div> -->
            <?php } elseif ($whoIsNext === 'researcher-required' && $user_project) { ?>
                <div class="alert danger mt-20">
                    <h5 class="title"><?= lang('Nagoya Protocol review', 'Nagoya-Protokoll Bewertung') ?></h5>
                    <?= lang('You are required to provide additional Nagoya Protocol information.', 'Sie sind verpflichtet, zusätzliche Nagoya-Protokoll Informationen bereitzustellen.') ?>
                    <br>
                    <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $id ?>" class="btn danger">
                        <i class="ph ph-clipboard-text"></i>
                        <?= lang('Provide information', 'Informationen bereitstellen') ?>
                    </a>
                </div>
            <?php } else if ($whoIsNext === 'permits-pending' && ($user_project || $Settings->hasPermission('nagoya.view'))) { ?>
                <div class="alert warning mt-20">
                    <h5 class="title"><?= lang('Nagoya Protocol review', 'Nagoya-Protokoll Bewertung') ?></h5>
                    <?= lang('There are pending permits related to the Nagoya Protocol.', 'Es gibt ausstehende Genehmigungen im Zusammenhang mit dem Nagoya-Protokoll.') ?>
                </div>
            <?php } ?>
        </div>

    <?php } ?>



    <section id="general">

        <?php
        $mentioned_fields = [];
        ?>
        <div class="row row-eq-spacing mt-0">
            <div class="col-md-8">
                <h2>
                    <?= lang('Proposal details', 'Antragsdetails') ?>
                </h2>

                <div class="tabs" id="status-tabs">
                    <button class="btn font-weight-bold active" style="--primary-color: var(--signal-color);--primary-color-20: var(--signal-color-20);" onclick="selectTab('proposal')" id="proposal-btn">
                        <i class="ph ph-file-text"></i>
                        <?= lang('Proposal', 'Antrag') ?>
                    </button>
                    <?php if ($status == 'approved') { ?>
                        <button class="btn font-weight-bold" style="--primary-color: var(--success-color);--primary-color-20: var(--success-color-20);" onclick="selectTab('approval')" id="approval-btn">
                            <i class="ph ph-check-circle"></i>
                            <?= lang('Approval', 'Bewilligung') ?>
                        </button>
                        <!-- finance -->
                        <button class="btn font-weight-bold" onclick="selectTab('finance')" id="finance-btn">
                            <i class="ph ph-money"></i>
                            <?= lang('Finance', 'Finanzen') ?>
                        </button>
                    <?php } ?>
                    <?php if ($status == 'rejected') { ?>
                        <button class="btn font-weight-bold" style="--primary-color: var(--danger-color);--primary-color-20: var(--danger-color-20);" onclick="selectTab('rejection')" id="rejection-btn">
                            <i class="ph ph-x-circle"></i>
                            <?= lang('Rejection', 'Ablehnung') ?>
                        </button>
                    <?php } ?>

                    <!-- documents -->
                    <button class="btn font-weight-bold" onclick="selectTab('documents')" id="documents-btn">
                        <i class="ph ph-file-text"></i>
                        <?= lang('Documents', 'Dokumente') ?>
                        <span class="index"><?= count($documents) ?></span>
                    </button>

                    <?php if ($nagoyaRelevant) { ?>
                        <button class="btn font-weight-bold" onclick="selectTab('nagoya')" id="nagoya-btn" style="--primary-color: var(--<?= $nagoya_status_color ?>-color);--primary-color-20: var(--<?= $nagoya_status_color ?>-color-20);">
                            <span><?= Nagoya::icon($project) ?></span>
                            <?= lang('Nagoya Protocol', 'Nagoya-Protokoll') ?>
                        </button>
                    <?php } ?>

                </div>
                <table class="table" id="proposal-details">
                    <tbody>
                        <?php
                        $fields = $Project->getFields($type, 'proposed');
                        foreach ($fields as $f) {
                            $key = $f['module'];
                            if ($key == 'nagoya') {
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
                                if ($key == 'nagoya') {
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

                    <div id="finance-details" style="display:none;">
                        <table class="table">
                            <tbody>
                                <?php
                                $fields = [
                                    'grant_income_proposed',
                                    'grant_income',
                                    'grant_sum_proposed',
                                    'grant_sum',
                                ];
                                foreach ($fields as $key) {
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

                        <h5 class="mb-0">
                            <?= lang('Third-party funding per year', 'Drittmitteleinnahmen pro Jahr') ?>

                            <a href="<?= ROOTPATH ?>/proposals/finance/<?= $id ?>">
                                <i class="ph ph-edit"></i>
                            </a>
                        </h5>

                        <table class="table">
                            <tbody>
                                <?php
                                $years = $project['grant_years'] ?? [];
                                if (empty($years)) {
                                    echo '<tr><td>' . lang('No funding information available.', 'Keine Drittmitteleinnahmen verfügbar.') . '</td></tr>';
                                } else foreach ($years as $year => $amount) {
                                    if (empty($amount)) continue;
                                ?>
                                    <tr>
                                        <td class="w-50 font-weight-bold"><?= $year ?></td>
                                        <td>
                                            <?= $Project->printField('grant_sum', $amount); ?>
                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>


                <?php } ?>
                <?php if ($status == 'rejected') { ?>
                    <table class="table" id="rejection-details" style="display:none;">
                        <tbody>
                            <?php
                            $fields = $Project->getFields($type, 'rejected');
                            foreach ($fields as $f) {
                                $key = $f['module'];
                                if ($key == 'nagoya') {
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


                <?php if ($Settings->hasPermission('proposals.view-documents') || $user_project) { ?>
                    <div id="documents-details" style="display:none;">
                        <table class="table">
                            <tbody>
                                <?php
                                if (empty($documents)) {
                                    echo '<tr><td>' . lang('No documents available.', 'Keine Dokumente verfügbar.') . '</td></tr>';
                                } else {
                                    foreach ($documents as $doc) {
                                        $file_url = ROOTPATH . '/uploads/' . $doc['_id'] . '.' . $doc['extension'];
                                ?>
                                        <tr>
                                            <td>
                                                <div class="dropdown float-right">
                                                    <button class="btn link" data-toggle="dropdown" type="button" id="delete-doc-<?= $doc['_id'] ?>" aria-haspopup="true" aria-expanded="false">
                                                        <i class="ph ph-trash text-danger"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="delete-doc-<?= $doc['_id'] ?>">
                                                        <div class="content">
                                                            <form action="<?= ROOTPATH ?>/data/delete" method="post">
                                                                <span class="text-danger"><?= lang('Do you want to delete this document?', 'Möchtest du dieses Dokument wirklich löschen?') ?></span>
                                                                <input type="hidden" name="id" value="<?= $doc['_id'] ?>">
                                                                <button class="btn btn-block danger" type="submit"><?= lang('Delete', 'Löschen') ?></button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                <a href="<?= $file_url ?>" class="">
                                                    <h6 class="m-0">
                                                        <?= $Vocabulary->getValue('proposal-document-types', $doc['name'] ?? '', lang('Sonstiges', 'Other')) ?>
                                                        <i class="ph ph-download"></i>
                                                    </h6>
                                                </a>
                                                <?= $doc['description'] ?? '' ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?= $doc['filename'] ?> (<?= $doc['size'] ?> Bytes)
                                                    <br>
                                                    <?= lang('Uploaded by', 'Hochgeladen von') ?> <?= $DB->getNameFromId($doc['uploaded_by']) ?>
                                                    <?= lang('on', 'am') ?> <?= date('d.m.Y', strtotime($doc['uploaded'])) ?>
                                                </small>
                                            </td>
                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php if ($Settings->hasPermission('proposals.upload-documents')) { ?>
                            <form action="<?= ROOTPATH ?>/data/upload" method="post" enctype="multipart/form-data" class="box padded">
                                <h5 class="title font-size-16">
                                    <?= lang('Upload document', 'Dokument hochladen') ?>
                                </h5>
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" id="upload-file" name="file" class="custom-file-input" required>
                                        <label for="upload-file" class="custom-file-label"><?= lang('Choose a file', 'Wähle eine Datei aus') ?></label>
                                    </div>
                                </div>
                                <input type="hidden" name="values[type]" value="proposals">
                                <input type="hidden" name="values[id]" value="<?= $id ?>">
                                <div class="form-group floating-form">
                                    <select class="form-control" name="values[name]" placeholder="Name" required>
                                        <?php
                                        $vocab = $Vocabulary->getValues('proposal-document-types');
                                        foreach ($vocab as $v) { ?>
                                            <option value="<?= $v['id'] ?>"><?= lang($v['en'], $v['de'] ?? null) ?></option>
                                        <?php } ?>
                                    </select>
                                    <label for="name" class="required"><?= lang('Document type', 'Dokumenttyp') ?></label>
                                </div>
                                <div class="form-group floating-form">
                                    <input type="text" class="form-control" name="values[description]" placeholder="<?= lang('Description', 'Beschreibung') ?>" value="">
                                    <label for="description"><?= lang('Description', 'Beschreibung') ?></label>
                                </div>
                                <button class="btn primary" type="submit"><?= lang('Upload', 'Hochladen') ?></button>
                            </form>
                        <?php } ?>
                    </div>

                <?php } ?>


                <?php if ($nagoyaRelevant) {
                    $nagoya         = DB::doc2Arr($project['nagoya'] ?? []);
                    $status         = $nagoya['status'] ?? 'unknown';
                    $scopeSubmitted = !empty($nagoya['scopeSubmitted']);

                    $countries   = DB::doc2Arr($nagoya['countries'] ?? []);
                    $absCountries    = [];
                    $nonAbsCountries = [];
                    $scopeBlocks     = 0;
                    $absWithScope    = 0;
                    $openCountryReviews = 0;

                    foreach ($countries as $c) {
                        if ($c['abs'] ?? false) {
                            $absCountries[] = $c;
                            $groups = DB::doc2Arr($c['scope']['groups'] ?? []);
                            $scopeBlocks += count($groups);
                            if (!empty($groups)) {
                                $absWithScope++;
                            }
                            $rev = $c['review'] ?? [];
                            $party = $rev['nagoyaParty']    ?? 'unknown';
                            $own   = $rev['ownABSMeasures'] ?? 'unknown';
                            if ($party === 'unknown' && $own === 'unknown') {
                                $openCountryReviews++;
                            }
                        } else {
                            $nonAbsCountries[] = $c;
                        }
                    }

                    $totalCountries = count($countries);
                    $totalAbs       = count($absCountries);
                    $totalNonAbs    = count($nonAbsCountries);
                    $scopeComplete  = Nagoya::scopeComplete($nagoya);
                    dump($nagoya['status']);
                ?>
                    <div class="box padded mt-0" id="nagoya-details" style="display:none;">

                        <!-- Header: Status + Summary -->
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <?= Nagoya::badge($project) ?>
                            </div>
                            <div class="text-right small text-muted">
                                <?php if ($totalCountries > 0): ?>
                                    <div>
                                        <?= lang('Countries:', 'Länder:') ?>
                                        <?= $totalCountries ?>
                                        (<?= $totalAbs ?> <?= lang('ABS-relevant', 'ABS-relevant') ?>,
                                        <?= $totalNonAbs ?> <?= lang('not ABS-relevant', 'nicht ABS-relevant') ?>)
                                    </div>
                                <?php endif; ?>
                                <?php if ($totalAbs > 0): ?>
                                    <div>
                                        <?= lang('Sample Collections:', 'Probensammlungen:') ?>
                                        <?= $scopeBlocks ?>
                                        <?php if ($scopeBlocks > 0 && $absWithScope < $totalAbs): ?>
                                            · <?= lang('some ABS countries without scope', 'einige ABS-Länder ohne Scope') ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Kontextabhängige Hinweise / Aktionen -->

                        <?php if ($edit_perm): ?>
                            <?php if ($status === 'researcher-input' && !$scopeComplete): ?>
                                <div class="alert signal mt-20">
                                    <?= lang(
                                        'Please complete the Nagoya scope information so that the ABS Compliance Team can review this project.',
                                        'Bitte vervollständige die Scope-Informationen zum Nagoya-Protokoll, damit das ABS-Compliance-Team diesen Antrag prüfen kann.'
                                    ) ?>
                                    <br>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $project['_id'] ?>" class="btn signal mt-5">
                                        <i class="ph ph-crosshair"></i> <?= lang('Edit scope information', 'Scope-Informationen bearbeiten') ?>
                                    </a>
                                </div>
                            <?php elseif ($status === 'researcher-input' && $scopeComplete && !$scopeSubmitted): ?>
                                <div class="alert info mt-20">
                                    <?= lang(
                                        'The scope information is complete but has not been submitted for ABS review yet.',
                                        'Die Scope-Informationen sind vollständig, wurden aber noch nicht zur ABS-Prüfung eingereicht.'
                                    ) ?>
                                    <br>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-scope/<?= $project['_id'] ?>" class="btn primary mt-5">
                                        <i class="ph ph-paper-plane-tilt"></i>
                                        <?= lang('Submit scope for ABS review', 'Scope-Analyse zur ABS-Prüfung einreichen') ?>
                                    </a>
                                </div>
                            <?php elseif ($status === 'awaiting-abs-evaluation'): ?>
                                <div class="alert info mt-20" style="--icon: '\e2b8';">
                                    <?= lang(
                                        'You have submitted the scope information. The ABS Compliance Team is now evaluating the project.',
                                        'Du hast die Scope-Informationen eingereicht. Das ABS-Compliance-Team bewertet nun den Antrag.'
                                    ) ?>
                                </div>
                            <?php elseif ($status === 'permits-pending'): ?>
                                <div class="alert warning mt-20" style="--icon: '\e198';">
                                    <?= lang(
                                        'There are pending permits related to the Nagoya Protocol.',
                                        'Es gibt ausstehende Genehmigungen im Zusammenhang mit dem Nagoya-Protokoll.'
                                    ) ?>
                                    <br>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $project['_id'] ?>" class="btn warning mt-5">
                                        <i class="ph ph-pencil"></i> <?= lang('Update permit information', 'Genehmigungsinformationen aktualisieren') ?>
                                    </a>
                                </div>
                            <?php elseif (in_array($status, ['in-scope-eu', 'in-scope-national', 'out-of-scope', 'compliant', 'not-relevant'])): ?>
                                <!-- <div class="alert info mt-20 small">
                                    <?= lang(
                                        'The ABS Compliance process is in an advanced state. Please contact the ABS Compliance Team if you have questions or need to update the information.',
                                        'Der ABS-Compliance-Prozess ist bereits weit fortgeschritten. Bitte wende dich an das ABS-Compliance-Team, wenn du Fragen hast oder Angaben aktualisieren musst.'
                                    ) ?>
                                </div> -->
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if ($Settings->hasPermission('nagoya.view')): ?>
                            <?php if ($status === 'abs-review' && $openCountryReviews > 0): ?>
                                <div class="alert signal mt-20" style="--icon: '\e40c';">
                                    <?= lang(
                                        'There are countries with pending ABS review. Please complete the country review.',
                                        'Es gibt Länder mit ausstehender ABS-Bewertung. Bitte schließe die Länderprüfung ab.'
                                    ) ?>
                                    <br>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $project['_id'] ?>" class="btn signal mt-5">
                                        <i class="ph ph-pencil"></i> <?= lang('Review countries', 'Länder prüfen') ?>
                                    </a>
                                </div>
                            <?php elseif ($status === 'awaiting-abs-evaluation'): ?>
                                <div class="alert signal mt-20" style="--icon: '\e198';">
                                    <?= lang(
                                        'The scope information has been submitted and is complete. Please perform the ABS evaluation (A/B/C classification).',
                                        'Die Scope-Informationen wurden eingereicht und sind vollständig. Bitte führe die ABS-Bewertung (A/B/C-Klassifikation) durch.'
                                    ) ?>
                                    <br>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-evaluation/<?= $project['_id'] ?>" class="btn signal mt-5">
                                        <i class="ph ph-checks"></i> <?= lang('Open ABS evaluation', 'ABS-Bewertung öffnen') ?>
                                    </a>
                                </div>
                            <?php elseif ($status === 'permits-pending'): ?>
                                <div class="alert warning mt-20" style="--icon: '\e198';">
                                    <?= lang(
                                        'There are pending permits related to the Nagoya Protocol. Please review and update the permit information if necessary.',
                                        'Es gibt ausstehende Genehmigungen im Zusammenhang mit dem Nagoya-Protokoll. Bitte überprüfe und aktualisiere die Genehmigungsinformationen bei Bedarf.'
                                    ) ?>
                                    <br>
                                    <a href="<?= ROOTPATH ?>/proposals/nagoya-permits/<?= $project['_id'] ?>" class="btn warning mt-5">
                                        <i class="ph ph-pencil"></i> <?= lang('Review permit information', 'Genehmigungsinformationen prüfen') ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Intro / Hinweis -->
                        <p class="text-muted mt-20">
                            <?= lang(
                                'This project may involve genetic resources from outside Germany. Below you can find the countries and related ABS information.',
                                'Dieses Projekt beinhaltet möglicherweise genetische Ressourcen außerhalb Deutschlands. Unten sind die Länder und zugehörigen ABS-Informationen aufgeführt.'
                            ) ?>
                        </p>

                        <!-- Countries and scope overview -->

                        <?php if ($Settings->hasPermission('nagoya.view')): ?>
                            <a href="<?= ROOTPATH ?>/proposals/nagoya-countries/<?= $project['_id'] ?>" class="btn primary small float-right">
                                <i class="ph ph-pencil"></i> <?= lang('Review countries', 'Länder bewerten') ?>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($countries)): ?>
                            <?php if ($totalAbs > 0): ?>
                                <h5 class="mt-20">
                                    <?= lang('ABS-relevant countries', 'ABS-relevante Länder') ?>
                                </h5>
                                <ul class="list-group mb-15">
                                    <?php foreach ($absCountries as $c):
                                        $review = $c['review'] ?? [];
                                        $scope  = $c['scope']['groups'] ?? [];
                                        $numGroups = count($scope);
                                    ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?></strong>
                                                <div class="small text-muted">
                                                    <?= lang('Sample Collections:', 'Probensammlungen:') ?> <?= $numGroups ?>
                                                    <?php if ($Settings->hasPermission('nagoya.view') && !empty($review['comment'])): ?>
                                                        · <?= htmlspecialchars($review['comment']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>

                            <?php if ($totalNonAbs > 0): ?>
                                <h5 class="mt-10">
                                    <?= lang('Countries without ABS obligations', 'Länder ohne ABS-Verpflichtungen') ?>
                                </h5>
                                <ul class="list-group">
                                    <?php foreach ($nonAbsCountries as $c): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= $DB->getCountry($c['code'], lang('name', 'name_de')) ?></strong>
                                            </div>
                                            <?= Nagoya::countryBadge(DB::doc2Arr($c)) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted"><?= lang('No countries specified yet.', 'Noch keine Länder angegeben.') ?></p>
                        <?php endif; ?>

                        <!-- Overall rationale -->
                        <?php if (!empty($project['nagoya']['absRationale'])): ?>
                            <div class="mt-20">
                                <h6><?= lang('Overall rationale / comments', 'Gesamtbegründung / Kommentare') ?></h6>
                                <div class="p-10 bg-light border rounded">
                                    <?= nl2br(htmlspecialchars($project['nagoya']['absRationale'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php } ?>


                <script>
                    // select tab function
                    function selectTab(tab) {
                        $('#proposal-details').hide();
                        $('#approval-details').hide();
                        $('#rejection-details').hide();
                        $('#finance-details').hide();
                        $('#documents-details').hide();
                        $('#nagoya-details').hide();
                        $('#' + tab + '-details').show();

                        $('#status-tabs .btn').removeClass('active');
                        $('#' + tab + '-btn').addClass('active');
                    }
                </script>

            </div>

            <div class="col-md-4">
                <h2>
                    <?= lang('Proposal members', 'Beteiligte Personen') ?>
                </h2>

                <?php if ($edit_perm) { ?>
                    <div class="btn-toolbar mb-10">
                        <a href="<?= ROOTPATH ?>/proposals/persons/<?= $id ?>" class="btn primary">
                            <i class="ph ph-edit"></i>
                            <?= lang('Edit', 'Bearbeiten') ?>
                        </a>
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

                <h2>
                    <?= lang('Units', 'Einheiten') ?>
                </h2>
                <table class="table unit-table w-full">
                    <tbody>
                        <?php
                        $units = DB::doc2Arr($project['units'] ?? []);
                        // $tree =  $Groups->getPersonHierarchyTree($units);
                        if (!empty($units)) {
                            $hierarchy = $Groups->getPersonHierarchyTree($units);
                            $tree = $Groups->readableHierarchy($hierarchy);

                            foreach ($tree as $row) { ?>
                                <tr>
                                    <td class="indent-<?= ($row['indent']) ?>">
                                        <a href="<?= ROOTPATH ?>/groups/view/<?= $row['id'] ?>">
                                            <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td>
                                    <?= lang('No units connected.', 'Keine Einheiten verknüpft.') ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </section>

    <!-- raw data -->
    <section id="raw-data" style="display: none;">
        <h2 class="title">
            <?= lang('Raw data', 'Rohdaten') ?>
        </h2>
        <p>
            <?= lang('Raw data of this activity.', 'Rohdaten dieser Aktivität.') ?>
        </p>
        <div class="box padded overflow-x-scroll">
            <pre><?= htmlspecialchars(json_encode($project, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
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
        if (empty($project['history'] ?? [])) {
            echo lang('No history available.', 'Keine Historie verfügbar.');
        } else {
        ?>
            <div class="history-list">
                <?php foreach (($project['history']) as $h) {
                    if (!isset($h['type'])) continue;
                ?>
                    <div class="box p-20">
                        <span class="badge primary float-md-right"><?= date('d.m.Y', strtotime($h['date'])) ?></span>
                        <h5 class="m-0">
                            <?php if ($h['type'] == 'created') {
                                echo lang('Created by ', 'Erstellt von ');
                            } else if ($h['type'] == 'edited') {
                                echo lang('Edited by ', 'Bearbeitet von ');
                            } else if ($h['type'] == 'imported') {
                                echo lang('Imported by ', 'Importiert von ');
                            } else {
                                echo $h['type'] . lang(' by ', ' von ');
                            }
                            if (isset($h['user']) && !empty($h['user'])) {
                                echo '<a href="' . ROOTPATH . '/profile/' . $h['user'] . '">' . $DB->getNameFromId($h['user']) . '</a>';
                            } else {
                                echo "System";
                            }
                            ?>
                        </h5>

                        <?php
                        if (isset($h['changes']) && count($h['changes']) > 0) {
                            echo '<div class="font-weight-bold mt-10">' .
                                lang('Changes to the project:', 'Änderungen am Projekt:') .
                                '</div>';
                            echo '<table class="table simple w-auto small border px-10">';
                            foreach ($h['changes'] as $key => $change) {
                                $before = $change['before'] ?? '<em>empty</em>';
                                $after = $change['after'] ?? '<em>empty</em>';
                                if ($before == $after) continue;
                                if (empty($before)) $before = '<em>empty</em>';
                                if (empty($after)) $after = '<em>empty</em>';
                                echo '<tr>
                                <td class="pl-0">
                                    <span class="key">' . $Project->printLabel($key) . '</span> 
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

                            echo '<table class="table simple w-auto small border px-10">';
                            foreach ($h['data'] as $key => $datum) {
                                echo '<tr>
                                <td class="pl-0">
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
</div>