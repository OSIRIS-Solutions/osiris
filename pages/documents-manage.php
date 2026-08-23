<?php

/**
 * Manage centrally available documents.
 *
 * @package OSIRIS
 * @since 2.2.0
 */

$categories = [];
foreach ($documents as $document) {
    $category = trim((string) ($document['category'] ?? ''));
    if ($category !== '') $categories[mb_strtolower($category)] = $category;
}
natcasesort($categories);

$formatSize = static function ($bytes): string {
    $bytes = max(0, (int) $bytes);
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return number_format($bytes / 1024, 1, ',', '.') . ' KB';
    return number_format($bytes / (1024 * 1024), 1, ',', '.') . ' MB';
};

$acceptedFiles = '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp,.rtf,.txt,.csv,.zip,.jpg,.jpeg,.png';

$filesize = Settings::getMaxFileSize('25M');
?>

<style>
    #central-documents-table td { vertical-align: middle; }
    .central-document-file { min-width: 22rem; }
    .central-document-icon { align-items: center; background: var(--muted-color-very-light); border-radius: var(--border-radius); display: inline-flex; flex: 0 0 4rem; height: 4rem; justify-content: center; }
    .central-document-icon i { font-size: 2.2rem; }
    .central-document-tags { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .5rem; }
    .central-document-tags .badge { font-weight: 400; }
    .central-document-actions { white-space: nowrap; }
    .central-document-empty { padding: 4rem 2rem; text-align: center; }
    .central-document-empty>i { color: var(--muted-color); display: block; font-size: 5rem; margin-bottom: 1rem; }
    .central-document-form-note { border-left: .3rem solid var(--primary-color); padding-left: 1rem; }
    @media (max-width: 767px) {
        .central-document-file { min-width: 0; }
        .central-document-meta-column { display: none; }
    }
</style>

