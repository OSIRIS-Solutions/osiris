<?php

if (!$Settings->featureEnabled('news-editorial')) return;

/** Batch actions apply only to the named ready or submitted outlet versions. */
$pendingReview = NewsReview::pending($news);
$preparedRequests = NewsEditorial::prepared($news);
$preparedNames = array_map(static fn($request) => NewsOutlets::label($editorialOutlets[$request['outlet_id']]['name'] ?? ['en' => $request['outlet_id']]), $preparedRequests);
?>
<?php if (!$editorialInternalOnly && ($preparedRequests || $pendingReview !== null)) { ?>
    <div class="box padded news-batch-actions" id="editorial-review-controls">
        <h3 class="title"><?= $pendingReview !== null ? t('news.awaiting_author_response') : t('news.release_ready_contributions') ?></h3>
        <p><b><?= e(implode(', ', $preparedNames)) ?></b></p>
        <?php if ($pendingReview !== null) { ?>
            <p class="text-muted"><?= t('news.the_submitted_contributions_stay_open_until_the_author_responds_or') ?></p>
            <div class="news-batch-options">
                <a href="#author-review-<?= e($pendingReview['id']) ?>"><?= t('news.show_submitted_versions') ?></a>
                <form action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <input type="hidden" name="editorial[review_id]" value="<?= e($pendingReview['id']) ?>">
                    <button class="btn small" name="action" value="cancel_review"><?= t('news.end_review_and_continue_editing') ?></button>
                </form>
            </div>
            <details class="mt-20">
                <summary class="cursor-pointer"><?= t('news.release_without_a_response') ?></summary>
                <form class="mt-10" action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <div class="custom-checkbox mb-10">
                        <input type="checkbox" id="release-without-response" name="editorial[confirm_without_response]" value="1" required>
                        <label for="release-without-response"><?= t('news.i_am_releasing_the_named_contributions_without_the_pending_author') ?></label>
                    </div>
                    <button class="btn success" name="action" value="release"><i class="ph ph-check-circle" aria-hidden="true"></i> <?= t('news.release_contributions') ?></button>
                </form>
            </details>
        <?php } else { ?>
            <p class="text-muted"><?= t('news.only_the_named_contributions_are_included_other_outlets_remain_in') ?></p>
            <div class="news-batch-options">
                <form action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <button class="btn success" name="action" value="release"><i class="ph ph-check-circle" aria-hidden="true"></i> <?= t('news.release_contributions') ?></button>
                    <p class="text-muted"><?= t('news.complete_the_editorial_work_directly') ?></p>
                </form>
                <form action="<?= $editorialAction ?>" method="post">
                    <?php $editorialHiddenFields(); ?>
                    <button class="btn" name="action" value="request_review"><i class="ph ph-paper-plane-tilt" aria-hidden="true"></i> <?= t('news.request_author_review') ?></button>
                    <p class="text-muted"><?= t('news.send_all_ready_contributions_together_author_approval_completes_th') ?></p>
                </form>
            </div>
        <?php } ?>
    </div>
<?php } ?>
