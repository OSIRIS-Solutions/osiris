<?php
/** The legacy visibility control is only needed at installations without outlets. */
$publicationConfiguration = $Settings->getNewsOutlets();
$publicationNews = isset($news['_id']) ? DB::doc2Arr($news) : null;
?>
<?php if (!NewsEditorial::workflowAvailable($publicationConfiguration, $publicationNews ?? [])) { ?>
    <div class="form-group">
        <label for="news-visibility" class="required"><?= t('common.visibility') ?></label>
        <select name="news[visibility]" id="news-visibility" class="form-control w-auto" required>
            <option value="internal" <?= ($news['visibility'] ?? 'internal') === 'internal' ? 'selected' : '' ?>><?= t('common.internal') ?></option>
            <option value="public" <?= ($news['visibility'] ?? '') === 'public' ? 'selected' : '' ?>><?= t('common.public') ?></option>
        </select>
        <small class="text-muted"><?= t('news.public_news_can_appear_in_portfolio_if_original_contributions_are') ?></small>
    </div>
<?php } ?>
