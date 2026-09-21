<style>
    .preference-view-banner {
        border-bottom: 1px solid var(--border-color, #ddd);
        padding: 1rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0;
        background-color: var(--gray-color-very-light);
    }

    .preference-view-banner.subtle {
        opacity: 0.95;
    }

    .preference-view-banner.info {
        background: rgba(0, 0, 0, 0.02);
    }

    .preference-view-banner .banner-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .preference-view-banner .banner-text {
        font-size: 1.2rem;
    }
</style>
<?php
// Current view of this page: set in controller/view
// $currentView = 'new' or 'legacy';
$preference = $USER['activity_view'] ?? 'none';

// Optional override via URL (does NOT save preference)
$forcedView = $_GET['view'] ?? null;
if (in_array($forcedView, ['new', 'legacy'], true)) {
    $currentView = $forcedView;
}

$otherView = ($currentView === 'new') ? 'legacy' : 'new';
$otherLabel = ($otherView === 'new')
    ? lang('activities.modern_view_preference_banner')
    : lang('activities.classic_view_preference_banner');

$currentLabel = ($currentView === 'new')
    ? lang('activities.modern_view_preference_banner')
    : lang('activities.classic_view_preference_banner');

$showBanner = false;
$bannerText = '';
$bannerToneClass = 'info'; // info | subtle | warning (your CSS)


$saveLabel = ($currentView === 'new')
    ? lang('activities.set_modern_view_as_default')
    : lang('activities.set_classic_view_as_default');

$switchLabel = ($otherView === 'new')
    ? lang('activities.try_modern_view')
    : lang('activities.switch_to_classic_view');


// Case A: no preference yet -> invite to try + set default
if ($preference === 'none') {
    $showBanner = true;
    $bannerText = lang('activities.you_can_switch_between_the_modern_and_classic_activity_view_pick_one_as_you');
}
// Case B: preference exists, but user is currently looking at the other view -> offer "make this my default"
elseif ($preference !== $currentView) {
    $showBanner = true;
    $bannerToneClass = 'subtle';
    $bannerText = lang('activities.you_are_viewing_the_currentlabel_want_to_make_this_your_default', replace: ['currentLabel' => ($currentLabel)]);
    $switchLabel = lang('activities.go_back_to_otherlabel', replace: ['otherLabel' => ($otherLabel)]);
}

?>

<?php if ($showBanner): ?>
    <div class="preference-view-banner <?= htmlspecialchars($bannerToneClass) ?>">
        <div class="banner-text">
            <b><?= lang('activities.activity_view') ?></b>
            <?= htmlspecialchars($bannerText) ?>
        </div>

        <div class="banner-actions">
            <!-- Switch view (no preference change) -->

            <a class="btn small"
                href="<?= ROOTPATH ?>/activities/view/<?= $id ?>?view=<?= $otherView ?>">
                <i class="ph ph-arrows-left-right" aria-hidden="true"></i>
                <?= htmlspecialchars($switchLabel) ?>
            </a>

            <!-- Save preference -->
            <form action="<?= ROOTPATH ?>/crud/users/set-preference" method="post" class="d-inline-block">
                <input type="hidden" name="key" value="activity_view">
                <input type="hidden" name="value" value="<?= htmlspecialchars($currentView) ?>">
                <input type="hidden" name="redirect" value="<?= ROOTPATH ?>/activities/view/<?= $id ?>?view=<?= $currentView ?>">
                <button type="submit" class="btn small primary">
                    <i class="ph ph-check" aria-hidden="true"></i>
                    <?= htmlspecialchars($saveLabel) ?>
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>