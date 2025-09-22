<?php

/**
 * Page for reports
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /activities/locking
 *
 * @package     OSIRIS
 * @since       1.0
 * 
 * @copyright	Copyright (c) 2025 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<style>
    .custom-radio input#open_access:checked~label::before {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }

    .custom-radio input#open_access-0:checked~label::before {
        background-color: var(--danger-color);
        border-color: var(--danger-color);
    }
</style>


<div class="container w-800 mw-full">
    <h1>
        <i class="ph ph-lock"></i>
        <?= lang('Lock a period', 'Zeitraum sperren') ?>
    </h1>
    <p>
        <?= lang('You can lock a period once a report has been generated. All activities that were report-worthy during this period will be locked and can no longer be deleted or edited.', 'Du kannst einen Zeitraum sperren, sobald ein Report generiert wurde. Alle aktivitäten, die in diesem Zeitraum report-würdig waren, werden dann gesperrt und können nicht mehr gelöscht oder bearbeitet werden.') ?>
    </p>

    <p>
        <?=lang('The following roles can still edit or delete locked activities:', 'Die folgenden Rollen können weiterhin gesperrte Aktivitäten bearbeiten oder löschen:')?>
        <br>
        <b><?=lang('Edit', 'Bearbeiten')?>:</b>
        <?php
            $roles = $osiris->adminRights->find([
                'right' => 'activities.edit-locked',
                'value' => true
            ])->toArray();
            echo implode(', ', array_column($roles, 'role'));
        ?>
        <br>
        <b><?=lang('Delete', 'Löschen')?>:</b>
        <?php
            $roles = $osiris->adminRights->find([
                'right' => 'activities.delete-locked',
                'value' => true
            ])->toArray();
            echo implode(', ', array_column($roles, 'role'));
        ?>
    </p>



    <p>
        <?= lang('Activities that are not report-worthy (e.g. Online ahead of print, Activities without affiliated authors) will not be locked.', 'Aktivitäten, die nicht report-würdig sind (z.B. Online ahead of print, Akt. ohne affiliierte Autoren) werden nicht gesperrt.') ?>
    </p>

    <div class="box padded">
        <form action="<?= ROOTPATH ?>/crud/activities/lock" method="post">

            <div class="form-row row-eq-spacing">
                <div class="col-sm">
                    <label class="required" for="start">
                        <?= lang('Beginning', 'Anfang') ?>
                    </label>
                    <input type="date" class="form-control" name="start" id="start" value="<?= CURRENTYEAR ?>-01-01" required>
                </div>
                <div class="col-sm">
                    <label class="required" for="end">
                        <?= lang('End', 'Ende') ?>
                    </label>
                    <input type="date" class="form-control" name="end" id="end" value="<?= CURRENTYEAR ?>-06-30" required>
                </div>
            </div>
            <div class="my-20">
                <span><?= lang('Action', 'Aktion') ?>:</span>

                <div class="custom-radio d-inline-block ml-10" style="--secondary-color: var(--danger-color);">
                    <input type="radio" name="action" id="action-lock" value="lock" checked="">
                    <label for="action-lock"><i class="ph ph-fill ph-lock text-danger"></i> <?= lang('Lock', 'Sperren') ?></label>
                </div>
                <div class="custom-radio d-inline-block ml-10" style="--secondary-color: var(--success-color);">
                    <input type="radio" name="action" id="action-unlock" value="unlock">
                    <label for="action-unlock"><i class="ph ph-fill ph-lock-open text-success"></i> <?= lang('Unlock', 'Entsperren') ?></label>
                </div>
            </div>
            <button class="btn" type="submit"><?= lang('Submit', 'Bestätigen') ?></button>

        </form>
    </div>
</div>