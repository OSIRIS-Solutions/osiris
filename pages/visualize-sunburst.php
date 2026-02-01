<?php

/**
 * Page to visualize activities of units and users in a sunburst
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link /visualize/sunburst
 *
 * @package OSIRIS
 * @since 1.0 
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$filter = [];

$links = array();

$time = $_GET['time'] ?? '';
// 12,2017,12,2024
if (!empty($time) && $time != 'undefined') {
    if (!is_array($time)) {
        $time = explode(',', $time);
    }
    if (count($time) !== 4 || array_sum($time) <= 0) {
        // echo "Time is invalid.";
        $time = ['', '', '', ''];
    } else {
        $startmonth = intval($time[0]);
        $startyear = intval($time[1]);
        $endmonth = intval($time[2]);
        $endyear = intval($time[3]);

        $filter['$and'] = [
            ['$or' => [['$and' => [['year' => $startyear], ['month' => ['$gte' => $startmonth]]]], ['$and' => [['year' => ['$gt' => $startyear]]]]]],
            ['$or' => [['$and' => [['year' => $endyear], ['month' => ['$lte' => $endmonth]]]], ['$and' => [['year' => ['$lt' => $endyear]]]]]],
        ];

        $links[] = 'time=' . implode(',', $time);
    }
} else {
    $time = ['', '', '', ''];
}

if (!isset($_GET['epub'])) {
    $filter['epub'] = ['$ne' => true];
}

$filter_type = $_GET['type'] ?? 'publication';
if (!empty($filter_type) && $filter_type != 'undefined') {
    $filter['type'] = $filter_type;
    $links[] = 'type=' . $filter_type;
}


// generate graph json

$departments = [];
$flare = [];

$links = implode('&', $links);

$raw = $osiris->activities->aggregate([
    ['$match' => $filter],
    ['$project' => ['authors' => 1]],
    ['$unwind' => '$authors'],
    ['$match' => ['authors.aoi' => ['$in' => [1, true, '1', 'true']]]],
    [
        '$group' => [
            '_id' => '$authors.user',
            'count' => ['$sum' => 1],
        ]
    ]
]);

$activities = [];
foreach ($raw as $row) {
    $activities[$row['_id']] = $row['count'];
}

function updateRecursive(array &$arr, $activities, $parentusers = [])
{
    global $osiris, $links;
    foreach ($arr as &$val) {
        $users = $osiris->persons->find(['units.unit' => $val['id']])->toArray();
        $usernames = [];
        // uncomment if users that belong to parent group should be skipped
        // $usernames = array_column($users, 'username');
        if (!empty($val['children']))
            updateRecursive($val['children'], $activities, $usernames);
        // get all person usernames associated
        foreach ($users as $u) {
            if (in_array($u['username'], $parentusers)) continue;
            if (!isset($activities[$u['username']])) continue;
            $val['children'][] = [
                'name' => $u['displayname'],
                'id' => $u['username'],
                'value' => $activities[$u['username']],
                'link' => ROOTPATH . "/my-activities?user=" . $u['username'] . '#' . $links
            ];
        }
    }
}

$flare = [$Groups->tree];

updateRecursive($flare, $activities);

?>

<h1>
    <i class="ph-duotone ph-graph" aria-hidden="true"></i>
    <?= lang('Department overview', 'Abteilungs-Übersicht') ?>
</h1>

<div class="dropdown">
    <button class="btn secondary" data-toggle="dropdown" type="button" id="dropdown-1" aria-haspopup="true" aria-expanded="false">
        <i class="ph ph-funnel"></i>
        Filter
        <i class="ph ph-caret-down ml-5" aria-hidden="true"></i>
    </button>
    <div class="dropdown-menu w-400 " aria-labelledby="dropdown-1">
        <form action="" method="get" class="content">


            <div class="form-group">
                <label for="type-select"><?= lang('Time range', 'Zeitspanne') ?></label>

                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><?= lang('From', 'Von') ?></span>
                    </div>
                    <input type="number" name="time[]" class="form-control" placeholder="month" min="1" max="12" step="1" id="from-month" value="<?= $time[0] ?? '' ?>">
                    <input type="number" name="time[]" class="form-control" placeholder="year" min="2000" max="<?= CURRENTYEAR + 1 ?>" step="1" id="from-year" value="<?= $time[1] ?? '' ?>">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><?= lang('to', 'bis') ?></span>
                    </div>
                    <input type="number" name="time[]" class="form-control" placeholder="month" min="1" max="12" step="1" id="to-month" value="<?= $time[2] ?? '' ?>">
                    <input type="number" name="time[]" class="form-control" placeholder="year" min="2000" max="<?= CURRENTYEAR + 1 ?>" step="1" id="to-year" value="<?= $time[3] ?? '' ?>">

                    <div class="input-group-append">
                        <a class="btn" type="button" href="<?= currentGET(['time']) ?>">&times;</a>
                    </div>
                </div>

            </div>
            <div class="form-group">
                <label for="type-select"><?= lang('Activities', 'Aktivitäten') ?></label>
                <select name="type" id="type-select" class="form-control ">
                    <option value=""><?= lang('All types', 'Alle Arten') ?></option>
                    <?php foreach ($Settings->getActivities as $a) { ?>
                        <option value="<?= $a['id'] ?>" <?= $a['id'] == $filter_type ? 'selected' : '' ?>><?= lang($a['name'], $a['name_de'] ?? $a['name']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <div class="custom-checkbox">
                    <input type="checkbox" id="epub" value="1" name="epub" <?= isset($_GET['epub']) ? 'checked' : '' ?>>
                    <label for="epub"><?= lang('Include online ahead of print', 'Inklusive Online ahead of print') ?></label>
                </div>
            </div>

            <button class="btn secondary " type="submit">Select</button>
        </form>
    </div>
</div>


<div id="flare" class="d-flex" style="max-width: 80rem"></div>


<script src="https://d3js.org/d3.v7.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-sunburst.js?v=<?=OSIRIS_BUILD?>"></script>
<script>
    var flare = JSON.parse('<?= json_encode($flare[0]) ?>')
    chart('#flare', flare);
</script>

<?php
if (isset($_GET['verbose'])){
    dump($flare, true);
}
?>