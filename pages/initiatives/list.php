<?php

/** All initiatives, with explicit access to archived and expired entries. */
$status = $_GET['status'] ?? 'current';

$objectives = $osiris->objectives->find([], ['sort' => ['start' => -1]])->toArray();
$initiatives = $osiris->initiatives->find([], ['sort' => ['start' => -1]])->toArray();
if (!in_array($status, ['current', 'past', 'all', 'objectives'], true)) $status = 'current';
if ($status === 'objectives') {
    $datas = $objectives;
} else {
    $datas = $initiatives;
}
$shown = 0;
?>
<style>
    .divider {
        display: inline-block;
        width: 1px;
        background-color: var(--border-color);
        margin: 0 10px;
        height: 2rem;
        vertical-align: middle;
    }
</style>
<div class="container w-800 mw-full">
    <h1><i class="ph-duotone ph-lightbulb"></i> <?= e(translate('common.initiatives')) ?></h1>
    <div class="btn-toolbar">
        <?php if ($Settings->hasPermission('initiatives.edit')): ?>
            <a class="btn secondary" href="<?= ROOTPATH ?>/initiatives/new">
                <i class="ph ph-plus"></i> <?= translate('common.new') ?>
            </a>
        <?php endif; ?>
        <?php if ($Settings->hasPermission('admin.see')): ?>
            <a class="btn light" href="<?= ROOTPATH ?>/admin/initiatives/fields">
                <i class="ph ph-gear"></i> <?= translate('initiatives.configure_form') ?>
            </a>
        <?php endif; ?>
    </div>

    <nav class="pills my-20">
        <?php foreach (['current', 'past', 'all'] as $filter): ?>
            <a class="btn <?= $status === $filter ? 'active' : '' ?>" href="<?= ROOTPATH ?>/initiatives?status=<?= $filter ?>">
                <?= translate('initiatives.' . $filter) ?>
            </a>
        <?php endforeach; ?>
        <?php if (!empty($objectives)): ?>
        <span class="divider"></span>
        <a class="btn <?= $status === 'objectives' ? 'active' : '' ?>" href="<?= ROOTPATH ?>/initiatives?status=objectives">
            <?= translate('initiatives.objectives') ?>
        </a>
        <?php endif; ?>
    </nav>
    <div id="initiatives">
        <?php foreach ($datas as $data):
            $archived = !empty($data['inactive']);
            $expired = Initiatives::isExpired($data);
            $past = $archived || $expired;
            if (($status === 'current' && $past) || ($status === 'past' && !$past)) continue;
            $shown++;
        ?>
            <div class="box padded">
                <?php if ($status === 'objectives' && !empty($data['code'])): ?><span class="badge"><?= e($data['code']) ?></span><?php endif; ?>
                <h4 class="title">
                    <a href="<?= ROOTPATH ?>/initiatives/view/<?= e($status === 'objectives' ? $data['initiative'] : $data['_id']) ?><?= $status === 'objectives' ? '#objective-' . e($data['_id']) : '' ?>">
                        <?= e(Initiatives::text($data['name'] ?? '')) ?>
                    </a>
                </h4>
                <?php if ($archived): ?><span class="badge muted"><?= translate('initiatives.archived') ?></span><?php endif; ?>
                <?php if ($expired): ?><span class="badge warning"><?= translate('initiatives.expired') ?></span><?php endif; ?>
                <div class="text-muted">
                    <?= e(Initiatives::period($data)) ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($shown === 0): ?><p class="text-muted"><?= translate('initiatives.no_entries') ?></p><?php endif; ?>
    </div>

</div>