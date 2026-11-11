<?php
// Run: php tests/project-forms.php. No live database required.
ob_start();
require __DIR__ . '/initiatives.php';
$baselineOutput = ob_get_clean();
ob_start();
$baselineChecks = $checks;
loadIsolated('php/ProjectFormMigration.php');
loadIsolated('php/ProjectForms.php');
require_once BASEPATH . '/php/LocalizedFields.php';
$definitions = array_column(json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true), null, 'id');
$projectType = ['_id' => new MongoDB\BSON\ObjectId(), 'id' => 'grant', 'name' => 'Grant project', 'process' => 'proposal', 'stage' => 2, 'phases' => [
    ['id' => 'proposed', 'name' => 'Custom phase', 'modules' => [['module' => 'abstract_de', 'required' => false]]],
    ['id' => 'approved', 'modules' => [['module' => 'grant_sum', 'required' => true]]],
    ['id' => 'project', 'modules' => [['module' => 'website', 'required' => false]]],
]];
check(array_keys(ProjectForms::phases($projectType)) === ['draft', 'proposed', 'approved', 'rejected', 'project'], 'Proposal types have four current phases plus planned draft.');
$directType = array_replace($projectType, ['_id' => new MongoDB\BSON\ObjectId(), 'id' => 'direct', 'process' => 'project']);
check(array_keys(ProjectForms::phases($directType)) === ['draft', 'project'], 'Direct projects expose only draft and project forms.');
check(ProjectForms::phase($projectType, 'draft')['planned'] === true, 'Draft is explicitly marked as planned.');
rejects(fn() => ProjectForms::phase($directType, 'approved'), 'Proposal phases are rejected for direct project types.');
rejects(fn() => ProjectForms::systemFields('invented'), 'Unknown phases cannot get system rules.');
foreach (ProjectForms::phases($projectType) as $phase) {
    $id = $phase['id'];
    $config = ProjectForms::builder($projectType, $id);
    foreach ($config->systemFields as $field => $rule) check(isset($config->modules[$field]), "Every $id system field is available.");
    $fields = ProjectForms::fields($projectType, $id, $definitions);
    ProjectForms::validate($fields, $config);
    check(ProjectForms::unavailable($fields, $config) === [], "Defaults for $id are compatible with its catalogue.");
    $withoutPersons = array_values(array_filter($fields, fn($f) => ($f['id'] ?? '') !== 'persons-project'));
    rejects(fn() => ProjectForms::validate($withoutPersons, $config), "Project persons cannot be removed in $id.");
    foreach (['submission_date', 'approval_date', 'rejection_date'] as $date) {
        $expected = ['submission_date' => 'proposed', 'approval_date' => 'approved', 'rejection_date' => 'rejected'][$date];
        check(isset($config->modules[$date]) === ($id === $expected), "$date is only available in its matching phase.");
    }
    check(isset($config->modules['project-date-proposed']) === in_array($id, ['draft', 'proposed'], true), "Proposed dates are phase-limited in $id.");
    check(isset($config->modules['ressources']) === in_array($id, ['draft', 'proposed'], true), "Planned resources are phase-limited in $id.");
    check(isset($config->modules['grant_sum_proposed']) === in_array($id, ['draft', 'proposed'], true), "Proposed grants are phase-limited in $id.");
    check(isset($config->modules['project-date']) === in_array($id, ['approved', 'project'], true), "Actual project dates are phase-limited in $id.");
    check(isset($config->modules['grant_sum']) === in_array($id, ['approved', 'project'], true), "Approved grants are phase-limited in $id.");
    check(str_contains($config->redirectUrl, '/fields/grant/' . $id) && str_ends_with($config->backUrl, '/2/grant'), 'Builder save and return URLs preserve the phase and project type.');
}
$proposed = ProjectForms::builder($projectType, 'proposed');
$fields = ProjectForms::fields($projectType, 'proposed', $definitions);
check(in_array('abstract-loc', array_column($fields, 'id'), true), 'Legacy fields are available before migration without rewriting old modules.');
$localized = $fields;
foreach ($localized as &$field) if (($field['id'] ?? '') === 'name') $field['id'] = 'name-loc';
unset($field);
ProjectForms::validate($localized, $proposed);
check(FormBuilder::systemFieldsFor($localized, $proposed->systemFields)['name-loc']['required'] === true, 'Localized names satisfy the system requirement.');
$localized = array_reverse($localized);
ProjectForms::validate($localized, $proposed);
check(true, 'Admins may reposition system fields.');
$forbidden = $fields; $forbidden[] = ['id' => 'project-date', 'type' => 'field', 'props' => []];
rejects(fn() => ProjectForms::validate($forbidden, $proposed), 'Posting a field hidden from the catalogue is rejected.');
check(ProjectForms::unavailable($forbidden, $proposed) === ['project-date'], 'Incompatible old fields are reported instead of silently discarded.');
$malformed = $fields; $malformed[] = 'invalid';
rejects(fn() => ProjectForms::validate($malformed, $proposed), 'Malformed schema elements are rejected.');
$duplicate = $fields; $duplicate[] = array_values(array_filter($fields, fn($field) => ($field['type'] ?? '') === 'field'))[0];
rejects(fn() => ProjectForms::validate($duplicate, $proposed), 'Duplicate fields are rejected.');
$layout = $fields; $layout[] = ['type' => 'script', 'props' => []];
rejects(fn() => ProjectForms::validate($layout, $proposed), 'Unrecognized element types are rejected.');
$custom = $fields; $custom[] = ['id' => 'secret', 'type' => 'custom', 'props' => []];
ProjectForms::validate($custom, $proposed);
check(true, 'Registered custom fields remain available across phases.');

