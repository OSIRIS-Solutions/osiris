<?php

/** Authors only see the saved versions that were explicitly sent for review. */
if (!NewsRequests::isAuthor($news) && !$Settings->hasPermission('news.editorial')) return;
require_once BASEPATH . '/php/NewsReview.php';
$authorReviews = array_reverse(NewsReview::read($news));
$releasedOutlets = array_filter(NewsRequests::read($news), static fn($request) => ($request['status'] ?? '') === 'released');
if (!$authorReviews && !$releasedOutlets) return;
$reviewOutlets = array_column($Settings->getNewsOutlets()['outlets'], null, 'id');
$reviewFormFields = static function (array $review) use ($news) { ?>
    <input type="hidden" name="csrf_token" value="<?= e(NewsRequests::csrfToken()) ?>">
    <input type="hidden" name="news_revision" value="<?= (int) ($news['revision'] ?? 0) ?>">
    <input type="hidden" name="editorial[review_id]" value="<?= e($review['id']) ?>">
<?php }; ?>

<section class="mt-20" id="news-author-review">
    <h2><i class="ph-duotone ph-check-circle" aria-hidden="true"></i> <?= lang('Author review and release', 'Autorenprüfung und Freigabe') ?></h2>
    <?php foreach ($releasedOutlets as $released) { ?>
        <p><b><?= e(NewsRequests::label($reviewOutlets[$released['outlet_id']]['name'] ?? ['en' => $released['outlet_id']])) ?>:</b>
            <?= e(NewsReview::releaseLabel($released['release_mode'])) ?>
            · <?= e(date('d.m.Y H:i', strtotime($released['released_at']))) ?>
        </p>
    <?php } ?>
    <?php foreach ($authorReviews as $index => $review) {
        $reviewPending = $review['status'] === 'pending';
        $reviewCurrent = NewsReview::matches($review, $news, $Settings->getNewsOutlets());
    ?>
        <details class="collapse-panel mb-20" <?= $index === 0 ? 'open' : '' ?>>
            <summary class="collapse-header">
                <?= lang('Author review', 'Autorenprüfung') ?> · <?= e(date('d.m.Y H:i', strtotime($review['requested_at']))) ?>
                <span class="badge"><?= e(NewsReview::statusLabel($review['status'])) ?></span>
            </summary>
            <div class="collapse-content">
                <p class="text-muted"><?= lang('Requested by', 'Angefordert von') ?> <?= e($DB->getNameFromId($review['requested_by'])) ?>.
                    <?= lang('The versions shown below were sent together for review. Confirming them does not itself publish them.', 'Die folgenden Fassungen wurden gemeinsam zur Prüfung geschickt. Die Bestätigung veröffentlicht sie noch nicht.') ?></p>
                <?php if ($reviewPending && !$reviewCurrent) { ?>
                    <div class="alert warning"><?= lang('The original or outlet configuration has changed. These versions can no longer be confirmed. The editorial team needs to end this review and prepare a new one.', 'Der Originalbeitrag oder die Outlet-Konfiguration wurde geändert. Diese Fassungen können nicht mehr bestätigt werden. Die Redaktion muss diese Prüfung beenden und eine neue vorbereiten.') ?></div>
                <?php } ?>
                <?php foreach ($review['contributions'] as $snapshot) { ?>
                    <div class="box padded">
                        <h3 class="title"><?= e(NewsRequests::label($snapshot['name'])) ?> <small class="text-muted"><?= lang('Version', 'Version') ?> <?= (int) $snapshot['revision'] ?></small></h3>
                        <?php foreach (lang('en', 'de') === 'de' ? ['de', 'en'] : ['en', 'de'] as $language) {
                            $reviewTexts = array_filter($snapshot['fields'], static fn($field) => ($snapshot[$field][$language] ?? '') !== '');
                            if (!$reviewTexts) continue;
                        ?>
                            <h4><?= $language === 'de' ? 'Deutsch' : 'English' ?></h4>
                            <?php foreach ($reviewTexts as $field) { ?>
                                <p><b><?= match ($field) { 'title' => lang('Title', 'Titel'), 'teaser' => lang('Teaser', 'Teaser'), default => lang('Contribution', 'Beitrag') } ?></b><br><?= nl2br(e($snapshot[$field][$language])) ?></p>
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
                <?php if ($reviewPending && NewsRequests::isAuthor($news)) { ?>
                    <?php if ($reviewCurrent) { ?>
                        <form action="<?= ROOTPATH ?>/crud/news/editorial/<?= e($news['_id']) ?>" method="post">
                            <?php $reviewFormFields($review); ?>
                            <button class="btn success" name="action" value="approve_review"><i class="ph ph-check" aria-hidden="true"></i> <?= lang('Confirm all versions', 'Alle Fassungen bestätigen') ?></button>
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
