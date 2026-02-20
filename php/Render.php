<?php
include_once 'init.php';
function renderActivities($filter = [])
{
    // global $Groups;
    global $Settings;
    $Format = new Document(true);

    $renderLang = $Settings->get('render_language', lang('en', 'de'));
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

        $DB->db->activities->updateOne(
            ['_id' => $id],
            ['$set' => $values]
        );
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


function renderAuthorUnits($doc, $old_doc = [])
{
    global $Groups;
    $DB = new DB;
    // Roles that may exist in different activity types
    $roles = ['authors', 'editors', 'supervisors', 'persons'];
    // If none of the roles exist, nothing to do
    $hasAny = false;
    foreach ($roles as $r) {
        if (!empty($doc[$r])) {
            $hasAny = true;
            break;
        }
    }
    if (!$hasAny) return $doc;

    // Ensure start_date is available (needed for time-filtering units)
    if (!isset($doc['start_date']) && isset($old_doc['start_date'])) {
        $doc['start_date'] = $old_doc['start_date'];
    }
    if (!isset($doc['start_date'])) {
        $doc = renderDates($doc);
    }
    if (!isset($doc['start_date'])) {
        $doc['start_date'] = '1970-01-01';
    }
    $startdate = strtotime($doc['start_date']);

    // Helper: get person's units active at $startdate (scientific only)
    $getUnitsForUserAtDate = function ($user) use ($DB, $startdate) {
        $person = $DB->getPerson($user);
        if (empty($person['units'])) return [];

        $u = DB::doc2Arr($person['units']);

        $u = array_filter($u, function ($unit) use ($startdate) {
            if (!($unit['scientific'] ?? false)) return false; // scientific only
            if (empty($unit['start'])) return true;            // unknown start => keep
            $s = strtotime($unit['start']);
            $e = empty($unit['end']) ? null : strtotime($unit['end']);
            return $s <= $startdate && ($e === null || $e >= $startdate);
        });

        $u = array_column($u, 'unit');
        return array_values(array_unique($u));
    };

    // Helper: index old role array by user for manual-flag carry-over
    $indexOldByUser = function ($arr) {
        $idx = [];
        foreach ($arr as $item) {
            if (empty($item['user'])) continue;
            $idx[$item['user']] = $item;
        }
        return $idx;
    };

    $allUnits = [];

    foreach ($roles as $role) {
        if (empty($doc[$role])) continue;

        $current = DB::doc2Arr($doc[$role]);
        $oldIdx  = $indexOldByUser($old_doc[$role] ?? []);
        foreach ($current as $i => $author) {
            // Consistent affiliation behavior:
            // - authors: only if aoi==true 
            // - editors/supervisors: currently you also require aoi==true
            // - persons: all aoi
            if ($role !== 'persons' && !($author['aoi'] ?? false)) {
                continue;
            }
            if (empty($author['user'])) continue;

            $user = $author['user'];

            // Respect manual units:
            // - if current says manually => keep
            // - OR if old had manually => keep (prevents accidental overwrite)
            $manualNow = ($author['manually'] ?? false) ? true : false;
            $manualOld = ($oldIdx[$user]['manually'] ?? false) ? true : false;
            if ($manualNow || $manualOld) {
                $kept = DB::doc2Arr($author['units'] ?? []);
                $current[$i]['units'] = $kept;
                $allUnits = array_merge($allUnits, $kept);
                continue;
            }

            // Auto-assign units from person profile at the activity date
            $u = $getUnitsForUserAtDate($user);

            $current[$i]['units'] = $u;
            $allUnits = array_merge($allUnits, $u);
        }
        $doc[$role] = $current;
    }

    // Build global units list (including parent units)
    $allUnits = array_values(array_unique($allUnits));

    foreach ($allUnits as $unit) {
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

function build_person_search_text(array $p): string
{
    $parts = [];
    if (!empty($p['last'])) $parts[] = $p['last'];
    if (!empty($p['first'])) $parts[] = $p['first'];

    // Alternative names / aliases (can be array or string)
    if (!empty($p['names'] ?? [])) {
        foreach ($p['names'] as $n) {
            if (!empty($n) && is_string($n)) $parts[] = $n;
        }
    }

    // Optional: add extra fields if they exist in your schema
    if ($p['username']) $parts[] = $p['username'];
    if (isset($p['orcid'])) $parts[] = $p['orcid'];
    if (isset($p['mail'])) $parts[] = $p['mail'];

    // Join, normalize whitespace, lowercase
    $text = implode(' ', $parts);
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim(mb_strtolower($text));

    return $text;
}
