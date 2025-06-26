<?php

/**
 * The detail view of an committee
 * Created in cooperation with DSMZ
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.5.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . "/php/committee.php";
include_once BASEPATH . "/php/Project.php";

$Project = new Project();

$edit_perm = ($committee['created_by'] == $_SESSION['username'] || $Settings->hasPermission('committees.edit'));

?>

<div class="committee">

    <h1 class="title">
        <?= $committee['name'] ?>
    </h1>

    <div class="btn-toolbar">
        <?php if ($Settings->hasPermission('committees.edit')) { ?>
            <a href="<?= ROOTPATH ?>/committees/edit/<?= $committee['_id'] ?>" class="btn btn-primary">
                <i class="ph ph-edit"></i>
                <?= lang('Edit committee', 'Gremium bearbeiten') ?>
            </a>
        <?php } ?>
    </div>

    <table class="table">
        <tbody>
            <tr>
                <td>
                    <?= Committee::getIcon($committee['type'], 'ph-fw ph-2x m-0 text-primary') ?>
                    <?= Committee::getType($committee['type']) ?>
                </td>
            </tr>
            <?php if (!empty($committee['link'] ?? '')) { ?>
                <tr>
                    <td>
                        <span class="key"><?= lang('Link') ?>:</span>
                        <a href="<?= $committee['link'] ?>" target="_blank" rel="noopener noreferrer">
                            <?= $committee['link'] ?>
                            <i class="ph ph-arrow-square-out"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
            <?php if (!empty($committee['description'] ?? '')) { ?>
                <tr>
                    <td>
                        <span class="key"><?= lang('Description', 'Beschreibung') ?>:</span>
                        <div class="text-muted">
                            <?= $committee['description'] ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <!-- parent_committee -->
            <?php if (!empty($committee['parent_committee'] ?? '')) {
                $parent = $osiris->committees->findOne(['_id' => $committee['parent_committee']]);
                if (empty($parent)) {
            ?>
                    <tr>
                        <td>
                            <?= lang('Parent committee', 'Übergeordnetes Gremium') ?>:
                            <a href="<?= ROOTPATH ?>/committees/view/<?= $parent['_id'] ?>" class="link">
                                <?= $parent['name'] ?>
                            </a>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>


<style>
    .module {
        border: none;
        box-shadow: none;
        padding: 0;
        margin: 0;
    }

    a.module:hover {
        box-shadow: none;
        transform: none;
        color: var(--primary-color);
    }
</style>

<h2>
    <?= lang('Connected activities', 'Verknüpfte Aktivitäten') ?>
</h2>
<div class="mt-20 w-full">
    <table class="table dataTable responsive" id="activities-table">
        <thead>
            <tr>
                <th><?= lang('Type', 'Typ') ?></th>
                <th><?= lang('Activity', 'Aktivität') ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
    $(document).ready(function() {
        // Initialize the DataTable
        activitiesTable = initActivities('#activities-table', {
            filter: {
                'committee': '<?= $id ?>'
            }
        })
    });
</script>


<?php
$persons = $osiris->activities->aggregate([
    ['$match' => ['committee' => $id]],
    ['$unwind' => '$authors'],
    ['$group' => [
        '_id' => '$authors._id',
        'docs' => ['$push' => '$$ROOT'],
        'person' => ['$first' => '$authors'],
    ]],
    ['$sort' => ['last' => 1]]
])->toArray();
?>

<h2>
    <?= lang('Connected persons', 'Verknüpfte Personen') ?>
</h2>
<div class="mt-20 w-full">
    <table class="table dataTable responsive" id="persons-table">
        <tbody>
            <?php foreach ($persons as $person) {
                $docs = DB::doc2Arr($person['docs'] ?? []);
                $person = $person['person'] ?? [];
                if (empty($person)) continue;
                $username = $person['user'] ?? '';
                if (empty($username)) continue;
                $current = array_filter($docs, function ($doc) {
                    return $doc['end_date'] === null || $doc['end_date'] > time();
                })[0] ?? [];
            ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">

                            <?= $Settings->printProfilePicture($username, 'profile-img small mr-20') ?>
                            <div>
                                <h5 class="my-0">
                                    <a href="<?= ROOTPATH ?>/profile/<?= $username ?>" class="">
                                        <?= $person['first'] . ' ' . $person['last'] ?>
                                    </a>
                                </h5>
                                <?php if (!empty($current)) { ?>
                                    <?= $current['role'] ?? '' ?>
                                    <br>
                                    <?= fromToYear($current['start'], $current['end']) ?>
                                <?php } else { ?>
                                    <span class="text-muted">
                                        <?= lang('No current activity', 'Keine aktuelle Aktivität') ?>
                                    </span>
                                <?php } ?>
                            </div>

                        </div>
                        <?php
                        $other = array_filter($docs, function ($doc) use ($current) {
                            return $doc['_id'] !== $current['_id'];
                        });
                        if (!empty($other)) {
                        ?>
                            <div class="committees mt-20">
                                <h5 class="title">
                                    <?= lang('Previous activities', 'Frühere Aktivitäten') ?>
                                </h5>
                                <?php foreach ($other as $doc) { ?>
                                    <a href="<?= ROOTPATH ?>/activities/view/<?= $doc['_id'] ?>">
                                        <b>
                                            <?= $doc['role'] ?? '' ?>
                                        </b>
                                        <br>
                                        <span class="text-muted">
                                            <?= fromToDate($doc['start_date'], $doc['end_date'], true) ?>
                                        </span>
                                    </a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>


<?php if ($Settings->hasPermission('committees.delete')) { ?>
    <button type="button" class="btn danger mt-20" id="delete-committee" onclick="$('#delete-committee-confirm').toggle();$(this).toggle();">
        <i class="ph ph-trash"></i>
        <?= lang('Delete committee', 'Gremium löschen') ?>
    </button>

    <div class="mt-20 alert danger" style="display: none;" id="delete-committee-confirm">
        <form action="<?= ROOTPATH ?>/crud/committees/delete/<?= $committee['_id'] ?>" method="post">
            <h4 class="title">
                <?= lang('Delete committee', 'Gremium löschen') ?>
            </h4>
            <p>
                <?= lang('Are you sure you want to delete this committee?', 'Sind Sie sicher, dass Sie diese Gremium löschen möchten?') ?>
            </p>
            <button type="submit" class="btn danger">
                <?= lang('Delete', 'Löschen') ?>
            </button>
        </form>
    </div>
<?php } ?>



<?php if (isset($_GET['verbose'])) {
    dump($committee, true);
} ?>