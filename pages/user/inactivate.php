<?php

/**
 * Page to inactive a user
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /user/delete/<username>
 *
 * @package     OSIRIS
 * @since       1.2.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

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
                    <td class="text-danger">
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
                        <?= $data['username'] ?>.jpg
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