<?php

require_once __DIR__ . '/Language.php';

final class FormBuilder
{
    public const INITIATIVE_SYSTEM_FIELDS = [
        'name' => ['required' => true],
        'date-range-ongoing' => ['required' => true],
    ];

    public const PROJECT_SYSTEM_FIELDS = ['persons-project' => ['required' => true]];

    public readonly array $systemFields;

    public static array $tagLabels = [
        'publication' => ['en' => 'Publication', 'de' => 'Publikation'],
        'general' => ['en' => 'General', 'de' => 'Allgemein'],
        'authors' => ['en' => 'Users', 'de' => 'Nutzende'],
        'layout' => ['en' => 'Layout', 'de' => 'Layout'],
        'location' => ['en' => 'Location', 'de' => 'Ort'],
        'event' => ['en' => 'Event', 'de' => 'Ereignis'],
        'date' => ['en' => 'Date', 'de' => 'Datum'],
        'teaching' => ['en' => 'Teaching', 'de' => 'Lehre'],
        'organizations' => ['en' => 'Organisations', 'de' => 'Organisationen'],
        'people' => ['en' => 'People', 'de' => 'Personen'],
    ];

    public $modules = [];
    public $tags = [];
    public $canBeCopied = false;

    public function __construct(
        public readonly string $entityLabel,
        public readonly string $redirectUrl,
        public readonly string $entity = 'activities',
        public readonly string $backUrl = ROOTPATH . '/admin',
        ?array $systemFields = null,
        private readonly bool $showCustomFields = true
    ) {

        $this->systemFields = $systemFields
            ?? (in_array($this->entity, ['projects', 'proposals'], true) ? self::PROJECT_SYSTEM_FIELDS : []);

        if ($this->entity === 'activities') {
            $this->canBeCopied = true;
        }

        include_once __DIR__ . '/DB.php';
        include_once __DIR__ . '/Modules.php';

        $DB = new DB();
        $osiris = $DB->db;

        global $Translations;
        $Translations ??= new Language(BASEPATH . '/lang');
        $Modules = new Modules();
        foreach ($Modules->all_modules as $key => $value) {
            if (($value['show'] ?? true) === false) {
                continue;
            }
            if (!in_array($this->entity, $value['entities'] ?? [])) {
                continue;
            }
            $this->modules[$key] = [
                'id' => $key,
                'name' => $value['name'] ?? $key,
                'name_de' => $value['name_de'] ?? null,
                'label' => translate($value['label']),
                'label_en' => $Translations->resolve($value['label'], 'en', 'en'),
                'label_de' => $Translations->resolve($value['label'], 'de', 'en'),
                'width' => $value['width'] ?? '12',
                'fields' => array_keys($value['fields'] ?? []),
                'type' => 'field',
                'portfolio' => $value['portfolio'] ?? false,
                'tags' => implode(',', $value['tags'] ?? []),
            ];
            foreach ($value['tags'] ?? [] as $tag) {
                if (!in_array($tag, $this->tags)) {
                    $this->tags[] = $tag;
                }
            }
        }
        // remove important tag from tags
        if (($key = array_search('important', $this->tags)) !== false) {
            unset($this->tags[$key]);
        }

        if ($showCustomFields) {
            require_once __DIR__ . '/CustomFields.php';
            $custom_fields = $osiris->adminFields->find()->toArray();
            foreach ($custom_fields as $f) {
                $this->modules[$f['id']] = [
                    'id' => $f['id'],
                    'name' => $f['name'],
                    'name_de' => $f['name_de'] ?? null,
                    'label' => $f['name'] ?? $f['id'],
                    'label_de' => $f['name_de'] ?? null,
                    'width' => $f['width'] ?? '12',
                    'fields' => [$f['id']],
                    'type' => 'custom',
                    'localized' => CustomFields::isLocalized($f),
                ];
            }
        }
        foreach ($this->systemFields as $id => $rules) {
            if (!isset($this->modules[$id]) || $this->modules[$id]['type'] !== ($rules['type'] ?? 'field')) {
                throw new InvalidArgumentException("Unknown system field: $id");
            }
        }
    }


    /** Localized modules can satisfy the same system-field requirement as their plain variant. */
    public static function systemFieldsFor(array $fields, array $systemFields): array
    {
        $ids = array_column(array_filter($fields, fn($field) => ($field['type'] ?? '') === 'field'), 'id');
        foreach ($systemFields as $id => $rules) {
            if (in_array($id . '-loc', $ids, true)) {
                unset($systemFields[$id]);
                $systemFields[$id . '-loc'] = $rules;
            }
        }
        return $systemFields;
    }

    /** Add missing system fields without changing the positions of existing fields. */
    public static function prepareFields(array $fields, array $systemFields): array
    {
        $systemFields = self::systemFieldsFor($fields, $systemFields);
        $missing = $systemFields;
        foreach ($fields as &$field) {
            $id = $field['id'] ?? '';
            if (!isset($systemFields[$id]) || ($field['type'] ?? '') !== ($systemFields[$id]['type'] ?? 'field')) {
                continue;
            }
            unset($missing[$id]);
            // System fields must remain visible to everyone filling in the form.
            unset($field['props']['roles']);
            if ($systemFields[$id]['required'] ?? false) {
                $field['props']['required'] = true;
                $field['props']['recommended'] = false;
            }
        }
        unset($field);

        $added = [];
        foreach ($missing as $id => $rules) {
            $added[] = ['id' => $id, 'type' => $rules['type'] ?? 'field', 'props' => [
                'required' => $rules['required'] ?? false,
            ]];
        }
        return array_merge($added, $fields);
    }

    public static function validateFields(array $fields, array $systemFields = []): void
    {
        self::validateSystemFields($fields, $systemFields);
        $ids = array_column(array_filter($fields, fn($field) => ($field['type'] ?? '') === 'field'), 'id');
        foreach ($ids as $id) {
            if (str_ends_with($id, '-loc') && in_array(substr($id, 0, -4), $ids, true)) {
                throw new InvalidArgumentException("Use either $id or its plain variant, since both store the same field.");
            }
        }
        $personFields = array_filter($fields, fn($field) => ($field['type'] ?? '') === 'field'
            && in_array($field['id'] ?? '', ['persons', 'persons-role'], true));
        if (count($personFields) > 1) {
            throw new InvalidArgumentException('Use either persons or persons-role, since both store the same persons.');
        }
    }

    /** Check submitted schemas against trusted configuration, never client-side flags. */
    public static function validateSystemFields(array $fields, array $systemFields): void
    {
        $systemFields = self::systemFieldsFor($fields, $systemFields);
        foreach ($systemFields as $id => $rules) {
            $matches = array_values(array_filter($fields, fn($field) => ($field['id'] ?? '') === $id));
            if (count($matches) !== 1
                || ($matches[0]['type'] ?? '') !== ($rules['type'] ?? 'field')
                || !empty($matches[0]['props']['roles'])
                || (($rules['required'] ?? false) && (
                    ($matches[0]['props']['required'] ?? false) !== true
                    || !empty($matches[0]['props']['recommended'])
                ))) {
                throw new InvalidArgumentException("Invalid or missing system field: $id");
            }
        }
    }
}
