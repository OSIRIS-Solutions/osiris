<div class="container my-10">
    <p><?= e(lang($phaseDefinition['description'], $phaseDefinition['description_de'])) ?></p>
    <?php if (!empty($phaseDefinition['planned'])): ?>
        <div class="alert signal"><?= t('admin.this_form_prepares_the_future_internal_approval_phase_it_does_not') ?></div>
    <?php endif; ?>
    <?php if (!empty($unavailable)): ?>
        <div class="alert danger">
            <?= t('admin.these_existing_fields_are_not_available_in_this_phase_remove_or_re') ?>
            <?= e(implode(', ', $unavailable)) ?>
        </div>
    <?php endif; ?>
</div>
<?php include BASEPATH . '/pages/admin/form-builder.php'; ?>
