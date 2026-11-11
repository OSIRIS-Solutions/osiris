<?php
/** Remaining project fields, included from Modules::print_module(). */
$required = $req || !empty($props['required']);
$vocabularies = ['funder' => 'funder', 'project_type' => 'project-type', 'purpose' => 'project-purpose', 'funding_program_select' => 'funding-program'];
$dates = ['submission_date', 'approval_date', 'rejection_date', 'start_proposed', 'end_proposed'];
$textareas = ['comment', 'personnel', 'in-kind'];
$complex = ['joint_project', 'research-countries', 'nagoya', 'ressources', 'kdsf-ffk', 'topics', 'image'];
if (in_array($module, ['funding_organization', 'scholarship', 'university'], true)) {
    include BASEPATH . '/components/modules/project-organization.php';
    return;
}
if (in_array($module, ['kdsf-ffk', 'topics'], true)) {
    include BASEPATH . '/components/modules/project-selection.php';
    return;
}
if (in_array($module, $complex, true)) {
    include BASEPATH . '/components/modules/project-' . $module . '.php';
    return;
}
$value = $this->form[$module] ?? '';
if ($module === 'funding_number' && (is_array($value) || $value instanceof Traversable)) $value = implode(', ', DB::doc2Arr($value));
if (in_array($module, $dates, true)) $value = valueFromDateArray($value);
if ($module === 'coordinator' && $value === '') $value = $Settings->get('affiliation', '');
?>
<div class="data-module col-sm-<?= $width ?> <?= isset($vocabularies[$module]) || $module !== 'public' ? 'floating-form' : '' ?>" data-module="<?= e($module) ?>">
    <?php if (isset($vocabularies[$module])):
        $options = $Vocabulary->getValues($vocabularies[$module], true);
    ?>
        <select class="form-control" id="project-<?= e($module) ?>" name="values[<?= e($module) ?>]" <?= $required ? 'required' : '' ?>>
            <option value=""></option>
            <?php foreach ($options as $option):
                if (!empty($option['inactive']) && $option['id'] !== $value) continue;
            ?>
                <option value="<?= e($option['id']) ?>" <?= $option['id'] === $value ? 'selected' : '' ?>><?= e(lang($option['en'], $option['de'] ?? null)) ?></option>
            <?php endforeach; ?>
            <?php if ($value !== '' && !in_array($value, array_column($options, 'id'), true)): ?><option selected value="<?= e($value) ?>"><?= e($value) ?></option><?php endif; ?>
        </select>
    <?php elseif (in_array($module, $textareas, true)): ?>
        <textarea class="form-control" id="project-<?= e($module) ?>" name="values[<?= e($module) ?>]" rows="3" <?= $required ? 'required' : '' ?>><?= e($value) ?></textarea>
    <?php elseif ($module === 'public'): ?>
        <input type="hidden" name="values[public]" value="false">
        <input type="checkbox" id="project-public" name="values[public]" value="true" <?= !empty($value) ? 'checked' : '' ?> <?= $required ? 'required' : '' ?>>
    <?php else:
        $type = in_array($module, $dates, true) ? 'date' : (str_starts_with($module, 'grant_') ? 'number' : 'text');
    ?>
        <input type="<?= $type ?>" class="form-control" id="project-<?= e($module) ?>" name="values[<?= e($module) ?>]" value="<?= e($value) ?>" <?= $type === 'number' ? 'step="0.01" min="0"' : '' ?> <?= $required ? 'required' : '' ?>>
    <?php endif; ?>
    <label for="project-<?= e($module) ?>" class="<?= $labelClass ?>"><?= e($label) ?><?= str_starts_with($module, 'grant_') ? ' (€)' : '' ?></label>
    <?php if ($module === 'funding_number'): ?><small class="text-muted"><?= lang('projects.multiple_seperated_by_comma') ?></small><?php endif; ?>
    <?= $this->render_help($help) ?>
</div>
