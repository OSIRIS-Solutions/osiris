<div class="container">
    <h1><?= e($config->entityLabel) ?></h1>
    <p><?= lang('admin.data_field_overview') ?></p>
    <a class="btn secondary" href="<?= e($builderUrl) ?>"><i class="ph ph-edit"></i> <?= lang('Edit in Form Builder', 'Im Form-Builder bearbeiten') ?></a>
    <table class="table my-20">
        <thead><tr><th><?= lang('common.name') ?></th><th>ID</th><th><?= lang('System field', 'Systemfeld') ?></th></tr></thead>
        <tbody>
        <?php foreach ($config->modules as $id => $module): ?>
            <tr><td><?= e(lang($module['name'], $module['name_de'] ?? null)) ?></td><td><code><?= e($id) ?></code></td><td><?= isset($config->systemFields[$id]) ? lang('common.yes') : '' ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
