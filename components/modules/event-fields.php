<?php
/** Event and deadline inputs retain their established storage keys and presentation. */
$field = match ($module) {
    'event-title', 'deadline-title' => 'title',
    'deadline-date' => 'date',
    default => $module,
};
$typeVocabulary = $module === 'deadline-type' ? 'deadline-type' : 'event-type';
?>
<?php if (in_array($module, ['event-type', 'deadline-type'], true)): ?>
<div class="data-module floating-form col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
    <select class="form-control" name="values[type]" id="<?= e($module) ?>" <?= $labelClass ?> placeholder=" ">
        <?php if ($this->val('type') !== '' && !in_array($this->val('type'), array_column($Vocabulary->getValues($typeVocabulary, true), 'id'), true)): ?>
            <option value="<?= $this->val('type') ?>" selected><?= $this->val('type') ?></option>
        <?php endif; ?>
        <?php foreach ($Vocabulary->getValues($typeVocabulary, true) as $option):
            if (!empty($option['inactive']) && $option['id'] !== $this->val('type')) continue;
        ?>
            <option value="<?= e($option['id']) ?>" <?= $this->val('type') === $option['id'] ? 'selected' : '' ?>><?= e(lang($option['en'], $option['de'] ?? null)) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="<?= e($module) ?>" class="<?= $labelClass ?>"><?= e($label) ?></label>
    <?= $this->render_help($help) ?>
</div>
<?php elseif (in_array($module, ['event-description', 'deadline-description'], true)): ?>
<div class="data-module col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
    <label for="<?= e($module) ?>" class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <div class="form-group title-editor" id="<?= e($module) ?>-quill"><?= $this->form['description'] ?? '' ?></div>
    <textarea name="values[description]" id="<?= e($module) ?>" class="d-none" readonly><?= $this->val('description') ?></textarea>
    <?= $this->render_help($help) ?>
</div>
<script>quillEditor('<?= e($module) ?>');</script>
<?php elseif ($module === 'event-public'): ?>
<div class="data-module col-sm-<?= $width ?>" data-module="event-public">
    <input type="hidden" name="values[public]" value="false">
    <div class="custom-checkbox">
        <input type="checkbox" id="event-public" name="values[public]" value="true" <?= filter_var($this->form['public'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' ?>>
        <label for="event-public"><?= e($label) ?></label>
    </div>
    <?= $this->render_help($help) ?>
</div>
<?php else: ?>
<div class="data-module floating-form col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
    <input type="<?= $module === 'url' ? 'url' : ($module === 'deadline-date' ? 'date' : 'text') ?>" class="form-control" id="event-<?= e($field) ?>" name="values[<?= e($field) ?>]" value="<?= $this->val($field) ?>" <?= $labelClass ?> placeholder=" ">
    <label for="event-<?= e($field) ?>" class="<?= $labelClass ?>"><?= e($label) ?></label>
    <?= $this->render_help($help) ?>
</div>
<?php endif; ?>
