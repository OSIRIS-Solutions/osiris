<?php

/**
 * The overview of all committees
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . "/php/Committee.php";

$committees  = $osiris->committees->find(
    []
)->toArray();
?>


<h1>
    <i class="ph ph-identification-card" aria-hidden="true"></i>
    <?= lang('Committees & Boards', 'Gremien und Boards') ?>
</h1>
<div class="btn-toolbar">
    <?php if ($Settings->hasPermission('committees.edit')) { ?>
        <a href="<?= ROOTPATH ?>/committees/new">
            <i class="ph ph-plus"></i>
            <?= lang('Add new committee', 'Neues Gremium anlegen') ?>
        </a>
    <?php } ?>
</div>

<table class="table" id="committees-table">
    <thead>
        <tr>
            <th><?= lang('Name', 'Name') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($committees as $committee) { ?>
            <tr data-row="">
                <td>
                    <div class="d-flex align-items-center">
                        <span data-toggle="tooltip" data-title="<?= Committee::getType($committee['type']) ?>" class="badge mr-10">
                            <?= Committee::getIcon($committee['type'], 'ph-fw ph-2x m-0') ?>
                        </span>
                        <div class="">
                            <a href="<?= ROOTPATH ?>/committees/view/<?= $committee['_id'] ?>" class="link font-weight-bold colorless">
                                <?= $committee['name'] ?>
                            </a>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<script>
    $('#committees-table').DataTable({});
</script>