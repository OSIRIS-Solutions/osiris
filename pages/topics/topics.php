<?php

/**
 * The overview of all topics
 * Created in cooperation with bicc
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.3.8
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$topics  = $osiris->topics->find();
?>


<h1>
    <i class="ph ph-puzzle-piece"></i>
    <?= lang('Research Topics', 'Forschungsbereiche') ?>
</h1>

<?php if ($Settings->hasPermission('topics.edit')) { ?>
    <a href="<?= ROOTPATH ?>/topics/new"><?= lang('Add new topic', 'Neuen Bereich hinzufügen') ?></a>
<?php } ?>

<div id="topics">
    <?php foreach ($topics as $topic) { ?>
        <div class="box padded topic" style="--topic-color: <?= $topic['color'] ?? '#333333' ?>">
            <h4 class="title">
                <span class="topic-icon"></span>
                <a href="<?= ROOTPATH ?>/topics/view/<?= $topic['_id'] ?>" class="colorless"><?= $topic['name'] ?></a>
            </h4>
            <p class="font-size-12 text-muted">
                <?= get_preview(lang($topic['description'], $topic['description_de'] ?? null), 400) ?>
            </p>
            <?php if ($Settings->hasPermission('topics.edit')) { ?>
                <a class="btn" href="<?= ROOTPATH ?>/topics/edit/<?= $topic['_id'] ?>">
                    <i class="ph ph-edit"></i>
                    <?= lang('Edit', 'Bearbeiten') ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>