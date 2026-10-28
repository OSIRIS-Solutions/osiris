<?php
include_once __DIR__ . '/DB.php';
include_once __DIR__ . '/Modules.php';

final class FormBuilder
{
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
        public readonly array $mandatory = [],
        private readonly bool $showCustomFields = true
    ) {

        if ($this->entity === 'activities') {
            $this->canBeCopied = true;
        }

        $DB = new DB();
        $osiris = $DB->db;

        $Modules = new Modules();
        foreach ($Modules->all_modules as $key => $value) {
            if ($value['show'] ?? true === false) {
                continue;
            }
            if (!in_array($this->entity, $value['entities'] ?? [])) {
                continue;
            }
            $this->modules[$key] = [
                'id' => $key,
                'name' => $value['name'] ?? $key,
                'name_de' => $value['name_de'] ?? null,
                'label' => $value['label'] ?? $value['name'] ?? $key,
                'label_de' => $value['label_de'] ?? null,
                'width' => $value['width'] ?? '12',
                'fields' => array_keys($value['fields'] ?? []),
                'type' => 'field',
                'portfolio' => $value['portfolio'] ?? false,
                'tags' => implode(',', $value['tags'] ?? []),
                'mandatory' => in_array($key, $this->mandatory),
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
                    'mandatory' => false,
                ];
            }
        }
    }
}
