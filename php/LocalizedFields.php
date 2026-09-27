<?php

/**
 * Render a multilingual form field.
 *
 * Example:
 * localizedField($form, 'name', lang('forms.full_name'), ['required' => true]);
 *
 * Supported options:
 * - type: text (default) or richtext
 * - required: whether the base language is required
 * - class: additional classes for a text input
 */
function localizedField($form, string $name, string $label, array $options = []): void
{
    global $Settings;

    $baseLanguage = OSIRIS_BASE_LANGUAGE;
    $type = $options['type'] ?? 'text';
    $required = $options['required'] ?? false;
    $inputClass = trim('form-control ' . ($options['class'] ?? ''));
    $translations = localizedFieldValues($form, $name, $baseLanguage);
    $configuredLanguages = $Settings->contentLanguages();

    $languages = array_values(array_filter(
        array_unique(array_merge([$baseLanguage], $configuredLanguages, array_keys($translations))),
        fn($language) => $language !== 'keys' && preg_match('/^[a-z]{2}(?:-[A-Za-z]{2})?$/', $language)
    ));

    $fieldId = preg_replace('/[^A-Za-z0-9_-]/', '-', $name);
    ?>
    <div class="form-group localized-field"
         data-localized-field="<?= e($fieldId) ?>"
         data-base-language="<?= e($baseLanguage) ?>">
        <div class="localized-field-heading">
            <label for="<?= e($fieldId . '-' . $baseLanguage) ?>" class="<?= $required ? 'required' : '' ?>">
                <?= $label ?>
            </label>
            <div class="localized-language-tabs" role="tablist" aria-label="<?= e($label) ?>">
                <?php foreach ($languages as $language) {
                    $translation = $translations[$language] ?? '';
                    $hasValue = localizedValueHasContent($translation, $type);
                    $flag = localizedLanguageFlag($language);
                    $isBaseLanguage = $language === $baseLanguage;
                    $tabLabel = lang('common.lang_' . $language);
                    if ($isBaseLanguage) {
                        $tabLabel .= ' · ' . lang('common.default_language');
                    }
                    ?>
                    <button type="button"
                            id="<?= e($fieldId . '-' . $language . '-tab') ?>"
                            class="localized-language-tab <?= $hasValue ? 'has-value' : 'is-empty' ?> <?= $isBaseLanguage ? 'active' : '' ?>"
                            data-language="<?= e($language) ?>"
                            role="tab"
                            tabindex="<?= $isBaseLanguage ? '0' : '-1' ?>"
                            aria-selected="<?= $isBaseLanguage ? 'true' : 'false' ?>"
                            aria-controls="<?= e($fieldId . '-' . $language . '-panel') ?>"
                            aria-label="<?= e($tabLabel) ?>"
                            title="<?= e($tabLabel) ?>"
                            onclick="selectLocalizedLanguage(this)">
                        <?php if ($flag !== null) { ?>
                            <img src="<?= e($flag) ?>" class="localized-language-flag" alt="" aria-hidden="true">
                        <?php } ?>
                        <span><?= e(strtoupper($language)) ?></span>
                        <?php if ($isBaseLanguage) { ?>
                            <i class="ph-fill ph-star localized-default-language" aria-hidden="true"></i>
                        <?php } ?>
                        <span class="localized-language-status" aria-hidden="true"></span>
                    </button>
                <?php } ?>
            </div>
        </div>

        <div class="localized-language-panels">
            <?php foreach ($languages as $language) {
                $translation = $translations[$language] ?? '';
                $isBaseLanguage = $language === $baseLanguage;
                ?>
                <div class="localized-language-panel"
                     id="<?= e($fieldId . '-' . $language . '-panel') ?>"
                     data-language="<?= e($language) ?>"
                     role="tabpanel"
                     aria-labelledby="<?= e($fieldId . '-' . $language . '-tab') ?>"
                     <?= $isBaseLanguage ? '' : 'hidden' ?>>
                    <?php renderLocalizedFieldInput(
                        $name,
                        $fieldId,
                        $language,
                        $translation,
                        $type,
                        $inputClass,
                        $required && $isBaseLanguage
                    ); ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}

/**
 * Normalize the current language map and the legacy field / field_<language> structure.
 * Legacy values are only considered until the main field contains a language map.
 */
function localizedFieldValues($form, string $name, string $baseLanguage): array
{
    if ($form instanceof Traversable) {
        $form = iterator_to_array($form);
    }
    if (!is_array($form)) {
        return [$baseLanguage => ''];
    }

    $value = $form[$name] ?? null;
    if ($value instanceof Traversable) {
        $value = iterator_to_array($value);
    }
    if (is_array($value)) {
        return $value;
    }

    $translations = [$baseLanguage => (string) ($value ?? '')];
    $legacyPattern = '/^' . preg_quote($name, '/') . '_([a-z]{2}(?:-[A-Za-z]{2})?)$/';

    foreach ($form as $key => $legacyValue) {
        if (!is_string($key) || !preg_match($legacyPattern, $key, $matches)) continue;
        if ($legacyValue === null || $legacyValue === '') continue;
        $translations[$matches[1]] = $legacyValue;
    }

    return $translations;
}

function localizedLanguageFlag(string $language): ?string
{
    $language = strtolower(explode('-', $language)[0]);
    $flag = match ($language) {
        'en' => 'gb',
        'ja' => 'jp',
        default => $language,
    };
    $file = BASEPATH . '/img/flags/' . $flag . '.svg';

    return is_file($file) ? ROOTPATH . '/img/flags/' . $flag . '.svg' : null;
}

function localizedValueHasContent($value, string $type): bool
{
    $value = trim((string) ($value ?? ''));
    if ($value === '') return false;
    if ($type !== 'richtext') return true;

    $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />'], '', $value)));
    return trim(str_replace("\xc2\xa0", ' ', $text)) !== ''
        || preg_match('/<(img|video|iframe)\b/i', $value) === 1;
}

function renderLocalizedFieldInput(
    string $name,
    string $fieldId,
    string $language,
    $value,
    string $type,
    string $inputClass,
    bool $required
): void {
    $id = $fieldId . '-' . $language;
    $inputName = 'values[' . $name . '][' . $language . ']';

    if ($type === 'richtext') { ?>
        <div class="localized-quill" id="<?= e($id) ?>-quill"><?= $value ?></div>
        <textarea name="<?= e($inputName) ?>"
                  id="<?= e($id) ?>"
                  class="d-none localized-value"
                  readonly><?= e($value) ?></textarea>
    <?php } else { ?>
        <input type="text"
               class="<?= e($inputClass) ?> localized-value"
               name="<?= e($inputName) ?>"
               id="<?= e($id) ?>"
               value="<?= e($value) ?>"
               <?= $required ? 'required' : '' ?>>
    <?php }
}
