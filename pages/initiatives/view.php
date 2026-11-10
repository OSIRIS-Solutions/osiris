<?php

/** Initiative overview with formatted metadata and optional objectives. */
$initiative = DB::doc2Arr($initiative);
$id = (string) $initiative['_id'];
$canEdit = $Settings->hasPermission('initiatives.edit');
$fields = Initiatives::fields($osiris);
$user = $osiris->persons->findOne(['username' => $_SESSION['username']]);
$fields = Initiatives::visibleFields($fields, DB::doc2Arr($user['roles'] ?? []));
$details = Initiatives::details($initiative, $fields);
$objectives = $osiris->objectives->find(['initiative' => $initiative['_id']], ['sort' => ['inactive' => 1, 'end_date' => 1, 'name' => 1]]);
?>
<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-lightbulb"></i> <?= e(Initiatives::text($initiative['name'] ?? '')) ?>
    </h1>

    <?php if (!empty($initiative['inactive'])): ?><span class="badge muted"><?= translate('initiatives.archived') ?></span><?php endif; ?>
    <?php if (Initiatives::isExpired($initiative)): ?><span class="badge warning"><?= translate('initiatives.expired') ?></span><?php endif; ?>

    <div class="btn-toolbar my-20">
        <a class="btn" href="<?= ROOTPATH ?>/initiatives"><i class="ph ph-arrow-left"></i> <?= e(translate('common.initiatives')) ?></a>
        <?php if ($canEdit): ?>
            <a class="btn secondary" href="<?= ROOTPATH ?>/initiatives/edit/<?= e($id) ?>"><i class="ph ph-edit"></i> <?= translate('action.edit') ?></a>
            <form action="<?= ROOTPATH ?>/crud/initiatives/archive/<?= e($id) ?>" method="post">
                <input type="hidden" name="inactive" value="<?= empty($initiative['inactive']) ? '1' : '0' ?>">
                <button class="btn" type="submit"><?= translate(empty($initiative['inactive']) ? 'initiatives.archive' : 'initiatives.restore') ?></button>
            </form>
        <?php endif; ?>
    </div>

    <?php foreach ($details['texts'] as $text): ?>
        <section class="my-20">
            <h3><?= e($text['label']) ?></h3>
            <div><?= $text['value'] ?></div>
        </section>
    <?php endforeach; ?>

    <?php if ($details['metadata']): ?>
        <table class="table">
            <tbody>
                <?php foreach ($details['metadata'] as $field): ?>
                    <tr>
                        <td>
                            <span class="key"><?= e($field['label']) ?></span>
                            <?= $field['value'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <section id="objectives" class="mt-20">
        <h2><?= e(translate('initiatives.objectives')) ?></h2>
        <p class="text-muted"><?= translate('initiatives.objectives_description') ?></p>
        <?php $objectiveCount = 0;
        foreach ($objectives as $objective): $objectiveCount++; ?>
            <div class="box padded">
                <h4><?= e(Initiatives::text($objective['name'])) ?></h4>
                <?php if (!empty($objective['inactive'])): ?><span class="badge muted"><?= translate('initiatives.archived') ?></span><?php endif; ?>
                <?php if (Initiatives::isExpired($objective)): ?><span class="badge warning"><?= translate('initiatives.expired') ?></span><?php endif; ?>
                <p><?= translate('initiatives.deadline') ?>: <?= e($objective['end_date'] ?? translate('common.open_end')) ?></p>
                <?php if ($canEdit): ?>
                    <details>
                        <summary><?= translate('action.edit') ?></summary>
                        <form class="mt-10" action="<?= ROOTPATH ?>/crud/initiatives/<?= e($id) ?>/objectives/update/<?= e($objective['_id']) ?>" method="post">
                            <div class="form-group">
                                <label class="required" for="objective-name-<?= e($objective['_id']) ?>"><?= translate('initiatives.name') ?></label>
                                <input id="objective-name-<?= e($objective['_id']) ?>" class="form-control" name="values[name]" value="<?= e($objective['name']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="objective-end-<?= e($objective['_id']) ?>"><?= translate('initiatives.deadline') ?></label>
                                <input id="objective-end-<?= e($objective['_id']) ?>" class="form-control" type="date" name="values[end_date]" value="<?= e($objective['end_date'] ?? '') ?>">
                            </div>
                            <button class="btn secondary" type="submit"><?= translate('action.save') ?></button>
                        </form>
                    </details>
                    <form class="mt-10" action="<?= ROOTPATH ?>/crud/initiatives/<?= e($id) ?>/objectives/archive/<?= e($objective['_id']) ?>" method="post">
                        <input type="hidden" name="inactive" value="<?= empty($objective['inactive']) ? '1' : '0' ?>">
                        <button class="btn" type="submit"><?= translate(empty($objective['inactive']) ? 'initiatives.archive' : 'initiatives.restore') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($objectiveCount === 0): ?><p class="text-muted"><?= translate('initiatives.no_objectives') ?></p><?php endif; ?>
        <?php if ($canEdit): ?>
            <details class="box padded">
                <summary><?= translate('initiatives.add_objective') ?></summary>
                <form class="mt-10" action="<?= ROOTPATH ?>/crud/initiatives/<?= e($id) ?>/objectives/create" method="post">
                    <div class="form-group">
                        <label class="required" for="objective-name"><?= translate('initiatives.name') ?></label>
                        <input id="objective-name" class="form-control" name="values[name]" required>
                    </div>
                    <div class="form-group">
                        <label for="objective-end"><?= translate('initiatives.deadline') ?></label>
                        <input id="objective-end" class="form-control" type="date" name="values[end_date]">
                    </div>
                    <button class="btn secondary" type="submit"><?= translate('action.save') ?></button>
                </form>
            </details>
        <?php endif; ?>
    </section>
</div>