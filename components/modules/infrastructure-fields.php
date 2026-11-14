<?php
if ($module === 'infrastructure-collaborative') {
    include BASEPATH . '/components/modules/infrastructure-collaborative.php';
    return;
}
$required = $req || !empty($props['required']);
$vocabularies = ['infrastructure-category' => ['infrastructure-category', 'type'], 'infrastructure-type' => ['infrastructure-type', 'infrastructure_type'], 'infrastructure-access' => ['infrastructure-access', 'access']];
?>
<div class="data-module col-sm-<?= $width ?> <?= ($module === 'infrastructure-date') ? 'row' : '' ?>" data-module="<?= e($module) ?>">
<?php if ($module === 'infrastructure-date'): ?>
        <?php foreach (['start_date' => translate('common.start_date'), 'end_date' => translate('common.end_date')] as $key => $dateLabel): ?>
            <div class="col-sm-6 floating-form">
                <input type="date" class="form-control" id="<?= $key ?>" name="values[<?= $key ?>]" value="<?= $this->val($key) ?>" <?= $key === 'start_date' && $required ? 'required' : '' ?> placeholder=" ">
                <label for="<?= $key ?>"><?= e($dateLabel) ?></label>
            </div>
        <?php endforeach; ?>
<?php elseif (isset($vocabularies[$module])):
    [$vocabulary, $key] = $vocabularies[$module];
    $selected = $this->form[$key] ?? '';
    $options = $Vocabulary->getValues($vocabulary, true);
?>
    <div class="floating-form">
        <select name="values[<?= e($key) ?>]" id="<?= e($module) ?>" class="form-control" <?= $required ? 'required' : '' ?> placeholder=" ">
            <?php if ($selected !== '' && !in_array($selected, array_column($options, 'id'), true)): ?><option value="<?= e($selected) ?>" selected><?= e($selected) ?></option><?php endif; ?>
            <?php foreach ($options as $option): if (!empty($option['inactive']) && $selected !== $option['id']) continue; ?>
                <option value="<?= e($option['id']) ?>" <?= $selected === $option['id'] ? 'selected' : '' ?>><?= e(lang($option['en'], $option['de'] ?? null)) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="<?= e($module) ?>" class="<?= $required ? 'required' : '' ?>"><?= e($label) ?></label>
    </div>
<?php else:
    $key = match ($module) { 'infrastructure-id' => 'id', 'infrastructure-link' => 'link', default => 'contact_email' };
    $type = match ($module) { 'infrastructure-link' => 'url', 'contact-email' => 'email', default => 'text' };
?>
    <div class="floating-form">
        <input type="<?= $type ?>" class="form-control" id="<?= e($module) ?>" name="values[<?= $key ?>]" value="<?= $this->val($key, $key === 'id' ? uniqid() : '') ?>" <?= $required ? 'required' : '' ?> <?= $key === 'id' && isset($this->form['_id']) ? 'readonly' : '' ?> placeholder=" ">
        <label for="<?= e($module) ?>" class="<?= $required ? 'required' : '' ?>"><?= e($label) ?></label>
    </div>
<?php endif; ?>
<?= $this->render_help($help) ?>
</div>
