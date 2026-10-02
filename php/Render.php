<?php
include_once 'init.php';
function renderActivities($filter = [], $return_updated = false)
{
    // global $Groups;
    global $Settings;
    $Format = new Document(true);
    $updated = 0;
    $renderLang = $Settings->get('render_language', lang('common.this_language'));
    $DB = new DB;
    $cursor = $DB->db->activities->find($filter);
    $rendered = [
        'print' => '',
        'web' => '',
        'icon' => '',
        'type' => '',
    ];
    foreach ($cursor as $doc) {
        $id = $doc['_id'];
        $Format->setDocument($doc);
        $Format->usecase = 'web';
        $doc['authors'] = DB::doc2Arr($doc['authors'] ?? []);

        $Format->usecase = 'print';
        $f = $Format->format($renderLang);
        $Format->usecase = 'web';
        $web = $Format->formatShort($renderLang);

        $Format->usecase = 'portal';
        $portfolio = $Format->formatPortfolio($renderLang);

        $rendered = [
            'print' => $f,
            'plain' => strip_tags($f),
            'portfolio' => $portfolio,
            'web' => $web,
            'icon' => trim($Format->activity_icon()),
            'type' => $Format->activity_type(),
            'subtype' => $Format->activity_subtype(),
            'title' => $Format->getTitle(),
            'subtitle' => $Format->getSubtitle(),
            'authors' => $Format->getAuthors('authors'),
            'editors' => $Format->getAuthors('editors'),
            'supervisors' => $Format->getAuthors('supervisors'),
            'users' => $Format->getUsers(false),
            'affiliated_users' => $Format->getUsers(true),
        ];
        $values = ['rendered' => $rendered];

        $values['start_date'] = valueFromDateArray($doc['start'] ?? $doc);
        if (array_key_exists('end', DB::doc2Arr($doc)) && is_null($doc['end'])) {
            $end = null;
        } else {
            $end = valueFromDateArray($doc['end'] ?? $doc['start'] ?? $doc);
        }
        $values['end_date'] = $end;

        if ($doc['type'] == 'publication' && isset($doc['journal'])) {
            // update impact if necessary
            $if = $DB->get_impact($doc);
            if (!empty($if)) {
                $values['impact'] = $if;
            } else {
                $values['impact'] = null;
            }
            $values['metrics'] = $DB->get_metrics($doc);
            $values['quartile'] = $values['metrics']['quartile'] ?? null;
        }
        $aoi_authors = array_filter($doc['authors'], function ($a) {
            return $a['aoi'] ?? false;
        });
        if (empty($aoi_authors) && isset($doc['editors'])) {
            $aoi_authors = array_filter(DB::doc2Arr($doc['editors']), function ($a) {
                return $a['aoi'] ?? false;
            });
        }
        if (empty($aoi_authors) && isset($doc['supervisors'])) {
            $aoi_authors = array_filter(DB::doc2Arr($doc['supervisors']), function ($a) {
                return $a['aoi'] ?? false;
            });
        }
        $values['affiliated'] = !empty($aoi_authors);
        $values['affiliated_positions'] = $Format->getAffiliationTypes('authors');
        $values['cooperative'] = $Format->getCooperationType($values['affiliated_positions'], $doc['units'] ?? []);

        $active = false;
        // if (!isset($doc['year'])) {dump($doc, true); die;}
        $sm = intval($doc['month'] ?? 0);
        $sy = intval($doc['year'] ?? 0);
        // die;
        $em = $sm;
        $ey = $sy;

        if (isset($doc['end']) && !empty($doc['end'])) {
            $em = $doc['end']['month'];
            $ey = $doc['end']['year'];
        } elseif (in_array($doc['subtype'], $Settings->continuousTypes) && empty($doc['end'])) {
            $em = CURRENTMONTH;
            $ey = CURRENTYEAR;
            $active = true;
        }
        $sq = $sy . 'Q' . ceil($sm / 3);
        $eq = $ey . 'Q' . ceil($em / 3);
        $quarter = $sq;
        if ($active) {
            $quarter .= ' - today';
        } elseif ($sq != $eq) {
            if ($sy == $ey) {
                $quarter .= ' - ' . 'Q' . ceil($em / 3);
            } else {
                $quarter .= ' - ' . $eq;
            }
        }
        $values['rendered']['quarter'] = $quarter;
        $values['rendered']['active'] = $active;

        $update = $DB->db->activities->updateOne(
            ['_id' => $id],
            ['$set' => $values]
        );
        if ($update->getModifiedCount() > 0) {
            $updated++;
        }
    }
    if ($return_updated) {
        return $updated;
    }
    // return last element in case that only one id has been rendered
    return $rendered;
}

