<?php

/** All initiatives, with explicit access to archived and expired entries. */
$status = $_GET['status'] ?? 'current';

$objectives = Initiatives::sortCurrentFirst($osiris->objectives->find()->toArray());
$initiatives = Initiatives::sortCurrentFirst($osiris->initiatives->find()->toArray());
$initiativeNames = [];
$objectiveCounts = [];
foreach ($initiatives as $entry) $initiativeNames[(string) $entry['_id']] = Initiatives::text($entry['name'] ?? '');
foreach ($objectives as $entry) {
    $parent = (string) $entry['initiative'];
    $objectiveCounts[$parent] = ($objectiveCounts[$parent] ?? 0) + 1;
}
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
<div>
    <h1><i class="ph-duotone ph-compass"></i> <?= e(t('common.initiatives')) ?></h1>
    <div class="btn-toolbar">
        <a class="btn" href="<?= ROOTPATH ?>/initiatives/statistics<?= $status === 'objectives' ? '?view=objectives' : '' ?>"><i class="ph ph-chart-line-up" aria-hidden="true"></i> <?= t('common.statistics') ?></a>
        <?php if ($Settings->hasPermission('initiatives.edit')): ?>
            <a class="btn secondary" href="<?= ROOTPATH ?>/initiatives/new">
                <i class="ph ph-plus"></i> <?= t('common.new') ?>
            </a>
        <?php endif; ?>
        <?php if ($Settings->hasPermission('admin.see')): ?>
            <a class="btn light" href="<?= ROOTPATH ?>/admin/initiatives/fields">
                <i class="ph ph-gear"></i> <?= t('initiatives.configure_form') ?>
            </a>
        <?php endif; ?>
    </div>

    <nav class="pills my-20">
        <?php foreach (['current', 'past', 'all'] as $filter): ?>
            <a class="btn <?= $status === $filter ? 'active' : '' ?>" href="<?= ROOTPATH ?>/initiatives?status=<?= $filter ?>">
                <?= t('initiatives.' . $filter) ?>
            </a>
        <?php endforeach; ?>
        <?php if (!empty($objectives)): ?>
        <span class="divider"></span>
        <a class="btn <?= $status === 'objectives' ? 'active' : '' ?>" href="<?= ROOTPATH ?>/initiatives?status=objectives">
            <?= t('initiatives.objectives') ?>
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
                    <i class="ph-duotone <?= $status === 'objectives' ? 'ph-target' : 'ph-compass' ?>"></i>
                    <a class="colorless" href="<?= ROOTPATH ?>/initiatives/<?= $status === 'objectives' ? 'objectives/' : '' ?>view/<?= e($data['_id']) ?>">
                        <?= e(Initiatives::text($data['name'] ?? '')) ?>
                    </a>
                </h4>
                <?php if ($archived): ?><span class="badge muted"><?= t('initiatives.archived') ?></span><?php endif; ?>
                <?php if ($expired): ?><span class="badge warning"><?= t('initiatives.expired') ?></span><?php endif; ?>
                <?php if ($status === 'objectives'): ?>
                    <p><a href="<?= ROOTPATH ?>/initiatives/view/<?= e($data['initiative']) ?>#objectives"><i class="ph ph-compass"></i> <?= e($initiativeNames[(string) $data['initiative']] ?? '') ?></a></p>
                <?php elseif (!empty($data['title'])): ?>
                    <p class="font-weight-bold"><?= e(Initiatives::text($data['title'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($data['description'])): ?><p class="text-muted"><?= e(get_preview(strip_tags(Initiatives::text($data['description'])), 250)) ?></p><?php endif; ?>
                <div class="text-muted">
                    <?= e(Initiatives::period($data)) ?>
                </div>
                <div class="mt-10">
                    <a href="<?= ROOTPATH ?>/initiatives/<?= $status === 'objectives' ? 'objectives/' : '' ?>view/<?= e($data['_id']) ?>"><?= t('topics.view_details') ?> <i class="ph ph-arrow-right"></i></a>
                    <?php if ($status !== 'objectives' && !empty($objectiveCounts[(string) $data['_id']])): ?>
                        <a class="ml-20" href="<?= ROOTPATH ?>/initiatives/view/<?= e($data['_id']) ?>#objectives"><i class="ph ph-target"></i> <?= $objectiveCounts[(string) $data['_id']] ?> <?= t('initiatives.objectives') ?></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($shown === 0): ?><p class="text-muted"><?= t('initiatives.no_entries') ?></p><?php endif; ?>
    </div>

</div>