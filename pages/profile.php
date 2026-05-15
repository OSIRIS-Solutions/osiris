<?php

/**
 * Page to see scientists profile
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /profile/<username>
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
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

if (!isset($scientist['is_active'])) {
    $scientist['is_active'] = true; // default value if not set
    // update in database because it leads to problems in the frontend otherwise
    $osiris->persons->updateOne(['username' => $user], ['$set' => ['is_active' => true]]);
}
$full_spectrum = $_GET['fullspectrum'] ?? false;
?>


<!-- all necessary javascript -->
<script src="<?= ROOTPATH ?>/js/chart.min.js"></script>
<script src="<?= ROOTPATH ?>/js/chartjs-plugin-datalabels.min.js"></script>
<script src="<?= ROOTPATH ?>/js/d3.v4.min.js"></script>
<script src="<?= ROOTPATH ?>/js/popover.js"></script>
<script src="<?= ROOTPATH ?>/js/d3-chords.js?v=<?= OSIRIS_BUILD ?>"></script>
<script src="<?= ROOTPATH ?>/js/d3.layout.cloud.js"></script>

<!-- all variables for this page -->
<script>
    const CURRENT_USER = '<?= $user ?>';
    // const HIGHTLIGHTS = <?= json_encode($scientist['highlighted'] ?? []) ?>;
</script>
<script src="<?= ROOTPATH ?>/js/profile.js?v=<?= OSIRIS_BUILD ?>"></script>


<link rel="stylesheet" href="<?= ROOTPATH ?>/css/achievements.css?<?= filemtime(BASEPATH . '/css/achievements.css') ?>">

<style>
    .box.h-full {
        height: calc(100% - 2rem) !important;
    }

    .expertise {
        border-radius: var(--border-radius);
        background-color: white;
        border: var(--border-width) solid #afafaf;
        display: inline-block;
        padding: .2rem .8rem;
        box-shadow: var(--box-shadow);
        margin-right: .5rem;
    }

    .user-role {
        border-radius: var(--border-radius);
        background-color: white;
        border: var(--border-width) solid #afafaf;
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
        if ($scientist['is_guest'] ?? false) { ?>
            <span class="badge signal">
                <?= lang('Guest Account', 'Gast-Account') ?>
            </span>
        <?php }
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

    if (isset($scientist['new']) && defined('USER_MANAGEMENT') && USER_MANAGEMENT == 'AUTH' && $scientist['username'] == ($_SESSION['realuser'] ?? $_SESSION['username'])) { ?>
        <!-- print message to change password -->
        <div class="alert danger mt-10">
            <a class="link text-danger" href='<?= ROOTPATH ?>/user/edit/<?= $scientist['_id'] ?>#section-account'>
                <?= lang(
                    "You have not yet set a password. Please change your password now.",
                    "Du hast noch kein Passwort gesetzt. Bitte ändere jetzt dein Passwort."
                ) ?>
            </a>
        </div>
    <?php  } ?>

    <div class="btn-toolbar">

        <div class="btn-group btn-group-lg">
            <!-- <a class="btn primary outline" href="<?= ROOTPATH ?>/add-activity" data-toggle="tooltip" data-title="<?= lang('Add activity', 'Aktivität hinzufügen') ?>">
                <i class="ph-duotone ph-plus-circle ph-fw"></i>
            </a> -->
            <a href="<?= ROOTPATH ?>/my-activities" class="btn primary outline" data-toggle="tooltip" data-title="<?= lang('My activities', 'Meine Aktivitäten ') ?>">
                <i class="ph-duotone ph-folder-user ph-fw"></i>
            </a>
            <a class="btn primary outline" href="<?= ROOTPATH ?>/my-year/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('My Year', 'Mein Jahr') ?>">
                <i class="ph-duotone ph-calendar ph-fw"></i>
            </a>

            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="btn primary outline" href="<?= ROOTPATH ?>/preview/person/<?= $scientist['_id'] ?>" data-toggle="tooltip" data-title="<?= lang('Preview', 'Vorschau') ?>">
                    <i class="ph-duotone ph-eye ph-fw"></i>
                </a>
            <?php } ?>

        </div>
        <div class="btn-group btn-group-lg">
            <?php if ($show_achievements) { ?>
                <a class="btn primary outline" href="<?= ROOTPATH ?>/achievements" data-toggle="tooltip" data-title="<?= lang('My Achievements', 'Meine Errungenschaften') ?>">
                    <i class="ph-duotone ph-trophy ph-fw"></i>
                </a>
            <?php } ?>
        </div>

        <div class="btn-group btn-group-lg">
            <a class="btn primary outline" href="<?= ROOTPATH ?>/user/edit/<?= $scientist['_id'] ?>" data-toggle="tooltip" data-title="<?= lang('Edit user profile', 'Bearbeite Profil') ?>">
                <i class="ph-duotone ph-note-pencil ph-fw"></i>
                <!-- <?= lang('Edit user profile', 'Bearbeite Profil') ?> -->
            </a>
            <a href="<?= ROOTPATH ?>/claim" class="btn primary outline" data-toggle="tooltip" data-title="<?= lang('Claim activities', 'Aktivitäten beanspruchen') ?>">
                <i class="ph-duotone ph-hand ph-fw"></i>
                <!-- <?= lang('Claim activities', 'Aktivitäten beanspruchen') ?> -->
            </a>
        </div>

        <form action="<?= ROOTPATH ?>/download" method="post">
            <input type="hidden" name="filter[user]" value="<?= $user ?>">
            <input type="hidden" name="highlight" value="user">
            <input type="hidden" name="format" value="word">
            <input type="hidden" name="type" value="cv">

            <button class="btn primary outline large" data-toggle="tooltip" data-title="<?= lang('Export CV', 'CV exportieren') ?>">
                <i class="ph-duotone ph-identification-card text-primary ph-fw"></i>
            </button>
        </form>

        <!-- <div class="dropdown with-arrow">
            <button class="btn large square text-primary border-primary" data-toggle="dropdown" type="button" id="user-options" aria-haspopup="true" aria-expanded="false" title="<?= lang('More options', 'Weitere Optionen') ?>">
                <i class="ph ph-dots-three-vertical ph-fw text-primary" aria-hidden="true"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-center" aria-labelledby="user-options">
                <a href="<?= ROOTPATH ?>/user/permissions" class="item">
                    <i class="ph ph-users-three ph-fw text-primary"></i>
                    <?= lang('Show permissions', 'Einheiten bearbeiten') ?>
                </a>
            </div>
        </div> -->
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
            <a class="btn primary outline" href="<?= ROOTPATH ?>/my-year/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('The year of ', 'Das Jahr von ') . $scientist['first'] ?> ">
                <i class="ph ph-calendar ph-fw"></i>
            </a>
            <a href="<?= ROOTPATH ?>/my-activities?user=<?= $user ?>" class="btn primary outline" data-toggle="tooltip" data-title="<?= lang('All activities of ', 'Alle Aktivitäten von ') . $scientist['first'] ?>">
                <i class="ph ph-folder-user ph-fw"></i>
            </a>
            <?php if ($show_achievements) { ?>
                <a class="btn primary outline" href="<?= ROOTPATH ?>/achievements/<?= $user ?>" data-toggle="tooltip" data-title="<?= lang('Achievements of ', 'Errungenschaften von ') . $scientist['first'] ?>">
                    <i class="ph ph-trophy ph-fw"></i>
                </a>
            <?php } ?>
            <?php if ($Settings->featureEnabled('portal')) { ?>
                <a class="btn primary outline" href="<?= ROOTPATH ?>/preview/person/<?= $scientist['_id'] ?>" data-toggle="tooltip" data-title="<?= lang('Preview', 'Vorschau') ?>">
                    <i class="ph ph-eye ph-fw"></i>
                </a>
            <?php } ?>
        </div>

        <?php if ($Settings->hasPermission('user.edit')) { ?>
            <a class="btn large text-primary border-primary" href="<?= ROOTPATH ?>/user/edit/<?= $scientist['_id'] ?>" data-toggle="tooltip" data-title="<?= lang('Edit user profile', 'Bearbeite Profil') ?>">
                <i class="ph ph-edit ph-fw"></i>
            </a>
        <?php } ?>

        <?php
        $is_admin = $Settings->hasPermission('user.inactive') || $Settings->hasPermission('user.delete') || $Settings->hasPermission('user.password-reset');
        if ($is_admin) {
        ?>
            <div class="dropdown with-arrow">
                <button class="btn large square text-primary border-primary" data-toggle="dropdown" type="button" id="user-options" aria-haspopup="true" aria-expanded="false" title="<?= lang('More options', 'Weitere Optionen') ?>">
                    <i class="ph ph-dots-three-vertical ph-fw text-primary" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-center" aria-labelledby="user-options">
                    <?php if ($currentuser || $Settings->hasPermission('user.edit')) { ?>
                        <a href="<?= ROOTPATH ?>/user/units/<?= $user ?>" class="item">
                            <i class="ph ph-users-three ph-fw text-primary"></i>
                            <?= lang('Edit org. units', 'Einheiten bearbeiten') ?>
                        </a>
                    <?php } ?>
                    <?php if (strtoupper(USER_MANAGEMENT) == 'AUTH' && $Settings->hasPermission('user.password-reset')) { ?>
                        <a class="item" href="<?= ROOTPATH ?>/user/password-reset/<?= $scientist['_id'] ?>">
                            <i class="ph ph-key ph-fw text-primary"></i>
                            <?= lang('Reset password', 'Passwort zurücksetzen') ?>
                        </a>
                    <?php } ?>
                    <?php if ($Settings->hasPermission('user.inactive')) { ?>
                        <?php if (($scientist['is_active'] ?? true)) { ?>
                            <a class="item" href="<?= ROOTPATH ?>/user/inactivate/<?= $user ?>">
                                <i class="ph ph-user-circle-dashed ph-fw text-danger"></i>
                                <?= lang('Inactivate user', 'Nutzer:in inaktivieren') ?>
                            </a>
                        <?php } elseif ($Settings->hasPermission('user.edit')) { ?>
                            <a class="item" href="<?= ROOTPATH ?>/user/edit/<?= $user ?>#section-account">
                                <i class="ph ph-user-circle-plus ph-fw text-success"></i>
                                <?= lang('Re-activate user', 'Nutzer:in reaktivieren') ?>
                            </a>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($Settings->hasPermission('user.delete')) { ?>
                        <a class="item" href="<?= ROOTPATH ?>/user/delete/<?= $user ?>">
                            <i class="ph ph-trash ph-fw text-danger"></i>
                            <?= lang('Delete user', 'Nutzer:in löschen') ?>
                        </a>
                    <?php } ?>

                </div>
            </div>
        <?php } ?>


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
        'rendered.users' => $user,
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
        ['$match' => ['type' => 'publication', 'rendered.users' => $user, 'year' => ['$gte' => CURRENTYEAR - 4]]],
        ['$unwind' => '$rendered.users'],
        ['$match' => ['rendered.users' => ['$ne' => null]]],
        [
            '$group' => [
                '_id' => '$rendered.users',
                'count' => ['$sum' => 1]
            ]
        ],
    ])->toArray();
    $count_coauthors = count($coauthors) - 1;
    if ($count_coauthors > 3) { ?>
        <a onclick="navigate('coauthors')" id="btn-coauthors" class="btn">
            <i class="ph ph-users" aria-hidden="true"></i>
            <?= lang('Coauthors', 'Koautoren')  ?>
            <span class="index"><?= $count_coauthors ?></span>
        </a>
    <?php } ?>

    <?php
    $activities_filter = [
        'rendered.users' => $user,
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
        'rendered.users' => $user,
        'subtype' => ['$in' => $Settings->continuousTypes]
    ];
    $count_memberships = $osiris->activities->count($membership_filter);
    if ($count_memberships > 0) { ?>
        <a onclick="navigate('memberships')" id="btn-memberships" class="btn">
            <i class="ph ph-user-list" aria-hidden="true"></i>
            <?= lang('Ongoing activities', 'Laufende Aktivitäten')  ?>
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
        ['$match' => [
            'rendered.users' => $user,
            'module_id' => ['$ne' => null]
        ]],
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


    <?php if ($Settings->featureEnabled('spectrum')) { ?>
        <?php
        $spectrum_filter = [
            'rendered.users' => $user,
            'type' => 'publication',
            'openalex.topics' => ['$exists' => true, '$ne' => []]
        ];
        $count_spectrum = $osiris->activities->count($spectrum_filter);

        if ($count_spectrum > 0) { ?>
            <a onclick="navigate('spectrum')" id="btn-spectrum" class="btn">
                <i class="ph ph-lightbulb" aria-hidden="true"></i>
                <?= lang('Research Spectrum', 'Forschungs-Spektrum')  ?>
            </a>
    <?php }
    } ?>


    <?php if ($Settings->featureEnabled('wordcloud')) { ?>
        <?php
        $count_wordcloud = $osiris->activities->count(['title' => ['$exists' => true], 'rendered.users' => $user, 'type' => 'publication']);
        if ($count_wordcloud > 0) { ?>
            <a onclick="navigate('wordcloud')" id="btn-wordcloud" class="btn">
                <i class="ph ph-cloud" aria-hidden="true"></i>
                <?= lang('Word cloud')  ?>
            </a>
        <?php } ?>
    <?php } ?>

</nav>


<?php if ($currentuser) { ?>
    <section id="news">

        <?php
        $announcement_active = $Settings->isAnnouncementActive();
        if ($announcement_active) {
            $announcement = $Settings->get('announcement'); ?>

            <style>
                #announcement {
                    display: flex;
                    align-items: center;
                    position: relative;
                    padding: 1.5rem;
                }

                #announcement img#sophie-announcement {
                    max-width: 120px;
                    margin: -1rem 2rem -1rem 0;
                }

                #announcement h1,
                #announcement h2,
                #announcement h3,
                #announcement h4,
                #announcement h5,
                #announcement h6 {
                    color: var(--primary-color);
                    margin-top: 0;
                }

                #announcement h1 {
                    font-size: 2.2rem;
                }

                #announcement h2 {
                    font-size: 1.8rem;
                }

                #announcement h3 {
                    font-size: 1.6rem;
                }
            </style>
            <div class="box mb-0 border-primary primary" id="announcement" data-announcement-version="<?= strtotime($announcement['updated_at'] ?? 'now') ?>">

                <img src="<?= ROOTPATH ?>/img/sophie/sophie-announced.png" alt="Announcement" id="sophie-announcement">
                <div>
                    <a data-dismiss="modal" class="float-right" role="button" aria-label="Close" onclick="dismissAnnouncementSession()">
                        <i class="ph ph-x"></i>
                    </a>
                    <?php
                    $en = trim($announcement['en'] ?? null);
                    $de = trim($announcement['de'] ?? null);
                    if (!empty(strip_tags($en)) && !empty(strip_tags($de))) {
                        echo  lang($en, $de);
                    } elseif (!empty(strip_tags($en))) {
                        echo $en;
                    } elseif (!empty(strip_tags($de))) {
                        echo $de;
                    }
                    ?>
                    <button class="btn primary small" onclick="dismissAnnouncement()">
                        <i class="ph ph-x-circle"></i>
                        <?= lang('Don’t show again', 'Nicht mehr anzeigen') ?>
                    </button>
                </div>
            </div>
            <script>
                function dismissAnnouncement() {
                    fetch('<?= ROOTPATH ?>/crud/users/dismiss-announcement', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    }).then(() => {
                        document.getElementById('announcement').remove();
                    });
                }

                function dismissAnnouncementSession() {
                    const announcementVersion = $('#announcement').data('announcement-version');
                    localStorage.setItem('osiris_hide_announcement_hidden_' + announcementVersion, Date.now() + 24 * 60 * 60 * 1000);
                    document.getElementById('announcement').remove();
                }
                $(document).ready(function() {
                    const announcementVersion = $('#announcement').data('announcement-version');
                    const hideUntil = localStorage.getItem('osiris_hide_announcement_hidden_' + announcementVersion);
                    if (hideUntil && Date.now() < hideUntil) {
                        $('#announcement').remove();
                    }
                });
            </script>
        <?php

        }

        ?>

        <style>
            .dashboard {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1.5rem;
            }
        </style>
        <div class="row row-eq-spacing my-0">
            <div class="col-md-6">
                <?php if (isset($n_notifications) && $n_notifications) { ?>
                    <?php if ($Settings->featureEnabled('quarterly-reporting', true) && isset($notifications['approval'])) {
                    ?>
                        <div class="box padded d-flex align-items-center">
                            <div>
                                <b>
                                    <?= lang("You can now approve the past quarter", "Du kannst jetzt das vergangene Quartal freigeben") ?>
                                </b>
                                <p class="text-muted my-5 font-size-12">
                                    <?= lang(
                                        'To complete the quarterly review, please confirm that all activities from the previous quarter have been entered and are up to date.',
                                        'Für den Quartalsabschluss brauchen wir seine Bestätigung, dass alle Aktivitäten aus dem vergangenen Quartal erfasst und aktuell sind.'
                                    ) ?>
                                </p>
                                <a class="btn success filled" href="<?= ROOTPATH ?>/my-year/<?= $_SESSION['username'] ?>?quarter=<?= $quarter ?>">
                                    <?= lang('Review & Approve', 'Überprüfen & Freigeben') ?>
                                </a>
                            </div>

                            <img src="<?= ROOTPATH ?>/img/sophie/sophie-checklist.png" class="w-100">
                        </div>
                    <?php } ?>

                    <script>
                        function openNotifications() {
                            $('.dropdown-menu.dropdown-menu-center.notifications').closest('.dropdown').toggleClass('show');
                        }
                    </script>
                <?php } ?>

                <?php if ($Settings->featureEnabled('new-colleagues')) { ?>
                    <!-- Show new users -->
                    <div class="box">
                        <div class="content">
                            <h4 class="title">
                                <?= lang('New Colleagues', 'Neue Kolleg:innen') ?>
                            </h4>
                            <p class="text-muted">
                                <?= lang('Recently added colleagues in OSIRIS.', 'Kürzlich hinzugefügte Kolleg:innen in OSIRIS.') ?>
                            </p>
                            <?php
                            $new_colleagues = $osiris->persons->find(
                                ['created' => ['$exists' => true], 'is_active' => ['$ne' => false]],
                                [
                                    'sort' => ['created' => -1],
                                    'limit' => 3,
                                ]
                            )->toArray();
                            ?>
                            <table class="table simple">
                                <?php foreach ($new_colleagues as $colleague) { ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?= $Settings->printProfilePicture($colleague['username'], 'profile-img small mr-20') ?>
                                                <div>
                                                    <h5 class="my-0">
                                                        <a href="<?= ROOTPATH ?>/profile/<?= $colleague['username'] ?>" class="">
                                                            <?= $colleague['displayname'] ?? $colleague['username'] ?>
                                                        </a>
                                                    </h5>
                                                    <?= lang($colleague['position'] ?? '', $colleague['position_de'] ?? null) ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </div>
                    </div>
                <?php } ?>

                <?php if ($Settings->featureEnabled('new-publications', true)) { ?>
                    <div class="box">
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
                <?php } ?>
            </div>

            <div class="col-md-6 h-full">

                <?php if (($Settings->featureEnabled('quarterly-reporting', true) && $Settings->hasPermission('report.dashboard')) && $currentuser) {
                    $n_scientists = $osiris->persons->count(["roles" => 'scientist', "is_active" => true]);
                    $n_approved = $osiris->persons->count(["roles" => 'scientist', "is_active" => true, "approved" => $lastquarter]);
                    $progress = ($n_scientists > 0) ? ($n_approved / $n_scientists) * 100 : 0;
                ?>
                    <div class="box">
                        <div class="content">
                            <div class="d-flex justify-content-between align-items-center mb-10">
                                <h5 class="title m-0"><?= lang('Last quarter', 'Vergangenes Quartal') ?></h5>
                                <button class="btn small" onclick="loadModal('components/controlling-approved', {q: '<?= $Q ?>', y: '<?= $Y ?>'})">
                                    <i class="ph ph-magnifying-glass-plus"></i> <?= lang('Details') ?>
                                </button>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?= $progress ?>%" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-10 font-size-12">
                                <span class="text-muted">
                                    <?= lang(
                                        "$n_approved of $n_scientists scientists have approved their activities.",
                                        "$n_approved von $n_scientists Wissenschaftler:innen haben ihre Aktivitäten freigegeben."
                                    ) ?>
                                </span>
                                <b class="badge success"><?= $lastquarter ?></b>
                            </div>
                        </div>
                    </div>
                <?php } ?>



                <?php if ($Settings->featureEnabled('events', true)) { ?>

                    <?php if ($Settings->featureEnabled('deadlines', false)) {
                        include_once BASEPATH . '/php/Vocabulary.php';
                        $Vocabulary = new Vocabulary();
                    ?>
                        <div class="box mb-20">
                            <div class="content">
                                <?php if ($Settings->hasPermission('deadlines.edit')) { ?>
                                    <a href="<?= ROOTPATH ?>/deadlines/new" class="float-md-right btn small">
                                        <i class="ph ph-plus"></i>
                                        <?= lang('Add', 'Hinzufügen') ?>
                                    </a>
                                <?php } ?>
                                <h4 class="title">
                                    <a href="<?= ROOTPATH ?>/deadlines" class="link">
                                        <?= lang('Deadlines') ?>
                                    </a>
                                </h4>
                                <?php
                                $deadlines = $osiris->deadlines->find(
                                    [
                                        '$and' => [
                                            ['date' => ['$gte' => date('Y-m-d', strtotime('-3 days'))]],
                                            ['date' => ['$lte' => date('Y-m-d', strtotime('+12 month'))]]
                                        ],
                                        // 'dismissed' => ['$ne' => $user],
                                        'roles' => ['$in' => $Settings->roles]
                                    ],
                                    ['sort' => ['date' => 1], 'projection' => [
                                        '_id' => 0,
                                        'id' => ['$toString' => '$_id'],
                                        'date' => 1,
                                        'type' => 1,
                                        'title' => 1
                                    ]]
                                )->toArray();
                                $deadlineTypes = $Vocabulary->getValues('deadline-type');
                                $deadlineTypes = array_column($deadlineTypes, null, 'id');
                                ?>
                                <div id="deadline-timeline" class="mt-20 mb-10"></div>
                                <style>
                                    #deadline-timeline {
                                        position: relative;
                                    }

                                    #deadline-timeline svg {
                                        width: 100%;
                                        height: auto;
                                        display: block;
                                    }

                                    #deadline-timeline .deadline-dot {
                                        transition: transform .15s ease;
                                    }
                                </style>
                                <script>
                                    $(document).ready(function() {

                                        deadlineTimeline({
                                            divSelector: '#deadline-timeline',
                                            monthsAhead: 12,
                                            startLabel: lang('Today', 'Heute'),
                                            endLabel: lang('+12 months', '+12 Monate'),
                                            typeInfo: <?= json_encode($deadlineTypes) ?>,
                                            deadlines: <?= json_encode($deadlines) ?>
                                        });
                                        // deadlineTimeline(options);
                                    });
                                </script>
                            </div>
                        </div>

                    <?php } ?>

                    <div class="box">
                        <div class="content">
                            <?php if ($Settings->hasPermission('conferences.edit')) { ?>
                                <a href="<?= ROOTPATH ?>/conferences/new" class="float-md-right btn small">
                                    <i class="ph ph-plus"></i>
                                    <?= lang('Add', 'Hinzufügen') ?>
                                </a>
                            <?php } ?>

                            <h4 class="title">
                                <a href="<?= ROOTPATH ?>/conferences" class="link">
                                    <?= lang('Events') ?>
                                </a>
                            </h4>
                            <p class="text-muted font-size-12">
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
                                                    <a href="<?= ROOTPATH ?>/conferences/view/<?= $c['_id'] ?>">
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

                        </div>
                    </div>
                <?php } ?>
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
                        <?php if ($Settings->hasPermission('raw-data')) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('OSIRIS-ID', 'OSIRIS-ID') ?></span>
                                    <?= $scientist['_id'] ?>
                                </td>
                            </tr>
                        <?php } ?>
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
                        <?php if ($active('mobile') && isset($scientist['mobile'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Mobile', 'Mobil') ?></span>
                                    <a href="tel:<?= $scientist['mobile'] ?>"><?= $scientist['mobile'] ?></a>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if ($active('room') && isset($scientist['room'])) { ?>
                            <tr>
                                <td>
                                    <span class="key"><?= lang('Room', 'Raum') ?></span>
                                    <?= $scientist['room'] ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (isset($scientist['orcid_validated']) && $scientist['orcid_validated']) { ?>
                            <tr>
                                <td>
                                    <span class="key">ORCID</span>

                                    <a href="http://orcid.org/<?= $scientist['orcid'] ?>" target="_blank" rel="noopener noreferrer">
                                        <img src="/img/orcid.svg" alt="ORCID iD" width="16" height="16"/>
                                        <?= $scientist['orcid'] ?>
                                    </a>

                                </td>
                            </tr>
                        <?php } elseif  (!empty($scientist['orcid'] ?? null)) { ?>
                            <tr>
                                <td>
                                    <span class="key">ORCID</span>

                                    <a href="http://orcid.org/<?= $scientist['orcid'] ?>" target="_blank" rel="noopener noreferrer">
                                        <img src="/img/orcid_unauth.svg" alt="ORCID iD" width="16" height="16" />
                                        <?= $scientist['orcid'] ?> 
                                    </a><?= lang('(unauthenticated)', '(nicht authentifiziert)') ?>

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

                <?php if ($active('research')) { ?>
                    <div class="content">

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


                <?php if (isset($scientist['highlighted'])) { ?>
                    <div class="content">
                        <h4 class="title">
                            <?= lang('Highlighted Research', 'Hervorgehobene Forschung') ?>
                        </h4>
                        <table class="table simple">
                            <?php
                            $highlights = DB::doc2Arr($scientist['highlighted']);
                            foreach ($highlights as $h) {
                                $pub = $osiris->activities->findOne(['_id' => DB::to_ObjectID($h)], ['projection' => ['rendered' => 1]]);
                                if ($pub) {
                                    echo '<tr><td class="w-50">';
                                    echo $pub['rendered']['icon'] ?? '';
                                    echo '</td><td>';
                                    echo $pub['rendered']['web'] ?? '';
                                    echo '</td></tr>';
                                }
                            }
                            ?>
                        </table>


                        <?php if ($currentuser || $Settings->hasPermission('user.edit')) { ?>
                            <p class="text-muted font-size-12">
                                <i class="ph ph-edit"></i> <?= lang('You can highlight/unhighlight publications by clicking on them and changing the "Displayed in your profile" option.', 'Du kannst Publikationen hervorheben/entfernen, indem du sie anklickst und die Option "Darstellung in deinem Profil" änderst.') ?>
                            </p>
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
                        <h4 class="title"><?= lang('Ongoing activities', 'Laufende Aktivitäten') ?></h4>
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
                <details class="collapse-panel">
                    <summary class="collapse-header">
                        <h4 class="m-0"><?= lang('Past activities', 'Vergangene Tätigkeiten') ?></h4>
                    </summary>
                    <div class="collapse-content p-0 pb-5">
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
                </details>
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
                                <?= $Project->widgetLarge($user, false, 'proposals') ?>
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

        <div class="collapse-group">
            <?php foreach ($teaching as $t) {
                $module = $osiris->teaching->findOne(['_id' => DB::to_ObjectID($t['_id'])]);
                $aff = $module['affiliation'] ?? '';
                if (DB::is_ObjectID($aff)) {
                    $aff = $osiris->organizations->findOne(['_id' => DB::to_ObjectID($aff)], ['projection' => ['name' => 1]]);
                    $aff = $aff['name'] ?? '';
                }
                $activities = $t['doc'] ?? [];
            ?>
                <details class="collapse-panel" id="<?= $t['_id'] ?>">
                    <summary class="collapse-header">
                        <h5 class="mt-0">
                            <strong class="text-primary"><?= $module['module'] ?></strong>:
                            <?= $module['title'] ?>
                            <span class="index-pill"><?= count($activities) ?></span>
                        </h5>

                        <em><?= $aff ?></em>
                    </summary>

                    <div class="collapse-content p-0">
                        <?php
                        if (count($activities) != 0) {
                        ?>
                            <table class="table simple">
                                <?php foreach ($activities as $n => $doc) :
                                    if (!isset($doc['rendered'])) renderActivities(['_id' => $doc['_id']]);
                                ?>
                                    <tr>
                                        <td style="padding-left: 4rem;">
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
                </details>
            <?php } ?>

    </section>
<?php } ?>


<?php if ($Settings->featureEnabled('spectrum')) { ?>
    <section id="spectrum" style="display:none">
        <h2>
            <?= lang('Research spectrum of', 'Forschungsspektrum von') ?> <?= $scientist['displayname'] ?>
        </h2>
        <?php
        $show_link = true;
        if (!$full_spectrum) {
            if ($count_spectrum < 10) {
                // if there are less than 10 publications with topics, show the full spectrum without filtering by year to ensure meaningful results
                $full_spectrum = true;
                $show_link = false;
            } else {
                // limit spectrum by default to the last 5 years to show current research focus
                // but only if there are more than 10 publications in the last 5 years to ensure meaningful results
                $spectrum_filter['year'] = ['$gte' => CURRENTYEAR - 4];
                if ($osiris->activities->count($spectrum_filter) < 10) {
                    unset($spectrum_filter['year']);
                    $full_spectrum = true;
                    $show_link = false;
                }
            }
        }

        $spectrum = $osiris->activities->aggregate([
            ['$match' => $spectrum_filter],

            // total number of matched activities
            ['$group' => [
                '_id' => null,
                'total' => ['$sum' => 1],
                'docs' => ['$push' => '$$ROOT']
            ]],
            ['$unwind' => '$docs'],
            ['$unwind' => '$docs.openalex.topics'],

            // group by topic id
            ['$group' => [
                '_id' => '$docs.openalex.topics.id',
                'count' => ['$sum' => 1],
                'sumScore' => ['$sum' => '$docs.openalex.topics.score'],
                'topic' => ['$first' => '$docs.openalex.topics'],
                'total' => ['$first' => '$total']
            ]],

            // compute averages + share
            ['$addFields' => [
                'avg_score' => ['$divide' => ['$sumScore', '$count']],
                'share' => ['$divide' => ['$count', '$total']],
                // optional combined weight (tweakable)
                'weight' => ['$multiply' => [
                    ['$divide' => ['$count', '$total']],
                    ['$divide' => ['$sumScore', '$count']]
                ]]
            ]],

            // filter noise
            ['$match' => ['share' => ['$gte' => 0.05], 'count' => ['$gte' => 2]]],
            ['$sort' => ['weight' => -1]],
            ['$limit' => 25]
        ])->toArray();
        if (!empty($spectrum)) :
        ?>
            <?php if ($show_link) { ?>
                <p class="text-muted mb-0">
                    <?php if (!$full_spectrum && $count_spectrum > 10) { ?>
                        <?= lang('Based on publications within the past 5 years.', 'Basierend auf Publikationen aus den vergangenen 5 Jahren.') ?>
                        <a href="?fullspectrum=1#section-spectrum"><?= lang('View full spectrum', 'Vollständiges Spektrum anzeigen') ?></a>
                    <?php } elseif ($full_spectrum) { ?>
                        <?= lang('Based on all publications in OSIRIS.', 'Basierend auf allen Publikationen in OSIRIS.') ?>
                        <a href="?#section-spectrum"><?= lang('View recent spectrum', 'Aktuelles Spektrum anzeigen') ?></a>
                    <?php } ?>
                </p>
            <?php } ?>
        <?php
            include_once BASEPATH . "/php/Spectrum.php";
            Spectrum::render($spectrum, $count_spectrum);
        else : ?>
            <p>
                <?= lang('We do not have enough data to display a research spectrum for this scientist yet. This could be because there are not enough publications in OSIRIS, or because the publications are not well covered by OpenAlex data.', 'Wir haben noch nicht genügend Daten, um ein Forschungsspektrum für diese Person anzuzeigen. Das könnte daran liegen, dass es noch nicht genügend Publikationen in OSIRIS gibt oder dass die Publikationen nicht gut von OpenAlex abgedeckt sind.') ?>
            </p>
        <?php endif; ?>
    </section>
<?php } ?>


<?php if ($Settings->featureEnabled('wordcloud')) { ?>
    <section id="wordcloud" style="display:none">
        <h3 class=""><?= lang('Word cloud') ?></h3>

        <p class="text-muted">
            <?= lang('Based on the title and abstract (if available) of activities in OSIRIS.', 'Basierend auf dem Titel und Abstract (falls verfügbar) von Aktivitäten in OSIRIS.') ?>
        </p>
        <div id="wordcloud-chart" style="max-width: 80rem" ;></div>
    </section>
<?php } ?>



<?php
if (isset($_GET['verbose'])) {
    dump($scientist, true);
}
?>