$collection = new class extends MemoryCollection {
    public int $writes = 0;
    public function distinct($key) { return array_column($this->documents, $key); }
    public function updateOne(array $filter, array $update) {
        foreach ($this->documents as &$document) {
            if ((string) $document['_id'] !== (string) $filter['_id']) continue;
            foreach ($update['$set'] ?? [] as $path => $value) {
                $target = &$document;
                foreach (explode('.', $path) as $key) $target = &$target[$key];
                $target = $value; unset($target);
            }
            foreach ($update['$push'] ?? [] as $key => $value) $document[$key][] = $value;
            $this->writes++;
            return new class { public function getModifiedCount() { return 1; } };
        }
        throw new RuntimeException('Test document missing.');
    }
};
$collection->insertOne($projectType);
ProjectForms::save($collection, $projectType, 'proposed', $localized);
$saved = $collection->findOne(['id' => 'grant']);
check($saved['phases'][0]['fields'] === $localized, 'Saved fields keep the selected ordering and properties.');
check($saved['phases'][0]['modules'] === $projectType['phases'][0]['modules'] && $saved['phases'][0]['name'] === 'Custom phase', 'Legacy modules and phase metadata remain untouched.');
check($saved['phases'][1] === $projectType['phases'][1] && $saved['phases'][2] === $projectType['phases'][2], 'Saving one phase preserves every other phase.');
$draft = ProjectForms::fields($saved, 'draft', $definitions);
ProjectForms::save($collection, $saved, 'draft', $draft);
$saved = $collection->findOne(['id' => 'grant']);
check(count($saved['phases']) === 4 && $saved['phases'][3]['id'] === 'draft', 'Saving a planned phase appends its configuration.');
ProjectForms::save($collection, $saved, 'draft', $draft);
check(count($collection->findOne(['id' => 'grant'])['phases']) === 4, 'Saving draft again does not duplicate its phase.');
$writeCount = $collection->writes;
rejects(fn() => ProjectForms::save($collection, $saved, 'proposed', $forbidden), 'Invalid fields cannot be persisted.');
check($collection->writes === $writeCount, 'Rejected saves perform no writes.');

