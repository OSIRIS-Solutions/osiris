<?php
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
        <h2 class="title"><i class="ph-duotone ph-newspaper" aria-hidden="true"></i> <?= lang('Outlet requests', 'Outlet-Anfragen') ?></h2>
        <?php if ($canSelectOutlets) { ?>
            <p class="text-muted"><?= lang('For which outlets would you like to suggest this news? The editorial team prepares suitable contributions.', 'Für welche Outlets möchtest du diese News vorschlagen? Die Redaktion bereitet passende Beiträge vor.') ?></p>
        <?php } ?>
        <?php if ($showInternalOnly && $canSelectOutlets) { ?>
            <div class="form-group">
                <div class="custom-checkbox">
                    <input type="checkbox" name="news[internal_only]" id="news-internal-only" value="1" <?= $internalOnly ? 'checked' : '' ?> <?= NewsEditorial::canChooseInternalOnly($outletNews) ? '' : 'disabled' ?>>
                    <label for="news-internal-only"><?= lang('Internal only', 'Nur intern') ?></label>
                </div>
                <small class="text-muted d-block"><?= lang('Only show this news in OSIRIS. Do not send it to Portfolio or request editorial outlet contributions.', 'Diese News nur in OSIRIS zeigen. Keine Ausgabe an Portfolio und keine redaktionellen Outlet-Beiträge beantragen.') ?></small>
                <?php if (!NewsEditorial::canChooseInternalOnly($outletNews)) { ?>
                    <small class="text-muted d-block"><?= lang('Editorial work has already started. Contact the editorial team to stop these contributions.', 'Die redaktionelle Bearbeitung hat bereits begonnen. Bitte kontaktiere die Redaktion, um diese Beiträge zu stoppen.') ?></small>
                <?php } ?>
            </div>
        <?php } elseif ($internalOnly) { ?>
            <p><span class="badge"><?= lang('Internal only', 'Nur intern') ?></span></p>
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
                        <span class="badge primary"><?= lang('Added by the editorial team', 'Von der Redaktion ergänzt') ?></span>
                    <?php } ?>
                    <?php if ($locked && $canSelectOutlets) { ?>
                        <small class="text-muted d-block"><?= lang('The editorial team has already processed this outlet. The request can no longer be changed here.', 'Die Redaktion hat dieses Outlet bereits bearbeitet. Die Anfrage kann hier nicht mehr geändert werden.') ?></small>
                    <?php } ?>
                <?php } ?>
                <?php if (empty($outlet['active'])) { ?>
                    <small class="text-muted d-block"><?= lang('No longer available for new requests.', 'Für neue Anfragen nicht mehr verfügbar.') ?></small>
                <?php } ?>
                <?php $description = NewsOutlets::label($outlet['description'] ?? []); ?>
                <?php if ($canSelectOutlets && $description !== '') { ?><small class="text-muted d-block"><?= e($description) ?></small><?php } ?>
            </div>
        <?php } ?>
        <?php if ($canSelectOutlets) { ?>
            <small class="text-muted"><?= lang('You can change your requests until the editorial team starts processing them. You can still edit your original news afterwards.', 'Du kannst deine Anfragen ändern, solange die Redaktion sie noch nicht bearbeitet hat. Deinen Originalbeitrag kannst du auch danach weiterhin bearbeiten.') ?></small>
            <?php if (isset($news['_id']) && !NewsEditorial::usesOutletWorkflow($outletNews)) { ?>
                <small class="text-muted d-block"><?= lang('For this older news item, the original retains its previous release until you change the selection here.', 'Bei dieser älteren News behält der Originalbeitrag seine bisherige Freigabe, bis du die Auswahl hier änderst.') ?></small>
            <?php } ?>
        <?php } ?>
    </div>
<?php } ?>
<?php if ($canSelectOutlets && NewsEditorial::workflowAvailable($outletConfiguration, $outletNews)) { ?>
    <script src="<?= ROOTPATH ?>/js/news-requests.js?v=<?= OSIRIS_BUILD ?>"></script>
<?php } ?>
