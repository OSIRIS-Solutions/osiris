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
    <?= lang('Delete', 'Löschen von') ?>
    <?= $data['username'] ?>
</h1>

<div class="alert danger">
    <h5 class="title">
        <?= lang('Warning! Destructive action!', 'Achtung! Zerstörerische Aktion!') ?>
    </h5>
    <?= lang('You are about to delete the user account:', 'Du bist dabei, das Benutzerkonto zu löschen:') ?>
    <b><?= $data['username'] ?></b>
    <br>
    <?= lang('All data of this account will be deleted, including all associated activities, projects, etc. This person will completely removed from the system!', 'Alle Daten dieses Kontos werden gelöscht, einschließlich aller Zugehörigkeiten von Aktivitäten, Projekte usw. Diese Person wird vollständig aus dem System entfernt!') ?>
    <br>
    <?= lang('Please note that activities, projects, etc. won’t be deleted but only connection to this account.', 'Bitte beachte, dass Aktivitäten, Projekte usw. nicht gelöscht werden, sondern nur die Verbindung zu diesem Konto.') ?>
    <br>
    <b class="text-danger"><?= lang('This action cannot be undone!', 'Diese Aktion kann nicht rückgängig gemacht werden!') ?></b>
</div>

<form action="<?= ROOTPATH ?>/crud/users/delete/<?= $user ?>" method="post">
    <p class="text-danger">
        <?= lang(
            'Be aware that all personal data will be deleted, except for the name and the username:',
            'Sei dir bewusst, dass alle persönlichen Daten, abgesehen vom Namen und Nutzernamen gelöscht werden:'
        ) ?>
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
        <?=lang('Furthermore, the connection to the following entities will be removed:', 'Außerdem wird die Verbindung zu den folgenden Entitäten entfernt:')?>
    </p>
    
    <table class="table">
        <tbody>
            <?php 
            $n = $osiris->activities->count(['rendered.users' => $user]);
            ?>
                <tr>
                    <th><?= lang('Activities', 'Aktivitäten') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->projects->count(['persons.user' => $user]);
            ?>
                <tr>
                    <th><?= lang('Projects', 'Projekte') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->proposals->count(['persons.user' => $user]);
            ?>
                <tr>
                    <th><?= lang('Proposals', 'Anträge') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->infrastructures->count(['persons.user' => $user]);
            ?>
                <tr>
                    <th><?= lang('Infrastructures', 'Infrastrukturen') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
            <?php
            $n = $osiris->teaching->count(['contact_person' => $user]);
            ?>
                <tr>
                    <th><?= lang('Teaching', 'Lehre') ?></th>
                    <td>
                        <?= $n ?>
                    </td>
                </tr>
        </tbody>
    </table>
    <br>

    <button class="btn danger">
        <i class="ph ph-trash"></i>
        <?= lang('Delete', 'Löschen') ?>
    </button>

</form>