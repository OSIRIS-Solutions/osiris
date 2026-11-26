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
<script src="<?= ROOTPATH ?>/js/organizations.js?v=<?= OSIRIS_BUILD ?>"></script>
<div class="container w-800 mw-full">
    <h1>
        <i class="ph-duotone ph-compass"></i>
        <?= e(t($editing ? 'action.edit' : 'common.new')) ?>: <?= e(t('common.initiative')) ?>
    </h1>

    <?php if ($editing): ?><h2 class="subtitle"><?= e(Initiatives::text($initiative['name'] ?? '')) ?></h2><?php endif; ?>

    <form action="<?= e($formaction) ?>" method="post" id="initiative-form">
        <div id="data-modules" class="row row-eq-spacing">
            <?php $Modules->render_form($fields); ?>
        </div>
        <?php $publicValue = $form['public'] ?? false;
        if ($Settings->featureEnabled('portal')): ?>
            <div class="form-group my-20">
                <input type="hidden" name="values[public]" value="false">
                <div class="custom-checkbox">
                    <input type="checkbox" name="values[public]" id="initiative-portfolio-public" value="true" <?= filter_var($publicValue ?? false, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>>
                    <label for="initiative-portfolio-public"><?= t('initiatives.portfolio_visibility') ?></label>
                </div>
                <small class="text-muted"><?= t('initiatives.portfolio_visibility_hint') ?></small>
            </div>
        <?php endif; ?>
        <button class="btn success" type="submit">
            <i class="ph ph-floppy-disk"></i> <?= t('action.save') ?>
        </button>
        <a class="btn" href="<?= ROOTPATH ?>/initiatives<?= $editing ? '/view/' . e($form['_id']) : '' ?>">
            <?= t('action.cancel') ?>
        </a>
    </form>
    <?php if ($editing):
        $archiveAction = ROOTPATH . '/crud/initiatives/archive/' . $form['_id'];
        $archiveName = $initiativeName ?? Initiatives::text($initiative['name'] ?? '');
        $archiveInactive = !empty($initiative['inactive']);
        include BASEPATH . '/pages/initiatives/archive-controls.php';
    endif; ?>
</div>