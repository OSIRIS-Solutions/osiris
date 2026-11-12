<?php
// Focused editor flow and localized content checks; no browser or database required.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/ProjectFormMigration.php'); loadIsolated('php/ProjectForms.php');
require_once BASEPATH . '/php/LocalizedFields.php';
class Project {
    public array $FIELDS;
    public function __construct($document = null) { $this->FIELDS = array_column(json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true), null, 'id'); }
    public function getProjectTypes() { return DB::$database->adminProjects->find()->toArray(); }
    public function getProjectType($id) { return DB::$database->adminProjects->findOne(['id' => $id]); }
    public function getProject($id) { return DB::$database->projects->findOne(['_id' => $id]); }
    public function getStatus($status) { return $status; }
}
$osiris->adminProjects = new MemoryCollection(); $osiris->projects = new MemoryCollection();
$fields = [['id' => 'name-loc', 'type' => 'field', 'props' => ['required' => true]], ['id' => 'title-loc', 'type' => 'field', 'props' => ['required' => true]], ['id' => 'abstract-loc', 'type' => 'field', 'props' => []]];
$osiris->adminProjects->insertOne(['id' => 'grant', 'name' => 'Grant', 'name_de' => 'Förderung', 'icon' => 'briefcase', 'process' => 'proposal', 'phases' => [
    ['id' => 'proposed', 'fields' => $fields], ['id' => 'approved', 'fields' => array_merge($fields, [['id' => 'grant_sum', 'type' => 'field', 'props' => []]])], ['id' => 'project', 'fields' => $fields],
]]);
$Settings->permissions = ['proposals.add', 'proposals.edit', 'projects.add', 'projects.edit'];
$osiris->persons->updateOne(['username' => 'reviewer'], ['$set' => ['first' => 'Rae', 'last' => 'Reviewer']]);
$_SERVER['REQUEST_URI'] = '/proposals/new';
function editor($form, $parameters = [], $from_proposal = false, $collection = 'proposals'): string {
    global $DB, $osiris, $Settings;
    $_GET = ['type' => 'grant'] + $parameters;
    $source = file_get_contents(BASEPATH . '/pages/proposals/edit.php');
    $source = preg_replace('/^require_once .*;\R/m', '', $source);
    ob_start(); eval('?>' . $source); return ob_get_clean();
}
$canonical = ['name' => ['en' => 'English name', 'de' => 'Deutscher Name'], 'title' => ['en' => 'English title', 'de' => 'Deutscher Titel'], 'abstract' => ['en' => 'English abstract', 'de' => 'Deutsche Zusammenfassung']];
$html = editor($canonical);
check(str_contains($html, 'name="values[name][de]"') && str_contains($html, 'Deutscher Name') && str_contains($html, 'Deutsche Zusammenfassung'), 'The editor prefills the migrated language maps.');
check(str_contains($html, '/crud/proposals/create') && str_contains($html, 'name="values[status]" value="proposed"'), 'New proposals keep their original create route and initial status.');
$existing = $canonical + ['_id' => new MongoDB\BSON\ObjectId(), 'type' => 'grant', 'status' => 'proposed', 'persons' => [['user' => 'reviewer', 'name' => 'Rae Reviewer', 'role' => 'applicant', 'units' => []]], 'start_proposed' => '2027-01-01', 'end_proposed' => '2028-12-31', 'grant_sum_proposed' => 1234];
$html = editor($existing, ['phase' => 'approved']);
check(str_contains($html, '/crud/proposals/update/') && str_contains($html, 'name="values[status]" value="approved"'), 'Approval keeps its existing update route and status.');
check(str_contains($html, 'name="values[start]" value="2027-01-01"') && str_contains($html, 'name="values[grant_sum]"') && str_contains($html, 'value="1234"'), 'Proposed dates and funding are carried over on approval.');
$html = editor($existing, ['phase' => 'rejected']);
check(str_contains($html, 'name="values[rejection_date]"') && !str_contains($html, 'data-module="persons-project"'), 'Rejection asks for the rejection date without editing existing project persons.');
$html = editor($existing, ['phase' => 'withdrawn']);
check(str_contains($html, 'name="values[withdrawn_reason]"') && !str_contains($html, 'data-module="persons-project"'), 'Withdrawal retains its separate reason-only form.');
$html = editor($existing, [], true);
check(str_contains($html, '/crud/projects/create') && str_contains($html, 'name="values[proposal_id]"'), 'Creating a project from a proposal keeps the original linkage.');
$project = array_replace($existing, ['status' => 'ongoing']);
$html = editor($project, [], false, 'projects');
check(str_contains($html, '/crud/projects/update/') && str_contains($html, 'name="values[status]" value="project"'), 'Existing projects use the project form regardless of a legacy status.');
$html = editor($existing, ['phase' => 'unknown']);
check(str_contains($html, 'Unknown project phase.') && !str_contains($html, 'id="proposal-form"'), 'Invalid proposal phases render an inline error without invoking a second page layout.');
$Settings->permissions = [];
$html = editor($existing);
check(!str_contains($html, 'id="proposal-form"'), 'Existing edit permission checks still prevent editing.');
ob_end_clean(); echo $baselineOutput; echo 'Project editor checks passed (' . ($checks - $baselineChecks) . " checks).\n";
