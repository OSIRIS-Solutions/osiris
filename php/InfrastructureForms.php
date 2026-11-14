<?php

require_once __DIR__ . '/FormBuilder.php';
require_once __DIR__ . '/Initiatives.php';
require_once __DIR__ . '/LocalizedFields.php';
require_once __DIR__ . '/../setup/defaults.php';

/** Infrastructure metadata forms; persons and statistics keep their own editors. */
final class InfrastructureForms
{
    public const SYSTEM_FIELDS = ['infrastructure-id' => ['required' => true], 'name-loc' => ['required' => true], 'infrastructure-date' => ['required' => true]];
    private const LEGACY_MODULES = ['description' => 'description-loc', 'type' => 'infrastructure-category', 'infrastructure_type' => 'infrastructure-type', 'access' => 'infrastructure-access', 'link' => 'infrastructure-link', 'contact_email' => 'contact-email', 'topics' => 'topics', 'collaborative' => 'infrastructure-collaborative'];

    public static function builder(): FormBuilder
    {
        return new FormBuilder(entityLabel: translate('common.infrastructures'), redirectUrl: ROOTPATH . '/crud/admin/infrastructuresFields', entity: 'infrastructures', backUrl: ROOTPATH . '/admin/infrastructures', systemFields: self::SYSTEM_FIELDS);
    }

    public static function defaults(): array
    {
        return FormBuilder::prepareFields(setupDefinition('infrastructures')['fields'], self::SYSTEM_FIELDS);
    }

    /** Preserve the legacy editor's order, optional fields and custom-field selection. */
    public static function legacyFields($database): array
    {
        $legacy = $database->adminGeneral->findOne(['key' => 'infrastructure-data']);
        if ($legacy === null) return self::defaults();
        $selected = DB::doc2Arr($legacy['value'] ?? []);
        $fields = [];
        foreach (self::defaults() as $field) {
            $id = $field['id'];
            if (isset(self::SYSTEM_FIELDS[$id]) || $id === 'subtitle-loc') $fields[] = $field;
            elseif (in_array(array_search($id, self::LEGACY_MODULES, true), $selected, true)) $fields[] = $field;
        }
        $custom = [];
        foreach ($database->adminFields->find() as $field) {
            if (in_array($field['id'], $selected, true)) $custom[] = ['id' => $field['id'], 'type' => 'custom', 'props' => []];
        }
        $insert = count($fields);
        foreach ($fields as $i => $field) if (in_array($field['id'], ['topics', 'infrastructure-collaborative'], true)) { $insert = $i; break; }
        array_splice($fields, $insert, 0, $custom);
        return $fields;
    }

    public static function fields($database): array
    {
        $definition = $database->adminGeneral->findOne(['id' => 'infrastructure-fields']);
        return FormBuilder::prepareFields(DB::doc2Arr($definition['fields'] ?? self::legacyFields($database)), self::SYSTEM_FIELDS);
    }

