<?php

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
    <h2><i class="ph-duotone ph-check-circle" aria-hidden="true"></i> <?= lang('Author review and release', 'Autorenprüfung und Freigabe') ?></h2>
    <?php foreach ($declinedOutlets as $declined) { ?>
        <details class="collapse-panel mb-20" open>
            <summary class="collapse-header">
                <?= e(NewsOutlets::label($reviewOutlets[$declined['outlet_id']]['name'] ?? ['en' => $declined['outlet_id']])) ?>
                <span class="badge danger"><?= e(NewsEditorial::statusLabel('declined')) ?></span>
            </summary>
            <div class="collapse-content">
                <?php if (!empty($declined['decline_reason'])) { ?>
                    <p><b><?= lang('Editorial reason', 'Begründung der Redaktion') ?></b></p>
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
                <?= $reviewPending && NewsEditorial::isAuthor($news) ? lang('Your review', 'Deine Prüfung') : lang('Author review', 'Autorenprüfung') ?> · <?= e(date('d.m.Y H:i', strtotime($review['requested_at']))) ?>
                <span class="badge <?= $reviewPending ? 'primary' : '' ?>"><?= e(NewsReview::statusLabel($review['status'])) ?></span>
            </summary>
            <div class="collapse-content">
                <p class="text-muted"><?= lang('Requested by', 'Angefordert von') ?> <?= e($DB->getNameFromId($review['requested_by'])) ?>.
                    <?= lang('The versions shown below were sent together for review. Your approval releases these contributions. Outlets enabled for Portfolio then appear there.', 'Die folgenden Fassungen wurden gemeinsam zur Prüfung geschickt. Mit deiner Zustimmung sind diese Beiträge freigegeben. Für Portfolio aktivierte Outlets erscheinen anschließend dort.') ?></p>
                <?php if ($reviewPending && !$reviewCurrent) { ?>
                    <div class="alert warning"><?= lang('The submitted outlet texts have changed. These contributions can no longer be approved. The editorial team needs to end this review and prepare a new one.', 'Die versendeten Outlet-Texte wurden geändert. Diese Beiträge können nicht mehr freigegeben werden. Die Redaktion muss diese Prüfung beenden und eine neue vorbereiten.') ?></div>
                <?php } ?>
                <?php foreach ($review['contributions'] as $snapshot) { ?>
                    <div class="box padded">
                        <h3 class="title"><?= e(NewsOutlets::label($snapshot['name'])) ?></h3>
                        <?php foreach (lang('en', 'de') === 'de' ? ['de', 'en'] : ['en', 'de'] as $language) {
                            $reviewTexts = array_filter($snapshot['fields'], static fn($field) => ($snapshot[$field][$language] ?? '') !== '');
                            if (!$reviewTexts) continue;
                        ?>
                            <h4><?= $language === 'de' ? 'Deutsch' : 'English' ?></h4>
                            <?php foreach ($reviewTexts as $field) { ?>
                                <div class="news-review-field"><b><?= match ($field) { 'title' => lang('Title', 'Titel'), 'teaser' => lang('Teaser', 'Teaser'), default => lang('Contribution', 'Beitrag') } ?></b>
                                    <div><?= render_rich_text($snapshot[$field][$language], $field === 'content' ? ($snapshot['format'] ?? 'plain') : 'plain') ?></div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if (!empty($review['comment'])) { ?>
                    <div class="alert warning"><b><?= lang('Requested change', 'Änderungswunsch') ?></b><br><?= nl2br(e($review['comment'])) ?></div>
                <?php } ?>
                <?php if (isset($review['closed_at'])) { ?>
                    <p class="text-muted"><?= e(NewsReview::statusLabel($review['status'])) ?> · <?= e($DB->getNameFromId($review['closed_by'])) ?> · <?= e(date('d.m.Y H:i', strtotime($review['closed_at']))) ?></p>
                <?php } ?>
                <?php if ($reviewPending && NewsEditorial::isAuthor($news)) { ?>
                    <?php if ($reviewCurrent) { ?>
                        <form action="<?= ROOTPATH ?>/crud/news/editorial/<?= e($news['_id']) ?>" method="post">
                            <?php $reviewFormFields($review); ?>
                            <button class="btn success" name="action" value="approve_review"><i class="ph ph-check" aria-hidden="true"></i> <?= lang('Approve all contributions', 'Alle Beiträge freigeben') ?></button>
                        </form>
                    <?php } ?>
                    <details class="mt-10">
                        <summary class="cursor-pointer"><?= lang('Request a change', 'Änderung anfragen') ?></summary>
                        <form class="mt-10" action="<?= ROOTPATH ?>/crud/news/editorial/<?= e($news['_id']) ?>" method="post">
                            <?php $reviewFormFields($review); ?>
                            <div class="form-group">
                                <label class="required" for="author-review-comment"><?= lang('What should be changed?', 'Was soll geändert werden?') ?></label>
                                <textarea class="form-control" id="author-review-comment" name="editorial[comment]" rows="3" maxlength="4000" required></textarea>
                            </div>
                            <button class="btn primary" name="action" value="request_changes"><?= lang('Send change request', 'Änderungswunsch senden') ?></button>
                        </form>
                    </details>
                <?php } ?>
            </div>
        </details>
    <?php } ?>
</section>
