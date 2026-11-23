<?php

if (!$Settings->featureEnabled('news-editorial')) return;

/** Only users with the editorial right see draft texts and editorial controls. */
if (!$Settings->hasPermission('news.editorial')) return;
require_once BASEPATH . '/php/NewsEditorial.php';
require_once BASEPATH . '/php/NewsReview.php';
$editorialConfiguration = $Settings->getNewsOutlets();
if (!NewsEditorial::workflowAvailable($editorialConfiguration, $news)) return;
include_once BASEPATH . '/header-editor.php';
include_once BASEPATH . '/components/news-editor-style.php';
$editorialContributions = NewsEditorial::contributions($news);
$editorialRequests = array_column(NewsEditorial::requests($news), null, 'outlet_id');
$editorialOutlets = array_column($editorialConfiguration['outlets'], null, 'id');
$editorialInternalOnly = NewsEditorial::internalOnly($news);
$editorialLanguages = ['en', 'de'];
$editorialOriginal = [];
foreach (['title', 'teaser', 'content'] as $field) {
    foreach (['en' => '', 'de' => '_de'] as $language => $suffix) {
        $value = (string) ($news[$field . $suffix] ?? '');
        $editorialOriginal[$field][$language] = $field === 'content' ? sanitize_rich_text($value) : $value;
    }
}
$editorialAction = ROOTPATH . '/crud/news/editorial/' . e($news['_id']);
$editorialHiddenFields = static function (?string $outletId = null, string $scope = 'review') use ($news) { ?>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token('news')) ?>">
    <input type="hidden" name="edit_token" value="<?= e(NewsEditorial::editToken($news, $outletId === null ? $scope : 'outlet:' . $outletId)) ?>">
    <?php if ($outletId !== null) { ?><input type="hidden" name="editorial[outlet_id]" value="<?= e($outletId) ?>"><?php } ?>
<?php }; ?>

