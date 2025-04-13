<?php

/**
 * The overview of all infrastructures
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

$infrastructures  = $osiris->infrastructures->find(
    [],
    ['sort' => ['end_date' => -1, 'start_date' => 1]]
)->toArray();
?>


<h1>
    <i class="ph ph-cube-transparent" aria-hidden="true"></i>
    <?= lang('Infrastructures', 'Infrastrukturen') ?>
</h1>
<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/infrastructures/statistics" class="btn">
        <i class="ph ph-chart-line-up"></i>
        <?= lang('Statistics', 'Statistiken') ?>
    </a>
    <?php if ($Settings->hasPermission('infrastructures.edit')) { ?>
        <a href="<?= ROOTPATH ?>/infrastructures/new">
            <i class="ph ph-plus"></i>
            <?= lang('Add new infrastructure', 'Neue Infrastruktur anlegen') ?>
        </a>
    <?php } ?>
</div>

<table class="table">
    <thead>
        <tr>
            <th><?= lang('Name', 'Name') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($infrastructures as $infra) { ?>
            <tr>
                <td>
                    <h6 class="m-0">
                        <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                            <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                        </a>
                        <br>
                    </h6>

                    <div class="text-muted mb-5">
                        <?php if (!empty($infra['subtitle'])) { ?>
                            <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                        <?php } else { ?>
                            <?= get_preview(lang($infra['description'], $infra['description_de'] ?? null), 300) ?>
                        <?php } ?>
                    </div>
                    <div>
                        <?= fromToYear($infra['start_date'], $infra['end_date'] ?? null, true) ?>
                    </div>
                </td>
            </tr>
        <?php } ?>
</table>