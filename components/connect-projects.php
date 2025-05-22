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
$project_list = $osiris->projects->find($filter, ['projection' => ['_id' => 1, 'name' => 1]])->toArray();
?>

<form action="<?= ROOTPATH ?>/crud/activities/update-project-data/<?= $id ?>" method="post">

    <table class="table simple">
        <thead>
            <tr>
                <th><?= lang('Project-ID', 'Projekt-ID') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody id="project-list">
            <?php
            if (!isset($activity['projects']) || empty($activity['projects'])) {
                $activity['projects'] = [''];
            }
            foreach ($activity['projects'] as $i => $con) { ?>
                <tr>
                    <td class="w-full">
                        <select name="projects[<?= $i ?>]" id="projects-<?= $i ?>" class="form-control" required>
                            <option value="" disabled <?= empty($con) ? 'selected' : '' ?>>-- <?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?> --</option>
                            <?php
                            foreach ($project_list as $s) { ?>
                                <option <?= $con == $s['_id'] ? 'selected' : '' ?> value="<?= $s['_id'] ?>"><?= $s['name'] ?></option>
                            <?php } ?>
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
                    <button class="btn small" type="button" onclick="addProjectRow()"><i class="ph ph-plus text-success"></i> <?= lang('Add row', 'Zeile hinzufügen') ?></button>
                </td>
            </tr>
        </tfoot>

    </table>

    <?php if ($full_permission) { ?>
        <p>
            <?= lang('Note: only projects are shown here. You cannot connect proposals.', 'Bemerkung: nur Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
        </p>
    <?php } else { ?>
        <p>
            <?= lang('Note: only your own projects are shown here. You cannot connect proposals.', 'Bemerkung: nur deine eigenen Projekte werden hier gezeigt. Du kannst keine Anträge verknüpfen.') ?>
        </p>
    <?php } ?>
    <button class="btn secondary">
        <i class="ph ph-check"></i>
        <?= lang('Submit', 'Bestätigen') ?>
    </button>
</form>


<script>
    var projectCounter = <?= $i ?? 0 ?>;
    const tr = $('#project-list tr').first()

    function addProjectRow() {
        projectCounter++;
        const row = tr.clone()
        row.find('select').first().attr('name', 'projects[' + projectCounter + ']');
        $('#project-list').append(row)
    }
</script>