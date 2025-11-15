<?php

/**
 * Overview of all reports
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.5
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$reports = $osiris->adminReports->find();
?>

<h1>
    <i class="ph-duotone ph-clipboard-text"></i>
    <?= lang('Report Templates', 'Berichtsvorlagen') ?>
</h1>

<?php foreach ($reports as $report) { ?>
    <div class="box">
        <div class="content">
            <h3><?= $report['title'] ?></h3>
            <p class="text-primary"><?= $report['description'] ?? '' ?></p>

            <span class="badge primary">
                <b><?= lang('Start month', 'Startmonat') ?></b>: <?= format_month($report['start'] ?? null) ?>
            </span>
            <span class="badge primary">
                <b><?= lang('Duration', 'Dauer') ?></b>: <?= $report['duration'] ?? '-' ?> <?= lang('months', 'Monate') ?>
            </span>
        </div>
        <div class="footer">

            <a href="<?= ROOTPATH ?>/admin/reports/preview/<?= $report['_id'] ?>" class="btn mr-10">
                <i class="ph ph-eye"></i>
                <?= lang('Preview', 'Vorschau') ?>
            </a>
            <a href="<?= ROOTPATH ?>/admin/reports/builder/<?= $report['_id'] ?>" class="btn">
                <i class="ph ph-edit"></i>
                <?= lang('Edit', 'Bearbeiten') ?>
            </a>

            <!-- dropdown for deleting -->
            <div class="dropdown ml-auto">
                <button class="btn danger " data-toggle="dropdown">
                    <i class="ph ph-trash "></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <form action="<?= ROOTPATH ?>/crud/reports/delete" method="post">
                        <input type="hidden" name="id" value="<?= $report['_id'] ?>">
                        <button type="submit" class="text-danger btn block link">
                            <i class="ph ph-trash"></i>
                            <?= lang('Delete', 'Löschen') ?>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
<?php } ?>

<!-- new report template -->
<div class="box">
    <div class="content">
        <form action="<?= ROOTPATH ?>/crud/reports/create" method="post">

            <h3>
                <i class="ph ph-plus-circle text-success"></i>
                <?= lang('New report template', 'Neue Vorlage') ?>
            </h3>
            <div class="form-group">
                <label for="title"><?= lang('Title', 'Titel') ?></label>
                <input type="text" class="form-control" name="title" required>
            </div>
    </div>
    <div class="footer">
        <button type="submit" class="btn success">
            <i class="ph ph-plus"></i>
            <?= lang('Create', 'Erstellen') ?>
        </button>
    </div>
    </form>
</div>