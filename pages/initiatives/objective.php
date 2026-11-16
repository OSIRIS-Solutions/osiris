<?php
require_once BASEPATH . '/php/Vocabulary.php';
$Vocabulary = new Vocabulary();
$association = ['objectives' => (string) $objective['_id']];
include BASEPATH . '/pages/initiatives/content-data.php';
?>
<div data-initiative-page>
    <div class="text-muted">
        <?= translate('common.initiative') ?>:
        <a href="<?= ROOTPATH ?>/initiatives/view/<?= e($initiative['_id']) ?>#objectives"><i class="ph ph-compass"></i> <?= e(Initiatives::text($initiative['name'])) ?></a>
        <i class="ph ph-caret-right" aria-hidden="true"></i> <?= translate('initiatives.objective') ?> <?= e($objective['code'] ?? '') ?>
    </div>
    <h1><i class="ph-duotone ph-target"></i> <?= e(Initiatives::text($objective['name'])) ?></h1>
    <p>
        <span class="badge <?= !empty($objective['inactive']) ? 'muted' : 'primary' ?>"><?= translate(!empty($objective['inactive']) ? 'initiatives.archived' : (Initiatives::isExpired($objective) ? 'initiatives.expired' : 'initiatives.current')) ?></span>
        <span class="text-muted ml-10"><?= e(Initiatives::period($objective)) ?></span>
    </p>
    <div class="btn-toolbar my-20">
        <a class="btn" href="<?= ROOTPATH ?>/initiatives/view/<?= e($initiative['_id']) ?>#objectives"><i class="ph ph-arrow-left"></i> <?= translate('initiatives.back_to_initiative') ?></a>
        <?php if ($Settings->hasPermission('initiatives.edit')): ?><a class="btn secondary" href="<?= ROOTPATH ?>/initiatives/objectives/edit/<?= e($objective['_id']) ?>"><i class="ph ph-edit"></i> <?= translate('action.edit') ?></a><?php endif; ?>
    </div>
    <div class="row row-eq-spacing">
        <?php if (!empty($objective['description'])): ?>
        <div class="col-lg-8">
            <?php if (!empty($objective['description'])): ?><div class="mb-20"><?= nl2br(e(Initiatives::text($objective['description']))) ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="<?= !empty($objective['description']) ? 'col-lg-4' : 'col' ?>">
            <table class="table"><tbody>
                <?php if (!empty($objective['code'])): ?><tr><td><span class="key"><?= translate('initiatives.identifier') ?></span><?= e($objective['code']) ?></td></tr><?php endif; ?>
                <?php if (!empty($objective['type'])): ?><tr><td><span class="key"><?= translate('initiatives.type') ?></span><?= e($Vocabulary->getValue('objective-type', $objective['type'])) ?></td></tr><?php endif; ?>
            </tbody></table>
        </div>
    </div>
    <section id="contents" class="mt-20">
        <?php include BASEPATH . '/pages/initiatives/content-tabs.php'; ?>
        <?php include BASEPATH . '/pages/initiatives/linked-content.php'; ?>
    </section>
</div>
