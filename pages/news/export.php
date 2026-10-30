<link rel="stylesheet" href="<?= ROOTPATH ?>/css/news-workflow.css?v=<?= OSIRIS_BUILD ?>">
<div class="container w-800 mw-full news-workflow">
    <h1>
        <i class="ph-duotone ph-export"></i>
        <?= lang('Export news report', 'News-Bericht exportieren') ?>
    </h1>
    <p><?= lang('Select the original contributions or one outlet. Outlet reports include released contributions only. The date range refers to the news date.', 'Wähle die Originalbeiträge oder ein Outlet. Outlet-Berichte enthalten ausschließlich freigegebene Beiträge. Der Zeitraum bezieht sich auf das Datum der Nachricht.') ?></p>
    <p class="text-muted"><?= lang('If a translation is empty, the available language is used. The news cover image is included automatically. Portfolio settings do not affect this internal report.', 'Fehlt eine Übersetzung, wird die vorhandene Sprache verwendet. Das Titelbild der Neuigkeit wird automatisch mit exportiert. Portfolio-Einstellungen beeinflussen diesen internen Bericht nicht.') ?></p>
    <?php if ($error) { ?>
        <div class="alert danger mb-20" role="alert"><?= e($error) ?></div>
    <?php } ?>

    <h2><?= lang('Select contributions', 'Beiträge auswählen') ?></h2>
    <form action="<?= ROOTPATH ?>/news/export" method="get" id="news-export-filters">
        <div class="form-group">
            <label for="report-channel"><?= lang('Channel', 'Kanal') ?></label>
            <select class="form-control" id="report-channel" name="channel">
                <option value="original" <?= $filters['channel'] === 'original' ? 'selected' : '' ?>><?= lang('Original contributions', 'Originalbeiträge') ?></option>
                <?php foreach ($configuration['outlets'] as $outlet) { ?>
                    <option value="<?= e($outlet['id']) ?>" <?= $filters['channel'] === $outlet['id'] ? 'selected' : '' ?>>
                        <?= e(NewsOutlets::label($outlet['name'])) ?><?= empty($outlet['active']) ? lang(' (inactive)', ' (inaktiv)') : '' ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="row row-eq-spacing">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-from"><?= lang('From (inclusive)', 'Von (einschließlich)') ?></label>
                    <input type="date" class="form-control" id="report-from" name="from" value="<?= e($filters['from']) ?>">
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-until"><?= lang('Until (inclusive)', 'Bis (einschließlich)') ?></label>
                    <input type="date" class="form-control" id="report-until" name="until" value="<?= e($filters['until']) ?>">
                </div>
            </div>
        </div>
        <h2><?= lang('Output', 'Ausgabe') ?></h2>
        <div class="row row-eq-spacing">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-language"><?= lang('Language', 'Sprache') ?></label>
                    <select class="form-control" id="report-language" name="language">
                        <option value="de" <?= $filters['language'] === 'de' ? 'selected' : '' ?>>Deutsch</option>
                        <option value="en" <?= $filters['language'] === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="report-format"><?= lang('Format', 'Format') ?></label>
                    <select class="form-control" id="report-format" name="format">
                        <option value="html" <?= $filters['format'] === 'html' ? 'selected' : '' ?>>HTML</option>
                        <option value="docx" <?= $filters['format'] === 'docx' ? 'selected' : '' ?>>Word (.docx)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="btn-toolbar">
            <button class="btn" type="submit"><?= lang('Show selection', 'Auswahl anzeigen') ?></button>
            <button class="btn primary" type="submit" name="download" value="1" <?= !$entries ? 'disabled' : '' ?>>
                <i class="ph ph-download-simple"></i> <?= lang('Export report', 'Bericht exportieren') ?>
            </button>
            <a class="btn" href="<?= ROOTPATH ?>/news"><?= lang('Back to news', 'Zurück zu News') ?></a>
        </div>
    </form>

    <p class="text-muted mt-20" id="news-export-selection-hint" role="status" hidden><?= lang('Filters changed. Show the selection to update the list, or export directly using the new filters.', 'Filter geändert. Mit „Auswahl anzeigen“ aktualisierst du die Liste. Der Export verwendet bereits die neuen Filter.') ?></p>
    <div id="news-export-selection">
    <h2 class="mt-20"><?= count($entries) ?> <?= count($entries) === 1 ? lang('matching contribution', 'passender Beitrag') : lang('matching contributions', 'passende Beiträge') ?></h2>
    <?php if (!$entries) { ?>
        <p class="text-muted"><?= lang('No contributions match these filters.', 'Keine Beiträge für diese Filter gefunden.') ?></p>
    <?php } else { ?>
        <ul>
            <?php foreach ($entries as $entry) { ?>
                <li>
                    <?= e($entry['date']) ?> ·
                    <a href="<?= ROOTPATH ?>/news/view/<?= e($entry['id']) ?>"><?= e(($entry['title'] ?? '') ?: (($entry['label'] ?? '') ?: lang('Contribution', 'Beitrag'))) ?></a>
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
