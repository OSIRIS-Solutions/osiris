<?php

if (!$Settings->featureEnabled('news-editorial')) return;
/** Shared request selection and read-only summary. No editorial text is stored here. */
$outletConfiguration = $Settings->getNewsOutlets();
$outletNews = DB::doc2Arr($news ?? []);
$outletRows = NewsEditorial::rows($outletConfiguration, $outletNews);
$internalOnly = NewsEditorial::internalOnly($outletNews);
$showInternalOnly = NewsEditorial::workflowAvailable($outletConfiguration, $outletNews) &&
    ($outletConfiguration['original_portfolio'] || $internalOnly);
$canSelectOutlets = ($outletSelectionMode ?? 'view') === 'edit' &&
    (!isset($news['_id']) || NewsEditorial::isAuthor(DB::doc2Arr($news)));
if (!$canSelectOutlets) {
    $outletRows = array_filter($outletRows, static fn($row) => $row['request'] !== null);
}
?>
<?php if ($canSelectOutlets) { ?>
    <input type="hidden" name="news[outlet_selection]" value="1">
    <?php if (NewsEditorial::workflowAvailable($outletConfiguration, $outletNews)) { ?>
        <input type="hidden" name="news[internal_only]" value="0">
        <input type="hidden" name="news[publication_choice_changed]" value="0" id="news-publication-choice-changed">
    <?php } ?>
<?php } ?>
<?php if ($outletRows || ($showInternalOnly && ($canSelectOutlets || $internalOnly))) { ?>
    <div class="box padded news-workflow" id="news-outlet-requests">
        <h2 class="title"><i class="ph-duotone ph-newspaper" aria-hidden="true"></i> <?= t('news.outlet_requests') ?></h2>
        <?php if ($canSelectOutlets) { ?>
            <p class="text-muted"><?= t('news.for_which_outlets_would_you_like_to_suggest_this_news_the_editoria') ?></p>
        <?php } ?>
        <?php if ($showInternalOnly && $canSelectOutlets) { ?>
            <div class="form-group">
                <div class="custom-checkbox">
                    <input type="checkbox" name="news[internal_only]" id="news-internal-only" value="1" <?= $internalOnly ? 'checked' : '' ?> <?= NewsEditorial::canChooseInternalOnly($outletNews) ? '' : 'disabled' ?>>
                    <label for="news-internal-only"><?= t('news.internal_only') ?></label>
                </div>
                <small class="text-muted d-block"><?= t('news.only_show_this_news_in_osiris_do_not_send_it_to_portfolio_or_reque') ?></small>
                <?php if (!NewsEditorial::canChooseInternalOnly($outletNews)) { ?>
                    <small class="text-muted d-block"><?= t('news.editorial_work_has_already_started_contact_the_editorial_team_to_s') ?></small>
                <?php } ?>
            </div>
        <?php } elseif ($internalOnly) { ?>
            <p><span class="badge"><?= t('news.internal_only') ?></span></p>
        <?php } ?>
        <?php foreach ($outletRows as $row) {
            $outlet = $row['outlet'];
            $request = $row['request'];
            if (!$canSelectOutlets && $request === null) continue;
            $selected = $request !== null && ($request['status'] ?? '') !== 'withdrawn' && ($request['status'] ?? '') !== 'declined';
            $locked = $request !== null && NewsEditorial::locked($request);
            $disabled = $locked || (!$selected && empty($outlet['active']));
            $outletName = NewsOutlets::label($outlet['name']);
        ?>
            <div class="form-group news-request-row">
                <?php if ($canSelectOutlets) { ?>
                    <div class="custom-checkbox">
                        <input type="checkbox" name="news[outlets][]" id="request-outlet-<?= e($outlet['id']) ?>" value="<?= e($outlet['id']) ?>" <?= $selected ? 'checked' : '' ?> <?= $disabled ? 'disabled' : '' ?>>
                        <label for="request-outlet-<?= e($outlet['id']) ?>"><?= e($outletName) ?></label>
                    </div>
                <?php } else { ?>
                    <b><?= e($outletName) ?></b>
                <?php } ?>
                <?php if ($request !== null) { ?>
                    <span class="<?= NewsEditorial::statusClass($request['status'] ?? '') ?>"><?= e(NewsEditorial::statusLabel($request['status'] ?? '')) ?></span>
                    <?php if (($request['source'] ?? 'author') === 'editorial') { ?>
                        <span class="badge primary"><?= t('news.added_by_the_editorial_team') ?></span>
                    <?php } ?>
                    <?php if ($locked && $canSelectOutlets) { ?>
                        <small class="text-muted d-block"><?= t('news.the_editorial_team_has_already_processed_this_outlet_the_request_c') ?></small>
                    <?php } ?>
                <?php } ?>
                <?php if (empty($outlet['active'])) { ?>
                    <small class="text-muted d-block"><?= t('news.no_longer_available_for_new_requests') ?></small>
                <?php } ?>
                <?php $description = NewsOutlets::label($outlet['description'] ?? []); ?>
                <?php if ($canSelectOutlets && $description !== '') { ?><small class="text-muted d-block"><?= e($description) ?></small><?php } ?>
            </div>
        <?php } ?>
        <?php if ($canSelectOutlets) { ?>
            <small class="text-muted"><?= t('news.you_can_change_your_requests_until_the_editorial_team_starts_proce') ?></small>
            <?php if (isset($news['_id']) && !NewsEditorial::usesOutletWorkflow($outletNews)) { ?>
                <small class="text-muted d-block"><?= t('news.for_this_older_news_item_the_original_retains_its_previous_release') ?></small>
            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>
<?php if ($canSelectOutlets && NewsEditorial::workflowAvailable($outletConfiguration, $outletNews)) { ?>
    <script src="<?= ROOTPATH ?>/js/news-requests.js?v=<?= OSIRIS_BUILD ?>"></script>
<?php } ?>
