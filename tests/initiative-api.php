<?php
// Read-model privacy, language handling, scoping and endpoint authorization.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
function apiLocalized($value) { return ($_GET['lang'] ?? '') === 'all' ? DB::doc2Arr($value) : localized($value, $_GET['lang'] ?? 'en'); }
function apiLocalizedFields(array $document, array $fields) {
    foreach ($fields as $field) if (array_key_exists($field, $document)) $document[$field] = apiLocalized($document[$field]);
    return $document;
}
$allowedScopes = ['initiatives.read'];
$portfolioAllowed = true;
function apikey_check($key = null, $scope = null) { global $allowedScopes; return in_array($scope, $allowedScopes, true); }
function portfolio_apikey_check($key) { global $portfolioAllowed; return $portfolioAllowed; }
// Load the existing response helpers and route callbacks without application bootstrap.
foreach (['routes/api/api.php' => 'return_rest', 'routes/api/portfolio.php' => 'rest'] as $file => $function) {
    $source = str_replace("\r\n", "\n", file_get_contents(BASEPATH . '/' . $file));
    $offset = strpos($source, 'function ' . $function . '(');
    eval(substr($source, $offset, strpos($source, "\n}\n", $offset) + 3 - $offset));
}
foreach (['routes/api/api.php' => ["Route::get('/api/(initiatives|objectives)'", "Route::get('/api/activities'"],
          'routes/api/portfolio.php' => ["Route::get('/portfolio/(initiatives|objectives)'", "Route::get('/portfolio/settings'"]] as $file => [$start, $end]) {
    $source = str_replace("\r\n", "\n", file_get_contents(BASEPATH . '/' . $file));
    $offset = strpos($source, $start);
    $source = substr($source, $offset, strpos($source, $end, $offset) - $offset);
    $source = str_replace("include_once BASEPATH . '/php/init.php';", 'global $osiris, $Settings;', $source);
    $source = preg_replace('/^\s*require_once .*;\R/m', '', $source);
    eval($source);
}
function initiativeApiRequest(string $api, string $entity, ?string $id = null, ?string $relation = null): array
{
    $portfolio = $api === 'portfolio';
    $pattern = $id === null ? ($portfolio ? '/portfolio/(initiatives|objectives)' : '/api/(initiatives|objectives)')
        : ($portfolio ? '/portfolio/(initiative|objective)/([^/]+)' : '/api/(initiatives|objectives)/([^/]+)');
    if ($relation !== null) $pattern .= $portfolio ? '/(publications|activities|all-activities|projects|events|infrastructures)' : '/(objectives|activities|projects|proposals|events|infrastructures|deadlines)';
    $args = [$portfolio && $id !== null ? substr($entity, 0, -1) : $entity];
    if ($id !== null) $args[] = $id;
    if ($relation !== null) $args[] = $relation;
    http_response_code(200);
    ob_start(); Route::$routes['GET ' . $pattern][0](...$args); return json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
}
$osiris->initiatives = new MemoryCollection();
$osiris->objectives = new MemoryCollection();
$osiris->adminGeneral = new MemoryCollection();
$publicId = new MongoDB\BSON\ObjectId();
$privateId = new MongoDB\BSON\ObjectId();
$objectiveId = new MongoDB\BSON\ObjectId();
$public = ['_id' => $publicId, 'name' => ['en' => '001', 'de' => 'Wasser'], 'title' => ['en' => 'Public title'], 'description' => ['en' => 'Private description'], 'public' => true, 'created_by' => 'internal-user', 'persons' => [['user' => 'private-user']], 'inactive' => false, 'start_date' => '2026-01-01'];
$osiris->initiatives->insertOne($public);
$osiris->initiatives->insertOne(['_id' => $privateId, 'name' => ['en' => 'Internal initiative'], 'public' => false]);
$osiris->objectives->insertOne(['_id' => $objectiveId, 'initiative' => (string) $publicId, 'name' => 'Public objective', 'code' => '001', 'public' => true, 'type' => 'objective']);
$hiddenObjective = new MongoDB\BSON\ObjectId();
$osiris->objectives->insertOne(['_id' => $hiddenObjective, 'initiative' => (string) $privateId, 'name' => 'Must remain hidden', 'public' => true]);
$archivedObjectiveId = new MongoDB\BSON\ObjectId();
$osiris->objectives->insertOne(['_id' => $archivedObjectiveId, 'initiative' => (string) $publicId, 'name' => 'Archived objective', 'public' => true, 'inactive' => true]);
$osiris->objectives->insertOne(['initiative' => (string) $publicId, 'name' => 'Private objective', 'public' => false]);
$fields = Initiatives::fields($osiris);
foreach ($fields as &$field) if ($field['id'] === 'description') $field['props']['portfolio'] = false;
unset($field);
$osiris->adminGeneral->insertOne(['id' => 'initiative-fields', 'fields' => $fields]);
check(initiativeApiRequest('portfolio', 'initiatives', (string) $privateId)['status'] === 404, 'Internal initiatives cannot be read through the Portfolio API.');
check(initiativeApiRequest('portfolio', 'objectives', (string) $hiddenObjective)['status'] === 404, 'A published objective remains private when its initiative is not published.');
$visible = initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data'];
check($visible['name'] === '001' && !isset($visible['description'], $visible['created_by'], $visible['persons']), 'Public metadata respects field visibility and omits audit data and raw person memberships.');
check(array_column($visible['objectives'], 'id') === [(string) $objectiveId, (string) $archivedObjectiveId] && $visible['numbers']['objectives'] === 2, 'Initiative details embed only their public objectives, with archived entries last and a matching count.');
$_GET = ['lang' => 'de'];
check(initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data']['name'] === 'Wasser', 'Public metadata uses the requested language.');
$_GET = ['lang' => 'all'];
check(initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data']['name'] === $public['name'], 'lang=all preserves the complete language map.');
$_GET = [];
check(count(initiativeApiRequest('portfolio', 'initiatives')['data']) === 1 && count(initiativeApiRequest('portfolio', 'objectives')['data']) === 1, 'Public lists exclude unpublished parents and their objectives.');
$_GET = ['search' => 'Private description'];
check(initiativeApiRequest('portfolio', 'initiatives')['data'] === [], 'Public search cannot discover hidden metadata.');
$_GET = ['initiative' => (string) $privateId];
check(initiativeApiRequest('api', 'initiatives', (string) $publicId, 'objectives')['data'] === [], 'Query parameters cannot override a parent-scoped endpoint.');
$_GET = [];
$response = initiativeApiRequest('portfolio', 'initiatives');
check($response['data'][0]['name'] === '001', 'JSON output keeps numeric-looking names as strings.');
$_GET = ['json' => '{invalid'];
$response = initiativeApiRequest('api', 'initiatives');
check($response['status'] === 400 && http_response_code() === 400, 'Malformed filters return an HTTP/JSON 400 response.');
$_GET = [];
$allowedScopes = [];
$response = initiativeApiRequest('api', 'initiatives');
check($response['status'] === 403, 'Internal endpoints require the initiatives.read scope.');
$allowedScopes = ['initiatives.read'];
$response = initiativeApiRequest('api', 'initiatives', (string) $publicId, 'activities');
check($response['status'] === 403, 'Related content additionally requires its own read scope.');
$portfolioAllowed = false;
$response = initiativeApiRequest('portfolio', 'initiatives');
check($response['status'] === 403, 'Portfolio endpoints enforce their separate API key.');
$portfolioAllowed = true;
$response = initiativeApiRequest('portfolio', 'initiatives', 'bad-id');
check($response['status'] === 404, 'Invalid or unpublished detail IDs return 404.');
$_GET = ['status' => 'all', 'offset' => 1, 'limit' => 1];
$response = initiativeApiRequest('api', 'initiatives');
check($response['count'] === 2 && count($response['data']) === 1, 'Pagination preserves the total match count.');
$_GET = ['offset' => 1];
$response = initiativeApiRequest('api', 'initiatives');
check($response['count'] === 2 && count($response['data']) === 1, 'Offsets work without a limit.');
$_GET = ['offset' => 1, 'limit' => 1];
check(initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data']['name'] === '001', 'List pagination does not truncate detail documents.');
check(count(initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data']['objectives']) === 2, 'List pagination does not truncate embedded objectives or their count.');
class InitiativeContentCollection extends MemoryCollection
{
    public array $filter = [];
    public array $options = [];
    public array $countFilters = [];
    public function countDocuments(array $filter = []) { $this->countFilters[] = $filter; return 3; }
    public function find(array $filter = [], array $options = []) {
        $this->filter = $filter; $this->options = $options;
        return parent::find([], $options);
    }
}
$originalInitiatives = $osiris->initiatives;
$osiris->initiatives = new InitiativeContentCollection();
$_GET = ['filter' => ['$or' => [['_id' => (string) $publicId], ['initiatives' => ['$in' => [(string) $privateId]]]]]];
initiativeApiRequest('api', 'initiatives');
$query = $osiris->initiatives->filter;
check($query['$or'][0]['_id'] instanceof MongoDB\BSON\ObjectId && $query['$or'][1]['initiatives']['$in'][0] === (string) $privateId, 'Nested filters convert primary IDs to BSON while relationship IDs stay strings.');
$_GET = ['filter' => ['$or' => ['invalid']]];
check(initiativeApiRequest('api', 'initiatives')['status'] === 400, 'Malformed logical groups return a controlled error.');
$osiris->initiatives = $originalInitiatives;
$contentSettings = new class {
    public array $disabled = [];
    public function featureEnabled($feature) { return !in_array($feature, $this->disabled, true); }
    public function getActivitiesPortfolio($all = true) { return $all ? ['article', 'lecture'] : ['lecture']; }
    public function get($key, $default = null) { return $key === 'portfolio-workflow-visibility' ? 'only-approved' : $default; }
};
$osiris->activities = new InitiativeContentCollection();
$osiris->activities->insertOne(['title' => 'Public activity', 'type' => 'publication', 'subtype' => 'article', 'rendered' => ['portfolio' => '<p>Public</p>', 'plain' => 'Public', 'web' => 'Internal'], 'created_by' => 'private', 'private_note' => 'secret']);
$_GET = [];
$originalSettings = $Settings;
$Settings = $contentSettings;
$content = initiativeApiRequest('portfolio', 'initiatives', (string) $publicId, 'publications')['data'];
check($osiris->activities->filter['initiatives'] === (string) $publicId && $osiris->activities->filter['workflow.status'] === 'verified' && $osiris->activities->filter['hide']['$ne'] === true, 'Public relation queries retain their parent, hidden-record and workflow restrictions.');
check($osiris->activities->filter['type'] === 'publication' && $osiris->activities->filter['subtype']['$in'] === ['article', 'lecture'], 'Publication relations honor the enabled Portfolio subtypes.');
check($content[0]['html'] === '<p>Public</p>' && !isset($content[0]['rendered'], $content[0]['created_by'], $content[0]['private_note']), 'Public relations return the public activity rendering without internal caches or metadata.');
initiativeApiRequest('portfolio', 'initiatives', (string) $publicId, 'activities');
$osiris->projects = new InitiativeContentCollection();
$osiris->conferences = new InitiativeContentCollection();
$osiris->infrastructures = new InitiativeContentCollection();
$details = initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data'];
$countFilters = array_slice($osiris->activities->countFilters, -2);
check($details['numbers']['publications'] === 3 && $details['numbers']['activities'] === 3 && $countFilters[0]['initiatives'] === (string) $publicId, 'Detail counters return database totals scoped to the initiative.');
check($countFilters[0]['workflow.status'] === 'verified' && $countFilters[0]['hide']['$ne'] === true && $countFilters[0]['type'] === 'publication' && $countFilters[1]['type']['$ne'] === 'publication' && $countFilters[1]['subtype']['$in'] === ['lecture'], 'Publication and activity counters use the same workflow, hidden-record and subtype restrictions as their lists.');
check($details['numbers']['projects'] === 3 && $osiris->projects->countFilters[0] === ['initiatives' => (string) $publicId, 'public' => true] && $osiris->conferences->countFilters[0]['public'] === true && $osiris->infrastructures->countFilters[0]['public'] === true, 'Project, event and infrastructure counters require public content and the parent association.');
$contentSettings->disabled = ['projects', 'infrastructures'];
$disabledDetails = initiativeApiRequest('portfolio', 'initiatives', (string) $publicId)['data'];
check($disabledDetails['numbers']['projects'] === 0 && $disabledDetails['numbers']['infrastructures'] === 0 && count($osiris->projects->countFilters) === 1 && count($osiris->infrastructures->countFilters) === 1, 'Disabled features have zero counts without querying their content.');
$Settings = $originalSettings;
check($osiris->activities->filter['type']['$ne'] === 'publication' && $osiris->activities->filter['subtype']['$in'] === ['lecture'], 'The activities endpoint follows the existing convention of excluding publications.');
$_GET = [];
$_SESSION['loggedin'] = true;
$Settings->permissions = [];
$response = initiativeApiRequest('api', 'initiatives');
check($response['status'] === 403, 'Authenticated browser requests also require the initiative view permission.');
ob_end_clean(); echo $baselineOutput; echo 'Initiative API checks passed (' . ($checks - $baselineChecks) . " checks).\n";
