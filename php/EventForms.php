<?php

require_once __DIR__ . '/FormBuilder.php';
require_once __DIR__ . '/Initiatives.php';

/** One form definition for event editing, quick creation and details. */
final class EventForms
{
    public const SYSTEM_FIELDS = [
        'event-title' => ['required' => true],
        'event-type' => ['required' => true],
        'date-range' => ['required' => true],
        'location' => ['required' => true],
    ];

    private const TEXT_FIELDS = ['title', 'title_full', 'type', 'location', 'country', 'url', 'description'];
    private const PROTECTED_FIELDS = ['_id', 'id', 'participants', 'interests', 'activities', 'created', 'created_by', 'updated', 'updated_by'];

    public static function defaults(): array
    {
        $fields = [];
        foreach (['event-title', 'title_full', 'event-type', 'event-description', 'date-range', 'location', 'country', 'url', 'topics', 'tags', 'event-public'] as $id) {
            $props = [];
            if (in_array($id, ['location', 'country'], true)) $props['width'] = 6;
            if ($id === 'date-range') $props['width'] = 12;
            $fields[] = ['id' => $id, 'type' => 'field', 'props' => $props];
        }
        return FormBuilder::prepareFields($fields, self::SYSTEM_FIELDS);
    }

    /** Rename the initial draft's module IDs without changing stored event fields or form properties. */
    public static function upgradeFields(array $fields): array
    {
        $aliases = ['title' => 'event-title', 'description' => 'event-description', 'public' => 'event-public'];
        foreach ($fields as &$field) {
            if (($field['type'] ?? 'field') !== 'field') continue;
            $field['id'] = $aliases[$field['id']] ?? $field['id'];
            unset($field['props']['event']);
        }
        unset($field);
        return $fields;
    }

    public static function builder(): FormBuilder
    {
        return new FormBuilder(
            entityLabel: translate('common.events'),
            redirectUrl: ROOTPATH . '/crud/admin/eventsFields',
            entity: 'events',
            backUrl: ROOTPATH . '/admin/data-model#events',
            systemFields: self::SYSTEM_FIELDS
        );
    }

    public static function fields($database): array
    {
        $definition = $database->adminGeneral->findOne(['id' => 'event-fields']);
        return FormBuilder::prepareFields(self::upgradeFields(DB::doc2Arr($definition['fields'] ?? self::defaults())), self::SYSTEM_FIELDS);
    }

    public static function visibleFields($database, $settings, bool $forInput = false): array
    {
        $user = $database->persons->findOne(['username' => $_SESSION['username']]);
        $fields = Initiatives::visibleFields(self::fields($database), DB::doc2Arr($user['roles'] ?? []));
        return array_values(array_filter($fields, function ($field) use ($settings, $forInput) {
            $id = $field['id'] ?? '';
            if (in_array($id, ['topics', 'tags'], true) && !$settings->featureEnabled($id)) return false;
            if (in_array($id, ['initiatives', 'objectives'], true) && !$settings->featureEnabled('initiatives')) return false;
            if ($id === 'event-public' && !$settings->featureEnabled('portal')) return false;
            return $id !== 'tags' || !$forInput || $settings->hasPermission('events.tags');
        }));
    }

    public static function render($database, $settings, array $values): void
    {
        require_once __DIR__ . '/Modules.php';
        $fields = self::visibleFields($database, $settings, true);
        $modules = new Modules($values);
        $modules->setFields($fields);
        $modules->render_form($fields);
    }

    public static function values(array $input, $database, $settings, array $previous = []): array
    {
        $config = self::builder();
        $keys = [];
        $fields = self::visibleFields($database, $settings, true);
        foreach ($fields as $field) {
            if (!in_array($field['type'] ?? 'field', ['field', 'custom'], true)) continue;
            $keys = array_merge($keys, $config->modules[$field['id']]['fields'] ?? []);
        }
        $keys = array_diff($keys, self::PROTECTED_FIELDS);
        $values = array_intersect_key($input, array_flip($keys));
        foreach ($fields as $field) {
            if (empty($field['props']['required'])) continue;
            $module = $config->modules[$field['id']] ?? [];
            $props = $field['props'] ?? [];
            $label = !empty($props['label']) || !empty($props['label_de'])
                ? lang(($props['label'] ?? '') ?: ($module['label'] ?? ''), $props['label_de'] ?? null)
                : ($module['label'] ?? $field['id']);
            foreach ($module['fields'] ?? [] as $key) {
                if ($key === 'end') continue;
                $value = $values[$key] ?? null;
                if (!empty($module['localized']) && is_array($value)) $value = $value[OSIRIS_BASE_LANGUAGE] ?? '';
                if ($value === null || $value === [] || (is_string($value) && trim(strip_tags($value)) === '')) {
                    throw new InvalidArgumentException(translate('initiatives.required_field') . ': ' . $label);
                }
            }
        }
        foreach (self::TEXT_FIELDS as $key) {
            if (isset($values[$key]) && !is_string($values[$key])) throw new InvalidArgumentException(translate('error.no_values'));
        }
        $start = Initiatives::dateInput($values['start'] ?? null);
        $end = Initiatives::dateInput($values['end'] ?? null) ?: $start;
        if ($start === null || $end < $start) throw new InvalidArgumentException(translate('initiatives.invalid_date_range'));
        $vocabulary = new Vocabulary();
        $knownType = in_array($values['type'] ?? '', array_column($vocabulary->getValues('event-type'), 'id'), true);
        if (!$knownType && ($values['type'] ?? '') !== ($previous['type'] ?? null)) {
            throw new InvalidArgumentException(translate('events.invalid_event_type'));
        }
        // Preserve legacy text and date storage; normalize new associations and custom fields.
        $plain = array_intersect_key($values, array_flip(self::TEXT_FIELDS));
        $values = validateValues(array_diff_key($values, $plain, ['start' => true, 'end' => true]), new DB(), 'conferences');
        return array_replace($values, $plain, [
            'start' => $start,
            'end' => $end,
            'year' => (int) substr($start, 0, 4),
            'month' => (int) substr($start, 5, 2),
            'day' => (int) substr($start, 8, 2),
            'quarter' => (int) ceil((int) substr($start, 5, 2) / 3),
        ]);
    }

    public static function redisplay(array $input, array $previous = []): array
    {
        $input = array_diff_key($input, array_flip(self::PROTECTED_FIELDS));
        foreach (array_merge(self::TEXT_FIELDS, ['start', 'end']) as $key) {
            if (isset($input[$key]) && !is_string($input[$key])) unset($input[$key]);
        }
        return array_replace($previous, $input);
    }

}
