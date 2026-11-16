<?php
$initiative = DB::doc2Arr($initiative);
$id = (string) $initiative['_id'];
$canEdit = $Settings->hasPermission('initiatives.edit');
$fields = Initiatives::fields($osiris);
$user = $osiris->persons->findOne(['username' => $_SESSION['username']]);
$fields = Initiatives::visibleFields($fields, DB::doc2Arr($user['roles'] ?? []));
$titleVisible = array_intersect(['title', 'title-loc'], array_column($fields, 'id'));
$detailFields = array_values(array_filter($fields, fn($field) => !in_array($field['id'] ?? '', ['title', 'title-loc', 'topics', 'date-range-ongoing'], true)));
$details = Initiatives::details($initiative, $detailFields);
$objectives = Initiatives::sortCurrentFirst($osiris->objectives->find(['initiative' => (string) $initiative['_id']])->toArray());
$association = ['initiatives' => (string) $initiative['_id']];
$directTopics = $Settings->featureEnabled('topics') && in_array('topics', array_column($fields, 'id'), true) ? DB::doc2Arr($initiative['topics'] ?? []) : [];
include BASEPATH . '/pages/initiatives/content-data.php';
?>
<div data-initiative-page>
    <a class="" href="<?= ROOTPATH ?>/initiatives"><i class="ph ph-arrow-left"></i> <?= translate('common.initiatives') ?></a>
    <!-- <div class="text-muted"><i class="ph ph-compass"></i> <?= translate('common.initiative') ?></div> -->
    <h1><i class="ph-duotone ph-compass"></i> <?= e(Initiatives::text($initiative['name'] ?? '')) ?></h1>
    <?php if ($titleVisible && !empty($initiative['title'])): ?><h2 class="subtitle"><?= e(Initiatives::text($initiative['title'])) ?></h2><?php endif; ?>
    <p>
        <span class="badge <?= !empty($initiative['inactive']) ? 'muted' : 'primary' ?>"><?= translate(!empty($initiative['inactive']) ? 'initiatives.archived' : (Initiatives::isExpired($initiative) ? 'initiatives.expired' : 'initiatives.current')) ?></span>
        <span class="text-muted ml-10"><?= e(Initiatives::period($initiative)) ?></span>
    </p>
    <?php if ($canEdit): ?>
        <div class="btn-toolbar my-20">
            <a class="btn secondary" href="<?= ROOTPATH ?>/initiatives/edit/<?= e($id) ?>"><i class="ph ph-edit"></i> <?= translate('action.edit') ?></a>
        </div>
    <?php endif; ?>

    <?php if ($details['metadata'] || $directTopics || $details['texts']): ?>
    <section id="details" class="py-5">
        <table class="table">
            <tbody>
                <?php foreach ($details['metadata'] as $field): ?>
                    <tr>
                        <td><span class="key"><?= e($field['label']) ?></span><?= $field['value'] ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($directTopics): ?>
                    <tr>
                        <td><span class="key"><?= e($Settings->topicLabel()) ?></span><?= $Settings->printTopics($directTopics, '', false) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($details['texts']): ?>
                    <tr>
                        <td>
                            <?php foreach ($details['texts'] as $text): ?>
                                <h3 class="mt-10"><?= e($text['label']) ?></h3>
                                <div><?= $text['value'] ?></div>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>

    <section id="objectives" class="py-5">
        <?php if ($canEdit): ?><a class="btn primary float-right mt-20" href="<?= ROOTPATH ?>/initiatives/<?= e($id) ?>/objectives/new"><i class="ph ph-plus"></i> <?= translate('initiatives.add_objective') ?></a><?php endif; ?>
        <h2><i class="ph-duotone ph-target"></i> <?= translate('initiatives.objectives') ?> <small class="badge primary"><?= count($objectives) ?></small></h2>
        <?php foreach ($objectives as $objective): ?>
            <div class="box padded" id="objective-<?= e($objective['_id']) ?>">
                <?php if (!empty($objective['code'])): ?><span class="badge"><?= e($objective['code']) ?></span><?php endif; ?>
                <h4 class="title"><i class="ph ph-target"></i> <a class="colorless" href="<?= ROOTPATH ?>/initiatives/objectives/view/<?= e($objective['_id']) ?>"><?= e(Initiatives::text($objective['name'])) ?></a></h4>
                <?php if (!empty($objective['inactive'])): ?><span class="badge muted"><?= translate('initiatives.archived') ?></span><?php endif; ?>
                <?php if (Initiatives::isExpired($objective)): ?><span class="badge warning"><?= translate('initiatives.expired') ?></span><?php endif; ?>
                <div class="text-muted"><?= e(Initiatives::period($objective)) ?></div>
            </div>
        <?php endforeach; ?>
        <?php if (!$objectives): ?><p class="text-muted"><?= translate('initiatives.no_objectives') ?></p><?php endif; ?>
    </section>

    <section id="contents" class="py-5">
        <?php include BASEPATH . '/pages/initiatives/content-tabs.php'; ?>
        <?php include BASEPATH . '/pages/initiatives/linked-content.php'; ?>
    </section>
</div>