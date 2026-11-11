<?php
$image = $this->form['image'] ?? '';
$uploadId = $this->form['_id'] ?? new MongoDB\BSON\ObjectId();
if (is_array($uploadId) || $uploadId instanceof Traversable) $uploadId = $uploadId['$oid'] ?? '';
?>
<div class="data-module col-sm-<?= $width ?>" data-module="image" id="module-project-image">
    <label for="project-image-file" class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <input type="hidden" name="values[image]" value="<?= e($image) ?>">
    <img class="project-image-preview w-400 mw-full" alt="<?= e($label) ?>" <?= $image === '' ? 'hidden' : 'src="' . e(ROOTPATH . '/uploads/' . $image) . '"' ?>>
    <input type="file" class="form-control" id="project-image-file" accept="image/jpeg,image/png,image/gif" <?= $required && $image === '' ? 'required' : '' ?>>
    <button type="button" class="btn remove-image"><?= lang('action.remove') ?></button>
    <p class="image-status" role="status"></p>
    <?= $this->render_help($help) ?>
</div>
<script>
(() => {
    const widget = document.getElementById('module-project-image');
    const value = widget.querySelector('input[type="hidden"]');
    const file = widget.querySelector('input[type="file"]');
    const preview = widget.querySelector('img');
    const status = widget.querySelector('.image-status');
    const form = widget.closest('form');
    const required = <?= json_encode($required) ?>;
    const uploads = <?= json_encode(ROOTPATH . '/uploads/', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    let uploading = false;
    const update = () => { preview.hidden = !value.value; if (value.value) preview.src = uploads + value.value; file.required = required && !value.value; };
    file.addEventListener('change', async () => {
        if (!file.files.length || uploading) return;
        uploading = true;
        file.disabled = true;
        widget.querySelector('.remove-image').disabled = true;
        status.textContent = <?= json_encode(lang('Uploading…', 'Wird hochgeladen…'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        try {
            const data = new FormData(); data.append('image', file.files[0]); data.append('project_id', <?= json_encode((string) $uploadId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
            const response = await fetch(<?= json_encode(ROOTPATH . '/crud/upload-image') ?>, {method: 'POST', body: data});
            const result = await response.json();
            if (!response.ok || !result.url || !result.url.startsWith(uploads)) throw new Error(result.error || 'Upload failed');
            value.value = result.url.slice(uploads.length);
            status.textContent = '';
            update();
        } catch (error) { status.textContent = error.message; file.value = ''; }
        finally { uploading = false; file.disabled = false; widget.querySelector('.remove-image').disabled = false; }
    });
    widget.querySelector('.remove-image').addEventListener('click', () => { value.value = ''; file.value = ''; update(); });
    form?.addEventListener('submit', event => { if (uploading) event.preventDefault(); });
    form?.addEventListener('reset', () => setTimeout(update, 0));
    update();
})();
</script>
