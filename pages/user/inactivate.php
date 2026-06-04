<?php

/**
 * Page to inactive a user
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /user/delete/<username>
 *
 * @package     OSIRIS
 * @since       1.2.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
$data = $data ?? [];
$user = $data['username'] ?? null;
if (!$user) {
    echo '<div class="alert alert-danger">No user specified</div>';
    return;
}
?>

<h1>
    <?= lang('Inactivate', 'Inaktivieren von') ?>
    <?= $data['name'] ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/users/inactivate/<?= $user ?>" method="post">

    <p class="text-danger">
        <?= lang(
            'Be aware that all personal data will be deleted, except for the name and the username:',
            'Sei dir bewusst, dass alle persönlichen Daten, abgesehen vom Namen und Nutzernamen gelöscht werden:'
        ) ?>
    </p>

    <table class="table">
        <tbody>
            <?php
            $keep = [
                '_id',
                'displayname',
                'formalname',
                'first_abbr',
                'updated',
                'updated_by',
                "academic_title",
                "first",
                "last",
                "name",
                "orcid",
                "units",
                "username",
                "created",
                "created_by",
                'uniqueid',
            ];

            foreach ($data as $key => $value) {
                if (empty($value)) continue;
                if (in_array($key, ['_id', 'displayname', 'formalname', 'first_abbr', 'updated', 'updated_by', 'is_active'])) continue;
                $delete = true;
                if (in_array($key, $keep)) {
                    $delete = false;
                }
            ?>
                <tr>
                    <th><?= $key ?></th>
                    <td>
                        <?php
                        if ($key == 'units') {
                            $value = array_column(DB::doc2Arr($value), 'unit');
                        }
                        if (empty($value)) {
                            echo '-';
                        } else if ($value instanceof MongoDB\Model\BSONArray && count($value) > 0 && is_string($value[0])) {
                            echo implode(', ', DB::doc2Arr($value));
                        } else if (is_array($value) && count($value) > 0 && is_string($value[0])) {
                            echo implode(', ', $value);
                        } else if (is_string($value)) {
                            echo $value;
                        } else {
                            echo json_encode($value, JSON_UNESCAPED_SLASHES);
                        } ?>
                    </td>
                    <td class="text-danger no-wrap">
                        <?php if ($delete) { ?>
                            <i class="ph ph-trash"></i>
                            <?= lang('Delete', 'Wird gelöscht') ?>
                        <?php } ?>

                    </td>
                </tr>
            <?php } ?>
            <?php if (file_exists(BASEPATH . "/img/users/$user.jpg")) { ?>
                <tr>
                    <th>
                        profile_picture
                    </th>
                    <td>
                        <?= $user ?>.jpg
                    </td>
                    <td class="text-danger">
                        <i class="ph ph-trash"></i>
                        <?= lang('Delete', 'Wird gelöscht') ?>
                    </td>
                </tr>
            <?php } ?>
            <!-- Todo: add delete for DB -->


        </tbody>
    </table>


    <?php
    $running_projects = $osiris->projects->find(
        [
            'persons' => ['$elemMatch' => ['user' => $user, '$or' => [['end' => null], ['end' => ['$gt' => date('Y-m-d')]]]]],
            'end_date' => ['$gt' => date('Y-m-d')]
        ],
        ['projection' => ['name' => 1]]
    )->toArray();
    if (count($running_projects) > 0) { ?>
        <h5>
            <?= lang('Running Projects', 'Laufende Projekte') ?>
        </h5>
        <p>
            <?= lang(
                'The user is assigned to the following <b>running projects</b>. Inactivating the user will not remove them from the projects but end the association.',
                'Die Person ist den folgenden <b>laufenden Projekten</b> zugeordnet. Das Inaktivieren der Person wird sie nicht aus den Projekten entfernen, sondern die Zuordnung beenden.'
            ) ?>
        </p>
        <ul class="list">
            <?php foreach ($running_projects as $project) { ?>
                <li>
                    <a href="<?= ROOTPATH ?>/projects/view/<?= $project['_id'] ?>">
                        <?= $project['name'] ?? 'No name' ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <?php
    // ongoing activities
    $ongoing_activities = $osiris->activities->find(
        [
            'subtype' => ['$in' => $Settings->continuousTypes],
            'rendered.users' => $user,
            // has only one user
            'authors' => ['$size' => 1],
            '$or' => [
                ['end_date' => null],
                ['end_date' => ['$gt' => date('Y-m-d')]]
            ]
        ],
        ['projection' => ['title' => '$rendered.title']]
    )->toArray();
    if (count($ongoing_activities) > 0) { ?>
        <h5>
            <?= lang('Ongoing Activities', 'Laufende Aktivitäten') ?>
        </h5>
        <p>
            <?= lang(
                'The user is involved as only person in the following <b>ongoing activities</b>. Inactivating the user will not remove them from the activities but end the activity.',
                'Die Person ist an den folgenden <b>laufenden Aktivitäten</b> als einzige Person beteiligt. Das Inaktivieren der Person wird sie nicht aus den Aktivitäten entfernen, sondern die Laufzeit der Aktivität beenden.'
            ) ?>
        </p>
        <ul class="list">
            <?php foreach ($ongoing_activities as $activity) { ?>
                <li>
                    <a href="<?= ROOTPATH ?>/activities/view/<?= $activity['_id'] ?>">
                        <?= $activity['title'] ?? 'No title' ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <?php
    // ongoing infrastructures
    $ongoing_infrastructures = $osiris->infrastructures->find(
        [
            'persons' => ['$elemMatch' => ['user' => $user, '$or' => [['end' => null], ['end' => ['$gt' => date('Y-m-d')]]]]],
            '$or' => [
                ['end_date' => null],
                ['end_date' => ['$gt' => date('Y-m-d')]]
            ]
        ],
        ['projection' => ['name' => 1]]
    )->toArray();
    if (count($ongoing_infrastructures) > 0) { ?>
        <h5>
            <?= lang('Ongoing Infrastructures', 'Laufende Infrastrukturen') ?>
        </h5>
        <p>
            <?= lang(
                'The user is involved in the following <b>ongoing infrastructures</b>. Inactivating the user will not remove them from the infrastructures but end the association.',
                'Die Person ist an den folgenden <b>laufenden Infrastrukturen</b> beteiligt. Das Inaktivieren der Person wird sie nicht aus den Infrastrukturen entfernen, sondern die Zuordnung beenden.'
            ) ?>
        </p>
        <ul class="list">
            <?php foreach ($ongoing_infrastructures as $infrastructure) { ?>
                <li>
                    <a href="<?= ROOTPATH ?>/infrastructures/view/<?= $infrastructure['_id'] ?>">
                        <?= $infrastructure['name'] ?? 'No name' ?>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>




    <p>
        <?= lang(
            'After inactivation, a hint will be displayed in the user profile, indicating that it is an inactive account.',
            'Nach dem Inaktivieren wird ein Hinweis auf dem Nutzerprofil zu sehen sein, dass es sich um einen inaktiven Benutzeraccount handelt'
        ) ?>
    </p>

    <button class="btn danger">
        <i class="ph ph-trash"></i>
        <?= lang('Inactivate', 'Inaktivieren') ?>
    </button>

</form>