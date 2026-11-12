<?php
// Configured project details with production formatters and in-memory data.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
loadIsolated('php/ProjectFormMigration.php');
loadIsolated('php/ProjectForms.php');
loadIsolated('php/Project.php');
class DetailProject extends Project {
    public function __construct($document, private array $definition) {
        $this->project = $document;
        $this->FIELDS = array_column(json_decode(file_get_contents(BASEPATH . '/data/project-fields.json'), true), null, 'id');
    }
    public function getProjectType($id) { return $this->definition; }
}
$field = fn($id, $props = [], $type = 'field') => ['type' => $type, 'id' => $id, 'props' => $props];
$initiativeId = new MongoDB\BSON\ObjectId();
$osiris->initiatives->insertOne(['_id' => $initiativeId, 'name' => ['en' => 'Climate'], 'title' => ['en' => 'Climate research']]);
$osiris->adminFields->insertOne(['id' => 'local-reference', 'name' => 'Local reference', 'format' => 'string', 'values' => []]);
$programs = (new Vocabulary())->getValues('funding-program');
$schema = [
    ['type' => 'heading', 'props' => ['text' => 'Our details']],
    $field('website', ['label' => 'More information']),
    $field('initiatives'), $field('acronym'), $field('grant_sum'),
    $field('funding_program_select'), $field('abstract-loc'), $field('local-reference', [], 'custom'),
    $field('comment', ['roles' => 'admin']),
    ['type' => 'paragraph', 'props' => ['text' => '<b>Note</b>']],
    $field('name'), $field('title'), $field('project-date'), $field('funder'), $field('persons-project'),
];
$project = new DetailProject([
    'type' => 'grant', 'name' => ['en' => 'Project'], 'title' => ['en' => 'Full title'],
    'website' => 'https://example.org', 'initiatives' => [$initiativeId],
    'acronym' => '<script>bad</script>', 'grant_sum' => 1234.5,
    'local-reference' => 'Ref <42>', 'funding_program_select' => $programs[0]['id'], 'abstract' => ['en' => '<p>Rich summary</p>'],
    'comment' => 'Restricted',
], ['id' => 'grant', 'process' => 'proposal', 'phases' => [
    ['id' => 'project', 'modules' => [['module' => 'comment']], 'fields' => $schema],
    ['id' => 'approved', 'fields' => [$field('approval_date')]],
    ['id' => 'rejected', 'fields' => [$field('rejection_date')]],
]]);
$details = ProjectForms::details($project, 'project', ['scientist']);
$rows = array_column(array_filter($details, fn($row) => $row['type'] === 'field'), null, 'id');
check($details[0]['type'] === 'heading' && array_keys($rows)[0] === 'website', 'Configured headings and field order are preserved.');
check($rows['website']['label'] === 'More information' && str_contains($rows['website']['value'], 'href="https://example.org"'), 'Configured labels and shared website formatting are used.');
check(str_contains($rows['initiatives']['value'], 'Climate') && str_contains($rows['initiatives']['value'], 'Climate research'), 'New initiative modules render linked names and title tooltips.');
check($rows['acronym']['value'] === '&lt;script&gt;bad&lt;/script&gt;', 'Plain project text remains escaped.');
check($rows['grant_sum']['value'] === '1.234,50 €', 'Existing currency formatting is retained.');
check($rows['funding_program_select']['value'] === $programs[0]['en'], 'Project vocabulary modules read their own storage field and resolve labels.');
check($rows['local-reference']['label'] === 'Local reference' && $rows['local-reference']['value'] === 'Ref &lt;42&gt;', 'Configured custom fields use their labels and value formatter.');
check($rows['abstract-loc']['value'] === '<p>Rich summary</p>', 'Localized rich text retains its formatting.');
check(!isset($rows['comment']), 'Role-restricted fields and stale legacy modules are not displayed.');
$adminDetails = ProjectForms::details($project, 'project', ['admin']);
check(in_array('comment', array_column($adminDetails, 'id'), true), 'Authorized users see role-restricted fields.');
$rejected = ProjectForms::details($project, 'rejected');
check(array_column($rejected, 'id') === ['rejection_date'], 'Rejected details use their own configuration without project persons.');
$Project = $project; $edit_perm = false;
ob_start(); include BASEPATH . '/components/project-details.php'; $html = ob_get_clean();
check(str_contains($html, 'Our details') && str_contains($html, '&lt;b&gt;Note&lt;/b&gt;') && !str_contains($html, 'Restricted'), 'The shared table component renders layout text safely.');
ob_end_clean(); echo $baselineOutput; echo 'Project detail checks passed (' . ($checks - $baselineChecks) . " checks).\n";
