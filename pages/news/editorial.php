<?php

/** Only users with the editorial right see draft texts and editorial controls. */
if (!$Settings->hasPermission('news.editorial')) return;
require_once BASEPATH . '/php/NewsEditorial.php';
include_once BASEPATH . '/header-editor.php';
include_once BASEPATH . '/components/news-editor-style.php';
$editorialConfiguration = $Settings->getNewsOutlets();
$editorialContributions = NewsEditorial::contributions($news);
$editorialRequests = array_column(NewsRequests::read($news), null, 'outlet_id');
$editorialOutlets = array_column($editorialConfiguration['outlets'], null, 'id');
$editorialInternalOnly = NewsRequests::internalOnly($news);
$editorialLanguages = lang('en', 'de') === 'de' ? ['de', 'en'] : ['en', 'de'];
$editorialAction = ROOTPATH . '/crud/news/editorial/' . e($news['_id']);
$editorialHiddenFields = static function (?string $outletId = null) use ($news) { ?>
    <input type="hidden" name="csrf_token" value="<?= e(NewsRequests::csrfToken()) ?>">
    <input type="hidden" name="news_revision" value="<?= (int) ($news['revision'] ?? 0) ?>">
    <?php if ($outletId !== null) { ?><input type="hidden" name="editorial[outlet_id]" value="<?= e($outletId) ?>"><?php } ?>
<?php }; ?>

