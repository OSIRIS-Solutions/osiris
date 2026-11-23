<div class="container">
    <h1><?= e($config->entityLabel) ?></h1>
    <p><?= t('admin.data_field_overview') ?></p>
    <a class="btn secondary" href="<?= e($builderUrl) ?>"><i class="ph ph-edit"></i> <?= t('admin.edit_in_form_builder') ?></a>
    <table class="table my-20">
        <thead><tr><th><?= t('common.name') ?></th><th>ID</th><th><?= t('admin.system_field') ?></th></tr></thead>
        <tbody>
        <?php foreach ($config->modules as $id => $module): ?>
            <tr><td><?= e(lang($module['name'], $module['name_de'] ?? null)) ?></td><td><code><?= e($id) ?></code></td><td><?= isset($config->systemFields[$id]) ? t('common.yes') : '' ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
