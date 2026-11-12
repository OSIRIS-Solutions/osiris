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
<div class="data-module col-sm-<?= $width ?>" data-module="initiatives" id="module-initiatives" data-initiative-selector data-required="<?= json_encode($required) ?>">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <input type="hidden" name="values[initiatives]" value="">
        <?php include BASEPATH . '/components/modules/initiative-selection-controls.php'; ?>
        <div class="initiative-options">
            <?php foreach ($initiatives as $initiative):
                $id = (string) $initiative['_id'];
                $title = trim(strip_tags(localized($initiative['title'] ?? '')));
                $archived = !empty($initiative['inactive']);
                $expired = Initiatives::isExpired($initiative);
                $class = '';
                if ($expired || $archived) $class .= ' inactive';
            ?>
                <label data-selection-option data-period="<?= e(json_encode(Initiatives::selectionBounds($initiative))) ?>" data-search-text="<?= e(Initiatives::text($initiative['name'] ?? '') . ' ' . $title) ?>" class="initiative-option<?= $class ?>" <?= $title !== '' ? 'title="' . e($title) . '"' : '' ?>>
                    <input type="checkbox" name="values[initiatives][]" value="<?= e($id) ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                    <span class="initiative-option-name"><i class="ph ph-compass" aria-hidden="true"></i> <?= e(trim(strip_tags(localized($initiative['name'] ?? '')))) ?>
                        <?php if ($archived || $expired): ?><small class="text-muted">(<?= $archived ? lang('Archived', 'Archiviert') : lang('Expired', 'Abgelaufen') ?>)</small><?php endif; ?>
                        <small class="text-danger" data-period-warning hidden><?= translate('initiatives.outside_period') ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<style>
    .initiative-options { display: flex; flex-wrap: wrap; gap: .75rem; }
    .initiative-option[hidden] { display: none; }
    .initiative-option { position: relative; display: flex; align-items: center; gap: .6rem; border: 1px solid var(--border-color); border-radius: .4rem; padding: .7rem 1rem; cursor: pointer; margin: 0; }
    .initiative-option:has(input:checked) { border-color: var(--primary-color); background: var(--primary-color-20); color: var(--primary-color); }
    .initiative-option:focus-within { outline: 2px solid var(--primary-color); outline-offset: 2px; }
    .initiative-option input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .initiative-option.inactive { opacity: 0.6; }
</style>
