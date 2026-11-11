<?php
if ($module === 'topics' && !$Settings->featureEnabled('topics')) return;
$selected = array_map('strval', DB::doc2Arr($this->form[$module] ?? []));
$options = [];
if ($module === 'topics') {
    foreach ($this->DB->db->topics->find() as $topic) {
        $name = is_string($topic['name'] ?? '') ? lang($topic['name'], $topic['name_de'] ?? null) : localized($topic['name']);
        $options[(string) $topic['id']] = strip_tags($name) . (!empty($topic['inactive']) ? ' (' . lang('Archived', 'Archiviert') . ')' : '');
    }
} else {
    $flatten = function ($items, $prefix = '') use (&$flatten, &$options) {
        foreach ($items as $item) {
            $name = lang($item['labels']['en'], $item['labels']['de'] ?? null);
            if (!empty($item['children'])) $flatten($item['children'], $prefix . $name . ' / ');
            else $options[(string) $item['id']] = $prefix . $name;
        }
    };
    $flatten(json_decode(file_get_contents(BASEPATH . '/data/kdsf-ffk.json'), true));
}
foreach ($selected as $id) if ($id !== '' && !isset($options[$id])) $options[$id] = $id;
?>
<div class="data-module col-sm-<?= $width ?>" data-module="<?= e($module) ?>">
    <label for="project-<?= e($module) ?>" class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <input type="hidden" name="values[<?= e($module) ?>][]" value="">
    <select class="form-control" multiple size="6" id="project-<?= e($module) ?>" name="values[<?= e($module) ?>][]" <?= $required ? 'required' : '' ?>>
        <?php foreach ($options as $id => $name): ?>
            <option value="<?= e($id) ?>" <?= in_array((string) $id, $selected, true) ? 'selected' : '' ?>><?= e($name) ?></option>
        <?php endforeach; ?>
    </select>
    <?= $this->render_help($help) ?>
</div>
