<nav class="pills">
    <a href="<?= ROOTPATH ?>/admin/general" class="btn <?= $page == 'general' ? 'active' : '' ?>"><?= lang('General', 'Allgemein') ?></a>
    <a href="<?= ROOTPATH ?>/admin/features" class="btn <?= $page == 'features' ? 'active' : '' ?>"><?= lang('Features', 'Funktionen') ?></a>
    <a href="<?= ROOTPATH ?>/admin/activities" class="btn <?= $page == 'activities' ? 'active' : '' ?>"><?= lang('common.activities') ?></a>
    <a href="<?= ROOTPATH ?>/admin/roles" class="btn <?= $page == 'roles' ? 'active' : '' ?>"><?= lang('common.roles') ?></a>
</nav>