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
    <?= lang('people.inactivate') ?>
    <?= $data['name'] ?>
</h1>

<form action="<?= ROOTPATH ?>/crud/users/inactivate/<?= $user ?>" method="post">

    <p class="text-danger">
        <?= lang('common.be_aware_that_all_personal_data_will_be_deleted_except_for_the_name_and_the') ?>
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
                            <?= lang('common.delete') ?>
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
                        <?= lang('common.delete') ?>
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
            <?= lang('people.running_projects') ?>
        </h5>
        <p>
            <?= lang('people.the_user_is_assigned_to_the_following_running_projects_inactivating_the_use') ?>
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
            <?= lang('people.ongoing_activities') ?>
        </h5>
        <p>
            <?= lang('people.the_user_is_involved_as_only_person_in_the_following_ongoing_activities_ina') ?>
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
            <?= lang('people.ongoing_infrastructures') ?>
        </h5>
        <p>
            <?= lang('people.the_user_is_involved_in_the_following_ongoing_infrastructures_inactivating') ?>
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
        <?= lang('people.after_inactivation_a_hint_will_be_displayed_in_the_user_profile_indicating') ?>
    </p>

    <button class="btn danger">
        <i class="ph ph-trash"></i>
        <?= lang('people.inactivate_inactivate') ?>
    </button>

</form>