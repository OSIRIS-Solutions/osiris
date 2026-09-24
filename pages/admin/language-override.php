<?php

/**
 * Page for admin dashboard for general settings
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /admin/general
 *
 * @package OSIRIS
 * @since 1.1.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$lang = $lang ?? 'en';
require_once BASEPATH . '/php/LanguageOverrides.php';

$repository = new LanguageOverrides($osiris, BASEPATH . '/lang');
$catalogue = $repository->catalogue($lang);
$overrides = $repository->all($lang);
$_SESSION['language_override_csrf'] ??= bin2hex(random_bytes(32));
$csrfToken = (string) $_SESSION['language_override_csrf'];
$selectedKey = isset($_GET['key']) ? (string) $_GET['key'] : null;
if ($selectedKey !== null && (!isset($catalogue[$selectedKey]) || !$catalogue[$selectedKey]['editable'])) {
    $selectedKey = null;
}

$available = array_filter(
    $catalogue,
    fn(array $entry, string $key): bool => $entry['editable'] && !isset($overrides[$key]),
    ARRAY_FILTER_USE_BOTH
);
$rows = array_keys($overrides);
if ($selectedKey !== null && !in_array($selectedKey, $rows, true)) {
    $rows[] = $selectedKey;
}
natcasesort($rows);
?>

<div class="container w-800 mw-full">

    <h1>
        <i class="ph-duotone ph-translate"></i>
        <?= translate('common.lang_' . $lang) ?>
    </h1>
    <h2 class="subtitle">
        <?= translate('admin.language_override') ?>
    </h2>

    <p class="text-muted">
        <?= translate('admin.language_override_description') ?>
    </p>

    <?php if (!empty($available)) { ?>
        <form action="<?= ROOTPATH ?>/admin/language-override/<?= e($lang) ?>" method="get" class="box padded mb-20">
            <label for="language-override-key"><?= translate('admin.add_language_override') ?></label>
            <div class="d-flex align-items-end">
                <div class="flex-grow-1 mr-10">
                    <select name="key" id="language-override-key" class="form-control" required>
                        <option value=""><?= translate('admin.select_language_key') ?></option>
                        <?php foreach ($available as $key => $entry) { ?>
                            <option value="<?= e($key) ?>" <?= $selectedKey === $key ? 'selected' : '' ?>>
                                <?= e($key . ' — ' . $entry['source']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <button class="btn primary flex-shrink-0" type="submit">
                    <i class="ph ph-plus"></i>
                    <?= translate('action.add') ?>
                </button>
            </div>
        </form>
    <?php } ?>

    <?php if (empty($rows)) { ?>
        <div class="alert signal">
            <?= translate('admin.no_language_overrides') ?>
        </div>
    <?php } else { ?>
        <form action="<?= ROOTPATH ?>/crud/admin/language-override/<?= e($lang) ?>" method="post">
            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
            <div class="table-responsive">
                <table class="table simple small">
                    <thead>
                        <tr>
                            <th><?= translate('admin.language_key') ?></th>
                            <th><?= translate('admin.standard_translation') ?></th>
                            <th><?= translate('admin.language_override_value') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $key) {
                            $entry = $catalogue[$key] ?? null;
                            $override = (string) ($overrides[$key]['value'] ?? '');
                            ?>
                            <tr>
                                <td>
                                    <code><?= e($key) ?></code>
                                    <?php if ($entry !== null && $lang !== 'en') { ?>
                                        <small class="text-muted d-block"><?= e($entry['source']) ?></small>
                                    <?php } ?>
                                </td>
                                <td><?= e($entry['default'] ?? '') ?></td>
                                <td>
                                    <?php if ($entry !== null && $entry['editable']) { ?>
                                        <textarea
                                            class="form-control"
                                            name="overrides[<?= e($key) ?>]"
                                            rows="2"
                                            maxlength="5000"
                                            required
                                        ><?= e($override) ?></textarea>
                                        <?php if (!empty($entry['placeholders'])) { ?>
                                            <small class="text-muted">
                                                <?= translate('admin.required_placeholders') ?>:
                                                <?= e(implode(', ', $entry['placeholders'])) ?>
                                            </small>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <span class="text-muted"><?= translate('admin.language_key_no_longer_available') ?></span>
                                    <?php } ?>
                                </td>
                                <td class="text-right">
                                    <?php if (isset($overrides[$key])) { ?>
                                        <button
                                            class="btn link text-danger small"
                                            type="submit"
                                            name="delete"
                                            value="<?= e($key) ?>"
                                            formnovalidate
                                            title="<?= translate('admin.reset_to_default') ?>"
                                        >
                                            <i class="ph ph-arrow-counter-clockwise"></i>
                                            <?= translate('admin.reset_to_default') ?>
                                        </button>
                                    <?php } else { ?>
                                        <a class="btn link small" href="<?= ROOTPATH ?>/admin/language-override/<?= e($lang) ?>">
                                            <?= translate('action.cancel') ?>
                                        </a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <button class="btn success mt-20" type="submit">
                <i class="ph ph-floppy-disk"></i>
                <?= translate('common.save_changes') ?>
            </button>
        </form>
    <?php } ?>


</div>

<script>
    $(function () {
        $('#language-override-key').selectize({
            create: false,
            maxOptions: 5000,
            sortField: [{ field: 'text', direction: 'asc' }]
        });
    });
</script>
