<?php

/** Configuration shared by news administration and Portfolio output. */
class NewsOutlets
{
    public const TEXT_FIELDS = ['title', 'teaser', 'content'];

    /** Existing outlets keep their title and contribution fields without a migration. */
    public static function fields(array $outlet): array
    {
        if (!array_key_exists('fields', $outlet)) return ['title', 'content'];
        $selected = self::toArray($outlet['fields']);
        return array_values(array_filter(self::TEXT_FIELDS, static fn($field) => in_array($field, $selected, true)));
    }

    private static function toArray($value): array
    {
        if (!is_iterable($value)) return [];
        $result = [];
        foreach ($value as $key => $item) $result[$key] = $item;
        return $result;
    }

    public static function read($value): array
    {
        $value = self::toArray($value);
        $outlets = [];
        foreach (self::toArray($value['outlets'] ?? []) as $outlet) {
            $outlet = self::toArray($outlet);
            foreach (['name', 'description'] as $field) {
                $outlet[$field] = self::toArray($outlet[$field] ?? []);
            }
            $outlet['fields'] = self::fields($outlet);
            $outlets[] = $outlet;
        }
        return [
            // Existing installations continue to expose explicitly public original news.
            'original_portfolio' => $value['original_portfolio'] ?? true,
            'outlets' => $outlets,
        ];
    }

    public static function label($localized): string
    {
        $localized = self::toArray($localized);
        return lang($localized['en'] ?? '', ($localized['de'] ?? '') !== '' ? $localized['de'] : null);
    }

    public static function revision(array $configuration): string
    {
        return hash('sha256', json_encode($configuration, JSON_THROW_ON_ERROR));
    }

    private static function text($value, int $limit): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(lang('Invalid outlet text.', 'Ungültiger Outlet-Text.'));
        }
        $value = trim($value);
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length > $limit) {
            throw new InvalidArgumentException(lang('An outlet text exceeds the maximum length.', 'Ein Outlet-Text überschreitet die maximale Länge.'));
        }
        return $value;
    }

    private static function flag($value): bool
    {
        if (!in_array($value, [true, false, 1, 0, '1', '0'], true)) {
            throw new InvalidArgumentException(lang('Invalid outlet option.', 'Ungültige Outlet-Option.'));
        }
        return in_array($value, [true, 1, '1'], true);
    }

    private static function localizedText($value, int $limit): array
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException(lang('Invalid localized outlet text.', 'Ungültiger lokalisierter Outlet-Text.'));
        }
        return [
            'en' => self::text($value['en'] ?? '', $limit),
            'de' => self::text($value['de'] ?? '', $limit),
        ];
    }

    public static function normalize(array $input, array $current): array
    {
        if (!is_array($input['outlets'] ?? [])) {
            throw new InvalidArgumentException(lang('Invalid outlet list.', 'Ungültige Outlet-Liste.'));
        }
        $knownIds = array_column($current['outlets'], 'id');
        $seenIds = [];
        $outlets = [];
        foreach ($input['outlets'] ?? [] as $row) {
            if (!is_array($row)) {
                throw new InvalidArgumentException(lang('Invalid outlet.', 'Ungültiges Outlet.'));
            }
            $id = self::text($row['id'] ?? '', 24);
            if ($id === '') {
                do {
                    $id = bin2hex(random_bytes(12));
                } while (in_array($id, $knownIds, true) || in_array($id, $seenIds, true));
            } elseif (!in_array($id, $knownIds, true) || in_array($id, $seenIds, true)) {
                throw new InvalidArgumentException(lang('Unknown or duplicate outlet ID. Reload the settings.', 'Unbekannte oder doppelte Outlet-Kennung. Bitte lade die Einstellungen neu.'));
            }
            $name = self::localizedText($row['name'] ?? [], 120);
            if ($name['en'] === '') {
                throw new InvalidArgumentException(lang('Please enter a name for every outlet.', 'Bitte gib für jedes Outlet einen Namen ein.'));
            }
            $fields = $row['fields'] ?? ['title', 'content'];
            if (!is_array($fields) || !$fields) {
                throw new InvalidArgumentException(lang('Select at least one text field for every outlet.', 'Wähle für jedes Outlet mindestens ein Textfeld aus.'));
            }
            foreach ($fields as $field) {
                if (!is_string($field) || !in_array($field, self::TEXT_FIELDS, true)) {
                    throw new InvalidArgumentException(lang('Invalid outlet text field.', 'Ungültiges Outlet-Textfeld.'));
                }
            }
            $seenIds[] = $id;
            $outlets[] = [
                'id' => $id,
                'name' => $name,
                'description' => self::localizedText($row['description'] ?? [], 500),
                'fields' => self::fields(['fields' => $fields]),
                'active' => self::flag($row['active'] ?? false),
                'portfolio' => self::flag($row['portfolio'] ?? false),
                'order' => count($outlets),
            ];
        }
        if (array_diff($knownIds, $seenIds)) {
            throw new InvalidArgumentException(lang('Saved outlets cannot be deleted. Deactivate them instead.', 'Gespeicherte Outlets können nicht gelöscht werden. Bitte deaktiviere sie stattdessen.'));
        }
        return [
            'original_portfolio' => self::flag($input['original_portfolio'] ?? false),
            'outlets' => $outlets,
        ];
    }
}
