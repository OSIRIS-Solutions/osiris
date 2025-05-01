<?php

/**
 * Page to see scientists profile
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /profile/<username>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

$data_fields = $Settings->get('person-data');
if (!is_null($data_fields)) {
    $data_fields = DB::doc2Arr($data_fields);
} else {
    $fields = file_get_contents(BASEPATH . '/data/person-fields.json');
    $fields = json_decode($fields, true);

    $data_fields = array_filter($fields, function ($field) {
        return $field['default'] ?? false;
    });
    $data_fields = array_column($data_fields, 'id');
}

$active = function ($field) use ($data_fields) {
    return in_array($field, $data_fields);
};
?>


<!-- all necessary javascript -->
<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?= CSS_JS_VERSION ?>"></script>
<script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script>

<!-- all variables for this page -->
<script>
    const CURRENT_USER = '<?= $user ?>';
</script>
<script src="<?= ROOTPATH ?>/js/profile.js?v=<?= CSS_JS_VERSION ?>"></script>


<link rel="stylesheet" href="<?= ROOTPATH ?>/css/achievements.css?<?= filemtime(BASEPATH . '/css/achievements.css') ?>">

<style>
    .box.h-full {
        height: calc(100% - 2rem) !important;
    }

    .expertise {
        border-radius: var(--border-radius);
        background-color: white;
        border: 1px solid #afafaf;
        display: inline-block;
        padding: .2rem .8rem;
        box-shadow: var(--box-shadow);
        margin-right: .5rem;
    }

    .user-role {
        border-radius: var(--border-radius);
        background-color: white;
        border: 1px solid #afafaf;
        display: inline-block;
        padding: .2rem .8rem;
        box-shadow: var(--box-shadow);
        margin-right: .5rem;
        font-family: 'Consolas', 'Courier New', Courier, monospace;
        font-weight: 500;
    }
</style>

<?php


$Q = CURRENTQUARTER - 1;
$Y = CURRENTYEAR;
if ($Q < 1) {
    $Q = 4;
    $Y -= 1;
}
$lastquarter = $Y . "Q" . $Q;

$currentuser = $user == $_SESSION['username'];

// Check for new achievements

if ($Settings->featureEnabled('achievements')) {
    $Achievement = new Achievement($osiris);
    $Achievement->initUser($user);
    $Achievement->checkAchievements();
    $user_ac = $Achievement->userac;
    $show_achievements =  !empty($user_ac) && !($scientist['hide_achievements'] ?? false);
} else {
    $show_achievements = false;
}


// $showcoins = (!($scientist['hide_coins'] ?? true));
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

if ($showcoins) {
    if (!isset($_SESSION['coins']) || empty($_SESSION['coins'])) {
        include_once BASEPATH . "/php/Coins.php";
        $Coins = new Coins();
        $coins = $Coins->getCoins($user);
        $_SESSION['coins'] = $coins;
    } else {
        $coins = $_SESSION['coins'];
    }
}


?>

<?php if ($showcoins) { ?>
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
<?php } ?>

<?php


if ($currentuser || $Settings->hasPermission('user.image')) { ?>
    <!-- Modal for updating the profile picture -->
    <div class="modal modal-lg" id="change-picture" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content w-600 mw-full">
                <a href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </a>

                <h2 class="title">
                    <?= lang('Change profile picture', 'Profilbild ändern') ?>
                </h2>

                <form action="<?= ROOTPATH ?>/crud/users/profile-picture/<?= $user ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">
                    <div class="custom-file mb-20" id="file-input-div">
                        <input type="file" id="profile-input" name="file" data-default-value="<?= lang("No file chosen", "Keine Datei ausgewählt") ?>">
                        <label for="profile-input"><?= lang('Upload new profile image', 'Lade ein neues Profilbild hoch') ?></label>
                        <br><small class="text-danger">Max. 2 MB.</small>
                    </div>

                    <p>
                        <?= lang('Please note that your profile picture will be visible to all users of OSIRIS.', 'Bitte beachte, dass dein Profilbild für alle OSIRIS-Personen sichtbar sein wird.') ?>
                    </p>
                    <script>
                        var uploadField = document.getElementById("profile-input");

                        uploadField.onchange = function() {
                            if (this.files[0].size > 2097152) {
                                toastError(lang("File is too large! Max. 2MB is supported!", "Die Datei ist zu groß! Max. 2MB werden unterstützt."));
                                this.value = "";
                            };
                        };
                    </script>
                    <button class="btn secondary">
                        <i class="ph ph-upload"></i>
                        Upload
                    </button>
                </form>

                <?php if (true) { ?>
                    <hr>
                    <form action="<?= ROOTPATH ?>/crud/users/profile-picture/<?= $user ?>" method="post">
                        <input type="hidden" name="delete" value="true">
                        <button class="btn danger">
                            <i class="ph ph-trash"></i>
                            <?= lang('Delete current picture', 'Aktuelles Bild löschen') ?>
                        </button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
<?php } ?>



<div class="row align-items-center my-0">
    <div class="col flex-grow-0">
        <div class="position-relative">
            <?= $Settings->printProfilePicture($user, 'profile-img') ?>
            <?php if ($currentuser && $Settings->hasPermission('user.image-own') || $Settings->hasPermission('user.image')) { ?>
                <a href="#change-picture" class="position-absolute p-10 bottom-0 right-0 text-white"><i class="ph ph-edit"></i></a>
            <?php } ?>
        </div>
    </div>
    <div class="col ml-20">
        <h1 class="mt-0"><?= $name ?></h1>
        <h5 class="subtitle">
            <?= lang($scientist['position'] ?? '', $scientist['position_de'] ?? null) ?>
            <?php if ($scientist['hide'] ?? false) { ?>
                <small class="badge danger" data-toggle="tooltip" data-title="<?= lang('This person does not wish to be found in Portfolio', 'Diese Person möchte nicht in OSIRIS Portfolio gefunden werden.') ?>">
                    <i class="ph ph-globe-x m-0"></i>
                </small>
            <?php } ?>
        </h5>

        <?php if (!($scientist['is_active'] ?? true)) { ?>
            <span class="text-danger badge">
                <?= lang('Former Employee', 'Ehemalige Beschäftigte') ?>
                <?php if (isset($scientist['inactivated'])) { ?>
                    <small>
                        <?= lang('since', 'seit') ?>
                        <?= date('d.m.Y', strtotime($scientist['inactivated'])) ?>
                    </small>
                <?php } ?>

            </span>
        <?php } ?>

        <!-- <span class="badge">Last login: <?= $scientist['lastlogin'] ?? 'Never' ?></span> -->
        <?php
        // show current guest state
        if ($Settings->featureEnabled('guests')) {
            $guestState = $osiris->guests->findOne(['username' => $user]);
            if (!empty($guestState)) { ?>
                <span class="badge">
                    <?= lang('Guest:', 'Gast:') ?>
                    <?= fromToDate($guestState['start'], $guestState['end'] ?? null) ?>
                </span>
        <?php }
        }
        ?>

        <?php if ($active('topics')) {
            echo $Settings->printTopics($scientist['topics'] ?? [], 'mt-10');
        } ?>


    </div>

    <div id="units">
        <h5 class="mt-0">
            <?= lang('Organisational Unit(s)', 'Organisationseinheit(en)') ?>

            <?php if ($currentuser || $Settings->hasPermission('user.edit')) { ?>
                <a href="<?= ROOTPATH ?>/user/units/<?= $user ?>" class="font-size-14 ml-5">
                    <i class="ph ph-edit"></i>
                </a>
            <?php } ?>
        </h5>
        <?php
        $units = DB::doc2Arr($scientist['units'] ?? []);
        // filter units from the past
        $units = array_filter($units, function ($unit) {
            return !isset($unit['end']) || strtotime($unit['end']) > time();
        });
        $unit_ids = array_column($units, 'unit');
        ?>
        <table class="table unit-table">
            <tbody>
                <?php
                if (!empty($unit_ids)) {
                    $hierarchy = $Groups->getPersonHierarchyTree($unit_ids);
                    $tree = $Groups->readableHierarchy($hierarchy);

                    foreach ($tree as $row) {
                        $selected = in_array($row['id'], $unit_ids);
                        $dept = $Groups->getGroup($row['id']);
                        $head = (in_array($user, $dept['head'] ?? []));
                        if ($selected) { ?>
                            <tr>
                                <td class="indent-<?= $row['indent'] ?>">
                                    <a href="<?= ROOTPATH ?>/groups/view/<?= $row['id'] ?>">
                                        <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                    </a>
                                    <?php if ($head) { ?>
                                        <span data-toggle="tooltip" data-title="<?= lang('The person is leading this unit.', 'Die Person leitet diese Einheit.') ?>">
                                            <i class="ph ph-crown-simple text-signal"></i>
                                        </span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td class="text-muted indent-<?= $row['indent'] ?>">
                                    <?= lang($row['name_en'], $row['name_de'] ?? null) ?>
                                </td>
                            </tr>
                    <?php }
                    }
                } else { ?>
                    <tr>
                        <td>
                            <?= lang('No organisational unit selected', 'Keine Organisationseinheit ausgewählt') ?>
                        </td>
                    </tr>
                <?php }
                ?>
            </tbody>
        </table>
    </div>

</div>


<div class="achievements d-flex align-baseline">
    <style>
        .achievement-widget-small img {
            height: 2.8rem;
        }
    </style>

    <?php if ($showcoins) { ?>
        <p class="lead m-0">
            <i class="ph ph-lg ph-coin text-signal"></i>
            <b id="lom-points"><?= $coins ?></b>
            Coins
            <a href='#coins' class="text-muted">
                <i class="ph ph-question text-muted"></i>
            </a>

            <?php
            if ($show_achievements) {
                $Achievement->widget('small ml-20');
            } ?>
        </p>
    <?php } ?>
</div>


<?php if ($currentuser) {

    if (isset($scientist['new']) && defined('USER_MANAGEMENT') && USER_MANAGEMENT == 'AUTH') { ?>
        <!-- print message to change password -->
        <div class="alert danger mt-10">
            <a class="link text-danger" href='<?= ROOTPATH ?>/user/edit/<?= $user ?>#section-account'>
                <?= lang(
                    "You have not yet set a password. Please change your password now.",
                    "Du hast noch kein Passwort gesetzt. Bitte ändere jetzt dein Passwort."
                ) ?>
            </a>
        </div>
    <?php  } ?>

    <div class="btn-toolbar">

        <div class="btn-group btn-group-lg">
            <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/add-activity" data-toggle="tooltip" data-title="<?= lang('Add activity', 'Aktivität hinzufügen') ?>">
                <i class="ph ph-plus-circle ph-fw"></i>
                <!-- <?= lang('Add activity', 'Aktivität hinzufügen') ?> -->
            </a>
            <a href="<?= ROOTPATH ?>/my-activities" class="btn text-primary border-primary" data-toggle="tooltip" data-title="<?= lang('My activities', 'Meine Aktivitäten ') ?>">
                <i class="ph ph-folder-user ph-fw"></i>
                <!-- <?= lang('My activities', 'Meine Aktivitäten ') ?> -->
            </a>
            <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/my-year/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('My Year', 'Mein Jahr') ?>">
                <i class="ph ph-calendar ph-fw"></i>
                <!-- <?= lang('My Year', 'Mein Jahr') ?> -->
            </a>

            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/preview/person/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Preview', 'Vorschau') ?>">
                    <i class="ph ph-eye ph-fw"></i>
                </a>
            <?php } ?>

        </div>
        <div class="btn-group btn-group-lg">
            <?php if ($show_achievements) { ?>
                <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/achievements" data-toggle="tooltip" data-title="<?= lang('My Achievements', 'Meine Errungenschaften') ?>">
                    <i class="ph ph-trophy ph-fw"></i>
                </a>
            <?php } ?>
        </div>

        <div class="btn-group btn-group-lg">
            <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/user/edit/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Edit user profile', 'Bearbeite Profil') ?>">
                <i class="ph ph-edit ph-fw"></i>
                <!-- <?= lang('Edit user profile', 'Bearbeite Profil') ?> -->
            </a>
            <a href="<?= ROOTPATH ?>/claim" class="btn text-primary border-primary" data-toggle="tooltip" data-title="<?= lang('Claim activities', 'Aktivitäten beanspruchen') ?>">
                <i class="ph ph-hand ph-fw"></i>
                <!-- <?= lang('Claim activities', 'Aktivitäten beanspruchen') ?> -->
            </a>
        </div>
        <form action="<?= ROOTPATH ?>/download" method="post">

            <input type="hidden" name="filter[user]" value="<?= $user ?>">
            <input type="hidden" name="highlight" value="user">
            <input type="hidden" name="format" value="word">
            <input type="hidden" name="type" value="cv">

            <button class="btn text-primary border-primary large" data-toggle="tooltip" data-title="<?= lang('Export CV', 'CV exportieren') ?>">
                <i class="ph ph-identification-card text-primary ph-fw"></i>
            </button>
        </form>
    </div>


    <?php
    if ($show_achievements) {
        $new = $Achievement->new;

        if (!empty($new)) {
            $notification = true;
            echo '<div class="mt-20">';
            echo '<h5 class="title font-size-16">' . lang('Congratulation, you achieved something new: ', 'Glückwunsch, du hast neue Errungenschaften erlangt:') . '</h5>';
            foreach ($new as $i => $n) {
                $Achievement->snack($n);
            }
            $Achievement->save();
            echo '</div>';
        }
    }
    ?>

<?php } else { ?>
    <div class="btn-toolbar">
        <div class="btn-group btn-group-lg">
            <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/my-year/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('The year of ', 'Das Jahr von ') . $scientist['first'] ?> ">
                <i class="ph ph-calendar ph-fw"></i>
            </a>
            <a href="<?= ROOTPATH ?>/my-activities?user=<?= $user ?>" class="btn text-primary border-primary" data-toggle="tooltip" data-title="<?= lang('All activities of ', 'Alle Aktivitäten von ') . $scientist['first'] ?>">
                <i class="ph ph-folder-user ph-fw"></i>
            </a>
            <a href="<?= ROOTPATH ?>/visualize/coauthors?scientist=<?= $user ?>" class="btn text-primary border-primary" data-toggle="tooltip" data-title="<?= lang('Coauthor Network of ', 'Koautoren-Netzwerk von ') . $scientist['first'] ?>">
                <i class="ph ph-graph ph-fw"></i>
            </a>
            <?php if ($show_achievements) { ?>
                <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/achievements/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Achievements of ', 'Errungenschaften von ') . $scientist['first'] ?>">
                    <i class="ph ph-trophy ph-fw"></i>
                </a>
            <?php } ?>

        </div>
        <?php if ($Settings->featureEnabled('portal')) { ?>
            <div class="btn-group btn-group-lg">
                <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/preview/person/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Preview', 'Vorschau') ?>">
                    <i class="ph ph-eye ph-fw"></i>
                </a>
            </div>
        <?php } ?>
        <div class="btn-group btn-group-lg">
            <?php if ($Settings->hasPermission('user.edit')) { ?>
                <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/user/edit/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Edit user profile', 'Bearbeite Profil') ?>">
                    <i class="ph ph-edit ph-fw"></i>
                </a>
            <?php } ?>
            <?php if (($scientist['is_active'] ?? true) && $Settings->hasPermission('user.inactive')) { ?>
                <a class="btn text-primary border-primary" href="<?= ROOTPATH ?>/user/delete/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Inactivate user', 'Nutzer:in inaktivieren') ?>">
                    <i class="ph ph-trash ph-fw"></i>
                </a>
            <?php } ?>

        </div>
    </div>

<?php } ?>




<!-- TAB AREA -->

<nav class="pills mt-20 mb-0">
    <?php if ($currentuser) { ?>
        <a onclick="navigate('news')" id="btn-news" class="btn active">
            <i class="ph ph-star" aria-hidden="true"></i>
            <?= lang('News') ?>
        </a>
    <?php } ?>

    <a onclick="navigate('general')" id="btn-general" class="btn <?= $currentuser ? '' : 'active' ?>">
        <i class="ph ph-info" aria-hidden="true"></i>
        <?= lang('General', 'Allgemein') ?>
    </a>

    <?php
    $publication_filter = [
        'authors.user' => "$user",
        'type' => 'publication'
    ];
    $count_publications = $osiris->activities->count($publication_filter);

    if ($count_publications > 0) { ?>
        <a onclick="navigate('publications')" id="btn-publications" class="btn">
            <i class="ph ph-books" aria-hidden="true"></i>
            <?= lang('Publications', 'Publikationen')  ?>
            <span class="index"><?= $count_publications ?></span>
        </a>
    <?php } ?>

    <?php
    $coauthors = $osiris->activities->aggregate([
        ['$match' => ['type' => 'publication', 'authors.user' => $user, 'year' => ['$gte' => CURRENTYEAR - 4]]],
        ['$unwind' => '$authors'],
        ['$match' => ['authors.user' => ['$ne' => null]]],
        [
            '$group' => [
                '_id' => '$authors.user',
                'count' => ['$sum' => 1]
            ]
        ],
    ])->toArray();
    $count_coauthors = count($coauthors) - 1;
    if ($count_coauthors > 0) { ?>
        <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Coauthors', 'Koautoren')  ?>
            <span class="index"><?= $count_coauthors ?></span>
        </a>
    <?php } ?>

    <?php
    $activities_filter = [
        'authors.user' => "$user",
        'type' => ['$ne' => 'publication']
    ];
    $count_activities = $osiris->activities->count($activities_filter);

    if ($count_activities > 0) { ?>
        <a onclick="navigate('activities')" id="btn-activities" class="btn">
            <i class="ph ph-briefcase" aria-hidden="true"></i>
            <?= lang('Activities', 'Aktivitäten')  ?>
            <span class="index"><?= $count_activities ?></span>
        </a>
    <?php } ?>

    <?php
    $membership_filter = [
        'authors.user' => "$user",
        // 'end' => null,
        '$or' => array(
            ['type' => 'misc', 'subtype' => 'misc-annual'],
            ['type' => 'review', 'subtype' =>  'editorial'],
        )
    ];
    $count_memberships = $osiris->activities->count($membership_filter);
    if ($count_memberships > 0) { ?>
        <a onclick="navigate('memberships')" id="btn-memberships" class="btn">
            <i class="ph ph-user-list" aria-hidden="true"></i>
            <?= lang('Committee work', 'Gremienarbeit')  ?>
            <span class="index"><?= $count_memberships ?></span>
        </a>
    <?php } ?>

    <?php if ($Settings->featureEnabled('projects')) { ?>
        <?php
        $project_filter = [
            '$or' => array(
                ['contact' => $user],
                ['persons.user' => $user]
            ),
            // "status" => ['$in' => ["approved", 'finished']]
        ];
        // if ($currentuser) {
        //     $project_filter['status']['$in'][] = 'applied';
        // }

        $count_projects = $osiris->projects->count($project_filter);
        if ($count_projects > 0) { ?>
            <a onclick="navigate('projects')" id="btn-projects" class="btn">
                <i class="ph ph-tree-structure" aria-hidden="true"></i>
                <?= lang('Projects', 'Projekte')  ?>
                <span class="index"><?= $count_projects ?></span>
            </a>
        <?php } ?>
    <?php } ?>

    <?php if ($Settings->featureEnabled('infrastructures')) { ?>
        <?php
        $infrastructure_filter = ['persons.user' => $user];
        $count_infrastructures = $osiris->infrastructures->count($infrastructure_filter);
        if ($count_infrastructures > 0) { ?>
            <a onclick="navigate('infrastructures')" id="btn-infrastructures" class="btn">
                <i class="ph ph-cube-transparent" aria-hidden="true"></i>
                <?= $Settings->infrastructureLabel() ?>
                <span class="index"><?= $count_infrastructures ?></span>
            </a>
        <?php } ?>
    <?php } ?>


    <!-- Teaching activities -->
    <?php
    $teaching = $osiris->activities->aggregate([
        ['$match' => ['authors.user' => $user, 'type' => 'teaching', 'module_id' => ['$ne' => null]]],
        [
            '$group' => [
                '_id' => '$module_id',
                'count' => ['$sum' => 1],
                'doc' => ['$push' => '$$ROOT']
            ]
        ],
        ['$sort' => ['count' => -1]]
    ])->toArray();
    $count_teaching = count($teaching);

    if ($count_teaching > 0) { ?>
        <a onclick="navigate('teaching')" id="btn-teaching" class="btn">
            <i class="ph ph-graduation-cap" aria-hidden="true"></i>
            <?= lang('Teaching', 'Lehre')  ?>
            <span class="index"><?= $count_teaching ?></span>
        </a>
    <?php } ?>



    <?php if ($Settings->featureEnabled('wordcloud')) { ?>
        <?php
        $count_wordcloud = $osiris->activities->count(['title' => ['$exists' => true], 'authors.user' => $user, 'type' => 'publication']);
        if ($count_wordcloud > 0) { ?>
            <a onclick="navigate('wordcloud')" id="btn-wordcloud" class="btn">
                <i class="ph ph-cloud" aria-hidden="true"></i>
                <?= lang('Word cloud')  ?>
            </a>
        <?php } ?>
    <?php } ?>

    <?php if ($Settings->featureEnabled('concepts')) { ?>
        <?php
        $concepts = [];
        $concepts = $osiris->activities->aggregate(
            [
                ['$match' => ['authors.user' => $user, 'concepts' => ['$exists' => true]]],
                ['$project' => ['concepts' => 1]],
                [
                    '$group' => [
                        '_id' => null,
                        'total' => ['$sum' => 1],
                        'concepts' => ['$push' => '$concepts']
                    ]
                ],
                ['$unwind' => '$concepts'],
                ['$unwind' => '$concepts'],
                ['$group' => [
                    '_id' => '$concepts.display_name',
                    'count' => ['$sum' => 1],
                    'score' => ['$sum' => ['$divide' => [
                        ['$multiply' => ['$concepts.score', ['$sum' => 1]]],
                        '$total'
                    ]]],
                    'concept' => ['$first' => '$concepts']
                ]],
                ['$match' => ['score' => ['$gte' => 0.05]]],
                ['$sort' => ['score' => -1]]
            ]
        )->toArray();
        $count_concepts = count($concepts);
        if ($count_concepts > 0) { ?>
            <a onclick="navigate('concepts')" id="btn-concepts" class="btn">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('Concepts', 'Konzepte')  ?>
                <span class="index"><?= $count_concepts ?></span>
            </a>
        <?php } ?>
    <?php } ?>

</nav>


<?php if ($currentuser) { ?>
    <section id="news">
        <div class="row row-eq-spacing my-0">
            <div class="col-md-6">
                <div class="box h-full">
                    <div class="content">
                        <h4 class="title">
                            <?= lang('Newest publications', 'Neuste Publikationen') ?>
                        </h4>
                        <p class="text-muted">
                            <?= lang('Here you can find the latest publications from your institute.', 'Hier findest du die neusten Publikationen deines Instituts.') ?>
                        </p>

                        <?php
                        $pubs = $osiris->activities->find(
                            ['authors.aoi' => true, 'type' => 'publication'],
                            [
                                'sort' => ['start_date' => -1],
                                'limit' => 5,
                                'projection' => ['html' => '$rendered.web', 'date' => '$start_date']
                            ]
                        )->toArray();
                        ?>
                        <table class="table simple">
                            <?php foreach ($pubs as $doc) { ?>
                                <tr>
                                    <td>
                                        <small class="badge primary font-weight-bold"><?= format_date($doc['date']) ?></small><br>
                                        <?= $doc['html'] ?>
                                    </td>
                                </tr>
                            <?php } ?>

                        </table>

                        <a href="<?= ROOTPATH ?>/activities" class="btn primary">
                            <?= lang('All activities', 'Zeige alle Aktivitäten') ?>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 h-full">

                <?php if ($currentuser && (($Settings->featureEnabled('quarterly-reporting', true) && $Settings->hasPermission('report.dashboard')) || $Settings->hasPermission('report.generate'))) { ?>

                    <div class="row row-eq-spacing">

                        <?php if ($Settings->featureEnabled('quarterly-reporting', true) && $currentuser && $Settings->hasPermission('report.dashboard')) {

                            $n_scientists = $osiris->persons->count(["roles" => 'scientist', "is_active" => true]);
                            $n_approved = $osiris->persons->count(["roles" => 'scientist', "is_active" => true, "approved" => $lastquarter]);
                        ?>
                            <div class="col-6">
                                <div class="box h-full">
                                    <div class="chart content">
                                        <h5 class="title text-center"><?= $lastquarter ?></h5>

                                        <canvas id="approved-<?= $lastquarter ?>"></canvas>
                                        <div class="text-right mt-5">
                                            <button class="btn small" onclick="loadModal('components/controlling-approved', {q: '<?= $Q ?>', y: '<?= $Y ?>'})">
                                                <i class="ph ph-magnifying-glass-plus"></i> <?= lang('Details') ?>
                                            </button>
                                        </div>

                                        <script>
                                            var ctx = document.getElementById('approved-<?= $lastquarter ?>')
                                            var myChart = new Chart(ctx, {
                                                type: 'doughnut',
                                                data: {
                                                    labels: ['<?= lang("Approved", "Bestätigt") ?>', '<?= lang("Approval missing", "Bestätigung fehlt") ?>'],
                                                    datasets: [{
                                                        label: '# of Scientists',
                                                        data: [<?= $n_approved ?>, <?= $n_scientists - $n_approved ?>],
                                                        backgroundColor: [
                                                            '#00808395',
                                                            '#f7810495',
                                                        ],
                                                        borderColor: '#464646', //'',
                                                        borderWidth: 1,
                                                    }]
                                                },
                                                plugins: [ChartDataLabels],
                                                options: {
                                                    responsive: true,
                                                    plugins: {
                                                        datalabels: {
                                                            color: 'black',
                                                            // anchor: 'end',
                                                            // align: 'end',
                                                            // offset: 10,
                                                            font: {
                                                                size: 20
                                                            }
                                                        },
                                                        legend: {
                                                            position: 'bottom',
                                                            display: false,
                                                        },
                                                        title: {
                                                            display: false,
                                                            text: 'Scientists approvation'
                                                        }
                                                    }
                                                }
                                            });
                                        </script>

                                    </div>
                                </div>
                            </div>
                        <?php } ?>


                        <?php if ($currentuser && $Settings->hasPermission('report.generate')) { ?>
                            <div class="col-6">
                                <div class=" h-full">
                                    <div class="py-10">
                                        <div class="link-list">
                                            <?php if ($Settings->hasPermission('report.dashboard')) { ?>
                                                <a class="border" href="<?= ROOTPATH ?>/dashboard"><?= lang('Dashboard', 'Dashboard') ?></a>
                                            <?php } ?>

                                            <?php if ($Settings->hasPermission('report.queue')) { ?>
                                                <a class="border" href="<?= ROOTPATH ?>/queue/editor"><?= lang('Queue', 'Warteschlange') ?></a>
                                            <?php } ?>

                                            <?php if ($Settings->hasPermission('report.generate')) { ?>
                                                <a class="border" href="<?= ROOTPATH ?>/reports"><?= lang('Reports', 'Berichte') ?></a>
                                            <?php } ?>

                                            <?php if ($Settings->hasPermission('activities.lock')) { ?>
                                                <a class="border" href="<?= ROOTPATH ?>/controlling"><?= lang('Lock activities', 'Aktivitäten sperren') ?></a>
                                            <?php } ?>

                                            <?php if ($Settings->hasPermission('admin.see')) { ?>
                                                <a class="border" href="<?= ROOTPATH ?>/admin/general"><?= lang('Admin-Panel') ?></a>
                                            <?php } ?>
                                        </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                <?php } ?>

                <div class="box h-full">
                    <div class="content">
                        <?php if ($Settings->hasPermission('conferences.edit')) { ?>
                            <a href="<?= ROOTPATH ?>/conferences#add-conference" class="float-md-right btn primary">
                                <i class="ph ph-plus"></i>
                                <?= lang('Add', 'Hinzufügen') ?>
                            </a>
                        <?php } ?>

                        <h4 class="title">
                            <a href="<?= ROOTPATH ?>/conferences" class="link">
                                <?= lang('Events') ?>
                            </a>
                        </h4>
                        <p class="text-muted">
                            <?= lang('Shown are approaching events in the next 6 month and events you attended within the past six month.', 'Gezeigt sind zukünftige Events in den nächsten 6 Monaten und vergangene, an denen du in den letzten sechs Monaten teilgenommen hast.') ?>
                        </p>

                        <?php
                        // conferences max past 3 month
                        $conferences = $osiris->conferences->find(
                            [
                                '$or' => [
                                    ['end' => ['$gte' => date('Y-m-d', strtotime('-3 days'))], 'start' => ['$lte' => date('Y-m-d', strtotime('+6 month'))]],
                                    [
                                        'start' => ['$gte' => date('Y-m-d', strtotime('-6 month'))],
                                        '$or' => [
                                            ['participants' => $user],
                                            ['interests' => $user]
                                        ]
                                    ]
                                ],
                                'dismissed' => ['$ne' => $user]
                            ],
                            ['sort' => ['start' => 1]]
                        )->toArray();
                        ?>
                        <table class="table simple">
                            <?php foreach ($conferences as $n => $c) {
                                $past = strtotime($c['end']) > time();
                                if ($past) {
                                    $days = ceil((strtotime($c['start']) - time()) / 86400);
                                    $days = $days > 0 ? $days : 0;
                                    $days = $days == 0 ? lang('today', 'heute') : 'in ' . $days . ' ' . lang('days', 'Tagen');
                                }
                                // user is interested in conference
                                $interest = in_array($user, DB::doc2Arr($c['interests'] ?? []));
                                $participate = in_array($user, DB::doc2Arr($c['participants'] ?? []));
                                $interestTooltip = $interest ? lang('Click to remove interest', 'Klicken um Interesse zu entfernen') : lang('Click to show interest', 'Klicken um Interesse zu zeigen');
                                $participateTooltip = $participate ? lang('Click to remove participation', 'Klicken um Teilnahme zu entfernen') : lang('Click to show participation', 'Klicken um Teilnahme zu zeigen');

                            ?>
                                <tr>
                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <h6 class="m-0">
                                                <a href="<?= ROOTPATH ?>/conferences/<?= $c['_id'] ?>">
                                                    <?= $c['title'] ?>
                                                </a>
                                                <?php if (!empty($c['url'] ?? null)) { ?>
                                                    <a href="<?= $c['url'] ?>" target="_blank" rel="noopener noreferrer">
                                                        <i class="ph ph-link"></i>
                                                    </a>
                                                <?php } ?>
                                            </h6>
                                            <!-- dismiss btn -->
                                            <a class="text-danger" onclick="conferenceToggle(this, '<?= $c['_id'] ?>', 'dismissed')" data-toggle="tooltip" data-title="<?= lang('Dismiss', 'Verwerfen') ?>">
                                                <i class="ph ph-x"></i>
                                            </a>
                                        </div>
                                        <p class="my-5 text-muted">
                                            <?= $c['title_full'] ?? '' ?>
                                        </p>
                                        <p class="my-5 text-muted">
                                            <small class="text- mr-10">
                                                <?= fromToDate($c['start'], $c['end']) ?>
                                            </small>
                                            <small>
                                                <?= $c['location'] ?>
                                            </small>
                                        </p>

                                        <div class="btn-toolbar font-size-12">
                                            <?php
                                            // check if conference is in the future
                                            if ($past) {
                                            ?>
                                                <div class="btn-group">
                                                    <small class="btn small cursor-default">
                                                        <?= $days ?>
                                                    </small>
                                                    <a class="btn small" href="<?= ROOTPATH ?>/conference/ics/<?= $c['_id'] ?>" data-toggle="tooltip" data-title="<?= lang('Add to calendar', 'Zum Kalender hinzufügen') ?>">
                                                        <i class="ph ph-calendar-plus"></i>
                                                    </a>
                                                </div>
                                                <div class="btn-group">
                                                    <a class="btn small <?= $interest ? 'active primary' : '' ?>" onclick="conferenceToggle(this, '<?= $c['_id'] ?>', 'interests')" data-toggle="tooltip" data-title="<?= $interestTooltip ?>">
                                                        <b><?= count($c['interests'] ?? []) ?></b>
                                                        <?= lang('Interested', 'Interessiert') ?>
                                                    </a>
                                                    <a class="btn small <?= $participate ? 'active primary' : '' ?>" onclick="conferenceToggle(this, '<?= $c['_id'] ?>', 'participants')" data-toggle="tooltip" data-title="<?= $participateTooltip ?>">
                                                        <b><?= count($c['participants'] ?? []) ?></b>
                                                        <?= lang('Participants', 'Teilnehmer') ?>
                                                    </a>
                                                </div>
                                            <?php } else { ?>
                                                <a class="btn small primary" href="<?= ROOTPATH ?>/add-activity?type=poster&conference=<?= $c['_id'] ?>">
                                                    <i class="ph ph-plus-circle"></i>
                                                    <?= lang('Add contribution', 'Beitrag hinzufügen') ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>

                        </table>
                        <small class="text-muted"> <?= lang('Events were added by users of the OSIRIS system.', 'Events wurden von Nutzenden des OSIRIS-Systems angelegt.') ?></small>

                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>



<section id="general" <?= $currentuser ? 'style="display:none"' : '' ?>>

    <div class="row row-eq-spacing my-0">
        <div class="col-md-6 col-lg-4">
            <div class="box h-full">
                <div class="content">
                    <h4 class="title">
                        <?= lang('Details') ?>
                        <?php if ($currentuser) { ?>
                            <a class="font-size-14 ml-10" href="<?= ROOTPATH ?>/user/edit/<?= $user ?>">
                                <i class="ph ph-note-pencil ph-lg"></i>
                            </a>
                        <?php } ?>
                    </h4>
                </div>
                <table class="table simple small">
                    <tbody>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Username', 'Benutzername') ?></span>
                                <?= $user ?>
                            </td>
                        </tr>
                        <?php if ($active('internal_id') && isset($scientist['internal_id'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Internal ID', 'Interne ID') ?></span>
                                    <?= $scientist['internal_id'] ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td>
                                <span class="key"><?= lang('Last name', 'Nachname') ?></span>
                                <?= $scientist['last'] ?? '' ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span class="key"><?= lang('First name', 'Vorname') ?></span>
                                <?= $scientist['first'] ?? '' ?>
                            </td>
                        </tr>
                        <?php if (isset($scientist['academic_title'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Academic title', 'Akademischer Titel') ?></span>
                                    <?= $scientist['academic_title'] ?? '' ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <tr>
                            <td>
                                <span class="key"><?= lang('Roles', 'Rollen') ?></span>

                                <?php foreach (($scientist['roles'] ?? []) as $role) { ?>
                                    <span class="badge">
                                        <?= strtoupper($role) ?>
                                    </span>
                                <?php } ?>
                            </td>
                        </tr>

                        <?php if (($Settings->featureEnabled('quarterly-reporting', true) && $Settings->hasPermission('report.dashboard')) && isset($scientist['approved'])) {
                            $approvedQ = DB::doc2Arr($scientist['approved']);
                            sort($approvedQ);
                            echo "<tr><td>";
                            echo "<span class='key'>" . lang('Quarters approved', 'Bestätigte Quartale') . ":</span>";
                            foreach ($approvedQ as $appr) {
                                $Q = explode('Q', $appr);
                                echo "<a href='" . ROOTPATH . "/my-year/$user?year=$Q[0]&quarter=$Q[1]' class='badge success mr-5 mb-5'>$appr</a>";
                            }
                            echo "</td></tr>";
                        } ?>
                        <?php
                        // check if user has custom fields
                        $custom_fields = $osiris->adminFields->find()->toArray();
                        if (!empty($custom_fields)) {
                            foreach ($custom_fields as $field) {
                                if ($active($field['id']) && isset($scientist[$field['id']])) { ?>
                                    <tr>
                                        <td>
                                            <span class="key"><?= lang($field['name'], $field['name_de'] ?? null) ?></span>
                                            <?= $scientist[$field['id']] ?>
                                        </td>
                                    </tr>
                        <?php }
                            }
                        } ?>
                        <?php if (isset($scientist['mail'])) { ?>
                            <tr>
                                <td>
                                    <span class="key">Email</span>
                                    <a href="mailto:<?= $scientist['mail'] ?>"><?= $scientist['mail'] ?></a>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($active('telephone') && isset($scientist['telephone'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Telephone', 'Telefon') ?></span>
                                    <a href="tel:<?= $scientist['telephone'] ?>"><?= $scientist['telephone'] ?></a>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($active('room') && isset($scientist['room'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Room', 'Raum') ?></span>
                                    <a href="tel:<?= $scientist['room'] ?>"><?= $scientist['room'] ?></a>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($active('mobile') && isset($scientist['mobile'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Mobile', 'Mobil') ?></span>
                                    <a href="tel:<?= $scientist['mobile'] ?>"><?= $scientist['mobile'] ?></a>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($scientist['orcid'] ?? null)) { ?>
                            <tr>
                                <td>
                                    <span class="key">ORCID</span>

                                    <a href="http://orcid.org/<?= $scientist['orcid'] ?>" target="_blank" rel="noopener noreferrer"><?= $scientist['orcid'] ?></a>

                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($scientist['google_scholar'] ?? null)) { ?>
                            <tr>
                                <td>
                                    <span class="key">Google Scholar</span>

                                    <a href="https://scholar.google.com/citations?user=<?= $scientist['google_scholar'] ?>" target="_blank" rel="noopener noreferrer"><?= $scientist['google_scholar'] ?></a>

                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($active('socials') && isset($scientist['socials'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Social media') ?></span>
                                    <?php
                                    foreach ($scientist['socials'] as $key => $val) { ?>
                                        <a class="btn primary" href="<?= $val ?>" target="_blank" rel="noopener noreferrer"> <i class="ph <?= socialLogo($key) ?>"></i></a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <?php if ($active('expertise') && ($currentuser || !empty($scientist['expertise'] ?? array()))) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Expertise') ?></span>
                                    <?php foreach ($scientist['expertise'] ?? array() as $key) { ?><a href="<?= ROOTPATH ?>/expertise?search=<?= $key ?>" class="badge primary mr-5 mb-5"><?= $key ?></a><?php } ?>
                                    <?php if ($currentuser) { ?> <a href="<?= ROOTPATH ?>/user/edit/<?= $user ?>#section-research" class=""><i class="ph ph-edit"></i></a> <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($active('keywords') && !empty($scientist['keywords'] ?? array())) {
                            $kw_name = $Settings->get('staff-keyword-name', 'Keywords');
                            $selected_kw = DB::doc2Arr($scientist['keywords'] ?? []);
                        ?>
                            <tr>
                                <td>
                                    <span class="key"><?= $kw_name ?></span>
                                    <?php foreach ($selected_kw as $key) { ?><a href="<?= ROOTPATH ?>/keywords?search=<?= $key ?>" class="badge primary mr-5 mb-5"><?= $key ?></a><?php } ?>
                                    <?php if ($currentuser) { ?> <a href="<?= ROOTPATH ?>/user/edit/<?= $user ?>#section-research" class=""><i class="ph ph-edit"></i></a> <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>


        <div class="col-md-6 col-lg-8">
            <div class="box h-full">
                <div class="content">

                    <?php if ($active('research')) { ?>

                        <h4 class="title">
                            <?= lang('Research interest', 'Forschungsinteressen') ?>
                            <?php if ($currentuser || $Settings->hasPermission('user.edit')) { ?>
                                <a class="font-size-14 ml-10" href="<?= ROOTPATH ?>/user/edit/<?= $user ?>#section-research">
                                    <i class="ph ph-note-pencil ph-lg"></i>
                                </a>
                            <?php } ?>
                        </h4>

                        <?php if (isset($scientist['research']) && !empty($scientist['research'])) {
                            $scientist['research_de'] = array_map(
                                fn($val1, $val2) => empty($val1) ? $val2 : $val1,
                                DB::doc2Arr($scientist['research_de'] ?? $scientist['research']),
                                DB::doc2Arr($scientist['research'])
                            );
                            $research = lang($scientist['research'], $scientist['research_de'] ?? null);
                        ?>
                            <ul class="list">
                                <?php foreach ($research as $key) { ?>
                                    <li><?= $key ?></li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <p><?= lang('No research interests stated.', 'Keine Forschungsinteressen angegeben.') ?></p>
                        <?php } ?>

                        <?php if (isset($scientist['research_profile'])) { ?>
                            <h6 class="title">
                                <?= lang('Research profile', 'Forschungsprofil') ?>
                            </h6>
                            <?= lang($scientist['research_profile'], $scientist['research_profile_de'] ?? null); ?>
                        <?php } ?>

                </div>
                <hr>
            <?php } ?>

            <div class="content">

                <?php if ($active('cv')) { ?>
                    <h4 class="title">
                        <?= lang('Curriculum Vitae') ?>
                        <?php if ($currentuser || $Settings->hasPermission('user.edit')) { ?>
                            <a class="font-size-14 ml-10" href="<?= ROOTPATH ?>/user/edit/<?= $user ?>#section-biography">
                                <i class="ph ph-note-pencil ph-lg"></i>
                            </a>
                        <?php } ?>
                    </h4>

                    <?php if (isset($scientist['cv']) && !empty($scientist['cv'])) {
                        $cv = DB::doc2Arr($scientist['cv']);
                    ?>
                        <div class="biography">
                            <?php foreach ($cv as $entry) { ?>
                                <div class="cv">
                                    <span class="time"><?= $entry['time'] ?></span>
                                    <h5 class="title"><?= $entry['position'] ?></h5>
                                    <span class="affiliation"><?= $entry['affiliation'] ?></span>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p><?= lang('No CV given.', 'Kein CV angegeben.') ?></p>
                    <?php } ?>
                <?php } ?>


                <?php if ($active('biography')) { ?>
                    <?php if (isset($scientist['biography']) && !empty($scientist['biography'])) { ?>
                        <h6 class="title">
                            <?= lang('Biography', 'Biografie') ?>
                        </h6>
                        <p><?= lang($scientist['biography'], $scientist['biography_de'] ?? null); ?></p>
                    <?php } ?>
                <?php } ?>

                <?php if ($active('education')) { ?>
                    <?php if (isset($scientist['education']) && !empty($scientist['education'])) { ?>
                        <h6 class="title">
                            <?= lang('Education', 'Ausbildung') ?>
                        </h6>
                        <p><?= lang($scientist['education'], $scientist['education_de'] ?? null); ?></p>
                    <?php } ?>
                <?php } ?>

            </div>
            </div>
        </div>
    </div>

</section>


<section id="publications" style="display:none">

    <h2><?= lang('Publications', 'Publikationen') ?></h2>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="publication-table">
            <thead>
                <tr>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Activity', 'Aktivität') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>

    <div class="row row-eq-spacing my-0">

        <?php
        // IMPACT FACTOR WIDGET
        if (($currentuser || $Settings->featureEnabled('user-metrics'))) { ?>
            <div class="col-md-6 col-lg-8" id="chart-impact">
                <div class="box h-full">
                    <div class="chart content">
                        <h4 class="title mb-0">
                            <?= lang('Impact factor histogram', 'Impact Factor Histogramm') ?>
                        </h4>
                        <p class="text-muted mt-0"><?= lang('since', 'seit') . " " . $Settings->get('startyear') ?></p>
                        <canvas id="chart-impact-canvas" style="max-height: 30rem;"></canvas>
                    </div>
                </div>
            </div>
        <?php } ?>



        <?php
        // ROLE WIDGET
        if (($currentuser || $Settings->featureEnabled('user-metrics'))) { ?>
            <div class="col-md-6 col-lg-4" id="chart-authors">
                <div class="box h-full">
                    <div class="chart content">
                        <h4 class="title mb-0">
                            <?= lang('Role in publications', 'Rolle in Publikationen') ?>
                        </h4>
                        <p class="text-muted mt-0"><?= lang('since', 'seit') . " " . $Settings->get('startyear') ?></p>

                        <canvas id="chart-authors-canvas" style="max-height: 30rem;"></canvas>

                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>


<section id="activities" style="display:none">


    <h2><?= lang('Other activities', 'Andere Aktivitäten') ?></h2>

    <div class="mt-20 w-full">
        <table class="table dataTable responsive" id="activities-table">
            <thead>
                <tr>
                    <th><?= lang('Type', 'Typ') ?></th>
                    <th><?= lang('Activity', 'Aktivität') ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>

        </table>
    </div>

    <?php if (($currentuser || $Settings->featureEnabled('user-metrics'))) { ?>
        <div class="" id="chart-activities">
            <div class="box">
                <div class="chart content">
                    <h4 class="title mb-0">
                        <?= lang('All activities', 'Alle Aktivitäten') ?>
                    </h4>
                    <p class="text-muted mt-0"><?= lang('in which ' . $scientist['first'] . ' was involved', 'an denen ' . $scientist['first'] . ' beteiligt war') ?></p>

                    <canvas id="chart-activities-canvas" style="max-height: 35rem;"></canvas>

                    <small class="text-muted">
                        <?= lang('For multi-year activities, only the start date is relevant.', 'Bei mehrjährigen Aktivitäten wird nur das Startdatum gezählt.') ?>
                    </small>
                </div>
            </div>
        </div>
    <?php } ?>

</section>


<section id="memberships" style="display:none">

    <?php

    if ($count_memberships > 0) {
        $memberships = $osiris->activities->find($membership_filter, ['sort' => ["type" => 1, "year" => -1, "month" => -1, "day" => -1]]);
        $ongoing = [];
        $past = [];

        foreach ($memberships as $doc) {
            $element = [
                '_id' => $doc['_id'],
                'icon' => $doc['rendered']['icon'],
                'web' => $doc['rendered']['web'],
            ];
            if (empty($doc['end']) || new DateTime() < getDateTime($doc['end'])) {
                $ongoing[] = $element;
            } else {
                $past[] = $element;
            }
        }
    ?>

        <div class="">
            <?php if (!empty($ongoing)) { ?>
                <div class="box">
                    <div class="content">
                        <h4 class="title"><?= lang('Ongoing committee works', 'Laufende Gremienarbeit') ?></h4>
                    </div>
                    <table class="table simple">
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($ongoing as $doc) {
                                $id = $doc['_id'];
                            ?>
                                <tr id='tr-<?= $id ?>'>
                                    <td class="w-50"><?= $doc['icon']; ?></td>
                                    <td>
                                        <?= $doc['web'] ?>
                                    </td>
                                    <td class="unbreakable w-25">
                                        <a class="btn link square" href="<?= ROOTPATH . "/activities/view/" . $id ?>">
                                            <i class="ph ph-arrow-fat-line-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
            <?php if (!empty($past)) { ?>
                <div class="box">
                    <div class="content">
                        <h4 class="title"><?= lang('Past committee works', 'Vergangene Gremienarbeiten') ?></h4>
                    </div>
                    <table class="table simple">
                        <tbody>
                            <?php
                            $i = 0;
                            foreach ($past as $doc) {
                                $id = $doc['_id'];
                            ?>
                                <tr id='tr-<?= $id ?>'>
                                    <td class="w-50"><?= $doc['icon']; ?></td>
                                    <td>
                                        <?= $doc['web'] ?>
                                    </td>
                                    <td class="unbreakable w-25">
                                        <a class="btn link square" href="<?= ROOTPATH . "/activities/view/" . $id ?>">
                                            <i class="ph ph-arrow-fat-line-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

        </div>

    <?php } ?>


</section>



<?php if ($Settings->featureEnabled('projects')) {

    require_once BASEPATH . "/php/Project.php";
    $Project = new Project();

?>
    <section id="projects" style="display:none">
        <h3 class="title">
            <?= lang('Timeline of all approved projects', 'Zeitstrahl aller bewilligten Projekte') ?>
        </h3>
        <div class="box">
            <div class="content">
                <div id="project-timeline"></div>
            </div>
        </div>


        <?php
        if ($count_projects > 0) {
            $projects = $osiris->projects->find($project_filter, ['sort' => ["start" => -1, "end" => -1]]);

            $ongoing = [];
            $past = [];

            foreach ($projects as $project) {
                $Project->setProject($project);
                if ($Project->inPast()) {
                    $past[] = $Project->widgetLarge($user);
                } else {
                    $ongoing[] = $Project->widgetLarge($user);
                }
            }
            $i = 0;
        ?>
            <?php if (!empty($ongoing)) { ?>
                <h2><?= lang('Ongoing projects', 'Laufende Projekte') ?></h2>

                <div class="row row-eq-spacing my-0">
                    <?php foreach ($ongoing as $html) { ?>
                        <div class="col-md-6">
                            <?= $html ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>


            <?php if ($currentuser) {
                $proposals = $osiris->proposals->find(['persons.user' => $user, 'status' => 'proposed'], ['sort' => ["start" => -1, "end" => -1]])->toArray();
                if (!empty($proposals)) {
            ?>

                    <h2><?= lang('Proposed projects', 'Beantragte Projekte') ?></h2>

                    <div class="row row-eq-spacing my-0">
                        <?php foreach ($proposals as $proposal) {
                            $Project->setProject($proposal);
                        ?>
                            <div class="col-md-6">
                                <?= $Project->widgetLarge($user) ?>
                            </div>
                        <?php } ?>
                    </div>

                    <p class="text-muted font-size-12">
                        <?= lang('Others can not see your proposals on your profile page.', 'Andere Nutzende können Projektanträge nicht auf deiner Profilseite sehen.') ?>
                        <a href="<?= ROOTPATH ?>/proposals" class="link"><?= lang('See all proposals', 'Zeige alle Anträge') ?></a>
                    </p>
                <?php } ?>
            <?php } ?>


            <?php if (!empty($past)) { ?>
                <h2><?= lang('Past projects', 'Vergangene Projekte') ?></h2>

                <div class="row row-eq-spacing my-0">
                    <?php foreach ($past as $html) { ?>
                        <div class="col-md-6">
                            <?= $html ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } ?>
    </section>
<?php } ?>


<?php if ($Settings->featureEnabled('infrastructures')) { ?>
    <section id="infrastructures" style="display:none">

        <h2 class="title">
            <?= $Settings->infrastructureLabel() ?>
        </h2>

        <?php if ($count_infrastructures > 0) {
            include_once BASEPATH . "/php/Infrastructure.php";
            $Infra = new Infrastructure();
            $infrastructures = $osiris->infrastructures->find($infrastructure_filter, ['sort' => ["start_date" => -1, "end_date" => -1]]);
        ?>
            <table class="table">
                <tbody>
                    <?php foreach ($infrastructures as $infra) {
                        if (empty($infra)) continue;
                        $person_role = array_filter(DB::doc2Arr($infra['persons'] ?? []), function ($v) use ($user) {
                            return $v['user'] == $user;
                        });
                        if (empty($person_role)) continue;
                        $person_role = array_values($person_role)[0];
                    ?>
                        <tr>
                            <td>
                                <h6 class="m-0">
                                    <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infra['_id'] ?>" class="link">
                                        <?= lang($infra['name'], $infra['name_de'] ?? null) ?>
                                    </a>
                                    <br>
                                </h6>

                                <div class="text-muted mb-5">
                                    <?php if (!empty($infra['subtitle'])) { ?>
                                        <?= lang($infra['subtitle'], $infra['subtitle_de'] ?? null) ?>
                                    <?php } else { ?>
                                        <?= get_preview(lang($infra['description'], $infra['description_de'] ?? null), 300) ?>
                                    <?php } ?>
                                </div>
                                <div>
                                    <?= lang('Active as', 'Aktiv als') ?>
                                    <b class="text-primary"><?= $Infra->getRole($person_role['role']) ?></b>
                                    <?= lang('from', 'von') ?>
                                    <?= fromToYear($person_role['start'], $person_role['end'] ?? null, true) ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
            </table>

        <?php } else { ?>
            <?= lang('No infrastructures connected.', 'Noch keine Infrastrukturen verknüpft.') ?>
        <?php } ?>


    </section>
<?php } ?>


<section id="coauthors" style="display:none">
    <h2>
        <i class="ph ph-graph" aria-hidden="true"></i>
        <?= lang('Coauthor network of', 'Koautoren-Netzwerk von') ?> <?= $scientist['displayname'] ?>
    </h2>
    <p class="text-muted">
        <?= lang('Based on publications within the past 5 years.', 'Basierend auf Publikationen aus den vergangenen 5 Jahren.') ?>
    </p>
    <div class="box">
        <div class="row">
            <div class="col-md-8" style="max-width: 80rem">
                <div id="chord"></div>
            </div>
            <div class="col-md-4">
                <div id="legend"></div>
            </div>
        </div>
    </div>

</section>

<?php if ($count_teaching > 0) { ?>
    <section id="teaching" style="display: none;">

        <h2><?= lang('Teaching activities', 'Lehrtätigkeiten') ?></h2>

        <div class="row row-eq-spacing">
            <?php foreach ($teaching as $t) {
                $module = $osiris->teaching->findOne(['_id' => DB::to_ObjectID($t['_id'])]);
            ?>
                <div class="col-md-6">
                    <div class="box mb-0" id="<?= $t['_id'] ?>">
                        <div class="content">
                            <h5 class="mt-0">
                                <span class="highlight-text"><?= $module['module'] ?></span>
                                <?= $module['title'] ?>
                            </h5>

                            <em><?= $module['affiliation'] ?></em>
                        </div>

                        <hr>
                        <div class="content">
                            <?php
                            $activities = $t['doc'] ?? [];
                            if (count($activities) != 0) {
                            ?>
                                <table class="w-full">
                                    <?php foreach ($activities as $n => $doc) :
                                        if (!isset($doc['rendered'])) renderActivities(['_id' => $doc['_id']]);
                                    ?>
                                        <tr>
                                            <td class="pb-5">
                                                <?= $doc['rendered']['icon'] ?>
                                                <?= $doc['rendered']['web'] ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>


                            <?php } else { ?>

                                <?= lang('No activities connected.', 'Keine Aktivitäten verknüpft.') ?>

                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>

    </section>
<?php } ?>


<?php if ($Settings->featureEnabled('concepts')) { ?>
    <section id="concepts" style="display:none">
        <?php if (!empty($concepts)) :
        ?>

            <h3 class=""><?= lang('Concepts', 'Konzepte') ?></h3>
            <div class="box" id="concepts">
                <div class="content">
                    <?php foreach ($concepts as $concept) {
                        $score =  round($concept['score'] * 100);
                    ?><span class="concept" target="_blank" data-score='<?= $score ?>' data-name='<?= $concept['_id'] ?>' data-count='<?= $concept['count'] ?>' data-wikidata='<?= $concept['concept']['wikidata'] ?>'>
                            <div role="progressbar" aria-valuenow="67" aria-valuemin="0" aria-valuemax="100" style="--value: <?= $score ?>"></div>
                            <?= $concept['_id'] ?>
                        </span><?php } ?>
                </div>
            </div>
        <?php else : ?>
            <p>
                <?= lang('No concepts are assigned to this person.', 'Zu dieser Person sind keine Konzepte zugewiesen.') ?>
            </p>
        <?php endif; ?>
    </section>
<?php } ?>


<?php if ($Settings->featureEnabled('wordcloud')) { ?>
    <section id="wordcloud" style="display:none">
        <h3 class=""><?= lang('Word cloud') ?></h3>

        <p class="text-muted">
            <?= lang('Based on the title and abstract (if available) of publications in OSIRIS.', 'Basierend auf dem Titel und Abstract (falls verfügbar) von Publikationen in OSIRIS.') ?>
        </p>
        <div id="wordcloud-chart" style="max-width: 80rem" ;></div>
    </section>
<?php } ?>



<?php
if (isset($_GET['verbose'])) {
    dump($scientist, true);
}
?>