<?php

require_once __DIR__ . '/FormBuilder.php';
require_once __DIR__ . '/../setup/defaults.php';

/** Shared rules for initiative forms and their simple objectives. */
final class Initiatives
{
    public static function fields($database): array
    {
        $definition = $database->adminGeneral->findOne(['id' => 'initiative-fields']);
        return FormBuilder::prepareFields(
            DB::doc2Arr($definition['fields'] ?? setupDefinition('initiatives')['fields']),
            FormBuilder::INITIATIVE_SYSTEM_FIELDS
        );
    }

    public static function visibleFields(array $fields, array $roles): array
    {
        return array_values(array_filter($fields, fn($field) =>
            empty($field['props']['roles']) || in_array($field['props']['roles'], $roles, true)
        ));
    }

    /** Prepare detail content using the same value formatter as activity views. */
    public static function details(array $initiative, array $fields): array
    {
        include_once __DIR__ . '/Modules.php';
        include_once __DIR__ . '/Document.php';

        $modules = new Modules($initiative);
        $modules->setFields($fields);
        $format = new Document(false, 'list');
        // This is entity data, so activity type/schema initialization is unnecessary.
        $format->doc = $initiative;
        $details = ['texts' => [], 'metadata' => []];
        foreach ($fields as $field) {
            if (!in_array($field['type'] ?? 'field', ['field', 'custom'], true)) continue;
            $id = $field['id'];
            if (in_array($id, ['name', 'name-loc'], true)) continue; // Already shown as the page title.
            $value = $format->get_field($id);
            if ($value === null || $value === '' || $value === '-') continue;

            $customFormat = $format->custom_fields[$id]['format'] ?? null;
            $longText = in_array($id, ['abstract', 'description', 'details', 'abstract-loc', 'description-loc'], true)
                || in_array($customFormat, ['text', 'text-format'], true);
            // The formatter returns HTML for links/rich text, but ordinary text is escaped.
            if (($customFormat !== null && !in_array($customFormat, ['url', 'text-format'], true))
                || in_array($id, ['subtitle', 'details', 'abstract'], true)) {
                $value = nl2br(e($value));
            }
            $details[$longText ? 'texts' : 'metadata'][] = [
                'label' => $modules->get_name($id),
                'value' => $value,
            ];
        }
        return $details;
    }

    /** Keys actually offered to this user by the form. */
    public static function editableValues(array $input, array $fields, array $modules): array
    {
        $keys = [];
        foreach ($fields as $field) {
            if (in_array($field['type'] ?? '', ['field', 'custom'], true)) {
                $keys = array_merge($keys, $modules[$field['id']]['fields'] ?? []);
            }
        }
        $reserved = ['_id', 'id', 'inactive', 'created', 'created_by', 'updated', 'updated_by', 'objectives'];
        $keys = array_diff($keys, $reserved);
        return array_replace(array_fill_keys($keys, null), array_intersect_key($input, array_flip($keys)));
    }

    /** Accept only fields offered by the current form, never system metadata. */
    public static function formValues(array $input, array $fields, array $modules): array
    {
        $values = self::editableValues($input, $fields, $modules);
        foreach ($fields as $field) {
            if (!in_array($field['type'] ?? '', ['field', 'custom'], true)) continue;
            $module = $modules[$field['id']] ?? null;
            if ($module === null) continue;
            foreach ($module['fields'] as $key) {
                if (!array_key_exists($key, $values)) continue;
                // In a date range only the start is required; an empty end means ongoing.
                $required = !empty($field['props']['required']) && $key !== 'end';
                $value = $values[$key];
                if ($required && (str_ends_with($field['id'], '-loc') || !empty($module['localized']))) {
                    $value = is_array($value) ? ($value[OSIRIS_BASE_LANGUAGE] ?? '') : $value;
                }
                if ($required && ($value === null || $value === [] || (is_string($value) && trim(strip_tags($value)) === ''))) {
                    throw new InvalidArgumentException(translate('initiatives.required_field') . ': ' . $key);
                }
            }
        }
        $name = $values['name'] ?? null;
        if (is_array($name)) $name = $name[OSIRIS_BASE_LANGUAGE] ?? '';
        if (!is_string($name) || trim($name) === '') {
            throw new InvalidArgumentException(translate('initiatives.name_required'));
        }
        if (is_string($values['name'])) $values['name'] = trim($values['name']);
        foreach (['start', 'end'] as $key) {
            if (array_key_exists($key, $values)) $values[$key] = self::dateInput($values[$key]);
        }
        if (!empty($values['start']) && !empty($values['end']) && $values['end'] < $values['start']) {
            throw new InvalidArgumentException(translate('initiatives.invalid_date_range'));
        }
        return $values;
    }

