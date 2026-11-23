<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">
<div class="container w-800 mw-full news-workflow">
    <h1>
        <i class="ph-duotone ph-export"></i>
        <?= t('news.export_news_report') ?>
    </h1>
    <p><?= t('news.select_the_original_contributions_or_one_outlet_outlet_reports_inc') ?></p>
    <p class="text-muted"><?= t('news.if_a_translation_is_empty_the_available_language_is_used_the_news') ?></p>
    <?php if ($error) { ?>
        <div class="alert danger mb-20" role="alert"><?= e($error) ?></div>
    <?php } ?>

    <h2><?= t('news.select_contributions') ?></h2>
    <form action="<?= ROOTPATH ?>/news/export" method="get" id="news-export-filters">
        <div class="form-group">
            <label for="report-channel"><?= t('news.channel') ?></label>
            <select class="form-control" id="report-channel" name="channel">
                <option value="original" <?= $filters['channel'] === 'original' ? 'selected' : '' ?>><?= t('news.original_contributions') ?></option>
                <?php foreach ($configuration['outlets'] as $outlet) { ?>
                    <option value="<?= e($outlet['id']) ?>" <?= $filters['channel'] === $outlet['id'] ? 'selected' : '' ?>>
                        <?= e(NewsOutlets::label($outlet['name'])) ?><?= empty($outlet['active']) ? t('news.inactive') : '' ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="row row-eq-spacing">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-from"><?= t('news.from_inclusive') ?></label>
                    <input type="date" class="form-control" id="report-from" name="from" value="<?= e($filters['from']) ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-until"><?= t('news.until_inclusive') ?></label>
                    <input type="date" class="form-control" id="report-until" name="until" value="<?= e($filters['until']) ?>">
                </div>
            </div>
        </div>
        <h2><?= t('news.output') ?></h2>
        <div class="row row-eq-spacing">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-language"><?= t('news.language') ?></label>
                    <select class="form-control" id="report-language" name="language">
                        <option value="de" <?= $filters['language'] === 'de' ? 'selected' : '' ?>>Deutsch</option>
                        <option value="en" <?= $filters['language'] === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-format"><?= t('news.format') ?></label>
                    <select class="form-control" id="report-format" name="format">
                        <option value="html" <?= $filters['format'] === 'html' ? 'selected' : '' ?>>HTML</option>
                        <option value="docx" <?= $filters['format'] === 'docx' ? 'selected' : '' ?>>Word (.docx)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="btn-toolbar">
            <button class="btn" type="submit"><?= t('news.show_selection') ?></button>
            <button class="btn primary" type="submit" name="download" value="1" <?= !$entries ? 'disabled' : '' ?>>
                <i class="ph ph-download-simple"></i> <?= t('news.export_report') ?>
            </button>
            <a class="btn" href="<?= ROOTPATH ?>/news"><?= t('news.back_to_news') ?></a>
        </div>
    </form>

    <p class="text-muted mt-20" id="news-export-selection-hint" role="status" hidden><?= t('news.filters_changed_show_the_selection_to_update_the_list_or_export_di') ?></p>
    <div id="news-export-selection">
    <h2 class="mt-20"><?= count($entries) ?> <?= count($entries) === 1 ? t('news.matching_contribution') : t('news.matching_contributions') ?></h2>
    <?php if (!$entries) { ?>
        <p class="text-muted"><?= t('news.no_contributions_match_these_filters') ?></p>
    <?php } else { ?>
        <ul>
            <?php foreach ($entries as $entry) { ?>
                <li>
                    <?= e($entry['date']) ?> ·
                    <a href="<?= ROOTPATH ?>/news/view/<?= e($entry['id']) ?>"><?= e(($entry['title'] ?? '') ?: (($entry['label'] ?? '') ?: t('news.contribution'))) ?></a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
    </div>
</div>

<script>
(() => {
    const form = document.getElementById('news-export-filters');
    const selection = document.getElementById('news-export-selection');
    const hint = document.getElementById('news-export-selection-hint');
    const filters = () => ['channel', 'from', 'until', 'language'].map(name => form.elements[name].value).join('|');
    const initial = filters();
    const download = form.querySelector('[name="download"]');
    const initiallyEmpty = download.disabled;
    const update = () => {
        const changed = filters() !== initial;
        selection.hidden = changed;
        hint.hidden = !changed;
        download.disabled = !changed && initiallyEmpty;
        form.elements.until.min = form.elements.from.value;
    };
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
})();
</script>