// Render the actual overview and shared builder.
$project = $projectType;
$Project = (object) ['FIELDS' => $definitions];
$overviewSource = str_replace("include_once BASEPATH . '/php/ProjectForms.php';", '', file_get_contents(BASEPATH . '/pages/admin/project-phases.php'));
ob_start(); eval('?>' . $overviewSource); $overview = ob_get_clean();
$dom = new DOMDocument(); @$dom->loadHTML($overview); $xpath = new DOMXPath($dom);
check($xpath->query('//div[contains(@class,"phase") and @data-id]')->length === 5, 'The phase overview renders one card per phase.');
check(substr_count($overview, 'Im Form-Builder') === 0 && substr_count($overview, 'Edit in Form Builder') === 5, 'Each phase has a prominent builder link.');
check(str_contains($overview, 'ph-text-h') && str_contains($overview, 'ph-lock-simple'), 'Overview badges show headings and system fields.');
check(!str_contains($overview, 'name="phase[') && !str_contains($overview, 'toggleCheckboxStates'), 'Legacy phase checkboxes are no longer rendered.');
$config = ProjectForms::builder($projectType, 'approved');
$fields = ProjectForms::fields($projectType, 'approved', $definitions);
$type = ['id' => 'grant', 'name' => $config->entityLabel]; $copy = false;
$Settings->permissions = ['admin.see'];
// The shared builder expects the role list in its settings service.
$SettingsWithRoles = new class($Settings) {
    public function __construct(private $settings) {}
    public function getRoles() { return ['scientist', 'admin']; }
    public function get($key, $default = null) { return $key === 'roles' ? ['scientist', 'admin'] : $this->settings->get($key, $default); }
    public function __call($method, $args) { return $this->settings->$method(...$args); }
};
$originalSettings = $Settings; $Settings = $SettingsWithRoles;
ob_start(); include BASEPATH . '/pages/admin/form-builder.php'; $builderHtml = ob_get_clean();
$Settings = $originalSettings;
check(str_contains($builderHtml, 'const SYSTEM_FIELDS = ') && str_contains($builderHtml, 'approval_date'), 'Phase system rules are passed to the shared builder.');
check(!str_contains($builderHtml, 'data-id="project-date-proposed"'), 'The approved builder does not offer proposed dates.');
check(str_contains($builderHtml, 'ph-lock-simple') && str_contains($builderHtml, '/crud/admin/projects/fields/grant/approved'), 'The shared builder retains locking and the phase save target.');
if (isset($argv[1])) {
    file_put_contents($argv[1] . '/project-phases.html', $overview);
    file_put_contents($argv[1] . '/approved-builder.html', $builderHtml);
}
// Exercise both complete configuration pages as well as the route boundaries.
class Project {
    public array $FIELDS;
    public function __construct() { $this->FIELDS = array_column(json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true), null, 'id'); }
}
$osiris->adminProjects = $collection;
$osiris->projects = new class { public function count($filter) { return 0; } };
$osiris->proposals = new class { public function count($filter) { return 0; } };
foreach ($osiris->persons->documents as &$person) { $person['first'] ??= 'Test'; $person['last'] ??= 'User'; }
unset($person);
$project = $projectType; $type = 'grant'; $id = 'grant';
$Settings = $SettingsWithRoles;
for ($stage = 1; $stage <= 2; $stage++) {
    $pageSource = str_replace("include BASEPATH . '/pages/admin/project-phases.php';", "eval('?>' . \$overviewSource);", file_get_contents(BASEPATH . '/pages/admin/project.php'));
    ob_start(); eval('?>' . $pageSource); $page = ob_get_clean();
    if ($stage === 1) check(str_contains($page, 'name="values[id]"') && str_contains($page, 'name="values[process]"'), 'The complete first configuration page retains its ID and process inputs.');
    else check(str_contains($page, 'id="phase-proposed"') && str_contains($page, 'id="phase-draft"') && !str_contains($page, 'name="phase['), 'The complete second configuration page contains the new phase overview.');
    if (isset($argv[1])) file_put_contents($argv[1] . '/configuration-' . $stage . '.html', $page);
}
$Settings = $originalSettings;
loadIsolated('routes/admin.php');
$getRoute = Route::$routes['GET /admin/projects/([^/]+)/fields/(draft|proposed|approved|rejected|project)'];
$postRoute = Route::$routes['POST /crud/admin/projects/fields/([^/]+)/(draft|proposed|approved|rejected|project)'];
check($getRoute[1] === 'login' && $postRoute[1] === 'login', 'Both form routes require login.');
$Settings->permissions = [];
aborts(fn() => $getRoute[0]('grant', 'project'), 403);
aborts(fn() => $postRoute[0]('grant', 'project'), 403);
$Settings->permissions = ['admin.see'];
$collection->insertOne($directType);
aborts(fn() => $getRoute[0]('direct', 'approved'), 404);
aborts(fn() => $postRoute[0]('missing', 'project'), 404);
$_GET = ['overview' => 1];
ob_start(); $getRoute[0]('grant', 'approved'); $phaseOverview = ob_get_clean();
check(str_contains($phaseOverview, 'approval_date') && !str_contains($phaseOverview, 'project-date-proposed'), 'The real overview route renders the filtered phase catalogue.');
$_GET = [];
$_POST = ['schema' => '{broken'];
aborts(fn() => $postRoute[0]('grant', 'project'), 400);
$_POST = ['schema' => json_encode(['items' => $forbidden])];
aborts(fn() => $postRoute[0]('grant', 'proposed'), 400);
$_POST = ['schema' => json_encode(['items' => $localized])];
$postRoute[0]('grant', 'proposed');
check($collection->findOne(['id' => 'grant'])['phases'][0]['fields'] === $localized, 'The real save route persists the validated phase schema.');
$writesBeforeLegacyPost = $collection->writes;
$_POST = ['stage' => 2];
Route::$routes['POST /crud/admin/projects/update/([A-Za-z0-9]*)'][0]((string) $projectType['_id']);
check($collection->writes === $writesBeforeLegacyPost, 'An old stage-two submission can no longer erase phase configurations.');
ob_end_clean();
echo $baselineOutput;
echo 'Project forms tests passed (' . ($checks - $baselineChecks) . " checks).\n";
