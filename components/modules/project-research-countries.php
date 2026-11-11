<?php
$countries = $this->DB->getCountries(lang('common.field_name_language'));
$selected = DB::doc2Arr($this->form['research-countries'] ?? []);
$roles = ['in' => 'projects.research_in_this_country', 'about' => 'projects.research_about_this_country', 'both' => 'projects.both'];
$renderRow = function ($entry = []) use ($countries, $roles) {
    if (is_string($entry)) { [$iso, $role] = array_pad(explode(';', $entry, 2), 2, 'both'); }
    else { $iso = $entry['iso'] ?? ''; $role = $entry['role'] ?? 'both'; }
?>
<tr>
    <td><select class="form-control research-country" required>
        <option value=""></option>
        <?php foreach ($countries as $code => $name): ?><option value="<?= e($code) ?>" <?= $code === $iso ? 'selected' : '' ?>><?= e($name) ?></option><?php endforeach; ?>
        <?php if ($iso !== '' && !isset($countries[$iso])): ?><option selected value="<?= e($iso) ?>"><?= e($iso) ?></option><?php endif; ?>
    </select></td>
    <td><select class="form-control research-role" required>
        <?php foreach ($roles as $key => $text): ?><option value="<?= $key ?>" <?= $key === $role ? 'selected' : '' ?>><?= lang($text) ?></option><?php endforeach; ?>
        <?php if (!isset($roles[$role])): ?><option selected value="<?= e($role) ?>"><?= e($role) ?></option><?php endif; ?>
    </select></td>
    <td><input type="hidden" name="values[research-countries][]" value="<?= e($iso . ';' . $role) ?>"><button type="button" class="btn remove-country" aria-label="<?= e(lang('action.remove')) ?>"><i class="ph ph-trash"></i></button></td>
</tr>
<?php }; ?>
<div class="data-module col-sm-<?= $width ?>" data-module="research-countries" id="module-research-countries">
    <label class="floating-title <?= $labelClass ?>"><?= e($label) ?></label>
    <input type="hidden" name="values[research-countries][]" value="">
    <table class="table simple"><thead><tr><th><?= lang('common.country') ?></th><th><?= lang('common.role') ?></th><th></th></tr></thead>
        <tbody><?php foreach ($selected as $entry) $renderRow($entry); ?></tbody>
    </table>
    <button type="button" class="btn add-country"><i class="ph ph-plus"></i> <?= lang('activities.add_country') ?></button>
    <?= $this->render_help($help) ?>
    <template><?php $renderRow(); ?></template>
</div>
<script>
(() => {
    const widget = document.getElementById('module-research-countries');
    const body = widget.querySelector('tbody');
    const required = <?= json_encode($required) ?>;
    const add = () => body.append(widget.querySelector('template').content.cloneNode(true));
    widget.querySelector('.add-country').addEventListener('click', add);
    widget.addEventListener('click', event => {
        const button = event.target.closest('.remove-country');
        if (button && (!required || body.rows.length > 1)) button.closest('tr').remove();
    });
    widget.addEventListener('change', event => {
        const row = event.target.closest('tbody tr');
        if (!row) return;
        row.querySelector('input[type="hidden"]').value = row.querySelector('.research-country').value + ';' + row.querySelector('.research-role').value;
    });
    if (required && !body.rows.length) add();
})();
</script>
