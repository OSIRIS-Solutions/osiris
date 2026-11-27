<?php

/**
 * Component to connect projects to activities.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activity
 *
 * @package OSIRIS
 * @since 1.2.2
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$full_permission = $Settings->hasPermission('projects.edit') || $Settings->hasPermission('projects.connect');
$filter = [];
if (!$full_permission) {
    // make sure to include currently selected projects
    $filter = ['$or' => [['persons.user' => $_SESSION['username']], ['_id' => ['$in' => $activity['projects'] ?? []]]]];
}
$project_list = $osiris->projects->find($filter, [
    'projection' => ['_id' => 1, 'name' => 1, 'acronym' => 1, 'title' => 1, 'title_de' => 1, 'internal_number' => 1],
    'sort' => ['name.en' => 1]
])->toArray();
?>

<form action="<?= ROOTPATH ?>/crud/activities/update-project-data/<?= $id ?>" method="post">

    <table class="table">
        <thead>
            <tr>
                <th><?= t('common.connected_projects') ?>:</th>
                <th></th>
            </tr>
        </thead>
        <tbody id="project-list">
            <?php
            foreach ($activity['projects'] ?? [] as $i => $con) {
                if (empty($con)) continue;
                $p = $osiris->projects->findOne(['_id' => $con]);
                if (empty($p)) continue;
            ?>
                <tr id="project-<?= $con ?>">
                    <td class="w-full">
                        <input type="hidden" name="projects[]" value="<?= $p['_id'] ?>">
                        <b><?= isset($p['acronym']) ? $p['acronym'] . ' – ' : '' ?><?= e(localized($p['name'] ?? '')) ?></b>
                        <br>
                        <span class="text-muted">
                            <?= localized($p['title'] ?? '') ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <p class="font-weight-bold"><?= t('projects.connect_other_project') ?>:</p>
    <div class="input-group">
        <select id="project-select" class="form-control" placeholder="<?= t('forms.project_select') ?>">
            <option value=""><?= t('forms.project_select') ?></option>
            <?php
            foreach ($project_list as $s) { ?>
                <option value="<?= $s['_id'] ?>"><?= isset($s['acronym']) ? $s['acronym'] . ' – ' : '' ?><?= e(localized($s['name'] ?? '')) ?> <?= e(localized($s['title'] ?? '')) ?> <?= isset($s['internal_number']) ? ('(ID ' . $s['internal_number'] . ')') : '' ?></option>
            <?php } ?>
        </select>
        <div class="input-group-append">
            <button class="btn" type="button" onclick="addProjectRow()"><i class="ph ph-plus text-success"></i> <?= t('projects.add_project') ?></button>
        </div>
    </div>

    <?php if ($full_permission) { ?>
        <p class="text-muted">
            <i class="ph ph-info"></i>
            <?= t('common.note_only_projects_are_shown_here_you_cannot_connect_proposals') ?>
        </p>
    <?php } else { ?>
        <p class="text-muted">
            <i class="ph ph-info"></i>
            <?= t('projects.note_only_your_own_projects_are_shown_here_you_cannot_connect_proposals') ?>
        </p>
    <?php } ?>
    <button class="btn secondary">
        <i class="ph ph-check"></i>
        <?= t('action.submit') ?>
    </button>
</form>


<script>
    function addProjectRow() {
        const row = $('<tr>')
        const projectId = $('#project-select').val();
        const projectName = $('#project-select option:selected').text();
        if (!projectId) {
            alert('<?= t('forms.project_select') ?>');
            return;
        }
        // check if project already exists
        if ($('#project-list').find(`#project-${projectId}`).length > 0) {
            toastError('<?= t('common.this_project_is_already_connected') ?>');
            return;
        }
        row.append(`<td class="w-full">
            <input type="hidden" name="projects[]" value="${projectId}">
            <b>${projectName}</b>
            </td>
            `);
        row.append(`<td>
            <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
        </td>`);
        row.attr('id', `project-${projectId}`);
        $('#project-list').append(row)
    }

    $("#project-select").selectize();
</script>