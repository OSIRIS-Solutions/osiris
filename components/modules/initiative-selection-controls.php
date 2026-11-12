<?php /** Shared, unobtrusive controls for initiative and objective selection. */ ?>
<div data-selection-controls
    data-choose-date="<?= e(translate('initiatives.choose_activity_date')) ?>"
    data-no-entries="<?= e(translate('initiatives.no_entries')) ?>"
    data-no-matches="<?= e(translate('initiatives.no_matching_entries')) ?>"
    data-invalid="<?= e(translate('initiatives.selection_outside_period')) ?>">
    <?php if ($Settings->hasPermission('initiatives.select-outside-period')): ?>
        <button type="button" class="btn small light" data-show-all aria-expanded="false"
            data-show-label="<?= e(translate('initiatives.show_all')) ?>" data-hide-label="<?= e(translate('initiatives.show_matching')) ?>">
            <?= translate('initiatives.show_all') ?>
        </button>
        <input type="search" class="form-control small mt-10" data-selection-search hidden
            placeholder="<?= e(translate('initiatives.search_selection')) ?>" aria-label="<?= e(translate('initiatives.search_selection')) ?>">
    <?php endif; ?>
    <p class="text-muted font-size-12 my-10" data-selection-status role="status"><?= translate('initiatives.selection_hint') ?></p>
    <select class="form-control" data-selection-empty required hidden disabled aria-label="<?= e($label) ?>">
        <option value=""><?= translate('initiatives.no_matching_entries') ?></option>
    </select>
</div>
<style>[data-initiative-selector] [hidden] { display: none !important; }</style>
<script src="<?= ROOTPATH ?>/js/initiative-selector.js?v=<?= OSIRIS_BUILD ?>" defer></script>
