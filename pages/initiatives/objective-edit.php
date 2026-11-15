<?php
require_once BASEPATH . '/php/Vocabulary.php';
$Vocabulary = new Vocabulary();
$objectiveTypes = $Vocabulary->getValues('objective-type');
$editing = isset($objective['_id']);
$parentId = (string) $initiative['_id'];
$objectiveFormId = $editing ? 'edit-objective' : 'new-objective';
$objectiveForm = $editing ? DB::doc2Arr($objective) : [
    'start_date' => valueFromDateArray($initiative['start_date'] ?? $initiative['start'] ?? null),
    'end_date' => valueFromDateArray($initiative['end_date'] ?? $initiative['end'] ?? null),
];
$draftKey = $editing ? (string) $objective['_id'] : 'new';
$objectiveForm = array_replace($objectiveForm, $_SESSION['objective_form'][$parentId][$draftKey] ?? []);
unset($_SESSION['objective_form'][$parentId][$draftKey]);
$formAction = ROOTPATH . '/crud/initiatives/' . $parentId . '/objectives/' . ($editing ? 'update/' . $objective['_id'] : 'create');
$backUrl = ROOTPATH . ($editing ? '/initiatives/objectives/view/' . $objective['_id'] : '/initiatives/view/' . $parentId . '#objectives');
?>
<div class="container w-800 mw-full">
    <div class="text-muted"><?= translate('common.initiative') ?>: <a href="<?= ROOTPATH ?>/initiatives/view/<?= e($parentId) ?>#objectives"><i class="ph ph-compass"></i> <?= e(Initiatives::text($initiative['name'])) ?></a></div>
    <h1><i class="ph-duotone ph-target"></i> <?= $editing ? translate('action.edit') . ': ' . translate('initiatives.objective') : translate('initiatives.add_objective') ?></h1>
    <?php if ($editing): ?><h2 class="subtitle"><?= e(Initiatives::text($objective['name'])) ?></h2><?php endif; ?>
    <form action="<?= e($formAction) ?>" method="post" id="objective-form">
        <?php include BASEPATH . '/pages/initiatives/objective-form.php'; ?>
        <div class="btn-toolbar mt-20">
            <button class="btn success" type="submit"><i class="ph ph-floppy-disk"></i> <?= translate('action.save') ?></button>
            <a class="btn" href="<?= e($backUrl) ?>"><?= translate('action.cancel') ?></a>
        </div>
    </form>
    <?php if ($editing):
        $archiveAction = ROOTPATH . '/crud/initiatives/' . $parentId . '/objectives/archive/' . $objective['_id'];
        $archiveName = Initiatives::text($objective['name']);
        $archiveInactive = !empty($objective['inactive']);
        include BASEPATH . '/pages/initiatives/archive-controls.php';
    endif; ?>
</div>
