<?php

/**
 * Page to see all teaching modules
 * 
 * This file is part of the OSIRIS package.
 * Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * 
 * @link        /teaching
 *
 * @package     OSIRIS
 * @since       1.1.0
 * 
 * @copyright	Copyright (c) 2024 Julia Koblitz, OSIRIS Solutions GmbH
 * @author		Julia Koblitz <julia.koblitz@osiris-solutions.de>
 * @license     MIT
 */
include_once BASEPATH . "/php/Render.php";

$Format = new Document(true);
$form = $form ?? array();

function val($index, $default = '')
{
    $val = $GLOBALS['form'][$index] ?? $default;
    if (is_string($val)) {
        return htmlspecialchars($val);
    }
    return $val;
}

?>
<?php include_once BASEPATH . '/header-editor.php'; ?>



<div class="modal" id="add-teaching" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <a data-dismiss="modal" href="#close-modal" class="btn float-right" role="button" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </a>

            <h3 class="title"><?= lang('Add new teaching module', 'Neue Lehrveranstaltung hinzufügen') ?></h3>
            <p class="text-danger mt-0">
                <?= lang(
                    'Please add only teaching module from an university with a module number.',
                    'Bitte füge hier nur Lehrveranstaltungen von Universitäten mit einer Modulnummer hinzu.'
                ) ?>
            </p>


            <form action="<?= ROOTPATH ?>/crud/teaching/create" method="post" enctype="multipart/form-data" id="activity-form">
                <input type="hidden" class="hidden" name="redirect" value="<?= $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?>">

                <div class="form-group lang-<?= lang('en', 'de') ?>">
                    <label for="title" class="required element-title">
                        <?= lang('Name of the module', 'Name des Moduls') ?>
                    </label>

                    <div class="form-group title-editor" id="title-editor"><?= $form['title'] ?? '' ?></div>
                    <input type="text" class="form-control hidden" name="values[title]" id="title" required value="<?= val('title') ?>">
                </div>

                <script>
                    initQuill(document.getElementById('title-editor'));
                </script>



                <div class="form-row row-eq-spacing" data-visible="students,guests,teaching">

                    <div class="col-sm-2">
                        <label for="module" class="required element-other"><?= lang('Module number', 'Modulnummer') ?></label>
                        <input type="text" class="form-control" name="values[module]" id="module" required value="<?= val('module') ?>" placeholder="MB05">
                    </div>

                    <div class="col-sm">
                        <label for="teaching-affiliation" class="required element-other"><?= lang('Affiliation (Name, City, Country)', 'Einrichtung (Name, Ort, ggf. Land)') ?></label>
                        <input type="text" class="form-control" name="values[affiliation]" id="teaching-affiliation" required value="<?= val('affiliation') ?>" placeholder="TU Braunschweig">
                    </div>

                    <div class="col-sm-4">
                        <label class="required element-author" for="username">
                            <?= lang('Contact person', 'Ansprechpartner:in') ?>
                        </label>
                        <select class="form-control" id="username" name="values[contact_person]" required autocomplete="off">
                            <?php
                            $userlist = $osiris->persons->find([], ['sort' => ['is_active' => -1, 'last' => 1]]);
                            foreach ($userlist as $j) { ?>
                                <option value="<?= $j['username'] ?>" <?= $j['username'] == ($form['user'] ?? $_SESSION['username']) ? 'selected' : '' ?>><?= $j['last'] ?>, <?= $j['first'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <button class="btn secondary" type="submit" id="submit-btn"><i class="ph ph-check"></i> <?= lang("Save", "Speichern") ?></button>

            </form>
        </div>
    </div>
</div>


<!-- <a target="_blank" href="<?= ROOTPATH ?>/docs/add-activities" class="btn tour float-right ml-5" id="docs-btn">
        <i class="ph ph-lg ph-question mr-5"></i>
        <?= lang('Read the Docs', 'Zur Hilfeseite') ?>
    </a> -->

<h1 class="mt-0">
    <i class="ph ph-chalkboard-simple text-osiris mr-5"></i>
    <?= lang('Teaching Modules', 'Lehrveranstaltungen') ?>
</h1>

<div class="btn-toolbar">
    <a href="<?= ROOTPATH ?>/teaching/statistics" class="btn">
        <i class="ph ph-chart-line-up"></i>
        <?= lang('Statistics', 'Statistiken') ?>
    </a>
    <a href="#add-teaching" class="btn link px-0">
        <i class="ph ph-plus"></i>
        <?= lang('Add new teaching module', 'Neue Lehrveranstaltung anlegen') ?>
    </a>
</div>
<br>

<div class="form-group with-icon">
    <input class="form-control mb-20" type="search" name="search" id="search" oninput="filterTeaching(this.value);" placeholder="Filter ...">
    <i class="ph ph-x" onclick="$(this).prev().val('');filterTeaching('')"></i>
</div>


<div class="masonry">

    <?php
    $modules = $osiris->teaching->find([], ['sort' => ['module' => 1]]);
    foreach ($modules as $module) {
        $contact = $DB->getPerson($module['contact_person'] ?? '', true);
    ?>
        <div class="teaching col-md-6">
            <div class="box" id="<?= $module['_id'] ?>">
                <div class="content">
                    <h5 class="mt-0">
                        <span class="highlight-text"><?= $module['module'] ?></span>
                        <?= $module['title'] ?>
                    </h5>

                    <span><?= $module['affiliation'] ?></span>
                    |
                    <a class="" href="<?= ROOTPATH ?>/profile/<?= $module['contact_person'] ?? '' ?>"><?= $contact['displayname'] ?? '' ?></a>

                    <div class="float-right ">
                        <a href="#add-teaching" class="btn text-teaching small" onclick="$('#module').val('<?= $module['module'] ?>');">
                            <i class="ph ph-edit"></i>
                            <span class="sr-only"><?= lang('Edit course', 'Veranstaltung bearbeiten') ?></span>
                        </a>
                        <a href="<?= ROOTPATH ?>/add-activity?type=teaching&teaching=<?= $module['module'] ?>" class="btn text-teaching small">
                            <i class="ph ph-plus"></i>
                            <span class="sr-only"><?= lang('Add course', 'Veranstaltung hinzufügen') ?></span>
                        </a>
                    </div>
                </div>

                <hr>
                <div class="content">
                    <?php
                    $activities = $osiris->activities->find(['module_id' => strval($module['_id'])])->toArray();
                    $activities = $DB::doc2Arr($activities);
                    if (count($activities) != 0) {
                    ?>
                        <a onclick="showAll(this)">
                            <?php
                            $N = count($activities);
                            if ($N == 1)
                                echo lang('Show 1 connected activity', 'Zeige  1 verknüpfte Aktivität');
                            else
                                echo lang('Show ' . $N . ' connected activities', 'Zeige  ' . $N . ' verknüpfte Aktivitäten');
                            ?>
                        </a>
                        <table class="w-full hidden">
                            <?php foreach ($activities as $n => $doc) :
                                if (!isset($doc['rendered'])) renderActivities(['_id' => $doc['_id']]);
                            ?>
                                <tr>
                                    <td class="pb-5">
                                        <?= $doc['rendered']['icon'] ?>
                                        <?= $doc['rendered']['web'] ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>


                    <?php } else { ?>

                        <?= lang('No activities connected.', 'Keine Aktivitäten verknüpft.') ?>
                        <form action="<?= ROOTPATH ?>/crud/teaching/delete/<?= strval($module['_id']) ?>" method="post">
                            <input type="hidden" name="redirect" value="<?= $_SERVER['REQUEST_URI'] ?>">
                            <button class="btn danger small">
                                <i class="ph ph-trash"></i>
                                <?= lang('Delete', 'Löschen') ?>
                            </button>
                        </form>



                    <?php } ?>


                </div>
            </div>
        </div>
    <?php } ?>

</div>

<style>
    .masonry {
        margin: -1rem;
    }

    .teaching {
        padding: 1rem;
    }

    .teaching .box {
        margin: 0;
    }
</style>

<script src="<?= ROOTPATH ?>/js/masonry.pkgd.min.js"></script>
<script>
    // $(document).on('load', function() {
    const layout = {
        // options
        itemSelector: '.teaching',
        // columnWidth: 300,
        columnWidth: '.teaching',
        percentPosition: true,
    }
    var mason = $('.masonry').masonry(layout);
    // });

    function showAll(el) {
        console.log($(el));
        // $(el).closest('.content').find('.hidden').removeClass('hidden');
        // $(el).remove();
        $(el).hide().next().removeClass('hidden')
        mason.masonry(layout)
    }

    function filterTeaching(input) {
        if (input == "") {
            $('.teaching').show()
            mason.masonry(layout)
            return
        }
        // input = input.toUpperCase()
        console.log(input);
        $('.teaching').hide()
        $('.teaching:contains("' + input + '")').show()
        mason.masonry(layout)
    }
</script>