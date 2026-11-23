<?php foreach ($contentSections as $key => [$label, $icon, $route]):
    if (!$linkedContents[$key]) continue;
?>
<section class="initiative-section mt-20" data-initiative-section="<?= $key ?>" id="contents-<?= $key ?>" hidden>
    <table class="table initiative-content-table w-full">
        <thead><tr><th><?= t('common.name') ?></th><th><?= t('common.date') ?></th></tr></thead>
        <tbody>
        <?php foreach ($linkedContents[$key] as $content):
            $contentId = (string) $content['_id'];
            $name = Initiatives::text($content['name'] ?? $content['title'] ?? $content['id'] ?? '');
            $title = Initiatives::text($content['title'] ?? $content['subtitle'] ?? '');
            $start = $key === 'proposals' ? ($content['start_proposed'] ?? null) : ($content['start_date'] ?? $content['start'] ?? null);
            $end = $key === 'proposals' ? ($content['end_proposed'] ?? null) : ($content['end_date'] ?? $content['end'] ?? null);
            $date = match ($key) {
                'publications', 'activities' => e($content['year'] ?? ''),
                'deadlines' => empty($content['date']) ? '' : format_date($content['date']),
                default => !$start && !$end ? '' : e(Initiatives::period(['start_date' => $start, 'end_date' => $end])),
            };
        ?>
            <tr>
                <td>
                    <?php if (in_array($key, ['publications', 'activities'], true) && !empty($content['rendered']['web'])): ?>
                        <?= $content['rendered']['web'] ?>
                    <?php else: ?>
                        <a href="<?= ROOTPATH ?>/<?= $route ?>/view/<?= e($contentId) ?>"><?= e($name) ?></a>
                        <?php if ($title !== '' && $title !== $name): ?><div class="text-muted"><?= e($title) ?></div><?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?= $date ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endforeach; ?>
<script src="<?= ROOTPATH ?>/js/initiatives.js?v=<?= OSIRIS_BUILD ?>"></script>
