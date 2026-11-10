<?php

/** Normalize the shared persons/persons-role field without bibliographic author positions. */
final class PersonField
{
    public static function defaultRole(array $roles): string
    {
        $active = array_values(array_filter($roles, fn($role) => empty($role['inactive'])));
        return in_array('member', array_column($active, 'id'), true) ? 'member' : ($active[0]['id'] ?? '');
    }

    public static function normalize($input, $DB): array
    {
        if (!is_array($input) && !($input instanceof Traversable)) return [];
        $persons = [];
        foreach ($input as $entry) {
            $entry = DB::doc2Arr($entry);
            if (!is_array($entry)) continue;
            $user = is_string($entry['user'] ?? null) ? trim($entry['user']) : '';
            $account = $user !== '' ? $DB->getPerson($user) : [];
            $last = $account['last'] ?? $entry['last'] ?? '';
            $last = is_scalar($last) ? trim((string) $last) : '';
            $first = $account['first'] ?? $entry['first'] ?? '';
            $first = is_scalar($first) ? trim((string) $first) : '';
            if ($last === '' && $first === '' && !$account) continue;
            $person = [
                'user' => $account ? $user : null,
                'last' => $last,
                'first' => $first,
                'aoi' => filter_var($entry['aoi'] ?? (bool) $account, FILTER_VALIDATE_BOOLEAN),
                'approved' => $account && $user === ($_SESSION['username'] ?? null),
            ];
            if (is_string($entry['role'] ?? null) && $entry['role'] !== '') $person['role'] = $entry['role'];
            if (isset($entry['units']) && is_array($entry['units'])) {
                $person['units'] = array_values(array_filter($entry['units'], 'is_string'));
            }
            if (array_key_exists('manually', $entry)) {
                $person['manually'] = filter_var($entry['manually'], FILTER_VALIDATE_BOOLEAN);
            }
            $persons[] = $person;
        }
        return $persons;
    }
}
