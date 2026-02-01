 <?php

    /**
     * View details of a teaching module
     * 
     * This file is part of the OSIRIS package.
     * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
     *
     * @package     OSIRIS
     * @since       1.8.0
     * 
     * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
     * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
     * @license     MIT
     */
    $distinct_years = $osiris->activities->aggregate([
        ['$match' => [
            'module_id' => strval($module['_id'])
        ]],
        ['$group' => [
            '_id' => '$year',
            'count' => ['$sum' => 1]
        ]],
        ['$sort' => ['_id' => -1]]
    ])->toArray();
    ?>
 <h1>
     <span class="highlight-text"><?= $module['module'] ?></span>
     <?= $module['title'] ?>
 </h1>

 <?php if ($Settings->hasPermission('teaching.edit') || ($module['created_by'] ?? '') == $_SESSION['username']) { ?>

     <div class="btn-toolbar">
         <a href="<?= ROOTPATH ?>/teaching/edit/<?= $module['_id'] ?>" class="btn">
             <i class="ph ph-edit"></i>
             <?= lang('Edit Teaching Module', 'Lehrveranstaltung bearbeiten') ?>
         </a>
     </div>
 <?php } ?>


 <table class="table">
     <tbody>
         <tr>
             <th><?= lang('Module No.', 'Modulnummer') ?></th>
             <td><?= $module['module'] ?></td>
         </tr>
         <tr>
             <th><?= lang('Title', 'Titel') ?></th>
             <td><?= $module['title'] ?></td>
         </tr>
         <tr>
             <th><?= lang('Teaching venue / University', 'Lehrort / Hochschule') ?></th>
             <td>
                 <?php
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
                        $affiliation = htmlspecialchars($module['affiliation']);
                    }
                    echo $affiliation;
                    ?>
             </td>
         </tr>
         <tr>
             <th><?= lang('Contact Person', 'Ansprechpartner') ?></th>
             <td>
                 <?php
                    if (isset($module['contact_person'])) {
                    ?>
                     <a href="<?= ROOTPATH ?>/profile/<?= $module['contact_person'] ?>">
                         <?= $DB->getNameFromId($module['contact_person']) ?>
                     </a>
                 <?php } ?>
             </td>
         </tr>
     </tbody>
 </table>

 <div class="">
     <h2>
         <?= lang('Connected Activities', 'Verknüpfte Aktivitäten') ?>
     </h2>
     <?php
        if (count($activities) != 0) {
        ?>
         <h6>
             <?= lang('Activities with supervisors', 'Aktivitäten mit Betreuenden') ?>
         </h6>
         <table class="table" id="supervisor-table">
             <thead>
                 <tr>
                     <th></th>
                     <th><?= lang('Supervisors', 'Betreuende') ?></th>
                     <th><?= lang('Category', 'Kategorie') ?></th>
                     <th><?= lang('Start Date', 'Anfangsdatum') ?></th>
                     <th><?= lang('End Date', 'Enddatum') ?></th>
                     <th><?= lang('Affiliated', 'Zugehörig') ?></th>
                     <th><?= lang('Total SWS', 'Gesamt SWS') ?></th>
                     <th><?= lang('Affiliated SWS', 'Zugehörige SWS') ?></th>
                 </tr>
             </thead>
             <tbody>
                 <?php foreach ($activities as $n => $doc) :
                        if (!isset($doc['supervisors'])) continue;
                        $supervisors = DB::doc2Arr($doc['supervisors']);
                        $svprint = [];
                        $total = 0;
                        $affiliation = 0;
                        $affiliated = $doc['affiliated'] ?? false;
                        foreach ($supervisors as $a) {
                            $aoi = $a['aoi'] ?? null;
                            if ($aoi === 'true' || $aoi === true || $aoi == "1") {
                                $affiliated = true;
                            }
                            if (isset($a['sws']) && !empty($a['sws'])) {
                                $total += $a['sws'];
                                if ($affiliated) {
                                    $affiliation += floatval($a['sws']);
                                }
                            }
                            $name = $a['first'] . ' ' . $a['last'];
                            if (isset($a['user'])) {
                                $name = '<a href="' . ROOTPATH . '/profile/' . $a['user'] . '">' . $name . '</a>';
                            }
                            $svprint[] =  $name . " (" . ($a['sws'] ?? '0') . " SWS)";
                        }
                    ?>
                     <tr>
                         <td class="w-50">
                             <a href="<?= ROOTPATH ?>/activities/view/<?= strval($doc['_id']) ?>">
                                 <i class="ph ph-eye"></i>
                             </a>
                         </td>
                         <td>
                             <?= implode("<br>", $svprint) ?>
                         </td>
                         <td style="max-width: 20rem;">
                             <a href="<?= ROOTPATH ?>/activities/view/<?= strval($doc['_id']) ?>">
                                 <?= $Document->translateCategory($doc['category'] ?? '-') ?>
                             </a>
                         </td>
                         <td><?= isset($doc['start_date']) ? date('d.m.Y', strtotime($doc['start_date'])) : '-' ?></td>
                         <td><?= isset($doc['end_date']) ? date('d.m.Y', strtotime($doc['end_date'])) : '-' ?></td>
                         <td>
                             <?php if ($affiliated): ?>
                                 <i class="ph ph-check-circle text-primary"></i>
                             <?php else: ?>
                                 <i class="ph ph-x-circle text-secondary"></i>
                             <?php endif ?>
                         <td><?= $total ?></td>
                         <td class="text-weight-bold"><?= $affiliation ?></td>
                     </tr>
                 <?php endforeach; ?>
             </tbody>
         </table>

         <h6>
             <?= lang('All other activities', 'Alle anderen Aktivitäten') ?>
         </h6>

         <table class="table" id="activities-table">
             <thead>
                 <tr>
                     <th></th>
                     <th><?= lang('Type', 'Typ') ?></th>
                     <th><?= lang('Activity', 'Aktivität') ?></th>
                 </tr>
             </thead>
             <tbody>
                 <?php foreach ($activities as $n => $doc) :
                        if (isset($doc['supervisors'])) continue;
                    ?>
                     <tr>
                         <td class="w-50">
                             <a href="<?= ROOTPATH ?>/activities/view/<?= strval($doc['_id']) ?>">
                                 <i class="ph ph-eye"></i>
                             </a>
                         </td>
                         <td class="w-50">
                             <?= $doc['rendered']['icon'] ?>
                         </td>
                         <td>
                             <?= $doc['rendered']['web'] ?>
                         </td>
                     </tr>
                 <?php endforeach; ?>
             </tbody>
         </table>

     <?php } else { ?>

         <?= lang('No activities connected.', 'Keine Aktivitäten verknüpft.') ?>

     <?php } ?>
 </div>

 <?php if ($Settings->hasPermission('teaching.edit')) { ?>
     <div id="delete" class="mt-20">
         <?php if (count($activities) == 0) { ?>

             <form action="<?= ROOTPATH ?>/crud/teaching/delete/<?= strval($module['_id']) ?>" method="post">
                 <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                 <button class="btn danger small">
                     <i class="ph ph-trash"></i>
                     <?= lang('Delete', 'Löschen') ?>
                 </button>
             </form>
         <?php } else { ?>
             <div class="alert warning">
                 <?= lang('Teaching module cannot be deleted because there are activities connected to it.', 'Die Lehrveranstaltung kann nicht gelöscht werden, da Aktivitäten mit ihr verknüpft sind.') ?>
             </div>
         <?php } ?>
     </div>
 <?php } ?>


 <?php if (isset($_GET['verbose'])) {
        dump($module);
    } ?>