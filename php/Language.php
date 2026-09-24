<?php

require_once __DIR__ . '/LanguageOverrides.php';

/**
 * Resolves versioned interface translations and optional database overrides.
 */
final class Language
{
    /** @var array<string, array<string, string>> */
    private array $translations = [];

    private ?LanguageOverrides $overrides = null;
    private bool $overrideLookupFailed = false;

    public function __construct(private string $languageDirectory)
    {
    }

    public function useDatabase($database): void
    {
        $this->overrides = new LanguageOverrides($database, $this->languageDirectory);
        $this->overrideLookupFailed = false;
    }

    public function resolve(string $key, string $language, string $baseLanguage): string
    {
        if ($language === 'keys') {
            return $key;
        }

        $separator = strpos($key, '.');
        if ($separator === false || $separator === 0 || $separator === strlen($key) - 1) {
            return $key;
        }
        $domain = substr($key, 0, $separator);
        $messageKey = substr($key, $separator + 1);

        foreach (array_unique([$language, $baseLanguage]) as $candidate) {
            $override = $this->override($candidate, $key);
            if ($override !== null) {
                return $override;
            }

            $messages = $this->load($candidate, $domain);
            if (isset($messages[$messageKey]) && is_string($messages[$messageKey])) {
                return $messages[$messageKey];
            }
        }

        return $key;
    }

    private function override(string $language, string $key): ?string
    {
        if ($this->overrides === null || $this->overrideLookupFailed || $language === 'keys') {
            return null;
        }
        try {
            return $this->overrides->value($language, $key);
        } catch (Throwable $exception) {
            // Overrides are optional. A lookup problem must not take down the
            // interface or prevent the versioned translations from working.
            error_log('Could not load language overrides: ' . $exception->getMessage());
            $this->overrideLookupFailed = true;
            return null;
        }
    }

    /** @return array<string, string> */
    private function load(string $language, string $domain): array
    {
        if (!preg_match('/^[a-z]{2}$/', $language) || !preg_match('/^[A-Za-z0-9_-]+$/', $domain)) {
            return [];
        }

        $cacheKey = $language . '.' . $domain;
        if (isset($this->translations[$cacheKey])) {
            return $this->translations[$cacheKey];
        }

        $file = rtrim($this->languageDirectory, '/') . '/' . $language . '/' . $domain . '.php';
        if (!is_file($file)) {
            return $this->translations[$cacheKey] = [];
        }
        $messages = require $file;
        if (!is_array($messages)) {
            return $this->translations[$cacheKey] = [];
        }

        return $this->translations[$cacheKey] = $messages;
    }
}
