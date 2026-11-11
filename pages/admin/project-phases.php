<?php
/** Phase cards on the second project configuration page. */
include_once BASEPATH . '/php/ProjectForms.php';
foreach (ProjectForms::phases($project) as $phase):
    $phaseId = $phase['id'];
    $config = ProjectForms::builder($project, $phaseId);
    $fields = ProjectForms::fields($project, $phaseId, $Project->FIELDS);
    $systemFields = FormBuilder::systemFieldsFor($fields, $config->systemFields);
    $builderUrl = ROOTPATH . '/admin/projects/' . rawurlencode($project['id']) . '/fields/' . $phaseId;
?>
<div class="box phase" id="phase-<?= e($phaseId) ?>" data-id="<?= e($phaseId) ?>">
    <div class="content">
        <code class="float-right text-muted"><?= e($phaseId) ?></code>
        <h2 class="title">
            <span class="badge <?= e($phase['color']) ?>"><?= e(lang($phase['name'], $phase['name_de'])) ?></span>
            <?php if (!empty($phase['planned'])): ?><span class="badge muted"><?= lang('Planned', 'Geplant') ?></span><?php endif; ?>
        </h2>
        <p><?= e(lang($phase['description'], $phase['description_de'])) ?></p>
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <b><?= lang('common.data_fields') ?>:</b>
            <a href="<?= e($builderUrl) ?>?overview=1" target="_blank" rel="noopener noreferrer"><?= lang('admin.field_overview') ?> <i class="ph ph-arrow-square-out"></i></a>
        </div>
        <div class="project-phase-fields my-10">
            <?php foreach ($fields as $field):
                $props = $field['props'] ?? [];
                $fieldType = $field['type'] ?? 'field';
                $id = $field['id'] ?? '';
                $module = $config->modules[$id] ?? [];
                $name = '';
                $title = '';
                $icon = match ($fieldType) { 'custom' => 'ph-textbox', 'heading' => 'ph-text-h', 'paragraph' => 'ph-paragraph', 'hr' => 'ph-minus', default => 'ph-database' };
                if (in_array($fieldType, ['field', 'custom'], true)) {
                    $name = lang($props['label'] ?? $module['name'] ?? $id, $props['label_de'] ?? $module['name_de'] ?? null);
                    $title = $id;
                } else {
                    $title = strip_tags(lang($props['text'] ?? 'Divider', $props['text_de'] ?? 'Trennlinie'));
                }
            ?>
                <span class="badge" title="<?= e($title) ?>"><i class="ph <?= $icon ?>"></i> <?= e($name) ?>
                    <?php if (!empty($props['required'])): ?><i class="ph ph-asterisk text-danger" aria-label="<?= e(lang('admin.required_fields')) ?>"></i><?php endif; ?>
                    <?php if (isset($systemFields[$id])): ?><i class="ph ph-lock-simple" aria-label="<?= e(lang('System field', 'Systemfeld')) ?>"></i><?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
        <a class="btn secondary" href="<?= e($builderUrl) ?>"><i class="ph ph-edit"></i> <?= lang('Edit in Form Builder', 'Im Form-Builder bearbeiten') ?></a>
    </div>
</div>
<?php endforeach; ?>
<a class="btn" href="<?= ROOTPATH ?>/admin/projects/1/<?= e(rawurlencode($project['id'])) ?>"><i class="ph ph-arrow-left"></i> <?= lang('common.general_settings') ?></a>
<a class="btn success" href="<?= ROOTPATH ?>/admin/projects"><?= lang('Done', 'Fertig') ?></a>
