<?php

/** Keep the existing project membership schema separate from activity persons. */
final class ProjectPersons
{
    public static function normalize($input, $DB): array
    {
        if (!is_array($input) && !($input instanceof Traversable)) return [];
        $persons = [];
        foreach ($input as $entry) {
            $entry = DB::doc2Arr($entry);
            if (!is_array($entry)) continue;
            $user = is_string($entry['user'] ?? null) ? trim($entry['user']) : '';
            if ($user === '') continue;
            $account = $DB->getPerson($user);
            $units = DB::doc2Arr($entry['units'] ?? []);
            if (!is_array($units)) $units = [];
            $person = [
                'user' => $user,
                'name' => !empty($account) ? trim(($account['first'] ?? '') . ' ' . ($account['last'] ?? '')) : (is_string($entry['name'] ?? null) ? $entry['name'] : $user),
                'role' => is_string($entry['role'] ?? null) ? $entry['role'] : 'applicant',
                'units' => array_values(array_unique(array_filter($units, fn($unit) => is_string($unit) && $unit !== ''))),
            ];
            foreach (['start', 'end'] as $key) {
                $value = $entry[$key] ?? '';
                $date = is_string($value) && $value !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $value) : false;
                $person[$key] = $date && $date->format('Y-m-d') === $value ? $value : null;
            }
            $persons[] = $person;
        }
        return $persons;
    }
}
