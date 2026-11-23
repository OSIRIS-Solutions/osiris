<nav class="pills">
    <a href="<?= ROOTPATH ?>/admin/general" class="btn <?= $page == 'general' ? 'active' : '' ?>"><?= t('common.general') ?></a>
    <a href="<?= ROOTPATH ?>/admin/features" class="btn <?= $page == 'features' ? 'active' : '' ?>"><?= t('admin.features') ?></a>
    <a href="<?= ROOTPATH ?>/admin/activities" class="btn <?= $page == 'activities' ? 'active' : '' ?>"><?= t('common.activities') ?></a>
    <a href="<?= ROOTPATH ?>/admin/roles" class="btn <?= $page == 'roles' ? 'active' : '' ?>"><?= t('common.roles') ?></a>
</nav>