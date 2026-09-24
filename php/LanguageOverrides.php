<?php

/**
 * Storage and validation for institution-wide language overrides.
 *
 * Shared by the translation resolver and the language-override admin page.
 */
class LanguageOverrides
{
    private $collection;

    /** @var array<string, array<string, array<string, mixed>>> */
    private array $catalogues = [];

    /** @var array<string, array<string, array<string, mixed>>> */
    private array $overrideCache = [];

    /** @var array<string, true> */
    private array $loadedOverrideLanguages = [];

    private bool $indexReady = false;

    public function __construct(private $database, private string $languageDirectory)
    {
        $this->collection = $database->languageOverrides;
    }

    /**
     * @return array<string, array{key: string, domain: string, source: string, default: string, editable: bool, placeholders: list<string>}>
     */
    public function catalogue(string $language): array
    {
        $english = $this->loadLanguage('en');
        $translated = $language === 'en' ? $english : $this->loadLanguage($language);
        $catalogue = [];

        foreach ($english as $domain => $messages) {
            foreach ($messages as $messageKey => $source) {
                $key = $domain . '.' . $messageKey;
                $default = $translated[$domain][$messageKey] ?? $source;
                $catalogue[$key] = [
                    'key' => $key,
                    'domain' => $domain,
                    'source' => $source,
                    'default' => $default,
                    // Database content is not trusted like versioned language
                    // files. Rich-text overrides need a separate safe design.
                    'editable' => !$this->containsMarkup($source) && !$this->containsMarkup($default),
                    'placeholders' => $this->placeholders($source),
                ];
            }
        }

        ksort($catalogue, SORT_NATURAL | SORT_FLAG_CASE);
        return $catalogue;
    }

    /** @return array<string, array<string, mixed>> */
    public function all(string $language): array
    {
        if (isset($this->loadedOverrideLanguages[$language])) {
            return $this->overrideCache[$language] ?? [];
        }

        $overrides = [];
        $documents = $this->collection->find(
            ['language' => $language],
            ['sort' => ['key' => 1]]
        );
        foreach ($documents as $document) {
            $item = DB::doc2Arr($document);
            if (isset($item['key'], $item['value'])) {
                $overrides[(string) $item['key']] = $item;
            }
        }
        ksort($overrides, SORT_NATURAL | SORT_FLAG_CASE);
        $this->loadedOverrideLanguages[$language] = true;
        return $this->overrideCache[$language] = $overrides;
    }

    public function value(string $language, string $key): ?string
    {
        $override = $this->all($language)[$key]['value'] ?? null;
        return is_string($override) && $override !== '' ? $override : null;
    }

    public function save(string $language, string $key, string $value, string $username): void
    {
        $catalogue = $this->catalogue($language);
        if (!isset($catalogue[$key])) {
            throw new InvalidArgumentException('language_override_unknown_key');
        }

        $entry = $catalogue[$key];
        if (!$entry['editable']) {
            throw new InvalidArgumentException('language_override_html_not_supported');
        }

        $value = trim($value);
        if ($value === '' || $value === $entry['default']) {
            $this->delete($language, $key);
            return;
        }
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length > 5000) {
            throw new InvalidArgumentException('language_override_too_long');
        }
        if ($this->containsMarkup($value)) {
            throw new InvalidArgumentException('language_override_html_not_supported');
        }
        if ($this->placeholders($value) !== $entry['placeholders']) {
            throw new InvalidArgumentException('language_override_placeholder_mismatch');
        }

        $this->ensureIndex();
        $this->collection->updateOne(
            ['language' => $language, 'key' => $key],
            ['$set' => [
                'value' => $value,
                'updated_by' => $username,
                'updated_at' => new MongoDB\BSON\UTCDateTime(),
            ]],
            ['upsert' => true]
        );
        if (isset($this->loadedOverrideLanguages[$language])) {
            $this->overrideCache[$language][$key] = [
                'language' => $language,
                'key' => $key,
                'value' => $value,
                'updated_by' => $username,
            ];
            ksort($this->overrideCache[$language], SORT_NATURAL | SORT_FLAG_CASE);
        }
    }

    public function delete(string $language, string $key): void
    {
        $this->collection->deleteOne(['language' => $language, 'key' => $key]);
        unset($this->overrideCache[$language][$key]);
    }

    /** @return array<string, array<string, string>> */
    private function loadLanguage(string $language): array
    {
        if (isset($this->catalogues[$language])) {
            return $this->catalogues[$language];
        }
        if (!preg_match('/^[a-z]{2}$/', $language)) {
            return [];
        }

        $directory = rtrim($this->languageDirectory, '/') . '/' . $language;
        $catalogue = [];
        foreach (glob($directory . '/*.php') ?: [] as $file) {
            $messages = require $file;
            if (!is_array($messages)) {
                continue;
            }
            $domain = pathinfo($file, PATHINFO_FILENAME);
            foreach ($messages as $key => $value) {
                if (is_string($key) && is_string($value)) {
                    $catalogue[$domain][$key] = $value;
                }
            }
        }
        return $this->catalogues[$language] = $catalogue;
    }

    private function containsMarkup(string $value): bool
    {
        return str_contains($value, '<') || str_contains($value, '>');
    }

    private function ensureIndex(): void
    {
        if ($this->indexReady) {
            return;
        }
        // The migration can create the same index up front once the storage
        // model is finalized. Until then this remains idempotent per save run.
        $this->collection->createIndex(['language' => 1, 'key' => 1], ['unique' => true]);
        $this->indexReady = true;
    }

    /** @return list<string> */
    private function placeholders(string $value): array
    {
        preg_match_all('/\{\{[A-Za-z0-9_.-]+\}\}/u', $value, $matches);
        $placeholders = $matches[0] ?? [];
        sort($placeholders, SORT_STRING);
        return array_values($placeholders);
    }
}
