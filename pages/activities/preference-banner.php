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
    ? lang('Modern view', 'Modernen Ansicht')
    : lang('Classic view', 'Klassische Ansicht');

$currentLabel = ($currentView === 'new')
    ? lang('Modern view', 'Modernen Ansicht')
    : lang('Classic view', 'Klassische Ansicht');

$showBanner = false;
$bannerText = '';
$bannerToneClass = 'info'; // info | subtle | warning (your CSS)


$saveLabel = ($currentView === 'new')
    ? lang('Set modern view as default', 'Moderne Ansicht als Standard')
    : lang('Set classic view as default', 'Klassische Ansicht als Standard');

$switchLabel = ($otherView === 'new')
    ? lang('Try modern view', 'Moderne Ansicht ausprobieren')
    : lang('Switch to classic view', 'Zur klassischen Ansicht wechseln');


// Case A: no preference yet -> invite to try + set default
if ($preference === 'none') {
    $showBanner = true;
    $bannerText = lang(
        'You can switch between the modern and classic activity view. Pick one as your default anytime.',
        'Du kannst zwischen moderner und klassischer Aktivitätsansicht wechseln. Wenn du magst, setze eine davon als Standard.'
    );
}
// Case B: preference exists, but user is currently looking at the other view -> offer "make this my default"
elseif ($preference !== $currentView) {
    $showBanner = true;
    $bannerToneClass = 'subtle';
    $bannerText = lang(
        'You are viewing the ' . ($currentLabel) . '. Want to make this your default?',
        'Du nutzt gerade die ' . ($currentLabel) . '. Soll das dein Standard werden?'
    );
    $switchLabel = lang('Go back to ' . ($otherLabel), 'Zurück zur ' . ($otherLabel));
}

?>

<?php if ($showBanner): ?>
    <div class="preference-view-banner <?= htmlspecialchars($bannerToneClass) ?>">
        <div class="banner-text">
            <b><?= lang('Activity view:', 'Aktivitätsansicht:') ?></b>
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