<?php

require_once __DIR__ . '/FormBuilder.php';

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
        ['common.persons', ['persons-project']],
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
        unset($selected['type'], $selected['status'], $selected['applicants'], $selected['scholar'], $selected['supervisor']);
        $selected['persons-project'] = true;

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
        return $this->upgradeFields($fields);
    }

    /** Also update fields generated during the earlier transitional migration. */
    public function upgradeFields(array $fields): array
    {
        $result = [];
        $dates = [];
        $persons = null;
        foreach ($fields as $field) {
            $field = DB::doc2Arr($field);
            if (($field['type'] ?? 'field') !== 'field') { $result[] = $field; continue; }
            $id = $field['id'] ?? '';
            if (in_array($id, ['applicants', 'scholar', 'supervisor', 'persons-project'], true)) {
                if ($persons === null) {
                    $field['id'] = 'persons-project';
                    $props = DB::doc2Arr($field['props'] ?? []);
                    if ($id !== 'persons-project') unset($props['label'], $props['label_de']);
                    $field['props'] = array_replace(['label' => 'Persons', 'label_de' => 'Personen'], $props, ['required' => true]);
                    unset($field['props']['roles']);
                    $persons = count($result);
                    $result[] = $field;
                }
                continue;
            }
            if ($id === 'role') $field['id'] = 'institute-role';
            if (in_array($id, ['start', 'end', 'project-date', 'start_proposed', 'end_proposed', 'project-date-proposed'], true)) {
                $proposed = in_array($id, ['start_proposed', 'end_proposed', 'project-date-proposed'], true);
                $target = $proposed ? 'project-date-proposed' : 'project-date';
                if (!isset($dates[$target])) {
                    $field['id'] = $target;
                    if ($id !== $target) {
                        $field['props'] = array_replace(DB::doc2Arr($field['props'] ?? []), ['label' => $proposed ? 'Proposed project dates' : 'Project dates', 'label_de' => $proposed ? 'Beantragte Projektlaufzeit' : 'Projektlaufzeit']);
                        unset($field['props']['width']);
                    }
                    $dates[$target] = count($result);
                    $result[] = $field;
                } else {
                    $result[$dates[$target]]['props']['required'] = !empty($result[$dates[$target]]['props']['required']) || !empty($field['props']['required']);
                }
                continue;
            }
            $result[] = $field;
        }
        if ($persons === null) {
            array_unshift($result, ['id' => 'persons-project', 'type' => 'field', 'props' => ['label' => 'Persons', 'label_de' => 'Personen', 'required' => true]]);
        }
        return FormBuilder::prepareFields($result, FormBuilder::PROJECT_SYSTEM_FIELDS);
    }

    /** University is an activity alias for publisher, not an organization link. */
    public function activityFields($modules, array $fields): array
    {
        if (empty($fields)) {
            foreach ($modules as $module) {
                $id = rtrim($module, '*');
                $fields[] = ['id' => $id, 'type' => !empty($this->definitions[$id]['custom']) ? 'custom' : 'field', 'props' => ['required' => str_ends_with($module, '*')]];
            }
        }
        foreach ($fields as &$field) {
            $field = DB::doc2Arr($field);
            if (($field['type'] ?? 'field') === 'field' && ($field['id'] ?? '') === 'university') {
                $field['id'] = 'publisher';
                $field['props'] = array_replace(['label' => 'University', 'label_de' => 'Universität'], DB::doc2Arr($field['props'] ?? []));
            }
        }
        unset($field);
        return $fields;
    }
}
