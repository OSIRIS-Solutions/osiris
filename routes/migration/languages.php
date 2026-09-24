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
            'message' => lang('No matching translation key is configured.', 'Es ist kein passender Sprachschlüssel konfiguriert.'),
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
            $row['message'] = lang('No stored value exists for this language.', 'Für diese Sprache ist kein Wert gespeichert.');
            $skipped++;
        } elseif (!isset($catalogues[$language][$translationKey])) {
            $row['status'] = 'skipped';
            $row['message'] = lang('The target translation key does not exist.', 'Der Ziel-Sprachschlüssel existiert nicht.');
            $skipped++;
        } elseif (isset($existingOverrides[$language][$translationKey])) {
            $row['status'] = 'existing';
            $row['message'] = lang('An override already exists and was not changed.', 'Es existiert bereits eine Anpassung, die nicht verändert wurde.');
            $existing++;
        } elseif ($value === $catalogues[$language][$translationKey]['default']) {
            $row['status'] = 'unchanged';
            $row['message'] = lang('The value matches the standard translation; no override is needed.', 'Der Wert entspricht der Standardübersetzung; es ist keine Anpassung nötig.');
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
                $row['message'] = lang('Override created.', 'Anpassung angelegt.');
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
    <h1><?= lang('Migrate language labels', 'Sprachbezeichnungen migrieren') ?></h1>

    <div class="migration-card">
        <h3 class="migration-ok">✓ <?= lang('Language label migration completed', 'Migration der Sprachbezeichnungen abgeschlossen') ?></h3>
        <p>
            <?= lang(
                'Stored English and German label settings were converted to language overrides. Existing overrides and legacy settings were left unchanged.',
                'Gespeicherte deutsche und englische Label-Einstellungen wurden in Sprachanpassungen überführt. Bestehende Anpassungen und die alten Einstellungen wurden nicht verändert.'
            ) ?>
        </p>
        <div class="migration-summary">
            <div class="migration-stat"><strong><?= $created ?></strong><span><?= lang('created', 'angelegt') ?></span></div>
            <div class="migration-stat"><strong><?= $existing ?></strong><span><?= lang('already present', 'bereits vorhanden') ?></span></div>
            <div class="migration-stat"><strong><?= $unchanged ?></strong><span><?= lang('standard values', 'Standardwerte') ?></span></div>
            <div class="migration-stat"><strong><?= $skipped ?></strong><span><?= lang('skipped', 'übersprungen') ?></span></div>
        </div>
    </div>

    <?php if (empty($storedLabels)) { ?>
        <div class="alert signal">
            <?= lang('No stored label settings were found.', 'Es wurden keine gespeicherten Label-Einstellungen gefunden.') ?>
        </div>
    <?php } else { ?>
        <div class="table-responsive">
            <table class="table simple small">
                <thead>
                    <tr>
                        <th><?= lang('Legacy setting', 'Alte Einstellung') ?></th>
                        <th><?= lang('Translation key', 'Sprachschlüssel') ?></th>
                        <th><?= lang('Language', 'Sprache') ?></th>
                        <th><?= lang('Value', 'Wert') ?></th>
                        <th><?= lang('Result', 'Ergebnis') ?></th>
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
