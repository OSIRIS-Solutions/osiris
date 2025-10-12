<?php

/**
 * Page to see and approve current quarter
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /my-year/<username>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
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
$filter = ['authors.user' => $user];
$filter['$or'] =   array(
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
);

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
        'title' => htmlspecialchars(strip_tags($doc['rendered']['title'])),
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
                    <?= lang('My Year', 'Mein Jahr') ?>
                <?php } else { ?>
                    <?= lang('The year of', 'Das Jahr von') ?>
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
                        <?= lang('Selected quarter is not over yet.', 'Gewähltes Quartal ist noch nicht zu Ende.') ?>
                    </a>
                <?php

                } elseif ($approved) { ?>
                    <a href="#close-modal" class="btn disabled">
                        <i class="ph ph-fill ph-seal-check mr-5 text-success"></i>
                        <?= lang('You have already approved the currently selected quarter.', 'Du hast das aktuelle Quartal bereits bestätigt.') ?>
                    </a>
                <?php } else { ?>
                    <a class="btn large success" href="#approve">
                        <i class="ph ph-seal-check mr-5"></i>
                        <?= lang('Approve selected quarter', 'Ausgewähltes Quartal freigeben') ?>:
                        <b><?= $YEAR . ' Q' . $QUARTER ?></b>
                    </a>
                <?php } ?>

            <?php } ?>
        </div>
        <div class="col-lg">

            <form id="" action="" method="get" class="w-400 mw-full ml-lg-auto">
                <div class="form-group">
                    <label for="year">
                        <?= lang('Change year and quarter', 'Ändere Jahr und Quartal') ?>:
                    </label>


                    <div class="btn-group">
                        <a href="?year=<?= $YEAR - 1 ?>&quarter=<?= $QUARTER ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('Previous year', 'Vorheriges Jahr') ?>">
                            <i class="ph ph-caret-double-left"></i>
                        </a>
                        <a href="?year=<?= $lastY ?>&quarter=<?= $lastQ ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('Previous quarter', 'Vorheriges Quartal') ?>">
                            <i class="ph ph-caret-left"></i>
                        </a>
                        <a class="btn text-primary border-primary" onclick="$('#detailed').slideToggle()" data-toggle="tooltip" data-title="<?= lang('Select quarter in detail', 'Wähle ein Quartal aus') ?>">
                            <!-- <i class="ph ph-circle"></i> -->
                            <?= $YEAR ?>
                            Q<?= $QUARTER ?>
                        </a>
                        <a href="?year=<?= $nextY ?>&quarter=<?= $nextQ ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('Next quarter', 'Nächstes Quartal') ?>">
                            <i class="ph ph-caret-right"></i>
                        </a>
                        <a href="?year=<?= $YEAR + 1 ?>&quarter=<?= $QUARTER ?>" class="btn primary" data-toggle="tooltip" data-title="<?= lang('Next year', 'Nächstes Jahr') ?>">
                            <i class="ph ph-caret-double-right"></i>
                        </a>
                    </div>

                    <div class="alert w-400 position-absolute" id="detailed" style="display: none">
                        <div class="input-group">

                            <div class="input-group-prepend">
                                <div class="input-group-text" data-toggle="tooltip" data-title="<?= lang('Select quarter', 'Wähle ein Quartal aus') ?>">
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
                                <button class="btn secondary"><i class="ph ph-check"></i></button>
                            </div>
                        </div>
                        <a href="?year=<?= CURRENTYEAR ?>&quarter=<?= CURRENTQUARTER ?>"><?= lang('Current quarter', 'Aktuelles Quartal') ?></a>
                    </div>
                </div>
            </form>
            <div class="text-lg-right">
                <a target="_blank" href="https://wiki.osiris-app.de/users/profile/scientist_view/" class="btn tour" id="tour">
                    <i class="ph ph-lg ph-question mr-5"></i>
                    <?= lang('Read the Docs', 'Zur Hilfeseite') ?>
                </a>
            </div>


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
                <?= lang('Activities in ', 'Aktivitäten in ') . $YEAR ?>
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
        <?= lang('The entire year is shown here. Activities in the selected quarter <b class="Q">' . $q . '</b> are highlighted. ', 'Das gesamte Jahr ist hier gezeigt. Aktivitäten innerhalb des gewählten Quartals <b class="Q">' . $q . '</b> sind farbig hinterlegt.') ?>

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
                            <?= lang('No activities found.', 'Noch keine Aktivitäten vorhanden.') ?>
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
                                            <?= lang('This activity has unresolved warnings.', 'Diese Aktivität hat ungelöste Warnungen.') ?>
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
                                            <i class="<?= (in_array($id, $cart)) ? 'ph ph-fill ph-shopping-cart ph-shopping-cart-plus text-success' : 'ph ph-shopping-cart ph-shopping-cart-plus' ?>"></i>
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
                                <i class="ph ph-<?= $Settings->getActivities($col)['icon'] ?> mr-5"></i> <?= lang('My ', 'Meine ') ?><?= $Settings->getActivities($col)[lang('name', 'name_de')] ?>
                            </a>
                            <a href="<?= ROOTPATH . "/add-activity?type=" . $t ?>" class="btn"><i class="ph ph-plus"></i></a>
                            <?php if ($col == 'publication') { ?>
                                <a class="btn mr-20" href="<?= ROOTPATH ?>/activities/online-search?authors=<?= $scientist['last'] ?>&year=<?= $YEAR ?>">
                                    <i class="ph ph-magnifying-glass-plus mr-5"></i>
                                    <?= lang('Search in Pubmed', 'Suche in Pubmed') ?>
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
                    <div class="title"><?= lang('Activities', 'Aktivitäten') ?></div>
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
                    <h5 class="title text-success"><?= lang("Approve quarter $QUARTER", "Quartal $QUARTER freigeben") ?></h5>

                    <?php
                    if (!$quarter_in_past) {
                        echo "<p>" . lang('Quarter is not over yet.', 'Das gewählte Quartal ist noch nicht zu Ende.') . "</p>";
                    } else  if ($approved) {
                        echo "<p>" . lang('You have already approved the currently selected quarter.', 'Du hast das aktuelle Quartal bereits bestätigt.') . "</p>";
                    } else if (!empty($approval_needed)) {

                        $tagnames = [
                            'approval' => lang('Approval needed', 'Überprüfung nötig'),
                            'epub' => 'Online ahead of print',
                            'students' => lang('Student\' graduation', "Studenten-Abschluss"),
                            'openend' => lang('Open-end'),
                            'journal_id' => lang('Non-standardized journal', 'Nicht-standardisiertes Journal')
                        ];

                        echo "<p>" . lang(
                            "The following activities have unresolved warnings. Please <a href='" . ROOTPATH . "/issues' class='link'>review all issues</a> before approving the current quarter.",
                            "Die folgenden Aktivitäten haben ungelöste Warnungen. Bitte <a href='" . ROOTPATH . "/issues' class='link'>kläre alle Probleme</a> bevor du das aktuelle Quartal freigeben kannst."
                        ) . "</p>";
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

                        <p>
                            <?= lang('
                            You are about to approve the current quarter. Your data will be sent to the Controlling and you hereby confirm that you have entered or checked all reportable activities for this year and that all data is correct. This process cannot be reversed and any changes to the quarter after this must be reported to Controlling.
                            ', '
                            Du bist dabei, das aktuelle Quartal freizugeben. Deine Daten werden an das Controlling übermittelt und du bestätigst hiermit, dass du alle meldungspflichtigen Aktivitäten für dieses Jahr eingetragen bzw. überprüft hast und alle Daten korrekt sind. Dieser Vorgang kann nicht rückgängig gemacht werden und alle Änderungen am Quartal im Nachhinein müssen dem Controlling gemeldet werden.
                            ') ?>
                        </p>

                        <form action="<?= ROOTPATH ?>/crud/users/approve" method="post">
                            <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                            <input type="hidden" name="quarter" class="hidden" value="<?= $YEAR . "Q" . $QUARTER ?>">
                            <button class="btn success"><?= lang('Approve', 'Freigeben') ?></button>
                        </form>
                    <?php } ?>

                </div>
            </div>
        </div>
    <?php } ?>

</div>