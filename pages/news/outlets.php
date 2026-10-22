<?php
/** Shared request selection and read-only summary. No editorial text is stored here. */
$outletRows = NewsRequests::rows($Settings->getNewsOutlets(), DB::doc2Arr($news ?? []));
$canSelectOutlets = ($outletSelectionMode ?? 'view') === 'edit' &&
    (!isset($news['_id']) || NewsRequests::isAuthor(DB::doc2Arr($news)));
?>
<?php if ($canSelectOutlets) { ?>
    <input type="hidden" name="news[outlet_selection]" value="1">
<?php } ?>
<?php if ($outletRows) { ?>
    <div class="box padded" id="news-outlet-requests">
        <h2 class="title"><i class="ph-duotone ph-newspaper" aria-hidden="true"></i> <?= lang('Outlet requests', 'Outlet-Anfragen') ?></h2>
        <?php if ($canSelectOutlets) { ?>
            <p class="text-muted"><?= lang('For which outlets would you like the editorial team to prepare this news? The selection is optional and does not publish any outlet contributions. Your original news appears internally as usual.', 'Für welche Outlets soll die Redaktion diese News aufbereiten? Die Auswahl ist freiwillig und veröffentlicht keine Outlet-Beiträge. Deine ursprüngliche News erscheint wie gewohnt intern.') ?></p>
        <?php } ?>
        <?php foreach ($outletRows as $row) {
            $outlet = $row['outlet'];
            $request = $row['request'];
            if (!$canSelectOutlets && $request === null) continue;
            $selected = $request !== null && ($request['status'] ?? '') !== 'withdrawn' && ($request['status'] ?? '') !== 'declined';
            $locked = $request !== null && NewsRequests::locked($request);
            $disabled = $locked || (!$selected && empty($outlet['active']));
            $outletName = NewsRequests::label($outlet['name']);
        ?>
            <div class="form-group">
                <?php if ($canSelectOutlets) { ?>
                    <div class="custom-checkbox">
                        <input type="checkbox" name="news[outlets][]" id="request-outlet-<?= e($outlet['id']) ?>" value="<?= e($outlet['id']) ?>" <?= $selected ? 'checked' : '' ?> <?= $disabled ? 'disabled' : '' ?>>
                        <label for="request-outlet-<?= e($outlet['id']) ?>"><?= e($outletName) ?></label>
                    </div>
                <?php } else { ?>
                    <b><?= e($outletName) ?></b>
                <?php } ?>
                <?php if ($request !== null) { ?>
                    <span class="badge"><?= e(NewsRequests::statusLabel($request['status'] ?? '')) ?></span>
                    <?php if (($request['source'] ?? 'author') === 'editorial') { ?>
                        <span class="badge primary"><?= lang('Added by the editorial team', 'Von der Redaktion ergänzt') ?></span>
                    <?php } ?>
                    <?php if ((int) ($news['original_revision'] ?? 1) > (int) ($request['original_revision'] ?? 1)) { ?>
                        <span class="badge warning"><?= lang('Original changed since request', 'Original seit Anfrage geändert') ?></span>
                    <?php } ?>
                    <?php if ($locked && $canSelectOutlets) { ?>
                        <small class="text-muted d-block"><?= lang('The editorial team has already processed this outlet. The request can no longer be changed here.', 'Die Redaktion hat dieses Outlet bereits bearbeitet. Die Anfrage kann hier nicht mehr geändert werden.') ?></small>
                    <?php } ?>
                <?php } ?>
                <?php if (empty($outlet['active'])) { ?>
                    <small class="text-muted d-block"><?= lang('No longer available for new requests.', 'Für neue Anfragen nicht mehr verfügbar.') ?></small>
                <?php } ?>
                <?php $description = NewsRequests::label($outlet['description'] ?? []); ?>
                <?php if ($description !== '') { ?><small class="text-muted d-block"><?= e($description) ?></small><?php } ?>
                <?php if (!empty($request['decline_reason'])) { ?><p class="text-muted"><?= e($request['decline_reason']) ?></p><?php } ?>
            </div>
        <?php } ?>
        <?php if ($canSelectOutlets) { ?>
            <small class="text-muted"><?= lang('You can change your requests until the editorial team starts processing them. You can still edit your original news afterwards.', 'Du kannst deine Anfragen ändern, solange die Redaktion sie noch nicht bearbeitet hat. Deinen Originalbeitrag kannst du auch danach weiterhin bearbeiten.') ?></small>
        <?php } ?>
    </div>
<?php } ?>
