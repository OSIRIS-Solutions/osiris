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
$labelId = uniqid('initiative-label-');
?>
<div class="data-module col-sm-<?= $width ?>" data-module="initiatives" id="module-initiatives" data-initiative-selector data-required="<?= json_encode($required) ?>">
    <label id="<?= $labelId ?>" class="<?= $labelClass ?> floating-title"><?= e($label) ?></label>
    <div class="module" role="group" aria-labelledby="<?= $labelId ?>">
        <input type="hidden" name="values[initiatives]" value="">
        <div data-selection-controls
            data-choose-date="<?= e(t('initiatives.choose_activity_date')) ?>"
            data-no-entries="<?= e(t('initiatives.no_entries')) ?>"
            data-no-matches="<?= e(t('initiatives.no_matching_entries')) ?>"
            data-invalid="<?= e(t('initiatives.selection_outside_period')) ?>">
            <?php if ($Settings->hasPermission('initiatives.select-outside-period')): ?>
                <button type="button" class="btn small" data-show-all aria-expanded="false"
                    data-show-label="<?= e(t('initiatives.show_all')) ?>" data-hide-label="<?= e(t('initiatives.show_matching')) ?>">
                    <?= t('initiatives.show_all') ?>
                </button>
                <input type="search" class="form-control small mt-10" data-selection-search hidden
                    placeholder="<?= e(t('initiatives.search_selection')) ?>" aria-label="<?= e(t('initiatives.search_selection')) ?>">
            <?php endif; ?>
            <p class="text-muted font-size-12" data-selection-status role="status"><?= t('initiatives.selection_hint') ?></p>
            <select class="form-control" data-selection-empty required hidden disabled aria-label="<?= e($label) ?>">
                <option value=""><?= t('initiatives.no_matching_entries') ?></option>
            </select>
        </div>
        <style>
            [data-initiative-selector] [hidden] {
                display: none !important;
            }
        </style>
        <script src="<?= ROOTPATH ?>/js/initiative-selector.js?v=<?= OSIRIS_BUILD ?>" defer></script>

        <div class="initiative-options">
            <?php foreach ($initiatives as $initiative):
                $id = (string) $initiative['_id'];
                $title = trim(strip_tags(localized($initiative['title'] ?? '')));
                $archived = !empty($initiative['inactive']);
                $expired = Initiatives::isExpired($initiative);
                $class = '';
                if ($expired || $archived) $class .= ' inactive';
            ?>
                <label data-selection-option data-period="<?= e(json_encode(Initiatives::selectionBounds($initiative))) ?>" data-search-text="<?= e(Initiatives::text($initiative['name'] ?? '') . ' ' . $title) ?>" class="initiative-option<?= $class ?>" <?= $title !== '' ? 'data-toggle="tooltip" data-title="' . e($title) . '"' : '' ?>>
                    <input type="checkbox" name="values[initiatives][]" value="<?= e($id) ?>" <?= in_array($id, $selected, true) ? 'checked' : '' ?>>
                    <span class="initiative-option-name"><i class="ph ph-compass" aria-hidden="true"></i> <?= e(trim(strip_tags(localized($initiative['name'] ?? '')))) ?>
                        <?php if ($archived || $expired): ?><small class="text-muted">(<?= $archived ? t('initiatives.archived') : t('initiatives.expired') ?>)</small><?php endif; ?>
                        <small class="text-danger" data-period-warning hidden><?= t('initiatives.outside_period') ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?= $this->render_help($help) ?>
</div>
<style>
    .initiative-options {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .initiative-option[hidden] {
        display: none;
    }

    .initiative-option {
        position: relative;
        display: flex;
        align-items: center;
        gap: .6rem;
        border: 1px solid var(--border-color);
        border-radius: .4rem;
        padding: .7rem 1rem;
        cursor: pointer;
        margin: 0;
    }

    .initiative-option:has(input:checked) {
        border-color: var(--primary-color);
        background: var(--primary-color-20);
        color: var(--primary-color);
    }

    .initiative-option:focus-within {
        outline: 2px solid var(--primary-color);
        outline-offset: 2px;
    }

    .initiative-option input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .initiative-option.inactive {
        opacity: 0.6;
    }
</style>