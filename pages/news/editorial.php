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
    <h2><i class="ph-duotone ph-pencil-line" aria-hidden="true"></i> <?= lang('Editorial team', 'Redaktion') ?></h2>
        <a class="btn small" href="<?= ROOTPATH ?>/news/editorial"><i class="ph ph-arrow-left" aria-hidden="true"></i> <?= lang('Editorial overview', 'Redaktionsübersicht') ?></a>
    </div>
    <div class="alert warning mt-10" id="news-unsaved-warning" role="alert" hidden>
        <p><?= lang('There are other unsaved changes. Continuing will discard those changes.', 'Es gibt weitere ungespeicherte Änderungen. Beim Fortfahren gehen diese Änderungen verloren.') ?></p>
        <div class="btn-toolbar">
            <button type="button" class="btn" data-news-keep-editing><?= lang('Continue editing', 'Weiterbearbeiten') ?></button>
            <button type="button" class="btn danger" data-news-discard-changes><?= lang('Discard other changes and continue', 'Andere Änderungen verwerfen und fortfahren') ?></button>
        </div>
    </div>
    <p class="text-muted"><?= lang('Prepare an individual contribution for each outlet. The original contribution above remains unchanged.', 'Bereite für jedes Outlet einen eigenen Beitrag vor. Der Originalbeitrag oben bleibt erhalten.') ?></p>
    <?php if ($editorialInternalOnly) { ?>
        <div class="alert warning"><?= lang('This news is internal only. Editorial outlet work is unavailable until the author changes this selection.', 'Diese News ist nur intern. Die Bearbeitung von Outlet-Beiträgen ist erst möglich, wenn die Autorin oder der Autor diese Auswahl ändert.') ?></div>
    <?php } ?>

    <?php include BASEPATH . '/pages/news/review-controls.php'; ?>

    <h3><?= lang('Editorial outlets', 'Redaktionelle Outlets') ?></h3>

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
                <span class="badge signal" data-news-unsaved hidden role="status"><?= lang('Unsaved', 'Ungespeichert') ?></span>
                <span class="<?= NewsEditorial::statusClass($status) ?>">
                    <i class="ph <?= NewsEditorial::statusIcon($status) ?>"></i>
                    <?= e(NewsEditorial::statusLabel($status)) ?>
                </span>
            </summary>
            <div class="collapse-content">
                <?php if (($request['source'] ?? 'author') === 'editorial') { ?>
                    <p><span class="badge primary"><?= lang('Added by the editorial team', 'Von der Redaktion ergänzt') ?></span></p>
                <?php } ?>
                <?php if (empty($outlet['active'])) { ?><p class="text-muted"><?= lang('This outlet is no longer available for new requests. Existing work is retained.', 'Dieses Outlet ist für neue Anfragen deaktiviert. Vorhandene Bearbeitungen bleiben erhalten.') ?></p><?php } ?>
                <?php if (!empty($request['decline_reason'])) { ?>
                    <p><b><?= lang('Reason for declining:', 'Ablehnungsgrund:') ?></b> <?= e($request['decline_reason']) ?></p>
                <?php } ?>
                <?php if ($status === 'released') { ?><p class="text-success"><?= e(NewsEditorial::releaseLabel($request['release_mode'] ?? 'without_review')) ?></p><?php } ?>
                <?php if (!$editorialInternalOnly && in_array($status, ['approved', 'released'], true)) { ?>
                    <form action="<?= $editorialAction ?>" method="post">
                        <?php $editorialHiddenFields($outletId); ?>
                        <button class="btn" name="action" value="revise"><?= lang('Edit version again', 'Fassung erneut bearbeiten') ?></button>
                        <p class="text-muted"><?= lang('Editing again returns this outlet to draft. The other outlet releases remain unchanged.', 'Durch erneute Bearbeitung wird dieses Outlet wieder zum Entwurf. Die Freigaben der anderen Outlets bleiben erhalten.') ?></p>
                    </form>
                <?php } ?>
                <?php if (!$editorialInternalOnly && $status === 'requested') { ?>
                    <form action="<?= $editorialAction ?>" method="post">
                        <?php $editorialHiddenFields($outletId); ?>
                        <button class="btn primary" name="action" value="start"><i class="ph ph-pencil" aria-hidden="true"></i> <?= lang('Start editing', 'Bearbeitung starten') ?></button>
                    </form>
                <?php } ?>
                <?php if (!$editorialInternalOnly && in_array($status, ['declined', 'withdrawn'], true) && !empty($outlet['active'])) { ?>
                    <form action="<?= $editorialAction ?>" method="post">
                        <?php $editorialHiddenFields($outletId); ?>
                        <button class="btn" name="action" value="reopen"><i class="ph ph-arrow-counter-clockwise" aria-hidden="true"></i> <?= lang('Reopen editing', 'Bearbeitung wieder aufnehmen') ?></button>
                    </form>
                <?php } ?>

                <?php if ($contribution !== null) { ?>
                    <p class="text-muted mt-10">
                        <?= lang('Last edited by', 'Zuletzt bearbeitet von') ?> <?= e($DB->getNameFromId($contribution['updated_by'] ?? $contribution['created_by'])) ?>
                        · <?= e(date('d.m.Y H:i', strtotime($contribution['updated_at'] ?? $contribution['created_at']))) ?>
                    </p>
                    <?php if ($sourceChanged) { ?><p class="text-muted"><?= lang('The original has since changed. This outlet text and its release remain unchanged.', 'Das Original wurde inzwischen geändert. Dieser Outlet-Text und seine Freigabe bleiben davon unberührt.') ?></p><?php } ?>
                    <?php if ($canEditContribution) { ?>
                        <form action="<?= $editorialAction ?>" method="post">
                            <?php $editorialHiddenFields($outletId); ?>
                            <?php if (in_array('content', $textFields, true)) { ?><input type="hidden" name="editorial[format]" value="<?= e($contribution['format'] ?? 'plain') ?>"><?php } ?>
                            <div class="news-source-tools">
                                <button type="button" class="btn" data-news-copy-original><i class="ph ph-copy" aria-hidden="true"></i> <?= lang('Copy contents from original', 'Inhalte aus Original übernehmen') ?></button>
                                <p class="text-muted font-size-12 mb-0"><?= lang('Replaces the fields in both languages with the original texts. Save the draft to keep the changes.', 'Ersetzt die Felder in beiden Sprachen durch die Originaltexte. Zum Übernehmen anschließend den Entwurf speichern.') ?></p>
                            </div>
                            <div class="news-language-tabs" role="tablist" aria-label="<?= lang('Contribution language', 'Beitragssprache') ?>" hidden>
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
                                                'title' => lang('Title (optional)', 'Titel (optional)'),
                                                'teaser' => lang('Teaser (optional)', 'Teaser (optional)'),
                                                'content' => lang('Contribution', 'Beitrag'),
                                            };
                                        ?>
                                            <div class="form-group">
                                                <label id="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>-label" for="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>"><?= $fieldLabel ?></label>
                                                <?php if ($field === 'title') { ?>
                                                    <input class="form-control" data-news-original-field="title" data-news-language="<?= $language ?>" id="editorial-<?= e($outletId) ?>-title-<?= $language ?>" name="editorial[title][<?= $language ?>]" maxlength="240" value="<?= e($contribution['title'][$language] ?? '') ?>">
                                                <?php } elseif ($field === 'content') { ?>
                                                    <div id="editorial-<?= e($outletId) ?>-content-<?= $language ?>-quill" data-news-content-editor data-image-error="<?= e(lang('Choose a PNG, JPEG or GIF image up to 500 KB.', 'Wähle ein PNG-, JPEG- oder GIF-Bild bis 500 KB.')) ?>" data-input="editorial-<?= e($outletId) ?>-content-<?= $language ?>" hidden><?= render_rich_text($contribution['content'][$language] ?? '', $contribution['format'] ?? 'plain') ?></div>
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
                                <button class="btn primary" name="action" value="save"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= lang('Save draft', 'Entwurf speichern') ?></button>
                                <button class="btn" name="action" value="ready"><i class="ph ph-check" aria-hidden="true"></i> <?= lang('Mark as ready', 'Als fertig markieren') ?></button>
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
                        <summary class="text-danger cursor-pointer"><?= lang('Decline outlet', 'Outlet ablehnen') ?></summary>
                        <form class="mt-10" action="<?= $editorialAction ?>" method="post">
                            <?php $editorialHiddenFields($outletId); ?>
                            <div class="form-group">
                                <label for="editorial-<?= e($outletId) ?>-decline-reason" class="required"><?= lang('Reason', 'Begründung') ?></label>
                                <textarea class="form-control" id="editorial-<?= e($outletId) ?>-decline-reason" name="editorial[decline_reason]" rows="2" maxlength="2000" required></textarea>
                            </div>
                            <button class="btn danger" name="action" value="decline"><?= lang('Decline outlet', 'Outlet ablehnen') ?></button>
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
            <h3 class="title"><?= lang('Add another outlet', 'Weiteres Outlet ergänzen') ?></h3>
            <form action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(); ?>
                <div class="form-group">
                    <label for="editorial-add-outlet"><?= lang('Outlet', 'Outlet') ?></label>
                    <select class="form-control" id="editorial-add-outlet" name="editorial[outlet_id]" required>
                        <option value=""><?= lang('Please select', 'Bitte auswählen') ?></option>
                        <?php foreach ($additionalOutlets as $outlet) { ?><option value="<?= e($outlet['id']) ?>"><?= e(NewsOutlets::label($outlet['name'])) ?></option><?php } ?>
                    </select>
                </div>
                <button class="btn" name="action" value="add"><i class="ph ph-plus" aria-hidden="true"></i> <?= lang('Add outlet', 'Outlet ergänzen') ?></button>
            </form>
        </div>
    <?php } elseif (!$editorialRequests && !$editorialInternalOnly) { ?>
        <p class="text-muted"><?= lang('No outlets have been requested or configured yet.', 'Es wurden noch keine Outlets beantragt oder eingerichtet.') ?></p>
    <?php } ?>

    <details class="collapse-panel mb-20" id="editorial-connections">
        <summary class="collapse-header d-flex"><?= lang('Edit shared connections', 'Gemeinsame Verknüpfungen bearbeiten') ?></summary>
        <div class="collapse-content">
            <p class="text-muted"><?= lang('These connections apply to the whole news item and all its outlet contributions.', 'Diese Verknüpfungen gelten für die gesamte News und alle Outlet-Beiträge.') ?></p>
            <form action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(null, 'connections'); ?>
                <input type="hidden" name="action" value="connections">
                <?php include BASEPATH . '/pages/news/connections-editor.php'; ?>
                <?php if ($Settings->featureEnabled('topics')) { $Settings->topicChooser(DB::doc2Arr($news['topics'] ?? []), 'news[topics]'); } ?>
                <button class="btn primary"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= lang('Save connections', 'Verknüpfungen speichern') ?></button>
            </form>
        </div>
    </details>

    <?php $editorialHistory = array_reverse(NewsEditorial::history($news)); ?>
    <?php if ($editorialHistory) { ?>
        <details class="collapse-panel mb-20">
            <summary class="collapse-header d-flex"><?= lang('Editorial history', 'Redaktionsverlauf') ?></summary>
            <div class="collapse-content">
                <table class="table small simple">
                    <thead><tr><th><?= lang('When / who', 'Wann / wer') ?></th><th><?= lang('Change', 'Änderung') ?></th></tr></thead>
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
