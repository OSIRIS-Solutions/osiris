<?php
$resources = $this->form['ressources'] ?? [];
$resourceLabels = ['material' => 'projects.additional_material_resources', 'personnel' => 'projects.additional_personnel_resources', 'room' => 'projects.additional_room_capacities', 'other' => 'projects.other_resources'];
?>
<div class="data-module col-sm-<?= $width ?>" data-module="ressources" id="module-project-ressources">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <?php foreach ($resourceLabels as $key => $text): $enabled = ($resources[$key] ?? 'no') === 'yes'; ?>
            <div class="resource-group form-group">
                <label for="project-resource-<?= $key ?>"><?= lang($text) ?></label>
                <select class="form-control" name="values[ressources][<?= $key ?>]" id="project-resource-<?= $key ?>" <?= $required ? 'required' : '' ?>>
                    <option value="no" <?= !$enabled ? 'selected' : '' ?>><?= lang('common.no') ?></option>
                    <option value="yes" <?= $enabled ? 'selected' : '' ?>><?= lang('common.yes') ?></option>
                </select>
                <textarea class="form-control resource-details" name="values[ressources][<?= $key ?>_details]" aria-label="<?= e(lang('common.details')) ?>" <?= $enabled ? '' : 'hidden' ?>><?= e($resources[$key . '_details'] ?? '') ?></textarea>
            </div>
        <?php endforeach; ?>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<script>
(() => {
    const widget = document.getElementById('module-project-ressources');
    const update = () => widget.querySelectorAll('.resource-group').forEach(group => { group.querySelector('textarea').hidden = group.querySelector('select').value !== 'yes'; });
    widget.addEventListener('change', update);
    widget.closest('form')?.addEventListener('reset', () => setTimeout(update, 0));
    update();
})();
</script>
