<?php
/** Shared input for persons and persons-role; included in Modules::print_module(). */
require_once BASEPATH . '/php/PersonField.php';
$withRoles = $module === 'persons-role';
$persons = array_values(DB::doc2Arr($this->form['persons'] ?? []));
$personRoles = $withRoles ? $Vocabulary->getValues('persons-role', true) : [];
$widgetId = 'module-' . $module;
$renderPersonRow = function ($index, $person = []) use ($withRoles, $personRoles, $widgetId) {
    $selectedRole = $person['role'] ?? PersonField::defaultRole($personRoles);
?>
<tr>
    <td><input class="form-control person-user" list="<?= e($widgetId) ?>-users" name="values[persons][<?= e($index) ?>][user]" value="<?= e($person['user'] ?? '') ?>">
        <?php if (!$withRoles && isset($person['role'])): ?><input type="hidden" name="values[persons][<?= e($index) ?>][role]" value="<?= e($person['role']) ?>"><?php endif; ?>
    </td>
    <td><input class="form-control person-last" name="values[persons][<?= e($index) ?>][last]" value="<?= e($person['last'] ?? '') ?>" required></td>
    <td><input class="form-control person-first" name="values[persons][<?= e($index) ?>][first]" value="<?= e($person['first'] ?? '') ?>"></td>
    <?php if ($withRoles): ?>
        <td><select class="form-control" name="values[persons][<?= e($index) ?>][role]">
            <?php foreach ($personRoles as $option):
                if (!empty($option['inactive']) && $option['id'] !== $selectedRole) continue;
            ?>
                <option value="<?= e($option['id']) ?>" <?= $option['id'] === $selectedRole ? 'selected' : '' ?>><?= e(lang($option['en'], $option['de'] ?? null)) ?></option>
            <?php endforeach; ?>
            <?php if (!in_array($selectedRole, array_column($personRoles, 'id'), true)): ?>
                <option value="<?= e($selectedRole) ?>" selected><?= e($selectedRole) ?></option>
            <?php endif; ?>
        </select></td>
    <?php endif; ?>
    <td>
        <input type="hidden" name="values[persons][<?= e($index) ?>][aoi]" value="0">
        <input type="checkbox" class="person-aoi" name="values[persons][<?= e($index) ?>][aoi]" value="1" <?= !empty($person['aoi']) ? 'checked' : '' ?> aria-label="<?= e(lang('common.affiliated')) ?>">
    </td>
    <td><button type="button" class="btn text-danger remove-person" aria-label="<?= e(lang('action.delete')) ?>"><i class="ph ph-trash"></i></button></td>
</tr>
<?php }; ?>
<div class="data-module col-sm-<?= $width ?>" data-module="<?= e($module) ?>" id="<?= e($widgetId) ?>">
    <label class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <input type="hidden" name="values[persons]" value="">
    <table class="table simple small">
        <thead><tr>
            <th>Username</th><th><?= lang('common.name_last') ?></th><th><?= lang('common.name_first') ?></th>
            <?php if ($withRoles): ?><th><?= lang('common.role') ?></th><?php endif; ?>
            <th><?= lang('common.affiliated') ?></th><th></th>
        </tr></thead>
        <tbody><?php foreach ($persons as $index => $person) $renderPersonRow($index, $person); ?></tbody>
    </table>
    <button class="btn add-person" type="button"><i class="ph ph-plus"></i> <?= lang('activities.add_person') ?></button>
    <?= $this->render_help($help) ?>
    <datalist id="<?= e($widgetId) ?>-users">
        <?php foreach ($this->userlist as $person): ?>
            <option value="<?= e($person['username']) ?>" data-first="<?= e($person['first'] ?? '') ?>" data-last="<?= e($person['last'] ?? '') ?>"><?= e(($person['last'] ?? '') . ', ' . ($person['first'] ?? '')) ?></option>
        <?php endforeach; ?>
    </datalist>
    <template><?php $renderPersonRow('__INDEX__'); ?></template>
</div>
<script>
(() => {
    const widget = $('#<?= $widgetId ?>');
    const required = <?= json_encode($req || !empty($props['required'])) ?>;
    let counter = <?= count($persons) ?>;
    function addPerson() {
        const row = $(widget.find('template').html().replaceAll('__INDEX__', counter++));
        widget.find('tbody').append(row);
    }
    widget.on('click', '.add-person', addPerson);
    widget.on('click', '.remove-person', function () {
        if (required && widget.find('tbody tr').length === 1) return;
        $(this).closest('tr').remove();
    });
    widget.on('change', '.person-user', function () {
        const username = this.value;
        const option = widget.find('datalist option').filter(function () { return this.value === username; });
        if (!option.length) return;
        const row = $(this).closest('tr');
        row.find('.person-first').val(option.attr('data-first'));
        row.find('.person-last').val(option.attr('data-last'));
        row.find('.person-aoi').prop('checked', true);
    });
    if (required && !widget.find('tbody tr').length) addPerson();
})();
</script>
