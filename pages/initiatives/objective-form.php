<?php
/** Fixed objective inputs, shared by the create modal and the edit form. */
$objectiveFormId = $objectiveFormId ?? 'objective-form';
$objectiveTypes = $objectiveTypes ?? [];
$objectiveDrafts = $objectiveDrafts ?? [];
?>
<div class="row row-eq-spacing">
    <div class="col-sm-6">
        <label for="<?= e($objectiveFormId) ?>-code"><?= translate('initiatives.identifier') ?></label>
        <input id="<?= e($objectiveFormId) ?>-code" class="form-control" name="values[code]" value="<?= e($objectiveForm['code'] ?? '') ?>" placeholder="26-01">
    </div>
    <div class="col-sm-6">
        <label for="<?= e($objectiveFormId) ?>-type"><?= translate('initiatives.type') ?></label>
        <select id="<?= e($objectiveFormId) ?>-type" class="form-control" name="values[type]" required>
            <?php foreach ($objectiveTypes as $type): ?>
                <option value="<?= e($type['id']) ?>" <?= $type['id'] === ($objectiveForm['type'] ?? 'objective') ? 'selected' : '' ?>><?= e(lang($type['en'], $type['de'] ?? null)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<div class="form-group">
    <label class="required" for="<?= e($objectiveFormId) ?>-name"><?= translate('initiatives.title') ?></label>
    <input id="<?= e($objectiveFormId) ?>-name" class="form-control" name="values[name]" value="<?= e($objectiveForm['name'] ?? '') ?>" required>
</div>
<div class="form-group">
    <label for="<?= e($objectiveFormId) ?>-description"><?= translate('initiatives.description') ?></label>
    <textarea id="<?= e($objectiveFormId) ?>-description" class="form-control" name="values[description]" rows="4"><?= e($objectiveForm['description'] ?? '') ?></textarea>
</div>
<div class="row row-eq-spacing">
    <div class="col-sm-6">
        <label for="<?= e($objectiveFormId) ?>-start"><?= translate('initiatives.start_date') ?></label>
        <input id="<?= e($objectiveFormId) ?>-start" class="form-control" type="date" name="values[start_date]" value="<?= e($objectiveForm['start_date'] ?? '') ?>" onchange="document.getElementById('<?= e($objectiveFormId) ?>-end').min = this.value">
    </div>
    <div class="col-sm-6">
        <label for="<?= e($objectiveFormId) ?>-end"><?= translate('initiatives.deadline') ?></label>
        <input id="<?= e($objectiveFormId) ?>-end" class="form-control" type="date" name="values[end_date]" value="<?= e($objectiveForm['end_date'] ?? '') ?>" min="<?= e($objectiveForm['start_date'] ?? '') ?>">
    </div>
</div>