function renderDates($doc)
{
    $doc['start_date'] = valueFromDateArray($doc['start'] ?? $doc);
    if (array_key_exists('end', DB::doc2Arr($doc)) && is_null($doc['end'])) {
        $end = null;
    } else {
        $end = valueFromDateArray($doc['end'] ?? $doc['start'] ?? $doc);
    }
    $doc['end_date'] = $end;
    return $doc;
}


/**
 * Materialize the effective current unit memberships of persons.
 *
 * The historical `units` assignments remain the source of truth. An assignment
 * is active on the reference date when its start is empty or not later than the
 * reference date and its end is empty or not earlier than the reference date.
 * Every active direct unit is expanded to include all parent units up to and
 * including the institute root.
 *
 * @param array $filter Optional MongoDB filter selecting persons to update.
 * @return int Number of person documents whose current_units field changed.
 */
function renderCurrentUnits(array $filter = []): int
{
    global $Groups;
    $today = date('Y-m-d');
    $DB = new DB;
    $cursor = $DB->db->persons->find(
        $filter,
        ['projection' => ['units' => 1, 'current_units' => 1]]
    );
    $updated = 0;

    foreach ($cursor as $person) {
        $directUnits = [];
        foreach (DB::doc2Arr($person['units'] ?? []) as $assignment) {
            $assignment = DB::doc2Arr($assignment);
            $unit = $assignment['unit'] ?? null;
            if (!is_string($unit) || $unit === '') {
                continue;
            }

            $start = $assignment['start'] ?? null;
            $end = $assignment['end'] ?? null;
            $startsInFuture = $start !== null && $start !== '' && $start > $today;
            $endedInPast = $end !== null && $end !== '' && $end < $today;
            if ($startsInFuture || $endedInPast) {
                continue;
            }
            $directUnits[] = $unit;
        }

        $currentUnits = [];
        foreach (array_values(array_unique($directUnits)) as $unit) {
            $currentUnits = array_merge($currentUnits, $Groups->getParents($unit, true));
        }
        $currentUnits = array_values(array_unique($currentUnits));
        sort($currentUnits, SORT_STRING);

        $previousUnits = array_values(array_unique(
            DB::doc2Arr($person['current_units'] ?? [])
        ));
        sort($previousUnits, SORT_STRING);
        if ($currentUnits === $previousUnits) {
            continue;
        }

        $result = $DB->db->persons->updateOne(
            ['_id' => $person['_id']],
            ['$set' => ['current_units' => $currentUnits]]
        );
        if ($result->getModifiedCount() > 0) {
            $updated++;
        }
    }

    return $updated;
}


