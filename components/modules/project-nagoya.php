<?php
if (!$Settings->featureEnabled('nagoya')) return;
$nagoya = $this->form['nagoya'] ?? [];
$enabled = is_string($nagoya) ? $nagoya === 'yes' : !empty($nagoya['enabled']);
$countries = is_string($nagoya) ? [] : DB::doc2Arr($nagoya['countries'] ?? []);
?>
<div class="data-module col-sm-<?= $width ?>" data-module="nagoya" id="module-project-nagoya">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <?php if ($enabled && !empty($countries)): ?>
            <p class="text-muted"><?= lang('projects.nagoya_protocol_settings_can_be_found_in_the_nagoya_protocol_tab_after_savi') ?></p>
        <?php else: ?>
            <p><?= lang('projects.do_you_plan_to_collect_obtain_or_utilise_genetic_resources_biological_sampl') ?></p>
            <?php foreach (['yes' => 'common.yes', 'no' => 'common.no'] as $value => $text): ?>
                <label><input type="radio" name="values[nagoya]" value="<?= $value ?>" <?= $enabled === ($value === 'yes') ? 'checked' : '' ?> <?= $required ? 'required' : '' ?>> <?= lang($text) ?></label>
            <?php endforeach; ?>
            <div class="nagoya-countries" <?= $enabled ? '' : 'hidden' ?>>
                <label for="project-nagoya-countries"><?= lang('projects.countries_of_research') ?></label>
                <input type="hidden" name="values[nagoya_countries][]" value="">
                <select multiple class="form-control" id="project-nagoya-countries" name="values[nagoya_countries][]" size="5">
                    <?php foreach ($this->DB->getCountries(lang('common.field_name_language')) as $code => $name): ?>
                        <option value="<?= e($code) ?>" <?= in_array($code, array_column($countries, 'code'), true) ? 'selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<script>
(() => {
    const widget = document.getElementById('module-project-nagoya');
    const countries = widget.querySelector('.nagoya-countries');
    if (!countries) return;
    const update = () => { countries.hidden = widget.querySelector('input[type="radio"]:checked')?.value !== 'yes'; countries.querySelector('select').disabled = countries.hidden; };
    widget.addEventListener('change', update);
    widget.closest('form')?.addEventListener('reset', () => setTimeout(update, 0));
    update();
})();
</script>
