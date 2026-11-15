<section class="box padded mt-20">
    <h3 class="title"><?= translate('common.status') ?></h3>
    <p class="text-muted"><?= translate('initiatives.archive_hint') ?></p>
    <div class="dropdown">
        <button class="btn <?= $archiveInactive ? 'secondary' : 'text-danger' ?>" type="button" data-toggle="dropdown" aria-haspopup="true">
            <i class="ph ph-archive"></i> <?= translate($archiveInactive ? 'initiatives.restore' : 'initiatives.archive') ?>
        </button>
        <div class="dropdown-menu">
            <form class="content" action="<?= e($archiveAction) ?>" method="post">
                <p><?= e(translate($archiveInactive ? 'initiatives.confirm_restore' : 'initiatives.confirm_archive', ['name' => $archiveName])) ?></p>
                <input type="hidden" name="inactive" value="<?= $archiveInactive ? '0' : '1' ?>">
                <button class="btn <?= $archiveInactive ? 'secondary' : 'danger' ?>" type="submit"><?= translate($archiveInactive ? 'initiatives.restore' : 'initiatives.archive') ?></button>
            </form>
        </div>
    </div>
</section>
