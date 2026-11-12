<?php

/** Initiative overview with formatted metadata and optional objectives. */
include_once BASEPATH . '/php/Vocabulary.php';
$initiative = DB::doc2Arr($initiative);
$id = (string) $initiative['_id'];
$canEdit = $Settings->hasPermission('initiatives.edit');
$Vocabulary = new Vocabulary();
$objectiveTypes = $Vocabulary->getValues('objective-type');
$fields = Initiatives::fields($osiris);
$user = $osiris->persons->findOne(['username' => $_SESSION['username']]);
$fields = Initiatives::visibleFields($fields, DB::doc2Arr($user['roles'] ?? []));
$details = Initiatives::details($initiative, $fields);
$objectiveDrafts = $_SESSION['objective_form'][$id] ?? [];
unset($_SESSION['objective_form'][$id]);
$objectives = $osiris->objectives->find(['initiative' => $initiative['_id']], ['sort' => ['inactive' => 1, 'end_date' => 1, 'name' => 1]]);
?>
<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-compass"></i> <?= e(Initiatives::text($initiative['name'] ?? '')) ?>
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
        <?php if ($canEdit): ?>
            <a class="btn secondary float-right" href="#add-objective"><i class="ph ph-plus"></i> <?= translate('initiatives.add_objective') ?></a>
        <?php endif; ?>
        <h2><?= e(translate('initiatives.objectives')) ?></h2>
        <p class="text-muted"><?= translate('initiatives.objectives_description') ?></p>
        <?php $objectiveCount = 0;
        foreach ($objectives as $objective): $objectiveCount++; ?>
            <div class="box padded" id="objective-<?= e($objective['_id']) ?>">
                <?php if (!empty($objective['code'])): ?><span class="badge"><?= e($objective['code']) ?></span><?php endif; ?>
                <h4><?= e(Initiatives::text($objective['name'])) ?></h4>
                <?php if (!empty($objective['inactive'])): ?><span class="badge muted"><?= translate('initiatives.archived') ?></span><?php endif; ?>
                <?php if (Initiatives::isExpired($objective)): ?><span class="badge warning"><?= translate('initiatives.expired') ?></span><?php endif; ?>
                <?php if (!empty($objective['type'])): ?><p class="text-muted"><?= translate('initiatives.type') ?>: <?= e($Vocabulary->getValue('objective-type', $objective['type'])) ?></p><?php endif; ?>
                <?php if (!empty($objective['description'])): ?><p><?= nl2br(e($objective['description'])) ?></p><?php endif; ?>
                <p><?= e(Initiatives::period($objective)) ?></p>
                <?php if ($canEdit): ?>
                    <a class="btn secondary" href="#edit-objective-<?= e($objective['_id']) ?>"><i class="ph ph-edit"></i> <?= translate('action.edit') ?></a>
                    <div class="modal" id="edit-objective-<?= e($objective['_id']) ?>" tabindex="-1" role="dialog" aria-labelledby="edit-objective-<?= e($objective['_id']) ?>-title">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <a href="#close-modal" class="close" role="button" aria-label="<?= e(translate('action.cancel')) ?>"><span aria-hidden="true">&times;</span></a>
                                <h3 class="title" id="edit-objective-<?= e($objective['_id']) ?>-title"><?= translate('action.edit') ?>: <?= translate('initiatives.objective') ?></h3>
                                <form action="<?= ROOTPATH ?>/crud/initiatives/<?= e($id) ?>/objectives/update/<?= e($objective['_id']) ?>" method="post">
                                    <?php
                                    $objectiveForm = array_replace(DB::doc2Arr($objective), $objectiveDrafts[(string) $objective['_id']] ?? []);
                                    $objectiveFormId = 'edit-objective-' . $objective['_id'];
                                    include BASEPATH . '/pages/initiatives/objective-form.php';
                                    ?>
                                    <button class="btn secondary" type="submit"><?= translate('action.save') ?></button>
                                    <a class="btn" href="#close-modal"><?= translate('action.cancel') ?></a>
                                </form>
                            </div>
                        </div>
                    </div>
                    <form class="mt-10" action="<?= ROOTPATH ?>/crud/initiatives/<?= e($id) ?>/objectives/archive/<?= e($objective['_id']) ?>" method="post">
                        <input type="hidden" name="inactive" value="<?= empty($objective['inactive']) ? '1' : '0' ?>">
                        <button class="btn" type="submit"><?= translate(empty($objective['inactive']) ? 'initiatives.archive' : 'initiatives.restore') ?></button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($objectiveCount === 0): ?><p class="text-muted"><?= translate('initiatives.no_objectives') ?></p><?php endif; ?>
    </section>
</div>

<?php if ($canEdit): ?>
    <div class="modal" id="add-objective" tabindex="-1" role="dialog" aria-labelledby="add-objective-title">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <a href="#close-modal" class="close" role="button" aria-label="<?= e(translate('action.cancel')) ?>"><span aria-hidden="true">&times;</span></a>
                <h3 class="title" id="add-objective-title"><?= translate('initiatives.add_objective') ?></h3>
                <form action="<?= ROOTPATH ?>/crud/initiatives/<?= e($id) ?>/objectives/create" method="post">
                    <?php
                    $objectiveForm = array_replace([
                        'start_date' => valueFromDateArray($initiative['start_date'] ?? $initiative['start'] ?? null),
                        'end_date' => valueFromDateArray($initiative['end_date'] ?? $initiative['end'] ?? null),
                    ], $objectiveDrafts['new'] ?? []);
                    $objectiveFormId = 'new-objective';
                    include BASEPATH . '/pages/initiatives/objective-form.php';
                    ?>
                    <button class="btn secondary" type="submit"><?= translate('action.save') ?></button>
                    <a class="btn" href="#close-modal"><?= translate('action.cancel') ?></a>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
