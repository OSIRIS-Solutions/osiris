<?php

/** One-way conversion of legacy phase configuration; does not change project data. */
final class ProjectFormMigration
{
    // The order and sections come from the legacy pages/proposals/edit.php form,
    // rather than the differently ordered field catalogue or checkbox submission.
    private const SECTIONS = [
        ['projects.submission', ['submission_date']],
        ['common.approval', ['approval_date']],
        ['common.rejection', ['rejection_date']],
        [null, ['comment']],
        ['projects.general_information', ['acronym', 'name', 'name_de', 'title', 'title_de', 'start_proposed', 'end_proposed', 'start', 'end', 'purpose', 'internal_number']],
        ['common.persons', ['applicants', 'scholar', 'supervisor']],
        ['projects.scholarship', ['scholarship', 'university']],
        ['projects.funding', ['funder', 'funding_organization', 'funding_program_select', 'funding_program', 'funding_number', 'joint_project', 'role', 'coordinator', 'funding_type', 'project_type']],
        ['projects.grant_sum', ['grant_sum_proposed', 'grant_income_proposed', 'grant_subproject_proposed', 'grant_sum', 'grant_income', 'grant_subproject']],
        ['projects.project_description', ['public', 'abstract', 'abstract_de', 'website']],
        [null, ['tags', 'kdsf-ffk']],
        ['projects.countries_of_research', ['countries']],
        ['projects.countries_you_will_do_research_on_in', ['research-countries']],
        [null, ['topics']],
        ['projects.institutional_fields', []],
        ['common.nagoya_protocol', ['nagoya']],
        ['projects.resources_and_personnel', ['personnel', 'in-kind', 'ressources']],
    ];

    public function __construct(private array $definitions, private Language $language) {}

    public function fields(string $phase, $modules): array
    {
        $selected = [];
        foreach ($modules as $module) {
            if ($module instanceof Traversable) $module = iterator_to_array($module);
            if (is_string($module)) $module = ['module' => rtrim($module, '*'), 'required' => str_ends_with($module, '*')];
            $id = $module['module'] ?? '';
            if ($id === '') continue;
            $selected[$id] = ($selected[$id] ?? false) || !empty($module['required']);
        }
        foreach ($this->definitions as $id => $definition) {
            if (!empty($definition['scope'][$phase])) $selected[$id] = true;
        }

        // These are hidden workflow values, not configurable form inputs.
        // Type and status continue to come from the project type and phase.
        unset($selected['type'], $selected['status']);

        $fields = [];
        $emitted = [];
        $append = function (string $id) use (&$fields, &$emitted, $selected): void {
            if (!array_key_exists($id, $selected)) return;
            $base = str_ends_with($id, '_de') ? substr($id, 0, -3) : $id;
            $localized = in_array($base, ['name', 'title', 'abstract'], true)
                && array_key_exists($base . '_de', $selected);
            $target = $localized ? $base . '-loc' : $id;
            if (isset($emitted[$target])) return;
            $definition = $this->definitions[$localized ? $base : $id] ?? [];
            $props = [
                // A formerly required German translation becomes optional.
                'required' => $selected[$localized ? $base : $id] ?? false,
                'label' => $definition['en'] ?? $base,
                'label_de' => $definition['de'] ?? $definition['en'] ?? $base,
            ];
            $fields[] = ['id' => $target, 'type' => !empty($definition['custom']) ? 'custom' : 'field', 'props' => $props];
            $emitted[$target] = true;
        };

        foreach (self::SECTIONS as [$heading, $ids]) {
            if ($heading === 'projects.institutional_fields') {
                $ids = array_keys(array_filter($this->definitions, fn($definition) => !empty($definition['custom'])));
            }
            $start = count($fields);
            foreach ($ids as $id) $append($id);
            if ($heading !== null && count($fields) > $start) {
                $en = $this->language->resolve($heading, 'en', 'en');
                $de = $this->language->resolve($heading, 'de', 'en');
                if ($heading === 'projects.grant_sum') {
                    $en .= ' in Euro';
                    $de .= ' in Euro';
                }
                array_splice($fields, $start, 0, [['type' => 'heading', 'props' => ['text' => $en, 'text_de' => $de]]]);
            }
        }
        // Preserve unrecognized/retired fields as well, so no configuration is
        // silently lost while the shared project module catalogue is built.
        foreach (array_keys($selected) as $id) $append($id);
        return $fields;
    }
}
