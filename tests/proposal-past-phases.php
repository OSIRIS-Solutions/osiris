<?php
// Production permission rules, save handler, history and date derivation; no live DB.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/ProjectFormMigration.php'); loadIsolated('php/ProjectForms.php');
loadIsolated('php/Project.php'); loadIsolated('php/Render.php');
class PastPhaseProject extends Project {
    protected $db;
    public function __construct($document = []) {
        $this->db = DB::$database; $this->project = $document;
        $this->FIELDS = array_column(json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true), null, 'id');
    }
    public function to_ObjectID($id) { return DB::to_ObjectID($id); }
    // History comparisons do not need organization and country lookups in this fixture.
    public function printField($field, $value, $portfolio = false) { if (in_array($field, ['name', 'title', 'abstract'], true)) return parent::printField($field, $value, $portfolio); return is_array($value) || $value instanceof Traversable ? json_encode(DB::doc2Arr($value)) : (string) $value; }
}
$osiris->adminProjects = new MemoryCollection(); $osiris->proposals = new MemoryCollection();
$field = fn($id, $props = []) => ['id' => $id, 'type' => 'field', 'props' => $props];
$osiris->adminProjects->insertOne(['id' => 'grant', 'process' => 'proposal', 'phases' => [
    ['id' => 'proposed', 'fields' => [$field('name-loc'), $field('title-loc'), $field('submission_date'), $field('project-date-proposed'), $field('persons-project'), $field('funder'), $field('comment', ['roles' => 'admin'])]],
    ['id' => 'approved', 'fields' => [$field('approval_date'), $field('project-date')]],
]]);
$id = new MongoDB\BSON\ObjectId();
$document = ['_id' => $id, 'type' => 'grant', 'status' => 'approved', 'created_by' => 'reviewer', 'name' => ['en' => 'Grant'], 'title' => ['en' => 'Long title'],
    'start' => ['year' => 2025, 'month' => 1, 'day' => 1], 'end' => ['year' => 2028, 'month' => 12, 'day' => 31],
    'start_date' => '2025-01-01', 'end_date' => '2028-12-31', 'start_proposed' => '2024-01-01', 'end_proposed' => '2027-12-31',
    'history' => [], 'applicants' => [], 'nagoya' => ['enabled' => true, 'status' => 'abs-review']];
