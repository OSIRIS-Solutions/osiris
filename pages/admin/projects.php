

<h1>
    <?=lang('Project Settings', 'Projekt-Einstellungen')?>
</h1>


<div class="btn-toolbar">
    <a class="btn" href="<?= ROOTPATH ?>/admin/project/new">
        <i class="ph ph-plus-circle"></i>
        <?= lang('Add category', 'Kategorie hinzufügen') ?>
    </a>

    <a class="btn" href="<?= ROOTPATH ?>/admin/project/vocabulary">
        <i class="ph ph-list"></i>
        <?= lang('Vocabulary', 'Vokabular') ?>
    </a>
</div>





<?php
$types = $osiris->adminProjects->find();
foreach ($types as $type) { ?>
    <div class="box px-20 py-10 mb-10">
        <h3 class="title" style="color: <?= $type['color'] ?? 'inherit' ?>">
            <i class="ph ph-<?= $type['icon'] ?? 'placeholder' ?> mr-10"></i>
            <?= lang($type['name'], $type['name_de'] ?? $type['name']) ?>
        </h3>
        <a href="<?= ROOTPATH ?>/admin/projects/<?= $type['id'] ?>">
            <?= lang('Edit', 'Bearbeiten') ?>
        </a>
    </div>
<?php } ?>