    public static function visibleFields($database, $settings): array
    {
        $user = $database->persons->findOne(['username' => $_SESSION['username']]);
        $fields = Initiatives::visibleFields(self::fields($database), DB::doc2Arr($user['roles'] ?? []));
        return array_values(array_filter($fields, function ($field) use ($settings) {
            if (($field['id'] ?? '') === 'topics') return $settings->featureEnabled('topics');
            if (in_array($field['id'] ?? '', ['initiatives', 'objectives'], true)) return $settings->featureEnabled('initiatives');
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
        $fields = self::visibleFields($database, $settings);
        $keys = [];
        foreach ($fields as $field) {
            if (in_array($field['type'] ?? '', ['field', 'custom'], true)) $keys = array_merge($keys, $config->modules[$field['id']]['fields'] ?? []);
        }
        $keys = array_diff($keys, ['_id', 'persons', 'created', 'created_by', 'updated', 'updated_by', 'history', 'name_de', 'subtitle_de', 'description_de']);
        $values = array_intersect_key($input, array_flip($keys));
        // Infrastructure identifiers remain immutable after creation.
        $values['id'] = $previous['id'] ?? ($values['id'] ?? '');
        foreach (['name', 'subtitle', 'description'] as $key) {
            if (!array_key_exists($key, $values)) continue;
            $value = $values[$key];
            if (!is_array($value) && !is_string($value) && $value !== null) throw new InvalidArgumentException(translate('error.no_values'));
            $values[$key] = normalizeLocalizedValues(is_array($value) ? $value : ['en' => $value ?? ''], false, $key === 'description');
        }
        foreach ($fields as $field) {
            if (empty($field['props']['required']) || !isset($config->modules[$field['id'] ?? ''])) continue;
            foreach ($config->modules[$field['id']]['fields'] as $key) {
                if ($key === 'end_date' || ($field['id'] === 'infrastructure-collaborative' && $key !== 'collaborative')) continue;
                $value = $values[$key] ?? null;
                if (is_array($value) && (str_ends_with($field['id'], '-loc') || !empty($config->modules[$field['id']]['localized']))) $value = $value[OSIRIS_BASE_LANGUAGE] ?? '';
                if ($value === null || $value === [] || (is_string($value) && trim(strip_tags($value)) === '')) throw new InvalidArgumentException(translate('initiatives.required_field') . ': ' . $config->modules[$field['id']]['label']);
            }
        }
        foreach (['id', 'link', 'contact_email', 'type', 'infrastructure_type', 'access'] as $key) {
            if (isset($values[$key]) && !is_string($values[$key])) throw new InvalidArgumentException(translate('error.no_values'));
        }
        $start = Initiatives::dateInput($values['start_date'] ?? null);
        $end = Initiatives::dateInput($values['end_date'] ?? null);
        if ($start === null || ($end !== null && $end < $start)) throw new InvalidArgumentException(translate('initiatives.invalid_date_range'));
        $vocabulary = new Vocabulary();
        foreach (['type' => 'infrastructure-category', 'infrastructure_type' => 'infrastructure-type', 'access' => 'infrastructure-access'] as $key => $vocab) {
            if (!isset($values[$key]) || $values[$key] === '') continue;
            if (!in_array($values[$key], array_column($vocabulary->getValues($vocab), 'id'), true) && $values[$key] !== ($previous[$key] ?? null)) throw new InvalidArgumentException(translate('error.no_values'));
        }
        $plainKeys = ['id', 'name', 'subtitle', 'description', 'link', 'contact_email', 'type', 'infrastructure_type', 'access', 'start_date', 'end_date', 'collaborative', 'collaborators', 'coordinator'];
        $plain = array_intersect_key($values, array_flip($plainKeys));
        $values = array_replace(validateValues(array_diff_key($values, $plain), new DB(), 'infrastructures'), $plain);
        $values['start_date'] = $start;
        $values['end_date'] = $end;
        if (array_key_exists('collaborative', $values)) {
            $values['collaborative'] = in_array($values['collaborative'], [true, 'true', 'yes', '1'], true);
            $values['collaborators'] = Initiatives::normalizeLinks($values['collaborators'] ?? []);
            $coordinator = $values['coordinator'] ?? '';
            if (!is_string($coordinator)) throw new InvalidArgumentException(translate('error.no_values'));
            $organization = DB::is_ObjectID($coordinator) ? DB::to_ObjectID($coordinator) : null;
            if ($organization !== null && !in_array((string) $organization, array_map('strval', $values['collaborators']), true)) throw new InvalidArgumentException(translate('error.no_values'));
            $values['coordinator_organization'] = $organization;
            $values['coordinator_institute'] = $organization === null;
            unset($values['coordinator']);
        }
        // These operational settings deliberately remain outside the Form Builder.
        if (isset($input['statistic_frequency'])) {
            if (!in_array($input['statistic_frequency'], ['annual', 'quarterly', 'monthly', 'irregularly'], true)) throw new InvalidArgumentException(translate('error.no_values'));
            $values['statistic_frequency'] = $input['statistic_frequency'];
        }
        $stats = $input['statistic_fields'] ?? [];
        if (!is_array($stats) || count(array_filter($stats, 'is_string')) !== count($stats)) throw new InvalidArgumentException(translate('error.no_values'));
        $values['statistic_fields'] = array_values(array_unique($stats));
        if ($settings->featureEnabled('portal') && isset($input['public'])) $values['public'] = filter_var($input['public'], FILTER_VALIDATE_BOOLEAN);
        return $values;
    }
    public static function redisplay(array $input, array $previous = []): array
    {
        $input = array_diff_key($input, array_flip(['_id', 'persons', 'created', 'created_by', 'updated', 'updated_by', 'history']));
        foreach (['id', 'start_date', 'end_date', 'link', 'contact_email', 'type', 'infrastructure_type', 'access', 'coordinator', 'statistic_frequency'] as $key) {
            if (isset($input[$key]) && !is_string($input[$key])) unset($input[$key]);
        }
        foreach (['name', 'subtitle', 'description'] as $key) {
            if (!isset($input[$key])) continue;
            $value = $input[$key];
            $input[$key] = normalizeLocalizedValues(is_array($value) ? $value : ['en' => is_string($value) ? $value : ''], false, $key === 'description');
        }
        $input['collaborators'] = Initiatives::normalizeLinks($input['collaborators'] ?? []);
        if (isset($input['coordinator'])) {
            $input['coordinator_institute'] = $input['coordinator'] === '0';
            $input['coordinator_organization'] = DB::is_ObjectID($input['coordinator']) ? DB::to_ObjectID($input['coordinator']) : null;
        }
        if (array_key_exists('collaborative', $input)) $input['collaborative'] = in_array($input['collaborative'], [true, 'true', 'yes', '1'], true);
        if (array_key_exists('public', $input)) $input['public'] = filter_var($input['public'], FILTER_VALIDATE_BOOLEAN);
        return array_replace($previous, $input, isset($previous['id']) ? ['id' => $previous['id']] : []);
    }

}
