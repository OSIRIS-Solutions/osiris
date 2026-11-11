<div class="container my-10">
    <p><?= e(lang($phaseDefinition['description'], $phaseDefinition['description_de'])) ?></p>
    <?php if (!empty($phaseDefinition['planned'])): ?>
        <div class="alert signal"><?= lang('This form prepares the future internal approval phase. It does not activate an approval process.', 'Dieses Formular bereitet die künftige interne Freigabephase vor. Es aktiviert noch keinen Freigabeprozess.') ?></div>
    <?php endif; ?>
    <?php if (!empty($unavailable)): ?>
        <div class="alert danger">
            <?= lang('These existing fields are not available in this phase. Remove or replace them before saving:', 'Diese vorhandenen Felder sind in dieser Phase nicht verfügbar. Entferne oder ersetze sie vor dem Speichern:') ?>
            <?= e(implode(', ', $unavailable)) ?>
        </div>
    <?php endif; ?>
</div>
<?php include BASEPATH . '/pages/admin/form-builder.php'; ?>
