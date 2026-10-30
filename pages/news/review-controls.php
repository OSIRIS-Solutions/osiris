<?php

if (!$Settings->featureEnabled('news-editorial')) return;

/** Batch actions apply only to the named ready or submitted outlet versions. */
$pendingReview = NewsReview::pending($news);
$preparedRequests = NewsEditorial::prepared($news);
$preparedNames = array_map(static fn($request) => NewsOutlets::label($editorialOutlets[$request['outlet_id']]['name'] ?? ['en' => $request['outlet_id']]), $preparedRequests);
?>
<?php if (!$editorialInternalOnly && ($preparedRequests || $pendingReview !== null)) { ?>
    <div class="box padded news-batch-actions" id="editorial-review-controls">
        <h3 class="title"><?= $pendingReview !== null ? lang('Awaiting author response', 'Wartet auf Autorenrückmeldung') : lang('Release ready contributions', 'Fertige Fassungen freigeben') ?></h3>
        <p><b><?= e(implode(', ', $preparedNames)) ?></b></p>
        <?php if ($pendingReview !== null) { ?>
            <p class="text-muted"><?= lang('The submitted contributions stay open until the author responds or the editorial team ends the review.', 'Die versendeten Fassungen bleiben offen, bis die Autorin oder der Autor antwortet oder die Redaktion die Prüfung beendet.') ?></p>
            <div class="news-batch-options">
                <a href="#author-review-<?= e($pendingReview['id']) ?>"><?= lang('Show submitted versions', 'Versendete Fassungen ansehen') ?></a>
                <form action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <input type="hidden" name="editorial[review_id]" value="<?= e($pendingReview['id']) ?>">
                    <button class="btn small" name="action" value="cancel_review"><?= lang('End review and continue editing', 'Prüfung beenden und weiterbearbeiten') ?></button>
                </form>
            </div>
            <details class="mt-20">
                <summary class="cursor-pointer"><?= lang('Release without a response', 'Ohne Rückmeldung freigeben') ?></summary>
                <form class="mt-10" action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <div class="custom-checkbox mb-10">
                        <input type="checkbox" id="release-without-response" name="editorial[confirm_without_response]" value="1" required>
                        <label for="release-without-response"><?= lang('I am releasing the named contributions without the pending author response.', 'Ich gebe die genannten Fassungen ohne die ausstehende Autorenrückmeldung frei.') ?></label>
                    </div>
                    <button class="btn success" name="action" value="release"><i class="ph ph-check-circle" aria-hidden="true"></i> <?= lang('Release contributions', 'Fassungen freigeben') ?></button>
                </form>
            </details>
        <?php } else { ?>
            <p class="text-muted"><?= lang('Only the named contributions are included. Other outlets remain in progress. Author review is optional.', 'Nur die genannten Fassungen sind einbezogen. Andere Outlets bleiben in Bearbeitung. Eine Autorenprüfung ist optional.') ?></p>
            <div class="news-batch-options">
                <form action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <button class="btn success" name="action" value="release"><i class="ph ph-check-circle" aria-hidden="true"></i> <?= lang('Release contributions', 'Fassungen freigeben') ?></button>
                    <p class="text-muted"><?= lang('Complete the editorial work directly.', 'Die redaktionelle Bearbeitung direkt abschließen.') ?></p>
                </form>
                <form action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <button class="btn" name="action" value="request_review"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i> <?= lang('Request author review', 'Autorenprüfung anfordern') ?></button>
                    <p class="text-muted"><?= lang('Send all ready contributions together. Author approval completes their release.', 'Alle fertigen Fassungen gemeinsam versenden. Die Zustimmung schließt die Freigabe ab.') ?></p>
                </form>
            </div>
        <?php } ?>
    </div>
<?php } ?>
