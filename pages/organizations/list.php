<?php

/**
 * The overview of all organizations
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.4.1
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . "/php/Organization.php";

$organizations  = $osiris->organizations->find(
    [],
    ['sort' => ['end_date' => -1, 'start_date' => 1]]
)->toArray();
?>


<h1>
    <i class="ph ph-building-office" aria-hidden="true"></i>
    <?= lang('External Organisations', 'Externe Organisationen') ?>
</h1>
<div class="btn-toolbar">
    <?php if ($Settings->hasPermission('organizations.edit')) { ?>
        <a href="<?= ROOTPATH ?>/organizations/new">
            <i class="ph ph-plus"></i>
            <?= lang('Add new organisation', 'Neue Organisation anlegen') ?>
        </a>
    <?php } ?>
</div>

<table class="table" id="organizations-table">
    <thead>
        <tr>
            <th><?= lang('Name', 'Name') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($organizations as $org) { ?>
            <tr data-row="">
                <td>
                    <div class="d-flex align-items-center">
                        <span data-toggle="tooltip" data-title="<?= $org['type'] ?>" class="badge mr-10">
                            <?= Organization::getIcon($org['type'], 'ph-fw ph-2x m-0') ?>
                        </span>
                        <div class="">
                            <a href="<?= ROOTPATH ?>/organizations/view/<?= $org['_id'] ?>" class="link font-weight-bold colorless">
                                <?= $org['name'] ?>
                            </a><br>
                            <?= $org['location'] ?>
                            <?php if (isset($org['ror'])) { ?>
                                <a href="<?= $org['ror'] ?>" class="ml-10" target="_blank" rel="noopener noreferrer">ROR <i class="ph ph-arrow-square-out"></i></a>
                            <?php } ?> 
                                                
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>


<script>
    $('#organizations-table').DataTable({});
</script>