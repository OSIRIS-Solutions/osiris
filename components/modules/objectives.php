<?php
/** Objective association field; included in Modules::print_module(). */
if (!class_exists('Initiatives')) require_once BASEPATH . '/php/Initiatives.php';
if (!$Settings->featureEnabled('initiatives')) return;
$selected = array_map('strval', Initiatives::normalizeLinks($this->val('objectives', [])));
$groups = [];
foreach ($this->DB->db->initiatives->find() as $initiative) {
    $groups[(string) $initiative['_id']] = ['initiative' => $initiative, 'objectives' => []];
}
foreach ($this->DB->db->objectives->find() as $objective) {
    $parent = (string) ($objective['initiative'] ?? '');
    if (isset($groups[$parent])) $groups[$parent]['objectives'][] = $objective;
}
$groups = array_filter($groups, fn($group) => !empty($group['objectives']));
usort($groups, function ($a, $b) {
    $pastA = !empty($a['initiative']['inactive']) || Initiatives::isExpired($a['initiative']);
    $pastB = !empty($b['initiative']['inactive']) || Initiatives::isExpired($b['initiative']);
    return ($pastA <=> $pastB) ?: strnatcasecmp(Initiatives::text($a['initiative']['name'] ?? ''), Initiatives::text($b['initiative']['name'] ?? ''));
});
$required = $req || !empty($props['required']);
?>
<div class="data-module col-sm-<?= $width ?>" data-module="objectives" id="module-objectives" data-initiative-selector data-required="<?= json_encode($required) ?>">
    <fieldset class="form-group">
        <legend class="font-size-16 <?= $labelClass ?>"><?= e($label) ?></legend>
        <input type="hidden" name="values[objectives]" value="">
        <?php include BASEPATH . '/components/modules/initiative-selection-controls.php'; ?>
        <?php foreach ($groups as $group):
            $initiative = $group['initiative'];
            usort($group['objectives'], function ($a, $b) {
                $pastA = !empty($a['inactive']) || Initiatives::isExpired($a);
                $pastB = !empty($b['inactive']) || Initiatives::isExpired($b);
                return ($pastA <=> $pastB) ?: strnatcasecmp(($a['code'] ?? '') . Initiatives::text($a['name'] ?? ''), ($b['code'] ?? '') . Initiatives::text($b['name'] ?? ''));
            });
        ?>
            <div class="mb-10" data-objective-group>
                <div class="text-muted mb-5"><i class="ph ph-compass" aria-hidden="true"></i> <?= e(Initiatives::text($initiative['name'] ?? '')) ?></div>
                <div class="objective-options">
                    <?php foreach ($group['objectives'] as $objective):
                        $id = (string) $objective['_id'];
                        $description = trim(strip_tags(localized($objective['description'] ?? '')));
                        $archived = !empty($objective['inactive']) || !empty($initiative['inactive']);
                        $expired = Initiatives::isExpired($objective) || Initiatives::isExpired($initiative);
                    ?>
                        <label data-selection-option data-period="<?= e(json_encode(Initiatives::selectionBounds($objective, $initiative))) ?>" data-search-text="<?= e(Initiatives::text($initiative['name'] ?? '') . ' ' . ($objective['code'] ?? '') . ' ' . Initiatives::text($objective['name'] ?? '') . ' ' . $description) ?>" class="objective-option<?= $archived || $expired ? ' inactive' : '' ?>" <?= $description !== '' ? 'title="' . e($description) . '"' : '' ?>>
                            <input type="checkbox" name="values[objectives][]" value="<?= e($id) ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                            <span><i class="ph ph-target" aria-hidden="true"></i>
                                <?php if (!empty($objective['code'])): ?><?= e($objective['code']) ?>: <?php endif; ?><?= e(Initiatives::text($objective['name'] ?? '')) ?>
                                <?php if ($archived || $expired): ?><small class="text-muted">(<?= translate($archived ? 'initiatives.archived' : 'initiatives.expired') ?>)</small><?php endif; ?>
                                <small class="text-danger" data-period-warning hidden><?= translate('initiatives.outside_period') ?></small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?= $this->render_help($help) ?>
    </fieldset>
</div>
<style>
    .objective-options { display: flex; flex-wrap: wrap; gap: .75rem; }
    .objective-option[hidden] { display: none; }
    .objective-option { position: relative; display: flex; align-items: center; border: 1px solid var(--border-color); border-radius: .4rem; padding: .7rem 1rem; cursor: pointer; margin: 0; }
    .objective-option:has(input:checked) { border-color: var(--primary-color); background: var(--primary-color-20); color: var(--primary-color); }
    .objective-option:focus-within { outline: 2px solid var(--primary-color); outline-offset: 2px; }
    .objective-option input { position: absolute; opacity: 0; width: 1px; height: 1px; }
    .objective-option.inactive { opacity: 0.6; }
</style>
