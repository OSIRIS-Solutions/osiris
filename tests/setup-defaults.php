<?php
// Check fresh installation and repeated initialization without a live database.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/ProjectFormMigration.php');
loadIsolated('php/ProjectForms.php');
loadIsolated('php/EventForms.php');
loadIsolated('php/DeadlineForms.php');

class SetupCollection extends MemoryCollection
{
    public function updateOne(array $filter, array $update, array $options = [])
    {
        if ($this->findOne($filter) === null && !empty($options['upsert'])) {
            $this->insertOne(array_replace($filter, $update['$setOnInsert'] ?? [], $update['$set'] ?? []));
            return;
        }
        return parent::updateOne($filter, ['$set' => $update['$set'] ?? []]);
    }
}
$database = (object) ['adminGeneral' => new SetupCollection(), 'adminProjects' => new SetupCollection()];
setupDefaults($database);
check(count($database->adminGeneral->documents) === 3 && count($database->adminProjects->documents) === 1, 'Fresh installation creates all three forms and a project type.');
$project = $database->adminProjects->findOne(['id' => 'third-party']);
check($project['process'] === 'proposal' && array_column($project['phases'], 'id') === ['draft', 'proposed', 'approved', 'rejected', 'project'], 'The third-party default covers the proposal lifecycle with draft prepared.');
foreach ($project['phases'] as $phase) {
    FormBuilder::validateFields($phase['fields'], ProjectForms::systemFields($phase['id']));
    $config = ProjectForms::builder($project, $phase['id']);
    $ids = array_column(array_filter($phase['fields'], fn($field) => ($field['type'] ?? '') === 'field'), 'id');
    check(array_diff($ids, array_keys($config->modules)) === [], 'All default modules are available in phase ' . $phase['id']);
}
$database->adminGeneral->updateOne(['id' => 'initiative-fields'], ['$set' => ['fields' => []]]);
$before = serialize($database);
setupDefaults($database);
check(serialize($database) === $before, 'Repeating initialization preserves every existing definition, including intentionally empty forms.');
$existing = (object) ['adminGeneral' => new SetupCollection(), 'adminProjects' => new SetupCollection()];
$existing->adminProjects->insertOne(['id' => 'institute-specific', 'phases' => [['id' => 'project', 'modules' => ['website']]]]);
$beforeProjects = serialize($existing->adminProjects);
setupDefaults($existing, ['events', 'deadlines', 'initiatives']);
check(serialize($existing->adminProjects) === $beforeProjects, 'Version migration initializes forms without changing or adding project types.');
setupDefaults($existing);
check(serialize($existing->adminProjects) === $beforeProjects, 'Even full initialization preserves existing project types.');
check(EventForms::defaults() === EventForms::fields($database) && DeadlineForms::defaults() === DeadlineForms::fields($database), 'Runtime fallbacks and installed configurations use the same shipped forms.');
ob_end_clean(); echo $baselineOutput; echo 'Setup checks passed (' . ($checks - $baselineChecks) . " checks).\n";
