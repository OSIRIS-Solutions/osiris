<?php

/**
 * Test migration for legacy adminGeneral *_label settings.
 *
 * The legacy settings remain untouched so the migration can be inspected and
 * repeated safely while their consumers are moved to translation keys.
 */

require_once BASEPATH . '/php/LanguageOverrides.php';

$labelKeyMap = [
    'infrastructures_label' => 'common.infrastructures',
    'topics_label' => 'common.research_topics',
    'tags_label' => 'common.tags',
    'journals_label' => 'common.journals',
    'impact_label' => 'common.cite_factor',
];

$repository = new LanguageOverrides($osiris, BASEPATH . '/lang');
$catalogues = [
    'en' => $repository->catalogue('en'),
    'de' => $repository->catalogue('de'),
];
$existingOverrides = [
    'en' => $repository->all('en'),
    'de' => $repository->all('de'),
];

$storedLabels = $osiris->adminGeneral->find([
    'key' => new MongoDB\BSON\Regex('_label$'),
], [
    'sort' => ['key' => 1],
])->toArray();

$rows = [];
$created = 0;
$unchanged = 0;
$existing = 0;
$skipped = 0;

foreach ($storedLabels as $document) {
    $document = DB::doc2Arr($document);
    $legacyKey = (string) ($document['key'] ?? '');
    $translationKey = $labelKeyMap[$legacyKey] ?? null;

    if ($translationKey === null) {
        $rows[] = [
            'legacy_key' => $legacyKey,
            'translation_key' => '—',
            'language' => '—',
            'value' => '',
            'status' => 'skipped',
            'message' => t('admin.no_matching_translation_key_is_configured'),
        ];
        $skipped++;
        continue;
    }

    $values = DB::doc2Arr($document['value'] ?? []);
    if (!is_array($values)) {
        $values = [];
    }

    foreach (['en', 'de'] as $language) {
        $value = trim((string) ($values[$language] ?? ''));
        $row = [
            'legacy_key' => $legacyKey,
            'translation_key' => $translationKey,
            'language' => $language,
            'value' => $value,
            'status' => '',
            'message' => '',
        ];

        if ($value === '') {
            $row['status'] = 'skipped';
            $row['message'] = t('admin.no_stored_value_exists_for_this_language');
            $skipped++;
        } elseif (!isset($catalogues[$language][$translationKey])) {
            $row['status'] = 'skipped';
            $row['message'] = t('admin.the_target_translation_key_does_not_exist');
            $skipped++;
        } elseif (isset($existingOverrides[$language][$translationKey])) {
            $row['status'] = 'existing';
            $row['message'] = t('admin.an_override_already_exists_and_was_not_changed');
            $existing++;
        } elseif ($value === $catalogues[$language][$translationKey]['default']) {
            $row['status'] = 'unchanged';
            $row['message'] = t('admin.the_value_matches_the_standard_translation_no_override_is_needed');
            $unchanged++;
        } else {
            try {
                $repository->save(
                    $language,
                    $translationKey,
                    $value,
                    (string) ($_SESSION['username'] ?? 'migration')
                );
                $existingOverrides[$language][$translationKey] = ['value' => $value];
                $row['status'] = 'created';
                $row['message'] = t('admin.override_created');
                $created++;
            } catch (Throwable $exception) {
                $row['status'] = 'skipped';
                $row['message'] = $exception->getMessage();
                $skipped++;
            }
        }

        $rows[] = $row;
    }
}

?>
<div class="migration-report">
    <h1><?= t('admin.migrate_language_labels') ?></h1>

    <div class="migration-card">
        <h3 class="migration-ok">✓ <?= t('admin.language_label_migration_completed') ?></h3>
        <p>
            <?= t('admin.stored_english_and_german_label_settings_were_converted_to_languag') ?>
        </p>
        <div class="migration-summary">
            <div class="migration-stat"><strong><?= $created ?></strong><span><?= t('common.created') ?></span></div>
            <div class="migration-stat"><strong><?= $existing ?></strong><span><?= t('admin.already_present') ?></span></div>
            <div class="migration-stat"><strong><?= $unchanged ?></strong><span><?= t('admin.standard_values') ?></span></div>
            <div class="migration-stat"><strong><?= $skipped ?></strong><span><?= t('admin.skipped') ?></span></div>
        </div>
    </div>

    <?php if (empty($storedLabels)) { ?>
        <div class="alert signal">
            <?= t('admin.no_stored_label_settings_were_found') ?>
        </div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table simple small">
                <thead>
                    <tr>
                        <th><?= t('admin.legacy_setting') ?></th>
                        <th><?= t('admin.translation_key') ?></th>
                        <th><?= t('news.language') ?></th>
                        <th><?= t('common.value') ?></th>
                        <th><?= t('admin.result') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row) { ?>
                        <tr>
                            <td><code><?= e($row['legacy_key']) ?></code></td>
                            <td><code><?= e($row['translation_key']) ?></code></td>
                            <td><?= e(strtoupper($row['language'])) ?></td>
                            <td><?= e($row['value']) ?></td>
                            <td>
                                <?php if ($row['status'] === 'created') { ?>
                                    <span class="text-success">✓</span>
                                <?php } elseif ($row['status'] === 'skipped') { ?>
                                    <span class="text-danger">–</span>
                                <?php } else { ?>
                                    <span class="text-muted">•</span>
                                <?php } ?>
                                <?= e($row['message']) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>
