<?php

/**
 * Display centrally managed documents as a filterable card library.
 *
 * @package OSIRIS
 * @since 2.2.0
 */

$formatSize = static function ($bytes): string {
    $bytes = max(0, (int) $bytes);
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    return number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB';
};

$categoryCounts = [];
$typeCounts = [];
$tagCounts = [];

foreach ($documents as $document) {
    $category = trim((string) ($document['category'] ?? ''));
    if ($category !== '') {
        $key = mb_strtolower($category);
        $categoryCounts[$key] ??= ['label' => $category, 'count' => 0];
        $categoryCounts[$key]['count']++;
    }

    $extension = strtolower(trim((string) ($document['extension'] ?? '')));
    if ($extension !== '') $typeCounts[$extension] = ($typeCounts[$extension] ?? 0) + 1;

    foreach (DB::doc2Arr($document['tags'] ?? []) as $tag) {
        $tag = trim((string) $tag);
        if ($tag === '') continue;
        $key = mb_strtolower($tag);
        $tagCounts[$key] ??= ['label' => $tag, 'count' => 0];
        $tagCounts[$key]['count']++;
    }
}

uasort($categoryCounts, static fn($a, $b) => strnatcasecmp($a['label'], $b['label']));
ksort($typeCounts, SORT_NATURAL | SORT_FLAG_CASE);
uasort($tagCounts, static fn($a, $b) => strnatcasecmp($a['label'], $b['label']));
?>

