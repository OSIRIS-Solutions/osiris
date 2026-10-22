<?php

/** Batch actions reuse the editorial form protection and version checks. */
$pendingReview = NewsReview::pending($news);
$preparedRequests = NewsReview::prepared($news);
$readyRequests = array_filter($preparedRequests, static fn($request) => $request['status'] === 'ready');
$preparedNames = array_map(static fn($request) => NewsRequests::label($editorialOutlets[$request['outlet_id']]['name'] ?? ['en' => $request['outlet_id']]), $preparedRequests);
?>
<?php if (!$editorialInternalOnly && ($preparedRequests || $pendingReview !== null)) { ?>
    <div class="box padded" id="editorial-review-controls">
        <h3 class="title"><?= lang('Review and release', 'Prüfung und Freigabe') ?></h3>
        <p><?= lang('Prepared outlets:', 'Vorbereitete Outlets:') ?> <b><?= e(implode(', ', $preparedNames)) ?></b>.</p>
        <p class="text-muted"><?= lang('These actions apply to the prepared outlets together. Other outlets remain in progress. Release records the editorial decision; output via Portfolio or other channels is a separate step.', 'Diese Aktionen gelten gemeinsam für die vorbereiteten Outlets. Andere Outlets bleiben in Bearbeitung. Die Freigabe dokumentiert die redaktionelle Entscheidung; die Ausgabe über Portfolio oder andere Kanäle ist ein eigener Schritt.') ?></p>
        <?php if ($pendingReview !== null) { ?>
            <p><span class="badge warning"><?= lang('Author response pending', 'Autorenrückmeldung ausstehend') ?></span> <a href="#news-author-review"><?= lang('Show submitted versions', 'Versendete Fassungen ansehen') ?></a></p>
            <form class="mb-20" action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(); ?>
                <input type="hidden" name="editorial[review_id]" value="<?= e($pendingReview['id']) ?>">
                <button class="btn" name="action" value="cancel_review"><?= lang('End review and continue editing', 'Prüfung beenden und weiterbearbeiten') ?></button>
            </form>
        <?php } elseif ($readyRequests) { ?>
            <form class="mb-20" action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(); ?>
                <button class="btn primary" name="action" value="request_review"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i> <?= lang('Request author review', 'Autorenprüfung anfordern') ?></button>
                <p class="text-muted mb-0"><?= lang('Send all ready versions that have not yet been confirmed to the author in one inbox message.', 'Alle fertigen, noch nicht bestätigten Fassungen werden gemeinsam mit einer Inbox-Nachricht an die Autorin oder den Autor geschickt.') ?></p>
            </form>
        <?php } ?>
        <?php if ($preparedRequests) { ?>
            <form action="<?= $editorialAction ?>" method="post">
                <?php $editorialHiddenFields(); ?>
                <?php if ($pendingReview !== null) { ?>
                    <div class="custom-checkbox mb-10">
                        <input type="checkbox" id="release-without-response" name="editorial[confirm_without_response]" value="1" required>
                        <label for="release-without-response"><?= lang('I am releasing these versions without the pending author response.', 'Ich gebe diese Fassungen ohne die ausstehende Autorenrückmeldung frei.') ?></label>
                    </div>
                <?php } ?>
                <button class="btn success" name="action" value="release"><i class="ph ph-check-circle" aria-hidden="true"></i> <?= $pendingReview !== null ? lang('Release without a response', 'Ohne Rückmeldung freigeben') : lang('Release prepared outlets', 'Vorbereitete Outlets freigeben') ?></button>
            </form>
        <?php } ?>
    </div>
<?php } ?>
