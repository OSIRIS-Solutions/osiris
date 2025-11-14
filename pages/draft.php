<?php

/**
 * This page contains drafts of activities.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<h1>
    <i class="ph-duotone ph-file-text"></i>
    <?= $draft['title'] ?? lang('Draft', 'Entwurf') ?>
</h1>


<p class="text-muted">
    <?= lang('A draft from', 'Ein Entwurf von') ?> <?= $DB->getNameFromId($draft['created_by'] ?? '') ?> <?= lang('created at', 'erstellt am') ?> <?= format_date($draft['created'] ?? '', 'd.m.Y') ?>
    <?php if ($draft['created_by'] !== $_SESSION['username']) { ?>
        (<?= lang('shared with you', 'mit dir geteilt') ?>)
    <?php } ?>
</p>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/add-activity?draft=<?= $draft['_id'] ?>" class="btn primary">
        <i class="ph ph-pencil"></i>
        <?= lang('Edit', 'Bearbeiten') ?>
    </a>
    <div class="dropdown">
        <button class="btn" data-toggle="dropdown" type="button" id="invite-editor" aria-haspopup="true" aria-expanded="false">
            <i class="ph ph-user-plus"></i> <?= lang('Share this draft', 'Diesen Entwurf teilen') ?>
        </button>
        <div class="dropdown-menu w-300" aria-labelledby="invite-editor">
            <form action="<?= ROOTPATH ?>/crud/activities/invite-draft/<?= $draft['_id'] ?>" method="post" class="content">
                <?= lang('You shared this draft with:', 'Du hast diesen Entwurf mit geteilt mit:') ?>
                <ul class="list">
                    <?php
                    $shared_with = $draft['draft_shared_with'] ?? [];
                    if (!empty($shared_with)) foreach ($shared_with as $user) {
                    ?>
                        <li>
                            <i class="ph ph-user"></i>
                            <?= $DB->getNameFromId($user) ?>
                        </li>
                    <?php
                    } else {
                        echo '<li>' . lang('no one', 'niemand') . '</li>';
                    }
                    ?>
                </ul>
                <div class="form-group">
                    <label for="invitee"><?= lang('Select a user to invite', 'Wählen Sie einen Nutzenden aus, um ihn einzuladen') ?></label>
                    <select class="form-control" name="invitee" id="invitee" required>
                        <option value=""><?= lang('Select editor', 'Editor auswählen') ?></option>
                        <?php
                        $users = $osiris->persons->find(['is_active' => ['$ne' => false]], ['projection' => ['username' => 1, 'formalname' => 1], 'sort' => ['formalname' => 1]]);
                        foreach ($users as $user) { ?>
                            <option value="<?= $user['username'] ?>"><?= $user['formalname'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button class="btn btn-block" type="submit"><?= lang('Share', 'Teilen') ?></button>
            </form>
        </div>
    </div>
    <form action="<?= ROOTPATH ?>/crud/activities/delete-draft/<?= $draft['_id'] ?>" method="post" style="display:inline;">
        <button type="submit" class="btn danger" onclick="return confirm('<?= lang('Are you sure you want to delete this draft?', 'Sind Sie sicher, dass Sie diesen Entwurf löschen möchten?') ?>');">
            <i class="ph ph-trash"></i>
            <?= lang('Delete', 'Löschen') ?>
        </button>
    </form>
</div>


<?php
include_once BASEPATH . "/php/Modules.php";
?>

<table class="table" id="detail-table">
    <?php
    $Modules = new Modules($draft);

    $Format = new Document;
    $Format->setDocument($draft);
    $Format->usecase = "list";


    $typeArr = $Format->typeArr;
    $upload_possible = $typeArr['upload'] ?? true;
    $subtypeArr = $Format->subtypeArr;
    $typeModules = DB::doc2Arr($subtypeArr['modules'] ?? array());
    foreach ($typeModules as $m) {
        if (str_ends_with($m, '*')) $m = str_replace('*', '', $m);
        if ($m == 'date-range-ongoing') $ongoing = true;
        if ($m == 'supervisor') $sws = true;
        if ($m == 'supervisor-thesis') $supervisorThesis = true;
    }

    $emptyModules = [];
    foreach ($typeModules as $module) {
        if (str_ends_with($module, '*')) $module = str_replace('*', '', $module);
        if (in_array($module, ["semester-select", "event-select"])) continue;
    ?>
        <?php if ($module == 'projects' && isset($draft['projects'])) :
            $projects = [];
            foreach ($draft['projects'] as $pid) {
                $projects[] = $DB->getConnected('projects', $pid);
            }
            $projects = array_filter($projects);
            if (empty($projects)) {
                $emptyModules[] = 'projects';
                continue;
            }
        ?>
            <tr>
                <td>
                    <span class="key"><?= lang('Projects', 'Projekte') ?></span>
                    <?php foreach ($projects as $project) { ?>
                        <a class="module " href="<?= ROOTPATH ?>/projects/view/<?= $project['_id'] ?>">
                            <h5 class="m-0"><?= $project['name'] ?></h5>
                            <span class="text-muted-"><?= $project['title'] ?? '' ?></span>
                        </a>
                    <?php } ?>
                </td>
            </tr>

        <?php elseif ($module == 'teaching-course' && isset($draft['module_id'])) :
            $module = $DB->getConnected('teaching', $draft['module_id']);
            if (empty($module)) {
                $emptyModules[] = 'teaching-course';
                continue;
            }
        ?>
            <tr>
                <td>
                    <span class="key"><?= lang('Teaching module', 'Lehrveranstaltung') ?></span>

                    <a class="module " href="<?= ROOTPATH ?>/teaching#<?= $draft['module_id'] ?>">
                        <h5 class="m-0"><span class="highlight-text"><?= $module['module'] ?></span> <?= $module['title'] ?></h5>
                        <span class="text-muted-"><?= $module['affiliation'] ?></span>
                    </a>
                </td>
            </tr>

        <?php elseif ($module == 'journal' && isset($draft['journal_id'])) :
            $journal = $DB->getConnected('journal', $draft['journal_id']);
            if (empty($journal)) {
                $emptyModules[] = 'journal';
                continue;
            }
        ?>

            <tr>
                <td>
                    <span class="key"><?= $Settings->journalLabel() ?></span>

                    <a class="module " href="<?= ROOTPATH ?>/journal/view/<?= $draft['journal_id'] ?>">
                        <h6 class="m-0"><?= $journal['journal'] ?></h6>
                        <span class="float-right text-muted-"><?= $journal['publisher'] ?></span>
                        <span class="text-muted-">
                            ISSN: <?= print_list($journal['issn']) ?>
                            <br>
                            Impact:
                            <?= $draft['impact'] ?? 'unknown' ?>
                        </span>
                    </a>
                </td>
            </tr>
        <?php elseif ($module == 'conference' && isset($draft['conference_id'])) :
            $conference = $DB->getConnected('conference', $draft['conference_id']);
        ?>

            <tr>
                <td>
                    <span class="key">Event</span>
                    <?php if (empty($conference)) { ?>
                        <div><?= $draft['conference'] ?? '' ?></div>
                        <span class="text-danger">
                            <?= lang('This event has been deleted.', 'Diese Veranstaltung wurde gelöscht.') ?>
                        </span>
                    <?php } else { ?>

                        <div class="module ">
                            <h6 class="m-0">
                                <a href="<?= ROOTPATH ?>/conferences/view/<?= $draft['conference_id'] ?>">
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


    <?php if (isset($draft['comment'])) : ?>
        <tr class="text-muted">
            <td>
                <span class="key" style="text-decoration: 1px dotted underline;" data-toggle="tooltip" data-title="<?= lang('Only visible for authors and editors.', 'Nur sichtbar für Autoren und Editor-MA.') ?>">
                    <?= lang('Comment', 'Kommentar') ?>:
                </span>

                <?= $draft['comment'] ?>
            </td>
        </tr>
    <?php endif; ?>


</table>

<?php if (isset($_GET['verbose'])) { ?>
    <div class="box"><?php dump($draft); ?></div>
<?php } ?>