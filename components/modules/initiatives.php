<?php
/** Initiative association field; included in Modules::print_module(). */
if (!class_exists('Initiatives')) require_once BASEPATH . '/php/Initiatives.php';
if (!$Settings->featureEnabled('initiatives')) return;
$selected = array_map('strval', Initiatives::normalizeLinks($this->val('initiatives', [])));
$initiatives = $this->DB->db->initiatives->find()->toArray();
usort($initiatives, function ($a, $b) {
    $pastA = !empty($a['inactive']) || Initiatives::isExpired($a);
    $pastB = !empty($b['inactive']) || Initiatives::isExpired($b);
    return ($pastA <=> $pastB) ?: strnatcasecmp(Initiatives::text($a['name'] ?? ''), Initiatives::text($b['name'] ?? ''));
});
$required = $req || !empty($props['required']);
?>
<div class="data-module col-sm-<?= $width ?>" data-module="initiatives" id="module-initiatives">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <input type="hidden" name="values[initiatives]" value="">
        <div class="initiative-options">
            <?php foreach ($initiatives as $initiative):
                $id = (string) $initiative['_id'];
                $title = trim(strip_tags(localized($initiative['title'] ?? '')));
                $archived = !empty($initiative['inactive']);
                $expired = Initiatives::isExpired($initiative);
                $class = '';
                if ($expired || $archived) $class .= ' inactive';
            ?>
                <label class="initiative-option<?= $class ?>" <?= $title !== '' ? 'title="' . e($title) . '"' : '' ?>>
                    <input type="checkbox" name="values[initiatives][]" value="<?= e($id) ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                    <span class="initiative-option-name"><i class="ph ph-compass" aria-hidden="true"></i> <?= e(trim(strip_tags(localized($initiative['name'] ?? '')))) ?>
                        <?php if ($archived || $expired): ?><small class="text-muted">(<?= $archived ? lang('Archived', 'Archiviert') : lang('Expired', 'Abgelaufen') ?>)</small><?php endif; ?>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?php if (empty($initiatives)): ?>
            <p class="text-muted"><?= lang('No initiatives available.', 'Keine Initiativen verfügbar.') ?></p>
            <?php if ($required): ?><select class="form-control" required aria-label="<?= e($label) ?>"><option value=""><?= lang('Please create an initiative first.', 'Bitte zuerst eine Initiative anlegen.') ?></option></select><?php endif; ?>
        <?php endif; ?>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<style>
    .initiative-options { display: flex; flex-wrap: wrap; gap: .75rem; }
    .initiative-option { display: flex; align-items: center; gap: .6rem; border: 1px solid var(--border-color); border-radius: .4rem; padding: .7rem 1rem; cursor: pointer; margin: 0; }
    .initiative-option:has(input:checked) { border-color: var(--primary-color); background: var(--primary-color-20); color: var(--primary-color); }
    .initiative-option:focus-within { outline: 2px solid var(--primary-color); outline-offset: 2px; }
    .initiative-option input { display: none; }
    .initiative-option.inactive { opacity: 0.6; }
</style>
<script>
(() => {
    const widget = document.getElementById('module-initiatives');
    const checkboxes = [...widget.querySelectorAll('input[type="checkbox"]')];
    const required = <?= json_encode($required) ?>;
    const updateRequired = () => {
        if (checkboxes.length) checkboxes[0].required = required && !checkboxes.some(input => input.checked);
    };
    widget.addEventListener('change', updateRequired);
    widget.closest('form')?.addEventListener('reset', () => setTimeout(updateRequired, 0));
    updateRequired();
})();
</script>
