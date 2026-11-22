<?php
/** The legacy visibility control is only needed at installations without outlets. */
$publicationConfiguration = $Settings->getNewsOutlets();
$publicationNews = isset($news['_id']) ? DB::doc2Arr($news) : null;
?>
<?php if (!NewsEditorial::workflowAvailable($publicationConfiguration, $publicationNews ?? [])) { ?>
    <div class="form-group">
        <label for="news-visibility" class="required"><?= lang('Visibility', 'Sichtbarkeit') ?></label>
        <select name="news[visibility]" id="news-visibility" class="form-control w-auto" required>
            <option value="internal" <?= ($news['visibility'] ?? 'internal') === 'internal' ? 'selected' : '' ?>><?= lang('Internal', 'Intern') ?></option>
            <option value="public" <?= ($news['visibility'] ?? '') === 'public' ? 'selected' : '' ?>><?= lang('Public', 'Öffentlich') ?></option>
        </select>
        <small class="text-muted"><?= lang('Public news can appear in Portfolio if original contributions are enabled there.', 'Öffentliche News können in Portfolio erscheinen, wenn dort Originalbeiträge aktiviert sind.') ?></small>
    </div>
<?php } ?>