function renderAuthorUnits($doc, $old_doc = [])
{
    global $Groups;
    $DB = new DB;
    $doc = DB::doc2Arr($doc);
    $old_doc = DB::doc2Arr($old_doc);
    $roles = ['authors', 'editors', 'supervisors', 'persons'];

    // A missing role is unchanged; an explicitly empty role removes its members.
    $hasRoles = false;
    foreach ($roles as $role) {
        if (!array_key_exists($role, $doc) && array_key_exists($role, $old_doc)) {
            $doc[$role] = $old_doc[$role];
        }
        $hasRoles = $hasRoles || array_key_exists($role, $doc);
    }
    if (!$hasRoles) return $doc;

    // Raw date changes take precedence over a previously materialized start_date.
    $effective = array_replace($old_doc, $doc);
    if (array_key_exists('start', $doc)) {
        $doc['start_date'] = valueFromDateArray($doc['start'] ?? $effective);
    } elseif (array_intersect(['year', 'month', 'day'], array_keys($doc))) {
        $doc['start_date'] = valueFromDateArray($effective);
    } elseif (!array_key_exists('start_date', $doc)) {
        $doc['start_date'] = $old_doc['start_date']
            ?? valueFromDateArray($effective['start'] ?? $effective);
    }
    $startdate = empty($doc['start_date']) ? false : strtotime($doc['start_date']);

    $getUnitsForUserAtDate = function ($user) use ($DB, $startdate) {
        // getPerson(null) defaults to the logged-in user. Never use that fallback here.
        if (empty($user) || $startdate === false) return [];
        $person = $DB->getPerson($user);
        $units = [];
        foreach (DB::doc2Arr($person['units'] ?? []) as $unit) {
            if (!($unit['scientific'] ?? false) || empty($unit['unit'])) continue;
            $start = empty($unit['start']) ? null : strtotime($unit['start']);
            $end = empty($unit['end']) ? null : strtotime($unit['end']);
            // Missing bounds are open; malformed bounds are not valid memberships.
            if ($start === false || $end === false) continue;
            if (($start === null || $start <= $startdate)
                && ($end === null || $end >= $startdate)) {
                $units[] = $unit['unit'];
            }
        }
        return array_values(array_unique($units));
    };

    $nameKey = function ($person) {
        return json_encode([$person['last'] ?? '', $person['first'] ?? '']);
    };
    // Retain all matches so ambiguous names or accounts cannot overwrite each other.
    $indexPeople = function ($people) use ($nameKey) {
        $index = ['users' => [], 'names' => []];
        foreach ($people as $person) {
            $person = DB::doc2Arr($person);
            if (!empty($person['user'])) $index['users'][$person['user']][] = $person;
            if (!empty($person['last']) || !empty($person['first'])) {
                $index['names'][$nameKey($person)][] = $person;
            }
        }
        return $index;
    };

    $allUnits = [];
    foreach ($roles as $role) {
        if (!array_key_exists($role, $doc)) continue;
        $current = DB::doc2Arr($doc[$role] ?? []);
        $oldIdx = $indexPeople(DB::doc2Arr($old_doc[$role] ?? []));
        $currentIdx = $indexPeople($current);
        foreach ($current as $i => $author) {
            $author = DB::doc2Arr($author);
            $user = $author['user'] ?? null;
            $name = $nameKey($author);
            $previous = [];
            if (!empty($user) && count($oldIdx['users'][$user] ?? []) === 1
                && count($currentIdx['users'][$user] ?? []) === 1) {
                $previous = $oldIdx['users'][$user][0];
            } elseif (count($oldIdx['names'][$name] ?? []) === 1
                && count($currentIdx['names'][$name] ?? []) === 1) {
                $candidate = $oldIdx['names'][$name][0];
                // A name match must not transfer assignments between different accounts.
                if (empty($user) || empty($candidate['user']) || $user === $candidate['user']) {
                    $previous = $candidate;
                }
            }

            if (array_key_exists('manually', $author)) {
                // Explicit false restores automatic assignment; an empty manual list is valid.
                $manual = (bool) $author['manually'];
                $units = DB::doc2Arr($author['units'] ?? []);
            } else {
                $manual = (bool) ($previous['manually'] ?? false);
                $units = DB::doc2Arr($previous['units'] ?? []);
            }
            $author['manually'] = $manual;
            if (!$manual) {
                $units = ($role === 'persons' || ($author['aoi'] ?? false))
                    ? $getUnitsForUserAtDate($user) : [];
            }
            $author['units'] = array_values(array_unique($units));
            $current[$i] = $author;
            if ($role === 'persons' || ($author['aoi'] ?? false)) {
                $allUnits = array_merge($allUnits, $author['units']);
            }
        }
        $doc[$role] = $current;
    }

    $directUnits = array_values(array_unique($allUnits));
    foreach ($directUnits as $unit) {
        $allUnits = array_merge($allUnits, $Groups->getParents($unit, true));
    }
    $doc['units'] = array_values(array_unique($allUnits));
    return $doc;
}


