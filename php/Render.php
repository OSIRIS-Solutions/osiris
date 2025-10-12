<?php
include_once 'init.php';
function renderActivities($filter = [])
{
    global $Groups;
    $Format = new Document(true);
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

        // $depts = $Groups->getDeptFromAuthors($doc['authors']);

        $Format->usecase = 'print';
        $f = $Format->format();
        $Format->usecase = 'web';
        $web = $Format->formatShort();

        $Format->usecase = 'portal';
        $portfolio = $Format->formatPortfolio();

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
        $values['affiliated'] = !empty($aoi_authors);
        $values['affiliated_positions'] = $Format->getAffiliationTypes();
        $values['cooperative'] = $Format->getCooperationType($values['affiliated_positions'], $doc['units'] ?? []);
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

function renderAuthorUnits($doc, $old_doc = [], $author_key = 'authors')
{
    global $Groups;
    if ($author_key == 'authors' || $author_key == 'editors') {
        // check both authors and editors
        if (!isset($doc['authors']) && !isset($doc['editors'])) {
            return $doc; // no authors or editors to process
        }
    } else if (!isset($doc[$author_key])) return $doc;

    $DB = new DB;
    $osiris = $DB->db;

    $units = [];
    // make sure that start_date is set because we need it to filter units
    if (!isset($doc['start_date']) && isset($old_doc['start_date'])) {
        $doc['start_date'] = $old_doc['start_date'];
    }
    if (!isset($doc['start_date'])) {
        $doc = renderDates($doc);
    }
    // if it still does not exist, use start of all times
    if (!isset($doc['start_date'])) {
        $doc['start_date'] = '1970-01-01';
    }
    $startdate = strtotime($doc['start_date']);

    $authors = $doc[$author_key] ?? [];
    $old = $old_doc[$author_key] ?? [];

    // check if old authors are equal to new authors
    if (count($authors) == count($old) && $authors == $old) {
        return $doc;
    }

    // add user as key to authors
    // $old = array_column($old, 'units', 'user');

    foreach ($authors as $i => $author) {
        if ($author_key == 'authors' && (!($author['aoi'] ?? false))) continue;
        // check if author has been manually set, if so, do not update units
        if ($author['manually'] ?? false) {
            $units = array_merge($units, DB::doc2Arr($authors[$i]['units']));
            continue;
        }
        // $old_author = $old[$user] ?? [];
        // if (isset($author['manually']) && $author['manually']) {
        //     $old_author = DB::doc2Arr($author);
        // }
        // if (!empty($old_author) && $author['manually']) {
        //     $authors[$i]['units'] = $old_author['units'] ?? [];
        //     $units = array_merge($units, $authors[$i]['units']);
        //     continue;
        // }
        if (!isset($author['user'])) continue; // skip if no user
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
            $authors[$i]['units'] = $u;
            $units = array_merge($units, $u);
        }
    }

    // Check for editors if the key is 'authors'
    if ($author_key == 'authors') {
        $editors = $doc['editors'] ?? [];
        foreach ($editors as $i => $editor) {
            if (!isset($editor['user']) || !($editor['aoi'] ?? false)) continue; // skip if no user or not an aoi editor
            $user = $editor['user'];
            $person = $DB->getPerson($user);

            if ($editor['manually'] ?? false) {
                $units = array_merge($units, DB::doc2Arr($editors[$i]['units']));
                continue;
            }
            if (isset($person['units']) && !empty($person['units'])) {
                $u = DB::doc2Arr($person['units']);
                // filter units that have been active at the time of activity
                $u = array_filter($u, function ($unit) use ($startdate) {
                    if (!$unit['scientific']) return false; // we are only interested in scientific units
                    if (empty($unit['start'])) return true; // we have basically no idea when this unit was active
                    return strtotime($unit['start']) <= $startdate && (empty($unit['end']) || strtotime($unit['end']) >= $startdate);
                });
                $u = array_column($u, 'unit');
                $editors[$i]['units'] = $u;
                $units = array_merge($units, $u);
            }
        }
        $doc['editors'] = $editors;
    }

    $units = array_unique($units);
    foreach ($units as $unit) {
        $units = array_merge($units, $Groups->getParents($unit, true));
    }
    $units = array_unique($units);
    $doc['units'] = array_values($units);
    $doc[$author_key] = $authors;
    return $doc;
}


function renderAuthorUnitsMany($filter = [])
{
    $DB = new DB;
    $cursor = $DB->db->activities->find($filter, ['projection' => ['authors' => 1, 'editors' => 1, 'units' => 1, 'start_date' => 1, 'subtype' => 1]]);
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