<style>
    .central-document-toolbar { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
    .central-document-search { flex: 1 1 24rem; margin: 0; position: relative; }
    .central-document-search > i { color: var(--muted-color); font-size: 1.8rem; left: 1.2rem; position: absolute; top: 50%; transform: translateY(-50%); }
    .central-document-search input { padding-left: 4rem; }
    .central-document-sort { flex: 0 0 18rem; margin: 0; }
    .central-document-result-meta { align-items: center; display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; margin-bottom: 1rem; min-height: 2.8rem; }
    .central-document-active-filters { display: flex; flex-wrap: wrap; gap: .5rem; }
    .central-document-active-filters .badge { align-items: center; display: inline-flex; gap: .5rem; }
    .central-document-active-filters button { background: none; border: 0; color: inherit; cursor: pointer; font-size: 1.6rem; line-height: 1; padding: 0; }
    .central-document-grid { display: grid; gap: 1.5rem; grid-template-columns: repeat(auto-fill, minmax(25rem, 1fr)); }
    .central-document-card { background: var(--box-bg-color); border: var(--border-width) solid var(--border-color); border-radius: var(--border-radius); display: flex; flex-direction: column; min-width: 0; overflow: hidden; transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
    .central-document-card:hover { border-color: var(--primary-color); box-shadow: 0 .6rem 1.8rem rgba(0, 0, 0, .08); transform: translateY(-.2rem); }
    .central-document-preview { align-items: center; background: var(--muted-color-very-light); display: flex; height: 15rem; justify-content: center; overflow: hidden; position: relative; }
    .central-document-preview img, .central-document-preview iframe { border: 0; height: 100%; object-fit: cover; pointer-events: none; width: 100%; }
    .central-document-preview iframe { background: white; object-fit: initial; position: absolute; inset: 0; }
    .central-document-pdf-preview { inset: 0; position: absolute; }
    .central-document-preview-link { inset: 0; position: absolute; z-index: 2; border-bottom: 1px solid var(--border-color);}
    .central-document-preview-link:focus { box-shadow: inset 0 0 0 .3rem var(--primary-color); }
    .central-document-file-fallback { align-items: center; color: var(--muted-color); display: flex; flex-direction: column; gap: .5rem; justify-content: center; }
    .central-document-file-fallback i { font-size: 5rem; }
    .central-document-extension { background: var(--box-bg-color); border-radius: var(--border-radius); color: var(--primary-color); font-size: 1.1rem; font-weight: 700; letter-spacing: .08em; padding: .25rem .6rem; }
    .central-document-category { align-self: flex-start; background: var(--primary-color-very-light); border-radius: 10rem; color: var(--primary-color-dark); display: inline-block; font-size: 1.1rem; font-weight: 600; margin-bottom: .8rem; max-width: 100%; overflow: hidden; padding: .3rem .8rem; text-overflow: ellipsis; white-space: nowrap; }
    .central-document-body { display: flex; flex: 1; flex-direction: column; padding: 1.5rem; }
    .central-document-title { font-size: 1.8rem; line-height: 1.25; margin: 0 0 .75rem; overflow-wrap: anywhere; }
    .central-document-description { color: var(--muted-color); display: -webkit-box; font-size: 1.3rem; line-height: 1.45; margin: 0 0 1rem; overflow: hidden; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
    .central-document-tags { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1rem; }
    .central-document-tags .badge { font-size: 1.05rem; font-weight: 400; }
    .central-document-spacer { flex: 1; }
    .central-document-details { border-top: var(--border-width) solid var(--border-color); color: var(--muted-color); font-size: 1.1rem; margin-top: .5rem; padding-top: 1rem; }
    .central-document-filename { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .central-document-footer { align-items: center; display: flex; gap: .75rem; justify-content: space-between; margin-top: 1.2rem; }
    .central-document-empty { padding: 5rem 2rem; text-align: center; }
    .central-document-empty > i { color: var(--muted-color); display: block; font-size: 5rem; margin-bottom: 1rem; }
    .central-document-filter-clear { font-size: 1.2rem; }
    .central-document-filter-table .active { background: var(--primary-color-very-light); }
    .central-document-filter-table a { align-items: center; display: flex; justify-content: space-between; }
    .central-document-filter-table .index { flex-shrink: 0; margin-left: 1rem; }
    @media (max-width: 767px) {
        .central-document-toolbar { align-items: stretch; flex-direction: column; }
        .central-document-sort { flex-basis: auto; }
        .central-document-grid { grid-template-columns: repeat(auto-fill, minmax(22rem, 1fr)); }
        .central-document-filter-column { margin-top: 2rem; }
    }
</style>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-10">
    <h1><i class="ph-duotone ph-files"></i> <?= lang('Documents', 'Dokumente') ?></h1>
    <?php if ($managePerm ?? false) { ?>
        <a href="<?= ROOTPATH ?>/documents/manage" class="btn primary"><i class="ph ph-file-plus"></i> <?= lang('Manage central documents', 'Zentrale Dokumente verwalten') ?></a>
    <?php } ?>
</div>

<?php if ((($centralPerm ?? false) || ($managePerm ?? false)) && ($connectPerm ?? false)) { ?>
    <div class="pills mb-20">
        <a href="<?= ROOTPATH ?>/documents/central" class="btn active"><?= lang('Central documents', 'Zentrale Dokumente') ?></a>
        <a href="<?= ROOTPATH ?>/documents/connected" class="btn"><?= lang('Connected documents', 'Verknüpfte Dokumente') ?></a>
    </div>
<?php } ?>

<?php if (empty($documents)) { ?>
    <div class="box central-document-empty">
        <i class="ph-duotone ph-files"></i>
        <h3 class="mt-0 mb-5"><?= lang('No central documents available yet', 'Noch keine zentralen Dokumente verfügbar') ?></h3>
        <p class="text-muted mt-0 mb-20"><?= lang('Central documents will appear here after they have been uploaded.', 'Zentrale Dokumente erscheinen hier, sobald sie hochgeladen wurden.') ?></p>
        <?php if ($managePerm ?? false) { ?>
            <a href="<?= ROOTPATH ?>/documents/manage" class="btn primary"><i class="ph ph-upload-simple"></i> <?= lang('Upload document', 'Dokument hochladen') ?></a>
        <?php } ?>
    </div>
<?php } else { ?>
    <div class="row row-eq-spacing">
        <div class="col-md-9 order-last order-md-first">
            <div class="central-document-toolbar">
                <label class="central-document-search">
                    <span class="sr-only"><?= lang('Search documents', 'Dokumente durchsuchen') ?></span>
                    <i class="ph ph-magnifying-glass"></i>
                    <input type="search" class="form-control" id="central-document-search" placeholder="<?= lang('Search title, description, category or tags …', 'Titel, Beschreibung, Kategorie oder Schlagwörter durchsuchen …') ?>">
                </label>
                <label class="central-document-sort">
                    <span class="sr-only"><?= lang('Sort documents', 'Dokumente sortieren') ?></span>
                    <select class="form-control" id="central-document-sort">
                        <option value="date-desc"><?= lang('Newest first', 'Neueste zuerst') ?></option>
                        <option value="date-asc"><?= lang('Oldest first', 'Älteste zuerst') ?></option>
                        <option value="title-asc"><?= lang('Title A–Z', 'Titel A–Z') ?></option>
                        <option value="title-desc"><?= lang('Title Z–A', 'Titel Z–A') ?></option>
                    </select>
                </label>
            </div>

            <div class="central-document-result-meta">
                <div id="central-document-active-filters" class="central-document-active-filters"></div>
                <span class="text-muted font-size-12" id="central-document-result-count"></span>
            </div>

            <div class="central-document-grid" id="central-document-grid">
                <?php foreach ($documents as $document) {
                    $id = (string) $document['_id'];
                    $title = trim((string) ($document['name'] ?? '')) ?: (string) ($document['filename'] ?? lang('Untitled document', 'Unbenanntes Dokument'));
                    $description = trim((string) ($document['description'] ?? ''));
                    $category = trim((string) ($document['category'] ?? ''));
                    $tags = array_values(array_filter(array_map(static fn($tag) => trim((string) $tag), DB::doc2Arr($document['tags'] ?? []))));
                    $filename = (string) ($document['filename'] ?? '');
                    $extension = strtolower((string) ($document['extension'] ?? ''));
                    $uploaded = (string) ($document['uploaded'] ?? '');
                    $timestamp = $uploaded !== '' ? (strtotime($uploaded) ?: 0) : 0;
                    $uploadedDate = $timestamp ? date('d.m.Y', $timestamp) : '–';
                    $uploader = !empty($document['uploaded_by']) ? (string) $DB->getNameFromId($document['uploaded_by']) : '';
                    $fileUrl = ROOTPATH . '/documents/central/file/' . $id;
                    $searchText = mb_strtolower(implode(' ', [$title, $description, $category, implode(' ', $tags), $filename, $uploader]));
                    $lowerTags = array_map('mb_strtolower', $tags);
                    $isImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);
                    $isPdf = $extension === 'pdf';
                ?>
                    <article class="central-document-card"
                        data-title="<?= e(mb_strtolower($title)) ?>"
                        data-search="<?= e($searchText) ?>"
                        data-category="<?= e(mb_strtolower($category)) ?>"
                        data-file-type="<?= e($extension) ?>"
                        data-tags="<?= e(json_encode($lowerTags, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>"
                        data-date="<?= e($timestamp) ?>">
                        <div class="central-document-preview">
                            <?php if ($isImage) { ?>
                                <img src="<?= e($fileUrl) ?>" alt="" loading="lazy">
                            <?php } else { ?>
                                <div class="central-document-file-fallback">
                                    <i class="ph-duotone ph-<?= e(getFileIcon($extension)) ?>"></i>
                                    <span class="central-document-extension"><?= e(strtoupper($extension ?: 'FILE')) ?></span>
                                </div>
                                <?php if ($isPdf) { ?>
                                    <!-- <div class="central-document-pdf-preview" data-pdf-url="<?= e($fileUrl) ?>" aria-hidden="true"></div> -->
                                <?php } ?>
                            <?php } ?>
                            <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="central-document-preview-link" aria-label="<?= e(lang('Open document', 'Dokument öffnen') . ': ' . $title) ?>"></a>
                        </div>

                        <div class="central-document-body">
                            <?php if ($category !== '') { ?><span class="central-document-category"><?= e($category) ?></span><?php } ?>
                            <h2 class="central-document-title"><a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener"><?= e($title) ?></a></h2>
                            <?php if ($description !== '') { ?><p class="central-document-description"><?= nl2br(e($description)) ?></p><?php } ?>
                            <?php if (!empty($tags)) { ?>
                                <div class="central-document-tags">
                                    <?php foreach ($tags as $tag) { ?><span class="badge"><?= e($tag) ?></span><?php } ?>
                                </div>
                            <?php } ?>
                            <div class="central-document-spacer"></div>
                            <div class="central-document-details">
                                <span class="central-document-filename" title="<?= e($filename) ?>"><?= e($filename) ?></span>
                                <span><?= e(strtoupper($extension)) ?> · <?= e($formatSize($document['size'] ?? 0)) ?> · <?= e($uploadedDate) ?></span>
                                <?php if ($uploader !== '') { ?><span> · <?= lang('by', 'von') ?> <?= e($uploader) ?></span><?php } ?>
                            </div>
                            <div class="central-document-footer">
                                <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="btn small primary"><i class="ph ph-arrow-square-out"></i> <?= lang('Open', 'Öffnen') ?></a>
                                <button type="button" class="btn small ml-auto" onclick="copyTextToClipboard('<?= e($fileUrl) ?>')" title="<?= lang('Copy link to clipboard', 'Link in die Zwischenablage kopieren') ?>"><i class="ph ph-copy"></i></button>
                                <a href="<?= e($fileUrl) ?>?download=1" class="btn small" title="<?= lang('Download', 'Herunterladen') ?>"><i class="ph ph-download-simple"></i></a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>

            <div id="central-document-no-results" class="box central-document-empty d-none">
                <i class="ph-duotone ph-magnifying-glass"></i>
                <h3 class="mt-0 mb-5"><?= lang('No matching documents', 'Keine passenden Dokumente') ?></h3>
                <p class="text-muted mt-0 mb-20"><?= lang('Change your search or reset the active filters.', 'Ändere die Suche oder setze die aktiven Filter zurück.') ?></p>
                <button type="button" class="btn" id="central-document-reset-empty"><i class="ph ph-arrow-counter-clockwise"></i> <?= lang('Reset filters', 'Filter zurücksetzen') ?></button>
            </div>
        </div>

        <div class="col-md-3 central-document-filter-column">
            <div class="filters content" id="central-document-filters">
                <div class="title d-flex align-items-center justify-content-between">
                    <span><?= lang('Filters', 'Filter') ?></span>
                    <button type="button" class="btn link central-document-filter-clear" id="central-document-reset"><?= lang('Reset', 'Zurücksetzen') ?></button>
                </div>

                <?php if (!empty($categoryCounts)) { ?>
                    <h6><?= lang('By category', 'Nach Kategorie') ?></h6>
                    <div class="filter"><table class="table small simple central-document-filter-table">
                        <?php foreach ($categoryCounts as $key => $info) { ?>
                            <tr><td><a href="#" data-filter-kind="category" data-filter-value="<?= e($key) ?>"><span><?= e($info['label']) ?></span><span class="index"><?= e($info['count']) ?></span></a></td></tr>
                        <?php } ?>
                    </table></div>
                <?php } ?>

                <?php if (!empty($typeCounts)) { ?>
                    <h6><?= lang('By file type', 'Nach Dateityp') ?></h6>
                    <div class="filter"><table class="table small simple central-document-filter-table">
                        <?php foreach ($typeCounts as $extension => $count) { ?>
                            <tr><td><a href="#" data-filter-kind="fileType" data-filter-value="<?= e($extension) ?>"><span><i class="ph ph-<?= e(getFileIcon($extension)) ?>"></i> <?= e(strtoupper($extension)) ?></span><span class="index"><?= e($count) ?></span></a></td></tr>
                        <?php } ?>
                    </table></div>
                <?php } ?>

                <?php if (!empty($tagCounts)) { ?>
                    <h6><?= lang('By tag', 'Nach Schlagwort') ?></h6>
                    <div class="filter" style="max-height: 22rem; overflow-y: auto;"><table class="table small simple central-document-filter-table">
                        <?php foreach ($tagCounts as $key => $info) { ?>
                            <tr><td><a href="#" data-filter-kind="tag" data-filter-value="<?= e($key) ?>"><span><?= e($info['label']) ?></span><span class="index"><?= e($info['count']) ?></span></a></td></tr>
                        <?php } ?>
                    </table></div>
                <?php } ?>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const grid = document.getElementById('central-document-grid');
            const cards = Array.from(grid.querySelectorAll('.central-document-card'));
            const search = document.getElementById('central-document-search');
            const sort = document.getElementById('central-document-sort');
            const count = document.getElementById('central-document-result-count');
            const activeFilters = document.getElementById('central-document-active-filters');
            const noResults = document.getElementById('central-document-no-results');
            const filterLabels = <?= json_encode([
                'category' => lang('Category', 'Kategorie'),
                'fileType' => lang('File type', 'Dateityp'),
                'tag' => lang('Tag', 'Schlagwort'),
                'oneResult' => lang('1 document', '1 Dokument'),
                'manyResults' => lang('%s documents', '%s Dokumente'),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
            const state = { category: '', fileType: '', tag: '', query: '' };

            function normalize(value) {
                return String(value || '').trim().toLocaleLowerCase();
            }

            function filterCards() {
                let visible = 0;
                cards.forEach(function(card) {
                    const tags = JSON.parse(card.dataset.tags || '[]');
                    const matches = (!state.query || card.dataset.search.includes(state.query)) &&
                        (!state.category || card.dataset.category === state.category) &&
                        (!state.fileType || card.dataset.fileType === state.fileType) &&
                        (!state.tag || tags.includes(state.tag));
                    card.classList.toggle('d-none', !matches);
                    if (matches) visible++;
                });

                count.textContent = visible === 1 ? filterLabels.oneResult : filterLabels.manyResults.replace('%s', visible);
                noResults.classList.toggle('d-none', visible !== 0);
                grid.classList.toggle('d-none', visible === 0);
                renderActiveFilters();
            }

            function renderActiveFilters() {
                activeFilters.innerHTML = '';
                ['category', 'fileType', 'tag'].forEach(function(kind) {
                    if (!state[kind]) return;
                    const selected = Array.from(document.querySelectorAll('[data-filter-kind="' + kind + '"]'))
                        .find(function(button) { return button.dataset.filterValue === state[kind]; });
                    const label = selected ? selected.querySelector('span').textContent.trim() : state[kind];
                    const badge = document.createElement('span');
                    badge.className = 'badge primary';
                    badge.append(document.createTextNode(filterLabels[kind] + ': ' + label));
                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.setAttribute('aria-label', '<?= e(lang('Remove filter', 'Filter entfernen')) ?>');
                    remove.innerHTML = '&times;';
                    remove.addEventListener('click', function() {
                        state[kind] = '';
                        updateFilterButtons();
                        filterCards();
                    });
                    badge.append(remove);
                    activeFilters.append(badge);
                });
            }

            function updateFilterButtons() {
                document.querySelectorAll('[data-filter-kind]').forEach(function(button) {
                    const active = state[button.dataset.filterKind] === button.dataset.filterValue;
                    button.closest('tr').classList.toggle('active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
            }

            function resetFilters() {
                state.category = '';
                state.fileType = '';
                state.tag = '';
                state.query = '';
                search.value = '';
                updateFilterButtons();
                filterCards();
                search.focus();
            }

            function sortCards() {
                const mode = sort.value;
                cards.sort(function(a, b) {
                    if (mode === 'title-asc') return a.dataset.title.localeCompare(b.dataset.title);
                    if (mode === 'title-desc') return b.dataset.title.localeCompare(a.dataset.title);
                    if (mode === 'date-asc') return Number(a.dataset.date) - Number(b.dataset.date);
                    return Number(b.dataset.date) - Number(a.dataset.date);
                }).forEach(function(card) { grid.append(card); });
            }

            document.querySelectorAll('[data-filter-kind]').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault();
                    const kind = button.dataset.filterKind;
                    state[kind] = state[kind] === button.dataset.filterValue ? '' : button.dataset.filterValue;
                    updateFilterButtons();
                    filterCards();
                });
            });

            search.addEventListener('input', function() {
                state.query = normalize(search.value);
                filterCards();
            });
            sort.addEventListener('change', sortCards);
            document.getElementById('central-document-reset').addEventListener('click', resetFilters);
            document.getElementById('central-document-reset-empty').addEventListener('click', resetFilters);

            // const pdfPreviews = document.querySelectorAll('.central-document-pdf-preview');
            // function loadPdfPreview(preview) {
            //     if (preview.dataset.loaded) return;
            //     const frame = document.createElement('iframe');
            //     frame.src = preview.dataset.pdfUrl + '#page=1&view=FitH&toolbar=0&navpanes=0&scrollbar=0';
            //     frame.title = '<?= e(lang('PDF preview', 'PDF-Vorschau')) ?>';
            //     frame.tabIndex = -1;
            //     preview.append(frame);
            //     preview.dataset.loaded = 'true';
            // }

            // if ('IntersectionObserver' in window) {
            //     const observer = new IntersectionObserver(function(entries) {
            //         entries.forEach(function(entry) {
            //             if (!entry.isIntersecting) return;
            //             loadPdfPreview(entry.target);
            //             observer.unobserve(entry.target);
            //         });
            //     }, { rootMargin: '250px' });
            //     pdfPreviews.forEach(function(preview) { observer.observe(preview); });
            // } else {
            //     pdfPreviews.forEach(loadPdfPreview);
            // }

            sortCards();
            filterCards();
        })();
    </script>
<?php } ?>
