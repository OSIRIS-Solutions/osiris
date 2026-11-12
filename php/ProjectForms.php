<?php

require_once __DIR__ . '/FormBuilder.php';
require_once __DIR__ . '/ProjectFormMigration.php';
require_once __DIR__ . '/Language.php';

/** Admin form configuration. Draft is preparatory and does not enable a lifecycle state. */
final class ProjectForms
{
    private const PHASES = [
        'draft' => ['name' => 'Draft', 'name_de' => 'Entwurf', 'color' => 'muted', 'planned' => true,
            'description' => 'Prepare the form for the future internal approval process. This phase is not yet available when entering proposals or projects.',
            'description_de' => 'Bereite das Formular für den künftigen internen Freigabeprozess vor. Diese Phase ist bei der Erfassung von Anträgen oder Projekten noch nicht verfügbar.'],
        'proposed' => ['name' => 'Proposed', 'name_de' => 'Beantragt', 'color' => 'signal',
            'description' => 'Fields for submitted project proposals.', 'description_de' => 'Felder für eingereichte Projektanträge.'],
        'approved' => ['name' => 'Approved', 'name_de' => 'Bewilligt', 'color' => 'success',
            'description' => 'Fields for the approval and the approved project dates and funding.', 'description_de' => 'Felder für die Bewilligung sowie die bewilligte Laufzeit und Förderung.'],
        'rejected' => ['name' => 'Rejected', 'name_de' => 'Abgelehnt', 'color' => 'danger',
            'description' => 'Fields for rejected project proposals.', 'description_de' => 'Felder für abgelehnte Projektanträge.'],
        'project' => ['name' => 'Project', 'name_de' => 'Projekt', 'color' => 'primary',
            'description' => 'Fields for projects of this type.', 'description_de' => 'Felder für Projekte dieses Typs.'],
    ];

    // Everything not listed here (including custom fields) can be used in any phase.
    private const MODULE_PHASES = [
        'persons-project' => ['draft', 'proposed', 'approved', 'project'],
        'submission_date' => ['proposed'],
        'approval_date' => ['approved'],
        'rejection_date' => ['rejected'],
        'project-date-proposed' => ['draft', 'proposed'],
        'grant_sum_proposed' => ['draft', 'proposed'],
        'grant_income_proposed' => ['draft', 'proposed'],
        'grant_subproject_proposed' => ['draft', 'proposed'],
        'grant_sum' => ['approved', 'project'],
        'grant_income' => ['approved', 'project'],
        'grant_subproject' => ['approved', 'project'],
        'funding_number' => ['approved', 'project'],
        'project-date' => ['approved', 'project'],
        'personnel' => ['draft', 'proposed'],
        'in-kind' => ['draft', 'proposed'],
        'ressources' => ['draft', 'proposed'],
        'image' => ['project'],
        'public' => ['project'],
    ];

    public static function phases($projectType): array
    {
        $ids = ($projectType['process'] ?? 'proposal') === 'project'
            ? ['draft', 'project'] : ['draft', 'proposed', 'approved', 'rejected', 'project'];
        $phases = [];
        foreach ($ids as $id) $phases[$id] = ['id' => $id] + self::PHASES[$id];
        return $phases;
    }

    public static function phase($projectType, string $phase): array
    {
        return self::phases($projectType)[$phase] ?? throw new InvalidArgumentException('Unknown project phase.');
    }

    public static function systemFields(string $phase): array
    {
        $fields = match ($phase) {
            'draft' => ['name'],
            'proposed' => ['name', 'title', 'submission_date', 'project-date-proposed', 'funder'],
            'approved' => ['name', 'title', 'approval_date', 'project-date'],
            'rejected' => ['rejection_date'],
            'project' => ['name', 'title', 'project-date', 'funder'],
            default => throw new InvalidArgumentException('Unknown project phase.'),
        };
        return array_fill_keys($fields, ['required' => true]) + ($phase === 'rejected' ? [] : FormBuilder::PROJECT_SYSTEM_FIELDS);
    }

