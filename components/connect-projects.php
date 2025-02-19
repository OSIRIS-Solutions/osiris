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
            if (!isset($doc['projects']) || empty($doc['projects'])) {
                $doc['projects'] = [''];
            }
            foreach ($doc['projects'] as $i => $con) { ?>
                <tr>
                    <td class="w-full">
                        <select name="projects[<?= $i ?>]" id="projects-<?= $i ?>" class="form-control" required>
                            <option value="" disabled <?= empty($con) ? 'selected' : '' ?>>-- <?= lang('Please select a project', 'Bitte wähle ein Projekt aus') ?> --</option>
                            <?php
                            foreach ($osiris->projects->distinct('name', ['status' => ['$in'=> ['approved', 'finished']]]) as $s) { ?>
                                <option <?= $con == $s ? 'selected' : '' ?>><?= $s ?></option>
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

    <p>
        <?= lang('Note: only approved projects are shown here.', 'Bemerkung: nur bewilligte Projekte werden hier gezeigt.') ?>
        <a href="<?= ROOTPATH ?>/projects" class="link"><?= lang('See all', 'Zeige alle') ?></a>
    </p>
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