$osiris->proposals->insertOne($document);
$Settings->permissions = ['proposals.edit-own', 'proposals.edit-past-phases'];
check(ProjectForms::pastPhases($document) === ['proposed'], 'Approved proposals expose the proposed phase without inventing draft history.');
check(ProjectForms::pastPhases(array_replace($document, ['status' => 'rejected'])) === ['proposed'] && ProjectForms::pastPhases(array_replace($document, ['status' => 'withdrawn'])) === ['proposed'], 'Alternative outcomes share proposed as their predecessor.');
check(ProjectForms::pastPhases(array_replace($document, ['history' => [['status_before' => 'draft', 'status_after' => 'proposed']]])) === ['draft', 'proposed'], 'Actually used draft phases are included in chronological order.');
check(ProjectForms::canEditPhase($document, 'proposed', $Settings, 'reviewer', true) && !ProjectForms::canEditPhase($document, 'proposed', $Settings, 'other', true), 'The additional right respects the scope of edit-own.');
check(!ProjectForms::canEditPhase($document, 'rejected', $Settings, 'reviewer', true) && !ProjectForms::canEditPhase($document, 'proposed', $Settings, 'reviewer'), 'Neither sibling phases nor backwards status transitions are corrections.');
$Settings->permissions = ['proposals.edit', 'proposals.edit-past-phases'];
// Replace only application bootstrap and the fixture constructor; execute the real save code.
$source = file_get_contents(BASEPATH . '/routes/projects.php');
$source = str_replace('include_once BASEPATH . "/php/init.php";', 'global $DB, $osiris, $Settings, $USER;', $source);
$source = preg_replace('/^\s*(?:include|require)(?:_once)? .*;\R/m', '', $source);
$source = str_replace('new Project(', 'new PastPhaseProject(', $source);
eval('?>' . $source);
$save = Route::$routes['POST /crud/(projects|proposals)/update/([A-Za-z0-9]*)'][0];
$_POST = ['edit_phase' => 'proposed', 'values' => ['type' => 'forged', 'status' => 'rejected', 'title' => ['en' => 'Long title', 'de' => 'Korrigierter Titel'], 'start_proposed' => '2024-03-01', 'end_proposed' => '2027-09-30', 'start' => '2099-01-01', 'grant_sum' => 999, 'comment' => 'Restricted', 'history' => ['forged']]];
$save('proposals', (string) $id);
$saved = $osiris->proposals->findOne(['_id' => $id]);
check($saved['status'] === 'approved' && $saved['type'] === 'grant', 'A real correction request preserves status and type even when payload values are forged.');
check($saved['start_proposed'] === '2024-03-01' && $saved['end_proposed'] === '2027-09-30', 'Only the selected phase inputs are updated.');
check($saved['start_date'] === '2025-01-01' && $saved['end_date'] === '2028-12-31' && $saved['start'] === $document['start'], 'Corrected proposed dates do not replace the approved effective dates.');
check(!isset($saved['grant_sum'], $saved['comment']), 'Future-phase fields and role-restricted fields are ignored.');
$last = end($saved['history']);
check($last['phase'] === 'proposed' && $last['past_phase'] === true && !isset($last['status_before']), 'History records the corrected phase without claiming a status transition.');
check(isset($last['changes']['title']) && str_contains($last['changes']['title']['after'], 'Korrigierter Titel'), 'Changes to another translation remain visible in history even when the current-language text is unchanged.');
// Older imports may have only effective date strings, without start/end arrays.
$cacheOnly = $document; unset($cacheOnly['start'], $cacheOnly['end']);
$osiris->proposals->documents[(string) $id] = $cacheOnly;
$save('proposals', (string) $id);
$cached = $osiris->proposals->findOne(['_id' => $id]);
check($cached['start_date'] === '2025-01-01' && $cached['end_date'] === '2028-12-31', 'Imported approvals with only effective date strings also retain their dates.');
$Settings->permissions = ['proposals.edit'];
$_GET = ['edit_phase' => 'proposed'];
$getEditor = Route::$routes['GET /(projects|proposals)/(edit|collaborators|finance|persons)/([a-zA-Z0-9]*)'][0];
try { $getEditor('proposals', 'edit', (string) $id); throw new RuntimeException('Expected GET permission rejection.'); }
catch (Aborted $error) { check($error->getCode() === 403, 'Direct editor links enforce the same extra permission before rendering.'); }
try { $save('proposals', (string) $id); throw new RuntimeException('Expected permission rejection.'); }
catch (Aborted $error) { check($error->getCode() === 403 && count($osiris->proposals->findOne(['_id' => $id])['history']) === 1, 'Direct POSTs without the extra permission cannot write changes.'); }
$_POST = ['values' => ['type' => 'grant', 'status' => 'proposed']];
try { $save('proposals', (string) $id); throw new RuntimeException('Expected backwards transition rejection.'); }
catch (Aborted $error) { check($error->getCode() === 403, 'Omitting the correction marker cannot bypass the past-phase permission.'); }
$beforeApproval = array_replace($document, ['status' => 'proposed']);
$osiris->proposals->updateOne(['_id' => $id], ['$set' => $beforeApproval]);
$Settings->enabled = false;
$_POST = ['values' => ['type' => 'grant', 'status' => 'approved']];
$save('proposals', (string) $id);
$history = $osiris->proposals->findOne(['_id' => $id])['history']; $last = end($history);
check($last['status_before'] === 'proposed' && $last['status_after'] === 'approved' && empty($last['past_phase']), 'Ordinary approvals remain genuine status transitions.');
ob_end_clean(); echo $baselineOutput; echo 'Past proposal phase checks passed (' . ($checks - $baselineChecks) . " checks).\n";
