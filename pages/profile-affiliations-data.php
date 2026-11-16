<?php
// Explicit affiliations of the profile owner; activity links do not imply membership.
$affiliationToday = date('Y-m-d');
$affiliationTopics = $Settings->featureEnabled('topics') && $active('topics') ? DB::doc2Arr($scientist['topics'] ?? []) : [];
$affiliationUnits = ['current' => [], 'upcoming' => [], 'past' => []];
$affiliationInitiatives = ['current' => [], 'upcoming' => [], 'past' => []];
$affiliationDates = [strtotime($affiliationToday)];
$affiliationTimeline = [];
$affiliationDate = static function ($value): string {
    if (!is_string($value) || $value === '') return '';
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $date && $date->format('Y-m-d') === $value ? $value : '';
};
$unitMemberships = DB::doc2Arr($scientist['units'] ?? []);
$recordedUnits = [];
foreach ($unitMemberships as $membership) {
    $start = $affiliationDate($membership['start'] ?? null);
    $end = $affiliationDate($membership['end'] ?? null);
    if ($start <= $affiliationToday && ($end === '' || $end >= $affiliationToday)) $recordedUnits[] = $membership['unit'] ?? '';
}
// current_units also contains ancestors without a current direct membership record.

foreach (DB::doc2Arr($scientist['current_units'] ?? []) as $unitId) {
    if (is_string($unitId) && !in_array($unitId, $recordedUnits, true)) $unitMemberships[] = ['unit' => $unitId, 'implicit' => true];
}
foreach ($unitMemberships as $membership) {
    $membership = DB::doc2Arr($membership);
    if (!is_string($membership['unit'] ?? null) || $membership['unit'] === '') continue;
    $unitId = $membership['unit'];
    $group = $Groups->getGroup($unitId);
    $start = $affiliationDate($membership['start'] ?? null);
    $end = $affiliationDate($membership['end'] ?? null);
    $state = $end !== '' && $end < $affiliationToday ? 'past' : ($start > $affiliationToday ? 'upcoming' : 'current');
    $parents = $Groups->getParents($unitId);
    array_pop($parents);
    $entry = [
        'id' => $unitId, 'name' => strip_tags($Groups->getName($unitId)),
        'parents' => array_map(fn($id) => strip_tags($Groups->getName($id)), $parents),
        'start' => $start, 'end' => $end, 'state' => $state, 'implicit' => !empty($membership['implicit']),
        'lead' => $state === 'current' && in_array($user, DB::doc2Arr($group['head'] ?? []), true),
        'scientific' => !empty($membership['scientific']),
    ];
    $affiliationUnits[$state][] = $entry;
    if ($start !== '' && ($end === '' || $end >= $start)) {
        $entry['from'] = strtotime($start);
        $entry['to'] = strtotime($end ?: max($start, $affiliationToday));
        $affiliationTimeline[] = $entry;
        $affiliationDates[] = $entry['from'];
        $affiliationDates[] = $entry['to'];
    }
}
foreach ($affiliationUnits as &$entries) usort($entries, fn($a, $b) => strcmp($b['start'], $a['start']) ?: strnatcasecmp($a['name'], $b['name']));
unset($entries);
usort($affiliationTimeline, fn($a, $b) => $a['from'] <=> $b['from']);
$affiliationAxisStart = min($affiliationDates);
$affiliationAxisEnd = max(max($affiliationDates), $affiliationAxisStart + 86400);

if ($Settings->featureEnabled('initiatives')) {
    require_once BASEPATH . '/php/Initiatives.php';
    if (Initiatives::canView($Settings)) {
        $viewer = $osiris->persons->findOne(['username' => $_SESSION['username']]);
        $initiativeFields = Initiatives::visibleFields(Initiatives::fields($osiris), DB::doc2Arr($viewer['roles'] ?? []));
        if (array_intersect(['persons', 'persons-role'], array_column($initiativeFields, 'id'))) {
            require_once BASEPATH . '/php/Vocabulary.php';
            $affiliationVocabulary = new Vocabulary();
            foreach ($osiris->initiatives->find(['persons.user' => $user]) as $initiative) {
                $initiative = DB::doc2Arr($initiative);
                $roles = [];
                foreach ($initiative['persons'] ?? [] as $person) {
                    if (($person['user'] ?? null) !== $user) continue;
                    $role = $person['role'] ?? 'member';
                    $roles[] = $affiliationVocabulary->getValue('persons-role', $role, $role);
                }
                if (!$roles) continue;
                $start = valueFromDateArray($initiative['start_date'] ?? $initiative['start'] ?? null) ?? '';
                $state = !empty($initiative['inactive']) || Initiatives::isExpired($initiative, $affiliationToday) ? 'past' : ($start > $affiliationToday ? 'upcoming' : 'current');
                $initiative['affiliation_roles'] = array_unique($roles);
                $initiative['affiliation_title'] = array_intersect(['title', 'title-loc'], array_column($initiativeFields, 'id')) ? Initiatives::text($initiative['title'] ?? '') : '';
                $affiliationInitiatives[$state][] = $initiative;
            }
            foreach ($affiliationInitiatives as &$entries) $entries = Initiatives::sortCurrentFirst($entries);
            unset($entries);
        }
    }
}
$hasAffiliations = $affiliationTopics || array_filter($affiliationUnits) || array_filter($affiliationInitiatives);
