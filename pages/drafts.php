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
    <i class="ph ph-file-text"></i>
    <?=lang('Drafts', 'Entwürfe')?>
</h1>

<table class="table" id="draft-table">
    <thead>
        <tr>
            <th><?=lang('Title', 'Titel')?></th>
            <th><?=lang('Created', 'Erstellt')?></th>
            <th><?=lang('Actions', 'Aktionen')?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($drafts as $draft): ?>
            <tr>
                <td><?=$draft['title'] ?? ''?></td>
                <td><?=format_date($draft['created'] ?? '')?></td>
                <td>
                    <a href="<?=ROOTPATH?>/add-activity?draft=<?=$draft['_id']?>" class="btn text-primary mr-10"><?=lang('Load into editor', 'In den Editor laden')?></a>
                    <a href="<?=ROOTPATH?>/activities/drafts/<?=$draft['_id']?>" target="_blank" class="link"><?=lang('View', 'Anzeigen')?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