    public static function objectiveValues(array $input): array
    {
        foreach (['code', 'name', 'description', 'type', 'start_date', 'end_date'] as $field) {
            if (isset($input[$field]) && !is_string($input[$field])) {
                throw new InvalidArgumentException(translate('initiatives.invalid_objective_field') . ': ' . $field);
            }
        }
        if (!is_string($input['name'] ?? null) || trim($input['name']) === '') {
            throw new InvalidArgumentException(translate('initiatives.objective_title_required'));
        }
        require_once __DIR__ . '/Vocabulary.php';
        $type = trim($input['type'] ?? '') ?: 'objective';
        $vocabulary = new Vocabulary();
        if (!in_array($type, array_column($vocabulary->getValues('objective-type'), 'id'), true)) {
            throw new InvalidArgumentException(translate('initiatives.invalid_objective_field') . ': type');
        }
        $start = self::dateInput($input['start_date'] ?? null);
        $end = self::dateInput($input['end_date'] ?? null);
        if ($start !== null && $end !== null && $end < $start) {
            throw new InvalidArgumentException(translate('initiatives.invalid_date_range'));
        }
        return [
            'code' => trim($input['code'] ?? ''),
            // Keep the existing name key as the objective title for compatibility.
            'name' => trim($input['name']),
            'description' => trim($input['description'] ?? ''),
            'type' => $type,
            'start_date' => $start,
            'end_date' => $end,
            'start' => $start === null ? null : valiDate($start),
            'end' => $end === null ? null : valiDate($end),
        ];
    }

    /** Retain only safe form inputs when redisplaying an invalid objective. */
    public static function objectiveDraft(array $input): array
    {
        return array_filter(array_intersect_key($input, array_flip([
            'code', 'name', 'description', 'type', 'start_date', 'end_date',
        ])), 'is_string');
    }