function renderAuthorUnitsMany($filter = [])
{
    $DB = new DB;
    $cursor = $DB->db->activities->find($filter, ['projection' => ['authors' => 1, 'editors' => 1, 'supervisors' => 1, 'units' => 1, 'start_date' => 1, 'subtype' => 1]]);
    foreach ($cursor as $doc) {
        $doc = renderAuthorUnits($doc);
        $DB->db->activities->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => $doc]
        );
    }
}
function renderAuthorUnitsProjects($filter = [])
{
    $DB = new DB;
    $cursor = $DB->db->projects->find($filter, ['projection' => ['persons' => 1, 'units' => 1, 'start_date' => 1]]);
    foreach ($cursor as $doc) {
        $doc = renderAuthorUnits($doc, [], 'persons');
        $DB->db->projects->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => ['units' => $doc['units'] ?? []]]
        );
    }
    $cursor = $DB->db->proposals->find($filter, ['projection' => ['persons' => 1, 'units' => 1, 'start_date' => 1]]);
    foreach ($cursor as $doc) {
        $doc = renderAuthorUnits($doc, [], 'persons');
        $DB->db->proposals->updateOne(
            ['_id' => $doc['_id']],
            ['$set' => ['units' => $doc['units'] ?? []]]
        );
    }
}

function renderProject($doc, $col = 'projects', $id = null)
{
    global $Groups;
    $DB = new DB;
    $project = [];
    if (isset($id)) {
        $project = $DB->db->$col->findOne(
            ['_id' => $id],
            ['projection' => ['start' => 1, 'end' => 1, 'start_date' => 1, 'end_date' => 1, 'start_proposed' => 1, 'end_proposed' => 1]]
        );
    }
    if (isset($doc['start'])) {
        $doc['start_date'] = valueFromDateArray($doc['start']);
    } elseif (isset($doc['start_proposed'])) {
        $doc['start_date'] = $doc['start_proposed'];
    } elseif (isset($project['start'])) {
        $doc['start_date'] = valueFromDateArray($project['start']);
    } elseif (isset($project['start_proposed'])) {
        $doc['start_date'] = $project['start_proposed'];
    }
    if (isset($doc['end'])) {
        $doc['end_date'] = valueFromDateArray($doc['end']);
    } elseif (isset($doc['end_proposed'])) {
        $doc['end_date'] = $doc['end_proposed'];
    } elseif (isset($project['end'])) {
        $doc['end_date'] = valueFromDateArray($project['end']);
    } elseif (isset($project['end_proposed'])) {
        $doc['end_date'] = $project['end_proposed'];
    }
    if (isset($doc['persons'])) {
        if (isset($doc['start_date']) && $id == null) {
            $units = [];
            $startdate = strtotime($doc['start_date']);
            // initialize units
            foreach ($doc['persons'] as $i => $author) {
                $user = $author['user'];
                $person = $DB->getPerson($user);
                if (isset($person['units']) && !empty($person['units'])) {
                    $u = DB::doc2Arr($person['units']);
                    // filter units that have been active at the time of activity
                    $u = array_filter($u, function ($unit) use ($startdate) {
                        if (!$unit['scientific']) return false; // we are only interested in scientific units
                        if (empty($unit['start'])) return true; // we have basically no idea when this unit was active
                        return strtotime($unit['start']) <= $startdate && (empty($unit['end']) || strtotime($unit['end']) >= $startdate);
                    });
                    $u = array_column($u, 'unit');
                    $doc['persons'][$i]['units'] = $u;
                    $units = array_merge($units, $u);
                }
            }
        } else {
            $units = flatten(array_column($doc['persons'], 'units'));
        }
        $units = array_unique($units);
        foreach ($units as $unit) {
            $units = array_merge($units, $Groups->getParents($unit, true));
        }
        $units = array_unique($units);
        $doc['units'] = array_values($units);
        // $doc = renderAuthorUnits($doc, [], 'persons');
    }
    return $doc;
}
