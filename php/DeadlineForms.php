<?php

require_once __DIR__ . '/FormBuilder.php';
require_once __DIR__ . '/../setup/defaults.php';
require_once __DIR__ . '/Initiatives.php';

/** One form definition for deadline editing and details. */
final class DeadlineForms
{
    public const SYSTEM_FIELDS = [
        'deadline-title' => ['required' => true],
        'deadline-date' => ['required' => true],
        'deadline-type' => ['required' => true],
    ];

    private const TEXT_FIELDS = ['title', 'type', 'url', 'description'];
    private const PROTECTED_FIELDS = ['_id', 'id', 'roles', 'created', 'created_by', 'updated', 'updated_by'];

    public static function defaults(): array
    {
        return FormBuilder::prepareFields(setupDefinition('deadlines')['fields'], self::SYSTEM_FIELDS);
    }

    public static function builder(): FormBuilder
    {
        return new FormBuilder(
            entityLabel: translate('common.deadlines'),
            redirectUrl: ROOTPATH . '/crud/admin/deadlinesFields',
            entity: 'deadlines',
            backUrl: ROOTPATH . '/admin/data-model#deadlines',
            systemFields: self::SYSTEM_FIELDS
        );
    }

    public static function fields($database): array
    {
        $definition = $database->adminGeneral->findOne(['id' => 'deadline-fields']);
        return FormBuilder::prepareFields(DB::doc2Arr($definition['fields'] ?? self::defaults()), self::SYSTEM_FIELDS);
    }

    public static function visibleFields($database, $settings): array
    {
        $user = $database->persons->findOne(['username' => $_SESSION['username']]);
        $fields = Initiatives::visibleFields(self::fields($database), DB::doc2Arr($user['roles'] ?? []));
        return array_values(array_filter($fields, function ($field) use ($settings) {
            $id = $field['id'] ?? '';
            if (in_array($id, ['initiatives', 'objectives'], true) && !$settings->featureEnabled('initiatives')) return false;
            return true;
        }));
    }

    public static function render($database, $settings, array $values): void
    {
        require_once __DIR__ . '/Modules.php';
        $fields = self::visibleFields($database, $settings);
        $modules = new Modules($values);
        $modules->setFields($fields);
        $modules->render_form($fields);
    }

    public static function values(array $input, $database, $settings, array $previous = []): array
    {
        $config = self::builder();
        $keys = [];
        $fields = self::visibleFields($database, $settings);
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
        $date = Initiatives::dateInput($values['date'] ?? null);
        if ($date === null) throw new InvalidArgumentException(translate('initiatives.invalid_date'));
        $vocabulary = new Vocabulary();
        $knownType = in_array($values['type'] ?? '', array_column($vocabulary->getValues('deadline-type'), 'id'), true);
        if (!$knownType && ($values['type'] ?? '') !== ($previous['type'] ?? null)) {
            throw new InvalidArgumentException(lang('Invalid deadline type.', 'Ungültiger Termintyp.'));
        }
        // Keep the existing date and text keys; normalize custom fields and associations separately.
        $plain = array_intersect_key($values, array_flip(self::TEXT_FIELDS));
        $values = validateValues(array_diff_key($values, $plain, ['date' => true]), new DB(), 'deadlines');
        $roles = $input['roles'] ?? [];
        if (!is_array($roles)) throw new InvalidArgumentException(translate('error.no_values'));
        $definition = $database->adminGeneral->findOne(['key' => 'roles']);
        $allowedRoles = array_merge(DB::doc2Arr($definition['value'] ?? ['user', 'scientist', 'admin']), DB::doc2Arr($previous['roles'] ?? []));
        $roles = array_values(array_intersect(array_keys($roles), $allowedRoles));
        return array_replace($values, $plain, [
            'date' => $date,
            'year' => (int) substr($date, 0, 4),
            'month' => (int) substr($date, 5, 2),
            'day' => (int) substr($date, 8, 2),
            'quarter' => (int) ceil((int) substr($date, 5, 2) / 3),
            'roles' => $roles ?: ['user'],
        ]);
    }

    public static function redisplay(array $input, array $previous = []): array
    {
        $selectedRoles = is_array($input['roles'] ?? null) ? array_keys($input['roles']) : DB::doc2Arr($previous['roles'] ?? []);
        $input = array_diff_key($input, array_flip(self::PROTECTED_FIELDS));
        $input['roles'] = $selectedRoles;
        foreach (array_merge(self::TEXT_FIELDS, ['date']) as $key) {
            if (isset($input[$key]) && !is_string($input[$key])) unset($input[$key]);
        }
        return array_replace($previous, $input);
    }

}
