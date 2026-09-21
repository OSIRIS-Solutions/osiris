<?php

/**
 * Page to see and approve current quarter
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /my-year/<username>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$currentuser = $user == $_SESSION['username'];

$YEAR = intval($_GET['year'] ?? CURRENTYEAR);
$QUARTER = intval($_GET['quarter'] ?? CURRENTQUARTER);

if (isset($_GET['quarter']) && strpos($_GET['quarter'], 'Q') !== false) {
    $temp = explode("Q", $_GET['quarter']);
    $YEAR = intval($temp[0]);
    $QUARTER = intval($temp[1]);
}

$q = $YEAR . "Q" . $QUARTER;


$lastQ = $QUARTER - 1;
$lastY = $YEAR;
if ($lastQ < 1) {
    $lastQ = 4;
    $lastY -= 1;
}
// $lastquarter = $lastY . "Q" . $lastQ;

$nextQ = $QUARTER + 1;
$nextY = $YEAR;
if ($nextQ > 4) {
    $nextQ = 1;
    $nextY += 1;
}
// $nextquarter = $nextY . "Q" . $nextQ;

include_once BASEPATH . "/php/Coins.php";
$Coins = new Coins();

$coins = $Coins->getCoins($user, $YEAR);


$groups = [];
foreach ($Categories->categories as $value) {
    $groups[$value['id']] = [];
}

$timeline = [];
//, 'editors.user' => $user
$filter = [
    'rendered.users' => $user,
    '$or' => [
        [
            "start.year" => array('$lte' => $YEAR),
            '$or' => array(
                ['end.year' => array('$gte' => $YEAR)],
                [
                    'end' => null,
                    'subtype' => ['$in' => $Settings->continuousTypes]
                ]
            )
        ],
        ['year' => $YEAR]
    ]
];
$options = [
    'sort' => ["year" => -1, "month" => -1],
    // 'projection' => ['file' => -1]
];
$cursor = $osiris->activities->find($filter, $options);


$endOfYear = new DateTime("$YEAR-12-31");
$startOfYear = new DateTime("$YEAR-01-01");
foreach ($cursor as $doc) {
    if (!array_key_exists($doc['type'], $groups)) continue;

    // $doc['format'] = $format;
    $groups[$doc['type']][] = $doc;
    $icon = $Format->activity_icon($doc, false);

    $date = getDateTime($doc['start'] ?? $doc);

    // make sure date lies in range
    if ($date < $startOfYear) $date = $startOfYear;

    $starttime = $date->getTimestamp();
    $event = [
        'starting_time' => $starttime,
        'type' => $doc['type'],
        'id' => strval($doc['_id']),
        'title' => e(strip_tags($doc['rendered']['title'])),
    ];
    $timeline[] = $event;
}

if (!$Settings->featureEnabled('coins')) {
    $showcoins = false;
} else {
    $showcoins = ($scientist['show_coins'] ?? 'no');
    if ($showcoins == 'all') {
        $showcoins = true;
    } elseif ($showcoins == 'myself' && $currentuser) {
        $showcoins = true;
    } else {
        $showcoins = false;
    }
}
?>

<style>
    .download-buttons {
        position: absolute;
        top: 1rem;
        right: 1rem;
        z-index: 10;
    }

    #timeline-container {
        position: relative;
    }
</style>

<div class="modal modal-lg" id="coins" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content w-600 mw-full">
            <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>
            <?php
            include BASEPATH . "/components/what-are-coins.php";
            ?>

        </div>
    </div>
</div>

<div class="">

    <div class="row align-items-center">
        <div class="col flex-grow-0">
            <?= $Settings->printProfilePicture($user, 'profile-img') ?>

        </div>
        <div class="col ml-20">
            <h1 class="m-0">
                <?php if ($user == $_SESSION['username']) { ?>
                    <?= lang('common.my_year') ?>
                <?php } else { ?>
                    <?= lang('activities.the_year_of') ?>
                    <a href="<?= ROOTPATH ?>/profile/<?= $user ?>" class="link colorless">
                        <?= $name ?>
                    </a>
                <?php } ?>
            </h1>
            <?php if ($showcoins) { ?>
                <p class="lead m-0">
                    <i class="ph ph-lg ph-coin text-signal"></i>
                    <b id="coin-number"><?= $coins ?></b>
                    Coins in <?= $YEAR ?>
                    <a href='#coins' class="text-muted">
                        <i class="ph ph-question text-muted"></i>
                    </a>
                </p>
            <?php } ?>

            <?php
            $quarter_in_past = false;
            if ($currentuser && $Settings->featureEnabled('quarterly-reporting', true)) {
                $approved = isset($USER['approved']) && in_array($q, DB::doc2Arr($USER['approved']));
                $approval_needed = array();

                $q_end = new DateTime($YEAR . '-' . (3 * $QUARTER) . '-' . ($QUARTER == 1 || $QUARTER == 4 ? 31 : 30) . ' 23:59:59');
                $quarter_in_past = new DateTime() > $q_end;
            ?>

                <?php if (!$quarter_in_past) { ?>
                    <a href="#close-modal" class="btn disabled">
                        <i class="ph ph-seal-question mr-5 text-signal"></i>
                        <?= lang('activities.selected_quarter_is_not_over_yet') ?>
                    </a>
                <?php

                } elseif ($approved) { ?>
                    <a href="#close-modal" class="btn disabled">
                        <i class="ph ph-duotone ph-seal-check mr-5 text-success"></i>
                        <?= lang('activities.you_have_already_approved_the_currently_selected_quarter') ?>
                    </a>
                <?php } else { ?>
                    <a class="btn large success" href="#approve">
                        <i class="ph ph-seal-check mr-5"></i>
                        <?= lang('activities.approve_selected_quarter') ?>:
                        <b><?= $YEAR . ' Q' . $QUARTER ?></b>
                    </a>
                <?php } ?>

            <?php } ?>
        </div>
        
        <div class="col-md text-md-right">
            <div class=" float-right float-md-none">
                <a target="_blank" href="https://wiki.osiris-app.de/users/profile/scientist_view/" class="btn tour" id="tour">
                    <i class="ph ph-lg ph-question mr-5"></i>
                    <?= lang('common.read_the_docs') ?>
                </a>
            </div>

            <form id="" action="" method="get" class="d-block w-400 mw-full ml-md-auto mt-20">
                <div class="form-group">
                    <label for="year">
                        <?= lang('activities.change_year_and_quarter') ?>:
                    </label>


                    <div class="btn-group">
                        <a href="?year=<?= $YEAR - 1 ?>&quarter=<?= $QUARTER ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('activities.previous_year') ?>">
                            <i class="ph ph-caret-double-left"></i>
                        </a>
                        <a href="?year=<?= $lastY ?>&quarter=<?= $lastQ ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('activities.previous_quarter') ?>">
                            <i class="ph ph-caret-left"></i>
                        </a>
                        <a class="btn primary outline" onclick="$('#detailed').slideToggle()" data-toggle="tooltip" data-title="<?= lang('activities.select_quarter_in_detail') ?>">
                            <!-- <i class="ph ph-circle"></i> -->
                            <?= $YEAR ?>
                            Q<?= $QUARTER ?>
                        </a>
                        <a href="?year=<?= $nextY ?>&quarter=<?= $nextQ ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('activities.next_quarter') ?>">
                            <i class="ph ph-caret-right"></i>
                        </a>
                        <a href="?year=<?= $YEAR + 1 ?>&quarter=<?= $QUARTER ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('activities.next_year') ?>">
                            <i class="ph ph-caret-double-right"></i>
                        </a>
                    </div>

                    <div class="card w-400 position-absolute z-20" id="detailed" style="display: none">
                        <div class="input-group">

                            <div class="input-group-prepend">
                                <div class="input-group-text" data-toggle="tooltip" data-title="<?= lang('activities.select_quarter') ?>">
                                    <i class="ph ph-calendar-check"></i>
                                </div>
                            </div>
                            <select name="year" id="year" class="form-control">
                                <?php foreach (range($Settings->get('startyear'), CURRENTYEAR) as $year) { ?>
                                    <option value="<?= $year ?>" <?= $YEAR == $year ? 'selected' : '' ?>><?= $year ?></option>
                                <?php } ?>
                            </select>
                            <select name="quarter" id="quarter" class="form-control">
                                <option value="1" <?= $QUARTER == '1' ? 'selected' : '' ?>>Q1</option>
                                <option value="2" <?= $QUARTER == '2' ? 'selected' : '' ?>>Q2</option>
                                <option value="3" <?= $QUARTER == '3' ? 'selected' : '' ?>>Q3</option>
                                <option value="4" <?= $QUARTER == '4' ? 'selected' : '' ?>>Q4</option>
                            </select>
                            <div class="input-group-append">
                                <button class="btn primary"><i class="ph ph-check"></i></button>
                            </div>
                        </div>
                        <a href="?year=<?= CURRENTYEAR ?>&quarter=<?= CURRENTQUARTER ?>"><?= lang('activities.current_quarter') ?></a>
                    </div>
                </div>
            </form>


        </div>
    </div>



    <style>
        .table tbody tr:target,
        .table tbody tr.target {
            -moz-box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
            -webkit-box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
            box-shadow: 0 0 0 0.3rem var(--signal-box-shadow-color);
            z-index: 2;
            position: relative;
        }

        svg .axes line,
        svg .axes path {
            stroke: var(--text-color);
        }

        svg .axes text {
            fill: var(--text-color);
        }

        tr.in-quarter {
            background: rgba(236, 175, 0, 0.1);
        }

        tr.in-quarter .quarter {
            color: #9f7606;
        }

        .Q {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
            color: #9f7606;
        }
    </style>

    <div id="timeline-container" class="box">
        <div class="content my-0">

            <h2>
                <?= lang('activities.activities_in') . $YEAR ?>
            </h2>

        </div>
        <div id="timeline"></div>
    </div>

    <script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
    <script src="<?= ROOTPATH ?>/js/popover.js"></script>
    <script src="<?= ROOTPATH ?>/js/my-year.js"></script>

    <script>
        let typeInfo = JSON.parse('<?= json_encode($Settings->getActivities(null)) ?>');
        var typeInfoNew = {}
        typeInfo.forEach(el => {
            typeInfoNew[el.id] = el;
        });
        let events = JSON.parse('<?= json_encode(array_values($timeline)) ?>');
        console.log(events);
        var year = <?= $YEAR ?>;
        var quarter = <?= $QUARTER ?>;
        timeline(year, quarter, typeInfoNew, events);
    </script>


    <div class="alert signal">
        <?= lang('activities.the_entire_year_is_shown_here_activities_in_the_selected_quarter_q_are_high', replace: ['q' => $q]) ?>

    </div>


    <div class="row row-eq-spacing">
        <div class="col-lg-9">

            <?php
            foreach ($groups as $col => $data) {
                $type = $Settings->getActivities($col);
            ?>

                <div class="box box-<?= $col ?>" id="<?= $col ?>">
                    <div class="content mb-0">
                        <h3 class="title text-<?= $col ?> m-0">
                            <i class="ph ph-fw ph-<?= $type['icon'] ?> mr-5"></i>
                            <?= lang($type['name'], $type['name_de'] ?? null) ?>
                        </h3>
                    </div>
                    <?php if (empty($data)) { ?>
                        <div class="content text-muted">
                            <?= lang('activities.no_activities_found_my_year') ?>
                        </div>
                    <?php } else { ?>

                        <table class="table simple">
                            <tbody>
                                <?php
                                // $filter['type'] = $col;
                                // $cursor = $collection->find($filter, $options);
                                // dump($cursor);
                                foreach ($data as $doc) {
                                    $id = $doc['_id'];
                                    $l = $Coins->activityCoins($doc, $user);
                                    $Format->setDocument($doc);

                                    if ($doc['year'] == $YEAR) {
                                        $q = getQuarter($doc);
                                        $in_quarter = $q == $QUARTER;
                                        $q = "Q$q";
                                    } else {
                                        $q = getQuarter($doc);
                                        $in_quarter = false;
                                        $q = $doc['year'] . "Q$q";
                                    }


                                    echo "<tr class='" . ($in_quarter ? 'in-quarter' : '') . "' id='tr-$id'>";
                                    // echo "<td class='w-25'>";
                                    // echo$Format->activity_icon($doc);
                                    // echo "</td>";
                                    echo "<td class='quarter'>";
                                    if (!empty($q)) echo "$q";
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<div class='font-size-12 font-weight-bold text-$col'>" . $Format->activity_subtype() . "</div>";
                                    // echo $doc['format'];
                                    if ($USER['display_activities'] == 'web') {
                                        echo $Format->formatShort();
                                    } else {
                                        echo $Format->format();
                                    }

                                    // show error messages, warnings and todos
                                    $has_issues = $Format->has_issues();
                                    if ($currentuser && !empty($has_issues)) {
                                        $approval_needed[] = array(
                                            'type' => $col,
                                            'id' => $id,
                                            'title' => $Format->title,
                                            'badge' => $Format->activity_badge(),
                                            'tags' => $has_issues
                                        );
                                ?>
                                        <br>
                                        <b class="text-danger">
                                            <?= lang('activities.this_activity_has_unresolved_warnings') ?>
                                            <a href="<?= ROOTPATH ?>/issues#tr-<?= $id ?>" class="link">Review</a>
                                        </b>
                                    <?php
                                    }

                                    ?>

                                    </td>

                                    <td class="unbreakable w-50">
                                        <a class="btn link square" href="<?= ROOTPATH . "/activities/view/" . $id ?>">
                                            <i class="ph ph-arrow-fat-line-right"></i>
                                        </a>
                                        <button class="btn link square" onclick="addToCart(this, '<?= $id ?>')">
                                            <i class="<?= (in_array($id, $cart)) ? 'ph ph-duotone ph-basket ph-basket-plus text-success' : 'ph ph-basket ph-basket-plus' ?>"></i>
                                        </button>
                                        <?php if ($currentuser) { ?>
                                            <a class="btn link square" href="<?= ROOTPATH . "/activities/edit/" . $id ?>">
                                                <i class="ph ph-pencil-simple-line"></i>
                                            </a>
                                        <?php } ?>
                                    </td>
                                    <?php if ($showcoins) { ?>
                                        <td class='coins unbreakable'>
                                            <span data-toggle='tooltip' data-title='<?= $l['comment'] ?>'>
                                                <?= round($l["coins"]) ?>
                                            </span>
                                        </td>
                                    <?php } ?>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>


                    <?php } ?>

                    <div class="content mt-0">
                        <?php if ($currentuser) {
                            $t = $col;
                            if ($col == "publication") $t = "article";
                        ?>
                            <a href="<?= ROOTPATH ?>/my-activities?type=<?= $col ?>" class="btn text-<?= $Settings->getActivities($col)['color'] ?>">
                                <i class="ph ph-<?= $Settings->getActivities($col)['icon'] ?> mr-5"></i> <?= lang('activities.my') ?><?= $Settings->getActivities($col)[lang('common.field_name_language')] ?>
                            </a>
                            <a href="<?= ROOTPATH . "/add-activity?type=" . $t ?>" class="btn"><i class="ph ph-plus"></i></a>
                            <?php if ($col == 'publication') { ?>
                                <a class="btn mr-20" href="<?= ROOTPATH ?>/activities/online-search?authors=<?= $scientist['last'] ?>&year=<?= $YEAR ?>">
                                    <i class="ph ph-magnifying-glass-plus mr-5"></i>
                                    <?= lang('common.search_in_pubmed') ?>
                                </a>
                            <?php } ?>

                        <?php } ?>

                    </div>

                </div>

            <?php } ?>

        </div>
        <div class="col-lg-3 d-none d-lg-block">
            <nav class="on-this-page-nav">
                <div class="content">
                    <div class="title"><?= lang('common.activities') ?></div>
                    <?php foreach ($groups as $col => $data) {
                        $type = $Settings->getActivities($col);
                    ?>
                        <a href="#<?= $col ?>" class="text-<?= $col ?>">
                            <i class="ph ph-fw ph-<?= $type['icon'] ?> mr-5"></i>
                            <?= lang($type['name'], $type['name_de'] ?? null) ?>
                            (<?= count($data) ?>)
                        </a>
                    <?php } ?>
                </div>
            </nav>
        </div>
    </div>



    <?php if ($currentuser && $Settings->featureEnabled('quarterly-reporting', true)) { ?>
        <div class="modal modal-lg" id="approve" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content w-600 mw-full" style="border: 2px solid var(--success-color);">
                    <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                    <h5 class="title text-success"><?= lang('activities.approve_quarter_quarter', replace: ['QUARTER' => $QUARTER]) ?></h5>

                    <?php
                    if (!$quarter_in_past) {
                        echo "<p>" . lang('activities.quarter_is_not_over_yet') . "</p>";
                    } else  if ($approved) {
                        echo "<p>" . lang('activities.you_have_already_approved_the_currently_selected_quarter') . "</p>";
                    } else if (!empty($approval_needed)) {

                        $tagnames = [
                            'approval' => lang('activities.approval_needed'),
                            'epub' => 'Online ahead of print',
                            'students' => lang('activities.student_graduation'),
                            'openend' => lang('Open-end'),
                            'journal_id' => lang('activities.non_standardized_journal')
                        ];

                        echo "<p>" . lang('activities.the_following_activities_have_unresolved_warnings_please_review_all_issues', replace: ['rootpath' => ROOTPATH]) . "</p>";
                        echo "<table class='table simple'><tbody>";
                        foreach ($approval_needed as $item) {
                            // $type = ucfirst($item['type']);
                            echo "<tr><td class='px-0'>
                                $item[title]
                                <br>
                                $item[badge]";
                            foreach ($item['tags'] as $tag) {
                                $tag = $tagnames[$tag] ?? $tag;
                                echo "<a class='badge danger filled ml-5' href='" . ROOTPATH . "/issues#tr-$item[id]'>$tag</a>";
                            }

                            echo "</td></tr>";
                        }
                        echo "</tbody></table>";
                    } else { ?>

                        <img src="<?= ROOTPATH ?>/img/sophie/sophie-report.png" class="w-300 float-right">
                        <p>
                            <?= lang('activities.you_are_about_to_approve_the_current_quarter_by_confirming_you_verify_that') ?>
                        </p>
                        <p>
                            <?= lang('activities.this_action_cannot_be_undone_any_later_changes_should_be_coordinated_with_t') ?>
                        </p>

                        <form action="<?= ROOTPATH ?>/crud/users/approve" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <input type="hidden" name="quarter" class="hidden" value="<?= $YEAR . "Q" . $QUARTER ?>">
                            <button class="btn success large filled"><?= lang('common.approve') ?></button>
                        </form>
                    <?php } ?>

                </div>
            </div>
        </div>
    <?php } ?>

</div>