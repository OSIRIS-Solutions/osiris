<?php

/**
 * Page to search for experts
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /expertise
 *
 * @package     OSIRIS
 * @since       1.0.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */

 if ($collection == 'keywords') {
    $icon ="tag";
    $title = $Settings->get('staff-keyword-name', 'Keywords');
} else if ($collection == 'expertise') {
    $icon = 'dumbbell';
    $title = lang('Expertise', 'Expertise');
 }  

$cursor = $osiris->persons->aggregate([
    [
        '$match' => [
            $collection => ['$exists' => true],
            'is_active' => ['$ne'=>false]
        ]
    ],
    ['$sort' => ['last' => 1]],
    ['$project' => [$collection => 1, 'displayname' => 1, 'username' => 1]],
    ['$unwind' => '$'.$collection],
    [
        '$group' => [
            '_id' => ['$toLower' => '$'.$collection],
            'count' => ['$sum' => 1],
            'users' => ['$push' => '$$ROOT']
        ]
    ],
    ['$sort' => ['count' => -1]]
]);

?>

<style>
    .badge {
        font-weight: bold;
    }
<?php foreach ($Groups->tree['children'] as $d) { ?>
    .badge.<?= $d['id'] ?> {
        background-color: <?= $d['color'] ?>30;
        color: <?= $d['color'] ?> !important;
    }
    .badge.<?= $d['id'] ?>:hover {
        background-color: <?= $d['color'] ?>;
        color: white !important;
    }
<?php } ?>
    
</style>


<h1 class="mt-0">
    <i class="ph-duotone ph-<?= $icon ?> mr-10"></i>
    <?= $title ?>
</h1>

<div class="form-group with-icon mw-full w-400">
    <input class="form-control mb-20" type="search" name="search" id="search" oninput="filterFAQ(this.value);" placeholder="Filter ..." value="<?= $_GET['search'] ?? '' ?>">
    <i class="ph ph-x" onclick="$(this).prev().val('');filterFAQ('')"></i>
</div>

<table class="table">

    <?php foreach ($cursor as $doc) { ?>
        <tr class="expertise">
            <td class="">
                    <h3 class="mt-0"><?= strtoupper($doc['_id']) ?></h3>
                    <small class="text-muted"><?= $doc['count'] ?> <?= lang('experts found:', 'Expert:innen gefunden:') ?></small><br>
                    <?php foreach ($doc['users'] as $u) { 
                        $color = 'var(--highlight-color) ';
                        $units = $Groups->getPersonUnit($u['username']);
                        $unit = '';
                        if (!empty($units)) {
                            $unit = $Groups->deptHierarchy(array_column($units, 'unit'), 1);
                            $color = $unit['color'] ?? $color;
                        }
                        ?><a href="<?= ROOTPATH ?>/profile/<?= $u['username'] ?>" class="badge mr-5 mb-5 <?=$unit['id'] ?? ''?>"><?= $u['displayname'] ?></a><?php 
                    } ?>
            </td>
        </tr>
    <?php } ?>
    <tr id="not-found" style="display: none;">
        <td class="text-center">
            <h3><?= lang('No results found.', 'Keine Ergebnisse gefunden.') ?></h3>
        </td>
    </tr>
</table>


<script>
    function filterFAQ(input) {
        if (input == "") {
            $('.expertise').show()
            return
        }
        input = input.toUpperCase()
        console.log(input);
        $('.expertise').hide()
        $('.expertise:contains("' + input + '")').show()
        if ($('.expertise:visible').length == 0) {
            $('#not-found').show()
        } else {
            $('#not-found').hide()
        }
    }
</script>
<?php if (isset($_GET['search'])) { ?>
    <script>
        filterFAQ('<?= $_GET['search'] ?>');
    </script>
<?php } ?>