<section id="news-editorial" class="mt-20">
    <h2><i class="ph-duotone ph-pencil-line" aria-hidden="true"></i> <?= lang('Editorial team', 'Redaktion') ?></h2>
    <p class="text-muted"><?= lang('Prepare an individual contribution for each outlet. The original contribution above remains unchanged.', 'Bereite für jedes Outlet einen eigenen Beitrag vor. Der Originalbeitrag oben bleibt erhalten.') ?></p>
    <?php if ($editorialInternalOnly) { ?>
        <div class="alert warning"><?= lang('This news is internal only. Editorial outlet work is unavailable until the author changes this selection.', 'Diese News ist nur intern. Die Bearbeitung von Outlet-Beiträgen ist erst möglich, wenn die Autorin oder der Autor diese Auswahl ändert.') ?></div>
    <?php } ?>

    <?php foreach (NewsRequests::rows($editorialConfiguration, $news) as $row) {
        $request = $row['request'];
        if ($request === null) continue;
        $outlet = $row['outlet'];
        $outletId = $outlet['id'];
        $outletName = NewsRequests::label($outlet['name']);
        $textFields = NewsOutlets::fields($outlet);
        $status = $request['status'] ?? '';
        $contribution = $editorialContributions[$outletId] ?? null;
        $canEditContribution = !$editorialInternalOnly && $contribution !== null && in_array($status, ['in_progress', 'ready'], true);
        $sourceChanged = $contribution !== null && (int) ($news['original_revision'] ?? 1) > (int) ($contribution['original_revision'] ?? 1);
    ?>
        <details class="collapse-panel mb-20" id="editorial-outlet-<?= e($outletId) ?>" <?= in_array($status, ['requested', 'in_progress'], true) ? 'open' : '' ?>>
            <summary class="collapse-header">
                <?= e($outletName) ?>
                <span class="badge"><?= e(NewsRequests::statusLabel($status)) ?></span>
            </summary>
            <div class="collapse-content">
                <?php if (($request['source'] ?? 'author') === 'editorial') { ?>
                    <p><span class="badge primary"><?= lang('Added by the editorial team', 'Von der Redaktion ergänzt') ?></span></p>
                <?php } ?>
                <?php if (empty($outlet['active'])) { ?><p class="text-muted"><?= lang('This outlet is no longer available for new requests. Existing work is retained.', 'Dieses Outlet ist für neue Anfragen deaktiviert. Vorhandene Bearbeitungen bleiben erhalten.') ?></p><?php } ?>
                <?php if (!empty($request['decline_reason'])) { ?>
                    <p><b><?= lang('Reason for declining:', 'Ablehnungsgrund:') ?></b> <?= e($request['decline_reason']) ?></p>
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
                        · <?= lang('Version', 'Version') ?> <?= (int) ($contribution['revision'] ?? 0) ?>
                    </p>
                    <?php if ($sourceChanged) { ?><div class="alert warning"><?= lang('The original or its shared connections have changed since work on this version began. Please review the original above.', 'Der Originalbeitrag oder seine gemeinsamen Verknüpfungen wurden seit Beginn dieser Fassung geändert. Bitte prüfe den Originalbeitrag oben.') ?></div><?php } ?>
                    <?php if ($canEditContribution) { ?>
                        <form action="<?= $editorialAction ?>" method="post">
                            <?php $editorialHiddenFields($outletId); ?>
                            <div class="row row-eq-spacing">
                                <?php foreach ($editorialLanguages as $language) { ?>
                                    <div class="col-md-6">
                                        <h3><?= $language === 'de' ? 'Deutsch' : 'English' ?></h3>
                                        <?php foreach ($textFields as $field) {
                                            $fieldLabel = match ($field) {
                                                'title' => lang('Title (optional)', 'Titel (optional)'),
                                                'teaser' => lang('Teaser (optional)', 'Teaser (optional)'),
                                                'content' => lang('Contribution', 'Beitrag'),
                                            };
                                        ?>
                                            <div class="form-group">
                                                <label for="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>"><?= $fieldLabel ?></label>
                                                <?php if ($field === 'title') { ?>
                                                    <input class="form-control" id="editorial-<?= e($outletId) ?>-title-<?= $language ?>" name="editorial[title][<?= $language ?>]" maxlength="240" value="<?= e($contribution['title'][$language] ?? '') ?>">
                                                <?php } else { ?>
                                                    <textarea class="form-control" id="editorial-<?= e($outletId) ?>-<?= $field ?>-<?= $language ?>" name="editorial[<?= $field ?>][<?= $language ?>]" rows="<?= $field === 'teaser' ? 3 : 8 ?>" maxlength="<?= $field === 'teaser' ? 2000 : 100000 ?>"><?= e($contribution[$field][$language] ?? '') ?></textarea>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                            <?php if ($sourceChanged) { ?>
                                <div class="custom-checkbox mb-10">
                                    <input type="checkbox" id="editorial-<?= e($outletId) ?>-source-reviewed" name="editorial[source_reviewed]" value="1">
                                    <label for="editorial-<?= e($outletId) ?>-source-reviewed"><?= lang('I have reviewed the changes to the original.', 'Ich habe die Änderungen am Originalbeitrag berücksichtigt.') ?></label>
                                </div>
                            <?php } ?>
                            <div class="btn-toolbar">
                                <button class="btn primary" name="action" value="save"><i class="ph ph-floppy-disk" aria-hidden="true"></i> <?= lang('Save draft', 'Entwurf speichern') ?></button>
                                <button class="btn success" name="action" value="ready"><i class="ph ph-check" aria-hidden="true"></i> <?= lang('Mark editorial work complete', 'Redaktionell fertig markieren') ?></button>
                            </div>
                        </form>
                    <?php } else { ?>
                        <?php foreach ($editorialLanguages as $language) {
                            $visibleTexts = array_filter($textFields, static fn($field) => ($contribution[$field][$language] ?? '') !== '');
                            if (!$visibleTexts) continue;
                        ?>
                            <h4><?= $language === 'de' ? 'Deutsch' : 'English' ?><?php if (in_array('title', $textFields, true)) { ?>: <?= e($contribution['title'][$language] ?? '') ?><?php } ?></h4>
                            <?php foreach ($visibleTexts as $field) { if ($field === 'title') continue; ?>
                                <p><?= nl2br(e($contribution[$field][$language])) ?></p>
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
                        <?php foreach ($additionalOutlets as $outlet) { ?><option value="<?= e($outlet['id']) ?>"><?= e(NewsRequests::label($outlet['name'])) ?></option><?php } ?>
                    </select>
                </div>
                <button class="btn" name="action" value="add"><i class="ph ph-plus" aria-hidden="true"></i> <?= lang('Add outlet', 'Outlet ergänzen') ?></button>
            </form>
        </div>
    <?php } elseif (!$editorialRequests && !$editorialInternalOnly) { ?>
        <p class="text-muted"><?= lang('No outlets have been requested or configured yet.', 'Es wurden noch keine Outlets beantragt oder eingerichtet.') ?></p>
    <?php } ?>

    <details class="collapse-panel mb-20" id="editorial-connections">
        <summary class="collapse-header"><?= lang('Edit shared connections', 'Gemeinsame Verknüpfungen bearbeiten') ?></summary>
        <div class="collapse-content">
            <p class="text-muted"><?= lang('These connections apply to the whole news item and all its outlet contributions.', 'Diese Verknüpfungen gelten für die gesamte News und alle Outlet-Beiträge.') ?></p>
            <form action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(); ?>
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
            <summary class="collapse-header"><?= lang('Editorial history', 'Redaktionsverlauf') ?></summary>
            <div class="collapse-content">
                <table class="table small">
                    <thead><tr><th><?= lang('When / who', 'Wann / wer') ?></th><th><?= lang('Change', 'Änderung') ?></th></tr></thead>
                    <tbody>
                        <?php foreach ($editorialHistory as $event) { ?>
                            <tr>
                                <td><?= e(date('d.m.Y H:i', strtotime($event['at']))) ?><br><?= e($DB->getNameFromId($event['actor'])) ?></td>
                                <td><?= e(NewsEditorial::actionLabel($event['action'])) ?>
                                    <?php if (isset($event['outlet_id'])) { ?> · <?= e(NewsRequests::label($editorialOutlets[$event['outlet_id']]['name'] ?? ['en' => $event['outlet_id']])) ?><?php } ?>
                                    <?php if (isset($event['reason'])) { ?><br><?= e($event['reason']) ?><?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </details>
    <?php } ?>
</section>