<div class="container">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-10 mb-20">
        <div>
            <h1 class="mb-5">
                <i class="ph-duotone ph-files"></i>
                <?= lang('Manage central documents', 'Zentrale Dokumente verwalten') ?>
            </h1>
            <p class="text-muted mt-0 mb-0">
                <?= lang(
                    'Upload documents once and make them available throughout OSIRIS via a stable link.',
                    'Lade Dokumente einmalig hoch und stelle sie über einen stabilen Link in OSIRIS bereit.'
                ) ?>
            </p>
        </div>
        <button type="button" class="btn primary" data-toggle="modal" data-target="central-document-upload">
            <i class="ph ph-upload-simple"></i>
            <?= lang('Upload document', 'Dokument hochladen') ?>
        </button>
    </div>

    <div class="">
        <?php if (empty($documents)) { ?>
            <div class="central-document-empty">
                <i class="ph-duotone ph-file-plus"></i>
                <h3 class="mt-0 mb-5"><?= lang('No central documents yet', 'Noch keine zentralen Dokumente') ?></h3>
                <p class="text-muted mt-0">
                    <?= lang(
                        'Upload the first document to start building the central library.',
                        'Lade das erste Dokument hoch, um die zentrale Bibliothek aufzubauen.'
                    ) ?>
                </p>
                <button type="button" class="btn primary" data-toggle="modal" data-target="central-document-upload">
                    <i class="ph ph-plus"></i> <?= lang('Upload first document', 'Erstes Dokument hochladen') ?>
                </button>
            </div>
        <?php } else { ?>
            <table id="central-documents-table" class="table table-hover">
                <thead>
                    <tr>
                        <th><?= lang('Document', 'Dokument') ?></th>
                        <th><?= lang('Category', 'Kategorie') ?></th>
                        <th class="central-document-meta-column"><?= lang('Last file upload', 'Letzter Datei-Upload') ?></th>
                        <th class="text-right"><?= lang('Actions', 'Aktionen') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document) {
                        $id = (string) $document['_id'];
                        $title = trim((string) ($document['name'] ?? '')) ?: (string) ($document['filename'] ?? lang('Untitled document', 'Unbenanntes Dokument'));
                        $description = trim((string) ($document['description'] ?? ''));
                        $category = trim((string) ($document['category'] ?? ''));
                        $tags = DB::doc2Arr($document['tags'] ?? []);
                        $filename = (string) ($document['filename'] ?? '');
                        $extension = strtolower((string) ($document['extension'] ?? ''));
                        $uploaded = (string) ($document['uploaded'] ?? '');
                        $uploadedDate = $uploaded !== '' ? date('d.m.Y H:i', strtotime($uploaded)) : '–';
                        $uploader = !empty($document['uploaded_by']) ? $DB->getNameFromId($document['uploaded_by']) : '';
                        $fileUrl = ROOTPATH . '/documents/central/file/' . $id;
                        $editData = json_encode([
                            'id' => $id,
                            'name' => $title,
                            'description' => $description,
                            'category' => $category,
                            'tags' => implode(', ', $tags),
                            'filename' => $filename,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    ?>
                        <tr>
                            <td class="central-document-file">
                                <div class="d-flex align-items-start gap-10">
                                    <span class="central-document-icon">
                                        <i class="ph ph-<?= e(getFileIcon($extension)) ?> text-muted"></i>
                                    </span>
                                    <div class="overflow-hidden">
                                        <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="font-weight-bold">
                                            <?= e($title) ?> <i class="ph ph-arrow-square-out"></i>
                                        </a>
                                        <?php if ($description !== '') { ?>
                                            <div class="text-muted font-size-12 mt-5"><?= nl2br(e($description)) ?></div>
                                        <?php } ?>
                                        <div class="text-muted font-size-12 mt-5">
                                            <?= e($filename) ?> · <?= e(strtoupper($extension)) ?> · <?= e($formatSize($document['size'] ?? 0)) ?>
                                        </div>
                                        <?php if (!empty($tags)) { ?>
                                            <div class="central-document-tags">
                                                <?php foreach ($tags as $tag) { ?>
                                                    <span class="badge"><?= e($tag) ?></span>
                                                <?php } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>
                            <td><?= $category !== '' ? e($category) : '<span class="text-muted">–</span>' ?></td>
                            <td class="central-document-meta-column">
                                <span class="d-none"><?= e($uploaded) ?></span>
                                <?= e($uploadedDate) ?>
                                <?php if ($uploader !== '') { ?>
                                    <div class="text-muted font-size-12"><?= lang('by', 'von') ?> <?= e($uploader) ?></div>
                                <?php } ?>
                            </td>
                            <td class="text-right central-document-actions">
                                <div class="btn-group">
                                    <a href="<?= e($fileUrl) ?>?download=1" class="btn small" title="<?= lang('Download', 'Herunterladen') ?>">
                                        <i class="ph ph-download-simple"></i>
                                    </a>
                                    <button type="button" class="btn small edit-central-document" data-toggle="modal" data-target="central-document-edit" data-document="<?= e($editData) ?>" title="<?= lang('Edit metadata', 'Metadaten bearbeiten') ?>">
                                        <i class="ph ph-pencil-simple"></i>
                                    </button>
                                    <button type="button" class="btn small replace-central-document" data-toggle="modal" data-target="central-document-replace" data-document="<?= e($editData) ?>" title="<?= lang('Replace file', 'Datei ersetzen') ?>">
                                        <i class="ph ph-arrows-clockwise"></i>
                                    </button>
                                </div>
                                <form action="<?= ROOTPATH ?>/crud/documents/central/delete/<?= e($id) ?>" method="post" class="d-inline" onsubmit="return confirm('<?= e(lang('Delete this central document permanently?', 'Dieses zentrale Dokument endgültig löschen?')) ?>')">
                                    <button type="submit" class="btn small danger" title="<?= lang('Delete', 'Löschen') ?>">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<datalist id="central-document-categories">
    <?php foreach ($categories as $category) { ?>
        <option value="<?= e($category) ?>">
    <?php } ?>
</datalist>

<div class="modal" id="central-document-upload" tabindex="-1" role="dialog" data-overlay-dismissal-disabled>
    <div class="modal-dialog" role="document">
        <div class="modal-content w-600 mw-full">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            <h4 class="title"><i class="ph ph-upload-simple"></i> <?= lang('Upload central document', 'Zentrales Dokument hochladen') ?></h4>

            <form action="<?= ROOTPATH ?>/crud/documents/central/upload" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="required" for="central-document-upload-title"><?= lang('Title', 'Titel') ?></label>
                    <input type="text" class="form-control" id="central-document-upload-title" name="values[name]" maxlength="200" required>
                </div>
                <div class="form-group">
                    <label for="central-document-upload-description"><?= lang('Short description', 'Kurzbeschreibung') ?></label>
                    <textarea class="form-control" id="central-document-upload-description" name="values[description]" maxlength="500" rows="3"></textarea>
                    <small class="text-muted"><?= lang('Plain text, maximum 500 characters.', 'Nur Text, maximal 500 Zeichen.') ?></small>
                </div>
                <div class="row row-eq-spacing">
                    <div class="col-md-6">
                        <label for="central-document-upload-category"><?= lang('Category', 'Kategorie') ?></label>
                        <input type="text" class="form-control" id="central-document-upload-category" name="values[category]" maxlength="100" list="central-document-categories">
                    </div>
                    <div class="col-md-6">
                        <label for="central-document-upload-tags"><?= lang('Tags', 'Schlagwörter') ?></label>
                        <input type="text" class="form-control" id="central-document-upload-tags" name="values[tags]" placeholder="<?= lang('guideline, branding, template', 'Richtlinie, Marke, Vorlage') ?>">
                    </div>
                </div>
                <div class="form-group mt-10">
                    <label class="required" for="central-document-upload-file"><?= lang('File', 'Datei') ?></label>
                    <div class="custom-file">
                        <input type="file" id="central-document-upload-file" name="file" class="custom-file-input" accept="<?= e($acceptedFiles) ?>" maxsize="<?= $filesize['bytes'] ?>" required>
                        <label for="central-document-upload-file" class="custom-file-label"><?= lang('Choose a file', 'Datei auswählen') ?></label>
                    </div>
                    <small class="text-muted"><?= lang('Common office documents, PDFs, text files, ZIP archives and images up to ' . $filesize['human'] . '.', 'Gängige Office-Dokumente, PDFs, Textdateien, ZIP-Archive und Bilder bis ' . $filesize['human'] . '.') ?></small>
                </div>
                <button type="submit" class="btn primary mt-10"><i class="ph ph-upload-simple"></i> <?= lang('Upload document', 'Dokument hochladen') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="central-document-edit" tabindex="-1" role="dialog" data-overlay-dismissal-disabled>
    <div class="modal-dialog" role="document">
        <div class="modal-content w-600 mw-full">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            <h4 class="title"><i class="ph ph-pencil-simple"></i> <?= lang('Edit document', 'Dokument bearbeiten') ?></h4>
            <form action="" method="post" id="central-document-edit-form">
                <div class="form-group">
                    <label class="required" for="central-document-edit-title"><?= lang('Title', 'Titel') ?></label>
                    <input type="text" class="form-control" id="central-document-edit-title" name="values[name]" maxlength="200" required>
                </div>
                <div class="form-group">
                    <label for="central-document-edit-description"><?= lang('Short description', 'Kurzbeschreibung') ?></label>
                    <textarea class="form-control" id="central-document-edit-description" name="values[description]" maxlength="500" rows="3"></textarea>
                </div>
                <div class="row row-eq-spacing">
                    <div class="col-md-6">
                        <label for="central-document-edit-category"><?= lang('Category', 'Kategorie') ?></label>
                        <input type="text" class="form-control" id="central-document-edit-category" name="values[category]" maxlength="100" list="central-document-categories">
                    </div>
                    <div class="col-md-6">
                        <label for="central-document-edit-tags"><?= lang('Tags', 'Schlagwörter') ?></label>
                        <input type="text" class="form-control" id="central-document-edit-tags" name="values[tags]">
                    </div>
                </div>
                <p class="text-muted font-size-12 central-document-form-note mt-20 mb-10">
                    <?= lang('Editing metadata does not change the file or its stable link.', 'Das Bearbeiten der Metadaten verändert weder die Datei noch ihren stabilen Link.') ?>
                </p>
                <button type="submit" class="btn primary"><i class="ph ph-floppy-disk"></i> <?= lang('Save changes', 'Änderungen speichern') ?></button>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="central-document-replace" tabindex="-1" role="dialog" data-overlay-dismissal-disabled>
    <div class="modal-dialog" role="document">
        <div class="modal-content w-600 mw-full">
            <a data-dismiss="modal" class="btn float-right" role="button" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            <h4 class="title"><i class="ph ph-arrows-clockwise"></i> <?= lang('Replace file', 'Datei ersetzen') ?></h4>
            <form action="" method="post" enctype="multipart/form-data" id="central-document-replace-form">
                <p>
                    <?= lang('Current file:', 'Aktuelle Datei:') ?>
                    <strong id="central-document-replace-filename"></strong>
                </p>
                <div class="alert signal">
                    <?= lang(
                        'The title and metadata are retained. Existing links continue to work after the file has been replaced.',
                        'Titel und Metadaten bleiben erhalten. Bestehende Links funktionieren auch nach dem Ersetzen weiter.'
                    ) ?>
                </div>
                <div class="form-group">
                    <label class="required" for="central-document-replace-file"><?= lang('New file', 'Neue Datei') ?></label>
                    <div class="custom-file">
                        <input type="file" id="central-document-replace-file" name="file" class="custom-file-input" accept="<?= e($acceptedFiles) ?>" maxsize="<?= $filesize['bytes'] ?>" required>
                        <label for="central-document-replace-file" class="custom-file-label"><?= lang('Choose replacement file', 'Ersatzdatei auswählen') ?></label>
                    </div>
                    <small class="text-muted"><?= lang('Maximum file size: ' . $filesize['human'] . '.', 'Maximale Dateigröße: ' . $filesize['human'] . '.') ?></small>
                </div>
                <button type="submit" class="btn primary"><i class="ph ph-arrows-clockwise"></i> <?= lang('Replace file', 'Datei ersetzen') ?></button>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.edit-central-document').forEach(function(button) {
        button.addEventListener('click', function() {
            const documentData = JSON.parse(button.dataset.document);
            const form = document.getElementById('central-document-edit-form');
            form.action = '<?= ROOTPATH ?>/crud/documents/central/update/' + documentData.id;
            document.getElementById('central-document-edit-title').value = documentData.name || '';
            document.getElementById('central-document-edit-description').value = documentData.description || '';
            document.getElementById('central-document-edit-category').value = documentData.category || '';
            document.getElementById('central-document-edit-tags').value = documentData.tags || '';
        });
    });

    document.querySelectorAll('.replace-central-document').forEach(function(button) {
        button.addEventListener('click', function() {
            const documentData = JSON.parse(button.dataset.document);
            const form = document.getElementById('central-document-replace-form');
            form.action = '<?= ROOTPATH ?>/crud/documents/central/replace/' + documentData.id;
            form.reset();
            document.getElementById('central-document-replace-filename').textContent = documentData.filename || documentData.name;
        });
    });

    <?php if (!empty($documents)) { ?>
        $('#central-documents-table').DataTable({
            pageLength: 25,
            order: [[2, 'desc']],
            columnDefs: [{ orderable: false, searchable: false, targets: 3 }],
            language: {
                emptyTable: '<?= e(lang('No central documents found.', 'Keine zentralen Dokumente gefunden.')) ?>',
                search: '<?= e(lang('Search:', 'Suchen:')) ?>'
            }
        });
    <?php } ?>
</script>
