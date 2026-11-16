<?php
// Profile affiliation data and markup, without a live database.
ob_start(); require __DIR__ . '/initiatives.php'; $baselineOutput = ob_get_clean();
ob_start(); $baselineChecks = $checks;
Vocabulary::$roles = null;
class ProfileInitiativeCollection extends MemoryCollection
{
    public array $lastFilter = [];
    public function find(array $filter = [], array $options = []) {
        $this->lastFilter = $filter;
        $records = array_filter($this->documents, function ($document) use ($filter) {
            if (!isset($filter['persons.user'])) return true;
            foreach ($document['persons'] ?? [] as $person) if (($person['user'] ?? null) === $filter['persons.user']) return true;
            return false;
        });
        return new class(array_values($records)) implements IteratorAggregate {
            public function __construct(private array $records) {}
            public function toArray() { return $this->records; }
            public function getIterator(): Traversable { return new ArrayIterator($this->records); }
        };
    }
}
$osiris->initiatives = new ProfileInitiativeCollection();
$osiris->adminGeneral = new MemoryCollection();
$osiris->adminGeneral->insertOne(setupDefinition('initiatives'));
$osiris->topics = new MemoryCollection();
$osiris->topics->insertOne(['id' => 'climate', 'name' => 'Climate dynamics', 'color' => '#008083']);
$osiris->initiatives->insertOne(['name' => ['en' => 'Climate Futures'], 'title' => ['en' => 'Research for resilient climate futures'], 'start_date' => '2021-01-01', 'persons' => [['user' => 'alice', 'role' => 'lead'], ['user' => 'bob', 'role' => 'co-lead']]]);
$osiris->initiatives->insertOne(['name' => 'Archived mission', 'inactive' => true, 'persons' => [['user' => 'alice']]]);
$osiris->initiatives->insertOne(['name' => 'Future mission', 'start_date' => ((int) date('Y') + 1) . '-01-01', 'persons' => [['user' => 'alice']]]);
$osiris->initiatives->insertOne(['name' => 'Unrelated mission', 'persons' => [['user' => 'bob']]]);
$osiris->activities->insertOne(['initiatives' => ['unrelated'], 'rendered' => ['users' => ['alice']]]);
$Groups = new class {
    public function getGroup($id) { return ['head' => $id === 'lab' ? ['alice'] : []]; }
    public function getName($id) { return ['lab' => 'Climate Laboratory', 'old' => 'Ocean Observations', 'next' => 'Future Lab', 'legacy' => 'Shared Services', 'today' => 'Transition Team', 'institute' => 'ANKH Institute'][$id] ?? $id; }
    public function getParents($id) { return ['institute', $id]; }
};
$profile = ['topics' => ['climate'], 'current_units' => ['lab', 'institute'], 'units' => [
    ['unit' => 'legacy'],
    ['unit' => 'lab', 'start' => '2021-01-01', 'end' => null, 'scientific' => true],
    ['unit' => 'old', 'start' => '2016-01-01', 'end' => '2020-12-31'],
    ['unit' => 'next', 'start' => ((int) date('Y') + 1) . '-01-01', 'end' => null],
    ['unit' => 'today', 'start' => '2025-01-01', 'end' => date('Y-m-d')],
]];
$dataSource = preg_replace('/^\s*require_once .*;\R/m', '', file_get_contents(BASEPATH . '/pages/profile-affiliations-data.php'));
$render = function ($scientist, $profileUser = 'alice') use ($dataSource, $Groups, $osiris, $Settings) {
    $user = $profileUser;
    $active = fn($field) => $field === 'topics';
    $currentuser = false;
    eval('?>' . $dataSource);
    $source = file_get_contents(BASEPATH . '/pages/profile.php');
    $start = strpos($source, '<?php if ($hasAffiliations) { ?>');
    $section = substr($source, $start, strpos($source, '<section id="general">', $start) - $start);
    ob_start(); eval('?>' . $section); $html = ob_get_clean();
    return compact('html', 'hasAffiliations', 'affiliationUnits', 'affiliationInitiatives', 'affiliationTimeline');
};
$result = $render($profile);
check(count($result['affiliationUnits']['current']) === 4 && count($result['affiliationUnits']['past']) === 1 && count($result['affiliationUnits']['upcoming']) === 1, 'Current, past, future and undated cached unit affiliations are classified without losing history; end dates include today.');
check(count($result['affiliationTimeline']) === 4, 'Only memberships with known start dates appear on the timeline.');
$currentUnits = array_column($result['affiliationUnits']['current'], null, 'id');
check($currentUnits['institute']['implicit'] && !$currentUnits['legacy']['implicit'] && str_contains($result['html'], 'Derived from subunits') && str_contains($result['html'], 'Dates not specified'), 'Derived parent units are marked explicitly; direct memberships without dates keep the missing-dates label.');
$withPastParent = $profile;
$withPastParent['units'][] = ['unit' => 'institute', 'start' => '2010-01-01', 'end' => '2015-12-31'];
$pastParentResult = $render($withPastParent);
check(array_column($pastParentResult['affiliationUnits']['current'], null, 'id')['institute']['implicit'] && count($pastParentResult['affiliationUnits']['past']) === 2, 'A previous direct parent assignment does not hide its current implicit affiliation.');

check(count($result['affiliationInitiatives']['current']) === 1 && count($result['affiliationInitiatives']['past']) === 1 && count($result['affiliationInitiatives']['upcoming']) === 1, 'Explicit initiative memberships are split into current, archived and upcoming entries.');
check(str_contains($result['html'], 'Climate Futures') && str_contains($result['html'], '>Lead<') && !str_contains($result['html'], 'Unrelated mission') && !str_contains($result['html'], 'Co-Lead'), 'The profile shows the person’s own initiative roles, not roles or associations belonging to other people.');
check(str_contains($result['html'], 'Climate dynamics') && str_contains($result['html'], 'affiliation-timeline') && !str_contains($result['html'], '/user/units/alice'), 'The affiliation tab renders explicit topics and the timeline without unauthorized edit controls.');
if (isset($argv[1])) file_put_contents($argv[1] . '/affiliations.html', str_replace('style="display:none"', '', $result['html']));
$Settings->permissions = [];
check(!str_contains($render($profile)['html'], 'Climate Futures'), 'Initiatives are hidden from viewers without initiative access.');
$Settings->permissions = ['initiatives.view'];
$fields = setupDefinition('initiatives')['fields'];
foreach ($fields as &$field) if ($field['id'] === 'persons-role') $field['props']['roles'] = 'admin';
unset($field);
$osiris->adminGeneral->updateOne(['id' => 'initiative-fields'], ['$set' => ['fields' => $fields]]);
check(!str_contains($render($profile)['html'], 'Climate Futures'), 'Role-restricted initiative membership fields do not leak through the profile.');
check(!$render([], 'nobody')['hasAffiliations'], 'Profiles with no affiliations do not gain an empty navigation tab.');
ob_end_clean(); echo $baselineOutput; echo 'Profile affiliation checks passed (' . ($checks - $baselineChecks) . " checks).\n";
