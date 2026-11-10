<?php
/** Create or edit an initiative using the configured form. */
include_once BASEPATH . '/header-editor.php';
include_once BASEPATH . '/php/Modules.php';

$form = DB::doc2Arr($initiative);
// Failed submissions keep raw date inputs; the renderer reads their companion fields.
foreach (['start', 'end'] as $dateKey) {
    if (is_string($form[$dateKey] ?? null)) $form[$dateKey . '_date'] = $form[$dateKey];
}
$editing = isset($form['_id']);
$formaction = ROOTPATH . '/crud/initiatives/' . ($editing ? 'update/' . $form['_id'] : 'create');
$fields = Initiatives::fields($osiris);
$Modules = new Modules($form, false);
// Apply configured labels and other properties, including in module helper methods.
$Modules->setFields($fields);
?>
<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-lightbulb"></i>
        <?= e(translate($editing ? 'action.edit' : 'common.new')) ?>: <?= e(translate('common.initiative')) ?>
    </h1>

    <form action="<?= e($formaction) ?>" method="post" id="initiative-form">
        <div id="data-modules" class="row row-eq-spacing">
            <?php $Modules->render_form($fields); ?>
        </div>
        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i> <?= translate('action.save') ?>
        </button>
        <a class="btn" href="<?= ROOTPATH ?>/initiatives<?= $editing ? '/view/' . e($form['_id']) : '' ?>">
            <?= translate('action.cancel') ?>
        </a>
    </form>
</div>