    public static function dateInput($value): ?string
    {
        if ($value === null || $value === '') return null;
        if (!is_string($value) || !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $value)) {
            throw new InvalidArgumentException(translate('initiatives.invalid_date'));
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if (!$date || $date->format('Y-m-d') !== $value || !checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4))) {
            throw new InvalidArgumentException(translate('initiatives.invalid_date'));
        }
        return $value;
    }

    /** Stable, unique database references submitted by association fields. */
    public static function normalizeLinks($values): array
    {
        if ($values instanceof Traversable) $values = iterator_to_array($values);
        if (!is_array($values)) $values = empty($values) ? [] : [$values];
        $links = [];
        foreach ($values as $value) {
            // Activity detail pages convert BSON references to Extended JSON.
            if (is_array($value)) $value = $value['$oid'] ?? null;
            if ($value instanceof MongoDB\BSON\ObjectId) $value = (string) $value;
            if (!is_string($value) || !DB::is_ObjectID($value)) continue;
            $id = DB::to_ObjectID($value);
            $links[(string) $id] = $id;
        }
        return array_values($links);
    }

    /** Rebuild initiative links from this submission, including the selected objectives' parents. */
    public static function resolveLinks(array $values, $database): array
    {
        if (!array_key_exists('objectives', $values)) return $values;
        $values['objectives'] = self::normalizeLinks($values['objectives']);
        $initiatives = self::normalizeLinks($values['initiatives'] ?? []);
        if ($values['objectives'] !== []) {
            foreach ($database->objectives->find(['_id' => ['$in' => $values['objectives']]], ['projection' => ['initiative' => 1]]) as $objective) {
                $initiatives[] = $objective['initiative'] ?? null;
            }
        }
        // Do not merge previously stored links: removing an objective must remove its implicit initiative.
        $values['initiatives'] = self::normalizeLinks($initiatives);
        return $values;
    }

    /** An objective is selectable only within its own and its initiative's period. */
    public static function selectionBounds($document, $parent = []): array
    {
        $start = max(valueFromDateArray($document['start_date'] ?? $document['start'] ?? null) ?? '', valueFromDateArray($parent['start_date'] ?? $parent['start'] ?? null) ?? '');
        $ends = array_filter([valueFromDateArray($document['end_date'] ?? $document['end'] ?? null), valueFromDateArray($parent['end_date'] ?? $parent['end'] ?? null)]);
        return ['start' => $start, 'end' => $ends ? min($ends) : '', 'archived' => !empty($document['inactive']) || !empty($parent['inactive'])];
    }

    /** Partial dates cover the known year/month; a range without an end uses its start. */
    public static function activityPeriod(array $input): ?array
    {
        $start = $input['start_date'] ?? $input['start'] ?? null;
        if (!empty($start)) {
            $start = valueFromDateArray($start);
            $end = valueFromDateArray($input['end_date'] ?? $input['end'] ?? null) ?: $start;
            try {
                if (self::dateInput($start) === null || self::dateInput($end) === null) return null;
            } catch (InvalidArgumentException $e) {
                return null;
            }
            return [$start, max($start, $end)];
        }
        $year = (int) ($input['year'] ?? 0);
        $month = (int) ($input['month'] ?? 0);
        $day = (int) ($input['day'] ?? 0);
        if ($year < 1900 || $year > 2155 || $month < 0 || $month > 12 || $day < 0 || $day > 31 || ($day && !$month)) return null;
        if ($day && !checkdate($month, $day, $year)) return null;
        $start = sprintf('%04d-%02d-%02d', $year, $month ?: 1, $day ?: 1);
        $end = $day ? $start : ($month ? date('Y-m-t', strtotime($start)) : "$year-12-31");
        return [$start, $end];
    }

    public static function matchesPeriod(array $bounds, ?array $period): bool
    {
        return $period !== null && !$bounds['archived']
            && (!$bounds['start'] || !$bounds['end'] || $bounds['start'] <= $bounds['end'])
            && (!$bounds['start'] || $bounds['start'] <= $period[1])
            && (!$bounds['end'] || $bounds['end'] >= $period[0]);
    }

    /** Existing links remain valid; new out-of-period links require the additional right. */
    public static function validateActivitySelection(array $input, $previous, $database, $settings): void
    {
        if ($settings->hasPermission('initiatives.select-outside-period')) return;
        $activity = array_replace(DB::doc2Arr($previous), $input);
        // Cached dates must not override a newly entered year or date range.
        if (array_key_exists('year', $input) && !array_key_exists('start', $input)) {
            unset($activity['start'], $activity['end'], $activity['start_date'], $activity['end_date']);
        } elseif (array_key_exists('start', $input)) {
            unset($activity['start_date'], $activity['end_date']);
        }
        $period = self::activityPeriod($activity);
        foreach (['initiatives', 'objectives'] as $field) {
            if (!array_key_exists($field, $input)) continue;
            $old = array_map('strval', self::normalizeLinks($previous[$field] ?? []));
            $new = array_filter(self::normalizeLinks($input[$field]), fn($id) => !in_array((string) $id, $old, true));
            foreach ($new as $id) {
                $document = $database->$field->findOne(['_id' => $id]);
                $parent = $field === 'objectives' && $document ? $database->initiatives->findOne(['_id' => $document['initiative']]) : [];
                if (!$document || ($field === 'objectives' && !$parent) || !self::matchesPeriod(self::selectionBounds($document, $parent), $period)) {
                    abortwith(403, translate('initiatives.selection_outside_period'));
                }
            }
        }
    }

    public static function period($document): string
    {
        $start = $document['start_date'] ?? $document['start'] ?? null;
        $end = $document['end_date'] ?? $document['end'] ?? null;
        if (empty($start)) {
            return empty($end) ? translate('common.open_end') : translate('initiatives.deadline') . ': ' . format_date($end);
        }
        return format_date($start) . ' – ' . (empty($end) ? translate('common.open_end') : format_date($end));
    }

    public static function isExpired($document, ?string $today = null): bool
    {
        $end = $document['end_date'] ?? null;
        if (empty($end) && !empty($document['end']['year'])) {
            $end = sprintf('%04d-%02d-%02d', $document['end']['year'], $document['end']['month'] ?? 12, $document['end']['day'] ?? 31);
        }
        return !empty($end) && $end < ($today ?? date('Y-m-d'));
    }

    public static function canView($settings): bool
    {
        return $settings->hasPermission('initiatives.view') || $settings->hasPermission('initiatives.edit');
    }

    public static function requireAccess(bool $edit = false): void
    {
        global $Settings;
        if (!$Settings->featureEnabled('initiatives')) {
            http_response_code(404);
            abortwith(404, translate('common.initiatives'), '/');
        }
        if ($edit ? !$Settings->hasPermission('initiatives.edit') : !self::canView($Settings)) {
            http_response_code(403);
            abortwith(403, translate('initiatives.no_permission'), '/initiatives');
        }
    }

    public static function find($database, string $id)
    {
        $initiative = DB::is_ObjectID($id) ? $database->initiatives->findOne(['_id' => DB::to_ObjectID($id)]) : null;
        if (empty($initiative)) {
            http_response_code(404);
            abortwith(404, translate('common.initiative'), '/initiatives');
        }
        return $initiative;
    }

    /** Plain-text fallback for scalar, localized and compound field values. */
    public static function text($value): string
    {
        if ($value === null) return '';
        if (is_bool($value)) return translate($value ? 'common.yes' : 'common.no');
        if ($value instanceof Traversable) $value = iterator_to_array($value);
        if (is_array($value)) {
            if (isset($value['year'])) return sprintf('%04d-%02d-%02d', $value['year'], $value['month'] ?? 1, $value['day'] ?? 1);
            if (isset($value['en']) || isset($value['de'])) return strip_tags(localized($value));
            return implode(', ', array_filter(array_map([self::class, 'text'], $value), fn($item) => $item !== ''));
        }
        return strip_tags((string) $value);
    }
}