    public static function builder($projectType, string $phase): FormBuilder
    {
        $definition = self::phase($projectType, $phase);
        $name = lang($projectType['name'] ?? $projectType['id'], $projectType['name_de'] ?? null);
        $config = new FormBuilder(
            entityLabel: $name . ' — ' . lang($definition['name'], $definition['name_de']),
            redirectUrl: ROOTPATH . '/crud/admin/projects/fields/' . rawurlencode($projectType['id']) . '/' . $phase,
            entity: 'projects',
            backUrl: ROOTPATH . '/admin/projects/2/' . rawurlencode($projectType['id']),
            systemFields: self::systemFields($phase)
        );
        $config->modules = array_filter($config->modules, fn($module) => $module['type'] === 'custom'
            || !isset(self::MODULE_PHASES[$module['id']]) || in_array($phase, self::MODULE_PHASES[$module['id']], true));
        $tags = [];
        foreach ($config->modules as $module) $tags = array_merge($tags, array_filter(explode(',', $module['tags'] ?? '')));
        $config->tags = array_values(array_unique($tags));
        return $config;
    }

    public static function arrays($value)
    {
        if ($value instanceof Traversable) $value = iterator_to_array($value);
        return is_array($value) ? array_map([self::class, 'arrays'], $value) : $value;
    }

    public static function fields($projectType, string $phase, array $legacyDefinitions): array
    {
        self::phase($projectType, $phase);
        $stored = null;
        foreach ($projectType['phases'] ?? [] as $candidate) {
            if ($candidate['id'] === $phase) { $stored = self::arrays($candidate); break; }
        }
        $language = new Language(BASEPATH . '/lang');
        $migration = new ProjectFormMigration($legacyDefinitions, $language);
        $fields = isset($stored['fields']) ? $migration->upgradeFields($stored['fields'], $phase)
            : ($phase === 'draft' ? [] : $migration->fields($phase, $stored['modules'] ?? []));
        return FormBuilder::prepareFields($fields, self::systemFields($phase));
    }

    public static function unavailable(array $fields, FormBuilder $config): array
    {
        $ids = [];
        foreach ($fields as $field) {
            if (in_array($field['type'] ?? 'field', ['field', 'custom'], true)
                && !isset($config->modules[$field['id'] ?? ''])) $ids[] = $field['id'] ?? '';
        }
        return array_values(array_unique($ids));
    }

    public static function validate(array $fields, FormBuilder $config): void
    {
        if (!array_is_list($fields)) throw new InvalidArgumentException('Invalid form schema.');
        $seen = [];
        foreach ($fields as $field) {
            if (!is_array($field) || !is_string($field['type'] ?? null)
                || (isset($field['props']) && !is_array($field['props']))) throw new InvalidArgumentException('Invalid form element.');
            if (in_array($field['type'], ['heading', 'paragraph', 'hr'], true)) continue;
            $id = $field['id'] ?? null;
            if (!is_string($id) || !isset($config->modules[$id]) || $config->modules[$id]['type'] !== $field['type']) {
                throw new InvalidArgumentException('Field not available in this phase: ' . (is_string($id) ? $id : ''));
            }
            if (isset($seen[$id])) throw new InvalidArgumentException('Duplicate field: ' . $id);
            $seen[$id] = true;
        }
        FormBuilder::validateFields($fields, $config->systemFields);
    }

    public static function save($collection, $projectType, string $phase, array $fields): void
    {
        self::validate($fields, self::builder($projectType, $phase));
        $set = ['updated' => date('Y-m-d'), 'updated_by' => $_SESSION['username'], 'stage' => max(2, (int) ($projectType['stage'] ?? 1))];
        foreach ($projectType['phases'] ?? [] as $index => $existing) {
            if ($existing['id'] !== $phase) continue;
            $set['phases.' . $index . '.fields'] = $fields;
            $collection->updateOne(['_id' => $projectType['_id']], ['$set' => $set]);
            return;
        }
        $collection->updateOne(['_id' => $projectType['_id']], [
            '$set' => $set,
            '$push' => ['phases' => self::phase($projectType, $phase) + ['fields' => $fields]],
        ]);
    }
}
