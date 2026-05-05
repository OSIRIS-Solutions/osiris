<?php

/**
 * List of teaching modules
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 *
 * @package     OSIRIS
 * @since       1.8.0
 * 
 * @copyright	Copyright (c) 2026 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

include_once BASEPATH . "/php/Vocabulary.php";
$Vocabulary = new Vocabulary();

$user = $_SESSION['username'];

$topicsEnabled = $Settings->featureEnabled('topics') && $osiris->topics->count() > 0;
$tagsEnabled = $Settings->featureEnabled('tags');
?>


<h1>
    <i class="ph-duotone ph-calendar-dots"></i>
    <?= lang('Teaching Modules', 'Lehrveranstaltungen') ?>
</h1>


<div class="btn-toolbar">
    <!-- Statistics -->
    <a href="<?= ROOTPATH ?>/teaching/statistics" class="mr-20">
        <i class="ph ph-chart-bar"></i>
        <?= lang('Statistics', 'Statistiken') ?>
    </a>

    <?php if ($Settings->hasPermission('teaching.edit')) { ?>
        <a href="<?= ROOTPATH ?>/teaching/new" class="">
            <i class="ph ph-plus"></i>
            <?= lang('Add Teaching module', 'Lehrveranstaltung hinzufügen') ?>
        </a>
    <?php } ?>

</div>

<?php
$teaching = $osiris->teaching->aggregate([
    ['$addFields' => [
        'module_id' => [
            '$toString' => '$_id'
        ]
    ]],
    // join by string id to module_id in activities
    ['$lookup' => [
        'from' => 'activities',
        'localField' => 'module_id',
        'foreignField' => 'module_id',
        'as' => 'activities'
    ]],
    // count activities
    ['$addFields' => [
        'activity_count' => ['$size' => '$activities']
    ]],
])->toArray();
?>
<div class="teaching">

    <table class="table" id="teaching-table">
        <thead>
            <tr>
                <th><?= lang('Module No.', 'Modulnummer') ?></th>
                <th><?= lang('Title', 'Titel') ?></th>
                <th><?= lang('Teaching venue / University', 'Lehrort / Hochschule') ?></th>
                <th><?= lang('Number of Activities', 'Anzahl der Aktivitäten') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teaching as $module) {
                $affiliation = '';
                if (isset($module['organization'])) {
                    if (DB::is_ObjectID($module['organization'])) {
                        $org = $osiris->organizations->findOne(['_id' => DB::to_ObjectID($module['organization'])]);
                        if ($org) {
                            $affiliation = '<a href="' . ROOTPATH . '/organizations/view/' . $org['_id'] . '">' . $org['name'] . '</a>, ' . $org['location'];
                        } else {
                            $affiliation = $module['organization'];
                        }
                    }
                } else {
                    $affiliation = e($module['affiliation']);
                }
            ?>
                <tr>
                    <th>
                        <a href="<?= ROOTPATH ?>/teaching/view/<?= strval($module['_id']) ?>">
                            <?= e($module['module']) ?>
                        </a>
                    </th>
                    <td>
                        <?= e($module['title']) ?>
                    </td>
                    <td>
                        <?= $affiliation ?>
                    </td>
                    <td>
                        <?= intval($module['activity_count'] ?? 0) ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<script>
    const topicsEnabled = <?= $topicsEnabled ? 'true' : 'false' ?>;

    var dataTable;
    var rootpath = '<?= ROOTPATH ?>'

    $(document).ready(function() {
        dataTable = $('#teaching-table').DataTable({});

    });
</script>