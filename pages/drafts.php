<?php

/**
 * This page contains drafts of activities.
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.6.0
 * 
 * @copyright	Copyright (c) 2026  Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

?>

<h1>
    <i class="ph-duotone ph-file-text"></i>
    <?= t('common.drafts') ?>
</h1>

<table class="table" id="draft-table">
    <thead>
        <tr>
            <th><?= t('common.title') ?></th>
            <th><?= t('common.created') ?></th>
            <th><?= t('common.actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drafts as $draft): ?>
            <tr>
                <td><?= $draft['title'] ?? '' ?></td>
                <td><?= format_date($draft['created'] ?? '') ?></td>
                <td>
                    <a href="<?= ROOTPATH ?>/add-activity?draft=<?= $draft['_id'] ?>" class="btn text-primary mr-10">
                        <i class="ph ph-pencil"></i>
                        <?= t('activities.continue_editing') ?>
                    </a>
                    <a href="<?= ROOTPATH ?>/activities/drafts/<?= $draft['_id'] ?>" target="_blank" class="link"><?= t('common.view') ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>