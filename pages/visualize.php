<?php

/**
 * Page for overview on visualizations
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /visualize
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$users = $osiris->persons->find(['roles' => 'scientist'], ['sort' => ['is_active' => -1, 'last' => 1]]);

$scientist = $_GET['scientist'] ?? $_SESSION['username'];
$selectedUser = $osiris->persons->findone(['user' => $scientist]);

?>

<div class="content">

    <h1>
        <i class="ph-duotone ph-graph" aria-hidden="true"></i>
        <?= lang('search.visualisations') ?>
    </h1>


    <div class="tiles">
        <a href="<?= ROOTPATH ?>/visualize/coauthors" class="tile">
            <h5 class="title">
                <?= lang('common.coauthor_network') ?>
            </h5>
            <img src="<?= ROOTPATH ?>/img/charts/chord.svg" alt="" class="w-full">
        </a>
        <a href="<?= ROOTPATH ?>/visualize/sunburst" class="tile">
            <h5 class="title">
                <?= lang('common.department_overview') ?>
            </h5>
            <img src="<?= ROOTPATH ?>/img/charts/sunburst.svg" alt="" class="w-full">
        </a>
        <a href="<?= ROOTPATH ?>/visualize/departments" class="tile">
            <h5 class="title">
                <?= lang('common.activity_network') ?>
            </h5>
            <img src="<?= ROOTPATH ?>/img/charts/departments.svg" alt="" class="w-full">
        </a>
        <a href="<?= ROOTPATH ?>/visualize/openaccess" class="tile">
            <h5 class="title">
                <?= lang('journals.open_access') ?>
            </h5>
            <img src="<?= ROOTPATH ?>/img/charts/open-access.png" alt="" class="w-full">
        </a>
        <?php if ($Settings->featureEnabled('projects')) { ?>
            <a href="<?= ROOTPATH ?>/visualize/map" class="tile">
                <h5 class="title">
                    <?= lang('search.collaborator_map') ?>
                </h5>
                <img src="<?= ROOTPATH ?>/img/charts/map.png" alt="" class="w-full">
            </a>
        <?php } ?>
    </div>
</div>