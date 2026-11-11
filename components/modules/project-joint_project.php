<?php $enabled = !empty($this->form['joint_project']); ?>
<div class="data-module col-sm-<?= $width ?>" data-module="joint_project" id="module-joint-project">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <?php foreach (['true' => 'common.yes', 'false' => 'common.no'] as $value => $text): ?>
            <label><input type="radio" name="values[joint_project]" value="<?= $value ?>" <?= $enabled === ($value === 'true') ? 'checked' : '' ?> <?= $required ? 'required' : '' ?>> <?= lang($text) ?></label>
        <?php endforeach; ?>
        <fieldset class="joint-project-details" <?= $enabled ? '' : 'hidden disabled' ?>>
            <?php foreach (['identifier' => 'projects.identifier_of_the_joint_project', 'title' => 'projects.title_of_the_joint_project'] as $key => $text): ?>
                <label for="joint-project-<?= $key ?>"><?= lang($text) ?></label>
                <input class="form-control" id="joint-project-<?= $key ?>" name="values[joint_project_<?= $key ?>]" value="<?= e($this->form['joint_project_' . $key] ?? '') ?>">
            <?php endforeach; ?>
            <input type="hidden" name="values[joint_project_speaker]" value="false">
            <label><input type="checkbox" name="values[joint_project_speaker]" value="true" <?= !empty($this->form['joint_project_speaker']) ? 'checked' : '' ?>> <?= lang('projects.speaker_coordinator_consortium_leader_role') ?></label>
        </fieldset>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<script>
(() => {
    const widget = document.getElementById('module-joint-project');
    const details = widget.querySelector('.joint-project-details');
    const update = () => { const enabled = widget.querySelector('input[type="radio"]:checked')?.value === 'true'; details.hidden = !enabled; details.disabled = !enabled; };
    widget.addEventListener('change', update);
    widget.closest('form')?.addEventListener('reset', () => setTimeout(update, 0));
    update();
})();
</script>
