<?php
if ($Settings->featureEnabled('spectrum')) {
    require_once BASEPATH . '/php/Spectrum.php';
    $spectrum = Spectrum::retrieve($osiris, array_key_first($association), current($association), $Settings->getActivityFilter($association));
    if ($spectrum) {
        echo '<h2>' . t('common.research_spectrum') . '</h2>';
        Spectrum::render($spectrum, $spectrum[0]['total'], '', json_encode($association));
    }
}
?>
<h2><?= t('initiatives.linked_content') ?></h2>
<?php if (!array_filter($linkedContents)): ?>
    <p class="text-muted"><?= t('initiatives.no_linked_content') ?></p>
<?php else: ?>
    <nav class="pills mb-0" aria-label="<?= e(t('initiatives.linked_content')) ?>">
        <?php foreach ($contentSections as $key => [$label, $icon, $route]): if (!$linkedContents[$key]) continue; ?>
            <a class="btn" href="#contents-<?= $key ?>" data-initiative-tab="<?= $key ?>"><i class="ph <?= $icon ?>"></i> <?= t($label) ?> <span class="index"><?= count($linkedContents[$key]) ?></span></a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>
