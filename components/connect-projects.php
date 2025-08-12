<?php

/**
 * Component to connect projects to activities.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /activity
 *
 * @package OSIRIS
 * @since 1.2.2
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$full_permission = $Settings->hasPermission('projects.edit');
$filter = [];
if (!$full_permission) {
    // make sure to include currently selected projects
    $filter = ['$or' => [['persons.user' => $_SESSION['username']], ['_id' => ['$in' => $activity['projects'] ?? []]]]];
}
$project_list = $osiris->projects->find($filter, [
    'projection' => ['_id' => 1, 'name' => 1, 'title'=>1, 'title_de'=>1, 'internal_number'=>1], 
    'sort' => ['name' => 1]
])->toArray();
?>

<form action="<?= ROOTPATH ?>/crud/activities/update-project-data/<?= $id ?>" method="post">

    <table class="table">
        <thead>
            <tr>
                <th><?= lang('Connected projects', 'Verknüpfte Projekte') ?>:</th>
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
                        <b><?= $p['name'] ?></b>
                        <br>
                        <span class="text-muted">
                            <?= $p['title'] ?? '' ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn danger" type="button" onclick="$(this).closest('tr').remove()"><i class="ph ph-trash"></i></button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <p class="font-weight-bold"><?= lang('Connect other project', 'Verknüpfe weiteres Projekt') ?>:</p>
    <div class="input-group">
        <select id="project-select" class="form-control" placeholder="<?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?>">
            <option value=""><?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?></option>
            <?php
            foreach ($project_list as $s) { ?>
                <option value="<?= $s['_id'] ?>"><?= $s['name'] ?> <?=lang($s['title'], $s['title_de'] ?? null)?> <?=isset($s['internal_number']) ? ('(ID '.$s['internal_number'].')') : '' ?></option>
            <?php } ?>
        </select>
        <div class="input-group-append">
            <button class="btn" type="button" onclick="addProjectRow()"><i class="ph ph-plus text-success"></i> <?= lang('Add project', 'Projekt hinzuf.') ?></button>
        </div>
    </div>

    <?php if ($full_permission) { ?>
        <p class="text-muted">
            <i class="ph ph-info"></i>
            <?= lang('Note: only projects are shown here. You cannot connect proposals.', 'Bemerkung: nur Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
        </p>
    <?php } else { ?>
        <p class="text-muted">
            <i class="ph ph-info"></i>
            <?= lang('Note: only your own projects are shown here. You cannot connect proposals.', 'Bemerkung: nur deine eigenen Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
        </p>
    <?php } ?>
    <button class="btn secondary">
        <i class="ph ph-check"></i>
        <?= lang('Submit', 'Bestätigen') ?>
    </button>
</form>


<script>
    function addProjectRow() {
        const row = $('<tr>')
        const projectId = $('#project-select').val();
        const projectName = $('#project-select option:selected').text();
        if (!projectId) {
            alert('<?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?>');
            return;
        }
        // check if project already exists
        if ($('#project-list').find(`#project-${projectId}`).length > 0) {
            toastError('<?= lang('This project is already connected', 'Dieses Projekt ist bereits verbunden') ?>');
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