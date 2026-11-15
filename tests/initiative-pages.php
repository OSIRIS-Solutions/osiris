<?php
// Linked-content scope, access filters and objective navigation.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
class InitiativePageCollection extends MemoryCollection
{
    public array $lastFilter = [];
    public function find(array $filter = [], array $options = []) {
        $this->lastFilter = $filter;
        $documents = array_filter($this->documents, function ($document) use ($filter) {
            foreach (['initiatives', 'objectives'] as $field) {
                if (isset($filter[$field]) && !in_array($filter[$field], $document[$field] ?? [])) return false;
            }
            return true;
        });
        return new class(array_values($documents)) implements IteratorAggregate {
            public function __construct(private array $documents) {}
            public function getIterator(): Traversable { return new ArrayIterator($this->documents); }
            public function toArray() { return $this->documents; }
        };
    }
}
$parentId = new MongoDB\BSON\ObjectId();
$objectiveId = new MongoDB\BSON\ObjectId();
$siblingId = new MongoDB\BSON\ObjectId();
foreach (['activities', 'projects', 'proposals', 'conferences', 'infrastructures', 'deadlines'] as $collection) $osiris->$collection = new InitiativePageCollection();
$osiris->activities->insertOne(['name' => 'Objective publication', 'type' => 'publication', 'initiatives' => [$parentId], 'objectives' => [$objectiveId], 'topics' => ['climate'], 'year' => 2026]);
$osiris->activities->insertOne(['name' => 'Sibling activity', 'type' => 'other', 'initiatives' => [$parentId], 'objectives' => [$siblingId], 'topics' => ['water'], 'year' => 2025]);
$osiris->conferences->insertOne(['title' => 'Climate workshop', 'initiatives' => [$parentId], 'objectives' => [$objectiveId], 'start' => '2026-11-01']);
$osiris->topics->insertOne(['id' => 'climate', 'name' => 'Climate', 'color' => '#008899']);
$osiris->topics->insertOne(['id' => 'water', 'name' => 'Water', 'color' => '#008899']);
$parentContents = Initiatives::linkedContents($osiris, $Settings, ['initiatives' => $parentId]);
check(count($parentContents['publications']) === 1 && count($parentContents['activities']) === 1 && count($parentContents['events']) === 1, 'An initiative includes content from both objectives and partitions publications from other activities.');
check(isset($osiris->projects->lastFilter['$or']) && $osiris->infrastructures->lastFilter['persons.user'] === 'reviewer', 'Project and infrastructure queries retain ownership restrictions without global view permission.');
$ownContents = Initiatives::linkedContents($osiris, $Settings, ['objectives' => $objectiveId]);
check(count($ownContents['publications']) === 1 && count($ownContents['activities']) === 0 && count($ownContents['events']) === 1, 'An objective includes only content explicitly linked to that objective.');
check(array_column(Initiatives::contentTopics($osiris, $ownContents), 'id') === ['climate'], 'Objective topics derive from its own linked content, excluding sibling topics.');
check(count(Initiatives::contentTopics($osiris, $parentContents)) === 2, 'The parent topic overview combines linked content without duplicate topics.');
$settingsSpy = new class {
    public array $activityFilter = [];
    public function getActivityFilter($filter) { $this->activityFilter = $filter; return $filter + ['restricted' => true]; }
    public function hasPermission($permission) { return false; }
    public function featureEnabled($feature) { return false; }
};
$restricted = Initiatives::linkedContents($osiris, $settingsSpy, ['objectives' => $objectiveId]);
check($settingsSpy->activityFilter === ['objectives' => $objectiveId] && $osiris->activities->lastFilter['restricted'] === true && $restricted['projects'] === [], 'Activity permissions receive the association scope and disabled features are omitted.');
$sorted = Initiatives::sortCurrentFirst([['name' => 'Archived', 'inactive' => true], ['name' => 'Expired', 'end_date' => '2000-01-01'], ['name' => 'Current']]);
check($sorted[0]['name'] === 'Current', 'Current entries appear before expired and archived entries.');
$initiative = ['_id' => $parentId, 'name' => 'Mission overview'];
$objective = ['_id' => $objectiveId, 'initiative' => $parentId, 'name' => 'Objective overview', 'code' => '26-01', 'description' => 'Objective description', 'type' => 'objective'];
// Keep the shared page includes; only the already loaded vocabulary bootstrap is removed.
$view = str_replace("require_once BASEPATH . '/php/Vocabulary.php';", '', file_get_contents(BASEPATH . '/pages/initiatives/objective.php'));
$Settings->permissions = ['initiatives.view'];
ob_start(); eval('?>' . $view); $html = ob_get_clean();
check(str_contains($html, 'Objective publication') && str_contains($html, 'Climate workshop') && !str_contains($html, 'Sibling activity') && !str_contains($html, '>Water<'), 'The real objective page renders its scoped content and topics without sibling data.');
check(str_contains($html, '/initiatives/view/' . $parentId . '#objectives') && !str_contains($html, '#edit-objective-'), 'Objective pages provide parent navigation and hide edit actions for readers.');
check(str_contains($html, "class='topic-pill ") && !str_contains($html, 'data-initiative-tab="topics"') && !str_contains($html, 'data-initiative-tab="general"'), 'Topics use the existing widget and overview metadata stays outside content tabs.');
aborts(fn() => Route::$routes['GET /initiatives/objectives/view/(.*)'][0]('not-an-id'), 404);
$Settings->permissions = [];
aborts(fn() => Route::$routes['GET /initiatives/objectives/view/(.*)'][0]((string) $objectiveId), 403);
ob_end_clean(); echo $baselineOutput; echo 'Initiative page checks passed (' . ($checks - $baselineChecks) . " checks).\n";
