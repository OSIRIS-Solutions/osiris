<?php
/** Event inputs retain their established storage keys and presentation. */
$field = $module === 'event-title' ? 'title' : $module;
?>
<?php if ($module === 'event-type'): ?>
<div class="data-module floating-form col-sm-<?= $width ?>" data-module="event-type">
    <select class="form-control" name="values[type]" id="event-type" <?= $labelClass ?> placeholder=" ">
        <?php if ($this->val('type') !== '' && !in_array($this->val('type'), array_column($Vocabulary->getValues('event-type', true), 'id'), true)): ?>
            <option value="<?= $this->val('type') ?>" selected><?= $this->val('type') ?></option>
        <?php endif; ?>
        <?php foreach ($Vocabulary->getValues('event-type', true) as $option):
            if (!empty($option['inactive']) && $option['id'] !== $this->val('type')) continue;
        ?>
            <option value="<?= e($option['id']) ?>" <?= $this->val('type') === $option['id'] ? 'selected' : '' ?>><?= e(lang($option['en'], $option['de'] ?? null)) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="event-type" class="<?= $labelClass ?>"><?= e($label) ?></label>
    <?= $this->render_help($help) ?>
</div>
<?php elseif ($module === 'event-description'): ?>
<div class="data-module col-sm-<?= $width ?>" data-module="event-description">
    <label for="event-description" class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <div class="form-group title-editor" id="event-description-quill"><?= $this->form['description'] ?? '' ?></div>
    <textarea name="values[description]" id="event-description" class="d-none" readonly><?= $this->val('description') ?></textarea>
    <?= $this->render_help($help) ?>
</div>
<script>quillEditor('event-description');</script>
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
    <input type="<?= $module === 'url' ? 'url' : 'text' ?>" class="form-control" id="event-<?= e($field) ?>" name="values[<?= e($field) ?>]" value="<?= $this->val($field) ?>" <?= $labelClass ?> placeholder=" ">
    <label for="event-<?= e($field) ?>" class="<?= $labelClass ?>"><?= e($label) ?></label>
    <?= $this->render_help($help) ?>
</div>
<?php endif; ?>
