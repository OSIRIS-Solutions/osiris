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
$pickerId = uniqid('objective-selector-');
?>
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/objective-selector.css?v=<?= OSIRIS_BUILD ?>">
<div class="data-module col-sm-<?= $width ?> objective-selector" data-module="objectives" id="<?= $pickerId ?>" data-initiative-selector data-objective-selector data-required="<?= json_encode($required) ?>"
    data-remove-label="<?= e(translate('initiatives.remove_selection')) ?>"
    data-added-label="<?= e(translate('initiatives.selection_added')) ?>"
    data-removed-label="<?= e(translate('initiatives.selection_removed')) ?>"
    data-required-label="<?= e(translate('initiatives.selection_required')) ?>"
    data-count-label="<?= e(translate('initiatives.selected_count')) ?>"
    data-results-label="<?= e(translate('initiatives.selection_results')) ?>">
    <label id="<?= $pickerId ?>-label" for="<?= $pickerId ?>-button" class="<?= $labelClass ?> floating-title"><?= e($label) ?></label>
    <div class="module" role="group" aria-labelledby="<?= $pickerId ?>-label">
        <input type="hidden" name="values[objectives]" value="">
        <p class="text-muted objective-selection-empty" data-objective-selection-empty <?= $selected ? 'hidden' : '' ?>><?= translate('initiatives.selection_empty') ?></p>
        <ul class="objective-selected" data-objective-selected aria-label="<?= e(translate('initiatives.selected_entries')) ?>" hidden></ul>
        <div class="objective-picker" data-objective-picker>
            <button id="<?= $pickerId ?>-button" class="btn objective-picker-button" type="button" data-objective-toggle aria-expanded="false" aria-controls="<?= $pickerId ?>-panel" hidden>
                <i class="ph ph-target ph-2x" aria-hidden="true"></i>
                <span class="objective-picker-button-text"><strong><?= e(translate('initiatives.choose_entries', ['label' => $label])) ?></strong><small><?= translate('initiatives.objective_search_hint') ?></small></span>
                <span class="badge primary" data-objective-count hidden></span>
                <i class="ph ph-caret-down" aria-hidden="true"></i>
            </button>
            <div id="<?= $pickerId ?>-panel" class="objective-picker-panel" data-objective-panel role="region" aria-labelledby="<?= $pickerId ?>-button">
                <div class="objective-picker-toolbar">
                    <div class="objective-picker-search">
                        <label class="sr-only" for="<?= $pickerId ?>-search"><?= translate('initiatives.search_selection') ?></label>
                        <input id="<?= $pickerId ?>-search" type="search" class="form-control" data-objective-search disabled placeholder="<?= e(translate('initiatives.search_selection')) ?>" autocomplete="off" aria-controls="<?= $pickerId ?>-options">
                    </div>
                    <div data-selection-controls
                        data-choose-date="<?= e(translate('initiatives.choose_activity_date')) ?>"
                        data-no-entries="<?= e(translate('initiatives.no_entries')) ?>"
                        data-no-matches="<?= e(translate('initiatives.no_matching_entries')) ?>"
                        data-invalid="<?= e(translate('initiatives.selection_outside_period')) ?>">
                        <?php if ($Settings->hasPermission('initiatives.select-outside-period')): ?>
                            <button type="button" class="btn small primary" data-show-all aria-expanded="false"
                                data-show-label="<?= e(translate('initiatives.show_all')) ?>" data-hide-label="<?= e(translate('initiatives.show_matching')) ?>">
                                <?= translate('initiatives.show_all') ?>
                            </button>
                        <?php endif; ?>
                        <p class="text-muted font-size-12" data-selection-status role="status"><?= translate('initiatives.selection_hint') ?></p>
                        <select class="form-control" data-selection-empty required hidden disabled aria-label="<?= e($label) ?>">
                            <option value=""><?= translate('initiatives.no_matching_entries') ?></option>
                        </select>
                    </div>
                    <style>
                        [data-initiative-selector] [hidden] {
                            display: none !important;
                        }
                    </style>
                    <script src="<?= ROOTPATH ?>/js/initiative-selector.js?v=<?= OSIRIS_BUILD ?>" defer></script>

                </div>
                <p class="objective-result-count" data-objective-results role="status" aria-live="polite" aria-atomic="true"></p>
                <div id="<?= $pickerId ?>-options" class="objective-picker-options">
                    <?php foreach ($groups as $group):
                        $initiative = $group['initiative'];
                        $parentName = Initiatives::text($initiative['name'] ?? '');
                        usort($group['objectives'], function ($a, $b) {
                            $pastA = !empty($a['inactive']) || Initiatives::isExpired($a);
                            $pastB = !empty($b['inactive']) || Initiatives::isExpired($b);
                            return ($pastA <=> $pastB) ?: strnatcasecmp(($a['code'] ?? '') . Initiatives::text($a['name'] ?? ''), ($b['code'] ?? '') . Initiatives::text($b['name'] ?? ''));
                        });
                    ?>
                        <div class="objective-group" data-objective-group role="group" aria-label="<?= e($parentName) ?>">
                            <div class="objective-group-heading"><i class="ph ph-compass" aria-hidden="true"></i> <?= e($parentName) ?></div>
                            <div class="objective-options">
                                <?php foreach ($group['objectives'] as $objective):
                                    $id = (string) $objective['_id'];
                                    $name = Initiatives::text($objective['name'] ?? '');
                                    $name = (empty($objective['code']) ? '' : $objective['code'] . ': ') . $name;
                                    $description = trim(strip_tags(localized($objective['description'] ?? '')));
                                    $archived = !empty($objective['inactive']) || !empty($initiative['inactive']);
                                    $expired = Initiatives::isExpired($objective) || Initiatives::isExpired($initiative);
                                    $state = $archived || $expired ? translate($archived ? 'initiatives.archived' : 'initiatives.expired') : '';
                                ?>
                                    <label data-selection-option data-period="<?= e(json_encode(Initiatives::selectionBounds($objective, $initiative))) ?>"
                                        data-search-text="<?= e($parentName . ' ' . $name . ' ' . $description) ?>" data-objective-name="<?= e($name) ?>" data-objective-parent="<?= e($parentName) ?>" data-objective-state="<?= e($state) ?>"
                                        class="objective-option<?= $archived || $expired ? ' inactive' : '' ?>">
                                        <input type="checkbox" name="values[objectives][]" value="<?= e($id) ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                                        <span class="objective-option-content"><strong class="objective-option-title"><?= e($name) ?></strong>
                                            <?php if ($description !== ''): ?><small class="objective-option-description"><?= e($description) ?></small><?php endif; ?>
                                            <?php if ($state !== ''): ?><small class="text-muted"><?= e($state) ?></small><?php endif; ?>
                                            <small class="objective-period-marker" data-period-warning hidden><i class="ph ph-clock" aria-hidden="true"></i> <?= translate('initiatives.outside_period') ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <span class="sr-only" data-objective-announcement role="status" aria-live="polite" aria-atomic="true"></span>
    </div>
    <?= $this->render_help($help) ?>
</div>
<script src="<?= ROOTPATH ?>/js/objective-selector.js?v=<?= OSIRIS_BUILD ?>" defer></script>