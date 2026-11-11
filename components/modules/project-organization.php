<?php
$field = $module === 'scholarship' ? 'funding_organization' : $module;
$value = $this->form[$field] ?? '';
if (is_array($value) || $value instanceof Traversable) $value = $value['$oid'] ?? '';
$value = (string) $value;
$organizations = $this->DB->db->organizations->find([], ['sort' => ['name' => 1]])->toArray();
$found = false;
?>
<div class="data-module col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
    <label for="project-<?= e($module) ?>" class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <select class="form-control" id="project-<?= e($module) ?>" name="values[<?= e($field) ?>]" <?= $required ? 'required' : '' ?>>
        <option value=""></option>
        <?php foreach ($organizations as $organization):
            $selected = (string) $organization['_id'] === $value;
            $found = $found || $selected;
        ?>
            <option value="<?= e($organization['_id']) ?>" <?= $selected ? 'selected' : '' ?>><?= e(trim(strip_tags(localized($organization['name'] ?? '')))) ?><?= empty($organization['location']) ? '' : ' (' . e($organization['location']) . ')' ?></option>
        <?php endforeach; ?>
        <?php if (!$found && $value !== ''): ?><option selected value="<?= e($value) ?>"><?= e($value) ?></option><?php endif; ?>
    </select>
    <?php if ($Settings->hasPermission('organizations.edit')): ?>
        <a href="<?= ROOTPATH ?>/organizations/new" target="_blank" rel="noopener noreferrer" class="font-size-12"><?= lang('Add an organization', 'Organisation hinzufügen') ?></a>
    <?php endif; ?>
    <?= $this->render_help($help) ?>
</div>
