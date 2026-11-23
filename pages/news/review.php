<?php

if (!$Settings->featureEnabled('news-editorial')) return;

/** Authors only see the saved versions that were explicitly sent for review. */
if (!NewsEditorial::isAuthor($news) && !$Settings->hasPermission('news.editorial')) return;
require_once BASEPATH . '/php/NewsReview.php';
$authorReviews = array_reverse(NewsReview::read($news));
$releasedOutlets = array_filter(NewsEditorial::requests($news), static fn($request) => in_array($request['status'] ?? '', ['approved', 'released'], true));
$declinedOutlets = array_filter(NewsEditorial::requests($news), static fn($request) => ($request['status'] ?? '') === 'declined');
if (!$authorReviews && !$releasedOutlets && !$declinedOutlets) return;
$reviewOutlets = array_column($Settings->getNewsOutlets()['outlets'], null, 'id');
$reviewFormFields = static function (array $review) use ($news) { ?>
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token('news')) ?>">
    <input type="hidden" name="editorial[review_id]" value="<?= e($review['id']) ?>">
<?php }; ?>

<section class="news-workflow mt-20" id="news-author-review">
    <h2><i class="ph-duotone ph-check-circle" aria-hidden="true"></i> <?= t('news.author_review_and_release') ?></h2>
    <?php foreach ($declinedOutlets as $declined) { ?>
        <details class="collapse-panel mb-20" open>
            <summary class="collapse-header">
                <?= e(NewsOutlets::label($reviewOutlets[$declined['outlet_id']]['name'] ?? ['en' => $declined['outlet_id']])) ?>
                <span class="badge danger"><?= e(NewsEditorial::statusLabel('declined')) ?></span>
            </summary>
            <div class="collapse-content">
                <?php if (!empty($declined['decline_reason'])) { ?>
                    <p><b><?= t('news.editorial_reason') ?></b></p>
                    <p><?= nl2br(e($declined['decline_reason'])) ?></p>
                <?php } ?>
                <?php if (!empty($declined['declined_by']) || !empty($declined['declined_at'])) { ?>
                    <p class="text-muted">
                        <?php if (!empty($declined['declined_by'])) { ?><?= e($DB->getNameFromId($declined['declined_by'])) ?><?php } ?>
                        <?php if (!empty($declined['declined_at'])) { ?> · <?= e(date('d.m.Y H:i', strtotime($declined['declined_at']))) ?><?php } ?>
                    </p>
                <?php } ?>
            </div>
        </details>
    <?php } ?>
    <?php foreach ($releasedOutlets as $released) {
        $releaseReview = array_column($authorReviews, null, 'id')[$released['author_review_id'] ?? ''] ?? [];
        $releaseDate = $released['released_at'] ?? $releaseReview['closed_at'] ?? null;
    ?>
        <p><b><?= e(NewsOutlets::label($reviewOutlets[$released['outlet_id']]['name'] ?? ['en' => $released['outlet_id']])) ?>:</b>
            <?= e(NewsEditorial::releaseLabel($released['release_mode'] ?? 'author_approved')) ?>
            <?php if ($releaseDate) { ?> · <?= e(date('d.m.Y H:i', strtotime($releaseDate))) ?><?php } ?>
        </p>
    <?php } ?>
    <?php foreach ($authorReviews as $review) {
        $reviewPending = $review['status'] === 'pending';
        $reviewCurrent = NewsReview::matches($review, $news);
    ?>
        <details class="collapse-panel mb-20" id="author-review-<?= e($review['id']) ?>" <?= $reviewPending ? 'open' : '' ?>>
            <summary class="collapse-header">
                <?= $reviewPending && NewsEditorial::isAuthor($news) ? t('news.your_review') : t('news.author_review') ?> · <?= e(date('d.m.Y H:i', strtotime($review['requested_at']))) ?>
                <span class="badge <?= $reviewPending ? 'primary' : '' ?>"><?= e(NewsReview::statusLabel($review['status'])) ?></span>
            </summary>
            <div class="collapse-content">
                <p class="text-muted"><?= t('news.requested_by') ?> <?= e($DB->getNameFromId($review['requested_by'])) ?>.
                    <?= t('news.the_versions_shown_below_were_sent_together_for_review_your_approv') ?></p>
                <?php if ($reviewPending && !$reviewCurrent) { ?>
                    <div class="alert warning"><?= t('news.the_submitted_outlet_texts_have_changed_these_contributions_can_no') ?></div>
                <?php } ?>
                <?php foreach ($review['contributions'] as $snapshot) { ?>
                    <div class="box padded">
                        <h3 class="title"><?= e(NewsOutlets::label($snapshot['name'])) ?></h3>
                        <?php foreach (['en', 'de'] as $language) {
                            $reviewTexts = array_filter($snapshot['fields'], static fn($field) => ($snapshot[$field][$language] ?? '') !== '');
                            if (!$reviewTexts) continue;
                        ?>
                            <h4><?= $language === 'de' ? 'Deutsch' : 'English' ?></h4>
                            <?php foreach ($reviewTexts as $field) { ?>
                                <div class="news-review-field"><b><?= match ($field) { 'title' => t('common.title'), 'teaser' => t('news.teaser'), default => t('news.contribution') } ?></b>
                                    <div><?= render_rich_text($snapshot[$field][$language], $field === 'content' ? ($snapshot['format'] ?? 'plain') : 'plain') ?></div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if (!empty($review['comment'])) { ?>
                    <div class="alert warning"><b><?= t('news.requested_change') ?></b><br><?= nl2br(e($review['comment'])) ?></div>
                <?php } ?>
                <?php if (isset($review['closed_at'])) { ?>
                    <p class="text-muted"><?= e(NewsReview::statusLabel($review['status'])) ?> · <?= e($DB->getNameFromId($review['closed_by'])) ?> · <?= e(date('d.m.Y H:i', strtotime($review['closed_at']))) ?></p>
                <?php } ?>
                <?php if ($reviewPending && NewsEditorial::isAuthor($news)) { ?>
                    <?php if ($reviewCurrent) { ?>
                        <form action="<?= ROOTPATH ?>/crud/news/editorial/<?= e($news['_id']) ?>" method="post">
                            <?php $reviewFormFields($review); ?>
                            <button class="btn success" name="action" value="approve_review"><i class="ph ph-check" aria-hidden="true"></i> <?= t('news.approve_all_contributions') ?></button>
                        </form>
                    <?php } ?>
                    <details class="mt-10">
                        <summary class="cursor-pointer"><?= t('news.request_a_change') ?></summary>
                        <form class="mt-10" action="<?= ROOTPATH ?>/crud/news/editorial/<?= e($news['_id']) ?>" method="post">
                            <?php $reviewFormFields($review); ?>
                            <div class="form-group">
                                <label class="required" for="author-review-comment"><?= t('news.what_should_be_changed') ?></label>
                                <textarea class="form-control" id="author-review-comment" name="editorial[comment]" rows="3" maxlength="4000" required></textarea>
                            </div>
                            <button class="btn primary" name="action" value="request_changes"><?= t('news.send_change_request') ?></button>
                        </form>
                    </details>
                <?php } ?>
            </div>
        </details>
    <?php } ?>
</section>
