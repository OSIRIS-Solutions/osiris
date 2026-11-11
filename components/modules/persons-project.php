<?php
/** Project membership editor, compatible with pages/projects/persons.php. */
$persons = array_values(DB::doc2Arr($this->form['persons'] ?? []));
$roles = $Vocabulary->getValues('project-person-role', true);
$accounts = [];
foreach ($this->userlist as $account) {
    $units = [];
    foreach ($account['units'] ?? [] as $unit) {
        if (!empty($unit['unit'])) $units[] = $unit['unit'];
    }
    $accounts[$account['username']] = [
        'name' => trim(($account['first'] ?? '') . ' ' . ($account['last'] ?? '')),
        'units' => array_values(array_unique($units)),
    ];
}
$renderRow = function ($index, $person = []) use ($roles, $accounts) {
    $user = $person['user'] ?? '';
    $role = $person['role'] ?? 'applicant';
    $units = array_values(DB::doc2Arr($person['units'] ?? []));
?>
<tr>
    <td><input type="hidden" name="values[persons][<?= e($index) ?>][name]" value="<?= e($person['name'] ?? '') ?>"><select class="form-control project-person-user" name="values[persons][<?= e($index) ?>][user]" required>
        <option value=""><?= lang('Select a person', 'Person auswählen') ?></option>
        <?php foreach ($accounts as $username => $account): ?>
            <option value="<?= e($username) ?>" <?= $username === $user ? 'selected' : '' ?>><?= e($account['name'] . ' (' . $username . ')') ?></option>
        <?php endforeach; ?>
        <?php if ($user !== '' && !isset($accounts[$user])): ?><option value="<?= e($user) ?>" selected><?= e($person['name'] ?? $user) ?></option><?php endif; ?>
    </select></td>
    <td><select class="form-control" name="values[persons][<?= e($index) ?>][role]" required>
        <?php foreach ($roles as $option):
            if (!empty($option['inactive']) && $option['id'] !== $role) continue;
        ?>
            <option value="<?= e($option['id']) ?>" <?= $option['id'] === $role ? 'selected' : '' ?>><?= e(lang($option['en'], $option['de'] ?? null)) ?></option>
        <?php endforeach; ?>
        <?php if (!in_array($role, array_column($roles, 'id'), true)): ?><option value="<?= e($role) ?>" selected><?= e($role) ?></option><?php endif; ?>
    </select></td>
    <td><input type="date" class="form-control" name="values[persons][<?= e($index) ?>][start]" value="<?= e($person['start'] ?? '') ?>"></td>
    <td><input type="date" class="form-control" name="values[persons][<?= e($index) ?>][end]" value="<?= e($person['end'] ?? '') ?>"></td>
    <td class="project-person-units">
        <input type="hidden" name="values[persons][<?= e($index) ?>][units][]" value="">
        <?php foreach (array_unique(array_merge($accounts[$user]['units'] ?? [], $units)) as $j => $unit): ?>
            <label class="d-block"><input type="checkbox" name="values[persons][<?= e($index) ?>][units][]" value="<?= e($unit) ?>" <?= in_array($unit, $units, true) ? 'checked' : '' ?>> <?= e($unit) ?></label>
        <?php endforeach; ?>
    </td>
    <td><button type="button" class="btn text-danger remove-project-person" aria-label="<?= e(lang('action.delete')) ?>"><i class="ph ph-trash"></i></button></td>
</tr>
<?php }; ?>
<div class="data-module col-sm-<?= $width ?>" data-module="persons-project" id="module-persons-project">
    <label class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <input type="hidden" name="values[persons]" value="">
    <div class="table-responsive"><table class="table simple small">
        <thead><tr><th><?= lang('common.persons') ?></th><th><?= lang('common.role') ?></th><th><?= lang('Start', 'Beginn') ?></th><th><?= lang('End', 'Ende') ?></th><th><?= lang('common.units') ?></th><th></th></tr></thead>
        <tbody><?php foreach ($persons as $index => $person) $renderRow($index, $person); ?></tbody>
    </table></div>
    <button type="button" class="btn add-project-person"><i class="ph ph-plus"></i> <?= lang('activities.add_person') ?></button>
    <?= $this->render_help($help) ?>
    <template><?php $renderRow('__INDEX__'); ?></template>
</div>
<script>
(() => {
    const widget = document.getElementById('module-persons-project');
    const accounts = <?= json_encode($accounts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const required = <?= json_encode($req || !empty($props['required'])) ?>;
    const body = widget.querySelector('tbody');
    let counter = <?= count($persons) ?>;
    const add = () => {
        body.insertAdjacentHTML('beforeend', widget.querySelector('template').innerHTML.replaceAll('__INDEX__', counter++));
    };
    widget.querySelector('.add-project-person').addEventListener('click', add);
    widget.addEventListener('click', event => {
        const button = event.target.closest('.remove-project-person');
        if (button && (!required || body.rows.length > 1)) button.closest('tr').remove();
    });
    widget.addEventListener('change', event => {
        if (!event.target.matches('.project-person-user')) return;
        const cell = event.target.closest('tr').querySelector('.project-person-units');
        const name = cell.querySelector('input[type="hidden"]').name;
        cell.querySelectorAll('label').forEach(label => label.remove());
        (accounts[event.target.value]?.units || []).forEach(unit => {
            const label = document.createElement('label');
            label.className = 'd-block';
            const input = document.createElement('input');
            input.type = 'checkbox'; input.name = name; input.value = unit; input.checked = true;
            label.append(input, document.createTextNode(' ' + unit));
            cell.append(label);
        });
    });
    if (required && !body.rows.length) add();
})();
</script>
