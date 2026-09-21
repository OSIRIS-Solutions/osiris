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

?>

<h1>
    <?= lang('people.delete') ?>
    <?= $data['username'] ?>
</h1>

<div class="alert danger">
    <h5 class="title">
        <?= lang('people.warning_destructive_action') ?>
    </h5>
    <?= lang('people.you_are_about_to_delete_the_user_account') ?>
    <b><?= $data['username'] ?></b>
    <br>
    <?= lang('people.all_data_of_this_account_will_be_deleted_including_all_associated_activitie') ?>
    <br>
    <?= lang('people.please_note_that_activities_projects_etc_won_t_be_deleted_but_only_connecti') ?>
    <br>
    <b class="text-danger"><?= lang('people.this_action_cannot_be_undone') ?></b>
</div>

<form action="<?= ROOTPATH ?>/crud/users/delete/<?= $user ?>" method="post">
    <p class="text-danger">
        <?= lang('common.be_aware_that_all_personal_data_will_be_deleted_except_for_the_name_and_the') ?>
    </p>

    <table class="table">
        <tbody>
            <?php foreach ($data as $key => $value) {
                if (empty($value)) continue;
                if (in_array($key, ['_id', 'displayname', 'formalname', 'first_abbr', 'updated', 'updated_by'])) continue;
                $delete = true;
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
                    <td class="text-danger">
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
                        <?= $data['username'] ?>.jpg
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

    <p>
        <?=lang('people.furthermore_the_connection_to_the_following_entities_will_be_removed')?>
    </p>
    
    <table class="table">
        <tbody>
            <?php 
            $n = $osiris->activities->count(['rendered.users' => $user]);
            ?>
                <tr>
                    <th><?= lang('common.activities') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->projects->count(['persons.user' => $user]);
            ?>
                <tr>
                    <th><?= lang('common.projects') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->proposals->count(['persons.user' => $user]);
            ?>
                <tr>
                    <th><?= lang('common.proposals') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->infrastructures->count(['persons.user' => $user]);
            ?>
                <tr>
                    <th><?= lang('common.infrastructures') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
        </tbody>
    </table>
    <br>

    <button class="btn danger">
        <i class="ph ph-trash"></i>
        <?= lang('action.delete') ?>
    </button>

</form>