<section id="news-editorial" class="news-workflow mt-20">
    <script type="application/json" id="news-editorial-original"><?= json_encode($editorialOriginal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
    <div class="news-section-heading">
    <h2><i class="ph-duotone ph-pencil-line" aria-hidden="true"></i> <?= t('news.editorial_team') ?></h2>
        <a class="btn small" href="<?= ROOTPATH ?>/news/editorial"><i class="ph ph-arrow-left" aria-hidden="true"></i> <?= t('news.editorial_overview') ?></a>
    </div>
    <div class="alert warning mt-10" id="news-unsaved-warning" role="alert" hidden>
        <p><?= t('news.there_are_other_unsaved_changes_continuing_will_discard_those_chan') ?></p>
        <div class="btn-toolbar">
            <button type="button" class="btn" data-news-keep-editing><?= t('activities.continue_editing') ?></button>
            <button type="button" class="btn danger" data-news-discard-changes><?= t('news.discard_other_changes_and_continue') ?></button>
        </div>
    </div>
    <p class="text-muted"><?= t('news.prepare_an_individual_contribution_for_each_outlet_the_original_co') ?></p>
    <?php if ($editorialInternalOnly) { ?>
        <div class="alert warning"><?= t('news.this_news_is_internal_only_editorial_outlet_work_is_unavailable_un') ?></div>
    <?php } ?>

    <?php include BASEPATH . '/pages/news/review-controls.php'; ?>

    <h3><?= t('news.editorial_outlets') ?></h3>

    <div class="collapse-group mb-20">
    <?php $outletOpened = false; ?>
    <?php foreach (NewsEditorial::rows($editorialConfiguration, $news) as $row) {
        $request = $row['request'];
        if ($request === null) continue;
        $outlet = $row['outlet'];
        $outletId = $outlet['id'];
        $outletName = NewsOutlets::label($outlet['name']);
        $textFields = NewsOutlets::fields($outlet);
        $status = $request['status'] ?? '';
        $contribution = $editorialContributions[$outletId] ?? null;
        $canEditContribution = !$editorialInternalOnly && $contribution !== null && in_array($status, ['in_progress', 'ready'], true);
        $openOutlet = !$outletOpened && in_array($status, ['requested', 'in_progress', 'ready'], true);
        if ($openOutlet) $outletOpened = true;
        $sourceChanged = $contribution !== null && (int) ($news['original_revision'] ?? 1) !== (int) ($contribution['original_revision'] ?? 1);
    ?>
        <details class="collapse-panel news-outlet-card" id="editorial-outlet-<?= e($outletId) ?>" <?= $openOutlet ? 'open' : '' ?>>
            <summary class="collapse-header d-flex">
                <span class="news-outlet-name"><?= e($outletName) ?></span>
                <span class="badge signal" data-news-unsaved hidden role="status"><?= t('news.unsaved') ?></span>
                <span class="<?= NewsEditorial::statusClass($status) ?>">
                    <i class="ph <?= NewsEditorial::statusIcon($status) ?>"></i>
                    <?= e(NewsEditorial::statusLabel($status)) ?>
                </span>
            </summary>
            <div class="collapse-content">
                <?php if (($request['source'] ?? 'author') === 'editorial') { ?>
                    <p><span class="badge primary"><?= t('news.added_by_the_editorial_team') ?></span></p>
                <?php } ?>
                <?php if (empty($outlet['active'])) { ?><p class="text-muted"><?= t('news.this_outlet_is_no_longer_available_for_new_requests_existing_work') ?></p><?php } ?>
                <?php if (!empty($request['decline_reason'])) { ?>
                    <p><b><?= t('news.reason_for_declining') ?></b> <?= e($request['decline_reason']) ?></p>
                <?php } ?>
                <?php if ($status === 'released') { ?><p class="text-success"><?= e(NewsEditorial::releaseLabel($request['release_mode'] ?? 'without_review')) ?></p><?php } ?>
                <?php if (!$editorialInternalOnly && in_array($status, ['approved', 'released'], true)) { ?>
                    <form action="<?= $editorialAction ?>" method="post">
                        <?php $editorialHiddenFields($outletId); ?>
                        <button class="btn" name="action" value="revise"><?= t('news.edit_version_again') ?></button>
                        <p class="text-muted"><?= t('news.editing_again_returns_this_outlet_to_draft_the_other_outlet_releas') ?></p>
                    </form>
                <?php } ?>
                <?php if (!$editorialInternalOnly && $status === 'requested') { ?>
                    <form action="<?= $editorialAction ?>" method="post">
                        <?php $editorialHiddenFields($outletId); ?>
                        <button class="btn primary" name="action" value="start"><i class="ph ph-pencil" aria-hidden="true"></i> <?= t('news.start_editing') ?></button>
                    </form>
                <?php } ?>
                <?php if (!$editorialInternalOnly && in_array($status, ['declined', 'withdrawn'], true) && !empty($outlet['active'])) { ?>
                    <form action="<?= $editorialAction ?>" method="post">
                        <?php $editorialHiddenFields($outletId); ?>
                        <button class="btn" name="action" value="reopen"><i class="ph ph-arrow-counter-clockwise" aria-hidden="true"></i> <?= t('news.reopen_editing') ?></button>
                    </form>
                <?php } ?>

                <?php if ($contribution !== null) { ?>
                    <p class="text-muted mt-10">
                        <?= t('news.last_edited_by') ?> <?= e($DB->getNameFromId($contribution['updated_by'] ?? $contribution['created_by'])) ?>
                        · <?= e(date('d.m.Y H:i', strtotime($contribution['updated_at'] ?? $contribution['created_at']))) ?>
                    </p>
                    <?php if ($sourceChanged) { ?><p class="text-muted"><?= t('news.the_original_has_since_changed_this_outlet_text_and_its_release_re') ?></p><?php } ?>
                    <?php if ($canEditContribution) { ?>
                        <form action="<?= $editorialAction ?>" method="post">
                            <?php $editorialHiddenFields($outletId); ?>
                            <?php if (in_array('content', $textFields, true)) { ?><input type="hidden" name="editorial[format]" value="<?= e($contribution['format'] ?? 'plain') ?>"><?php } ?>
                            <div class="news-source-tools">
                                <button type="button" class="btn" data-news-copy-original><i class="ph ph-copy" aria-hidden="true"></i> <?= t('news.copy_contents_from_original') ?></button>
                                <p class="text-muted font-size-12 mb-0"><?= t('news.replaces_the_fields_in_both_languages_with_the_original_texts_save') ?></p>
                            </div>
                            <div class="news-language-tabs" role="tablist" aria-label="<?= t('news.contribution_language') ?>" hidden>
                                <?php foreach ($editorialLanguages as $language) { ?>
                                    <button type="button" class="btn" role="tab" id="language-<?= e($outletId) ?>-<?= $language ?>" aria-controls="fields-<?= e($outletId) ?>-<?= $language ?>" aria-selected="<?= $language === $editorialLanguages[0] ? 'true' : 'false' ?>" data-news-language-tab="<?= $language ?>"><?= $language === 'de' ? 'Deutsch' : 'English' ?></button>
                                <?php } ?>
                            </div>
                            <div>
                                <?php foreach ($editorialLanguages as $language) { ?>
                                    <div id="fields-<?= e($outletId) ?>-<?= $language ?>" role="tabpanel" aria-labelledby="language-<?= e($outletId) ?>-<?= $language ?>" data-news-language-panel="<?= $language ?>">
                                        <h3 data-news-language-heading><?= $language === 'de' ? 'Deutsch' : 'English' ?></h3>
                                        <?php foreach ($textFields as $field) {
                                            $fieldLabel = match ($field) {
                                                'title' => t('news.title_optional'),
                                                'teaser' => t('news.teaser_optional'),
                                                'content' => t('news.contribution'),
                                            };
                                        ?>
                                            <div class="form-group">
                                                <label id="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>-label" for="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>"><?= $fieldLabel ?></label>
                                                <?php if ($field === 'title') { ?>
                                                    <input class="form-control" data-news-original-field="title" data-news-language="<?= $language ?>" id="editorial-<?= e($outletId) ?>-title-<?= $language ?>" name="editorial[title][<?= $language ?>]" maxlength="240" value="<?= e($contribution['title'][$language] ?? '') ?>">
                                                <?php } elseif ($field === 'content') { ?>
                                                    <div id="editorial-<?= e($outletId) ?>-content-<?= $language ?>-quill" data-news-content-editor data-image-error="<?= e(t('news.choose_a_png_jpeg_or_gif_image_up_to_500_kb')) ?>" data-input="editorial-<?= e($outletId) ?>-content-<?= $language ?>" hidden><?= render_rich_text($contribution['content'][$language] ?? '', $contribution['format'] ?? 'plain') ?></div>
                                                    <textarea class="form-control" data-news-original-field="content" data-news-language="<?= $language ?>" id="editorial-<?= e($outletId) ?>-content-<?= $language ?>" name="editorial[content][<?= $language ?>]" rows="8" maxlength="1000000"><?= e($contribution['content'][$language] ?? '') ?></textarea>
                                                <?php } else { ?>
                                                    <textarea class="form-control" data-news-original-field="<?= $field ?>" data-news-language="<?= $language ?>" id="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>" name="editorial[<?= $field ?>][<?= $language ?>]" rows="<?= $field === 'teaser' ? 3 : 8 ?>" maxlength="<?= $field === 'teaser' ? 2000 : 100000 ?>"><?= e($contribution[$field][$language] ?? '') ?></textarea>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="btn-toolbar news-editor-actions">
                                <button class="btn primary" name="action" value="save"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= t('news.save_draft') ?></button>
                                <button class="btn" name="action" value="ready"><i class="ph ph-check" aria-hidden="true"></i> <?= t('news.mark_as_ready') ?></button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <?php foreach ($editorialLanguages as $language) {
                            $visibleTexts = array_filter($textFields, static fn($field) => ($contribution[$field][$language] ?? '') !== '');
                            if (!$visibleTexts) continue;
                        ?>
                            <h4><?= $language === 'de' ? 'Deutsch' : 'English' ?><?php if (in_array('title', $textFields, true)) { ?>: <?= e($contribution['title'][$language] ?? '') ?><?php } ?></h4>
                            <?php foreach ($visibleTexts as $field) { if ($field === 'title') continue; ?>
                                <div class="mb-10"><?= render_rich_text($contribution[$field][$language], $field === 'content' ? ($contribution['format'] ?? 'plain') : 'plain') ?></div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>

                <?php if (!$editorialInternalOnly && in_array($status, ['requested', 'in_progress', 'ready'], true)) { ?>
                    <details class="mt-20">
                        <summary class="text-danger cursor-pointer"><?= t('news.decline_outlet') ?></summary>
                        <form class="mt-10" action="<?= $editorialAction ?>" method="post">
                            <?php $editorialHiddenFields($outletId); ?>
                            <div class="form-group">
                                <label for="editorial-<?= e($outletId) ?>-decline-reason" class="required"><?= t('news.reason') ?></label>
                                <textarea class="form-control" id="editorial-<?= e($outletId) ?>-decline-reason" name="editorial[decline_reason]" rows="2" maxlength="2000" required></textarea>
                            </div>
                            <button class="btn danger" name="action" value="decline"><?= t('news.decline_outlet') ?></button>
                        </form>
                    </details>
                <?php } ?>
            </div>
        </details>
    <?php } ?>
</div>

    <?php $additionalOutlets = array_filter($editorialConfiguration['outlets'], static fn($outlet) => !empty($outlet['active']) && !isset($editorialRequests[$outlet['id']])); ?>
    <?php if (!$editorialInternalOnly && $additionalOutlets) { ?>
        <div class="box padded">
            <h3 class="title"><?= t('news.add_another_outlet') ?></h3>
            <form action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(); ?>
                <div class="form-group">
                    <label for="editorial-add-outlet"><?= t('news.outlet') ?></label>
                    <select class="form-control" id="editorial-add-outlet" name="editorial[outlet_id]" required>
                        <option value=""><?= t('news.please_select') ?></option>
                        <?php foreach ($additionalOutlets as $outlet) { ?><option value="<?= e($outlet['id']) ?>"><?= e(NewsOutlets::label($outlet['name'])) ?></option><?php } ?>
                    </select>
                </div>
                <button class="btn" name="action" value="add"><i class="ph ph-plus" aria-hidden="true"></i> <?= t('news.add_outlet') ?></button>
            </form>
        </div>
    <?php } elseif (!$editorialRequests && !$editorialInternalOnly) { ?>
        <p class="text-muted"><?= t('news.no_outlets_have_been_requested_or_configured_yet') ?></p>
    <?php } ?>

    <details class="collapse-panel mb-20" id="editorial-connections">
        <summary class="collapse-header d-flex"><?= t('news.edit_shared_connections') ?></summary>
        <div class="collapse-content">
            <p class="text-muted"><?= t('news.these_connections_apply_to_the_whole_news_item_and_all_its_outlet') ?></p>
            <form action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(null, 'connections'); ?>
                <input type="hidden" name="action" value="connections">
                <?php include BASEPATH . '/pages/news/connections-editor.php'; ?>
                <?php if ($Settings->featureEnabled('topics')) { $Settings->topicChooser(DB::doc2Arr($news['topics'] ?? []), 'news[topics]'); } ?>
                <button class="btn primary"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= t('news.save_connections') ?></button>
            </form>
        </div>
    </details>

    <?php $editorialHistory = array_reverse(NewsEditorial::history($news)); ?>
    <?php if ($editorialHistory) { ?>
        <details class="collapse-panel mb-20">
            <summary class="collapse-header d-flex"><?= t('news.editorial_history') ?></summary>
            <div class="collapse-content">
                <table class="table small simple">
                    <thead><tr><th><?= t('news.when_who') ?></th><th><?= t('groups.change') ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($editorialHistory as $event) { ?>
                            <tr>
                                <td><?= e(date('d.m.Y H:i', strtotime($event['at']))) ?><br><?= e($DB->getNameFromId($event['actor'])) ?></td>
                                <td><?= e(NewsEditorial::actionLabel($event['action'])) ?>
                                    <?php if (isset($event['outlet_id'])) { ?> · <?= e(NewsOutlets::label($editorialOutlets[$event['outlet_id']]['name'] ?? ['en' => $event['outlet_id']])) ?><?php } ?>
                                    <?php if (isset($event['reason'])) { ?><br><?= e($event['reason']) ?><?php } ?>
                                    <?php if (isset($event['comment'])) { ?><br><?= nl2br(e($event['comment'])) ?><?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </details>
    <?php } ?>
</section>
<script src="<?= ROOTPATH ?>/js/news-editorial.js?v=<?= OSIRIS_BUILD ?>"></script>
